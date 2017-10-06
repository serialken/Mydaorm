/*

SET @idtrt=null;
call INT_MROAD2HEURESGARANTIES(0,@idtrt,false);
select * from pai_int_erreur where idtrt=@idtrt;
select * from pai_oct_contprev where pers_mat='7000199420'

select * from pai_int_traitement order by id desc;
select * from pai_int_log where idtrt in(select max(id) from pai_int_traitement) order by id desc;
select * from pai_int_log where idtrt=1 order by id desc;

SHOW WARNINGS;
*/
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS INT_MROAD2HEURESGARANTIES;
CREATE PROCEDURE INT_MROAD2HEURESGARANTIES(
    IN 		_utilisateur_id INT,
    IN  _idtrt		      INT
) BEGIN
    DECLARE _validation_id int;
    DECLARE CONTINUE  HANDLER FOR SQLWARNING    CALL int_logwarning(_idtrt);
    DECLARE EXIT      HANDLER FOR SQLEXCEPTION  CALL int_logerreur(_idtrt);

    CALL int_logdebut(_utilisateur_id,_idtrt,'MROAD2HEURESGARANTIES',null,null,null);
    CALL INT_mroad2heuresgaranties_exec(_idtrt);
    CALL int_logfin2(_idtrt,'MROAD2HEURESGARANTIES');
END;

-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS INT_mroad2heuresgaranties_exec;
-- Rafraichir les tables pai_poct avant de d'appeler cette procédure
CREATE PROCEDURE INT_mroad2heuresgaranties_exec(
    IN    _idtrt		      INT
) BEGIN
  call INT_mroad2heuresgaranties_nettoyage(_idtrt);
  call INT_mroad2heuresgaranties_init(_idtrt);
END;

-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS INT_mroad2heuresgaranties_nettoyage;
CREATE PROCEDURE INT_mroad2heuresgaranties_nettoyage(
    IN    _idtrt		      INT
) BEGIN
  CALL int_logger(_idtrt,'','Vide les tables de travail');
  delete from pai_int_oct_hgaranties where idtrt=_idtrt;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
