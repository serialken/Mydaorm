/*
select * from depot

SET @idtrt=null;
CALL INT_PNG2MROAD(0,@idtrt,null,null);
SET @idtrt=null;
CALL INT_PNG2MROAD(0,@idtrt,24,1);
select @idtrt
SET @idtrt=1;
call int_png2mroad_maj_emp_contrat_type(@idtrt,null,null,null);
call int_png2mroad_maj_emp_pop_depot(@idtrt,null,null,null);
SET @idtrt=1;
call int_png2mroad_maj_remplacement(@idtrt,null,null,null);
select * from pai_int_log where idtrt in(select max(id) from pai_int_traitement) order by id desc;
select * from pai_int_log where idtrt=1 order by id desc;
select * from pai_int_traitement order by id desc;
SHOW WARNINGS
select * from v_employe where matricule='Z004967800 '
select * from emp_pop_depot where employe_id=674
  select *  from pai_int_traitement where statut='C' and (typetrt in ('PNG2MROAD','ALIM_EMPLOYE') OR typetrt like 'GENERE_PLEIADES_%');
  update pai_int_traitement set statut='E' where id=7986
  
  kill 184706;
    call mod_valide_rh(@validation_id,null,null);

select * from  emp_pop_depot 
where cycle='' and societe_id>0;
select * from  emp_cycle 
where cycle='';
update emp_pop_depot 
set cycle=cycle_to_string(lundi,mardi,mercredi,jeudi,vendredi,samedi,dimanche)
where cycle='';
update emp_cycle
set cycle=cycle_to_string(lundi,mardi,mercredi,jeudi,vendredi,samedi,dimanche)
where cycle='';
select * from emp_contrat_type

set @validation_id=null;
  call emp_valide_rh(@validation_id,NULL,2);
  call mod_valide_rh(@validation_id,NULL,2);

  CALL pai_valide_rh_activite(@validation_id, NULL, 2, NULL, NULL);
  call recalcul_tournee_date_distrib(null, NULL, 2);

  call pai_valide_pleiades(@validation_id,NULL,2);

*/

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS INT_PNG2MROAD;
CREATE PROCEDURE INT_PNG2MROAD(
  IN 		_utilisateur_id INT,
  INOUT _idtrt		      INT,
  IN    _depot_id		    INT,
  IN    _flux_id		    INT
) BEGIN
declare _validation_id INT;
DECLARE	_batchactif BOOLEAN;
DECLARE _date_int   DATETIME;
DECLARE CONTINUE  HANDLER FOR SQLWARNING    CALL int_logwarning(_idtrt);
DECLARE EXIT      HANDLER FOR SQLEXCEPTION  CALL int_logerreur(_idtrt);
   
  CALL int_logdebut(_utilisateur_id,_idtrt,'PNG2MROAD',null,_depot_id,_flux_id);
  select max(id) into _batchactif from pai_int_traitement where statut='C' and (typetrt in ('PNG2MROAD','ALIM_EMPLOYE') OR typetrt like 'GENERE_PLEIADES_%') and id<>_idtrt;
  if _batchactif is not null then
      call int_loglevel(_idtrt,0,'INT_PNG2MROAD','Une procédure d''alimentation est déjà en cours d''éxécution.');
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Une procédure d''alimentation est déjà en cours d''éxécution.';
  end if;
  select date_debut into _date_int from pai_int_traitement where id=_idtrt;

  CALL int_png2mroad_exec(_idtrt,_depot_id,_flux_id,_date_int);
  
  -- Ajout des activité hors-presse en retro
  call alim_act_delete_activite_hors_presse(_utilisateur_id,_idtrt,_depot_id,_flux_id);
  call alim_act_insert_activite_hors_presse_retro(_utilisateur_id,_idtrt,_depot_id,_flux_id);
  
  -- Nettoyage
  delete pm 
  from pai_majoration pm
  WHERE pm.date_extrait is null
  AND not exists(select null from emp_pop_depot epd where pm.employe_id=epd.employe_id and pm.date_distrib between epd.date_debut and epd.date_fin)
  ;

  call int_logger(_idtrt,'int_png2mroad_exec','Recalcul des tournées');
  call emp_valide_rh(_validation_id,_depot_id,_flux_id);
  call mod_valide_rh(_validation_id,_depot_id,_flux_id);

  CALL pai_valide_rh_activite(_validation_id, _depot_id, _flux_id, NULL, NULL);
  call recalcul_tournee_date_distrib(null, _depot_id, _flux_id);
--  call recalcul_horaire(_validation_id,_depot_id,_flux_id,null,null);

  call pai_valide_pleiades(_validation_id,_depot_id,_flux_id);

  call int_logfin2(_idtrt,'PNG2MROAD');
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_png2mroad_exec;
CREATE PROCEDURE int_png2mroad_exec(
  IN    _idtrt		      INT,
  IN    _depot_id		    INT,
  IN    _flux_id		    INT,
  IN    _date_int		    DATE
) BEGIN
DECLARE EXIT      HANDLER FOR SQLEXCEPTION  BEGIN
  -- call int_png2mroad_drop_temporary_table(_idtrt);
  RESIGNAL;
END;
  call int_png2mroad_create_temporary_table(_idtrt);
--  CALL int_png2mroad_nettoyage(_idtrt);

  -- CALL int_png2mroad_maj_jour_ferie(_idtrt);
  CALL int_png2mroad_maj_remuneration(_idtrt);
  CALL int_png2mroad_maj_ref_activite(_idtrt);
  -- CALL int_png2mroad_maj_ref_emploi(_idtrt);

  CALL int_png2mroad_maj_date(_idtrt);
  
  -- ajout de toutes les dates de changement
  INSERT INTO pai_int_png_rcoid(rcoid,date_debut)
  SELECT DISTINCT rc.oid,rc.relatdatedeb FROM pai_png_relationcontrat rc
  UNION SELECT DISTINCT rc.oid,po.begin_date FROM pai_png_relationcontrat rc,pai_png_salpopulationw po  WHERE po.salarie=rc.relatmatricule AND po.numrc=rc.relatnum
  UNION SELECT DISTINCT rc.oid,po.begin_date FROM pai_png_relationcontrat rc,pai_png_rcpopulationw po   WHERE po.relationcontrat=rc.oid
  UNION	SELECT DISTINCT rc.oid,em.begin_date FROM pai_png_relationcontrat rc,pai_png_emploi em          WHERE em.emploirelation=rc.oid
  UNION SELECT DISTINCT rc.oid,et.begin_date FROM pai_png_relationcontrat rc,pai_png_etablissrel et     WHERE et.etabrelation=rc.oid
  UNION SELECT DISTINCT rc.oid,xh.begin_date FROM pai_png_relationcontrat rc,pai_png_xhorporpol xh      WHERE xh.relationcontrat=rc.oid
  UNION SELECT DISTINCT rc.oid,s.begin_date  FROM pai_png_relationcontrat rc,pai_png_contrat c,pai_png_suspension s WHERE rc.oid=c.ctrrelation AND c.oid=s.suspcontrat
  UNION SELECT DISTINCT rc.oid,s.end_date+INTERVAL 1 DAY FROM pai_png_relationcontrat rc,pai_png_contrat c,pai_png_suspension s WHERE rc.oid=c.ctrrelation AND c.oid=s.suspcontrat
  UNION SELECT DISTINCT rc.oid,ao.begin_date FROM pai_png_relationcontrat rc,pai_png_affoctimew ao      WHERE rc.oid=ao.relationcontrat
  UNION SELECT DISTINCT rc.oid,h.begin_date FROM pai_png_relationcontrat rc,pai_png_horaire h           WHERE rc.oid=h.horcontr
  UNION SELECT DISTINCT eco.rcoid,et.date_debut FROM emp_transfert et,emp_contrat eco                   WHERE et.contrat_id=eco.id and et.date_debut between eco.date_debut and eco.date_fin
  ;
  CALL int_png2mroad_maj_tmp_salarie(_idtrt);
  CALL int_png2mroad_maj_tmp_depot(_idtrt);
  CALL int_png2mroad_maj_tmp_flux(_idtrt);
  CALL int_png2mroad_maj_tmp_emploi(_idtrt);
  CALL int_png2mroad_maj_tmp_population(_idtrt);
  CALL int_png2mroad_maj_tmp_cycle(_idtrt);
  CALL int_png2mroad_maj_tmp_societe(_idtrt);
  CALL int_png2mroad_regroupement(_idtrt);

  SET foreign_key_checks = 0;
  CALL int_png2mroad_maj_employe(_idtrt,_depot_id,_flux_id,_date_int);
  CALL int_png2mroad_create_emp_in_depot_flux(_idtrt,_depot_id,_flux_id);
  CALL int_png2mroad_maj_emp_contrat(_idtrt,_depot_id,_flux_id,_date_int);
  CALL int_png2mroad_maj_emp_contrat_type(_idtrt,_depot_id,_flux_id,_date_int);
  CALL int_png2mroad_maj_cycle(_idtrt,_depot_id,_flux_id,_date_int);
  CALL int_png2mroad_maj_contrat_hp(_idtrt,_depot_id,_flux_id,_date_int);

  CALL int_png2mroad_maj_emp_pop_depot(_idtrt,_depot_id,_flux_id,_date_int);
  SET foreign_key_checks = 0;

  CALL int_png2mroad_maj_remplacement(_idtrt,_depot_id,_flux_id,_date_int);

  -- call int_png2mroad_drop_temporary_table(_idtrt);
END;

-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_png2mroad_create_temporary_table;
CREATE PROCEDURE int_png2mroad_create_temporary_table(_idtrt INTEGER)
BEGIN
  drop temporary table if exists pai_int_png_rcoid;
  CREATE TEMPORARY TABLE pai_int_png_rcoid (
     rcoid        VARCHAR(36) COLLATE utf8_unicode_ci,
     date_debut   DATE
  )
  ENGINE = memory 
  DEFAULT CHARSET=utf8
  COLLATE=utf8_unicode_ci;
  CREATE INDEX pai_int_png_rcoid_idx ON pai_int_png_rcoid(rcoid, date_debut);
  CREATE INDEX pai_int_png_rcoid_idx2 ON pai_int_png_rcoid(rcoid);

  drop temporary table if exists pai_int_png_info;
  CREATE TEMPORARY TABLE pai_int_png_info (
    id                  INT AUTO_INCREMENT,
    date_debut          DATE,
    date_fin            DATE,
    saloid              VARCHAR(36) COLLATE utf8_unicode_ci,
    matricule           VARCHAR(10) COLLATE utf8_unicode_ci,
    rcoid               VARCHAR(36) COLLATE utf8_unicode_ci,
    relatnum            VARCHAR(6) COLLATE utf8_unicode_ci,
    dRC                 DATE,
    fRC                 DATE,
    employe_id          INT,
    etabcode            VARCHAR(6) COLLATE utf8_unicode_ci,
    depot_id            INT,
    flux_id             INT,
    emploi_code         VARCHAR(3) COLLATE utf8_unicode_ci,
    emploi_id           INT,
    popcode             VARCHAR(6) COLLATE utf8_unicode_ci,
    population_id       INT,
    societe_id          INT,
    heure_debut         TIME,
    nbheures_garanties  DECIMAL(5,2),
    cycle               VARCHAR(7) COLLATE utf8_unicode_ci,
    CONSTRAINT PRIMARY KEY(id)
  )
  ENGINE = memory 
  DEFAULT CHARSET=utf8
  COLLATE=utf8_unicode_ci;
  CREATE INDEX pai_int_png_info_idx1 ON pai_int_png_info(matricule, date_debut);
  CREATE INDEX pai_int_png_info_idx2 ON pai_int_png_info(matricule, date_fin);
  CREATE INDEX pai_int_png_info_idx3 ON pai_int_png_info(rcoid, depot_id, flux_id, emploi_id, population_id, heure_debut, nbheures_garanties);
  CREATE INDEX pai_int_png_info_idx4 ON pai_int_png_info(depot_id);
  CREATE INDEX pai_int_png_info_idx5 ON pai_int_png_info(flux_id);
  CREATE INDEX pai_int_png_info_idx6 ON pai_int_png_info(emploi_id);
  CREATE INDEX pai_int_png_info_idx7 ON pai_int_png_info(population_id);
  CREATE INDEX pai_int_png_info_idx8 ON pai_int_png_info(societe_id);
  CREATE INDEX pai_int_png_info_idx9 ON pai_int_png_info(cycle);

  drop temporary table if exists pai_int_png_rejet;
  CREATE TEMPORARY TABLE pai_int_png_rejet (
    id                  INT,
    date_debut          DATE,
    date_fin            DATE,
    saloid              VARCHAR(36) COLLATE utf8_unicode_ci,
    matricule           VARCHAR(10) COLLATE utf8_unicode_ci,
    rcoid               VARCHAR(36) COLLATE utf8_unicode_ci,
    relatnum            VARCHAR(6) COLLATE utf8_unicode_ci,
    dRC                 DATE,
    fRC                 DATE,
    employe_id          INT,
    etabcode            VARCHAR(6) COLLATE utf8_unicode_ci,
    depot_id            INT,
    flux_id             INT,
    emploi_code         VARCHAR(3) COLLATE utf8_unicode_ci,
    emploi_id           INT,
    popcode             VARCHAR(6) COLLATE utf8_unicode_ci,
    population_id       INT,
    societe_id          INT,
    heure_debut         TIME,
    nbheures_garanties  DECIMAL(5,2),
    cycle               VARCHAR(7) COLLATE utf8_unicode_ci
  )
  ENGINE = memory 
  DEFAULT CHARSET=utf8
  COLLATE=utf8_unicode_ci;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_png2mroad_drop_temporary_table;
