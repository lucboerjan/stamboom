$(() => {
    if ($('#inhoudPersoon').length) INHOUDPERSOON.init();
});

const INHOUDPERSOON = {
    csrfToken: $('meta[name="csrf-token"]').attr('content'),

    boodschappenFamilieVerwijder: [],
    boodschappenFamilieNieuw: [],
    boodschappenOuderVerwijder: [],
    boodschappenOuderNieuw: [],

    init: () => {        
        // ophalen boodschappen
        INHOUDPERSOON.getBoodschappen();

        // ophalen boodschappen
        // TODO vakantietaak Roger

        /* FAMILIES */
        // familie verwijder -> info
        $('#inhoudPersoon').on('click', '.inhoudPersoonFamilieInfo', function(evt) {
            INHOUDPERSOON.familieInfo($(this).parent().parent().prop('id').split('_')[1]);
        });

        // familie verwijder -> verwijder
        $('body').on('click', '#inhoudPersoonFamilieVerwijder', () => { INHOUDPERSOON.familieVerwijder(); })

        // familie nieuw
        $('#inhoudPersoonFamilieNieuw').on('click', () => { INHOUDPERSOON.familieNieuw(); });
        $('body').on('click', '#familieNieuwLijst li', function(evt) {
            $('#familieNieuwNaam').val($(this).text());
            $('#familieNieuwID').val($(this).prop('id').split('_')[1]);
            INHOUDPERSOON.familieNieuwBewaar();
        });
        $('body').on('click', '#inhoudPersoonNieuw', () => {
            INHOUDPERSOON.familieNieuwBewaar();
        });

        // verberg modaal venster
        $('body').on('click', '#verberg', () => { INHOUDPERSOON.verberg(); });

        /* --- OUDERS --- */
        // verwijder ouder
        $('#inhoudPersoon').on('click', '.inhoudPersoonOuderVerwijder', function(evt) {
            INHOUDPERSOON.ouderInfo($(this).parent().parent().prop('id').split('_')[1]);
        });
        $('body').on('click', '#inhoudPersoonOuderVerwijder', () => {
            INHOUDPERSOON.ouderVerwijder();
        });
    // ouder nieuw
        $('#inhoudPersoon').on('click', '#inhoudPersoonOuderNieuw', () => {INHOUDPERSOON.ouderNieuw();
        });
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
            }
        }).catch((error) => {
            console.log(error);
        });
    },


    /* VERWIJDER GEKOPPELDE FAMILIES */

    familieInfo: (familieID) => {
        let frmDta = { 'familieID': familieID};

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
            voet += MODAAL.knop('inhoudPersoonFamilieVerwijder', 'primary', 'scissors', INHOUDPERSOON.boodschappenFamilieVerwijder.btnOk);
            voet += MODAAL.knop('verberg', 'secondary', 'x-square', INHOUDPERSOON.boodschappenFamilieVerwijder.btnCa);
            MODAAL.voet(voet);
            MODAAL.toon();
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
                if ($('#inhoudPersoonID').val() ==  res.persoonID) {
                    $(`#familieID_${res.familieID}`).remove();
                    $('#inhoudPersoonFamilieNieuw').show();
                }                
            }
            MODAAL.verberg();
        }).catch(err => {
            console.log(err);
        }); 
    },

    /* --- FAMILIE KOPPELEN AAN PERSOON --- */
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
        });
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
            voet += MODAAL.knop('inhoudPersoonNieuw', 'primary', 'check-square', INHOUDPERSOON.boodschappenFamilieNieuw.btnOk);
            voet += MODAAL.knop('verberg', 'secondary', 'x-square', INHOUDPERSOON.boodschappenFamilieNieuw.btnCa);
            MODAAL.voet(voet);
            MODAAL.toon();
        }
    },

    familieNieuwBewaar: () => {
        let frmDta = {
            'familienaam': $('#familieNieuwNaam').val(),
            'familieID': $('#familieNieuwID').val()
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
        });
    },

    familieNieuwBewaarSucces: (res) => {
        if (res.succes) {
            let familie = `<div class="card-body mb-1 inhoudPersoonFamilie" id="familieID_${res.familieID}">
                                <div class="meta clearfix">
                                    <strong>
                                        ${res.familienaam}
                                    </strong>
                                    <button class="btn btn-light inhoudPersoonFamilieInfo float-end" type="button">
                                        <i class="bi bi-x-square"></i>
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
        }
        MODAAL.verberg()
    },

    /* --- OUDERS --- */
    // VERWIJDER OUDER
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
                <p>${INHOUDPERSOON.boodschappenOuderVerwijder.ouder}<br>
                    <strong>${res.ouder.naam} ${res.ouder.voornamen}</strong><br>
                    ${res.ouder.roepnaam}<br>
                    ${res.ouder.geborendatum?res.ouder.geborendatum:''} ${res.ouder.geborenplaats?res.ouder.geborenplaats:''}<br>
                    ${res.ouder.gestorvendatum?res.ouder.gestorvendatum:''} ${res.ouder.gestorvenplaats?res.ouder.gestorvenplaats:''}                    
                </p>
                <input type="hidden" id="ouderVerwijderID" value="${res.ouder.id}">
            `;
            MODAAL.inhoud(inhoudContainer);
            let voet = '';
            voet += MODAAL.knop('inhoudPersoonOuderVerwijder', 'primary', 'check-square', INHOUDPERSOON.boodschappenOuderVerwijder.btnOk);
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
                if (res.succes){
                    if($('#inhoudPersoonID').val() == res.persoonID) {
                       $(`#ouderID_${res.ouderID}`).remove();
                       $('#inhoudPersoonOuderNieuw').show();
                    }
                }
            MODAAL.verberg();
       
        }).catch(err => {
            console.log(err);
        });
    },

    //OUDERNIEUW
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
        if (res.succes){
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
            res.ouders.forEach(ouder =>{
                let html = `
                    <strong>${ouder.naam} ${ouder.voornamen}</strong> (<em>${ouder.roepnaam}</em>)<br>
                    ${ouder.geborenop?ouder.geborenop: ''}  ${ouder.geborenplaats?ouder.geborenplaats: ''}<br>
                    ${ouder.gestorvenop?ouder.gestorvenop: ''}  ${ouder.gestorvenplaats?ouder.gestorvenplaats: ''}<br>
                    `;
                    lijst.append($('<li>').addClass('list-group-item').prop('id', `oID_${ouder.id}`).html(html));
            });
            inhoudContainer.append(lijst).append($('<input>').prop('type', 'hidden').prop('id', 'ouderNieuwID')
                .prop('value', '0'));

            MODAAL.inhoud(inhoudContainer);
            let voet = '';
            voet += MODAAL.knop('verberg', 'secondary', 'x-square', INHOUDPERSOON.boodschappenOuderNieuw.btnCa);
            MODAAL.voet(voet);
            MODAAL.toon();
        }
    },

    verberg: () => {
        MODAAL.verberg();
    }
}