/*
select * from pai_int_traitement order by id desc limit 100;
select * from pai_int_log where idtrt in(select max(id) from pai_int_traitement) order by id desc;
select * from pai_int_log order by id desc limit 12800;
select * from pai_int_log where date_log between'2014-10-22' and '2014-10-23' order by id desc limit 12800;
select * from pai_int_log where idtrt=128 order by id desc;
select * from pai_int_log where idtrt=1 order by id desc;
select * from pai_int_log where idtrt=1 and module='recalcul_tournee_id' order by id desc;
*/

/* select * from depot */
/*
DROP TABLE IF EXISTS pai_int_erreur;
DROP TABLE IF EXISTS pai_int_log;

DROP TABLE IF EXISTS pai_int_traitement;
CREATE TABLE IF NOT EXISTS pai_int_traitement (
	id 		          INT(11) NOT NULL AUTO_INCREMENT, 
	date_debut 	    DATETIME NOT NULL, 
	date_fin 	      DATETIME, 
	utilisateur_id 	INT(11) NOT NULL, 
	typetrt 	      VARCHAR(128) COLLATE utf8_unicode_ci, 
	CONSTRAINT pai_int_traitement_pk PRIMARY KEY (id))
	CONSTRAINT pai_int_traitement_utilisateur_fk FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id))
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
alter table pai_int_traitement add anneemois varchar(6);
create index pai_int_traitement_idx1 on pai_int_traitement(typetrt,statut,anneemois,flux_id);

CREATE TABLE IF NOT EXISTS pai_int_log(
	id 		    INT AUTO_INCREMENT,
	idtrt 		INT NOT NULL, 
	date_log 	DATETIME NOT NULL, 
	module 		VARCHAR(64) COLLATE utf8_unicode_ci, 
  level     INT NOT NULL,
	msg 		  VARCHAR(1024) COLLATE utf8_unicode_ci, 
	CONSTRAINT pai_int_log_pk PRIMARY KEY (id), 
	CONSTRAINT pai_int_log_int_traitelent_fk FOREIGN KEY (idtrt) REFERENCES pai_int_traitement (id))
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
  CREATE INDEX pai_int_log_idx ON pai_int_log (idtrt) 
  ;
 
 
CREATE TABLE IF NOT EXISTS pai_int_erreur(
	id 		        INT NOT NULL AUTO_INCREMENT, 
	idtrt 		    INT NOT NULL, 
	date_distrib 	DATE, 
	matricule 	  VARCHAR(10) COLLATE utf8_unicode_ci, 
	eta 		      VARCHAR(3) COLLATE utf8_unicode_ci, 
	msg 		      VARCHAR(1024) COLLATE utf8_unicode_ci, 
	CONSTRAINT pai_int_erreur_pk PRIMARY KEY (id), 
	CONSTRAINT pai_int_erreur_int_traitement_fk FOREIGN KEY (idtrt) REFERENCES pai_int_traitement (id))
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
  CREATE INDEX pai_int_erreur_idx ON pai_int_erreur (idtrt) 
  ;
 */

-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
-- A SUPPRIMER
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_debut;
CREATE PROCEDURE int_debut(
    INOUT	_idtrt		      INT,
    IN		_utilisateur_id	INT,
    IN		_typetrt    	  VARCHAR(32)
) BEGIN
	IF (_idtrt IS NULL) THEN
		INSERT INTO pai_int_traitement(date_debut,utilisateur_id,typetrt,statut) VALUES(SYSDATE(),_utilisateur_id,_typetrt,'C');
    SELECT LAST_INSERT_ID() INTO _idtrt;
	END IF
	;
  CALL int_loglevel(_idtrt,4,_typetrt,'Début des traitements');
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_fin;
CREATE PROCEDURE int_fin(
    IN		_idtrt			INT
) BEGIN
  CALL int_loglevel(_idtrt,4,'','Fin des traitements');
  UPDATE pai_int_traitement set date_fin=SYSDATE() WHERE id=_idtrt;
--  commit;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_logdebut;
CREATE PROCEDURE int_logdebut(
    IN		_utilisateur_id	INT,
    INOUT	_idtrt		      INT,
    IN		_typetrt    	  VARCHAR(32),
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT
) BEGIN
	IF (_idtrt IS NULL) THEN
    -- un peu de nettoyage
    delete from pai_int_log where idtrt=1 and date_log<date_add(sysdate(), interval -3 day);

    INSERT INTO pai_int_traitement(date_debut,utilisateur_id,typetrt,date_distrib,depot_id,flux_id,statut) 
    VALUES(SYSDATE(),_utilisateur_id,_typetrt,_date_distrib,_depot_id,_flux_id,'C');
    SELECT LAST_INSERT_ID() INTO _idtrt;
	END IF
	;
  CALL int_loglevel(_idtrt,4,_typetrt,'Début des traitements');
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_logdebutanneemois;
CREATE PROCEDURE int_logdebutanneemois(
    IN		_utilisateur_id	INT,
    INOUT	_idtrt		      INT,
    IN		_typetrt    	  VARCHAR(32),
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT,
    IN 		_anneemois      VARCHAR(6)
) BEGIN
	IF (_idtrt IS NULL) THEN
    -- un peu de nettoyage
    delete from pai_int_log where idtrt=1 and date_log<date_add(sysdate(), interval -3 day);

		INSERT INTO pai_int_traitement(date_debut,utilisateur_id,typetrt,date_distrib,depot_id,flux_id,statut,anneemois) 
    VALUES(SYSDATE(),_utilisateur_id,_typetrt,_date_distrib,_depot_id,_flux_id,'C',_anneemois);
    SELECT LAST_INSERT_ID() INTO _idtrt;
	END IF
	;
  CALL int_loglevel(_idtrt,4,_typetrt,'Début des traitements');
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_logfin;
CREATE PROCEDURE int_logfin(
    IN		_idtrt			INT
) BEGIN
  CALL int_loglevel(_idtrt,4,'','Fin des traitements');
  UPDATE pai_int_traitement set date_fin=SYSDATE(),statut='T' WHERE id=_idtrt and statut='C';
