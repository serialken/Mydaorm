/*
UPDATE pai_tournee
set duree=pai_duree(date_distrib,employe_id,valrem,nbcli,nbtitre)
;
*/

-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP FUNCTION IF EXISTS pai_type_jour;
DROP FUNCTION IF EXISTS pai_typejour_employe;
CREATE FUNCTION pai_typejour_employe(_date DATE, _employe_id INT)
  RETURNS INT
  LANGUAGE SQL
  READS SQL DATA
BEGIN
DECLARE _ferie DATE;
/*
"1"	"Semaine"	"S"
"2"	"Dimanche"	"D"
"3"	"Ferié"		"F"
*/
    SELECT prf.jfdate INTO _ferie 
    FROM emp_pop_depot epd
    INNER JOIN pai_ref_ferie prf ON epd.societe_id=prf.societe_id AND prf.jfdate=_date
    WHERE epd.employe_id=_employe_id AND _date between epd.date_debut and epd.date_fin
    ;
    IF _ferie IS NOT NULL THEN
            RETURN 3;
    ELSEIF DAYOFWEEK(_date)=1 THEN
            RETURN 2;
    ELSE
            RETURN 1;
    END IF;
END;

-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP FUNCTION IF EXISTS pai_typejour_societe;
CREATE FUNCTION pai_typejour_societe(_date DATE,_societe_id INT)
  RETURNS INT
  LANGUAGE SQL
  READS SQL DATA
BEGIN
DECLARE _ferie DATE;
/*
"1"	"Semaine"	"S"
"2"	"Dimanche"	"D"
"3"	"Ferié"		"F"
*/
    SELECT prf.jfdate INTO _ferie FROM pai_ref_ferie prf WHERE _societe_id=prf.societe_id AND prf.jfdate=_date
    ;
    IF _ferie IS NOT NULL THEN
            RETURN 3;
    ELSEIF DAYOFWEEK(_date)=1 THEN
            RETURN 2;
    ELSE
            RETURN 1;
    END IF;
END;

-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP FUNCTION IF EXISTS pai_valrem_calculee;
-- utilisé par Ams\PaieBundle\Repository\PaiTourneeRepository.php
CREATE FUNCTION pai_valrem_calculee(_date_distrib DATE,_flux_id INT,_duree TIME,_nbcli DECIMAL(4,0))
  RETURNS DECIMAL(7,5)
  LANGUAGE SQL
  READS SQL DATA
BEGIN
  -- arrondir la valrem à 4 décimale pour Neo/Media
	RETURN COALESCE((SELECT 
	CASE
	  WHEN (_nbcli = 0 or _nbcli is null) THEN 0
	  WHEN _flux_id in (1,2) THEN round((TIME_TO_SEC(_duree)/3600*r.valeur)/_nbcli, 5)
    ELSE 0
	END
	FROM ref_typetournee tt
	INNER JOIN pai_ref_remuneration r ON tt.societe_id=r.societe_id AND  tt.population_id=r.population_id AND _date_distrib BETWEEN r.date_debut AND r.date_fin
	WHERE tt.id=_flux_id)
	,0)
	;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP FUNCTION IF EXISTS pai_valrem_org;
-- utilisé par Ams\PaieBundle\Repository\PaiTourneeRepository.php
CREATE FUNCTION pai_valrem_org(_flux_id INT,_employe_id INT,_mr_actif TINYINT(1),_mr_employe_id INT,_mrj_valrem_moyen DECIMAL(7,5),_mrj_valrem DECIMAL(7,5),_mtj_employe_id INT,_mtj_remplacant_id INT,_mtj_valrem_moyen DECIMAL(7,5),_mtj_valrem DECIMAL(7,5))
  RETURNS VARCHAR(2)
  LANGUAGE SQL
  READS SQL DATA
BEGIN
  -- arrondir la valrem à 4 décimale pour Neo/Media
	RETURN case
		  when _mr_actif and _employe_id=_mr_employe_id then 'RE'
		  when _employe_id=_mtj_remplacant_id then 'MR'
		  when _employe_id=_mtj_employe_id then 'MT'
		  else 'PO'
		  end
	;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP FUNCTION IF EXISTS pai_valrem;
