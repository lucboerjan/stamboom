<nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/inhoudzoek') }}">
            @if (isset(App\Http\Middleware\Instelling::get('app')['logo']) &&
                    !empty(App\Http\Middleware\Instelling::get('app')['logo']))
                <img src="{{ URL::to(App\Http\Middleware\Instelling::get('app')['logo']) }}" id="logo">
            @endif
            {{-- {{ config('app.name', 'Laravel') }} --}}
            {{ __('boodschappen.navkop_titel') }}

        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Left Side Of Navbar -->
            <ul class="navbar-nav me-auto">
                @auth
                    {{-- dropdown Mijn Familie --}}
                    @if (Auth::user()->level & 0x01)
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-vcard"></i>
                            {{ __('boodschappen.navkop_mijnfamilie') }}
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="/inhoudzoek">
                                    <i class="bi bi-people"></i>
                                    {{ __('boodschappen.navkop_mijnfamilie') }}
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="/verjaardagen">
                                    <i class="bi bi-cake"></i>
                                    {{ __('boodschappen.navkop_verjaardagen') }}
                                </a>
                            </li>
                        </ul>
                    </li>

                    @endif
                    {{-- dropdown Ledenbeheer (gebruikers) --}}
                    @if (Auth::user()->level & 0x02)
                        <li class="nav-item">
                            <a class="nav-link" href="/leden" role="button">
                                <i class="bi bi-person-square"></i>
                                {{ __('boodschappen.navkop_leden') }}
                            </a>
                        </li>
                    @endif
                    {{-- dropdown Inhoudbeheer --}}
                    @if (Auth::user()->level & 0x04)
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-vcard"></i>
                            {{ __('boodschappen.navkop_inhoud') }}
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="/inhoudpersoon/0">
                                    <i class="bi bi-person-add"></i>
                                    {{ __('boodschappen.navkop_inhoudNieuw') }}
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="/inhoudzoek/beheer">
                                    <i class="bi bi-people"></i>
                                    {{ __('boodschappen.navkop_inhoudZoek') }}
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item" href="/inhoudfamilie">
                                    <i class="bi bi-people"></i>
                                    {{ __('boodschappen.navkop_inhoudFamilies') }}
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="/inhoudplaats">
                                    <i class="bi bi-signpost-split"></i>
                                    {{ __('boodschappen.navkop_inhoudPlaatsen') }}
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif
                    {{-- dropdown Admin --}}
                    @if (Auth::user()->level & 0x08)
                    <li class="nav-item">
                        <a class="nav-link" href="/beheer" role="button">
                                <i class="bi bi-ui-checks"></i>
                                {{ __('boodschappen.navkop_admin') }}
                            </a>
                        </li>
                    @endif

                @endauth
            </ul>

            <!-- Right Side Of Navbar -->
            <ul class="navbar-nav ms-auto">
                <!-- dropdown Authentication -->
                @auth
                    <li class="nav-item dropdown">
                        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            <i class="bi bi-person-bounding-box"></i>
                            {{ Auth::user()->name }}
                        </a>

                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                {{ __('authenticatie.logout') }}
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </li>
                @endauth
                <!-- dropdown TALEN -->
                <li class="nav-item dropdown">
                    <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                        <i class="bi bi-globe-americas"></i>
                        {{ __('boodschappen.taal') }}
                    </a>

                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        @foreach (App\Http\Middleware\Instelling::get('talen') as $taal => $taalOpties)
                            <a class="nav-link" href="/taal/{{ $taal }}">
                                <img src="{{ URL::to($taalOpties[1]) }}">
                                {{ $taalOpties[0] }}
                            </a>
                        @endforeach
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>
