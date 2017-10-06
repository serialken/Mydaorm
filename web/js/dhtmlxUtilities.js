//      IDX_ENTETE          ,IDX_HEADER1   ,IDX_HEADER2    , IDX_LINK_BEFOREINSERT      ,IDX_LINK_AFTERINSERT,IDX_FUNC_ONSELECT,IDX_VERIFICATOR
IDX_ENTETE = 0;
IDX_HEADER1 = 1;
IDX_HEADER2 = 2;
IDX_VERIFICATOR = 3;
IDX_LINK_BEFOREINSERT = 4;
IDX_LINK_AFTERINSERT = 5;
IDX_FUNC_ONSELECT = 6;
IDX_FUNC_ONCELLCHANGE = 7;

cellIdSorting=null;

dhtmlxEventOnFilterEndId=null;

//------------------------------------------------------------------------------
function initGrid(dhtmlx_img, path, selectedRowId) {
    gridIsLoaded = false;
    dhtmlxgridModified(false);
    grid.setImagePath(dhtmlx_img);

    /** pagination */
//        grid.enablePaging(true, 20, 3, "recinfoArea");
//        grid.setPagingSkin("toolbar", "dhx_skyblue");

//       grid.enableColumnAutoSize(true); 
//       grid.enableRowsHover(true,'grid_hover');
//        grid.enableSmartRendering(true);
//grid.enableLightMouseNavigation(true); // enables/disables light mouse navigation mode (row selection with mouse over, editing with single click), mutual exclusive with enableEditEvents
    grid.attachEvent("onXLS", function() {
        document.getElementById('cover').style.display = 'block';
    });
    grid.attachEvent("onXLE", function() {
        document.getElementById('cover').style.display = 'none';
    });
    grid.enableEditEvents(true, false, false);
    grid.enableUndoRedo();
    grid.enableEditTabOnly(true);
    grid.enableAlterCss("","");
    dhtmlxInitHeader(IDX_HEADER1);
    dhtmlxInitHeader(IDX_HEADER2);
    grid.enableColumnMove(true);
    grid.init();
    grid.setSkin("dhx_skyblue");
    grid.enableAlterCss("","");
//    grid.enableAutoHeight(true,500,true);
//    grid.setSizes();
    if (typeof dhtmlxgridBeforeLoad == 'function' && $.isFunction(dhtmlxgridBeforeLoad)) {
        dhtmlxgridBeforeLoad();
    }
    grid.loadXML(path,
            function() {
                //Initialisation des filtres
                grid.loadOrderFromCookie(path + '_order');
                dhtmlxInitCellIdSorting(path + '_sorting');
                grid.loadSortingFromCookie(path + '_sorting');
                grid.loadSizeFromCookie(path + '_size');
//                grid.loadHiddenColumnsFromCookie(path + '_hidden');
                dhtmlxLoadCookieFilter();

                grid.enableAutoSizeSaving(path + '_size'); // enable saving cookies 
                grid.enableSortingSaving(path + '_sorting');
                grid.enableOrderSaving(path + '_order');
//                grid.enableAutoHiddenColumnsSaving(path + '_hidden');
//    grid.enableHeaderMenu("false,false"); // Pour cacher ou montrer une colonne avec un menu click droit
                
                if (selectedRowId) {
                    grid.selectRowById(selectedRowId);
                }
                gridIsLoaded = true;
                if (typeof dhtmlxgridOnLoad == 'function' && $.isFunction(dhtmlxgridOnLoad)) {
                    dhtmlxgridOnLoad();
                }
            }
    );
    grid.attachEvent("onBeforeSorting",dhtmlxEventOnBeforeSort);
//    grid.attachEvent("onFilterStart", dhtmlxEventOnFilterStart);
    dhtmlxEventOnFilterEndId=grid.attachEvent("onFilterEnd", dhtmlxEventOnFilterEnd);
//    grid.attachEvent("onCollectValues", dhtmlxEventOnCollectValues);
    
    grid.attachEvent("onBeforeSelect", dhtmlxEventOnBeforeSelect);
    grid.attachEvent("onRowSelect", dhtmlxEventOnRowSelect);
    grid.attachEvent("onEditCell", dhtmlxEventOnEditCell);
    grid.attachEvent("onCellChanged", dhtmlxEventOnCellChanged);
}
//------------------------------------------------------------------------------
// Récupère le numéro de la colonne triée dans les cookies
function dhtmlxInitCellIdSorting(a) {
    b=grid._getCookie(a,2),b=(b||"").split(",");
    if (b.length>1 && b[0]<grid._cCount)
        cellIdSorting=b[0];
    else
        cellIdSorting=null;
};

