<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

// TAALCONTROLLER
use App\Http\Controllers\TaalController;

Route::controller(TaalController::class)->group(function () {
    Route::get('/taal/{taal?}', 'zetTaal');
});

// APPCONTROLLER
use App\Http\Controllers\AppController;

Route::controller(AppController::class)->group(function () {
    // Route::get('/', 'index');
    // Route::get('/home', 'index');
});

// MIJNFAMILIECONTROLLER
use App\Http\Controllers\MijnFamilieController;

Route::controller(MijnFamilieController::class)->group(function () {
    // Route::get('/', 'index');
    // Route::get('/home', 'index');
    // Route::get('/mijnfamilie', 'index');
});

// LEDENCONTROLLER
use App\Http\Controllers\LedenController;

Route::controller(LedenController::class)->group(function () {
    Route::get('/leden', 'index');
    Route::post('/jxLedenLijst', 'jxLedenLijst');
    Route::post('/jxLedenGet', 'jxLedenGet');
    Route::post('/jxLedenBewaar', 'jxLedenBewaar');
    Route::post('/jxLedenResetInfo', 'jxLedenResetInfo');
    Route::post('/jxLedenResetMail', 'jxLedenResetMail');
});

// INHOUDCONTROLLER-ZOEK
use App\Http\Controllers\InhoudZoekController;

Route::controller(InhoudZoekController::class)->group(function () {
    Route::get('/', 'inhoudZoek');
    Route::get('/home', 'inhoudZoek');
    Route::get('/inhoudzoek/{beheer?}', 'inhoudZoek');
    Route::post('/jxInhoudZoekInhoudBeheerder', 'jxInhoudZoekInhoudBeheerder');
    Route::post('/jxInhoudZoekFamilies', 'jxInhoudZoekFamilies');
    Route::post('/jxInhoudZoekGeboorteplaatsen', 'jxInhoudZoekGeboorteplaatsen');
    Route::post('/jxInhoudZoekSterfteplaatsen', 'jxInhoudZoekSterfteplaatsen');
    Route::post('/jxInhoudZoekBoodschappen', 'jxInhoudZoekBoodschappen');
    Route::post('/jxInhoudZoekPersoon', 'jxInhoudZoekPersoon');
    Route::post('/jxInhoudZoekPersoonInfo', 'jxInhoudZoekPersoonInfo');
    Route::post('/jxInhoudZoekPersoonDocument', 'jxInhoudZoekPersoonDocument');
    Route::post('/jxAppInfo', 'jxAppInfo');
    //verjaardagen
    Route::get('/verjaardagen', 'verjaardagen');
});

// INHOUDCONTROLLER-PERSOON
use App\Http\Controllers\InhoudPersoonController;

Route::controller(InhoudPersoonController::class)->group(function () {
    Route::get('/inhoudpersoon/{persoonID?}', 'inhoudPersoon');
    Route::post('/jxInhoudPersoonBoodschappen', 'jxInhoudPersoonBoodschappen');

    // beheer GEKOPPELDE FAMILIE
    Route::post('/jxInhoudPersoonFamilieInfo', 'jxInhoudPersoonFamilieInfo');
    Route::post('/jxInhoudPersoonFamilieVerwijder', 'jxInhoudPersoonFamilieVerwijder');
    Route::post('/jxInhoudPersoonFamilies', 'jxInhoudPersoonFamilies');
    Route::post('/jxInhoudPersoonFamilieBewaar', 'jxInhoudPersoonFamilieBewaar');
    Route::post('/jxInhoudPersoonFamilieLink', 'jxInhoudPersoonFamilieLink');

    // beheer OUDERS
    Route::post('/jxInhoudPersoonOuderInfo', 'jxInhoudPersoonOuderInfo');
    Route::post('/jxInhoudPersoonOuderVerwijder', 'jxInhoudPersoonOuderVerwijder');
    Route::post('/jxInhoudPersoonOuderLijst', 'jxInhoudPersoonOuderLijst');
    Route::post('/jxInhoudPersoonOuderBewaar', 'jxInhoudPersoonOuderBewaar');
    // beheer KINDEREN
    Route::post('/jxInhoudPersoonKindInfo', 'jxInhoudPersoonKindInfo');
    Route::post('/jxInhoudPersoonKindVerwijder', 'jxInhoudPersoonKindVerwijder');
    Route::post('/jxInhoudPersoonKindLijst', 'jxInhoudPersoonKindLijst');
    Route::post('/jxInhoudPersoonKindBewaar', 'jxInhoudPersoonKindBewaar');
    // bewaar PERSOON
    Route::post('/jxInhoudPersoonBewaar', 'jxInhoudPersoonBewaar');
    // verwijder PERSOON
    Route::post('/jxInhoudPersoonVerwijderBoodschap', 'jxInhoudPersoonVerwijderBoodschap');
    Route::post('/jxInhoudPersoonVerwijder', 'jxInhoudPersoonVerwijder');
    // plaatsen ophalen en bewaren
    Route::post('/jxPlaatsenLijst', 'jxPlaatsenLijst');
    Route::post('/jxInhoudPersoonPlaatsBewaar', 'jxInhoudPersoonPlaatsBewaar');
    Route::post('/jxInhoudPersoonPlaatsen', 'jxInhoudPersoonPlaatsen');
    Route::post('/jxInhoudPersoonPlaats', 'jxInhoudPersoonPlaats');
    // documenten
    Route::post('/jxInhoudPersoonDocumentUpload', 'jxInhoudPersoonDocumentUpload');
    Route::post('/jxInhoudPersoonDocument', 'jxInhoudPersoonDocument');
    Route::post('/jxInhoudPersoonDocumentMeta', 'jxInhoudPersoonDocumentMeta');
    Route::post('/jxInhoudPersoonDocumentVerwijder', 'jxInhoudPersoonDocumentVerwijder');
    Route::post('/jxGeborenPlaatsLedigen', 'jxGeborenPlaatsLedigen');
    Route::post('/jxGestorvenPlaatsLedigen', 'jxGestorvenPlaatsLedigen');

    // TREE
    Route::post('/jxInhoudPersoonTree', 'jxInhoudPersoonTree');

    // DUPLICEER
    Route::post('/jxInhoudPersoonDupliceer', 'jxInhoudPersoonDupliceer');


    // AppSettings
    Route::post('/jxGetAppSettings', 'jxGetAppSettings');
});

