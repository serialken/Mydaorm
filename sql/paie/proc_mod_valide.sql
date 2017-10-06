/*
set @id:=null;
CALL mod_valide_cycle(@id,NULL,NULL);
CALL mod_valide_activite(@id,NULL,NULL,NULL);
CALL mod_valide_tournee(@id,NULL,NULL,NULL);
CALL mod_valide_tournee_jour(@id,NULL,NULL,NULL,NULL);
CALL mod_valide_remplacement(@id,NULL,NULL,NULL);
CALL mod_valide_remplacement(@id,NULL,NULL,NULL);
-- CALL mod_valide_rh(@id,null,null);
 CALL mod_valide_rh_tournee(@id,null,null,null);
 CALL mod_valide_rh_activite(@id,null,null,null);

delete from pai_int_log where idtrt=1;
select * from pai_int_log where idtrt=1 order by id desc limit 100;
select * from modele_journal where depot_id in (22)
select * from v_employe where employe_id=10358

insert into modele_ref_erreur(rubrique,code,level,msg,valide) values('RR','RHC',0,'Remplacement hors contrat',0);
insert into modele_ref_erreur(rubrique,code,level,msg,valide) values('RR','RHY',5,'Remplacement hors cycle',0);
insert into modele_ref_erreur(rubrique,code,level,msg,valide) values('RE','INC',1,'Remplacement incomplet',0);
insert into modele_ref_erreur(rubrique,code,level,msg,valide) values('RR','CYC',5,'Cycle remplaçant différent de cycle remplacé',1);
insert into modele_ref_erreur(rubrique,code,level,msg,valide) values('RR','PRM',0,'Plusieurs remplaçants pour la même tournée',0);
insert into modele_ref_erreur(rubrique,code,level,msg,valide) values('RJ','CDD',0,'Tournée réalisée par CDD',0);
update modele_ref_erreur set msg='Tournée réalisée par CDD' where id=27
select * from modele_ref_erreur
select * from modele_journal where erreur_id=27
*/
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS mod_delete;
CREATE PROCEDURE `mod_delete`(IN _rubrique VARCHAR(2), IN _depot_id INT, IN _flux_id INT)
BEGIN
  call mod_valide_log('mod_delete','');
	DELETE mj FROM modele_journal mj
  INNER JOIN modele_ref_erreur me on mj.erreur_id=me.id
  WHERE me.rubrique=_rubrique
	AND (mj.depot_id=_depot_id OR _depot_id IS NULL)
	AND (mj.flux_id=_flux_id OR _flux_id IS NULL)
  ;
  call mod_valide_log('mod_delete','mod_delete');
END;

DROP PROCEDURE IF EXISTS mod_delete_tournee;
CREATE PROCEDURE `mod_delete_tournee`(IN _rubrique VARCHAR(2), IN _depot_id INT, IN _flux_id INT, IN _tournee_id INT)
BEGIN
  call mod_valide_log('mod_delete_tournee','');
	DELETE mj FROM modele_journal mj
  INNER JOIN modele_ref_erreur me on mj.erreur_id=me.id
  WHERE me.rubrique=_rubrique
	AND (mj.depot_id=_depot_id OR _depot_id IS NULL)
	AND (mj.flux_id=_flux_id OR _flux_id IS NULL)
  AND (tournee_id=_tournee_id OR _tournee_id IS NULL AND  tournee_id IS NOT NULL)
  ;
  call mod_valide_log('mod_delete_tournee','mod_delete_tournee');
END;

DROP PROCEDURE IF EXISTS mod_delete_tournee_jour;
CREATE PROCEDURE `mod_delete_tournee_jour`(IN _rubrique VARCHAR(2), IN _depot_id INT, IN _flux_id INT, IN _tournee_id INT, IN _tournee_jour_id INT)
BEGIN
	DELETE mj FROM modele_journal mj
  INNER JOIN modele_ref_erreur me on mj.erreur_id=me.id
  WHERE me.rubrique=_rubrique
	AND (mj.depot_id=_depot_id OR _depot_id IS NULL)
	AND (mj.flux_id=_flux_id OR _flux_id IS NULL)
  AND (tournee_id=_tournee_id OR _tournee_id IS NULL AND  tournee_id IS NOT NULL)
  AND (tournee_jour_id=_tournee_jour_id OR _tournee_jour_id IS NULL AND  tournee_jour_id IS NOT NULL)
  ;
  call mod_valide_log('mod_delete_tournee_jour','mod_delete_tournee_jour');
END;

DROP PROCEDURE IF EXISTS mod_delete_activite;
CREATE PROCEDURE `mod_delete_activite`(IN _rubrique VARCHAR(2), IN _depot_id INT, IN _flux_id INT, IN _activite_id INT)
BEGIN
	DELETE mj FROM modele_journal mj
  INNER JOIN modele_ref_erreur me on mj.erreur_id=me.id
  WHERE me.rubrique=_rubrique
	AND (mj.depot_id=_depot_id OR _depot_id IS NULL)
	AND (mj.flux_id=_flux_id OR _flux_id IS NULL)
  AND (activite_id=_activite_id OR _activite_id IS NULL AND  activite_id IS NOT NULL)
  ;
  call mod_valide_log('mod_delete_activite','mod_delete_activite');
END;

DROP PROCEDURE IF EXISTS mod_delete_remplacement;
CREATE PROCEDURE `mod_delete_remplacement`(IN _rubrique VARCHAR(2), IN _depot_id INT, IN _flux_id INT, IN _remplacement_id INT)
BEGIN
	DELETE mj FROM modele_journal mj
  INNER JOIN modele_ref_erreur me on mj.erreur_id=me.id
  WHERE me.rubrique=_rubrique
	AND (mj.depot_id=_depot_id OR _depot_id IS NULL)
	AND (mj.flux_id=_flux_id OR _flux_id IS NULL)
  AND (remplacement_id=_remplacement_id OR _remplacement_id IS NULL AND  remplacement_id IS NOT NULL)
  ;
  call mod_valide_log('mod_delete_remplacement','mod_delete_remplacement');
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
-- RH
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS mod_valide_rh_tournee;
CREATE PROCEDURE `mod_valide_rh_tournee`(INOUT _validation_id INT, IN _depot_id INT, IN _flux_id INT, IN _tournee_id INT)
BEGIN
  IF (_validation_id IS NULL) THEN
  	INSERT INTO modele_validation(utilisateur_id) VALUES(1);
    SELECT LAST_INSERT_ID() INTO _validation_id;
    call mod_valide_log('MOD_VALIDE_RH_TOURNEE',concat_ws('*',_validation_id,_depot_id,_flux_id,_tournee_id));
  END IF;
  
  CALL mod_delete_tournee('RT', _depot_id, _flux_id, _tournee_id);

  -- Employé hors contrat
	INSERT INTO modele_journal(validation_id,depot_id,flux_id,erreur_id,jour_id,employe_id,tournee_id,tournee_jour_id,activite_id)
	SELECT DISTINCT _validation_id,gt.depot_id,gt.flux_id,1,NULL,mt.employe_id ,mt.id,NULL,NULL
	FROM modele_tournee mt
	INNER JOIN groupe_tournee gt ON mt.groupe_id=gt.id
	WHERE mt.actif
	AND (gt.depot_id=_depot_id OR _depot_id IS NULL)
	AND (gt.flux_id=_flux_id OR _flux_id IS NULL)
	AND mt.employe_id IS NOT NULL
	AND NOT EXISTS(SELECT NULL 
                  FROM emp_pop_depot epd 
                  WHERE mt.employe_id=epd.employe_id 
                  AND NOW() BETWEEN epd.date_debut AND epd.date_fin
                  AND gt.depot_id=epd.depot_id 
                  AND gt.flux_id=epd.flux_id)
	AND NOT EXISTS(SELECT NULL 
                  FROM emp_pop_depot epd
				  INNER JOIN emp_affectation eaf ON epd.contrat_id=eaf.contrat_id AND epd.depot_id=eaf.depot_org_id
                  WHERE mt.employe_id=epd.employe_id 
                  AND NOW() BETWEEN epd.date_debut AND epd.date_fin
                  AND NOW() BETWEEN eaf.date_debut AND eaf.date_fin
                  AND gt.depot_id=eaf.depot_dst_id 
                  AND gt.flux_id=eaf.flux_id)
  AND (mt.id=_tournee_id OR _tournee_id IS NULL)
  ;
  call mod_valide_log('mod_valide_rh_tournee','Tournee hors contrat');

  -- Heure de début Neo/Media différente de celle du groupe
	INSERT INTO modele_journal(validation_id,depot_id,flux_id,erreur_id,jour_id,employe_id,tournee_id,tournee_jour_id,activite_id,commentaire)
	SELECT DISTINCT _validation_id,gt.depot_id,gt.flux_id,2,NULL,mt.employe_id ,mt.id,NULL,NULL,concat(epd.heure_debut,' <> ',gt.heure_debut)
	FROM modele_tournee mt
	INNER JOIN groupe_tournee gt ON mt.groupe_id=gt.id
	INNER JOIN emp_pop_depot epd ON mt.employe_id=epd.employe_id AND NOW() BETWEEN epd.date_debut AND epd.date_fin
	WHERE mt.actif
	AND (gt.depot_id=_depot_id OR _depot_id IS NULL)
	AND (gt.flux_id=_flux_id OR _flux_id IS NULL)
  AND (mt.id=_tournee_id OR _tournee_id IS NULL)
  AND epd.typetournee_id=2
  AND (gt.heure_debut<>epd.heure_debut OR epd.heure_debut is null)
  ;
  call mod_valide_log('mod_valide_rh_tournee','Heure de debut du contrat differente de celle du groupe');
