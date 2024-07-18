<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Http\Middleware\Instelling;
use App\Http\Controllers\TaalController;

use App\Models\InhoudFotos;

class InhoudFotosController extends Controller
{
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
