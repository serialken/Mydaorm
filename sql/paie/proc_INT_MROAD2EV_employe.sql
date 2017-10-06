﻿-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_select_contrat;
CREATE PROCEDURE int_mroad2ev_select_contrat(
    IN 		_idtrt		  INT,
    IN 		_idtrt_org	INT,
    IN 		_isStc      BOOLEAN,
    IN 		_is1M       BOOLEAN,
    IN 		_date_debut DATE,
    IN 		_date_fin 	DATE,
    IN 		_depot_id		INT,
    IN 		_flux_id		INT
) BEGIN
DECLARE _date_execution datetime;
  SELECT date_debut INTO _date_execution FROM pai_int_traitement WHERE id=coalesce(_idtrt_org,_idtrt);

  call int_png2mroad_create_temporary_table(_idtrt);
  
  -- ------------------------------------------------------------------------------------------------------------------------------------------------
  -- pai_ev_emp_pop_depot
  -- ------------------------------------------------------------------------------------------------------------------------------------------------
	INSERT INTO pai_int_png_rcoid(rcoid,date_debut)
	SELECT DISTINCT rc.oid,rc.relatdatedeb FROM pai_png_relationcontrat rc
	UNION	SELECT DISTINCT rc.oid,po.begin_date FROM pai_png_relationcontrat rc,pai_png_rcpopulationw po WHERE po.relationcontrat=rc.oid
	UNION	SELECT DISTINCT rc.oid,et.begin_date FROM pai_png_relationcontrat rc,pai_png_etablissrel et WHERE et.etabrelation=rc.oid
	UNION	SELECT DISTINCT rc.oid,s.begin_date FROM pai_png_relationcontrat rc,pai_png_contrat c,pai_png_suspension s WHERE rc.oid=c.ctrrelation AND c.oid=s.suspcontrat
	UNION	SELECT DISTINCT rc.oid,s.end_date+INTERVAL 1 DAY FROM pai_png_relationcontrat rc,pai_png_contrat c,pai_png_suspension s WHERE rc.oid=c.ctrrelation AND c.oid=s.suspcontrat
  UNION SELECT DISTINCT rc.oid,ao.begin_date FROM pai_png_relationcontrat rc,pai_png_affoctimew ao      WHERE rc.oid=ao.relationcontrat
	;
  DELETE i FROM pai_int_png_rcoid i WHERE (_isStc AND i.rcoid NOT IN (SELECT epd.rcoid FROM pai_stc s INNER JOIN emp_pop_depot epd on s.employe_id=epd.employe_id and s.date_stc=epd.fRC WHERE s.date_extrait IS NULL or _idtrt_org is not null and s.date_extrait=_date_execution));
  -- Supprime les STC déjà réalisé
  DELETE i FROM pai_int_png_rcoid i WHERE (i.rcoid IN (SELECT epd.rcoid FROM pai_stc s INNER JOIN emp_pop_depot epd on s.employe_id=epd.employe_id and s.date_stc=epd.fRC WHERE s.date_extrait IS NOT NULL and s.date_extrait<_date_execution));

	CALL int_png2mroad_maj_tmp_salarie(_idtrt);
  -- Supprime les individus non créés dans MRoad finissant en 01 (RC simultanées = interdit)
  DELETE i FROM pai_int_png_info i WHERE i.employe_id is null;
	CALL int_png2mroad_maj_tmp_depot_paie(_idtrt);
  DELETE i FROM pai_int_png_info i WHERE i.depot_id<>_depot_id;
	CALL int_png2mroad_maj_tmp_flux(_idtrt);
  DELETE i FROM pai_int_png_info i WHERE i.flux_id<>_flux_id;
	CALL int_png2mroad_maj_tmp_emploi(_idtrt);
  -- Supprime les individus non interfacable en paie
  DELETE i FROM pai_int_png_info i LEFT OUTER JOIN ref_emploi re ON i.emploi_id=re.id WHERE coalesce(re.paie,0)=0;
  update pai_int_png_info i set i.emploi_id=NULL; -- pour le regroupement sur POR/POL en cas de passage CDD/CDI
	CALL int_png2mroad_maj_tmp_population_paie(_idtrt);
	CALL int_png2mroad_maj_tmp_societe(_idtrt);
  update pai_int_png_info set heure_debut='00:00';
  update pai_int_png_info set nbheures_garanties=0;
	CALL int_png2mroad_regroupement(_idtrt);

  DELETE FROM pai_ev_emp_pop_depot;
	INSERT INTO pai_ev_emp_pop_depot(employe_id,depot_id,flux_id,population_id,typetournee_id,societe_id,emploi_code,dRC,fRC,dCtr,fCtr,d,f,rc,matricule)
	-- changement en cours de periode
  -- ATTENTION : ici il faut récupérer typeurssaf_id dans Pleiades NG
	SELECT DISTINCT i.employe_id,i.depot_id,i.flux_id,i.population_id,rp.typetournee_id,i.societe_id,i.emploi_code,i.dRC,i.fRC,i.date_debut,i.date_fin,GREATEST(i.date_debut,prm.date_debut),LEAST(i.date_fin,prm.date_fin),i.relatnum,i.matricule
	FROM pai_int_png_info_regroupement i
  INNER JOIN ref_population rp ON i.population_id=rp.id
  INNER JOIN pai_ref_mois prm on i.date_debut<=prm.date_fin and i.date_fin>=prm.date_debut
	WHERE prm.date_fin>=_date_debut and i.date_fin>=_date_debut 
  and (prm.date_debut<=_date_fin and i.date_debut<=_date_fin -- cas normal
  or not _is1M and prm.date_debut<=_date_execution and i.fRC<_date_execution AND i.rcoid IN (SELECT epd.rcoid FROM pai_stc s INNER JOIN emp_pop_depot epd on s.employe_id=epd.employe_id and s.date_stc=epd.fRC WHERE s.date_extrait IS NULL or _idtrt_org is not null and s.date_extrait=_date_execution)) -- stc
  ;
  call int_logrowcount_C(_idtrt,5,'int_mroad2ev_select_contrat','Table pai_ev_emp_pop_depot');

  -- ------------------------------------------------------------------------------------------------------------------------------------------------
  -- pai_ev_emp_depot
  -- ------------------------------------------------------------------------------------------------------------------------------------------------
	UPDATE pai_int_png_info SET population_id=NULL;
	UPDATE pai_int_png_info SET emploi_id=NULL;
	UPDATE pai_int_png_info SET emploi_code=NULL;
  update pai_int_png_info set heure_debut='00:00';
  update pai_int_png_info set nbheures_garanties=0;
	CALL int_png2mroad_regroupement(_idtrt);

  DELETE FROM pai_ev_emp_depot;
  INSERT INTO pai_ev_emp_depot(employe_id,depot_id,flux_id,dRC,fRC,dCtr,fCtr,d,f,rc,matricule)
	SELECT DISTINCT i.employe_id,i.depot_id,i.flux_id,i.dRC,i.fRC,i.date_debut,i.date_fin,GREATEST(i.date_debut,prm.date_debut),LEAST(i.date_fin,prm.date_fin),i.relatnum,i.matricule
	FROM pai_int_png_info_regroupement i
  INNER JOIN pai_ref_mois prm on i.date_debut<=prm.date_fin and i.date_fin>=prm.date_debut
	WHERE prm.date_fin>=_date_debut and i.date_fin>=_date_debut 
  and (prm.date_debut<=_date_fin and i.date_debut<=_date_fin -- cas normal
  or not _is1M and prm.date_debut<=_date_execution and i.fRC<_date_execution AND i.rcoid IN (SELECT epd.rcoid FROM pai_stc s INNER JOIN emp_pop_depot epd on s.employe_id=epd.employe_id and s.date_stc=epd.fRC WHERE s.date_extrait IS NULL or _idtrt_org is not null and s.date_extrait=_date_execution)) -- stc
  ;
  call int_logrowcount_C(_idtrt,5,'int_mroad2ev_select_contrat','Table pai_ev_emp_depot');

  -- ------------------------------------------------------------------------------------------------------------------------------------------------
  -- pai_ev_emp_pop
  -- ------------------------------------------------------------------------------------------------------------------------------------------------
  call int_png2mroad_nettoyage(_idtrt);
	INSERT INTO pai_int_png_rcoid(rcoid,date_debut)
	SELECT DISTINCT rc.oid,rc.relatdatedeb
  FROM pai_png_relationcontrat rc
	UNION	SELECT DISTINCT rc.oid,po.begin_date FROM pai_png_relationcontrat rc,pai_png_rcpopulationw po WHERE po.relationcontrat=rc.oid
