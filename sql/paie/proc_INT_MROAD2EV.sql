/**
A FAIRE, NETTOYER les tables avec les interfaces de plus de 3 mois sauf les CLOTUREs !!!
select * from pai_int_traitement where date_debut<date_add(sysdate(), interval -3 month) order by date_debut desc

commit;
    set @idtrt=null;
    call INT_MROAD2EV_HEBDO(@idtrt,15,2);
    select * from pai_int_traitement order by id desc;
    select * from pai_int_log where idtrt in(select max(id) from pai_int_traitement) order by id desc;
    select * from pai_int_log where idtrt in(5820) order by id desc;
    select * from pai_tournee where date_extrait='2015-02-24 13:02:59';
    select * from pai_activite where date_extrait='2015-02-24 13:02:59';
    select * from pai_stc where date_extrait='2015-02-24 13:02:59';
    select * from pai_tournee where employe_id=6766 and date_extrait is null
  
    set @idtrt=null;
    call INT_MROAD2EV_STC(@idtrt,15,1);

kill 7191930

    set @idtrt=null;
    call INT_MROAD2EV_QUOTIDIEN(@idtrt,15,1);
    set @idtrt=null;
    call INT_MROAD2EV_QUOTIDIEN(@idtrt,15,2);
    set @idtrt=null;
    call INT_MROAD2EV_MENSUEL(@idtrt,15,2,true);
    commit;
    select * from pai_int_traitement order by id desc;
    select * from pai_int_traitement where typetrt like '%PLEIADES_CLOTURE%' order by id desc;
    select * from pai_int_traitement order by id desc;
    select * from pai_int_log where idtrt=7481 order by id desc;
    call int_mroad2ev_calcul_nb_supplement(7481);
    select default_character_set_name,i.* from information_schema.SCHEMATA i
    set @idtrt=null;
    call INT_MROAD2EV_HISTORIQUE(@idtrt,9795);
    select @idtrt;
call int_mroad2ev_history_diff(10552,10570);
call int_mroad2ev_history_diff(10561,10563);
select * from pai_ev_hst where idtrt=7423
select * from pai_int_ev_diff_history where idtrt1=7636 and idtrt2=7670 order by ordre


select * from employe where matricule='7000624120'
select * from pai_tournee where employe_id=11035 order by date_distrib desc
select * from emp_contrat where employe_id=11035

select ped.*,null as difference from pai_ev_diff ped where idtrt1=10561 and idtrt2=10563  and diff<>'='
select ped.*,null as difference from pai_ev_diff ped where idtrt1=10552 and idtrt2=10570  and diff<>'='
union
select idtrt1,idtrt2,null,typev,matricule,rc,poste,datev,998,concat(libelle,' TOTAL'),sum(qte1),sum(qte2),null,null,sum(val1),sum(val2),sum(val2)-sum(val1) as difference
from pai_ev_diff where idtrt1=9795 and idtrt2=10090  and diff<>'=' 
and poste not in ('0560','HJPX','HTPX')
group by idtrt1,idtrt2,typev,matricule,rc,poste,datev,libelle 
union
select idtrt1,idtrt2,null,'TOTAL',matricule,rc,null,datev,999,'TOTAL',null,null,null,null,sum(val1) as avant,sum(val2) as apres,sum(val2)-sum(val1) as difference 
from pai_ev_diff where idtrt1=9795 and idtrt2=10090  and diff<>'=' 
and poste not in ('0560','HJPX','HTPX')
group by idtrt1,idtrt2,matricule,rc,datev
order by 5,8,10,9

select * from pai_ev where poste in ('HC1','HC2','HS1','HS2')
select * from v_employe where matricule='7000046200'
select * from pai_ev_diff where idtrt1=7629 and idtrt2=7673  and diff<>'=' order by matricule -- STC nuit
select * from pai_ev_diff where idtrt1=7376 and idtrt2=7680  and diff<>'=' order by matricule -- cloture sept jour
select * from pai_ev_emp_pop_depot_hst where matricule='Z004011820' and idtrt in (7376,7680)
select * from pai_ev_emp_pop_hst where matricule='Z004011820' and idtrt in (7376,7680)
select * from pai_ev_emp_pop_depot_hst where idtrt=7635 and depot_id=22

-- chgt de depot
select * from pai_ev_emp_pop_depot_hst p1
left outer join pai_ev_emp_pop_hst p2 on p1.idtrt=p2.idtrt and p1.employe_id=p2.employe_id and p1.d=p2.d
where p1.idtrt=7635 
and p2.employe_id is null
-- pb dans getAnnexe
select * from pai_ev_emp_depot_hst ped
inner join pai_ev_emp_pop_depot_hst q on ped.idtrt=q.idtrt and ped.employe_id=q.employe_id
where ped.idtrt=7680
group by ped.idtrt,ped.employe_id
having count(distinct q.d)<>count(distinct ped.d)

select * from employe where matricule='7000030420'
select * from pai_hchs where employe_id=7462
select * from pai_stc where employe_id=7462
select * from pai_ev where typev like 'MAJO HCHS'
select * from pai_ev where matricule ='7000022420'
select * from pai_hchs where employe_id=8896
select * from v_employe where matricule='Z004449720'



select * from pai_ev_emp_depot_hst where matricule='7000477320' and idtrt in (7636,7670);
select * from pai_ev_emp_pop_depot_hst where matricule='7000477320' and idtrt in (7636,7670);
select * from pai_ev_emp_pop_hst where matricule='7000477320' and idtrt in (7636,7670);
select * from pai_ev_tournee_hst where employe_id=8961 and idtrt in (7636,7670);
select * from pai_ev_emp_pop_depot where matricule='7000477320'
select * from pai_ev where matricule='7000477320'
select * from pai_ev_emp_depot

select * FROM pai_ev_diff d inner join employe e on d.matricule=e.matricule WHERE  idtrt1=5535 and idtrt2=5542 and d.matricule in ('7000260020','7000480420','Z005051920')
ORDER BY d.matricule,d.datev,d.poste,d.ordre;
select * FROM pai_ev_diff d inner join employe e on d.matricule=e.matricule WHERE  d.diff<>'=' and idtrt1=5535 and idtrt2=5543
ORDER BY d.matricule,d.datev,d.poste,d.ordre;
select * FROM pai_ev_diff d inner join employe e on d.matricule=e.matricule WHERE  d.diff<>'=' and idtrt1=5533 and idtrt2=5544
ORDER BY d.matricule,d.datev,d.poste,d.ordre;


select * FROM pai_ev_diff d inner join employe e on d.matricule=e.matricule WHERE  d.matricule='Z004976200' and idtrt1=5264 and idtrt2=5289
ORDER BY e.nom,d.matricule,d.datev,d.poste,d.ordre;

5847	25/04/2016 17:27:01	25/04/2016 17:34:40	123	GENERE_PLEIADES_CLOTURE		1		T	201604
5533	23/03/2016 16:43:32	23/03/2016 16:50:19	123	GENERE_PLEIADES_CLOTURE		1		T	201603
select * from employe where nom='FERCHICHI'

1095	FERCHICHI	5fe02be2-925c-11e3-80d7-95fa70670ad2	7000298500	Ali		21/08/2014 00:00:00	
select * from pai_ev_hst where matricule='7000298500' and idtrt in (5533,5847) and typev='PRIME'
  */

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_setmois;
CREATE PROCEDURE int_mroad2ev_setmois(
    IN 		_anneemois		  varchar(6),
    IN 		_flux_id		    INT)
