ALTER TABLE ReferentialTour$ CONVERT TO CHARACTER SET utf8 COLLATE 'utf8_unicode_ci';

ALTER TABLE `ReferentialTour$` ADD societe_id INT;
ALTER TABLE `ReferentialTour$` ADD depot_id INT;
ALTER TABLE `ReferentialTour$` ADD flux_id INT;
ALTER TABLE `ReferentialTour$` ADD jour_id INT;
ALTER TABLE `ReferentialTour$` ADD transport_id INT;
ALTER TABLE `ReferentialTour$` ADD groupe VARCHAR(2);
ALTER TABLE `ReferentialTour$` ADD heure_debut TIME;
ALTER TABLE `ReferentialTour$` ADD groupe_id INT;
ALTER TABLE `ReferentialTour$` ADD employe_id INT;
ALTER TABLE `ReferentialTour$` ADD fluxCode VARCHAR(1);
ALTER TABLE `ReferentialTour$` ADD numero VARCHAR(3);
ALTER TABLE `ReferentialTour$` ADD codeDCS VARCHAR(3);
ALTER TABLE `ReferentialTour$` ADD tournee_id INT;

UPDATE `ReferentialTour$` SET societe_id=2 WHERE soc='PAP';
UPDATE `ReferentialTour$` SET societe_id=3 WHERE societe_id IS NULL;

UPDATE `ReferentialTour$` SET depot_id=11,flux_id=2,fluxcode='J' WHERE depot='Arcueil';
UPDATE `ReferentialTour$` SET depot_id=24,flux_id=2,fluxcode='J' WHERE depot='Bercy - Jour';
UPDATE `ReferentialTour$` SET depot_id=24,flux_id=1,fluxcode='N' WHERE depot='Bercy - Nuit';
UPDATE `ReferentialTour$` SET depot_id=20,flux_id=2,fluxcode='J' WHERE depot='La Chapelle - Jour';
UPDATE `ReferentialTour$` SET depot_id=20,flux_id=1,fluxcode='N' WHERE depot='La Chapelle - Nuit';

UPDATE `ReferentialTour$` SET jour_id=1 WHERE jour='Dimanche';
UPDATE `ReferentialTour$` SET jour_id=2 WHERE jour='Lundi';
UPDATE `ReferentialTour$` SET jour_id=3 WHERE jour='Mardi';
UPDATE `ReferentialTour$` SET jour_id=4 WHERE jour='Mercredi';
UPDATE `ReferentialTour$` SET jour_id=5 WHERE jour='Jeudi';
UPDATE `ReferentialTour$` SET jour_id=6 WHERE jour='Vendredi';
UPDATE `ReferentialTour$` SET jour_id=7 WHERE jour='Samedi';

UPDATE `ReferentialTour$` SET transport_id=1 WHERE transport='P�destre / V�lo';
UPDATE `ReferentialTour$` SET transport_id=2 WHERE transport='2 roues motoris�';
UPDATE `ReferentialTour$` SET transport_id=3 WHERE transport='4 roues motoris�';

SELECT DISTINCT depot_id,flux_id,heure_debut FROM `ReferentialTour$` ORDER BY depot_id,flux_id,heure_debut;