// INHOUDCONTROLLER-FOTOS
use App\Http\Controllers\InhoudFotosController;

Route::controller(InhoudFotosController::class)->group(function () {
    Route::get('/inhoudfotos', 'inhoudFotos');
});

// BEHEERCONTROLLER
use App\Http\Controllers\BeheerController;

Route::controller(BeheerController::class)->group(function () {
    Route::get('/beheer', 'index');
    Route::post('/jxGetInstellingen', 'jxGetInstellingen');
    Route::post('/jxSetInstellingen', 'jxSetInstellingen');
    Route::post('/jxSetInstellingen', 'jxSetInstellingen');
    Route::post('/jxBeheerUpdate', 'jxBeheerUpdate');

});


// INHOUDCONTROLLER-FAMILIE
use App\Http\Controllers\InhoudFamilieController;
Route::controller(InhoudFamilieController::class)->group(function() {
    Route::get('/inhoudfamilie', 'inhoudFamilie');
    Route::post('/jxInhoudFamilieLijst', 'jxInhoudFamilieLijst');
    Route::post('/jxInhoudFamilieUpdate', 'jxInhoudFamilieUpdate');
    Route::post('/jxInhoudFamilieVerwijderInfo', 'jxInhoudFamilieVerwijderInfo');
    Route::post('/jxInhoudFamilieVerwijderBevestig', 'jxInhoudFamilieVerwijderBevestig');
    Route::post('/jxInhoudFamilieMergeLijst', 'jxInhoudFamilieMergeLijst');
    Route::post('/jxInhoudFamilieMergeUitvoeren', 'jxInhoudFamilieMergeUitvoeren');

});

// INHOUDCONTROLLER-PLAATS
use App\Http\Controllers\InhoudPlaatsController;
Route::controller(InhoudPlaatsController::class)-> group( function() {
    Route::get('/inhoudplaats', 'inhoudPlaats');
    Route::post('/jxInhoudPlaatsLijst', 'jxInhoudPlaatsLijst');

    // update wijziging plaatsnaam
    Route::post('/jxInhoudPlaatsUpdate', 'jxInhoudPlaatsUpdate');

    // 
    Route::post('/jxInhoudPlaatsVerwijderInfo', 'jxInhoudPlaatsVerwijderInfo');
    Route::post('/jxInhoudPlaatsVerwijderBevestig', 'jxInhoudPlaatsVerwijderBevestig');

    // plaatsmerge
    Route::post('/jxInhoudPlaatsMergeLijst', 'jxInhoudPlaatsMergeLijst');
    Route::post('/jxInhoudPlaatsMergeUitvoeren', 'jxInhoudPlaatsMergeUitvoeren');

    // plaats fout
    Route::post('/jxInhoudPlaatsFout', 'jxInhoudPlaatsFout');
} );


Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