//------------------------------------------------------------------------------
function dhtmlxEventOnFilterStart(indexes,values) {
    //dhtmlx.message({text: "dhtmlxEventOnFilterStart*" +indexes+values, expire: -1});
    // var inp = grid.getFilterElement(ind);
    // Envoyer le filtre en ajax
    return true;
}
//------------------------------------------------------------------------------
function dhtmlxEventOnFilterEnd(elements) {
    // dhtmlx.message({text: "dhtmlxEventOnFilterEnd*" +elements, expire: -1});
    // Envoyer le filtre en ajax
    dhtmlxSaveCookieFilter();
    return true;
}
//------------------------------------------------------------------------------
function dhtmlxEventOnCollectValues(index) {
    // dhtmlx.message({text: "dhtmlxEventOnCollectValues*" +index, expire: -1});
    // Envoyer le filtre en ajax
    return true;
}
//------------------------------------------------------------------------------
function dhtmlxRemoveFilter() {
    grid.detachEvent(dhtmlxEventOnFilterEndId);
/*    for (var i=0;i<grid.filters.length;i++){
        grid.filterBy(i,""); //unfilters the grid
    }*/
    var num_cols = grid.getColumnCount();

    for (var col_idx=0; col_idx<num_cols; col_idx++) {
        var filter = grid.getFilterElement(col_idx);
        if (filter && filter.value!="") { // not all columns may have a filter
            grid.filterBy(col_idx,""); //unfilters the grid
        }
    }    
    grid._f_rowsBuffer = null; //clears the cache
    return true;
}
//------------------------------------------------------------------------------
function dhtmlxRestoreFilter() {
    dhtmlxLoadCookieFilter();
    dhtmlxEventOnFilterEndId=grid.attachEvent("onFilterEnd", dhtmlxEventOnFilterEnd);
}
//------------------------------------------------------------------------------
function dhtmlxLoadCookieFilter() {
// cookies to filter vals
var cookie_prefix = "MRoad_filter_";
var num_cols = grid.getColumnCount();
var filter_ok = false;

    for (var col_idx=0; col_idx<num_cols; col_idx++) {
        var filter = grid.getFilterElement(col_idx);
        if (filter) { // not all columns may have a filter
            var col_id = grid.getColumnId(col_idx);
            var filter_val = getCookie(cookie_prefix+col_id);
            if (filter_val) {
                filter.value = filter_val;
                filter_ok=true;
            }
        }
    } 
    if (filter_ok) {
        grid.filterByAll();
    }
    return true;
}
//------------------------------------------------------------------------------
function dhtmlxSaveCookieFilter() {
// filter vals to cookies
var cookie_prefix = "MRoad_filter_";
var cookie_dur = 365;
var num_cols = grid.getColumnCount();

    for (var col_idx=0; col_idx<num_cols; col_idx++) {
        var filter = grid.getFilterElement(col_idx);
        if (filter) { // not all columns may have a filter
            var col_id = grid.getColumnId(col_idx);
            var cookie_name = cookie_prefix+col_id;
            setCookie(cookie_name, filter.value, cookie_dur);
            // Si employe_id, on le sauvegarde aussi en session
/*            if (col_id=="employe_id") { ATTENTION, ici il faudrait envoyer l'identifiant au lieu du nom
                ajaxSaveSessionValue(col_id,filter.value);
            }*/
        }
    }
    return true;
}
//------------------------------------------------------------------------------
function dhtmlxEventOnBeforeSort(ind,grid) {
    if (DHTMLXDEBUG) dhtmlx.message({text: "dhtmlxEventOnBeforeSort*" +ind, expire: DHTMLXEXPIRE});
    cellIdSorting=ind;
    return true;
}
//------------------------------------------------------------------------------
function dhtmlxEventOnBeforeSelect(new_row, old_row) {
    if (DHTMLXDEBUG) dhtmlx.message({text: "dhtmlxEventOnBeforeSelect*" +new_row+'*'+old_row, expire: DHTMLXEXPIRE});
    if (rowIsModified && new_row !== old_row) {
        // Empeche le changement de ligne si on est en modification
        return false;
    }
    if (typeof dhtmlxgridBeforeSelect == 'function' && $.isFunction(dhtmlxgridBeforeSelect)) {
        return dhtmlxgridBeforeSelect(new_row, old_row);
    }
    return true;
}
//------------------------------------------------------------------------------
function dhtmlxEventOnRowSelect(rId, cInd, nValue, oValue) {
    if (DHTMLXDEBUG) dhtmlx.message({text: "onRowSelect*" + rId + '*' + cInd + '*' + nValue + '*' + oValue, expire: DHTMLXEXPIRE});
    dhtmlxEvalFunc(rId, cInd, nValue, IDX_FUNC_ONSELECT);
    if (typeof dhtmlxgridRowSelect == 'function' && $.isFunction(dhtmlxgridRowSelect)) {
        dhtmlxgridRowSelect(rId, cInd, nValue, oValue);
    }
}
//------------------------------------------------------------------------------
function dhtmlxEventOnEditCell(stage, rId, cInd, nValue, oValue) {
    if (DHTMLXDEBUG) dhtmlx.message({text: "dhtmlxEventOnEditCell*" + stage + '*' + rId + '*' + cInd + '*' + nValue + '*' + oValue, expire: DHTMLXEXPIRE});
    returnEditCell=true;
    if (typeof dhtmlxgridOnEditCell == 'function' && $.isFunction(dhtmlxgridOnEditCell)) {
        returnEditCell=dhtmlxgridOnEditCell(stage, rId, cInd, nValue, oValue);
    }
    if (returnEditCell && stage === 2 && nValue !== oValue) { // onEdit finish
        dhtmlxgridModified(true,rId,"updated");
    }
    return returnEditCell;
}
//------------------------------------------------------------------------------
function dhtmlxEventOnCellChanged(rId, cInd, nValue) {
    if (gridIsLoaded && DHTMLXDEBUG) dhtmlx.message({text: "onCellChanged*" + rId + '*' + cInd + '*' + nValue , expire: DHTMLXEXPIRE});
    dhtmlxEvalFunc(rId, cInd, nValue, IDX_FUNC_ONCELLCHANGE);
    if (typeof dhtmlxgridCellChanged == 'function' && $.isFunction(dhtmlxgridCellChanged)) {
        dhtmlxgridCellChanged(rId, cInd, nValue);
    }
}
//------------------------------------------------------------------------------
function dhtmlxEvalFunc(rId, cInd, nValue, IDX_FUNC) {
     if (gridIsLoaded && dhtmlxInit && dhtmlxInit[cInd] && dhtmlxInit[cInd][IDX_FUNC]) {
        if (DHTMLXDEBUG) dhtmlx.message({text: "dhtmlxEvalFunc*" + dhtmlxInit[cInd][IDX_FUNC], expire: DHTMLXEXPIRE});
        eval(dhtmlxInit[cInd][IDX_FUNC]);
    }
}
//------------------------------------------------------------------------------
function dhtmlxInitHeader(idx) {
    var header = '';
    var headerOk = false;
    for (i = 0; i < dhtmlxInit.length; i++) {
        if (dhtmlxInit[i][idx] && dhtmlxInit[i][idx] !== '#ismodif') {
            header += dhtmlxInit[i][idx];
            headerOk = true;
        }
        if (dhtmlxInit[i][idx] !== '#ismodif' || isModif) {
            header += ',';
        }
    }
    if (headerOk) {
        header = header.substr(0, header.length - 1);
        grid.attachHeader(header);
    }
}
//------------------------------------------------------------------------------
function onClickLink() {
    alert(grid.getSelectedRowId());
}
function goLinkGrid(url, selectedId, newWindow=false) {
    if (selectedId) {
        if (newWindow){
            window.open(url+selectedId)
        } else {
            document.location.href = url + selectedId;
        }
    }
}