-- UPDATE `ReferentialTour$` SET groupe='KH' WHERE depot_id='11' AND flux_id='2' AND heure_debut='13:00';
UPDATE `ReferentialTour$` SET heure_debut='14:15' WHERE heure_debut='14:16';
UPDATE `ReferentialTour$` SET heure_debut='14:00' WHERE heure_debut='14:01';
UPDATE `ReferentialTour$` SET groupe='KI' WHERE depot_id='11' AND flux_id='2' AND heure_debut='14:00';
UPDATE `ReferentialTour$` SET groupe='KJ' WHERE depot_id='11' AND flux_id='2' AND heure_debut='14:30';
UPDATE `ReferentialTour$` SET groupe='T1' WHERE depot_id='20' AND flux_id='1' AND heure_debut='03:00';
UPDATE `ReferentialTour$` SET groupe='T2' WHERE depot_id='20' AND flux_id='1' AND heure_debut='03:15';
UPDATE `ReferentialTour$` SET groupe='T3' WHERE depot_id='20' AND flux_id='1' AND heure_debut='03:30';
UPDATE `ReferentialTour$` SET groupe='T4' WHERE depot_id='20' AND flux_id='1' AND heure_debut='04:00';
UPDATE `ReferentialTour$` SET groupe='TA' WHERE depot_id='20' AND flux_id='2' AND heure_debut='11:00';
UPDATE `ReferentialTour$` SET groupe='TB' WHERE depot_id='20' AND flux_id='2' AND heure_debut='13:00';
UPDATE `ReferentialTour$` SET groupe='TC' WHERE depot_id='20' AND flux_id='2' AND heure_debut='13:30';
UPDATE `ReferentialTour$` SET groupe='TE' WHERE depot_id='20' AND flux_id='2' AND heure_debut='14:00';
UPDATE `ReferentialTour$` SET groupe='TF' WHERE depot_id='20' AND flux_id='2' AND heure_debut='14:15';
UPDATE `ReferentialTour$` SET groupe='TG' WHERE depot_id='20' AND flux_id='2' AND heure_debut='14:30';
UPDATE `ReferentialTour$` SET groupe='TH' WHERE depot_id='20' AND flux_id='2' AND heure_debut='15:00';
UPDATE `ReferentialTour$` SET groupe='XB' WHERE depot_id='24' AND flux_id='1' AND heure_debut='01:30';
UPDATE `ReferentialTour$` SET groupe='XC' WHERE depot_id='24' AND flux_id='1' AND heure_debut='03:00';
UPDATE `ReferentialTour$` SET groupe='XD' WHERE depot_id='24' AND flux_id='1' AND heure_debut='03:30';
UPDATE `ReferentialTour$` SET groupe='XE' WHERE depot_id='24' AND flux_id='1' AND heure_debut='04:00';
UPDATE `ReferentialTour$` SET groupe='XF' WHERE depot_id='24' AND flux_id='2' AND heure_debut='09:00';
UPDATE `ReferentialTour$` SET groupe='XG' WHERE depot_id='24' AND flux_id='2' AND heure_debut='12:00';
UPDATE `ReferentialTour$` SET groupe='XH' WHERE depot_id='24' AND flux_id='2' AND heure_debut='12:30';
UPDATE `ReferentialTour$` SET groupe='XI' WHERE depot_id='24' AND flux_id='2' AND heure_debut='13:00';
UPDATE `ReferentialTour$` SET groupe='XJ' WHERE depot_id='24' AND flux_id='2' AND heure_debut='13:30';
UPDATE `ReferentialTour$` SET groupe='XK' WHERE depot_id='24' AND flux_id='2' AND heure_debut='14:00';
UPDATE `ReferentialTour$` SET groupe='XL' WHERE depot_id='24' AND flux_id='2' AND heure_debut='14:30';
UPDATE `ReferentialTour$` SET groupe='XM' WHERE depot_id='24' AND flux_id='2' AND heure_debut='15:00';
SELECT * FROM `ReferentialTour$`  WHERE depot_id IS NULL OR flux_id IS NULL OR groupe IS NULL OR heure_debut IS NULL;
SELECT DISTINCT depot_id,flux_id,heure_debut FROM `ReferentialTour$` WHERE groupe IS NULL;

