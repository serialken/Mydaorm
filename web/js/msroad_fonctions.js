// Surcharge custom de JQuery
// 
// Permet de masquer une option d'un select sous tous les navigateurs (chrome inclus)
jQuery.fn.toggleOption = function (show) {
    if (!show)
        jQuery(this).remove();
};

/********** Image d'attente lors d'un traitement **********/
/* Affiche image d'attente lors d'un traitement (chargement de page, ajax, ...)  */
var afficheImgAttente = function () {
    // si l'image d'attente n'est pas instancie, on le cree avant de l'afficher
    if ($('.imgAttente').length == 0) {
        $('body').append('<div class="imgAttente" />');
    }
    $('.imgAttente').fadeIn(200);	// $('.imgAttente').show();

    // si l'image d'attente reste bloque plus de 60s, on le ferme
    setTimeout('masqueImgAttente()', 60000);
};

/* Affiche image d'attente lors d'un traitement (chargement de page, ajax, ...)  */
var masqueImgAttente = function () {
    $('.imgAttente').fadeOut(200);// $('.imgAttente').hide();
};

/*
 *  creation d'un objet XMLHttpRequest
 * @returns {XMLHttpRequest|ActiveXObject|Boolean}
 */
function getHTTPObject() {
    var xmlhttp = false;
    if (window.XMLHttpRequest)
        xmlhttp = new XMLHttpRequest();
    else if (window.ActiveXObject) {
        try {
            xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
        }
        catch (e) {
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
    }
    return xmlhttp;
}

function handleHttpResponse(zonediv, objhttp) {
    if (objhttp.readyState == 4) {
        document.getElementById(zonediv).innerHTML = unescape(objhttp.responseText);
    }
}
/*
 * remplir une zone avec le contenu  d'une url
 */
function ChargeZone(url, zonediv) {
    var http = getHTTPObject();
    http.open("POST", url, true);
    http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    http.onreadystatechange = function () {
        handleHttpResponse(zonediv, http);
    };
    http.send(null);
}

function ChargeModal(path, param1, param2) {
    $.ajax({
        url: path,
        type: "GET",
        data: {param1: param1, param2: param2},
        success: function (data) {
            $('#amsModal').html(data);
        },
        error: function (xhr, ajaxOptions, thrownError) {
            $('#amsModalLabel').html('Une erreur est survenue.');
            $('#amsModalBody').html(thrownError + ' - code erreur:' + xhr.status);
        }
    });
}

/**
 * Fonction qui affiche une alerte éphémère dans la modale après une opération Ajax
 * @param string type Le type d'alerte (info|warning|danger|success)
 * @param string msg Le message à afficher
 * @param int duree La durée d'affichage du message
 */
function showModalFlashAlert(type, msg, duree) {
    $('#modalAlerte .alert').html('');
    // Changement de classes
    $('#modalAlerte .alert').removeClass('alert-defaultLoad');
    $('#modalAlerte .alert').addClass('alert-' + type);

    // Ajout du message
    $('#modalAlerte .alert').append('<p>' + msg + '</p>');

    // Affichage éphémère
    if (duree > 0) {
        showFlashAlert('#modalAlerte .alert', duree);
    }
    else {
        // Affichage sans mise en place d'un timeout
        $('#modalAlerte .alert').fadeIn();
    }
}


/**
 * Fonction qui affiche une alerte éphémère sur un selecteur précis après une opération Ajax 
 * @param srting valeur selecteur sur lequel on veut afficher (précisez le nom de l'id sans le #) 
 * @param string type Le type d'alerte (info|warning|danger|success)
 * @param string msg Le message à afficher
 * @param int duree La durée d'affichage du message
 */
function showSelecteurFlashAlert(valeur, type, msg, duree) {
    var selecteur = $('#' + valeur + ' .alert');
    selecteur.html('');
    // Changement de classes
    selecteur.removeClass('alert-defaultLoad');
    selecteur.addClass('alert-' + type);

    // Ajout du message
    selecteur.append('<p>' + msg + '</p>');

    // Affichage éphémère
    if (duree > 0) {
        var temp = '#' + valeur + ' .alert';
        showFlashAlert(temp, duree);
    }
    else {
        // Affichage sans mise en place d'un timeout
        selecteur.fadeIn();
    }
}


/**
 * Fonction qui affiche une alerte éphémère
 * @param string selecteur le sélecteur CSS/JQuery de l'alerte
 * @param int duree La durée d'affichage souhaitée en millisecondes
 */
function showFlashAlert(selecteur, duree) {
    var alerte = $(selecteur).fadeIn();
    window.setTimeout(function () {
        alerte.fadeOut();
    }, duree);
}

/**
 * Opérations lancées au chargement de la page
 */
$(document).ready(function () {
    // On masque la partie alerte lorsqu'elle est chargée dans une modale en attente d'utilisation via Ajax
    $('div#modalAlerte .alert.alert-defaultLoad').removeAttr('fade').removeAttr('in').hide();
});

/**
 * Fonction pour revenir en haut de la page
 */
function moveToTop() {
    $('html, body').animate({scrollTop: 50}, 'slow');
}
;

/**
 * Fonction pour revenir au milieu de la page
 */
function moveToMiddle() {
    $('html, body').animate({scrollTop: 175}, 'slow');
}
;

/*
 * Fonction qui affiche une infos-bulles sur un lien 
 */
function affPopoverLien(elem) {
    var titre = $(elem).attr('data-description');

    $(elem).popover({
        trigger: 'hover',
        title: titre,
        html: true
    });
}

//Variable globale ou on enregistre le tr du produit qu'on va supprimer appeler dans delete Product
var delLineProduct = '';

/*
 * Fonction pour supprimer des donnés(produit) via un appel ajax
 * @param string selecteur  avec a l'interieur des data avec ttes les données
 */
function deleteProduct(selecteur)
{
    var route = $(selecteur).data("route");
    var valeur = $(selecteur).data("valeur");
    //var societeId = $(selecteur).data("societe");
    var tmp = $(selecteur).data("parent");
    var parentCall = $(tmp);
    $.ajax({
        url: route,
        type: "GET",
        cache: false,
        success: function (data) {
            parentCall.remove();
            showSelecteurFlashAlert(valeur, 'success', data.alert, 5000);
            $(delLineProduct).remove();
        },
        error: function (data) {
            alert("An unexpeded error occured. Please reload the page or contact the administrator if this error persists");
        }
    });
}

/*
 * Fonction pour supprimer des donnés(societe) via un appel ajax
 * @param string selecteur  avec a l'interieur des data avec ttes les données
 */
function deleteSociety(selecteur)
{
    var route = $(selecteur).data("route");
    var valeur = $(selecteur).data("valeur");
    var tmp = $(selecteur).data("parent");
    var parentCall = $(tmp);
    //console.log(route);
    //alert('toto');
    //return;
    // console.log(valeur);
    //console.log(parentCall);
    //return;
    $.ajax({
        url: route,
        type: "GET",
        cache: false,
        success: function (data) {
            parentCall.remove();
            showSelecteurFlashAlert(valeur, 'success', data.alert, 5000);
            window.setTimeout(function () {
                window.location.reload();
            }, 3000);
        },
        error: function (data) {
            alert("An unexpeded error occured. Please reload the page or contact the administrator if this error persists");
        }
    });
}

/*
 * Fonction qui gere la confirmation avant la suppression(produit) 
 * @param string routeSupp la route concerné que l'on va appeler plutard
 * @param string societeId l'id de la societe pour construire des selecteur avec des id
 * @param string produitId l'id du produit pour construire des selecteur avec des id  Ex: 'confirmflashAlerteSuppProduit_produitId' 
 * @param string produitName corespond au nom du produit pour renseigner les message flash
 * @param string selecteur correspond a lobjet tr de la ligne a supprimer dans le tableau html
 */
function suppConfirmProduit(routeSupp, societeId, produitId, produitName, selecteur)
{
    delLineProduct = $(selecteur).closest('tr');
    var tmp = $('#ajoutProduit_' + societeId);
    var divConfirm = '<div id="confirmFlashAlerteSuppProduit_' + produitId + '" class="alert alert-danger alert-dismissible" role="alert">'
            + '<strong>Attention!</strong> êtes vous sur de vouloir supprimer <strong>' + produitName + '</strong> , la suppression sera définitive.'
            + '<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true"></span>Annuler</button>'
            + '<button type="button" class="close" id="boutonConfirmProduit"  data-valeur="flashAlerteSuppProduit_'
            + societeId + '"  data-route="' + routeSupp + '"  data-parent="confirmFlashAlerteSuppProduit_' + produitId + '" data_societe="' + societeId + '" onclick="deleteProduct(this);" data-dismiss="alert"><span aria-hidden="true"></span>Confirmer</button>'
            + '</div>';
    // on rajoute la div de confirmation juste apres me lien ajouter un produit
    tmp.append(divConfirm);
}

/*
 * Fonction qui gere la confirmation avant la suppression(societe) 
 * @param string routeSupp la route concerné que l'on va appeler plutard
 * @param string societeId 
 * @param string societeName 
 */
function suppConfirmSociete(routeSupp, societeId, societeName)
{
    //console.log(routeSupp);
    //console.log(societeId);
    //console.log(societeName);
    var tmp = $('#flashAlerteSuppSociete_' + societeId);
    var divConfirm = '<div id="confirmFlashAlerteSuppSociete_' + societeId + '" class="alert alert-danger alert-dismissible" role="alert">'
            + '<strong>Attention!</strong> êtes vous sur de vouloir supprimer <strong>' + societeName + '</strong> , la suppression sera définitive.'
            + '<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true"></span>Annuler</button>'
            + '<button type="button" class="close" id="boutonConfirmProduit"  data-valeur="flashAlerteSuppSociete_'
            + societeId + '"  data-route="' + routeSupp + '"  data-parent="confirmFlashAlerteSuppSociete_' + societeId + '" data_societe="' + societeId + '" onclick="deleteSociety(this);" data-dismiss="alert"><span aria-hidden="true"></span>Confirmer</button>'
            + '</div>';
    // on rajoute la div de confirmation juste avant la div de flash alert qui pour le moment est caché
    tmp.prepend(divConfirm);
}


function GetMinutes(timeStr) {

    var str = timeStr.split(':');
    var hours = parseInt(str[0]);
    var mins = parseInt(str[1]);
    var totalMins = hours * 60 + mins;
    return totalMins;
}

function addTime(time1, time2) {
    var min1 = GetMinutes(time1.toString());
    var min2 = GetMinutes(time2.toString());
    var totalMins = parseInt(min1 + min2);

    var hours = parseInt(totalMins / 60);
    var mins = parseInt(totalMins % 60);

    return hours + ":" + mins;
}

function isOrNotTime(value) {
    if (!/^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/.test(value)) {
        return false;
    }
    return true;
}


/* compare une date à la date du jour */
function checkDateInpuWithTodays(inputDateText) {
    //get today's date in string
    var todayDate = new Date();
    //need to add one to get current month as it is start with 0
    var todayMonth = todayDate.getMonth() + 1;
    var todayDay = todayDate.getDate();
    var todayYear = todayDate.getFullYear();
    var todayDateText = todayDay + "/" + todayMonth + "/" + todayYear;

    //Convert both input to date type
    var inputToDate = Date.parse(inputDateText);
    var todayToDate = Date.parse(todayDateText);

    //compare dates
    if (inputToDate >= todayToDate) {
        return true;
    }
    else if (inputToDate < todayToDate) {
        return false;
    }

}



// Déplacement animé entre les ancres
$(document).ready(function () {
    $('a.scroll[href^="#"]').live('click', function (e) {
        console.log('click');
        e.preventDefault();

        var target = this.hash;
        var $target = $(target);

        $('html, body').stop().animate({
            'scrollTop': $target.offset().top
        }, 900, 'swing', function () {
            window.location.hash = target;
        });
    });
});

/**
 * Alimente les <element>s dans <selecteur> avec les items de <liste> en utilisant les clés de <map>
 * @param {string} Le sélecteur permettant de cibler le select
 * @param {object} liste La liste contenant les options
 * @param {object} map La table de correspondance valeur/label
 * @param {object} formatFunc La fonction optionnelle de formatage des données avant insertion
 */
function pushOptionsToElem(selecteur, element, liste, map, formatFunc){
    // Invocation de la fonction de formatage
    if (formatFunc){
        formatFunc(liste);
    }
    
    for (index in liste){
//        console.log('ajout...'+liste[index][map]);
        switch (element){
            case '<option>':
                $(selecteur).append($('<option>', {value : liste[index][map.val], text: liste[index][map.label]}));
                break;
            case '<li>':
                $(selecteur).append('<li data-val="'+liste[index][map.val]+'">'+liste[index][map.label]+'</li>');
                break;
            case '<li|sortable>':
                $(selecteur).append('<li data-val="'+liste[index][map.val]+'" id="'+map.sortableId+liste[index][map.val]+'">'+liste[index][map.label]+'</li>');
                break;
            case '<li|non-sortable>':
                $(selecteur).append('<li class="non-sortable" data-val="'+liste[index][map.val]+'" id="'+map.sortableId+liste[index][map.val]+'">'+liste[index][map.label]+'</li>');
                break;
        }
    };
}

/*
 * Fonction qui affiche une boite de dialogue avec l'utilisateur
 * @param object domDestination Le sélecteur du noeud dans lequel le contenu doit être inséré
 * @param string content Le contenu de la boite de dialogue
 * @param object preFunc La fonction à appeler avant de faire quoi que ce soit
 * @param object postFunc La fonction à appeler à la fin
 */
function openDialogBox(domDestination, content, preFunc, postFunc)
{
    // Fonction de début
    if (preFunc) {
        preFunc();
    }

    $(domDestination).html(content);

    // Fonction de fin
    if (postFunc) {
        postFunc();
    }
}

/**
 * Méthode affichant une notification n'importe où dans la page
 * @param string type Le type notification (success|error|info)
 * @param string title Le titre de la notification
 * @param string msg Le corps du message de notification
 */
function displayNotification(type, title, msg, tempo) {
    var notification = {
        title: title,
        text: msg,
        type: type
    }

    // Temporisation ?
    if (tempo) {
        notification.delay = tempo;
    }

    new PNotify(notification);
}

/**
 * Objet de configuration globale
 */
window.backendApp = {
    env : {
        code: window.appEnv,
    },
    urlRewriteMode: false,
    urlBase : '/web/',
    getBaseUrl: function(sModuleRoot){
        if (this.env.code){
            if (this.urlRewriteMode){
                console.log('Logique à implémenter');
            }
            else{
                switch (window.appEnv){
                    case 'prod':
                        return this.urlBase + 'app.php';
                        break;
                    default:
                        console.log('pas en prod');
                        return document.URL.substring(0, document.URL.lastIndexOf(sModuleRoot));
                        break;
                }
            }
        }
        else{
            console.error('Pas d\'environnement trouvé');
            return false;
        }
    }
};

/**
 * Fonction qui surligne visuellement un élément pendant une durée donnée
 * Encapsulation d'une fonction JQuery UI
 * @see https://api.jqueryui.com/highlight-effect/
 */
function highlightElem(sTarget, sType, iDelay){
    var color;
    var iTempo = iDelay || 1000;
    switch (sType){
        case 'error':
            color = "#E57373";
            break;
        case 'info':
            color = "#bce8f1";
            break;
    }
    
    $(sTarget).effect( "highlight" , {'color':color}, iTempo);
}

/*
 * Supprime la partie heure (Heure, Minutes, Secondes et Millisecondes)
 * d'un objet date
 * 
 */
function stripTimeFromDateObj(oDate){
    oDate.setHours(0);
    oDate.setMinutes(0);
    oDate.setSeconds(0);
    oDate.setMilliseconds(0);
    
    return oDate;
}

/**
 * Convertit une date au format FR vers le format US et vice versa
 * @param {string} sDate La date au format FR
 * @param {string} sDelimiter Le délimiteur du champ date
 * @returns {string} sDateUs La date au format US
 */
function DateFormatFrUs(sDate, sDelimiter){
    var sDateUs;
    
    var aDateParts = sDate.split(sDelimiter);
    var sDateUs = aDateParts[1] + sDelimiter + aDateParts[0] + sDelimiter + aDateParts[2];
    
    return sDateUs;
}
