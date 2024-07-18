$(() => {

    if ($('#inhoudPersoon').length) INHOUDPERSOON.init();
});

const INHOUDPERSOON = {
    csrfToken: $('meta[name="csrf-token"]').attr('content'),

    boodschappenFamilieVerwijder: [],
    boodschappenFamilieNieuw: [],
    boodschappenOuderVerwijder: [],
    boodschappenOuderNieuw: [],
    boodschappenKindVerwijder: [],
    boodschappenKindNieuw: [],
    boodschappenPlaatsNieuw: [],
    boodschappenGeboren: [],
    boodschappenGestorven: [],
    boodschappenBewaar: [],
    boodschappenDocumenten: [],

    crucifix: '',
    deleteIcon: '',
    linkIcon: '',

    init: () => {
        // ophalen boodschappen
        INHOUDPERSOON.getBoodschappen();

        // ophalen crusifix
        INHOUDPERSOON.getAppSettings();

        /* FAMILIES */
        // familie verwijder -> info
        $('#inhoudPersoon').on('click', '.inhoudPersoonFamilieInfo', function (evt) {
            INHOUDPERSOON.familieInfo($(this).parent().parent().prop('id').split('_')[1]);
        });

        $('body').on('keyup', '#familieNieuwNaam', function (evt) { INHOUDPERSOON.familieFilter(evt.target.value); });


        // familie verwijder -> verwijder
        $('body').on('click', '#inhoudPersoonFamilieVerwijder', () => { INHOUDPERSOON.familieVerwijder(); })

        // familie nieuw
        $('#inhoudPersoonFamilieNieuw').on('click', () => { INHOUDPERSOON.familieNieuw(); });

        $('body').on('click', '#familieNieuwLijst li', function (evt) {
            $('#familieNieuwNaam').val($(this).text());
            $('#familieNieuwID').val($(this).prop('id').split('_')[1]);
            INHOUDPERSOON.familieNieuwBewaar();

        });

        $('body').on('click', '#inhoudPersoonNieuw', () => {
            INHOUDPERSOON.familieNieuwBewaar();
        })

        // verberg modaal venster
        $('body').on('click', '#verberg', () => { INHOUDPERSOON.verberg(); });

        /** --- OUDERS --- */
        // verxwijder ouder
        $('#inhoudPersoon').on('click', '.inhoudPersoonOuderVerwijder', function (evt) {
            INHOUDPERSOON.ouderInfo($(this).parent().parent().prop('id').split('_')[1]);
        });
        $('body').on('click', '#inhoudPersoonOuderVerwijder', () => {
            INHOUDPERSOON.ouderVerwijder();
        });
        // ouder nieuw
        $('#inhoudPersoon').on('click', '#inhoudPersoonOuderNieuw', () => { INHOUDPERSOON.ouderNieuw(); });
        // ouder filter
        $('body').on('keyup', '#ouderNieuwNaam', function (evt) { INHOUDPERSOON.ouderFilter(evt.target.value); });
        $('body').on('click', '#ouderNieuwNaamClear', function (evt) { INHOUDPERSOON.ouderNieuwNaamClear, $('#ouderNieuwNaam').val(''); INHOUDPERSOON.ouderFilter('') });

        // lijst ouder filteren + clear inputfield
        // $('body').on('input', '#ouderNieuwNaam', () => INHOUDPERSOON.filterOuderNaamNieuw());
        // $('body').on('click', '#ouderNieuwNaamClear', () => INHOUDPERSOON.ouderNieuwNaamClear());

        // selecteer ouder > bewaar
        $('body').on('click', '#ouderNieuwLijst li', function (evt) { INHOUDPERSOON.ouderBewaar($(this).prop('id').split('_')[1]) });


        /** --- KINDEREN --- */
        // verwijder kind
        $('#inhoudPersoon').on('click', '.inhoudPersoonKindVerwijder', function (evt) {
            INHOUDPERSOON.kindInfo($(this).parent().parent().prop('id').split('_')[1]);
        });
        $('body').on('click', '#inhoudPersoonKindVerwijder', () => {
            INHOUDPERSOON.kindVerwijder();
        });
        // kind nieuw
        $('#inhoudPersoon').on('click', '#inhoudPersoonKindNieuw', () => { INHOUDPERSOON.kindNieuw(); });

        // lijst kinderen filteren + clear inputfield
        $('body').on('keyup', '#kindNieuwNaam', function (evt) { INHOUDPERSOON.kindFilter(evt.target.value); });
        $('body').on('click', '#kindNieuwNaamClear', function (evt) {
            $('#kindNieuwNaam').val('');
            INHOUDPERSOON.kindFilter('');
        });

        // kind selecteer > bewaar
        $('body').on('click', '#kindNieuwLijst li', function (evt) {
            INHOUDPERSOON.kindBewaar($(this).attr('id').split('_')[1]);
        });

        //BEWERKEN EERSTELIJNSVERWANTEN
        $('body').on('click', '.persoonBewerk', function (evt) { INHOUDPERSOON.persoonBewerk($(this).attr('id').split('_')[1]); });

        // verwijder persoon
        $('#inhoudPersoonVerwijder').on('click', () => { INHOUDPERSOON.persoonVerwijderBoodschap(); });
        $('body').on('click', '#inhoudPersoonVerwijderOK', () => { INHOUDPERSOON.persoonVerwijder(); });
        $('body').on('click', '#inhoudZoek', () => { window.location = '/inhoudzoek'; });


        //bewaar persoon
        $('#inhoudPersoonBewaar').on('click', () => { INHOUDPERSOON.persoonBewaar(); })
        $('body').on('click', '#inhoudPersoonVernieuw', () => { INHOUDPERSOON.persoonVernieuw(); })

        //indien roepnaam leeg vul de voornaam in
        $('#inhoudPersoonVoornamen').on('blur', () => {
            let voornamen = $('#inhoudPersoonVoornamen').val().trim();
            let roepnaam = $('#inhoudPersoonRoepnaam').val().trim();

            if (roepnaam.length == 0) {
                // alert('roepnaam wordt' + ': ' + voornamen.split(' ')[0]);
                $('#inhoudPersoonRoepnaam').val(voornamen.split(' ')[0]);
            }

        });

        // dupliceer persoon (masterdata)
        // familienaam, familie1ID, familie2ID, ouder1ID, ouder2ID, gebrenplaatsID 

        $('#inhoudPersoonDupliceer').on('click', () => { INHOUDPERSOON.persoonDupliceer(); });

        // geboorteplaats en sterfteplaats
        $('#inhoudPersoonGeborenplaatsKnop').on('click', () => { INHOUDPERSOON.geborenPlaats(); });
        $('#inhoudPersoonGestorvenplaatsKnop').on('click', () => { INHOUDPERSOON.gestorvenPlaats(); });

        $('body').on('click', "#plaatsLijst li", function (evt) {
            $('#dlgPlaatsID').val($(this).prop('id').split('_')[1]);
            $('#dlgPlaats').val($(this).text().trim());

            if ($('#dlgMode').val() == 'geboren') INHOUDPERSOON.geborenPlaatsBewaar();
            else INHOUDPERSOON.gestorvenPlaatsBewaar();
        });

        $('body').on('click', '#inhoudPersoonGeborenplaatsOK, #inhoudPersoonGestorvenplaatsOK', () => {
            if ($('#dlgMode').val() == 'geboren') INHOUDPERSOON.geborenPlaatsBewaar()
            else INHOUDPERSOON.gestorvenPlaatsBewaar();
        });

        $('body').on('keyup', '#dlgPlaats', function (evt) {
            INHOUDPERSOON.plaatsFilter(evt.target.value);
            $('#dlgPlaatsID').val('');
        });

        $('#inhoudPersoonGeborenplaatsLedigen').on('click', () => { INHOUDPERSOON.geborenPlaatsLedigen(); });
        $('#inhoudPersoonGestorvenplaatsLedigen').on('click', () => { INHOUDPERSOON.gestorvenPlaatsLedigen(); });

        /* --- DOCUMENTEN --- */
        // upload bestand
        $('#inhoudPersoonDocumentNieuw').on('dragenter', function (evt) {
            evt.stopPropagation();
            evt.preventDefault();
            if (!$(this).hasClass('upload')) $(this).addClass('over');
        });

        $('#inhoudPersoonDocumentNieuw').on('dragover', function (evt) {
            evt.stopPropagation();
            evt.preventDefault();
        });

        $('#inhoudPersoonDocumentNieuw').on('dragleave', function (evt) {
            evt.stopPropagation();
            evt.preventDefault();
            $(this).removeClass('over');
        });

        $('#inhoudPersoonDocumentNieuw').on('drop', function (evt) {
            evt.preventDefault();
            if (!$(this).hasClass('upload')) {
                INHOUDPERSOON.persoondocumentUpload(Array.from(evt.originalEvent.dataTransfer.files)[0], $(this));
            }
        });



        // bewerk/verwijder document
        $('#inhoudPersoonDocumentLijst').on('click', '.inhoudPersoonDocumentBewerk', function (evt) {
            INHOUDPERSOON.persoonDocumentBewerk($(this).parent().parent().prop('id').split('_')[1]);
        })

        $('body').on('click', '#inhoudPersoonDocumentOK', () => {
            INHOUDPERSOON.persoonDocumentMeta();
        });

        $('body').on('click', '#inhoudPersoonDocumentVerwijder', () => {
            INHOUDPERSOON.persoonDocumentVerwijder();
        });

        /* --- family TREE ---*/
        $('#inhoudPersoonTree').on('click', () => {
            INHOUDPERSOON.persoonTree();
        });

        $('body').on('change', '.treeFamilie', function (evt) {
            let familieID = $('.treeFamilie:checked').val();
            let persoonID = $('#mermaidPersoonID').val();
            INHOUDPERSOON.persoonTree(persoonID, familieID);
        });

        $('body').on('click', 'g.node', function (evt) {
            let el = evt.target;
            do {
                el = el.parentElement;
            }
            while (el.tagName != 'g');
            el = el.parentElement;

            let persoonID = el.id.split('-')[2];
            let familieID = $('.treeFamilie.checked').val();
            INHOUDPERSOON.persoonTree(persoonID, familieID);
        });



        // begin eigen creatie   

        //indien roepnaam leeg vul de voornaam in
        $('#inhoudPersoonVoornamen').on('blur', () => {
            let voornamen = $('#inhoudPersoonVoornamen').val().trim();
            let roepnaam = $('#inhoudPersoonRoepnaam').val().trim();
            if (roepnaam.length == 0) {
                // alert('roepnaam wordt' + ': ' + voornamen.split(' ')[0]);
                $('#inhoudPersoonRoepnaam').val(voornamen.split(' ')[0]);
            }

        });

        //gemeente en land peroon
        $('#inhoudPersoonGeborenplaatsKnop').on('click', () => { INHOUDPERSOON.plaatsenLijst('geboren') });
        $('#inhoudPersoonGestorvenplaatsKnop').on('click', () => { INHOUDPERSOON.plaatsenLijst('gestorven') });

        // lijst plaatsen filteren + clear inputfield
        // $('body').on('keyup', '#plaatsNieuwGemeente', function (evt) { INHOUDPERSOON.plaatsFilter(evt.target.value); });
        // $('body').on('click', '#plaatsNieuwGemeenteClear', function (evt) {
        //     $('#plaatsNieuwGemeente').val('');
        //     INHOUDPERSOON.plaatsFilter('');
        // });
        // plaats nieuw
        $('#inhoudPersoonPlaatsNieuw').on('click', () => { INHOUDPERSOON.plaatsNieuw(); });


        // plaats selecteer > bewaar
        $('body').on('click', '#plaatsNieuwLijst li', function (evt) {
            INHOUDPERSOON.plaatsBewaar($('#plaatsVeld').val(), $(this).attr('id').split('_')[1], $(this).text());
        });

        $('body').on('click', '#inhoudPersoonPlaasNieuw', () => {
            INHOUDPERSOON.plaatsBewaar($('#plaatsVeld').val(), 0, $('#plaatsNieuwGemeente').val());
        });

        // einde eigen creatie    




    },

    getBoodschappen: () => {
        fetch('/jxInhoudPersoonBoodschappen', {
            method: 'post',
            body: new FormData(),

            headers: {
                "X-CSRF-Token": INHOUDPERSOON.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then((response) => {
            return response.json()
        }).then((res) => {
            if (res.succes) {
                // familieverwijder
                res.familieverwijder.split(',').forEach(boodschap => {
                    let tmp = boodschap.split(':');
                    INHOUDPERSOON.boodschappenFamilieVerwijder[tmp[0]] = tmp[1];
                });

                // familieNieuw
                res.familienieuw.split(',').forEach(boodschap => {
                    let tmp = boodschap.split(':');
                    INHOUDPERSOON.boodschappenFamilieNieuw[tmp[0]] = tmp[1];
                });

                // ouderverwijder
                res.ouderverwijder.split(',').forEach(boodschap => {
                    let tmp = boodschap.split(':');
                    INHOUDPERSOON.boodschappenOuderVerwijder[tmp[0]] = tmp[1];
                });

                // ouderNieuw
                res.oudernieuw.split(',').forEach(boodschap => {
                    let tmp = boodschap.split(':');
                    INHOUDPERSOON.boodschappenOuderNieuw[tmp[0]] = tmp[1];
                });
                // kindverwijder
                res.kindverwijder.split(',').forEach(boodschap => {
                    let tmp = boodschap.split(':');
                    INHOUDPERSOON.boodschappenKindVerwijder[tmp[0]] = tmp[1];
                });

                // kindNieuw
                res.kindnieuw.split(',').forEach(boodschap => {
                    let tmp = boodschap.split(':');
                    INHOUDPERSOON.boodschappenKindNieuw[tmp[0]] = tmp[1];
                });

                // plaatsNieuw
                res.plaatsnieuw.split(',').forEach(boodschap => {
                    let tmp = boodschap.split(':');
                    INHOUDPERSOON.boodschappenPlaatsNieuw[tmp[0]] = tmp[1];
                });
                // geboren
                res.geboren.split(',').forEach(boodschap => {
                    let tmp = boodschap.split(':');
                    INHOUDPERSOON.boodschappenGeboren[tmp[0]] = tmp[1];
                });
                // gestorven
                res.gestorven.split(',').forEach(boodschap => {
                    let tmp = boodschap.split(':');
                    INHOUDPERSOON.boodschappenGestorven[tmp[0]] = tmp[1];
                });
                // bewaar
                res.bewaar.split(',').forEach(boodschap => {
                    let tmp = boodschap.split(':');
                    INHOUDPERSOON.boodschappenBewaar[tmp[0]] = tmp[1];

                });
                // documenten
                res.documenten.split(',').forEach(boodschap => {
                    let tmp = boodschap.split(':');
                    INHOUDPERSOON.boodschappenDocumenten[tmp[0]] = tmp[1];
                });
            }
        }).catch((err) => {
            console.log(err);
        });
    },


    /* VERWIJDER GEKOPPELDE FAMILIES */

    familieInfo: (familieID) => {
        let frmDta = { 'familieID': familieID };

        fetch('/jxInhoudPersoonFamilieInfo', {
            method: 'post',
            body: JSON.stringify(frmDta),

            headers: {
                'X-CSRF-Token': INHOUDPERSOON.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then(response => {
            return response.json();
        }).then(res => {
            INHOUDPERSOON.familieInfoSucces(res);
        }).catch(err => {
            console.log(err);
        })
    },

    familieInfoSucces: (res) => {
        if (res.succes) {
            MODAAL.kop(INHOUDPERSOON.boodschappenFamilieVerwijder.titel);
            let inhoudContainer = `
                <p>${INHOUDPERSOON.boodschappenFamilieVerwijder.boodschap}</p>
                <p>${INHOUDPERSOON.boodschappenFamilieVerwijder.familie}: <strong>${res.familie.naam}</strong></p>
                <input type="hidden" id="familieVerwijderID" value="${res.familie.id}">
            `;
            MODAAL.inhoud(inhoudContainer);
            let voet = '';
            voet += MODAAL.knop('inhoudPersoonFamilieVerwijder', 'danger', INHOUDPERSOON.linkIcon.split('-')[1], INHOUDPERSOON.boodschappenFamilieVerwijder.btnOk);
            voet += MODAAL.knop('verberg', 'secondary', 'x-square', INHOUDPERSOON.boodschappenFamilieVerwijder.btnCa);
            MODAAL.voet(voet);
            MODAAL.toon();
        }
    },

    familieFilter: (filter) => {
        if (filter.length === 0) {
            $('#familieNieuwLijst li').show();
        } else {
            $('#familieNieuwLijst li').each((ndx, li) => {
                //zoek op naam, plaats, datum
                let tekst = li.innerHTML.replace(/<[^>]+>/g, '').replace(/\s+/g, '').replace('\n', '');
                //zoek enkel op naam/voornaam/roepnaam
                // let tekst = li.innerHTML.split('<br>')[0].replace(/<[^>]+>/g, '').replace(/\s+/g, '').replace('\n', '');
                if (tekst.toUpperCase().indexOf(filter.toUpperCase()) === -1) {
                    $(li).hide();
                }

                else {
                    $(li).show();
                }

            });
        }
    },


    familieVerwijder: () => {
        let frmDta = {
            'familieID': $('#familieVerwijderID').val(),
            'persoonID': $('#inhoudPersoonID').val(),
        };
        fetch('/jxInhoudPersoonFamilieVerwijder', {
            method: 'post',
            body: JSON.stringify(frmDta),

            headers: {
                'X-CSRF-Token': INHOUDPERSOON.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then(response => {
            return response.json();
        }).then(res => {
            if (res.succes) {
                if ($('#inhoudPersoonID').val() == res.persoonID) {
                    $(`#familieID_${res.familieID}`).remove();
                    $('#inhoudPersoonFamilieNieuw').show();

                }
            }
            MODAAL.verberg();
        }).catch(err => {
            console.log(err);
        })

    },

    /* --- FAMIIE KOPPELEN AAN PERSOON ---*/
    familieNieuw: () => {
        fetch('/jxInhoudPersoonFamilies', {
            method: 'post',
            body: JSON.stringify({}),

            headers: {
                'X-CSRF-Token': INHOUDPERSOON.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then(response => {
            return response.json();
        }).then(res => {
            INHOUDPERSOON.familieNieuwSucces(res);
        }).catch(err => {
            console.log(err);
        })


    },

    familieNieuwSucces: (res) => {
        if (res.succes) {
            MODAAL.kop(INHOUDPERSOON.boodschappenFamilieNieuw.titel)
            let inhoudContainer = $('<div>').addClass('mb-2')
                .append(
                    $('<label>').addClass('form-label').text(INHOUDPERSOON.boodschappenFamilieNieuw.familie)
                )
                .append(
                    $('<input>').addClass('form-control').prop('type', 'text').prop('id', 'familieNieuwNaam')
                );
            let lijst = $('<ul>').addClass('list-group').prop('id', 'familieNieuwLijst');
            res.families.forEach(familie => {
                // lijst.append($('<li>').addClass('list-group-item').data('id',familie.id).text(fammilie.naam));
                lijst.append($('<li>').addClass('list-group-item').prop('id', `fID_${familie.id}`).text(familie.naam));
            });
            inhoudContainer.append(lijst);
            inhoudContainer.append($('<input>').prop('type', 'hidden').prop('id', 'familieNieuwID').prop('value', "0"));
            MODAAL.inhoud(inhoudContainer);
            let voet = '';
            voet += MODAAL.knop('inhoudPersoonNieuw', 'success', INHOUDPERSOON.linkIcon.split('-')[1], INHOUDPERSOON.boodschappenFamilieNieuw.btnOk);
            voet += MODAAL.knop('verberg', 'primary', 'x-square', INHOUDPERSOON.boodschappenFamilieNieuw.btnCa);
            MODAAL.voet(voet);
            MODAAL.toon();
            $('#modDlg').on('shown.bs.modal', function () {
                $('#familieNieuwNaam').focus();
            });
        }
    },

    familieNieuwBewaar: () => {
        let frmDta = {
            'familienaam': $('#familieNieuwNaam').val(),
            'familieID': $('#familieNieuwID').val(),
            'persoonID': $('#inhoudPersoonID').val()
        }
        fetch('/jxInhoudPersoonFamilieBewaar', {
            method: 'post',
            body: JSON.stringify(frmDta),

            headers: {
                'X-CSRF-Token': INHOUDPERSOON.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then(response => {
            return response.json();
        }).then(res => {
            INHOUDPERSOON.familieNieuwBewaarSucces(res);
        }).catch(err => {
            console.log(err);
        })


    },



    familieNieuwBewaarSucces: (res) => {
        if (res.succes) {
            let familie = `<div class="card-body mb-1 inhoudPersoonFamilie" id="familieID_${res.familieID}">
                                <div class="meta clearfix">
                                    <strong>
                                        ${res.familienaam}
                                    </strong>
                                    <button class="btn btn-danger inhoudPersoonFamilieInfo float-end" type="button">
                                        <i class="bi ${INHOUDPERSOON.linkIcon}"></i>

                                    </button>
                                </div>
                            </div>`;
            $('#inhoudPersoonFamilie h5').after(familie);

            if ($('.inhoudPersoonFamilie').length == 2) {
                $('#inhoudPersoonFamilieNieuw').hide();
            }
            else {
                $('#inhoudPersoonFamilieNieuw').show();
            }

            //familie koppelen aan de persoon

            let frmDta = {
                'persoonID': $('#inhoudPersoonID').val(),
                'familieID': res.familieID
            }

            fetch('/jxInhoudPersoonFamilieLink', {
                method: 'post',
                body: JSON.stringify(frmDta),

                headers: {
                    'X-CSRF-Token': INHOUDPERSOON.csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            }).then(response => {
                return response.json();
            }).then(res => {
                console.log(res);
            }).catch(err => {
                console.log(err);
            })


        }





        MODAAL.verberg()
    },


    /** --- OUDERS --- */
    //VERWIJDER OUDER
    ouderInfo: (ouderID) => {
        let frmDta = {
            'ouderID': ouderID
        }
        fetch('/jxInhoudPersoonOuderInfo', {
            method: 'post',
            body: JSON.stringify(frmDta),

            headers: {
                'X-CSRF-Token': INHOUDPERSOON.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then(response => {
            return response.json();
        }).then(res => {
            INHOUDPERSOON.ouderInfoSucces(res);
        }).catch(err => {
            console.log(err);
        });


    },

    ouderInfoSucces: (res) => {
        if (res.succes) {
            MODAAL.kop(INHOUDPERSOON.boodschappenOuderVerwijder.titel);

            let inhoudContainer = `
                <p>${INHOUDPERSOON.boodschappenOuderVerwijder.boodschap}</p>
                <p>${INHOUDPERSOON.boodschappenOuderVerwijder.ouder}
                    <strong>${res.ouder.naam} ${res.ouder.voornamen}</strong><br>
                    ${res.ouder.roepnaam ? res.ouder.roepnaam : ''}<br>
                    ${res.ouder.geborendatum ? res.ouder.geborendatum : ''} ${res.ouder.geborenplaats ? res.ouder.geborenplaats : ''}<br>
                    ${res.ouder.gestorvendatum ? res.ouder.gestorvendatum : ''} ${res.ouder.gestorvenplaats ? res.ouder.gestorvenplaats : ''}<br>
                </p>
                <input type="hidden" id="ouderVerwijderID" value="${res.ouder.id}">
            `;
            MODAAL.inhoud(inhoudContainer);
            let voet = '';
            voet += MODAAL.knop('inhoudPersoonOuderVerwijder', 'danger', INHOUDPERSOON.linkIcon.split('-')[1], INHOUDPERSOON.boodschappenOuderVerwijder.btnOk);
            voet += MODAAL.knop('verberg', 'secondary', 'x-square', INHOUDPERSOON.boodschappenOuderVerwijder.btnCa);
            MODAAL.voet(voet);

            MODAAL.toon();
        }

    },

    ouderVerwijder: () => {
        let frmDta = {
            'ouderID': $('#ouderVerwijderID').val(),
            'persoonID': $('#inhoudPersoonID').val()
        }
        fetch('/jxInhoudPersoonOuderVerwijder', {
            method: 'post',
            body: JSON.stringify(frmDta),

            headers: {
                'X-CSRF-Token': INHOUDPERSOON.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then(response => {
            return response.json();
        }).then(res => {
            if (res.succes) {
                if ($('#inhoudPersoonID').val() == res.persoonID) {
                    $(`#ouderID_${res.ouderID}`).remove();
                    $('#inhoudPersoonOuderNieuw').show();
                }
            }
            MODAAL.verberg();



        }).catch(err => {
            console.log(err);
        });

    },
    // OUDERNIEUW
    ouderNieuw: () => {
        let frmDta = {
            'persoonID': $('#inhoudPersoonID').val()
        }
        fetch('/jxInhoudPersoonOuderLijst', {
            method: 'post',
            body: JSON.stringify(frmDta),

            headers: {
                'X-CSRF-Token': INHOUDPERSOON.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then(response => {
            return response.json();
        }).then(res => {
            INHOUDPERSOON.ouderNieuwLijst(res);
        }).catch(err => {
            console.log(err);
        });
    },

    ouderNieuwLijst: (res) => {
        if (res.succes) {
            MODAAL.kop(INHOUDPERSOON.boodschappenOuderNieuw.titel);
            let inhoudContainer = $('<div>');
            inhoudContainer.append($('<div>').addClass('mb-2')
                .append(
                    $('<label>').addClass('form-label').text(INHOUDPERSOON.boodschappenOuderNieuw.filter)

                )
                .append($('<div>').addClass('input-group')
                    .append($('<input>').addClass('form-control').prop('type', 'text').prop('id', 'ouderNieuwNaam'))
                    .append($('<button>').addClass('btn btn-primary').prop('id', 'ouderNieuwNaamClear')
                        .append($('<i>').addClass('bi bi-x-square'))
                    )
                )
            );
            let lijst = $('<ul>').addClass('list-group').prop('id', 'ouderNieuwLijst');
            res.ouders.forEach(ouder => {
                let roepnaam = ouder.roepnaam ? '(' + ouder.roepnaam + ')' : '';
                let persoonInfo = '';
                if (ouder.leeftijd == 'Leeftijd onbekend')
                    persoonInfo = '(' + INHOUDPERSOON.boodschappenOuderNieuw.leeftijdonbekend + ')';
                else {
                    if (ouder.gestorvendatum !== null) {
                        persoonInfo = '(<img class="crucifix" src="' + INHOUDPERSOON.crucifix + '"/>' + ouder.leeftijd + ')';
                    }
                    else {
                        persoonInfo = '(' + ouder.leeftijd + ')';
                    }
                }

                let geborenresult = `${(ouder.geborenop === null || ouder.geborenop === '') ? '(??) ' : ouder.geborendatum} ${ouder.geborenplaats ? ouder.geborenplaats : ''}<br>`;
                let gestorvenresult = `${(ouder.gestorvenop === null || ouder.gestorvenop === '') ? '(??) ' : ouder.gestorvendatum} ${ouder.gestorvenplaats ? ouder.gestorvenplaats : ''}<br>`;
                
                let html = `
                    <strong>${ouder.naam} ${ouder.voornamen}</strong> <em>${roepnaam}</em> ${persoonInfo}<br>
                    ${geborenresult} 
                    ${gestorvenresult}
                `;
                lijst.append($('<li>').addClass('list-group-item').prop('id', `oID_${ouder.id}`).html(html));
            });
            inhoudContainer.append(lijst).append($('<input>').prop('type', 'hidden').prop('id', 'ouderNieuwId').prop('value', '0'))
            MODAAL.inhoud(inhoudContainer);
            let voet = '';
            voet += MODAAL.knop('verberg', 'secondary', 'x-square', INHOUDPERSOON.boodschappenOuderNieuw.btnCa);
            MODAAL.voet(voet);
            MODAAL.toon();

            $('#modDlg').on('shown.bs.modal', function () {
                $('#ouderNieuwNaam').focus();
            });


        }

    },

    ouderNieuwNaamClear: () => {
        $('#ouderNieuwNaam').val('');
        $("li").each(function () { $(this).show(); })

    },


    ouderFilter: (filter) => {
        if (filter.length === 0) {
            $('#ouderNieuwLijst li').show();
        }
        else {
            $('#ouderNieuwLijst li').each((ndx, li) => {
                let tekst = li.innerHTML.split('<br')[0].replace(/<[^>]+>/g, '').replace(/\s+/g, ' ').replace('\n', '');
                if (tekst.toUpperCase().indexOf(filter.toUpperCase()) === -1) {
                    $(li).hide();
                }
                else {
                    $(li).show();
                }
            });
        }
    },

    ouderBewaar: (ouderID) => {
        let frmDta = {
            'persoonID': $('#inhoudPersoonID').val(),
            'ouderID': ouderID
        }
        fetch('/jxInhoudPersoonOuderBewaar', {
            method: 'post',
            body: JSON.stringify(frmDta),

            headers: {
                'X-CSRF-Token': INHOUDPERSOON.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then(response => {
            return response.json();
        }).then(res => {
            INHOUDPERSOON.ouderBewaarSucces(res)
        }).catch(err => {
            console.log(err);
        });
    },

    ouderBewaarSucces: (res) => {
        if (res.succes) {
            $('.inhoudPersoonOuder').remove();
            res.ouders.forEach(ouder => {
                let persoonInfo = '';
                if (ouder.leeftijd == 'Leeftijd onbekend')
                    persoonInfo = '(<img class="crucifix" src="' + INHOUDPERSOON.crucifix + '"/> ??)';
                else {
                    if (ouder.gestorvendatum !== null) {
                        persoonInfo = '(<img class="crucifix" src="' + INHOUDPERSOON.crucifix + '"/>' + ouder.leeftijd + ')';
                    }
                    else {
                        persoonInfo = '(' + ouder.leeftijd + ')';
                    }
                }

                let geslachtsClass = ouder.sex === "M" ? "mannelijk" : "vrouwelijk";
                let containerOuder = $('<div>').prop('id', `ouderID_${ouder.id}`).addClass('card-body mb-1 inhoudPersoonOuder')
                    .append(
                        $('<div>').addClass(`meta clearfix ${geslachtsClass}`).html(`
                        <strong>${ouder.naam} ${ouder.voornamen} ${persoonInfo}<br></strong>
                        <button type="button" class="btn btn-success float-end persoonBewerk mt-1 me-1" id="oID_${ouder.id}">
                            <i class="bi bi-info-square"></i>
                        </button>
                        <button type="button" class="btn btn-danger float-end mt-1 me-1 inhoudPersoonOuderVerwijder">
                            <i class="bi ${INHOUDPERSOON.linkIcon}"></i>
                        </button><br>
                        <em>${ouder.roepnaam ? ouder.roepnaam : ''}</em><br>
                        ${ouder.geborendatum ? ouder.geborendatum : ''} ${ouder.geborenplaats ? ouder.geborenplaats : ''}<br>
                        ${ouder.gestorvendatum ? ouder.gestorvendatum : ''} ${ouder.gestorvenplaats ? ouder.gestorvenplaats : ''}
                     `)
                    );
                $('#inhoudPersoonOuders h5').after(containerOuder);
            });
            if (res.ouders.length === 2) $('#inhoudPersoonOuderNieuw').hide();
            else $('#inhoudPersoonOuderNieuw').show();
        }

        MODAAL.verberg();


    },

    filterOuderNaamNieuw: () => {
        let val2use = $('#ouderNieuwNaam').val().toLowerCase();
        let regex = new RegExp(val2use, 'i'); // 'i' flag for case-insensitive matching
        if (val2use.length >= 2) {
            $("li").each(function () {
                if ($(this).text().match(regex)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
        else {
            $("li").each(function () { $(this).show(); })
        }
    },
    /** --- KINDEREN --- */
    //VERWIJDER KIND
    kindInfo: (kindID) => {
        let frmDta = {
            'kindID': kindID
        }
        fetch('/jxInhoudPersoonKindInfo', {
            method: 'post',
            body: JSON.stringify(frmDta),

            headers: {
                'X-CSRF-Token': INHOUDPERSOON.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then(response => {
            return response.json();
        }).then(res => {
            INHOUDPERSOON.kindInfoSucces(res);
        }).catch(err => {
            console.log(err);
        });


    },

    kindInfoSucces: (res) => {
        if (res.succes) {
            MODAAL.kop(INHOUDPERSOON.boodschappenKindVerwijder.titel);

            let inhoudContainer = `
                <p>${INHOUDPERSOON.boodschappenKindVerwijder.boodschap}</p>
                <p>${INHOUDPERSOON.boodschappenKindVerwijder.kind}
                    <strong>${res.kind.naam} ${res.kind.voornamen}</strong><br>
                    ${res.kind.roepnaam ? res.kind.roepnaam : ''}<br>
                    ${res.kind.geborendatum ? res.kind.geborendatum : ''} ${res.kind.geborenplaats ? res.kind.geborenplaats : ''}<br>
                    ${res.kind.gestorvendatum ? res.kind.gestorvendatum : ''} ${res.kind.gestorvenplaats ? res.kind.gestorvenplaats : ''}<br>
                </p>
                <input type="hidden" id="kindVerwijderID" value="${res.kind.id}">
            `;
            MODAAL.inhoud(inhoudContainer);
            let voet = '';
            voet += MODAAL.knop('inhoudPersoonKindVerwijder', 'danger', INHOUDPERSOON.linkIcon.split('-')[1], INHOUDPERSOON.boodschappenKindVerwijder.btnOk);
            voet += MODAAL.knop('verberg', 'secondary', 'x-square', INHOUDPERSOON.boodschappenKindVerwijder.btnCa);
            MODAAL.voet(voet);

            MODAAL.toon();
        }

    },

    kindVerwijder: () => {
        let frmDta = {
            'kindID': $('#kindVerwijderID').val(),
            'persoonID': $('#inhoudPersoonID').val()
        }
        fetch('/jxInhoudPersoonKindVerwijder', {
            method: 'post',
            body: JSON.stringify(frmDta),

            headers: {
                'X-CSRF-Token': INHOUDPERSOON.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then(response => {
            return response.json();
        }).then(res => {
            if (res.succes) {
                if ($('#inhoudPersoonID').val() == res.persoonID) {
                    $(`#kindID_${res.kindID}`).remove();
                    $('#inhoudPersoonKindNieuw').show();
                }
            }
            MODAAL.verberg();

            var count = ($('#lijstKinderen').find('div').length / 2) - 1;
            let titel = $('#titel-kinderen').text().split('(')[0].trim() + ' (' + count + ')';
            $('#titel-kinderen').text(titel);


        }).catch(err => {
            console.log(err);
        });

    },
    // KINDNIEUW
    kindNieuw: () => {
        let frmDta = {
            'persoonID': $('#inhoudPersoonID').val()
        }
        fetch('/jxInhoudPersoonKindLijst', {
            method: 'post',
            body: JSON.stringify(frmDta),

            headers: {
                'X-CSRF-Token': INHOUDPERSOON.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then(response => {
            return response.json();
        }).then(res => {
            INHOUDPERSOON.kindNieuwLijst(res);
        }).catch(err => {
            console.log(err);
        });
    },

    kindNieuwLijst: (res) => {
        if (res.succes) {
            MODAAL.kop(INHOUDPERSOON.boodschappenKindNieuw.titel);
            let inhoudContainer = $('<div>');
            inhoudContainer.append($('<div>').addClass('mb-2')
                .append(
                    $('<label>').addClass('form-label').text(INHOUDPERSOON.boodschappenKindNieuw.filter)

                )
                .append($('<div>').addClass('input-group')
                    .append($('<input>').addClass('form-control').prop('type', 'text').prop('id', 'kindNieuwNaam'))
                    .append($('<button>').addClass('btn btn-primary').prop('id', 'kindNieuwNaamClear')
                        .append($('<i>').addClass('bi bi-x-square'))
                    )
                )
            );
            let lijst = $('<ul>').addClass('list-group').prop('id', 'kindNieuwLijst');

            res.kinderen.forEach(kind => {
                let roepnaam = kind.roepnaam ? '(' + kind.roepnaam + ')' : '';
                let persoonInfo = '';
                if (kind.leeftijd == 'Leeftijd onbekend') {
                    persoonInfo = '(<img class="crucifix" src="' + INHOUDPERSOON.crucifix + '"/> ??)';
                }
                else {
                    if (kind.gestorvendatum !== null) {
                        persoonInfo = '(<img class="crucifix" src="' + INHOUDPERSOON.crucifix + '"/>' + kind.leeftijd + ')';
                    }

                    else {
                        persoonInfo = '(' + kind.leeftijd + ')';
                    }
                }
                let html = `
                <strong>${kind.naam} ${kind.voornamen}</strong> <em>${roepnaam}</em> ${persoonInfo}<br>
                ${kind.geborendatum ? kind.geborendatum : ''} ${kind.geborenplaats ? kind.geborenplaats : ''}<br>
                ${kind.gestorvendatum ? kind.gestorvendatum : ''} ${kind.gestorvenplaats ? kind.gestorvenplaats : ''}<br>
            `;
                lijst.append($('<li>').addClass('list-group-item').prop('id', `oID_${kind.id}`).html(html));
            });

            inhoudContainer.append(lijst).append($('<input>').prop('type', 'hidden').prop('id', 'kindNieuwId').prop('value', '0'))
            MODAAL.inhoud(inhoudContainer);
            let voet = '';
            voet += MODAAL.knop('verberg', 'secondary', 'x-square', INHOUDPERSOON.boodschappenKindNieuw.btnCa);
            MODAAL.voet(voet);
            MODAAL.toon();

            $('#modDlg').on('shown.bs.modal', function () {
                $('#kindNieuwNaam').focus();
            });
        }

    },
    kindFilter: (filter) => {
        if (filter.length === 0) {
            $('#kindNieuwLijst li').show();
        } else {
            $('#kindNieuwLijst li').each((ndx, li) => {
                //zoek op naam, plaats, datum
                let tekst = li.innerHTML.replace(/<[^>]+>/g, '').replace(/\s+/g, '').replace('\n', '');
                //zoek enkel op naam/voornaam/roepnaam
                // let tekst = li.innerHTML.split('<br>')[0].replace(/<[^>]+>/g, '').replace(/\s+/g, '').replace('\n', '');
                if (tekst.toUpperCase().indexOf(filter.toUpperCase()) === -1) {
                    $(li).hide();
                }

                else {
                    $(li).show();
                }

            });
        }
    },

    kindBewaar: (kindID) => {

        let frmDta = {
            'persoonID': $('#inhoudPersoonID').val(),
            'kindID': kindID
        }

        fetch('/jxInhoudPersoonKindBewaar', {
            method: 'post',
            body: JSON.stringify(frmDta),

            headers: {
                'X-CSRF-Token': INHOUDPERSOON.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then(response => {
            return response.json();
        }).then(res => {
            INHOUDPERSOON.kindBewaarSucces(res);
        }).catch(err => {
            console.log(err);
        });
    },

    kindBewaarSucces: (res) => {
        if (res.succes) {
            $('.inhoudPersoonKind').remove();
            res.kinderen.forEach(kind => {
                let persoonInfo = '';
                if (kind.leeftijd == 'Leeftijd onbekend')
                    persoonInfo = '(<img class="crucifix" src="' + INHOUDPERSOON.crucifix + '"/> ??)';
                else {
                    if (kind.gestorvendatum !== null) {
                        persoonInfo = '(<img class="crucifix" src="' + INHOUDPERSOON.crucifix + '"/>' + kind.leeftijd + ')';
                    }
                    else {
                        persoonInfo = '(' + kind.leeftijd + ')';
                    }
                }
                let geslachtsClass = kind.sex === "M" ? "mannelijk" : "vrouwelijk";

                let containerKind = $('<div>').prop('id', `kindID_${kind.id}`)
                    .addClass('card-body mb-1 inhoudPersoonKind')
                    .append($('<div>').addClass(`meta clearfix ${geslachtsClass}`).html(`
                                                <strong>${kind.naam} ${kind.voornamen} ${persoonInfo}</strong>
                                                <button type="button" class="btn btn-success float-end persoonBewerk mt-1 me-1" id="oID_${kind.id}">
                                                    <i class="bi bi-info-square"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger float-end mt-1 me-1 inhoudPersoonKindVerwijder">
                                                     <i class="bi ${INHOUDPERSOON.linkIcon}"></i>
                                                 </button>
                                                 <br>


                                                 <em>${kind.roepnaam ? kind.roepnaam : ''}</em><br>
                                                 ${kind.geborendatum ? kind.geborendatum : ''} ${kind.geborenplaats ? kind.geborenplaats : ''}<br>
                                                 ${kind.gestorvendatum ? kind.gestorvendatum : ''} ${kind.gestorvenplaats ? kind.gestorvenplaats : ''}
                                                 `));

                $('#inhoudPersoonKinderen h5').after(containerKind);

            });
        }

        MODAAL.verberg();
        var count = ($('#lijstKinderen').find('div').length / 2) - 1;
        let titel = $('#titel-kinderen').text().split('(')[0].trim() + ' (' + count + ')';
        $('#titel-kinderen').text(titel);



    },

    // bewaar persoon
    persoonBewaar: () => {
        let frmDta = {};
        frmDta.persoonID = $('#inhoudPersoonID').val();
        frmDta.naam = $('#inhoudPersoonNaam').val().trim();
        frmDta.voornamen = $('#inhoudPersoonVoornamen').val().trim();
        frmDta.roepnaam = $('#inhoudPersoonRoepnaam').val().trim();
        frmDta.sex = $('.inhoudPersoonSex:checked').val();
        frmDta.geadopteerd = $('#inhoudPersoonGeadopteerd').val();
        frmDta.geborenop = $('#inhoudPersoonGeborenop').val();
        frmDta.geborenplaatsID = $('#inhoudPersoonGeborenplaatsID').val();
        frmDta.gestorvenop = $('#inhoudPersoonGestorvenop').val();
        frmDta.gestorvenplaatsID = $('#inhoudPersoonGestorvenplaatsID').val();
        frmDta.info = rte.root.innerHTML.replace('\'', '`');
        frmDta.families = [];
        if (INHOUDPERSOON.persoonBewaarValideer(frmDta)) INHOUDPERSOON.persoonBewaarBewaar(frmDta);


    },

    persoonBewaarValideer: (frmDta) => {

        let boodschappen = INHOUDPERSOON.boodschappenBewaar;
        let boodschap = [];
        if (frmDta.naam.length == 0) boodschap.push(`<li>${boodschappen.naam}</li>`);
        if (frmDta.voornamen.length == 0) boodschap.push(`<li>${boodschappen.voornamen}</li>`);
        if (frmDta.roepnaam.length == 0) boodschap.push(`<li>${boodschappen.roepnaam}</li>`);

        if (boodschap.length == 0) {
            return true;
        }
        else {
            MODAAL.grootte('');
            MODAAL.kop(boodschappen.titel);
            MODAAL.inhoud(`
                <div class="alert alert-warning">
                    <h5>${boodschappen.boodschap}</h5>
                    <ul>
                        ${boodschap.join('')}
                    </ul>
                <div>    
            `);
            MODAAL.voet(MODAAL.knop('verberg', 'secondary', 'x-square', boodschappen.btnCa));
            MODAAL.toon();
            return false;
        }
    },

    persoonBewaarBewaar: (frmDta) => {
        fetch('/jxInhoudPersoonBewaar', {
            method: 'post',
            body: JSON.stringify({ 'frmDta': frmDta }),

            headers: {
                'X-CSRF-Token': INHOUDPERSOON.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then(response => {
            return response.json();
        }).then(res => {
            INHOUDPERSOON.persoonBewaarBewaarSucces(res);
        }).catch(err => {
            console.log(err);
        })

    },

    persoonBewaarBewaarSucces: (res) => {
        let klasse = res.status == 1 ? 'success' : 'warning';
        let boodschappen = [];
        res.boodschap.split(',').forEach(boodschap => {
            let tmp = boodschap.split(':');
            boodschappen[tmp[0]] = tmp[1];
        });

        MODAAL.grootte('');
        MODAAL.kop(boodschappen.titel);
        MODAAL.inhoud(`
            <div class="alert alert-${klasse}">
                ${boodschappen.boodschap}
            </div>
        `);
        MODAAL.voet(MODAAL.knop('inhoudPersoonVernieuw', 'primary', 'hand-thumbs-up-fill', boodschappen.btnOk));
        MODAAL.toon();
    },

    getAppSettings: () => {
        let frmDta = {}
        fetch('/jxGetAppSettings', {
            method: 'post',
            body: JSON.stringify(frmDta),

            headers: {
                'X-CSRF-Token': INHOUDPERSOON.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then(response => {
            return response.json();
        }).then(res => {
            INHOUDPERSOON.crucifix = '../' + res.crucifix;
            INHOUDPERSOON.deleteIcon = res.deleteicon;
            INHOUDPERSOON.linkIcon = res.linkicon;
        }).catch(err => {
            console.log(err);
        });

    },

    persoonVernieuw: (id=0) => {
        let persoonID = id == 0 ? $('#inhoudPersoonID').val() : id;
        window.location = `/inhoudpersoon/${persoonID}`;
    },

    // verwijder persoon
    persoonVerwijderBoodschap: () => {
        let frmDta = {};
        fetch('/jxInhoudPersoonVerwijderBoodschap', {
            method: 'post',
            body: JSON.stringify(frmDta),

            headers: {
                'X-CSRF-Token': INHOUDPERSOON.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then(response => {
            return response.json();
        }).then(res => {
            INHOUDPERSOON.persoonVerwijderboodschapUpdate(res);
        }).catch(err => {
            console.log(err);
        })

    },

    persoonVerwijderboodschapUpdate: (res) => {
        let boodschappen = [];
        res.boodschap.split(',').forEach(boodschap => {
            let tmp = boodschap.split(':');
            boodschappen[tmp[0]] = tmp[1];
        });

        MODAAL.grootte('');
        MODAAL.kop(boodschappen.titel);
        MODAAL.inhoud(boodschappen.boodschap);
        let voet = '';
        voet += MODAAL.knop('inhoudPersoonVerwijderOK', 'primary', 'scissors', boodschappen.btnOk);
        voet += MODAAL.knop('verberg', 'secondary', 'x-square', boodschappen.btnCa);
        MODAAL.voet(voet);
        MODAAL.toon();
    },

    persoonVerwijder: () => {
        let frmDta = { 'persoonID': $('#inhoudPersoonID').val() };

        fetch('/jxInhoudPersoonVerwijder', {
            method: 'post',
            body: JSON.stringify(frmDta),

            headers: {
                'X-CSRF-Token': INHOUDPERSOON.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then(response => {
            return response.json();
        }).then(res => {
            INHOUDPERSOON.persoonVerwijderSucces(res);
        }).catch(err => {
            console.log(err);
        })

    },

    persoonVerwijderSucces: (res) => {
        if (res.succes) {
            let klasse = res.status == 1 ? 'success' : 'warning';
            let boodschappen = [];
            res.boodschap.split(',').forEach(boodschap => {
                let tmp = boodschap.split(':');
                boodschappen[tmp[0]] = tmp[1];
            });

            MODAAL.grootte('');
            MODAAL.kop(boodschappen.titel);
            MODAAL.inhoud(`
                <div clas="alert alert-${klasse}"> 
                    ${boodschappen.boodschap}
                </div>    
            `);
            let voet = '';
            voet += MODAAL.knop('inhoudZoek', 'primary', 'hand-thumbs-up-fill', boodschappen.btnOk);
            MODAAL.voet(voet);
            MODAAL.toon();
        }
    },

    plaatsenLijst: (veld) => {
        return;
        let frmDta = { 'veld': veld };
        fetch('/jxPlaatsenLijst', {
            method: 'post',
            body: JSON.stringify(frmDta),

            headers: {
                'X-CSRF-Token': INHOUDPERSOON.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then(response => {
            return response.json();
        }).then(res => {
            INHOUDPERSOON.plaatsenLijstSucces(res);
        }).catch(err => {
            console.log(err);
        })
    },

    plaatsenLijstSucces: (res) => {
        if (res.succes) {
            MODAAL.kop(INHOUDPERSOON.boodschappenPlaatsNieuw.titel);
            let inhoudContainer = $('<div>');
            inhoudContainer.append($('<div>').addClass('mb-2')

                .append(
                    $('<label>').addClass('form-label').text(INHOUDPERSOON.boodschappenPlaatsNieuw.filter)

                )
                .append($('<div>').addClass('input-group')
                    .append($('<input>').addClass('form-control').prop('type', 'text').prop('id', 'plaatsNieuwGemeente'))
                    .append($('<button>').addClass('btn btn-primary').prop('id', 'plaatsNieuwGemeenteClear')
                        .append($('<i>').addClass('bi bi-x-square'))
                    )
                )
            );
            let lijst = $('<ul>').addClass('list-group').prop('id', 'plaatsNieuwLijst');

            res.plaatsen.forEach(plaats => {
                let gemeente = plaats.gemeente + ', ' + '<strong>' + plaats.land + '</strong>';

                let html = `<strong>${gemeente}</strong>`;
                lijst.append($('<li>').addClass('list-group-item').prop('id', `oID_${plaats.id}`).html(html));
            });

            inhoudContainer.append(lijst).append($('<input>').prop('type', 'hidden').prop('id', 'plaatsNieuwId').prop('value', '0'))
                .append(lijst).append($('<input>').prop('type', 'hidden').prop('id', 'plaatsVeld').prop('value', res.veld));
            MODAAL.inhoud(inhoudContainer);
            let voet = '';
            voet += MODAAL.knop('inhoudPersoonPlaasNieuw', 'success', INHOUDPERSOON.linkIcon.split('-')[1], INHOUDPERSOON.boodschappenPlaatsNieuw.btnOk);
            voet += MODAAL.knop('verberg', 'secondary', 'x-square', INHOUDPERSOON.boodschappenPlaatsNieuw.btnCa);
            MODAAL.voet(voet);
            MODAAL.toon();
        }

    },

    plaatsFilter: (filter) => {
        if (filter.length === 0) {
            $('#plaatsLijst li').show();
        } else {
            $('#plaatsLijst li').each((ndx, li) => {
                let tekst = li.innerHTML.replace(/<[^>]+>/g, '').replace(/\s+>/g, '').replace('\n', '');
                if (tekst.toUpperCase().indexOf(filter.toUpperCase()) === -1) {
                    $(li).hide();
                }
                else {
                    $(li).show();
                }
            });
        }
    },

    plaatsNieuw: () => {

    },

    plaatsBewaar: (plaatsveld, id, plaats) => {
        let gemeente = plaats.split(',')[0];
        let land = plaats.split(',')[1];
        switch (plaatsveld) {
            case 'geboren':

                $('#inhoudPersoonGeborenplaats').val(plaats);

                break;
            case 'gestorven':
                $('#inhoudPersoonGestorvenplaats').val(plaats);
                break;
        }
        let frmDta = {
            'persoonID': $('#inhoudPersoonID').val(),
            'plaatsveld': plaatsveld,
            'id': id,
            'gemeente': gemeente,
            'land': land
        };

        fetch('/jxInhoudPersoonPlaatsBewaar', {
            method: 'post',
            body: JSON.stringify(frmDta),

            headers: {
                'X-CSRF-Token': INHOUDPERSOON.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then(response => {
            return response.json();
        }).then(res => {

            if (res.succes) {
                if (plaatsveld == 'geboren') $('#inhoudPersoonGeborenplaatsID').val(res.id);
                else $('#inhoudPersoonGestorvenplaatsID').val(res.id);

                MODAAL.verberg();
            }
        }).catch(err => {
            console.log(err);
        })



        // $('#familieNieuwID').val($(this).prop('id').split('_')[1]);
    },

    // persoon bewerk
    persoonBewerk: (persoonID) => {
        location.href = `/inhoudpersoon/${persoonID}`;
    },


    /* --- PERSOON GEBORENPLAATS ---*/
    geborenPlaats: () => {
        let frmDta = {};

        fetch('/jxInhoudPersoonPlaatsen', {
            method: 'post',
            body: JSON.stringify(frmDta),

            headers: {
                'X-CSRF-Token': INHOUDPERSOON.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then(response => {
            return response.json();
        }).then(res => {
            INHOUDPERSOON.geborenPlaatsSucces(res);
        }).catch(err => {
            console.log(err);
        })
    },

    geborenPlaatsSucces: (res) => {
        let geborenplaatsID = $('#inhoudPersoonGeborenPlaatsID').val();
        let geborenplaats = $('#inhoudPersoonGeborenplaats').val();

        if (res.succes) {
            MODAAL.kop(INHOUDPERSOON.boodschappenGeboren.titel);
            let inhoudContainer = `
                <input type="hidden" id="dlgMode" value="geboren">
                <input type="hidden" id="dlgPlaatsID" value="${geborenplaatsID}">
                <div class="alert alert-warning" style="visibility: hidden;" id="dlgBoodschap"></div>
                <div>
                    <input type="text" class="form-control" id="dlgPlaats" value="${geborenplaats}">
                </div>
                <div>
                    <ul class="list-group" id="plaatsLijst">
                

            `;
            res.plaatsen.forEach(plaats => {
                if (plaats.plaats.split(',')[0].trim()) {
                    inhoudContainer += `
                    <li class="list-group-item plaatsLijstItem" id="plaatsLijstID_${plaats.id}">
                        ${plaats.plaats}
                    </li>
                `;
                }
            });

            inhoudContainer += `
                    </ul>
                </div>      
            `;

            MODAAL.inhoud(inhoudContainer);

            let voet = '';
            voet += MODAAL.knop('inhoudPersoonGeborenplaatsOK', 'primary', 'check-square', INHOUDPERSOON.boodschappenGeboren.btnOk);
            voet += MODAAL.knop('verberg', 'secondary', 'x-square', INHOUDPERSOON.boodschappenGeboren.btnCa);
            MODAAL.voet(voet);
            MODAAL.grootte('modal-lg');
            MODAAL.toon();

            $('#modDlg').on('shown.bs.modal', function () {
                $('#dlgPlaats').focus();
            });


        }

    },

    geborenPlaatsBewaar: () => {
        let geborenPlaatsID = $('#dlgPlaatsID').val();
        let geborenPlaats = $('#dlgPlaats').val().trim();
        if (geborenPlaatsID) {
            $('#inhoudPersoonGeborenplaatsID').val(geborenPlaatsID);
            $('#inhoudPersoonGeborenplaats').val(geborenPlaats);

            MODAAL.verberg(); // Assuming this is defined elsewhere
        }

        else {

            let geborenplaats = geborenPlaats.split(',');
            if (geborenplaats.length == 2 && geborenplaats[0].trim().length > 0 && geborenplaats[1].trim().length > 0) {
                $('#dlgBoodschap').text('').css('visibility', 'hidden');
                geborenPlaats = geborenPlaats.split(',');
                let frmDta = {
                    'gemeente': geborenPlaats[0].trim(),
                    'land': geborenPlaats[1].trim()
                }
                fetch('/jxInhoudPersoonPlaats', {
                    method: 'post',
                    body: JSON.stringify(frmDta),

                    headers: {
                        'X-CSRF-Token': INHOUDPERSOON.csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                }).then(response => {
                    return response.json();
                }).then(res => {
                    INHOUDPERSOON.geborenPlaatsBewaarSucces(res);
                }).catch(err => {
                    console.log(err);
                })

            }
            else {
                $('#dlgBoodschap').text(INHOUDPERSOON.boodschappenGeboren.fout).css('visibility', 'visible');
            }
        }
    },

    geborenPlaatsBewaarSucces: (res) => {

        if (res.succes) {

            $('#inhoudPersoonGeborenplaatsID').val(res.plaats[0].id);
            $('#inhoudPersoonGeborenplaats').val(res.plaats[0].plaats);
            MODAAL.verberg();
        }
    },

    geborenPlaatsLedigen: () => {
        let persoonID = $('#inhoudPersoonID').val();
        let frmDta = {
            'persoonID': persoonID
        }   
        fetch('/jxGeborenPlaatsLedigen', {
            method: 'post',
            body: JSON.stringify(frmDta),

            headers: {
                'X-CSRF-Token': INHOUDPERSOON.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then(response => {
            return response.json();
        }).then(res => {
            if (res.succes) {
                $('#inhoudPersoonGeborenplaats').val('');
            }
        }).catch(err => {
            console.log(err);
        })

    },
    gestorvenPlaatsLedigen: () => {
        let persoonID = $('#inhoudPersoonID').val();
        let frmDta = {
            'persoonID': persoonID
        }   
        fetch('/jxGestorvenPlaatsLedigen', {
            method: 'post',
            body: JSON.stringify(frmDta),

            headers: {
                'X-CSRF-Token': INHOUDPERSOON.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then(response => {
            return response.json();
        }).then(res => {
            if (res.succes) {
                $('#inhoudPersoonGestorvenplaats').val('');
            }
        }).catch(err => {
            console.log(err);
        })

    },




    /* --- PERSOON GESTORVENPLAATS ---*/
    gestorvenPlaats: () => {
        let frmDta = {};

        fetch('/jxInhoudPersoonPlaatsen', {
            method: 'post',
            body: JSON.stringify(frmDta),

            headers: {
                'X-CSRF-Token': INHOUDPERSOON.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then(response => {
            return response.json();
        }).then(res => {
            INHOUDPERSOON.gestorvenPlaatsSucces(res);
        }).catch(err => {
            console.log(err);
        })
    },

    gestorvenPlaatsSucces: (res) => {
        let gestorvenplaatsID = $('#inhoudPersoonGestorvenPlaatsID').val();
        let gestorvenplaats = $('#inhoudPersoonGestorvenplaats').val();

        if (res.succes) {
            MODAAL.kop(INHOUDPERSOON.boodschappenGestorven.titel);
            let inhoudContainer = `
                    <input type="hidden" id="dlgMode" value="gestorven">
                    <input type="hidden" id="dlgPlaatsID" value="${gestorvenplaatsID}">
                    <div class="alert alert-warning" style="visibility: hidden;" id="dlgBoodschap"></div>
                    <div>
                        <input type="text" class="form-control" id="dlgPlaats" value="${gestorvenplaats}">
                    </div>
                    <div>
                        <ul class="list-group" id="plaatsLijst">
                    
    
                `;
            res.plaatsen.forEach(plaats => {
                if (plaats.plaats.split(',')[0].trim()) {
                    inhoudContainer += `
                        <li class="list-group-item plaatsLijstItem" id="plaatsLijstID_${plaats.id}">
                            ${plaats.plaats}
                        </li>
                    `;
                }
            });

            inhoudContainer += `
                        </ul>
                    </div>      
                `;

            MODAAL.inhoud(inhoudContainer);

            let voet = '';
            voet += MODAAL.knop('inhoudPersoonGestorvenplaatsOK', 'primary', 'check-square', INHOUDPERSOON.boodschappenGestorven.btnOk);
            voet += MODAAL.knop('verberg', 'secondary', 'x-square', INHOUDPERSOON.boodschappenGestorven.btnCa);
            MODAAL.voet(voet);
            MODAAL.grootte('modal-lg');
            MODAAL.toon();
            
            $('#modDlg').on('shown.bs.modal', function () {
                $('#dlgPlaats').focus();
            });

        }

    },

    gestorvenPlaatsBewaar: () => {

        let gestorvenPlaatsID = $('#dlgPlaatsID').val();
        let gestorvenPlaats = $('#dlgPlaats').val().trim();
        if (gestorvenPlaatsID) {
            $('#inhoudPersoonGestorvenplaatsID').val(gestorvenPlaatsID);
            $('#inhoudPersoonGestorvenplaats').val(gestorvenPlaats);

            MODAAL.verberg();
        }

        else {

            let gestorvenplaats = gestorvenPlaats.split(',');
            if (gestorvenplaats.length == 2 && gestorvenplaats[0].trim().length > 0 && gestorvenplaats[1].trim().length > 0) {
                $('#dlgBoodschap').text('').css('visibility', 'hidden');
                gestorvenPlaats = gestorvenPlaats.split(',');
                let frmDta = {
                    'gemeente': gestorvenPlaats[0].trim(),
                    'land': gestorvenPlaats[1].trim()
                }
                fetch('/jxInhoudPersoonPlaats', {
                    method: 'post',
                    body: JSON.stringify(frmDta),

                    headers: {
                        'X-CSRF-Token': INHOUDPERSOON.csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                }).then(response => {
                    return response.json();
                }).then(res => {
                    INHOUDPERSOON.gestorvenPlaatsBewaarSucces(res);
                }).catch(err => {
                    console.log(err);
                })

            }
            else {
                $('#dlgBoodschap').text(INHOUDPERSOON.boodschappenGestorven.fout).css('visibility', 'visible');
            }
        }
    },

    gestorvenPlaatsBewaarSucces: (res) => {

        if (res.succes) {

            $('#inhoudPersoonGestorvenplaatsID').val(res.plaats[0].id);
            $('#inhoudPersoonGestorvenplaats').val(res.plaats[0].plaats);
            MODAAL.verberg();
        }
    },

    /* --- DOCUMENTEN --- */
    persoondocumentUpload: (bestand, obj) => {
        $(obj).removeClass('over').addClass('upload');
        $(obj).empty().append($('<div>').addClass('spinner-border'));

        let frmDta = new FormData();
        frmDta.append('persoonID', $('#inhoudPersoonID').val());
        frmDta.append('bestand', bestand);

        fetch('/jxInhoudPersoonDocumentUpload', {
            method: 'post',
            body: frmDta,

            headers: {
                'X-CSRF-Token': INHOUDPERSOON.csrfToken,
            }
        }).then(response => {
            return response.json();
        }).then(res => {
            INHOUDPERSOON.persoonDocumentUploadSucces(res);
        }).catch(err => {
            console.log(err);
        });

    },


    persoonDocumentUploadSucces: (res) => {
        $('#inhoudPersoonDocumentNieuw').removeClass('upload').empty().append($('<i>').addClass('bi bi-cloud-upload'));

        let boodschap = INHOUDPERSOON.boodschappenDocumenten;

        let inhoudContainer = '';

        if (res.succes) {
            MODAAL.kop(INHOUDPERSOON.boodschappenDocumenten.titel);
            let inhoud = '';
            if (res.pad.endsWith('.pdf')) {
                inhoud = `<embed id="persoonDocumentDocument" class="document" src="${res.pad}" type="application/pdf">`;
            }
            else {
                inhoud = `<img id="persoonDocumentDocument" class="foto" src="${res.pad}">`;
            }

            inhoudContainer = `
                <input type="hidden" id="persoonDocumentID" value="${res.documentID}">
                <div class="container">
                    <div class="row">
                        <div class="col-6">
                            ${inhoud}
                        </div>
                        <div class="col-6">
                            <div class="">
                                <label for="persoonDocumentTitel" class="form-label">${INHOUDPERSOON.boodschappenDocumenten._titel}</label>
                                <input type="text" class="form-control" value="${res.titel}" id="persoonDocumentTitel">
                            </div>
                            <div class="">
                                <label for="persoonDocumentInfo" class="form-label">${INHOUDPERSOON.boodschappenDocumenten.info}</label>
                                <input type="text" class="form-control" value="${res.info}" id="persoonDocumentInfo">
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        else {
            inhoudContainer = `
                ${res.boodschappen}
            `;
        }
        MODAAL.inhoud(inhoudContainer);

        let voet = '';
        if (res.succes) {
            voet += MODAAL.knop('inhoudPersoonDocumentOK', 'primary', 'check-square', boodschap.btnOk);
            voet += MODAAL.knop('inhoudPersoonDocumentVerwijder', 'warning', 'dash-square', boodschap.btnVerwijder);
        }
        voet += MODAAL.knop('verberg', 'secondary', 'x-square', boodschap.btnCa);
        MODAAL.voet(voet);

        MODAAL.grootte('modal-xl');
        MODAAL.toon();
    },

    persoonDocumentBewerk: (documentID) => {
        let frmDta = { 'documentID': documentID };

        fetch('/jxInhoudPersoonDocument', {
            method: 'post',
            body: JSON.stringify(frmDta),

            headers: {
                'X-CSRF-Token': INHOUDPERSOON.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then(response => {
            return response.json();
        }).then(res => {
            INHOUDPERSOON.persoonDocumentUploadSucces(res);
        }).catch(err => {
            console.log(err);
        })

    },

    persoonDocumentMeta: () => {
        let frmDta = {
            'persoonID': $('#inhoudPersoonID').val(),
            'documentID': $('#persoonDocumentID').val(),
            'documentTitel': $('#persoonDocumentTitel').val(),
            'documentInfo': $('#persoonDocumentInfo').val()
        }
        fetch('/jxInhoudPersoonDocumentMeta', {
            method: 'post',
            body: JSON.stringify(frmDta),

            headers: {
                'X-CSRF-Token': INHOUDPERSOON.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then(response => {
            return response.json();
        }).then(res => {
            INHOUDPERSOON.persoonDocumentUploadMetaSucces(res);
        }).catch(err => {
            console.log(err);
        })
    },

    persoonDocumentUploadMetaSucces: (res) => {
        let lijst = $('#inhoudPersoonDocumentLijst');
        lijst.empty();
        let lijstHTML = '';

        if (res.succes) {
            res.documenten.forEach(document => {
                lijstHTML += `
                    <li id="documentID_${document.id}" class="list-group-item inhoudPersoonDocument">
                        <div class="clearfix">
                            <a class="fancybox" href="/${document.bestand}" data-fancybox="document" data-caption="<h4>${document.titel}</h4><div>${document.info}</div>">
                                <strong>${document.titel}</strong>
                            </a>
                            <button type="button" class="btn btn-light inhoudPersoonDocumentBewerk float-end">
                                <i class="bi bi-pencil-square"></i>
                            </button>        
                        </div>
                    </li>    

                `;
            });
            lijst.html(lijstHTML);

        }
        MODAAL.verberg();

    },

    persoonDocumentVerwijder: () => {
        let frmDta = {
            'persoonID': $('#inhoudPersoonID').val(),
            'documentID': $('#persoonDocumentID').val()
        };

        fetch('/jxInhoudPersoonDocumentVerwijder', {
            method: 'post',
            body: JSON.stringify(frmDta),

            headers: {
                'X-CSRF-Token': INHOUDPERSOON.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then(response => {
            return response.json();
        }).then(res => {
            INHOUDPERSOON.persoonDocumentUploadMetaSucces(res);
        }).catch(err => {
            console.log(err);
        })
    },

    /* --- FAMILY TREE ---*/
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
                'X-CSRF-Token': INHOUDPERSOON.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then(response => {
            return response.json();
        }).then(res => {
            INHOUDPERSOON.persoonTreeSucces(res);
        }).catch(err => {
            console.log(err);
        })

    },

    persoonTreeSucces: (res) => {
        if (res.succes) {
            let kop = `<input type="hidden" id="mermaidPersoonID" value="${res.persoonID}">
            `;

            res.families.forEach(familie => {
                let checked = familie.id == res.familieID ? ' checked' : '';
                kop += `<div class="form-check form-check-inline">
                            <input type="radio" class="form-check-input treeFamilie" name="treeFamilie" ${checked} value="${familie.id}">
                            <label class="form-check-label" for="">
                                ${familie.naam}
                            </label>
                        </div>

                                    

            `;


            });

            MODAAL.kop(kop)

            MODAAL.voet(MODAAL.knop('verberg', 'secondary', 'x-square', 'verberg'));
            MODAAL.grootte('modal-xl');
            MODAAL.toon();

            mermaid.render('mermaid', res.tree).then(({ svg, bindFunctions }) => {
                document.getElementById('modDlgInhoud').innerHTML = svg;
                bindFunctions?.(element);
            });
        }
    },

    persoonDupliceer: () => {
        let frmDta = {
            'persoonID': $('#inhoudPersoonID').val()
        }

        fetch('/jxInhoudPersoonDupliceer', {
            method: 'post',
            body: JSON.stringify(frmDta),

            headers: {
                'X-CSRF-Token': INHOUDPERSOON.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then(response => {
            return response.json();
        }).then(res => {
            console.log(res);
            INHOUDPERSOON.persoonVernieuw(res.persoonID);
        }).catch(err => {
            console.log(err);
        })



        // MODAAL.voet(MODAAL.knop('verberg', 'secondary', 'x-square', 'verberg'));
        // MODAAL.grootte('modal-xl');
        // MODAAL.toon();
    },

    verberg: () => {
        MODAAL.verberg();
    }
}