-- numerotation des tournees
DELIMITER |
DROP PROCEDURE IF EXISTS tmp_neo_numero|
CREATE PROCEDURE tmp_neo_numero()
BEGIN
	DECLARE done INT DEFAULT 0;
	DECLARE l_name VARCHAR(255);
	DECLARE b VARCHAR(255);
	DECLARE a INT;
	DECLARE _numero INT;
	DECLARE _groupe_id INT;
	DECLARE cur1 CURSOR FOR SELECT DISTINCT groupe_id,tournee FROM ReferentialTour$ ORDER BY groupe_id,tournee;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

	SET _groupe_id=0;
	OPEN cur1;
	read_loop: LOOP
		FETCH cur1 INTO a, b;
		IF done=1 THEN
			LEAVE read_loop;
		END IF;
		IF _groupe_id<>a THEN
			SET _numero=0;
			SET _groupe_id=a;
		END IF;
		SET _numero=_numero+1;
		UPDATE ReferentialTour$ r SET numero=LPAD(_numero,3,'0') WHERE r.groupe_id=a AND r.tournee=b;
	END LOOP read_loop;
	CLOSE cur1;
	SELECT nom INTO l_name FROM ReferentialTour$ LIMIT 1;
END|
DROP PROCEDURE IF EXISTS tmp_neo_codeDCS|
CREATE PROCEDURE tmp_neo_codeDCS()
BEGIN
	DECLARE done INT DEFAULT 0;
	DECLARE l_name VARCHAR(255);
	DECLARE b VARCHAR(255);
	DECLARE a INT;
	DECLARE _numero INT;
	DECLARE _depot_id INT;
	DECLARE cur1 CURSOR FOR SELECT DISTINCT depot_id,tournee FROM ReferentialTour$ ORDER BY depot_id,tournee;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

	SET _depot_id=0;
	OPEN cur1;
	read_loop: LOOP
		FETCH cur1 INTO a, b;
		IF done=1 THEN
			LEAVE read_loop;
		END IF;
		IF _depot_id<>a THEN
			SET _numero=0;
			SET _depot_id=a;
		END IF;
		SET _numero=_numero+1;
		IF _depot_id<>24 THEN
			IF _numero<100 THEN
				UPDATE ReferentialTour$ r SET codeDCS=CONCAT(SUBSTR(LPAD(_numero,2,'0'),1,1),CHAR(r.depot_id+64),SUBSTR(LPAD(_numero,2,'0'),2,1)) WHERE r.depot_id=a AND r.tournee=b;
			ELSE
				UPDATE ReferentialTour$ r SET codeDCS=CONCAT(SUBSTR(LPAD(_numero,3,'0'),2,2),CHAR(r.depot_id+64)) WHERE r.depot_id=a AND r.tournee=b;
			END IF;
		ELSE
			IF _numero<100 THEN
				UPDATE ReferentialTour$ r SET codeDCS=CONCAT(CHAR(r.depot_id+64),LPAD(_numero,2,'0')) WHERE r.depot_id=a AND r.tournee=b;
			ELSE
				UPDATE ReferentialTour$ r SET codeDCS=CONCAT(SUBSTR(LPAD(_numero,3,'0'),2,2),CHAR(r.depot_id+64)) WHERE r.depot_id=a AND r.tournee=b;
			END IF;
		END IF;
	END LOOP read_loop;
	CLOSE cur1;
	SELECT nom INTO l_name FROM ReferentialTour$ LIMIT 1;
END|
DELIMITER ;
CALL tmp_neo_numero();
CALL tmp_neo_codeDCS();
  

-- Nettoyage
UPDATE client_a_servir_logist SET tournee_jour_id=NULL,pai_tournee_id=NULL WHERE tournee_jour_id IS NOT NULL OR pai_tournee_id IS NOT NULL;
DELETE FROM modele_journal WHERE employe_id IN (SELECT id FROM employe WHERE matricule=saloid);
DELETE FROM modele_tournee_jour WHERE date_creation='2014-08-02';
DELETE FROM modele_tournee_jour WHERE date_creation='2014-08-02';
DELETE FROM modele_tournee WHERE date_creation='2014-08-02';
DELETE FROM groupe_tournee WHERE date_creation='2014-08-02';
DELETE FROM pai_journal;
DELETE FROM pai_incident;
DELETE FROM pai_prd_tournee;
DELETE FROM pai_tournee;
-- ATTENTION, faire la transco des employes !!!!!!!!!!!!!!!!!

-- creation des dépots dans mroad
INSERT INTO depot(commune_id,CODE,libelle,adresse,date_debut) VALUE(698,'042','CD42 BERCY','','2014-06-01');

