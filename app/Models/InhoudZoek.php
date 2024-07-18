<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Middleware\Instelling;


use Illuminate\Support\Facades\DB;

use App\Models\InhoudPersoon;

class InhoudZoek extends Model
{
    use HasFactory;

    /**
     * families()
     * @return array met id-familienaam
     */
    public function families()
    {
        $dbSql = sprintf("
            SELECT id,naam
            FROM families
            ORDER BY naam
        ");

        return DB::select($dbSql);
    }

    /**
     * geboorteplaatsen()
     * @return array met geboorteplaatsen alfabetisch
     */
    public function geboorteplaatsen()
    {
        $dbSql = sprintf("
            SELECT DISTINCT CONCAT(pl.land, ': ', pl.gemeente) AS plaats, pl.id
            FROM persoon ps
            JOIN plaats pl
            ON ps.geborenplaatsID = pl.id
            ORDER BY plaats
        ");

        return DB::select($dbSql);
    }

    /**
     * sterfteplaatsen()
     * @return array met sterfteplaatsen alfabetisch
     */
    public function sterfteplaatsen()
    {
        $dbSql = sprintf("
            SELECT DISTINCT CONCAT(pl.land, ': ', pl.gemeente) AS plaats, pl.id
            FROM persoon ps
            JOIN plaats pl
            ON ps.gestorvenplaatsID = pl.id
            ORDER BY plaats
        ");

        return DB::select($dbSql);
    }

    public function zoekOpdracht($frmDta, $pagina, $aantaPerPagina)
    {
        // verwijder dummy personen
        $inhoudPersoon = new InhoudPersoon();
        $inhoudPersoon->persoonDummyVerwijder();

        // aantal items zoekenresultaat
        $tmp = $this->zoekOpNaam($frmDta, $tel = true, 1e6, 0, $aantaPerPagina);
        $aantal = $tmp['aantal'];
        $aantalPaginas = $tmp['aantalPaginas'];
        $dbRslt = $this->zoekOpNaam($frmDta, $tel = false, $aantal, $pagina, $aantaPerPagina);


        //poging om plaats tabel goed te zetten
        // $temp = $this->onderhoudPlaatsen();

        return [
            'aantal' => $aantal,
            'aantalPaginas' => $aantalPaginas,
            'dbRslt' => $dbRslt['dbRslt'],
            'sqlZoek' => $dbRslt['sqlZoek'],
            'plaatsen' => $this->plaatsen()
        ];

    }

    public function zoekOpNaam($frmDta, $tel, $aantal, $pagina, $aantalPerPagina)
    {
        $overlijdensGrens = Instelling::get('leeftijdsgrens')['overlijden'];

        // ---where criteria
        $dbWhere = [];
        // familie
        if ($frmDta['familie'] > 0)
            $dbWhere[] = sprintf("(`familie1ID`=%1\$d OR `familie2ID`=%1\$d)", $frmDta['familie']);
        //naam
        if (!empty ($frmDta['naam']))
            $dbWhere[] = sprintf("(`naam` LIKE '%%%1\$s%%')", $frmDta['naam']);
        //voornaam
        if (!empty ($frmDta['voornaam']))
            $dbWhere[] = sprintf("(`voornamen` LIKE '%%%1\$s%%')", $frmDta['voornaam']);
        //roepnaam
        if (!empty ($frmDta['roepnaam']))
            $dbWhere[] = sprintf("(`roepnaam` LIKE '%%%1\$s%%')", $frmDta['roepnaam']);
        // geadopteerd
        if ($frmDta['geadopteerd'] == 1)
            $dbWhere[] = sprintf("(`geadopteerd`) = 1");

        // geborenop
        if (!empty ($frmDta['geboortejaar'])) {
            $geboortejaar = explode('-', $frmDta['geboortejaar']);
            if (count($geboortejaar) == 2) {
                $dbWhere[] = sprintf("(YEAR(`geborenop`) BETWEEN %1\$d AND %2\$d)", $geboortejaar[0], $geboortejaar[1]);
            } else {
                $dbWhere[] = sprintf("(YEAR(`geborenop`) = %1\$d)", $geboortejaar[0]);
            }
        }
        //geboorteplaats
        if (!empty ($frmDta['geboorteplaats']))
            $dbWhere[] = sprintf("(`geborenplaatsID` = %1\$d)", $frmDta['geboorteplaats']);

        // gestorvenop
        if (!empty ($frmDta['sterftejaar'])) {
            $sterftejaar = explode('-', $frmDta['sterftejaar']);
            if (count($sterftejaar) == 2) {
                $dbWhere[] = sprintf("(YEAR(`gestorvenop`) BETWEEN %1\$d AND %2\$d)", $sterftejaar[0], $sterftejaar[1]);
            } else {
                $dbWhere[] = sprintf("(YEAR(`gestorvenop`) = %1\$d)", $sterftejaar[0]);
            }
        }
        //sterfteplaats
        if (!empty ($frmDta['sterfteplaats']))
            $dbWhere[] = sprintf("(`gestorvenplaatsID` = %1\$d)", $frmDta['sterfteplaats']);

        if ($tel) {
            $dbSql = sprintf("
            SELECT COUNT(1) AS aantal FROM persoon %s", empty ($dbWhere) ? '' : "WHERE " . implode(' AND ', $dbWhere));

            $aantal = DB::SELECT($dbSql)[0]->aantal;
            $aantalPaginas = ceil($aantal / $aantalPerPagina);

            return [
                'aantal' => $aantal,
                'aantalPaginas' => $aantalPaginas,
            ];
        } else {
            // order by
            $dbOrder = 'ORDER BY geborenop';

            // --- limit
            $aantalPaginas = ceil($aantal / $aantalPerPagina);
            if ($pagina > $aantalPaginas)
                $pagina = $aantalPaginas;
            $dbLimit = sprintf("LIMIT %s,%s", $pagina * $aantalPerPagina, $aantalPerPagina);

            $dbSql = sprintf('
        SELECT *,                   
        CASE 
            WHEN gestorvenop IS NOT NULL THEN
                CASE 
                    WHEN geborenop IS NOT NULL THEN
                        TIMESTAMPDIFF(YEAR, geborenop, gestorvenop)
                    ELSE "Leeftijd onbekend"
                END 
            ELSE
                CASE
                    WHEN geborenop IS NULL THEN 
                        "Leeftijd onbekend"
                    ELSE
                        CASE     
                            WHEN TIMESTAMPDIFF(YEAR, geborenop, CURRENT_DATE) > %4$d THEN
                                "Leeftijd onbekend"
                            ELSE
                                TIMESTAMPDIFF(YEAR, geborenop, CURRENT_DATE)
                        END
                END
        END AS leeftijd
    FROM persoon
            %s
            %s
            %s', empty ($dbWhere) ? '' : "WHERE " . implode(' AND ', $dbWhere), $dbOrder, $dbLimit, $overlijdensGrens);


            $rslt = ['sqlZoek' => $dbSql];
            $rslt['dbRslt'] = DB::SELECT($dbSql);
            return $rslt;

        }
    }

    public function plaatsen()
    {
        // $dbSql = sprintf("
        // SELECT id, CONCAT(gemeente, ' (', land, ')') AS plaats
        // FROM plaats
        // ORDER BY id
        // ");

        // $dbRslt = DB::select($dbSql);
        // $dbReturn = [0 => ''];

        // $dbTel = 1;
        // foreach ($dbRslt as $dbRij) {

        //     if ($dbTel ===$dbRij->id)  {

        //         $dbReturn[] = $dbRij->plaats;
        //     } else {
        //         $dbReturn[] = '';
        //     }
        //     $dbTel++;
        // }

        $dbSql = sprintf("
    SELECT id, CONCAT(gemeente, ' (', land, ')') AS plaats
    FROM plaats
    ORDER BY id
");

        $dbRslt = DB::select($dbSql);
        $dbReturn = [0 => ''];

        $dbTel = 1;
        foreach ($dbRslt as $dbRij) {
            while ($dbTel < $dbRij->id) {
                $dbReturn[] = ''; // Fill in missing IDs with empty strings
                $dbTel++;
            }
            $dbReturn[] = $dbRij->plaats;
            $dbTel++;
        }

        // If there are still IDs missing at the end, fill them with empty strings
        while ($dbTel <= $dbRslt[count($dbRslt) - 1]->id) {
            $dbReturn[] = '';
            $dbTel++;
        }



        return $dbReturn;
    }

    public function persoonInfo($persoonID)
    {
        $overlijdensGrens = Instelling::get('leeftijdsgrens')['overlijden'];
        //persoon
        $dbSql = sprintf('
            SELECT *, gestorvenop AS gestorvendatum,
            CASE 
            WHEN gestorvenop IS NOT NULL THEN
                CASE 
                    WHEN geborenop IS NOT NULL THEN
                        TIMESTAMPDIFF(YEAR, geborenop, gestorvenop)
                    ELSE "Leeftijd onbekend"
                END 
            ELSE
                CASE
                    WHEN geborenop IS NULL THEN 
                        "Leeftijd onbekend"
                    ELSE
                        CASE     
                            WHEN TIMESTAMPDIFF(YEAR, geborenop, CURRENT_DATE) > %2$d THEN
                                "Leeftijd onbekend"
                            ELSE
                                TIMESTAMPDIFF(YEAR, geborenop, CURRENT_DATE)
                        END
                END
            END AS leeftijd

            FROM persoon
            WHERE id=%1$d
        ', $persoonID, $overlijdensGrens);

        $dbPersoon = DB::select($dbSql)[0];


        /** ouders **/
        $dbSql = sprintf('
        SELECT * ,gestorvenop AS gestorvendatum,
        CASE 
        WHEN gestorvenop IS NOT NULL THEN
            CASE 
                WHEN geborenop IS NOT NULL THEN
                    TIMESTAMPDIFF(YEAR, geborenop, gestorvenop)
                ELSE "Leeftijd onbekend"
            END 
        ELSE
            CASE
                WHEN geborenop IS NULL THEN 
                    "Leeftijd onbekend"
                ELSE
                    CASE     
                        WHEN TIMESTAMPDIFF(YEAR, geborenop, CURRENT_DATE) > %3$d THEN
                            "Leeftijd onbekend"
                        ELSE
                            TIMESTAMPDIFF(YEAR, geborenop, CURRENT_DATE)
                    END
            END
        END AS leeftijd
        FROM persoon
            WHERE id IN (SELECT ouder1ID
                         FROM persoon
                         WHERE id=%1$d)
                OR id IN (SELECT ouder2ID
                FROM persoon
                WHERE id=%1$d)             
        ', $persoonID, $persoonID, $overlijdensGrens);

        $dbOuders = DB::select($dbSql);


        //kinderen

        $dbSql = sprintf('
            SELECT * ,gestorvenop AS gestorvendatum
            ,
            CASE 
            WHEN gestorvenop IS NOT NULL THEN
                CASE 
                    WHEN geborenop IS NOT NULL THEN
                        TIMESTAMPDIFF(YEAR, geborenop, gestorvenop)
                    ELSE "Leeftijd onbekend"
                END 
            ELSE
                CASE
                    WHEN geborenop IS NULL THEN 
                        "Leeftijd onbekend"
                    ELSE
                        CASE     
                            WHEN TIMESTAMPDIFF(YEAR, geborenop, CURRENT_DATE) > %3$d THEN
                                "Leeftijd onbekend"
                            ELSE
                                TIMESTAMPDIFF(YEAR, geborenop, CURRENT_DATE)
                        END
                END
            END AS leeftijd
            FROM persoon
            WHERE ouder1ID = %1$d OR ouder2ID = %2$d 
            ORDER BY geborenop ASC
        ', $persoonID, $persoonID, $overlijdensGrens);

        $dbKinderen = DB::select($dbSql);


        return [
            'persoon' => $dbPersoon,
            'ouders' => $dbOuders,
            'kinderen' => $dbKinderen,
            'plaatsen' => $this->plaatsen()
        ];
    }


    public function onderhoudPlaatsen()
    {

        $ndx = 0;
        $dbSql = sprintf("
    SELECT id, CONCAT(gemeente, ' (', land, ')') AS plaats
    FROM plaats
    ORDER BY id
    ");
        $dbRslt = DB::select($dbSql);

        foreach ($dbRslt as $dbRij) {
            $ndx++;

            if ($ndx === $dbRij->id) {
                //record met id $ndx bestaat => niks doen

            } else {
                //haal het laatste record uit de tabel op
                $dbSql = sprintf('
                SELECT id, gemeente, land
                FROM plaats
                ORDER BY id DESC
                LIMIT 1
            ');
                $plaats = DB::SELECT($dbSql)[0];
                $oudePlaatsplaatsID = $plaats->id;

                //verwijder het record uit te tabel
                $dbSql = sprintf('
                DELETE FROM plaats
                WHERE id=%d
            ', $oudePlaatsplaatsID);
                DB::select($dbSql);

                //voeg record toe met id=$ndx
                $dbSql = sprintf('
            INSERT INTO plaats (id, gemeente, land) VALUES (%d, "%s","%s")
            ', $ndx, $plaats->gemeente, $plaats->land);
                DB::SELECT($dbSql);

                //pas geborenplaatsID aan met de nieuwe id ($ndx)
                $dbSql = sprintf('
                UPDATE persoon
                SET geborenplaatsID=%d
                WHERE geborenplaatsID=%d 
            ', $ndx, $oudePlaatsplaatsID);
                DB::SELECT($dbSql);

                //pas gestorvenplaatsID aan met de nieuwe id ($ndx)
                $dbSql = sprintf('
                UPDATE persoon
                SET gestorvenplaatsID=%d
                WHERE gestorvenplaatsID=%d 
            ', $ndx, $oudePlaatsplaatsID);
                DB::SELECT($dbSql);

            }

        }

        //tel het aantal record in de tabel en zet autoincreament veld goed

        $dbSql = sprintf('
        SELECT COUNT(1) as aantal
        FROM plaats 
    ');

        $aantal = DB::SELECT($dbSql)[0]->aantal;
        $aantal += 1;

        $dbsql = sprintf('
            ALTER TABLE plaats AUTO_INCREMENT=%d
            ', $aantal);
        DB::select($dbSql);



    }
}