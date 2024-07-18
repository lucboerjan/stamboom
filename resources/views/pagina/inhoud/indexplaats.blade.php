@extends('layout.app')

@section('inhoud')
    <div id="inhoudPlaats">
        <h1>{{ __('boodschappen.inhoudplaats_titel') }}</h1>
        <hr>

        <div class="row mb-5">
            <div class="col-6">
                <div class="input-group">
                    <input type="text" class="form-control" id="inhoudPlaatsZoek">
                    <button class="btn btn-primary" type="button" id="inhoudPlaatsFilter">
                        <i class="bi bi-search"></i>
                    </button>
                    <button class="btn btn-secondary" type="button" id="inhoudPlaatsZoekAnnuleer">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
            <div class="col-6">
                <button class="btn btn-primary float-end" type="button" id="inhoudPlaatsNieuw">
                    <i class="bi bi-person-add"></i>
                    {{ __('boodschappen.inhoudplaats_nieuw') }}
                </button>
                <button class="btn btn-primary float-end me-1" type="button" disabled id="inhoudPlaatsMerge">
                    <i class="bi bi-person-bounding-box"></i>
                    {{ __('boodschappen.inhoudplaats_samenvoegen') }}
                </button>
            </div>
        </div>
        <div class="row mb-3" id="inhoudPlaatsLijst">

        </div>
    </div>
@endsection