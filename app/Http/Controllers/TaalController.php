<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App;
use Auth;
use App\Http\Middleware\Instelling;

class TaalController extends Controller
{
    /**
     * public function taal(Request $request, $taal=null)
     * zet taal interface -> taal geselecteerd door gebruiker in navigatiebalk
     * @param $request
     * @param $taal
     * @return void
     */
    public function zetTaal(Request $request, $taal = null) {
        // valideer talen
        // is $taal anders standaardtaal
        if ($taal) {
            $taal = strtolower(trim($taal));
            // $taal komt niet in $talen voor > ken standaardtaal toe
            if (! array_key_exists($taal, Instelling::get('talen'))) {
                $taal = Instelling::get('taal');
            }
        }
        else {
            $taal = Instelling::get('taal');
        }

        // taal in sessievariabele
        $request->session()->put('taal', $taal);
        // zet taal interface project
        App::setLocale($taal);

        // redirect naar vorige pagina
        return redirect()->back();
    }

    /**
     * zet standaardtaal indien taal niet geselecteerd
     * public static function taal()
     * @return void
     */
    public static function taal() {
        // indien sessievariabele taal niet bestaat (gebruiker heeft geen taal geselecteerd)
        // haal standaardtaal inteface op, ken toe aan sessievariabele + zet als interfacetaal app
        if (!session()->has('taal')) {
            $taal = Instelling::get('taal');
            session()->put('taal', $taal);
            App::setLocale($taal);
        }
    }
}