BEGIN
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_erreur;
CREATE PROCEDURE int_mroad2ev_erreur(
    IN 	  _idtrt		INT
) BEGIN
  CALL int_mroad2ev_reinit(_idtrt);
  CALL int_logerreur(_idtrt);
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS INT_MROAD2EV_STC;
CREATE PROCEDURE INT_MROAD2EV_STC(
    INOUT _idtrt		      INT,
    IN 		_utilisateur_id	INT,
    IN 		_flux_id		    INT
) BEGIN
DECLARE _date_debut DATE;
DECLARE _date_fin DATE;
DECLARE _anneemois  VARCHAR(6);
  DECLARE CONTINUE  HANDLER FOR SQLWARNING    CALL int_logwarning(_idtrt);
  DECLARE EXIT      HANDLER FOR SQLEXCEPTION  CALL int_mroad2ev_erreur(_idtrt);
  SELECT anneemois,date_debut,date_fin INTO _anneemois,_date_debut,_date_fin FROM pai_mois WHERE flux_id=_flux_id;
  CALL int_logdebutanneemois(_utilisateur_id,_idtrt,'MROAD2PNG_EV_STC',null,null, _flux_id, _anneemois);

  call recalcul_hg(_anneemois,null,_flux_id);
  call int_mroad2ev_recalcul_hchs(_anneemois,_flux_id);
  call int_mroad2ev_exec(_idtrt,null,TRUE,_date_debut,_date_fin, null, _flux_id,true);
  CALL int_logger(_idtrt,'INT_MROAD2EV_STC','int_mroad2ev_diff');
  call int_mroad2ev_diff(_idtrt);
  CALL int_logger(_idtrt,'INT_MROAD2EV_STC','int_mroad2ev_extrait');
  call int_mroad2ev_extrait(_idtrt,_date_debut,_date_fin, _flux_id);
  CALL int_logger(_idtrt,'INT_MROAD2EV_STC','int_mroad2ev_annexe');
  call int_mroad2ev_annexe(_idtrt);
  CALL int_logfin2(_idtrt,'MROAD2PNG_EV_STC');
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS INT_MROAD2EV_QUOTIDIEN;
CREATE PROCEDURE INT_MROAD2EV_QUOTIDIEN(
    INOUT _idtrt		      INT,
    IN 		_utilisateur_id	INT,
    IN 		_flux_id		    INT
) BEGIN
DECLARE _date_debut DATE;
DECLARE _date_fin   DATE;
DECLARE _anneemois  VARCHAR(6);
  DECLARE CONTINUE  HANDLER FOR SQLWARNING    CALL int_logwarning(_idtrt);
  DECLARE EXIT      HANDLER FOR SQLEXCEPTION  CALL int_mroad2ev_erreur(_idtrt);
  SELECT anneemois,date_debut,date_fin INTO _anneemois,_date_debut,_date_fin FROM pai_mois WHERE flux_id=_flux_id;
  CALL int_logdebutanneemois(_utilisateur_id,_idtrt,'MROAD2PNG_EV_QUOTIDIEN',null, null, _flux_id, _anneemois);

  call recalcul_hg(_anneemois,null,_flux_id);
  call int_mroad2ev_recalcul_hchs(_anneemois,_flux_id);
  call int_mroad2ev_exec(_idtrt,null,FALSE,_date_debut,_date_fin, null, _flux_id,false);
  CALL int_logger(_idtrt,'MROAD2PNG_EV_QUOTIDIEN','int_mroad2ev_annexe');
  call int_mroad2ev_annexe(_idtrt);
  CALL int_logfin2(_idtrt,'MROAD2PNG_EV_QUOTIDIEN');
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS INT_MROAD2EV_MENSUEL;
CREATE PROCEDURE INT_MROAD2EV_MENSUEL(
    INOUT _idtrt		      INT,
    IN 		_utilisateur_id	INT,
    IN 		_flux_id		    INT,
    IN    _bloquant     BOOLEAN
) BEGIN
DECLARE _date_debut DATE;
DECLARE _date_fin   DATE;
DECLARE _anneemois  VARCHAR(6);
  DECLARE CONTINUE  HANDLER FOR SQLWARNING    CALL int_logwarning(_idtrt);
  DECLARE EXIT      HANDLER FOR SQLEXCEPTION  CALL int_mroad2ev_erreur(_idtrt);
  SELECT anneemois,date_debut,date_fin INTO _anneemois,_date_debut,_date_fin FROM pai_mois WHERE flux_id=_flux_id;
  CALL int_logdebutanneemois(_utilisateur_id,_idtrt,'MROAD2PNG_EV_MENSUEL',null, null, _flux_id, _anneemois);

  call recalcul_hg(_anneemois,null,_flux_id);
  call int_mroad2ev_recalcul_hchs(_anneemois,_flux_id);
  call int_mroad2ev_exec(_idtrt,null,FALSE,_date_debut,_date_fin, null, _flux_id,true);
  CALL int_logger(_idtrt,'INT_MROAD2EV_MENSUEL','int_mroad2ev_diff');
  call int_mroad2ev_diff(_idtrt);
  CALL int_logger(_idtrt,'INT_MROAD2EV_MENSUEL','int_mroad2ev_annexe');
  call int_mroad2ev_annexe(_idtrt);
  CALL int_logfin2(_idtrt,'MROAD2PNG_EV_MENSUEL');
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS INT_MROAD2EV_CLOTURE;
CREATE PROCEDURE INT_MROAD2EV_CLOTURE(
    INOUT _idtrt		      INT,
    IN 		_utilisateur_id	INT,
    IN 		_flux_id		    INT
) BEGIN
DECLARE _date_debut DATE;
DECLARE _date_fin   DATE;
DECLARE _anneemois  VARCHAR(6);
  DECLARE CONTINUE  HANDLER FOR SQLWARNING    CALL int_logwarning(_idtrt);
  DECLARE EXIT      HANDLER FOR SQLEXCEPTION  CALL int_mroad2ev_erreur(_idtrt);
  SELECT anneemois,date_debut,date_fin INTO _anneemois,_date_debut,_date_fin FROM pai_mois WHERE flux_id=_flux_id;
  CALL int_logdebutanneemois(_utilisateur_id,_idtrt,'INT_MROAD2EV_CLOTURE',null, null, _flux_id, _anneemois);
  
  call recalcul_hg(_anneemois,null,_flux_id);
  call int_mroad2ev_recalcul_hchs(_anneemois,_flux_id);
  call int_mroad2ev_exec(_idtrt,null,FALSE,_date_debut,_date_fin, null, _flux_id,true);
  CALL int_logger(_idtrt,'INT_MROAD2EV_CLOTURE','int_mroad2ev_diff');
  call int_mroad2ev_diff(_idtrt);
  CALL int_logger(_idtrt,'INT_MROAD2EV_CLOTURE','int_mroad2ev_extrait');
  call int_mroad2ev_extrait(_idtrt,_date_debut,_date_fin, _flux_id);
  CALL int_logger(_idtrt,'INT_MROAD2EV_CLOTURE','int_mroad2ev_annexe');
  call int_mroad2ev_annexe(_idtrt);

  CALL int_logger(_idtrt,'INT_MROAD2EV_CLOTURE','Nettoyage des interfaces à M-1');
  call int_mroad2ev_nettoyage_historique(_flux_id);

  CALL int_logger(_idtrt,'INT_MROAD2EV_CLOTURE','Ajout des STC du mois en cours');
  INSERT INTO pai_stc(rcoid,employe_id,date_stc,anneemois,utilisateur_id,date_modif,date_extrait)
    SELECT DISTINCT
      epd.rcoid,
      epd.employe_id,
      epd.fRC,
      pit.anneemois,
      pit.utilisateur_id,
      now(),
      pit.date_debut
  FROM pai_int_traitement pit
  INNER JOIN pai_ev_emp_pop_hst pep on pit.id=pep.idtrt
  INNER JOIN emp_pop_depot epd on pep.employe_id=epd.employe_id and pep.rc=epd.rc
  INNER JOIN pai_mois pm on pm.flux_id=pit.flux_id 
  WHERE pit.id=_idtrt
  AND epd.fRC between pm.date_debut and pm.date_fin
  AND NOT exists(SELECT NULL
                  FROM pai_stc ps
                  WHERE ps.rcoid=epd.rcoid
                 )
  ;               
  CALL int_logger(_idtrt,'INT_MROAD2EV_CLOTURE','Changement de periode de paie');
  UPDATE pai_ref_mois prm
  INNER JOIN pai_int_traitement pit ON pit.id=_idtrt
  SET prm.date_extrait=pit.date_debut
  WHERE prm.anneemois=_anneemois
  ;
  DELETE FROM pai_mois WHERE flux_id=_flux_id;
  INSERT INTO pai_mois(flux_id,anneemois,anneemois_reclamation,annee,mois,libelle,date_debut,date_fin,date_debut_string,date_fin_string)
  SELECT _flux_id,anneemois,anneemois,annee,mois,libelle,date_debut,date_fin,date_format(date_debut,'%Y%m%d'),date_format(date_fin,'%Y%m%d') 
  FROM pai_ref_mois 
  WHERE anneemois IN (SELECT MIN(anneemois) FROM pai_ref_mois WHERE anneemois>_anneemois);
  
  CALL int_logfin2(_idtrt,'INT_MROAD2EV_CLOTURE');
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS INT_MROAD2EV_HISTORIQUE;
CREATE PROCEDURE INT_MROAD2EV_HISTORIQUE(
    INOUT 	_idtrt		INT,
    IN 		  _idtrt_org	INT
) BEGIN
DECLARE _date_debut DATE;
DECLARE _date_fin   DATE;
DECLARE _anneemois  VARCHAR(6);
DECLARE _depot_id INT;
DECLARE _flux_id INT;
DECLARE _typetrt  VARCHAR(128);
  DECLARE CONTINUE  HANDLER FOR SQLWARNING    CALL int_logwarning(_idtrt);
  DECLARE EXIT      HANDLER FOR SQLEXCEPTION  CALL int_mroad2ev_erreur(_idtrt);
  SELECT typetrt,anneemois,depot_id,flux_id INTO _typetrt,_anneemois,_depot_id,_flux_id FROM pai_int_traitement WHERE id=_idtrt_org;
  SELECT date_debut,date_fin INTO _date_debut,_date_fin FROM pai_ref_mois WHERE anneemois=_anneemois;
  CALL int_logdebutanneemois(0,_idtrt,'MROAD2PNG_EV_HISTORIQUE',null, _depot_id, _flux_id, _anneemois);

  call int_mroad2ev_exec(_idtrt,_idtrt_org, (_typetrt='GENERE_PLEIADES_STC'),_date_debut,_date_fin, _depot_id, _flux_id,true);

  call int_mroad2ev_history_diff(_idtrt_org, _idtrt);
  
  CALL int_logfin2(_idtrt,'INT_MROAD2EV_HISTORIQUE');
