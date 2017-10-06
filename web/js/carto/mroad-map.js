tourneesAffTbl = []; // Variable contenant la liste des tournées affichées
tourneesOrdreModif = []; // Contiendra le tournées dont l'ordre aura été modifié
urlFetchPoints = '';
jsonTourneesAffTbl = []; // Variable contenant les JSON orginaux des tournées affichées
pointAction = ''; // Variable contenant l'action réalisée lors d'un clic sur la carte (affichage étape ou désignation d'un nouveau point de livraison ?)
groupesPointsTbl = {}; // Objet contenant les points de livraison pour détecter ceux qui ont plusieurs abonnés
var markerPointAction; // La référence du marqueur symbolisant le point désigné sur la carte
var newMarkerLayer = new OpenLayers.Layer.Vector("Overlay"); // La couche qui reçoit le marqueur du point désigné sur la carte
var markerPointFeatureID = '';
var flux_selectionne; // Variable contenant l'ID du flux dont les tournées sont affichées
var date_selectionnee; // Vairable contenant le daté concerné par l'affichage
var date_YYYYMMDD; // Vairable contenant le daté concerné par l'affichage au format YYYY-MM-DD
var tournee_selectionnee_id; // Variable contenant l'ID de la tournée sélectionnée (création/rattachement d'un nouveau point de livraison)
var point_selectionne_id; // Variable contenant l'ID du point sélectionné
var point_selectionne_td_id; // Variable contenant l'ID du point sélectionné dans tournée détail
var ptCorrectifRnvp = {}; // Objet contenant les informations du point désigné pour la modification d'une adresse RNVP
var pointSelectionMode = false;
var adresseAbonne;
var zipAbonne;
var cityAbonne;
var point_selectionne_data = '';
var iZoomDft = 11; // Niveau de zoom par défaut lorsqu'on sélectionne un point dans la recherche
var coucheControl;

/**
 * Fonction qui permet de désigner un point dont les informations serviront à corriger une adresse RNVP
 */
function getPointInfos() {
    console.log('Mode désignation de point');
    clicNewMarker = new OpenLayers.Control.Click();
    var map = GCUI.getMap('map');
    map.addControl(clicNewMarker);
    clicNewMarker.activate();
    $('div.olMap').css('cursor', 'crosshair');
    pointSelectionMode = true; // Permet de désactiver l'affichage du bloc d'info de point
}

function populateDataPoint() {
    $('#amsModalLabel').html('Modification du point de livraison');
    $('.updatePointLivraisonId').show();
    $('#amsModal').modal('show');
    var city = '';
    if (ptCorrectifRnvp.codePostal >= 75000 && ptCorrectifRnvp.codePostal <= 75999)
        city = 'PARIS';
    else
        city = ptCorrectifRnvp.ville;
    $('input[name=adresse_rejet]').val(decodeURIComponent(ptCorrectifRnvp.adresse));
    $('input[name=zip_rejet]').val(ptCorrectifRnvp.codePostal);
    $('input[name=city_rejet]').val(city);
    $('input[name=longitude_rejet]').val(ptCorrectifRnvp.x);
    $('input[name=latitude_rejet]').val(ptCorrectifRnvp.y);

    // Reset
    ptCorrectifRnvp = {};
    clicNewMarker.activate();
}
/**
 * Fonction qui remet le curseur dans son style par défaut et désactive la sélection
 */
function disableCursor(cursor) {
    cursor.deactivate();
    var cursorUrl = $('div.olMap').attr('data-defaultCursorUrl');
    $('div.olMap').css('cursor', 'url("' + cursorUrl + '"), default');
}

/*
 * Fontion qui place le curseur en attente
 */
function waitCursor(cursor, bDisable) {
    if (bDisable) {
        cursor.deactivate();
    }
    console.log(cursor);
    $('div.olMap').css('cursor', 'wait');
}

/**
 * Ajoute à l'objet ptCorrectifRnvp l'adresse du point sur la base de ses coordonnées
 */
function getAdressForPoint() {
    if (ptCorrectifRnvp.init != 'coords') {
        console.log('Le point ne permet pas de récupérer son adresse.');
        throw ('Mauvais paramétrage du point');
    }

    if (ptCorrectifRnvp.x != "" && ptCorrectifRnvp.y != "") {
        $('#message_error').remove();
        var url = "";
        var data = {};
        var servUrl = $('#newP2L_lnk').data('urlgeows');
        if (typeof clicNewMarker != 'undefined') {
            waitCursor(clicNewMarker, true);
        }

        $.ajax({
            type: "GET",
            url: servUrl + '/' + ptCorrectifRnvp.x + '/' + ptCorrectifRnvp.y,
            data: data,
            success: function (data) {
                if (data.listReverseGeocodingResults[0].addresses.length > 0) {
                    var adresse = data.listReverseGeocodingResults[0];
                    var adresseTrouvee = {
                        "adresse": encodeURIComponent(adresse.addresses[0].streetLine),
                        "ville": encodeURIComponent(adresse.addresses[0].cityName),
                        "codePostal": adresse.addresses[0].zipCode
                    };

                    ptCorrectifRnvp.init = 'adresse';
                    ptCorrectifRnvp.adresse = adresseTrouvee.adresse;
                    ptCorrectifRnvp.ville = adresseTrouvee.ville;
                    ptCorrectifRnvp.codePostal = adresseTrouvee.codePostal;
                    console.log(ptCorrectifRnvp);
                    populateDataPoint();

                }
                else {
                    // Affiche l'erreur durant un instant
                    $('.modal-body .well').prepend('<div id="message_error" class="alert alert-danger">Une erreur est survenue. Veuillez verifier vos coordonnées</div>');

                    var errMsg = 'Erreur dans la réponse de Geoconcept';
                    console.log(errMsg);
                    throw errMsg;
                    showFlashAlert('#mapNotifications .alert.alert-warning', 4000);
                }
                if (typeof clicNewMarker != 'undefined')
                    disableCursor(clicNewMarker);
            },
            error: function () {
                var errMsg = 'Erreur détecté';
                console.log(errMsg);
                throw errMsg;
                disableCursor(clicNewMarker);

                //showFlashAlert('#mapContainer .alert.alert-danger', 5000);
            },
            dataType: 'json'
        });
    }


}

/**
 * Fonction d'affichage des informations de points de livraisons
 */
