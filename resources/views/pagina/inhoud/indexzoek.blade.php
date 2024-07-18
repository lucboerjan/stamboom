@extends('layout.app');

@section('inhoud')
    <div id="inhoudZoek">
        <h1>{{ __('boodschappen.inhoudzoek_titel') }}</h1>
        <hr>
        
        <div class="row">
            <div class="col-sm-4">
                <h2>{{ __('boodschappen.mijnfamilie_zoekfilter') }}</h2>

                <div class="alert alert-warning invisible" id="inhoudZoekFout"></div>

                <div class="mb-3">
                    <label for="inhoudZoekFamilie" class="form-label">
                        {{ __('boodschappen.inhoudzoek_familie') }}
                    </label>
                    <select id="inhoudZoekFamilie" class="form-select"></select>
                </div>

                <div class="mb-3">
                    <input type="text" class="form-control" id="inhoudZoekNaam" placeholder="{{ __('boodschappen.inhoudzoek_naam') }}">
                </div>

                <div class="mb-3">
                    <input type="text" class="form-control" id="inhoudZoekVoornaam" placeholder="{{ __('boodschappen.inhoudzoek_voornaam') }}">
                </div>

                <div class="mb-3">
                    <input type="text" class="form-control" id="inhoudZoekRoepnaam" placeholder="{{ __('boodschappen.inhoudzoek_roepnaam') }}">
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="inhoudZoekGeadopteerd">
                        <label for="inhoudZoekGeadopteerd" class="form-check-label">
                            {{ __('boodschappen.inhoudzoek_geadopteerd') }}
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <input type="text" class="form-control" id="inhoudZoekGeboortejaar" placeholder="{{ __('boodschappen.inhoudzoek_geboortejaar') }}">
                </div>
                
                <div class="mb-3">
                    <label for="inhoudZoekGeboorteplaats" class="form-label">
                        {{ __('boodschappen.inhoudzoek_geboorteplaats') }}
                    </label>
                    <select id="inhoudZoekGeboorteplaats" class="form-select"></select>
                </div>

                <div class="mb-3">
                    <input type="text" class="form-control" id="inhoudZoekSterftejaar" placeholder="{{ __('boodschappen.inhoudzoek_sterftejaar') }}">
                </div>

                <div class="mb-3">
                    <label for="inhoudZoekSterfteplaats" class="form-label">
                        {{ __('boodschappen.inhoudzoek_sterfteplaats') }}
                    </label>
                    <select id="inhoudZoekSterfteplaats" class="form-select"></select>
                </div>

                <div class="d-flex justify-content-end mb-3">
                    <button type="button" class="btn btn-primary me-1" id="inhoudZoekKnop">
                        <i class="bi bi-search"></i>
                        {{ __('boodschappen.inhoudzoek_knop') }}
                    </button>
                    <button type="button" class="btn btn-secondary" id="inhoudZoekReset">
                        <i class="bi bi-x-square"></i>
                        {{ __('boodschappen.inhoudzoek_reset') }}
                    </button>
                </div>
            </div>
            <div class="col-sm-8">
                <h2 class="position-relative mb-5">
                    
                    <div class="float-end">
                        @if  ($isInhoudBeheerder)
                        <button class="btn btn-primary position-absolute end-0" type="button" id="inhoudZoekNieuw">
                            <i class="bi bi-plus-square"></i>
                            {{ __('boodschappen.inhoudzoek_nieuw') }}

                        </button>
                    @endif

                    </div>

                    <div id="titelZoekResultaat">
                        {{ __('boodschappen.mijnfamilie_zoekresultaat') }}
                    </div>


                    
                </h2>
                <div id="inhoudZoekResultaat">
                    

                </div>
                
                <h5><div class=" card card-body mt-3 appinfo" id="appInfo">
                                          
                        <div class="row">
                            <div class="col-sm-7">
                                <div class="card card-body dbInfo" id="dbInfo">

                                </div>


                            </div>
                            <div class="col-sm-5">
                                <div class="card card-body familieInfo" id="familieInfo">
                                
                             </div>

                        </div>
                    </div>


                </div></h5>

                <h3><div class=" card card-body mt-1" id="gebruiksaanwijzing">
                        
                
            </div></h3>
        </div>
    </div>
@endsection