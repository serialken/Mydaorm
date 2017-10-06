DHTMLXDEBUG = 0;
DHTMLXEXPIRE = -1;
//      IDX_ENTETE          ,IDX_HEADER1   ,IDX_HEADER2    , IDX_LINK_BEFOREINSERT      ,IDX_LINK_AFTERINSERT,IDX_FUNC_ONSELECT,IDX_VERIFICATOR
IDX_ENTETE = 0;
IDX_HEADER1 = 1;
IDX_HEADER2 = 2;
IDX_VERIFICATOR = 3;
IDX_LINK_BEFOREINSERT = 4;
IDX_LINK_AFTERINSERT = 5;
IDX_FUNC_ONSELECT = 6;
IDX_FUNC_ONCELLCHANGE = 7;

rowIsInsertion = false; // Permet de supprimer la validation des champs (Initialisation de l'insertion)
rowIsInserted = false; // Permet de ne pas bloquer certaines colonnes lorsque l'on est en insertion (la ligne insérée est cours de modification)
rowIdInserted =0; // identifiant uid temporaire de la ligne en cours d'insertion
//
colorError="#FAD9DD";
colorWarning="#F5D5A2";
colorOk="";

newRows=null;
//------------------------------------------------------------------------------
//------------------------------------------------------------------------------
function initDataProcessor() {
    dp.setTransactionMode("POST");
    dp.enableDataNames(true);
    dp.setUpdateMode("row");
//        dp.enableDebug(true);
    // Initialise la vérification des colonnes
    for (i = 0; i < dhtmlxInit.length; i++) {
        if (dhtmlxInit[i][IDX_VERIFICATOR]) {
            dp.setVerificator(i, dhtmlxInit[i][IDX_VERIFICATOR]);
        }
    }
    /*    
    dp.defineAction("invalid",function(response){ alert(response.getAttribute("message")); return true; });
    dp.defineAction("error",function(response){   alert(response.getAttribute("message")); return true; });
     */
    dp.defineAction("delete",function(response){   
        dhtmlxRemoveFilter();
        return true; }
            );
//    dp.attachEvent("onBeforeUpdate",        function(id,state,data)             { return onBeforeUpdateDataProcessor(id,state,data); })
    dp.attachEvent("onAfterUpdate",         function(id, action, tid, tag)      { return onAfterUpdateDataProcessor(id, action, tid, tag); });
//    dp.attachEvent("onAfterUpdateFinish",   function()                          { onAfterUpdateFinishDataProcessor(); })
/*    dp.attachEvent("onRowMark",function(){ dhtmlx.message({text: "onRowMark"+JSON.stringify(arguments), expire: DHTMLXEXPIRE}); return true; })
    dp.attachEvent("onValidatationError",function(){ dhtmlx.message({text: "onValidatationError"+JSON.stringify(arguments), expire: DHTMLXEXPIRE}); return true; })
    dp.attachEvent("onFullSync",function(){ dhtmlx.message({text: "onFullSync"+JSON.stringify(arguments), expire: DHTMLXEXPIRE}); return true; })	
*/    
    dp.init(grid);
}/*
function onBeforeUpdateDataProcessor(id, state, data) {
    dhtmlx.message({text: "onBeforeUpdate"+JSON.stringify(arguments), expire: DHTMLXEXPIRE});
    if (state=="deleted") {
        // On supprime les filtres pour laisser dataprocessor modifier les lignes
        dhtmlxRemoveFilter();
    }
    return true;
}*/
//------------------------------------------------------------------------------
function onAfterUpdateDataProcessor(id, action, tid, tag) {
    if (DHTMLXDEBUG) dhtmlx.message({text: "onAfterUpdate"+JSON.stringify(arguments), expire: DHTMLXEXPIRE});
    switch (action) {
        case "insert":
            // Rempli les colonnes avec les liens après l'insert
            for (i = 0; i < dhtmlxInit.length; i++) {
                if (dhtmlxInit[i][IDX_LINK_AFTERINSERT]) {
                    grid.cellById(tid, i).setValue(dhtmlxInit[i][IDX_LINK_AFTERINSERT]);
                }
            }
            if (typeof dhtmlxgridAfterInsert == 'function' && $.isFunction(dhtmlxgridAfterInsert)) {
                dhtmlxgridAfterInsert(id, action, tid, tag);
            }
            dhtmlx.message({text: 'Enregistrement ajouté'});
            dhtmlxgridModified(false);
            rowIsInserted = false;
            rowIdInserted=0;
            break;
        case "update":
            if (tid && id!=tid) {
                grid.changeRowId(id, tid);
            }
            if (typeof dhtmlxgridAfterUpdate == 'function' && $.isFunction(dhtmlxgridAfterUpdate)) {
                dhtmlxgridAfterUpdate(id, action, tid, tag);
            }
            dhtmlx.message({text: 'Enregistrement modifié'});
            dhtmlxgridModified(false);
//            dp.setUpdated(id);
            break;
        case "delete":
            if (tid && id!=tid) {
//                dp.setUpdated(id, false);
                grid.changeRowId(id, tid);
            }
            if (typeof dhtmlxgridAfterDelete == 'function' && $.isFunction(dhtmlxgridAfterDelete)) {
                dhtmlxgridAfterDelete(id, action, tid, tag);
            }
            dhtmlx.message({text: 'Enregistrement supprimé'});
            dhtmlxgridModified(false);
            break;
        case "error":
            dp.setUpdated(tid);
            break;
        case "timeout":
            // redirige sur la page de login
            document.location.href=urlAuthentification;
            return false;
            break;
    }
    // On récupère les informations supplémentaires
    valide= getXmlResponse(tag,"valide");
    msg= getXmlResponse(tag,"msg");
    level= getXmlResponse(tag,"level");
    journal_id= getXmlResponse(tag,"journal_id");
//    newRows= getXmlResponse(tag,"rows");
    newRows= getXmlObject(tag,"rows");

    if (newRows){
        if (typeof dhtmlxgridBeforeRefreshRows == 'function' && $.isFunction(dhtmlxgridBeforeRefreshRows)) {
            dhtmlxgridBeforeRefreshRows(id, action, tid, tag);
        }
        dhtmlxRemoveFilter();
        grid._refresh_mode=[true,true,false]; //2nd and 3rd parameters are the same as in updateFromXML command
        grid.loadXMLString(newRows);
        if (typeof dhtmlxgridAfterRefreshRows == 'function' && $.isFunction(dhtmlxgridAfterRefreshRows)) {
            dhtmlxgridAfterRefreshRows(id, action, tid, tag);
        }
    }
    // Affiche le message de retour
    if (msg != '') { 
        if (action == "error") { // Erreur SQL
            dhtmlx.alert({title: "Erreur!", type: "alert-error", text: msg, expire: -1 });
        } else { // Il y a une erreur de validation, on affiche le message
            dhtmlx.message({type: "error", text: msg, expire: -1 });
        }
    }
    // On restaure les filtres dans tous les cas : delete, error,loadXml ....
    dhtmlxRestoreFilter();
    return true; //confirm block 
}/*
function onAfterUpdateFinishDataProcessor() {
    // On ajoute les filtres puisque dataprocessor a modifier les lignes
    dhtmlxRestoreFilter();    
}*/
//------------------------------------------------------------------------------
function addRow() {
    if (rowIsModified)
        return;
    initRow = '';
    for (i = 0; i < grid.getColumnsNum(); i++) {
        initRow += ',';
    }
    rowIsInsertion = true; // Permet de supprimer la validation des champs
    //if (DHTMLXDEBUG) dhtmlx.message({text: "addRow*Debut", expire: DHTMLXEXPIRE});
    rowIdInserted=grid.uid();
    dhtmlxRemoveFilter();
    grid.addRow(rowIdInserted, initRow, 0);
    grid.selectRowById(rowIdInserted);
    // Rempli les colonnes avec les liens avant l'insert
    for (i = 0; i < dhtmlxInit.length; i++) {
        if (dhtmlxInit[i][IDX_LINK_BEFOREINSERT] && i < grid.getColumnsNum()) {
            grid.cellById(rowIdInserted, i).setValue(dhtmlxInit[i][IDX_LINK_BEFOREINSERT]);
        }
    }
    // Rempli les colonnes avec les filtres
    for (i = 0; i < grid.getColumnsNum(); i++) {
        filter=grid.getFilterElement(i);
        if (filter && filter.value!='') {
            if (filter.type=="select-one") {
                selectCombo(grid.cellById(rowIdInserted, i),filter.value);
            } else {
                grid.cellById(rowIdInserted, i).setValue(filter.value);
            }
        }
    }
    dhtmlxRestoreFilter();
    rowIsInsertion = false;
    rowIsInserted = true;
    dhtmlxgridModified(true,rowIdInserted,'inserted');
    //if (DHTMLXDEBUG) dhtmlx.message({text: "addRow*Fin", expire: DHTMLXEXPIRE});
}
//------------------------------------------------------------------------------
function dupliqueRow(id) {
    if (rowIsModified)
        return;
    fromId=grid.getSelectedRowId();
    rowIdInserted=grid.uid();
    dhtmlxRemoveFilter();
    addRow(rowIdInserted);
    dhtmlxRestoreFilter();
    grid.copyRowContent(fromId,rowIdInserted);
}
//------------------------------------------------------------------------------
function removeRow(id,cInd) {
    // 02/06/2016 On généralise à tous les écrans la vérification de l'icone
    // On vérifie que l'icone est dans la cellule
    if (grid.cellById(id, cInd).getValue()=='') return;
    if (rowIsModified) return;
    // Si la ligne n'est pas en base de données, on ne demande pas la confirmation
    if (id === rowIdInserted) {
        confirmedRemoveRow(id);
    } else {
        dhtmlx.confirm({
            type: "confirm-warning",
            text: "Voulez-vous vraiment supprimer l'enregistrement ?",
            ok: "Oui",
            cancel: "Non",
            callback: function(result) {
                if (result == true)
                    confirmedRemoveRow(id);
            }
        });
    }
}
function confirmedRemoveRow(id) {
    dhtmlxgridModified(true,id,'deleted');
    grid.deleteRow(id);
    dp.sendData(id);
}
//------------------------------------------------------------------------------
function dhtmlxgridUndo() {
    id = grid.getSelectedRowId();
    //if (DHTMLXDEBUG) dhtmlx.message({text: "dhtmlxgridUndo*"+id, expire: DHTMLXEXPIRE});
    if (id == rowIdInserted) { // Attention === ne marche par !!! (???)
        //if (DHTMLXDEBUG) dhtmlx.message({text: "dhtmlxgridUndo*"+id, expire: DHTMLXEXPIRE});
        dhtmlxRemoveFilter();
        grid.deleteSelectedRows();
        dhtmlxRestoreFilter();
        rowIsInserted = false;
    } else {
        for (i = 0; i < grid.getUndo().length; i++)
            grid.doUndo();
        if (id) {
            dp.setUpdated(id, false);
            grid.setRowColor(id, "");
        }
    }
    grid._UndoRedoData = [];
    grid._UndoRedoPos = -1;
    dhtmlxgridModified(false);
}
//------------------------------------------------------------------------------
function dhtmlxgridModified(isModified,rowId,action) {
    //if (DHTMLXDEBUG) dhtmlx.message({text: "dhtmlxgridModified*"+isModified+rowId+"*"+action, expire: DHTMLXEXPIRE});
    rowIsModified = isModified;
    if (isModified) {
        if (rowSelected!=rowId){
            rowSelected=rowId;
            rowAction=action;
            if (document.getElementById('cancel_grid')) document.getElementById('cancel_grid').style.visibility="visible";
            blockNavigation();
            dp.setUpdated(rowId,true);
            grid.setUserData(rowId, "!nativeeditor_status", action);
        }
    } else {
        //if (dp) dp.setUpdated(id, false);
        if (document.getElementById('cancel_grid')) document.getElementById('cancel_grid').style.visibility="hidden";
        unblockNavigation();
        rowSelected=null;
        rowAction='';
    }
}
//------------------------------------------------------------------------------
$.prototype.saveDisabled = function () {
    $.each(this,function (index, e1) {
        $(e1).attr('_disabled',$(e1).prop('disabled'));
    });
};
$.prototype.restoreDisabled = function () {
    $.each(this,function (index, e1) {
        $(e1).prop('disabled',$(e1).attr('_disabled')=='true');
    });
};
function blockNavigation() {
    $('a').bind("click.myDisable", function() { return false; });
    $('input').saveDisabled();
    $('select').saveDisabled();
    $('textarea').saveDisabled();
    $('input').prop('disabled', true);
    $('select').prop('disabled', true);
    $('textarea').prop('disabled', true);
//    $('input').attr('disabled', 'disabled');
//    $('select').attr('disabled', 'disabled');
    $('#cancel_grid').removeAttr('disabled');
    //if (DHTMLXDEBUG) dhtmlx.message({text: "Navigation bloquée", expire: -1});
}
function unblockNavigation() {
    $('a').unbind("click.myDisable");
    $('input').restoreDisabled();
    $('select').restoreDisabled();
    $('textarea').restoreDisabled();
//    $('input').removeAttr('disabled');
//    $('select').removeAttr('disabled');
    //if (DHTMLXDEBUG) dhtmlx.message({text: "Navigation débloquée", expire: -1});
}
 
 //------------------------------------------------------------------------------
function getXmlResponse(tag,tagName){
  tagElements=tag.getElementsByTagName(tagName);
  if (tagElements.length==1){
      return tagElements[0].textContent;
  } else {
      return null;
  }
   
}
function getXmlObject(tag,tagName){
  tagElements=tag.getElementsByTagName(tagName);
  if (tagElements.length==1){
      return tagElements[0].outerHTML;
      ///return tagElements;
  } else {
      return null;
  }
   
}