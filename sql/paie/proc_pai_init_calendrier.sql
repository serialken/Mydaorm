/*
DROP TABLE IF EXISTS `pai_ref_calendrier`;
CREATE TABLE IF NOT EXISTS `pai_ref_calendrier` (
  `datecal` DATE NOT NULL,
  `jour` VARCHAR(10) COLLATE utf8_unicode_ci NOT NULL,
  `mois` VARCHAR(10) COLLATE utf8_unicode_ci NOT NULL,
  `annee` VARCHAR(4) COLLATE utf8_unicode_ci NOT NULL,
  `jour_id` INT NOT NULL,
  `typejour_id` INT NOT NULL,
  `jour_oct` INT NOT NULL,
  `numsem` INT NOT NULL,
  PRIMARY KEY (`datecal`))
 ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
*/

DROP PROCEDURE IF EXISTS init_ref_calendrier;
CREATE PROCEDURE `init_ref_calendrier`()
 BEGIN
 DECLARE datecal DATE DEFAULT DATE('2014-08-21');
   WHILE datecal<=DATE('2099-12-31') DO
      INSERT INTO pai_ref_calendrier(datecal,jour,mois,annee,jour_id,typejour_id,jour_oct,numsem) VALUES (
      datecal
      ,CASE DATE_FORMAT(datecal,'%w')
      WHEN 0 THEN 'Dimanche'
      WHEN 1 THEN 'Lundi'
      WHEN 2 THEN 'Mardi'
      WHEN 3 THEN 'Mercredi'
      WHEN 4 THEN 'Jeudi'
      WHEN 5 THEN 'Vendredi'
      WHEN 6 THEN 'Samedi'
      END
      ,CASE DATE_FORMAT(datecal,'%m')
      WHEN '01' THEN 'Janvier'
      WHEN '02' THEN 'Février'
      WHEN '03' THEN 'Mars'
      WHEN '04' THEN 'Avril'
      WHEN '05' THEN 'Mai'
      WHEN '06' THEN 'Juin'
      WHEN '07' THEN 'Juilet'
      WHEN '08' THEN 'Août'
      WHEN '09' THEN 'Septembre'
      WHEN '10' THEN 'Octobre'
      WHEN '11' THEN 'Novembre'
      WHEN '12' THEN 'Décembre'
      END
      ,DATE_FORMAT(datecal,'%Y')
      ,DAYOFWEEK(datecal) -- DATE_FORMAT(datecal,'%w')+1
      ,pai_type_jour(datecal)
      ,CASE WHEN DATE_FORMAT(datecal,'%w')=0 THEN 7 ELSE DATE_FORMAT(datecal,'%w') END
      ,DATE_FORMAT(datecal,'%V')
      );
      SET datecal=datecal+INTERVAL 1 DAY;
    END WHILE;
END;
/*
CALL init_ref_calendrier();
SELECT * FROM pai_ref_calendrier;

DROP TABLE IF EXISTS `pai_ref_semaine`;
CREATE TABLE IF NOT EXISTS `pai_ref_semaine` (
  `anneesem` VARCHAR(6) COLLATE utf8_unicode_ci NOT NULL,
  `annee` VARCHAR(4) COLLATE utf8_unicode_ci NOT NULL,
  `numsem` INT NOT NULL,
--  `mois` VARCHAR(2) COLLATE utf8_unicode_ci NOT NULL,
--  `anneemois` VARCHAR(6) NOT NULL,
  `date_debut` DATE NOT NULL,
  `date_fin` DATE NOT NULL,
  PRIMARY KEY (`anneesem`))
 ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
*/

DROP PROCEDURE IF EXISTS init_ref_semaine;
CREATE PROCEDURE `init_ref_semaine`()
 BEGIN
 DECLARE datecal DATE DEFAULT DATE('2014-07-28');
   WHILE datecal<=DATE('2099-12-31') DO
      INSERT INTO pai_ref_semaine(anneesem,annee,numsem,/*mois,anneemois,*/date_debut,date_fin) VALUES (
      concat(DATE_FORMAT(datecal,'%Y'),DATE_FORMAT(datecal,'%V'))
      ,DATE_FORMAT(datecal,'%Y')
      ,DATE_FORMAT(datecal,'%V')
--      ,DATE_FORMAT(datecal,'%m')
--      ,concat(DATE_FORMAT(datecal,'%Y'),DATE_FORMAT(datecal,'%m'))
      ,datecal
      ,datecal+INTERVAL 6 DAY
      );
      SET datecal=datecal+INTERVAL 7 DAY;
    END WHILE;
END;
/*
CALL init_ref_semaine();
SELECT * FROM pai_ref_semaine where annee='2016';
*/
 