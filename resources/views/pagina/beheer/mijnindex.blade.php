@extends('layout.app');

@section('inhoud')
    <div id="beheerIndex">
        <h1>{{ __('boodschappen.beheer_titel') }}</h1>
        <hr>

        <div class="row">
            <div class="col-sm-4">
            </div>
            <div class="col-sm-8">
                <h2>{{ __('boodschappen.mijnfamilie_zoekresultaat') }}</h2>
                <div id="editor" class="card card-body">
                </div>
                <button type="button" class="btn btn-primary mt-1" id="bewaarInstellingen">
                    <i class="bi bi-floppy"></i>
                    {{ __('boodschappen.instellingen_bewaar') }}
                </button>

            </div>
        </div>
    </div>


    <script>
 
    </script>
@endsection
