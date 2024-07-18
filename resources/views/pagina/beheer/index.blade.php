@extends('layout.app');

@section('inhoud')
    <div id="inhoudBeheer">
        <h1>{{ __('boodschappen.beheer_titel') }}</h1>
        <hr>

        <div class="row">
            <div class="col-sm-6">
                <h2>{{ __('boodschappen.beheer_layout') }}</h2>
                <div class="mb-5">
                    <label for="aantalItemsPerPagina" class="form-label">
                        {{ __('boodschappen.beheer_aantalperpagia') }}
                    </label>
                    <select id="aantalItemsPerPagina" class="form-select">
                        @foreach ($aantalperpaginakeuzelijst as $optie)
                            <option value="{{ $optie }}"
                                @php
                                    if ($optie == $aantalperpagina) print('selected');
                                @endphp
                                >{{ $optie }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-5">
                    <label for="aantalKnoppen" class="form-label">
                        {{ __('boodschappen.beheer_aantalknoppen') }}
                    </label>
                    <select id="aantalKnoppen" class="form-select">
                        @foreach ($aantalknoppenkeuzelijst as $optie)
                            <option value="{{ $optie }}"
                                @php
                                    if ($optie == $aantalknoppen) print('selected');
                                @endphp
                                >{{ $optie }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <h2>{{ __('boodschappen.beheer_leeftijdgrens') }}</h2>
                <div class="mb-3">
                    <label for="leeftijdgrensOuder" class="form-label">{{ __('boodschappen.beheer_leeftijdgrensouder')}}</label>
                    <input id="leeftijdgrensOuder" class="leeftijdgrens" type="text" 
                            value="" 
                            data-type="double" 
                            data-min="0"
                            data-max="120"
                            data-from="{{ $leeftijdgrensOuder[0] }}"
                            data-to="{{ $leeftijdgrensOuder[1] }}"
                            data-grid="true">

                    </input>
                </div>
                <div class="mb-3">
                    <label for="leeftijdgrenskind" class="form-label">{{ __('boodschappen.beheer_leeftijdgrenskind')}}</label>
                    <input id="leeftijdgrensKind" class="leeftijdgrens" type="text" 
                            value="" 
                            data-type="double" 
                            data-min="0"
                            data-max="120"
                            data-from="{{ $leeftijdgrensKind[0] }}"
                            data-to="{{ $leeftijdgrensKind[1] }}"
                            data-grid="true">

                    </input>
                </div>

                <div class="mb-3">
                    <label for="leeftijdgrensOverlijden" class="form-label">{{ __('boodschappen.beheer_leeftijdgrensoverlijden')}}</label>
                    <input id="leeftijdgrensOverlijden" class="leeftijdgrens" type="text" 
                            value="" 
                            data-type="single" 
                            data-min="0"
                            data-max="120"
                            data-from="{{ $overlijden }}"
                            data-to="{{ $overlijden }}"
                            data-grid="true">

                    </input>
                </div>
            </div>

            <div class="col-sm-6">
                <h2>{{ __('boodschappen.beheer_tree') }}</h2>
                    <div class="mb-5">
                        <label for="thema" class="form-label">
                            {{ __('boodschappen.beheer_treethema')}}
                        </label>
                        <select id="thema" class="form-select">
                            @foreach ($treethemas as $optie)
                                <option value="{{ $optie}}"
                                    @php
                                        if ($optie == $treethema) print('selected');
                                    @endphp                                
                                >{{ $optie }}
                            </option>
                                
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-5">
                        <h4>{{ __('boodschappen.beheer_kleurstandaard')}}</h4>
                        <div class="row mb-5">
                            <div class="col-4">
                                <label class="fom-label">{{ __('boodschappen.beheer_kleurvulling') }}</label>
                                <input type="color" class="form-control form-control-color" id="themaKleurStandaardVulling" value="{{ $themastandaard['fill']}}">
                            </div>
                            <div class="col-4">
                                <label class="fom-label">{{ __('boodschappen.beheer_kleurlijn') }}</label>
                                <input type="color" class="form-control form-control-color" id="themaKleurStandaardLijn" value="{{ $themastandaard['stroke']}}">
                            </div>
                            <div class="col-4">
                                <label class="fom-label">{{ __('boodschappen.beheer_kleurtekst') }}</label>
                                <input type="color" class="form-control form-control-color" id="themaKleurStandaardTekst" value="{{ $themastandaard['color']}}">
                            </div>
                        </div>    

                        <h4>{{ __('boodschappen.beheer_kleurrelatie')}}</h4>
                        <div class="row mb-5">
                            <div class="col-4">
                                <label class="fom-label">{{ __('boodschappen.beheer_kleurvulling') }}</label>
                                <input type="color" class="form-control form-control-color" id="themaKleurRelatieVulling" value="{{ $themalijn['fill']}}">
                            </div>
                            <div class="col-4">
                                <label class="fom-label">{{ __('boodschappen.beheer_kleurlijn') }}</label>
                                <input type="color" class="form-control form-control-color" id="themaKleurRelatieLijn" value="{{ $themalijn['stroke']}}">
                            </div>
                            <div class="col-4">
                                <label class="fom-label">{{ __('boodschappen.beheer_kleurtekst') }}</label>
                                <input type="color" class="form-control form-control-color" id="themaKleurRelatieTekst" value="{{ $themalijn['color']}}">
                            </div>
                        </div>    

                        <h4>{{ __('boodschappen.beheer_kleurpersoon')}}</h4>
                        <div class="row mb-5">
                            <div class="col-4">
                                <label class="fom-label">{{ __('boodschappen.beheer_kleurvulling') }}</label>
                                <input type="color" class="form-control form-control-color" id="themaKleurPersoonVulling" value="{{ $themapersoon['fill']}}">
                            </div>
                            <div class="col-4">
                                <label class="fom-label">{{ __('boodschappen.beheer_kleurlijn') }}</label>
                                <input type="color" class="form-control form-control-color" id="themaKleurPersoonLijn" value="{{ $themapersoon['stroke']}}">
                            </div>
                            <div class="col-4">
                                <label class="fom-label">{{ __('boodschappen.beheer_kleurtekst') }}</label>
                                <input type="color" class="form-control form-control-color" id="themaKleurPersoonTekst" value="{{ $themapersoon['color']}}">
                            </div>
                        </div> 
                    </div>


            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <button class="btn btn-primary float-end" type="button" id="beheerBewaar">
                    <i class="bi bi-check-square"></i>&NonBreakingSpace;    
                    {{ __('boodschappen.beheer_bewaar')}}

                    
                </button>


            </div>

        </div>
    </div>
@endsection