//------------------------------------------------------------------------------
// Utilisé pour montrer le message d'erreur généré par l'exception lors d'une action
function expandError() {
    if (document.getElementById("msgCompleteError").style.display !== "none") {
        document.getElementById("msgCompleteError").style.display = "none";
        document.getElementById("moins").style.display = "none";
        document.getElementById("plus").style.display = "inline";
    } else {
        document.getElementById("msgCompleteError").style.display = "inline";
        document.getElementById("moins").style.display = "inline";
        document.getElementById("plus").style.display = "none";
    }
}

//------------------------------------------------------------------------------
function selectCombo(Objet,texte)
{
	var  isChange = false;
	var i=0;
        var combo=Objet.combo;
	while ((i<combo.size()) && (combo.values[i]!==texte))	{i++;}
	if (i!==combo.size()){
            isChange=(combo.selectedIndex!==i);
            Objet.setValue(combo.keys[i]);
	}
	return isChange;
}

//------------------------------------------------------------------------------
function sortDate(a,b,order){
    if (b=='') return (order=="asc"?1:-1);
    a=a.split("/")
    b=b.split("/")
    if (a[2]==b[2]){
        if (a[1]==b[1])
            return (a[0]>b[0]?1:-1)*(order=="asc"?1:-1);
        else
            return (a[1]>b[1]?1:-1)*(order=="asc"?1:-1);
    } else
        return (a[2]>b[2]?1:-1)*(order=="asc"?1:-1);
};
/*
function sortDate(a,b,order){
    if (b=='') return (order=="asc"?1:-1);
    a=a.split("/");
    b=b.split("/");
    if (a[2]==b[2]){
        if (a[1]==b[1]) return (a[0]>b[0]?1:-1)*(order=="asc"?1:-1);
        else return (a[1]>b[1]?1:-1)*(order=="asc"?1:-1);
    } else return (a[2]>b[2]?1:-1)*(order=="asc"?1:-1);
};
*/
function sortDateTime(a,b,order){
    if (b==='') return (order=="asc"?1:-1);
    aD=a.substr(0,10);
    bD=b.substr(0,10);
    aD=aD.split("/");
    bD=bD.split("/");
    if (aD[2]===bD[2]){
        if (aD[1]===bD[1]) {
            if (aD[0]===bD[0]) {
                aH=a.substr(11,8);
                bH=b.substr(11,8);
                aH=aH.split(":");
                bH=bH.split(":");
                if (aH[0]===bH[0]){
                    if (aH[1]===bH[1]) return (aH[2]>bH[2]?1:-1)*(order==="asc"?1:-1);
                    else return (aH[1]>bH[1]?1:-1)*(order==="asc"?1:-1);
                } else return (aH[0]>bH[0]?1:-1)*(order==="asc"?1:-1);
            } else return (aD[0]>bD[0]?1:-1)*(order==="asc"?1:-1);
        } else return (aD[1]>bD[1]?1:-1)*(order==="asc"?1:-1);
    } else return (aD[2]>bD[2]?1:-1)*(order==="asc"?1:-1);
};
/* Ne sert à rien
  function sortTime(a,b,order){ 
    a=a.split(":")
    b=b.split(":")
    if (a[0]==b[0])
        return (a[1]>b[1]?1:-1)*(order=="asc"?1:-1);
    else
        return (a[0]>b[0]?1:-1)*(order=="asc"?1:-1);
};
*/
function sortCombo(a,b,ord,a_id,b_id){
    a=grid.cells(a_id,cellIdSorting).getText();
    b=grid.cells(b_id,cellIdSorting).getText();
    return ord=="asc"?(a>b?1:-1):(a>b?-1:1);
};
/**
 * dhtmlxGrid
 * Total d'une colonne
 * @param string mygrid variable de nom du tableau
 * @param integer ind
 * @param int que_entier  si non defini ou si == 1 => les valeurs du tableau a prendre en compte ne sont que les entiers
 * @returns {Number}
 */
