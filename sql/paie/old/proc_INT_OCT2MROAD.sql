/**
    IMPORTANT
    Les fichiers SQL qui sont exécutés par Symfony/Doctrine ne doivent pas comporter de changement de délimiteur.
    Le délimiteur naturel (;) est toujours utilisé.
    Attention à ne pas inclure d'instruction DELIMITER dans le script SQL.
*/
/*
set @idtrt=null;
CALL INT_OCT2MROAD(0,@idtrt,null,null);
select * from pai_int_log where idtrt in(select max(id) from pai_int_traitement) order by id desc;
select * from emp_cycle;
*/
-- INUTILISE : Les cycles sont mis � jour � partir de Pleiades
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS INT_OCT2MROAD;
/*
CREATE PROCEDURE INT_OCT2MROAD(
  IN 		_utilisateur_id INT,
  INOUT _idtrt		      INT,
  IN    _depot_id		    INT,
  IN    _flux_id		    INT
) BEGIN
DECLARE CONTINUE  HANDLER FOR SQLWARNING    CALL int_logwarning(_idtrt);
DECLARE EXIT      HANDLER FOR SQLEXCEPTION  CALL int_logerreur(_idtrt);
        
  CALL int_logdebut(_utilisateur_id,_idtrt,'OCT2MROAD',null,_depot_id,_flux_id);
  CALL INT_OCT2MROAD_exec(_idtrt);

  call int_logger(_idtrt,'INT_OCT2MROAD','Validation des mod�les');
  CALL mod_valide_rh(_idtrt,_depot_id,_flux_id);
  call int_logger(_idtrt,'INT_OCT2MROAD','Validation de la paie');
  call pai_valide_rh(_idtrt,_depot_id,_flux_id);

  CALL int_logfin(_idtrt);
END;*/
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_oct2mroad_exec;
/*
CREATE PROCEDURE int_oct2mroad_exec(
    IN    _idtrt		      INT
)
BEGIN
  DECLARE EXIT      HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
    RESIGNAL;
  END;
  START TRANSACTION;
  
  -- nettoyage des tables
  DELETE FROM emp_cycle;

  INSERT INTO emp_cycle(employe_id,date_debut,date_fin,cyc_cod,lundi,mardi,mercredi,jeudi,vendredi,samedi,dimanche)
  SELECT e.id,h.hor_dat,h.hor_datf,h.cyc_cod,FALSE,FALSE,FALSE,FALSE,FALSE,FALSE,FALSE
  FROM employe e,pai_oct_horprev h
  WHERE e.matricule=h.pers_mat;
  call int_logrowcount(_idtrt,5,'int_oct2mroad_exec','int_png_tmp_nettoyage');
  
  UPDATE emp_cycle e
  INNER JOIN pai_oct_cyclig c ON e.cyc_cod=c.cyc_cod AND c.cyc_num=1
  SET lundi=CASE WHEN hor_cod=16 THEN TRUE ELSE FALSE END;

  UPDATE emp_cycle e
  INNER JOIN pai_oct_cyclig c ON e.cyc_cod=c.cyc_cod AND c.cyc_num=2
  SET mardi=CASE WHEN hor_cod=16 THEN TRUE ELSE FALSE END;

  UPDATE emp_cycle e
  INNER JOIN pai_oct_cyclig c ON e.cyc_cod=c.cyc_cod AND c.cyc_num=3
  SET mercredi=CASE WHEN hor_cod=16 THEN TRUE ELSE FALSE END;

  UPDATE emp_cycle e
  INNER JOIN pai_oct_cyclig c ON e.cyc_cod=c.cyc_cod AND c.cyc_num=4
  SET jeudi=CASE WHEN hor_cod=16 THEN TRUE ELSE FALSE END;

  UPDATE emp_cycle e
  INNER JOIN pai_oct_cyclig c ON e.cyc_cod=c.cyc_cod AND c.cyc_num=5
  SET vendredi=CASE WHEN hor_cod=16 THEN TRUE ELSE FALSE END;

  UPDATE emp_cycle e
  INNER JOIN pai_oct_cyclig c ON e.cyc_cod=c.cyc_cod AND c.cyc_num=6
  SET samedi=CASE WHEN hor_cod=16 THEN TRUE ELSE FALSE END;

  UPDATE emp_cycle e
  INNER JOIN pai_oct_cyclig c ON e.cyc_cod=c.cyc_cod AND c.cyc_num=7
  SET dimanche=CASE WHEN hor_cod=16 THEN TRUE ELSE FALSE END;
  
  COMMIT;
 END;
 */