-- utilisé par Ams\PaieBundle\Repository\PaiTourneeRepository.php
CREATE FUNCTION pai_valrem(_flux_id INT,_employe_id INT,_mr_actif TINYINT(1),_mr_employe_id INT,_mrj_valrem_moyen DECIMAL(7,5),_mrj_valrem DECIMAL(7,5),_mtj_employe_id INT,_mtj_remplacant_id INT,_mtj_valrem_moyen DECIMAL(7,5),_mtj_valrem DECIMAL(7,5))
  RETURNS DECIMAL(7,5)
  LANGUAGE SQL
  READS SQL DATA
BEGIN
  -- arrondir la valrem à 4 décimale pour Neo/Media
	RETURN case
		  when _flux_id=1 and _mr_actif and _employe_id=_mr_employe_id then _mrj_valrem_moyen
		  when _employe_id=_mtj_remplacant_id then _mtj_valrem
		  when _employe_id=_mtj_employe_id then if(_flux_id=1,_mtj_valrem_moyen,_mtj_valrem)
		  else _mtj_valrem
		  end
	;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
-- AETTENTION : ne semble plus utilisé
DROP FUNCTION IF EXISTS pai_duree;
/*
CREATE FUNCTION pai_duree(_date_distrib DATE,_employe_id INT,_valrem DECIMAL(7,5),_nbcli DECIMAL(4,0),_nbrep DECIMAL(4,0))
  RETURNS TIME
  LANGUAGE SQL
  READS SQL DATA
BEGIN
-- N'est plus utilisée
	RETURN COALESCE((SELECT 
	CASE
	  WHEN _nbcli = 0 THEN '00:00'
	  WHEN e.typetournee_id in (1) THEN SEC_TO_TIME(_valrem*(1+rp.majoration/100)*(_nbcli+2*_nbrep)/r.valeur*3600)
	  WHEN e.typetournee_id in (2) THEN SEC_TO_TIME(_valrem*(1+rp.majoration/100)*(_nbcli+_nbrep)/r.valeur*3600)
	  WHEN e.typetournee_id = 3 THEN '00:00'
    ELSE SEC_TO_TIME(_valrem*_nbcli/r.valeur*3600)
	END
	FROM pai_ref_remuneration r
  LEFT OUTER JOIN emp_pop_depot e ON e.employe_id=_employe_id AND _date_distrib BETWEEN e.date_debut AND e.date_fin
  LEFT OUTER JOIN ref_population rp ON e.population_id=rp.id
	WHERE coalesce(e.societe_id,1)=r.societe_id AND coalesce(e.population_id,1)=r.population_id AND _date_distrib BETWEEN r.date_debut AND r.date_fin
	),'00:00')
	;
END;
*/
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP FUNCTION IF EXISTS pai_duree_tournee;
CREATE FUNCTION pai_duree_tournee(_tournee_org_id INT, _split_id INT, _typetournee_id INT,_valrem DECIMAL(7,5),_majoration DECIMAL(5,2),_remuneration DECIMAL(8,5),_nbcli DECIMAL(4,0))
  RETURNS TIME
  LANGUAGE SQL
  READS SQL DATA
BEGIN
	RETURN COALESCE(
	CASE
    WHEN _tournee_org_id is not null AND _split_id is null THEN '00:00'
	  WHEN _typetournee_id in (1,2) THEN SEC_TO_TIME(_valrem*(1+_majoration/100)*(_nbcli)/_remuneration*3600)
    ELSE '00:00'
  END
	,'00:00')
	;
END;

-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP FUNCTION IF EXISTS pai_duree_reperage;
CREATE FUNCTION pai_duree_reperage(_tournee_org_id INT, _split_id INT, _typetournee_id INT,_valrem DECIMAL(7,5),_majoration DECIMAL(5,2),_remuneration DECIMAL(8,5),_nbrep DECIMAL(4,0))
  RETURNS TIME
  LANGUAGE SQL
  READS SQL DATA
