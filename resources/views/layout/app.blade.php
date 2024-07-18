<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @if (isset(App\Http\Middleware\Instelling::get('app')['favicon']))
        @php
            $favicon = URL::to(App\Http\Middleware\Instelling::get('app')['favicon']);
        @endphp
        <link rel="shortcut icon" href="{{ $favicon }}" type="image/pngn">
    @endif
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css">
    <!-- Scripts -->

    <script src="{{ URL::to('js/jquery.js') }}"></script>
    <script src="{{ URL::to('js/ajx.js') }}"></script>
    <script src="{{ URL::to('js/modaal.js') }}"></script>
    <script src="{{ URL::to('js/paginering.js') }}"></script>
    
    <!-- Quill -->
    <link rel="stylesheet" href="{{ URL::to('js/quill/quill.snow.css')}}">
    <script src="{{ URL::to('js/quill/quill.min.js') }}"></script>
    
    <!-- FANCYBOX -->
    <link rel="stylesheet" href="{{ URL::to('js/fancybox/jquery.fancybox.min.css')}}">
    <script src="{{ URL::to('js/fancybox/jquery.fancybox.min.js') }}"></script>

    <!-- ionslider -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.3.1/css/ion.rangeSlider.min.css"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.3.1/js/ion.rangeSlider.min.js"></script>
    
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

        <!-- mermaid.js -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/mermaid/10.8.0/mermaid.min.js"></script>
        <script>
            mermaid.initialize({
                startOnLoad:true,
                securityLevel: 'loose'
            })
        </script>
</head>
<body>
    <div id="app">
        @include('include.kop')
        <main class="py-4">
            <div class="container">
                @yield('inhoud')
            </div>
        </main>
    </div>
</body>
</html>
