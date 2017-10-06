/*
delete from pai_int_log where idtrt=0
select * from depot
SET @idtrt=null;
CALL ALIM_PAIE(0,@idtrt,'2016-11-08',4,1,'2016-10-24',1,1,1,1,1,1,1,1);
SET @idtrt=null;
CALL alim_tournee(0,@idtrt,'2017-02-09',null,null);
SET @idtrt=null;
CALL ALIM_ACTIVITE(0,@idtrt,'2017-02-09',null,null,null);
SET @idtrt=null;
CALL supprim_tournee(0,@idtrt,'2016-11-08',null,null);
SET @idtrt=null;
call ALIM_TOURNEE_FROM_LAST_WEEK(0,@idtrt,'2016-11-01',null,2);

select * from pai_int_traitement order by id desc
select * from pai_int_log order by 
select * from pai_tournee where duree_attente<>'00:00' and duree_retard<>'00:00' and date_extrait is null order by date_distrib desc
*/
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS ALIM_PAIE;
CREATE PROCEDURE ALIM_PAIE(
    IN 		_utilisateur_id INT,
    INOUT _idtrt		      INT,
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT,
    IN 		_date_org                           DATE,
    IN 		_alim_tournee                       BOOLEAN,
    IN 		_maz_duree_attente                  BOOLEAN,
    IN 		_maz_duree_retard                   BOOLEAN,
    IN 		_maz_nbkm_paye_tournee              BOOLEAN,
    IN 		_alim_activite_presse               BOOLEAN,
    IN 		_maz_nbkm_paye_activite_presse      BOOLEAN,
    IN 		_maz_duree_activite_horspresse      BOOLEAN,
    IN 		_maz_nbkm_paye_activite_horspresse  BOOLEAN
) BEGIN
    DECLARE CONTINUE  HANDLER FOR SQLWARNING    CALL int_logwarning(_idtrt);
    DECLARE EXIT      HANDLER FOR SQLEXCEPTION  CALL int_logerreur(_idtrt);
        
    CALL int_logdebut(_utilisateur_id,_idtrt,'ALIM_PAIE',_date_distrib,_depot_id,_flux_id);
    if (_alim_tournee) then
      if (_date_org is null) then
        CALL ALIM_TOURNEE(_utilisateur_id, _idtrt, _date_distrib, _depot_id, _flux_id);
       else
        call ALIM_TOURNEE_FROM_DATE(_utilisateur_id, _idtrt, _date_distrib, _depot_id, _flux_id, _date_org);
       end if;
    end if;
    if (_maz_duree_attente) then
      update pai_heure ph
      inner join groupe_tournee gt on ph.groupe_id=gt.id
      set ph.duree_attente='00:00' 
      where ph.date_distrib=_date_distrib 
      and (gt.depot_id=_depot_id or _depot_id is null) 
      and (gt.flux_id=_flux_id or _flux_id is null)
      and ph.duree_attente<>0 
      ;
      call int_logrowcount_C(_idtrt,5,'ALIM_PAIE', 'Suppression des attentes');
    end if;
    if (_maz_duree_retard) then
      update pai_tournee pt
      set pt.duree_retard='00:00' 
      where pt.date_distrib=_date_distrib 
      and (pt.depot_id=_depot_id or _depot_id is null) 
      and (pt.flux_id=_flux_id or _flux_id is null)
      and pt.duree_retard<>0 
      ;
       call int_logrowcount_C(_idtrt,5,'ALIM_PAIE', 'Suppression des retards');
   end if;
    if (_maz_nbkm_paye_tournee) then
      update pai_tournee pt
      set pt.nbkm_paye=0
      where pt.date_distrib=_date_distrib 
      and (pt.depot_id=_depot_id or _depot_id is null) 
      and (pt.flux_id=_flux_id or _flux_id is null)
      and pt.nbkm_paye<>0 
      ;
       call int_logrowcount_C(_idtrt,5,'ALIM_PAIE', 'Suppression des kilomètres payés tournées');
    end if;
    if (_alim_activite_presse) then
      if (_date_org is null) then
        CALL ALIM_ACTIVITE(_utilisateur_id, _idtrt, _date_distrib, _depot_id, _flux_id, false);
       else
        call ALIM_ACTIVITE_FROM_DATE(_utilisateur_id, _idtrt, _date_distrib, _depot_id, _flux_id, _date_org);
       end if;
    end if;
    if (_maz_nbkm_paye_activite_presse) then
      update pai_activite pa
      inner join ref_activite ra on pa.activite_id=ra.id and not ra.est_hors_presse
      set pa.nbkm_paye=0 
      where pa.date_distrib=_date_distrib 
      and (pa.depot_id=_depot_id or _depot_id is null) 
      and (pa.flux_id=_flux_id or _flux_id is null)
      and pa.nbkm_paye<>0 
      ;
       call int_logrowcount_C(_idtrt,5,'ALIM_PAIE', 'Suppression des kilomètres payés activités presse');
    end if;
    if (_maz_duree_activite_horspresse) then
      update pai_activite pa
      inner join ref_activite ra on pa.activite_id=ra.id and ra.est_hors_presse
      set pa.duree='00:00' 
      where pa.date_distrib=_date_distrib 
      and (pa.depot_id=_depot_id or _depot_id is null) 
      and (pa.flux_id=_flux_id or _flux_id is null)
      and pa.duree<>0 
      ;
       call int_logrowcount_C(_idtrt,5,'ALIM_PAIE', 'Suppression des durée');
    end if;
     if (_maz_nbkm_paye_activite_horspresse) then
      update pai_activite pa
      inner join ref_activite ra on pa.activite_id=ra.id and ra.est_hors_presse
      set pa.nbkm_paye=0 
      where pa.date_distrib=_date_distrib 
      and (pa.depot_id=_depot_id or _depot_id is null) 
      and (pa.flux_id=_flux_id or _flux_id is null)
      and pa.nbkm_paye<>0 
      ;
        call int_logrowcount_C(_idtrt,5,'ALIM_PAIE', 'Suppression des kilomètres payés activités hors-presse');
   end if;
   CALL int_logfin2(_idtrt,'ALIM_PAIE');
    
    call recalcul_horaire(@validation_id,_depot_id,_flux_id,_date_distrib,null);
END;