CREATE PROCEDURE int_png2mroad_drop_temporary_table(_idtrt INTEGER)
BEGIN
  drop temporary table if exists pai_int_png_rcoid;
  drop temporary table if exists pai_int_png_info;
  drop temporary table if exists pai_int_png_rejet;
  drop temporary table if exists pai_int_png_info2;
  drop temporary table if exists pai_int_png_info3;
  drop temporary table if exists pai_int_png_info4;
  drop temporary table if exists pai_int_png_info_regroupement;
  drop temporary table if exists pai_int_png_emp_in_depot_flux;
END;

-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_png2mroad_nettoyage;
CREATE PROCEDURE int_png2mroad_nettoyage(_idtrt INTEGER)
BEGIN
  call int_logger(_idtrt,'png2mroad','int_png2mroad_nettoyage');
 -- nettoyage des tables
 TRUNCATE TABLE pai_int_png_rcoid;
 TRUNCATE TABLE pai_int_png_info;
 TRUNCATE TABLE pai_int_png_rejet;
END;

-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_png2mroad_maj_jour_ferie;
/*
CREATE PROCEDURE int_png2mroad_maj_jour_ferie(_idtrt INTEGER)
BEGIN
  INSERT INTO pai_ref_ferie(societe_id,jfdate)
  SELECT res.id,legalferiedate
  FROM pai_png_legalferie
  INNER JOIN ref_emp_societe res ON res.id>0
  WHERE legalferiedate NOT IN (SELECT jfdate FROM pai_ref_ferie)
  ;
  call int_logrowcount_C(_idtrt,5,'int_png2mroad_maj_jour_ferie','Ajout dans table pai_ref_ferie')
  ;
  DELETE FROM pai_ref_ferie
  WHERE jfdate NOT IN (SELECT legalferiedate FROM pai_png_legalferie)
  ;
  call int_logrowcount_C(_idtrt,5,'int_png2mroad_maj_jour_ferie','Suppression dans table pai_ref_ferie')
  ;
END;
*/
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_png2mroad_maj_remuneration;
CREATE PROCEDURE int_png2mroad_maj_remuneration(_idtrt INTEGER)
BEGIN
-- ATTENTION, il faut recalculer tous les modele, valeur de rémunération, durée lorsqu'il y a un changement de rémunération !!!!!!
-- ATTENTION problème d'arrondi
 INSERT INTO pai_ref_remuneration(population_id,societe_id,date_debut,date_fin,valeur,valeurHP,valeurHP2)
 SELECT rp.id,rs.id,t.begin_date,t.end_date,t.tauxhoraire ,t.tauxhorairedisc ,t.tauxhorairedis2
 FROM pai_png_tarifhorairew t
 INNER JOIN pai_png_ta_populationw p ON t.population=p.oid
 INNER JOIN ref_population rp ON p.code=rp.code
 INNER JOIN pai_png_societe s ON t.societe=s.oid
 INNER JOIN ref_emp_societe rs ON s.societecode=rs.code
 AND (rp.id,rs.id,t.begin_date) NOT IN (SELECT population_id,societe_id,date_debut FROM pai_ref_remuneration)
  ;
 call int_logrowcount_C(_idtrt,5,'int_png2mroad_maj_remuneration','insert')
 ;
 UPDATE pai_ref_remuneration prr
 INNER JOIN pai_png_tarifhorairew t ON prr.date_debut=t.begin_date
 INNER JOIN pai_png_ta_populationw p ON t.population=p.oid
 INNER JOIN ref_population rp ON p.code=rp.code AND rp.id=prr.population_id
 INNER JOIN pai_png_societe s ON t.societe=s.oid
 INNER JOIN ref_emp_societe rs ON s.societecode=rs.code AND rs.id=prr.societe_id
 SET prr.date_fin=t.end_date
 ,prr.valeur=t.tauxhoraire
 ,prr.valeurHP=t.tauxhorairedisc
 ,prr.valeurHP2=t.tauxhorairedis2
 where prr.date_fin<>t.end_date
 or prr.valeur<>t.tauxhoraire
 or prr.valeurHP<>t.tauxhorairedisc
 or prr.valeurHP2<>t.tauxhorairedis2
  ;
 call int_logrowcount_C(_idtrt,5,'int_png2mroad_maj_remuneration','update')
 ;
 DELETE FROM pai_ref_remuneration
 WHERE (population_id,societe_id,date_debut) NOT IN (SELECT rp.id,rs.id,t.begin_date
         FROM pai_png_tarifhorairew t
         INNER JOIN pai_png_ta_populationw p ON t.population=p.oid
         INNER JOIN ref_population rp ON p.code=rp.code
         INNER JOIN pai_png_societe s ON t.societe=s.oid
         INNER JOIN ref_emp_societe rs ON s.societecode=rs.code
         )
  ;
 call int_logrowcount_C(_idtrt,5,'int_png2mroad_maj_remuneration','delete')
 ;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_png2mroad_maj_ref_activite;
CREATE PROCEDURE int_png2mroad_maj_ref_activite(_idtrt INTEGER)
BEGIN
  INSERT INTO ref_activite(code,libelle,affichage_modele,km_paye,actif,est_hors_presse,est_hors_travail,est_1mai,est_pleiades,est_JTPX,est_badge,est_garantie,utilisateur_id,date_creation)
  SELECT code,libelle,false,true,true,true,false,true,true,false,false,true,0,sysdate()
  FROM pai_png_xta_rcactivhpre xa
  WHERE xa.code NOT IN (SELECT code FROM ref_activite)
  ;
  call int_logrowcount_C(_idtrt,5,'int_png2mroad_maj_ref_activite','Ajout d''activités hors-presse')
  ;
  -- On met à jour le calendrier avec les supprimés
  UPDATE ref_activite ra
  INNER JOIN pai_png_xta_rcactivhpre xa on ra.code=xa.code
  SET ra.libelle    =xa.libelle
  ,affichage_modele =false
  ,km_paye          =true
  ,actif            =true
  ,est_hors_presse  =true
  ,est_hors_travail =false
  ,est_1mai         =true
  ,est_pleiades     =true
--  ,est_JTPX         =false
  ,est_badge        =false
  ,est_garantie     =true
  ,utilisateur_id   =0
  ,date_modif       =sysdate()
  WHERE ra.libelle    <>xa.libelle
  OR affichage_modele <>false
  OR km_paye          <>true
  OR actif            <>true
  OR est_hors_presse  <>true
  OR est_hors_travail <>false
  OR est_1mai         <>true
  OR est_pleiades     <>true
  OR est_badge        <>false
--  OR est_JTPX         <>false
  OR est_garantie     <>true
  ;
  call int_logrowcount_C(_idtrt,5,'int_png2mroad_maj_ref_activite','Maj d''activités hors-presse')
  ;
  UPDATE ref_activite ra
  SET actif            =false
  ,utilisateur_id   =0
  ,date_modif       =sysdate()
  WHERE ra.est_pleiades  =true
  AND ra.code NOT IN (SELECT xa.code FROM pai_png_xta_rcactivhpre xa)
  ;
  call int_logrowcount_C(_idtrt,5,'int_png2mroad_maj_ref_activite','Maj d''activités hors-presse')
  ;
  UPDATE pai_png_xrcautreactivit xa
  INNER JOIN pai_png_xta_rcactivhpre xra on xa.xta_rcactivhpre=xra.oid
  INNER JOIN ref_activite ra on ra.code=xra.code
  SET xa.activite_id=ra.id
  ;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_png2mroad_maj_ref_emploi;
CREATE PROCEDURE int_png2mroad_maj_ref_emploi(_idtrt INTEGER)
BEGIN
  INSERT INTO ref_emploi(code,libelle,codeNG,affichage_modele_tournee,affichage_modele_activite,paie,prime)
  SELECT 'AUT',te.emploilibelle,te.emploicode,false,false,false,false
  FROM pai_png_ta_emploi te
  WHERE te.emploicode NOT IN (SELECT codeNG FROM ref_emploi)
  ;
  call int_logrowcount_C(_idtrt,5,'int_png2mroad_maj_ref_emploi','Ajout emploi')
  ;
  -- On met à jour le calendrier avec les supprimés
  UPDATE ref_emploi re
  INNER JOIN pai_png_ta_emploi te on  te.emploicode=re.codeNG
  SET re.libelle    =te.emploilibelle
  WHERE re.libelle    <>te.emploilibelle
  ;
  call int_logrowcount_C(_idtrt,5,'int_png2mroad_maj_ref_emploi','Maj emploi')
  ;
END;


-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_png2mroad_maj_date;
CREATE PROCEDURE int_png2mroad_maj_date(_idtrt INTEGER)
BEGIN
-- Corrige les date des contrats hors-presse en fonction des dates de la relation contractuelle
  delete xa
  from pai_png_xrcautreactivit xa
  inner join pai_png_relationcontrat rc on xa.relationcontrat=rc.oid
  where (xa.begin_date<rc.relatdatedeb and xa.end_date<rc.relatdatedeb)
  or    (xa.begin_date>rc.relatdatefinw and xa.end_date>rc.relatdatefinw)
  ;
  call int_logrowcount_C(_idtrt,5,'int_png2mroad_maj_date','Suppression de contrat hors-presse hors rc')
  ;
  update pai_png_xrcautreactivit xa
  inner join pai_png_relationcontrat rc on xa.relationcontrat=rc.oid
  set xa.begin_date=rc.relatdatedeb
  where xa.begin_date<rc.relatdatedeb and xa.end_date>=rc.relatdatedeb
  ;
  call int_logrowcount_C(_idtrt,5,'int_png2mroad_maj_date','Maj date début de contrat hors-presse')
  ;
  update pai_png_xrcautreactivit xa
  inner join pai_png_relationcontrat rc on xa.relationcontrat=rc.oid
  set xa.end_date=rc.relatdatefinw
  where xa.end_date>rc.relatdatefinw and xa.begin_date<=rc.relatdatefinw
  ;
  call int_logrowcount_C(_idtrt,5,'int_png2mroad_maj_date','Maj date fin de contrat hors-presse')
  ;
END;


-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_png2mroad_maj_tmp_salarie;
CREATE PROCEDURE int_png2mroad_maj_tmp_salarie(_idtrt INTEGER)
BEGIN
  -- calcul de la date de fin
  INSERT INTO pai_int_png_info(rcoid,date_debut)
  SELECT DISTINCT i.rcoid,i.date_debut
  FROM pai_int_png_rcoid i
  ;
  call int_logrowcount_C(_idtrt,5,'int_png2mroad_maj_tmp_salarie','insert')
  ;
  -- on met la date de fin de rc si date de fin non renseignée
  UPDATE pai_int_png_info i
  SET date_fin=(SELECT MIN(i2.date_debut)- INTERVAL 1 DAY FROM pai_int_png_rcoid i2 WHERE i.rcoid=i2.rcoid AND i.date_debut<i2.date_debut GROUP BY i2.rcoid)
  ;
  call int_logrowcount_C(_idtrt,5,'int_png2mroad_maj_tmp_salarie','update interval')
  ;
  -- on met la date de fin de rc si date de fin non renseignée
  UPDATE pai_int_png_info i
  SET date_fin=(SELECT relatdatefinw FROM pai_png_relationcontrat rc WHERE rc.oid=i.rcoid AND rc.relatdatefinw>=date_debut)
  WHERE date_fin IS NULL
  ;
  call int_logrowcount_C(_idtrt,5,'int_png2mroad_maj_tmp_salarie','update fin rc')
  ;
  -- on supprime les lignes qui correspondent à des suspensions
  DELETE i
  FROM pai_int_png_info i
  INNER JOIN pai_png_contrat c ON i.rcoid=c.ctrrelation
  INNER JOIN pai_png_suspension s ON  c.oid=s.suspcontrat
  WHERE i.date_debut=s.begin_date
  ;
  call int_logrowcount_C(_idtrt,5,'int_png2mroad_maj_tmp_salarie','suspension')
  ;
  -- on met à jour les informations rc et les informations salarié
  UPDATE pai_int_png_info i
  INNER JOIN pai_png_relationcontrat rc ON rc.oid=i.rcoid
  INNER JOIN pai_png_salarie s ON s.oid=rc.relatmatricule
  LEFT OUTER JOIN pai_png_salpopulationw po ON po.salarie=s.oid AND po.numrc=rc.relatnum AND  i.date_debut BETWEEN po.begin_date AND po.end_date
  LEFT OUTER JOIN pai_png_xhorporpol xh on i.rcoid=xh.relationcontrat and date_debut between xh.begin_date and xh.end_date
  LEFT OUTER JOIN employe e ON e.matricule=CONCAT(s.matricule,po.nocarrierealpha)
  SET  i.saloid=s.oid
  , i.matricule=CONCAT(s.matricule,po.nocarrierealpha)
  , i.relatnum=rc.relatnum
  , i.dRC=rc.relatdatedeb
  , i.fRC=rc.relatdatefinW
  , i.employe_id=e.id
  , i.heure_debut=sec_to_time(xh.heuredebctr)
  , i.nbheures_garanties=xh.horcontractuel
  ;
  call int_logrowcount_C(_idtrt,5,'int_png2mroad_maj_tmp_salarie','update')
  ;
  delete from pai_int_png_info where date_debut>fRC;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