/*
  -- Type de tournee incompatible avec employe
	INSERT INTO modele_journal(validation_id,depot_id,flux_id,erreur_id,jour_id,employe_id,tournee_id,tournee_jour_id,activite_id)
	SELECT DISTINCT _validation_id,gt.depot_id,gt.flux_id,3,NULL,mt.employe_id ,mt.id,NULL,NULL
	FROM modele_tournee mt
	INNER JOIN groupe_tournee gt ON mt.groupe_id=gt.id
  INNER JOIN emp_pop_depot epd ON mt.employe_id=epd.employe_id AND NOW() BETWEEN epd.date_debut AND epd.date_fin
	WHERE mt.actif
	AND (gt.depot_id=_depot_id OR _depot_id IS NULL)
	AND (gt.flux_id=_flux_id OR _flux_id IS NULL)
  AND (mt.id=_tournee_id OR _tournee_id IS NULL)
  AND mt.typetournee_id<>epd.typetournee_id
  ;
  call mod_valide_log('mod_valide_rh_tournee','Type de tournee incompatible avec employe');
  */
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS mod_valide_rh_tournee_jour;
CREATE PROCEDURE `mod_valide_rh_tournee_jour`(INOUT _validation_id INT, IN _depot_id INT, IN _flux_id INT, IN _tournee_id INT, IN _tournee_jour_id INT)
BEGIN
/*
ATTENTION
	TR	VEH	Mode de transport incohérent avec celui de l’individu	X	X	
*/
  IF (_validation_id IS NULL) THEN
  	INSERT INTO modele_validation(utilisateur_id) VALUES(1);
    SELECT LAST_INSERT_ID() INTO _validation_id;
    call mod_valide_log('MOD_VALIDE_RH_TOURNEE_JOUR',concat_ws('*',_validation_id,_depot_id,_flux_id,_tournee_id,_tournee_jour_id));
  END IF;

  CALL mod_delete_tournee_jour('RJ', _depot_id, _flux_id, _tournee_id, _tournee_jour_id);

  -- Employé hors contrat
	INSERT INTO modele_journal(validation_id,depot_id,flux_id,erreur_id,jour_id,employe_id,tournee_id,tournee_jour_id,activite_id)
	SELECT DISTINCT _validation_id,gt.depot_id,gt.flux_id,4,mtj.jour_id,mtj.employe_id ,mtj.tournee_id,mtj.id,NULL
	FROM modele_tournee_jour mtj
	INNER JOIN modele_tournee mt ON mtj.tournee_id=mt.id
	INNER JOIN groupe_tournee gt ON mt.groupe_id=gt.id
	WHERE mt.actif
	AND NOW()<=mtj.date_fin
	AND (gt.depot_id=_depot_id OR _depot_id IS NULL)
	AND (gt.flux_id=_flux_id OR _flux_id IS NULL)
  AND (mt.id=_tournee_id OR _tournee_id IS NULL)
  AND (mtj.id=_tournee_jour_id OR _tournee_jour_id IS NULL)
  AND (mt.employe_id<>mtj.employe_id OR mt.employe_id is null)
	AND mtj.employe_id IS NOT NULL
	AND NOT EXISTS(SELECT NULL 
                    FROM emp_pop_depot epd 
                    WHERE mtj.employe_id=epd.employe_id 
                    AND NOW() BETWEEN epd.date_debut AND epd.date_fin
                    AND gt.depot_id=epd.depot_id 
                    AND gt.flux_id=epd.flux_id)
	AND NOT EXISTS(SELECT NULL 
                  FROM emp_pop_depot epd
				  INNER JOIN emp_affectation eaf ON epd.contrat_id=eaf.contrat_id AND epd.depot_id=eaf.depot_org_id
                  WHERE mtj.employe_id=epd.employe_id 
                  AND NOW() BETWEEN epd.date_debut AND epd.date_fin
                  AND NOW() BETWEEN eaf.date_debut AND eaf.date_fin
                  AND gt.depot_id=eaf.depot_dst_id 
                  AND gt.flux_id=eaf.flux_id)
  ;
  call mod_valide_log('mod_valide_rh_tournee_jour','Tournee hors contrat');

  -- Tournée réalisée par CDD
	INSERT INTO modele_journal(validation_id,depot_id,flux_id,erreur_id,jour_id,employe_id,tournee_id,tournee_jour_id,activite_id)
	SELECT DISTINCT _validation_id,gt.depot_id,gt.flux_id,27,mtj.jour_id,mtj.employe_id ,mtj.tournee_id,mtj.id,NULL
	FROM modele_tournee_jour mtj
	INNER JOIN modele_tournee mt ON mtj.tournee_id=mt.id
	INNER JOIN groupe_tournee gt ON mt.groupe_id=gt.id
  INNER JOIN emp_pop_depot epd ON mtj.employe_id=epd.employe_id AND NOW() BETWEEN epd.date_debut AND epd.date_fin
                    AND epd.typecontrat_id=1
	WHERE mt.actif
	AND NOW()<=mtj.date_fin
	AND (gt.depot_id=_depot_id OR _depot_id IS NULL)
	AND (gt.flux_id=_flux_id OR _flux_id IS NULL)
  AND (mt.id=_tournee_id OR _tournee_id IS NULL)
  AND (mtj.id=_tournee_jour_id OR _tournee_jour_id IS NULL)
  AND (mt.employe_id<>mtj.employe_id OR mt.employe_id is null)
	AND mtj.employe_id IS NOT NULL
  ;
  call mod_valide_log('mod_valide_rh_tournee_jour','Tournée réalisée par CDD');

  -- Employé hors cycle
	INSERT INTO modele_journal(validation_id,depot_id,flux_id,erreur_id,jour_id,employe_id,tournee_id,tournee_jour_id,activite_id,commentaire)
	SELECT DISTINCT _validation_id,gt.depot_id,gt.flux_id,5,mtj.jour_id,mtj.employe_id ,mtj.tournee_id,mtj.id,NULL,cycle_to_string(ec.lundi,ec.mardi,ec.mercredi,ec.jeudi,ec.vendredi,ec.samedi,ec.dimanche)
	FROM modele_tournee_jour mtj
	INNER JOIN modele_tournee mt ON mtj.tournee_id=mt.id
	INNER JOIN groupe_tournee gt ON mt.groupe_id=gt.id
  INNER JOIN emp_pop_depot epd ON mtj.employe_id=epd.employe_id AND NOW() BETWEEN epd.date_debut AND epd.date_fin
                    AND epd.typecontrat_id=2
  LEFT OUTER JOIN emp_cycle ec ON mtj.employe_id=ec.employe_id AND mtj.date_fin>=ec.date_debut AND mtj.date_debut<=ec.date_fin
	WHERE mt.actif
	AND NOW()<=mtj.date_fin
	AND (gt.depot_id=_depot_id OR _depot_id IS NULL)
	AND (gt.flux_id=_flux_id OR _flux_id IS NULL)
  AND (mt.id=_tournee_id OR _tournee_id IS NULL)
  AND (mtj.id=_tournee_jour_id OR _tournee_jour_id IS NULL)
	AND NOT EXISTS(SELECT NULL 
                    FROM emp_cycle ec2
                    WHERE ec.id=ec2.id
                    AND CASE mtj.jour_id
                        WHEN 1 THEN ec2.dimanche
                        WHEN 2 THEN ec2.lundi
                        WHEN 3 THEN ec2.mardi
                        WHEN 4 THEN ec2.mercredi
                        WHEN 5 THEN ec2.jeudi
                        WHEN 6 THEN ec2.vendredi
                        WHEN 7 THEN ec2.samedi
                        END
                        )
  ;
  call mod_valide_log('mod_valide_rh_tournee_jour','Tournee hors cycle');
 
  -- Employé hors cycle
	INSERT INTO modele_journal(validation_id,depot_id,flux_id,erreur_id,jour_id,employe_id,tournee_id,tournee_jour_id,activite_id)
 	SELECT DISTINCT _validation_id,gt.depot_id,gt.flux_id,20,mtj.jour_id,mtj.employe_id ,mtj.tournee_id,mtj.id,NULL
	FROM modele_tournee_jour mtj
	INNER JOIN modele_tournee mt ON mtj.tournee_id=mt.id
	INNER JOIN groupe_tournee gt ON mt.groupe_id=gt.id
  INNER JOIN emp_pop_depot epd ON mtj.employe_id=epd.employe_id AND mtj.date_fin>=epd.date_debut AND mtj.date_debut<=epd.date_fin
	WHERE mt.actif
	AND NOW()<=mtj.date_fin
  AND epd.emploi_id=2
	AND (gt.depot_id=_depot_id OR _depot_id IS NULL)
	AND (gt.flux_id=_flux_id OR _flux_id IS NULL)
  AND (mt.id=_tournee_id OR _tournee_id IS NULL)
  AND (mtj.id=_tournee_jour_id OR _tournee_jour_id IS NULL)
  ;
  call mod_valide_log('mod_valide_rh_tournee_jour','Tournée réalisée par polyvalent');
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS mod_valide_rh_activite;
CREATE PROCEDURE `mod_valide_rh_activite`(INOUT _validation_id INT, IN _depot_id INT, IN _flux_id INT, IN _activite_id INT)
BEGIN
/*
ATTENTION
	AC	VEH	Mode de transport incohérent avec celui de l’individu	X		X
*/

  IF (_validation_id IS NULL) THEN
  	INSERT INTO modele_validation(utilisateur_id) VALUES(1);
    SELECT LAST_INSERT_ID() INTO _validation_id;
    call mod_valide_log('MOD_VALIDE_RH_ACTIVITE',concat_ws('*',_validation_id,_depot_id,_flux_id,_activite_id));
  END IF
  ;

  CALL mod_delete_activite('RA',_depot_id,_flux_id,_activite_id);

  -- Activité hors contrat
	INSERT INTO modele_journal(validation_id,depot_id,flux_id,erreur_id,jour_id,employe_id,tournee_id,tournee_jour_id,activite_id)
	SELECT DISTINCT _validation_id,ma.depot_id,ma.flux_id,6,ma.jour_id,ma.employe_id ,NULL,NULL,ma.id
	FROM modele_activite ma
	WHERE NOW()<=ma.date_fin
	AND (ma.depot_id=_depot_id OR _depot_id IS NULL)
	AND (ma.flux_id=_flux_id OR _flux_id IS NULL)
  AND (ma.id=_activite_id OR _activite_id IS NULL)
	AND ma.employe_id IS NOT NULL
  AND NOT EXISTS(SELECT NULL 
                    FROM emp_pop_depot epd 
                    WHERE ma.employe_id=epd.employe_id 
                    AND NOW() BETWEEN epd.date_debut AND epd.date_fin
                    AND ma.depot_id=epd.depot_id 
                    AND ma.flux_id=epd.flux_id)
	AND NOT EXISTS(SELECT NULL 
                  FROM emp_pop_depot epd
				  INNER JOIN emp_affectation eaf ON epd.contrat_id=eaf.contrat_id AND epd.depot_id=eaf.depot_org_id
                  WHERE ma.employe_id=epd.employe_id 
                  AND NOW() BETWEEN epd.date_debut AND epd.date_fin
                  AND NOW() BETWEEN eaf.date_debut AND eaf.date_fin
                  AND ma.depot_id=eaf.depot_dst_id 
                  AND ma.flux_id=eaf.flux_id)
  ;
  call mod_valide_log('mod_valide_rh_activite','Activite hors contrat');

  -- Activité hors cycle
	INSERT INTO modele_journal(validation_id,depot_id,flux_id,erreur_id,jour_id,employe_id,tournee_id,activite_id,commentaire)
	SELECT DISTINCT _validation_id,ma.depot_id,ma.flux_id,7,ma.jour_id,ma.employe_id ,NULL, ma.id, ec.cycle
	FROM modele_activite ma
