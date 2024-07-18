const MODAAL = {
    oMD: null,

    init: (grootte) => {
        if (!MODAAL.oMD) {
            $('body').append(`
                <div class="modal fade" tabindex="-1" id="modDlg" data-bs-backdrop="static" data-bs-keyboard="false">
                    <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header w-100">
                        <h5 class="modal-title w-100" id="modDlgKop"></h5>
                        </div>
                        <div class="modal-body" id="modDlgInhoud"> </div>
                        <div class="modal-footer" id="modDlgVoet"></div>
                    </div>
                    </div>
                </div>
            `);
        }

        MODAAL.oMD = $('#modDlg');
        MODAAL.verberg();
    },

    kop: (html) => {
        if (!MODAAL.oMD) MODAAL.init();

        $(MODAAL.oMD).find('.modal-title').empty().html(html);
    },

    inhoud: (html) => {
        if (!MODAAL.oMD) MODAAL.init();

        $(MODAAL.oMD).find('.modal-body').empty().html(html);
    },

    voet: (html) => {
        if (!MODAAL.oMD) MODAAL.init();

        $(MODAAL.oMD).find('.modal-footer').empty().html(html);
    },

    toon: () => {
        if (!MODAAL.oMD) MODAAL.init();

        $(MODAAL.oMD).modal('show');
    },

    verberg: () => {
        if (!MODAAL.oMD) MODAAL.init();

        $(MODAAL.oMD).modal('hide');
    },

    spinner: (boodschap='') => {
        if (!MODAAL.oMD) MODAAL.init();

        $(MODAAL.oMD).find('.modal-body').empty().html(`
        <div class="text-center">
            <p>${boodschap}</p>
            <div class="spinner-border text-primary" role="status"></div>
        </div>
        `);
    },

    knop: (id, kleur, bi, tekst) => {
        return `
            <button type="button" id="${id}" class="btn btn-${kleur}">
                <i class="bi bi-${bi}"></i> ${tekst}
            </button>
        `;
    },

    grootte: (klasse) => {
        klasse = klasse ? klasse : '';
        $(MODAAL.oMD).find('.modal-dialog').prop('class', `modal-dialog ${klasse}`);
    }
 };