-- creation des employés dans mroad
DELETE FROM employe WHERE matricule=saloid;
UPDATE ReferentialTour$ r set employe_id=null;

INSERT INTO employe(nom,prenom1,matricule,saloid) SELECT DISTINCT nom, prenom, concat(case when soc='Mediapresse' then 'ME' else 'NE' end,mat,case when soc='Mediapresse' then '20' else '00' end),concat(case when soc='Mediapresse' then 'ME' else 'NE' end,mat,case when soc='Mediapresse' then '20' else '00' end) FROM `ReferentialTour$` WHERE mat IS NOT NULL AND employe_id IS NULL;
UPDATE ReferentialTour$ r 
INNER JOIN employe e
SET r.employe_id=e.id
WHERE e.matricule=concat(case when soc='Mediapresse' then 'ME' else 'NE' end,r.mat,case when soc='Mediapresse' then '20' else '00' end)
;
SELECT distinct tournee,mat,nom,prenom FROM ReferentialTour$ WHERE employe_id IS NULL;
SELECT * from employe where saloid=matricule;

-- creation des groupes dans mroad
INSERT INTO groupe_tournee(depot_id,flux_id,utilisateur_id,CODE,libelle,heure_debut,date_creation)
SELECT DISTINCT depot_id,flux_id,0,groupe,'Neo/Media',heure_debut,'2014-08-02'
FROM ReferentialTour$
;

UPDATE ReferentialTour$ r 
INNER JOIN groupe_tournee g 
SET r.groupe_id=g.id
WHERE r.depot_id=g.depot_id AND r.flux_id=g.flux_id AND TRIM(r.groupe)=TRIM(g.code)
;
SELECT * FROM ReferentialTour$ WHERE groupe_id IS NULL;
SELECT * FROM groupe_tournee WHERE date_creation='2014-08-02';

-- creation des tournees dans mroad
delete from modele_tournee where date_creation='2014-08-02';
INSERT INTO modele_tournee(groupe_id,utilisateur_id,date_creation,CODE,actif,typetournee_id,employe_id,numero,codeDCS,libelle,valide)
SELECT DISTINCT r.groupe_id,0,'2014-08-02',CONCAT(d.code,fluxcode,r.groupe,r.numero),1,2,r.employe_id,r.numero,codeDCS,tournee,1
FROM ReferentialTour$ r
INNER JOIN depot d ON r.depot_id=d.id
;
UPDATE ReferentialTour$ r 
INNER JOIN modele_tournee t
INNER JOIN depot d ON r.depot_id=d.id
SET r.tournee_id=t.id
WHERE t.code=CONCAT(d.code,fluxcode,r.groupe,r.numero)
;
SELECT * FROM ReferentialTour$ WHERE tournee_id IS NULL;

delete from modele_journal where tournee_jour_id in (SELECT id FROM modele_tournee_jour where date_creation='2014-08-02');
DELETE FROM modele_tournee_jour where date_creation='2014-08-02';
INSERT INTO modele_tournee_jour(tournee_id,jour_id,employe_id,transport_id,date_debut,date_fin,valrem,duree,dureeHP,nbkm,nbkm_paye,utilisateur_id,date_creation,valide,CODE,duree_geo,dureehp_debut_geo,dureehp_fin_geo,qte_geo,nbcli_geo,nbkm_geo,nbkm_hp_debut_geo,nbkm_hp_fin_geo,nbadr_geo)
SELECT tournee_id,jour_id,employe_id,transport_id,'2014-06-01','2999-01-01',valremabo,SEC_TO_TIME(duree*60),SEC_TO_TIME(duree_debut_hp*60),nbkm+nbkm_hp,nbkm+nbkm_hp,0,'2014-08-02',1,CONCAT(d.code,fluxcode,r.groupe,r.numero,SUBSTR(UPPER(jour),1,2)),SEC_TO_TIME(duree*60),SEC_TO_TIME(duree_debut_hp*60),SEC_TO_TIME(duree_fin_hp*60),qte,nbcli,nbkm,nbkm_hp,nbkm_fin_hp,nbadr
FROM ReferentialTour$ r
INNER JOIN depot d ON r.depot_id=d.id
;