/*  INNER JOIN emp_pop_depot epd ON ma.employe_id=epd.employe_id AND epd.date_debut<=ma.date_debut AND ma.date_fin<=epd.date_fin 
                    AND ma.depot_id=epd.depot_id 
                    AND ma.flux_id=epd.flux_id*/
  LEFT OUTER JOIN emp_cycle ec ON ma.employe_id=ec.employe_id and ma.date_fin>=ec.date_debut AND ma.date_debut<=ec.date_fin
	WHERE NOW()<=ma.date_fin
	AND (ma.depot_id=_depot_id OR _depot_id IS NULL)
	AND (ma.flux_id=_flux_id OR _flux_id IS NULL)
  AND (ma.id=_activite_id OR _activite_id IS NULL)
	AND ma.employe_id IS NOT NULL
	AND NOT EXISTS(SELECT NULL 
                    FROM emp_cycle ec2
                    WHERE ec.id=ec2.id
                    AND CASE ma.jour_id
                        WHEN 1 THEN ec2.dimanche
                        WHEN 2 THEN ec2.lundi
                        WHEN 3 THEN ec2.mardi
                        WHEN 4 THEN ec2.mercredi
                        WHEN 5 THEN ec2.jeudi
                        WHEN 6 THEN ec2.vendredi
                        WHEN 7 THEN ec2.samedi
                        END
                        )
  ;
  call mod_valide_log('mod_valide_rh_activite','Activite hors cycle');
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS mod_valide_rh_remplacement;
CREATE PROCEDURE `mod_valide_rh_remplacement`(INOUT _validation_id INT, IN _depot_id INT, IN _flux_id INT, IN _remplacement_id INT)
BEGIN
/*
ATTENTION
	AC	VEH	Mode de transport incohérent avec celui de l’individu	X		X
*/

  IF (_validation_id IS NULL) THEN
  	INSERT INTO modele_validation(utilisateur_id) VALUES(1);
    SELECT LAST_INSERT_ID() INTO _validation_id;
    call mod_valide_log('MOD_VALIDE_RH_REMPLACEMENT',concat_ws('*',_validation_id,_depot_id,_flux_id,_remplacement_id));
  END IF
  ;

  CALL mod_delete_remplacement('RR',_depot_id,_flux_id,_remplacement_id);

  -- Activité hors contrat
	INSERT INTO modele_journal(validation_id,depot_id,flux_id,erreur_id,jour_id,employe_id,tournee_id,tournee_jour_id,activite_id,remplacement_id)
	SELECT DISTINCT _validation_id,mr.depot_id,mr.flux_id,22,null,mr.employe_id ,NULL,NULL,NULL,mr.id
	FROM modele_remplacement mr
  INNER JOIN pai_mois pm ON pm.flux_id=mr.flux_id AND pm.date_debut<=mr.date_fin
	WHERE (mr.depot_id=_depot_id OR _depot_id IS NULL)
	AND (mr.flux_id=_flux_id OR _flux_id IS NULL)
  AND (mr.id=_remplacement_id OR _remplacement_id IS NULL)
  AND NOT EXISTS(SELECT NULL 
                    FROM emp_contrat_type ect
                    WHERE mr.contrattype_id=ect.id
                    AND mr.date_debut between ect.date_debut and ect.date_fin
                    AND mr.date_fin between ect.date_debut and ect.date_fin)
  ;
  call mod_valide_log('mod_valide_rh_remplacement','Remplacement hors contrat');

  -- Remplacement hors cycle
	INSERT INTO modele_journal(validation_id,depot_id,flux_id,erreur_id,jour_id,employe_id,tournee_id,tournee_jour_id,activite_id,remplacement_id,commentaire)
	SELECT DISTINCT _validation_id,mr.depot_id,mr.flux_id,23,NULL,mr.employe_id ,NULL, NULL, NULL, mr.id,ec.cycle
	FROM modele_remplacement mr
  INNER JOIN modele_remplacement_jour mrj ON mrj.remplacement_id=mr.id
  INNER JOIN pai_mois pm ON pm.flux_id=mr.flux_id AND pm.date_debut<=mr.date_fin
  INNER JOIN emp_contrat_type ect ON mr.contrattype_id=ect.id
                    AND mr.date_debut between ect.date_debut and ect.date_fin
                    AND mr.date_fin between ect.date_debut and ect.date_fin
  INNER JOIN emp_cycle ec ON mr.employe_id=ec.employe_id and mr.date_debut<=ec.date_fin AND mr.date_fin>=ec.date_debut
	WHERE (mr.depot_id=_depot_id OR _depot_id IS NULL)
	AND (mr.flux_id=_flux_id OR _flux_id IS NULL)
  AND (mr.id=_remplacement_id OR _remplacement_id IS NULL)
	AND NOT EXISTS(SELECT NULL 
                    FROM emp_cycle ec2
                    WHERE ec.id=ec2.id 
                    AND CASE mrj.jour_id
                        WHEN 1 THEN ec2.dimanche
                        WHEN 2 THEN ec2.lundi
                        WHEN 3 THEN ec2.mardi
                        WHEN 4 THEN ec2.mercredi
                        WHEN 5 THEN ec2.jeudi
                        WHEN 6 THEN ec2.vendredi
                        WHEN 7 THEN ec2.samedi
                        END
                        )
  ;
  call mod_valide_log('mod_valide_rh_remplacement','Remplacement hors cycle');
  
  -- Cycle remplacant différent de cycle remplacé
	INSERT INTO modele_journal(validation_id,depot_id,flux_id,erreur_id,jour_id,employe_id,tournee_id,tournee_jour_id,activite_id,remplacement_id,commentaire)
	SELECT DISTINCT _validation_id,mr.depot_id,mr.flux_id,25,NULL,mr.employe_id ,NULL, NULL, NULL, mr.id,concat_ws(' ',r.nom,r.prenom1,r.prenom2,'(',ecye.cycle,'<>',ecyr.cycle,')')
	FROM modele_remplacement mr
  INNER JOIN emp_cycle ecye on mr.employe_id=ecye.employe_id and mr.date_debut<=ecye.date_fin and mr.date_fin>=ecye.date_debut
  INNER JOIN emp_contrat_type ect on mr.contrattype_id=ect.id
  INNER JOIN emp_cycle ecyr on ect.remplace_id=ecyr.employe_id and ect.date_debut<=ecyr.date_fin and ect.date_fin>=ecyr.date_debut
  INNER JOIN employe r on ect.remplace_id=r.id
  INNER JOIN pai_mois pm ON pm.flux_id=mr.flux_id AND pm.date_debut<=mr.date_fin
	WHERE (mr.depot_id=_depot_id OR _depot_id IS NULL)
	AND (mr.flux_id=_flux_id OR _flux_id IS NULL)
  AND (mr.id=_remplacement_id OR _remplacement_id IS NULL)
  AND ecye.cycle<>ecyr.cycle
  ;
  call mod_valide_log('mod_valide_rh_remplacement','Cycle remplacant différent de cycle remplacé');
  
	UPDATE modele_remplacement mr
  INNER JOIN pai_mois pm ON pm.flux_id=mr.flux_id AND pm.date_debut<=mr.date_fin
  INNER JOIN modele_journal mj ON mr.id=mj.remplacement_id
  INNER JOIN modele_ref_erreur mre on mj.erreur_id=mre.id and not mre.valide
  SET mr.actif=false
	WHERE (mr.depot_id=_depot_id OR _depot_id IS NULL)
	AND (mr.flux_id=_flux_id OR _flux_id IS NULL)
  AND (mr.id=_remplacement_id OR _remplacement_id IS NULL)
  ;  
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS mod_valide_rh;
CREATE PROCEDURE `mod_valide_rh`(INOUT _validation_id INT, IN _depot_id INT, IN _flux_id INT)
BEGIN
  IF (_validation_id IS NULL) THEN
  	INSERT INTO modele_validation(utilisateur_id) VALUES(1);
    SELECT LAST_INSERT_ID() INTO _validation_id;
    call mod_valide_log('MOD_VALIDE_RH',concat_ws('*',_validation_id,_depot_id,_flux_id));
  END IF
  ;
  CALL mod_valide_rh_tournee(_validation_id, _depot_id, _flux_id, NULL);
  CALL mod_valide_rh_tournee_jour(_validation_id, _depot_id, _flux_id, NULL, NULL);
  CALL mod_valide_rh_activite(_validation_id, _depot_id, _flux_id, NULL);
  CALL mod_valide_remplacement(_validation_id, _depot_id, _flux_id, NULL);
 END;
 
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS mod_valide_tournee;
CREATE PROCEDURE `mod_valide_tournee`(INOUT _validation_id INT, IN _depot_id INT, IN _flux_id INT, _tournee_id INT)
BEGIN
  IF (_validation_id IS NULL) THEN
  	INSERT INTO modele_validation(utilisateur_id) VALUES(1);
    SELECT LAST_INSERT_ID() INTO _validation_id;
    call mod_valide_log('MOD_VALIDE_TOURNEE',concat_ws('*',_validation_id,_tournee_id));
  END IF
  ;
  CALL mod_valide_rh_tournee(_validation_id, _depot_id, _flux_id, _tournee_id);
  call mod_valide_tournee_jour(_validation_id, _depot_id, _flux_id, _tournee_id, null);
  CALL mod_valide_chevauchement_tournee(_validation_id, _depot_id, _flux_id, _tournee_id, null);

  CALL mod_delete_tournee('TO',null,null,_tournee_id);
