/*
SET @idtrt=null;
CALL alim_poids(0,@idtrt,'2015-03-17');
select * from pai_int_log where idtrt in(select max(id) from pai_int_traitement) order by id desc;
select * from pai_int_traitement order by id desc;
select @idtrt;
*/
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS alim_poids;
CREATE PROCEDURE alim_poids(
    IN 		_utilisateur_id INT,
    INOUT _idtrt		      INT,
    IN 		_date_distrib   DATE
) BEGIN
    DECLARE CONTINUE  HANDLER FOR SQLWARNING    CALL int_logwarning(_idtrt);
    DECLARE EXIT      HANDLER FOR SQLEXCEPTION  CALL int_logerreur(_idtrt);
        
    CALL int_logdebut(_utilisateur_id,_idtrt,'ALIM_POIDS',_date_distrib,null,null);
    CALL alim_poi_exec(_utilisateur_id, _idtrt, _date_distrib);
    CALL int_logfin2(_idtrt,'ALIM_POIDS');
END;
-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS alim_poi_exec;
CREATE PROCEDURE alim_poi_exec(
    IN 		_utilisateur_id INT,
    INOUT _idtrt		      INT,
    IN 		_date_distrib   DATE
) BEGIN
    CALL alim_poi_nettoyage(_idtrt, _date_distrib);
    CALL alim_poi_insert(_utilisateur_id, _idtrt, _date_distrib);
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS alim_poi_nettoyage;
CREATE PROCEDURE alim_poi_nettoyage(
    IN    _idtrt		      INT,
    IN 		_date_distrib   DATE
) BEGIN
    DELETE FROM prd_caract_jour 
    WHERE date_distrib=_date_distrib
    AND prd_caract_id IN (SELECT id FROM prd_caract pc WHERE pc.code='POIDS')
    ;
    call int_logrowcount_C(_idtrt,5,'alim_poi_nettoyage', 'Nettoyage des poids');
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS alim_poi_insert;
CREATE PROCEDURE alim_poi_insert(
    IN    _utilisateur_id	INT,
    IN    _idtrt		      INT,
    IN 		_date_distrib   DATE
) BEGIN
    INSERT INTO prd_caract_jour(prd_caract_id,produit_id,utilisateur_id,valeur_int,date_distrib)
    SELECT distinct pc.id,p.id,_utilisateur_id,null,prd.date_distrib
    FROM produit_recap_depot prd
    INNER JOIN produit p ON prd.produit_id=p.id
    INNER JOIN prd_caract pc ON p.type_id=pc.produit_type_id
    WHERE pc.code='POIDS'
    AND prd.date_distrib=_date_distrib
    ;
    call int_logrowcount_C(_idtrt,5,'alim_poi_insert', 'Insertion des poids');
END;
