<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use App\Http\Middleware\Instelling;
use App\Http\Controllers\TaalController;

class InhoudController extends Controller
{ 
    /**
     * public function inhoudZoek()
     * staat in voor het weergeven van de homepage (na aanmelden)
     * @return view indexzoek.blade.php
     */
    public function inhoudZoek() {
        // zet taal interface
        TaalController::taal();

        // redirect login indien niet aangemeld
        if (! Auth::check()) {
            return redirect('login');
        }
        $dta = [];
        $dta['isInhoudBeheerder'] = Auth::user()->level & 4 ? true : false;


        return view('pagina.inhoud.indexzoek')->with($dta);
    }

    /**
     * public function inhoudPersoon()
     * staat in voor het weergeven van de homepage (na aanmelden)
     * @return view indexpersoon.blade.php
     */
    public function inhoudPersoon() {
        // zet taal interface
        TaalController::taal();

        // redirect login indien niet aangemeld
        if (! Auth::check()) {
            return redirect('login');
        }
        $dta = [];


        return view('pagina.inhoud.indexpersoon')->with($dta);
    }

    /**
     * public function inhoudFotos()
     * staat in voor het weergeven van de homepage (na aanmelden)
     * @return view indexfotos.blade.php
     */
    public function inhoudFotos() {
        // zet taal interface
        TaalController::taal();

        // redirect login indien niet aangemeld
        if (! Auth::check()) {
            return redirect('login');
        }
        $dta = [];


        return view('pagina.inhoud.indexfotos')->with($dta);
    }
}
