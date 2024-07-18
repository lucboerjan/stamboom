@extends('layout.app')

@section('inhoud')
    <div id="inhoudFamilie">
        <h1>{{ __('boodschappen.inhoudfamilie_titel') }}</h1>
        <hr>

        <div class="row mb-5">
            <div class="col-6">
                <div class="input-group">
                    <input type="text" class="form-control" id="inhoudFamilieZoek">
                    <button class="btn btn-primary" id="inhoudFamilieFilter">
                        <i class="bi bi-search"></i>
                    </button>
                    <button class="btn btn-secondary" id="inhoudFamilieZoekAnnuleer">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
            <div class="col-6">
                <button class="btn btn-primary float-end" id="inhoudFamilieNieuw" type="button">
                    <i class="bi bi-person-add"></i>
                    {{ __('boodschappen.inhoudfamilie_nieuw') }}
                </button>
                <button class="btn btn-primary float-end me-1" id="inhoudFamilieMerge" type="button" disabled>
                    <i class="bi bi-person-bounding-box"></i>
                    {{ __('boodschappen.inhoudfamilie_samenvoegen') }}
                </button>
            </div>
        </div>
        <div class="row mb-3" id="inhoudFamilieLijst"></div>
    </div>
@endsection