@extends('layout.app');

@section('inhoud')
@php
$crucifix = '';
if (isset(App\Http\Middleware\Instelling::get('app')['crucifix'])) {
    $crucifixIcon = URL::to(App\Http\Middleware\Instelling::get('app')['crucifix']);
    $crucifix = '<img class="crucifix" src="' . $crucifixIcon . '" />';
}
if (isset(App\Http\Middleware\Instelling::get('app')['delete-icon'])) {
    $deleteIcon = App\Http\Middleware\Instelling::get('app')['delete-icon'];
}

if (isset(App\Http\Middleware\Instelling::get('app')['link-icon'])) {
    $linkIcon = App\Http\Middleware\Instelling::get('app')['link-icon'];
}

$disabled = $nieuwRecord ? "disabled" : "";

@endphp

<div id="inhoudPersoon">


    <h1>{{ __('boodschappen.inhoudpersoon_titel') }}</h1>
    <hr>

    <div class="row">
        <div class="col-sm-5">
            <h2>&nbsp;</h2>

            <div class="card mb-3" id="inhoudPersoonFamilie">
                <div class="card-body">
                    <h5 class="card-title">
                        {{ __('boodschappen.inhoudpersoon_families') }}
                        @php
                            if (!$families) {
                                $stijl = 'display:inline-block;';
                            } else {
                                $stijl = count($families) == 2 ? 'display:none;' : 'display:inline-block;';
                            }
                         @endphp
                    
                        <button class="btn btn-success float-end me-3" style="{{ $stijl }}" type="button"
                            id="inhoudPersoonFamilieNieuw">
                            <i class="bi {{ $linkIcon }}"></i>
                        </button>
                    </h5>
                    @foreach ($families as $familie)
                    <div class="card-body mb-1 inhoudPersoonFamilie" id="familieID_{{ $familie->id }}">
                        <div class="meta clearfix">
                            <strong>
                                {{ $familie->naam }}
                            </strong>
                            <button class="btn btn-danger inhoudPersoonFamilieInfo float-end" type="button">
                            <i class="bi {{ $linkIcon }}"></i>

                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <h2>{{ __('boodschappen.inhoudpersoon_verwanten') }} </h2>

            <div class="card mb-2" id="inhoudPersoonOuders">
                <div class="card-body">
                    <h5 class="card-title">
                        {{ __('boodschappen.inhoudpersoon_ouders') }}
                        @php
                            if (!$ouders) {
                                $stijl = 'display:inline-block;';
                            } else {
                                $stijl = count($ouders) == 2 ? 'display:none;' : 'display:inline-block;';
                            }
                        @endphp

                        <button class="btn btn-success float-end me-3" id="inhoudPersoonOuderNieuw" {{ $disabled }} type="button"
                            style="{{ $stijl }} ">
                            <i class="bi {{ $linkIcon }}"></i>

                        </button>


                    </h5>
                    @foreach ($ouders as $ouder)

                    @php
    if ($ouder->leeftijd == 'Leeftijd onbekend')
        $persoonInfo = '(' . $crucifix . ' ??)';
    else {

        if (!is_null($ouder->gestorvendatum)) {
            $persoonInfo = '(' . $crucifix . ' ' . $ouder->leeftijd . ')';
        } else {
            $persoonInfo = '(' . $ouder->leeftijd . ')';
        }

    }
    $geslachtsClass = $ouder->sex == "M" ? "mannelijk" : "vrouwelijk";
   

                    @endphp

                    <div class="card-body mb-1 inhoudPersoonOuder" id="ouderID_{{ $ouder->id }}">
                        <div class="meta clearfix {{ $geslachtsClass }}">
                            <strong>
                                {{ $ouder->naam }} {{ $ouder->voornamen }} @php echo $persoonInfo; @endphp
                            </strong>
                            <button class="btn btn-success float-end persoonBewerk  mt-1 me-1" id="persoonID_{{$ouder->id}}"
                                type="button">
                                <i class="bi bi-pencil-square"></i>
                            </button>

                            <button class="btn btn-danger inhoudPersoonOuderVerwijder float-end mt-1 me-1" type="button">
                                <i class="bi {{ $linkIcon }}"></i>
                            </button>
                            <br>
                            <em>{{ $ouder->roepnaam }}</em><br>
                             {{ $ouder->geborendatum }} {{ $ouder->geborenplaats }} <br>
                             {{ $ouder->gestorvendatum }} {{ $ouder->gestorvenplaats }}
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="card mb-2" id="inhoudPersoonKinderen">

                

                <div class="card-body" id="lijstKinderen">
                    <h5 class="card-title">
        
                    <div class="float-end">
                        <button class="btn btn-success float-end me-3" id="inhoudPersoonKindNieuw" {{ $disabled }} type="button">
                            <i class="bi {{ $linkIcon }}"></i>

                        </button>

                    </div>
                    <div id="titel-kinderen">    
                        {{ __('boodschappen.inhoudpersoon_kinderen') }} ({{ count($kinderen) }})
                    </div>
                    </h5>
                    @foreach ($kinderen as $kind)
                    @php
    if ($kind->leeftijd == 'Leeftijd onbekend')
        $persoonInfo = '(' . $crucifix . ' ??)';
    else {

        if (!is_null($kind->gestorvendatum)) {
            $persoonInfo = '(' . $crucifix . ' ' . $kind->leeftijd . ')';
        } else {
            $persoonInfo = '(' . $kind->leeftijd . ')';
        }
    }
        $geslachtsClass = $kind->sex == "M" ? "mannelijk" : "vrouwelijk";
   
                    @endphp

                    <div class="card-body mb-1 inhoudPersoonKind" id="kindID_{{ $kind->id }}">

                        <div class="meta clearfix {{ $geslachtsClass }}">
                            <strong>
                                {{ $kind->naam }} {{ $kind->voornamen }} @php echo $persoonInfo; @endphp
                            </strong>
                            <button class="btn btn-success float-end persoonBewerk mt-1 me-1" id="persoonID_{{$kind->id}}"
                                type="button">
                                <i class="bi bi-info-square"></i>
                            </button>
                            <button class="btn btn-danger inhoudPersoonKindVerwijder float-end mt-1 me-1" type="button">
                                <i class="bi {{ $linkIcon }}"></i>
                            </button>
                            <br>
                            <em>{{ $kind->roepnaam }}</em><br>
                            {{ $kind->geborendatum }} {{ $kind->geborenplaats }} <br>
                            {{ $kind->gestorvendatum }} {{ $kind->gestorvenplaats }}
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <h2>{{ __('boodschappen.inhoudpersoon_documenten') }}</h2>
            <div class="card mb-3" id="inhoudPersoonDocumenten">
                <div class="card-body">
                    <h5 class="card-title" id="inhoudPersoonDocumentNieuw">
                        <i class="bi bi-cloud-upload"></i>
                    </h5>
                    <div class="card-body">
                        <ul class="list-group" id="inhoudPersoonDocumentLijst">
                            @if (count($documenten) > 0)
                                @foreach($documenten as $document)
                                    <li class="list-group-item inhoudPersoondocument" id="documentID_{{ $document->id }}">
                                        <div class="clearfix">
                                            <a href="{{ URL::to($document->bestand) }}" data-fancybox="document" class="fancybox" data-caption="<h4>{{ $document->titel}}</h4><div>{{ $document->info }}</div>">
                                                <strong>
                                                    {{ $document->titel }}
                                                </strong>
                                            </a>
                                            <button class="btn btn-light inhoudPersoonDocumentBewerk float-end" type="button">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                        </div>
                                    </li>
                                @endforeach
                            @endif
                        </ul>
                    </div>
                </div>
                
            </div>
        </div>


        <div class="col-sm-7" id="inhoudPersoonKolomRechts">
            @php
                $persoonInfoLevend = '';
                $persoonInfoOverleden = '';
                if ($persoon->leeftijd == 'Leeftijd onbekend')
                    $persoonInfoOverleden = '(' . $crucifix . ' ??)';
                else {
                    if (!is_null($persoon->gestorvendatum)) {
                        $persoonInfoOverleden = '(' . $crucifix . ' ' . $persoon->leeftijd . ')';
                    } else {
                        $persoonInfoLevend = '(Leeftijd ' . $persoon->leeftijd . ')';
                    }
                }
            @endphp

