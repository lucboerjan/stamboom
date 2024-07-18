<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use App\Http\Middleware\Instelling;
use App\Http\Controllers\TaalController;
use App\Models\InhoudPersoon;
use App\Models\Plaats;
use Validator;

use PhpParser\Node\Stmt\Catch_;

class InhoudPersoonController extends Controller
{
    /**
     * constructor
     */
    public function __construct() {
        $this->_oInhoudPersoon = new inhoudPersoon();
        $this->_oPlaats = new Plaats();
    }

    /**
     * public function jxInhoudPersoonBoodschappen()
     * haalt boodschappen op mbt verwijderen van persoon
     * @return string (csv)
     */
    public function jxInhoudPersoonBoodschappen(Request $request) {
        $json = [
            'succes' => true,
            'familieverwijder' => trans('boodschappen.inhoudpersoon_boodschappen_familie_verwijder'),
            'familienieuw' => trans('boodschappen.inhoudpersoon_boodschappen_familie_nieuw'),
            'ouderverwijder' => trans('boodschappen.inhoudpersoon_boodschappen_ouder_verwijder'),
            'oudernieuw' => trans('boodschappen.inhoudpersoon_boodschappen_ouder_nieuw'),
           'kindverwijder' => trans('boodschappen.inhoudpersoon_boodschappen_kind_verwijder'),
            'kindnieuw' => trans('boodschappen.inhoudpersoon_boodschappen_kind_nieuw'),
            'plaatsnieuw' => trans('boodschappen.inhoudpersoon_boodschappen_plaats_nieuw'),
            'geboren' => trans('boodschappen.inhoudpersoon_boodschappen_geboren'),
            'gestorven' => trans('boodschappen.inhoudpersoon_boodschappen_gestorven'),
            'bewaar' => trans('boodschappen.inhoudpersoon_bewaar_boodschap'),
            'documenten' => trans('boodschappen.inhoudpersoon_document_upload_boodschap')

        ];

        return response()->json($json);
    }

    /**
     * public function inhoudPersoon()
     * staat in voor het weergeven van de homepage (na aanmelden)
     * @return view indexpersoon.blade.php
     */
    public function inhoudPersoon($persoonID=0) {
        // zet taal interface
        TaalController::taal();

        // redirect login indien niet aangemeld
        if (! Auth::check()) {
            return redirect('login');
        }
        $this->_oInhoudPersoon->persoonDummyVerwijder();


        $dta = [];
        $dta['nieuwRecord'] = false;
        
        if ($persoonID == 0) {
            $dta['nieuwRecord'] = true;
            $persoonID = $this->_oInhoudPersoon->persoonNieuw();
        }
       
        $dta['persoon'] = $this->_oInhoudPersoon->persoon($persoonID);
        $dta['families'] = $this->_oInhoudPersoon->persoonFamilies($persoonID);
        $dta['ouders'] = $this->_oInhoudPersoon->ouders($persoonID);
        $dta['kinderen'] = $this->_oInhoudPersoon->kinderen($persoonID);
        $dta['documenten'] = $this->_oInhoudPersoon->documentLijst($persoonID);

        //print($dta['ouders'][0]->leeftijd);  die();
    
        return view('pagina.inhoud.indexpersoon')->with($dta);
    }

    /* --- FAMILIES --- */

    /**
     * public function jxInhoudPersoonFamilieInfo()
     * haalt info op van betreffende familie
     * @param $familieID
     * @return array
     */
    public function jxInhoudPersoonFamilieInfo(Request $request) {
        $json = ['succes' => false];

        $familieID = $request->familieID;
        try {
            $json['familie'] = $this->_oInhoudPersoon->familieInfo($familieID);
            $json['succes'] = true;
        }
        catch(Exception $ex) {

        }

        return response()->json($json);
    }

    /**
     * public function jxInhoudPersoonFamilieVerwijder() 
     * amputeert familie van persoon
     * @param $familieID
     * @param $persoonID
     * @return arry
     * 
     */
    public function jxInhoudPersoonFamilieVerwijder(Request $request) {

        $json = ['succes' => false];
        $familieID = $request->familieID;
        $persoonID = $request->persoonID;
        $json['familieID']= $familieID;
        $json['persoonID']= $persoonID;

        try {
            $this->_oInhoudPersoon->familieVerwijder($familieID, $persoonID);
            $json['succes'] = true;
        }
        catch (\ThrExeption $ex) {
            //throw $th;
        }

        return response()->json($json);
    }

/**
     * public function jxInhoudPersoonFamilies() 
     * haalt lijst met families op
     * @return arry
     * 
     */
    public function jxInhoudPersoonFamilies() {

        $json = [
            'succes' => true,
            'families' => $this->_oInhoudPersoon->families()
        ];
       
        return response()->json($json);
    }