-- Enrichissement de la table png_info
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_png2mroad_maj_tmp_depot;
CREATE PROCEDURE int_png2mroad_maj_tmp_depot(
        _idtrt          INTEGER
) BEGIN
  -- on met à jour les informations etablissement
  UPDATE pai_int_png_info i
  INNER JOIN pai_png_etablissrel er ON er.etabrelation=i.rcoid and i.date_debut between er.begin_date and er.end_date
  INNER JOIN pai_png_etablissement et ON er.etabrel=et.oid
  LEFT OUTER JOIN depot d ON CONCAT('0',RIGHT(et.etabcode,2))=d.code and not RIGHT(et.etabcode,3)  REGEXP '^[0-9]+$' -- le code contient seulement 2 caractères numériques
                          OR RIGHT(et.etabcode,3)=d.code
  SET  i.depot_id=d.id
  , i.etabcode=et.etabcode
  ;
  call int_logrowcount_C(_idtrt,5,'int_png2mroad_maj_tmp_depot','update')
  ;
  UPDATE pai_int_png_info i
  INNER JOIN pai_png_etablissrel er ON er.etabrelation=i.rcoid and i.date_debut between er.begin_date and er.end_date
  INNER JOIN pai_png_etablissement et ON er.etabrel=et.oid
  SET  i.depot_id=18 -- St-Ouen
  , i.etabcode=et.etabcode
  where RIGHT(et.etabcode,3) between '101' and '121' -- Paris Centre
  ;
  call int_logrowcount_C(_idtrt,5,'int_png2mroad_maj_tmp_depot','update PARIS')
  ;
  UPDATE pai_int_png_info i
  INNER JOIN emp_contrat eco ON i.rcoid=eco.rcoid
  INNER JOIN emp_transfert et ON eco.id=et.contrat_id and et.date_debut between eco.date_debut and eco.date_fin
  INNER JOIN depot d ON et.depot_dst_id=d.id
  SET  i.depot_id=et.depot_dst_id
  , i.etabcode=d.code
  where i.depot_id=et.depot_org_id
  and i.date_debut between et.date_debut and et.date_fin
  ;
  call int_logrowcount_C(_idtrt,5,'int_png2mroad_maj_tmp_depot','update transfert de centre')
  ;
  
  INSERT INTO pai_int_png_rejet SELECT * FROM pai_int_png_info WHERE depot_id is null;
  call int_logrowcount_C(_idtrt,5,'int_png2mroad_maj_tmp_depot','rejet')
  ;
  delete from pai_int_png_info where depot_id is null
  ;
  UPDATE pai_png_xrcautreactivit xa
  INNER JOIN pai_png_etablissement et ON xa.etabgestion=et.oid
  LEFT OUTER JOIN depot d ON CONCAT('0',RIGHT(et.etabcode,2))=d.code and not RIGHT(et.etabcode,3)  REGEXP '^[0-9]+$' -- le code contient seulement 2 caractères numériques
                          OR RIGHT(et.etabcode,3)=d.code
  SET  xa.depot_id=d.id
  ;
  UPDATE pai_png_xrcautreactivit xa
  INNER JOIN pai_png_etablissement et ON xa.etabgestion=et.oid
  SET  xa.depot_id=18 -- St-Ouen
  where RIGHT(et.etabcode,3) between '101' and '121' -- Paris Centre
  ;
  -- Rustine pour la création du centre de montataire (affecté sur longueil avant)
  update pai_int_png_info i
  set i.depot_id=22
  , i.etabcode='050'
  where (i.matricule between '7000573900' and '7000574800'
  or i.matricule in ('Z003015300'))
  and i.depot_id=14
  and i.date_debut='2016-10-02';
  /*
  select *
  from v_employe i
  INNER JOIN pai_png_etablissrel er ON er.etabrelation=i.rcoid and i.date_debut between er.begin_date and er.end_date
  INNER JOIN pai_png_etablissement et ON er.etabrel=et.oid
  where (i.matricule between '7000573900' and '7000574800'
  or i.matricule in ('Z003015300'))
  */
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
-- 10/02/2015 Permet de faire une rupture en cas de mutation sur un etablissement Parisien (plusieurs buletins de paie)
DROP PROCEDURE IF EXISTS int_png2mroad_maj_tmp_depot_paie;
CREATE PROCEDURE int_png2mroad_maj_tmp_depot_paie(
        _idtrt          INTEGER
) BEGIN
  -- on met à jour les informations etablissement
  UPDATE pai_int_png_info i
  INNER JOIN pai_png_etablissrel er ON er.etabrelation=i.rcoid and i.date_debut between er.begin_date and er.end_date
  INNER JOIN pai_png_etablissement et ON er.etabrel=et.oid
  LEFT OUTER JOIN depot d ON CONCAT('0',RIGHT(et.etabcode,2))=d.code and not RIGHT(et.etabcode,3)  REGEXP '^[0-9]+$' -- le code contient seulement 2 caractères numériques
                          OR RIGHT(et.etabcode,3)=d.code
  SET  i.depot_id=d.id
  , i.etabcode=et.etabcode
  ;
  call int_logrowcount_C(_idtrt,5,'int_png2mroad_maj_tmp_depot','update')
  ;
  UPDATE pai_int_png_info i
  INNER JOIN pai_png_etablissrel er ON er.etabrelation=i.rcoid and i.date_debut between er.begin_date and er.end_date
  INNER JOIN pai_png_etablissement et ON er.etabrel=et.oid
  SET  i.depot_id=-RIGHT(et.etabcode,3) -- pour la paie, on fait une rupture sur les etablissement Parisiens qui sont gérés par St-Ouen
  , i.etabcode=et.etabcode
  where RIGHT(et.etabcode,3) between '101' and '121' -- Paris Centre
  ;
  call int_logrowcount_C(_idtrt,5,'int_png2mroad_maj_tmp_depot','update PARIS')
  ;
  INSERT INTO pai_int_png_rejet SELECT * FROM pai_int_png_info WHERE depot_id is null;
  call int_logrowcount_C(_idtrt,5,'int_png2mroad_maj_tmp_depot','rejet')
  ;
  delete from pai_int_png_info where depot_id is null
  ;
/*  UPDATE pai_png_xrcautreactivit xa
  INNER JOIN pai_png_etablissement et ON xa.etabgestion=et.oid
  LEFT OUTER JOIN depot d ON CONCAT('0',RIGHT(et.etabcode,2))=d.code and not RIGHT(et.etabcode,3)  REGEXP '^[0-9]+$' -- le code contient seulement 2 caractères numériques
                          OR RIGHT(et.etabcode,3)=d.code
  SET  xa.depot_id=d.id
  ;
  UPDATE pai_png_xrcautreactivit xa
  INNER JOIN pai_png_etablissement et ON xa.etabgestion=et.oid
  SET  xa.depot_id=-RIGHT(et.etabcode,3) -- pour la paie, on fait une rupture sur les etablissement Parisiens qui sont gérés par St-Ouen
  where RIGHT(et.etabcode,3) between '101' and '121' -- Paris Centre
  ;*/
  -- Rustine pour la création du centre de montataire (affecté sur longueil avant)
  update pai_int_png_info i
  set i.depot_id=22
  , i.etabcode='050'
  where (i.matricule between '7000573900' and '7000574800'
  or i.matricule in ('Z003015300'))
  and i.depot_id=14
  and i.date_debut='2016-10-02';
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_png2mroad_maj_tmp_flux;
CREATE PROCEDURE int_png2mroad_maj_tmp_flux(
        _idtrt          INTEGER
) BEGIN
/*
Si affectaction Octime = NORD MEDIA ou SUD MEDIA alors flux=Jour, sinon flux=Nuit
==> ne pas utiliser le flux comme indicateur ^pour les règles de paie.
*/

  UPDATE pai_int_png_info i
  inner join pai_png_affoctimew ao on i.rcoid=ao.relationcontrat and i.date_debut between ao.begin_date and ao.end_date
  SET  i.flux_id=CASE when ao.niveau2='941c5fdd-1882-11e4-9370-8749f6d713da' or ao.niveau2='a447522e-1882-11e4-9370-8749f6d713da' then 2 else 1 end
  ;
  call int_logrowcount_C(_idtrt,5,'int_png2mroad_maj_tmp_flux','update');
  -- Rustine pour affecter les salariés au flux nuit alors que Octime n'existait pas, ni le flux jour !!!!
  UPDATE pai_int_png_info i
  SET  i.flux_id=1
  where  i.flux_id is null and depot_id is not null
  and i.date_fin<'2010-01-01'
  ;
  call int_logrowcount_C(_idtrt,5,'int_png2mroad_maj_tmp_flux','update Rustine Octime/Mediapresse')
  ;
/*
  UPDATE pai_int_png_info i
  inner join pai_png_relationcontrat rc on i.rcoid=rc.oid
  inner join pai_png_societe so on rc.relatsociete=so.oid
  inner join ref_emp_societe rso on so.societecode=rso.code
  SET  i.flux_id=CASE WHEN so.societecode='SDV' THEN 1  WHEN so.societecode='MED' THEN 2 ELSE NULL END
  ;
*/
  call int_logrowcount_C(_idtrt,5,'int_png2mroad_maj_tmp_flux','update')
  ;
  INSERT INTO pai_int_png_rejet SELECT * FROM pai_int_png_info WHERE flux_id is null;
  call int_logrowcount_C(_idtrt,5,'int_png2mroad_maj_tmp_flux','rejet')
  ;
  delete from pai_int_png_info where flux_id is null;

  UPDATE pai_png_xrcautreactivit xa
  INNER JOIN pai_int_png_info i on xa.relationcontrat=i.rcoid and xa.begin_date between i.date_debut and i.date_fin
  SET  xa.flux_id=i.flux_id
  ;
  UPDATE pai_png_xrcautreactivit xa
  SET  xa.flux_id=2
  WHERE  xa.flux_id is null
  ;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_png2mroad_maj_tmp_emploi;
CREATE PROCEDURE int_png2mroad_maj_tmp_emploi(_idtrt INTEGER)
BEGIN
  UPDATE pai_int_png_info i
  LEFT OUTER JOIN pai_png_emploi em ON em.emploirelation=i.rcoid and i.date_debut BETWEEN em.begin_date AND em.end_date
  LEFT OUTER JOIN pai_png_ta_emploi tem ON em.emploi=tem.oid
  LEFT OUTER JOIN ref_emploi re ON tem.emploicode=re.codeNG and re.paie
  SET  i.emploi_id=COALESCE(re.id,3)
  , i.emploi_code=COALESCE(re.code,'AUT')
  ;
  call int_logrowcount_C(_idtrt,5,'int_png2mroad_maj_tmp_emploi','update')
  ;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_png2mroad_maj_tmp_population;
CREATE PROCEDURE int_png2mroad_maj_tmp_population(_idtrt INTEGER)
BEGIN
  UPDATE pai_int_png_info i
  INNER JOIN pai_png_rcpopulationw po ON po.relationcontrat=i.rcoid and i.date_debut BETWEEN po.begin_date AND po.end_date
  INNER JOIN pai_png_ta_populationw tp ON po.population=tp.oid
  LEFT OUTER JOIN ref_population rp ON tp.code=rp.code
  SET  i.population_id=rp.id
  , i.popcode=tp.code
  ;
  call int_logrowcount_C(_idtrt,5,'int_png2mroad_maj_tmp_population','update')
  ;
  INSERT INTO pai_int_png_rejet SELECT * FROM pai_int_png_info WHERE population_id is null;
  DELETE FROM pai_int_png_info WHERE population_id is null
  ;
  call int_logrowcount_C(_idtrt,5,'int_png2mroad_maj_tmp_population','update')
  ;
  
  INSERT INTO pai_int_log(idtrt,date_log,module,msg) 
  SELECT distinct _idtrt,now(),'int_png2mroad_maj_tmp_population',CONCAT_WS(' ','Population',rp.code,'incorrecte pour',e.matricule,e.nom,e.prenom1,e.prenom2)
  FROM pai_int_png_info i
  INNER JOIN employe e on i.employe_id=e.id
  inner join ref_population rp on i.population_id=rp.id
  where flux_id=1 and population_id in (5,6,7,8,9,10,11,12)
  ;
  UPDATE pai_int_png_info i set population_id=1 where population_id in (5,6) and flux_id=1;
  UPDATE pai_int_png_info i set population_id=2 where population_id in (9,10) and flux_id=1;
  UPDATE pai_int_png_info i set population_id=3 where population_id in (11,12) and flux_id=1;
  UPDATE pai_int_png_info i set population_id=4 where population_id in (7,8) and flux_id=1;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_png2mroad_maj_tmp_population_paie;
