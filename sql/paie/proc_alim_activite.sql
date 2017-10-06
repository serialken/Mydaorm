/*
SET @idtrt=null;
CALL ALIM_ACTIVITE(0,@idtrt,'2016-11-01',null,null,null);
SET @idtrt=null;
CALL SUPPRIM_ACTIVITE(0,@idtrt,'2016-10-09',null,null,null);
select * from pai_int_log where idtrt in(select max(id) from pai_int_traitement) order by id desc;
select * from pai_int_traitement order by id desc;
select @idtrt;
select * from  pai_int_log where idtrt=1;
select * from pai_activite where date_distrib='2016-10-10' and xaoid is not null
select * from pai_activite where date_distrib='2015-12-20' and duree_garantie<>'00:00'
select * from pai_activite where date_distrib='2015-12-21' and duree_garantie<>'00:00'
select * from pai_activite where employe_id=1698 and date_distrib>='2015-12-19'

SET @idtrt=null;
call alim_act_maj_garantie(0,@idtrt,null,null,null);
SET @idtrt=1;
call alim_act_insert_activite_hors_presse_retro(0,@idtrt,null,null);

SET @idtrt=null;
call ALIM_ACTIVITE_FROM_LAST_WEEK(0,@idtrt,'2016-11-01',null,2,0);
*/
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS ALIM_ACTIVITE;
CREATE PROCEDURE ALIM_ACTIVITE(
    IN 		_utilisateur_id INT,
    INOUT _idtrt		      INT,
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT,
    IN    _est_hors_presse BOOLEAN
) BEGIN
    DECLARE CONTINUE  HANDLER FOR SQLWARNING    CALL int_logwarning(_idtrt);
    DECLARE EXIT      HANDLER FOR SQLEXCEPTION  CALL int_logerreur(_idtrt);
        
    CALL int_logdebut(_utilisateur_id,_idtrt,'ALIM_ACTIVITE',_date_distrib,_depot_id,_flux_id);
    CALL alim_act_exec(_utilisateur_id, _idtrt, _date_distrib, _depot_id, _flux_id,_est_hors_presse);
    CALL int_logfin2(_idtrt,'ALIM_ACTIVITE');
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS SUPPRIM_ACTIVITE;
CREATE PROCEDURE SUPPRIM_ACTIVITE(
    IN 		_utilisateur_id INT,
    INOUT _idtrt		      INT,
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT,
    IN    _est_hors_presse BOOLEAN
) BEGIN
    DECLARE CONTINUE  HANDLER FOR SQLWARNING    CALL int_logwarning(_idtrt);
    DECLARE EXIT      HANDLER FOR SQLEXCEPTION  CALL int_logerreur(_idtrt);
        
    CALL int_logdebut(_utilisateur_id,_idtrt,'SUPPRIM_ACTIVITE',_date_distrib,_depot_id,_flux_id);
    CALL alim_act_nettoyage(_utilisateur_id, _idtrt, _date_distrib, _depot_id, _flux_id,_est_hors_presse);
    CALL int_logfin2(_idtrt,'SUPPRIM_ACTIVITE');
