<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

use App\Http\Middleware\Instelling;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class Leden extends Model
{
    use HasFactory;

    /**
     * public function lijst($zoek='', $pagina=0, $aantalPerPagina=1)
     * haalt lijst op met gebruikers die aan voorwaarde (zoekterm, pagina) voldoen
     *     bepaalt het aantal rijen
     *     haalt rijen voor pagina op
     * @param $zoek: string, zoekterm
     * @param $pagina: integer, pagina
     * @param $aantalPerPagina: integer, aantal items per pagina
     * return array
     */
    public function lijst($zoek='', $pagina=0, $aantalPerPagina=1) {
        $dta = false;

        // WHERE-clausule indien zoekterm niet leeg
        $zoek = trim($zoek);
        $where = empty($zoek) ? '' : sprintf("WHERE `name` LIKE '%%%1\$s%%' 
                                                    OR `fullname` LIKE '%%%1\$s%%' 
                                                    OR email LIKE '%%%1\$s%%'", 
                                                $zoek);

        // aantal rijen in zoekresultaat
        $dbSql = sprintf("
            SELECT COUNT(1) AS aantal
            FROM users
            %s
        ", $where);
        $dta['sql-1'] = $dbSql;
        $dta['aantal'] = DB::select($dbSql)[0]->aantal;

        // bereken LIMIT
        $aantalPaginas = ceil($dta['aantal'] / $aantalPerPagina);
        $dta['aantalPaginas'] = $aantalPaginas;
        if ($pagina > $aantalPaginas) $pagina = $aantalPaginas;
        $limit = sprintf("LIMIT %s, %s", $pagina * $aantalPerPagina, $aantalPerPagina);

        // rijen users van bevraging
        $dbSql = sprintf("
            SELECT id, name, fullname, email, level 
            FROM users
            %1s
            ORDER BY `fullname`
            %s
        ", $where, $limit);
        $dta['sql-2'] = $dbSql;
        $dta['leden'] = DB::select($dbSql);

        // resultaat
        return $dta;
    }

    /**
     * public function getLid($lidID)
     * haalt van opgegeven lid met id 'lidID' id, name, fullname, email en level op
     * @param lidID integer
     * @return array
     */
    public function getLid($lidID) {
        return DB::select(sprintf('SELECT id, `name`, fullname, email, `level` FROM users WHERE id=%d', $lidID))[0];
    }

    /**
     * public function setLid($lidID, $naam, $vNaam, $email, $level)
     * schrijft gegevens lid weg naar database
     * @param $lidID integer (id)
     * @param naam: String (name)
     * @param vNaam: String (fullname)
     * @param $email: String (email)
     * @param $level: integer (level)
     * @return array
     */
    public function setLid($lidID, $naam, $vNaam, $email, $level) {
        $dta = ['succes' => false, 'boodschap' => ''];

        if ($lidID == 0) {
            if (count(DB::select(sprintf("SELECT 1 FROM users WHERE email='%s'", $email))) == 0) {
                $lid = new User();
                $lid->name = $naam;
                $lid->fullname = $vNaam;
                $lid->email = $email;
                $lid->level = $level;
                $dta['wachtwoord'] = Str::random(10);
                $lid->password = Hash::make($dta['wachtwoord']);
                $lid->save();
                $dta['lidID'] = $lid->id;
                $dta['succes'] = true;
            }
            else {
                $json['boodschap'] = __('boodschappen.setLidEmail');
            }
        }
        else {
            if (count(DB::select(sprintf("SELECT 1 FROM users WHERE email='%s' AND id!=%d", $email, $lidID))) == 0) {
            $lid = User::find($lidID);
            $lid->name = $naam;
            $lid->fullname = $vNaam;
            $lid->email = $email;
            $lid->level = $level;
            $lid->update();
            $dta['succes'] = true;
        }
        else {
            $json['boodschap'] = __('boodschappen.setLidEmail');
        }

        }

        return $dta;
    }

    /**
     * public function verwijderLid²($lidID)
     * verwijdert opgegeven gebruiker uit tabel 'users'
     * @param $lidID: integer
     * @return array
     */
    public function verwijderLid($lidID) {
        $dta = ['succes' => false, 'boodschap' => ''];

        User::where('id', '=', $lidID)->delete();
        $dta['succes'] = true;

        return $dta;
    }
    
}