function displayP2LDatas(marker, urlBase) {
    $("#multi_abonnes_infos_carousel div#aboInfos_0").addClass('active');
    console.log('marker=:', marker);
    pointAction = 'etape';
    // Formatage du type de client
    var typeClient;
    var iconeType;

    // On vérifie que le point de livraison ne comprend qu'un seul abonné à livrer
    if (groupesPointsTbl[marker.attributes.aTourneeCode][marker.attributes.aPointLivraisonId]){
        var nbProduitsAbos = groupesPointsTbl[marker.attributes.aTourneeCode][marker.attributes.aPointLivraisonId].length;
    }
    else{
        console.error('Point de livraison fantome. @TODO: Voir d ou ça peut venir');
        var nbProduitsAbos = 1;
    }
//        alert(nbProduitsAbos+' produits à livrer sur ce point');
    var abosPoint = [];
    for (abonne in groupesPointsTbl[marker.attributes.aTourneeCode][marker.attributes.aPointLivraisonId]) {
        abosPoint.push(groupesPointsTbl[marker.attributes.aTourneeCode][marker.attributes.aPointLivraisonId][abonne].attributes.aUniqueId);
    }
    var abosUnique = jQuery.unique(abosPoint);
    var nbAbos = abosUnique.length;
    var abosMarquePluriel = nbAbos > 1 ? 's' : '';
//        alert(nbAbos+' abonné à servir');
    $('#multi_abonnes_infos_carousel').show();

    // Suppression des éventuels clones dans le caroussel
    $("#multi_abonnes_infos_carousel div.item").each(function (index, value) {
        if (index > 0 && $(this).attr('id') != 'aboInfos_0') {
            $("#multi_abonnes_infos_carousel div#aboInfos_" + index).remove();
        }
        else {
            if ($(this).attr('id') == 'aboInfos_0') {
                $("#multi_abonnes_infos_carousel div#aboInfos_0").removeClass('next');
                $("#multi_abonnes_infos_carousel div#aboInfos_0").removeClass('prev');
                $("#multi_abonnes_infos_carousel div#aboInfos_0").removeClass('left');
                $("#multi_abonnes_infos_carousel div#aboInfos_0").removeClass('right');
            }
        }
    });

    if (nbProduitsAbos > 1) {
        $('#multi_abonnes_infos_block #nbAbosPoint').text(nbAbos);
        $('#multi_abonnes_infos_block #nbAbosPointPluriel').text(abosMarquePluriel);
        $('#multi_abonnes_infos_block #nbProduitsPoint').text(nbProduitsAbos);
        $('#multi_abonnes_infos_block').show();
        $('#multi_abonnes_controls_block').show();
        $('#multi_abonnes_infos_carousel div.item h5').show();

        // Affichage du lien "voir la liste" à l'intérieur de la bulle de la carte
        if (nbAbos > 1) {
            $('#map .sortClientsLink').show();
            $('#pointInfos .toolbar #toolPointListCustomers').show();
        }
        else {
            $('#map .sortClientsLink').hide();
            $('#pointInfos .toolbar #toolPointListCustomers').hide();
        }
    }
    else {
        $('#multi_abonnes_infos_block').hide();
        $('#multi_abonnes_controls_block').hide();
        $('#multi_abonnes_infos_carousel div.item h5').hide();
        $('#pointInfos .toolbar #toolPointListCustomers').hide();
    }

    for (abo in groupesPointsTbl[marker.attributes.aTourneeCode][marker.attributes.aPointLivraisonId]) {
        switch (groupesPointsTbl[marker.attributes.aTourneeCode][marker.attributes.aPointLivraisonId][abo].attributes.aType) {
            default:
            case 'abo':
                typeClient = 'Abonné';
                iconeType = urlBase + "/carto/marker_circle_" + marker.attributes.aTourneeNumber + ".png";
                break;
            case 'reperage':
                typeClient = 'Repérage';
                iconeType = urlBase + "/carto/marker_target_" + marker.attributes.aTourneeNumber + ".png";
                break;
            case 'depart':
                typeClient = 'Démarrage';
                iconeType = urlBase + "/carto/marker_start_" + marker.attributes.aTourneeNumber + ".png";
                break;
            case 'l2v':
                typeClient = 'Lieu de vente';
                iconeType = urlBase + "/carto/marker_triangle_" + marker.attributes.aTourneeNumber + ".png";
                break;
        }
        displayAboDatas(abo, groupesPointsTbl[marker.attributes.aTourneeCode][marker.attributes.aPointLivraisonId][abo], typeClient, iconeType, urlBase);
    }

    // Ouverture/Fermeture des cadres d'informations de tournée
    $('.windowTools .glyphicon.glyphicon-minus').trigger('click');
    $('#tourneeInfos_' + marker.attributes.aTourneeNumber + ' .windowTools .glyphicon.glyphicon-plus').trigger('click');

    // Affichage de la div d'informations
    point_selectionne_id = marker.attributes.aPointLivraisonId;
    point_selectionne_td_id = marker.attributes.aTdId;
    point_selectionne_data = marker.attributes;
    switch (typeClient) {
        case 'Démarrage':
            var htmlPopup = '<img src="' + iconeType + '"/>&nbsp;<span class="mapInfoBulleType">' + typeClient + '</span><br/><br/><strong>' + marker.attributes.aNomClient + '</strong><br/>' + marker.attributes.aTitle + '<div class="cartoPopMainContent"><img src="' + urlBase + '/icones_interface/distribution.png"/><p class="titreProduit">' + marker.attributes.aAdresse + '</p></div>';
            break;
        default:
            // Contenu de la popup selon le nombre d'abonné
            if (nbAbos == 1) {
                var htmlPopup = '<img src="' + iconeType + '"/>&nbsp;<span class="mapInfoBulleType">' + typeClient + '</span><br/><br/><strong>' + marker.attributes.aNomClient + '</strong><br/>' + marker.attributes.aTitle + '<div class="cartoPopMainContent"><img src="' + marker.attributes.aImageProduit + '"/><p class="titreProduit">' + marker.attributes.aTitreProduit + '</p></div>';
            }
            else {
                htmlPopup = '<p class="cartoTitrePop">Point de regroupement</p><div class="cartoPopMainContent"><img class="cartoPopIconMultiAbos" src="' + urlBase + '/regroupement_abonnes_icone.png"/><p>' + nbAbos + ' abonnés à livrer sur ce point.</p></div><p class="sortClientsLink"><a class=""><span class="glyphicon glyphicon-list"></span> Voir la liste</a></p>';
            }

            // Traitement des valeurs nulles (liées au recalcul d'optim par ex.)
            var besoinRecalcul_txt = ' - à recalculer - ';
            if (marker.attributes.aHeureClient == null) {
                marker.attributes.aHeureClient = besoinRecalcul_txt;
            }
            if (marker.attributes.aDureeClient == null) {
                marker.attributes.aDureeClient = besoinRecalcul_txt;
            }
            if (marker.attributes.aTrajetClient == null) {
                marker.attributes.aTrajetClient = besoinRecalcul_txt;
            }
            if (marker.attributes.aTrajetCumulClient == null) {
                marker.attributes.aTrajetCumulClient = besoinRecalcul_txt;
            }
            if (marker.attributes.aOrdreClient == null) {
                marker.attributes.aOrdreClient = besoinRecalcul_txt;
            }

            $('#idClientTourneeInfos').text('#' + marker.attributes.aId);
//            $('#pointLivraisonTourneeInfos').text(marker.attributes.aPointLivraisonId);
            $('#ordreClientTourneeInfos').text(marker.attributes.aOrdreClient);

            // Ajout des informations nécessaires aux boutons d'action du cadre d'informations
            $('#toolPointChangeTurn').attr('data-tourneeId', marker.attributes.aTourneeId);
            $('#toolPointChangeTurn').attr('data-tourneeCode', marker.attributes.aTourneeCode);

            break;
    }

    // Changement de couleur de bordure
    $('#pointLivraisonTourneeInfos').css('color', marker.attributes.aPointColor);
    $('#typeClientTourneeInfos').text(typeClient);

    $('#pointInfos').show();

    var map = GCUI.getMap('map');
    popup = new OpenLayers.Popup.FramedCloud("chicken",
            new OpenLayers.LonLat(marker.geometry.x, marker.geometry.y),
            new OpenLayers.Size(200, 200),
            htmlPopup,
            null,
            true,
            null

            );

    popup.imageSrc = urlBase + "/../js/carto/img/cloud-popup-relative.png";
    map.addPopup(popup);

    $('.sortClientsLink').click(function () {
        $('#toolPointListCustomers').click();
    });
}


/**
 * Fonction qui affiche les informations d'un abonné dans son cadre d'informations
 */