END;
-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS alim_act_exec;
CREATE PROCEDURE alim_act_exec(
    IN 		_utilisateur_id INT,
    INOUT _idtrt		      INT,
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT,
    IN    _est_hors_presse BOOLEAN
) BEGIN
DECLARE _validation_id INT;
    INSERT INTO pai_validation(utilisateur_id) VALUES(_utilisateur_id);
  	SELECT LAST_INSERT_ID() INTO _validation_id;
    
    -- 02/05/2017 On alimente plus les activités le 01/05
    IF SUBSTR(_date_distrib,5,6)<>'-05-01' THEN
      -- CALL ALIM_ACTIVITE_FROM_LAST_WEEK(_utilisateur_id, _idtrt, _date_distrib, _depot_id, _flux_id,_est_hors_presse);
    -- ELSE
      CALL alim_act_nettoyage(_utilisateur_id, _idtrt, _date_distrib, _depot_id, _flux_id,_est_hors_presse);
      CALL alim_act_insert_activite(_utilisateur_id, _idtrt, _date_distrib, _depot_id, _flux_id,_est_hors_presse);
    END IF;
    CALL alim_act_insert_activite_hors_presse(_utilisateur_id, _idtrt, _date_distrib, _depot_id, _flux_id,_est_hors_presse);
    
    CALL int_logger(_idtrt, 'alim_act_exec', 'Validation des activites');
    CALL pai_valide_activite(_validation_id, _depot_id, _flux_id, _date_distrib, null);
    CALL int_logger(_idtrt, 'alim_act_exec', 'Recalcul des horaires');
    CALL recalcul_horaire(_validation_id, _depot_id, _flux_id, _date_distrib, null);
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS alim_act_nettoyage;
CREATE PROCEDURE alim_act_nettoyage(
    IN 		_utilisateur_id INT,
    IN    _idtrt		      INT,
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT,
    IN    _est_hors_presse BOOLEAN
) BEGIN
    CALL alim_act_nettoyage_journal(_idtrt, _date_distrib, _depot_id, _flux_id,_est_hors_presse);
    CALL alim_act_nettoyage_activite(_idtrt, _date_distrib, _depot_id, _flux_id,_est_hors_presse);
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS alim_act_nettoyage_journal;
CREATE PROCEDURE alim_act_nettoyage_journal(
    IN    _idtrt		      INT,
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT,
    IN    _est_hors_presse BOOLEAN
) BEGIN
    delete pj
    from pai_journal pj
    inner join pai_activite pa on pj.activite_id=pa.id
    inner join ref_activite ra on pa.activite_id=ra.id
    where pj.date_distrib=_date_distrib and (pj.depot_id=_depot_id or _depot_id is null) and (pj.flux_id=_flux_id or _flux_id is null)
    and pj.date_extrait is null
    and pa.date_extrait is null
    and ra.id>0
    and (ra.est_hors_presse=_est_hors_presse OR _est_hors_presse is null)
    ;
    call int_logrowcount_C(_idtrt,5,'alim_act_nettoyage_journal', 'Nettoyage du journal');
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS alim_act_nettoyage_activite;
CREATE PROCEDURE alim_act_nettoyage_activite(
    IN    _idtrt		      INT,
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT,
    IN    _est_hors_presse BOOLEAN
) BEGIN
    delete pa 
    from pai_activite pa
    inner join ref_activite ra on pa.activite_id=ra.id
    where pa.date_distrib=_date_distrib and (pa.depot_id=_depot_id or _depot_id is null) and (pa.flux_id=_flux_id or _flux_id is null)
    and pa.date_extrait is null
    and ra.id>0
    and (ra.est_hors_presse=_est_hors_presse OR _est_hors_presse is null)
    ;
    call int_logrowcount_C(_idtrt,5,'alim_act_nettoyage_activite', 'Suppression des activites');
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS alim_act_insert_activite;
CREATE PROCEDURE alim_act_insert_activite(
    IN    _utilisateur_id	INT,
    IN    _idtrt		      INT,
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT,
    IN    _est_hors_presse BOOLEAN
) BEGIN
    insert into pai_activite(
      utilisateur_id,date_creation
      ,date_distrib
      ,depot_id,flux_id
      ,activite_id,employe_id,transport_id
      ,heure_debut,duree
      ,nbkm_paye
      ,commentaire
      ,date_extrait
    ) select 
      _utilisateur_id,sysdate()
      ,_date_distrib
      ,ma.depot_id,ma.flux_id
      ,ma.activite_id,ma.employe_id,ma.transport_id
      ,ma.heure_debut,ma.duree
      , case when coalesce(epd.km_paye,1) and  coalesce(rt.km_paye,1) and coalesce(ra.km_paye,1) then ma.nbkm_paye else 0 end
      ,ma.commentaire
      ,null
    from modele_activite ma
    inner join ref_activite ra on ma.activite_id=ra.id
    left outer join emp_pop_depot epd on ma.employe_id=epd.employe_id and _date_distrib between epd.date_debut and epd.date_fin
    left outer join ref_transport rt on rt.id=ma.transport_id
    where ma.jour_id=dayofweek(_date_distrib)
    and (ma.depot_id=_depot_id or _depot_id is null) 
    and (ma.flux_id=_flux_id or _flux_id is null)
    and ra.est_hors_presse=false
    and (_est_hors_presse=false OR _est_hors_presse is null)
    ;
    call int_logrowcount_C(_idtrt,5,'alim_act_insert_activite', 'Insertion des activites');
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS alim_act_insert_activite_hors_presse;
CREATE PROCEDURE alim_act_insert_activite_hors_presse(
    IN    _utilisateur_id	INT,
    IN    _idtrt		      INT,
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT,
    IN    _est_hors_presse BOOLEAN
) BEGIN
    insert into pai_activite(
      utilisateur_id,date_creation
      ,xaoid
      ,date_distrib
      ,depot_id,flux_id
      ,activite_id,employe_id,transport_id
      ,heure_debut,duree
      ,nbkm_paye
      ,date_extrait
    ) select 
      _utilisateur_id,sysdate()
      ,ech.xaoid
      ,_date_distrib
      ,ech.depot_id,ech.flux_id
      ,ech.activite_id,ech.employe_id,4 -- voiture societe
      ,case dayofweek(_date_distrib) when 1 then ech.heure_debut_dimanche when 2 then ech.heure_debut_lundi when 3 then ech.heure_debut_mardi when 4 then ech.heure_debut_mercredi when 5 then ech.heure_debut_jeudi when 6 then ech.heure_debut_vendredi when 7 then ech.heure_debut_samedi end
      ,case 
 /*     when _date_distrib<='2015-12-20' then sec_to_time(xa.nbheurejr*3600)
      when xrc.code='RC' then pai_horaire_moyen_HP(xa.oid,epd.nbheures_garanties,prm.date_debut,prm.date_fin)*/
      when pai_hp_est_mensualise(ech.xaoid,ech.date_debut,ech.date_fin) then pai_horaire_moyen_HP(ech.xaoid,ech.nbheures_mensuel,prm.date_debut,prm.date_fin)
      else sec_to_time(case dayofweek(_date_distrib) when 1 then ech.nbheures_dimanche when 2 then ech.nbheures_lundi when 3 then ech.nbheures_mardi when 4 then ech.nbheures_mercredi when 5 then ech.nbheures_jeudi when 6 then ech.nbheures_vendredi when 7 then ech.nbheures_samedi end*3600)
      end
      ,0
      ,null
    from emp_contrat_hp ech
    inner join pai_ref_mois prm on _date_distrib between prm.date_debut and prm.date_fin
    where (dayofweek(_date_distrib)=1 and ech.dimanche
    or dayofweek(_date_distrib)=2 and ech.lundi
    or dayofweek(_date_distrib)=3 and ech.mardi
    or dayofweek(_date_distrib)=4 and ech.mercredi
    or dayofweek(_date_distrib)=5 and ech.jeudi
    or dayofweek(_date_distrib)=6 and ech.vendredi
    or dayofweek(_date_distrib)=7 and ech.samedi)
    and (ech.depot_id=_depot_id or _depot_id is null) 
    and (ech.flux_id=_flux_id or _flux_id is null)
    and _date_distrib between ech.date_debut and ech.date_fin
    and (_est_hors_presse=true OR _est_hors_presse is null)
    ;
    call int_logrowcount_C(_idtrt,5,'alim_act_insert_activite_hors_presse', 'Insertion des activites hors-presse');
    call alim_act_maj_garantie(_utilisateur_id,_idtrt,_date_distrib,_depot_id,_flux_id);
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 -- Appelé après une mise à jour NG dans INT_PNG2MROAD
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS alim_act_insert_activite_hors_presse_retro;
CREATE PROCEDURE alim_act_insert_activite_hors_presse_retro(
    IN    _utilisateur_id	INT,
    IN    _idtrt		      INT,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT
) BEGIN
    insert into pai_activite(
      utilisateur_id,date_creation
      ,xaoid
      ,date_distrib
      ,depot_id,flux_id
      ,activite_id,employe_id,transport_id
      ,heure_debut,duree
      ,nbkm_paye
      ,date_extrait
    ) select 
      0,sysdate()
      ,ech.xaoid
      ,prc.datecal
      ,ech.depot_id,ech.flux_id
      ,ech.activite_id,ech.employe_id,4 -- voiture societe
      ,case dayofweek(prc.datecal) when 1 then ech.heure_debut_dimanche when 2 then ech.heure_debut_lundi when 3 then ech.heure_debut_mardi when 4 then ech.heure_debut_mercredi when 5 then ech.heure_debut_jeudi when 6 then ech.heure_debut_vendredi when 7 then ech.heure_debut_samedi end
      ,case 
      when pai_hp_est_mensualise(ech.xaoid,ech.date_debut,ech.date_fin) then pai_horaire_moyen_HP(ech.xaoid,ech.nbheures_mensuel,prm.date_debut,prm.date_fin)
      else sec_to_time(case dayofweek(prc.datecal) when 1 then ech.nbheures_dimanche when 2 then ech.nbheures_lundi when 3 then ech.nbheures_mardi when 4 then ech.nbheures_mercredi when 5 then ech.nbheures_jeudi when 6 then ech.nbheures_vendredi when 7 then ech.nbheures_samedi end*3600)
      end
      ,0
      ,null
    from pai_mois pm
    inner join pai_ref_calendrier prc on prc.datecal between pm.date_debut and sysdate()
    inner join pai_ref_mois prm on prc.datecal between prm.date_debut and prm.date_fin
    inner join emp_contrat_hp ech on prc.datecal between ech.date_debut and ech.date_fin and pm.flux_id=ech.flux_id
    where (dayofweek(prc.datecal)=1 and ech.dimanche
        or dayofweek(prc.datecal)=2 and ech.lundi
        or dayofweek(prc.datecal)=3 and ech.mardi
        or dayofweek(prc.datecal)=4 and ech.mercredi
        or dayofweek(prc.datecal)=5 and ech.jeudi
        or dayofweek(prc.datecal)=6 and ech.vendredi
        or dayofweek(prc.datecal)=7 and ech.samedi)
    and (ech.depot_id=_depot_id or _depot_id is null) 
    and (ech.flux_id=_flux_id or _flux_id is null)
    and not exists(select null from pai_activite pa2 where pa2.date_distrib=prc.datecal and pa2.depot_id=ech.depot_id and pa2.flux_id=ech.flux_id and pa2.employe_id=ech.employe_id and pa2.activite_id=ech.activite_id and pa2.xaoid=ech.xaoid)
    ;
    call int_logrowcount_C(_idtrt,5,'alim_act_insert_activite_hors_presse_retro', 'Insertion des activites hors-presse');
    call alim_act_maj_garantie(_utilisateur_id,_idtrt,null,_depot_id,_flux_id);
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 -- Appelé après une mise à jour NG dans INT_PNG2MROAD
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS alim_act_delete_activite_hors_presse;
CREATE PROCEDURE alim_act_delete_activite_hors_presse(
    IN    _utilisateur_id	INT,
    IN    _idtrt		      INT,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT
) BEGIN
  	delete pj
  	FROM pai_activite a
    inner join pai_journal pj on pj.activite_id=a.id
    INNER JOIN ref_activite ra on a.activite_id=ra.id and ra.est_pleiades
  	WHERE a.date_extrait is null
    and (a.depot_id=_depot_id or _depot_id is null) 
    and (a.flux_id=_flux_id or _flux_id is null)
    and not exists(select null 
                  from emp_contrat_hp ech
                  inner join ref_activite ra on ech.activite_id=ra.id and ra.est_pleiades
                  where a.date_distrib between ech.date_debut AND ech.date_fin
                  and a.activite_id=ra.id
                  and a.employe_id=ech.employe_id 
                  AND a.depot_id=ech.depot_id -- Gestion dans un autre dépot !!!
                  AND a.flux_id=ech.flux_id
                  AND a.xaoid=ech.xaoid
                  )
    ;
  	insert into pai_activite_hp_sav (date_suppression,id, jour_id, depot_id, activite_id, employe_id, transport_id, utilisateur_id, date_distrib, heure_debut, duree, nbkm_paye, date_creation, date_modif, date_extrait, typejour_id, flux_id, commentaire, duree_nuit, tournee_id, heure_debut_calculee, duree_garantie, ouverture, xaoid) 
    select now(),a.*
  	FROM pai_activite a
    INNER JOIN ref_activite ra on a.activite_id=ra.id and ra.est_pleiades
  	WHERE a.date_extrait is null
    and (a.depot_id=_depot_id or _depot_id is null) 
    and (a.flux_id=_flux_id or _flux_id is null)
    and not exists(select null 
                  from emp_contrat_hp ech
                  inner join ref_activite ra on ech.activite_id=ra.id and ra.est_pleiades
                  where a.date_distrib between ech.date_debut AND ech.date_fin
                  and a.activite_id=ra.id
                  and a.employe_id=ech.employe_id 
                  AND a.depot_id=ech.depot_id -- Gestion dans un autre dépot !!!
                  AND a.flux_id=ech.flux_id
                  AND a.xaoid=ech.xaoid
                  )
    ;
  	delete a
  	FROM pai_activite a
    INNER JOIN ref_activite ra on a.activite_id=ra.id and ra.est_pleiades
  	WHERE a.date_extrait is null
    and (a.depot_id=_depot_id or _depot_id is null) 
    and (a.flux_id=_flux_id or _flux_id is null)
    and not exists(select null 
                  from emp_contrat_hp ech
                  inner join ref_activite ra on ech.activite_id=ra.id and ra.est_pleiades
                  where a.date_distrib between ech.date_debut AND ech.date_fin
                  and a.activite_id=ra.id
                  and a.employe_id=ech.employe_id 
                  AND a.depot_id=ech.depot_id -- Gestion dans un autre dépot !!!
                  AND a.flux_id=ech.flux_id
                  AND a.xaoid=ech.xaoid
                  )
    ;
    call int_logrowcount_C(_idtrt,5,'alim_act_delete_activite_hors_presse', 'Suppression des activites hors-presse');
