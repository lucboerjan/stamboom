@extends('layout.app');

@section('inhoud')
    <div id="ledenIndex">
        <h1>{{ __('boodschappen.leden_titel') }}</h1>
        <hr>

        <div class="row">
            <div class="input-group mb-3">
                <input class="form-control" type="search" placeholder="Search" aria-label="Search" id="zoekVeld">
                <button class="btn btn-outline-success" type="button" id="zoekKnop">
                    <i class="bi bi-search"></i>
                </button>
                <button class="btn btn-outline-success me-5" type="button" id="zoekReset">
                    <i class="bi bi-x"></i>
                </button>
                <button class="btn btn-primary me" type="button" id="nieuwKnop">
                    <i class="bi bi-plus-square"></i>
                    Nieuw
                </button>
            </div>
        </div>
        <div class="row" id="lijst">

        </div>
    </div>
@endsection