function sumColumn(mygrid, ind, que_entier) {
    var out = 0;
    var val = 0;
    for (var i = 0; i < mygrid.getRowsNum(); i++) {
        if (typeof que_entier == 'undefined' || que_entier == 1)
        {
            if ((parseFloat(mygrid.cells2(i, ind).getValue()) == parseInt(mygrid.cells2(i, ind).getValue())) && !isNaN(mygrid.cells2(i, ind).getValue()))
            {
                out += parseFloat(mygrid.cells2(i, ind).getValue());
            }
        }
        else
        {
            val = parseFloat(mygrid.cells2(i, ind).getValue());
            if (!isNaN(val))
            {
                out += val;
            }
        }
    }
    return out;
}

//------------------------------------------------------------------------------
function ajaxMessage(msg) {
    dhtmlx.message({
        id:"ajaxBox",
        text:msg,
        expire:-1
    });
}
function ajaxSucces(msg) {
    dhtmlx.message.hide("ajaxBox");
    dhtmlx.message({
        id:"ajaxBox",
        text:msg
    });
}
function ajaxErreur(xhr, ajaxOptions, thrownError) {
    dhtmlx.message.hide("ajaxBox");
    msg=xhr.responseText;
    if (!msg) {
        msg=xhr.status+thrownError
    }
    dhtmlx.alert({
        id:"errorBox",
        title:"Erreur!",
        type:"alert-error",
        text:msg,
        expire: -1
    });
}
 //------------------------------------------------------------------------------
