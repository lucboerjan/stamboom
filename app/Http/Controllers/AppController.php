<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use App\Http\Middleware\Instelling;
use App\Http\Controllers\TaalController;

class AppController extends Controller
{
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

        return view('pagina.index')->with($dta);
        //return view('pagina.inhoud.inhoudzoek')->with($dta);
        return view('pagina.inhoud.indexzoek')->with($dta);
    }
}