    /**
     * public function jxInhoudPersoonFamilieBewaar()
     * update / bewaart familie
     * @param familienaam
     * @param familieID
     * @return json
     */
    public function jxInhoudPersoonFamilieBewaar(Request $request) {
        $json = ['succes' => false];

        $familieNaam = trim($request->familienaam);
        $familieID = $request->familieID;
        $persoonID = $request->persoonID;

        // familienaam leeg
        if (strlen($familieNaam) == 0) return response()->json($json);

        try {
            $dbRslt = $this->_oInhoudPersoon->familieBewaar($familieNaam, $familieID, $persoonID);
            $this->_oInhoudPersoon->familiePersoon($persoonID,  $dbRslt[0]);
            $json['familieID'] = $dbRslt[0];
            $json['familienaam'] = $dbRslt[1];
            $json['succes'] = true;

        }
        catch(Exception $ex) {

        }

        return response()->json($json);
    }

    /**
     * public function jxInhoudPersoonFamilieBewaar()
     * update / bewaart familie
     * @param familienaam
     * @param familieID
     * @return json
     */
    public function jxInhoudPersoonFamilieLink(Request $request) {
        $json = ['succes' => false];

        $familieID = $request->familieID;
        $persoonID = $request->persoonID;
        try {
            $dbRslt = $this->_oInhoudPersoon->familieLinkBewaar($familieID, $persoonID);
            $json['familieID'] = $familieID;
            $json['persoonID'] = $persoonID;

            $json['succes'] = true;
        }
        catch(Exception $ex) {

        }

        return response()->json($json);
    }
    

    /* --- OUDERS ---*/
    /**
     * public function jxInhoudPersoonOuderInfo(Request $request)
     * @param $ouderID
     * @return @array
     */
    public function jxInhoudPersoonOuderInfo(Request $request) {
        $json = ['succes' => false];

        $ouderID = $request->ouderID;
        try {
            $json['ouder']= $this->_oInhoudPersoon->ouderInfo($ouderID);
            $json['succes'] = true;
        }
        catch(Exception $ex) {

        }

        return response()->json($json);
    }
    
/**
 * public function jxInhoudPersoonOuderVerwijder()
 * verwijderd persoon van ouder
 * @param $ouderID
 * @param $persoonID
 * @return array
 * 
 */
    public function jxInhoudPersoonOuderVerwijder(Request $request) {
        $json = ['succes' => false];
        
        $ouderID = $request->ouderID;
        $persoonID = $request->persoonID;

        $json['ouderID'] = $ouderID;
        $json['persoonID'] = $persoonID;

        try {
            $this->_oInhoudPersoon->ouderVerwijder($ouderID, $persoonID);
            $json['succes'] = true;
        }
        catch (Exception $ex) {

        }
    

        return response()->json($json);
    }

/**
 * public function jxInhoudPersoonOuderLijst()
 * lijst van personen die ouder zijn dan de huidige persoon !instelling leeftijdsgrens
 * @param $persoonID
 * @return array
 * 
 */
    public function jxInhoudPersoonOuderLijst(Request $request) {
        $json = ['succes' => false];
        
        $persoonID = $request->persoonID;
        $json['persoonID'] = $persoonID;
        try {
            $json['ouders'] = $this->_oInhoudPersoon->ouderLijst($persoonID);
            $json['succes'] = true;
        }
        catch (Exception $ex) {
        }

        return response()->json($json);
}

/**
 * public function jxInhoudPersoonOuderBewaar()
 * koppelt ouder aan persoon
 * @param $persoonID
 * @param $ouderID
 * @return array
 * 
 */
public function jxInhoudPersoonOuderBewaar(Request $request) {
    $json = ['succes' => false];
    
    $persoonID = $request->persoonID;
    $ouderID = $request->ouderID;

    $json['persoonID'] = $persoonID;
    $json['ouderID'] = $ouderID;

    try {
        $json['ouders'] = $this->_oInhoudPersoon->ouderBewaar($persoonID, $ouderID);
        $json['succes'] = true;
    }
    catch (Exception $ex) {

    }

   

    return response()->json($json);
}
/* --- KINDEREN ---*/
    /**
     * public function jxInhoudPersoonKindInfo(Request $request)
     * haalt informatie van betreffende kind op
					
     * @param $kindID
     * @return @array
   
     */
    public function jxInhoudPersoonKindInfo(Request $request) {
        $json = ['succes' => false];
	
									 
								 

        $kindID = $request->kindID;
        try {
            $json['kind'] = $this->_oInhoudPersoon->kindInfo($kindID);
            $json['succes'] = true;
        }
        catch(Exception $ex) {

        }

        return response()->json($json);
    }
						   

