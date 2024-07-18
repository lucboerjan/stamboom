<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

class InhoudFamilie extends Model
{
    /**
     * public function lijstFamilie()
     * haalt een alfabetische lijst met alle familienamen op
     * @return array
     */
    public function lijstFamilie($limit) {
        if ($limit===0) {
            $limit='';
            $order = 'f.naam';
        } else {
            $limit = sprintf('LIMIT %s', $limit);
            $order = 'aantal DESC';
        }

        $dbSql = sprintf('
            SELECT f.id, f.naam, COUNT(DISTINCT p.id) AS aantal
            FROM families f
            LEFT OUTER JOIN persoon p
            ON f.id = p.familie1ID OR f.id = p.familie2ID
            GROUP BY f.id, f.naam
            ORDER BY %s %s
        ', $order, $limit);

        return DB::select($dbSql);
    }

    /**
     * public function updateFamilie($familieID, $naam)
     * update naam van familie met familieID
     * @param $familieID
     * @param $naam
     * @return void
     */
    public function updateFamilie($familieID, $naam) {
        $dbSql = sprintf("
            UPDATE families
            SET naam = '%s'
            WHERE id = %s
        ", $naam, $familieID);

        DB::select($dbSql);
    }

    /**
     * public function nieuwFamilie($naam)
     * nieuwe familie met naam
     * @param $naam
     * @return void
     */
    public function nieuwFamilie($naam) {
        $dbSql = sprintf("
            INSERT INTO families(naam)
            VALUES('%s')
        ", $naam);

        DB::select($dbSql);
    }

    /**
     * public function verwijderInfo($familieID)
     * haalt info van betreffende familie op
     * @param $familieID
     * @return array
     */
    public function verwijderInfo($familieID) {
        $rslt = [
            'succes' => false,
            'familieID' => $familieID,
            'naam' => '',
            'aantal' => 0
        ];

        $dbSql = sprintf("
            SELECT naam
            FROM families
            WHERE id = %d
        ", $familieID);

        $dbRslt = DB::select($dbSql);
        if (count($dbRslt) == 1) {
            $rslt['naam'] = $dbRslt[0]->naam;
        }

        $dbSql = sprintf("
            SELECT COUNT(1) AS aantal
            FROM persoon
            WHERE familie1ID=%d OR familie2ID=%d
        ", $familieID, $familieID);

        $rslt['aantal'] = DB::select($dbSql)[0]->aantal;

        $rslt['succes'] = true;

        return $rslt;
    }

    /**
     * public function verwijderFamilie($familieID)
     * verwijdert familie + verwijdert familieID van gekoppelde personen
     * @param $familieID
     * @return boolean
     */
    public function verwijderFamilie($familieID) {
        DB::beginTransaction();
        try {
            $dbSql = sprintf("
                UPDATE persoon
                SET familie1ID = CASE WHEN familie1ID=%d THEN NULL ELSE familie1ID END,
                    familie2ID = CASE WHEN familie2ID=%d THEN NULL ELSE familie2ID END
            ", $familieID, $familieID);

            DB::select($dbSql);

            $dbSql = sprintf("
                DELETE FROM families
                WHERE id=%d
            ", $familieID);

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
     * public function mergeLijstFamilie()
     * haalt info families op
     * @param $familieID
     * @return array
     */
    public function mergeLijstFamilie($families) {
        $dbSql = sprintf("
            SELECT f.id, f.naam, COUNT(DISTINCT p.id) AS aantal
            FROM families f
            LEFT OUTER JOIN persoon p
            ON f.id = p.familie1ID OR f.id = p.familie2ID
            WHERE f.id IN (%s)
            GROUP BY f.id, f.naam
            ORDER BY f.naam
        ", $families);

        return DB::select($dbSql);
    }

    /**
     * public function mergeLijstUitvoeren($familie, $families)
     * 
     */
    public function mergeLijstUitvoeren($familie, $families) {
        DB::beginTransaction();

        try {
            // update familie1ID
            $dbSql = sprintf('
                UPDATE persoon
                SET familie1ID = %s
                WHERE id IN (SELECT id
                             FROM persoon
                             WHERE familie1ID in (%s))
            ', $familie, $families);
            DB::select($dbSql);
            
            // update familie2ID
            $dbSql = sprintf('
                UPDATE persoon
                SET familie2ID = %s
                WHERE id IN (SELECT id
                             FROM persoon
                             WHERE familie2ID in (%s))
            ', $familie, $families);
            DB::select($dbSql);

            // verwijder familie2ID van gewijzigde personen met zelfde familie1ID en familie2ID
            $dbSql = sprintf("
                UPDATE persoon
                SET familie2ID = NULL
                WHERE familie1ID = familie2ID
                    AND id IN (SELECT id
                                FROM persoon
                                WHERE familie2ID in (%s))
            
            ", $families);
            DB::select($dbSql);

            // verwijder andere families, behalve uitzondering familie
            $dbSql = sprintf("
                DELETE FROM families
                WHERE id in (%s) 
                AND ID != %s

            ", $families, $familie);

            DB::select($dbSql);


            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
        }
    }

}
