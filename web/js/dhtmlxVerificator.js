function validError(value, id, ind, msg) {
    //if (DHTMLXDEBUG) dhtmlx.message({text: "validError*" + value + "*" + id + "*" + ind + "*" + msg, expire:DHTMLXEXPIRE});
//        if (grid.getSelectedRowId()!=0 || id!=0){
//    if (grid.getSelectedRowId() !== 0) {
    if (rowAction=='deleted') return true; // 12/10/2016 On ne fait pas de vérification en suppression
    if (!rowIsInsertion) {
        grid.selectRowById(id);
        dhtmlx.alert({
            type: "alert-error",
            text: id + ' : ' + msg,
            expire: -1
        });
    }
    return false;
}

function isNotEmpty(value, id, ind) {
    if (value === "") {
        return validError(value, id, ind, "Le champs doit être renseigné.");
    }
    return true;
}
function isLength(value, id, ind, length) {
    if (value.length !== length) {
        return validError(value, id, ind, "Le champs doit avoir une longueur de " + length.toString() + " caractères.");
    }
    return true;
}
function isLength1(value, id, ind) {
    return isLength(value, id, ind, 1);
}
function isLength2(value, id, ind) {
    return isLength(value, id, ind, 2);
}
function isLength3(value, id, ind) {
    return isLength(value, id, ind, 3);
}
function isLength4(value, id, ind) {
    return isLength(value, id, ind, 4);
}
function isLength6(value, id, ind) {
    return isLength(value, id, ind, 6);
}
function isMax(value, id, ind, length) {
    if (!value || value.length > length) {
        return validError(value, id, ind, "Le champs doit avoir une longueur de " + length.toString() + " caractères maximum.");
    }
    return true;
}
function isMaxOrNull(value, id, ind, length) {
    if (value.length > length) {
        return validError(value, id, ind, "Le champs doit avoir une longueur de " + length.toString() + " caractères maximum.");
    }
    return true;
}
function isMax10(value, id, ind) {
    return isMax(value, id, ind, 10);
}
function isMax10OrNull(value, id, ind) {
    return isMaxOrNull(value, id, ind, 10);
}
function isMax50(value, id, ind) {
    return isMax(value, id, ind, 128);
}
function isMax128(value, id, ind) {
    return isMax(value, id, ind, 128);
}
function isLibelle(value, id, ind) {
    return isMax(value, id, ind, 32);
}
function isCommentaire(value, id, ind) {
    return (!value || isMax(value, id, ind, 1024));
}
function isOrdre(value, id, ind) {
    if (!/^[1-9]$/.test(value)) {
        return validError(value, id, ind, "Le champs doit être un ordre.");
    }
    return true;
}
function isNumeric3(value, id, ind) {
    if (!/^[0-9][0-9][0-9]$/.test(value)) {
        return validError(value, id, ind, "Le champs doit être un code numérique sur 3 caractères.");
    }
    return true;
}
function isQuantiteNonZero(value, id, ind) {
    if (!/^([1-9][0-9][0-9][0-9]|[1-9][0-9][0-9]|[1-9][0-9]|[1-9])$/.test(value)) {
        return validError(value, id, ind, "Le champs doit être une quantité valide.");
    }
    return true;
}
function isQuantite(value, id, ind) {
    if (!/^([1-9][0-9][0-9][0-9]|[1-9][0-9][0-9]|[1-9][0-9]|[0-9])$/.test(value)) {
        return validError(value, id, ind, "Le champs doit être une quantité valide.");
    }
    return true;
}
function isQuantiteOrNull(value, id, ind) {
    if (!/^([1-9][0-9][0-9][0-9]|[1-9][0-9][0-9]|[1-9][0-9]|[0-9])$/.test(value) && value != "") {
        return validError(value, id, ind, "Le champs doit être une quantité valide ou non renseignée.");
    }
    return true;
}function isPoids(value, id, ind) {
    if (!/^([1-9][0-9][0-9][0-9][0-9][0-9][0-9]|[1-9][0-9][0-9][0-9][0-9][0-9]|[1-9][0-9][0-9][0-9][0-9]|[1-9][0-9][0-9][0-9]|[1-9][0-9][0-9]|[1-9][0-9]|[0-9])$/.test(value)) {
        return validError(value, id, ind, "Le champs doit être une poids valide.");
    }
    return true;
}
function isPoidsOrNull(value, id, ind) {
    if (!/^([1-9][0-9][0-9][0-9][0-9][0-9][0-9]|[1-9][0-9][0-9][0-9][0-9][0-9]|[1-9][0-9][0-9][0-9][0-9]|[1-9][0-9][0-9][0-9]|[1-9][0-9][0-9]|[1-9][0-9]|[0-9])$/.test(value) && value != "") {
        return validError(value, id, ind, "Le champs doit être une poids valide ou non renseigné.");
    }
    return true;
}
function isDate(value, id, ind) {
    // ^(?:(?:31(\/|-|\.)(?:0?[13578]|1[02]))\1|(?:(?:29|30)(\/|-|\.)(?:0?[1,3-9]|1[0-2])\2))(?:(?:1[6-9]|[2-9]\d)?\d{2})$|^(?:29(\/|-|\.)0?2\3(?:(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00))))$|^(?:0?[1-9]|1\d|2[0-8])(\/|-|\.)(?:(?:0?[1-9])|(?:1[0-2]))\4(?:(?:1[6-9]|[2-9]\d)?\d{2})$
    // ^(?:(?:31(\/)(?:0?[13578]|1[02]))\1|(?:(?:29|30)(\/)(?:0?[1,3-9]|1[0-2])\2))(?:(?:1[6-9]|[2-9]\d)?\d{2})$|^(?:29(\/)0?2\3(?:(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00))))$|^(?:0?[1-9]|1\d|2[0-8])(\/)(?:(?:0?[1-9])|(?:1[0-2]))\4(?:(?:1[6-9]|[2-9]\d)?\d{2})$
    if (!/^(?:(?:31(\/)(?:0?[13578]|1[02]))\1|(?:(?:29|30)(\/)(?:0?[1,3-9]|1[0-2])\2))(?:(?:1[6-9]|[2-9]\d)?\d{2})$|^(?:29(\/)0?2\3(?:(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00))))$|^(?:0?[1-9]|1\d|2[0-8])(\/)(?:(?:0?[1-9])|(?:1[0-2]))\4(?:(?:1[6-9]|[2-9]\d)?\d{2})$/.test(value)) {
        return validError(value, id, ind, "Le champs doit être une date valide.");
    }
    return true;
}
function isDateOrNull(value, id, ind) {
    // ^(?:(?:31(\/|-|\.)(?:0?[13578]|1[02]))\1|(?:(?:29|30)(\/|-|\.)(?:0?[1,3-9]|1[0-2])\2))(?:(?:1[6-9]|[2-9]\d)?\d{2})$|^(?:29(\/|-|\.)0?2\3(?:(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00))))$|^(?:0?[1-9]|1\d|2[0-8])(\/|-|\.)(?:(?:0?[1-9])|(?:1[0-2]))\4(?:(?:1[6-9]|[2-9]\d)?\d{2})$
    // ^(?:(?:31(\/)(?:0?[13578]|1[02]))\1|(?:(?:29|30)(\/)(?:0?[1,3-9]|1[0-2])\2))(?:(?:1[6-9]|[2-9]\d)?\d{2})$|^(?:29(\/)0?2\3(?:(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00))))$|^(?:0?[1-9]|1\d|2[0-8])(\/)(?:(?:0?[1-9])|(?:1[0-2]))\4(?:(?:1[6-9]|[2-9]\d)?\d{2})$
    if (!/^(?:(?:31(\/)(?:0?[13578]|1[02]))\1|(?:(?:29|30)(\/)(?:0?[1,3-9]|1[0-2])\2))(?:(?:1[6-9]|[2-9]\d)?\d{2})$|^(?:29(\/)0?2\3(?:(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00))))$|^(?:0?[1-9]|1\d|2[0-8])(\/)(?:(?:0?[1-9])|(?:1[0-2]))\4(?:(?:1[6-9]|[2-9]\d)?\d{2})$/.test(value) && value != "") {
        return validError(value, id, ind, "Le champs doit être une date valide ou non renseignée.");
    }
    return true;
}
function isTime(value, id, ind) {
    // ^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$ --  time without leading zero
    //^([0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$ --  RegExp for time with leading zero
    if (!/^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/.test(value)) {
        return validError(value, id, ind, "Le champs doit être une heure valide.");
    }
    return true;
}
function isTimeOrNull(value, id, ind) {
    if (!/^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/.test(value) && value != "") {
        return validError(value, id, ind, "Le champs doit être une heure valide ou non renseignée.");
    }
    return true;
}
function isDuree(value, id, ind) {
//    if (!/^([0-9]|[0-5][0-9])(:[0-5][0-9]|:[0-5]|:|)$/.test(value)) {
    if (!/^([0-9]|[0-5][0-9]):[0-5][0-9]$/.test(value)) {
        return validError(value, id, ind, "Le champs doit être une durée valide.");
    }
    return true;
}
function isDureeMax5(value, id, ind) {
    if (!/^(([0-4]|0[0-4]):[0-5][0-9])|(5|05):00$/.test(value)) {
        return validError(value, id, ind, "Le champs doit être une durée valide inferieure à 05:00.");
    }
    return true;
}
function isDureeOrNull(value, id, ind) {
    if (!/^([0-9]|[0-5][0-9]):[0-5][0-9]$/.test(value) && value != "") {
        return validError(value, id, ind, "Le champs doit être une durée valide ou non renseignée.");
    }
    return true;
}
function isDureeMax5OrNull(value, id, ind) {
    if (!/^(([0-4]|0[0-4]):[0-5][0-9])|(5|05):00$/.test(value) && value != "") {
        return validError(value, id, ind, "Le champs doit être une durée valide inferieure à 05:00 ou non renseignée.");
    }
    return true;
}
function isDureeMax10OrNull(value, id, ind) {
    if (!/^(([0-9]|0[0-9]):[0-5][0-9])|10:00$/.test(value) && value != "") {
        return validError(value, id, ind, "Le champs doit être une durée valide inferieure à 10:00 ou non renseignée.");
    }
    return true;
}
function isDureeSeconde(value, id, ind) {
//    if (!/^([0-9]|[0-5][0-9])(:[0-5][0-9]|:[0-5]|:|)$/.test(value)) {
    if (!/^([0-9]|[0-5][0-9]):[0-5][0-9]:[0-5][0-9]$/.test(value)) {
        return validError(value, id, ind, "Le champs doit être une durée avec seconde valide.");
    }
    return true;
}
function isValRem(value, id, ind) {
    if (!/^([0-9]|).([0-9][0-9][0-9][0-9][0-9]|[0-9][0-9][0-9][0-9]|[0-9][0-9][0-9]|[0-9][0-9]|[0-9])$/.test(value)) {
        return validError(value, id, ind, "Le champs doit être une valeur de rémunération valide.");
    }
    return true;
}