/*
DROP PROCEDURE IF EXISTS INT_mroad2heuresgaranties_init;
CREATE PROCEDURE INT_mroad2heuresgaranties_init(
    IN    _idtrt		      INT
) BEGIN
DECLARE _anneemois  VARCHAR(6);
DECLARE _depot_id  VARCHAR(6);
DECLARE _flux_id  VARCHAR(6);
  SELECT anneemois,depot_id,flux_id INTO _anneemois,_depot_id,_flux_id FROM pai_int_traitement WHERE id=_idtrt;

    call create_tmp_pai_suivi_horaire(_depot_id,_flux_id,_anneemois);
    
    insert into pai_int_oct_hgaranties(idtrt,employe_id,date_distrib,hgaranties,hdelegation,hhorspresse)
    select 
    _idtrt,
    t.employe_id,
    t.date_fin,
    SEC_TO_TIME(sum(if(_flux_id=1 or t.est_hors_presse,0,greatest(-t.nbheures_apayer,0)+coalesce(t.nbheures_garanties_majorees,0)))*3600),
    SEC_TO_TIME(sum(t.nbheures_delegation)*3600),
    SEC_TO_TIME(sum(if(t.est_hors_presse,greatest(nbheures_a_realiser,nbheures_realisees),nbheures_hors_presse))*3600)
    from tmp_pai_suivi_horaire t
    ,(select distinct employe_id from pai_ev_emp_pop e) e 
    where t.employe_id=e.employe_id
-- On génère même si tout est à 0 car ça permet de remttre éventuellement les compteurs à 0    
--    and (not t.est_hors_presse and (t.nbheures_apayer<0 or nbheures_hors_presse>0) or t.nbheures_delegation>0 or t.est_hors_presse and greatest(nbheures_a_realiser,nbheures_realisees)>0)
    group by t.employe_id,t.date_fin
    ;
    
    -- Pour les stc
    select min(anneemois) INTO _anneemois from pai_ref_mois prm where anneemois>_anneemois;
    call create_tmp_pai_suivi_horaire(_depot_id,_flux_id,_anneemois);
    delete t from tmp_pai_suivi_horaire t
    where not exists(select null from pai_ev_emp_pop e where t.employe_id=e.employe_id and t.date_fin between e.d and e.f);
    
    insert into pai_int_oct_hgaranties(idtrt,employe_id,date_distrib,hgaranties,hdelegation,hhorspresse)
    select 
    _idtrt,
    t.employe_id,
    t.date_fin,
    SEC_TO_TIME(sum(if(_flux_id=1 or t.est_hors_presse,0,greatest(-t.nbheures_apayer,0)+coalesce(t.nbheures_garanties_majorees,0)))*3600),
    SEC_TO_TIME(sum(t.nbheures_delegation)*3600),
    SEC_TO_TIME(sum(if(t.est_hors_presse,greatest(nbheures_a_realiser,nbheures_realisees),nbheures_hors_presse))*3600)
    from tmp_pai_suivi_horaire t
    ,(select distinct employe_id from pai_ev_emp_pop e) e 
    where t.employe_id=e.employe_id
-- On génère même si tout est à 0 car ça permet de remttre éventuellement les compteurs à 0    
--    and (not t.est_hors_presse and (t.nbheures_apayer<0 or nbheures_hors_presse>0) or t.nbheures_delegation>0 or t.est_hors_presse and greatest(nbheures_a_realiser,nbheures_realisees)>0)
    group by t.employe_id,t.date_fin
    ;
END;  
*/
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS INT_mroad2heuresgaranties_init;
CREATE PROCEDURE INT_mroad2heuresgaranties_init(
    IN    _idtrt		      INT
) BEGIN
DECLARE _anneemois  VARCHAR(6);
DECLARE _depot_id  VARCHAR(6);
DECLARE _flux_id  VARCHAR(6);
  SELECT anneemois,depot_id,flux_id INTO _anneemois,_depot_id,_flux_id FROM pai_int_traitement WHERE id=_idtrt;

    insert into pai_int_oct_hgaranties(idtrt,employe_id,date_distrib,hgaranties,hdelegation,hhorspresse)
    select 
    _idtrt,
    phg.employe_id,
    phg.date_fin,
    SEC_TO_TIME(sum(phg.nbheures_garanties_apayer)*3600),
    SEC_TO_TIME(sum(phg.nbheures_delegation)*3600),
    SEC_TO_TIME(sum(phg.nbheures_hors_presse)*3600)
    from pai_hg phg
    ,(select distinct employe_id from pai_ev_emp_pop e) e 
    where phg.employe_id=e.employe_id
    and phg.anneemois=_anneemois  and phg.flux_id=_flux_id
    group by phg.employe_id,phg.date_fin
    ;
    
    -- Pour les stc
    select min(anneemois) INTO _anneemois from pai_ref_mois prm where anneemois>_anneemois;
    
    insert into pai_int_oct_hgaranties(idtrt,employe_id,date_distrib,hgaranties,hdelegation,hhorspresse)
    select 
    _idtrt,
    phg.employe_id,
    phg.date_fin,
    SEC_TO_TIME(sum(phg.nbheures_garanties_apayer)*3600),
    SEC_TO_TIME(sum(phg.nbheures_delegation)*3600),
    SEC_TO_TIME(sum(phg.nbheures_hors_presse)*3600)
    from pai_hg phg
    ,(select distinct employe_id from pai_ev_emp_pop e) e 
    where phg.employe_id=e.employe_id
    and phg.anneemois=_anneemois  and phg.flux_id=_flux_id
    and  exists(select null from pai_ev_emp_pop e where phg.employe_id=e.employe_id and phg.date_fin between e.d and e.f)
    group by phg.employe_id,phg.date_fin
    ;
END;  
