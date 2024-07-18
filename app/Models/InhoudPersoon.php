<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\InhoudZoek;
use App\Models\Exception;
use App\Http\Middleware\Instelling;

class InhoudPersoon extends Model
{
    protected $leeftijdsGrensOuder;    
    protected $leeftijdsGrensKind;
    protected $overlijdensGrens;

    public function __construct()
    {
            $this->leeftijdsGrensOuder = Instelling::get('leeftijdsgrens')['ouder'];
            $this->leeftijdsGrensKind = Instelling::get('leeftijdsgrens')['kind'];
            $this->overlijdensGrens = Instelling::get('leeftijdsgrens')['overlijden'];
    }


    /**
     * public function persoon($persoonID)
     * haalt informatie van betreffende persoon op
     * @param $persoonID
     * @return array
     */
    public function persoon($persoonID)
    {
        $dbSql = sprintf('
            SELECT p.id, p.naam, p.voornamen, p.roepnaam,
                DATE_FORMAT(p.geborenop, "%%d-%%m-%%Y") AS geborendatum,
                CONCAT(pl1.gemeente, ", ", pl1.land) AS geborenplaats,
                p.geborenop, p.geborenplaatsID,
                DATE_FORMAT(p.gestorvenop, "%%d-%%m-%%Y") AS gestorvendatum,
                CONCAT(pl2.gemeente, ", ", pl2.land) AS gestorvenplaats,
                p.gestorvenop, p.gestorvenplaatsID,
                p.sex,p.geadopteerd, p.info,
            CASE 
                WHEN p.gestorvenop IS NOT NULL THEN
                    CASE 
                        WHEN p.geborenop IS NOT NULL THEN
                            ROUND(DATEDIFF(p.gestorvenop, p.geborenop)/365.2425)
                        ELSE "Leeftijd onbekend"
                    END 
                ELSE
                    CASE
                        WHEN p.geborenop IS NULL THEN 
                            "Leeftijd onbekend"
                        ELSE
                            CASE     
                                WHEN ROUND(DATEDIFF(CURRENT_DATE, p.geborenop)/365.2425) > %d THEN
                                    "Leeftijd onbekend"
                                ELSE
                                    ROUND(DATEDIFF(CURRENT_DATE, p.geborenop)/365.2425)
                            END
                    END

            END AS leeftijd

            FROM persoon p
            LEFT OUTER JOIN plaats pl1 ON p.geborenplaatsID = pl1.ID
            LEFT OUTER JOIN plaats pl2 ON p.gestorvenplaatsID = pl2.ID
            WHERE p.id=%d
        ', $this->overlijdensGrens,$persoonID);


       // print($dbSql); die();

        return DB::select($dbSql)[0];
    }

    /**
     * public function persoonFamilies()
     * families gerelateerd aan persoon
     * @param $persoonID
     * @return array
     */
    public function persoonFamilies($persoonID)
    {
        $dbSql = sprintf('
            SELECT id, naam
            FROM families
            WHERE id IN (SELECT familie1ID FROM persoon WHERE id=%1$d)
               OR id IN (SELECT familie2ID FROM persoon WHERE id=%1$d)
        ', $persoonID);

        return DB::select($dbSql);
    }

    /* --- FAMILIES --- */
    /**
     * public function familieInfo($familieID)
     * haalt info familie op
     * @param $familieID
     * @return array
     */
    public function familieInfo($familieID)
    {
        $dbSql = sprintf('
            SELECT id, naam
            FROM families
            WHERE id=%d
        ', $familieID);

        $dbRslt = DB::select($dbSql);

        return $dbRslt ? $dbRslt[0] : false;
    }

    /**
     * public function familieVerwijder($familieID, $persoonID)
     * verwijder inhoud familie-veld
     * @param $familieID
     * @param $persoonID
     * @return array
     */
    public function familieVerwijder($familieID, $persoonID)
    {
        $dbSql = sprintf('
            UPDATE persoon
            SET familie1ID = CASE WHEN familie1ID=%1$d THEN NULL ELSE familie1ID END,
                familie2ID = CASE WHEN familie2ID=%1$d THEN NULL ELSE familie2ID END
            wHERE id=%2$d
        ', $familieID, $persoonID);
        return DB::select($dbSql);

    }

    /**
     * public function families()
     * haalt lijst met families op
     * @return array
     */
    public function families()
    {
        $oInhoudzoek = new InhoudZoek();
        return $oInhoudzoek->families();

    }

    /**
     * public function familieBewaar($familieNaam, $familieID)
     * @param $familieNaam
     * @param $familieID
     */
    public function familieBewaar($familieNaam, $familieID, $persoonID)
    {
        if ($familieID) {
            $dbSql = sprintf('
                UPDATE `families`
                SET `naam` = "%s"
                WHERE `id` = %d
            ', $familieNaam, $familieID);

            DB::select($dbSql);

            return [$familieID, $familieNaam];
        } else {
            // controleer of familie reeds bestaat
            $dbSql = sprintf('
                SELECT id, naam
                FROM families
                WHERE UPPER(naam) = "%s"
            ', strtoupper($familieNaam));

            $dbRslt = DB::select($dbSql);
            if (count($dbRslt) > 0)
                return [$dbRslt[0]->id, $dbRslt[0]->naam];

            // familie bestaat niet -> toevoegen
            $dbSql = sprintf('
                INSERT INTO `families` (`naam`)
                VALUES ("%s")
            ', $familieNaam);

            DB::select($dbSql);

            return [DB::getPdo()->lastInsertId(), $familieNaam];
        }
    }

    public function familiePersoon($persoonID, $familieID)
    {
        // haal familie1ID en familie2ID op
        // $dbSql = sprintf('
        //     SELECT familie1ID, familie2ID
        //     FROM persoon
        //     WHERE id = %d
        // ', $persoonID);

        // $dbRslt = DB::select($dbSql)[0];

        // if (!$dbRslt->familie1ID) {
        //     $dbSql = sprintf('
        //         UPDATE persoon
        //         SET familie1ID = %d
        //         WHERE id = %d
        //     ', $familieID, $persoonID);

        //     DB::select($dbSql);
        // } elseif (!$dbRslt->familie2ID) {
        //     $dbSql = sprintf('
        //         UPDATE persoon
        //         SET familie2ID = %d
        //         WHERE id = %d
        //     ', $familieID, $persoonID);
        //     DB::select($dbSql);
        //}
    }

    /**
     * public function familieLinkBewaar
     * link een familie aan een persoon
     * @param $persoonID
     * @param $familieID
     * @return array
     */

    public function familieLinkBewaar($familieID, $persoonID)
    {
              // haal familie1ID en familie2ID op
              $dbSql = sprintf('
              SELECT familie1ID, familie2ID
              FROM persoon
              WHERE id = %d
          ', $persoonID);

              $dbRslt = DB::select($dbSql)[0];

              if (!$dbRslt->familie1ID) {
                  $dbSql = sprintf('
                  UPDATE persoon
                  SET familie1ID = %d
                  WHERE id = %d
              ', $familieID, $persoonID);
                  DB::select($dbSql);
              } elseif (!$dbRslt->familie2ID) {
                  $dbSql = sprintf('
                  UPDATE persoon
                  SET familie2ID = %d
                  WHERE id = %d
              ', $familieID, $persoonID);
                  DB::select($dbSql);
              }
        return $this->persoonFamilies($persoonID);
    }




    /**
     * public function ouders($persoonID)
     * haalt de ouders van de betrokken persoon op
     * @param $persoonID
     * @return array
     */

    public function ouders($persoonID)
    {
        //$overlijdensGrens = Instelling::get('leeftijdsgrens')['overlijden'];
        $dbSql = sprintf('
            SELECT p.id, p.naam, p.voornamen, p.roepnaam,
                DATE_FORMAT(p.geborenop, "%%d-%%m-%%Y") AS geborendatum, CONCAT(pl1.gemeente, ", ", pl1.land) AS geborenplaats, geborenplaatsID,
                DATE_FORMAT(p.gestorvenop, "%%d-%%m-%%Y") AS gestorvendatum, CONCAT(pl2.gemeente, ", ", pl2.land) AS gestorvenplaats, gestorvenplaatsID,
                p.geadopteerd, p.info, p.sex,
                   
                CASE 
                    WHEN p.gestorvenop IS NOT NULL THEN
                        CASE 
                            WHEN p.geborenop IS NOT NULL THEN
                                ROUND(DATEDIFF(p.gestorvenop, p.geborenop)/365.2425)
                            ELSE "Leeftijd onbekend"
                        END 

                    ELSE
                        CASE 
                            WHEN ROUND((DATEDIFF(CURRENT_DATE, p.gestorvenop)/365.2425  > %2$d) OR p.geborenop IS NULL) THEN
                                "Leeftijd onbekend"
                            ELSE
                                ROUND(DATEDIFF(CURRENT_DATE, p.geborenop)/365.2425)

                        end    
                END AS leeftijd

            FROM persoon p
            LEFT OUTER JOIN plaats pl1
                ON p.geborenplaatsID = pl1.id
            LEFT OUTER JOIN plaats pl2
                ON p.gestorvenplaatsID = pl2.id
            WHERE p.id = (SELECT ouder1ID FROM persoon WHERE id = %1$d)
              OR p.id = (SELECT ouder2ID FROM persoon WHERE id = %1$d)
            ORDER BY geborenop, voornamen
        ', $persoonID, $this->overlijdensGrens);
        //print($dbSql);die();

        return DB::select($dbSql);
    }


    /**
     * public function ouderInfo($ouderID);
     * haalt informatie ouder op
     * @param $ouderID
     * @return array

     */

    public function ouderInfo($ouderID)
    {
        return $this->persoon($ouderID);
    }

    /**
     * public function ouderVerwijder($ouderID, $persoonID);
     * verwijderd ouder van persoon
     * @param $ouderID
     * @param $persoonID
     * @return array
     */
    public function ouderVerwijder($ouderID, $persoonID)
    {
        $dbSql = sprintf('
            UPDATE persoon
            SET ouder1ID = CASE WHEN ouder1id=%1$d  THEN NULL ELSE ouder1ID END,
                ouder2ID = CASE WHEN ouder2id=%1$d  THEN NULL ELSE ouder2ID END
            WHERE id=%2$d
        
        ', $ouderID, $persoonID);
        return DB::select($dbSql);
    }

    /**
     * public function ouderLijst()
     * lijst van personen die ouder zijn dan de huidige persoon !instelling leeftijdsgrens
     * @param $persoonID
     * @return array
     * 
     */
    public function ouderLijst($persoonID) 
    {
        
        $dbSql = sprintf('
            SELECT geborenop
            FROM persoon
            WHERE id = %d
            
        ', $persoonID);
        $geborenop = DB::select($dbSql);

        $dbSql = sprintf('
            SELECT gestorvenop
            FROM persoon
            WHERE id = %d
        ', $persoonID);

        $gestorvenop = DB::select($dbSql);

        
    
        $dbSql = sprintf('
SELECT 
    p.id, 
    p.naam, 
    p.voornamen, 
    p.roepnaam, 
    DATE_FORMAT(p.geborenop, "%%d-%%m-%%Y") AS geborendatum, 
    CONCAT(pl1.gemeente, ", ", pl1.land) AS geborenplaats, 
    p.geborenop, 
    p.geborenplaatsID, 
    DATE_FORMAT(p.gestorvenop, "%%d-%%m-%%Y") AS gestorvendatum, 
    CONCAT(pl2.gemeente, ", ", pl2.land) AS gestorvenplaats, 
    p.gestorvenop, 
    p.gestorvenplaatsID, 
    CASE 
        WHEN p.gestorvenop IS NOT NULL THEN 
            CASE 
                WHEN p.geborenop IS NOT NULL THEN ROUND(DATEDIFF(p.gestorvenop, p.geborenop) / 365.2425) 
                ELSE "Leeftijd onbekend" 
            END 
        ELSE 
            CASE 
                WHEN p.geborenop IS NOT NULL THEN ROUND(DATEDIFF(CURRENT_DATE, p.geborenop) / 365.2425) 
                ELSE "Leeftijd onbekend" 
            END 
    END AS leeftijd 
FROM 
    persoon p 
LEFT OUTER JOIN 
    plaats pl1 ON p.geborenplaatsID = pl1.id 
LEFT OUTER JOIN 
    plaats pl2 ON p.gestorvenplaatsID = pl2.id 
WHERE 
    (
        (SELECT geborenop FROM persoon WHERE id = %1$d) IS NULL 
        OR (DATE(p.geborenop) BETWEEN 
            DATE_SUB((SELECT geborenop FROM persoon WHERE id = %1$d), INTERVAL %3$d YEAR) 
            AND DATE_SUB((SELECT geborenop FROM persoon WHERE id = %1$d), INTERVAL %2$d YEAR)
        
        OR p.geborenop IS NULL)
    ) 
 
    AND p.naam IS NOT NULL 
    AND p.voornamen IS NOT NULL 
ORDER BY 
    p.geborenop DESC,
    p.naam, 
    p.voornamen;

            ', $persoonID, $this->leeftijdsGrensOuder[0], $this->leeftijdsGrensOuder[1]);

            //print($dbSql); die();

      

        return DB::select($dbSql);

    
       

    
    return DB::select($dbSql);
    
    }

    /**
     * public function ouderBewaar()
     * koppel ouder aan persoon
     * @param $persoonID
     * @param $ouderID
     * @return array
     * 
     */
    public function ouderBewaar($persoonID, $ouderID)
    {
        // haal ouder1ID en ouder2ID op
        $dbSql = sprintf('
            SELECT ouder1ID, ouder2ID
            FROM persoon
            WHERE id = %d
        ', $persoonID);

        $dbRslt = DB::select($dbSql);
        //print($dbRslt[0]->ouder1ID); die() ;   

        if (!$dbRslt[0]->ouder1ID) {
            $dbSql = sprintf('
            UPDATE persoon
            SET ouder1ID = %d
            WHERE id = %d
        ', $ouderID, $persoonID);
            DB::select($dbSql);
        } elseif (!$dbRslt[0]->ouder2ID) {
            $dbSql = sprintf('
            UPDATE persoon
            SET ouder2ID = %d
            WHERE id = %d
        ', $ouderID, $persoonID);
            DB::select($dbSql);
        }
        return $this->ouders($persoonID);
    }

    /** --KINDEREN-- */

    /**
     * public function kinderen($persoonID)
     * haalt de kinderen van de betrokken persoon op
     * @param $persoonID
     * @return array
     */
    public function kinderen($persoonID)
    {
        $dbSql = sprintf('
            SELECT p.id, p.naam, p.voornamen, p.roepnaam,
                   DATE_FORMAT(p.geborenop, "%%d-%%m-%%Y") AS geborendatum, CONCAT(pl1.gemeente, ", ", pl1.land) AS geborenplaats, geborenplaatsID,
                   DATE_FORMAT(p.gestorvenop, "%%d-%%m-%%Y") AS gestorvendatum, CONCAT(pl2.gemeente, ", ", pl2.land) AS gestorvenplaats, gestorvenplaatsID,          
                   p.geadopteerd, p.info, p.sex,
                   
                   CASE 
                   WHEN p.gestorvenop IS NOT NULL THEN
                       CASE 
                           WHEN p.geborenop IS NOT NULL THEN
                               ROUND(DATEDIFF(p.gestorvenop, p.geborenop)/365.2425)
                           ELSE "Leeftijd onbekend"
                       END 
                       
               ELSE	CASE
                   WHEN p.gestorvenop IS NULL THEN
                       CASE 
                           WHEN ROUND(DATEDIFF(CURRENT_DATE, p.geborenop)/365.2425) > %2$d THEN
                               "Leeftijd onbekend"
                           ELSE
                               ROUND(DATEDIFF(CURRENT_DATE, p.geborenop)/365.2425)
                       END
                   end    
               END AS leeftijd

            FROM persoon p
            LEFT OUTER JOIN plaats pl1
              ON p.geborenplaatsID = pl1.id
            LEFT OUTER JOIN plaats pl2
              ON p.gestorvenplaatsID = pl2.id
            WHERE ouder1ID=%1$d
               OR ouder2ID=%1$d
            ORDER BY DATE_FORMAT(p.geborenop, "%%Y-%%m"), voornamen
        ', $persoonID, $this->overlijdensGrens);

           return DB::select($dbSql);
    }

    /**
     * public function kindInfo($kindID)
     * haalt informatie kind op
     * @param $kindID
     * @return array
     */
    public function kindInfo($kindID)
    {
        return $this->persoon($kindID);
    }

    /**
     * public function kindVerwijder($kindID, $persoonID)
     * verwijdert persoon van kind
     * @param $kindID
     * @param $persoonID
     * @return array
     */
    public function kindVerwijder($kindID, $persoonID)
    {
        $dbSql = sprintf('
            UPDATE  persoon
            SET ouder1ID = CASE WHEN ouder1ID=%1$d  THEN NULL ELSE ouder1ID END,
                ouder2ID = CASE WHEN ouder2ID=%1$d  THEN NULL ELSE ouder2ID END
            WHERE id=%2$d
        ', $persoonID, $kindID);

        return DB::select($dbSql);

    }
    /**
     * public function jxInhoudPersoonKindLijst()
     * levert lijst op van personen die jonger zijn dan huidige persoon
     * @param $persoonID
     * @return array
     */
    public function kindLijst($persoonID)
    {
        $dbSql = sprintf('
        SELECT p.id, 
            p.naam, 
            p.voornamen, 
            p.roepnaam, 
            DATE_FORMAT(p.geborenop, "%%d-%%m-%%Y") AS geborendatum, 
            CONCAT(pl1.gemeente, ", ", pl1.land) AS geborenplaats, 
            p.geborenop, 
            p.geborenplaatsID, 
            DATE_FORMAT(p.gestorvenop, "%%d-%%m-%%Y") AS gestorvendatum, 
            CONCAT(pl2.gemeente, ", ", pl2.land) AS gestorvenplaats, 
            p.gestorvenop, 
            p.gestorvenplaatsID, 
            CASE 
                WHEN p.gestorvenop IS NOT NULL THEN 
                    CASE 
                        WHEN p.geborenop IS NOT NULL THEN ROUND(DATEDIFF(p.gestorvenop, p.geborenop)/365.2425) 
                        ELSE "Leeftijd onbekend" 
                    END 
                ELSE 
                    CASE 
                        WHEN p.gestorvenop IS NULL THEN 
                            CASE 
                                WHEN ROUND(DATEDIFF(p.gestorvenop, CURRENT_DATE)/365.2425) > %4$d THEN "Leeftijd onbekend" 
                                ELSE ROUND(DATEDIFF(p.gestorvenop, CURRENT_DATE)/365.2425) 
                            END 
                    END 
            END AS leeftijd 
            FROM persoon p 
            LEFT OUTER JOIN plaats pl1 ON p.geborenplaatsID = pl1.id 
            LEFT OUTER JOIN plaats pl2 ON p.gestorvenplaatsID = pl2.id 
            WHERE (
                (SELECT geborenop FROM persoon WHERE id = %1$d) IS NULL 
                    OR (DATE(p.geborenop) BETWEEN 
                    ((SELECT geborenop FROM persoon WHERE id = %1$d) + INTERVAL %4$d YEAR) 
                    AND ((SELECT geborenop FROM persoon WHERE id = %1$d) + INTERVAL %3$d YEAR)) 
                    OR p.geborenop IS NULL
                ) 
                AND (
                    (p.ouder1ID IS NULL AND p.ouder2ID IS NULL) 
                    OR (p.ouder2ID IS NULL AND p.ouder1ID != %1$d) 
                    OR (p.ouder1ID IS NULL AND p.ouder2ID != %1$d)
                ) 
                AND p.naam IS NOT NULL 
                AND p.voornamen IS NOT NULL 
                ORDER BY p.geborenop DESC, p.naam, p.voornamen
            ', $persoonID, $this->leeftijdsGrensKind[0], $this->leeftijdsGrensKind[1], $this->overlijdensGrens);

            //print($dbSql); die();

      

        return DB::select($dbSql);
        // AND (p.ouder1ID IS NULL OR p.ouder2ID IS NULL)
        // AND (p.ouder1ID != %1$d OR p.ouder2ID != %1$d)

    }

    /**
     *  public function kindBewaar
     * kent kind aan ouder toe
     * @param $persoonID
     * @param $kindID 
     * @return array
     */

    public function kindBewaar($persoonID, $kindID)
    {
        $dbSql = sprintf('
           SELECT ouder1ID, ouder2ID
           FROM persoon 
           WHERE id = %d
        ', $kindID);

        $dbRslt = DB::select($dbSql)[0];

        $ouder = '';
        if (!$dbRslt->ouder1ID) {
            $ouder = 'ouder1ID';
        } elseif (!$dbRslt->ouder2ID) {
            $ouder = 'ouder2ID';
        } else {
            return $this->kinderen($persoonID);
        }

        $dbSql = sprintf('
            UPDATE persoon
            SET %s = %d
            WHERE id = %d
        ', $ouder, $persoonID, $kindID);

        DB::select($dbSql);
        return $this->kinderen($persoonID);

    }
    /**
     *  public function documentLijst
     * haalt lijst documenten database op
     * @param $persoonID
     * @return array
     */
    public function documentLijst($persoonID)
    {
        $dbSql = sprintf('
            SELECT id, CONCAT("%s", "/", bestand) AS bestand, titel, info
            FROM document
            WHERE id IN (
                SELECT documentID
                FROM persoondocument
                WHERE persoonID=%d
            )
            ORDER BY info
        ', instelling::get('upload')['map'], $persoonID);
        return DB::select($dbSql);
    }


    /**
     * public function persoonBewaar($frmDta
     * update gegevens persoon
     * @param $frmDta
     * @return integer
     */
    public function persoonBewaar($frmDta)
    {
        $status = 0;

        try {
            $persoonID = $frmDta['persoonID'];
            $naam = $frmDta['naam'];
            $voornamen = $frmDta['voornamen'];
            $roepnaam = $frmDta['roepnaam'];
            $sex = $frmDta['sex'];
            $geadopteerd = $frmDta['geadopteerd'];
            $geborenop = $frmDta['geborenop'] ? "\"{$frmDta['geborenop']}\"" : "NULL";
            $geborenplaatsID = $frmDta['geborenplaatsID'] ? $frmDta['geborenplaatsID'] : "NULL";
            $gestorvenop = $frmDta['gestorvenop'] ? "\"{$frmDta['gestorvenop']}\"" : "NULL";
            $gestorvenplaatsID = $frmDta['gestorvenplaatsID'] ? $frmDta['gestorvenplaatsID'] : "NULL";
            $info = addslashes($frmDta['info']);

            $dbSql = sprintf('
            
                UPDATE `persoon`
                SET naam = "%s",
                    voornamen = "%s",
                    roepnaam = "%s",
                    sex = "%s",
                    geadopteerd = %d,
                    geborenop = %s,
                    geborenplaatsID = %d,
                    gestorvenop = %s,
                    gestorvenplaatsID = %d,
                    info = "%s"
                WHERE id = %d    
            ', $naam, $voornamen, $roepnaam, $sex, $geadopteerd, $geborenop, $geborenplaatsID, $gestorvenop, $gestorvenplaatsID, $info, $persoonID);

            DB::select($dbSql);
            $status = 1;
        } catch (\Exception $ex) {

        }

        return $status;

    }

    /**
     * public function persoonDummyVerwijder()
     * verwijderd alle lege personen: personen zonder naam en voornamen
     * @return void
     */

    public function persoonDummyVerwijder()
    {
        $tijdstip = Carbon::now();

        $dbSql = sprintf('
            DELETE FROM persoon
            WHERE naam IS NULL
                AND voornamen IS NULL
                AND TIMESTAMPDIFF(MINUTE, created_at, "%s") >= %d
        ', $tijdstip->format('Y-m-d H:i:s'), Instelling::get('tijd-dummy-persoon'));

        DB::select($dbSql);
    }

    /**
     *  public function persoonNieuw()
     *  maakt een nieuwe persoon aan in de database
     *  @return int
     */

    public function persoonNieuw()
    {
        $tijdstip = Carbon::now();

        $dbSql = sprintf('
            INSERT INTO persoon (created_at)
            VALUES ("%s")
        ', $tijdstip->format('Y-m-d H:i:s'));

        DB::select($dbSql);

        return DB::getPdo()->lastInsertId();

    }

    /**
     * public function verwijderPersoon($persoonID))
     * verwijdert persoon uit database :
     *      - persoon
     *      - gekoppelde documenten
     *      - verwijder kinderen
     * @param $persoonID
     * @return boolean
     */

    public function verwijderPersoon($persoonID)
    {
        DB::beginTransaction();

        try {
            // van kinderen verwijder ouderID
            $dbSql = sprintf('
                UPDATE persoon
                SET ouder1ID = CASE WHEN ouder1ID=%1$d THEN NULL ELSE ouder1ID END,
                    ouder2ID = CASE WHEN ouder2ID=%1$d THEN NULL ELSE ouder2ID END
                
            ', $persoonID);
            DB::select($dbSql);

            // verwijder persoon
            $dbSql = sprintf('
                DELETE FROM persoon
                WHERE id = %d
            ', $persoonID);
            DB::select($dbSql);

            // verwijder uit persoondocument
            $dbSql = sprintf('
                DELETE FROM persoondocument
                WHERE persoonID=%d
            ', $persoonID);
            DB::select($dbSql);

            DB::commit();
            return true;
        } catch (\Exception $ex) {
            DB::rollBack();
            return false;
        }
    }


    /**
     * public function plaatsBewaar() 
     * bewaar de plaats en voeg eventueel nieuwe plaats toe aan plaatsen
     * @param plaatsveld
     * @param id
     * @param gemeente
     * @param land
     * @return array      
     */

    //   public function plaatsBewaar($persoonID, $plaatsveld, $id, $gemeente, $land) {
    //     //indien gemeente nog niet in de tabel plaatsz
    //     if ($id == 0) {
    //         $dbSql = sprintf('
    //             INSERT INTO plaats (gemeente, land)
    //             VALUES ("%s","%s")

    //         ', $gemeente, $land);

    //        // echo($dbSql);die();

    //         DB::select($dbSql);
    //         $id = DB::getPdo()->lastInsertId();
    //     }

    //     //schrijf de id van de gemeente weg naar de geboorteplaats of sterfteplaats
    //     switch ($plaatsveld) {
    //         case ('geboren'):
    //             $dbSql = sprintf('
    //                 UPDATE persoon
    //                 SET geborenplaatsID=%d
    //                 WHERE id=%d
    //             ', $id, $persoonID);
    //             break;
    //         case ('gestorven'):
    //             $dbSql = sprintf('
    //                 UPDATE persoon
    //                 SET gestorvenplaatsID=%d
    //                 WHERE id=%d
    //             ', $id, $persoonID);
    //             break;
    //     }
    //     return  DB::select($dbSql);


    //   }

    /* --- PLAATSEN ---*/

    /**
     * public funtion plaatsen()
     * haamt lijst op mezt plaatsnamen
     * @return array
     * 
     */

    public function plaatsen()
    {
        $dbSql = sprintf('
            SELECT id, CONCAT(gemeente, ", " , land) AS plaats
            FROM plaats
            ORDER BY land, gemeente
        ');

        return DB::select($dbSql);


    }

    /**
     * public function plaatsnieuw() 
     * bewaar plaats en levert ID plaats op
     * @param string land
     * @param string gemeente
     * @return       
     */
    public function plaatsNieuw($gemeente, $land)
    {
        // land en gemeente moeten inhoud

        if (strlen($gemeente) == 0 || strlen($land) == 0)
            return false;


        // bestaat plaats al?
        $dbSql = sprintf('
            SELECT id, CONCAT(gemeente, ", ", land) AS plaats
            FROM plaats
            WHERE gemeente = "%s" AND land = "%s"
        ', $gemeente, $land);

        $dbRslt = DB::select($dbSql);
        if (count($dbRslt) == 1) {
            return $dbRslt[0];
        }

        // plaats bestaat niet -> invoegen
        $dbSql = sprintf('
            INSERT INTO plaats (gemeente, land)
            VALUES ("%s","%s")
        ', $gemeente, $land);

        DB::select($dbSql);

        $plaatsID = DB::getPdo()->lastInsertId();

        $dbSql = sprintf('
        SELECT id, CONCAT(gemeente, ", ", land) AS plaats
        FROM plaats
        WHERE id = %d
        LIMIT 1
    ', $plaatsID);

        //echo($dbSql); die();
        return DB::select($dbSql);

    }



    /* --- DOCUMENTEN --- */

    /**
     * public function documentBewaar($persoonID, $bestandsNaam)
     * koppelt document of foto aan persoon
     * @param $persoonID
     * @param $bestandsNaam
     * @return integer $documentID
     */
    public function documentBewaar($persoonID, $bestandsNaam)
    {
        $dbSql = sprintf('
            INSERT INTO document (typenID, bestand, titel, info)
            VALUES (0, "%s", "- todo -", "")',
            $bestandsNaam
        );

        DB::select($dbSql);

        $documentID = DB::getPdo()->lastInsertId();

        $dbSql = sprintf('
            INSERT INTO persoondocument (persoonID, documentID)
            VALUES ("%s", "%s")',
            $persoonID,
            $documentID
        );

        DB::select($dbSql);

        return $documentID;
    }


    public function document($documentID)
    {
        $dbSql = sprintf('
            SELECT *
            FROM document
            WHERE id= %d
        ', $documentID);
        return DB::select($dbSql)[0];
    }

    /**
     * public function documentMeta()
     * update meta (titel, info) van het bertreffende document
     
     * @param $documentID
     * @param $documentTitel
     * @param $documentInfo
     * @return void
     */
    public function documentMeta($documentID, $documentTitel, $documentInfo)
    {
        $dbSql = sprintf('
            UPDATE document
            SET titel = "%s",
                info = "%s"
            WHERE id = %d    
        ', $documentTitel, $documentInfo, $documentID);

        DB::select($dbSql);
    }

    /**
     *  public function documentVerwijder($persoonID, $documentID)
     * gegevens document aan persoon verwijderen
     * @param $persoonID
     * @param $documentID
     * @reurn void
     */
    public function documentVerwijder($persoonID, $documentID)
    {
        $dbSql = sprintf('
            DELETE FROM persoondocument
            WHERE persoonID = %d
            AND documentID = %d
        ', $persoonID, $documentID);

        DB::select($dbSql);

        $dbSql = sprintf('
            DELETE FROM document
            WHERE id = %d
        ', $documentID);

        DB::select($dbSql);
    }

    /**
     *  public function tree()
     * haalt mermaid tree van betreffende persoon op
     * @param $persoonID
     * @param $familieID
     * @reurn array
     */
    public function tree($persoonID, $familieID)
    {
        $rslt = [
            'succes' => false,
            'families' => [],
            'familieID' => $familieID,
            'tree' => [],
            'boodschap' => '',
            'persoonID' => $persoonID,
            'ouderID' => 0,
            'grootouderID' => 0
        ];

        // --- families ---
        // indien geen families gekoppeld aan persoon -> foutmelding
        $dbRsltFamilies = $this->_treeFamilies($persoonID);
        if (count($dbRsltFamilies) == 0) {
            $rslt['boodschap'] = __('boodschappen.inhoudpersoon_treefoutfamilies');
            return $rslt;
        }

        
        // familieID is 0 => ken eerste familie uit resultaat toe
        if ($familieID == 0)
            $familieID = $dbRsltFamilies[0]->id;
        $rslt['familieID'] = $familieID;
        $rslt['families'] = $dbRsltFamilies;

       
        
        // --- ouder ---
        // haal ID ouder op van persoon, anders standaard 0
        $ouderID = $this->_treeOuderIDVan($persoonID, $familieID);
        

        $rslt['ouderID'] = $ouderID ? $ouderID : 0;

        // haal ID van grootouder op, standaard false
        $grootouderID =null;
        if ($ouderID > 0)
            $grootouderID = $this->_treeOuderIDVan($ouderID, $familieID);
        $rslt['grootouderID'] = $grootouderID  ? $grootouderID : 0;
        
        // --- tree ---
        // ophalen kleuren tree
        $treeKleur = Instelling::get('tree.kleur');

        // de tree(nog een zaadje)
        $tree = sprintf("flowchart LR\nclassDef standaard %s\nclassDef lijn %s\nclassDef persoon %s\n", $treeKleur['standaard'], $treeKleur['lijn'], $treeKleur['persoon']);



        // GENERATIE 3: GROOTOUDER
        if ($grootouderID > 0) {
            $grootouder = $this->_treeTotText($this->persoon($grootouderID));
            $tree .= sprintf("%s:::lijn\n", $grootouder);
        }
        
        // GENERATIE 2: OUDER - OOM - TANTE
       
        if ($grootouderID> 0) {
            $dbRslt = $this->_treeKinderen($grootouderID);
            $grootouder = sprintf("ID-%s --- ", $grootouderID);
            foreach($dbRslt as $dbKind) {
                $kleur = $dbKind->id == $ouderID ? 'lijn' : 'standaard';
                $kind = $this->_treeTotText($this->persoon($dbKind->id));
                $tree .= sprintf("%s%s:::%s\n", $grootouder, $kind , $kleur);
            }
            

        } 
        else {
            $grootouder = '';
            if ($ouderID > 0) {

            
                $ouder = $this->_treeTotText($this->persoon($ouderID));
                $tree .= sprintf("%s%s:::lijn\n", $grootouder, $ouder);
            }
        }

       
        // GENERATIE 1: PERSOON - BROER - ZUS

        if ($ouderID> 0) {
            $dbRslt = $this->_treeKinderen($ouderID);
            $ouder = sprintf("ID-%s --- ", $ouderID);
            foreach($dbRslt as $dbKind) {
                $kleur = $dbKind->id == $persoonID ? 'persoon' : 'standaard';
                $kind = $this->_treeTotText($this->persoon($dbKind->id));
                $tree .= sprintf("%s%s:::%s\n", $ouder, $kind , $kleur);
            }

        } else {
            $kind = $this->_treeTotText($this->persoon($persoonID));
            $tree .= sprintf("%s:::persoon\n", $kind);
        }


        // GENERATIE 0: KINDEREN

        $dbRslt = $this->_treeKinderen($persoonID);
        if (count($dbRslt) > 0) {
            $persoon = sprintf("ID-%s --- ", $persoonID);
            foreach($dbRslt as $dbKind) {
                $kind = $this->_treeTotText($this->persoon($dbKind->id));
                $tree .= sprintf("%s%s:::lijn\n", $persoon, $kind);
            }
    

        }
        
       


        $rslt['tree'] = rtrim($tree, "\n");
        
        return $rslt;

    }

    /**
     * private function _treeFamilies($persoonID)
     * haalt de gekoppelde famileis aan persoon op
     * @param $persoonID
     * @return array
     */
    private function _treeFamilies($persoonID)
    {

        $dbSql = sprintf('
            SELECT f.id, f.naam
            FROM families f
            JOIN persoon p
                ON f.id = p.familie1ID or f.id = p.familie2ID
            WHERE p.id = %d    
        ', $persoonID);

        return DB::select($dbSql);
    }

    /**
     * private function _treeOuderIDVan($persoonID)
     * haalt ID ouder op (volgens familie)
     * @param $persoonID
     * @param $familieID
     * @return 
     */
    private function _treeOuderIDVan($persoonID, $familieID)
    {

        $dbSql = sprintf('
            SELECT id
            FROM persoon
            WHERE (familie1ID=%2$d OR familie2ID=%2$d)
                AND id != %1$d
                AND (id = (SELECT ouder1ID FROM persoon WHERE id=%1$d)
                            OR
                            id = (SELECT ouder2ID FROM persoon WHERE id=%1$d)
                            )
        ', $persoonID, $familieID);

    
        $dbRslt = DB::select($dbSql);
        if (count($dbRslt) == 1)
            return $dbRslt[0]->id;
        else
            return false;
    }


    /**
     * 
     * 
     * 
     * 
     */
    private function _treeKinderen($ouderID)
    {
        $dbSql = sprintf('
        SELECT p.id, p.naam, p.voornamen, p.roepnaam,
        DATE_FORMAT(p.geborenop, "%%d-%%m-%%Y") AS geborendatum, CONCAT(pl1.gemeente, ", ", pl1.land) AS geborenplaats, geborenop, geborenplaatsID,
        DATE_FORMAT(p.gestorvenop, "%%d-%%m-%%Y") AS gestorvendatum, CONCAT(pl2.gemeente, ", ", pl2.land) AS gestorvenplaats, gestorvenop, gestorvenplaatsID

        FROM persoon p
        LEFT OUTER JOIN plaats pl1
        ON p.geborenplaatsID=pl1.id
        LEFT OUTER JOIN plaats pl2
        ON p.gestorvenplaatsID=pl2.id
        WHERE p.ouder1ID = %1$d
           OR p.ouder2ID = %1$d
        ORDER BY geborenop ASC, naam, voornamen
        
        ', $ouderID);
        //print($dbSql); die();
        return DB::select($dbSql);

    }


    /**
     * private function _treeTotText($dta)
     * zet gegevens persoon om in string
     * @param $dta
     * @return string
     */
    private function _treeTotText($dta)
    {
        $id = $dta->id;
        $volledigeNaam = sprintf('%s %s', $dta->naam, $dta->voornamen);
        $roepnaam = $dta->roepnaam ? sprintf('<br>-- %s --', $dta->roepnaam) : '';

        $geborenplaats = $dta->geborenplaats ? str_replace('(', '&#40;', $dta->geborenplaats) : '';
        //$geborenplaats = $dta->geborenplaats ? str_replace('(', '\(', $dta->geborenplaats) : '';
        $geborenplaats = str_replace(')','&#41;', $geborenplaats);

        $geboren = sprintf('%s %s', $geborenplaats, $dta->geborendatum ? $dta->geborendatum : '');
        $geboren = strlen(trim($geboren)) > 0 ? '<br>' . $geboren : '';
        
        $gestorven = sprintf('%s %s', $dta->gestorvenplaats ? $dta->gestorvenplaats : '', $dta->gestorvendatum ? $dta->gestorvendatum : '');
        $gestorven = strlen(trim($gestorven)) > 0 ? '<br>' . $gestorven : '';

        return <<<TREEDATA
            ID-{$id}({$volledigeNaam}{$roepnaam}{$geboren}{$gestorven})
        TREEDATA;
    }

    public function appInfo() {
        $rslt = [];
        $dbSql = ('
            SELECT COUNT(*) AS aantalpersonen
            FROM persoon
           
        ');
        $rslt['aantalpersonen'] =  DB::select($dbSql)[0]->aantalpersonen;

        $dbSql = ('
            SELECT COUNT(*) AS aantalfamilies
            FROM families
           
        ');
        $rslt['aantalfamilies'] =  DB::select($dbSql)[0]->aantalfamilies;

        $dbSql = ('
            SELECT COUNT(*) AS aantalplaatsen
            FROM plaats
           
        ');
        $rslt['aantalplaatsen'] =  DB::select($dbSql)[0]->aantalplaatsen;

        $dbSql = ('
            SELECT COUNT(DISTINCT Land) AS aantallanden
            FROM plaats;    
           
        ');
        $rslt['aantallanden'] =  DB::select($dbSql)[0]->aantallanden;

        $dbSql = ('
            SELECT MIN(geborenop)  AS vroegstgeboren
            FROM persoon
            
           
        ');
        $rslt['vroegstgeboren'] =  DB::select($dbSql)[0]->vroegstgeboren;
        
        $dbSql = ('
            SELECT MIN(gestorvenop)  AS vroegstgestorven
            FROM persoon
            
           
        ');
        $rslt['vroegstgestorven'] =  DB::select($dbSql)[0]->vroegstgestorven;




        return $rslt;

    }

    public function plaatsLedigen($persoonID, $veldnaam) {
        switch ($veldnaam) {
            case 'geborenplaats':
                $dbSql = sprintf('  
                    UPDATE persoon  
                    SET gestorvenplaatsID = NULL
                    WHERE id = %d   
                ', $persoonID);
                break;
            
            case 'gestorvenplaats':
                $dbSql = sprintf('  
                    UPDATE persoon  
                    SET gestorvenplaatsID = NULL
                    WHERE id = %d   
                ', $persoonID);
                break;
        
        }
        return DB::select($dbSql);
    }

    public function dupliceerPersoon($persoonID) {
        $dbSql = sprintf('
            SELECT naam,familie1ID, familie2ID, geborenplaatsID, ouder1ID, ouder2ID
            
            FROM persoon
            WHERE id = %d
        ', $persoonID);

        $persoon = DB::select($dbSql)[0];  

        $naam = $persoon->naam;
        $ouder1ID = $persoon->ouder1ID;
        $ouder2ID = $persoon->ouder2ID;	
        $familie1ID = $persoon->familie1ID;
        $familie2ID = $persoon->familie2ID;
        $geborenplaatsID = $persoon->geborenplaatsID;
        $tijdstip = Carbon::now();

        $dbSql = sprintf('
            INSERT INTO persoon 
            
            (
            naam,
            ouder1ID,
            ouder2ID,
            familie1ID,
            familie2ID,
            geborenplaatsID,           
            created_at)
            VALUES ("%s", %d, %d, %d, %d, %d, "%s")
        ',$naam, $ouder1ID, $ouder2ID, $familie1ID, $familie2ID, $geborenplaatsID, $tijdstip->format('Y-m-d H:i:s'));

        DB::select($dbSql);

        return DB::getPdo()->lastInsertId();
    }

    public function verjaardagen() {
        $dbSql = sprintf('
        SELECT id,naam, voornamen, geborenop, 
               DATE_FORMAT(geborenop, "%%m-%%d") AS verjaardag_this_year,
               DATE_FORMAT(geborenop, "%%d-%%m-%%Y") AS verjaardag
        FROM persoon
        WHERE 
            (DATE_FORMAT(geborenop, "%%m-%%d") BETWEEN DATE_FORMAT(CURDATE(), "%%m-%%d") 
            AND DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 30 DAY), "%%m-%%d"))
            AND geborenop IS NOT NULL            
        ORDER BY DATE_FORMAT(geborenop, "%%m-%%d");
    ');

    return DB::select($dbSql);
    
    }
}