CREATE PROCEDURE int_png2mroad_maj_tmp_population_paie(_idtrt INTEGER)
BEGIN
  UPDATE pai_int_png_info i
  INNER JOIN pai_png_rcpopulationw po ON po.relationcontrat=i.rcoid and i.date_debut BETWEEN po.begin_date AND po.end_date
  INNER JOIN pai_png_ta_populationw tp ON po.population=tp.oid
  LEFT OUTER JOIN ref_population p ON tp.code=p.code
  SET  i.population_id=p.pop_paie_id
  , i.popcode=tp.code
  ;
  call int_logrowcount_C(_idtrt,5,'int_png2mroad_maj_tmp_population','update')
  ;
  INSERT INTO pai_int_png_rejet SELECT * FROM pai_int_png_info WHERE population_id is null;
  DELETE FROM pai_int_png_info WHERE population_id is null
  ;
  call int_logrowcount_C(_idtrt,5,'int_png2mroad_maj_tmp_population','update')
  ;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_png2mroad_maj_tmp_cycle;
CREATE PROCEDURE int_png2mroad_maj_tmp_cycle(_idtrt INTEGER)
BEGIN
  UPDATE pai_int_png_info i
  INNER JOIN pai_png_horaire h on h.horcontr=i.rcoid and i.date_debut BETWEEN h.begin_date AND h.end_date
  inner join pai_png_semainetype st on h.horsemainetype =st.oid
  SET  i.cycle=cycle_to_string(st.semtypedurlun<>0,st.semtypedurmar<>0,st.semtypedurmer<>0,st.semtypedurjeu<>0,st.semtypedurven<>0,st.semtypedursam<>0,st.semtypedurdim<>0)
--  , i.cyclecode=st.semtypecode
  ;
  call int_logrowcount_C(_idtrt,5,'int_png2mroad_maj_tmp_population','update')
  ;
/*  INSERT INTO pai_int_png_rejet SELECT * FROM pai_int_png_info WHERE population_id is null;
  DELETE FROM pai_int_png_info WHERE population_id is null
  ;
  call int_logrowcount_C(_idtrt,5,'int_png2mroad_maj_tmp_population','update')
  ;*/
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_png2mroad_maj_tmp_societe;
CREATE PROCEDURE int_png2mroad_maj_tmp_societe(_idtrt INTEGER)
BEGIN
  UPDATE pai_int_png_info i
  inner join pai_png_relationcontrat rc on i.rcoid=rc.oid
  inner join pai_png_societe so on rc.relatsociete=so.oid
  left outer join ref_emp_societe rso on so.societecode=rso.code
  SET  i.societe_id=rso.id
  ;
  call int_logrowcount_C(_idtrt,5,'int_png2mroad_maj_tmp_societe','update')
  ;
  INSERT INTO pai_int_png_rejet SELECT * FROM pai_int_png_info WHERE societe_id is null;
  DELETE FROM pai_int_png_info WHERE societe_id is null
  ;
  call int_logrowcount_C(_idtrt,5,'int_png2mroad_maj_tmp_societe','rejet')
  ;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
-- regroupement des enregistrements
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
-- CALL int_png(0);
DROP PROCEDURE IF EXISTS int_png2mroad_regroupement;
CREATE PROCEDURE int_png2mroad_regroupement(_idtrt INTEGER)
BEGIN
DECLARE _nb INT;
  call int_logger(_idtrt,'int_png2mroad_regroupement','Debut');
  update pai_int_png_info set depot_id=0 where depot_id is null;
  update pai_int_png_info set flux_id=0 where flux_id is null;
  update pai_int_png_info set emploi_id=0 where emploi_id is null;
  update pai_int_png_info set emploi_code='' where emploi_code is null;
  update pai_int_png_info set population_id=0 where population_id is null;
  update pai_int_png_info set heure_debut='00:00' where heure_debut is null;
  update pai_int_png_info set nbheures_garanties=0 where nbheures_garanties is null;
  update pai_int_png_info set cycle='' where cycle is null;
  
  drop temporary table if exists pai_int_png_info2;
  CREATE TEMPORARY TABLE pai_int_png_info2 engine=memory  as select rcoid, depot_id, flux_id, emploi_id, emploi_code, population_id, heure_debut, nbheures_garanties, cycle, date_debut, date_fin from pai_int_png_info;
  drop temporary table if exists pai_int_png_info3;
  CREATE TEMPORARY TABLE pai_int_png_info3 engine=memory  as select rcoid, depot_id, flux_id, emploi_id, emploi_code, population_id, heure_debut, nbheures_garanties, cycle, date_debut, date_fin from pai_int_png_info;
  drop temporary table if exists pai_int_png_info4;
  CREATE TEMPORARY TABLE pai_int_png_info4 engine=memory  as select rcoid, depot_id, flux_id, emploi_id, emploi_code, population_id, heure_debut, nbheures_garanties, cycle,  date_debut, date_fin from pai_int_png_info;
  CREATE INDEX pai_int_png_info_idx20 ON pai_int_png_info2(rcoid, depot_id, flux_id, emploi_id, emploi_code, population_id, heure_debut, nbheures_garanties, cycle, date_fin);
  CREATE INDEX pai_int_png_info_idx30 ON pai_int_png_info3(rcoid, depot_id, flux_id, emploi_id, emploi_code, population_id, heure_debut, nbheures_garanties, cycle, date_fin);
  CREATE INDEX pai_int_png_info_idx40 ON pai_int_png_info4(rcoid, depot_id, flux_id, emploi_id, emploi_code, population_id, heure_debut, nbheures_garanties, cycle, date_debut);
  
  drop temporary table if exists pai_int_png_info_regroupement;
  create temporary table pai_int_png_info_regroupement engine=memory as
  SELECT        employe_id, matricule, saloid, rcoid, relatnum, depot_id, flux_id, emploi_id, emploi_code, population_id, societe_id, heure_debut, nbheures_garanties, cycle, dRC, fRC, date_debut, date_fin
  FROM  (SELECT employe_id, matricule, saloid, rcoid, relatnum, depot_id, flux_id, emploi_id, emploi_code, population_id, societe_id, heure_debut, nbheures_garanties, cycle, dRC, fRC, date_debut
                ,(SELECT MIN(t3.date_fin)
                  FROM  pai_int_png_info3 AS t3
                  WHERE t1.rcoid=t3.rcoid
                  AND t1.depot_id=t3.depot_id
                  AND t1.flux_id=t3.flux_id
                  AND t1.emploi_id=t3.emploi_id
                  AND t1.emploi_code=t3.emploi_code
                  AND t1.population_id=t3.population_id
                  AND t1.heure_debut=t3.heure_debut
                  AND t1.nbheures_garanties=t3.nbheures_garanties
                  AND t1.cycle=t3.cycle
                  AND t3.date_fin >= t1.date_debut
                  AND NOT EXISTS (SELECT null
                                  FROM  pai_int_png_info4 AS t4
                                  WHERE t4.rcoid=t3.rcoid
                                  AND t4.depot_id=t3.depot_id
                                  AND t4.flux_id=t3.flux_id
                                  AND t4.emploi_id=t3.emploi_id
                                  AND t4.emploi_code=t3.emploi_code
                                  AND t4.population_id=t3.population_id
                                  AND t4.heure_debut=t3.heure_debut
                                  AND t4.nbheures_garanties=t3.nbheures_garanties
                                  AND t4.cycle=t3.cycle
                                  AND t4.date_debut = t3.date_fin + INTERVAL 1 Day 
                                  )
                  ) AS date_fin
  FROM  pai_int_png_info AS t1
  WHERE NOT EXISTS (SELECT null
                    FROM  pai_int_png_info2 AS t2
                    WHERE t1.rcoid=t2.rcoid
                    AND t1.depot_id=t2.depot_id
                    AND t1.flux_id=t2.flux_id
                    AND t1.emploi_id=t2.emploi_id
                    AND t1.emploi_code=t2.emploi_code
                    AND t1.population_id=t2.population_id
                    AND t1.heure_debut=t2.heure_debut
                    AND t1.nbheures_garanties=t2.nbheures_garanties
                    AND t1.cycle=t2.cycle
                    AND t2.date_fin = t1.date_debut - INTERVAL 1 Day 
                    )
  ) as r1
--  ORDER BY employe_id, depot_id , flux_id, emploi_id, population_id,date_debut
  ;
  call int_logrowcount_C(_idtrt,5,'int_png2mroad_regroupement','interval')
  ;
  update pai_int_png_info_regroupement set depot_id=null where depot_id=0;
  update pai_int_png_info_regroupement set flux_id=null where flux_id=0;
  update pai_int_png_info_regroupement set emploi_code=null where emploi_code='';
  update pai_int_png_info_regroupement set emploi_id=null where emploi_id=0;
  update pai_int_png_info_regroupement set population_id=null where population_id=0;
  update pai_int_png_info_regroupement set cycle=null where cycle='';
  call int_logger(_idtrt,'int_png2mroad_regroupement','Fin');
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
-- Maj des tables employé et emp_pop_depot
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_png2mroad_maj_employe;
CREATE PROCEDURE int_png2mroad_maj_employe(_idtrt INTEGER, _depot_id INTEGER, _flux_id INTEGER, _date_int DATETIME)
BEGIN
  INSERT INTO pai_int_log(idtrt,date_log,module,msg) 
  SELECT distinct _idtrt,now(),'int_png2mroad_maj_employe',CONCAT_WS(' ','Ajout de',i.matricule,s.nom_usuel,s.prenom1,s.prenom2)
  FROM pai_int_png_info_regroupement i
  INNER JOIN pai_png_salarie s ON s.oid=i.saloid
  WHERE i.employe_id IS NULL
  AND substr(i.matricule,10,1)='0'
  AND (i.depot_id=_depot_id OR _depot_id is null)
  AND (i.flux_id=_flux_id OR _flux_id is null)
  ;
  INSERT INTO pai_int_log(idtrt,date_log,module,msg) 
  SELECT distinct _idtrt,now(),'int_png2mroad_maj_employe',CONCAT_WS(' ','Création impossible de',i.matricule,s.nom_usuel,s.prenom1,s.prenom2,' RC simultanées ',i.relatnum)
  FROM pai_int_png_info_regroupement i
  INNER JOIN pai_png_salarie s ON s.oid=i.saloid
  WHERE i.employe_id IS NULL
  AND substr(i.matricule,10,1)<>'0'
  AND (i.depot_id=_depot_id OR _depot_id is null)
  AND (i.flux_id=_flux_id OR _flux_id is null)
  ;
  INSERT INTO employe(saloid,matricule,nom,nom_patronymique,prenom1,prenom2,civilite_id,nationalite_id,date_creation)
  SELECT DISTINCT i.saloid,i.matricule,s.nom_usuel,s.nompatronymique,s.prenom1,s.prenom2,s.civilite_id,rn.id,now()
  FROM pai_int_png_info_regroupement i
  INNER JOIN pai_png_salarie s ON s.oid=i.saloid
  INNER JOIN pai_png_nationalite ppn ON s.nationalite=ppn.oid
  INNER JOIN ref_nationalite rn ON ppn.code=rn.code
  WHERE i.employe_id IS NULL
  AND substr(i.matricule,10,1)='0'
  AND (i.depot_id=_depot_id OR _depot_id is null)
  AND (i.flux_id=_flux_id OR _flux_id is null)
  ;
  call int_logrowcount_C(_idtrt,1,'int_png2mroad_maj_employe',concat_ws(' ','Ajout de',row_count(),'employé(s).'))
  ;
  INSERT INTO pai_int_log(idtrt,date_log,module,msg) 
  SELECT distinct _idtrt,now(),'int_png2mroad_maj_employe',CONCAT_WS(' ','Modification de',i.matricule,s.nom_usuel,s.prenom1,s.prenom2)
  FROM  employe e
  INNER JOIN pai_int_png_info_regroupement i ON e.matricule=i.matricule
  INNER JOIN pai_png_salarie s ON s.oid=i.saloid
  INNER JOIN pai_png_nationalite ppn ON s.nationalite=ppn.oid
  INNER JOIN ref_nationalite rn ON ppn.code=rn.code
  WHERE (i.depot_id=_depot_id OR _depot_id is null)
  AND   (i.flux_id=_flux_id OR _flux_id is null)
  AND    (e.saloid<>i.saloid OR    e.nom<>s.nom_usuel OR    coalesce(e.nom_patronymique,'')<>coalesce(s.nompatronymique,'') OR    e.prenom1<>s.prenom1 OR    coalesce(e.prenom2,'')<>coalesce(s.prenom2,'') OR    e.civilite_id<>s.civilite_id OR e.nationalite_id<>rn.id)
  ;
  UPDATE employe e
  INNER JOIN pai_int_png_info_regroupement i ON e.matricule=i.matricule
  INNER JOIN pai_png_salarie s ON s.oid=i.saloid
  INNER JOIN pai_png_nationalite ppn ON s.nationalite=ppn.oid
  INNER JOIN ref_nationalite rn ON ppn.code=rn.code
  SET e.saloid=i.saloid
  ,e.nom=s.nom_usuel
  ,e.nom_patronymique=s.nompatronymique
  ,e.prenom1=s.prenom1
  ,e.prenom2=s.prenom2
  ,e.civilite_id=s.civilite_id
  ,e.nationalite_id=rn.id
  ,e.date_modif=now()
  WHERE (i.depot_id=_depot_id OR _depot_id is null)
  AND   (i.flux_id=_flux_id OR _flux_id is null)
  AND    (e.saloid<>i.saloid OR    e.nom<>s.nom_usuel OR    coalesce(e.nom_patronymique,'')<>coalesce(s.nompatronymique,'') OR    e.prenom1<>s.prenom1 OR    coalesce(e.prenom2,'')<>coalesce(s.prenom2,'') OR    e.civilite_id<>s.civilite_id OR e.nationalite_id<>rn.id )
  ;
  call int_logrowcount_C(_idtrt,1,'int_png2mroad_maj_employe',concat_ws(' ','Mis à jour de',row_count(),'employé(s).'))
  ;
  UPDATE pai_int_png_info_regroupement i 
  SET employe_id=(SELECT e.id FROM employe e WHERE i.matricule=e.matricule) 
  WHERE i.employe_id IS NULL
  ;
  call int_logrowcount_C(_idtrt,5,'int_png2mroad_maj_employe','update')
  ;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_png2mroad_create_emp_in_depot_flux;