   /**
     * public function jxInhoudPersoonKindVerwijder
     * verwijdert persoon van kind
     * @param $kindID
     * @param $persoonID
     * @return array
     */

    public function jxInhoudPersoonKindVerwijder(Request $request){
        $json = ['succes' => false];
        
        $kindID = $request->kindID;
        $persoonID = $request->persoonID;

        $json['kindID'] = $kindID;
        $json['persoonID'] = $persoonID;

        try {
           $this->_oInhoudPersoon->kindVerwijder($kindID, $persoonID);
           $json['succes'] = true;
        }
        catch(Exception $ex) {

        }

        return response()->json($json);
    }

 /**
     * public function jxInhoudPersoonKindLijst()
     * levert lijst op van personen die ouder zijn dan huidige persoon
     * @param $persoonID
     * @return array
     */

     public function jxInhoudPersoonKindLijst(Request $request){
        $json = ['succes' => false];
        
        $persoonID = $request->persoonID;

        $json['persoonID'] = $persoonID;

        try {
           $json['kinderen']=$this->_oInhoudPersoon->kindLijst($persoonID);
           $json['succes'] = true;
        }
        catch(Exception $ex) {

        }

        return response()->json($json);
    }

    public function jxGetAppSettings() {
        
        $json['crucifix'] = Instelling::get('app')['crucifix'];
        $json['deleteicon'] = Instelling::get('app')['delete-icon'];
        $json['linkicon'] = Instelling::get('app')['link-icon'];
        return response()->json($json);
    }
    
 /**
     * public function jxInhoudPersoonKindBewaar()
     * koppel een kind aan een persoon
     * @param $persoonID
     * @param $kindID
     * @return array
     */

     public function jxInhoudPersoonKindBewaar(Request $request){
        $json = ['succes' => false];
        
        $persoonID = $request->persoonID;
        $kindID = $request->kindID;

        $json['persoonID'] = $persoonID;
        $json['kindID'] = $kindID;

        try {
           $json['kinderen']=$this->_oInhoudPersoon->kindBewaar($persoonID, $kindID);
           $json['succes'] = true;
        }
        catch(Exception $ex) {

        }

        return response()->json($json);
    }

     /**
     * public function jxInhoudPersoonBewaar()
     * update persoon in database
     * @param $frmDta associatief array met data persoon
     * @return array
     */
    public function jxInhoudPersoonBewaar(Request $request) {
        $json = ['success'=> false];    

        $frmDta = $request->frmDta;

        try {
            switch($this->_oInhoudPersoon->persoonBewaar($frmDta)) {
                case 1:
                    $json['boodschap'] = trans('boodschappen.inhoudpersoon_bewaar_succes');
                    $json['status'] = true;
                    break;
                default:
                    $json['boodschap'] = trans('boodschappen.inhoudpersoon_bewaar_fout');
                    $json['status'] = false;
                }
                $json['success'] = true;
        
        }
        Catch(Excetion $ex) {

        }


        return response($json);
    
    }

    /* --- VERWIJDER PERSOON --- */
    /**
     *  public function jxInhoudPersoonVerwijderBoodschap()
     *  boodschap bevestig verwijderen persoon
     *  @return string
     */

     public function jxInhoudPersoonVerwijderBoodschap() {
        $json = [
            'succes'=> true,
            'boodschap' => trans('boodschappen.inhoudpersoon_verwijder_boodschap')
        ];

        return response()->json($json);
     }
     
     /**
      *  public function jxInhoudPersoonVerwijder()
      * verwijdert persoon uit database
      *  @param $persoonID 
      * @return array
      */
     public function jxInhoudPersoonVerwijder(Request $request) {
        $json = ['succes' => false ];

        $persoonID = $request->persoonID;

        try {
            if ($this->_oInhoudPersoon->verwijderPersoon($persoonID)) { 
                $json['status'] = 1;
                $json['boodschap'] = trans('boodschappen.inhoudpersoon_verwijder_succes');
            }
            else {
                $json['status'] = 0;
                $json['boodschap'] = trans('boodschappen.inhoudpersoon_verwijder_gefaald');
            }    
            $json['succes'] = true;
        }
        catch(Exception $ex) {

        }

        return response($json);

     }