function displayAboDatas(index, marker, typeClient, icone, urlBase) {
    // Modifications communes à tous les types de clients
    switch (typeClient) {
        case 'Démarrage':
            $('#pointInfos h4').html('Démarrage de tournée <p></p>');
            $('#pointInfos h4').next('p').hide();
            // Image/Logo du produit
            $("#aboInfos_" + index + ' img.logoProduit').attr('src', urlBase + '/icones_interface/distribution.png');
            $("#aboInfos_" + index + ' p.titreProduit').text(marker.attributes.aAdresse);

            // Masquage de la div contenant les informations "multi-abonnés"
            $('#multi_abonnes_infos_block').hide();
            $('#toolPointListCustomers').hide();

            // Masquage du bouton de changement de tournée
            $('#toolPointChangeTurn').hide();

            // Masquage des flèches de défilement
            $('#multi_abonnes_controls_block').hide();

            break;
        default:
            $('#pointInfos h4').html('Point de livraison n°<span id="pointLivraisonTourneeInfos">' + marker.attributes.aPointLivraisonId + '</span>');
            $('#pointInfos h4').next('p').show();
            // Clonage du premier élément abonné
            if (index > 0) {
                var nouvAbo = $("#aboInfos_0").clone().appendTo($("#aboInfos_0").parent());
                $(nouvAbo).attr('id', 'aboInfos_' + index);
                $(nouvAbo).removeClass('active left right next prev');
            }

            var numOrdre = Math.abs(index) + 1;
            $('#pointInfos h4').attr('class', marker.attributes.aTourneeCode);
            $("#aboInfos_" + index + ' h5').text('#' + numOrdre);
            $("#aboInfos_" + index + ' p.titreProduit').text(marker.attributes.aTitreProduit);

            // Image/Logo du produit
            $("#aboInfos_" + index + ' img.logoProduit').attr('src', marker.attributes.aImageProduit);

            // Affichage du bouton de changement de tournée
            $('#toolPointChangeTurn').show();
            break;
    }

    $("#aboInfos_" + index + ' img.typeClient').attr('src', icone);
    $("#aboInfos_" + index + ' span.typeAbonne').text('(' + typeClient + ' - ' + marker.attributes.aIdClient + ') ');
    $("#aboInfos_" + index + ' span.nomAbonne').text(marker.attributes.aNomClient);
    $("#aboInfos_" + index + ' p.adresse1Abonne').text(marker.attributes.aTitle);
    adresseAbonne = marker.attributes.aAdresseAbonne;
    zipAbonne = marker.attributes.aZipAbonne;
    cityAbonne = marker.attributes.aCityAbonne;
}

/**
 * Fonction d'affichage des information de la tournée
 * @param object tourneeDatas Les propriétés de la FeatureCollection provenant du GeoJSON
 * @param int index Le numéro de la tournée dans la liste d'affichage
 */