/*
-- 26/01/2017, depuis l'étalonneage, les valeurs de rémunération sont différentes sur la semaine
-- C'est l'étalon moyen qui est identique
  -- Valeurs de rémunération différentes sur la semaine
	INSERT INTO modele_journal(validation_id,depot_id,flux_id,erreur_id,jour_id,employe_id,tournee_id,tournee_jour_id,activite_id)
	SELECT DISTINCT _validation_id,gt.depot_id,gt.flux_id,19, null, null ,mt.id, null ,NULL
	FROM modele_tournee mt
  INNER JOIN modele_tournee_jour mtj ON mtj.tournee_id=mt.id
	INNER JOIN groupe_tournee gt ON mt.groupe_id=gt.id
	WHERE mt.actif
  AND NOW() <= mtj.date_fin
	AND (gt.depot_id=_depot_id OR _depot_id IS NULL)
	AND (gt.flux_id=_flux_id OR _flux_id IS NULL)
  AND (mt.id=_tournee_id OR _tournee_id IS NULL)
  group by gt.id,mt.id,mtj.date_debut
  having count(distinct mtj.valrem)>1
  union
	SELECT DISTINCT _validation_id,gt.depot_id,gt.flux_id,19, null, null ,mt.id, null ,NULL
	FROM modele_tournee mt
  INNER JOIN modele_tournee_jour mtj ON mtj.tournee_id=mt.id
	INNER JOIN groupe_tournee gt ON mt.groupe_id=gt.id
	WHERE mt.actif
  AND NOW() <= mtj.date_fin
	AND (gt.depot_id=_depot_id OR _depot_id IS NULL)
	AND (gt.flux_id=_flux_id OR _flux_id IS NULL)
  AND (mt.id=_tournee_id OR _tournee_id IS NULL)
  group by gt.id,mt.id,mtj.date_fin
  having count(distinct mtj.valrem)>1
	;
  call mod_valide_log('mod_valide_tournee','Valeurs de rémunération différentes sur la semaine');
*/
  -- Dimanche renseigné sur une tournée Néo/Média
  -- N'est pas en doublon avec celui dans mod_valide_tournee_jour ???
	INSERT INTO modele_journal(validation_id,depot_id,flux_id,erreur_id,jour_id,employe_id,tournee_id,tournee_jour_id,activite_id)
	SELECT DISTINCT _validation_id,gt.depot_id,gt.flux_id,8,mtj.jour_id,mtj.employe_id ,mt.id,mtj.id,NULL
	FROM modele_tournee mt
  INNER JOIN modele_tournee_jour mtj ON mtj.tournee_id=mt.id
	INNER JOIN groupe_tournee gt ON mt.groupe_id=gt.id
  INNER JOIN emp_pop_depot epd ON mtj.employe_id=epd.employe_id AND mtj.date_fin>=epd.date_debut AND mtj.date_debut<=epd.date_fin
	WHERE mt.actif
  AND NOW() <= mtj.date_fin
	AND (gt.depot_id=_depot_id OR _depot_id IS NULL)
	AND (gt.flux_id=_flux_id OR _flux_id IS NULL)
  AND (mt.id=_tournee_id OR _tournee_id IS NULL)
  AND epd.typetournee_id=2
  AND mtj.jour_id=1
	;
  call mod_valide_log('mod_valide_tournee','Dimanche renseigne sur une tournée Neo/Media');
