/*
--truncate table pai_journal;
call pai_valide_activite(@id,null,null,null,null);
call pai_valide_tournee(@id,null,null,null,null);
call pai_valide_produit(@id,null,null,null,null,null);
*/
/*
select * from depot
set @validation_id:=1;
call recalcul_horaire(@validation_id,null,null,null,null);


select * from pai_recalcul_horaire where employe_id=10464 order by date_distrib
    select * from pai_int_log where idtrt=1 order by id desc limit 1000;

*/

-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_horaire_tournee;
CREATE PROCEDURE recalcul_horaire_tournee(
    INOUT _validation_id  INT,
    IN 		_tournee_id     INT
) begin
declare _employe_id   int;
declare _date_distrib date;
  select date_distrib, employe_id into _date_distrib, _employe_id from pai_tournee where id=_tournee_id;
  
  call recalcul_horaire(_validation_id, null, null, _date_distrib, _employe_id);
end;

-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_horaire_contrathp;
CREATE PROCEDURE recalcul_horaire_contrathp(
    INOUT _validation_id  INT,
    IN 		_date_distrib   DATE,
    IN 		_xaoid          varchar(36)
) begin
declare _employe_id   int;
  select distinct employe_id into _employe_id from emp_contrat_hp where xaoid=_xaoid;
  
 call recalcul_horaire(_validation_id, null, null, _date_distrib, _employe_id);
end;

 
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
-- recalcul les tournées d'un modele à partir d'une date de début
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_horaire_modele;
CREATE PROCEDURE `recalcul_horaire_modele`(INOUT _validation_id INT, IN _date_debut DATE, IN _modele_tournee_jour_id INT)
BEGIN
  DECLARE v_finished INTEGER DEFAULT 0;
  DECLARE _date_distrib DATE;
  DECLARE _employe_id INT;
 
  -- declare cursor for employee email
  DEClARE _cursor CURSOR FOR
  SELECT pt.employe_id,pt.date_distrib
  FROM pai_tournee pt
  WHERE pt.modele_tournee_jour_id=_modele_tournee_jour_id
  AND (pt.date_distrib>=_date_debut or _date_debut is null)
  AND pt.date_extrait is null;
   
  -- declare NOT FOUND handler
  DECLARE CONTINUE HANDLER
  FOR NOT FOUND SET v_finished = 1;

  IF (_validation_id IS NULL) THEN
		INSERT INTO pai_validation(utilisateur_id) VALUES(1);
		SELECT LAST_INSERT_ID() INTO _validation_id;
  END IF;
  call recalcul_logger('recalcul_horaire_modele',concat_ws('*',_validation_id,_date_debut,_modele_tournee_jour_id));
     
  OPEN _cursor;
  _loop: LOOP
    FETCH _cursor INTO _employe_id,_date_distrib;
    IF v_finished = 1 THEN
      LEAVE _loop;
    END IF;
    call recalcul_horaire(_validation_id, null, null, _date_distrib, _employe_id);
  END LOOP _loop;
  CLOSE _cursor;
END;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_horaire_etalon;
CREATE PROCEDURE `recalcul_horaire_etalon`(INOUT _validation_id INT, IN _etalon_id INT)
BEGIN
  DECLARE v_finished INTEGER DEFAULT 0;
  DECLARE _date_distrib DATE;
  DECLARE _employe_id INT;
 
  -- declare cursor for employee email
  DEClARE _cursor CURSOR FOR
  SELECT pt.employe_id,pt.date_distrib
  FROM pai_tournee pt
  inner join etalon_transferer ett on ett.new_modele_tournee_jour_id=pt.modele_tournee_jour_id and pt.date_distrib>=ett.date_application
  where ett.etalon_id=_etalon_id
  and pt.date_extrait is null
  ;
   
  -- declare NOT FOUND handler
  DECLARE CONTINUE HANDLER
  FOR NOT FOUND SET v_finished = 1;

  IF (_validation_id IS NULL) THEN
		INSERT INTO pai_validation(utilisateur_id) VALUES(1);
		SELECT LAST_INSERT_ID() INTO _validation_id;
  END IF;
  call recalcul_logger('recalcul_horaire_etalon',concat_ws('*',_validation_id,_etalon_id));
     
  OPEN _cursor;
  _loop: LOOP
    FETCH _cursor INTO _employe_id,_date_distrib;
    IF v_finished = 1 THEN
      LEAVE _loop;
    END IF;
    call recalcul_horaire(_validation_id, null, null, _date_distrib, _employe_id);
  END LOOP _loop;
  CLOSE _cursor;
