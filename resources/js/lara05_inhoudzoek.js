$(() => {
    if ($('#inhoudZoek').length) INHOUDZOEK.init();
});


const INHOUDZOEK = {

    csrfToken: $('meta[name="csrf-token"]').attr('content'),
    isInhoudBeheerder: false,
    boodschappen: [],
    zoekOpdracht: {},
    crucifix: '',


    init: () => {
        // is gebruiker inhoudbeheerder of lid ?
        INHOUDZOEK.inhoudBeheerder();

        //ophalen boodschappen
        INHOUDZOEK.getBoodschappen();

        //lijst families
        INHOUDZOEK.families();

        // alfabetische liist met geboorte- en sterfteplaatsen
        INHOUDZOEK.geboortePlaatsen();
        INHOUDZOEK.sterftePlaatsen();

        //knop zoek
        $('#inhoudZoekKnop').on('click', () => { INHOUDZOEK.zoek() });
        $('#inhoudZoekReset').on('click', () => { INHOUDZOEK.reset(); });

        // knop nieuw
        $('#inhoudZoekNieuw').on('click', () => { INHOUDZOEK.persoonBewerk(0); });


        // knop info & bewerk
        $('#inhoudZoekResultaat').on('click', '.inhoudZoekInfo', function (evt) { INHOUDZOEK.persoonInfo($(this).parent().parent().parent().attr('id').split('_')[1]); });
        $('#inhoudZoekResultaat').on('click', '.inhoudZoekBewerk', function (evt) { INHOUDZOEK.persoonBewerk($(this).parent().parent().parent().attr('id').split('_')[1]); });


        $('body').on('click', '.knopBewerk', function (evt) { INHOUDZOEK.persoonBewerk($(this).attr('id').split('_')[1]); });
        $('body').on('click', '.knopInfo', function (evt) { INHOUDZOEK.persoonInfo($(this).attr('id').split('_')[1]); });

        //paginering
        $('#inhoudZoekResultaat').on('click', '#paginering a', function (evt) {
            INHOUDZOEK.zoekPagina($(this).data('pagina'));
        });

        //--+++ TREE +++---
        //PERSOONTREE

        $('body').on('change', '.treeFamilie', function (evt) {
            let familieID = $('.treeFamilie:checked').val();
            let persoonID = $('#mermaidPersoonID').val();
            INHOUDZOEK.persoonTree(persoonID, familieID);
        });

        $('body').on('click', '.knopTree', function (evt) {
            let persoonID = $(this).prop('id').split('_')[1];
            INHOUDZOEK.persoonTree(persoonID);
        });

        $('body').on('click', '.knopDoc', function (evt) {
            let persoonID = $(this).prop('id').split('_')[1];
            INHOUDZOEK.persoonDocument(persoonID);
        });


        $('body').on('click', 'g.node', function (evt) {
            let el = evt.target;
            do {
                el = el.parentElement;
            }
            while (el.tagName != 'g');

            el = el.parentElement;

            let persoonID = el.id.split('-')[2];
            let familieID = $('.treeFamilie:checked').val();
            INHOUDZOEK.persoonTree(persoonID, familieID);
        });


        $('body').on('click', '.inhoudZoekTree', function (evt) {

            let persoonID = $(this).parent().parent().parent().prop('id').split('_')[1];
            INHOUDZOEK.persoonTree(persoonID);

        });

        // 
        $('body').on('click', '#persoonInfoSluit', () => { MODAAL.verberg(); });
        $('body').on('click', '#verberg', () => { MODAAL.verberg(); });

        // persoondocument
        $('#inhoudZoek').on('click', '.inhoudZoekDocument', function (evt) {
            INHOUDZOEK.persoonDocument($(this).parent().parent().parent().prop('id').split('_')[1]);
        });

        $('body').on('click', '.inhoudZoekForward', function (evt) {
            let persoonID = $(this).prop('id').split('_')[1];
            INHOUDZOEK.persoonDocument(persoonID);
        });

        $('body').on('click', '#inhoudZoekDocumentTree', () => {
            INHOUDZOEK.persoonTree($('#inhoudZoekDocumentID').val());
        });

        $('body').on('click', '#inhoudZoekDocumentInfo', () => {
            INHOUDZOEK.persoonInfo($('#inhoudZoekDocumentID').val());
        });

        $('body').on('click', '#inhoudContainerTreeInfo', () => {
            let persoonID = $('#mermaidPersoonID').val();
            INHOUDZOEK.persoonInfo(persoonID)
        });
        $('body').on('click', '#inhoudContainerTreeDocs', () => {
            let persoonID = $('#mermaidPersoonID').val();
            INHOUDZOEK.persoonDocument(persoonID);
        });

        

        INHOUDZOEK.appinfo();


    },

    /* --- APPINFO --- */
    appinfo: () => {
        fetch('/jxAppInfo', {
            method: 'post',
            body: new FormData(),

            headers: {
                "X-CSRF-Token": INHOUDZOEK.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then((response) => {
            return response.json();
        }).then((res) => {
            if (res.succes) {

                let div2use = $('#dbInfo');

                div2use.empty();    
                let inhoud =  `
                    <div style="display:flex; flex-direction: column;">
                    <strong>${INHOUDZOEK.boodschappen.titelappinfo}</strong>
                    <hr>
                    </div>
                `;

                let vroegstgeboren = res.appinfo.vroegstgeboren.split('-');
                vroegstgeboren = vroegstgeboren[2] + '-' + vroegstgeboren[1] + '-' + vroegstgeboren[0];
                let vroegstgestorven = res.appinfo.vroegstgestorven.split('-');
                vroegstgestorven = vroegstgestorven[2] + '-' + vroegstgestorven[1] + '-' + vroegstgestorven[0];

                
                if (INHOUDZOEK.boodschappen.aantalpersonendatabase=== undefined) {
                    INHOUDZOEK.appinfo();
                    // inhoud += `  
                    //     Ververs de pagina om de gegevens te updaten.
                    // `
                }
                else {
                   inhoud += `
                    <div style="display:flex"><strong>${INHOUDZOEK.boodschappen.aantalpersonendatabase} :&nbsp;</strong> ${res.appinfo.aantalpersonen}</div>
                    <div style="display:flex"><strong>${INHOUDZOEK.boodschappen.aantalfamiliesdatabase} :&nbsp;</strong> ${res.appinfo.aantalfamilies}</div>
                    <div style="display:flex"><strong>${INHOUDZOEK.boodschappen.aantalwoonplaatsendatabase} :&nbsp;</strong> ${res.appinfo.aantalplaatsen}</div>
                    <div style="display:flex"><strong>${INHOUDZOEK.boodschappen.aantallandendatabase} :&nbsp;</strong> ${res.appinfo.aantallanden}</div>
                    <div style="display:flex"><strong>${INHOUDZOEK.boodschappen.vroegstegeboortedatumdatabase} :&nbsp;</strong> ${vroegstgeboren}</div>
                    <div style="display:flex"><strong>${INHOUDZOEK.boodschappen.vroegstegestorvendatabase} :&nbsp;</strong> ${vroegstgestorven}</div>
                `;
            }
                div2use.append(inhoud);

                div2use = $('#familieInfo');
                div2use.empty();  
                inhoud = `
                    <strong>${INHOUDZOEK.boodschappen.titelinhoudzoekfamilieinfo}</strong><hr>
                `;

                res.families.forEach(familie => {
                    inhoud += `${familie.naam} (${familie.aantal})<br>`
                });


                div2use.append(inhoud);

                div2use = $('#gebruiksaanwijzing');
                div2use.empty();  
                inhoud = `
                    <strong>${INHOUDZOEK.boodschappen.titelgebruiksaanwijzing}</strong><hr>
                    <div style="display:flex"><strong>${INHOUDZOEK.boodschappen.gebruiksaanwijzing}</div>
                `;
                div2use.append(inhoud);
            }


        }).catch((error) => {
            console.log(error);
        });
    },


    /* --- INHOUDBEHEERDER --- */
    inhoudBeheerder: () => {
        fetch('/jxInhoudZoekInhoudBeheerder', {
            method: 'post',
            body: new FormData(),

            headers: {
                "X-CSRF-Token": INHOUDZOEK.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then((response) => {
            return response.json();
        }).then((res) => {
            if (res.succes) {
                INHOUDZOEK.isInhoudBeheerder = res.isinhoudbeheerder;
            }
        }).catch((error) => {
            console.log(error);
        });
    },

    /* --- LIJST FAMILIENAMEN --- */
    families: () => {
        fetch('/jxInhoudZoekFamilies', {
            method: 'post',
            body: new FormData(),

            headers: {
                "X-CSRF-Token": INHOUDZOEK.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then((response) => {
            return response.json();
        }).then((res) => {
            if (res.succes) {
                let families = res.families;
                let select = $('#inhoudZoekFamilie');
                select.empty();
                select.append($('<option>').prop('value', 0).text('-'));
                families.forEach(familie => {
                    select.append($('<option>').prop('value', familie.id).text(familie.naam));
                });
            }
        }).catch((error) => {
            console.log(error);
        });
    },

    /* --- LIJST GEBOORTEPLAATSEN --- */
    geboortePlaatsen: () => {
        fetch('/jxInhoudZoekGeboorteplaatsen', {
            method: 'post',
            body: new FormData(),

            headers: {
                "X-CSRF-Token": INHOUDZOEK.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then((response) => {
            return response.json();
        }).then((res) => {
            if (res.succes) {
                let geboorteplaatsen = res.geboorteplaatsen;
                let select = $('#inhoudZoekGeboorteplaats');
                select.empty();
                select.append($('<option>').prop('value', 0).text('-'));
                geboorteplaatsen.forEach(geboorteplaats => {
                    select.append($('<option>').prop('value', geboorteplaats.id).text(geboorteplaats.plaats));
                });
            }
        }).catch((error) => {
            console.log(error);
        });
    },

    /* --- LIJST STERFTEPLAATSEN --- */
    sterftePlaatsen: () => {
        fetch('/jxInhoudZoekSterfteplaatsen', {
            method: 'post',
            body: new FormData(),

            headers: {
                "X-CSRF-Token": INHOUDZOEK.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then((response) => {
            return response.json();
        }).then((res) => {
            if (res.succes) {
                let sterfteplaatsen = res.sterfteplaatsen;
                let select = $('#inhoudZoekSterfteplaats');
                select.empty();
                select.append($('<option>').prop('value', 0).text('-'));
                sterfteplaatsen.forEach(sterfteplaats => {
                    select.append($('<option>').prop('value', sterfteplaats.id).text(sterfteplaats.plaats));
                });
            }
        }).catch((error) => {
            console.log(error);
        });
    },

    /* --- FOUTBOODSCHAPPEN --- */
    getBoodschappen: () => {
        fetch('/jxInhoudZoekBoodschappen', {
            method: 'post',
            body: new FormData(),

            headers: {
                "X-CSRF-Token": INHOUDZOEK.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then((response) => {
            return response.json();
        }).then((res) => {
            if (res.succes) {
                res.boodschappen.split(',').forEach(boodschap => {
                    let tmp = boodschap.split(':');
                    INHOUDZOEK.boodschappen[tmp[0]] = tmp[1];

                });
                
            }
        });
    },

    /* --- ZOEK --- */
    zoek: () => {
        // wis inhoud alert + verberg
        $('#inhoudZoekFout').removeClass('visible').addClass('invisible').empty();
        $('#inhoudZoekResultaat').empty();
        // variabelen
        let gevalideerd = true;
        let boodschap = [];

        // inhoud formulier
        let familie = $('#inhoudZoekFamilie').val();
        let naam = $('#inhoudZoekNaam').val().trim();
        let voornaam = $('#inhoudZoekVoornaam').val().trim();
        let roepnaam = $('#inhoudZoekRoepnaam').val().trim();
        let geadopteerd = $('#inhoudZoekGeadopteerd').is(':checked') ? 1 : 0;

        // geboortejaar en -plaats
        let geboortejaar = $('#inhoudZoekGeboortejaar').val().trim();
        if (/^\d{4}(\-\d{4})?$/.test(geboortejaar)) {
            let jaren = geboortejaar.split('-');
            if (jaren.length == 2 && jaren[0] > jaren[1]) {
                boodschap.push(INHOUDZOEK.boodschappen.geboortejaren);
                gevalideerd = false;
            }

        }
        else if (geboortejaar.length > 0) {
            boodschap.push(INHOUDZOEK.boodschappen.geboortejaar);
            gevalideerd = false;
        }
        let geboorteplaats = $('#inhoudZoekGeboorteplaats').val();

        // sterftejaar en -plaats
        let sterftejaar = $('#inhoudZoekSterftejaar').val().trim();

        if (/^\d{4}(\-\d{4})?$/.test(sterftejaar)) {
            let jaren = sterftejaar.split('-');
            if (jaren.length == 2 && jaren[0] > jaren[1]) {
                boodschap.push(INHOUDZOEK.boodschappen.sterftejaren);
                gevalideerd = false;
            }

        }
        else if (sterftejaar.length > 0) {
            boodschap.push(INHOUDZOEK.boodschappen.sterftejaar);
            gevalideerd = false;
        }

        let sterfteplaats = $('#inhoudZoekSterfteplaats').val();




        if (gevalideerd) {
            $('#inhoudZoekFout').removeClass('visible').addClass('invisible').empty();

            let frmDta = {
                familie: familie,
                naam: naam,
                voornaam: voornaam,
                roepnaam: roepnaam,

                geadopteerd: geadopteerd,
                geboortejaar: geboortejaar,
                geboorteplaats: geboorteplaats,
                sterftejaar: sterftejaar,
                sterfteplaats: sterfteplaats,
                pagina: 0
            }

            INHOUDZOEK.zoekVerstuur(frmDta);
        }
        else {
            let boodschapTekst = boodschap.join('<br>');
            $('#inhoudZoekFout').removeClass('invisible').addClass('visible').empty().html(boodschapTekst);
        }

    },

    // ZOEK PAGINA KNOP
    zoekPagina: (pagina = 0) => {
        let frmDta = INHOUDZOEK.zoekOpdracht;
        frmDta.pagina = pagina;
        INHOUDZOEK.zoekVerstuur(frmDta);
    },

    // VERSTUUR ZOEKREQUEST
    zoekVerstuur: (frmDta) => {
        fetch('/jxInhoudZoekPersoon', {
            method: 'post',
            body: JSON.stringify(frmDta),

            headers: {
                "X-CSRF-Token": INHOUDZOEK.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then((response) => {
            return response.json();
        }).then((res) => {
            INHOUDZOEK.crucifix = res.crucifix;
            let inhoudZoekResultaat = $('#inhoudZoekResultaat');
            inhoudZoekResultaat.empty();

            if (res.succes) {
                if (res.dbRslt.length == 0) {
                    INHOUDZOEK.zoekGeenResultaat(inhoudZoekResultaat);
                }
                else {
                    INHOUDZOEK.zoekOpdracht = res.zoekopdracht;
                    INHOUDZOEK.zoekResultaat(res, inhoudZoekResultaat);
                }

            }
            else {
                INHOUDZOEK.zoekGeenResultaat(inhoudZoekResultaat);
            }
        });
    },

    //verwerking zoekresultaat
    zoekResultaat: (res, inhoudZoekResultaat) => {
        $('#appInfo').hide();
        $('#gebruiksaanwijzing').hide();
        let titel = $('#titelZoekResultaat').text().split("(")[0].trim() + ' (#' + res.aantal + ')';
        $('#titelZoekResultaat').text(titel);
        res.dbRslt.forEach(persoon => {
            let persoonInfo = '';
            if (persoon.leeftijd == 'Leeftijd onbekend') {
                persoonInfo = '(' + INHOUDZOEK.boodschappen.leeftijdonbekend +')';
            }
            else {
                if (persoon.gestorvenop !== null) {
                    persoonInfo = '(<img class="crucifix" src="' + INHOUDZOEK.crucifix + '"/>' + persoon.leeftijd + ')';
                }

                else {
                    persoonInfo = '(' + persoon.leeftijd + ')';
                }
            }


            // let overleden = persoon.gestorvenop || persoon.leeftijd == "Leeftijd onbekend" ? '<img class="crucifix" src="' + INHOUDZOEK.crucifix + '"/>' : '';
            // let leeftijd = persoon.gestorvenop || persoon.leeftijd == "Leeftijd onbekend" ? '(' + overleden + persoon.leeftijd + ')' : '(' + persoon.leeftijd + ')';

            let naam = `${persoon.voornamen} ${persoon.naam} <small><em>${persoon.roepnaam ? persoon.roepnaam : ''}</em></small>  ${persoonInfo}`;
            let geboren = `${res.plaatsen[persoon.geborenplaatsID]} ${INHOUDZOEK.datum(persoon.geborenop)}`;
            let gestorven = `${persoon.gestorvenplaatsID ? res.plaatsen[persoon.gestorvenplaatsID] : ''} ${persoon.gestorvenop ? INHOUDZOEK.datum(persoon.gestorvenop) : ''}`;

            let inhoud = `${INHOUDZOEK.boodschappen.geboren}: ${geboren} <br>${persoon.gestorvenop ? INHOUDZOEK.boodschappen.gestorven + ": " + gestorven : ""}`;

            let kanBewerken = '';
            let beheer = window.location.toString().split('/').pop().startsWith('beheer');
            console.log(beheer);
            if (INHOUDZOEK.isInhoudBeheerder && beheer) {
                kanBewerken = $('<button>').addClass('inhoudZoekBewerk btn btn-primary me-1')
                    .append($('<i>').addClass('bi bi-pencil-square'))
            }

            $(inhoudZoekResultaat).append($('<div>').addClass('card mb-2').prop('id', `persoonID_${persoon.id}`)
                .append($('<div>').addClass('card-body position-relative')
                    .append($('<div>').addClass('float-end').append(
                        $('<button>').addClass('inhoudZoekInfo btn btn-success me-1')
                            .append($('<i>').addClass('bi bi-info-square'))
                    )
                        .append($('<button>').addClass('inhoudZoekDocument btn btn-primary me-1')
                            .append($('<i>').addClass('bi bi-files'))
                        )
                        .append($('<button>').addClass('inhoudZoekTree btn btn-primary me-1')
                            .append($('<i>').addClass('bi bi-diagram-3'))
                        )
                        .append(
                            kanBewerken
                        )
                    )
                    .append($('<h5>').addClass('card-title').html(naam)
                    )
                    .append($('<div>').addClass('card-title').html(inhoud)
                    )
                )
            )

        });

        //paginering
        if (parseInt(res.aantalpaginas) > 0) {
            $(inhoudZoekResultaat).append(
                $('<div>').css('text-align', 'center')
                    .addClass('mt-1')
                    .append(PAGINERING.pagineer(res.pagina, res.knoppen, res.aantalpaginas))
            );
        }
    },

    zoekGeenResultaat: (inhoudZoekResultaat) => {
        let titel = $('#titelZoekResultaat').text().split("(")[0].trim();
        $('#titelZoekResultaat').text(titel);
        $('#inhoudZoekResultaat').append($('<div>').addClass('alert alert-warning')
            .html(`<i class="bi bi-exclamation-triangle"></i> ${INHOUDZOEK.boodschappen.geenresultaat}`));

        $('#appInfo').hide();
    },

    //datum omzetten naar dd-mm-jjjj
    datum: (dtm) => {
        if (dtm) {
            let tmp = dtm.split('-');
            tmp.reverse()
            return tmp.join('-');
        }
        else {
            return '-';
        }
    },

    // persoon info
    persoonInfo: (persoonID) => {
        fetch('/jxInhoudZoekPersoonInfo', {
            method: 'post',
            body: JSON.stringify({ persoonID: persoonID }),

            headers: {
                "X-CSRF-Token": INHOUDZOEK.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then((response) => {
            return response.json();
        }).then((res) => {
            if (res.succes) {
                MODAAL.kop(INHOUDZOEK.boodschappen.persoonInfo);
                let inhoud = '';
                let persoonInfo = '';
                if (res.persoon.leeftijd == 'Leeftijd onbekend') {
                    persoonInfo = '(' + INHOUDZOEK.boodschappen.leeftijdonbekend +')';
                }
                else if (res.persoon.gestorvendatum !== null) {
                    persoonInfo = '(<img class="crucifix" src="' + INHOUDZOEK.crucifix + '"/>' + res.persoon.leeftijd + ')';
                }

                else {
                    persoonInfo = '(' + res.persoon.leeftijd + ')';
                }



                //let overleden = res.persoon.gestorvenop ? '<img class="crucifix" src="' + INHOUDZOEK.crucifix + '"/>' : '';
                //let leeftijd = res.persoon.gestorvenop ? '(' + overleden + res.persoon.leeftijd + ')' : '';

                // persoon
                let beheer = window.location.toString().split('/').pop().startsWith('beheer');
                let kanBewerken = INHOUDZOEK.isInhoudBeheerder && beheer ? 'block' : 'none';

                inhoud += `
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">
                            ${INHOUDZOEK.boodschappen.persoon}
                        </h5>
                    
                        <div class="card-text">
                            <h6>
                                <span class="fw-bolder">
                                    ${res.persoon.voornamen} ${res.persoon.naam} <small><em>${res.persoon.roepnaam ? res.persoon.roepnaam : ''}</em></small> ${persoonInfo}
                                </span>
                                <button class="btn btn-primary float-end knopBewerk" id="persoonID_${res.persoon.id}" style="display: ${kanBewerken}">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="btn btn-primary float-end knopTree me-1" id="persoonID_${res.persoon.id}">
                                    <i class="bi bi-diagram-3"></i>
                                </button>
                                <button class="btn btn-primary float-end knopDoc me-1" id="persoonID_${res.persoon.id}">
                                    <i class="bi bi-files"></i>
                                </button>
                            </h6>
                            <p>
                                ${INHOUDZOEK.boodschappen.geboren}: ${res.plaatsen[res.persoon.geborenplaatsID]} ${INHOUDZOEK.datum(res.persoon.geborenop)}<br>
                                ${INHOUDZOEK.boodschappen.gestorven}: ${res.persoon.gestorvenplaatsID ? res.plaatsen[res.persoon.gestorvenplaatsID] : ''} ${res.persoon.gestorvenop ? INHOUDZOEK.datum(res.persoon.gestorvenop) : ''}
                            </p>
                        </div>
                    </div>
                </div>
            `;

                // ouders
                if (res.ouders.length > 0) {

                    inhoud += `
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">
                                ${INHOUDZOEK.boodschappen.ouders}
                               
                            </h5>
                    
                        <div class="card-text">
                        `;
                    res.ouders.forEach(ouder => {
                        let persoonInfo = '';
                        if (ouder.leeftijd == 'Leeftijd onbekend') {
                            persoonInfo = '(' + INHOUDZOEK.boodschappen.leeftijdonbekend +')';
                        }
                        else {
                            if (ouder.gestorvendatum !== null) {
                                persoonInfo = '(<img class="crucifix" src="' + INHOUDZOEK.crucifix + '"/>' + ouder.leeftijd + ')';
                            }

                            else {
                                persoonInfo = '(' + ouder.leeftijd + ')';
                            }
                        }

                        inhoud += `
                        
                            <h6>
                                <span class="fw-bolder">
                                    ${ouder.voornamen} ${ouder.naam} <small><em>${ouder.roepnaam ? ouder.roepnaam : ''}</em></small> ${persoonInfo}
                                </span>
                                <button class="btn btn-primary float-end knopBewerk" id="persoonID_${ouder.id}" style="display: ${kanBewerken}">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="btn btn-primary float-end knopTree me-1" id="persoonID_${res.persoon.id}">
                                    <i class="bi bi-diagram-3"></i>
                                </button>

                                <button class="btn btn-primary float-end knopDoc me-1" id="persoonID_${res.persoon.id}">
                                    <i class="bi bi-files"></i>
                                </button>   
                                <button class="btn btn-success float-end knopInfo me-1" id="persoonID_${ouder.id}">
                                    <i class="bi bi-info-square"></i>
                                </button>                             

                                

                            </h6>
                            <p>
                                ${INHOUDZOEK.boodschappen.geboren}: ${res.plaatsen[ouder.geborenplaatsID]} ${INHOUDZOEK.datum(ouder.geborenop)} <br>
                                ${INHOUDZOEK.boodschappen.gestorven}: ${ouder.gestorvenplaatsID ? res.plaatsen[ouder.gestorvenplaatsID] : ''} ${ouder.gestorvenop ? INHOUDZOEK.datum(ouder.gestorvenop) : ''} <br>
                            </p>
                            `
                    });
                    inhoud += `
                            </div>
                        </div>
                    </div>
                    `;
                }

                // kinderen
                if (res.kinderen.length > 0) {

                    inhoud += `
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">
                                ${INHOUDZOEK.boodschappen.kinderen}
                               
                            </h5>
                    
                        <div class="card-text">
                        `;
                    res.kinderen.forEach(kind => {
                        let persoonInfo = '';

                        if (kind.leeftijd == 'Leeftijd onbekend') {
                            persoonInfo = '(' + INHOUDZOEK.boodschappen.leeftijdonbekend +')';
                        }
                        else {
                            if (kind.gestorvendatum !== null) {
                                persoonInfo = '(<img class="crucifix" src="' + INHOUDZOEK.crucifix + '"/>' + kind.leeftijd + ')';
                            }

                            else {
                                persoonInfo = '(' + kind.leeftijd + ')';
                            }

                        }

                        inhoud += `
                        
                            <h6>
                                <span class="fw-bolder">
                                    ${kind.voornamen} ${kind.naam} <small><em>${kind.roepnaam ? kind.roepnaam : ''}</em></small> ${persoonInfo}
                                </span>
                                <button class="btn btn-primary float-end knopBewerk" id="persoonID_${kind.id}" style="display: ${kanBewerken}">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="btn btn-primary float-end knopTree me-1" id="persoonID_${res.persoon.id}">
                                    <i class="bi bi-diagram-3"></i>
                                </button>
                                <button class="btn btn-primary float-end knopDoc me-1" id="persoonID_${res.persoon.id}">
                                    <i class="bi bi-files"></i>
                                </button>
                                <button class="btn btn-success float-end knopInfo me-1" id="persoonID_${kind.id}">
                                    <i class="bi bi-info-square"></i>
                                </button>
                                

                            </h6>
                            <p>
                                ${INHOUDZOEK.boodschappen.geboren}: ${res.plaatsen[kind.geborenplaatsID]} ${INHOUDZOEK.datum(kind.geborenop)} <br>
                                ${INHOUDZOEK.boodschappen.gestorven}: ${kind.gestorvenplaatsID ? res.plaatsen[kind.gestorvenplaatsID] : ''} ${kind.gestorvenop ? INHOUDZOEK.datum(kind.gestorvenop) : ''} <br>
                            </p>
                            `
                    });
                    inhoud += `
                            </div>
                        </div>
                    </div>
                    `;
                }

                let inhoudContainer = $('<div>').prop('id', 'modaalScroll')
                    .css({ 'overflow-y': 'scroll' })
                    .append($('<div>').prop('id', 'modaalScrollInhoud')
                        .html(inhoud));
                MODAAL.inhoud(inhoudContainer);
                MODAAL.voet(MODAAL.knop('persoonInfoSluit', 'primary', 'x-square', INHOUDZOEK.boodschappen.sluit));
                MODAAL.grootte('modal-xl');
                MODAAL.toon();
            }
        });

    },





    // persoon bewerk
    persoonBewerk: (persoonID) => {
        location.href = `/inhoudpersoon/${persoonID}`;
    },

    // reset formuler - wis resultaat
    reset: () => {
        $('#inhoudZoekResultaat').empty();
        $('#inhoudZoekFamilie option:first-child').prop('selected', 'selected');
        $('#inhoudZoekNaam').val('');
        $('#inhoudZoekVoornaam').val('');
        $('#inhoudZoekRoepnaam').val('');
        $('#inhoudZoekGeadopteerd').prop('checked', false);
        $('#inhoudZoekGeboortejaar').val('');
        $('#inhoudZoekGeboorteplaats option:first-child').prop('selected', 'selected');
        $('#inhoudZoekSterftejaar').val('');
        $('#inhoudZoekSterfteplaats option:first-child').prop('selected', 'selected');

    },

    // --- FAMILY TREE --- 
    persoonTree: (persoonID = 0, familieID = 0) => {
        if (persoonID == 0) persoonID = $('#inhoudPersoonID').val();
        let frmDta = {
            'persoonID': persoonID,
            'familieID': familieID
        }

        fetch('/jxInhoudPersoonTree', {
            method: 'post',
            body: JSON.stringify(frmDta),

            headers: {
                'X-CSRF-Token': INHOUDZOEK.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then(response => {
            return response.json();
        }).then(res => {
            INHOUDZOEK.persoonTreeSucces(res);
        }).catch(err => {
            console.log(err);
        });
    },

    persoonTreeSucces: (res) => {
        if (res.succes) {
            let kop = `<input type="hidden" id="mermaidPersoonID" value="${res.persoonID}">
                <div class="float-end">
                    <button type="button" class="btn btn-primary" id="inhoudContainerTreeInfo">
                        <i class="bi bi-info-square"></i>
                    </button>
                
                    <button type="button" class="btn btn-primary" id="inhoudContainerTreeDocs">
                        <i class="bi bi-files"></i>
                    </button>
                </div>
            `;

            res.families.forEach(familie => {
                let checked = familie.id == res.familieID ? ' checked' : '';
                kop += `<div class="form-check form-check-inline">
                        <input type="radio" class="form-check-input treeFamilie" name="treeFamilie" ${checked} value="${familie.id}">
                        <label class="form-check-label" for="">
                            ${familie.naam}
                        </label>
                    </div>`;
            });

            MODAAL.kop(kop);

            MODAAL.voet(MODAAL.knop('verberg', 'secondary', 'x-square', INHOUDZOEK.boodschappen.sluit));
            MODAAL.grootte('modal-xl');
            MODAAL.toon();

            if (res.tree.length == 0) {
                $('#modDlgInhoud').html(`<h5 class='text-danger'>${res.boodschap}</h5>`);
            }

            mermaid.render('mermaid', res.tree).then(({ svg, bindFunctions }) => {
                document.getElementById('modDlgInhoud').innerHTML = svg;
                bindFunctions?.(element);
            });
        }
    },
    // --- PERSOONDOCUMENT ---
    persoonDocument: (persoonID = 0) => {
        let frmDta = {
            persoonID: persoonID
        }
        fetch('/jxInhoudZoekPersoonDocument', {
            method: 'post',
            body: JSON.stringify(frmDta),

            headers: {
                'X-CSRF-Token': INHOUDZOEK.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then(response => {
            return response.json();
        }).then(res => {
            INHOUDZOEK.persoonDocumentSucces(res);
        }).catch(err => {
            console.log(err);
        });

    },

    persoonDocumentSucces: (res) => {
        if (res.succes) {
            let kop = ` 
                <input type="hidden" id="inhoudZoekDocumentID" value="${res.persoon.id}">
                ${res.persoon.naam} ${res.persoon.voornamen}
                <div class="float-end">
                    <button type="button" class="btn btn-primary" id="inhoudZoekDocumentInfo">
                        <i class="bi bi-info-square"></i>
                    </button>
                    <button type="button" class="btn btn-primary" id="inhoudZoekDocumentTree">
                        <i class="bi bi-diagram-3"></i>
                    </button>
                </div>
            `;
            MODAAL.kop(kop);

            //--- inhoud-begin

            // kolom links
            let inhoudLinks = `
                <h5>${INHOUDZOEK.boodschappen.persoon}</h5>
                <div class="alert alert-dark"><div class="mb-3">
    <i class="bi bi-gender-${res.persoon.sex === 'M' ? 'male' : 'female'}"></i> &nbsp;
    <strong>
        ${res.persoon.naam !== null ? res.persoon.naam : ''} 
        ${res.persoon.voornamen !== null ? res.persoon.voornamen : ''} &nbsp;&nbsp;
        ${(res.persoon.leeftijd > 0 && res.persoon.gestorvendatum !== null) ?
                    `<img class="crucifix" src="${INHOUDZOEK.crucifix}"/> ${res.persoon.leeftijd}` : ''}  
        ${(res.persoon.leeftijd > 0 && res.persoon.gestorvendatum == null) ?
                    `${res.persoon.leeftijd}` : ''}  
         
    </strong>
</div>

                    <div class="mb-3">
                        <i class="bi bi-person-circle"></i> &nbsp; ${res.persoon.roepnaam !== null ? res.persoon.roepnaam : ''}
                    </div>
                    <div class="mb-3">
                        <i class="bi bi-sunrise"></i> &nbsp; 
                            ${res.persoon.geborendatum !== null ? res.persoon.geborendatum : ''}
                            ${res.persoon.geborenplaats !== null ? res.persoon.geborenplaats : ''}
                    </div>
                    <div class="mb-3">
                        <i class="bi bi-sunset"></i> &nbsp; 
                            ${res.persoon.gestorvendatum !== null ? res.persoon.gestorvendatum : ''}
                            ${res.persoon.gestorvenplaats !== null ? res.persoon.gestorvenplaats : ''}
                    </div>

                </div>

            `;

            if (res.persoon.info.trim().length > 0) {

                inhoudLinks += `
                    <h5>${INHOUDZOEK.boodschappen.persooninfo}</h5>
                    <div class="mb-3 inhoudzoekPersoonInfo alert alert-dark">
                        ${res.persoon.info}
                    </div>
                `
            }

            let documenten = '';
            if (res.documenten.length > 0) {
                res.documenten.forEach(document => {
                    let inhoud = '';
                    if (document.bestand.endsWith('.pdf')) {
                        inhoud = `<embed id="persoonDocumentDocument" class="document" src="/${document.bestand}" type="application/pdf">`;
                    }
                    else {
                        inhoud = `<img id="persoonDocumentDocument" class="foto" src="/${document.bestand}"> `;
                    }

                    documenten += `
                        <a class="fancy-box documentCel" href="/${document.bestand}" data-fancybox="document" data-caption="<h4>${document.titel}</h4><div>${document.info}</div>">
                        <div>
                            ${inhoud}
                        </div>
                        <strong>${document.titel}</strong></a>
                    `;
                });

                inhoudLinks += `
                    <h5>${INHOUDZOEK.boodschappen.persoondocument}</h5>
                    <div class="mb-3 alert alert-dark">
                        ${documenten}
                    </div>
                `;
            }

            // kolom rechts
            let ouders = '';
            if (res.ouders.length > 0) {
                ouders += `<h5>${INHOUDZOEK.boodschappen.ouders}</h5>`;
                res.ouders.forEach(ouder => {
                    ouders += `
                        <div class="mb-1 alert alert-dark">
                            <i class="bi bi-arrow-up-right float-end inhoudZoekForward" id="ouderID_${ouder.id}"></i>
                            <i class="bi bi-gender-${ouder.sex == 'M' ? 'male' : 'female'}"></i> &nbsp;<strong> ${ouder.naam} ${ouder.voornamen}</strong><br>
                            <i class="bi bi-sunrise"></i> &nbsp;${ouder.geborendatum !== null ? ouder.geborendatum : ''} ${ouder.geborenplaats !== null ? ouder.geborenplaats : ''}<br>
                            <i class="bi bi-sunset"></i> &nbsp;${ouder.gestorvendatum !== null ? ouder.gestorvendatum : ''} ${ouder.gestorvenplaats !== null ? ouder.gestorvenplaats : ''}
                        </div>
                    `
                })
            }

            let kinderen = '';
            if (res.kinderen.length > 0) {
                kinderen += `<h5>${INHOUDZOEK.boodschappen.kinderen}</h5>`;
                res.kinderen.forEach(kind => {
                    kinderen += `
                        <div class="mb-1 alert alert-dark">
                            <i class="bi bi-arrow-up-right float-end inhoudZoekForward" id="kindID_${kind.id}"></i>
                            <i class="bi bi-gender-${kind.sex == 'M' ? 'male' : 'female'}"></i> &nbsp;<strong> ${kind.naam} ${kind.voornamen}</strong><br>
                            <i class="bi bi-sunrise"></i> &nbsp;${kind.geborendatum !== null ? kind.geborendatum : ''} ${kind.geborenplaats !== null ? kind.geborenplaats : ''}<br>
                            <i class="bi bi-sunset"></i> &nbsp;${kind.gestorvendatum !== null ? kind.gestorvendatum : ''} ${kind.gestorvenplaats !== null ? kind.gestorvenplaats : ''}
                        </div>
                    `
                })
            }

            let inhoudRechts = `
                ${ouders.length > 0 ? '<div class="mb-3">' + ouders + '</div' : ''}
                ${kinderen.length > 0 ? '<div class="mb-3">' + kinderen + '</div' : ''}
            `;

            MODAAL.inhoud(`
                <div id="inhoudContainerDocument" class="row">
                    <div class="col-8">
                        ${inhoudLinks}
                    </div>
                    <div class="col-4">    
                        ${inhoudRechts}
                    </div>    
                </div>
            `);
            // --- inhoud-einde
            MODAAL.voet(MODAAL.knop('verberg', 'secondary', 'x-square', INHOUDZOEK.boodschappen.sluit));
            MODAAL.grootte('modal-xl');
            MODAAL.toon();

        }
    },

    verberg: () => {
        MODAAL.verberg();
    }


}