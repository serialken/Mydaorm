/* 
 * Fonctions liées au schéma de répartition
 * @author maadelise Marc-Antoine Adélise
 */

/**
 * Chargement initial
 */
function loadRepartSchemas() {
    $('form#firstStepSelect .btn-primary').on('click', function() {
        console.log('FIRST STEP');
        // RAZ des éléments déjà récupérés
        $('.panneauReglesGlobales div.listeRegles').empty();
        $('#repartProd .exceptionFlux').remove();
        $('#repartSoc .exceptionFlux').remove();

        var exceptionsFeedUrl = $(this).attr("data-feed-exception-url");
        window.delExcProdUrl = $(this).attr("data-del-excep-url");

        // Récupération des valeurs du formulaire
        var dptVal = $('form#firstStepSelect #ams_silogbundle_repartitiontype_dpt').val();
        window.dptVal = dptVal;
        window.societeVal = $('form#firstStepSelect #ams_silogbundle_repartitiontype_societe').val();
        window.societeNom = $('form#firstStepSelect #ams_silogbundle_repartitiontype_societe option:selected').text();
        var fluxVal = $('form#firstStepSelect #ams_silogbundle_repartitiontype_flux').val();
        var fluxNom = $('form#firstStepSelect #ams_silogbundle_repartitiontype_flux option:selected').text();
        window.fluxId = fluxVal;
        window.fluxNom = fluxNom;

        /** UPDATE URL XLS**/
        var str = $('.generate_xls').attr('href');
        var aStr = str.split('/');
        var res = str.replace(aStr.slice(-1)[0], window.societeVal);
        $('.generate_xls').attr('href', res);

        // Test sur les valeurs nécessaires à la validation du formulaire
        if (!dptVal || !window.societeVal) {
            alert('Merci de renseigner le formulaire');
        }
        else {
            var exceptFeedUrl = exceptionsFeedUrl + dptVal + '/' + window.societeVal;
            if (fluxVal) {
                exceptFeedUrl += '/' + fluxVal
            }

            // Appel à la méthode de Feed JSON en AJAX pour récupérer les exceptions
            $.ajax({
                url: exceptFeedUrl,
                dataType: "json"
            }).done(function(data) {

                console.log(data);
                window.exceptionsProd = [];
                // Initialisation du tableau d'exceptions produits
                if (Object.keys(data.exceptions.produit).length > 0) {
                    for (flux in data.exceptions.produit) {
                        for (prod in data.exceptions.produit[flux]) {
                            window.exceptionsProd.push(data.exceptions.produit[flux][prod]);
                        }
                    }
                }
                console.log('windows exceptions prod', window.exceptionsProd);
                window.exceptionsSoc = data.exceptions.societe;
                window.infoSte = data.societe;

                // Informations sur la société
                if (data.societe) {
                    $('div.rep_schema_ste').show();
                    $('div.rep_schema_ste img').attr('src', data.societe.logosrc);
                    $('div.rep_schema_ste span').html(data.societe.rsociale);
                    $('div.panneauReglesSte span.labelNomSte').html(data.societe.rsociale);
                    $('div.panneauReglesProd span.labelNomSte').html(data.societe.rsociale);
                }
                else {
                    $('div.rep_schema_ste').hide();
                }

                $('.panneauReglesProd').show();
                $('.panneauReglesSte').show();

                $('#repartSoc .backToTop').remove();
                $('#repartProd .backToTop').remove();
                $('.prodSep').remove();

                // Exceptions
                // ID uniques de produit/flux
                window.aProdFluxUniq = [];

                // Produit
                // RAZ du sélecteur
                $('#prodSelect').empty();
                $('#prodSelect').html('<option value="">Choisir un produit...</option>');

                $('.exceptionProd').remove();

                // Désactivation du bouton de répartition sur un nouveau produit si les exceptions de produits couvrent tous les produits
                setProdIdUniqArray(data.exceptions.produit);

                // Bouton de nouvelle répartition ON ou OFF?
                if (window.aProdIdUniq.length >= window.infoSte.nbProds) {
                    $('#repProdAddBtn').attr('disabled', 'disabled');
                }
                else {
                    $('#repProdAddBtn').removeAttr('disabled');
                }

                if (data.exceptions.produit.length <= 0) {
                    $('.panneauReglesProd .listeRegles .alert').show();
                    $('#prodSelect').attr('disabled', 'disabled');
                }
                else {
                    $('.panneauReglesProd .listeRegles .alert').hide();
                    $('#prodSelect').removeAttr('disabled');

                    for (fluxExcept in data.exceptions.produit) { // Boucle sur les flux
                        console.log("Flux " + fluxExcept);
                        // Ajout du flux à la liste
                        var divFluxId = 'prodFlux' + fluxExcept;
                        if (window.aProdFluxUniq.lastIndexOf(divFluxId) == -1) {
                            $('div.panneauReglesProd div.listeRegles').append('<div class="exceptionFlux" id="' + divFluxId + '"><p><h3>Flux de ' + fluxExcept + '</h3></p></div>');
                            window.aProdFluxUniq.push(divFluxId);
                        }

                        for (regleProd in data.exceptions.produit[fluxExcept]) { // Boucle sur les produits
                            var aRegleProd = data.exceptions.produit[fluxExcept][regleProd];
                            var depotsProdTrouves = [];
                            console.log(aRegleProd);

                            // Ajout du produit à la liste
                            var idDivDepot = 'prod_' + fluxExcept + '_' + aRegleProd.info.id;
                            $('div.panneauReglesProd div.listeRegles #' + divFluxId)
                                    .append('<div id="' + idDivDepot + '" class="exceptionProd"><h4 class="labelNomProduit">'
                                            + aRegleProd.info.nom_produit + ' <small>- '
                                            + aRegleProd.info.code + '</small></h4><div class="repartEditBtn" align="left"><button data-prod-id="'
                                            + aRegleProd.info.id + '" data-prod-nom="'
                                            + aRegleProd.info.nom_produit
                                            + '" type="button" class="btn btn-warning btn-xs editProd">Nouvelle répartition du produit.</button></div></div>')
                                    .append('<div class="backToTop"><a class="scroll" href="#repartProd"><span aria-hidden="true" class="glyphicon glyphicon-chevron-up"></span>Retour à la répartition par produit</a></div>');

                            // Ajout du produit au Select pour la navigation
                            $('#prodSelect').append($('<option>', {value: 'prod_' + fluxExcept + '_' + aRegleProd.info.id, text: aRegleProd.info.nom_produit + ' (' + fluxExcept + ')'}));

                            // Parcours des exceptions de ce produit
                            for (excProd in aRegleProd) { // Boucle sur les INSEEs
                                if (excProd != 'info') {
                                    var aExceptionP = aRegleProd[excProd];

                                    var idDivCibleDepot = 'div.panneauReglesProd div.listeRegles div#' + idDivDepot;
                                    var idProdDepotDiv = 'except_prod_depot_' + fluxExcept + '_' + aRegleProd.info.id + '_' + aExceptionP.depot_id;

                                    if ($.inArray(Math.abs(aExceptionP.depot_id), depotsProdTrouves) == -1) {
                                        console.log('nouveau dépot !! ' + Math.abs(aExceptionP.depot_id));
                                        depotsProdTrouves.push(Math.abs(aExceptionP.depot_id));

                                        // Ajout du séparateur
                                        if (excProd > 0) {
                                            $(idDivCibleDepot).append('<hr/>');
                                        }

                                        // On ajoute le dépot à la liste
                                        $(idDivCibleDepot)
                                                .append('<div class="row" id="' + idProdDepotDiv
                                                        + '"><div class="col-md-2"><div class="labelNomDepot">'
                                                        + aExceptionP.depot_nom + '</div><div>'
                                                        + aExceptionP.adresse + '</div></div></div>');


                                        $('div.panneauReglesProd div.listeRegles div#' + idProdDepotDiv)
                                                .append('<div class="col-md-10 labelsContainer"><span class="label label-primary">'
                                                        + aExceptionP.insee + ' '
                                                        + aExceptionP.ville
                                                        + ' (' + aExceptionP.cp
                                                        + ')</span></div>');

                                        $('div.panneauReglesProd div.listeRegles div#' + idProdDepotDiv)
                                                .append('<div class="delBtn"><button type="button" class="btn btn-danger btn-xs" data-prod_depot_flux="'
                                                        + aRegleProd.info.id + '_'
                                                        + aExceptionP.depot_id + '_'
                                                        + aExceptionP.flux_id
                                                        + '">Supprimer la répartition sur '
                                                        + aExceptionP.depot_nom + '</button></div>');
                                    }
                                    else {
                                        console.log('dépot déjà trouvé');
                                        // Le dépot a déjà été listé pour ce produit
                                        $('div.panneauReglesProd div.listeRegles div#' + idProdDepotDiv + ' div.labelsContainer')
                                                .append('<span class="label label-primary">'
                                                        + aExceptionP.insee
                                                        + ' ' + aExceptionP.ville
                                                        + ' (' + aExceptionP.cp + ')</span>');
                                    }
                                }
                            }

                            // Séparateur de produits
                            var aProdKeys = Object.keys(data.exceptions.produit[fluxExcept]);
                            if (regleProd != aProdKeys[aProdKeys.length - 1]) {
                                $('div.panneauReglesProd div.listeRegles .exceptionFlux').append('<hr class="prodSep"/>');
                            }
                        }

                        // Séparateur de flux
                        var aFluxKeys = Object.keys(data.exceptions['produit']);
                        if (fluxExcept != aFluxKeys[aFluxKeys.length - 1]) {
                            $('div.panneauReglesProd div.listeRegles').append('<hr class="fluxSep"/>');
                        }
                    }
                }

                // Société
                $('div.exceptionSte').remove();
                if (data.exceptions.societe.length <= 0 && data.exceptions.societe.length <= 0) {
                    $('.panneauReglesSte .listeRegles .alert').show();

                    $('.exceptionSte').remove();
                    $('#socSelect').attr('disabled', 'disabled');
                    $('#socSelect').html('<option value="">Choisir un dépôt...</option>');
                    $('#repartSoc .listeRegles hr').remove();
                }
                else {
                    $('.panneauReglesSte .listeRegles .alert').hide();
                    $('#socSelect').removeAttr('disabled');

                    var depotsSteTrouves = [];

              

                    for (regleSte in data.exceptions.societe) {
                        var aRegleSte = data.exceptions.societe[regleSte];
                        if ($.inArray(Math.abs(aRegleSte.depot_id), depotsSteTrouves) == -1) {
                            depotsSteTrouves.push(Math.abs(aRegleSte.depot_id));

                            // Ajout du séparateur
                            if (regleSte.length > 0) {
                                $('div.panneauReglesSte div.listeRegles').append('<hr/>');
                                   //  ajout  modifier societe tidiane
                                $('div.panneauReglesSte div.listeRegles').append('<div class="repDepotEditBtn" align="left"><button data-ste-id="' + window.societeVal + '"data-depot-id="' + aRegleSte.depot_id + '" type="button" class="btn btn-warning btn-xs editSoc"> Nouvelle répartition de la société.' + window.societeVal+ '/ '+aRegleSte.depot_id +'</button></div>');
                            }
                            // On ajoute le dépot à la liste
                            $('div.panneauReglesSte div.listeRegles')
                                    .append('<div class="row exceptionSte" id="except_ste_depot_' + aRegleSte.depot_id + '"><div class="col-md-2"><div class="labelNomDepot">' + aRegleSte.depot_nom + '</div><div>' + aRegleSte.adresse + '</div></div></div><div class="backToTop"><a class="scroll" href="#repartSoc"><span aria-hidden="true" class="glyphicon glyphicon-chevron-up"></span>Retour à la répartition pour la société</a></div>');
                            $('div.panneauReglesSte div.listeRegles div#except_ste_depot_' + aRegleSte.depot_id).append('<div class="col-md-10 labelsContainer"><span class="label label-primary">' + aRegleSte.insee + ' ' + aRegleSte.ville + ' (' + aRegleSte.cp + ')</span></div>');
                            $('div.panneauReglesSte div.listeRegles div#except_ste_depot_' + aRegleSte.depot_id).append('<div class="delBtn"><button type="button" class="btn btn-danger" data-ste_depot="' + aRegleSte.depot_id + '">Supprimer la répartition sur ' + aRegleSte.depot_nom + '</button></div>');
                           
                                 
                            // Ajout du dépôt au Select pour la navigation
                            $('#socSelect').append($('<option>', {value: 'except_ste_depot_' + aRegleSte.depot_id, text: aRegleSte.depot_nom}));
                        }
                        else {
                            // Le dépot a déjà été listé
                            $('div.panneauReglesSte div.listeRegles div#except_ste_depot_' + aRegleSte.depot_id + ' div.labelsContainer').append('<span class="label label-primary">' + aRegleSte.insee + ' ' + aRegleSte.ville + ' (' + aRegleSte.cp + ')</span>');
                        }
                    }
                }
            });



    // Gestion de la suppression d'une exception produit
    $('div.delBtn button.btn').live('click', function() {
        console.log('suppression...');

        // Ouverture de la boite de dialogue
        var popUpElem = $('div#amsModalBody');
        // Sauvegarde du contenu original de la modale
        window.modalOrigContent = popUpElem.html();
        var popTitle = "Confirmez-vous la suppression ?";
        var popContent = "Voulez-vous vraiment supprimer cette exception ?<br/><strong>Cette action est irreversible.</strong>";

        var popOpenFunc = function() {
            $(popUpElem).closest('div.modal').modal('show');
        };
        var prepPopUpFunc = function() {
            // Affichage du titre
            popUpElem.parents().find('.modal-header .modal-title').html(popTitle);
            // Affichage des boutons de confirmation ou de suppression
            var modContainer = popUpElem.closest('.modal-content');
            var footerContent = '<div class="modal-footer repartprod">'
                    + '<button type="button" class="btn btn-xs btn-default" data-dismiss="modal">Non, annuler</button>'
                    + '<button type="button" class="btn btn-xs btn-warning confirmBtn">Oui, supprimer l\'exception</button></div>';

            if (modContainer.find('div.modal-footer').length > 0) {
                modContainer.find('div.modal-footer').remove();
            }
            modContainer.append(footerContent);

            // On pose les évènements sur les boutons
            modContainer.find('button.confirmBtn').on('click', function() {
                console.log('confirmation');
                flux_id = $('select#ams_silogbundle_repartitiontype_flux').val()
                // Appel à l'AJAX pour la suppression dans la BDD
                var postData = {
                    url: delExcProdUrl,
                    type: "POST",
                    data: {
                        'dpt': window.dptVal,
                        'ste': window.societeVal,
                        'fluxId': flux_id,
                        'depId': depot_id
                    },
                    dataType: "json"
                }

                if (delType == 'societe') {
                    postData.data.socId = window.societeVal;
                }
                else {
                    postData.data.prodId = prod_id;
                }

                console.log("données postées:");
                console.log(postData);

                $.ajax(postData).done(function(data) {
                    console.log("retour...");
                    console.log(data);
                    if (data.codeRetour == 'ok') {
                        $(popUpElem).closest('div.modal').modal('hide');
                        // Affichage de la notification
                        displayNotification('success', 'Suppression d\'exception', data.msgRetour, 4000);

                        // Suppression de la zone dans la page
                        removeItem(data.datas.item_id, data.datas.type, {depot_id: data.datas.depot_id, flux_id: data.datas.flux_id, flux_nom: data.datas.flux_nom});
                        var removeObj = {
                            'prod_id': prod_id,
                            'flux_id': flux_id,
                            'depot_id': depot_id,
                            'type': 'produit'
                        };
                        // Suppression du tableau
                        removeRepartProd(removeObj, window.exceptionsProd);

                        // Suppression de l'entête de bloc du flux si plus aucune exception ne correspond
                        var blocFluxId = '#prodFlux' + data.datas.flux_nom;
                        var nExceptRest = $(blocFluxId + ' div.exceptionProd').length;
                        if (nExceptRest == 0) {
                            console.log('On supprime du DOM le bloc ' + blocFluxId);
                            $(blocFluxId).remove();
                        }

                        // Désactivation du bouton de répartition sur un nouveau produit si les exceptions de produits couvrent tous les produits
                        if (data.datas.exceptions_produits.length >= window.infoSte.nbProds) {
                            console.log('desactivation 1');
                            $('#repProdAddBtn').attr('disabled', 'disabled');
                        }
                        else {
                            // Ré-alimentation de la liste des produits
                            window.aProdIdUniq.splice(window.aProdIdUniq.indexOf(prod_id), 1);

                            $('#repProdAddBtn').removeAttr('disabled');
                        }
                    }
                    else {
                        $(popUpElem).closest('div.modal').modal('hide');
                        // Affichage de la notification
                        displayNotification('error', 'Suppression d\'exception', data.msgErr, 4000);
                    }
                });

            });

            // Fermeture de la modale
            $(popUpElem).closest('div.modal').on('hidden.bs.modal', function() {
                console.log('modale fermée.');
                $(this).unbind();
                popUpElem.html(window.modalOrigContent); // Contenu d'origine
                // Suppression du footer
                popUpElem.next('button.confirmBtn').unbind();
                popUpElem.next('.repartprod').remove();
            });
        };

        openDialogBox(popUpElem, popContent, prepPopUpFunc, popOpenFunc);

        // Récupération du type
        if ($(this).parent().parent().hasClass('exceptionSte')) {
            var delType = 'societe';
            var depot_id = $(this).data('ste_depot');
            console.log('ste, dépot:', depot_id);
        }
        else {
            var delType = 'produit';
            var aInfo = $(this).data('prod_depot_flux').split('_');
            var prod_id = aInfo[0];
            var depot_id = aInfo[1];
            var flux_id = aInfo[2];
            console.log('prod ' + prod_id);
            console.log('depot ' + depot_id);
            console.log('flux ' + flux_id);
        }

        console.log(window.delExcProdUrl);
        console.log('dpt ' + window.dptVal);
        console.log('société ID ' + window.societeVal);
    });

    /**
     * Méthode de suppression complète d'un item de la page de la répartition
     * tout en englobant les éléments liés (liens, séparateurs...)
     * @param {int} itemId L'id numérique de l'item à supprimer
     * @param {string} type Le type d'exception à supprimer (produit|societe)
     * @param {object} info Un objet contenant des informations complémentaires
     */
    function removeItem(itemId, type, info) {
        switch (type) {
            case 'produit':
                // Combien de dépots concernés par ce produit (pour ce flux) ?
                var n = $('div#prod_' + info.flux_nom + '_' + itemId + ' div.labelNomDepot').length;
                if (n <= 1) {
                    // Plus d'exceptions concernant ce produit
                    $('div#prod_' + info.flux_nom + '_' + itemId).fadeOut();
                    $('div#prod_' + info.flux_nom + '_' + itemId).next('.backToTop').remove();
                    $('div#prod_' + info.flux_nom + '_' + itemId).next('hr.prodSep').remove();
                    $('div#prod_' + info.flux_nom + '_' + itemId).remove();
                    $('#prodSelect option[value=prod_' + info.flux_nom + '_' + itemId + ']').remove();

                    // Reste t-il un produit sur ce flux ?
                    // @TODO Supprimer le bloc flux quand plus d'exception
                }
                else {
                    // Au moins un autre dépôt pour ce produit
                    console.log('autre dépot que ' + info.depot_id);
                    console.log($('div#except_prod_depot_' + itemId + '_' + info.depot_id));
                    $('div#except_prod_depot_' + info.flux_nom + '_' + itemId + '_' + info.depot_id).prev('hr').remove();
                    $('div#except_prod_depot_' + info.flux_nom + '_' + itemId + '_' + info.depot_id).remove();
                }
                break;
            case 'societe':
                console.log('sup ' + 'div#except_ste_depot_' + itemId);
                $('div#except_ste_depot_' + itemId).fadeOut();
                $('div#except_ste_depot_' + itemId).next('.backToTop').remove();
                $('div#except_ste_depot_' + itemId).prev('hr').remove();
                $('div#except_ste_depot_' + itemId).remove();
                $('#socSelect option[value=except_ste_depot_' + itemId + ']').remove();
                break;
        }

        resetExceptBloc(type, 0);
    }

    // Redirection vers l'ancre à l'aide du sélecteur de produits
    $('.navSelect').change(function() {
        console.log($(this));
        if ($(this).val()) {
            var target = '#' + $(this).val();
            var $target = $(target);

            $('html, body').stop().animate({
                'scrollTop': $target.offset().top
            }, 900, 'swing', function() {
                window.location.hash = target;
            });
        }
    });

    // Ouverture de la modale pour la création des exceptions de produit
    $('#repProdAddBtn').on('click', function() {
        var infoObj = {
            dpt: window.dptVal,
            ste_nom: window.societeNom,
            ste_id: window.societeVal,
            flux_id: window.fluxId,
            flux_nom: window.fluxNom,
            mode: 'crea'
        };
        openRepExcepBox('produit', infoObj);
    });

    $('#repDepotAddBtn').on('click', function() {
        var infoObj = {
            dpt: window.dptVal,
            ste_nom: window.societeNom,
            ste_id: window.societeVal,
            flux_id: window.fluxId,
            flux_nom: window.fluxNom,
            mode: 'crea'
        };
        openRepExcepBox('depot', infoObj);
    });

    $(document).delegate('.panneauReglesProd a.alert-link', 'click', function() {
        $('#repProdAddBtn').trigger('click');
    });

    // Ouverture de la modale pour la modification des exceptions de produit
    // div.repartEditBtn button.btn.btn-warning.btn-xs
    $(document).delegate('.editProd', 'click', function() {
        var infoObj = {
            dpt: window.dptVal,
            ste_nom: window.societeNom,
            ste_id: window.societeVal,
            flux_id: window.fluxId,
            flux_nom: window.fluxNom,
            prod_id: $(this).data('prodId'),
            prod_nom: $(this).data('prodNom'),
            mode: 'edit'
        };
        console.log('obj conf');
        console.log(infoObj);
        openRepExcepBox('produit', infoObj);
    });

    // edit bouton societe tidiane
    $(document).delegate('.editSoc', 'click', function() {
        var infoObj = {
            dpt: window.dptVal,
            ste_nom: window.societeNom,
            ste_id: window.societeVal,
            flux_id: window.fluxId,
            flux_nom: window.fluxNom,
            mode: 'edit'
        };
        openRepExcepBox('depot', infoObj);
    });


}

// Ajout/Modification des exceptions
function openRepExcepBox(type, info) {
    console.log(type, info);

    // ouverture de la modale
    if (info.mode == 'edit') {
        // Mode édition
        var popTitle = "Modifier la répartition de " + info.prod_nom;
    }
    else {
        var popTitle = "Ajouter une nouvelle répartition de produit";
    }
    

    var popUpElem = $('div#amsModalBody');
    var popContent = '<strong>Utilisez le formulaire ci-dessous pour répartir un produit de la société <span class="nomSociete">' + info.ste_nom + '</span> pour:</strong><br/>';
    popContent += '<ul class="modalRecapRepartInfo"><li>Le département <span class="nomDpt">' + info.dpt + '</span></li></ul>';
    
    if(type=='produit'){
        popContent += '<div><table align="center" id="selectsTbl"><tr><td>Produit:</td>\n\
            <td><select id="exceptProdSelect"><option value="">Choisir un produit</option></select></td></tr>\n\
            <tr><td>Dépôt:</td><td><select id="exceptDepotSelect"><option value="">Choisir un dépôt</option></select></td></tr>\n\
            <tr><td>Flux:</td><td><select id="exceptFluxSelect"><option value="">Choisir un flux</option></select></td></tr>\n\
        </table></div>';
    }else{ //societe
          popContent += '\
            <tr><td>Dépôt:</td><td><select id="exceptDepotSelect"><option value="">Choisir un dépôt</option></select></td></tr>\n\
            <tr><td>Flux:</td><td><select id="exceptFluxSelect"><option value="">Choisir un flux</option></select></td></tr>\n\
        </table></div>';
    }

    
    
    popContent += '<div class="row"><div class="col-md-4 multipleContainer"><ul id="editDiagMult" class="exceptionLists"></ul></div><div class="col-md-8 listeContainer"><ul id="ExceptionTarget" class="exceptionLists produits"></ul></div></div>';

    // Alimentation des options des sélecteurs
    // Les produits de la société
    $.ajax({
        url: $('#ams_silogbundle_repartitiontype_save').data('feedProduitsUrl'),
        type: "GET",
        dataType: "json",
        data: {
            ste: info.ste_id
        },
        success: function(data) {
            var prodMapSelect = {val: 'id', label: 'libelle'};
            var prodSelectElem = '#exceptProdSelect';

            // Filtrage des produits ayant déjà au moins une exception
            if (info.mode == 'crea') {
                for (p in window.aProdIdUniq) { // p -> ID du produit
                    for (prod in data.produits) {
                        if (data.produits[prod].id == window.aProdIdUniq[p]) {
                            // Suppression du produit de la liste
                            data.produits.splice(prod, 1);
                        }
                    }
                }
            }

            pushOptionsToElem(prodSelectElem, '<option>', data.produits, prodMapSelect);
            console.log('data.produits:', data.produits);

            // Sélection du bon produit dans le select
            if (info.prod_id)
                $(prodSelectElem + ' option[value="' + info.prod_id + '"]').attr('selected', 'selected');
        },
        error: function(xhr, ajaxOptions, thrownError) {
            // Affichage de la notification
            displayNotification('error', 'Erreur fatale', 'La liste des produits n\'a pas pu être récupérée. Veuillez ré-essayer.', 4000);
        }
    }
            );

    // Les dépôts
    $.ajax({
        url: $('#ams_silogbundle_repartitiontype_save').data('feedDepotsUrl'),
        type: "GET",
        dataType: "json",
        data: {},
        success: function(data) {
            var depotsMapSelect = {val: 'id', label: 'libelle'};
            pushOptionsToElem('#exceptDepotSelect', '<option>', data.depots, depotsMapSelect);
        },
        error: function(xhr, ajaxOptions, thrownError) {
            // Affichage de la notification
            displayNotification('error', 'Erreur fatale', 'La liste des dépôts n\'a pas pu être récupérée. Veuillez ré-essayer.', 4000);
        }
    });

    // Les flux
    console.log('URL flux: ' + $('#ams_silogbundle_repartitiontype_save').data('feedFluxUrl'));
    $.ajax({
        url: $('#ams_silogbundle_repartitiontype_save').data('feedFluxUrl'),
        type: "GET",
        dataType: "json",
        data: {},
        success: function(data) {
            var fluxMapSelect = {val: 'id', label: 'libelle'};
            pushOptionsToElem('#exceptFluxSelect', '<option>', data.flux, fluxMapSelect);
        },
        error: function(xhr, ajaxOptions, thrownError) {
            // Affichage de la notification
            displayNotification('error', 'Erreur fatale', 'La liste des flux n\'a pas pu être récupérée. Veuillez ré-essayer.', 4000);
        }
    });

    var popEditDiagFunc = function() {
        // Reset du marqueur de modification pour rechargement
        window.needReload = false;

        $(popUpElem).closest('div.modal').modal('show');
        if ($('div.modal-footer.editRepart').length == 0) {
            var modalWidth = $(popUpElem).closest('div.ams-modal').width();
            $(popUpElem).closest('div.ams-modal').width(modalWidth * 1.25);


            // Affichage des boutons dans le footer
            console.log('footer vide');
            var footerContent = '<div class="modal-footer editRepart">'
                    + '<button type="button" class="btn btn-xs btn-primary confirmBtn" dizsabled="disabled">Enregistrer</button> ou '
                    + '<button type="button" class="btn btn-xs btn-default" data-dismiss="modal">Fermer</button></div>';
            popUpElem.parent().append(footerContent);

            // Vérouillage du sélecteur de produit
            if (info.prod_nom) {
                $('#exceptProdSelect').attr('disabled', 'disabled');
            }

            // Mise en place de la vérification de formulaire
            $('div.editRepart button.confirmBtn').on('click', function () {
                console.log('click bouton');
                var fieldsToCheck = {
                    depot_fld: {
                        selector: 'select#exceptDepotSelect',
                        check: 'non_empty_select',
                        errMsg: 'Merci de sélectionner un dépôt.'
                    },
                    flux_fld: {
                        selector: 'select#exceptFluxSelect',
                        check: 'non_empty_select',
                        errMsg: 'Merci de sélectionner un flux.'
                    },
                    exceptions_fld: {
                        selector: 'ul#ExceptionTarget',
                        check: 'non_empty_list',
                        errMsg: 'Merci de sélectionner au moins un code INSEE.',
                        sortableKey: 'editDiagMult'
                    },
                };


                if (!$('table#selectsTbl').find("tr:first").hasClass('hide')) {
                    var a = {produit_fld: {
                            selector: 'select#exceptProdSelect',
                            check: 'non_empty_select',
                            errMsg: 'Merci de sélectionner un produit.'
                        }
                    };
                    var fieldsToCheck=$.extend(fieldsToCheck,a);
                }
                var errMsgAlertFn = function (msg) {
                    displayNotification('error', 'Erreur détectée', msg, 3000);
                };

                var successFn = function () {
                    displayNotification('info', 'Formulaire validé', 'Informations en cours d\'envoi, veuillez patienter...', 2500);

                    // Récupération des valeurs
                    var produitId = null;
                    if (!$('table#selectsTbl').find("tr:first").hasClass('hide')) {
                         produitId = $(fieldsToCheck.produit_fld.selector).val();
                    }
                    var depotId = $(fieldsToCheck.depot_fld.selector).val();
                    var fluxId = $(fieldsToCheck.flux_fld.selector).val();

                    console.log('Envoi du produit ' + produitId + ' et du dépot ' + depotId + ' pour le flux ' + fluxId);
                    var inseeValues = $(fieldsToCheck.exceptions_fld.selector).sortable("toArray");
                    console.log(inseeValues);

                    // Envoi des informations du formulaire
                    $.ajax({
                        url: $('#ams_silogbundle_repartitiontype_save').data('storeExcepUrl'),
                        type: "POST",
                        dataType: "json",
                        data: {
                            dpt: window.dptVal,
                            steId: window.societeVal,
                            fluxId: fluxId,
                            prodId: produitId,
                            depotId: depotId,
                            insees: inseeValues
                        },
                        success: function(data) {
                            if (data.codeRetour == 'ok') {
                                displayNotification('success', 'Exception(s) enregistrée(s)', 'Enregistrement des informations effectué avec succès.', 4000);
                                window.needReload = true;
                            }
                            else {
                                displayNotification('error', 'Erreur fatale', 'Erreur(s) rencontrée(s) lors de l\'enregistrement. Veuillez ré-essayer.', 5000);
                            }

                        },
                        error: function(xhr, ajaxOptions, thrownError) {
                            // Affichage de la notification
                            displayNotification('error', 'Erreur fatale', 'Erreur lors de l\'enregistrement des informations. Veuillez contacter le support MRoad.', 5000);
                        }
                    });
                };

                checkBeforeSubmitActivation(fieldsToCheck, errMsgAlertFn, successFn);
            });
        }

        // récupération de la liste des insees dispos et utilisés en Ajax
        $.ajax({
            url: $('#repProdAddBtn').data('feedListInseeProd'),
            type: "GET",
            dataType: "json",
            data: {
                dpt: info.dpt,
                fluxId: info.flux_id,
                prodId: info.prod_id
            },
            success: function(data) {
                // Ajout des items dans le sélect multiple
                var listeItems = data.insees_dispos;

                // Suppression de la liste des Insees de tous ceux qui font déjà partie d'une exception
                // Récupération des insees qui ne sont plus disponibles
                if (window.exceptionsProd[info.prod_id]) {
                    console.log(listeItems);
                    for (a in window.exceptionsProd[info.prod_id]) {

                        if (window.exceptionsProd[info.prod_id][a].commune_id) {
                            // Suppression de l'INSEE dans la liste de base
                            var communeId = window.exceptionsProd[info.prod_id][a].commune_id;

                            for (item in listeItems) {
                                if (listeItems[item].id == communeId) {
                                    delete listeItems[item];
                                }
                            }
                        }
                    }
                }

                console.log(listeItems);

                // Fonction de formatage appliquée avant l'insertion des données dans le select
                var formatFunction = function() {
                    for (i in arguments[0]) {
                        arguments[0][i]['libelle'] = arguments[0][i]['insee'] + ' ' + arguments[0][i]['libelle'] + ' (' + arguments[0][i]['cp'] + ')'
                    }
                }
                pushOptionsToElem('#editDiagMult', '<li|sortable>', listeItems, {val: 'commune_id', label: 'libelle', sortableId: ''}, formatFunction);

                $("#editDiagMult, #ExceptionTarget").sortable({
                    items: "li:not(.non-sortable)",
                    connectWith: ".exceptionLists"
                }).disableSelection();
            }
        });
    };

    var prepEditDiagFunc = function() {
        console.log('preparation');
        console.log(info);
        // Affichage du titre
        popUpElem.parents().find('.modal-header .modal-title').html(popTitle);

        // Fermeture de la modale
        $(popUpElem).closest('div.modal').on('hidden.bs.modal', function() {
            console.log('modale ajout/edition fermée.');
            $(this).unbind();
            // Rechargement des informations si besoin
            if (window.needReload) {
                displayNotification('info', 'Rechargement en cours...', 'Rafraichissement des données pour prendre en compte vos modifications.');
                $('#ams_silogbundle_repartitiontype_save').trigger('click');
            }
        });
    };

    openDialogBox(popUpElem, popContent, prepEditDiagFunc, popEditDiagFunc);

    if (type == "depot") {
        $('table#selectsTbl').find("tr:first").addClass('hide');
    } else {
        $('table#selectsTbl').find("tr:first").removeClass('hide');
    }

    // Fonction de vérification des infos avant activation du bouton "valider"
    function checkBeforeSubmitActivation(fieldsToCheck, errFn, successFn) {
        var nbErr = 0;

        for (check in fieldsToCheck) {
            var bErrFound = false;
            switch (fieldsToCheck[check]['check']) {
                case 'non_empty_select':
                    if ($(fieldsToCheck[check]['selector']).val().length == 0) {
                        nbErr++;
                        bErrFound = true;
                    }
                    break;
                case 'non_empty_list':
                    if ($(fieldsToCheck[check]['selector']).find('li').length == 0) {
                        nbErr++;
                        bErrFound = true;
                    }
                    break;
            }

            if (bErrFound) {
                var errMsg = fieldsToCheck[check]['errMsg'];
                // Affichage du message d'erreur
                if (errFn) {
                    errFn(errMsg);
                }
            }
        }

        if (nbErr == 0 && successFn) {
            successFn();
        }
    }
}

// Chargement des informations à chaque changement de dépôt.
$(document).delegate('#exceptDepotSelect', 'change', function() {
    var depotVal = $(this).find('option:selected').val();
    var fluxId = $('#exceptFluxSelect option:selected').val();
    if (typeof info != 'undefined') {
        var prodId = info.prod_id;
    }
    else {
        var prodId = $('#exceptProdSelect option:selected').val();
    }
    


    if (depotVal > 0 && prodId && fluxId > 0) {
        // Récupération des insees
        $.ajax({
            url: $('#ams_silogbundle_repartitiontype_save').data('getProdExcepUrl'),
            type: "GET",
            dataType: "json",
            data: {
                prodId: prodId,
                steId: window.societeVal,
                dpt: window.dptVal,
                depotId: depotVal,
                fluxId: fluxId
            },
            success: function(data) {
                // Reset des listes
                $("ul#ExceptionTarget").empty();

                if (data.insees.length > 0) {

                    // Fonction de formatage appliquée avant l'insertion des données dans le select
                    var formatFunction = function() {
                        for (i in arguments[0]) {
                            // On masque le LI correspondant dans la liste des INSEEs disponibles
                            arguments[0][i]['libelle'] = arguments[0][i]['insee'] + ' ' + arguments[0][i]['ville'] + ' (' + arguments[0][i]['cp'] + ')'
                        }
                    }
                }
                // Suppression des insees pris dans d'autres flux
                if (Object.keys(data.inseesAutresFlux).length > 0) {
                    var aListeAutresInsees = [];
                    for (var z in data.inseesAutresFlux) {
                        aListeAutresInsees.push(data.inseesAutresFlux[z].id);
                    }

                    console.log('autres flux...', aListeAutresInsees);
                    for (insee in  data.insees) {
                        var indexInsee = aListeAutresInsees.lastIndexOf(data.insees[insee]);
                        if (indexInsee >= 0) {
                            console.log('Soustraction du flux ' + indexInsee);
                            data.insees.slice(indexInsee, indexInsee + 1);
                        }
                    }

                    var formatFunctionAutresFlux = function() {
                        for (i in arguments[0]) {
                            // On masque le LI correspondant dans la liste des INSEEs disponibles
                            arguments[0][i]['libelle'] = arguments[0][i]['insee'] + ' ' + arguments[0][i]['ville'] + ' (' + arguments[0][i]['cp'] + ') - ' + arguments[0][i]['flux_nom']
                        }
                    };
                }

                pushOptionsToElem('#ExceptionTarget', '<li|sortable>', data.insees, {val: 'commune_id', label: 'libelle', sortableId: ''}, formatFunction);

                // Affichage des insees des autres flux
                if (formatFunctionAutresFlux) {
                    pushOptionsToElem('#ExceptionTarget', '<li|non-sortable>', data.inseesAutresFlux, {val: 'commune_id', label: 'libelle', sortableId: ''}, formatFunctionAutresFlux);
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                // Affichage de la notification
                displayNotification('error', 'Erreur fatale', 'La liste des INSEE pour cette exception n \'a pas pu être récupérée. Veuillez ré-essayer.', 4000);
            }
        });
    }
});

// Déclenchement à partir du champ Produit
$(document).delegate('#exceptProdSelect', 'change', function() {
    $('#exceptDepotSelect').trigger('change');
});
// Déclenchement à partir du champ Flux
$(document).delegate('#exceptFluxSelect', 'change', function() {
    $('#exceptDepotSelect').trigger('change');
});

/**
 * Met à jour le tableau des IDs de produits
 * @param {array} aExceptions
 * @returns {void}
 */
function setProdIdUniqArray(aExceptions) {
    window.aProdIdUniq = [];
    for (x in aExceptions) {
        for (prd in aExceptions[x]) { // Parcours des exceptions produits dans le flux
            if (window.aProdIdUniq.lastIndexOf(aExceptions[x][prd].info.id) == -1) {
                window.aProdIdUniq.push(aExceptions[x][prd].info.id);
            }
        }
    }
}

/**
 * Supprime les exceptions correspondantes aux critères de info dans targetObj
 * @param {object} info L'objet utilisé pour la demande de suppression en Ajax Il contient toutes les informations nécessaires
 * @param {object} targetObj L'objet dans lequel est stockée la répartition
 */
function removeRepartProd(info, targetObj) {
    switch (info.type) {
        case 'produit':
            console.log(targetObj.length + ' exceptions produits avant suppression ');
            var index = 0;
            for (exp in targetObj) {
                if (info.prod_id == targetObj[exp]['info']['id']
                        && info.flux_id == targetObj[exp][0]['flux_id']
                        && info.depot_id == targetObj[exp][0]['depot_id']
                        ) {
                    console.log('Suppression dans l objet de: ', info);
                    //delete targetObj[exp];
                    targetObj.splice(index, 1);
                }

                index++;
            }
            console.log(targetObj.length + ' exceptions produits après suppression ');
            break;
    }
}

/**
 * Méthode de réinitialisation d'un bloc d'exception si besoin
 * @param {string} type Le type de bloc d'exception (produit|societe)
 * @param {bool} force Force le reset si vrai, ne le fait que si il n'y a plus d'items dans le cas contraire
 */
function resetExceptBloc(type, force) {
    if (!force) {
        // Compte le nombre d'items
        switch (type) {
            case 'produit':
                var n = $('#repartProd div.exceptionProd').length;
                break;
            case 'societe':
                var n = $('#repartSoc div.exceptionSte').length;
                break;
        }

        if (n > 0) {
            // Produit dispo pas de reset
            return false;
        }
    }
    if (n <= 0 || force) {
        switch (type) {
            case 'produit':
                console.log('reset prod');
                $('#prodSelect').attr('disabled', 'disabled');
                $('#prodSelect').next('.alert-warning').fadeIn();
                break;
            case 'societe':
                console.log('reset ste');
                $('#socSelect').attr('disabled', 'disabled');
                $('#socSelect').next('.alert-warning').fadeIn();
                break;
        }
    }
    
    
    
    
    
}