END;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_horaire_remplacement;
CREATE PROCEDURE `recalcul_horaire_remplacement`(INOUT _validation_id INT, IN _remplacement_id INT)
BEGIN
  DECLARE v_finished INTEGER DEFAULT 0;
  DECLARE _date_distrib DATE;
  DECLARE _employe_id INT;
 
  -- declare cursor for employee email
  DEClARE _cursor CURSOR FOR
  SELECT pt.employe_id,pt.date_distrib
  FROM pai_tournee pt
  inner join modele_tournee_jour mtj on pt.modele_tournee_jour_id=mtj.id
  left outer join modele_remplacement_jour mrj on mrj.modele_tournee_id=mtj.tournee_id and mrj.jour_id=mtj.jour_id
  left outer join modele_remplacement mr on mrj.remplacement_id=mr.id and pt.date_distrib between mr.date_debut and mr.date_fin
  where pt.date_extrait is null
  AND mr.id=_remplacement_id;
   
  -- declare NOT FOUND handler
  DECLARE CONTINUE HANDLER
  FOR NOT FOUND SET v_finished = 1;

  IF (_validation_id IS NULL) THEN
		INSERT INTO pai_validation(utilisateur_id) VALUES(1);
		SELECT LAST_INSERT_ID() INTO _validation_id;
  END IF;
  call recalcul_logger('recalcul_horaire_remplacement',concat_ws('*',_validation_id,_remplacement_id));
     
  OPEN _cursor;
  _loop: LOOP
    FETCH _cursor INTO _employe_id,_date_distrib;
    IF v_finished = 1 THEN
      LEAVE _loop;
    END IF;
    call recalcul_horaire(_validation_id, null, null, _date_distrib, _employe_id);
  END LOOP _loop;
  CLOSE _cursor;
END;

-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_horaire;
CREATE PROCEDURE recalcul_horaire(
    INOUT _validation_id  INT,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT,
    IN 		_date_distrib   DATE,
    IN 		_employe_id     INT
) begin
DECLARE CONTINUE  HANDLER FOR SQLWARNING    CALL int_logwarning(1);
DECLARE EXIT      HANDLER FOR SQLEXCEPTION  CALL int_logerreur(1);

  call recalcul_logger('recalcul_horaire', concat_ws('*',_validation_id,_depot_id,_flux_id,_date_distrib,_employe_id));

  call pai_valide_heure_delete(_validation_id, _depot_id, _flux_id, _date_distrib, _employe_id);
  -- On recalcul l'ordre des tournees
  call recalcul_ordre(_date_distrib, _depot_id, _flux_id, _employe_id);
  -- on recalcul la majoration avant car elle a une influence sur la durée
  call recalcul_majoration(_date_distrib, _depot_id, _flux_id, _employe_id);

  -- call pai_valide_heure_avant(_validation_id, _depot_id, _flux_id, _date_distrib, _employe_id);
  call recalcul_horaire_select(_depot_id, _flux_id, _date_distrib, _employe_id);
  call recalcul_horaire_calcul();
  call recalcul_horaire_update();

  call pai_valide_heure(_validation_id, _depot_id, _flux_id, _date_distrib, _employe_id);
  call recalcul_logger('recalcul_horaire','fin');
--  DROP TEMPORARY TABLE pai_recalcul_horaire;
end;

-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_horaire_select;
CREATE PROCEDURE recalcul_horaire_select(
    IN 		_depot_id       INT,
    IN 		_flux_id        INT,
    IN 		_date_distrib   DATE,
    IN 		_employe_id     INT
) begin
/*  DROP TEMPORARY TABLE IF EXISTS pai_recalcul_horaire;
  CREATE TEMPORARY TABLE pai_recalcul_horaire(*/
  DROP TEMPORARY TABLE IF EXISTS pai_recalcul_horaire;
  CREATE TEMPORARY TABLE pai_recalcul_horaire(
  date_distrib  date
  ,employe_id   int
  ,tournee_id   int
  ,activite_id  int
  ,valide       boolean
  ,ordre        varchar(32)
  ,heure_debut  time
  ,duree        time
  ) engine=memory
  DEFAULT CHARSET=utf8
  COLLATE=utf8_unicode_ci;
  call recalcul_logger('recalcul_horaire_select','create pai_recalcul_horaire');
  
  insert into pai_recalcul_horaire
    SELECT 
      pt.date_distrib
      ,e.id                                     as employe_id
      ,pt.id                                    as tournee_id
      ,null                                     as activite_id
      ,coalesce(min(pe.valide),true)            as valide
      ,cast(concat(pt.ordre,'-9') as char(32))  as ordre
