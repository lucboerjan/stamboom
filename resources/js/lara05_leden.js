$(() => {
    if ($('#ledenIndex').length) INDEX.init();
});

const INDEX = {
    csrfToken: $('meta[name="csrf-token"]').attr('content'),
    zoekTerm: '',
    reNaam: /^[\w\-\s\d]+$/,
    reVNaam: /^[\w\-\s]+$/,
    reEmail: /^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/,
    labels: null,
    geduld: null,
    fout: null,
    lidID: null,

    init: () => {
        INDEX.lijst(0);
        $('#ledenIndex').on('click', '#paginering a', function (evt) {
            evt.preventDefault();
            INDEX.lijst($(this).data('pagina'));
        });

        // ZOEKVELD, KNOPPEN
        $('#zoekVeld').on('keypress', function (evt) { if (evt.which === 13) INDEX.zoek(); })
        $('#zoekKnop').on('click', INDEX.zoek);
        $('#zoekReset').on('click', INDEX.zoekReset);


        // LEDENBEHEER
        // leden reset
        $('#ledenIndex').on('click', '.reset', function (evt) {
            evt.stopPropagation();
            INDEX.reset($(this).data('lidID'));
        });

        // leden bewerk
        $('#ledenIndex').on('click', '.bewerk', function (evt) {
            evt.stopPropagation();
            INDEX.bewerk($(this).data('lidID'), 'bewerk');
        });
        // knop nieuw
        $('#nieuwKnop').on('click', () => { INDEX.bewerk(0, 'nieuw'); });

        // leden verwijder
        $('#ledenIndex').on('click', '.verwijder', function (evt) {
            evt.stopPropagation();
            INDEX.bewerk($(this).data('lidID'), 'verwijder');
        });

        $('body').on('click', '#lidBewerkBewaar', INDEX.bewerkBewaar);
        $('body').on('click', '#lidBewerkAnnuleer, #lidInfoBtnAnnuleer', () => { MODAAL.verberg(); INDEX.lidID = null; });
        $('body').on('click', '#lidInfoBtnReset', INDEX.resetMail);
    },

    /* --- LEDEN LIJST --- */
    lijst: (pagina) => {
        let frmDta = {
            zoek: INDEX.zoekTerm,
            pagina: pagina
        }

        fetch('jxLedenLijst', {
            method: 'post',
            body: JSON.stringify(frmDta),
            headers: {
                "X-CSRF-Token": INDEX.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then((response) => {
            return response.json();
        }).then((res) => {
            $('#lijst').empty();

            if (!res.succes) return;

            let lijst = $('#lijst');
            if (res.aantal === 0) {
                $(lijst).append(
                    $('<div>').addClass('alert alert-info')
                        .append(
                            $('<h5>').addClass('alert-heading').text(res.infoTitel)
                        )
                        .append(
                            $('<hr>')
                        )
                        .append(
                            $('<p>').text(res.infoBoodschap)
                        )
                );
            }
            else {
                res.leden.forEach(lid => {
                    let lijnLinks = $('<div>').addClass('lijnLinks float-start lid').text(`${lid.fullname} | ${lid.email}`);
                    let lijnRechts = $('<div>').addClass('lijnRechts float-end');
                    let lidLevel = parseInt(lid.level);

                    // toont toegekende rechten
                    if (res.isAdmin) {
                        let rechten = $('<div>').addClass('btn-group me-1');
                        // user manager
                        $(rechten).append($('<button>').prop('type', 'button').prop('disabled', !(lidLevel & 2)).addClass('btn btn-secondary user').append($('<i>').addClass('bi bi-people')));
                        // content manager
                        $(rechten).append($('<button>').prop('type', 'button').prop('disabled', !(lidLevel & 4)).addClass('btn btn-secondary inhoud').append($('<i>').addClass('bi bi-person-vcard')));
                        // administrator
                        $(rechten).append($('<button>').prop('type', 'button').prop('disabled', !(lidLevel & 8)).addClass('btn btn-secondary admin').append($('<i>').addClass('bi bi-person-gear')));
                        $(lijnRechts).append(rechten)
                    }

                    // lid mag enkel viewer zijn of administrator
                    let knoppen = $('<div>').addClass('btn-group');
                    $(knoppen).append($('<button>').data('lidID', lid.id).prop('type', 'button').addClass('btn btn-primary reset').append($('<i>').addClass('bi bi-envelope')));
                    $(knoppen).append($('<button>').data('lidID', lid.id).prop('type', 'button').prop('disabled', !(lidLevel === 1 || res.isAdmin)).addClass('btn btn-primary bewerk').append($('<i>').addClass('bi bi-pencil')));
                    // knop 'trash' om lid te verwijderen
                    // enkel zichtbaar voor admin en enabled op niet leden zonder adminrechten
                    if (res.isAdmin) {
                        $(knoppen).append($('<button>').data('lidID', lid.id).prop('type', 'button').prop('disabled', Boolean(Number(lidLevel) & 8)).addClass('btn btn-warning verwijder').append($('<i>').addClass('bi bi-trash3')));
                    }

                    $(lijnRechts).append(knoppen);

                    let lijn = $('<div>').addClass('mt-3 float-none').addClass('lijn')
                        .append(lijnLinks)
                        .append(lijnRechts);
                    $(lijst).append(lijn);
                });

                $(lijst).append(PAGINERING.pagineer(res.pagina, res.knoppen, res.aantalPaginas))
            }
        }).catch((error) => {
            console.log(error);
        });
    },

    /* --- ZOEKVELD --- */
    zoek: () => {
        INDEX.zoekTerm = $('#zoekVeld').val().trim();
        INDEX.lijst(0);
    },

    zoekReset: () => {
        $('#zoekVeld').val('');
        INDEX.zoekTerm = '';
        INDEX.lijst(0);
    },

    /* --- LID BEWERKEN | NIEUW | VERWIJDER --- */
    bewerk: (lidID, mode) => {
        let frmDta = {
            lidID: lidID,
            mode: mode
        };

        fetch('jxLedenGet', {
            method: 'post',
            body: JSON.stringify(frmDta),
            headers: {
                "X-CSRF-Token": INDEX.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then((response) => {
            return response.json();
        }).then((res) => {
            INDEX.labels = res.labels;
            INDEX.geduld = res.geduld;

            if (res.succes) {
                let disabled = '';

                switch (res.mode) {
                    case 'bewerk':
                        MODAAL.kop(INDEX.labels.lidBewerkTitelBewerk);
                        break;
                    case 'nieuw':
                        MODAAL.kop(INDEX.labels.lidBewerkTitelNieuw);
                        break;
                    case 'verwijder':
                        disabled = 'disabled'
                        MODAAL.kop(INDEX.labels.lidBewerkTitelVerwijder);
                        break;
                }

                let inhoud = `
                            <div id="lidBewerkBoodschap"></div>
                            <input type="hidden" id="lidBewerkID" value="${res.lid.id}">
                            <input type="hidden" id="lidBewerkLevel" value="${res.lid.level}">
                            <input type="hidden" id="lidBewerkMode" value="${res.mode}">
                            <div class="form-group mb-3">
                                <label class="form-label" for="lidBewerkNaam">${INDEX.labels.lidBewerkNaam}</label>
                                <input class="form-control" type="text" id="lidBewerkNaam" value="${res.lid.name}" ${disabled}>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label" for="lidBewerkVNaam">${INDEX.labels.lidBewerkVNaam}</label>
                                <input class="form-control" type="text" id="lidBewerkVNaam" value="${res.lid.fullname}" ${disabled}>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label" for="lidBewerkEmail">${INDEX.labels.lidBewerkEmail}</label>
                                <input class="form-control" type="text" id="lidBewerkEmail" value="${res.lid.email}" ${disabled}>
                            </div>
                        `;
                if (res.isAdmin) {
                    inhoud += `
                            <h4 class="mb-2">${INDEX.labels.rechten}</h4>
                            <div class="form-check form-switch mt-3">
                                <input class="form-check-input" type="checkbox" role="switch" id="lidBewerkLeden" ${res.lid.level & 2 ? "checked" : ""} ${disabled}>
                                <label class="form-check-label" for="lidBewerkLeden"><i class="bi bi-people"></i>&nbsp;${INDEX.labels.lidBewerkLeden}</label>
                            </div>
                            <div class="form-check form-switch mt-3">
                                <input class="form-check-input" type="checkbox" role="switch" id="lidBewerkInhoud" ${res.lid.level & 4 ? "checked" : ""} ${disabled}>
                                <label class="form-check-label" for="lidBewerkInhoud"><i class="bi bi-person-vcard"></i>&nbsp;${INDEX.labels.lidBewerkInhoud}</label>
                            </div>
                            <div class="form-check form-switch mt-3">
                                <input class="form-check-input" type="checkbox" role="switch" id="lidBewerkAdmin" ${res.lid.level & 8 ? "checked" : ""} ${disabled}>
                                <label class="form-check-label" for="lidBewerkAdmin"><i class="bi bi-gear"></i>&nbsp;${INDEX.labels.lidBewerkAdmin}</label>
                            </div>
                        `;
                }
                MODAAL.inhoud(inhoud);
                let voet = '';
                if (res.mode === 'verwijder')
                    voet += MODAAL.knop('lidBewerkBewaar', 'primary', 'trash3', INDEX.labels.lidBewerkVerwijder);
                else
                    voet += MODAAL.knop('lidBewerkBewaar', 'primary', 'check-square', INDEX.labels.lidBewerkBewaar);
                voet += MODAAL.knop('lidBewerkAnnuleer', 'secondary', 'x-square', INDEX.labels.lidBewerkAnnuleer);
                MODAAL.voet(voet);
                MODAAL.toon();


            };



        }).catch((error) => {
            console.log(error);
            MODAAL.verberg();
        })

    },

    bewerkBewaar: () => {
        let mode = $('#lidBewerkMode').val();
        let lidID = $('#lidBewerkID').val();
        let naam = $('#lidBewerkNaam').val().trim();
        let vnaam = $('#lidBewerkVNaam').val().trim();
        let email = $('#lidBewerkEmail').val().trim().toLowerCase();

        $('#lidBewerkBoodschap').empty();

        let boodschap = '';
        boodschap += INDEX.reNaam.test(String(naam)) ? `` : `<li>${INDEX.labels.lidBewerkNaam}</li>`;
        boodschap += INDEX.reVNaam.test(String(vnaam)) ? `` : `<li>${INDEX.labels.lidBewerkVNaam}</li>`;
        boodschap += INDEX.reEmail.test(String(email)) ? `` : `<li>${INDEX.labels.lidBewerkEmail}</li>`;

        if (boodschap.length != 0) {
            $('#lidBewerkBoodschap').html(`
                <div class="alert alert-warning">
                    ${INDEX.labels.lidBewerkBoodschap}
                    <ul>
                        ${boodschap}
                    </ul>
                </div>
            `);
        }
        else {
            let level = 0;
            if ($('#lidBewerkLeden').length) {
                level = 1;
                level += $('#lidBewerkLeden').is(':checked') ? 2 : 0;
                level += $('#lidBewerkInhoud').is(':checked') ? 4 : 0;
                level += $('#lidBewerkAdmin').is(':checked') ? 8 : 0;
            }
            else {
                level = $('#lidBewerkLevel').val();
            }

            let frmDta = {
                mode: mode,
                lidID: lidID,
                naam: naam,
                vnaam: vnaam,
                email: email,
                level: level
            }

            fetch('jxLedenBewaar', {
                method: 'post',
                body: JSON.stringify(frmDta),
                headers: {
                    "X-CSRF-Token": INDEX.csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            }).then((response) => {
                return response.json();
            }).then((res) => {

                if (res.succes) {
                    MODAAL.verberg();

                    let pagina = 0;
                    if ($('.pagination').length > 0) {
                        $('.pagination a').each((ndx, el) => {
                            if ($(el).hasClass('active')) {
                                pagina = $(el).data('pagina');
                            }
                        })
                    }

                    // stuur resetMail voor nieuwe gebruiker
                    if (res.mode == 'nieuw') {
                        INDEX.lidID = res.lidID;

                        INDEX.resetMail();
                    }

                    INDEX.lijst(pagina);
                }
                else {
                    $('#lidBewerkBoodschap').html(`
                        <div clss="alert alert-warning">
                            ${res.boodschap}
                        </div>
                    `);
                    MODAAL.toon();
                }


            }).catch((error) => {
                console.log(error);
                MODAAL.kop(res.lid.id == 0 ? INDEX.labels.lidBewerkTitelNieuw : INDEX.labels.lidBewerkTitelBewerk);
                MODAAL.inhoud(`<p>${INDEX.fout}</p>`);
                MODAAL.voet(MODAAL.knop('lidBewerkAnnuleer', 'secondary', 'x-square', INDEX.labels.lidBewerkAnnuleer));
                MODAAL.toon();
            })
        }
    },

    /* --- LID RESET --- */
    reset: (lidID) => {

        let frmDta = {
            lidID: lidID
        }

        fetch('jxLedenResetInfo', {
            method: 'post',
            body: JSON.stringify(frmDta),
            headers: {
                "X-CSRF-Token": INDEX.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then((response) => {
            return response.json();


        }).then((res) => {
            if (res.succes) {
                INDEX.lidID = res.lid.id;
                MODAAL.kop(res.labels.lidInfoTitel);
                MODAAL.inhoud(`<p>${res.labels.lidInfoBericht}: <br><strong>${res.lid.fullname}<br>${res.lid.email}</strong</p>`)
                MODAAL.voet(MODAAL.knop('lidInfoBtnReset', 'primary', 'envelope', res.labels.lidInfoBtnReset) +
                    MODAAL.knop('lidInfoBtnAnnuleer', 'secondary', 'x-square', res.labels.lidInfoBtnAnnuleer));
                MODAAL.toon();
            }

        }).catch((error) => {

            console.log(error);
            MODAAL.verberg();
        })
    },



    resetMail: () => {
        MODAAL.verberg();

        let frmDta = {
            lidID: INDEX.lidID
        }

        INDEX.lidID = null;

        fetch('jxLedenResetMail', {
            method: 'post',
            body: JSON.stringify(frmDta),
            headers: {
                "X-CSRF-Token": INDEX.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then((response) => {
            return response.json();
        }).then((res) => {
        }).catch((error) => {
            console.log(error);
        })

    },
}