END;

/*
create table pai_activite_hp_sav as select * from pai_activite where id=0;
call alim_act_delete_activite_hors_presse(0,1,null,null);
select * from pai_int_log where idtrt=1 order by id desc
call alim_act_maj_garantie(0,@idtrt,null,null,null);
select * from pai_activite_hp_sav;

select * from pai_activite where date_extrait is null and duree<>duree_garantie and duree_garantie<>'00:00';
*/

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS alim_act_maj_garantie;
CREATE PROCEDURE alim_act_maj_garantie(
    IN    _utilisateur_id	INT,
    IN    _idtrt		      INT,
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT
) BEGIN
    update pai_activite pa
    inner join ref_activite ra on ra.est_hors_presse=true and pa.activite_id=ra.id
    inner join pai_mois pm on pa.flux_id=pm.flux_id and pa.date_distrib>=pm.date_debut
    set pa.duree_garantie='00:00'
    where pa.date_extrait is null
    and pa.duree_garantie<>'00:00'
    and (pa.date_distrib=_date_distrib or _date_distrib is null)
    and (pa.depot_id=_depot_id or _depot_id is null) 
    and (pa.flux_id=_flux_id or _flux_id is null)
    ;
    update pai_activite pa
    inner join emp_contrat_hp ech on pa.employe_id=ech.employe_id and pa.date_distrib between ech.date_debut and ech.date_fin and pa.activite_id=ech.activite_id
    inner join pai_mois pm on pa.flux_id=pm.flux_id and pa.date_distrib>=pm.date_debut
    inner join pai_ref_mois prm on pa.date_distrib between prm.date_debut and prm.date_fin
    set pa.heure_debut=case dayofweek(pa.date_distrib) when 1 then ech.heure_debut_dimanche when 2 then ech.heure_debut_lundi when 3 then ech.heure_debut_mardi when 4 then ech.heure_debut_mercredi when 5 then ech.heure_debut_jeudi when 6 then ech.heure_debut_vendredi when 7 then ech.heure_debut_samedi end
    ,   pa.duree_garantie=case
                          when  (dayofweek(pa.date_distrib)=1 and ech.dimanche
                              or dayofweek(pa.date_distrib)=2 and ech.lundi
                              or dayofweek(pa.date_distrib)=3 and ech.mardi
                              or dayofweek(pa.date_distrib)=4 and ech.mercredi
                              or dayofweek(pa.date_distrib)=5 and ech.jeudi
                              or dayofweek(pa.date_distrib)=6 and ech.vendredi
                              or dayofweek(pa.date_distrib)=7 and ech.samedi) then
                                case 
                                when pai_hp_est_mensualise(ech.xaoid,ech.date_debut,ech.date_fin) then pai_horaire_moyen_HP(ech.xaoid,ech.nbheures_mensuel,prm.date_debut,prm.date_fin)
                                else sec_to_time(case dayofweek(pa.date_distrib) when 1 then ech.nbheures_dimanche when 2 then ech.nbheures_lundi when 3 then ech.nbheures_mardi when 4 then ech.nbheures_mercredi when 5 then ech.nbheures_jeudi when 6 then ech.nbheures_vendredi when 7 then ech.nbheures_samedi end*3600)
                                end
                              else
                                '00:00'
                              end
    where pa.date_extrait is null
    and (pa.date_distrib=_date_distrib or _date_distrib is null)
    and (pa.depot_id=_depot_id or _depot_id is null) 
    and (pa.flux_id=_flux_id or _flux_id is null)
    ;
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 -- On ajoute les activité de la semaine précédente
 -- ------------------------------------------------------------------------------------------------------------------------------------------------ 
