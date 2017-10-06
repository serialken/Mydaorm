/**
    IMPORTANT
    Les fichiers SQL qui sont exécutés par Symfony/Doctrine ne doivent pas comporter de changement de délimiteur.
    Le délimiteur naturel (;) est toujours utilisé.
    Attention à ne pas inclure d'instruction DELIMITER dans le script SQL.
*/
/*
SET @idtrt=null;
call int_mroad2cycle(0,@idtrt,false);

select * from pai_int_oct_cjexp order by matricule,date_distrib;
select * from pai_int_oct_cjexp order by date_distrib,matricule;

select * from employe where  matricule='NEP0093100';
select * from pai_int_oct_pointage where matricule='NEP0093100';
select * from pai_oct_cjexp;
select * from pai_int_oct_cjexp where matricule='NEP0093100';
select * from pai_int_oct_horaire where matricule='NEP0093100';
select * from pai_int_log where idtrt in(select max(id) from pai_int_traitement) order by id desc;

select *
from emp_pop_depot epd
inner join employe e on epd.employe_id=e.id
and matricule in(
select distinct matricule from pai_int_oct_horaire
where matricule not in (select matricule from pai_int_oct_pointage))
select matricule from pai_int_oct_pointage
union
select matricule from pai_int_oct_horaire
order by 1

select distinct employe_id from emp_pop_depot where nbheures_garanties<>0 and nbheures_garanties is not null and flux_id=2
;
*/

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_oct_cycle;

-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS INT_MROAD2CYCLE;
CREATE PROCEDURE INT_MROAD2CYCLE(
    IN 		_utilisateur_id INT,
    INOUT _idtrt		      INT,
    IN 		_isStc          BOOLEAN
) BEGIN
    DECLARE CONTINUE  HANDLER FOR SQLWARNING    CALL int_logwarning(_idtrt);
    DECLARE EXIT      HANDLER FOR SQLEXCEPTION  CALL int_logerreur(_idtrt);
    
    CALL int_logdebut(_utilisateur_id,_idtrt,'MROAD2CYCLE',null,null,null);
    CALL int_mroad2cycle_exec(_idtrt, _isStc);
    CALL int_logfin(_idtrt);
END;
-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2cycle_exec;
CREATE PROCEDURE int_mroad2cycle_exec(
    IN    _idtrt		    INT,
    IN 		_isStc          BOOLEAN
  ) BEGIN
DECLARE _date_debut DATE;
DECLARE _date_fin DATE;
-- Attention, dans la vue materialisée pai_oct_Pointage, il y a le bornage des dates
-- En cas de problème, modifier dernierepaie dans gen_pepp
--    update PEPP.gen_pepp set dernierepaie='20131020',prochainepaie='20131120';
--  SELECT date_debut INTO _date_debut FROM pai_mois;
--  SET _date_fin:=CURDATE();
--  SET _date_debut:='2014-10-21';
  
  call int_mroad2cycle_nettoyage(_idtrt);
  call int_mroad2cycle_select(_idtrt,_isStc);
  -- call int_mroad2cycle_filtre(_idtrt);
  call int_mroad2cycle_update(_idtrt);
  call int_mroad2cycle_erreur(_idtrt);
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2cycle_filtre;
CREATE PROCEDURE int_mroad2cycle_filtre(
    IN    _idtrt		    INT
) BEGIN
-- envoi des temps seulement pour les mensualisés
/*  delete i
  from pai_int_oct_horaire i 
  inner join employe e on i.matricule=e.matricule
  left outer JOIN emp_pop_depot epd on e.id=epd.employe_id and i.date_distrib between epd.date_debut and epd.date_fin
  where epd.nbheures_garanties is null or epd.nbheures_garanties=0 or epd.flux_id=1 or epd.id is null;
  */
  -- envoi des temps seulement pour un individu
  delete i
  from pai_int_oct_horaire i 
  where i.matricule not in ('7000360120')
  ;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2cycle_nettoyage;
CREATE PROCEDURE int_mroad2cycle_nettoyage(
    IN    _idtrt		    INT
) BEGIN
  CALL int_logger(_idtrt,'int_mroad2cycle_nettoyage','Vide les tables de travail');
  truncate table pai_int_oct_horaire;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2cycle_select;