function displayTourneeDatas(tourneeDatas, index) {
    // Enregistrement de la tournée dans le tableau des tournées affichées
    tourneesAffTbl.push(Math.abs(tourneeDatas.tId));
    var sBaseFetchUrl = $('#tourneeInfos_' + index + ' .toolTurnChangeOrder').attr('data-fetchurl');
    var sDateUrl = date_selectionnee.replace(/\//g, '-');
    sBaseFetchUrl += '/' + tourneeDatas.tId + '/' + sDateUrl + '/' + flux_selectionne + '/' + index;
    // Intégration des informations
    $('#tourneeInfos_' + index + ' .idTourneeDatas').text(tourneeDatas.tCode);
    $('#tourneeInfos_' + index + ' .id2TourneeDatas').text(tourneeDatas.tCode);
//    $('#tourneeInfos_' + index + ' .dateTourneeDatas').text(tourneeDatas.tDate);
    $('#tourneeInfos_' + index + ' .heureDebutTourneeDatas').text(tourneeDatas.tHeureD);
    $('#tourneeInfos_' + index + ' .heureFinTourneeDatas').text(tourneeDatas.tHeureF);
    $('.depotTourneeDatas').text(tourneeDatas.tAdresseDepot);
    $('.tDepotNamePHolder').text(tourneeDatas.tDepot);
    $('#tourneeInfos_' + index + ' .tempsConduiteTourneeDatas').text(tourneeDatas.tTempsConduite);
    $('#tourneeInfos_' + index + ' .tempsVisiteTourneeDatas').text(tourneeDatas.tTempsVisite);
    $('#tourneeInfos_' + index + ' .dureeTourneeDatas').text(tourneeDatas.tDuree);
    $('#tourneeInfos_' + index + ' .nbStopsTourneeDatas').text(tourneeDatas.tNbArrets);
    $('#tourneeInfos_' + index + ' .nbAbonne').text(tourneeDatas.tNbAbonne);
    $('#tourneeInfos_' + index + ' .distanceTourneeDatas').text(tourneeDatas.tDistance);
    $('#tourneeInfos_' + index + ' .updateMyData .updateData').attr('title', tourneeDatas.tCode);
    $('#tourneeInfos_' + index + ' .updateMyData').attr('data-tourneedate', tourneeDatas.tDate);
    $('#tourneeInfos_' + index + ' .toolTurnChangeOrder').attr('data-tourneeid', tourneeDatas.tId);
    $('#tourneeInfos_' + index + ' .toolTurnChangeOrder').attr('data-tourneecode', tourneeDatas.tCode);
    $('#tourneeInfos_' + index + ' .toolTurnChangeOrder').attr('data-fetchurl', sBaseFetchUrl);
    $('#tourneeInfos_' + index + ' .moveAbo').attr('data-tourneeid', tourneeDatas.tId);
    $('#tourneeInfos_' + index + ' .moveAbo').attr('data-tourneecode', tourneeDatas.tCode);
    $('#tourneeInfos_' + index + ' .moveAbo').attr('data-tourneedate', tourneeDatas.tDate);

    // Changement de couleur de bordure
    $('#tourneeInfos_' + index).css('border-top-color', tourneeDatas.tColor);
    $('#tourneeInfos_' + index + ' .idTourneeDatas').css('color', tourneeDatas.tColor);
}

// Initialisation des champs et traitements après chargement
$(document).ready(function () {
    //On désactive le bouton de recherche tant que le formulaire de recherche n'est pas dûment rempli
    if ($("#selectTourneeType :selected").val() == "") {
        $('#searchTourBtn').attr('disabled', 'disabled');
    }

    $('#selectTourneeType').on("change", function () {
        if (($("#selectTourneeType :selected").val() == "")
                || ($('#dateTournee').val() === "")) {
            $('#searchTourBtn').attr('disabled', 'disabled');
        }
        else {
            $('#searchTourBtn').removeAttr('disabled');
        }
    });

    $('#dateTournee').on('change', function () {
        $('#selectTourneeType').trigger('change');
    });

    // Re-dimensionnement de la carte
    var hauteurCarte = $(window).height() * 0.8;
    var hauteurInterface = $(window).height() * 0.95;
    $('table.interface').css('height', hauteurInterface);
    $('#map').css('height', hauteurCarte);

    // Traitement du bouton de recherche de tournées
    $('#searchTourBtn').click(function () {
        // Récupération des paramètres pour la requête AJAX
        var codeDepot = '031'; // $("#selectTourneeType :selected").val();
        var typeTournee = $("#selectTourneeType :selected").val();
        var jourTournee = $("#dateTournee").val();

        // Formatage de la date
        var dateTbl = jourTournee.split('/');
        var dateFormat = dateTbl[2] + '-' + dateTbl[1] + '-' + dateTbl[0];

        var url = "";
        var data = {};
        $.ajax({
            type: "POST",
            url: $(this).data('fetchurl') + "/" + codeDepot + '/' + dateFormat,
            data: data,
            success: function (data) {
                if (data.tournees.length > 0) {
                    $('#listeTournees div.liste').html("");
                    for (var tournee in data.tournees) {
//                        console.log(tourneesAffTbl);
                        if (jQuery.inArray(Math.abs(data.tournees[tournee].idTournee), tourneesAffTbl) < 0) {
                            var cbCheckStatus = "";
                        }
                        else {
                            cbCheckStatus = ' checked="checked"';
                        }
                        var htmlBlock = '<div class="tourneeListeElement"><input type="checkbox" ' + cbCheckStatus + ' id="tournee' + data.tournees[tournee].idTournee + '" name="tournee' + data.tournees[tournee].idTournee + '" value="' + data.tournees[tournee].idTournee + '"/><label for="tournee' + data.tournees[tournee].idTournee + '">' + data.tournees[tournee].codeTournee + ' - ' + data.tournees[tournee].libDepot + '</label></div>';
                        $('#listeTournees div.liste').append(htmlBlock);
                    }
                }
                else {
                    $('#listeTournees div.liste').html('Aucune tournée disponible selon ces critères.');
                }

                $('#listeTournees').show();
            },
            dataType: 'json'
        });
    });

    // Mise en place des tooltips sur les icones de la toolbox
//    $('.toolbar a').popover({
//        trigger: 'hover'
//    });
//    $('.windowTools .glyphicon').popover({
//        trigger: 'hover'
//    });

    // Agrandissement / Réduction des cadres d'informations des tournées
    $(document).on('click', '.windowTools .glyphicon', function () {
        if ($(this).hasClass('glyphicon-minus')) {
            $(this).addClass('glyphicon-plus');
            $(this).removeClass('glyphicon-minus');

            // Changement du contenu de l'infobulle
            $(this).attr('data-content', 'Agrandir le cadre d\'informations de la tournée');
        }
        else {
            $(this).addClass('glyphicon-minus');
            $(this).removeClass('glyphicon-plus');

            // Changement du contenu de l'infobulle
            $(this).attr('data-content', 'Réduire le cadre d\'informations de la tournée');
        }
        $(this).parent().next('.infosContent').find('.tourneeDatasDetails').toggle();
    });

    // Bouton de d'activation de la fonction "ajouter un point à une tournée"
    $('.toolAddPoint').click(function () {
        clicNewMarker.activate();
        var map = GCUI.getMap('map');
        $('div.olMap').css('cursor', 'crosshair');

        // Enregistrement de l'ID de la tournée sélectionnée pour ce nouveau point
        var tourneeInfos = jsonTourneesAffTbl[$(this).attr('data-tourneeIndex')];
        tournee_selectionnee_id = tourneeInfos.properties.tId;
    });

    // Bouton d'annulation de création de nouveau point
    $('#cancelNewP2L_lnk').click(function () {
        var map = GCUI.getMap('map');
        // Récupération de l'URL du curseur par défaut de la carte
        var cursorUrl = $('div.olMap').attr('data-defaultCursorUrl');
        $('div.olMap').css('cursor', 'url("' + cursorUrl + '"), default');

        // On remet les controles de la carte dans l'état d'avant l'activation de la fonctionnalité "nouveau point de livraison"
        clicNewMarker.deactivate();

        // On bascule les informations affichée en haut de carte vers la légende
        $('#infosMapPoint .infosPoint').hide();
        $('#infosMapPoint .infosCarte').show();
        $('#infosMapPoint #mapLegende').show();

        // Suppression du point de la carte
        if (markerPointAction && newMarkerLayer) {
            newMarkerLayer.removeFeatures(newMarkerLayer.getFeatureById(markerPointFeatureID));
        }
    });

    // Bouton de lancement de la requete de géocodage inversé
    $('#newP2L_lnk').click(function () {
        // On remet les controles de la carte dans l'état d'avant l'activation de la fonctionnalité "nouveau point de livraison"
        clicNewMarker.deactivate();
        // Récupération de l'URL du curseur par défaut de la carte
        var cursorUrl = $('div.olMap').attr('data-defaultCursorUrl');
        $('div.olMap').css('cursor', 'url("' + cursorUrl + '"), default');

        // Préparation de la requête
        var lon = $('#nouvPointX').html();
        var lat = $('#nouvPointY').html();
        var urlCreapoint = $(this).data('urlcreapoint');

        if (lon != "" && lat != "") {
            var url = "";
            var data = {};
            $.ajax({
                type: "GET",
                url: $(this).data('urlgeows') + '/' + lon + '/' + lat,
                data: data,
                success: function (data) {
                    if (data.listReverseGeocodingResults[0].addresses.length > 0) {
                        var adresse = data.listReverseGeocodingResults[0];
                        var adresseTrouvee = {
                            "adresse": encodeURIComponent(adresse.addresses[0].streetLine),
                            "ville": encodeURIComponent(adresse.addresses[0].cityName),
                            "codePostal": adresse.addresses[0].zipCode
                        };

                        urlCreapoint += '?cartopreset=1&tourneeId=' + tournee_selectionnee_id
                                + '&ville=' + adresseTrouvee.ville
                                + '&cp=' + adresseTrouvee.codePostal
                                + '&adresse=' + adresseTrouvee.adresse;

                        // Ouverture d'un nouvel onglet pour la création du point de livraison
                        window.open(urlCreapoint, 'creapoint');

                        // On bascule les informations affichée en haut de carte vers la légende
                        $('#infosMapPoint .infosPoint').hide();
                        $('#infosMapPoint .infosCarte').show();
                        $('#infosMapPoint #mapLegende').show();
                    }
                    else {
                        // Affiche l'erreur durant un instant
                        showFlashAlert('#mapNotifications .alert.alert-warning', 4000);
                    }

                },
                error: function () {
                    showFlashAlert('#mapContainer .alert.alert-danger', 5000);
                },
                dataType: 'json'
            });
        }
    });

    // Bouton de déplacement d'un point vers une autre tournée
    $('#toolPointChangeTurn').on('click', function () {
        // On active tous les éléments de la liste
        $('#selectTournee').find("option:disabled").removeAttr('disabled');
        $('#selectTournee').find("option:selected").removeAttr('selected');

        var tourneeId = $(this).attr('data-tourneeid');
        var tourneeCode = $(this).attr('data-tourneecode');

        // On écrit le nom de la tournée à laquelle appartient actuellement le point
        $('#amsModal .infoFormTourneeActuelle').html(tourneeCode);
        $('#amsModal #sortTourneeForm').hide();
        $('#amsModal #sortOrderInStopForm').hide();
        $('#amsModal #moveTourneeForm').show();

        // Suppression de l'option de la tournée à laquelle ce point appartient
        $('#selectTournee').find("option[value=" + tourneeId + "]").attr('disabled', 'disabled');

        $('#amsModalLabel').html('Déplacer ce point vers une autre tournée');
        $('#amsModal').modal('show');
    });

    // Activation du bouton "Déplacer"
    $('#selectTournee').change(function () {
        if ($('#selectTournee option:selected').val()) {
            $('#movePointToTourBtn').removeAttr('disabled');
        }
        else {
            $('#movePointToTourBtn').attr('disabled', 'disabled');
        }
    });

    // Validation du déplacement de tournée
    $('#movePointToTourBtn').parent('form').submit(function (evt) {
        evt.preventDefault();

        // On désactive le bouton
        $('#movePointToTourBtn').attr('disabled', 'disabled');

        // Affichage du loader
        $('#loaderMoveSinglePt').show();

        // Mise en place de l'appel AJAX
        var url = $('#movePointToTourBtn').attr('data-posturl');
        var pointID = $('#pointInfos .infosContent #pointLivraisonTourneeInfos').html();
        var tourneeCode = $('#selectTournee option:selected').text();
        var tourneeId = $('#selectTournee option:selected').val();
        var tourneeActuelleCode = $('#toolPointChangeTurn').attr('data-tourneecode');
        var tourneeActuelleId = $('#toolPointChangeTurn').attr('data-tourneeid');
        var depotId = depot_id;
        // Reformatage de la date
        var dateTimeTbl = date_selectionnee.split(' ');
        var dateTbl = dateTimeTbl[0].split('/');
        var dateAjax = dateTbl[2] + '-' + dateTbl[1] + '-' + dateTbl[0];

        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: {
                tourneecible_id: tourneeId,
                depot_id: depotId,
                tourneecible_code: tourneeCode,
                tourneesource_id: tourneeActuelleId,
                tourneesource_code: tourneeActuelleCode,
                point_id: pointID,
                point_td_id: point_selectionne_td_id,
                flux_id: flux_selectionne,
                date: dateAjax
            },
            success: function (data) {
                console.log(data);
                // On masque le loader
                $('#loaderMoveSinglePt').hide();

                if (data.codeErr > 0) {
                    showModalFlashAlert('danger', data.msgErr, 7000);
                }
                else {
                    showModalFlashAlert('success', data.msgRetour, 5000);
                    // Maintenant on rafraichit la page pour éviter un certain nombre de bugs d'affichage HTC/Openlayers
                    window.location.href = window.location.href;
                }
            }
        });
    });

    // Tag pour retrouver mon code : yanyk
    // Boutons de Gestion du déplacement de n abonnés d'une tournée A a une Tournée B 
    $('.moveAbo').on('click', function () {

        // On active tous les éléments de la liste (on remet les param par defaut)
        $('#selectTourneeMove').find("option:disabled").removeAttr('disabled');
        $('#selectTourneeMove').find("option:selected").removeAttr('selected');
        $('#validateMoveMultPointsBtn').hide();
        $('#cancelMoveMultPointsBtn').hide();

        var tourneeId = $(this).attr('data-tourneeid');
        var tourneeCode = $(this).attr('data-tourneecode');

        // on renseigne le nom/id de la tournée actuelle
        $('#amsModal3 .infoChooseFormTourneeActuelle').html(tourneeCode);
        $('#amsModal3 .infoChooseFormTourneeActuelle').attr('data-tourneeid', tourneeId);
        $('#amsModal3 #modalAlerteMovePoints').hide();
        $('#amsModal3 #infoMoveMultPoints').hide();
        $('#amsModal3 #moveMultPointsForm').hide();
        $('#amsModal3 #chooseTourneeForm').show();

        //on rend inactif la tournée actuelle dans la liste
        $('#selectTourneeMove').find("option[value=" + tourneeId + "]").attr('disabled', 'disabled');

        $('#amsModal3 #amsModalLabel').html('Choisir une tournée de destination');
        $('#amsModal3').modal('show');
    });

    $('#validateChoiceTourneeBtn').on('click', function (e) {
        e.preventDefault();
        var tourneeOrigine = $('#amsModal3 .infoChooseFormTourneeActuelle').html();
        var tourneeOrigineId = $('#amsModal3 .infoChooseFormTourneeActuelle').attr('data-tourneeid');
        var tourneeDestination = $('#selectTourneeMove option:selected').text();
        var tourneeDestinationId = $('#selectTourneeMove option:selected').val();

        if (tourneeDestination == "Choisissez la tournée...")
        {
            $('#modalAlerteMovePoints').removeClass('alert-defaultLoad').addClass('alert-warning');
            $('#modalAlerteMovePoints').html('Selectionnez une tournée dans la liste avant de valider. ');
            $('#amsModal3 #modalAlerteMovePoints').show();
        }
        else if (tourneeOrigineId == tourneeDestinationId) {
            $('#modalAlerteMovePoints').removeClass('alert-defaultLoad').addClass('alert-warning');
            $('#modalAlerteMovePoints').html('');
            $('#modalAlerteMovePoints').html('La tournée de départ doit être différente de la tournée de destination. ');
            $('#amsModal3 #modalAlerteMovePoints').show();
        }
        else
        {
            //On renseigne le noms des tournées dans le tableau d'affichage et les input hidden
            $('#tourneeOrigine').html(tourneeOrigine);
            $('#tourneeDestination').html(tourneeDestination);
            $(' #moveMultPointsForm input[name=codeTourneeOr]').attr('value', tourneeOrigine);
            $(' #moveMultPointsForm input[name=idTourneeOr]').attr('value', tourneeOrigineId);
            $(' #moveMultPointsForm input[name=codeTourneeDest]').attr('value', tourneeDestination);
            $(' #moveMultPointsForm input[name=idTourneeDest]').attr('value', tourneeDestinationId);
            //on modifie le style de la modale et pour adapter au contenu
            $('#amsModal3 > div').addClass('modal-carto-points-move');

            $('#amsModal3 #modalAlerteMovePoints').hide();
            $('#amsModal3 #chooseTourneeForm').hide();
            $('#amsModal3 #amsModalLabel').html("Déplacer des points d'une tournée A vers une tournée B");
            $('#amsModal3 #infoMoveMultPoints').show();
            $('#validateMoveMultPointsBtn').show();
            $('#cancelMoveMultPointsBtn').show();

            //on ajoute les arrets dans les différente listes(origine ensuite destination)
            for (tourneeJSON in jsonTourneesAffTbl) {
                if (jsonTourneesAffTbl[tourneeJSON].properties.tId == tourneeOrigineId) {
                    $('#listeTourneeOr .listeArrets').html('');
                    var pointsTourneeOr = jsonTourneesAffTbl[tourneeJSON].features;
                    var couleurPointsOr = jsonTourneesAffTbl[tourneeJSON].features[0].properties.aPointColor;
                    var couleurTourneeOr = jsonTourneesAffTbl[tourneeJSON].properties.tColor;
                    $('#listeTourneeOr .listeArrets').css('color', couleurPointsOr);
                    $('#tourneeOrigine').css('color', couleurTourneeOr);
                    var aPointLivraisonOr = new Array();

                    //on boucle dans les points pour remplir la liste a puce
                    for (point in pointsTourneeOr)
                    {
                        if (!in_array(pointsTourneeOr[point].properties.aPointLivraisonId, aPointLivraisonOr)
                                && pointsTourneeOr[point].properties.aPointLivraisonId > 0
                                && pointsTourneeOr[point].properties.aPointLivraisonId != 'depart') {
                            aPointLivraisonOr.push(pointsTourneeOr[point].properties.aPointLivraisonId);
                            $('#listeTourneeOr .listeArrets').append('<li id="tourneesOrOrder_' + jsonTourneesAffTbl[tourneeJSON].features[point].properties.aTdId + '|' + pointsTourneeOr[point].properties.aPointLivraisonId + '" data-plivraisonId="' + pointsTourneeOr[point].properties.aPointLivraisonId + '"><span>' + pointsTourneeOr[point].properties.aTitle + '</span></li>');
                        }
                    }
                }
                else if (jsonTourneesAffTbl[tourneeJSON].properties.tId == tourneeDestinationId)
                {
                    $('#listeTourneeDest .listeArrets').html('');
                    var pointsTourneeDest = jsonTourneesAffTbl[tourneeJSON].features;
                    var couleurPointsDest = jsonTourneesAffTbl[tourneeJSON].features[0].properties.aPointColor;
                    var couleurTourneeDest = jsonTourneesAffTbl[tourneeJSON].properties.tColor;
                    $('#listeTourneeDest .listeArrets').css('color', couleurPointsDest);
                    $('#tourneeDestination').css('color', couleurTourneeDest);
                    var aPointLivraisonDest = new Array();

                    //on boucle dans les points pour remplir la liste a puce
                    for (point in pointsTourneeDest)
                    {
                        if (!in_array(pointsTourneeDest[point].properties.aPointLivraisonId, aPointLivraisonDest)
                                && pointsTourneeDest[point].properties.aPointLivraisonId > 0
                                && pointsTourneeDest[point].properties.aPointLivraisonId != 'depart') {
                            aPointLivraisonDest.push(pointsTourneeDest[point].properties.aPointLivraisonId);
                            $('#listeTourneeDest .listeArrets').append('<li id="tourneesDestOrder_' + jsonTourneesAffTbl[tourneeJSON].features[point].properties.aTdId + '|' + pointsTourneeDest[point].properties.aPointLivraisonId + '" data-plivraisonId="' + pointsTourneeDest[point].properties.aPointLivraisonId + '"><span>' + pointsTourneeDest[point].properties.aTitle + '</span></li>');
                        }
                    }
                }
            }

            $('#listeTourneeOr .listeArrets, #listeTourneeDest .listeArrets').multisortable({
                delay: 150, // Pour eviter le deplacement pdt une selection multiple
                dropOnEmpty: true,
                selectedClass: "selected-point-move"
            });
            $("#listeTourneeOr .listeArrets").sortable({
                connectWith: "#listeTourneeDest .listeArrets",
                dropOnEmpty: true,
                delay: 150 // Pour eviter le deplacement pdt une selection multiple
            });

            ordrePtArretsOrigin = $('#listeTourneeOr .listeArrets').sortable("serialize");
            ordrePtArretsDest = $('#listeTourneeDest .listeArrets').sortable("serialize");
//            console.log(ordrePtArretsOrigin);
//            console.log(ordrePtArretsDest);
            $('#amsModal3 #moveMultPointsForm').show();

            // On adapte la taille de la div contenant la tournée de destination
            var listeOrHeight = $('#amsModal3 #listeTourneeOr').height();
            $('#amsModal3 #listeTourneeDest').css('min-height', listeOrHeight + 'px');
            $('#amsModal3 #listeTourneeDest .listeArrets').css('min-height', listeOrHeight + 'px');
        }
    });

    $('#validateMoveMultPointsBtn').on('click', function (e) {
        e.preventDefault();
        // on réinitialise les div d'affichages
        $('#modalAlerteMovePoints').hide();
        $('#modalAlerteMovePoints').removeClass().addClass('alert alert-defaultLoad');
        $('#modalAlerteMovePoints').html('');
        //on recupere les infos sur la modif du tableau et les codes tournées

        date = date_YYYYMMDD;
        var url = $('#validateMoveMultPointsBtn').attr('data-posturl');
        /** POINT LIVRAISON ID ORIGINE **/
        var ptLivraisonIdOri = '';
        var tourneeOrigine = $('#tourneeOrigine').html();
        $('#listeTourneeOr .listeArrets li').each(function (index) {
            if (index)
                ptLivraisonIdOri += ',' + $(this).attr('data-plivraisonid');
            else
                ptLivraisonIdOri += $(this).attr('data-plivraisonid');
        });

        /** POINT LIVRAISON ID DESTINATION **/
        var ptLivraisonIdDest = '';
        var tourneeDestination = $('#tourneeDestination').html();
        $('#listeTourneeDest .listeArrets li').each(function (index) {
            if (index)
                ptLivraisonIdDest += ',' + $(this).attr('data-plivraisonid');
            else
                ptLivraisonIdDest += $(this).attr('data-plivraisonid');
        });

        /** RECUPERATION DE LA TOURNEE DE DESTINATION AVANT MODIFICATION **/
        var baseTourneeDestination = '';
        for (tourneeJSON in jsonTourneesAffTbl) {
            if (jsonTourneesAffTbl[tourneeJSON].properties.tCode == tourneeDestination) {
                for (point in jsonTourneesAffTbl[tourneeJSON].features) {
                    if (baseTourneeDestination == '')
                        baseTourneeDestination += jsonTourneesAffTbl[tourneeJSON].features[point].properties.aPointLivraisonId;
                    else
                        baseTourneeDestination += ',' + jsonTourneesAffTbl[tourneeJSON].features[point].properties.aPointLivraisonId;
                }
            }
        }
        /** COMPARAISON DES POINTS DANS LA TOURNEE DE DESTINATION (INITIAL,APRES MODIFICATION **/
        var newPointDest = arr_diff(baseTourneeDestination.split(','), ptLivraisonIdDest.split(','));
        if (newPointDest.length == 0) {
            $('#validateMoveMultPointsBtn').show();
            $('#cancelMoveMultPointsBtn').show();
            $('#loaderMoveMultPt').hide();
            $('#amsModal3 #moveMultPointsForm').animate({scrollTop: 5}, 'slow');
            $('#modalAlerteMovePoints').removeClass('alert-defaultLoad').addClass('alert-warning');
            $('#modalAlerteMovePoints').html('');
            messageAlert = "L'ordre n'a pas été modifié et/ou aucun point n'a été inséré dans la tournée [" + tourneeDestination + "] de destination.";
            $('#modalAlerteMovePoints').html(messageAlert);
            var alerte = $('#amsModal3 #modalAlerteMovePoints').fadeIn();
            window.setTimeout(function () {
                alerte.fadeOut();
            }, 15000);
            return false;
        }

        // chargement en cours .....
        $('#validateMoveMultPointsBtn').hide();
        $('#cancelMoveMultPointsBtn').hide();
        $('#loaderMoveMultPt').show();
        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: {
                tourneeOrigine: tourneeOrigine,
                tourneeDestination: tourneeDestination,
                pointLivraisonDest: ptLivraisonIdDest,
                newPointDest: newPointDest,
                dateDistrib: date_YYYYMMDD,
                pointLivraisonOrigine: ptLivraisonIdOri
            },
            success: function (data) {
                if (data.codeRetour == "ok")
                {
                    console.log(data.msgRetour);
                    $('#validateMoveMultPointsBtn').show();
                    $('#cancelMoveMultPointsBtn').show();
                    $('#loaderMoveMultPt').hide();
                    showSelecteurFlashAlert('cartoNotifEmptyDiv', 'success', data.msgRetour, 5000);
                    setTimeout(function () {
                        window.location.href = window.location.href;
                    }, 5500);
                    $('#amsModal3').modal('hide');
                }
                else
                {
                    console.log(data.msgErr);
                    $('#validateMoveMultPointsBtn').show();
                    $('#cancelMoveMultPointsBtn').show();
                    $('#loaderMoveMultPt').hide();
                    $('#amsModal3 #moveMultPointsForm').animate({scrollTop: 5}, 'slow');
                    $('#modalAlerteMovePoints').removeClass().addClass('alert alert-warning');
                    $('#modalAlerteMovePoints').html('');
                    $('#modalAlerteMovePoints').html(data.msgErr);
                    $('#modalAlerteMovePoints').show();
                }
            },
            error: function () {
                console.log("Une erreur a été détéctée et c'est pas bon revoit ton code");
                $('#validateMoveMultPointsBtn').show();
                $('#cancelMoveMultPointsBtn').show();
                $('#loaderMoveMultPt').hide();
                $('#amsModal3 #moveMultPointsForm').animate({scrollTop: 5}, 'slow');
                var msg = 'Une erreur a été détéctée , veuillez réessayer ......';
                $('#modalAlerteMovePoints').removeClass().addClass('alert alert-warning');
                $('#modalAlerteMovePoints').html('');
                $('#modalAlerteMovePoints').html(msg);
                $('#modalAlerteMovePoints').show();
            }
        });
        return false;
    });


    $('.toolTurnChangeOrder').on('click', function () {
        var tourneeId = $(this).attr('data-tourneeid');
        var tourneeCode = $(this).attr('data-tourneecode');
        var sFecthUrl = $(this).attr('data-fetchurl');
        $('#amsModal #moveTourneeForm').hide();
        $('#amsModal #sortOrderInStopForm').hide();
        $('#amsModal #sortTourneeForm').show();
        $('#amsModalLabel').html('Réorganiser la tournée');
        $(' #sortTourneeForm .titreFormModale .infoFormTourneeActuelle').html(tourneeCode);
        $(' #sortTourneeForm input[name=codeTournee]').val(tourneeCode);
        $(' #sortTourneeForm input[name=idTournee]').val(tourneeId);
        $(' #sortTourneeForm input[name=fetchUrl]').val(sFecthUrl);

        var tourneesJSON = jsonTourneesAffTbl;

        // Ajout des arrets dans la liste
        for (tourneeJSON in tourneesJSON) {
            if (jsonTourneesAffTbl[tourneeJSON].properties.tId == tourneeId) {
                console.log(jsonTourneesAffTbl[tourneeJSON]);
                $(' #sortTourneeForm .listeArrets').html('');
                var pointsTournee = jsonTourneesAffTbl[tourneeJSON].features;
                var couleurPoints = jsonTourneesAffTbl[tourneeJSON].features[0].properties.aPointColor;
                var couleurTournee = jsonTourneesAffTbl[tourneeJSON].properties.tColor;
                $(' #sortTourneeForm .listeArrets').css('color', couleurPoints);
                $(' #sortTourneeForm .titreFormModale .infoFormTourneeActuelle').css('color', couleurTournee);
                var aPointLivraison = new Array();

                // On boucle dans les points 
                for (point in pointsTournee) {
                    if (!in_array(pointsTournee[point].properties.aPointLivraisonId, aPointLivraison)
                            && pointsTournee[point].properties.aPointLivraisonId > 0
                            && pointsTournee[point].properties.aPointLivraisonId != 'depart') {
                        aPointLivraison.push(pointsTournee[point].properties.aPointLivraisonId);
                        var Css = (pointsTournee[point].properties.dateDistrib == date_YYYYMMDD) ? 'a-livrer' : 'non-livrer';
                        $(' #sortTourneeForm .listeArrets').append('<li class="' + Css + '" id="tourneesNewOrder_' + jsonTourneesAffTbl[tourneeJSON].features[point].properties.aTdId + '" data-plivraisonId="' + pointsTournee[point].properties.aPointLivraisonId + '"><span>' + pointsTournee[point].properties.aTitle + '</span></li>');
                    }
//                    else{
//                        var Css = (pointsTournee[point].properties.dateDistrib == date_YYYYMMDD) ? 'a-livrer' : 'non-livrer';
//                        console.log($( "li:contains('"+ pointsTournee[point].properties.aTitle +"')" ));
//                        $("li:contains('"+ pointsTournee[point].properties.aTitle +"')").removeClass("non-livrer");
//                        $("li:contains('"+ pointsTournee[point].properties.aTitle +"')").removeClass("a-livrer");
//                        $("li:contains('"+ pointsTournee[point].properties.aTitle +"')").addClass(Css);
//                        
//                    }
                }
//                $(' #sortTourneeForm .listeArrets').sortable();
                $("#sortTourneeForm .listeArrets").multisortable({
                    stop: function (e, ui) {
                        var $group = $('.ui-multisort-grouped').not(ui.item);
                        $group.clone().insertAfter($(ui.item));
                        $group.each(function () {
                            $(this).removeClass('ui-multisort-grouped');
                        });
                        $group.remove();
                    }
                });
                $("#sortTourneeForm .listeArrets").disableSelection();

                ordrePtArretsOrigin = $(' #sortTourneeForm .listeArrets').sortable("serialize");
            }
        }

        $('#amsModal').modal('show');
    });


    $('#toolPointListCustomers').on('click', function () {
        $('#amsModalLabel').html('Liste des clients');
        $('#amsModal #moveTourneeForm').hide();
        $('#amsModal #sortTourneeForm').hide();
        $('#amsModal #sortOrderInStopForm').show();
        $('#sortOrderInStopForm .listeArrets').html('');

        var OrderPoint = $('#ordreClientTourneeInfos').html();
        var url = $(' #sortOrderInStopForm .listeArrets').attr('data-urlordre');
        var code = $('#pointInfos h4').attr('class');

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                order: OrderPoint,
                code: code,
                date: date_YYYYMMDD,
                pointLivraison: point_selectionne_id
            },
            success: function (data) {
                $(' #sortOrderInStopForm .listeArrets').append(data);
            },
            error: function () {
                showModalFlashAlert('danger', 'Une erreur a été détectée.', 2500)
            },
        });

        $('#sortOrderInStopForm .listeArrets').sortable();
        $('#amsModal').modal('show');
    });

    $('#submit_order_stop').on('click', function () {
        var url = $(' #sortOrderInStopForm .listeArrets').attr('data-urlordre');
        var sId = '';
        $("#sortOrderInStopForm .listeArrets li").each(function (index) {
            sId += $(this).attr('class');
            if ((index + 1) < $("#sortOrderInStopForm .listeArrets li").length)
                sId += '_';
        });

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                sId: sId,
                order_stop: 'true'
            },
            success: function (data) {
                if (data == 'success')
                    showModalFlashAlert('success', 'La mise à jour à été effectué avec succès.', 2500);
            },
            error: function () {
                showModalFlashAlert('danger', 'Une erreur a été détectée.', 2500);
            },
        });
    });


    $('#sortTourBtn').on('click', function () {
//        var dataSend = $(' #sortTourneeForm .listeArrets').sortable('serialize');
        var dataSend = 'dateDistrib=' + date_YYYYMMDD;
        if (ordrePtArretsOrigin == dataSend) {
            showModalFlashAlert('warning', 'L\'ordre n\'a pas été modifié.', 2500);
        }
        else {
            showModalFlashAlert('info', 'La modification est en cours d\'enregistrement, veuillez patienter...', 2500);
//            window.location.hash = "#modalAlerte"; // Permet de remonter en début de liste pour afficher le message
            //
            // Ajout de données supplémentaires
            var sCodeTournee = $(' #sortTourneeForm input[name=codeTournee]').val();
            var iTourneeId = $(' #sortTourneeForm input[name=idTournee]').val();
            var sFecthUrl = $(' #sortTourneeForm input[name=fetchUrl]').val();
            var tourneesJSON = jsonTourneesAffTbl;
            var ptLivraisonIdOrder = '';
            $('#sortTourneeForm .listeArrets li').each(function (index) {
                if (index)
                    ptLivraisonIdOrder += ',' + $(this).attr('data-plivraisonid');
                else
                    ptLivraisonIdOrder += $(this).attr('data-plivraisonid');
            });
            dataSend += "&code=" + sCodeTournee + "&ptLivraisonIdOrder=" + ptLivraisonIdOrder;

            var url = $(' #sortTourneeForm .listeArrets').attr('data-urlordre');
            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json',
                data: dataSend,
                beforeSend: function () {
                    // Permet de remonter en début de liste pour afficher le message
                    $('#amsModal').animate({scrollTop: 0}, 500);
                },
                success: function (data) {
                    if (data.codeRetour == "ok") {
                        // On marque la tournée comme ayant un ordre modifié
                        if ($.inArray(iTourneeId, tourneesOrdreModif) < 0) {
                            tourneesOrdreModif.push(iTourneeId);
                            console.log('Tournées modifiées:');
                            console.log(tourneesOrdreModif);
                        }
                        showModalFlashAlert('success', data.msgRetour, 4500);

                        // Mise à jour des informations de la tournée
//                        $.ajax({
//                            url: sFecthUrl,
//                            success: function (data) {
////                                console.log(data);
//                                for (tourneeJSON in tourneesJSON) {
//                                    if (jsonTourneesAffTbl[tourneeJSON].properties.tId == data.properties.tId) {
//                                        jsonTourneesAffTbl[tourneeJSON] = data;
//                                    }
//                                }
//                            },
//                        });

                        setTimeout(function () {
                            window.location.href = window.location.href;
                        }, 1500);
                    }
                    else {
                        showModalFlashAlert('danger', data.msgErr, 2500);
                    }
                },
                error: function () {
                    showModalFlashAlert('danger', 'Une erreur a été détectée.', 2500);
                },
            });
        }
        return false;
    });

    function print() {
        var data = document.getElementById('map').innerHTML;
        var win = window.open('', '', 'height=500,width=900');
        win.document.write('<style>body{margin:0px}@page{size:landscape;-webkit-transform: rotate(-90deg); -moz-transform:rotate(-90deg);filter:progid:DXImageTransform.Microsoft.BasicImage(rotation=3);}</style><html><head><title></title>');
        win.document.write('</head><body>');
        win.document.write(data);
        win.document.write('</body></html>');
        win.print();
        win.close();
        return true;
    }

    // Inmpression dela carte
    $('.infosCarte #printMap').click(function () {
        print();
    });

    // Bouton Zoombox