BEGIN
	RETURN COALESCE(
	CASE
    WHEN _tournee_org_id is not null AND _split_id is null THEN '00:00'
	  WHEN _typetournee_id in (1) THEN SEC_TO_TIME(_valrem*(1+_majoration/100)*(2*_nbrep)/_remuneration*3600)
	  WHEN _typetournee_id in (2) THEN SEC_TO_TIME(_valrem*(1+_majoration/100)*(_nbrep)/_remuneration*3600)
    ELSE '00:00'
  END
	,'00:00')
	;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
-- A SUPPRIMER après mise en prod etalonnage ????
DROP FUNCTION IF EXISTS modele_valrem;
CREATE FUNCTION modele_valrem(_date_distrib DATE,_flux_id INT,_duree TIME,_nbcli DECIMAL(4,0))
  RETURNS DECIMAL(7,5)
  LANGUAGE SQL
  READS SQL DATA
BEGIN
	RETURN COALESCE((SELECT 
	CASE
  	WHEN (_nbcli = 0 or _nbcli is null) THEN 0
  	WHEN _flux_id in (1,2) THEN round((TIME_TO_SEC(_duree)/3600*r.valeur)/_nbcli, 5)
  	ELSE 0
	END
	FROM ref_typetournee tt
	INNER JOIN pai_ref_remuneration r ON tt.societe_id=r.societe_id AND  tt.population_id=r.population_id AND _date_distrib BETWEEN r.date_debut AND r.date_fin
	WHERE tt.id=_flux_id)
	,0)
	;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP FUNCTION IF EXISTS cal_modele_etalon;
CREATE FUNCTION cal_modele_etalon(_duree TIME,_nbcli DECIMAL(4,0)) RETURNS decimal(7,6)
    READS SQL DATA
BEGIN
	RETURN
	CASE
	WHEN (_nbcli = 0 or _nbcli is null) THEN 0
	ELSE round(TIME_TO_SEC(_duree)/3600/_nbcli, 6)
	END
	;
END;

-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
-- A SUPPRIMER après mise en prod etalonnage ????
DROP FUNCTION IF EXISTS modele_duree;
/*
CREATE FUNCTION modele_duree(_date_distrib DATE,_typetournee_id INT,_valrem DECIMAL(7,5),_nbcli DECIMAL(4,0))
  RETURNS TIME
  LANGUAGE SQL
  READS SQL DATA
BEGIN
	RETURN COALESCE((SELECT 
	CASE
	WHEN _typetournee_id in (1,2) AND (_nbcli = 0 or _nbcli is null) THEN '00:00'
	WHEN _typetournee_id in (1,2) THEN SEC_TO_TIME(_valrem*_nbcli/r.valeur*3600)
	ELSE '00:00'
	END
	FROM ref_typetournee tt
	INNER JOIN pai_ref_remuneration r ON tt.societe_id=r.societe_id AND tt.population_id=r.population_id AND _date_distrib BETWEEN r.date_debut AND r.date_fin
	WHERE tt.id=_typetournee_id)
	,'00:00')
	;
END;
*/
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
-- SELECT pai_heure_nuit(SEC_TO_TIME(03*60*60),SEC_TO_TIME(9*60*60));
DROP FUNCTION IF EXISTS pai_heure_nuit;
CREATE FUNCTION pai_heure_nuit(heure_debut TIME, duree TIME)
  RETURNS TIME
  LANGUAGE SQL
  READS SQL DATA
BEGIN
DECLARE TIME1 INT;
DECLARE TIME2 INT;
	SET TIME1=0;
	SET TIME2=0;
	IF TIME_TO_SEC(heure_debut)+TIME_TO_SEC(duree)>24*60*60 THEN
		SET TIME1=pai_heure_nuit24(TIME_TO_SEC(heure_debut),24*60*60-TIME_TO_SEC(heure_debut));
		SET TIME2=pai_heure_nuit24(0,TIME_TO_SEC(heure_debut)+TIME_TO_SEC(duree)-24*60*60);
		RETURN SEC_TO_TIME(TIME1+TIME2);
	ELSE
		SET TIME1=pai_heure_nuit24(TIME_TO_SEC(heure_debut),TIME_TO_SEC(duree));
		RETURN SEC_TO_TIME(TIME1);
	END IF;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP FUNCTION IF EXISTS pai_heure_nuit24;
CREATE FUNCTION pai_heure_nuit24(heure_debut INT, duree INT)
  RETURNS INT
  LANGUAGE SQL
  READS SQL DATA