CREATE PROCEDURE int_mroad2cycle_select(
    IN    _idtrt		    INT,
    IN 		_isStc        BOOLEAN
) BEGIN
  -- table CJOURN
  -- PORT=16
  -- RHP=51
  -- JNTP=56
  -- PORM=88
  INSERT INTO pai_int_oct_horaire(matricule,date_distrib,hor_cod_oct,hor_dat,eta) 
  SELECT h.pers_mat,prc.datecal,c.hor_cod,h.hor_dat,
  case when n.niv_cod3 between 'PA101' and 'PA120' then '040'
  when not SUBSTR(n.niv_cod3,3,3) REGEXP '^[0-9]+$' then CONCAT('0',SUBSTR(n.niv_cod3,4,2))
  else SUBSTR(n.niv_cod3,3,3)
  end
  FROM pai_ref_calendrier prc
  INNER JOIN pai_oct_contprev p ON prc.datecal BETWEEN p.cont_datd AND p.cont_datf
  INNER JOIN pai_oct_horprev h ON  p.pers_mat=h.pers_mat AND prc.datecal BETWEEN h.hor_dat AND h.hor_datf
  -- Dans Octime lundi=1, mardi=2, ... dimanche=7
  INNER JOIN pai_oct_cyclig c ON h.cyc_cod=c.cyc_cod AND prc.jour_oct=c.cyc_num -- AND c.hor_cod not in (16,51)
  -- on ne prend que les dates contenues DANS le poste ,'POR2','POL2','POLT','POS2','POST','POT2'
  INNER JOIN pai_oct_varprev v ON p.pers_mat=v.pers_mat AND prc.datecal BETWEEN v.var_dat AND v.var_datf
  -- ,pai_oct_nivprev n
  INNER JOIN pai_oct_nivprev n ON n.pers_mat=p.pers_mat AND prc.datecal BETWEEN n.niv_dat AND n.niv_datf
  inner join pai_mois pm on pm.flux_id=case left(right(h.pers_mat,2),1) when 2 then 2 else 1 end
  WHERE prc.datecal BETWEEN str_to_date(pm.date_debut_string,'%Y%m%d') AND CURDATE()
  -- p.pers_mat NOT IN ('Z003025700','Z003054900','Z003015300','Z004105200')
  --  and v.par_cod in ('PORT','POLY','POR2','POL2','POLT','POS2','POST','POT2') -- dans vue materialisée
  -- récupre l'établissement
--  and n.pers_mat=p.pers_mat and n.niv_dat<=prc.cal_dat and prc.cal_dat<=n.niv_datf
  AND (NOT _isStc OR p.pers_mat IN (SELECT e.matricule FROM pai_stc  s INNER JOIN employe e ON s.employe_id=e.id WHERE s.date_extrait IS NULL))
  ;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2cycle_update;
CREATE PROCEDURE int_mroad2cycle_update(
    IN    _idtrt		    INT
) BEGIN
  UPDATE pai_int_oct_horaire i
  INNER JOIN pai_oct_cjexp h ON h.pers_mat=i.matricule AND h.dat=i.date_distrib
  SET i.hor_cod_exp=h.hor_cod;
  
  UPDATE pai_int_oct_horaire i
  INNER JOIN pai_int_oct_badge t ON t.matricule=i.matricule AND t.date_distrib=i.date_distrib
  SET hor_cod_pepp=case substr(i.matricule,9,1) when '2' then 88 else 16 end
  WHERE t.heure_debut_mroad IS NOT NULL AND Contrat_ok=1
  ;
/*  update pai_int_oct_horaire i
  set hor_cod_pepp =98
  where hor_cod_pepp=88 and i.matricule='7000125820';
*/
  UPDATE pai_int_oct_horaire i
  SET i.hor_cod_pepp=51
  WHERE NOT EXISTS(SELECT NULL FROM pai_int_oct_badge t WHERE t.matricule=i.matricule AND t.date_distrib=i.date_distrib AND t.heure_debut_mroad IS NOT NULL AND t.Contrat_ok=1)
  ;
  UPDATE pai_int_oct_horaire i
  INNER JOIN pai_oct_saiabs s ON i.matricule=s.pers_mat AND i.date_distrib BETWEEN s.abs_dat AND s.abs_fin
  SET i.abs_cod=s.abs_cod
  WHERE s.abs_cod NOT IN ('JFER','AT99')
  ;
  UPDATE pai_int_oct_horaire i
  SET hor_cod_imp=CASE hor_cod_pepp WHEN 51 THEN 56 ELSE hor_cod_pepp END
  -- pas d'absence, Pepp et Octime différent et (pas d'horaire exceptionnel ou mauvais horaire exceptionnel)
  WHERE (abs_cod IS NULL)
  AND hor_cod_oct<>hor_cod_pepp 
  AND (hor_cod_exp IS NULL OR hor_cod_exp<>CASE hor_cod_pepp WHEN 51 THEN 56 ELSE hor_cod_pepp END)
  ;
  -- Permet de supprimer les horaires exceptionnels dans Octime
  UPDATE pai_int_oct_horaire i
  SET hor_cod_imp=0
  -- pas d'absence, Pepp et Octime egaux et un d'horaire exceptionnel
  WHERE (abs_cod IS NULL AND hor_cod_oct=hor_cod_pepp AND hor_cod_exp IS NOT NULL)
  -- une absence et un horaire exceptionnel
  OR  (abs_cod IS NOT NULL AND hor_cod_exp IS NOT NULL)
  ;
  
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2cycle_erreur;
CREATE PROCEDURE int_mroad2cycle_erreur(
    IN    _idtrt		    INT
) BEGIN
  CALL int_logger(_idtrt,'int_mroad2cycle_erreur','Log des erreurs');
  INSERT INTO pai_int_erreur(idtrt,date_distrib,matricule,eta,msg)
  SELECT DISTINCT _idtrt,NULL,r.matricule,NULL,
    CONCAT_WS(' ',
    p.pers_nom,
    p.pers_pre,
    '(',r.matricule,')',
    'a un code horaire (',
    r.hor_cod_oct,
    ') incorrect.'
    )
  FROM pai_int_oct_horaire r
  INNER JOIN pai_oct_pers p ON r.matricule=p.pers_mat
  WHERE r.hor_cod_oct NOT IN (16,51,88)
  ;
END;