//    $('#zboxBtn').click(function() {
//        alert('zb click');
//        var map = GCUI.getMap('map'); // La carte
//        var zb = map.controls[0].zoomBox; // Le controle Zoombox
//        var dp = map.controls[0].dragPan; // Le controle DragPan
//        dp.deactivate();
//        zb.activate();
//    });
});

/**
 * Fonction qui met à jour les coordonnées du point sélectionné dans la carte
 * @param float longitude La longitude du point
 * @param float latitude La latitude du point
 */
function updatePointCoords(longitude, latitude, iconeURL) {
    if (pointAction != 'etape') {
        $('.infosPoint #nouvPointX').html(longitude);
        $('.infosPoint #nouvPointY').html(latitude);

        // Conditionne l'affichage de la zone
        if (!pointSelectionMode) {
            console.log('Permutation de l affichage');
            // On permute l'information d'affichage
            $('#infosMapPoint .infosCarte').hide();
            $('#infosMapPoint #mapLegende').hide();

            $('#infosMapPoint .infosPoint').show();
        }
        else {
            console.log('Pas de permutation de l affichage');
            console.log('Consignation des coords: ' + longitude + ' ' + latitude);
            ptCorrectifRnvp.x = longitude;
            ptCorrectifRnvp.y = latitude;
            ptCorrectifRnvp.init = 'coords';
            console.log(ptCorrectifRnvp);

            // On permet à nouveau l'affichage de la zone d'info
            pointSelectionMode = false;
            if (!iconeURL) {
                iconeURL = map.pinPointURL;
            }

            getAdressForPoint(); // Géocodage inversé du point sélectionné
        }

        // On place un marqueur sur la carte
        map = GCUI.getMap('map');

        // Suppression d'un point précédemment affiché
        if (markerPointAction && newMarkerLayer) {
            newMarkerLayer.removeFeatures(newMarkerLayer.getFeatureById(markerPointFeatureID));
        }

        markerPointAction = new OpenLayers.Geometry.Point(longitude, latitude).transform(new OpenLayers.Projection("EPSG:4326"), new OpenLayers.Projection("EPSG:27572"));
        var feature = new OpenLayers.Feature.Vector(
                markerPointAction,
                {some: 'data'},
        {externalGraphic: iconeURL, graphicHeight: 32, graphicWidth: 32});
        console.log(feature);
        markerPointFeatureID = feature.id; // On enregistre l'ID du feature en global
        newMarkerLayer.addFeatures(feature); // Ajout du feature à la couche
    }

    // On réinitialise notre valeur témoin
    pointAction = '';
}