CREATE PROCEDURE int_png2mroad_create_emp_in_depot_flux(_idtrt INTEGER, _depot_id INTEGER, _flux_id INTEGER)
BEGIN
  -- On restreind aux employes qui ont appartenu au depot/flux
  drop temporary table if exists pai_int_png_emp_in_depot_flux;
  CREATE TEMPORARY TABLE pai_int_png_emp_in_depot_flux ENGINE = memory
  as select distinct employe_id
  from pai_int_png_info_regroupement i
  WHERE (i.depot_id=_depot_id OR _depot_id is null)
  AND   (i.flux_id=_flux_id OR _flux_id is null)
  union
  select distinct employe_id
  from emp_pop_depot i
  WHERE (i.depot_id=_depot_id OR _depot_id is null)
  AND   (i.flux_id=_flux_id OR _flux_id is null)
  AND   i.societe_id>0 -- On ne prend pas en compte les non-salariés (VCP)
  ;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_png2mroad_maj_emp_contrat;
CREATE PROCEDURE int_png2mroad_maj_emp_contrat(_idtrt INTEGER, _depot_id INTEGER, _flux_id INTEGER, _date_int DATETIME)
BEGIN
  DECLARE EXIT      HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
    RESIGNAL;
  END;
  START TRANSACTION;
  
  INSERT INTO pai_int_log(idtrt,date_log,module,msg) 
  SELECT distinct _idtrt,now(),'int_png2mroad_maj_emp_contrat',CONCAT_WS(' ','Modification du contrat pour',e.matricule,e.nom,e.prenom1,e.prenom2)
  FROM pai_int_png_info_regroupement i
  INNER JOIN pai_int_png_emp_in_depot_flux edf on i.employe_id=edf.employe_id
  INNER JOIN emp_contrat eco on  i.rcoid=eco.rcoid
  INNER JOIN employe e on eco.employe_id=e.id
  where eco.date_debut<>i.dRC
  or eco.date_fin<>i.fRC
  or eco.rc<>i.relatnum
  or eco.societe_id<>i.societe_id
  ;
  UPDATE pai_int_png_info_regroupement i
  INNER JOIN pai_int_png_emp_in_depot_flux edf on i.employe_id=edf.employe_id
  INNER JOIN emp_contrat eco on  i.rcoid=eco.rcoid
  set eco.date_debut=i.dRC
  ,eco.date_fin=i.fRC
  ,eco.rc=i.relatnum
  ,eco.societe_id=i.societe_id
  ,eco.date_modif=now()
  where eco.date_debut<>i.dRC
  or eco.date_fin<>i.fRC
  or eco.rc<>i.relatnum
  or eco.societe_id<>i.societe_id
  ;
  call int_logrowcount_C(_idtrt,1,'int_png2mroad_maj_emp_contrat',concat_ws(' ','Mis à jour de',row_count(),'contrats.'))
  ;
 
  INSERT INTO pai_int_log(idtrt,date_log,module,msg) 
  SELECT distinct _idtrt,now(),'int_png2mroad_maj_emp_contrat',CONCAT_WS(' ','Ajout du contrat pour',e.matricule,e.nom,e.prenom1,e.prenom2)
  FROM pai_int_png_info_regroupement i
  INNER JOIN pai_int_png_emp_in_depot_flux edf on i.employe_id=edf.employe_id
  INNER JOIN employe e on i.employe_id=e.id
  where not exists(select null from emp_contrat eco where i.rcoid=eco.rcoid)
  ;
  INSERT INTO emp_contrat(employe_id,date_debut,date_fin,rcoid,rc,societe_id,date_creation)
  SELECT distinct i.employe_id,i.dRC,i.fRC,i.rcoid,i.relatnum,i.societe_id,now()
  FROM pai_int_png_info_regroupement i
  INNER JOIN pai_int_png_emp_in_depot_flux edf on i.employe_id=edf.employe_id
  where not exists(select null from emp_contrat eco where i.rcoid=eco.rcoid)
  ;
  call int_logrowcount_C(_idtrt,1,'int_png2mroad_maj_emp_contrat',concat_ws(' ','Création de',row_count(),'contrats.'))
  ;
  
  INSERT INTO pai_int_log(idtrt,date_log,module,msg) 
  SELECT distinct _idtrt,now(),'int_png2mroad_maj_emp_contrat',CONCAT_WS(' ','Suppression du contrat pour',e.matricule,e.nom,e.prenom1,e.prenom2)
  FROM pai_int_png_emp_in_depot_flux edf
  INNER JOIN emp_contrat eco on eco.employe_id=edf.employe_id
  INNER JOIN employe e on eco.employe_id=e.id
  where not exists(select null from pai_int_png_info_regroupement i where i.rcoid=eco.rcoid)
  ;
  delete et 
  from pai_int_png_emp_in_depot_flux edf
  INNER JOIN emp_contrat eco on eco.employe_id=edf.employe_id
  INNER JOIN emp_transfert et on eco.id=et.contrat_id
  where not exists(select null from pai_int_png_info_regroupement i where i.rcoid=eco.rcoid)
  ;
  call int_logrowcount_C(_idtrt,1,'int_png2mroad_maj_emp_contrat',concat_ws(' ','Suppression de',row_count(),'transferts.'))
  ;
  delete eaf 
  from pai_int_png_emp_in_depot_flux edf
  INNER JOIN emp_contrat eco on eco.employe_id=edf.employe_id
  INNER JOIN emp_affectation eaf on eco.id=eaf.contrat_id
  where not exists(select null from pai_int_png_info_regroupement i where i.rcoid=eco.rcoid)
  ;
  call int_logrowcount_C(_idtrt,1,'int_png2mroad_maj_emp_contrat',concat_ws(' ','Suppression de',row_count(),'affectations.'))
  ;
  delete ect 
  from pai_int_png_emp_in_depot_flux edf
  INNER JOIN emp_contrat eco on eco.employe_id=edf.employe_id
  INNER JOIN emp_contrat_type ect on eco.id=ect.contrat_id
  where not exists(select null from pai_int_png_info_regroupement i where i.rcoid=eco.rcoid)
  ;
  call int_logrowcount_C(_idtrt,1,'int_png2mroad_maj_emp_contrat',concat_ws(' ','Suppression de',row_count(),'contrats type.'))
  ;
  delete eco from pai_int_png_emp_in_depot_flux edf
  INNER JOIN emp_contrat eco on eco.employe_id=edf.employe_id
  where not exists(select null from pai_int_png_info_regroupement i where i.rcoid=eco.rcoid)
  ;
  call int_logrowcount_C(_idtrt,1,'int_png2mroad_maj_emp_contrat',concat_ws(' ','Suppression de',row_count(),'contrats.'))
  ;
  ALTER TABLE pai_int_png_info_regroupement ADD contrat_id INT;
  UPDATE pai_int_png_info_regroupement i
  INNER JOIN pai_int_png_emp_in_depot_flux edf on i.employe_id=edf.employe_id
  INNER JOIN emp_contrat eco on  i.rcoid=eco.rcoid
  set i.contrat_id=eco.id
  ;
  COMMIT;
END;

-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_png2mroad_maj_emp_contrat_type;
CREATE PROCEDURE int_png2mroad_maj_emp_contrat_type(_idtrt INTEGER, _depot_id INTEGER, _flux_id INTEGER, _date_int DATETIME)
BEGIN
  DECLARE EXIT      HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
    RESIGNAL;
  END;
/*  drop temporary table if exists pai_int_png_info_regroupement2;
  create temporary table pai_int_png_info_regroupement2 engine=memory as select * from pai_int_png_info_regroupement;
  CREATE INDEX pai_int_png_info_regroupement2_idx ON pai_int_png_info_regroupement2(rcoid, date_fin, population_id);
  */
  drop temporary table if exists pai_int_png_contrat_type;
  CREATE TEMPORARY TABLE pai_int_png_contrat_type engine=memory  as 
  select distinct eco.employe_id,eco.id as contrat_id,eco.rcoid,rc.begin_date as date_debut,rc.end_date as date_fin,if(rc.contrattypecode='CDD',rc.cdddatefinprevu,null) as date_fin_prevue, rtc.id as typecontrat_id, r.id as remplace_id
  from pai_int_png_emp_in_depot_flux edf
  INNER JOIN emp_contrat eco on eco.employe_id=edf.employe_id
  INNER JOIN employe e ON eco.employe_id=e.id
  INNER JOIN pai_png_relationc rc on e.saloid=rc.relatmatricule and eco.rc=rc.relatnum
  INNER JOIN ref_typecontrat rtc on rc.contrattypecode=rtc.code
  -- Remplacant
  LEFT OUTER JOIN pai_png_relationcontrat rc2 on rc.salremplaw=rc2.oid
  LEFT OUTER JOIN pai_png_salarie s ON s.oid=rc2.relatmatricule
  LEFT OUTER JOIN pai_png_salpopulationw po ON po.salarie=rc2.relatmatricule AND po.numrc=rc2.relatnum AND  rc2.relatdatedeb BETWEEN po.begin_date AND po.end_date
  LEFT OUTER JOIN employe r ON r.matricule=CONCAT(s.matricule,po.nocarrierealpha)
  ;
/*
--  drop temporary table if exists pai_int_png_info_regroupement2;
  drop temporary table if exists pai_int_png_contrat_type2;
  create temporary table pai_int_png_contrat_type2 engine=memory as select * from pai_int_png_contrat_type;
  CREATE INDEX pai_int_png_contrat_type2_idx ON pai_int_png_contrat_type2(rcoid, date_debut);
  CREATE INDEX pai_int_png_contrat_type_idx ON pai_int_png_contrat_type2(contrat_id, date_debut);

  update pai_int_png_contrat_type i
  set i.date_fin=coalesce((select date_add(min(i2.date_debut),interval -1 day) from pai_int_png_contrat_type2 i2 where i.rcoid=i2.rcoid and i2.date_debut>i.date_debut),'2999-01-01')
  ;
  drop temporary table if exists pai_int_png_contrat_type2;
*/ 
  START TRANSACTION;
  INSERT INTO pai_int_log(idtrt,date_log,module,msg) 
  SELECT distinct _idtrt,now(),'int_png2mroad_maj_emp_contrat_type',CONCAT_WS(' ','Modification du contrat type pour',e.matricule,e.nom,e.prenom1,e.prenom2)
  FROM pai_int_png_contrat_type i
  INNER JOIN emp_contrat_type ect on i.contrat_id=ect.contrat_id and i.date_debut=ect.date_debut
  INNER JOIN employe e on i.employe_id=e.id
  where ect.date_fin<>i.date_fin
  or coalesce(ect.date_fin_prevue,'2999-01-01')<>coalesce(i.date_fin_prevue,'2999-01-01')
  or ect.typecontrat_id<>i.typecontrat_id
  or coalesce(ect.remplace_id,0)<>coalesce(i.remplace_id,0)
  ;
  UPDATE emp_contrat_type ect 
  INNER JOIN pai_int_png_contrat_type i on i.contrat_id=ect.contrat_id and i.date_debut=ect.date_debut
  set ect.date_fin=i.date_fin
  ,ect.date_fin_prevue=i.date_fin_prevue
  ,ect.typecontrat_id=i.typecontrat_id
  ,ect.remplace_id=i.remplace_id
  ,ect.date_modif=now()
  where ect.date_fin<>i.date_fin
  or coalesce(ect.date_fin_prevue,'2999-01-01')<>coalesce(i.date_fin_prevue,'2999-01-01')
  or ect.typecontrat_id<>i.typecontrat_id
  or coalesce(ect.remplace_id,0)<>coalesce(i.remplace_id,0)
  ;
  call int_logrowcount_C(_idtrt,1,'int_png2mroad_maj_emp_contrat_type',concat_ws(' ','Mis à jour de',row_count(),'contrats type.'))
  ;
  INSERT INTO pai_int_log(idtrt,date_log,module,msg) 
  SELECT distinct _idtrt,now(),'int_png2mroad_maj_emp_contrat_type',CONCAT_WS(' ','Ajout du contrat type pour',e.matricule,e.nom,e.prenom1,e.prenom2)
  FROM pai_int_png_contrat_type i
  INNER JOIN employe e on i.employe_id=e.id
  where not exists(select null from emp_contrat_type ect where i.contrat_id=ect.contrat_id and i.date_debut=ect.date_debut)
  ;
  INSERT INTO emp_contrat_type(contrat_id,date_debut,date_fin,date_fin_prevue,typecontrat_id,remplace_id,date_creation)
  SELECT i.contrat_id,i.date_debut,i.date_fin_prevue,i.date_fin,i.typecontrat_id,i.remplace_id,now()
  FROM pai_int_png_contrat_type i
  where not exists(select null from emp_contrat_type ect where i.contrat_id=ect.contrat_id and i.date_debut=ect.date_debut)
  ;
  call int_logrowcount_C(_idtrt,1,'int_png2mroad_maj_emp_contrat_type',concat_ws(' ','Création de',row_count(),'contrats type.'))
  ;
  
  INSERT INTO pai_int_log(idtrt,date_log,module,msg) 
  SELECT distinct _idtrt,now(),'int_png2mroad_maj_emp_contrat_type',CONCAT_WS(' ','Suppression du contrat type pour',e.matricule,e.nom,e.prenom1,e.prenom2)
  FROM  emp_contrat_type ect
  INNER JOIN emp_contrat eco on ect.contrat_id=eco.id
  INNER JOIN pai_int_png_emp_in_depot_flux edf on eco.employe_id=edf.employe_id
  INNER JOIN employe e on eco.employe_id=e.id
  where not exists(select null from pai_int_png_contrat_type i where i.contrat_id=ect.contrat_id and i.date_debut=ect.date_debut)
  ;
  delete ect from emp_contrat_type ect
  INNER JOIN emp_contrat eco on ect.contrat_id=eco.id
  INNER JOIN pai_int_png_emp_in_depot_flux edf on eco.employe_id=edf.employe_id
  where not exists(select null from pai_int_png_contrat_type i where i.contrat_id=ect.contrat_id and i.date_debut=ect.date_debut)  ;
  call int_logrowcount_C(_idtrt,1,'int_png2mroad_maj_emp_contrat_type',concat_ws(' ','Suppression de',row_count(),'contrats type.'))
  ;
  ALTER TABLE pai_int_png_info_regroupement ADD contrattype_id INT;
  UPDATE pai_int_png_info_regroupement i
  INNER JOIN pai_int_png_emp_in_depot_flux edf on i.employe_id=edf.employe_id
  INNER JOIN emp_contrat_type ect on i.contrat_id=ect.contrat_id and i.date_debut between ect.date_debut and ect.date_fin
  set i.contrattype_id=ect.id
  ;
  COMMIT;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_png2mroad_maj_cycle;