     /**
      * public function jxPlaatsenLijst() 
      * haalt de lijst met plaaten op
      * @param veld
      * @return array      
      */

      public function jxPlaatsenLijst(Request $request) {
        $json = ['succes' => false ];
        $json['veld'] = $request->veld;
        try {
            $json['plaatsen'] = $this->_oPlaats->plaatsenLijst();
            $json['succes'] = true;
        }
        catch(Exception $ex) {

        }
       
        return response($json);
      }

           /**
      * public function jxInhoudPersoonPlaatsBewaar() 
      * bewaar de plaats en voeg eventueel nieuwe plaats toe aan plaatsen
      * @param plaatsveld
      * @param id
      * @param gemeente
      * @param land
      * @return array      
      */

      public function jxInhoudPersoonPlaatsBewaar(Request $request) {
        $persoonID = $request->persoonID;
        $plaatsveld = $request->plaatsveld;
        $id = $request->id;
        $gemeente = $request->gemeente;
        $land = $request->land;

        $json = ['succes' => false ];
        $json['plaatsveld'] = $request->plaatsveld;
        $json['id'] = $request->id;
        $json['gemeente'] = $request->gemeente;
        $json['land'] = $request->land;

        try {
            $rslt =  $this->_oInhoudPersoon->plaatsBewaar($persoonID, $plaatsveld, $id, $gemeente, $land);
            $json['succes'] = true;
        }
        catch(Exception $ex) {

        }
       

        return response($json);
  

      }
      

      /* --- PLAATSEN --- */
      /**
      * public function jxInhoudPersoonPlaatsen() 
      * haalt lijst met plaatsen op
      * @return array      
      */

      public function jxInhoudPersoonPlaatsen() {
        $json = ['succes' => false ];

        try {
            $json['plaatsen'] = $this->_oInhoudPersoon->plaatsen();
            $json['succes'] = true;
        }
        catch(Exception $ex) {

        }

        return response($json);

      }

      /**
      * public function jxInhoudPersoonPlaats() 
      * bewaar plaats en levert ID plaats op
      * @param land
      * @param gemeente
      * @return array      
      */

      public function jxInhoudPersoonPlaats(Request $request) {
        $json = ['succes' => false ];

        $gemeente = $request->gemeente;
        $land = $request->land;

        try {
            $json['plaats'] = $this->_oInhoudPersoon->plaatsNieuw($gemeente, $land);
            $json['succes'] = true;
        }
        catch(Exception $ex) {

        }

        return response()->json($json);

      }

/* --- DOCUMENTEN ---*/
/**
 *  public function jxInhoudPersoonDocumentUpload()
 *  upload een bestand
 *  @param $persoonID
 *  @param $bestand
 *  @return array
 */ 