-- 	UNION	SELECT DISTINCT rc.oid,et.begin_date FROM pai_png_relationcontrat rc,pai_png_etablissrel et WHERE et.etabrelation=rc.oid
	UNION	SELECT DISTINCT rc.oid,s.begin_date FROM pai_png_relationcontrat rc,pai_png_contrat c,pai_png_suspension s WHERE rc.oid=c.ctrrelation AND c.oid=s.suspcontrat
	UNION	SELECT DISTINCT rc.oid,s.end_date+INTERVAL 1 DAY FROM pai_png_relationcontrat rc,pai_png_contrat c,pai_png_suspension s WHERE rc.oid=c.ctrrelation AND c.oid=s.suspcontrat
  UNION SELECT DISTINCT rc.oid,ao.begin_date FROM pai_png_relationcontrat rc,pai_png_affoctimew ao      WHERE rc.oid=ao.relationcontrat
	;
  DELETE i FROM pai_int_png_rcoid i WHERE (_isStc AND i.rcoid NOT IN (SELECT epd.rcoid FROM pai_stc s INNER JOIN emp_pop_depot epd on s.employe_id=epd.employe_id and s.date_stc=epd.fRC WHERE s.date_extrait IS NULL or _idtrt_org is not null and s.date_extrait=_date_execution));
  -- Supprime les STC déjà réalisé
  DELETE i FROM pai_int_png_rcoid i WHERE (i.rcoid IN (SELECT epd.rcoid FROM pai_stc s INNER JOIN emp_pop_depot epd on s.employe_id=epd.employe_id and s.date_stc=epd.fRC WHERE s.date_extrait IS NOT NULL and s.date_extrait<_date_execution));

  CALL int_png2mroad_maj_tmp_salarie(_idtrt);
  -- Supprime les individus non créés dans MRoad finissant en 01 (RC simultanées = interdit)
  DELETE i FROM pai_int_png_info i WHERE i.employe_id is null;
  IF (_depot_id is not null) THEN
  	CALL int_png2mroad_maj_tmp_depot_paie(_idtrt);
    DELETE i FROM pai_int_png_info i WHERE i.depot_id<>_depot_id;
    UPDATE pai_int_png_info set depot_id=null;
  END IF;
  IF (_flux_id is not null) THEN
  	CALL int_png2mroad_maj_tmp_flux(_idtrt);
    DELETE i FROM pai_int_png_info i WHERE i.flux_id<>_flux_id;
    UPDATE pai_int_png_info set flux_id=null;
  END IF;
	CALL int_png2mroad_maj_tmp_emploi(_idtrt);
  -- Supprime les individus non interfacable en paie
  -- pai_ev_emp_pop utilisé seulement pour le calcul de la prime
  DELETE i FROM pai_int_png_info i LEFT OUTER JOIN ref_emploi re ON i.emploi_id=re.id WHERE coalesce(re.paie,0)=0 and coalesce(re.prime,0)=0;
  update pai_int_png_info i set i.emploi_id=NULL; -- pour le regroupement sur POR/POL en cas de passage CDD/CDI
  
  CALL int_png2mroad_maj_tmp_societe(_idtrt);