--      ,if(ph.duree_attente='00:00' and pt.duree_retard='00:00' and pt.ordre=1,pt.heure_debut,null) -- On met l'heure de la tournee seulement si c'est la première et si il n'y a ni retard,ni attente
      ,if(pt.duree_attente='00:00' and pt.duree_retard='00:00' and pt.ordre=1,coalesce(pt.heure_debut,coalesce(ph.heure_debut,ph.heure_debut_theo)),null) -- On met l'heure de la tournee seulement si c'est la première et si il n'y a ni retard,ni attente
      ,pt.duree
    FROM pai_tournee pt
    INNER JOIN pai_heure ph on ph.id=pt.heure_id
    INNER JOIN employe e ON pt.employe_id=e.id
    left outer join pai_journal pj on pt.id=pj.tournee_id
    left outer join pai_ref_erreur pe on pj.erreur_id=pe.id
    WHERE pt.date_extrait is null
    AND (pt.tournee_org_id is null or split_id is not null)
--    AND pt.duree<>'00:00:00'
    and (pt.date_distrib=_date_distrib or _date_distrib is null)
    and (pt.depot_id=_depot_id or _depot_id is null)
    and (pt.flux_id=_flux_id or _flux_id is null)
    and (pt.employe_id=_employe_id or _employe_id is null)
    group by e.id,pt.id
    UNION ALL
    SELECT 
      pa.date_distrib
      ,e.id                                     as employe_id
      ,pt.id                                    as tournee_id
      ,pa.id                                    as activite_id
      ,coalesce(if(pt.id is not null,min(pet.valide),min(pea.valide)),true)            as valide
      ,cast(if(pa.heure_debut is not null,1,if(pt.id is not null,concat(pt.ordre,pa.activite_id),pa.id)) as char(32)) as ordre
      ,coalesce(pa.heure_debut,case
                                when pt.ordre>1 then null
                                when pa.activite_id=-1 then coalesce(pt.heure_debut,coalesce(ph.heure_debut,ph.heure_debut_theo)) -- retard
                                when pa.activite_id=-2 and pt.duree_retard='00:00' then coalesce(pt.heure_debut,coalesce(ph.heure_debut,ph.heure_debut_theo)) -- attente
--                                when pa.activite_id=-3 and pt.duree_retard='00:00' and ph.duree_attente='00:00' then pt.heure_debut
                                else null
                                end
                                )   as heure_debut
      ,pa.duree
    FROM pai_activite pa
    INNER JOIN employe e ON pa.employe_id=e.id
    left outer join pai_tournee pt on pa.tournee_id=pt.id
    left outer join pai_heure ph on ph.id=pt.heure_id
    left outer join pai_journal pja on pa.id=pja.activite_id
    left outer join pai_ref_erreur pea on pja.erreur_id=pea.id
    left outer join pai_journal pjt on pt.id=pjt.tournee_id
    left outer join pai_ref_erreur pet on pjt.erreur_id=pet.id
--    AND pa.duree<>'00:00:00'
    WHERE pa.date_extrait is null
    and (pa.date_distrib=_date_distrib or _date_distrib is null)
    and (pa.depot_id=_depot_id or _depot_id is null)
    and (pa.flux_id=_flux_id or _flux_id is null)
    and (pa.employe_id=_employe_id or _employe_id is null)
    and  (pt.id is null or pt.tournee_org_id is null or pt.split_id is not null)
    and pa.activite_id not in (-10,-11)
    group by e.id,pt.id,pa.id
    ;
  call recalcul_logger('recalcul_horaire_select','insert pai_recalcul_horaire');
  -- On reinitialise les tournee splittées 
    update pai_tournee pt
    set pt.heure_debut_calculee=null
    ,   pt.duree_nuit='00:00'
    WHERE pt.date_extrait is null
    AND (pt.tournee_org_id is not null and split_id is null)
    and (pt.date_distrib=_date_distrib or _date_distrib is null)
    and (pt.depot_id=_depot_id or _depot_id is null)
    and (pt.flux_id=_flux_id or _flux_id is null)
    and (pt.employe_id=_employe_id or _employe_id is null)
    and (pt.heure_debut_calculee is not null or pt.duree_nuit<>'00:00')
  ;