<h2>{{ __('boodschappen.inhoudpersoon_persoon') }}</h2>
<input type="hidden" id="inhoudPersoonID" value="{{ $persoon->id }}">
<div class="card">
    <div class="card-body">
        <h5 class="card-title">
        {{ __('boodschappen.inhoudpersoon_gegevens') }}
        <button type="button" class="btn btn-outline-success float-end" id="inhoudPersoonTree">
            <i class="bi bi-diagram-3"></i>
            {{ __('boodschappen.inhoudpersoon_lblbtntree') }}
        </button>
    </h5>
        <div class="mb-3">
            <label for="inhoudPersoonNaam" class="form-label">{{ __('boodschappen.inhoudpersoon_lblnaam')
                }}</label>
            <input type="text" class="form-control" id="inhoudPersoonNaam" value="{{ $persoon->naam }}">
        </div>
        <div class="mb-3">
            <label for="inhoudPersoonVoornamen" class="form-label">{{
__('boodschappen.inhoudpersoon_lblvoornamen') }}</label>
            <input type="text" class="form-control" id="inhoudPersoonVoornamen"
                value="{{ $persoon->voornamen }}">
        </div>
        <div class="mb-3">
            <label for="inhoudPersoonRoepnaam" class="form-label">{{
__('boodschappen.inhoudpersoon_lblroepnaam') }}</label>
            <input type="text" class="form-control" id="inhoudPersoonRoepnaam"
                value="{{ $persoon->roepnaam }}">
        </div>

        <div class="btn-group mb-3" role="group" aria-label="Basic radio toggle button group">

            <input type="radio" class="btn-check inhoudPersoonSex" name="btnsex" value="M" id="inhoudPersoonSexMan" autocomplete="off" {{ $persoon->sex == 'M' ? 'checked' : '' }}>
            <label for="inhoudPersoonSexMan" class="btn btn-outline-primary">
                <i class="bi bi-gender-male"></i></label>

            <input type="radio" class="btn-check inhoudPersoonSex" name="btnsex" value="V" id="inhoudPersoonSexVrouw" autocomplete="off" {{ $persoon->sex == 'V' ? 'checked' : '' }}>
            <label for="inhoudPersoonSexVrouw" class="btn btn-outline-primary">
            <i class="bi bi-gender-female"></i></label>

        </div>
        <div class="form-check form-switch">
            @php
                $checked = $persoon->geadopteerd ? "checked" : "";
            @endphp
            <input type="checkbox" class="form-check-input" role="switch" id="inhoudPersoonGeadopteerd" {{ $checked }}>
            <label for="inhoudPersoonGeadopteerd" class="form-check-label">{{__('boodschappen.inhoudpersoon_lblgeadopteerd') }}</label>
        </div>

        <div class="mb-3 ">
            <label for="inhoudPersoonGeboren" class="form-label">{{
__('boodschappen.inhoudpersoon_lblgeboren') }}</label>
            @php echo $persoonInfoLevend; @endphp
    <div class="row">
            <div class="col-4">
                <input type="date" class="form-control" id="inhoudPersoonGeborenop"
                    value="{{ $persoon->geborenop }}">
            </div>
            <div class="col-8">

                <div class="input-group">
                    <input type="text" class="form-control" readonly id="inhoudPersoonGeborenplaats"
                        value="{{ $persoon->geborenplaats }}">
                    <button class="btn btn-warning float-end" id="inhoudPersoonGeborenplaatsLedigen">
                        <i class="bi bi-trash"></i>
                    </button>
                    <button class="btn btn-primary float-end" id="inhoudPersoonGeborenplaatsKnop">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                </div>
            </div>
            </div>
            <input type="hidden" id="inhoudPersoonGeborenplaatsID" value="{{ $persoon->geborenplaatsID }}">
            <input type="hidden" id="inhoudPersoonGeborenop" value="{{ $persoon->geborenop }}">
            {{-- <input type="hidden" id="inhoudPersoonGeborenplaats" value="{{ $persoon->geborenplaatsID }}"> --}}
        </div>

        <div class="mb-3">
            <label for="inhoudPersoonGestorven" class="form-label">{{
__('boodschappen.inhoudpersoon_lblgestorven') }}</label>
            @php echo $persoonInfoOverleden; @endphp
            <div class="row">
                <div class="col-4">
                    <input type="date" class="form-control" id="inhoudPersoonGestorvenop"
                        value="{{ $persoon->gestorvenop }}">
                </div>
                <div class="col-8">

                    <div class="input-group">
                        <input type="text" class="form-control" readonly id="inhoudPersoonGestorvenplaats"
                            value="{{ $persoon->gestorvenplaats }}">
                            <button class="btn btn-warning float-end" id="inhoudPersoonGestorvenplaatsLedigen">
                                <i class="bi bi-trash"></i>
                            </button>
        
                        <button class="btn btn-primary float-end" id="inhoudPersoonGestorvenplaatsKnop">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                    </div>
                </div>
            </div>
            <input type="hidden" id="inhoudPersoonGestorvenplaatsID"
                value="{{ $persoon->gestorvenplaatsID }}">

            <input type="hidden" id="inhoudPersoonGestorvenop" value="{{ $persoon->gestorvenop }}">
            {{-- <input type="hidden" id="inhoudPersoonGestorvenplaats" value="{{ $persoon->gestorvenplaatsID }}"> --}}
        </div>
        <div class="mb-3">
            <label for="inhoudPersoonInfo" class="form-label">{{ __('boodschappen.inhoudpersoon_lblinfo')
                }}</label>
            <div class="form-control" id="inhoudPersoonInfo">
                {{ $persoon->info }}
            </div>
        </div>
    </div>