--	CALL int_png2mroad_maj_tmp_population_paie(_idtrt);
  update pai_int_png_info set heure_debut='00:00';
  update pai_int_png_info set nbheures_garanties=0;
  CALL int_png2mroad_regroupement(_idtrt);

  DELETE FROM pai_ev_emp_pop;
  INSERT INTO pai_ev_emp_pop(employe_id,emploi_code,societe_id,dRC,fRC,dCtr,fCtr,d,f,rc,matricule)
	SELECT DISTINCT i.employe_id,i.emploi_code,i.societe_id,i.dRC,i.fRC,i.date_debut,i.date_fin,GREATEST(i.date_debut,prm.date_debut),LEAST(i.date_fin,prm.date_fin),i.relatnum,i.matricule
	FROM pai_int_png_info_regroupement i
  INNER JOIN pai_ref_mois prm on i.date_debut<=prm.date_fin and i.date_fin>=prm.date_debut
	WHERE prm.date_fin>=_date_debut and i.date_fin>=_date_debut 
  and (prm.date_debut<=_date_fin and i.date_debut<=_date_fin  -- cas normal
  or not _is1M and prm.date_debut<=_date_execution and i.fRC<_date_execution AND i.rcoid IN (SELECT epd.rcoid FROM pai_stc s INNER JOIN emp_pop_depot epd on s.employe_id=epd.employe_id and s.date_stc=epd.fRC WHERE s.date_extrait IS NULL or _idtrt_org is not null and s.date_extrait=_date_execution)) -- stc
  ;
  call int_logrowcount_C(_idtrt,5,'int_mroad2ev_select_contrat','Table pai_ev_emp_pop');
  
  -- ------------------------------------------------------------------------------------------------------------------------------------------------
  if (_is1M) then
    update pai_ev_emp_pop_depot set d=_date_debut,f=_date_fin;
    update pai_ev_emp_depot set d=_date_debut,f=_date_fin;
    update pai_ev_emp_pop set d=_date_debut,f=_date_fin;
  end if;
  -- ------------------------------------------------------------------------------------------------------------------------------------------------
  call int_png2mroad_drop_temporary_table(_idtrt);
END;