/*
  -- Jour non renseigné sur une tournée Néo/Média
	INSERT INTO modele_journal(validation_id,depot_id,flux_id,erreur_id,jour_id,employe_id,tournee_id,tournee_jour_id,activite_id)
	SELECT DISTINCT _validation_id,gt.depot_id,gt.flux_id,9,NULL,mt.employe_id ,mt.id,NULL,NULL
	FROM modele_tournee mt
  INNER JOIN emp_pop_depot epd on mt.employe_id=epd.employe_id
	INNER JOIN groupe_tournee gt ON mt.groupe_id=gt.id
	WHERE mt.actif
	AND (gt.depot_id=_depot_id OR _depot_id IS NULL)
	AND (gt.flux_id=_flux_id OR _flux_id IS NULL)
  AND (mt.id=_tournee_id OR _tournee_id IS NULL)
  AND epd.typetournee_id=2
  AND (NOT exists(select null from modele_tournee_jour mtj WHERE mtj.tournee_id=mt.id AND mtj.jour_id=2)
  OR   NOT exists(select null from modele_tournee_jour mtj WHERE mtj.tournee_id=mt.id AND mtj.jour_id=3)
  OR   NOT exists(select null from modele_tournee_jour mtj WHERE mtj.tournee_id=mt.id AND mtj.jour_id=4)
  OR   NOT exists(select null from modele_tournee_jour mtj WHERE mtj.tournee_id=mt.id AND mtj.jour_id=5)
  OR   NOT exists(select null from modele_tournee_jour mtj WHERE mtj.tournee_id=mt.id AND mtj.jour_id=6)
  OR   NOT exists(select null from modele_tournee_jour mtj WHERE mtj.tournee_id=mt.id AND mtj.jour_id=7))
	;
  call mod_valide_log('mod_valide_tournee','Jour non renseigne sur une tournee Media');
  */
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS mod_valide_tournee_jour;
CREATE PROCEDURE `mod_valide_tournee_jour`(INOUT _validation_id INT, IN _depot_id INT, IN _flux_id INT, IN _tournee_id INT, IN _tournee_jour_id INT)
BEGIN
  IF (_validation_id IS NULL) THEN
  	INSERT INTO modele_validation(utilisateur_id) VALUES(1);
    SELECT LAST_INSERT_ID() INTO _validation_id;
    call mod_valide_log('MOD_VALIDE_TOURNEE_JOUR',concat_ws('*',_validation_id,_tournee_id,_tournee_jour_id));
  END IF
  ;
  CALL mod_valide_rh_tournee_jour(_validation_id, _depot_id, _flux_id,_tournee_id, _tournee_jour_id);
  CALL mod_valide_chevauchement_tournee(_validation_id, _depot_id, _flux_id, _tournee_id, _tournee_jour_id);
  
  CALL mod_delete_tournee_jour('TJ', _depot_id, _flux_id, _tournee_id,_tournee_jour_id);

  -- Tournée incomplète
	INSERT INTO modele_journal(validation_id,depot_id,flux_id,erreur_id,jour_id,employe_id,tournee_id,tournee_jour_id,activite_id)
	SELECT DISTINCT _validation_id,gt.depot_id,gt.flux_id,10,mtj.jour_id,mtj.employe_id ,mtj.tournee_id,mtj.id,NULL
	FROM modele_tournee_jour mtj
	INNER JOIN modele_tournee mt ON mtj.tournee_id=mt.id
	INNER JOIN groupe_tournee gt ON mt.groupe_id=gt.id
	WHERE mt.actif
	AND NOW()<=mtj.date_fin
	AND (gt.depot_id=_depot_id OR _depot_id IS NULL)
	AND (gt.flux_id=_flux_id OR _flux_id IS NULL)
  AND (mt.id=_tournee_id OR _tournee_id IS NULL)
	AND (mtj.id=_tournee_jour_id OR _tournee_jour_id IS NULL)
	AND (mtj.employe_id IS NULL
	OR    mtj.transport_id IS NULL
	OR    valrem IS NULL -- OR valrem=0
	OR    duree IS NULL -- OR duree=0
	OR    nbkm IS NULL -- or nbkm=0
	OR    nbkm_paye IS NULL -- or nbkm_paye=0
	)
	;
  call mod_valide_log('mod_valide_tournee_jour','Tournee incomplete');

  -- Employé semaine différent de employé jour pour les tournée Néo/Média
	INSERT INTO modele_journal(validation_id,depot_id,flux_id,erreur_id,jour_id,employe_id,tournee_id,tournee_jour_id,activite_id,commentaire)
	SELECT DISTINCT _validation_id,gt.depot_id,gt.flux_id,12,mtj.jour_id,mtj.employe_id ,mt.id,mtj.id,NULL, concat_ws(' ',e.nom,e.prenom1,e.prenom2)
	FROM modele_tournee_jour mtj
	INNER JOIN modele_tournee mt ON mtj.tournee_id=mt.id
	INNER JOIN groupe_tournee gt ON mt.groupe_id=gt.id
  INNER JOIN emp_pop_depot epd ON mt.employe_id=epd.employe_id AND NOW() BETWEEN epd.date_debut AND epd.date_fin
  INNER JOIN employe e ON mt.employe_id=e.id                            
	WHERE mt.actif
	AND NOW()<=mtj.date_fin
	AND (gt.depot_id=_depot_id OR _depot_id IS NULL)
	AND (gt.flux_id=_flux_id OR _flux_id IS NULL)
  AND (mt.id=_tournee_id OR _tournee_id IS NULL)
	AND (mtj.id=_tournee_jour_id OR _tournee_jour_id IS NULL)
  AND epd.typetournee_id=2
  AND mt.employe_id<>mtj.employe_id
	;
  call mod_valide_log('mod_valide_tournee_jour','Employe semaine different de employe jour sur une tournee Media');

  -- Dimanche renseigné sur une tournée Néo/Média
	INSERT INTO modele_journal(validation_id,depot_id,flux_id,erreur_id,jour_id,employe_id,tournee_id,tournee_jour_id,activite_id)
	SELECT DISTINCT _validation_id,gt.depot_id,gt.flux_id,13,mtj.jour_id,mtj.employe_id ,mt.id,mtj.id,NULL
	FROM modele_tournee mt
  INNER JOIN modele_tournee_jour mtj ON mtj.tournee_id=mt.id
	INNER JOIN groupe_tournee gt ON mt.groupe_id=gt.id
  INNER JOIN emp_pop_depot epd ON mt.employe_id=epd.employe_id AND NOW() BETWEEN epd.date_debut AND epd.date_fin
	WHERE mt.actif
  AND NOW()<=mtj.date_fin
	AND (gt.depot_id=_depot_id OR _depot_id IS NULL)
	AND (gt.flux_id=_flux_id OR _flux_id IS NULL)
  AND (mt.id=_tournee_id OR _tournee_id IS NULL)
  AND (mtj.id=_tournee_jour_id OR _tournee_jour_id IS NULL)
  AND epd.typetournee_id=2
  AND mtj.jour_id=1
	;
  call mod_valide_log('mod_valide_tournee_jour','Dimanche renseigne sur une tournee Media');
