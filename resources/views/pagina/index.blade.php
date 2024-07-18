@extends('layout.app');

@section('inhoud')
    <h1>{{ __('boodschappen.titel') }}</h1>

    <p>taal: {{ session()->get('taal') }}</p>
@endsection