<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;

use App\Http\Middleware\Instelling;
use App\Http\Controllers\TaalController;

Use App\Models\InhoudZoek;
Use App\Models\InhoudPersoon;
Use App\Models\InhoudFamilie;

class InhoudZoekController extends Controller
{
    /**
     * constructor
     */
    public function __construct() {
        $this->_oInhoudZoek = new InhoudZoek();
    }

    /**
     * public function inhoudZoek()
     * staat in voor het weergeven van de homepage (na aanmelden)
     * @return indexzoek
     */
    public function inhoudZoek(Request $request) {
        // zet taal interface
        TaalController::taal();

        // redirect login indien niet aangemeld
        if (! Auth::check()) {
            return redirect('login');
        }
        $dta = [];
        $dta['isInhoudBeheerder'] = $this->_isInhoudBeheerder(Auth::user()->level & 4) && $request->beheer == 'beheer';


        return view('pagina.inhoud.indexzoek')->with($dta);
    }

    private function _isInhoudBeheerder() {
        return boolval(Auth::user()->level & 4);
    }

    /**
     * jxInhoudzoekInhoudBeheerder()
     * *@return isinhoudbeheerder: true | andere: false
     */ 
    public function jxInhoudzoekInhoudBeheerder() {
        $json = [
            'succes' => true,
            'isinhoudbeheerder' => $this->_isInhoudBeheerder()
        ];

        return response()->json($json);
    }

    /**
    * jxInhoudzoekFamilies()
    ** @return lijst met familienamen (alfabetisch)
    */ 
    public function jxInhoudzoekFamilies() {
        $json = [
            'succes' => true,
            'families' => $this->_oInhoudZoek->families()
        ];

        return response()->json($json);
    }    

    /**
    * jxInhoudzoekGeboorteplaatsen()
    * *@return lijst met geboorteplaatsen (alfabetisch)
    */ 
    public function jxInhoudzoekGeboorteplaatsen() {
        $json = [
            'succes' => true,
            'geboorteplaatsen' => $this->_oInhoudZoek->geboorteplaatsen()
        ];

        return response()->json($json);
    } 
       
    /**
    * jxInhoudzoekSterfteplaatsen()
    * *@return lijst met sterfteplaatsen (alfabetisch)
    */ 
    public function jxInhoudzoekSterfteplaatsen() {
        $json = [
            'succes' => true,
            'sterfteplaatsen' => $this->_oInhoudZoek->sterfteplaatsen()
        ];

        return response()->json($json);
    }    


    /**
    * jxInhoudzoekBoodschappen()
    * @return string
    */ 
    public function jxInhoudzoekBoodschappen() {
        $json = [
            'succes' => true,
            'boodschappen' => trans('boodschappen.inhoudzoek_boodschappen')
        ];

        return response()->json($json);
    }   

    /**
    * jxInhoudZoekPersoon()
    * haalt lijst op van zoekresultaat  
    ** @return zoekresultaat
    */ 
    public function jxInhoudZoekPersoon(Request $request) {
        $json = [
            'succes' => false,
            
        ];

        try {
            $frmDta = [
                'familie' => $request->familie,
                'naam' => $request->naam,
                'voornaam' => $request->voornaam,
                'roepnaam' => $request->roepnaam,
                
               'geadopteerd' => $request->geadopteerd,
                'geboortejaar' => $request->geboortejaar,
                'geboorteplaats' => $request->geboorteplaats,
                'sterftejaar' => $request->sterftejaar,
                'sterfteplaats' => $request->sterfteplaats,
                
            ];
        

            $pagina = $request->pagina;
            $aantaPerPagina = Instelling::get('paginering')['aantalperpagina'];

            $rslt = $this->_oInhoudZoek->zoekOpdracht($frmDta, $pagina, $aantaPerPagina);
            $json['zoekopdracht'] = $frmDta;
            $json['pagina'] = $pagina;
            //$json['isbeheerder'] = $this->_isInhoudBeheerder();
            $json['aantal'] = $rslt['aantal'];
            $json['aantalpaginas'] = $rslt['aantalPaginas'];
            $json['dbRslt'] = $rslt['dbRslt'];
            $json['sqlZoek'] = $rslt['sqlZoek'];
            $json['plaatsen'] = $rslt['plaatsen'];
            $json['knoppen'] = Instelling::get('paginering')['knoppen'];
            $json['crucifix'] = Instelling::get('app')['crucifix'];
            $json['succes'] = true;

        }
        catch(exeption) {

        }

        return response()->json($json);
    } 
    
    public function jxInhoudZoekPersoonInfo(Request $request) {
        $json = ['succes' => false ];

        try {
            $tmp = $this->_oInhoudZoek->persoonInfo($request->persoonID);
            $json['persoon'] = $tmp['persoon'];
            $json['ouders'] = $tmp['ouders'];
            $json['kinderen'] = $tmp['kinderen'];
            $json['plaatsen'] = $tmp['plaatsen'];
            $json['succes'] = true;
        }
        catch(Exception) {

        }
        return response()->json($json);
    }
    
    // === PERSOON DOCUMENT
    /**
     * public function jxInhoudZoekPersoonDocument()
     * haalt info persoon + documenten op
     * @param $persoonID
     * @return array
     */
    public function jxInhoudZoekPersoonDocument(Request $request) {
        $json = ['succes' => false ];
        $persoonID = $request->persoonID;
        try {
            $oInhoudPersoon = new InhoudPersoon();
            $json['documenten'] = $oInhoudPersoon->documentlijst($persoonID);
            $json['persoon'] = $oInhoudPersoon->persoon($persoonID);
            $json['ouders'] =$oInhoudPersoon->ouders($persoonID) ;
            $json['kinderen'] = $oInhoudPersoon->kinderen($persoonID);
            $json['succes'] = true;
            
        } catch (\Exception $ex) {
            
        }


        return response()->json($json);

    }

    public function jxAppInfo(Request $request) {
        $oInhoudFamilie = New InhoudFamilie();
        $json = ['succes' => false ];

        //try {
            $oInhoudPersoon = new InhoudPersoon();
            $json['appinfo'] = $oInhoudPersoon->appInfo();
            $json['families'] = $oInhoudFamilie->lijstFamilie(5);
            $json['succes'] = true;
        //} catch (\Exception $ex) {
            
        //}
        return response()->json($json);
    }

    public function verjaardagen() {   
        $oInhoudPersoon = new InhoudPersoon();
        // redirect login indien niet aangemeld
        if (! Auth::check()) {
            return redirect('login');
        }

        $dta = [];
        $dta['verjaardagen'] = $oInhoudPersoon->verjaardagen();   
        return view('pagina.mijnfamilie.verjaardagen')->with($dta); 

    }

}