CREATE PROCEDURE int_png2mroad_maj_cycle(_idtrt INTEGER, _depot_id INTEGER, _flux_id INTEGER, _date_int DATETIME)
BEGIN
  DECLARE EXIT      HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
    RESIGNAL;
  END;
  START TRANSACTION;
  
  INSERT INTO pai_int_log(idtrt,date_log,module,msg) 
  SELECT distinct _idtrt,now(),'int_png2mroad_maj_cycle',CONCAT_WS(' ','Modification du cycle pour',e.matricule,e.nom,e.prenom1,e.prenom2)
  from emp_cycle ecy
  INNER JOIN pai_int_png_emp_in_depot_flux edf on ecy.employe_id=edf.employe_id
  inner join emp_contrat eco on edf.employe_id=eco.employe_id
  inner join pai_png_horaire h on h.horcontr=eco.rcoid
  inner join pai_png_semainetype st on h.horsemainetype =st.oid
  INNER JOIN employe e on ecy.employe_id=e.id
  where ecy.date_debut=greatest(eco.date_debut,h.begin_date)
  and (ecy.date_fin<>least(eco.date_fin,h.end_date)
  or ecy.cyc_cod<>st.semtypecode
  or ecy.lundi<>(st.semtypedurlun<>0)
  or ecy.mardi<>(st.semtypedurmar<>0)
  or ecy.mercredi<>(st.semtypedurmer<>0)
  or ecy.jeudi<>(st.semtypedurjeu<>0)
  or ecy.vendredi<>(st.semtypedurven<>0)
  or ecy.samedi<>(st.semtypedursam<>0)
  or ecy.dimanche<>(st.semtypedurdim<>0)
  )
  ;
  UPDATE emp_cycle ecy
  INNER JOIN pai_int_png_emp_in_depot_flux edf on ecy.employe_id=edf.employe_id
  inner join emp_contrat eco on edf.employe_id=eco.employe_id
  inner join pai_png_horaire h on h.horcontr=eco.rcoid
  inner join pai_png_semainetype st on h.horsemainetype =st.oid
  set ecy.date_fin=least(eco.date_fin,h.end_date)
  , ecy.cyc_cod=st.semtypecode
  , ecy.cycle=cycle_to_string(st.semtypedurlun<>0,st.semtypedurmar<>0,st.semtypedurmer<>0,st.semtypedurjeu<>0,st.semtypedurven<>0,st.semtypedursam<>0,st.semtypedurdim<>0)
  , ecy.lundi=(st.semtypedurlun<>0)
  , ecy.mardi=(st.semtypedurmar<>0)
  , ecy.mercredi=(st.semtypedurmer<>0)
  , ecy.jeudi=(st.semtypedurjeu<>0)
  , ecy.vendredi=(st.semtypedurven<>0)
  , ecy.samedi=(st.semtypedursam<>0)
  , ecy.dimanche=(st.semtypedurdim<>0)
  , ecy.date_modif=now()
  where ecy.date_debut=greatest(eco.date_debut,h.begin_date)
  and (ecy.date_fin<>least(eco.date_fin,h.end_date)
  or ecy.cycle<>cycle_to_string(st.semtypedurlun<>0,st.semtypedurmar<>0,st.semtypedurmer<>0,st.semtypedurjeu<>0,st.semtypedurven<>0,st.semtypedursam<>0,st.semtypedurdim<>0)
  or ecy.cyc_cod<>st.semtypecode
  or ecy.lundi<>(st.semtypedurlun<>0)
  or ecy.mardi<>(st.semtypedurmar<>0)
  or ecy.mercredi<>(st.semtypedurmer<>0)
  or ecy.jeudi<>(st.semtypedurjeu<>0)
  or ecy.vendredi<>(st.semtypedurven<>0)
  or ecy.samedi<>(st.semtypedursam<>0)
  or ecy.dimanche<>(st.semtypedurdim<>0)
  );
  call int_logrowcount(_idtrt,1,'int_png2mroad_maj_cycle',concat_ws(' ','Mis à jour de',row_count(),'cycles.'))
  ;

  INSERT INTO pai_int_log(idtrt,date_log,module,msg) 
  SELECT distinct _idtrt,now(),'int_png2mroad_maj_cycle',CONCAT_WS(' ','Ajout du cycle pour',e.matricule,e.nom,e.prenom1,e.prenom2)
  from emp_contrat eco
  INNER JOIN pai_int_png_emp_in_depot_flux edf on eco.employe_id=edf.employe_id
  inner join pai_png_horaire h on h.horcontr=eco.rcoid
  inner join pai_png_semainetype st on h.horsemainetype =st.oid
  INNER JOIN employe e on eco.employe_id=e.id
  where not exists(select null from emp_cycle ecy where ecy.employe_id=eco.employe_id and ecy.date_debut=greatest(eco.date_debut,h.begin_date))
  ;
  INSERT INTO emp_cycle(employe_id,date_debut,date_fin,cyc_cod,cycle,lundi,mardi,mercredi,jeudi,vendredi,samedi,dimanche,date_creation)
  select distinct eco.employe_id,greatest(eco.date_debut,h.begin_date),least(eco.date_fin,h.end_date),st.semtypecode
  ,cycle_to_string(st.semtypedurlun<>0,st.semtypedurmar<>0,st.semtypedurmer<>0,st.semtypedurjeu<>0,st.semtypedurven<>0,st.semtypedursam<>0,st.semtypedurdim<>0)
  ,st.semtypedurlun<>0,st.semtypedurmar<>0,st.semtypedurmer<>0,st.semtypedurjeu<>0,st.semtypedurven<>0,st.semtypedursam<>0,st.semtypedurdim<>0,now()
  from emp_contrat eco
  INNER JOIN pai_int_png_emp_in_depot_flux edf on eco.employe_id=edf.employe_id
  inner join pai_png_horaire h on h.horcontr=eco.rcoid
  inner join pai_png_semainetype st on h.horsemainetype =st.oid
  where not exists(select null from emp_cycle ecy where ecy.employe_id=eco.employe_id and ecy.date_debut=greatest(eco.date_debut,h.begin_date))
  ;
  call int_logrowcount(_idtrt,1,'int_png2mroad_maj_cycle',concat_ws(' ','Création de',row_count(),'cycles.'))
  ;

  INSERT INTO pai_int_log(idtrt,date_log,module,msg) 
  SELECT distinct _idtrt,now(),'int_png2mroad_maj_cycle',CONCAT_WS(' ','Suppression du cycle pour',e.matricule,e.nom,e.prenom1,e.prenom2)
  from emp_cycle ecy
  INNER JOIN pai_int_png_emp_in_depot_flux edf on ecy.employe_id=edf.employe_id
  INNER JOIN employe e on ecy.employe_id=e.id
  where not exists(select null 
                  from emp_contrat eco
                  inner join pai_png_horaire h on h.horcontr=eco.rcoid 
                  where ecy.employe_id=eco.employe_id and ecy.date_debut=greatest(eco.date_debut,h.begin_date))
  ;
  -- Attention si modele_remplacement existant !!!!
  delete ecy from emp_cycle ecy
  INNER JOIN pai_int_png_emp_in_depot_flux edf on ecy.employe_id=edf.employe_id
  where not exists(select null 
                  from emp_contrat eco
                  inner join pai_png_horaire h on h.horcontr=eco.rcoid
                  where ecy.employe_id=eco.employe_id and ecy.date_debut=greatest(eco.date_debut,h.begin_date));
  call int_logrowcount(_idtrt,1,'int_png2mroad_maj_cycle',concat_ws(' ','Suppression de',row_count(),'cycles.'));

  ALTER TABLE pai_int_png_info_regroupement ADD cycle_id INT;
  UPDATE pai_int_png_info_regroupement i
  INNER JOIN pai_int_png_emp_in_depot_flux edf on i.employe_id=edf.employe_id
  INNER JOIN emp_cycle ecy on i.employe_id=ecy.employe_id and i.date_debut between ecy.date_debut and ecy.date_fin
  set i.cycle_id=ecy.id
  ;
 COMMIT;
 END;