 public function jxInhoudPersoonDocumentUpload(Request $request) {
    $json = ['succes' => false];

    $validatie = Validator::make($request->all(),
                                 ['bestand' => sprintf('required|file|mimes:%s|max:%d', Instelling::get('upload')['mimes'], Instelling::get('upload')['bestandsgrootte'])],
                                 ['bestand.required' => trans('boodschappen.inhoudpersoon_document_verplichtveld'),
                                  'bestand.mimes' => trans('boodschappen.inhoudpersoon_document_mimetype'),
                                  'bestand.max' => sprintf('%s%s MB', trans('boodschap.inhoudpersoon_document_bestandsgrootte'), round(Instelling::get('upload')['bestandsgrootte']/1024, 0))]
                                );
    
    if ($validatie->fails()) {
        $boodschappen = '';
        foreach($validatie->messages()->all() as $boodschap) {
            $boodschappen .= sprintf('- %s<br>', $boodschap);
        }
        $json['boodschappen'] = $boodschappen;
    }
    else {
        $bestand = $request->bestand;
        $bestandsNaam = sprintf('doc_%s.%s', time(), $bestand->getClientOriginalExtension());
        $bestandPad = base_path(implode('/', ['', 'public', Instelling::get('upload')['map'], '']));
        $bestand->move($bestandPad, $bestandsNaam);

        $json['documentID'] = $this->_oInhoudPersoon->documentBewaar($request->persoonID, $bestandsNaam);

        $json['pad'] = sprintf('/%s/%s', Instelling::get('upload')['map'], $bestandsNaam);
        $json['titel'] = '- todo -';
        $json['info'] = '';

        $json['succes'] = true;
    }

    return response()->json($json);
}

/**
 * public function jxInhoudPersoonDocument()
 * haalt informatie document op
 * @param $documentID
 * @return array
 */
public function jxInhoudPersoonDocument(Request $request) {
    $json = ['succes' => false];

    $documentID = $request->documentID;

    try {
        $rslt = $this->_oInhoudPersoon->document($documentID);
        $json['pad'] = sprintf('/%s/%s', Instelling::get('upload')['map'], $rslt->bestand);
        $json['titel'] = $rslt->titel ;
        $json['info'] = $rslt->info ;
        $json['documentID'] = $documentID ;
        $json['succes'] = true ;
    } catch (\Exception $ex) {
        
    }

    return response()->json($json);
}

/**
 * public function jxInhoudPersoonDocumentMeta()
 * update de metadata ven het betreffende document
 * @param $persoonID
 * @param $documentID
 * @param $documentTitel
 * @param $documentInfo
 * @return array
 */
public function jxInhoudPersoonDocumentMeta(Request $request) {
    $json = ['succes' => false];
    $persoonID = $request->persoonID;
    $documentID = $request->documentID;
    $documentTitel = $request->documentTitel;
    $documentInfo = $request->documentInfo;

    try {
        $this->_oInhoudPersoon->documentMeta($documentID, $documentTitel, $documentInfo);
        $json['documenten'] = $this->_oInhoudPersoon->documentLijst($persoonID);
        $json['succes'] = true;
    } catch (\Exception $ex) {
       
    }

    return response()->json($json);
}

/**
 * public function jxInhoudPersoonDocumentVerwijder()
 * verwijder het betreffende document
 * @param $persoonID
 * @param $documentID
 * @return array
 */
public function jxInhoudPersoonDocumentVerwijder(Request $request) {
    $json = ['succes' => false];
    $persoonID = $request->persoonID;
    $documentID = $request->documentID;

    try {
        // verwijder bestand
        $rslt = $this->_oInhoudPersoon->document($documentID);
        $pad = implode(DIRECTORY_SEPARATOR, [base_path(), 'public', Instelling::get('upload')['map'], $rslt->bestand]);

        if (file_exists($pad)) unlink($pad);

        // verwijder records
        
        $this->_oInhoudPersoon->documentVerwijder($persoonID, $documentID);

        $json['documenten'] = $this->_oInhoudPersoon->documentLijst($persoonID);
        $json['succes'] = true;

    } catch (\Exception $ex) {
       
    }

    return response()->json($json);
}

/**
 * public function jxInhoudPersoonTree()
 * haalt tree van persoon op
 * @param $persoonID
 * @param $familie
 * @return array
 */
public function jxInhoudPersoonTree(Request $request) {
    $json = ['succes' => false];

    $persoonID = $request->persoonID;
    $familieID = $request->familieID;

    //try {
        $rslt = $this->_oInhoudPersoon->tree($persoonID, $familieID);
        $json['families'] = $rslt['families'];
        $json['familieID'] = $rslt['familieID'];
        $json['tree'] = $rslt['tree'];
        $json['boodschap'] = $rslt['boodschap'];
        $json['ouderID'] = $rslt['ouderID'];
        $json['persoonID'] = $persoonID;
        $json['grootouderID'] = $rslt['grootouderID'];
        $json['succes'] = true;

    //} catch (\Exception $ex) {
       
   // }

    return response()->json($json);
}

public function jxGeborenPlaatsLedigen(Request $request) {
    $json = ['succes' => false];
    $persoonID = $request->persoonID;
    $json['persoonID'] = $persoonID;
  try {
        $this->_oInhoudPersoon->plaatsLedigen($persoonID, 'geborenplaats');
        $json['succes'] = true;
   } catch (\Exception $ex) {
        //throw $th;
   }
   

    return response()->json($json);
}   
public function jxGestorvenPlaatsLedigen(Request $request) {
    $json = ['succes' => false];
    $persoonID = $request->persoonID;
    $json['persoonID'] = $persoonID;
    try {
        $this->_oInhoudPersoon->plaatsLedigen($persoonID, 'gestorvenplaats');
        $json['succes'] = true;
    } catch (\Exception $ex) {
        //throw $th;
    }
   

    return response()->json($json);
}   


public function jxInhoudPersoonDupliceer(Request $request) {   
    $json = ['succes' => false];
    $persoonID = $request->persoonID;
    $json['persoonID'] = $persoonID;
    try {
        $json['persoonID'] = $this->_oInhoudPersoon->dupliceerPersoon($persoonID);
        $json['succes'] = true;
    } catch (\Exception $ex) {
        //throw $th;
    }
    return response()->json($json);
}

}