END;

-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_exec;
CREATE PROCEDURE int_mroad2ev_exec(
    IN 		_idtrt		  INT,
    IN 		_idtrt_org	INT,
    IN 		_isStc      BOOLEAN,
    IN 		_date_debut DATE,
    IN 		_date_fin 	DATE,
    IN 		_depot_id		  INT,
    IN 		_flux_id		  INT,
    IN    _bloquant     BOOLEAN
) BEGIN
DECLARE	_date_1M DATE;
DECLARE	_batchactif BOOLEAN;
DECLARE	_validation_id INT;

  CALL int_logger(_idtrt,'ev_exec',CONCAT('Calcul de la paie pour la période du ',_date_debut,' au ',_date_fin));

  select max(id) into _batchactif from pai_int_traitement where statut='C' and (typetrt in ('PNG2MROAD','ALIM_EMPLOYE') OR typetrt like 'GENERE_PLEIADES_%') and id<>_idtrt;
  if _batchactif is not null then
      call int_loglevel(_idtrt,0,'int_mroad2ev_exec','Une procédure de paie est déjà en cours d''éxécution.');
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Une procédure de paie est déjà en cours d''éxécution.';
  end if;

  -- Permet de recalculer les produit, les tournées et de les revalider.
  -- Revalidation déja effectuée lors de la maj administrative NG
  CALL int_logger(_idtrt,'ev_exec','Recalcul et revalidation des produits et des tournées (par sécurité)');
  call recalcul_produit_date_distrib(null, _depot_id, _flux_id);
  CALL int_logger(_idtrt,'ev_exec','Revalidation des activités (par sécurité)');
  call pai_valide_activite(_validation_id, _depot_id, _flux_id,null,null);

  CALL int_mroad2ev_verif_stc(_idtrt,_isStc,_date_debut,_date_fin, _depot_id, _flux_id);

  -- Traitement du 1er mai
  IF SUBSTR(_date_debut,5,6)<='-05-01' AND '-05-01'<=SUBSTR(_date_fin,5,6) THEN
    SET _date_1M=CONCAT(SUBSTR(_date_debut,1,4),'-05-01');
    CALL int_mroad2ev_select_contrat(_idtrt,_idtrt_org,_isStc,TRUE,_date_1M,_date_1M, _depot_id, _flux_id);
    CALL int_mroad2ev_select(_idtrt,_idtrt_org,TRUE);
 --   CALL int_mroad2ev_maj_tournee(_idtrt,_date_1M,_date_1M); -- 08/02/2017, ne sert a rien, réalisé par recalcul majoration
    CALL int_mroad2ev_log_warning(_idtrt,_date_1M,_date_1M);
    CALL int_mroad2ev_log_erreur(_idtrt,_date_1M,_date_1M,_bloquant);
    CALL int_mroad2ev_calcul(_idtrt,_isStc,_date_1M,_date_1M,TRUE);
    call int_mroad2ev_historise_employe(_idtrt);
    call int_mroad2ev_historise_ev(_idtrt);
  END IF;

  CALL int_mroad2ev_select_contrat(_idtrt,_idtrt_org,_isStc,FALSE,_date_debut,_date_fin, _depot_id, _flux_id);
  -- passe la date au 05-02 pour éviter les multi-occurences le 05-01
  update pai_ev_emp_pop set d=CONCAT(SUBSTR(_date_debut,1,4),'-05-02') where d like '%-05-01';
  update pai_ev_emp_pop_depot set d=CONCAT(SUBSTR(_date_debut,1,4),'-05-02') where d like '%-05-01';
  update pai_ev_emp_depot set d=CONCAT(SUBSTR(_date_debut,1,4),'-05-02') where d like '%-05-01';
  
  CALL INT_MROAD2HEURESGARANTIES(0,_idtrt);

  CALL int_mroad2ev_select(_idtrt,_idtrt_org,FALSE);