-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS ALIM_ACTIVITE_FROM_LAST_WEEK;
CREATE PROCEDURE ALIM_ACTIVITE_FROM_LAST_WEEK(
    IN 		_utilisateur_id INT,
    INOUT _idtrt		      INT,
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT,
    IN    _est_hors_presse BOOLEAN
) BEGIN
    CALL ALIM_ACTIVITE_FROM_DATE(_utilisateur_id, _idtrt, _date_distrib, _depot_id, _flux_id, date_add(_date_distrib,interval -7 day));
END;
-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS ALIM_ACTIVITE_FROM_DATE;
CREATE PROCEDURE ALIM_ACTIVITE_FROM_DATE(
    IN 		_utilisateur_id INT,
    INOUT _idtrt		      INT,
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT,
    IN 		_date_org       DATE
) BEGIN
    DECLARE CONTINUE  HANDLER FOR SQLWARNING    CALL int_logwarning(_idtrt);
    DECLARE EXIT      HANDLER FOR SQLEXCEPTION  CALL int_logerreur(_idtrt);
        
    CALL int_logdebut(_utilisateur_id,_idtrt,'ALIM_ACTIVITE_FROM_DATE',_date_distrib,_depot_id,_flux_id);
    CALL int_logger(_idtrt,'ALIM_ACTIVITE_FROM_DATE',_date_org);

    CALL alim_act_nettoyage(_utilisateur_id,_idtrt, _date_distrib, _depot_id, _flux_id, false);
    CALL alim_act_insert_activite_from_date(_utilisateur_id, _idtrt, _date_distrib, _depot_id, _flux_id, _date_org);
    CALL int_logfin2(_idtrt,'ALIM_ACTIVITE_FROM_DATE');
