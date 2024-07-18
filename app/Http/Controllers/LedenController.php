<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use App\Http\Middleware\Instelling;
use App\Http\Controllers\TaalController;

use App\Models\Leden;
use App\Models\User;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class LedenController extends Controller
{ 
    /**
     * constructor
     */
    public function __construct() {
        $this->_oLeden = new Leden();
    }

    /**
     * public function index()
     * staat in voor het weergeven van de homepage (na aanmelden)
     * @return view index.blade.php
     */
    public function index() {
        // zet taal interface
        TaalController::taal();

        // redirect login indien niet aangemeld
        if (! Auth::check()) {
            return redirect('login');
        }

        $dta = [];

        return view('pagina.leden.index')->with($dta);
    }

    /**
     * public function lijst($request)
     * haalt lijst met leden op (zoek & paginering)
     * @param $request formulierdata: zoek | pagina
     * @return json
     */
    public function jxLedenLijst(Request $request) {
        $json = ['succes' => 'false'];
        // is gebruiker administrator
        $json['isAdmin'] = Auth::user()->level & 8;

        $zoekTerm = trim($request->zoek);
        $pagina = intval($request->pagina);
        $json['zoekterm'] = $zoekTerm;
        $json['pagina'] = $pagina;

        try {
            $dta = $this->_oLeden->lijst($zoekTerm, $pagina, Instelling::get('paginering')['aantalperpagina']);
            $json['sql-1'] = $dta['sql-1'];
            $json['aantal'] = $dta['aantal'];
            $json['infoTitel'] = $dta['aantal'] == 0 ? trans('boodschappen.jxledenlijst_titel') : '';
            $json['infoBoodschap'] = $dta['aantal'] == 0 ? trans('boodschappen.jxledenlijst_boodschap') : '';
            $json['aantalPaginas'] = $dta['aantalPaginas'];
            $json['sql-2'] = $dta['sql-2'];
            $json['leden'] = $dta['leden'];
            $json['knoppen'] = Instelling::get('paginering')['knoppen'];
            $json['succes'] = true;
        }
        catch(Exception $ex) {

        }

        return response()->json($json);
    }

    /**
     * public function jxLedenBewerk($request)
     * haalt gegevens gebruiker op en stuurt terug
     * @param $request: formulierData
     * @return array
     */
    public function jxLedenGet(Request $request) {
        $json = ['succes' => false, 
                    'isAdmin' => Auth::user()->level & 8, 
                    'mode' => $request->mode];

        // vertalingen
        $labels = [];
        foreach (explode(',', __('boodschappen.ledenBewerk')) as $item) {
            $tmp = explode(':', $item);
            $labels[$tmp[0]] = $tmp[1];
        }
        $json['labels'] = $labels;
        $json['geduld'] = __('boodschappen.geduld');
        $json['fout'] = __('boodschappen.fout');

        $lidID = intval($request->lidID);
        if ($request->mode == 'nieuw') {
            $json['lid'] = [
                'id' => 0,
                'name' => '',
                'email' => '',
                'fullname' => '',
                'level' => 1
            ];
        }
        else {
            $json['lid'] = $this->_oLeden->getLid($lidID);
        }
        $json['succes'] = true;

        return response()->json($json);
    }

    /**
     * public function jxLedenBewaar($request)
     * update lid:
     *     - verwijder
     *     - update
     *     - nieuw + stuur wachtwoord reset mail
     * @param $request formulierdata
     */
    public function jxLedenBewaar(Request $request) {
        $json = ['succes' => false];

        $mode = $request->mode;
        $json['mode'] = $mode;

        $lidID = $request->lidID;
        $naam = $request->naam;
        $vNaam = $request->vnaam;
        $email = $request->email;
        $level = $request->level;

        try {
            if ($mode == 'verwijder') {
                $dta = $this->_oLeden->verwijderLid($lidID);
            }
            else {
                $dta = $this->_oLeden->setLid($lidID, $naam, $vNaam, $email, $level);

                // verstuur reset wachtwoord mail naar nieuwe gebruiker
                // if ($mode == 'nieuw') $this->_verstuurResetW8WMail($dta['lidID']);
                if ($mode == 'nieuw' && $dta['succes']) $json['lidID'] = $dta['lidID'];
                
            }
            $json['succes'] = $dta['succes'];
            $json['boodschap'] = $dta['boodschap'];            
        }
        catch(Exception $ex) {

        }
        
        return response()->json($json);
    }

    /**
     * public function jxLedenResetInfo($request)
     * haalt info geselecteerde gebruiker op + vertalingen
     * @param $request: formulier met lidID
     * @return json
     */
    public function jxLedenResetInfo(Request $request) {
        $json = ['succes' => false];

        $lidID = $request->lidID;
        $json['lid'] = $this->_oLeden->getLid($lidID);
        
        // vertalingen
        $labels = [];
        foreach (explode(',', __('boodschappen.ledenInfo')) as $item) {
            $tmp = explode(':', $item);
            $labels[$tmp[0]] = $tmp[1];
        }
        $json['labels'] = $labels;

        $json['succes'] = true;

        return response()->json($json);
    }

    /**
     * public function jxLedenResetMail($request)
     * stuurt een reset wachtwoord mail naar de gebruiker
     * @param $request: array met formulierdata
     * @return json
     */
    public function jxLedenResetMail(Request $request) {
        $lidID = $request->lidID;
        $user = User::where('id', '=', $lidID)->first();
        $json['user'] = $user;
        $token = Password::getRepository()->create($user);
        $user->sendPasswordResetNotification($token);
    }
}