--  CALL int_mroad2ev_maj_tournee(_idtrt,_date_debut,_date_fin); -- 08/02/2017, ne sert a rien, réalisé par recalcul majoration
  CALL int_mroad2ev_log_warning(_idtrt,_date_debut,_date_fin);
  CALL int_mroad2ev_log_erreur(_idtrt,_date_debut,_date_fin,_bloquant);
  
  if (_idtrt_org is null) then
    call int_mroad2ev_maj_casl(_idtrt,_date_debut,_date_fin);
  end if;
-- ATTENTION si on est à M+1 et un individu a 2 STC !!!!
/*
  CALL INT_MROAD2HEURESGARANTIES(0,_idtrt);
  
  if (_idtrt_org is null) then
    delete a from pai_activite a
    where a.date_extrait is null 
    and (a.depot_id=_depot_id or _depot_id is null)
    and (a.flux_id=_flux_id or _flux_id is null)
    and a.activite_id=-10
    and (not _isStc or exists(SELECT null FROM pai_stc  s WHERE s.employe_id=a.employe_id and s.date_extrait IS NULL))
    ;
    call int_logrowcount_C(_idtrt,4,'int_mroad2ev_exec','Suppression complément heures garanties');

    INSERT INTO pai_activite (depot_id, activite_id, employe_id, transport_id, utilisateur_id, date_distrib, heure_debut, duree, nbkm_paye, date_extrait, flux_id, commentaire, duree_nuit, tournee_id, heure_debut_calculee) 
    SELECT epd.depot_id, -10, epd.employe_id, 1, pit.utilisateur_id, pih.date_distrib, '00:00:00', sec_to_time(abs(time_to_sec(pih.hgaranties))), 0, null, epd.flux_id, null, '00:00:00', null, null
    from pai_int_oct_hgaranties pih
    inner join emp_pop_depot epd on pih.employe_id=epd.employe_id and pih.date_distrib between epd.date_debut and epd.date_fin
    inner join pai_int_traitement pit on pit.id=_idtrt
    where pih.hgaranties>0
    and pih.idtrt=pit.id
    ;
    call int_logrowcount_C(_idtrt,4,'int_mroad2ev_exec','Ajout complément heures garanties')
    ;
    INSERT INTO pai_ev_activite(id,date_distrib,employe_id,depot_id,flux_id,jour_id,typejour_id,activite_id,nbkm_paye,transport_id,duree,duree_nuit) 
    SELECT a.id,a.date_distrib,a.employe_id,a.depot_id,a.flux_id,a.jour_id,a.typejour_id,a.activite_id,0,a.transport_id,a.duree,a.duree_nuit 
    FROM pai_activite a 
    INNER JOIN pai_ev_emp_pop_depot e ON e.employe_id=a.employe_id and a.date_distrib between e.d and e.f
    WHERE (a.date_extrait is null)
  --  AND e.typetournee_id in (1,2) -- on exclu les encadrants
    AND a.activite_id=-10
    ;
    call int_logrowcount_C(_idtrt,4,'int_mroad2ev_exec','Extraction heures garanties');
  else
    -- Faux pour le CALCUL_HISTORIQUE
    INSERT INTO pai_ev_activite(id,date_distrib,employe_id,depot_id,flux_id,jour_id,typejour_id,activite_id,nbkm_paye,transport_id,duree,duree_nuit) 
    SELECT a.id,a.date_distrib,a.employe_id,a.depot_id,a.flux_id,a.jour_id,a.typejour_id,a.activite_id,0,a.transport_id,a.duree,a.duree_nuit 
    FROM pai_activite a 
    INNER JOIN pai_ev_emp_pop_depot e ON e.employe_id=a.employe_id and a.date_distrib between e.d and e.f
    INNER JOIN pai_int_traitement pit on pit.id=_idtrt_org
    WHERE a.date_extrait=pit.date_debut
  --  AND e.typetournee_id in (1,2) -- on exclu les encadrants
    AND a.activite_id=-10
    ;
    call int_logrowcount_C(_idtrt,4,'int_mroad2ev_exec','Extraction heures garanties');
  end if;
*/


/*  SELECT a.id,a.date_distrib,a.employe_id,a.depot_id,a.flux_id,a.jour_id,a.typejour_id,a.activite_id,0,a.transport_id,a.duree,a.duree_nuit 
  FROM pai_activite a 
  WHERE a.date_extrait is null
  AND a.activite_id=-10
  and (a.depot_id=_depot_id or _depot_id is null)
  and (a.flux_id=_flux_id or _flux_id is null)
  and (not _isStc or exists(SELECT null FROM pai_stc WHERE s.employe_id=a.employe_id and date_extrait IS NULL))
  ;
*/
  CALL int_mroad2ev_calcul(_idtrt,_isStc,_date_debut,_date_fin,FALSE);
  CALL int_logger(_idtrt,'int_mroad2ev_exec','Historisation de ev');
  call int_mroad2ev_historise_employe(_idtrt);
  call int_mroad2ev_historise_ev(_idtrt);
END;