END;
 
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS mod_valide_activite;
CREATE PROCEDURE `mod_valide_activite`(INOUT _validation_id INT, IN _depot_id INT, IN _flux_id INT, IN _activite_id INT)

BEGIN
	IF (_validation_id IS NULL) THEN
		INSERT INTO modele_validation(utilisateur_id) VALUES(1);
		SELECT LAST_INSERT_ID() INTO _validation_id;
    call mod_valide_log('MOD_VALIDE_ACTIVITE',concat_ws('*',_validation_id,_activite_id));
	END IF
	;
	CALL mod_valide_rh_activite(_validation_id, _depot_id, _flux_id, _activite_id);
	CALL mod_valide_chevauchement_activite(_validation_id, _depot_id, _flux_id, _activite_id);

	CALL mod_delete_activite('AC', _depot_id, _flux_id,_activite_id);

  -- Acitivite incomplète
	INSERT INTO modele_journal(validation_id,depot_id,flux_id,erreur_id,jour_id,employe_id,tournee_id,tournee_jour_id,activite_id)
	SELECT DISTINCT _validation_id,a.depot_id,a.flux_id,14,a.jour_id,a.employe_id ,NULL,NULL,a.id
	FROM modele_activite a
	WHERE NOW()<=a.date_fin
	AND (a.id=_activite_id OR _activite_id IS NULL)
	AND (a.depot_id=_depot_id OR _depot_id IS NULL)
	AND (a.flux_id=_flux_id OR _flux_id IS NULL)
	AND  (a.employe_id IS NULL
	OR    a.transport_id IS NULL
	OR    a.duree IS NULL OR a.duree=0
	OR    a.nbkm_paye IS NULL
	)
	;
  call mod_valide_log('mod_valide_activite','Activite incomplete');
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS mod_valide_remplacement;
CREATE PROCEDURE `mod_valide_remplacement`(INOUT _validation_id INT, IN _depot_id INT, IN _flux_id INT, IN _remplacement_id INT)

BEGIN
	IF (_validation_id IS NULL) THEN
		INSERT INTO modele_validation(utilisateur_id) VALUES(1);
		SELECT LAST_INSERT_ID() INTO _validation_id;
    call mod_valide_log('MOD_VALIDE_REMPLACEMENT',concat_ws('*',_validation_id,_remplacement_id));
	END IF
	;
	CALL mod_valide_rh_remplacement(_validation_id, _depot_id, _flux_id, _remplacement_id);

	CALL mod_delete_remplacement('RE', _depot_id, _flux_id,_remplacement_id);

  -- Remplacement incomplet
	INSERT INTO modele_journal(validation_id,depot_id,flux_id,erreur_id,jour_id,employe_id,tournee_id,tournee_jour_id,activite_id,remplacement_id)
	SELECT DISTINCT _validation_id,mr.depot_id,mr.flux_id,24,null,mr.employe_id ,NULL,NULL,NULL,mr.id
	FROM modele_remplacement mr
  INNER JOIN modele_remplacement_jour mrj ON mrj.remplacement_id=mr.id
  INNER JOIN pai_mois pm ON pm.flux_id=mr.flux_id AND mr.date_fin>=pm.date_debut
	WHERE (mr.id=_remplacement_id OR _remplacement_id IS NULL)
	AND (mr.depot_id=_depot_id OR _depot_id IS NULL)
	AND (mr.flux_id=_flux_id OR _flux_id IS NULL)
	AND (mrj.modele_tournee_id is null
  OR mrj.pai_tournee_id is null
  OR mrj.valrem is null OR mrj.valrem=0
  )
	;
  call mod_valide_log('mod_valide_remplacement','Remplacement incomplet');
  -- Plusieurs remplaçants pour la même tournée
	INSERT INTO modele_journal(validation_id,depot_id,flux_id,erreur_id,jour_id,employe_id,tournee_id,tournee_jour_id,activite_id,remplacement_id,commentaire)
	SELECT DISTINCT _validation_id,mr1.depot_id,mr1.flux_id,26,mrj1.jour_id,mr1.employe_id ,mrj1.modele_tournee_id,NULL,NULL,mr1.id,concat_ws(' ',e.nom,e.prenom1,e.prenom2)
	FROM modele_remplacement mr1
  INNER JOIN modele_remplacement_jour mrj1 ON mrj1.remplacement_id=mr1.id
  INNER JOIN modele_remplacement_jour mrj2 ON mrj1.modele_tournee_id=mrj2.modele_tournee_id and mrj1.jour_id=mrj2.jour_id 
  INNER JOIN modele_remplacement mr2 ON mrj2.remplacement_id=mr2.id
  INNEr JOIN employe e ON mr2.employe_id=e.id
  INNER JOIN pai_mois pm ON pm.flux_id=mr1.flux_id AND mr1.date_fin>=pm.date_debut
	WHERE (mr1.id=_remplacement_id OR mr2.id=_remplacement_id OR _remplacement_id IS NULL)
	AND (mr1.depot_id=_depot_id OR _depot_id IS NULL)
	AND (mr1.flux_id=_flux_id OR _flux_id IS NULL)
  AND mr2.id<>mr1.id 
  AND mr1.date_debut<=mr2.date_fin and mr1.date_fin>=mr2.date_debut
	;
  call mod_valide_log('mod_valide_remplacement','Plusieurs remplaçants pour la même tournée');
  
	UPDATE modele_remplacement mr
  INNER JOIN pai_mois pm ON pm.flux_id=mr.flux_id AND pm.date_debut<=mr.date_fin
  INNER JOIN modele_journal mj ON mr.id=mj.remplacement_id
  INNER JOIN modele_ref_erreur mre on mj.erreur_id=mre.id and not mre.valide
  SET mr.actif=false
	WHERE (mr.depot_id=_depot_id OR _depot_id IS NULL)
	AND (mr.flux_id=_flux_id OR _flux_id IS NULL)
  AND (mr.id=_remplacement_id OR _remplacement_id IS NULL)
  ;  
  
  call mod_remplacement_update_valrem(0,_depot_id,_flux_id,_remplacement_id);
 END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS mod_valide_chevauchement;