-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_png2mroad_maj_emp_pop_depot;
CREATE PROCEDURE int_png2mroad_maj_emp_pop_depot(_idtrt INTEGER, _depot_id INTEGER, _flux_id INTEGER, _date_int DATETIME)
BEGIN
  DECLARE EXIT      HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
    RESIGNAL;
  END;
  START TRANSACTION;
  
  INSERT INTO pai_int_log(idtrt,date_log,module,msg) 
  SELECT distinct _idtrt,now(),'int_png2mroad_maj_emp_pop_depot',CONCAT_WS(' ','Modification d''une période pour',e.matricule,e.nom,e.prenom1,e.prenom2)
  FROM pai_int_png_info_regroupement i
  INNER JOIN pai_int_png_emp_in_depot_flux edf on i.employe_id=edf.employe_id
  INNER JOIN emp_pop_depot epd on  i.employe_id=epd.employe_id and i.date_debut=epd.date_debut
  INNER JOIN ref_population rp ON i.population_id=rp.id
  inner join emp_cycle ecy on ecy.employe_id=epd.employe_id and i.date_debut between ecy.date_debut and ecy.date_fin
  INNER JOIN employe e on epd.employe_id=e.id
  where epd.dRC<>i.dRC
  or epd.fRC<>i.fRC
  or epd.date_fin<>i.date_fin
  or epd.rcoid<>i.rcoid
  or epd.depot_id<>i.depot_id
  or epd.flux_id<>i.flux_id
  or epd.emploi_id<>i.emploi_id
  or epd.contrat_id<>i.contrat_id
  or epd.contrattype_id<>i.contrattype_id
  or epd.cycle_id<>ecy.id
  or epd.rc<>i.relatnum
  or epd.societe_id<>i.societe_id
  or epd.population_id<>rp.id
  or epd.typetournee_id<>rp.typetournee_id
  or epd.typeurssaf_id<>rp.typeurssaf_id
  or epd.typecontrat_id<>rp.typecontrat_id
  or epd.heure_debut<>i.heure_debut
  or epd.nbheures_garanties<>i.nbheures_garanties
  or epd.km_paye<>rp.km_paye
  or epd.cycle<>ecy.cycle
  or epd.lundi<>ecy.lundi
  or epd.mardi<>ecy.mardi
  or epd.mercredi<>ecy.mercredi
  or epd.jeudi<>ecy.jeudi
  or epd.vendredi<>ecy.vendredi
  or epd.samedi<>ecy.samedi
  or epd.dimanche<>ecy.dimanche
  ;
  UPDATE pai_int_png_info_regroupement i
  INNER JOIN pai_int_png_emp_in_depot_flux edf on i.employe_id=edf.employe_id
  INNER JOIN emp_pop_depot epd on  i.employe_id=epd.employe_id and i.date_debut=epd.date_debut
  INNER JOIN ref_population rp ON i.population_id=rp.id
  inner join emp_cycle ecy on ecy.employe_id=epd.employe_id and i.date_debut between ecy.date_debut and ecy.date_fin
  set epd.dRC=i.dRC
  ,epd.fRC=i.fRC
  ,epd.date_fin=i.date_fin
  ,epd.rcoid=i.rcoid
  ,epd.depot_id=i.depot_id
  ,epd.flux_id=i.flux_id
  ,epd.contrat_id=i.contrat_id
  ,epd.contrattype_id=i.contrattype_id
  ,epd.cycle_id=ecy.id
  ,epd.emploi_id=i.emploi_id
  ,epd.rc=i.relatnum
  ,epd.societe_id=i.societe_id
  ,epd.population_id=rp.id
  ,epd.typetournee_id=rp.typetournee_id
  ,epd.typeurssaf_id=rp.typeurssaf_id
  ,epd.typecontrat_id=rp.typecontrat_id
  ,epd.heure_debut=i.heure_debut
  ,epd.nbheures_garanties=i.nbheures_garanties
  ,epd.km_paye=rp.km_paye
  ,epd.cycle=ecy.cycle
  ,epd.lundi=ecy.lundi
  ,epd.mardi=ecy.mardi
  ,epd.mercredi=ecy.mercredi
  ,epd.jeudi=ecy.jeudi
  ,epd.vendredi=ecy.vendredi
  ,epd.samedi=ecy.samedi
  ,epd.dimanche=ecy.dimanche
  ,epd.date_modif=now()
  where epd.dRC<>i.dRC
  or epd.fRC<>i.fRC
  or epd.date_fin<>i.date_fin
  or epd.rcoid<>i.rcoid
  or epd.depot_id<>i.depot_id
  or epd.flux_id<>i.flux_id
  or epd.contrat_id<>i.contrat_id
  or epd.contrattype_id<>i.contrattype_id
  or epd.cycle_id<>ecy.id
  or epd.emploi_id<>i.emploi_id
  or epd.rc<>i.relatnum
  or epd.societe_id<>i.societe_id
  or epd.population_id<>rp.id
  or epd.typetournee_id<>rp.typetournee_id
  or epd.typeurssaf_id<>rp.typeurssaf_id
  or epd.typecontrat_id<>rp.typecontrat_id
  or epd.heure_debut<>i.heure_debut
  or epd.nbheures_garanties<>i.nbheures_garanties
  or epd.km_paye<>rp.km_paye
  or epd.cycle<>ecy.cycle
  or epd.lundi<>ecy.lundi
  or epd.mardi<>ecy.mardi
  or epd.mercredi<>ecy.mercredi
  or epd.jeudi<>ecy.jeudi
  or epd.vendredi<>ecy.vendredi
  or epd.samedi<>ecy.samedi
  or epd.dimanche<>ecy.dimanche
  ;
  call int_logrowcount_C(_idtrt,1,'int_png2mroad_maj_emp_pop_depot',concat_ws(' ','Mis à jour de',row_count(),'périodes.'))
  ;
 
  INSERT INTO pai_int_log(idtrt,date_log,module,msg) 
  SELECT distinct _idtrt,now(),'int_png2mroad_maj_emp_pop_depot',CONCAT_WS(' ','Absence de cycle pour',e.matricule,e.nom,e.prenom1,e.prenom2,'le',i.date_debut)
  FROM pai_int_png_info_regroupement i
  INNER JOIN pai_int_png_emp_in_depot_flux edf on i.employe_id=edf.employe_id
  INNER JOIN employe e on i.employe_id=e.id
  left outer join emp_cycle ecy on ecy.employe_id=i.employe_id and i.date_debut between ecy.date_debut and ecy.date_fin
  where not exists(select null from emp_pop_depot epd where i.employe_id=epd.employe_id and i.date_debut=epd.date_debut)
  and ecy.id is null
  ;
  INSERT INTO pai_int_log(idtrt,date_log,module,msg) 
  SELECT distinct _idtrt,now(),'int_png2mroad_maj_emp_pop_depot',CONCAT_WS(' ','Ajout d''une période pour',e.matricule,e.nom,e.prenom1,e.prenom2)
  FROM pai_int_png_info_regroupement i
  INNER JOIN pai_int_png_emp_in_depot_flux edf on i.employe_id=edf.employe_id
  INNER JOIN employe e on i.employe_id=e.id
  inner join emp_cycle ecy on ecy.employe_id=i.employe_id and i.date_debut between ecy.date_debut and ecy.date_fin
  where not exists(select null from emp_pop_depot epd where i.employe_id=epd.employe_id and i.date_debut=epd.date_debut)
  ;
  INSERT INTO emp_pop_depot(employe_id,dRC,fRC,date_debut,date_fin,rcoid,depot_id,flux_id,contrat_id,contrattype_id,cycle_id,emploi_id,rc,societe_id,population_id,typetournee_id,typeurssaf_id,typecontrat_id,heure_debut,nbheures_garanties,km_paye,cycle,lundi,mardi,mercredi,jeudi,vendredi,samedi,dimanche,date_creation)
  SELECT i.employe_id,i.dRC,i.fRC,i.date_debut,i.date_fin,i.rcoid,i.depot_id,i.flux_id,i.contrat_id,i.contrattype_id,ecy.id,i.emploi_id,i.relatnum
    ,i.societe_id
    ,rp.id
    ,rp.typetournee_id
    ,rp.typeurssaf_id
    ,rp.typecontrat_id
    ,i.heure_debut
    ,i.nbheures_garanties
    ,rp.km_paye
    ,ecy.cycle
    ,ecy.lundi
    ,ecy.mardi
    ,ecy.mercredi
    ,ecy.jeudi
    ,ecy.vendredi
    ,ecy.samedi
    ,ecy.dimanche
    ,now()
  FROM pai_int_png_info_regroupement i
  INNER JOIN pai_int_png_emp_in_depot_flux edf on i.employe_id=edf.employe_id
  INNER JOIN ref_population rp ON i.population_id=rp.id
  inner join emp_cycle ecy on ecy.employe_id=i.employe_id and i.date_debut between ecy.date_debut and ecy.date_fin
  where not exists(select null from emp_pop_depot epd where i.employe_id=epd.employe_id and i.date_debut=epd.date_debut)
  ;
  call int_logrowcount_C(_idtrt,1,'int_png2mroad_maj_emp_pop_depot',concat_ws(' ','Création de',row_count(),'périodes.'))
  ;
  
  INSERT INTO pai_int_log(idtrt,date_log,module,msg) 
  SELECT distinct _idtrt,now(),'int_png2mroad_maj_emp_pop_depot',CONCAT_WS(' ','Suppression d''une période pour',e.matricule,e.nom,e.prenom1,e.prenom2)
  FROM pai_int_png_emp_in_depot_flux edf
  INNER JOIN emp_pop_depot epd on epd.employe_id=edf.employe_id
  INNER JOIN employe e on epd.employe_id=e.id
  where not exists(select null from pai_int_png_info_regroupement i where i.employe_id=epd.employe_id and i.date_debut=epd.date_debut)
  ;
  delete epd from pai_int_png_emp_in_depot_flux edf
  INNER JOIN emp_pop_depot epd on epd.employe_id=edf.employe_id
  where not exists(select null from pai_int_png_info_regroupement i where i.employe_id=epd.employe_id and i.date_debut=epd.date_debut)
  ;
  call int_logrowcount_C(_idtrt,1,'int_png2mroad_maj_emp_pop_depot',concat_ws(' ','Suppression de',row_count(),'périodes.'))
  ;
  COMMIT;
END;

-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_png2mroad_maj_contrat_hp;
CREATE PROCEDURE int_png2mroad_maj_contrat_hp(_idtrt INTEGER, _depot_id INTEGER, _flux_id INTEGER, _date_int DATETIME)
BEGIN
  UPDATE pai_png_xrcautreactivit xa
  INNER JOIN pai_png_relationcontrat rc on xa.relationcontrat=rc.oid
  INNER JOIN pai_png_salarie s on rc.relatmatricule=s.oid
  INNER JOIN pai_png_salpopulationw po ON po.salarie=s.oid AND po.numrc=rc.relatnum AND  xa.begin_date BETWEEN po.begin_date AND po.end_date
  INNER JOIN employe e ON e.matricule=CONCAT(s.matricule,po.nocarrierealpha)
  SET xa.employe_id=e.id;

  UPDATE emp_contrat_hp ech
  INNER JOIN pai_png_xrcautreactivit xa on ech.xaoid=xa.oid
  SET ech.employe_id = xa.employe_id
  , ech.depot_id = xa.depot_id
  , ech.flux_id = xa.flux_id
  , ech.activite_id = xa.activite_id
  , ech.date_debut = xa.begin_date
  , ech.date_fin = xa.end_date
  , ech.lundi = xa.trvlundi
  , ech.mardi = xa.trvmardi
  , ech.mercredi = xa.trvmercredi
  , ech.jeudi = xa.trvjeudi
  , ech.vendredi = xa.trvvendredi
  , ech.samedi = xa.trvsamedi
  , ech.dimanche = xa.trvdimanche
  , ech.heure_debut_lundi = sec_to_time(xa.heuredeblun)
  , ech.heure_debut_mardi = sec_to_time(xa.heuredebmar)
  , ech.heure_debut_mercredi = sec_to_time(xa.heuredebmer)
  , ech.heure_debut_jeudi = sec_to_time(xa.heuredebjeu)
  , ech.heure_debut_vendredi = sec_to_time(xa.heuredebven)
  , ech.heure_debut_samedi = sec_to_time(xa.heuredebsam)
  , ech.heure_debut_dimanche = sec_to_time(xa.heuredebdim)
  , ech.nbheures_lundi = xa.nbheurelun
  , ech.nbheures_mardi = xa.nbheuremar
  , ech.nbheures_mercredi = xa.nbheuremer
  , ech.nbheures_jeudi = xa.nbheurejeu
  , ech.nbheures_vendredi = xa.nbheureven
  , ech.nbheures_samedi = xa.nbheuresam
  , ech.nbheures_dimanche = xa.nbheuredim
  , ech.heure_debut = sec_to_time(xa.heuredebctr)
  , ech.nbheures_jour = xa.nbheurejr
  , ech.nbheures_mensuel = xa.nbrheuremensuel
  , ech.travhorspresse = xa.travhorspresse
  , ech.xaoid = xa.oid
  , ech.rcoid = xa.relationcontrat
  , ech.ordre = xa.ordre
  , ech.xta_rcactivte = xa.xta_rcactivte
  , ech.xta_rcmetier = xa.xta_rcmetier
  , ech.xta_rcactivhpre = xa.xta_rcactivhpre
  , ech.date_modif = now()
  , ech.utilisateur_id = 0
  WHERE ech.employe_id <> xa.employe_id
  OR ech.depot_id <> xa.depot_id
  OR ech.flux_id <> xa.flux_id
  OR ech.activite_id <> xa.activite_id
  OR ech.date_debut <> xa.begin_date
  OR ech.date_fin <> xa.end_date
  OR ech.lundi <> xa.trvlundi
  OR ech.mardi <> xa.trvmardi
  OR ech.mercredi <> xa.trvmercredi
  OR ech.jeudi <> xa.trvjeudi
  OR ech.vendredi <> xa.trvvendredi
  OR ech.samedi <> xa.trvsamedi
  OR ech.dimanche <> xa.trvdimanche
  OR ech.heure_debut_lundi <> sec_to_time(xa.heuredeblun)
  OR ech.heure_debut_mardi <> sec_to_time(xa.heuredebmar)
  OR ech.heure_debut_mercredi <> sec_to_time(xa.heuredebmer)
  OR ech.heure_debut_jeudi <> sec_to_time(xa.heuredebjeu)
  OR ech.heure_debut_vendredi <> sec_to_time(xa.heuredebven)
  OR ech.heure_debut_samedi <> sec_to_time(xa.heuredebsam)
  OR ech.heure_debut_dimanche <> sec_to_time(xa.heuredebdim)
  OR ech.nbheures_lundi <> xa.nbheurelun
  OR ech.nbheures_mardi <> xa.nbheuremar
  OR ech.nbheures_mercredi <> xa.nbheuremer
  OR ech.nbheures_jeudi <> xa.nbheurejeu
  OR ech.nbheures_vendredi <> xa.nbheureven
  OR ech.nbheures_samedi <> xa.nbheuresam
  OR ech.nbheures_dimanche <> xa.nbheuredim
  OR ech.heure_debut <> sec_to_time(xa.heuredebctr)
  OR ech.nbheures_jour <> xa.nbheurejr
  OR ech.nbheures_mensuel <> xa.nbrheuremensuel
  OR ech.travhorspresse <> xa.travhorspresse
  OR ech.rcoid <> xa.relationcontrat
  OR ech.ordre <> xa.ordre
  OR ech.xta_rcactivte <> xa.xta_rcactivte
  OR ech.xta_rcmetier <> xa.xta_rcmetier
  OR ech.xta_rcactivhpre <> xa.xta_rcactivhpre
  ;
  call int_logrowcount(_idtrt,1,'int_png2mroad_maj_contrat_hp',concat_ws(' ','Mis à jour de',row_count(),'contrat hp.'));

  INSERT INTO emp_contrat_hp
  ( employe_id, depot_id, flux_id, activite_id
  , date_debut, date_fin
  , lundi, mardi, mercredi, jeudi, vendredi, samedi, dimanche
  , heure_debut_lundi, heure_debut_mardi, heure_debut_mercredi, heure_debut_jeudi, heure_debut_vendredi, heure_debut_samedi, heure_debut_dimanche
  , nbheures_lundi, nbheures_mardi, nbheures_mercredi, nbheures_jeudi, nbheures_vendredi, nbheures_samedi, nbheures_dimanche
  , heure_debut, nbheures_jour, nbheures_mensuel, travhorspresse
  , date_creation, utilisateur_id
  , xaoid, rcoid, ordre
  , xta_rcactivte, xta_rcmetier, xta_rcactivhpre
  ) SELECT xa.employe_id, xa.depot_id, xa.flux_id, xa.activite_id
  , xa.begin_date, xa.end_date
  , xa.trvlundi, xa.trvmardi, xa.trvmercredi, xa.trvjeudi, xa.trvvendredi, xa.trvsamedi, xa.trvdimanche
  , sec_to_time(xa.heuredeblun), sec_to_time(xa.heuredebmar), sec_to_time(xa.heuredebmer), sec_to_time(xa.heuredebjeu), sec_to_time(xa.heuredebven), sec_to_time(xa.heuredebsam), sec_to_time(xa.heuredebdim)
  , xa.nbheurelun, xa.nbheuremar, xa.nbheuremer, xa.nbheurejeu, xa.nbheureven, xa.nbheuresam, xa.nbheuredim
  , sec_to_time(xa.heuredebctr), xa.nbheurejr, xa.nbrheuremensuel, xa.travhorspresse
  , now(), 0
  , xa.oid, xa.relationcontrat, xa.ordre
  , xa.xta_rcactivte, xa.xta_rcmetier, xa.xta_rcactivhpre
  FROM pai_png_xrcautreactivit xa
  WHERE xa.oid not in (select xaoid from emp_contrat_hp)
  ;
  call int_logrowcount(_idtrt,1,'int_png2mroad_maj_contrat_hp',concat_ws(' ','Ajout de',row_count(),'contrat hp.'));
  
  DELETE ech FROM emp_contrat_hp ech
  WHERE  NOT EXISTS (SELECT null FROM pai_png_xrcautreactivit xa WHERE xa.oid=ech.xaoid)
  ;
  call int_logrowcount(_idtrt,1,'int_png2mroad_maj_contrat_hp',concat_ws(' ','Suppression de',row_count(),'contrat hp.'));
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_png2mroad_maj_remplacement;
CREATE PROCEDURE int_png2mroad_maj_remplacement(_idtrt INTEGER, _depot_id INTEGER, _flux_id INTEGER, _date_int DATETIME)
BEGIN
declare _validation_id INT;

  delete mj
  from modele_remplacement mr
  inner join modele_journal mj on mj.remplacement_id=mr.id
  inner join emp_contrat_type ect on mr.contrattype_id=ect.id
 -- INNER JOIN pai_int_png_emp_in_depot_flux edf on mr.employe_id=edf.employe_id
  INNER JOIN pai_mois pm on pm.flux_id=mr.flux_id -- and mr.date_fin>=pm.date_debut
 -- On ne modifie que la premiere occurence
  WHERE mr.date_debut>ect.date_fin
  ;
  delete mr
  from modele_remplacement mr
  inner join emp_contrat_type ect on mr.contrattype_id=ect.id
 -- INNER JOIN pai_int_png_emp_in_depot_flux edf on mr.employe_id=edf.employe_id
  INNER JOIN pai_mois pm on pm.flux_id=mr.flux_id -- and mr.date_fin>=pm.date_debut
 -- On ne modifie que la premiere occurence
  WHERE mr.date_debut>ect.date_fin
  ;
  call int_logrowcount(_idtrt,1,'int_png2mroad_maj_remplacement',concat_ws(' ','Suppression de',row_count(),'remplacements hors-contrat.'));
  