</div>
</div>
</div>
    <div class="row mb-5 mt-3">
        <hr>
        <div class="col-12" style="text-align:right;">
            <button type="button" class="btn btn-primary" id="inhoudPersoonBewaar">
                <i class="bi bi-check-square"></i>
                {{ __('boodschappen.inhoudpersoon_lblbtnbewaar') }}
            </button>
            <button type="button" class="btn btn-secondary" id="inhoudPersoonDupliceer">
                <i class="bi bi-files"></i>
                {{ __('boodschappen.inhoudpersoon_lblbtndupliceer') }}
            </button>
            <button type="button" class="btn btn-warning" id="inhoudPersoonVerwijder">
                <i class="bi bi-trash"></i>
                {{ __('boodschappen.inhoudpersoon_lblbtnverwijder') }}
            </button>
        </div>
    </div>
</div>
<script>
    let rte = new Quill(
        '#inhoudPersoonInfo',
        {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    [{ 'align': [] }],
                    ['bold', 'italic', 'underline'],
                    [{ 'list': 'bullet' }, { 'list': 'ordered' }],
                    [{ 'indent': '-1' }, { 'indent': '+1' }],
                    [{ 'color': [] }, { 'background': [] }],
                    ['link'],
                    ['image'],
                    ['clean']
                ]
            }
        }
    );

    @php
        $persoonInfo = html_entity_decode($persoon->info);
    @endphp

    rte.root.innerHTML = $('<textarea>').html('{{ $persoonInfo}}').text();

</script>
@endsection