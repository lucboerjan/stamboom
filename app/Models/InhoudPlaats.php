<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

use App\Http\Middleware\Instelling;

class InhoudPlaats extends Model
{
    /**
     * public function lijstPlaats()
     * haalt alfabetische lijst met alle plaatss op
     * @return array
     */
    public function lijstPlaats() {
        $dbSql = sprintf("
            SELECT pl.id, CONCAT(pl.gemeente, ' (', pl.land, ')') AS plaats, COUNT(DISTINCT p.id) AS aantal
            FROM plaats pl
            LEFT OUTER JOIN persoon p
            ON pl.id = p.geborenplaatsID OR pl.id = p.gestorvenplaatsID
            GROUP BY pl.id, plaats
            ORDER BY plaats
        ");
        
        return DB::select($dbSql);
    }

     /**
     * public function updatePlaats($plaatsID, $plaatsGemeente, $plaatsLand)
     * update naam van betreffende plaats
     * @param $plaatsID
     * @param $plaatsGemeente
     * @param $plaatsLand
     * @return void
     */
    public function updatePlaats($plaatsID, $plaatsGemeente, $plaatsLand) {
        $dbSql = sprintf("
            UPDATE plaats
            SET gemeente = '%s', land = '%s'
            WHERE id = %s
        ", $plaatsGemeente, $plaatsLand, $plaatsID);

        DB::select($dbSql);
    }

    /**
     * public function nieuwPlaats($naam)
     * voegt een nieuwe plaats toe aan tabel plaats
     * @param $plaatsGemeente
     * @param $plaatsLand
     * @return void
     */
    public function nieuwPlaats($plaatsGemeente, $plaatsLand) {
        $dbSql = sprintf("
            INSERT INTO plaats(gemeente, land)
            VALUES('%s', '%s')
        ", $plaatsGemeente, $plaatsLand);

        DB::select($dbSql);
    }

    /* --- VERWIJDER --- */
    /**
     * public function verwijderInfo($plaatsID)
     * haalt informatie over plaats op
     * @param $plaatsID
     * @return array
     */
    public function verwijderInfo($plaatsID) {
        $rslt = ['succes' => false,
                 'plaatsID' => $plaatsID,
                 'plaats' => '',
                 'aantal' => 0];

        $dbSql = sprintf("
            SELECT CONCAT(gemeente, ' (', land, ')') AS plaats
            FROM plaats
            WHERE id = %d
        ", $plaatsID);

        $dbRslt = DB::select($dbSql);
        if (count($dbRslt) == 1) {
            $rslt['plaats'] = $dbRslt[0]->plaats;
        }

        $dbSql = sprintf("
            SELECT COUNT(1) AS aantal
            FROM persoon
            WHERE geborenplaatsID=%d OR gestorvenplaatsID=%d
        ", $plaatsID, $plaatsID);

        $rslt['aantal'] = DB::select($dbSql)[0]->aantal;

        $rslt['succes'] = true;

        return $rslt;
    }

    /**
     * public function verwijderPlaats()
     * verwijdert plaats + plaatsID van persoon
     * @param $plaatsID
     * @return bool
     */
    public function verwijderPlaats($plaatsID) {
        DB::beginTransaction();
        try {
            $dbSql = sprintf("
            UPDATE persoon
            SET geborenplaatsID = CASE WHEN geborenplaatsID=%d THEN NULL ELSE geborenplaatsID END,
                gestorvenplaatsID = CASE WHEN gestorvenplaatsID=%d THEN NULL ELSE gestorvenplaatsID END
            ", $plaatsID, $plaatsID);

            DB::select($dbSql);

            $dbSql = sprintf("
                DELETE FROM plaats
                WHERE id = %d
            ", $plaatsID);

            DB::select($dbSql);

            DB::commit();
            return true;
        }
        catch(Exception $ex) {
            DB::rollback();
            return false;
        }
    }

    /**
     * public function mergeLijstPlaats($plaatsen)
     * haalt een lijst op met info over de geselecteerde plaatsen om te merge
     * @param $plaatsen
     * @return array
     */
    public function mergeLijstPlaats($plaatsen) {
        $dbSql = sprintf("
            SELECT pl.id, CONCAT(pl.gemeente, ' (', pl.land, ')') AS plaats, COUNT(DISTINCT p.id) AS aantal
            FROM plaats pl
            LEFT OUTER JOIN persoon p
            ON pl.id = p.geborenplaatsID OR pl.id = p.gestorvenplaatsID
            WHERE pl.id IN (%s)
            GROUP BY pl.id, plaats
            ORDER BY plaats
        ", $plaatsen);

        return DB::select($dbSql);
    }

    /**
     * public function mergeLijstUitvoeren($plaats, $plaatsen)
     * voegt geselecteerde plaatsen samen tot plaats
     * @param $plaats -> doelplaats
     * @param $plaatsen -> geselecteerde plaatsen
     * @return void
     */
    public function mergeLijstUitvoeren($plaats, $plaatsen) {
        DB::beginTransaction();
        try {
            // update geboorteplaats
            $dbSql = sprintf("
                UPDATE persoon
                SET geborenplaatsID = %s
                WHERE id IN (SELECT id
                            FROM persoon 
                            WHERE geborenplaatsID IN (%s))
            ", $plaats, $plaatsen);

            DB::select($dbSql);

            // update sterfteplaats
            $dbSql = sprintf("
                UPDATE persoon
                SET gestorvenplaatsID = %s
                WHERE id IN (SELECT id
                            FROM persoon 
                            WHERE gestorvenplaatsID IN (%s))
            ", $plaats, $plaatsen);

            DB::select($dbSql);

            // verwijder andere plaatsen
            $plaatsen = explode(',', $plaatsen);
            unset($plaatsen[array_search($plaats, $plaatsen)]);
            $plaatsen = implode(',', $plaatsen);

            // verwijder merge famileis
            $dbSql = sprintf("
                DELETE FROM plaats
                WHERE id IN (%s)
            ", $plaatsen);

            DB::select($dbSql);

            DB::commit();
        }
        catch(Exception $ex) {
            DB::rollback();
            die($ex);
        }
    }
}
