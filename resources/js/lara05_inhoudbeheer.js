$(() => {
    if ($('#inhoudBeheer').length) INHOUDBEHEER.init();
});

const INHOUDBEHEER = {
    csrfToken: $('meta[name="csrf-token"]').attr('content'),
    boodschappen: [],
    init: () => {
        $('#beheerBewaar').on('click', () => { INHOUDBEHEER.bewaar(); });

        $('.leeftijdgrens').ionRangeSlider();
        $('body').on('click', '#verberg', () => { MODAAL.verberg(); });
    },

    bewaar: () => {
        let kleurStandaard = `fill:${$('#themaKleurStandaardVulling').val()},stroke:${$('#themaKleurStandaardLijn').val()},color:${$('#themaKleurStandaardTekst').val()}`;
        let kleurRelatie = `fill:${$('#themaKleurRelatieVulling').val()},stroke:${$('#themaKleurRelatieLijn').val()},color:${$('#themaKleurRelatieTekst').val()}`;
        let kleurPersoon = `fill:${$('#themaKleurPersoonVulling').val()},stroke:${$('#themaKleurPersoonLijn').val()},color:${$('#themaKleurPersoonTekst').val()}`;

        let frmDta = {
            aantalItemsPerPagina: $('#aantalItemsPerPagina').val(),
            aantalKnoppen: $('#aantalKnoppen').val(),

            leeftijdgrensOuder: $('#leeftijdgrensOuder').val(),
            leeftijdgrensKind: $('#leeftijdgrensKind').val(),
            leeftijdgrensOverlijden: $('#leeftijdgrensOverlijden').val(),

            thema: $('#thema').val(),

            kleurStandaard: kleurStandaard,
            kleurRelatie: kleurRelatie,
            kleurPersoon: kleurPersoon
        }


        fetch('/jxBeheerUpdate', {
            method: 'post',
            body: JSON.stringify(frmDta),

            headers: {
                'X-CSRF-Token': INHOUDBEHEER.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
            .then(response => {
                return response.json()
            })
            .then(res => {
                if (res.succes) {
                    res.boodschappen.split(',').forEach(boodschap => {
                        let tmp = boodschap.split(':');
                        INHOUDBEHEER.boodschappen[tmp[0]] = tmp[1];
    
                    });

                    let kop = `
                    <h4>${INHOUDBEHEER.boodschappen.titel}</h4>`
                    MODAAL.kop(kop);
                    MODAAL.inhoud(INHOUDBEHEER.boodschappen.bewaarsucces);
                    
                    MODAAL.voet(MODAAL.knop('verberg', 'secondary', 'x-square', INHOUDBEHEER.boodschappen.sluit));
                    MODAAL.grootte('modal-sm');
                    MODAAL.toon();
                }
    
            })
            .catch((err) => {
                console.log(console.log(err));
            })

    },
    verberg: () => {
        MODAAL.verberg();
    }
}