end;

-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_horaire_calcul;
CREATE PROCEDURE recalcul_horaire_calcul()
begin
  set @date_distrib = '2000-01-01';
  set @employe_id = 0;
  set @heure_cumulee = null;

/*  DROP TEMPORARY TABLE IF EXISTS pai_recalcul_horaire2;
  CREATE TEMPORARY TABLE pai_recalcul_horaire2(*/
  DROP TEMPORARY TABLE IF EXISTS pai_recalcul_horaire2;
  CREATE TEMPORARY TABLE pai_recalcul_horaire2(
  date_distrib  date
  ,employe_id   int
  ,tournee_id   int
  ,activite_id  int
  ,valide       boolean
  ,ordre        varchar(32)
  ,heure_debut  time
  ,duree        time
  ,heure_debut_calculee  time
  ,heure_cumulee  time
  ) engine=memory
  DEFAULT CHARSET=utf8
  COLLATE=utf8_unicode_ci;
  call recalcul_logger('recalcul_horaire_calcul','create pai_recalcul_horaire2');

  insert into pai_recalcul_horaire2(date_distrib,employe_id,tournee_id ,activite_id,valide,ordre,heure_debut,duree,heure_debut_calculee,heure_cumulee)
  select ph.date_distrib,ph.employe_id,ph.tournee_id ,ph.activite_id,ph.valide,ph.ordre,ph.heure_debut,ph.duree,ph.heure_debut_calculee,ph.heure_cumulee from
--  insert into pai_recalcul_horaire2(tournee_id ,activite_id,heure_debut_calculee)
--  select ph.tournee_id ,ph.activite_id,ph.heure_debut_calculee from
    ( select h.tournee_id ,h.activite_id,h.valide,h.ordre,h.heure_debut,h.duree
        ,@heure_cumulee:=if(@date_distrib<>h.date_distrib or @employe_id<>h.employe_id,null,@heure_cumulee) as heure_cumulee_avant
        ,case         when not h.valide               then null
                      when h.heure_debut is not null  then h.heure_debut
                      else                                 coalesce(@heure_cumulee,h.heure_debut)
        end                               as heure_debut_calculee

        ,@heure_cumulee:=case
                      when not h.valide               then @heure_cumulee
                      when h.heure_debut is not null  then addtime(h.heure_debut,h.duree)
                      else                                 addtime(coalesce(@heure_cumulee,h.heure_debut),h.duree)
        end                               as heure_cumulee
        , @date_distrib:=h.date_distrib   as date_distrib
        , @employe_id:=h.employe_id       as employe_id
      from pai_recalcul_horaire h
      order by date_distrib,employe_id,ordre
    ) as ph
  ;
  call recalcul_logger('recalcul_horaire_calcul','insert pai_recalcul_horaire2');
end;

-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_horaire_update;
CREATE PROCEDURE recalcul_horaire_update()
BEGIN
  update pai_tournee pt
  inner join pai_recalcul_horaire2 ph on pt.id=ph.tournee_id and ph.activite_id is null
  set pt.heure_debut_calculee=ph.heure_debut_calculee
  ,   pt.duree_nuit=if(pt.flux_id=1,pai_heure_nuit(ph.heure_debut_calculee,pt.duree),'00:00')
  ;
  call recalcul_logger('recalcul_horaire_update','update tournee');
  update pai_activite pa
  inner join pai_recalcul_horaire2 ph on pa.id=ph.activite_id
  left outer join pai_ref_postepaie_activite prpa on pa.activite_id=prpa.activite_id and pa.typejour_id=prpa.typejour_id
  set pa.heure_debut_calculee=ph.heure_debut_calculee
  ,   pa.duree_nuit=if(pa.flux_id=1 && prpa.poste_hn<>'----',pai_heure_nuit(ph.heure_debut_calculee,pa.duree),'00:00')
  ;
  call recalcul_logger('recalcul_horaire_update','update activite');