function isKm(value, id, ind) {
    if (!/^([1-2][0-9][0-9]|[0-9][0-9]|[0-9])$/.test(value)) {
        return validError(value, id, ind, "Le champs doit être un nombre de kilomètres valide (0-299).");
    }
    return true;
}
function isKmOrNull(value, id, ind) {
    if (!/^([1-2][0-9][0-9]|[0-9][0-9]|[0-9])$/.test(value) && value != "") {
        return validError(value, id, ind, "Le champs doit être un nombre de kilomètres valide (0-299) ou non renseigné.");
    }
    return true;
}

function isKm2(value, id, ind) {
    if (!/^([1-2][0-9][0-9]|[0-9][0-9]|[0-9]|[1-2][0-9][0-9].[0-9]|[0-9][0-9].[0-9]|[0-9].[0-9])$/.test(value)) {
        return validError(value, id, ind, "Le champs doit être un nombre de kilomètres valide (0-299).");
    }
    return true;
}
function isKm2OrNull(value, id, ind) {
    if (!/^([1-2][0-9][0-9]|[0-9][0-9]|[0-9]|[1-2][0-9][0-9].[0-9]|[0-9][0-9].[0-9]|[0-9].[0-9])$/.test(value) && value != "") {
        return validError(value, id, ind, "Le champs doit être un nombre de kilomètres valide (0-299) ou non renseigné.");
    }
    return true;
}

