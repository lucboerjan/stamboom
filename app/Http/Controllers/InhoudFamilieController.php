<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use App\Http\Middleware\Instelling;
use App\Http\Controllers\TaalController;

use App\Models\InhoudFamilie;

class InhoudFamilieController extends Controller
{
    /**
     * constructor
     */
    public function __construct() {
        $this->_oInhoudFamilie = new InhoudFamilie();
    }

    /**
     * public function inhoudFamilie
     * weergeven pagina
     * @return view inhoudfamilie.blade.php
     */
    public function inhoudFamilie() {
        TaalController::taal();

        if (!Auth::check()) {
            return redirect('login');
        }

        $dta = [];

        return view('pagina.inhoud.indexfamilie')->with($dta);
    }

    /**
     * public function jxInhoudFamilieLijst()
     * ophalen alfabetische lijst families
     * @return array
     */
    public function jxInhoudFamilieLijst() {
        $json = ['succes' => false];
        $limit=0;

        try {
            $json['families'] = $this->_oInhoudFamilie->lijstFamilie($limit);
            $json['succes'] = true;
        }
        catch(Exception $ex) {

        }

        return response()->json($json);
    }

    /**
     * public function jxInhoudFamilieUpdate()
     * update naam familie
     * @param @familieID
     * @param $naam
     * @return array
     */
    public function jxInhoudFamilieUpdate(Request $request) {
        $json = ['succes' => false];

        $familieID = $request->familieID;
        $naam = $request->naam;

        try {
            if ($familieID == 0) $this->_oInhoudFamilie->nieuwFamilie($naam);
            else $this->_oInhoudFamilie->updateFamilie($familieID, $naam);

            $json['families'] = $this->_oInhoudFamilie->lijstFamilie();
            $json['succes'] = true;
        }
        catch(Exception $ex) {

        }

        return response()->json($json);
    }

    /**
     * public function jxInhoudFamilieVerwijderInfo()
     * haalt informatie over geselecteerde familie op
     * @param $familieID
     * @return array
     */
    public function jxInhoudFamilieVerwijderInfo(Request $request) {
        $json = ['succes' => false];

        $familieID = $request->familieID;

        try {
            $json = $this->_oInhoudFamilie->verwijderInfo($familieID);
            $json['boodschap'] = __('boodschappen.inhoudfamilie_verwijder');
        }
        catch(Exception $ex) {

        }

        return response()->json($json);
    }

    /**
     * public function jxInhoudFamilieVerwijderBevestig()
     * verwijdert familie + familieID van personen
     * @param $familieID
     * @return array
     */
    public function jxInhoudFamilieVerwijderBevestig(Request $request) {
        $json = ['succes' => false];

        $familieID = $request->familieID;

        try {
            $json['succes'] = $this->_oInhoudFamilie->verwijderFamilie($familieID);
        }
        catch(Exception $ex) {

        }

        return response()->json($json);
    }

    /**
     * public function jxInhoudFamilieMergeLijst()
     * genereert lijst met info van geselecteerde families
     * @param $families
     * @return array
     */
    public function jxInhoudFamilieMergeLijst(Request $request) {
        $json = ['succes' => false];

        $families = $request->families;

        try {
            $json['families'] = $this->_oInhoudFamilie->mergeLijstFamilie($families);
            $json['boodschap'] = __('boodschappen.inhoudfamilie_dlgsamenvoegen');
            $json['succes'] = true;
        }
        catch(Exception $ex) {

        }

        return response()->json($json);
    }   

    /**
     * public function jxInhoudFamilieMergeUitvoeren()
     * voegt families samen tot geselecteerde familie
     * @param $families
     * @param $familie
     * @return void
     */
    public function jxInhoudFamilieMergeUitvoeren(Request $request) {

        $json = ['succes' => false];

        $familie = $request->familie;
        $families = $request->families;

        try {
            $this->_oInhoudFamilie->mergeLijstUitvoeren($familie, $families);
            $json['succes'] = true;
        } catch (\Exception $ex) {

        }

        return response()->json($json);
    }
}