end;  
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS pai_valide_heure_delete;
CREATE PROCEDURE `pai_valide_heure_delete`(INOUT _validation_id INT, IN _depot_id INT, IN _flux_id INT, IN _date_distrib DATE, IN _employe_id INT)

BEGIN
	DELETE pj
  FROM pai_journal pj
  INNER JOIN pai_ref_erreur pe on pj.erreur_id=pe.id
	WHERE pj.date_extrait is null
  AND pe.rubrique='HO'
	AND (pj.depot_id=_depot_id OR _depot_id IS NULL)
	AND (pj.flux_id=_flux_id OR _flux_id IS NULL)
	AND (pj.date_distrib=_date_distrib OR _date_distrib IS NULL)
  AND (pj.employe_id=_employe_id OR _employe_id IS NULL)
/*	AND (pj.tournee_id in (select distinct t.id
                      	FROM pai_tournee t
                      	INNER JOIN pai_ref_mois m ON t.date_distrib between m.date_debut and  m.date_fin
                      	WHERE t.date_extrait is null
--                        AND (t.tournee_org_id is null or split_id is not null)
                        AND (t.date_distrib=_date_distrib OR _date_distrib IS NULL)
                        AND (t.depot_id=_depot_id OR _depot_id IS NULL)
                        AND (t.flux_id=_flux_id OR _flux_id IS NULL)
                        AND (t.employe_id=_employe_id OR _employe_id IS NULL)
                        )
  OR pj.activite_id in (SELECT distinct a.id
                        FROM pai_activite a
                      	INNER JOIN pai_ref_mois m ON a.date_distrib between m.date_debut and  m.date_fin
                      	WHERE a.date_extrait is null
                        AND (a.date_distrib=_date_distrib OR _date_distrib IS NULL)
                        AND (a.depot_id=_depot_id OR _depot_id IS NULL)
                        AND (a.flux_id=_flux_id OR _flux_id IS NULL)
                        AND (a.employe_id=_employe_id OR _employe_id IS NULL)
                        )
  )*/
	;
  call pai_valide_logger('pai_valide_heure_delete', 'pai_valide_heure_delete');
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS pai_valide_heure;
CREATE PROCEDURE `pai_valide_heure`(INOUT _validation_id INT, IN _depot_id INT, IN _flux_id INT, IN _date_distrib DATE, IN _employe_id INT)

