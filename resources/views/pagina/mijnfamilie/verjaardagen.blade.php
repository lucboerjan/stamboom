@extends('layout.app');

@section('inhoud')
    <div id="mijnFamilieIndex">
        <h1>{{ __('boodschappen.mijnfamilie_verjaardagen') }}</h1>
        <hr>

        <div class="row">
            <div class="col-sm-4">
                <h2>{{ __('boodschappen.mijnfamilie_zoekfilter') }}</h2>
            </div>
            <div class="col-sm-8">
                <h2>{{ __('boodschappen.mijnfamilie_zoekresultaat') }}</h2>
                <div id="formulier">
                    @foreach ($verjaardagen as $verjaardag)
                        <strong>
                            {{ $verjaardag->verjaardag }} {{ $verjaardag->naam }} {{ $verjaardag->voornamen }} <br>
                        </strong>
                    @endforeach    
                </div>
            </div>
        </div>
    </div>
@endsection