// Fonction qui rend les tournées vides "utilisables" sur la carte
function init_tournees_vides() {
    if (tournees_vides.length > 0) {
        console.log(tournees_vides);
        $('#amsModal4').modal('show');

        // Formatage du message d'avertissement
        if (tournees_vides.length == 1) {
            var sListeTourneesVide = tournees_vides[0].code;
        }
        else {
            var aListeTournees = [];
            for (var i in tournees_vides) {
                aListeTournees.push(tournees_vides[i].code);
            }
            sListeTourneesVide = aListeTournees.join(', ');
            if (aListeTournees.length > 1) {
                var pos = sListeTourneesVide.lastIndexOf(',');
                sListeTourneesVide = sListeTourneesVide.substring(0, pos) + ' ou ' + sListeTourneesVide.substring(pos + 1);
            }
        }
        $('#amsModal4 .liste_tournees').html(sListeTourneesVide);

    }
}

/**
 * Fonction qui retourne les informations du formulaires qui permettront
 * de requêter la BDD pour l'affichage des tournées
 */
function updateTourneesFormInfos() {
    depot_id = $('#ams_cartobundle_selectiontourneetype_depot option:selected').val();
    date = $('#ams_cartobundle_selectiontourneetype_date').val();
}

function in_array(needle, haystack, argStrict) {
    //  discuss at: http://phpjs.org/functions/in_array/
    // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // improved by: vlado houba
    // improved by: Jonas Sciangula Street (Joni2Back)
    //    input by: Billy
    // bugfixed by: Brett Zamir (http://brett-zamir.me)
    //   example 1: in_array('van', ['Kevin', 'van', 'Zonneveld']);
    //   returns 1: true
    //   example 2: in_array('vlado', {0: 'Kevin', vlado: 'van', 1: 'Zonneveld'});
    //   returns 2: false
    //   example 3: in_array(1, ['1', '2', '3']);
    //   example 3: in_array(1, ['1', '2', '3'], false);
    //   returns 3: true
    //   returns 3: true
    //   example 4: in_array(1, ['1', '2', '3'], true);
    //   returns 4: false

    var key = '',
            strict = !!argStrict;

    //we prevent the double check (strict && arr[key] === ndl) || (!strict && arr[key] == ndl)
    //in just one for, in order to improve the performance 
    //deciding wich type of comparation will do before walk array
    if (strict) {
        for (key in haystack) {
            if (haystack[key] === needle) {
                return true;
            }
        }
    } else {
        for (key in haystack) {
            if (haystack[key] == needle) {
                return true;
            }
        }
    }
}

