/*
set @validation_id=NULL;
call emp_valide_rh(@validation_id,null,null);
*/
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS emp_valide_delete;
CREATE PROCEDURE `emp_valide_delete`(IN _rubrique varchar(2), IN _depot_id INT, IN _flux_id INT)
BEGIN
	DELETE ej
  FROM emp_journal ej
  INNER JOIN emp_ref_erreur ere on ej.erreur_id=ere.id
	WHERE ere.rubrique=_rubrique
	AND (ej.depot_id=_depot_id OR _depot_id IS NULL)
	AND (ej.flux_id=_flux_id OR _flux_id IS NULL)
  ;
  call emp_valide_logger('emp_valide_delete', 'emp_valide_delete');
END;
/*
1	VE	ASS	6	Fin de validité d'assurance	1	#F5D5A2
2	VE	TEC	6	Fin de validité de contrôle technique	1	#F5D5A2
3	VE	AS2	9	Fin de validité d'assurance dans les 30 jours	1	#FFFFFF
4	VE	TE2	9	Fin de validité de contrôle technique dans les 30 jours	1	#FFFFFF
5	SE	SEJ	5	Fin de validité de la carte de séjour	1	#F5D5A2
6	SE	SE2	9	Fin de validité de la carte de séjour dans les 30 jours	1	#FFFFFF
7	VM	VME	5	Visite médicale à effectuer	1	#F5D5A2
8	VM	VM2	9	Visite médicale à effectuer dans les 30 jours	1	#FFFFFF
*/
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS emp_valide_vehicule;
CREATE PROCEDURE `emp_valide_vehicule`(INOUT _validation_id INT, IN _depot_id INT, IN _flux_id INT)
BEGIN
  SET lc_time_names = 'fr_FR';
	IF (_validation_id IS NULL) THEN
		INSERT INTO pai_validation(utilisateur_id) VALUES(1);
		SELECT LAST_INSERT_ID() INTO _validation_id;
    call emp_valide_logger('EMP_VALIDE_VEHICULE',concat_ws('*',_validation_id,_depot_id,_flux_id));
	END IF;
  
  CALL emp_valide_delete('VE', _depot_id, _flux_id);
	
  -- Fin validité assurance
	INSERT INTO emp_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,commentaire)
	SELECT DISTINCT if(v.dtfinassur<SYSDATE(),1,3),_validation_id,epd.depot_id,epd.flux_id,m.anneemois,m.date_debut,e.id,DATE_FORMAT(v.dtfinassur,'%d/%m/%Y')
  -- CONCAT_WS(' ','Fin validité assurance le',coalesce(DATE_FORMAT(v.dtfinassur,'%d %M %Y'),'???'),'pour',e.nom,e.prenom1,e.prenom2)
	FROM emp_pop_depot epd
	INNER JOIN employe e ON epd.employe_id=e.id
	INNER JOIN pai_png_vehiculew v ON e.saloid=v.salarie
	INNER JOIN pai_ref_mois m ON CURDATE() between m.date_debut and m.date_fin
	WHERE (epd.depot_id=_depot_id OR _depot_id IS NULL)
	AND (epd.flux_id=_flux_id OR _flux_id IS NULL)
  AND epd.date_fin>=m.date_debut
	AND v.begin_date<=m.date_fin AND  m.date_debut<=v.end_date
  AND v.dtfinassur<DATE_ADD(SYSDATE(),INTERVAL 1 MONTH)
	;
  call emp_valide_logger('emp_valide_vehicule', 'Fin validite assurance');
  
  -- Fin validité de contrôle technique
	INSERT INTO emp_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,commentaire)
	SELECT DISTINCT if(v.dtvaliditect<SYSDATE(),2,4),_validation_id,epd.depot_id,epd.flux_id,m.anneemois,m.date_debut,e.id,DATE_FORMAT(v.dtvaliditect,'%d/%m/%Y')
  -- CONCAT_WS(' ','Fin validité de contrôle technique le',coalesce(DATE_FORMAT(v.dtvaliditect,'%d %M %Y'),'???'),'pour',e.nom,e.prenom1,e.prenom2),
	FROM emp_pop_depot epd
	INNER JOIN employe e ON epd.employe_id=e.id
	INNER JOIN pai_png_vehiculew v ON e.saloid=v.salarie
	INNER JOIN pai_ref_mois m ON CURDATE() between m.date_debut and m.date_fin
	WHERE (epd.depot_id=_depot_id OR _depot_id IS NULL)
	AND (epd.flux_id=_flux_id OR _flux_id IS NULL)
  AND epd.date_fin>=m.date_debut
	AND v.begin_date<=m.date_fin AND  m.date_debut<=v.end_date
  AND v.dtvaliditect<DATE_ADD(SYSDATE(),INTERVAL 1 MONTH)
	;
  call emp_valide_logger('emp_valide_vehicule', 'Fin validite de contrôle technique');