CREATE PROCEDURE `mod_valide_chevauchement`(INOUT _validation_id INT, IN _depot_id INT, IN _flux_id INT, IN _tournee_id INT, IN _tournee_jour_id INT, IN _activite_id INT)

BEGIN
  -- Chevauchement tournée/activité
	INSERT INTO modele_journal(validation_id,depot_id,flux_id,erreur_id,jour_id,employe_id,tournee_id,tournee_jour_id,activite_id)
	SELECT DISTINCT IF(mt.id=_tournee_id OR mtj.id=_tournee_jour_id OR ma.id=_activite_id,_validation_id,0),gt.depot_id,gt.flux_id,15,mtj.jour_id,mtj.employe_id ,mtj.tournee_id,mtj.id,ma.id
	FROM modele_tournee_jour mtj
	INNER JOIN modele_tournee mt ON mtj.tournee_id=mt.id
	INNER JOIN groupe_tournee gt ON mt.groupe_id=gt.id
  INNER JOIN modele_activite ma ON mtj.employe_id=ma.employe_id AND mtj.jour_id=ma.jour_id
	WHERE mt.actif
	AND NOW()<=mtj.date_fin
	AND NOW()<=ma.date_fin
	AND (gt.depot_id=_depot_id OR _depot_id IS NULL)
	AND (gt.flux_id=_flux_id OR _flux_id IS NULL)
--  AND (mt.id=_tournee_id OR _tournee_id IS NULL)
--  AND (mtj.id=_tournee_jour_id OR _tournee_jour_id IS NULL)
--  AND (ma.id=_activite_id OR _activite_id IS NULL)
  AND (ma.heure_debut<=gt.heure_debut AND gt.heure_debut<addtime(ma.heure_debut,ma.duree)
	OR   gt.heure_debut<=ma.heure_debut AND ma.heure_debut<addtime(gt.heure_debut,mtj.duree))
	;
  call mod_valide_log('mod_valide_chevauchement','Chevauchement tournee/activite');
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS mod_valide_chevauchement_tournee;
CREATE PROCEDURE `mod_valide_chevauchement_tournee`(INOUT _validation_id INT, IN _depot_id INT, IN _flux_id INT, IN _tournee_id INT, IN _tournee_jour_id INT)

BEGIN
	IF (_validation_id IS NULL) THEN
		INSERT INTO modele_validation(utilisateur_id) VALUES(1);
		SELECT LAST_INSERT_ID() INTO _validation_id;
	END IF
	;
/*  CALL mod_delete_tournee_jour('HO', _depot_id, _flux_id, _tournee_id, _tournee_jour_id);
  CALL mod_valide_chevauchement(_validation_id, _depot_id, _flux_id, _tournee_id, _tournee_jour_id, null);*/
  CALL mod_delete_tournee_jour('HO', _depot_id, _flux_id, null, null);
  CALL mod_valide_chevauchement(_validation_id, _depot_id, _flux_id, null, null, null);
  
/*
10/03/2016 On utilise la numérotation automatique des tournées en fonction du code
	-- Chevauchement tournée/tournée
	INSERT INTO modele_journal(validation_id,depot_id,flux_id,erreur_id,jour_id,employe_id,tournee_id,tournee_jour_id,activite_id)
	SELECT DISTINCT IF(mt1.id=_tournee_id OR mtj1.id=_tournee_jour_id,_validation_id,0),gt1.depot_id,gt1.flux_id,16,mtj1.jour_id,mtj1.employe_id ,mtj1.tournee_id,mtj1.id,NULL
	FROM modele_tournee_jour mtj1
	INNER JOIN modele_tournee mt1 ON mtj1.tournee_id=mt1.id AND mt1.actif
	INNER JOIN groupe_tournee gt1 ON mt1.groupe_id=gt1.id
	INNER JOIN modele_tournee_jour mtj2 ON mtj1.employe_id=mtj2.employe_id AND mtj1.jour_id=mtj2.jour_id AND mtj1.id<>mtj2.id
	INNER JOIN modele_tournee mt2 ON mtj2.tournee_id=mt2.id AND mt2.actif
	INNER JOIN groupe_tournee gt2 ON mt2.groupe_id=gt2.id
	WHERE mt1.actif
  AND mt2.actif
	AND NOW()<=mtj1.date_fin
	AND NOW()<=mtj2.date_fin
	AND (gt1.depot_id=_depot_id OR _depot_id IS NULL)
	AND (gt1.flux_id=_flux_id OR _flux_id IS NULL)
--  AND (mt1.id=_tournee_id OR _tournee_id IS NULL)
--  AND (mtj1.id=_tournee_jour_id OR _tournee_jour_id IS NULL)
  AND mtj1.ordre=mtj2.ordre
  AND (mtj1.date_fin>=mtj2.date_debut and mtj1.date_debut<=mtj2.date_fin or mtj2.date_fin>=mtj1.date_debut and mtj2.date_debut<=mtj1.date_fin)
	AND (mtj1.id=_tournee_jour_id OR mtj2.id=_tournee_jour_id OR _tournee_jour_id IS NULL)
	AND (gt1.heure_debut<=gt2.heure_debut AND gt2.heure_debut<addtime(gt1.heure_debut,mtj1.duree)
	OR   gt2.heure_debut<=gt1.heure_debut AND gt1.heure_debut<addtime(gt2.heure_debut,mtj2.duree))
	;
  call mod_valide_log('mod_valide_chevauchement_tournee','Chevauchement tournee/tournee');
  -- Plusieurs tournées avec le même numéro d'ordre
	INSERT INTO modele_journal(validation_id,depot_id,flux_id,erreur_id,jour_id,employe_id,tournee_id,tournee_jour_id,activite_id)
	SELECT DISTINCT IF(mt1.id=_tournee_id OR mtj1.id=_tournee_jour_id,_validation_id,0),gt1.depot_id,gt1.flux_id,11,mtj1.jour_id,mtj1.employe_id ,mt1.id,mtj1.id,NULL
	FROM modele_tournee_jour mtj1
  INNER JOIN modele_tournee mt1 ON mtj1.tournee_id=mt1.id
	INNER JOIN groupe_tournee gt1 ON mt1.groupe_id=gt1.id
  INNER JOIN modele_tournee_jour mtj2 ON mtj1.employe_id=mtj2.employe_id and mtj1.jour_id=mtj2.jour_id and mtj1.id<>mtj2.id
  INNER JOIN modele_tournee mt2 ON mtj2.tournee_id=mt2.id
	WHERE mt1.actif
  AND mt2.actif
	AND NOW()<=mtj1.date_fin
	AND NOW()<=mtj2.date_fin
	AND (gt1.depot_id=_depot_id OR _depot_id IS NULL)
	AND (gt1.flux_id=_flux_id OR _flux_id IS NULL)
--  AND (mt.id=_tournee_id OR _tournee_id IS NULL)
--  AND (mtj.id=_tournee_jour_id OR _tournee_jour_id IS NULL)
  AND (mtj1.date_fin>=mtj2.date_debut and mtj1.date_debut<=mtj2.date_fin or mtj2.date_fin>=mtj1.date_debut and mtj2.date_debut<=mtj1.date_fin)
	AND (mtj1.id=_tournee_jour_id OR mtj2.id=_tournee_jour_id OR _tournee_jour_id IS NULL)
  AND mtj1.ordre=mtj2.ordre
	;
  call mod_valide_log('mod_valide_chevauchement_tournee','Plusieurs tournees avec le meme numero d''ordre');
  */
  END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS mod_valide_chevauchement_activite;