function dump(obj) {
    var out = '';
    for (var i in obj) {
        out += i + ": " + obj[i] + "\n";
    }

    alert(out);

    var pre = document.createElement('pre');
    pre.innerHTML = out;
    document.body.appendChild(pre)
}

function arr_diff(a1, a2)
{
    var o1 = {}, o2 = {}, diff = [], i, len, k;
    for (i = 0, len = a1.length; i < len; i++) {
        o1[a1[i]] = true;
    }
    for (i = 0, len = a2.length; i < len; i++) {
        o2[a2[i]] = true;
    }
    for (k in o1) {
        if (!(k in o2)) {
            diff.push(k);
        }
    }
    for (k in o2) {
        if (!(k in o1)) {
            diff.push(k);
        }
    }
    return diff;
}

/**
 * Méthode qui permet de faire zoomer la carte sur un point en particulier
 * Un 3e paramètre optionnel existe, un entier qui définit le niveau de zoom
 * @param {float} long La longitude
 * @param {float} lat La latittude
 * @returns {undefined}
 */
function goToZoom(long, lat) {
    var iZoom = arguments.length === 3 ? parseInt(arguments[2]) : iZoomDft;
    var map = GCUI.getMap('map');
    var lonlat = new OpenLayers.LonLat(long, lat);
    map.moveTo(lonlat.transform(new OpenLayers.Projection('EPSG:4326'),
            new OpenLayers.Projection(map.getProjection())), iZoom);
}