SELECT d.code AS "Code Débôt",d.libelle AS "Libellé dépôt",g.code AS "Code groupe",g.heure_debut AS "Heure début" FROM groupe_tournee g
INNER JOIN depot d ON g.depot_id=d.id
WHERE date_creation='2014-08-02';


SELECT DISTINCT tournee AS "Ancien code",CONCAT(d.code,fluxcode,r.groupe,r.numero) AS "Nouveau code",codeDCS AS "Code DCS",nom FROM `ReferentialTour$` r INNER JOIN depot d ON r.depot_id=d.id;
/*  
select * from modele_tournee_jour where tournee_id=3050;
SELECT * FROM client_a_servir_logist WHERE tournee_jour_id>=5547;
SELECT * FROM client_a_servir_logist WHERE tournee_jour_id is null;
desc ReferentialTour$;
SELECT * FROM `ReferentialTour$` where nom='LANCRIN';
select * from `ReferentialTour$` ;
SELECT * FROM `ReferentialTour$` WHERE mat='N01891';
SELECT * FROM `ReferentialTour$` WHERE mat is null or mat='';
SELECT * FROM depot;
select * from employe;
delete FROM employe where id>=1630;
select * from pai_png_etablissement;
*/
SELECT * FROM `ReferentialTour$` WHERE flux_id=1;
SELECT * FROM depot





select distinct soc from ReferentialTour$;
select distinct soc,mat,nom from ReferentialTour$ r
where exists(select null from employe e where concat(case when soc='Mediapresse' then 'ME' else 'NE' end,r.mat,case when soc='Mediapresse' then '20' else '00' end)=e.matricule);

delete from emp_pop_depot;
delete from employe where matricule like 'ME%' or matricule like 'NE%';
update employe e
inner join ReferentialTour$ r on e.matricule=r.mat
set matricule=concat(case when soc='Mediapresse' then 'ME' else 'NE' end,r.mat,case when soc='Mediapresse' then '20' else '00' end)
, saloid=concat(case when soc='Mediapresse' then 'ME' else 'NE' end,r.mat,case when soc='Mediapresse' then '20' else '00' end);

select count(*) from employe e where matricule=saloid;
select count(*) from employe e where matricule like 'ME%' or matricule like 'NE%';
select substr(matricule,9,2),e.* from employe e where matricule like 'ME%' and substr(matricule,9,2)<>'20';
select substr(matricule,9,2),e.* from employe e where matricule like 'NE%' and substr(matricule,9,2)<>'00';
delete from emp_pop_depot;
select * from employe where matricule like 'NE%' and substr(matricule,9,2)<>'00';
select * from employe where matricule like 'ME%' and substr(matricule,9,2)<>'20';
select * from modele_tournee mt inner join employe e on mt.employe_id=e.id  where employe_id in (select id from employe where matricule=saloid);
delete from employe where matricule=saloid;
select * from employe where nom='ZEROUROU';

select * from employe;
select * from modele_tournee mt
inner join employe e on mt.employe_id=e.id and e.matricule like 'NE%' and e.saloid=e.matricule
inner join employe e2 on substr(e.matricule,1,8)=substr(e2.matricule,1,8) and e2.saloid<>e2.matricule;

select * from modele_tournee where employe_id  in (5734,6087);
select * from emp_pop_depot where employe_id in (5734,6087);

select * from employe where nom in (select nom from employe where matricule=saloid) order by matricule;
select * from employe where matricule=saloid;
select * from modele_tournee where employe_id in (select id  from employe where matricule=saloid);
delete from employe where id=5934;
delete from pai_journal where employe_id in (select id  from employe where matricule=saloid);
delete from modele_journal where employe_id in (select id  from employe where matricule=saloid);
select id  from employe where matricule=saloid and id not in (select distinct employe_id from modele_tournee_jour);