CREATE PROCEDURE `mod_valide_chevauchement_activite`(INOUT _validation_id INT, IN _depot_id INT, IN _flux_id INT, IN _activite_id INT)

BEGIN
	IF (_validation_id IS NULL) THEN
		INSERT INTO modele_validation(utilisateur_id) VALUES(1);
		SELECT LAST_INSERT_ID() INTO _validation_id;
	END IF
	;
/*  CALL mod_delete_activite('HO', _depot_id, _flux_id, _activite_id);
  CALL mod_valide_chevauchement(_validation_id, _depot_id, _flux_id, null, null, _activite_id);*/
  CALL mod_delete_activite('HO', _depot_id, _flux_id, null);
  CALL mod_valide_chevauchement(_validation_id, _depot_id, _flux_id, null, null, null);
  
  -- Si le chevauchement ne concerne pas l'activité, on le met sur validation_id=0 pour ne pas polluer l'affichage
	INSERT INTO modele_journal(validation_id,depot_id,flux_id,erreur_id,jour_id,employe_id,tournee_id,tournee_jour_id,activite_id)
	SELECT DISTINCT IF(a1.id=_activite_id,_validation_id,0),a1.depot_id,a1.flux_id,17,a1.jour_id,a1.employe_id ,NULL,NULL,a1.id
	FROM modele_activite a1
  INNER JOIN modele_activite a2 ON a1.employe_id=a2.employe_id AND a1.jour_id=a2.jour_id
	WHERE a1.id<>a2.id
	AND NOW()<=a1.date_fin
	AND NOW()<=a2.date_fin
	AND (a1.depot_id=_depot_id OR _depot_id IS NULL)
	AND (a1.flux_id=_flux_id OR _flux_id IS NULL)
--  AND (a1.id=_activite_id OR _activite_id IS NULL)
  AND (a1.heure_debut<=a2.heure_debut AND a2.heure_debut<addtime(a1.heure_debut,a1.duree)
	OR   a2.heure_debut<=a1.heure_debut AND a1.heure_debut<addtime(a2.heure_debut,a2.duree))
	;
  call mod_valide_log('mod_valide_chevauchement_activite','Chevauchement activite/activite');
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS mod_valide_cycle;
CREATE PROCEDURE `mod_valide_cycle`(INOUT _validation_id INT, IN _depot_id INT, IN _flux_id INT)
BEGIN
	IF (_validation_id IS NULL) THEN
		INSERT INTO modele_validation(utilisateur_id) VALUES(1);
		SELECT LAST_INSERT_ID() INTO _validation_id;
	END IF
  ;
  CALL mod_delete('CY', _depot_id, _flux_id);

  INSERT INTO modele_journal(validation_id,depot_id,flux_id,erreur_id,jour_id,employe_id,tournee_id,tournee_jour_id,activite_id,commentaire)
  select _validation_id,epd.depot_id,epd.flux_id,21,null,epd.employe_id ,null,null,NULL
  ,concat(cycle_to_string(ec.lundi,ec.mardi,ec.mercredi,ec.jeudi,ec.vendredi,ec.samedi,ec.dimanche),' <> ',coalesce(tmp.cycle_modele,'-------'))
  from emp_pop_depot epd
  left outer join emp_cycle ec on epd.employe_id=ec.employe_id and NOW() BETWEEN ec.date_debut AND ec.date_fin 
  left outer join ( select epd.employe_id,cycle_to_string(
              exists(select null from modele_tournee_jour mtj INNER JOIN modele_tournee mt ON mtj.tournee_id=mt.id where mtj.jour_id=2 and mtj.employe_id=epd.employe_id and now() between mtj.date_debut and mtj.date_fin and mt.actif) or exists(select null from modele_activite ma where ma.jour_id=2 and ma.employe_id=epd.employe_id and now() between ma.date_debut and ma.date_fin)
            , exists(select null from modele_tournee_jour mtj INNER JOIN modele_tournee mt ON mtj.tournee_id=mt.id where mtj.jour_id=3 and mtj.employe_id=epd.employe_id and now() between mtj.date_debut and mtj.date_fin and mt.actif) or exists(select null from modele_activite ma where ma.jour_id=3 and ma.employe_id=epd.employe_id and now() between ma.date_debut and ma.date_fin)
            , exists(select null from modele_tournee_jour mtj INNER JOIN modele_tournee mt ON mtj.tournee_id=mt.id where mtj.jour_id=4 and mtj.employe_id=epd.employe_id and now() between mtj.date_debut and mtj.date_fin and mt.actif) or exists(select null from modele_activite ma where ma.jour_id=4 and ma.employe_id=epd.employe_id and now() between ma.date_debut and ma.date_fin)
            , exists(select null from modele_tournee_jour mtj INNER JOIN modele_tournee mt ON mtj.tournee_id=mt.id where mtj.jour_id=5 and mtj.employe_id=epd.employe_id and now() between mtj.date_debut and mtj.date_fin and mt.actif) or exists(select null from modele_activite ma where ma.jour_id=5 and ma.employe_id=epd.employe_id and now() between ma.date_debut and ma.date_fin)
            , exists(select null from modele_tournee_jour mtj INNER JOIN modele_tournee mt ON mtj.tournee_id=mt.id where mtj.jour_id=6 and mtj.employe_id=epd.employe_id and now() between mtj.date_debut and mtj.date_fin and mt.actif) or exists(select null from modele_activite ma where ma.jour_id=6 and ma.employe_id=epd.employe_id and now() between ma.date_debut and ma.date_fin)
            , exists(select null from modele_tournee_jour mtj INNER JOIN modele_tournee mt ON mtj.tournee_id=mt.id where mtj.jour_id=7 and mtj.employe_id=epd.employe_id and now() between mtj.date_debut and mtj.date_fin and mt.actif) or exists(select null from modele_activite ma where ma.jour_id=7 and ma.employe_id=epd.employe_id and now() between ma.date_debut and ma.date_fin)
            , exists(select null from modele_tournee_jour mtj INNER JOIN modele_tournee mt ON mtj.tournee_id=mt.id where mtj.jour_id=1 and mtj.employe_id=epd.employe_id and now() between mtj.date_debut and mtj.date_fin and mt.actif) or exists(select null from modele_activite ma where ma.jour_id=1 and ma.employe_id=epd.employe_id and now() between ma.date_debut and ma.date_fin)
            ) as cycle_modele
            from emp_pop_depot epd
            WHERE NOW() BETWEEN epd.date_debut AND epd.date_fin
	          AND epd.typetournee_id in (0,1,2)
          	AND (epd.depot_id=_depot_id OR _depot_id IS NULL)
          	AND (epd.flux_id=_flux_id OR _flux_id IS NULL)
            ) as tmp on tmp.employe_id=epd.employe_id
  WHERE NOW() BETWEEN epd.date_debut AND epd.date_fin
  and cycle_to_string(ec.lundi,ec.mardi,ec.mercredi,ec.jeudi,ec.vendredi,ec.samedi,ec.dimanche)<>coalesce(tmp.cycle_modele,'')
	AND epd.typetournee_id in (0,1,2)
	AND (epd.depot_id=_depot_id OR _depot_id IS NULL)
	AND (epd.flux_id=_flux_id OR _flux_id IS NULL);
  call mod_valide_log('mod_valide_rh_tournee_jour','Cycle employé différent de cycle modèle');
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS mod_valide_pleiades;
CREATE PROCEDURE `mod_valide_pleiades`(INOUT _validation_id INT, IN _depot_id INT, IN _flux_id INT)
BEGIN
	IF (_validation_id IS NULL) THEN
		INSERT INTO modele_validation(utilisateur_id) VALUES(1);
		SELECT LAST_INSERT_ID() INTO _validation_id;
	END IF
	;
  CALL mod_valide_rh(_validation_id, _depot_id, _flux_id);
  CALL mod_valide_cycle(_validation_id, _depot_id, _flux_id);
END;


-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