BEGIN
	IF (_validation_id IS NULL) THEN
		INSERT INTO modele_validation(utilisateur_id) VALUES(1);
		SELECT LAST_INSERT_ID() INTO _validation_id;
    call pai_valide_logger('PAI_VALIDE_HEURE',concat_ws('*',_validation_id,_depot_id,_flux_id,_date_distrib,_employe_id));
	END IF;
  
  -- Heure de début non renseignée
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id)
	SELECT DISTINCT 21,_validation_id,t.depot_id,t.flux_id,m.anneemois,t.date_distrib,t.employe_id,t.id ,NULL
	FROM pai_tournee t
	INNER JOIN pai_ref_mois m ON t.date_distrib between m.date_debut and  m.date_fin
	WHERE t.date_extrait is null
  AND NOT exists(SELECT NULL FROM pai_journal pj INNER JOIN pai_ref_erreur pe on pj.erreur_id=pe.id WHERE t.id=pj.tournee_id AND NOT pe.valide)
  AND (t.tournee_org_id is null or split_id is not null)
  AND (t.date_distrib=_date_distrib OR _date_distrib IS NULL)
  AND (t.depot_id=_depot_id OR _depot_id IS NULL)
  AND (t.flux_id=_flux_id OR _flux_id IS NULL)
  AND (t.employe_id=_employe_id OR _employe_id IS NULL)
	AND t.heure_debut_calculee IS NULL
  group by t.id,m.anneemois
	;
  call pai_valide_logger('pai_valide_heure', 'Heure de debut non renseignee Tournee');

  -- Heure de début non renseignée
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id)
	SELECT DISTINCT 21,_validation_id,a.depot_id,a.flux_id,m.anneemois,a.date_distrib,a.employe_id ,a.tournee_id,a.id
	FROM pai_activite a
	INNER JOIN pai_ref_mois m ON a.date_distrib between m.date_debut and  m.date_fin
  left outer join pai_journal pj on a.id=pj.activite_id
  left outer join pai_ref_erreur pe on pj.erreur_id=pe.id
	WHERE a.date_extrait is null
  AND NOT exists(SELECT NULL FROM pai_journal pj INNER JOIN pai_ref_erreur pe on pj.erreur_id=pe.id WHERE a.id=pj.activite_id AND NOT pe.valide)
  AND (a.date_distrib=_date_distrib OR _date_distrib IS NULL)
  AND (a.depot_id=_depot_id OR _depot_id IS NULL)
  AND (a.flux_id=_flux_id OR _flux_id IS NULL)
  AND (a.employe_id=_employe_id OR _employe_id IS NULL)
	AND a.heure_debut_calculee IS NULL
  AND a.activite_id>=-1
  and (pe.valide is null or pe.valide)
  group by a.id,m.anneemois
	;
  call pai_valide_logger('pai_valide_heure', 'Heure de debut non renseignee Activite');
  
  -- Chevauchement tournée/activité
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id)
	SELECT DISTINCT 18,_validation_id,a.depot_id,a.flux_id,m.anneemois,a.date_distrib,a.employe_id ,t.id,a.id
	FROM pai_activite a
	INNER JOIN pai_tournee t ON t.employe_id=a.employe_id AND t.date_distrib=a.date_distrib
	INNER JOIN pai_ref_mois m ON a.date_distrib between m.date_debut and  m.date_fin
	WHERE (a.date_extrait is null or t.date_extrait is null)
  AND (t.tournee_org_id is null or t.split_id is not null)
  AND (a.date_distrib=_date_distrib OR _date_distrib IS NULL)
  AND (a.depot_id=_depot_id OR _depot_id IS NULL)
  AND (a.flux_id=_flux_id OR _flux_id IS NULL)
  AND (a.employe_id=_employe_id OR _employe_id IS NULL)
  AND a.duree<>'00:00' AND t.duree<>'00:00'
	AND (t.heure_debut_calculee<=a.heure_debut_calculee AND a.heure_debut_calculee<addtime(t.heure_debut_calculee,t.duree)
	OR   a.heure_debut_calculee<=t.heure_debut_calculee AND t.heure_debut_calculee<addtime(a.heure_debut_calculee,a.duree))
  AND a.activite_id not in (-10,-11) -- heures garanties
	;
  call pai_valide_logger('pai_valide_heure', 'Chevauchement activite/tournee');

  call create_tmp_pai_horaire(_depot_id,_flux_id,_employe_id,null,_date_distrib);
  -- Plus de 10h par jour
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id,commentaire)
	SELECT DISTINCT 26,_validation_id,psh.depot_id,psh.flux_id,psh.anneemois,psh.date_distrib,psh.employe_id ,NULL,NULL,sec_to_time(sum(time_to_sec(psh.duree)))
  from tmp_pai_horaire psh
  WHERE psh.date_extrait is null
  group by psh.depot_id,psh.flux_id,psh.anneemois,psh.date_distrib,psh.employe_id
  having sum(time_to_sec(psh.duree))>10*3600
  ;
  call pai_valide_logger('pai_valide_heure', 'L''employe a travaille plus de 10h dans la meme journee');

  -- Plus de 6h de nuit
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id,commentaire)
	SELECT DISTINCT 27,_validation_id,psh.depot_id,psh.flux_id,psh.anneemois,psh.date_distrib,psh.employe_id ,NULL,NULL,sec_to_time(sum(time_to_sec(psh.duree_nuit)))
  from tmp_pai_horaire psh
  WHERE psh.date_extrait is null
  group by psh.depot_id,psh.flux_id,psh.anneemois,psh.date_distrib,psh.employe_id 
  having sum(time_to_sec(psh.duree_nuit))>6*3600
  ;
  call pai_valide_logger('pai_valide_heure', 'L''employe a travaille plus de 6h de nuit dans la meme journee');

  -- Plus de 300km
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id,commentaire)
	SELECT DISTINCT 28,_validation_id,psh.depot_id,psh.flux_id,psh.anneemois,psh.date_distrib,psh.employe_id ,NULL,NULL,sum(nbkm_paye)
  from tmp_pai_horaire psh
  WHERE  psh.date_extrait is null
  group by psh.depot_id,psh.flux_id,psh.anneemois,psh.date_distrib,psh.employe_id
  having sum(nbkm_paye)>300
  ;
  call pai_valide_logger('pai_valide_heure', 'L''employe est payé plus de 300 km dans la meme journee');
  
  call pai_valide_heure_tournee(_validation_id, _depot_id, _flux_id, _date_distrib, _employe_id);
  call pai_valide_heure_activite(_validation_id, _depot_id, _flux_id, _date_distrib, _employe_id);
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS pai_valide_heure_tournee;
CREATE PROCEDURE `pai_valide_heure_tournee`(INOUT _validation_id INT, IN _depot_id INT, IN _flux_id INT, IN _date_distrib DATE, IN _employe_id INT)