--  commit;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_logfin2;
CREATE PROCEDURE int_logfin2(
    IN		_idtrt			INT,
    IN		_typetrt    	  VARCHAR(32)
) BEGIN
  CALL int_loglevel(_idtrt,4,_typetrt,'Fin des traitements');
  UPDATE pai_int_traitement set date_fin=SYSDATE(),statut='T' WHERE id=_idtrt and typetrt=_typetrt and statut='C';
--  commit;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_logger;
CREATE PROCEDURE int_logger(
    IN		_idtrt			INT,
    IN		_module			VARCHAR(64),
    IN		_msg			  VARCHAR(1024)
) BEGIN
  INSERT INTO pai_int_log(idtrt,date_log,level,module,msg) VALUES(_idtrt,SYSDATE(),5,_module,_msg);
--  commit;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_loglevel;
CREATE PROCEDURE int_loglevel(
    IN		_idtrt			INT,
    IN		_level			INT,
    IN		_module			VARCHAR(64),
    IN		_msg			  VARCHAR(1024)
) BEGIN
  if (_level<=5) then
    INSERT INTO pai_int_log(idtrt,date_log,level,module,msg) VALUES(_idtrt,SYSDATE(),_level,_module,_msg);
  end if;
--  commit;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_logrowcount;
CREATE PROCEDURE int_logrowcount(
    IN		_idtrt			INT,
    IN		_level			INT,
    IN		_module			VARCHAR(64),
    IN		_msg			  VARCHAR(1024)
) BEGIN
  if (_level<=5) then
    INSERT INTO pai_int_log(idtrt,date_log,level,module,msg,count) VALUES(_idtrt,SYSDATE(),_level,_module,_msg,row_count());
  end if;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
-- Avec commit, ne pas utiliser dans les trigger ou les procedures appelées par trigger
DROP PROCEDURE IF EXISTS int_logrowcount_C;
CREATE PROCEDURE int_logrowcount_C(
    IN		_idtrt			INT,
    IN		_level			INT,
    IN		_module			VARCHAR(64),
    IN		_msg			  VARCHAR(1024)
) BEGIN
  if (_level<=5) then
    INSERT INTO pai_int_log(idtrt,date_log,level,module,msg,count) VALUES(_idtrt,SYSDATE(),_level,_module,_msg,row_count());
  end if;
  commit;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_logerreur;
CREATE PROCEDURE int_logerreur(
    IN		_idtrt			INT
) BEGIN
  SHOW WARNINGS;
  INSERT INTO pai_int_log(idtrt,date_log,level,module,msg) VALUES(_idtrt,SYSDATE(),0,'Error','Erreur, c''est moche !!!');
  UPDATE pai_int_traitement set date_fin=SYSDATE(),statut='E' WHERE id=_idtrt;
  RESIGNAL;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_logwarning;
CREATE PROCEDURE int_logwarning(
    IN		_idtrt			INT
) BEGIN
  SHOW WARNINGS;
  INSERT INTO pai_int_log(idtrt,date_log,level,module,msg) VALUES(_idtrt,SYSDATE(),2,'Warning','Attention !!!');
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_logger;
CREATE PROCEDURE recalcul_logger(
    IN		_module			VARCHAR(64),
    IN		_msg			  VARCHAR(1024)
) BEGIN
 --    call int_logrowcount(1,5,_module, _msg);
END;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS pai_valide_logger;
CREATE PROCEDURE `pai_valide_logger`(
    IN		_module			VARCHAR(64),
    IN		_msg			  VARCHAR(1024)
) BEGIN
 --     call int_logrowcount(1,5,_module, _msg);
END;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS emp_valide_logger;
CREATE PROCEDURE `emp_valide_logger`(
    IN		_module			VARCHAR(64),
    IN		_msg			  VARCHAR(1024)
) BEGIN
  --    call int_logrowcount(1,5,_module, _msg);
END;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS mod_valide_log;
CREATE PROCEDURE mod_valide_log(
    IN		_module			VARCHAR(64),
    IN		_msg			  VARCHAR(1024)
) BEGIN
--  call int_logrowcount(1,5,_module,_msg);
END;


/*
    select * from pai_int_log where idtrt=1 order by id desc limit 1000;
*/