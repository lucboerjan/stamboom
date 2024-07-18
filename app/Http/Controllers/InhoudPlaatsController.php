<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use App\Http\Middleware\Instelling;
use App\Http\Controllers\TaalController;

use App\Models\InhoudPlaats;

class InhoudPlaatsController extends Controller
{
    /**
     * constructor
     */
    public function __construct() {
        $this->_oInhoudPlaats = new InhoudPlaats();
    }

    /**
     * public function inhoudPlaats()
     * staat in voor het weergeven van de homepage inhoudPlaats voor het beheer van plaatsnamen (na aanmelden)
     * @return view inhoudPlaats.blade.php
     */
    public function inhoudPlaats() {
        // zet taal interface
        TaalController::taal();

        // redirect login indien niet aangemeld
        if (! Auth::check()) {
            return redirect('login');
        }

        // maak nieuwe plaats aan
        $dta = [];
        

        return view('pagina.inhoud.indexplaats')->with($dta);
    }

    /**
     * public function jxInhoudPlaatsLijst()
     * haalt alfabetische lijst met plaatsen op
     * @return array
     */
    public function jxInhoudPlaatsLijst() {
        $json = ['succes' => false];

        try {
            $json['plaatsen'] = $this->_oInhoudPlaats->lijstPlaats();
            $json['succes'] = true;
        }
        catch(Exception $ex) {

        }

        return response()->json($json);
    }

    /**
     * public function jxInhoudPlaatsFout() 
     * haalt foutboodschap op
     * @return aarray
     */
    public function jxInhoudPlaatsFout() {
        $boodschap = explode(',', __('boodschappen.inhoudplaats_nieuwfout'));
        
        $json = ['succes' => true];
        $json['titel'] = explode(':', $boodschap[0])[1];
        $json['info'] = explode(':', $boodschap[1])[1];
        $json['btnCa'] = explode(':', $boodschap[2])[1];

        return response()->json($json);
    }

    /**
     * public function jxInhoudPlaatsUpdate()
     * update naam van plaats
     * @param $plaatsID
     * @param $plaatsGemeente
     * @param $plaatsLand
     * @return array
     */
    public function jxInhoudPlaatsUpdate(Request $request) {
        $json = ['succes' => false];

        $plaatsID = $request->plaatsID;
        $plaatsGemeente = $request->plaatsGemeente;
        $plaatsLand = $request->plaatsLand;

        try {
            if ($plaatsID == 0) {
                $this->_oInhoudPlaats->nieuwPlaats($plaatsGemeente, $plaatsLand);
            }
            else {
                $this->_oInhoudPlaats->updatePlaats($plaatsID, $plaatsGemeente, $plaatsLand);
            }
            $json['plaats'] = $this->_oInhoudPlaats->lijstPlaats();
            $json['succes'] = true;
        }
        catch(Exception $ex) {

        }

        return response()->json($json);
    }

    /* --- VERWIJDER PLAATS --- */
    
    /**
     * public function jxInhoudPlaatsVerwijderInfo()
     * haalt informatie op van betreffende plaats
     * @param $plaatsID
     * @return array
     */
    public function jxInhoudPlaatsVerwijderInfo(Request $request) {
        $json = ['succes' => false];

        $plaatsID = $request->plaatsID;
        
        try {
            $json = $this->_oInhoudPlaats->verwijderInfo($plaatsID);
            $json['boodschap'] = __('boodschappen.inhoudplaats_verwijder');
        }
        catch(Exception $ex) {

        }

        return response()->json($json);
    }

    /**
     * public function jxInhoudPlaatsVerwijderBevestig()
     * verwijdert plaats + plaatsID van persoon
     * @param $plaatsID
     * @return boolean
     */
    public function jxInhoudPlaatsVerwijderBevestig(Request $request) {
        $json = ['succes' => false];

        $plaatsID = $request->plaatsID;

        try {
            $json['succes'] = $this->_oInhoudPlaats->verwijderPlaats($plaatsID);
        }
        catch(Exception $ex) {

        }

        return response()->json($json);
    }

    /**
     * public function jxInhoudPlaatsMergeLijst()
     * geeft een lijst van de geselecteerde plaatsen  + boodschap voor dialoogvenster
     * @param $plaatsen
     * @return array
     */
    public function jxInhoudPlaatsMergeLijst(Request $request) {
        $json = ['succes' => false];

        $plaatsen = $request->plaatsen;

        try {
            $json['plaatsen'] = $this->_oInhoudPlaats->mergeLijstPlaats($plaatsen);
            $json['boodschap'] = __('boodschappen.inhoudplaats_dlgsamenvoegen');
            $json['succes'] = true;
        }
        catch(EXception $ex) {

        }

        return response()->json($json);
    }

    /**
     * public function jxInhoudPlaatsMergeUitvoeren()
     * voegt plaatsen samen tot geselecteerde plaats
     * @param @plaats -> geselecteerde doelplaats
     * @param @plaatsen -> lijst met geselecteerde plaatsen om samen te voegen
     * @return array
     */
    public function jxInhoudPlaatsMergeUitvoeren(Request $request) {
        $json = ['succes' => false];

        $plaatsen = $request->plaatsen;
        $plaats = $request->plaats;

        try {
            $this->_oInhoudPlaats->mergeLijstUitvoeren($plaats, $plaatsen);
            $json['succes'] = true;
        }
        catch(Exception $ex) {

        }

        return response()->json($json);
    }
}
