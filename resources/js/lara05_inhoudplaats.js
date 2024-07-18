$(() => {
    if ($('#inhoudPlaats').length) INHOUDPLAATS.init();
});

const INHOUDPLAATS = {
    csrfToken: $('meta[name="csrf-token"]').attr('content'),
    plaatsID: null,

    init: () => {
        // ophalen lijst plaatsen
        INHOUDPLAATS.plaatsLijst();

        // zoek/filter-functie
        $('#inhoudPlaatsZoek').on('keyup', () => { INHOUDPLAATS.plaatsFilter(); });
        $('#inhoudPlaatsFilter').on('click', () => { INHOUDPLAATS.plaatsFilter(); });
        $('#inhoudPlaatsZoekAnnuleer').on('click', () => {
            $('#inhoudPlaatsZoek').val('');
            INHOUDPLAATS.plaatsFilter();
        });

        // nieuwe plaats
        $('#inhoudPlaatsNieuw').on('click', () => { INHOUDPLAATS.plaatsNieuw(); });

        // plaats checkbox
        $('#inhoudPlaats').on('click', '.inhoudPlaatsSelecteer', function (evt) { INHOUDPLAATS.plaatsSelecteer(evt); });
        // plaats bewerk
        $('#inhoudPlaats').on('click', '.inhoudPlaatsBewerk', function (evt) { INHOUDPLAATS.plaatsBewerk($(this)); });
        // plaats verwijder
        $('#inhoudPlaats').on('click', '.inhoudPlaatsVerwijder', function (evt) { INHOUDPLAATS.plaatsVerwijder(evt); });
        // -- plaats verwijder knop
        $('body').on('click', '#inhoudPlaatsVerwijderBtn', () => { INHOUDPLAATS.plaatsVerwijderBevestig(); });
        // bewaar plaats
        $('#inhoudPlaats').on('click', '.inhoudPlaatsBewaar', function (evt) { INHOUDPLAATS.plaatsBewaar(); });
        // annuleer plaats
        $('#inhoudPlaats').on('click', '.inhoudPlaatsAnnuleer', function (evt) { INHOUDPLAATS.plaatsAnnuleer(); });


        // plaats merge        
        $('#inhoudPlaatsMerge').on('click', () => { INHOUDPLAATS.plaatsMerge(); });
        $('body').on('click', '#plaatsMergeOk', () => { INHOUDPLAATS.plaatsMergeUitvoeren(); });

        // verberg modaal venster
        $('body').on('click', '#verberg', () => { INHOUDPLAATS.verberg(); });
    },

    // ophalen plaatsen
    plaatsLijst: () => {
        fetch('/jxInhoudPlaatsLijst', {
            method: 'post',
            body: new FormData(),

            headers: {
                "X-CSRF-Token": INHOUDPLAATS.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then((response) => {
            return response.json()
        }).then((res) => {
            if (res.succes) {
                $('#inhoudPlaatsZoek').val('');
                INHOUDPLAATS.plaatsLijstToon(res.plaatsen);
            }
        }).catch((error) => {
            console.log(error);
        });
    },

    // maak lijst met plaatsen
    plaatsLijstToon: (plaatsen) => {
        let inhoudPlaatsLijst = $('#inhoudPlaatsLijst');

        inhoudPlaatsLijst.empty();

        plaatsen.forEach(
            plaats => {
                inhoudPlaatsLijst.append(INHOUDPLAATS.plaatsLijstObject(plaats.id, plaats.plaats, plaats.aantal, 'disabled'));
            }
        )

        $('#inhoudPlaatsNieuw').prop('disabled', false);
        $(inhoudPlaatsLijst).scrollTop(0);
    },

    plaatsLijstObject: (plaatsID, plaatsNaam, aantal, status) => {
        let disabled = status ? 'disabled' : '';
        let knoppenStandaard = status ? 'block' : 'none';
        let knoppenVerborgen = status ? 'none' : 'block';

        return `<div class="input-group mb-1 plaatsLijstIem d-inline-flex" id="plaatsID_${plaatsID}">
                    <div class="input-group-text">
                        <input class="form-check-input mt-0 inhoudPlaatsSelecteer" type="checkbox">
                    </div>

                    <div class="badge text-bg-secondary" style="min-width: 50px;padding-top:12px;">${aantal}</div>

                    <input type="text" class="form-control inhoudPlaatsNaam" value="${plaatsNaam}" ${disabled}>

                    <button type="button" class="btn btn-primary inhoudPlaatsBewerk" style="display: ${knoppenStandaard};">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button type="button" class="btn btn-secondary inhoudPlaatsVerwijder" style="display: ${knoppenStandaard};">
                        <i class="bi bi-trash"></i>
                    </button>
                    <button type="button" class="btn btn-primary inhoudPlaatsBewaar" style="display: ${knoppenVerborgen};">
                        <i class="bi bi-check"></i>
                    </button>
                    <button type="button" class="btn btn-secondary inhoudPlaatsAnnuleer" style="display: ${knoppenVerborgen};">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>`;
    },

    /* --- FILTER OP PLAATSNAAM --- */
    // filter plaatsen op zoekveld
    plaatsFilter: () => {
        // verwijder gecheckte plaatsleden
        $('.inhoudPlaatsSelecteer').prop('checked', false);
        $('#inhoudPlaatsMerge').prop('disabled', true);

        // filter plaats
        let filter = $('#inhoudPlaatsZoek').val().trim().toUpperCase();

        if (filter.length == 0) {
            $('.plaatsLijstIem').removeClass('visually-hidden');
        }
        else {
            $('.plaatsLijstIem').each((ndx, item) => {
                let naam = $(item).find('.inhoudPlaatsNaam').val()
                    .replace(/<[^>]+>/g, '')
                    .replace(/\s+/g, ' ')
                    .replace('\n', '');

                if (naam.toUpperCase().indexOf(filter) === -1) {
                    $(item).addClass('visually-hidden');
                }
                else {
                    $(item).removeClass('visually-hidden');
                }
            });
        }
    },

    /* --- TOGGLE CONTROLS --- */
    toggleControls: (mode = '') => {
        // checboxes: deselecteer + disable
        $('.inhoudPlaatsSelecteer').prop('checked', false).prop('disabled', mode == 'nieuw' || mode == 'bewerk');

        // knoppen
        $('.inhoudPlaatsBewerk,.inhoudPlaatsVerwijder').prop('disabled', mode == 'nieuw' || mode == 'bewerk');
        $('.inhoudPlaatsBewaar,.inhoudPlaatsAnnuleer').prop('disabled', mode == 'nieuw');

        if (mode != 'bewerk') {
            // verberg knop bewerk en vewijder van huidig item, 
            $('.inhoudPlaatsBewerk,.inhoudPlaatsVerwijder').show();
            // toon knop bewaar en annuleer van huidig item
            $('.inhoudPlaatsBewaar,.inhoudPlaatsAnnuleer').hide();
        }

        // filter
        $('#inhoudPlaatsZoek, #inhoudPlaatsFilter, #inhoudPlaatsZoekAnnuleer').prop('disabled', mode == 'nieuw' || mode == 'bewerk')

        // nieuw
        $('#inhoudPlaatsNieuw').prop('disabled', mode == 'nieuw' || mode == 'bewerk');
        // merge
        $('#inhoudPlaatsMerge').prop('disabled', true);

        // disable invoervelden
        $('.inhoudPlaatsNaam').prop('disabled', true);
    },

    // --------------------------------------------------------------------------------------------------------------

    /* --- BEWERK PLAATSNAAM --- */
    // bewerk plaatsnaam
    plaatsBewerk: (obj) => {
        INHOUDPLAATS.toggleControls('bewerk');

        // verberg knop bewerk en verwijder van huidig item, 
        $(obj).parent().find('.inhoudPlaatsBewerk,.inhoudPlaatsVerwijder').hide();
        // toon knop bewaar en annuleer van huidig item
        $(obj).parent().find('.inhoudPlaatsBewaar,.inhoudPlaatsAnnuleer').show();

        INHOUDPLAATS.plaatsID = $(obj).parent().prop('id').split('_')[1];
        $(obj).parent().find('.inhoudPlaatsNaam').prop('disabled', false);
    },

    // bewaar plaats
    plaatsBewaar: () => {
        let plaatsNaam = $(`#plaatsID_${INHOUDPLAATS.plaatsID} .inhoudPlaatsNaam`).val().trim();
        if (!plaatsNaam.match(/\S+\s+\(\S+\)/)) {
            fetch('/jxInhoudPlaatsFout', {
                method: 'post',
                body: new FormData(),

                headers: {
                    "X-CSRF-Token": INHOUDPLAATS.csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            }).then((response) => {
                return response.json()
            }).then((res) => {
                MODAAL.grootte('');
                MODAAL.kop(res.titel);
                MODAAL.inhoud(res.info);
                MODAAL.voet(MODAAL.knop('verberg', 'secondary', 'X-square', res.btnCa));
                MODAAL.toon();
            }).catch((error) => {
                console.log(error);
            });

            return;
        }

        plaatsNaam = plaatsNaam.replace(/\s\s+/g, ' ').split('(');

        let plaatsLand = plaatsNaam[1].replace(')', '').trim();
        plaatsLand = plaatsNaam[1].split(')')[0].trim();
        let plaatsGemeente = plaatsNaam[0];

        let frmDta = {
            plaatsID: INHOUDPLAATS.plaatsID,
            plaatsGemeente: plaatsGemeente,
            plaatsLand: plaatsLand
        }

        fetch('/jxInhoudPlaatsUpdate', {
            method: 'post',
            body: JSON.stringify(frmDta),

            headers: {
                "X-CSRF-Token": INHOUDPLAATS.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then((response) => {
            return response.json()
        }).then((res) => {
            if (res.succes) {
                $('#inhoudPlaatsZoek').val('');
                INHOUDPLAATS.plaatsID = null;
                INHOUDPLAATS.plaatsLijstToon(res.plaats);
                INHOUDPLAATS.toggleControls('');

            }
        }).catch((error) => {
            INHOUDPLAATS.plaatsID = null;
        });
    },

    // bewerk annuleer plaats
    plaatsAnnuleer: () => {
        if (INHOUDPLAATS.plaatsID === 0) {
            $(`#plaatsID_0`).remove();
        }

        INHOUDPLAATS.toggleControls(false);
    },

    // knop Plaatss samenvoegen indien 2 of meer plaats geselecteerd
    plaatsSelecteer: (evt) => {
        $('#inhoudPlaatsMerge').prop('disabled', $('.inhoudPlaatsSelecteer:checked').length < 2);
    },

    /* --- VERWIJDER PLAATS --- */

    // verwijder plaats: ophalen info
    plaatsVerwijder: (evt) => {
        let plaatsID = $(evt.target).parent().prop('id').split('_')[1];
        if (!plaatsID) plaatsID = $(evt.target).parent().parent().prop('id').split('_')[1];

        let frmDta = { 'plaatsID': plaatsID }

        fetch('/jxInhoudPlaatsVerwijderInfo', {
            method: 'post',
            body: JSON.stringify(frmDta),

            headers: {
                "X-CSRF-Token": INHOUDPLAATS.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then((response) => {
            return response.json()
        }).then((res) => {
            if (res.succes) {
                INHOUDPLAATS.plaatsVerwijderInfo(res);
            }
        }).catch((error) => {
            console.log(error);
        });
    },

    // toon info plaats
    plaatsVerwijderInfo: (res) => {
        let boodschappen = {};
        res.boodschap.split(',').forEach(boodschap => {
            let tmp = boodschap.split(':');
            boodschappen[tmp[0]] = tmp[1];
        })

        MODAAL.kop(boodschappen.titel);
        let inhoud = `<input type="hidden" id="inhoudPlaatsVerwijderID" value="plaatsID_${res.plaatsID}">`;
        inhoud += boodschappen.info.replace('%plaats%', res.plaats).replace('%aantal%', res.aantal);
        MODAAL.inhoud(inhoud);
        let knoppen = '';
        knoppen += MODAAL.knop('inhoudPlaatsVerwijderBtn', 'primary', 'scissors', boodschappen.btnVerwijer);
        knoppen += MODAAL.knop('verberg', 'secondary', 'X-square', boodschappen.btnAnnuleer);
        MODAAL.voet(knoppen);
        MODAAL.toon();
    },

    // verwijder plaats
    plaatsVerwijderBevestig: () => {
        let frmDta = {
            'plaatsID': $('#inhoudPlaatsVerwijderID').val().split('_')[1]
        }

        fetch('/jxInhoudPlaatsVerwijderBevestig', {
            method: 'post',
            body: JSON.stringify(frmDta),

            headers: {
                "X-CSRF-Token": INHOUDPLAATS.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then((response) => {
            return response.json()
        }).then((res) => {
            INHOUDPLAATS.plaatsLijst();
            MODAAL.verberg();
        }).catch((error) => {
            console.log(error);
        });
    },

    /* --- NIEUWE PLAATS --- */
    plaatsNieuw: () => {
        INHOUDPLAATS.plaatsID = 0;
        INHOUDPLAATS.toggleControls('nieuw');
        $('#inhoudPlaatsLijst').prepend(INHOUDPLAATS.plaatsLijstObject(0, '', '', false));

    },

    /* --- PLAATS MERGE --- */
    plaatsMerge: () => {
        let plaats = []
        $('.inhoudPlaatsSelecteer:checked').each((ndx, el) => {
            plaats.push($(el).parent().parent().prop('id').split('_')[1]);
        });

        let frmDta = {
            plaatsen: plaats.join(',')
        }

        fetch('/jxInhoudPlaatsMergeLijst', {
            method: 'post',
            body: JSON.stringify(frmDta),

            headers: {
                "X-CSRF-Token": INHOUDPLAATS.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then((response) => {
            return response.json()
        }).then((res) => {
            if (res.succes) {
                INHOUDPLAATS.plaatsMergeLijst(res);
            }
        }).catch((error) => {
            console.log(error);
        });
    },

    plaatsMergeLijst: (res) => {
        if (res.succes) {
            let boodschap = {};
            res.boodschap.split(',').forEach(kv => {
                let tmp = kv.split(':');
                boodschap[tmp[0]] = tmp[1];
            });

            MODAAL.kop(boodschap['titel']);

            let inhoudTmp = ``;
            res.plaatsen.forEach((plaats, ndx) => {
                let geselecteerd = ndx == 0 ? 'checked' : '';
                let kleur = plaats.aantal == 0 ? 'light' : 'primary';
                inhoudTmp += `
                    <li class="list-group-item">
                        <input class="plaatsMerge form-check-input me-1" type="radio" name="plaatsMerge" id="plaats_${plaats.id}" ${geselecteerd}>
                        ${plaats.plaats}
                        <span class="badge text-bg-${kleur} rounded-pill float-end me-1">${plaats.aantal}</span>
                    </li>
                `;
            });

            MODAAL.inhoud(`
                <p>${boodschap.info}</p>
                <ul class="list-group">
                    ${inhoudTmp}
                </ul>
            `)

            let voet = '';
            voet += MODAAL.knop('plaatsMergeOk', 'primary', 'sign-merge-right', boodschap.btnOK);
            voet += MODAAL.knop('verberg', 'secondary', 'x-square', boodschap.btnCa);
            MODAAL.voet(voet);

            MODAAL.toon();

        }
    },

    plaatsMergeUitvoeren: () => {
        let plaatsen = [];
        $('.plaatsMerge').each((ndx, el) => {
            plaatsen.push($(el).prop('id').split('_')[1]);
        });
        plaatsen = plaatsen.join(',');

        let plaats = $('.plaatsMerge:checked').prop('id').split('_')[1];

        let frmDta = {
            plaatsen: plaatsen,
            plaats: plaats
        }

        fetch('/jxInhoudPlaatsMergeUitvoeren', {
            method: 'post',
            body: JSON.stringify(frmDta),

            headers: {
                "X-CSRF-Token": INHOUDPLAATS.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then((response) => {
            return response.json()
        }).then((res) => {
            if (res.succes) {
                INHOUDPLAATS.plaatsLijst();
                MODAAL.verberg();
            }
        }).catch((error) => {
            console.log(error);
        });
    },

    // --------------------------------------------------------------------------------------------------------------



    /* --- VERBERG DIALOOG --- */
    verberg: () => {
        MODAAL.verberg();
    }
}