-- Attention si suppression de emp_cycle !!!
  insert into modele_remplacement(depot_id,flux_id,employe_id,contrattype_id,date_debut,date_fin,utilisateur_id,date_creation)
  SELECT DISTINCT
      epd.depot_id,epd.flux_id,eco.employe_id,
      ect.id,
      greatest(ect.date_debut,prr.date_debut),
      least(ect.date_fin,prr.date_fin),
      0,
      now()
  FROM emp_contrat_type ect
  INNER JOIN emp_contrat eco on ect.contrat_id=eco.id
--  INNER JOIN pai_int_png_emp_in_depot_flux edf on eco.employe_id=edf.employe_id
  INNER JOIN emp_pop_depot epd ON eco.employe_id=epd.employe_id and ect.date_debut between epd.date_debut and epd.date_fin
  INNER JOIN ref_population rp ON epd.population_id=rp.id
  INNER JOIN pai_mois pm on pm.flux_id=epd.flux_id and  ect.date_fin>=pm.date_debut
  -- Pour faire une rupture à chaque chgt de taux horaire
  inner join ref_typetournee rtt on epd.flux_id=rtt.id
  inner join pai_ref_remuneration prr on rtt.societe_id=prr.societe_id AND rtt.population_id=prr.population_id AND ect.date_debut<=prr.date_fin and ect.date_fin>=prr.date_debut
  WHERE ect.typecontrat_id=1 -- CDD
  AND rp.emploi_id=1 -- porteur
  AND epd.flux_id=1
  AND not exists(select null from modele_remplacement mr where mr.contrattype_id=ect.id and mr.date_debut=greatest(ect.date_debut,prr.date_debut));
  call int_logrowcount(_idtrt,1,'int_png2mroad_maj_remplacement',concat_ws(' ','Ajout de',row_count(),'remplacements.'));
           
  update modele_remplacement mr
  inner join emp_contrat_type ect on mr.contrattype_id=ect.id
 -- INNER JOIN pai_int_png_emp_in_depot_flux edf on mr.employe_id=edf.employe_id
  INNER JOIN emp_pop_depot epd ON mr.employe_id=epd.employe_id and ect.date_debut between epd.date_debut and epd.date_fin
  INNER JOIN pai_mois pm on pm.flux_id=mr.flux_id and mr.date_fin>=pm.date_debut
  set mr.depot_id=epd.depot_id
  , mr.flux_id=epd.flux_id
  , mr.utilisateur_id=0
  , mr.date_modif=now()
  WHERE mr.depot_id<>epd.depot_id
  OR mr.flux_id<>epd.flux_id
  ;
  call int_logrowcount(_idtrt,1,'int_png2mroad_maj_remplacement',concat_ws(' ','Modification date de dépôt/flux de',row_count(),'remplacements.'));

  update modele_remplacement mr
  inner join emp_contrat_type ect on mr.contrattype_id=ect.id
 -- INNER JOIN pai_int_png_emp_in_depot_flux edf on mr.employe_id=edf.employe_id
  INNER JOIN pai_mois pm on pm.flux_id=mr.flux_id -- and mr.date_fin>=pm.date_debut
 -- On ne modifie que la premiere occurence
  inner join (select mr2.contrattype_id,min(mr2.date_debut) as date_debut from modele_remplacement mr2 group by mr2.contrattype_id) as min_mr on mr.contrattype_id=min_mr.contrattype_id and mr.date_debut=min_mr.date_debut
--  inner join (select mr2.contrattype_id,min(mr2.date_fin) as date_fin from modele_remplacement mr2 group by mr2.contrattype_id) as min_mr on mr.contrattype_id=min_mr.contrattype_id and mr.date_fin=min_mr.date_fin
  set mr.date_debut=ect.date_debut
  , mr.utilisateur_id=0
  , mr.date_modif=now()
  WHERE mr.date_debut<>ect.date_debut
  ;
  call int_logrowcount(_idtrt,1,'int_png2mroad_maj_remplacement',concat_ws(' ','Modification date de debut de',row_count(),'remplacements.'));

  update modele_remplacement mr
  inner join emp_contrat_type ect on mr.contrattype_id=ect.id
 -- INNER JOIN pai_int_png_emp_in_depot_flux edf on mr.employe_id=edf.employe_id
  INNER JOIN pai_mois pm on pm.flux_id=mr.flux_id -- and mr.date_fin>=pm.date_debut
-- On ne modifie que la derniere occurence
  inner join (select mr2.contrattype_id,max(mr2.date_debut) as date_debut from modele_remplacement mr2 group by mr2.contrattype_id) as max_mr on mr.contrattype_id=max_mr.contrattype_id and mr.date_debut=max_mr.date_debut
  set mr.date_fin=ect.date_fin
  , mr.utilisateur_id=0
  , mr.date_modif=now()
  WHERE mr.date_fin<>ect.date_fin
  ;
  call int_logrowcount(_idtrt,1,'int_png2mroad_maj_remplacement',concat_ws(' ','Modification date de fin de',row_count(),'remplacements.'));
 
  call mod_remplacement_insert_jour(0,_depot_id,_flux_id,null);
  call mod_valide_remplacement(_validation_id,_depot_id,_flux_id,null);
END;
/*
call int_png2mroad_maj_remplacement(@idtrt,null,null,null);
select @idtrt;
select * from pai_int_log where idtrt=1 order by id desc;
select * from modele_journal where remplacement_id is not null

Duplicate entry '19701-2016-12-05' for key 'un_modele_remplacement'

  update modele_remplacement mr
  inner join emp_contrat_type ect on mr.contrattype_id=ect.id
 -- INNER JOIN pai_int_png_emp_in_depot_flux edf on mr.employe_id=edf.employe_id
  INNER JOIN pai_mois pm on pm.flux_id=mr.flux_id -- and mr.date_fin>=pm.date_debut
 -- On ne modifie que la premiere occurence
  inner join (select mr2.contrattype_id,min(mr2.date_fin) as date_fin from modele_remplacement mr2 group by mr2.contrattype_id) as min_mr on mr.contrattype_id=min_mr.contrattype_id and mr.date_fin=min_mr.date_fin
  set mr.date_debut=ect.date_debut
  , mr.utilisateur_id=0
  , mr.date_modif=now()
  WHERE mr.date_debut<>ect.date_debut
  ;
  
  select * from modele_remplacement where contrattype_id=19701
  select * from modele_remplacement where contrattype_id=20482
  select * from modele_remplacement group by contrattype_id,date_fin having count(*)>1
  select * from modele_remplacement where contrattype_id=21233
  
  	SELECT DISTINCT mr1.depot_id,mr1.flux_id,26,mrj1.jour_id,mr1.employe_id ,mrj1.modele_tournee_id,NULL,NULL,mr1.id,concat_ws(' ',e.nom,e.prenom1,e.prenom2),mr1.*
	FROM modele_remplacement mr1
  INNER JOIN modele_remplacement_jour mrj1 ON mrj1.remplacement_id=mr1.id
  INNER JOIN modele_remplacement_jour mrj2 ON mrj1.modele_tournee_id=mrj2.modele_tournee_id and mrj1.jour_id=mrj2.jour_id 
  INNER JOIN modele_remplacement mr2 ON mrj2.remplacement_id=mr2.id
  INNEr JOIN employe e ON mr2.employe_id=e.id
  INNER JOIN pai_mois pm ON pm.flux_id=mr1.flux_id AND mr1.date_fin>=pm.date_debut
	WHERE mr2.id<>mr1.id 
  AND mr1.date_debut<=mr2.date_fin and mr1.date_fin>=mr2.date_debut
	;
  
  select * from modele_remplacement where date_debut<'2017-01-01' and date_fin>='2017-01-01'
update modele_remplacement 
set date_fin='2016-12-31' where date_debut<'2017-01-01' and date_fin>='2017-01-01'
  
  SELECT DISTINCT
      epd.depot_id,epd.flux_id,eco.employe_id,
      ect.id,
      greatest(ect.date_debut,prr.date_debut),
      least(ect.date_fin,prr.date_fin),
      0,
      now()
      
      select *
  FROM emp_contrat_type ect
  INNER JOIN emp_contrat eco on ect.contrat_id=eco.id
--  INNER JOIN pai_int_png_emp_in_depot_flux edf on eco.employe_id=edf.employe_id
  INNER JOIN emp_pop_depot epd ON eco.employe_id=epd.employe_id and ect.date_debut between epd.date_debut and epd.date_fin
  INNER JOIN ref_population rp ON epd.population_id=rp.id
  INNER JOIN pai_mois pm on pm.flux_id=epd.flux_id and  ect.date_fin>=pm.date_debut
  -- Pour faire une rupture à chaque chgt de taux horaire
  inner join ref_typetournee rtt on epd.flux_id=rtt.id
  inner join pai_ref_remuneration prr on rtt.societe_id=prr.societe_id AND rtt.population_id=prr.population_id AND ect.date_debut<=prr.date_fin and ect.date_fin>=prr.date_debut
  WHERE ect.typecontrat_id=1 -- CDD
  and ect.id=21233
  AND rp.emploi_id=1 -- porteur
  AND epd.flux_id=1
  AND not exists(select null from modele_remplacement mr where mr.contrattype_id=ect.id and mr.date_debut=greatest(ect.date_debut,prr.date_debut))


delete from modele_journal where remplacement_id in (
select id from modele_remplacement where flux_id=2)
delete from modele_remplacement where flux_id=2

  */
