$(() => {
    if ($('#beheerIndex').length) BEHEERINDEX.init();
});

const BEHEERINDEX = {
    csrfToken: $('meta[name="csrf-token"]').attr('content'),
    instellingen: [],
    init: () => {
        BEHEERINDEX.getInstellingen();

        $('#bewaarInstellingen').on("click", function (evt) { BEHEERINDEX.bewaarInstellingen()} );
    },

    getInstellingen: () => {
        fetch('/jxGetInstellingen', {
            method: 'post',
            body: new FormData(),

            headers: {
                "X-CSRF-Token": BEHEERINDEX.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then((response) => {
            return response.json()
        }).then((res) => {
            if (res.succes) {
                let rte = new Quill(
                    '#editor',
                    {
                        theme: 'snow',
                        modules: {
                            toolbar: [
                                [{'header': [1,2,3,false]}],
                                [{'align':[]}],
                                ['bold', 'italic', 'underline'],
                                [{'list': 'bullet'}, {'list': 'ordered'}],
                                [{'indent': '-1'}, {'indent': '+1'}],
                                [{'color':[]}, {'background':[]}],
                                ['image'],
                                ['clean']
                            ]
                        }
                    }
                );
                let instellingen = JSON.stringify(res.instellingen,null,2);
                
                rte.setText(instellingen);  
        
            };

           

        }).catch((error) => {
            console.log(error);
        });
    },

    bewaarInstellingen:() => {
        var quill = new Quill('#editor');
        const delta = quill.getText();
        let frmDta = { instellingen: delta };

        fetch('/jxSetInstellingen', {
            method: 'post',
            body: JSON.stringify(frmDta),

            headers: {
                'X-CSRF-Token': BEHEERINDEX.csrfToken,
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
    },
    

}


