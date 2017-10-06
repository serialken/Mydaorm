/* ========================================================================
 * MRoad Hors Presse: lister.js v0.1
 * Liste des campagne Hors Presse
 * ========================================================================
 * Author: Marc-Antoine Adélise
 * email: marcantoine.adelise@amaury.com
 * ======================================================================== */
(function () {
    console.log('JS des listes de campagne');

    var oHorsPresse = window.horspresse;

    // changement de l'étape
    oHorsPresse.currentState.mode = 'list';
    oHorsPresse.currentState.slug = 'lister';
    
    /**
     * Ergonomie & interactions
     */
    $(document).ready(function(){
        $('div.initCamp button.btn-primary').on('click',function(){
            console.log('-> ajout de campagne');
            window.location.href = oHorsPresse.baseUrls('new');
        });
         $('td.content button.btn-primary.new').on('click',function(){
            window.location.href = oHorsPresse.baseUrls('new'); 
         });
    });
    
}());