BEGIN
  -- Chevauchement tournée/tournée
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id)
	SELECT DISTINCT 19,_validation_id,t1.depot_id,t1.flux_id,m.anneemois,t1.date_distrib,t1.employe_id ,t1.id,null
	FROM pai_tournee t1
	INNER JOIN pai_tournee t2 ON t1.employe_id=t2.employe_id AND t1.date_distrib=t2.date_distrib
	INNER JOIN pai_ref_mois m ON t1.date_distrib between m.date_debut and  m.date_fin
--	WHERE (t1.date_extrait is null or t2.date_extrait is null)
	WHERE t1.date_extrait is null -- permet d'accelerer en utilisant un index
  AND (t1.tournee_org_id is null or t1.split_id is not null)
  AND (t2.tournee_org_id is null or t2.split_id is not null)
  AND t1.id<>t2.id
  AND (t1.depot_id=_depot_id OR _depot_id IS NULL)
  AND (t1.flux_id=_flux_id OR _flux_id IS NULL)
  AND (t1.date_distrib=_date_distrib OR _date_distrib IS NULL)
  AND (t1.employe_id=_employe_id OR _employe_id IS NULL)
  AND t1.ordre<t2.ordre
	AND addtime(t1.heure_debut_calculee,t1.duree)>t2.heure_debut_calculee
	;
  call pai_valide_logger('pai_valide_heure_tournee', 'Chevauchement tournee/tournee');
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS pai_valide_heure_activite;
CREATE PROCEDURE `pai_valide_heure_activite`(INOUT _validation_id INT, IN _depot_id INT, IN _flux_id INT, IN _date_distrib DATE, IN _employe_id INT)

BEGIN
	-- Chevauchement activité/activité
  -- Si le chevauchement ne concerne pas l'activité, on le met sur validation_id=0 pour ne pas polluer l'affichage
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id)
	SELECT DISTINCT 20,_validation_id,a1.depot_id,a1.flux_id,m.anneemois,a1.date_distrib,a1.employe_id,null,a1.id
	FROM pai_activite a1
	INNER JOIN pai_activite a2 ON a1.employe_id=a2.employe_id AND a1.date_distrib=a2.date_distrib
  LEFT OUTER JOIN pai_tournee t1 ON a1.tournee_id=t1.id
  LEFT OUTER JOIN pai_tournee t2 ON a2.tournee_id=t2.id
	INNER JOIN pai_ref_mois m ON a1.date_distrib between m.date_debut and  m.date_fin
--	WHERE (a1.date_extrait is null or a2.date_extrait is null)
	WHERE a1.date_extrait is null -- permet d'accelerer en utilisant un index
  AND a1.id<>a2.id
  AND (t1.tournee_org_id is null or t1.split_id is not null)
  AND (t2.tournee_org_id is null or t2.split_id is not null)
  AND (a1.depot_id=_depot_id OR _depot_id IS NULL)
  AND (a1.flux_id=_flux_id OR _flux_id IS NULL)
  AND (a1.date_distrib=_date_distrib OR _date_distrib IS NULL)
  AND (a1.employe_id=_employe_id OR _employe_id IS NULL)
  AND a1.duree<>'00:00' AND a2.duree<>'00:00'
	AND (a1.heure_debut_calculee<=a2.heure_debut_calculee AND a2.heure_debut_calculee<addtime(a1.heure_debut_calculee,a1.duree)
	OR   a2.heure_debut_calculee<=a1.heure_debut_calculee AND a1.heure_debut_calculee<addtime(a2.heure_debut_calculee,a2.duree))
  AND a1.activite_id not in (-1,-10,-11) -- heures garanties
  AND a2.activite_id not in (-1,-10,-11) -- heures garanties
	;
  call pai_valide_logger('pai_valide_heure_activite', 'Chevauchement activite/activite');
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
