<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use App\Http\Middleware\Instelling;
use App\Http\Controllers\TaalController;

class BeheerController extends Controller
{ 
    /**
     * public function index()
     * staat in voor het weergeven van de homepage (na aanmelden)
     * @return
     */
    public function index() {
        // zet taal interface
        TaalController::taal();

        // redirect login indien niet aangemeld
        if (! Auth::check()) {
            return redirect('login');
        }
        $dta=[
            'aantalperpagina'=> Instelling::get('paginering')['aantalperpagina'],
            'aantalperpaginakeuzelijst'=> Instelling::get('paginering')['aantalperpaginakeuzelijst'],
            'aantalknoppen' => Instelling::get('paginering')['knoppen'],
            'aantalknoppenkeuzelijst' => Instelling::get('paginering')['knoppenkeuzelijst'],

            'leeftijdgrensOuder' => Instelling::get('leeftijdsgrens')['ouder'],
            'leeftijdgrensKind' => Instelling::get('leeftijdsgrens')['kind'],
            'overlijden' => Instelling::get('leeftijdsgrens')['overlijden'],

            'treethemas' => Instelling::get('tree.themas'),
            'treethema' => Instelling::get('tree.thema'),
            'themastandaard' => $this->_themaKleuren(Instelling::get('tree.kleur')['standaard']),
            'themalijn' => $this->_themaKleuren(Instelling::get('tree.kleur')['lijn']),
            'themapersoon' => $this->_themaKleuren(Instelling::get('tree.kleur')['persoon']),
        
        ];
       
        return view('pagina.beheer.index')->with($dta);
    }

    public function jxGetInstellingen() {
        $instellingen = [];
        $dta['instellingen']= json_decode(file_get_contents(sprintf('%s/%s', storage_path(), 'instelling.json')), true);
       // $dta['instellingen']=   file_get_contents(sprintf('%s/%s', storage_path(), 'instelling.json'));
        $dta['succes'] = true;
        return $dta;

    }

    
    public function jxSetInstellingen(Request $request) {
        $instellingen = $request->instellingen;
        file_put_contents(sprintf('%s/%s', storage_path(), 'instelling.json'), $instellingen);
        $json['succes'] = true;
        return response()->json($json);

    }

    private function _themaKleuren($kleur) {
        $rslt = [];
        $tmp = explode(',', $kleur);
        foreach($tmp as $tmpItem) {
            $kv = explode(':',$tmpItem);
            $rslt[$kv[0]] = $kv[1];
        }

        return $rslt;


    }


    /**
     * public function jxBeheerUpdate()
     * update instellingen
     * return @array
     */
    public function jxBeheerUpdate(Request $request) {

        $json = ['succes' => false];
        $json['boodschappen' ] = trans('boodschappen.beheerbewaar_boodschappen');
        
        try {
            // pagineering
            $paginering = Instelling::get('paginering');
            $paginering['knoppen'] = $request->aantalKnoppen;
            $paginering['aantalperpagina'] = $request->aantalItemsPerPagina;
            Instelling::set('paginering', $paginering);

            // leeftijdgrens
            $leeftijdsgrens = [
                'ouder' => array_map('intval',explode(';', $request->leeftijdgrensOuder)),
                'kind' => array_map('intval',explode(';', $request->leeftijdgrensKind)),
                'overlijden' =>  $request->leeftijdgrensOverlijden

            ];
            Instelling::set('leeftijdsgrens', $leeftijdsgrens);

            // thema mermaid
            Instelling::set('tree.thema', $request->thema);

            // thema kleuren
            $treeKleur = [
                'standaard' => $request->kleurStandaard,
                'lijn' => $request->kleurRelatie,
                'persoon' => $request->kleurPersoon
            ];
            Instelling::set('tree.kleur', $treeKleur);
            $json['succes'] = true;

        } catch (\Exception $ex) {
                //throw $th;
        }

        return response()->json($json);

    }
}