BEGIN
DECLARE TIME1 INT;
DECLARE TIME2 INT;
	SET TIME1=0;
	SET TIME2=0;
	IF heure_debut<=6*60*60 THEN
		IF heure_debut+duree<=6*60*60 THEN
			SET TIME1=duree;
		ELSE
			SET TIME1=6*60*60-heure_debut;
		END IF;
	END IF;
	IF heure_debut+duree>=21*60*60 THEN
		IF heure_debut>=21*60*60 THEN
			SET TIME2=duree;
		ELSE
			SET TIME2=heure_debut+duree-21*60*60;
		END IF;
	END IF;
	RETURN TIME1+TIME2;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP FUNCTION IF EXISTS pai_horaire_moyen;
CREATE FUNCTION pai_horaire_moyen(employe_id INT, date_debut DATE, date_fin DATE, nbheures_garanties FLOAT, date_debut_mois DATE, date_fin_mois DATE)
  RETURNS TIME
  LANGUAGE SQL
  READS SQL DATA
BEGIN  
  RETURN sec_to_time(pai_horaire_moyen_float(employe_id, date_debut, date_fin, nbheures_garanties, date_debut_mois, date_fin_mois)*3600);
END;

DROP FUNCTION IF EXISTS pai_horaire_moyen_float;
CREATE FUNCTION pai_horaire_moyen_float(employe_id INT, date_debut DATE, date_fin DATE, nbheures_garanties FLOAT, date_debut_mois DATE, date_fin_mois DATE)
  RETURNS FLOAT
  LANGUAGE SQL
  READS SQL DATA
BEGIN  
  RETURN nbheures_garanties -- *nbjours_contrat/nbjours_mois/nbjours_cycle
        -- nbjours_contrat
        *( select sum(CASE DAYOFWEEK(prc.datecal)
                              WHEN 1 THEN ec.dimanche
                              WHEN 2 THEN ec.lundi
                              WHEN 3 THEN ec.mardi
                              WHEN 4 THEN ec.mercredi
                              WHEN 5 THEN ec.jeudi
                              WHEN 6 THEN ec.vendredi
                              WHEN 7 THEN ec.samedi
                              END
                              )
          from pai_ref_calendrier prc
          inner join emp_cycle ec on date_debut between ec.date_debut and ec.date_fin
          where employe_id=ec.employe_id 
          and prc.datecal between date_debut and date_fin
          and prc.datecal between date_debut_mois and date_fin_mois
          )
        -- nbjours_mois
        /( select sum(CASE DAYOFWEEK(prc.datecal)
                              WHEN 1 THEN ec.dimanche
                              WHEN 2 THEN ec.lundi
                              WHEN 3 THEN ec.mardi
                              WHEN 4 THEN ec.mercredi
                              WHEN 5 THEN ec.jeudi
                              WHEN 6 THEN ec.vendredi
                              WHEN 7 THEN ec.samedi
                              END
                              )
          from pai_ref_calendrier prc
          inner join emp_cycle ec on date_debut between ec.date_debut and ec.date_fin
          where employe_id=ec.employe_id 
          and prc.datecal between date_debut_mois and date_fin_mois
          )
        -- nbjours_cycle
          /( select sum(CASE DAYOFWEEK(prc.datecal)
                              WHEN 1 THEN ec.dimanche
                              WHEN 2 THEN ec.lundi
                              WHEN 3 THEN ec.mardi
                              WHEN 4 THEN ec.mercredi
                              WHEN 5 THEN ec.jeudi
                              WHEN 6 THEN ec.vendredi
                              WHEN 7 THEN ec.samedi
                              END
                              )
          from pai_ref_calendrier prc
          inner join emp_cycle ec on prc.datecal between ec.date_debut and ec.date_fin
          where employe_id=ec.employe_id 
          and prc.datecal between date_debut and date_fin
          and prc.datecal between date_debut_mois and date_fin_mois
          );
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP FUNCTION IF EXISTS pai_horaire_moyen_HP;
CREATE FUNCTION pai_horaire_moyen_HP(xa_oid varchar(36), nbheures_garanties FLOAT, date_debut_mois DATE, date_fin_mois DATE)
  RETURNS TIME
  LANGUAGE SQL
  READS SQL DATA