/**
 * ...
 * @param {array} aList
 * @param {int} iAboNum
 * @returns {array} result Le tableau contenat les résultats correspondants
 */
function getAboNumFromList(aList, iAboNum) {
    var result = $.grep(aList.features, function (e) {
        return e.properties.aId == iAboNum;
    });
    return result;
}

/**
 * Méthode qui lance la recherche et l'affichage d'un abonné sur la carte
 * @param {type} oSearch
 * @param {string} sMode
 * @returns {undefined}
 */
function showAbonnePoint(oSearch, sMode) {
    var aResults = [];
    jsonTourneesAffTbl.forEach(function (element) {
        var aResult = getAboNumFromList(element, oSearch.numAbo);
        if (aResult.length > 0) {
            aResult.forEach(function (element) {
                aResults.push(element);
            });
        }
    });


    // Affichage du 1er point trouvé, mais ce comportement peut être remplacé par autre chose
    if (aResults.length > 0) {
        var oAbo = aResults[0];
        
        if (sMode == 'info'){
            return oAbo;
        }
        
        goToZoom(oAbo.geometry.coordinates[0], oAbo.geometry.coordinates[1]);
        var projectionPoint = new OpenLayers.LonLat(oAbo.geometry.coordinates[0], oAbo.geometry.coordinates[1]);
        projectionPoint.transform(new OpenLayers.Projection("EPSG:4326"), new OpenLayers.Projection("EPSG:27572"));
        console.log('point projeté', projectionPoint);

        var oFakeMarker = {
            feature: {
                attributes: oAbo.properties,
                geometry: {
                    x: projectionPoint.lon,
                    y: projectionPoint.lat
                }
            },
        };

        // Ouverture de l'info-bulle (simulation du clic sur le point de la carte)
        coucheControl.layer.events.listeners.featureselected[0]['func'](oFakeMarker)
    }
    console.log(aResults);

}

function displaySearchInfo(oAbo){
    console.log('Affichage du résultat de recherche', oAbo);
    $('div#amsModalSearch #results .produit').html(oAbo.properties.aTitreProduit);
    $('div#amsModalSearch #results .ville').html(oAbo.properties.aCityAbonne);
    $('div#amsModalSearch #results .adresse').html(oAbo.properties.aAdresseAbonne);
    $('div#amsModalSearch #results .nom').html(oAbo.properties.aNomClient);
    $('div#amsModalSearch #results .id').html(oAbo.properties.aId);
    $('div#amsModalSearch #results #showAboBtn').off().on('click', function(aId){
        showAbonnePoint({numAbo: oAbo.properties.aId});
        $('div#amsModalSearch').modal('hide');
    });
    $('div#results').show();
}