function ajaxReloadCombo(_combo,_url,_data,_succes) {
     _combo.empty();
     $.ajax({
         url : _url,
         data: _data  ,
         dataType: 'json',
         success: function(json) {
/*                if (_blank) {
                 _combo.append('<option value=""></option>');
             }*/
             $.each(json, function(index, value) {
                 //alert(value);
                 _combo.append('<option value="'+ index +'">'+ value +'</option>');
             });
             if ( $.isFunction(_succes) ) {
                 _succes();
             }
         }
     });
 }
 //------------------------------------------------------------------------------
function ajaxReloadCheckBox(_div,_url,_data,_succes) {
     _div.empty();
     $.ajax({
         url : _url,
         data: _data  ,
         dataType: 'json',
         success: function(json) {
             $.each(json, function(index, value) {
                 $('<input />', { type: 'checkbox', value: index , checked:true}).appendTo(_div);
                 $('<label />', { 'for': index, text: value }).appendTo(_div);
                 $('<br />').appendTo(_div);
             });
             if ( $.isFunction(_succes) ) {
                 _succes();
             }
         }
     });
 }
//------------------------------------------------------------------------------
function ajaxReloadComboFilter(_combo,_url,_data,_succes) {
     _combo.clear();
     $.ajax({
         url: _url,
         data: _data  ,
         dataType: 'json',
         success: function(json) {
             $.each(json, function(index, value) {
                 _combo.put(index,value);
             });
             if ( $.isFunction(_succes) ) {
                 _succes();
             }
         }
     });
 }
//------------------------------------------------------------------------------
function ajaxSaveSession($objet) {
    $.ajax({
        url: "ajax-save-session",
        type: "GET",
        data: { 
            name : $objet.attr("id"),
            value: $objet.prop("checked") 
        }
    });
}
//------------------------------------------------------------------------------
function ajaxSaveSessionValue(id,value) {
    $.ajax({
        url: "ajax-save-session",
        type: "GET",
        data: { 
            name : id,
            value: value 
        }
    });
}
//------------------------------------------------------------------------------
//------------------------------------------------------------------------------
function dhtmlxLoadCookieObject($objet) {
// cookies to filter vals
    var cookie_prefix = "MRoad_object_";
    var val = getCookie(cookie_prefix+$objet.attr("id"));
    $objet.prop('checked',val=="true");
    return true;
}
//------------------------------------------------------------------------------
function dhtmlxSaveCookieObject($objet) {
// filter vals to cookies
    var cookie_prefix = "MRoad_object_";
    var cookie_dur = 365;
    var cookie_name = cookie_prefix+$objet.attr("id");
    setCookie(cookie_name, $objet.prop("checked"), cookie_dur);
    return true;
}
    
//------------------------------------------------------------------------------
//------------------------------------------------------------------------------
function MyGetSeconds(timeStr) {
//    if (/^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/.test(timeStr)) {
    if (/^([0-9]|[0-9][0-9]):[0-5][0-9]:[0-5][0-9]$/.test(timeStr)) {
        var str = timeStr.split(':');
        var hours = parseInt(str[0]);
        var mins = parseInt(str[1]);
        var secs = parseInt(str[2]);
        return hours * 3600 + mins*60 + secs;
    } else if (/^([0-9]|[0-9][0-9]):[0-5][0-9]$/.test(timeStr)) {
        var str = timeStr.split(':');
        var hours = parseInt(str[0]);
        var mins = parseInt(str[1]);
        return hours * 3600 + mins*60;
    } else {
        return 0;
    }
}
 
function MyGetMinutes(timeStr) {
//    if (/^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/.test(timeStr)) {
    if (/^([0-9]|[0-9][0-9]):[0-5][0-9]$/.test(timeStr)) {
        var str = timeStr.split(':');
        var hours = parseInt(str[0]);
        var mins = parseInt(str[1]);
        return hours * 60 + mins;
    } else {
//        alert(timeStr);
        return 0;
    }
}

function Second2Time(value) {
    var hours = parseInt(value / 3600);
    var mins = parseInt(value % 3600 / 60);
    var secs = parseInt(value % 3600 % 60);

    return (hours<10?"0":"")+hours + ":" + (mins<10?"0":"")+mins + ":" + (secs<10?"0":"")+secs ;
}

function Minute2Time(value) {
    var hours = parseInt(value / 60);
    var mins = parseInt(value % 60);

    return (hours<10?"0":"")+hours + ":" + (mins<10?"0":"")+mins ;
}
    
//------------------------------------------------------------------------------
//------------------------------------------------------------------------------
function confirmAction(msg, action ) {
    dhtmlx.confirm({
        type: "confirm-warning",
        text: msg,
        ok: "Oui",
        cancel: "Non",
        callback: function(result) {
            if (result == true)
                eval(action);
        }
    });
}