BEGIN  
  RETURN sec_to_time(pai_horaire_moyen_HP_float(xa_oid, nbheures_garanties, date_debut_mois, date_fin_mois)*3600);
END;

DROP FUNCTION IF EXISTS pai_horaire_moyen_HP_float;
CREATE FUNCTION pai_horaire_moyen_HP_float(xa_oid varchar(36), nbheures_garanties FLOAT, date_debut_mois DATE, date_fin_mois DATE)
  RETURNS FLOAT
  LANGUAGE SQL
  READS SQL DATA
BEGIN  
    RETURN nbheures_garanties/pai_nbjours_cycle_HP(xa_oid,date_debut_mois,date_fin_mois);
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP FUNCTION IF EXISTS pai_nbjours_cycle;
CREATE FUNCTION pai_nbjours_cycle(employe_id INT, date_debut DATE, date_debut_mois DATE, date_fin_mois DATE)
  RETURNS INT
  LANGUAGE SQL
  READS SQL DATA
BEGIN  
  RETURN ( select sum(CASE DAYOFWEEK(prc.datecal)
                              WHEN 1 THEN ec.dimanche
                              WHEN 2 THEN ec.lundi
                              WHEN 3 THEN ec.mardi
                              WHEN 4 THEN ec.mercredi
                              WHEN 5 THEN ec.jeudi
                              WHEN 6 THEN ec.vendredi
                              WHEN 7 THEN ec.samedi
                              END
                              )
          from pai_ref_calendrier prc
          inner join emp_cycle ec on date_debut between ec.date_debut and ec.date_fin
          where ec.employe_id=employe_id
          and prc.datecal between date_debut_mois and date_fin_mois
          );
END;                        
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP FUNCTION IF EXISTS pai_nbjours_cycle_HP;
CREATE FUNCTION pai_nbjours_cycle_HP(xa_oid varchar(36), date_debut_mois DATE, date_fin_mois DATE)
  RETURNS INT
  LANGUAGE SQL
  READS SQL DATA
BEGIN  
  RETURN ( select sum(CASE dayofweek(prc.datecal)
                            WHEN 1 THEN ech.dimanche
                            WHEN 2 THEN ech.lundi
                            WHEN 3 THEN ech.mardi
                            WHEN 4 THEN ech.mercredi
                            WHEN 5 THEN ech.jeudi
                            WHEN 6 THEN ech.vendredi
                            WHEN 7 THEN ech.samedi
                            END
                            )
          from pai_ref_calendrier prc, emp_contrat_hp ech
          where ech.xaoid=xa_oid
          and prc.datecal between date_debut_mois and date_fin_mois
          );
END; 
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP FUNCTION IF EXISTS pai_hp_est_mensualise;
CREATE FUNCTION pai_hp_est_mensualise(xa_oid varchar(36), date_debut DATE, date_fin DATE)
  RETURNS BOOLEAN
  LANGUAGE SQL
  READS SQL DATA
BEGIN  
  RETURN datediff(date_fin,date_debut)>30;
END; 
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP FUNCTION IF EXISTS cycle_to_string;
CREATE FUNCTION cycle_to_string(lundi tinyint(1),mardi tinyint(1),mercredi tinyint(1),jeudi tinyint(1),vendredi tinyint(1),samedi tinyint(1),dimanche tinyint(1))
  RETURNS VARCHAR(7)
  LANGUAGE SQL
  READS SQL DATA
BEGIN  
  RETURN concat(if(lundi,'L','-'),if(mardi,'M','-'),if(mercredi,'M','-'),if(jeudi,'J','-'),if(vendredi,'V','-'),if(samedi,'S','-'),if(dimanche,'D','-'));
END; 
/*
select pai_horaire_moyen_float(492,'2015-11-21','2015-12-20',104,'2015-11-21','2015-12-20')
select pai_hp_est_mensualise(1,'2015-11-01','2015-12-20')
select datediff('2015-12-20','2015-11-10')
select * from employe where nom='ID HAMOU'
select pai_duree('2014-06-20',3,0.13841,157,157) from dual;


*/