END; 
 
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS emp_valide_sejour;
CREATE PROCEDURE `emp_valide_sejour`(INOUT _validation_id INT, IN _depot_id INT, IN _flux_id INT)
BEGIN
  SET lc_time_names = 'fr_FR';
	IF (_validation_id IS NULL) THEN
		INSERT INTO pai_validation(utilisateur_id) VALUES(1);
		SELECT LAST_INSERT_ID() INTO _validation_id;
    call emp_valide_logger('EMP_VALIDE_SEJOUR',concat_ws('*',_validation_id,_depot_id,_flux_id));
	END IF;
  
  CALL emp_valide_delete('SE', _depot_id, _flux_id);
	
  -- Fin validité de la carte de séjour
	INSERT INTO emp_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,commentaire)
	SELECT DISTINCT if(se.end_date<SYSDATE(),5,6),_validation_id,epd.depot_id,epd.flux_id,m.anneemois,m.date_debut,e.id,DATE_FORMAT(se.end_date,'%d/%m/%Y')
	FROM employe e
  INNER JOIN ref_nationalite rn on e.nationalite_id=rn.id
  INNER JOIN emp_pop_depot epd ON epd.employe_id=e.id
	INNER JOIN pai_ref_mois m ON CURDATE() between m.date_debut and m.date_fin
  LEFT OUTER JOIN pai_png_saletranger se ON e.saloid=se.etrangmatricule and se.end_date in (select max(se2.end_date) from pai_png_saletranger se2 where e.saloid=se2.etrangmatricule)
	WHERE (epd.depot_id=_depot_id OR _depot_id IS NULL)
	AND (epd.flux_id=_flux_id OR _flux_id IS NULL)
  AND epd.date_fin>=m.date_debut
  AND rn.appartenanceue=0 and rn.code not in ('IS','NO','LI','CH')
	AND coalesce(se.end_date,'2000-01-01')<DATE_ADD(SYSDATE(),INTERVAL 1 MONTH)
	;
  call emp_valide_logger('emp_valide_sejour', 'Fin validite de la carte de sejour');
  
  -- Fin validité de la carte de séjour VCP
	INSERT INTO emp_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,commentaire)
	SELECT DISTINCT if(e.sejour_date<SYSDATE(),5,6),_validation_id,epd.depot_id,epd.flux_id,m.anneemois,m.date_debut,e.id ,DATE_FORMAT(e.sejour_date,'%d/%m/%Y')
	FROM employe e
  INNER JOIN emp_pop_depot epd ON epd.employe_id=e.id
	INNER JOIN pai_ref_mois m ON CURDATE() between m.date_debut and m.date_fin
	WHERE (epd.depot_id=_depot_id OR _depot_id IS NULL)
	AND (epd.flux_id=_flux_id OR _flux_id IS NULL)
  AND epd.date_fin>=m.date_debut
	AND e.sejour_date<DATE_ADD(SYSDATE(),INTERVAL 1 MONTH)
	;
  call pai_valide_logger('pai_valide_rh_sejour', 'Fin validite de la carte de sejour VCP');
END; 

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS emp_valide_medical;
CREATE PROCEDURE `emp_valide_medical`(INOUT _validation_id INT, IN _depot_id INT, IN _flux_id INT)
BEGIN
  SET lc_time_names = 'fr_FR';
	IF (_validation_id IS NULL) THEN
		INSERT INTO pai_validation(utilisateur_id) VALUES(1);
		SELECT LAST_INSERT_ID() INTO _validation_id;
    call emp_valide_logger('EMP_VALIDE_MEDICAL',concat_ws('*',_validation_id,_depot_id,_flux_id));
	END IF;
  
  CALL emp_valide_delete('VM', _depot_id, _flux_id);
	
  -- Fin validité de la carte de séjour
	INSERT INTO emp_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,commentaire)
	SELECT DISTINCT if(ppm.medicprochain<SYSDATE(),7,8),_validation_id,epd.depot_id,epd.flux_id,m.anneemois,m.date_debut,e.id,DATE_FORMAT(ppm.medicprochain,'%d/%m/%Y')
	FROM employe e
  INNER JOIN emp_pop_depot epd ON epd.employe_id=e.id
	INNER JOIN pai_ref_mois m ON CURDATE() between m.date_debut and m.date_fin
  LEFT OUTER JOIN pai_png_medicalvisite ppm ON e.saloid=ppm.medicmatricule and ppm.medicprochain in (select max(ppm2.medicprochain) from pai_png_medicalvisite ppm2 where e.saloid=ppm2.medicmatricule)
	WHERE (epd.depot_id=_depot_id OR _depot_id IS NULL)
	AND (epd.flux_id=_flux_id OR _flux_id IS NULL)
  AND epd.date_fin>=m.date_debut
	AND coalesce(ppm.medicprochain,'2000-01-01')<DATE_ADD(SYSDATE(),INTERVAL 1 MONTH)
	;
  call emp_valide_logger('emp_valide_medical', 'Fin validite de la visite médicale');
END; 

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS emp_valide_rh;
CREATE PROCEDURE `emp_valide_rh`(INOUT _validation_id INT, IN _depot_id INT, IN _flux_id INT)
BEGIN
  SET lc_time_names = 'fr_FR';
	IF (_validation_id IS NULL) THEN
		INSERT INTO pai_validation(utilisateur_id) VALUES(1);
		SELECT LAST_INSERT_ID() INTO _validation_id;
    call emp_valide_logger('EMP_VALIDE_MEDICAL',concat_ws('*',_validation_id,_depot_id,_flux_id));
	END IF;
  
	call emp_valide_vehicule(_validation_id, _depot_id, _flux_id);
	call emp_valide_sejour(_validation_id, _depot_id, _flux_id);
	call emp_valide_medical(_validation_id, _depot_id, _flux_id);
END; 