END;
-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS alim_act_insert_activite_from_lw;
DROP PROCEDURE IF EXISTS alim_act_insert_activite_from_date;
CREATE PROCEDURE alim_act_insert_activite_from_date(
    IN    _utilisateur_id	INT,
    IN    _idtrt		      INT,
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT,
    IN 		_date_org       DATE
) BEGIN
    insert into pai_activite(
      utilisateur_id,date_creation
      ,xaoid
      ,date_distrib
      ,depot_id,flux_id
      ,activite_id,employe_id,transport_id
      ,heure_debut,duree,duree_garantie,nbkm_paye,commentaire
      ,date_extrait
    ) select 
      _utilisateur_id,sysdate()
      ,pa.xaoid
      ,_date_distrib
      ,pa.depot_id,pa.flux_id
      ,pa.activite_id,pa.employe_id,pa.transport_id
      ,pa.heure_debut,pa.duree,pa.duree_garantie,pa.nbkm_paye,pa.commentaire
      ,null
    from pai_activite pa
    inner join ref_activite ra on pa.activite_id=ra.id
    where pa.date_distrib=_date_org
    and (pa.depot_id=_depot_id or _depot_id is null) 
    and (pa.flux_id=_flux_id or _flux_id is null)
    and ra.est_hors_presse=false and ra.est_1mai=true
    ;
    call int_logrowcount_C(_idtrt,5,'alim_act_insert_activite_from_date', 'Insertion des activites');
END;
/*
alter table ref_activite add est_1mai boolean not null
select * from ref_activite
update ref_activite set est_1mai=true where id>0 and id not in (24,13,18);
*/