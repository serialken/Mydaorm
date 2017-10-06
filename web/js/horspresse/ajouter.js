/* ========================================================================
 * MRoad Hors Presse: ajouter.js v0.1
 * Ajout de campagne Hors Presse
 * ========================================================================
 * Author: Marc-Antoine Adélise
 * email: marcantoine.adelise@amaury.com
 * ======================================================================== */
(function () {
    console.log('JS d\'ajout de campagne');

    var oHorsPresse = window.horspresse;

    // changement de l'étape
    oHorsPresse.currentState.mode = 'add';
    oHorsPresse.currentState.slug = 'ajouter';
    oHorsPresse.currentState.step = 1;

    oHorsPresse.config = {
        steps: {
            max: 10,
            1: {
                initValues: {
                    '#titre': '',
                    '#societe': '',
                    '#produit': '',
                    '#date_debut': '',
                    '#date_fin': '',
                },
                validation: {
                    '#titre': [
                        {test:
                                    {
                                        type: 'min-chars',
                                        min: 3
                                    },
                            'errMsg': 'Merci d\'entrer un nom de campagne d\'au moins 3 caractères.'
                        },
                        {test:
                                    {
                                        type: 'libelle-unique-url',
                                        url: oHorsPresse.baseUrls('check')
                                    }
                            , 'errMsg': 'Ce nom de campagne existe déjà.'},
                    ],
                    '#societe': [
                        {test:
                                    {
                                        type: 'gt',
                                        than: 0
                                    },
                            'errMsg': 'Merci de sélectionner une société cliente.'
                        }
                    ],
                    '#produit': [
                        {test:
                                    {
                                        type: 'gt',
                                        than: 0
                                    },
                            'errMsg': 'Merci de sélectionner un produit.'
                        }
                    ],
                    '#date_debut': [
                        {test:
                                    {
                                        type: 'relative-timediff',
                                        minDays: +3,
                                        from: 'now'
                                    },
                            'errMsg': 'La date de début doit se trouver à au moins 3 jours de maintenant'
                        }
                    ],
                    '#date_fin': [
                        {test:
                                    {
                                        type: 'relative-timediff',
                                        minDays: 0,
                                        from: '#date_debut'
                                    },
                            'errMsg': 'La date de fin ne peut pas être avant la date de début'
                        }
                    ],
                }
            },
            3: {
                initValues: {
                    'input[type=radio][name=fichier_nominatif]': '1',
                    '#periode_hist': '2',
                },
                validation: {
                    'input[type=radio][name=fichier_nominatif]': [
                        {test:
                                    {
                                        type: 'in-array',
                                        values: ["1", "0"]
                                    },
                            'errMsg': 'Valeur incorrecte'
                        }
                    ],
                    '#periode_hist': [
                        {test:
                                    {
                                        type: 'in-array',
                                        values: ["1", "2", "3", "4"]
                                    },
                            'errMsg': 'Valeur incorrecte'
                        }
                    ],
                },
            },
            5: {
                initValues: {
                    'input[type=radio][name=charge_tournees]': '',
                    '#charge_max': '',
                },
                validation: {
                    'input[type=radio][name=charge_tournees]': [
                        {test:
                                    {
                                        type: 'in-array',
                                        values: ["1", "0"]
                                    },
                            'errMsg': 'Valeur incorrecte'
                        }
                    ],
                    '#charge_max': [
                        {test:
                                    {
                                        type: 'gt',
                                        than: 0
                                    },
                            'errMsg': 'Valeur incorrecte'
                        },
                        {test:
                                    {
                                        type: 'lt',
                                        than: 100001
                                    },
                            'errMsg': 'Charge maximale 100 000 exemplaires.'
                        }
                    ],
                },
            },
            7: {
                initValues: {
                    'input[type=radio][name=choix_debordement]': '',
                    '#debordement_charge_max': '',
                },
                validation: {
                    'input[type=radio][name=choix_debordement]': [
                        {test:
                                    {
                                        type: 'in-array',
                                        values: ["1", "0"]
                                    },
                            'errMsg': 'Valeur incorrecte'
                        }
                    ],
                    '#debordement_charge_max': [
                        {test:
                                    {
                                        type: 'gt',
                                        than: 0
                                    },
                            'errMsg': 'Valeur incorrecte'
                        },
                        {test:
                                    {
                                        type: 'lt',
                                        than: 100001
                                    },
                            'errMsg': 'Charge maximale 100 000 exemplaires.'
                        }
                    ],
                },
            },
            8: {
                initValues: {
                    'input#temps_sup': '',
                    'select#multiselect_to': '',
                },
                validation: {
                    'input#temps_sup': [
                        {test:
                                    {
                                        type: 'gt',
                                        than: -1
                                    },
                            'errMsg': 'Valeur incorrecte'
                        }
                    ],
                    'select#multiselect_to': [
                        {test:
                                    {
                                        type: 'non-empty-array',
                                    },
                            'errMsg': 'Veuillez sélection au moins un flux.'
                        }
                    ],
                },
            }
        }
    }

    /**
     * Méthode qui effectue les tests de validation
     * @param string sTarget La cible du champ à valider
     * @param object oValidInfo Un objet contenant les informations liées à la validation
     * @param object oInfo Un objet contenant des informations complémentaires
     * @returns {bReturn} Faux si la validation n'est pas passée
     */
    oHorsPresse.validate = function (sTarget, oValidInfo, oInfo) {
        var bReturn, bWarning;
        console.log('validation en cours sur le champ ' + sTarget);
        if ($(sTarget).val() == oInfo.default[sTarget]) {
            // Traitement des exceptions
            switch (sTarget) {
                case 'input[type=radio][name=fichier_nominatif]':
                case '#periode_hist':
                    if (oInfo.step == "3")
                        bReturn = true;
                    break;
                default:
                    // Ici on affiche un avertissement pour attirer l'attention plutot qu'une erreur car rien n'a été saisi
                    oHorsPresse.setFormError(oInfo.step, sTarget, '', false, true);
                    oHorsPresse.addFormElemError(sTarget);
                    bReturn = false; // Champ obligatoire non rempli
                    break;
            }
        }
        else {
            switch (oValidInfo['test']['type']) {
                case 'in-array':
                    var mValue = $(sTarget).val();
                    if ($.inArray(mValue, oValidInfo.test.values) >= 0) {
                        bReturn = true;
                    }
                    else {
                        displayNotification('error', 'Erreur détectée', oValidInfo['errMsg']);
                        oHorsPresse.setFormError(oInfo.step, sTarget, oValidInfo['errMsg'], false);
                        oHorsPresse.addFormElemError(sTarget);
                        bReturn = false;
                    }
                    break;
                case 'non-empty-array':
                    var aValue = $(sTarget).val() || [];
                    if (aValue.length > 0 && Array.isArray(aValue)) {
                        oHorsPresse.setFormError(oInfo.step, sTarget, null, true);
                        bReturn = true;
                    }
                    else {
                        displayNotification('error', 'Erreur détectée', oValidInfo['errMsg']);
                        oHorsPresse.setFormError(oInfo.step, sTarget, oValidInfo['errMsg'], false);
                        oHorsPresse.addFormElemError(sTarget);
                        bReturn = false;
                    }
                    break;
                case 'relative-timediff':
                    var sDateSelectFr = $(sTarget).val();
                    var sDateSelect = DateFormatFrUs(sDateSelectFr, '/');
                    var oDateSelect = new Date(sDateSelect);

                    // Sélection de la date de référence
                    switch (oValidInfo['test']['from']) {
                        case 'now':
                            var today = new Date();
                            today = stripTimeFromDateObj(today);
                            var oMinDate = new Date(today);
                            oMinDate.setDate(oMinDate.getDate() + oValidInfo['test']['minDays']);
                            break;
                        default:
                            var date_ref = $(oValidInfo['test']['from']).val();
                            var sDateRef = DateFormatFrUs(date_ref, '/');
                            console.log('date de référence: ', sDateRef);
                            var oMinDate = new Date(sDateRef);
                            console.log('date mini: ', oMinDate);
                            oMinDate.setDate(oMinDate.getDate() + oValidInfo['test']['minDays']);
                            break;
                    }



                    if (oMinDate > oDateSelect) {
                        displayNotification('error', 'Erreur détectée', oValidInfo['errMsg']);
                        oHorsPresse.setFormError(oInfo.step, sTarget, oValidInfo['errMsg'], false);
                        oHorsPresse.addFormElemError(sTarget);
                        bReturn = false;
                    }
                    else {
                        if (oHorsPresse.getNumFormElemError(sTarget) == 0) {
                            oHorsPresse.setFormError(oInfo.step, sTarget, '', true);
                            bReturn = true;
                        }
                    }


                    console.log(oMinDate, sDateSelect);
                    break;
                case 'gt':
                    if (parseInt($(sTarget).val()) <= oValidInfo['test']['than'] || isNaN(parseInt($(sTarget).val()))) {
                        displayNotification('error', 'Erreur détectée', oValidInfo['errMsg']);
                        oHorsPresse.setFormError(oInfo.step, sTarget, oValidInfo['errMsg'], false);
                        oHorsPresse.addFormElemError(sTarget);
                        bReturn = false;
                    }
                    else {
                        if (oHorsPresse.getNumFormElemError(sTarget) == 0) {
                            oHorsPresse.setFormError(oInfo.step, sTarget, '', true);
                            bReturn = true;
                        }
                    }

                    oInfo['tests'][sTarget]--;
                    console.log('état des tests sur ' + oInfo['tests'][sTarget]);
                    break;

                    break;
                case 'lt':
                    if (parseInt($(sTarget).val()) >= oValidInfo['test']['than'] || isNaN(parseInt($(sTarget).val()))) {
                        displayNotification('error', 'Erreur détectée', oValidInfo['errMsg']);
                        oHorsPresse.setFormError(oInfo.step, sTarget, oValidInfo['errMsg'], false);
                        oHorsPresse.addFormElemError(sTarget);
                        bReturn = false;
                    }
                    else {
                        if (oHorsPresse.getNumFormElemError(sTarget) == 0) {
                            oHorsPresse.setFormError(oInfo.step, sTarget, '', true);
                            bReturn = true;
                        }
                    }

                    oInfo['tests'][sTarget]--;
                    console.log('état des tests sur ' + oInfo['tests'][sTarget]);
                    break;

                    break;
                case 'min-chars':
                    if ($(sTarget).val().length < oValidInfo['test']['min']) {
                        displayNotification('error', 'Erreur détectée', oValidInfo['errMsg']);
                        oHorsPresse.setFormError(oInfo.step, sTarget, oValidInfo['errMsg'], false);
                        oHorsPresse.addFormElemError(sTarget);
                        bReturn = false;
                    }
                    else {
                        if (oHorsPresse.getNumFormElemError(sTarget) == 0) {
                            oHorsPresse.setFormError(oInfo.step, sTarget, '', true);
                            bReturn = true;
                        }
                    }

                    oInfo['tests'][sTarget]--;
                    console.log('état des tests sur ' + oInfo['tests'][sTarget]);
                    break;
                case 'libelle-unique-url':
                    var tests = [];
                    tests[0] = 'exists';

                    $.when(
                            $.ajax({
                                url: oValidInfo['test']['url'],
                                data: {
                                    type: 'libelle',
                                    tests: tests,
                                    libelle: $(sTarget).val()
                                },
                                method: 'get',
                                datatype: 'jsonp',
                            }))
                            .then(function (data) {
                                console.log('retour ajax');
                                if (data.exists) {
                                    oHorsPresse.addFormElemError(sTarget);
                                    displayNotification('error', 'Erreur détectée', oValidInfo['errMsg']);
                                    oHorsPresse.setFormError(oInfo.step, sTarget, oValidInfo['errMsg'], false);
                                    bReturn = false;
                                }
                                else {
                                    if (oHorsPresse.getNumFormElemError(sTarget) == 0) {
                                        oHorsPresse.setFormError(oInfo.step, sTarget, '', true);
                                        bReturn = true;
                                    }
                                }

                                oInfo['tests'][sTarget]--;
                                console.log('état des tests sur ' + oInfo['tests'][sTarget]);
                                // Test final
                                oHorsPresse.checkIfFormOk(oInfo.step);
                            });
                    break;
            }
        }

        return bReturn;
    }

    /**
     * Ajoute un dans le compte des erreurs pour un champ donné
     * @param {string} sTarget Le champ cible
     */
    oHorsPresse.addFormElemError = function (sTarget) {
        if (oHorsPresse.oError[sTarget] > 0) {
            oHorsPresse.oError[sTarget]++;
        }
        else {
            oHorsPresse.oError[sTarget] = 1;
        }
    };

    /**
     * Retourne le nombre d'erreurs enregistrées pour un champ donné
     * @param {string} sTarget Le champ cible
     * @returns {Number} Le nombre d'erreurs enregistrées pour cet élément
     */
    oHorsPresse.getNumFormElemError = function (sTarget) {
        if (oHorsPresse.oError[sTarget]) {
            return Math.abs(oHorsPresse.oError[sTarget]);
        }
        else {
            return 0;
        }
    };

    /**
     * Vérifie qu'aucune erreur n'a été trouvée dans le formulaire
     * afin d'activer les boutons nécessaires au changement d'étape
     * @param {int} iStep Le numéro de l'étape à vérifier
     * @returns {boolean} Retourne vrai si aucune erreur
     */
    oHorsPresse.checkIfFormOk = function (iStep) {
        console.log('VALIDATION...', oHorsPresse.oError, oHorsPresse.oInfo.tests);
        if (oHorsPresse.oError && Object.keys(oHorsPresse.oError).length == 0) {

            for (oTest in oHorsPresse.oInfo.tests) {
                if (oHorsPresse.oInfo.tests[oTest].length > 0) {
                    console.log('Tests restants à effectuer', oHorsPresse.oInfo.tests[oTest].length);
                    break;
                }

                console.log('Aucune erreur dans le formulaire - étape : ' + iStep);
                switch (iStep) {
                    case 1:
                        // Activation du bouton
                        $('div#add_step1 button.btn-primary').removeAttr('disabled').off().on('click', function () {
                            oHorsPresse.stepUp();
                        });
                        break;
                    case 3:
                        // Activation du bouton
                        $('div#add_step3 button.btn-primary').removeAttr('disabled').off().on('click', function () {
                            oHorsPresse.stepTo(5);
                        });
                        break;
                    case 5:
                        // Activation du bouton
                        $('div#add_step5 button.btn-primary').removeAttr('disabled').off().on('click', function () {
                            // Charge des tournées
                            if ($('input[type=radio][name=charge_tournees]:checked').val() == "1") {
                                oHorsPresse.stepTo(7);
                            }
                            else {
                                oHorsPresse.stepTo(8);
                            }
                        });
                        break;
                    case 7:
                        // Activation du bouton
                        $('div#add_step7 button.btn-primary').removeAttr('disabled').off().on('click', function () {
                            oHorsPresse.stepUp();
                        });
                        break;
                    case 8:
                        // Est ce que la structure du fichier a été renseignée et le fichier uploadé?
                        if (oHorsPresse.fileStructure.status == 'saved' && oHorsPresse.userChoices.fichierClient.id ) {
                            console.info('ok, étape suivante');
                            $('div#add_step8 button.btn-primary').removeAttr('disabled').off().on('click', function () {
                                oHorsPresse.stepUp();
                            });
                        }
                        else {
                            displayNotification('error', 'Structure de fichier inconnue...', 'Veuillez définir la structure du fichier pour continuer.', 2500);
                        }
                        break;
                }


                return true;

            }
        }
        else {
            console.log(Object.keys(oHorsPresse.oError).length + ' erreurs dans le formulaire : ' + iStep);
            switch (iStep) {
                case 1:
                    // Activation du bouton
                    $('div#add_step1 button.btn-primary').attr('disabled', 'disabled');
                    break;
            }

            return false;
        }
    };

    /**
     * Affiche l'erreur sur un champ donné
     * @param {int} iSte L'étape du formulaire dans laquelle on est
     * @param {string} sTarget La cible désignant le champ en erreur
     * @param {string} $sErrMsg Le message d'erreur à afficher
     * @param {boolean} bCancel Efface l'erreur si vrai
     * @param {boolean} bWarning Présente un avertissement à la place d'une erreur si vrai
     */
    oHorsPresse.setFormError = function (iStep, sTarget, sErrMsg, bCancel, bWarning) {
        var highLightClass = bWarning ? 'info' : 'error';
        switch (iStep) {
            case 1:
                switch (sTarget) {
                    case '#titre':
                    case '#societe':
                    case '#produit':
                    case '#date_debut':
                    case '#date_fin':
                        if (!bCancel) {
                            $(sTarget).closest('tr').find('.formError').html(sErrMsg);
                            $(sTarget).closest('tr').addClass(highLightClass);
                            highlightElem($(sTarget).closest('tr'), highLightClass, 900);
                        }
                        else {
                            $(sTarget).closest('tr').find('.formError').html('');
                            $(sTarget).closest('tr').removeClass('error');
                            $(sTarget).closest('tr').removeClass('info');
                        }
                        break;
                }
                break;
            case 3:
                switch (sTarget) {
                    case 'input[type=radio][name=fichier_nominatif]':
                    case '#periode_hist':
                        if (!bCancel) {
                            $(sTarget).closest('tr').find('.formError').html(sErrMsg);
                            $(sTarget).closest('tr').addClass('error');
                            highlightElem($(sTarget).closest('tr'), highLightClass, 900);
                        }
                        else {
                            $(sTarget).closest('tr').find('.formError').html('');
                            $(sTarget).closest('tr').removeClass('error');
                        }
                        break;
                }
                break;
            case 5:
                switch (sTarget) {
                    case '#charge_max':
                        if (!bCancel) {
                            $(sTarget).closest('tr').find('.formError').html(sErrMsg);
                            $(sTarget).closest('tr').addClass('error');
                            highlightElem($(sTarget).closest('tr'), highLightClass, 900);
                        }
                        else {
                            $(sTarget).closest('tr').find('.formError').html('');
                            $(sTarget).closest('tr').removeClass('error');
                        }
                        break;
                }
                break;
            case 7:
                switch (sTarget) {
                    case 'input[type=radio][name=choix_debordement]':
                    case '#debordement_charge_max':
                        if (!bCancel) {
                            if ($.inArray(sTarget, oHorsPresse.config.steps[iStep]["elemErrors"]) === -1) {
                                console.log('Erreur non trouvée');
                                $(sTarget).closest('tr').find('.formError').html(sErrMsg);
                                $(sTarget).closest('tr').addClass('error');
                                highlightElem($(sTarget).closest('tr'), highLightClass, 900);
                                oHorsPresse.config.steps[iStep]["elemErrors"].push(sTarget);
                            }
                            else {
                                console.log('Erreur trouvée');
                            }
                        }
                        else {
                            $(sTarget).closest('tr').find('.formError').html('');
                            $(sTarget).closest('tr').removeClass('error');
                        }
                        break;
                }
                break;
            case 8:
                switch (sTarget) {
                    case 'input#temps_sup':
                        if (!bCancel) {
                            $(sTarget).closest('tr').next().find('.formError').html(sErrMsg);
                            $(sTarget).parent().parent().addClass('error');
                            highlightElem($(sTarget).closest('tr'), highLightClass, 900);
                        }
                        else {
                            $(sTarget).closest('tr').next().find('.formError').html('');
                            $(sTarget).parent().parent().removeClass('error');
                        }
                        break;
                    case 'select#multiselect_to':
                        console.debug(iStep, sTarget, sErrMsg, bCancel, bWarning);
                        console.info('Erreur...');
                        if (!bCancel) {
                            $(sTarget).parent().parent().next('.formError').html(sErrMsg);
                            $(sTarget).parent().parent().parent().parent().addClass('error');
                            highlightElem($(sTarget).closest('tr'), highLightClass, 900);
                        }
                        else {
                            console.info('Pas d\'erreur...');
                            $(sTarget).parent().parent().next('.formError').html('');
                            $(sTarget).parent().parent().parent().parent().removeClass('error');
                        }
                        break;
                }
                break;
        }
    };

    /**
     * Permet de passer à l'étape suivante
     * @returns {Boolean} false si le changement n'est pas possible
     */
    oHorsPresse.stepUp = function () {
        if (oHorsPresse.currentState.step >= oHorsPresse.config.steps.max) {
            // @TODO: Ajouter une notification
            return false;
        }
        else {
            var iNewTarget = oHorsPresse.currentState.step + 1;
            var iOldTarget = oHorsPresse.currentState.step;

            oHorsPresse.storeValues(iOldTarget); // enregistrement des valeurs

            console.log('de l\'étape ' + iOldTarget + ' à ' + iNewTarget);

            console.log('direction etape ' + iNewTarget);
            oHorsPresse.changeStep('#add_step' + iOldTarget, '#add_step' + iNewTarget);
            oHorsPresse.currentState.step = iNewTarget;
            oHorsPresse.lastState.step = iOldTarget;
            oHorsPresse.initForm(oHorsPresse.currentState.step);

            oHorsPresse.changeHist(iNewTarget);
            return false;
        }
    };

    /**
     * Permet de passer à l'étape précédente
     * @returns {Boolean} false si le changement n'est pas possible
     */
    oHorsPresse.stepDown = function () {
        if (oHorsPresse.currentState.step <= 1) {
            // @TODO: Ajouter une notification
            return false;
        }
        else {
            var iNewTarget = oHorsPresse.currentState.step - 1;
            var iOldTarget = oHorsPresse.currentState.step;

            console.log('de l\'étape ' + iOldTarget + ' à ' + iNewTarget);

            oHorsPresse.changeStep('#add_step' + iOldTarget, '#add_step' + iNewTarget);
            oHorsPresse.currentState.step = iNewTarget;
            oHorsPresse.lastState.step = iOldTarget;

            oHorsPresse.changeHist(iNewTarget);
        }
    };

    /**
     * Permet de sauter vers une nouvelle étape
     * @param {int} iStep L'étape vers laquelle on souhaite se diriger
     * @returns {Boolean} false si l'opération n'est pas possible
     */
    oHorsPresse.stepTo = function (iStep) {
        if (iStep < 1 || iStep > oHorsPresse.config.steps.max) {
            return false;
        }
        else {
            oHorsPresse.lastState.step = oHorsPresse.currentState.step;
            if (oHorsPresse.lastState.step != 9) {
                oHorsPresse.storeValues(oHorsPresse.lastState.step); // enregistrement des valeurs
            }
            oHorsPresse.changeStep('#add_step' + oHorsPresse.currentState.step, '#add_step' + iStep);
            oHorsPresse.currentState.step = iStep;
        }

        oHorsPresse.initForm(oHorsPresse.currentState.step);
    };


    /**
     * Change l'historique du navigateur
     */
    oHorsPresse.changeHist = function (etape) {
        var stateObj = {foo: "bar"};
        history.pushState(stateObj, "étape " + etape, oHorsPresse.baseUrls.add);
    };

    /**
     * Validation finale du formulaire
     */;
    oHorsPresse.validForm = function (aChampsOblig) {
        oHorsPresse.oError = {};

        oHorsPresse.oInfo = {};
        oHorsPresse.oInfo.tests = {}; // Remise à zéro de l'inventaire des tests
        oHorsPresse.oInfo.step = oHorsPresse.currentState.step;

        oHorsPresse.oInfo.default = oHorsPresse.config.steps[oHorsPresse.currentState.step]['initValues']; // Récupération des valeurs par défaut
        oHorsPresse.config.steps[oHorsPresse.currentState.step]["elemErrors"] = []; // Tableau contenant les éléments en erreur remis à zéro

        console.log('Validation des champs', aChampsOblig);

        console.log('Validation du formulaire de l\'étape ' + oHorsPresse.oInfo.step);

        // Traitement des exceptions
        switch (oHorsPresse.oInfo.step) {
            case 7:
                console.info('Exception en cours de traitement', $('input[type=radio][name=choix_debordement]:checked').val());
                if ($('input[type=radio][name=choix_debordement]:checked').val() != 1) {
                    aChampsOblig.splice(1, 1); // On supprime le 2nd test
                }

                break;
        }

        for (champ in aChampsOblig) {
            var aTestsVal = oHorsPresse.config.steps[oHorsPresse.currentState.step]['validation'][aChampsOblig[champ]];

            // On met à jour l'inventaire des tests
            oHorsPresse.oInfo.tests[aChampsOblig[champ]] = aTestsVal.length;
            console.log('tests à mener', oHorsPresse.oInfo.tests);

            if (aTestsVal.length > 0) {
                console.log('Validation à effectuer');
                for (oTest in aTestsVal) {
                    if (!oHorsPresse.validate(aChampsOblig[champ], aTestsVal[oTest], oHorsPresse.oInfo)) {
                        console.log('test échoué ', aTestsVal[oTest]);
                    }
                    else {
                        console.log('test réussi ', aTestsVal[oTest]);
                    }
                }
            }
        }


        // Test final
        oHorsPresse.checkIfFormOk(oHorsPresse.oInfo.step);
    };

    /**
     * Méthode d'enregistrement des valeurs saisies par l'opérateur
     * @param {int} etape Le numéro de l'étape en cours
     */
    oHorsPresse.storeValues = function (etape) {
        switch (etape) {
            case 1:
                console.log('Enregistrement de valeurs step 1');

                var values = {
                    titre: {
                        sTarget: '#titre',
                        sLabel: 'Nom de la campagne',
                        mValue: $('#titre').val(),
                        sText: $('#titre').val(),
                    },
                    societe: {
                        sTarget: '#societe',
                        sLabel: 'Société cliente',
                        mValue: $('#societe').val(),
                        sText: $('#societe option:selected').text(),
                    },
                    produit: {
                        sTarget: '#produit',
                        sLabel: 'Produit à distribuer',
                        mValue: $('#produit').val(),
                        sText: $('#produit option:selected').text(),
                    },
                    date_debut: {
                        sTarget: '#date_debut',
                        sLabel: 'Date de début',
                        mValue: $('#date_debut').val(),
                        sText: $('#date_debut').val(),
                    },
                    date_fin: {
                        sTarget: '#date_fin',
                        sLabel: 'Date de fin',
                        mValue: $('#date_fin').val(),
                        sText: $('#date_fin').val(),
                    },
                    produit_reference: {
                        sTarget: '#produit_reference',
                        sLabel: 'Produit de référence',
                        mValue: $('#produit_reference').val(),
                        sText: $('#produit_reference option:selected').text(),
                    },
                };
                break;
            case 2:
                console.log('Enregistrement de valeurs step 2');

                var values = {
                    fichier_fourni: {
                        sTarget: 'input[type=radio][name=fichier_fourni]',
                        sLabel: 'Fichier client fourni',
                        mValue: $('input[type=radio][name=fichier_fourni]:checked').val(),
                        sText: $('input[type=radio][name=fichier_fourni]:checked').parent().text(),
                    },
                }
                break;
            case 3:
                console.log('Enregistrement de valeurs step 3');

                var values = {
                    fichier_nominatif: {
                        sTarget: 'input[type=radio][name=fichier_nominatif]',
                        sLabel: 'Fichier client nominatif',
                        mValue: $('input[type=radio][name=fichier_nominatif]:checked').val(),
                        sText: $('input[type=radio][name=fichier_nominatif]:checked').parent().text(),
                    },
                    periode_hist: {
                        sTarget: '#periode_hist',
                        sLabel: 'Période d\'historique',
                        mValue: $('#periode_hist').val(),
                        sText: $('#periode_hist option:selected').text(),
                    },
                }
                break;
            case 5:
                console.info('Enregistrement de valeurs step 5');
                var values = {
                    charge_tournees: {
                        sTarget: 'input[type=radio][name=charge_tournees]',
                        sLabel: 'Charge des tournées',
                        mValue: $('input[type=radio][name=charge_tournees]:checked').val(),
                        sText: $('input[type=radio][name=charge_tournees]:checked').parent().text(),
                    },
                    charge_max: {
                        sTarget: '#charge_max',
                        sLabel: 'Charge maximale par tournée',
                        mValue: $('#charge_max').val(),
                        sText: $('#charge_max').val() + ' exemplaires',
                    }
                };
                break;
            case 7:
                console.info('Enregistrement de valeurs step 7');
                var values = {
                    tournees_debord: {
                        sTarget: 'input[type=radio][name=choix_debordement]',
                        sLabel: 'Tournées de débordement',
                        mValue: $('input[type=radio][name=choix_debordement]:checked').val(),
                        sText: $('input[type=radio][name=choix_debordement]:checked').parent().text(),
                    },
                    charge_debord_max: {
                        sTarget: '#charge_max_debordement',
                        sLabel: 'Charge maximale par tournée de débordement',
                        mValue: $('input#debordement_charge_max').val(),
                        sText: $('input#debordement_charge_max').val() + ' exemplaires',
                    }
                };
                break;
            case 8:
                console.info('Enregistrement de valeurs step 8');

                var values = {
                    temps_sup: {
                        sTarget: 'input#temps_sup',
                        sLabel: 'Temps suplémentaire',
                        mValue: $('input#temps_sup').val(),
                        sText: $('input#temps_sup').val() + ' secondes',
                    },
                    flux: {
                        sTarget: 'select#multiselect_to',
                        sLabel: 'Flux',
                        mValue: $('select#multiselect_to').val(),
                        sText: oHorsPresse.translateFluxIds(),
                    },
                    fichier_client: {
                        structure: oHorsPresse.fileSelectedFields,
                        delimiter: $('select#dataSep').val(),
                        entete: $('input#firstLineHeaders').is(':checked'),
                    }
                }
                break;
        }

        oHorsPresse.userChoices[etape] = values;
    };

    /**
     * Créé une chaine de caractère à partir des flux sélectionnés
     * @returns {string} L'information formatée à afficher
     */
    oHorsPresse.translateFluxIds = function () {
        var aFluxLabels = [];
        var aFluxSelect = $('select#multiselect_to').val() || [];
        if (aFluxSelect.length) {
            aFluxSelect.forEach(function (opt, i) {
                var aResult = $.grep(oHorsPresse.listeFlux, function (e) {
                    return e.id == aFluxSelect[i];
                });

                if (aResult.length > 0) {
                    aFluxLabels.push(aResult[0]['libelle']);
                }
            });
        }
        return aFluxLabels.join(', ');
    };

    /**
     * Initialisation du formulaire selon l'étape
     * @param {int} etape Le numéro de l'étape en cours
     */
    oHorsPresse.initForm = function (etape) {
        switch (etape) {
            case 1:
                console.log("Initialisation étape 1");

                // Changement des titres et consignes
                $('td.formHeader h4.titreSection').html('Informations générales');
                $('td.formHeader p.consigne_ppale').html('Remplissez le formulaire ci-dessous pour décrire votre nouvelle campagne de distribution de produits hors presse.');

                // Mise en place de la vérification des champs à chaque changement
                var aChampsOblig = ['#titre', '#societe', '#produit', '#date_debut', '#date_fin'];

                // Récupération de tous les produits pour le select
                $.ajax({
                    url: oHorsPresse.baseUrls('fetchAllProducts'),
                    type: "GET",
                    data: {},
                    datatype: 'jsonp',
                }).done(function (data) {
                    console.debug('data', data);
                    if (data.produits.length > 0) {
                        var prodMapSelect = {val: 'id', label: 'libelle'};
                        pushOptionsToElem('select#produit_reference', '<option>', data.produits, prodMapSelect);

                        displayNotification('info', 'Données récupérées...', 'Les produits ont bien été récupérés.', 2500);
                    }
                });

                // Récupération des produits d'une société
                $("#societe").change(function () {
                    if ($(this).find('option:selected').val() > 0) {
                        var steName = $(this).find('option:selected').text();
                        var iSteId = $(this).find('option:selected').val();
                        var prodSelectElem = '#produit';

                        // On  réinitialise les champs du <select> de produits
                        var prodSelectElem = '#produit';
                        $(prodSelectElem).find('option')
                                .remove()
                                .end()
                                .append('<option value="">Choisissez le produit...</option>')
                                .val('');

                        // On récupère les produits de la société
                        // @TODO Sans doute filtrer sur les produits HP
                        $.ajax({
                            url: oHorsPresse.baseUrls('fetchProducts4Company'),
                            data: {
                                ste: iSteId,
                            },
                            datatype: 'jsonp',
                            method: 'GET',
                        })
                                .done(function (data) {
                                    if (console && console.log) {
                                        console.log(data);
                                    }

                                    if (data.produits.length > 0) {
                                        var prodMapSelect = {val: 'id', label: 'libelle'};

                                        pushOptionsToElem(prodSelectElem, '<option>', data.produits, prodMapSelect);
                                        $('tr.tr_produits').removeClass('disabled');

                                        highlightElem($(prodSelectElem).closest('tr'), 'info', 2000);

                                        displayNotification('info', 'Mise à jour...', 'La liste des produits a été mise à jour.', 2500)
                                    }
                                });
                    }
                    else {
                        $('tr.tr_produits').addClass('disabled');
                    }
                });

                break;
            case 2:
                console.log('Initialisation étape  2');
                // Changement des titres et consignes
                $('td.formHeader h4.titreSection').html('Paramétrage de la distribution');
                $('td.formHeader p.consigne_ppale').html('Cette étape permet de définir les caractéristiques de la campagne');

                // Affichage et masquage du sélect pour la période d'historique
                $('input[type=radio][name=fichier_fourni]').on('change', function () {
                    console.log("change");
                    if (this.value == "1") {
                        console.log('Oui, fichier fourni');
                        oHorsPresse.stepTo(3);
                    }
                    else {
                        console.log('Non, fichier non fourni');
                        oHorsPresse.stepTo(6);
                    }
                });
                break;
            case 3:
                console.log('Initialisation étape  3');
                // Changement des titres et consignes
                $('td.formHeader h4.titreSection').html('Paramétrage de la distribution');
                $('td.formHeader p.consigne_ppale').html('Cette étape permet de définir les caractéristiques de la campagne');

                // Mise en place de la vérification des champs à chaque changement
                var aChampsOblig = ['input[type=radio][name=fichier_nominatif]', '#periode_hist'];

                // Affichage et masquage du sélect pour la période d'historique
                $('input[type=radio][name=fichier_nominatif]').on('change', function () {
                    console.log("change");
                    if (this.value == "1") {
                        console.log('Oui, fichier nominatif');
                        $('tr#periode_historique').removeClass('disabled');
                    }
                    else {
                        console.log('Non, fichier non nominatif - DTB');
                        $('tr#periode_historique').addClass('disabled');
                    }
                });
                break;
            case 5:
                console.log('Initialisation étape  5');
                // Changement des titres et consignes
                $('td.formHeader h4.titreSection').html('Paramétrage de la distribution');
                $('td.formHeader p.consigne_ppale').html('');

                // Mise en place de la vérification des champs à chaque changement
                var aChampsOblig = ['input[type=radio][name=charge_tournees]', '#charge_max'];

                // Affichage et masquage du sélect pour la période d'historique
                $('input[type=radio][name=charge_tournees]').on('change', function () {
                    console.log("change");
                    $('tr#charge_max_tournees').removeClass('disabled');
                    $('tr.centralExplain').show();
                    if (this.value == "1") {
                        console.log('Oui, charge de tournées');
                        $('label[for=charge_max]').html('Charge maximale par tournée:');
                        $('tr.centralExplain td').html('<span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>Des <strong>tournées mixtes</strong> seront mises en place durant cette campagne.');
                    }
                    else {
                        console.log('Non, uniquement tournées dédiées');
                        $('label[for=charge_max]').html('Quantité maximale par tournée:');
                        $('tr.centralExplain td').html('<span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>Des <strong>tournées dédiées</strong> seront créées pour cette campagne.');
                    }
                });

                break;
            case 7:
                console.log('Initialisation étape  7');
                // Changement des titres et consignes
                $('td.formHeader h4.titreSection').html('Paramétrage de la distribution');
                $('td.formHeader p.consigne_ppale').html('');

                $('tr.centralExplain').hide();

                // Mise en place de la vérification des champs à chaque changement
                var aChampsOblig = ['input[type=radio][name=choix_debordement]', '#debordement_charge_max'];

                // Affichage et masquage du sélect pour la période d'historique
                $('input[type=radio][name=choix_debordement]').on('change', function () {
                    console.log("change");
                    if (this.value == "1") {
                        console.log('Oui, débordement');
                        $('tr.centralExplain').show();
                        $('tr#charge_max_debordement').removeClass('disabled');
                        $('label[for=debordement_charge_max]').html('Quantité maximale par tournée de débordement:');
                        $('tr.centralExplain td').html('<span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>Des <strong>tournées dédiées</strong> seront utilisées si les tournées mixtes ne sont pas suffisantes pour cette campagne.');
                    }
                    else {
                        console.log('Non, pas de débordement');
                        $('tr.centralExplain').show();
                        $('tr#charge_max_debordement').removeClass('error');
                        $('tr#charge_max_debordement').addClass('disabled');
                        $('tr.centralExplain td').html('<span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>Aucune <strong>tournée dédiée</strong> ne sera créée pour distribuer un éventuel surplus des tournées mixtes.');

                    }
                });

                break;
            case 8:
                console.log('Initialisation étape  8');
                displayNotification('info', 'Aide', 'L\'envoi du fichier client démarre automatiquement lorsque vous avez choisi le fichier à envoyer et que sa structure a été définie.', 10500);

                // Mise en place de la vérification des champs à chaque changement
                var aChampsOblig = ['input#temps_sup', 'select#multiselect_to'];
                break;
            case 9:
                console.info('Affichage du récapitulatif');

                // Changement des titres et consignes
                $('td.formHeader h4.titreSection').html('Récapitulatif de la campagne ' + oHorsPresse.userChoices[1]['titre']['sText']);
                $('td.formHeader p.consigne_ppale').html('Merci de vérifier que les paramètres correspondent bien à votre besoin avant de valider');

                // Informations générales
                $('div.infoGenerales p.titreCampagne span.valPlaceHolder').html(oHorsPresse.userChoices[1]['titre']['sText']);
                $('div.infoGenerales p.produitCampagne span.valPlaceHolder').html(oHorsPresse.userChoices[1]['produit']['sText'] + ' / ' + oHorsPresse.userChoices[1]['societe']['sText']);
                $('div.infoGenerales p.dateDebut span.valPlaceHolder').html(oHorsPresse.userChoices[1]['date_debut']['sText']);
                $('div.infoGenerales p.dateFin span.valPlaceHolder').html(oHorsPresse.userChoices[1]['date_fin']['sText']);

                // Parmètres de distribution
                // Produit de référence
                if (oHorsPresse.userChoices[1]['produit_reference']['mValue'] != "") {
                    $('div.paramsDistrib p.prodRef span.valPlaceHolder').html(oHorsPresse.userChoices[1]['produit_reference']['sText']);
                }
                else {
                    $('div.paramsDistrib p.prodRef span.valPlaceHolder').html('Aucun');
                }

                // Fichier client fourni
                if (oHorsPresse.userChoices[2]['fichier_fourni']['mValue'] == "1") {
                    $('div.paramsDistrib p.fichierFourni input').attr('checked', 'checked');
                    $('div.infoFichier').show();
                    
                    $('div.infoFichier p.nomFichier span.valPlaceHolder').html(oHorsPresse.userChoices.fichierClient.nom + ' <em>(ID: '+ oHorsPresse.userChoices.fichierClient.id + ')</em>');
                    $('div.infoFichier p.prodQte span.valPlaceHolder').html(oHorsPresse.userChoices.fichierClient.qte);
                    $('div.infoFichier p.nbP2L span.valPlaceHolder').html(oHorsPresse.userChoices.fichierClient.nbP2L);
                    
                    // Demande de remplissage du tableau
                    oHorsPresse.feedSampleTable($('div.infoFichier table'), oHorsPresse.userChoices.fichierClient.datas);
                }
                else {
                    // Distribution paramétrée
                    $('div.infoFichier').hide();
                }

                // Adressage nominatif
                if (oHorsPresse.userChoices[3]['fichier_nominatif']['mValue'] == "1") {
                    $('div.paramsDistrib p.adressageNomin input').attr('checked', 'checked');
                    $('div.paramsDistrib p.adressageNomin input').removeAttr('disabled');
                    $('div.paramsDistrib p.adressageNomin span').removeClass('notActive');

                    $('div.paramsDistrib p.dtb input').attr('disabled', 'disabled');
                    $('div.paramsDistrib p.dtb input').removeAttr('checked');
                    $('div.paramsDistrib p.dtb span').addClass('notActive');

                    $('div.paramsDistrib p.periode span.valPlaceHolder').html(oHorsPresse.userChoices[3]['periode_hist']['sText']);
                    $('div.paramsDistrib p.periode').removeClass('notActive');
                }
                else {
                    $('div.paramsDistrib p.adressageNomin input').removeAttr('checked');
                    $('div.paramsDistrib p.adressageNomin input').attr('disabled', 'disabled');
                    $('div.paramsDistrib p.adressageNomin span').addClass('notActive');

                    $('div.paramsDistrib p.dtb input').removeAttr('disabled');
                    $('div.paramsDistrib p.dtb input').attr('checked', 'checked');
                    $('div.paramsDistrib p.dtb span').removeClass('notActive');

                    $('div.paramsDistrib p.periode span.valPlaceHolder').html('N/A');
                    $('div.paramsDistrib p.periode').addClass('notActive');
                }

                // Charge des tournées
                if (oHorsPresse.userChoices[5]['charge_tournees']['mValue'] == "1") {
                    $('div.paramsDistrib p.chargeTournees span').html('Distribution à charge des tournées existantes');
                    $('div.paramsDistrib p.chargeTournees input').attr('checked', 'checked');

                    $('div.paramsDistrib p.chargeMaxTournees span').html(oHorsPresse.userChoices[5]['charge_max']['mValue']);
                    $('div.paramsDistrib p.chargeMaxTournees').show();
                    $('div.paramsDistrib p.chargeMaxTourneesDediees').hide();
                }
                else {
                    $('div.paramsDistrib p.chargeTournees span').html('Distribution basée uniquement sur des tournées dédiées');
                    $('div.paramsDistrib p.chargeTournees input').attr('checked', 'checked');

                    $('div.paramsDistrib p.chargeMaxTourneesDediees span').html(oHorsPresse.userChoices[5]['charge_max']['mValue']);
                    $('div.paramsDistrib p.chargeMaxTourneesDediees').show();

                    $('div.paramsDistrib p.tourneesDediees').hide();
                    $('div.paramsDistrib p.chargeMaxTournees').hide();
                }

                // tournées de débordement
                if (oHorsPresse.userChoices[7]['tournees_debord']['mValue'] == "1") {
                    $('div.paramsDistrib p.tourneesDediees').show();
                    $('div.paramsDistrib p.tourneesDediees span.debordement').show();
                    $('div.paramsDistrib p.tourneesDediees input').attr('checked', 'checked');
                    $('div.paramsDistrib p.chargeMaxTourneesDediees').show();
                    $('div.paramsDistrib p.chargeMaxTourneesDediees span').html(oHorsPresse.userChoices[7]['charge_debord_max']['mValue']);
                }
                else {
                    $('div.paramsDistrib p.tourneesDediees span.debordement').hide();
                }

                // Flux
                $('div.paramsDistrib p.flux span.valPlaceHolder').html(oHorsPresse.userChoices[8]['flux']['sText']);

                // Temps supplémentaire
                $('div.paramsDistrib p.tempsSup span.valPlaceHolder').html(oHorsPresse.userChoices[8]['temps_sup']['sText']);
                
                // boutons de bas de page
                $('div#add_step9 div.bottomBtnPart button.btn-primary').removeAttr('disabled');

                break;
        }

        // Demande de validation du formulaire si champs obligatoires présents
        if (aChampsOblig && aChampsOblig.length > 0) {
            for (var champ in aChampsOblig) {

                // Ajout de l'asterisque
                if (!$(aChampsOblig[champ]).closest('tr').find('td label').next('span.required').length) {
                    $(aChampsOblig[champ]).closest('tr').find('td label').after('<span class="required">*</span>');
                }

                switch (etape) {
                    case 1:
                        if (aChampsOblig[champ] == '#societe') {
                            continue;
                        }
                        break;
                }
                console.log(aChampsOblig[champ]);
                $(aChampsOblig[champ]).change(function () {
                    console.log('écouteur sur ' + aChampsOblig[champ]);
                    oHorsPresse.validForm(aChampsOblig);
                });
            }
        }
    };

    // Permet de s'assurer que l'enregistrement des valeurs est possible
    oHorsPresse.checkIfCanSave = function () {
        console.log('Vérification des informations pour permettre l\'enregistrement');
        var aResult = $.grep(oHorsPresse.fileSelectedFields, function (e) {
            return e.val != 'n/a';
        });
        console.log(oHorsPresse.fileSelectedFields);
        if (aResult.length > 0 && $('select#dataSep').val()) {
            console.log('Enregistrement des valeurs possible', aResult.length);
            $('p.pSave button').removeAttr('disabled');
        }
        else {
            console.log('pas d enregistrement', aResult.length, $('select#dataSep').val());
            $('p.pSave button').attr('disabled', 'disabled');
        }
    };


    /**
     * Désactive une option dans les <select>
     * @param {object} oOption
     * @param {boolean} bInverse Active l'élément si vrai
     * @returns {undefined}
     */
    oHorsPresse.disableSelectOption = function (oOption, bInverse) {
        var aTargets = $('table#structure tr select');
        console.log('selects cible', aTargets);
        $(aTargets).each(function (element, index) {
            console.log('option 1: ', oOption);
            $(aTargets[element]).find('option').each(function (option, i) {
                // Réactivation
                if (bInverse && $(aTargets[element][option]).val() == oOption.val) {
                    console.info('Réactivation...');
                    $(aTargets[element][option]).removeAttr('disabled');
                }

                // On désactive si besoin
                if ($(aTargets[element][option]).val() == oOption.val
                        && oOption.val != $(aTargets[element]).val()
                        && !bInverse
                        && oOption.unique === true) {
                    $(aTargets[element][option]).attr('disabled', 'disabled');
                }
            });
        });
    };


    // Ajout des options disponibles aux <select> de définition de la structure des fichiers clients 
    oHorsPresse.feedFileSelect = function (selectElem, index) {
        console.log('méthode feedFileSelect');
        var aOptions = [];
        oHorsPresse.fileFields.forEach(function (element) {
            aOptions.push(element)
        });
        console.log(aOptions);

        var aSelectedOptions = oHorsPresse.fileSelectedFields;
        console.log('options déjà choisies', aSelectedOptions);

        // fonction de mappage
        var oFormatFunc = function (liste) {
        };

        var oMap = {
            label: 'txt',
            val: 'val',
        };

        pushOptionsToElem(selectElem, "<option>", aOptions, oMap, oFormatFunc);

        if (aSelectedOptions.length > 0) {
            aSelectedOptions.forEach(function (element, index) {
                var delIndex, aResults;

                aOptions.forEach(function (elem, ind) {
                    if (elem.val == element.val
                            && elem.unique === true
                            ) {
                        delIndex = ind;
                        aResults = elem;
                    }
                });

                if (delIndex && aResults) {
                    console.log('Suppression de l item ', delIndex, aResults);
                    oHorsPresse.disableSelectOption(aResults);

                    // On grise le bouton d'ajout de colonne si la seule option restante est "non utilisé"
                    if (aOptions.length == 1 && aOptions[0]['val'] == 'n/a') {
                        console.log('Plus d\'options disponibles, on grise le bouton');
                        $('p.addRow button.add').attr('disabled', 'disabled');
                    }
                }
            });
        }

        // On marque comme unique toutes les options qui le sont
        var aOptions = $(selectElem).find('option');
        aOptions.each(function (opt, i) {
            var aResult = $.grep(oHorsPresse.fileFields, function (e) {
                return e.val == $(aOptions[opt]).val();
            });
            if (aResult.length > 0) {
                if (aResult[0]['unique'] == true) {
                    $(aOptions[opt]).attr('data-uniq', '1');
                }
            }
        });
    };

    /**
     * Ajoute une nouvelle ligne au tableau de définition de la structure du fichier
     */
    oHorsPresse.addFileRow = function () {
        var nbLignes = $('table#structure tr').toArray().length;
        var sRowHtml = '<tr class="added"><td>#' + nbLignes + '</td><td><select class="dataField item' + nbLignes + '"></select></td><td><button type="button" class="btn btn-danger btn-xs removeItem"><i class="glyphicon glyphicon-minus"></i> Supprimer</button></td></tr>';
        console.log(nbLignes, 'lignes');
        $('table#structure tr:last-of-type').after(sRowHtml);
        oHorsPresse.feedFileSelect('table#structure tr select.item' + nbLignes, nbLignes);
    };

    /**
     * Enregistrement des options sélectionnées dans l'objet global
     * @returns {int} Retourne le nombre d'options sélectionnées
     */
    oHorsPresse.selectFileOptions = function () {
        var aSelected = [];
        $('table#structure select.dataField').each(function (index, element) {
            var oSelection = {
                txt: $(element).find('option:selected').text(),
                val: $(element).val(),
            };

            aSelected.push(oSelection);
            console.log('élément sélectionné ', oSelection);
        });

        oHorsPresse.fileSelectedFields = aSelected;
        console.log(oHorsPresse.fileSelectedFields, 'sélection');
        return aSelected.length;
    };

    /**
     * Supprime une option unique des <select> de définition de structure du fichier
     * @param {object} item Objet JQuery représentant le <select> à l'origine du changement
     */
    oHorsPresse.deleteUniqOptions = function (item) {
        var valUniq = item.val();
        console.log('Vérification de l\'unicité de ', valUniq);
        var autresSel = $('table#structure').find('select.dataField');
        if (autresSel.length) {
            console.log('Des elements a prendre en compte', autresSel);
        }
        else {
            console.log('Autres select', autresSel);
        }
    };

    /**
     * Recalcule les numéros de lignes pour l'écran de définition de la structure de fichier
     */
    oHorsPresse.FileSelectsProcessRowsNum = function () {
        console.info('recalcul des numéros de ligne en cours...');
        var aLignes = $('table#structure tr td select.dataField:not(.original)').toArray();
        console.info(aLignes.length + ' lignes trouvées');
        if (aLignes.length > 0) {
            console.debug(aLignes);
            aLignes.forEach(function (elem, idx) {
                // Modification des classes sur le select
                $(elem).attr("class", "dataField");
                var num = idx + 2;
                var itemClass = 'item' + num;
                $(elem).addClass(itemClass);

                // Changement du numéro
                $(elem).parent().prev('td').html('#' + num);
            });
        }
    };

    /**
     * Vérifie qu'il est possible et approprié de lancer un upload du fichier client
     */
    oHorsPresse.checkIfNeedToUpload = function () {
        var bReturn = false;
        console.info('Vérification du besoin d\'upload',oHorsPresse.uploader._queue.length);
        if (oHorsPresse.uploader._queue.length && oHorsPresse.fileStructure.status == 'saved') {
            bReturn = true;
        }

        return bReturn;
    };

    /**
     * Lance l'upload du fichier client
     */
    oHorsPresse.uploadClientFile = function () {
        console.info('Méthode upload');
        var btn = document.getElementById('uploadBtn');
        var url = oHorsPresse.baseUrls('uploadFile');
        
        oHorsPresse.uploader = new ss.SimpleUpload({
            self: this,
            button: btn, // HTML element used as upload button
            url: url, // URL of server-side upload handler
            name: 'fichier', // Parameter name of the uploaded file
            onSubmit: function() {
                // Refresh des données à poster
                this.setData({
                        config: JSON.stringify({
                        delim: oHorsPresse.fileStructure.separator,
                        hasHeader: oHorsPresse.fileStructure.headers,
                    })
                });
                
                var sFileName = oHorsPresse.uploader._queue[0].name;
                self.sFileName = sFileName;
                console.debug(sFileName, oHorsPresse.fileStructure.status);
                // Indication du nom de fichier
                $('#uploadBtn').next('span.infoFile').html(sFileName);
                    
                if (!oHorsPresse.checkIfNeedToUpload()){
                    displayNotification('warning', 'Paramètres manquants...', 'Veuillez définir la structure du fichier et choisir le fichier client à envoyer.', 6500);
                    
                    return false;
                }
                
                // Gestion des champs
                var aFields = [];
                if (oHorsPresse.fileStructure.fields.length){
                   oHorsPresse.fileStructure.fields.forEach(function(elem, i){
                       aFields.push(elem.val);
                   });
                }
                oHorsPresse.uploader._opts.data.structure = JSON.stringify(aFields);
                
                displayNotification('info', 'Envoi de fichier...', 'Le fichier client est en cours d\'envoi sur le serveur.', 2500);
            },
            onComplete: function(filename, response){
                console.info('upload terminé:');
                var oResponse = JSON.parse(response);
                console.debug(oResponse);
                if (oResponse.returnCode === 1){
                    displayNotification('success', 'Fichier enregistré!', 'Le fichier client a bien été enregistré sur le serveur.', 3500);
                    $('div#add_step8 p.formSuccess').html('<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>Structure du fichier définie<br/><span class="glyphicon glyphicon-ok" aria-hidden="true"></span>Fichier enregistré');
                    
                    // Enregistrement des infos sur le fichier
                    oHorsPresse.userChoices.fichierClient = {
                        'nom' : self.sFileName,
                        'chemin' : oResponse.result.chemin,
                        'id' : oResponse.result.idFichier,
                        'datas' : oResponse.result.datas,
                        'qte' : oResponse.result.info.qte,
                        'nbLignes' : oResponse.result.info.num,
                        'nbP2L' : oResponse.result.info.nbP2L,
                    };
                    
                    // Déclenchement de la validation
                    oHorsPresse.checkIfFormOk(oHorsPresse.oInfo.step);
                }
                else{
                    console.error(response.errMsg);
                    console.debug(response);
                    displayNotification('error', 'Erreur d\'enregistrement', 'Le fichier n\'a pas pu être enregistré sur le serveur.<br/>Veuillez contacter le support MRoad.', 3500);
                }
            }
        });
        
//        /**
//         * Déclenche le process d'upload
//         */
//        $('#fichier_upload').onf('click', function(){
//            $('#uploadBtn').trigger('click');
//        });
//        
    };

    /**
     * Ergonomie & interactions
     */
    $(document).ready(function () {
        // Initialisation du formulaire
        oHorsPresse.currentState.addform = {
            valid: false,
            state: 'new',
        };

        // Bouton Etape suivante
        $('.bottomBtnPart .btn').attr('disabled', 'disabled');
        $('.btn.prevBtn').removeAttr('disabled').on('click', function () {
            oHorsPresse.stepTo(oHorsPresse.lastState.step);
        });

        // Initialisation selon l'étape
        oHorsPresse.initForm(oHorsPresse.currentState.step);

        // Interception de la touche entrée
        $(window).keydown(function (event) {
            if (event.keyCode == 13) {
                console.log('touche entrée');
                event.preventDefault();
                $('#titre').trigger('change');
            }
        });


        $.datepicker.setDefaults($.datepicker.regional[ "fr" ]);
        $("form input.date").datepicker({
            dateFormat: 'dd/mm/yy',
            firstDay: 1,
            minDate: 0
        }).attr("readonly", "readonly");

        // Upload de fichiers
        $('input[type=file]').bootstrapFileInput();

        // bouton d'ouverture de popup
        $('#fileStrucBtn').on('click', function () {
            console.log('Ouverture de popup de structuration');
            $('#fileStrucBtnModal').modal('show');
        });
        $('#fileStrucRecapBtn').on('click', function () {
            $('#fileStrucBtn').trigger('click');
        });

        // enregistrement de l'état initial des valeurs sélectionnées (options de select)
        oHorsPresse.initSelectOptions = oHorsPresse.fileSelectedFields;

        // Peuplement des options du select pour les colonnes
        var aSelects = $('#fileStrucBtnModal table#structure select').toArray();
        aSelects.forEach(function (element, index) {
            console.log('select: ', element, 'index', index);
            oHorsPresse.feedFileSelect(element, index);
        });

        // Bouton d'ajout de colonne
        $('p.addRow button.add').on('click', function () {
            console.log('ajout de colonne');
            // Enregistrement de la sélection
            oHorsPresse.selectFileOptions();

            // Création de la nouvelle ligne
            oHorsPresse.addFileRow();
        });

        // Bouton de réinitialisation du formulaire
        $('p.addRow button.reset').on('click', function () {
            console.log('Réinitialisation du formulaire');
            oHorsPresse.fileSelectedFields = oHorsPresse.initSelectOptions;

            $('table#structure tr.added').remove();
            $('p.addRow button.add').removeAttr('disabled');
            $('p.pSave button').attr('disabled', 'disabled');
            $('#firstLineHeaders').prop('checked', false);
            $('select#dataSep').val('');
        });

        // Message sur la non définition de structure du fichier
        switch (oHorsPresse.fileStructure.status) {
            case 'empty':
                console.error('Structure de fichier non définie');
                $('input#fichier_upload').parent().next('p.formError').html('<span class="glyphicon glyphicon-warning-sign" aria-hidden="true"></span>Structure du fichier inconnue!');
                break;
        }

        // Bouton d'enregistrement
        $('body').on('click', 'p.pSave button', function () {
            console.info('Demande d\'enregistrement de la structure de fichier');

            // Sauvegarde des informations sur la structure de fichier
            oHorsPresse.fileStructure.fields = oHorsPresse.fileSelectedFields;
            oHorsPresse.fileStructure.headers = $('#firstLineHeaders').is(':checked');
            oHorsPresse.fileStructure.separator = $('select#dataSep').val();
            oHorsPresse.fileStructure.status = 'saved';

            // Affichage des messages
            displayNotification('success', 'Structure enregistrée...', 'La structure du fichier client est maintenant définie.', 2500);
            $('input#fichier_upload').parent().next('p.formError').hide();
            $('div#add_step8 p.formSuccess').show();
            $('div#add_step8 p.formSuccess').html('<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>Structure du fichier définie');
            
            
            // Déclenchement de l'envoi de fichier
            $(oHorsPresse.uploader).trigger('submit');

            // Fermeture de la modale
            $('#fileStrucBtnModal').modal('hide');

            // Demande de validation du formulaire
            oHorsPresse.checkIfFormOk(oHorsPresse.currentState.step);
        });


        // Changement de valeur sélectionnée
        $('body').on('change', 'select#dataSep, select.dataField', function () {
            // Enregistrement de la sélection
            oHorsPresse.selectFileOptions();

            console.log('Change option', $(this));
            // Suppression des options uniques en mémoire
            oHorsPresse.deleteUniqOptions($(this));

            // Désactivation des options uniques dans les autres <select>
            var oOption = {
                val: $(this).val(),
                txt: $(this).find('option:selected').text()
            };
            if ($(this).find('option:selected').data('uniq') == "1") {
                oOption.unique = true;
            }

            if ($(this).attr('data-init-val')) {
                // On réactive l'ancienne valeur sélectionnée
                console.info('on réactive l\'option', $(this).attr('data-init-val'));
                oHorsPresse.disableSelectOption({val: $(this).attr('data-init-val')}, true);
            }

            // enregistrement de la valeur sélectionnée
            $(this).attr('data-init-val', oOption.val);

            // Désactivation de cette option dans les autres <select>
            oHorsPresse.disableSelectOption(oOption);

            // Actication/désactivation du bouton enregistrement
            oHorsPresse.checkIfCanSave();
        });

        // Suppression d'une ligne
        $('body').on('click', 'button.removeItem', function () {
            console.info('Demande de suppression de ligne');
            // Récupération des infos sur la ligne supprimée
            var sValSelect = $(this).parent().parent().find('td select.dataField').val();
            console.info('Suppression de l\'option', sValSelect);

            // Suppression de la valeur en mémoire
            var delIdx;
            var aResult = $.grep(oHorsPresse.fileSelectedFields, function (e, idx) {
                if (e.val == sValSelect) {
                    delIdx = idx;
                }
                return e.val == sValSelect;
            });
            if (aResult.length > 0) {
                oHorsPresse.fileSelectedFields.splice(delIdx, 1);
            }
            else {
                console.warning('La valeur ' + sValSelect + ' n\a pas été trouvée dans les valeurs enregistrées.');
            }

            // Réactivation de l'option sélectionnée
            var oOption = {val: sValSelect};
            oHorsPresse.disableSelectOption(oOption, true);

            // Suppression de la ligne
            $(this).parent().parent().remove();

            // recalcul du numéro des colonnes (pour les lignes qui viennent après)
            oHorsPresse.FileSelectsProcessRowsNum();
        });

        // Récupération des options pour les flux
        var sFluxUrl = oHorsPresse.baseUrls('fetchFlux');
        $.when(
                $.ajax({
                    url: sFluxUrl,
                    data: {},
                    method: 'get',
                    datatype: 'jsonp',
                }))
                .then(function (data) {
                    console.log('retour ajax liste des flux');
                    if (data.aFlux.length) {
                        oHorsPresse.listeFlux = data.aFlux;
                        var prodMapSelect = {val: 'id', label: 'libelle'};
                        pushOptionsToElem('#multiselect', '<option>', data.aFlux, prodMapSelect);

                    }
                });

        // Multiselect pour le choix des flux (chargement de fichier)
        $('#multiselect').multiselect({
            keepRenderingSort: true,
            afterMoveToRight: function () {
                console.info('selection');
                $('input#temps_sup').trigger('change');
                $('#multiselect').trigger('change');
            },
            afterMoveToLeft: function () {
                console.info('selection');
                $('input#temps_sup').trigger('change');
                $('#multiselect').trigger('change');
            },
        });

        // Bouton d'enregistrement de campagne
        $('div#add_step9 div.bottomBtnPart button.btn-primary').on('click', function () {
            var oBtn = $(this);
            console.info('Demande d\'enregistrement de la campagne');
            var oPostVal = {
                titre: oHorsPresse.userChoices[1]['titre']['sText'],
                date_debut: oHorsPresse.userChoices[1]['date_debut']['sText'],
                date_fin: oHorsPresse.userChoices[1]['date_fin']['sText'],
                produit_id: oHorsPresse.userChoices[1]['produit']['mValue'],
                produit_ref_id: oHorsPresse.userChoices[1]['produit_reference']['mValue'] || null,
                ste_id: oHorsPresse.userChoices[1]['societe']['mValue'],
                fichier_fourni: oHorsPresse.userChoices[2]['fichier_fourni']['mValue'],
                fichier_nominatif: oHorsPresse.userChoices[3]['fichier_nominatif']['mValue'] || null,
                fichier_id: oHorsPresse.userChoices.fichierClient.id || null,
                // fichier_nb_bal: oHorsPresse.userChoices[3]['fichier_nominatif']['mValue'] || null,
                periode: oHorsPresse.userChoices[3]['periode_hist']['mValue'] || null,
                charge_tournees: oHorsPresse.userChoices[5]['charge_tournees']['mValue'] || null,
                charge_max: oHorsPresse.userChoices[5]['charge_max']['mValue'],
                tournees_debord: oHorsPresse.userChoices[7]['tournees_debord']['mValue'] || null,
                charge_debord_max: oHorsPresse.userChoices[7]['charge_debord_max']['mValue'] || null,
                fichier_client_delim: oHorsPresse.userChoices[8]['fichier_client']['delimiter'] || null,
                fichier_client_entete: oHorsPresse.userChoices[8]['fichier_client']['entete'] || false,
                fichier_client_structure: oHorsPresse.userChoices[8]['fichier_client']['structure'] || null,
                flux: oHorsPresse.userChoices[8]['flux']['mValue'] || [],
                temps_sup: oHorsPresse.userChoices[8]['temps_sup']['mValue'] || [],
            };

            console.debug(oPostVal);

            var urlPost = oHorsPresse.baseUrls("storeCampain");
            $.post(urlPost, oPostVal, function (data) {
                // Succès de l'opération
                if (data.returnCode == 1) {
                    oBtn.attr('disabled', 'disabled');
                    displayNotification('success', 'Enregistrement', data.msg);

                    // redirection
                    window.location.href = oHorsPresse.baseUrls("list");
                }
                else {
                    displayNotification('error', 'Erreur rencontrée', data.errMsg);
                }
            },
                    'json');
        });

        // Bouton d'édition des paramètres
        $('a#editParams').on('click', function () {
            oHorsPresse.stepTo(1);
        });
        
        // Upload de fichier
        oHorsPresse.uploadClientFile();
    });
}());
