$(
    () => {
        if ($('#inhoudFamilie').length) INHOUDFAMILIE.init();
    }
);

const INHOUDFAMILIE = {
    csrfToken: $('meta[name="csrf-token"]').attr('content'),
    familieID: null,

    init: () => {
        // ophalen lijst families
        INHOUDFAMILIE.familieLijst();

        // filterfunctie
        $('#inhoudFamilieZoek').on('keyup', () => { INHOUDFAMILIE.familieFilter(); });
        $('#inhoudFamilieFilter').on('click', () => { INHOUDFAMILIE.familieFilter(); });
        $('#inhoudFamilieZoekAnnuleer').on('click', () => { 
            $('#inhoudFamilieZoek').val('');
            INHOUDFAMILIE.familieFilter();
         });

        //  nieuw
        $('#inhoudFamilieNieuw').on('click', () => { INHOUDFAMILIE.familieNieuw(); });
        // checkbox
        $('#inhoudFamilie').on('click', '.inhoudFamilieSelecteer',  function(evt) { 
            INHOUDFAMILIE.familieSelecteer(evt); 
        });
        // bewerk
        $('#inhoudFamilie').on('click', '.inhoudFamilieBewerk',  function(evt) { 
            INHOUDFAMILIE.familieBewerk($(this)); 
        });
        // verwijder
        $('#inhoudFamilie').on('click', '.inhoudFamilieVerwijder',  function(evt) { 
            INHOUDFAMILIE.familieVerwijder(evt); 
        });
        $('body').on('click', '#inhoudFamilieVerwijderBtn', () => {
            INHOUDFAMILIE.familieVerwijderBevestig();
        });
        // bewaar
        $('#inhoudFamilie').on('click', '.inhoudFamilieBewaar',  function(evt) { 
            INHOUDFAMILIE.familieBewaar(); 
        });
        // annuleer
        $('#inhoudFamilie').on('click', '.inhoudFamilieAnnuleer',  function(evt) { 
            INHOUDFAMILIE.familieAnnuleer(); 
        });

        // merge families
        $('#inhoudFamilieMerge').on('click', () => { INHOUDFAMILIE.familieMerge() });
        $('body').on('click', '#familieMergeOK', () => { INHOUDFAMILIE.familieMergeUitvoeren(); });

        // verberg modaal venster
        $('body').on('click', '#verberg', () => { MODAAL.verberg(); });
    },

    // ophalen en weergevenfamilielijst
    familieLijst: () => {
        fetch('/jxInhoudFamilieLijst', {
            method: 'post',
            body: new FormData(),

            headers: {
                'X-CSRF-Token': INHOUDFAMILIE.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then((response) => {
            return response.json()
        })
        .then((res) => {
            if (res.succes) {
                $('#inhoudFamilieZoek').val('');
                INHOUDFAMILIE.familieLijstToon(res);
            }
        })
        .catch((err) => {
            console.log(error);
        })
    },

    familieLijstToon: (res) => {
        let inhoudFamilieLijst = $('#inhoudFamilieLijst');

        inhoudFamilieLijst.empty();

        res.families.forEach(
            familie => {
                inhoudFamilieLijst.append(INHOUDFAMILIE.familieLijstObject(familie.id,
                                                                            familie.naam,
                                                                            familie.aantal,
                                                                            true));
            }
        )

        $('#inhoudFamilieNieuw').prop('disabled', false);
    },

    familieLijstObject: (familieID, familieNaam, familieAantal, status) => {
        let disabled = status ? 'disabled' : '';
        let knoppenStandaard = status ? 'block' : 'none';
        let knoppenVerborgen = status ? 'none' : 'block';

        return `
            <div class="input-group mb-1 familieLijstItem d-inline-flex" id="familieID_${familieID}">

                <div class="input-group-text">
                    <input type="checkbox" class="for-check-input inhoudFamilieSelecteer">
                </div>

                <div class="badge text-bg-secondary" style="min-width:80px; padding-top: 12px;">${familieAantal}</div>
                
                <input type="text" class="form-control inhoudFamilieNaam" value="${familieNaam}" ${disabled}>

                <button type="button" class="btn btn-primary inhoudFamilieBewerk" style="display:${knoppenStandaard};">
                    <i class="bi bi-pencil"></i>
                </button>

                <button type="button" class="btn btn-secondary inhoudFamilieVerwijder" style="display:${knoppenStandaard};">
                    <i class="bi bi-trash"></i>
                </button>

                <button type="button" class="btn btn-primary inhoudFamilieBewaar" style="display:${knoppenVerborgen};">
                    <i class="bi bi-check"></i>
                </button>

                <button type="button" class="btn btn-secondary inhoudFamilieAnnuleer" style="display:${knoppenVerborgen};">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        `;
    },

    familieFilter: ()=> {
        $('.inhoudFamilieSelecteer').prop('checked', false);
        $('#inhoudFamilieMerge').prop('disabled', true);

        let filter = $('#inhoudFamilieZoek').val().trim().toUpperCase();

        if (filter.length == 0) {
            $('.familieLijstItem').removeClass('visually-hidden');
        }
        else {
            $('.familieLijstItem').each((ndx, item) => {
                let naam = $(item).find('.inhoudFamilieNaam').val()
                                                             .replace(/<[^>]+>/g,'')
                                                              .replace(/\s+/g ,'')
                                                             .replace('\n' ,'');

                if (naam.toUpperCase().indexOf(filter) === -1) {
                    $(item).addClass('visually-hidden');
                }
                else {
                    $(item).removeClass('visually-hidden');
                }
            });
        }
    },

    familieNieuw: () => {
        INHOUDFAMILIE.familieID = 0;       
        INHOUDFAMILIE.toggleControls('nieuw');
        $('#inhoudFamilieLijst').prepend(INHOUDFAMILIE.familieLijstObject(0,'','', false));

        // INHOUDFAMILIE.disableElementen($('#familieID_0 .inhoudFamilieBewerk'));
    },

    familieSelecteer: (evt) => {
        $('#inhoudFamilieMerge').prop('disabled', $('.inhoudFamilieSelecteer:checked').length < 2);
    },
    
    familieBewerk: (obj) => {
        INHOUDFAMILIE.toggleControls('bewerk'); 
        
        $(obj).parent().find('.inhoudFamilieBewerk, .inhoudFamilieVerwijder').hide();
        $(obj).parent().find('.inhoudFamilieBewaar, .inhoudFamilieAnnuleer').show();

        INHOUDFAMILIE.familieID = $(obj).parent().prop('id').split('_')[1];
        $(obj).parent().find('.inhoudFamilieNaam').prop('disabled', false);
    },

    toggleControls: (mode = '') => { 
        // deselecteer alle checkboxes + disable
        $('.inhoudFamilieSelecteer').prop('checked', false)
                                    .prop('disabled', mode=='nieuw' || mode=='bewerk');

        // knoppen bewerk, verwijder, bewaar en annuleer
        $('.inhoudFamilieBewerk, .inhoudFamilieVerwijder').prop('disabled', mode=='nieuw' || mode=='bewerk');
        $('.inhoudFamilieBewaar, .inhoudFamilieAnnuleer').prop('disabled', mode=='nieuw');

        if (mode != 'bewerk') {
            $('.inhoudFamilieBewerk, .inhoudFamilieVerwijder').show();
            $('.inhoudFamilieBewaar, .inhoudFamilieAnnuleer').hide();
        }

        // disable knop nieuw en familiemerge
        $('#inhoudFamilieNieuw').prop('disabled', mode=='nieuw' || mode=='bewerk');
        $('#inhoudFamilieMerge, .inhoudFamilieNaam').prop('disabled', true);

        // schakel filter functionaliteit uit
        $('#inhoudFamilieZoek').val('');
        $('#inhoudFamilieZoek, #inhoudFamilieFilter, #inhoudFamilieZoekAnnuleer').prop('disabled', mode=='nieuw' || mode=='bewerk');
    },

    familieVerwijder: (evt) => {
        $('.inhoudFamilieSelecteer').prop('checked', false);
        $('#inhoudFamilieMerge').prop('disabled', true);
        $('#inhoudFamilieZoek').val('');

        let familieID = $(evt.target).parent().prop('id').split('_')[1];
        if (!familieID) familieID = $(evt.target).parent().parent().prop('id').split('_')[1];

        let frmDta = { 'familieID' : familieID }

        fetch('/jxInhoudFamilieVerwijderInfo', {
            method: 'post',
            body: JSON.stringify(frmDta),

            headers: {
                'X-CSRF-Token': INHOUDFAMILIE.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then((response) => {
            return response.json()
        })
        .then((res) => {
            if (res.succes) {
                INHOUDFAMILIE.familieVerwijderInfo(res);
            }
        })
        .catch((err) => {
            console.log(error);
        })
    },

    familieVerwijderInfo: (res) => {
        let boodschappen = {};
        res.boodschap.split(',').forEach(boodschap => {
            let tmp = boodschap.split(':');
            boodschappen[tmp[0]] = tmp[1];
        });

        MODAAL.grootte('');
        MODAAL.kop(boodschappen.titel);
        let inhoud  = `<input type="hidden" id="inhoudFamilieVerwijderID" value="familieID_${res.familieID}">`;
            inhoud += boodschappen.info.replace('%naam%', res.naam).replace('%aantal%', res.aantal);
        MODAAL.inhoud(inhoud);
        let knoppen  = '';        
            knoppen += MODAAL.knop('inhoudFamilieVerwijderBtn', 'primary', 'scissors', boodschappen.btnVerwijder);
            knoppen += MODAAL.knop('verberg', 'secondary', 'x-square', boodschappen.btnAnnuleer);
        MODAAL.voet(knoppen);
        MODAAL.toon();
    },

    familieVerwijderBevestig: () => {
        let frmDta = {
            'familieID': $('#inhoudFamilieVerwijderID').val().split('_')[1]
        }

        fetch('/jxInhoudFamilieVerwijderBevestig', {
            method: 'post',
            body: JSON.stringify(frmDta),

            headers: {
                'X-CSRF-Token': INHOUDFAMILIE.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then((response) => {
            return response.json()
        })
        .then((res) => {
            INHOUDFAMILIE.familieLijst();
            MODAAL.verberg();
        })
        .catch((err) => {
            console.log(error);
        })
    },

    familieBewaar: () => {
        let frmDta = {
            familieID: INHOUDFAMILIE.familieID,
            naam: $(`#familieID_${INHOUDFAMILIE.familieID} .inhoudFamilieNaam`).val().trim()
        }

        fetch('/jxInhoudFamilieUpdate', {
            method: 'post',
            body: JSON.stringify(frmDta),

            headers: {
                'X-CSRF-Token': INHOUDFAMILIE.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then((response) => {
            return response.json()
        })
        .then((res) => {
            if (res.succes) {
                $('#inhoudFamilieZoek').val('');
                INHOUDFAMILIE.familieID = null;
                INHOUDFAMILIE.familieLijstToon(res);
            }
        })
        .catch((err) => {
            console.log(error);
        })
    },

    familieAnnuleer: () => {
        if (INHOUDFAMILIE.familieID === 0) {
            $('#familieID_0').remove();            
        }
        
        INHOUDFAMILIE.toggleControls('');
    },

    familieMerge: () => {
        let families = [];
        $('.inhoudFamilieSelecteer:checked').each((ndx, el) => {
            families.push($(el).parent().parent().prop('id').split('_')[1]);
        });

        let frmDta = {
            families: families.join(',')
        }

        fetch('/jxInhoudFamilieMergeLijst', {
            method: 'post',
            body: JSON.stringify(frmDta),

            headers: {
                'X-CSRF-Token': INHOUDFAMILIE.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then((response) => {
            return response.json()
        })
        .then((res) => {
            if (res.succes) {
                INHOUDFAMILIE.familieMergeLijst(res);
            }
        })
        .catch((err) => {
            console.log(error);
        })
    },

    familieMergeLijst: (res) => {
        let boodschap = {};
        res.boodschap.split(',').forEach(kv => {
            let tmp = kv.split(':');
            boodschap[tmp[0]] = tmp[1];
        });

        MODAAL.kop(boodschap.titel);

        let inhoudTmp = ``;
        res.families.forEach((familie, ndx) => {
            let geselecteerd = ndx == 0 ? 'checked' : '';
            let kleur = familie.aantal == 0 ? 'light' : 'primary';

            inhoudTmp += `
                <li class="list-group-item">
                    <input class="familieMerge form-check-input me-1" 
                        type="radio" name="familieMerge" id="familieID_${familie.id}" ${geselecteerd}>
                    ${familie.naam}
                    <span class="badge text-bg-${kleur} rounded-pill float-end me-1">${familie.aantal}</span>
                </li>
            `;
        });

        MODAAL.inhoud(`
            <p>${boodschap.info}</p>
            <ul class="list-group">
                ${inhoudTmp}
            </ul>
        `);

        let voet = ``;
            voet += MODAAL.knop('familieMergeOK', 'primary', 'sign-merge-right', boodschap.btnOK);
            voet += MODAAL.knop('verberg', 'secondary', 'x-square', boodschap.btnCa);
        MODAAL.voet(voet);

        MODAAL.toon();
    },

    familieMergeUitvoeren: () => {
        let families = [];
        $('.familieMerge').each((nsx, el) => {
            families.push($(el).prop('id').split('_')[1]);
        });
        families = families.join(',');
       
        let familie = $('.familieMerge:checked').prop('id').split('_')[1];

        let frmDta = {
            families: families,
            familie: familie
        }
        fetch('/jxInhoudFamilieMergeUitvoeren', {
            method: 'post',
            body: JSON.stringify(frmDta),

            headers: {
                'X-CSRF-Token': INHOUDFAMILIE.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then((response) => {
            return response.json()
        })
        .then((res) => {
            if (res.succes) {
                INHOUDFAMILIE.familieLijst();
                MODAAL.verberg();
            }
        })
        .catch((err) => {
            console.log(error);
        })

    }
}