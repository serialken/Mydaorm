/* ========================================================================
 * MRoad Hors Presse: hp.js v0.1
 * Hors Presse: Progamme central
 * ========================================================================
 * Author: Marc-Antoine Adélise
 * email: marcantoine.adelise@amaury.com
 * ======================================================================== */
(function () {
    console.log('JS de hors presse');
    window.horspresse = {
        fileSampleLimit : 10,
        urlModule: '/horspresse',
        currentState: {},
        lastState: {},
        userChoices: {},
        notifications: {},
        baseUrls: function (part) {
            switch (part) {
                case 'list':
                    return  window.backendApp.getBaseUrl(this.urlModule) + '/horspresse/index';
                    break;
                case 'add':
                    return  window.backendApp.getBaseUrl(this.urlModule) + '/horspresse/ajouter/'; // peut etre un doublon, mais conservée dans le doute
                    break;
                case 'new':
                    return  window.backendApp.getBaseUrl(this.urlModule) + '/horspresse/ajouter'; 
                    break;
                case 'fetchProducts4Company':
                    return  window.backendApp.getBaseUrl(this.urlModule) + '/admin/repartition/schemas/feedprodste';
                    break;
                case 'fetchAllProducts':
                    return  window.backendApp.getBaseUrl(this.urlModule) + '/produit/liste-tous-produits';
                    break;
                case 'check':
                    return  window.backendApp.getBaseUrl(this.urlModule) + '/horspresse/valider';
                    break;
                case 'fetchFlux':
                    return  window.backendApp.getBaseUrl(this.urlModule) + '/horspresse/listerflux';
                    break;
                case 'storeCampain':
                    return  window.backendApp.getBaseUrl(this.urlModule) + '/horspresse/sauvercampagne';
                    break;
                case 'uploadFile':
                    return  window.backendApp.getBaseUrl(this.urlModule) + '/horspresse/upload';
                    break;
            }
        },
        urls: {},
        initForm: {},
        fileFields:
                [
                    {
                        val: 'n/a',
                        txt: 'Non utilisé',
                        unique: false
                    },
                    {
                        val: 'vol1',
                        txt: 'Vol 1',
                        unique: true
                    },
                    {
                        val: 'vol2',
                        txt: 'Vol 2',
                        unique: true
                    },
                    {
                        val: 'vol3',
                        txt: 'Vol 3',
                        unique: true
                    },
                    {
                        val: 'vol4',
                        txt: 'Vol 4',
                        unique: true
                    },
                    {
                        val: 'vol5',
                        txt: 'Vol 5',
                        unique: true
                    },
                    {
                        val: 'cp',
                        txt: 'Code Postal',
                        unique: true
                    },
                    {
                        val: 'ville',
                        txt: 'Ville',
                        unique: true
                    },
                    {
                        val: 'type',
                        txt: 'Type de client',
                        unique: true
                    },
                    {
                        val: 'qte',
                        txt: 'Quantité',
                        unique: true
                    },
                    {
                        val: 'insee',
                        txt: 'INSEE',
                        unique: true
                    },
                    {
                        val: 'rsociale',
                        txt: 'Raison Sociale',
                        unique: true
                    },
                    {
                        val: 'nom',
                        txt: 'Nom',
                        unique: true
                    },
                    {
                        val: 'prenom',
                        txt: 'Prénom',
                        unique: true
                    },
                    {
                        val: 'civilite',
                        txt: 'Civilité',
                        unique: true
                    },
                    {
                        val: 'abo_id',
                        txt: 'ID Abonné',
                        unique: true
                    },
                    {
                        val: 'p2l_id',
                        txt: 'ID Point de livraison',
                        unique: true
                    },
                ],
        fileSelectedFields: [
        ],
        fileStructure: {
            status: 'empty',
            separator: null,
            headers: false,
            fields: [
            ],
        }
    };

    /**
     * Méthode de changement d'étape
     */
    window.horspresse.changeStep = function (sClassTargets, sDisplayTarget) {
        $(sClassTargets).fadeOut({
            duration: 750,
            done: function () {
                // On cache la div d'origine
                $(sClassTargets).removeClass('active');
                $(sClassTargets).addClass('toggleStep');
                $(sClassTargets).hide();

                // On affiche la nouvelle
                $(sDisplayTarget).addClass('active');
                $(sDisplayTarget).removeClass('toggleStep');
                $(sDisplayTarget).show();
            },
        });
    };
    
    /**
     * Méthode de remplissage du tableau d'échantillon de données (fichier client)
     */
    window.horspresse.feedSampleTable = function(tableTarget, oDatas){
        console.info('Remplissage du tableau');
        
        // Alimentation des en-tetêtes
        aHeaders = Object.keys(oDatas[0]);
        aHeaders.forEach(function(elem, idx){
            // Formatage des en-têtes AUTO N/Ax
            var bTest1 = /^Auto N\/A/.test(elem);
            var bTest2 = /^n\/a/.test(elem);
            
            if (bTest1 ||bTest2){
                var elemTxt = 'Non Util.';
            }
            else{
                var elemTxt = elem;
            }
            
            tableTarget.find('thead tr').append('<th align="center">'+elemTxt+'</th>');
        });
        
        // Ajout de l'échantillon
        for (var a=0; a < window.horspresse.fileSampleLimit; a++){
            var sLine ='<td>'+(a+1)+'</td>';
            aHeaders.forEach(function(elem, idx){
                sLine+= '<td>'+oDatas[a][elem]+'</td>';
            });
            tableTarget.find('tbody').append('<tr>'+sLine+'</tr>');
        }
    };
    
    /**
     * Réinitialise le tableau affichant l'échantillon de données
     */
    window.horspresse.resetSampleTable = function(tableTarget){
        console.info('Reset du tableau');
        var sTable = '<table border="1" width="100%"><thead><tr><th></th></tr></thead><tbody></tbody></table>';
        tableTarget.html(sTable);
    };
}());
