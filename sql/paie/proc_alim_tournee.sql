/*
delete from pai_int_log where idtrt=0
select * from depot
SET @idtrt=null;
CALL alim_tournee(0,@idtrt,'2017-02-24',10,2);
SET @idtrt=null;
CALL ALIM_ACTIVITE(0,@idtrt,'2016-11-08',null,null,2);
SET @idtrt=null;
CALL supprim_tournee(0,@idtrt,'2016-11-08',null,null);
SET @idtrt=null;
call ALIM_TOURNEE_FROM_LAST_WEEK(0,@idtrt,'2016-11-01',null,2);

select * from pai_int_log where idtrt=1
*/
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS ALIM_TOURNEE;
CREATE PROCEDURE ALIM_TOURNEE(
    IN 		_utilisateur_id INT,
    INOUT _idtrt		      INT,
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT
) BEGIN
    DECLARE CONTINUE  HANDLER FOR SQLWARNING    CALL int_logwarning(_idtrt);
    DECLARE EXIT      HANDLER FOR SQLEXCEPTION  CALL int_logerreur(_idtrt);
        
    CALL int_logdebut(_utilisateur_id,_idtrt,'ALIM_TOURNEE',_date_distrib,_depot_id,_flux_id);
    CALL alim_trn_exec(_utilisateur_id, _idtrt, _date_distrib, _depot_id, _flux_id);
    CALL int_logfin2(_idtrt,'ALIM_TOURNEE');
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS SUPPRIM_TOURNEE;
CREATE PROCEDURE SUPPRIM_TOURNEE(
    IN 		_utilisateur_id INT,
    INOUT _idtrt		      INT,
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT
) BEGIN
    DECLARE CONTINUE  HANDLER FOR SQLWARNING    CALL int_logwarning(_idtrt);
    DECLARE EXIT      HANDLER FOR SQLEXCEPTION  CALL int_logerreur(_idtrt);
        
    CALL int_logdebut(_utilisateur_id,_idtrt,'SUPPRIM_TOURNEE',_date_distrib,_depot_id,_flux_id);
    CALL alim_trn_nettoyage(_utilisateur_id, _idtrt, _date_distrib, _depot_id, _flux_id);
    CALL int_logfin2(_idtrt,'SUPPRIM_TOURNEE');
END;
-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS alim_trn_exec;
CREATE PROCEDURE alim_trn_exec(
    IN 		_utilisateur_id INT,
    IN    _idtrt		      INT,
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT
) BEGIN
DECLARE _validation_id INT;
DECLARE _anneemois VARCHAR(6);
    INSERT INTO pai_validation(utilisateur_id) VALUES(_utilisateur_id);
  	SELECT LAST_INSERT_ID() INTO _validation_id;
    
    CALL alim_trn_maj_tauxhoraire(_utilisateur_id, _idtrt, _date_distrib);
    
    IF SUBSTR(_date_distrib,5,6)='-05-01' THEN
      CALL ALIM_TOURNEE_FROM_LAST_WEEK(_utilisateur_id, _idtrt, _date_distrib, _depot_id, _flux_id);
      
      update pai_heure ph
      inner join groupe_tournee gt on ph.groupe_id=gt.id
      set ph.duree_attente='00:00' 
      where ph.date_distrib=_date_distrib 
      and (gt.depot_id=_depot_id or _depot_id is null) 
      and (gt.flux_id=_flux_id or _flux_id is null)
      and ph.duree_attente<>0 
      ;

      update pai_tournee pt
      set pt.duree_retard='00:00' 
      where pt.date_distrib=_date_distrib 
      and (pt.depot_id=_depot_id or _depot_id is null) 
      and (pt.flux_id=_flux_id or _flux_id is null)
      and pt.duree_retard<>0 
      ;

      update pai_tournee pt
      set pt.nbkm_paye=0
      where pt.date_distrib=_date_distrib 
      and (pt.depot_id=_depot_id or _depot_id is null) 
      and (pt.flux_id=_flux_id or _flux_id is null)
      and pt.nbkm_paye<>0 
      ;
    ELSE
      CALL alim_trn_nettoyage(_utilisateur_id,_idtrt, _date_distrib, _depot_id, _flux_id);
      CALL alim_trn_valide_client(_idtrt, _validation_id, _date_distrib, _depot_id, _flux_id);
      CALL alim_trn_insert_heure(_utilisateur_id, _idtrt, _date_distrib, _depot_id, _flux_id);
      CALL alim_trn_insert_tournee(_utilisateur_id, _idtrt, _date_distrib, _depot_id, _flux_id);
      CALL alim_trn_maj_client(_idtrt, _date_distrib, _depot_id, _flux_id);
      CALL alim_trn_maj_crm(_idtrt, _date_distrib, _depot_id, _flux_id);
      CALL alim_trn_insert_produit(_utilisateur_id, _idtrt, _date_distrib, _depot_id, _flux_id);
      CALL alim_trn_insert_supplement(_utilisateur_id, _idtrt, _date_distrib, _depot_id, _flux_id);
    END IF;
    
    call int_logger(_idtrt, 'alim_trn_exec', 'Recalcul des produits');
    call recalcul_produit_date_distrib(_date_distrib, _depot_id, _flux_id);
    -- La validation est faite dans le recalcul, on valide juste le rh mensuel
    select anneemois into _anneemois from pai_ref_mois where _date_distrib between date_debut and date_fin;
    call pai_valide_rh_mensuel(_validation_id,_anneemois,null);
    call pai_valide_rh_6jour(_validation_id,_anneemois,null);
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS alim_trn_nettoyage;
CREATE PROCEDURE alim_trn_nettoyage(
    IN 		_utilisateur_id INT,
    IN    _idtrt		      INT,
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT
) BEGIN
    drop temporary table if exists _tmp_pai_tournee_delete;
    CREATE TEMPORARY TABLE _tmp_pai_tournee_delete engine=memory  as
    select pt.id 
    from pai_tournee pt 
    where pt.date_distrib=_date_distrib and (pt.depot_id=_depot_id or _depot_id is null) and (pt.flux_id=_flux_id or _flux_id is null)
    and pt.date_extrait is null
    ;
    CALL alim_trn_nettoyage_client(_idtrt, _date_distrib, _depot_id, _flux_id);
    CALL alim_trn_nettoyage_journal(_idtrt, _date_distrib, _depot_id, _flux_id);
 --   CALL alim_trn_nettoyage_incident(_idtrt, _date_distrib, _depot_id, _flux_id);
    CALL alim_trn_nettoyage_reclamation(_idtrt, _date_distrib, _depot_id, _flux_id);
    CALL alim_trn_nettoyage_produit(_idtrt, _date_distrib, _depot_id, _flux_id);
    CALL alim_trn_nettoyage_tournee(_idtrt, _date_distrib, _depot_id, _flux_id);
    CALL alim_trn_nettoyage_heure(_idtrt, _date_distrib, _depot_id, _flux_id);
    /*          R�alis� par trigger
      $this->_em->getConnection()->executeQuery("delete from pai_journal
      where date_distrib='".$date_distrib."'
      and activite_id in (select id  from pai_activite where date_distrib='".$date_distrib."' and activite_id IN (SELECT id FROM ref_activite WHERE CODE='AT')".$this->getSqlFiltre($depot_id,$flux_id)
      );
      $this->_em->getConnection()->executeQuery("delete from pai_activite where date_distrib='".$date_distrib."' and activite_id IN (SELECT id FROM ref_activite WHERE CODE='AT')".$this->getSqlFiltre($depot_id,$flux_id));
     */
 --   drop temporary table if exists _tmp_pai_tournee_delete;
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS alim_trn_nettoyage_client;
CREATE PROCEDURE alim_trn_nettoyage_client(
    IN    _idtrt		      INT,
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT
) BEGIN
    UPDATE client_a_servir_logist l 
    SET l.pai_tournee_id=NULL 
    WHERE  l.pai_tournee_id is not NULL 
    and l.date_distrib=_date_distrib and (l.depot_id=_depot_id or _depot_id is null) and (l.flux_id=_flux_id or _flux_id is null)
    and not exists(select null from pai_tournee pt 
                  where pt.date_distrib=_date_distrib and (pt.depot_id=_depot_id or _depot_id is null) and (pt.flux_id=_flux_id or _flux_id is null)
                  and pt.date_extrait is not null
                  )
    ;
    call int_logrowcount_C(_idtrt,5,'alim_trn_nettoyage_client', 'Nettoyage des clients à servir');
    -- Par securité si pb affectation
    UPDATE client_a_servir_logist l 
    INNER JOIN _tmp_pai_tournee_delete d on l.pai_tournee_id=d.id
    SET l.pai_tournee_id=NULL 
    WHERE  l.pai_tournee_id is not NULL 
    ;
    call int_logrowcount_C(_idtrt,5,'alim_trn_nettoyage_client', 'Nettoyage des clients à servir');
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS alim_trn_nettoyage_journal;
CREATE PROCEDURE alim_trn_nettoyage_journal(
    IN    _idtrt		      INT,
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT
) BEGIN
    delete pj from pai_journal pj
    where pj.tournee_id is not null
    and pj.date_distrib=_date_distrib and (pj.depot_id=_depot_id or _depot_id is null) and (pj.flux_id=_flux_id or _flux_id is null)
    and pj.date_extrait is null
    ;
    call int_logrowcount_C(_idtrt,5,'alim_trn_nettoyage_journal', 'Suppression du journal');
    -- Par securité si pb affectation
    delete pj 
    from pai_journal pj
    INNER JOIN _tmp_pai_tournee_delete d on pj.tournee_id=d.id
    ;
    call int_logrowcount_C(_idtrt,5,'alim_trn_nettoyage_journal', 'Suppression du journal');
    -- ATTENTION, il faut aussi purger le journal pour les produits li�s aux tournées
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 /*
 -- 16/12/2015 : les incidents ne sont plus liés à une tournée, plus besoin de les supprimer
DROP PROCEDURE IF EXISTS alim_trn_nettoyage_incident;
CREATE PROCEDURE alim_trn_nettoyage_incident(
    IN    _idtrt		      INT,
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT
) BEGIN
    delete pi 
    from pai_incident pi 
    INNER JOIN _tmp_pai_tournee_delete d on pi.tournee_id=d.id
    WHERE pi.date_extrait is null
    ;
    call int_logrowcount_C(_idtrt,5,'alim_trn_nettoyage_incident', 'Suppression des incidents');
END;
*/
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS alim_trn_nettoyage_reclamation;
CREATE PROCEDURE alim_trn_nettoyage_reclamation(
    IN    _idtrt		      INT,
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT
) BEGIN
    delete pr 
    from pai_reclamation pr
    INNER JOIN _tmp_pai_tournee_delete d on pr.tournee_id=d.id
    WHERE pr.date_extrait is null
    ;
    call int_logrowcount_C(_idtrt,5,'alim_trn_nettoyage_reclamation', 'Suppression des réclamations');
    SET @TRIGGER_CRM_RECALCUL_MAJORATION=FALSE;
    update crm_detail cd
    INNER JOIN _tmp_pai_tournee_delete d on cd.pai_tournee_id=d.id
    SET cd.pai_tournee_id=null
    ;
    SET @TRIGGER_CRM_RECALCUL_MAJORATION=TRUE;
    call int_logrowcount_C(_idtrt,5,'alim_trn_nettoyage_reclamation', 'Suppression Crm');
    
    update crm_detail_tmp cd
    INNER JOIN _tmp_pai_tournee_delete d on cd.pai_tournee_id=d.id
    SET cd.pai_tournee_id=null
    ;
    call int_logrowcount_C(_idtrt,5,'alim_trn_nettoyage_reclamation', 'Suppression Crm tmp');
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS alim_trn_nettoyage_produit;
CREATE PROCEDURE alim_trn_nettoyage_produit(
    IN    _idtrt		      INT,
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT
) BEGIN
    delete pj from pai_journal pj 
    where pj.produit_id is not null
    and pj.date_distrib=_date_distrib and (pj.depot_id=_depot_id or _depot_id is null) and (pj.flux_id=_flux_id or _flux_id is null)
    and pj.date_extrait is null
    ;
    call int_logrowcount_C(_idtrt,5,'alim_trn_nettoyage_produit', 'Suppression des produits');
    delete ppt 
    from pai_prd_tournee ppt 
    INNER JOIN _tmp_pai_tournee_delete d on ppt.tournee_id=d.id
    WHERE ppt.date_extrait is null
    ;
    call int_logrowcount_C(_idtrt,5,'alim_trn_nettoyage_produit', 'Suppression des produits');
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS alim_trn_nettoyage_tournee;
CREATE PROCEDURE alim_trn_nettoyage_tournee(
    IN    _idtrt		      INT,
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT
) BEGIN
    update pai_tournee pt
    inner join _tmp_pai_tournee_delete d on pt.id=d.id
    set pt.tournee_org_id=null 
    where pt.tournee_org_id is not null 
    and pt.date_extrait is null
    ;
    call int_logrowcount_C(_idtrt,5,'alim_trn_nettoyage_tournee', 'Suppression des tournées');
  /*  INSERT INTO pai_int_log(idtrt,date_log,level,module,msg) 
    SELECT _idtrt,SYSDATE(),3,'alim_trn_nettoyage_tournee',concat_ws(' ','Suppression de la tournée',pt.id,'code',pt.code,'du',pt.date_distrib)
    from pai_tournee pt
    inner join _tmp_pai_tournee_delete d on pt.id=d.id
    ;*/
    delete pt 
    from pai_tournee pt
    inner join _tmp_pai_tournee_delete d on pt.id=d.id
    ;
    call int_logrowcount_C(_idtrt,5,'alim_trn_nettoyage_tournee', 'Suppression des tournées');
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS alim_trn_nettoyage_heure;
CREATE PROCEDURE alim_trn_nettoyage_heure(
    IN    _idtrt		      INT,
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT
) BEGIN
    delete ph from pai_heure ph
    where ph.date_distrib=_date_distrib 
    and ph.groupe_id in(select gt.id from groupe_tournee gt where (gt.depot_id=_depot_id or _depot_id is null) and (gt.flux_id=_flux_id or _flux_id is null))
    and not exists(select null from pai_tournee pt where pt.heure_id=ph.id) -- il y a une tournee extraite !!!
    ;
    CALL int_logrowcount_C(_idtrt,5, 'alim_trn_nettoyage_heure', 'Suppression des heures');
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS alim_trn_valide_client;
CREATE PROCEDURE alim_trn_valide_client(
    IN    _idtrt		      INT,
    IN    _validation_id  INT,
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT
) BEGIN
  	DELETE pj
    FROM pai_journal pj
    INNER JOIN pai_ref_erreur pe on pj.erreur_id=pe.id
    INNER JOIN pai_mois m ON pj.date_distrib between m.date_debut and _date_distrib and pj.flux_id=m.flux_id
    WHERE /*pj.date_distrib=_date_distrib AND*/ (pj.depot_id=_depot_id or _depot_id is null) and (pj.flux_id=_flux_id or _flux_id is null)
    AND pe.rubrique='AL'
    ;
    call int_logrowcount_C(_idtrt,5,'alim_trn_valide_client', 'Nettoyage du journal');
    -- Non affecté dépôt/flux/modèle
    INSERT INTO pai_journal(validation_id,depot_id,flux_id,anneemois,date_distrib,erreur_id,employe_id,tournee_id,activite_id)
    SELECT _validation_id,l.depot_id,l.flux_id,m.anneemois,l.date_distrib,22,null,null,null
    -- concat(count(*),' clients a servir non affectés à un ',case when depot_id is null or flux_id is null then 'dépot/flux' else 'modèle' end)
    FROM  client_a_servir_logist l
    INNER JOIN pai_mois m ON l.date_distrib between m.date_debut and _date_distrib and l.flux_id=m.flux_id
    WHERE(l.depot_id=_depot_id or _depot_id is null) and (l.flux_id=_flux_id or _flux_id is null)
    AND (l.tournee_jour_id IS NULL OR l.depot_id is null OR l.flux_id is null)
    GROUP BY l.depot_id,l.flux_id,m.anneemois,l.date_distrib
    HAVING count(*)<>0
    ;
    call int_logrowcount_C(_idtrt,5,'alim_trn_valide_client', 'Validation des clients a servir (NAF)');
    -- mal affecté
    INSERT INTO pai_journal(validation_id,depot_id,flux_id,anneemois,date_distrib,erreur_id,employe_id,tournee_id,activite_id)
    SELECT _validation_id,l.depot_id,l.flux_id,m.anneemois,l.date_distrib,23,null,null,null
    -- concat(count(*),' clients a servir mal affectés')
    FROM  pai_mois m
    INNER JOIN client_a_servir_logist l ON l.date_distrib between m.date_debut and _date_distrib and l.flux_id=m.flux_id
    WHERE (l.depot_id=_depot_id or _depot_id is null) and (l.flux_id=_flux_id or _flux_id is null)
    AND l.tournee_jour_id IS NOT NULL AND l.depot_id is not null AND l.flux_id is not null
    AND NOT EXISTS(SELECT NULL 
              		FROM modele_tournee_jour mtj
              		INNER JOIN modele_tournee mt ON mtj.tournee_id=mt.id -- and mt.actif
              		INNER JOIN groupe_tournee gt ON mt.groupe_id=gt.id
              		WHERE l.tournee_jour_id=mtj.id AND _date_distrib between mtj.date_debut and mtj.date_fin
              		AND DAYOFWEEK(l.date_distrib)=mtj.jour_id
                  AND l.depot_id=gt.depot_id AND l.flux_id=gt.flux_id
              		)
    GROUP BY l.depot_id,l.flux_id,m.anneemois,l.date_distrib
    HAVING count(*)<>0
    ;
    call int_logrowcount_C(_idtrt,5,'alim_trn_valide_client', 'Validation des clients à servir (MAF)');
    /*
    SELECT l.id,l.abonne_soc_id,l.date_distrib,mtj.code,
    l.depot_id as "depot client",
    l.flux_id as "flux client",
    DAYOFWEEK(l.date_distrib) as "jour client",
    gt.depot_id as "depot modele",
    gt.flux_id as "flux modele",
    mtj.jour_id as "jour modele"
    -- concat(count(*),' clients a servir mal affectés')
    FROM  client_a_servir_logist l
    LEFT OUTER JOIN modele_tournee_jour mtj ON l.tournee_jour_id=mtj.id AND l.date_distrib between mtj.date_debut and mtj.date_fin
    LEFT OUTER JOIN modele_tournee mt ON mtj.tournee_id=mt.id -- and mt.actif
    LEFT OUTER JOIN groupe_tournee gt ON mt.groupe_id=gt.id
    WHERE l.tournee_jour_id IS NOT NULL AND l.depot_id is not null AND l.flux_id is not null
    AND (l.depot_id<>gt.depot_id OR l.flux_id<>gt.flux_id OR DAYOFWEEK(l.date_distrib)<>mtj.jour_id)
    ORDER BY l.date_distrib,l.depot_id,l.flux_id
    ;
    */
END;

-- ------------------------------------------------------------------------------------------------------------------------------------------------
-- Met à jour les modèles avec le nouveau taux horaire si necessaire
-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS alim_trn_maj_tauxhoraire;
create procedure alim_trn_maj_tauxhoraire(
    IN 		_utilisateur_id INT,
    IN    _idtrt		      INT,
    IN    _date_debut DATE
) 
this_proc: begin
declare rowcount INTEGER DEFAULT 0;
declare _validation_id INTEGER;
    insert into modele_tournee_jour(
      tournee_id, jour_id
    , date_debut, date_fin
    , employe_id, remplacant_id
    , transport_id, nbkm, nbkm_paye
    , depart_depot, retour_depot
    , tauxhoraire
    , valrem, valrem_moyen
    , etalon, etalon_moyen
    , duree, nbcli
    , date_creation, utilisateur_id
      )
    select
      mtj.tournee_id,mtj.jour_id
    , _date_debut,mtj.date_fin
    , mtj.employe_id,mtj.remplacant_id
    , mtj.transport_id,mtj.nbkm,mtj.nbkm_paye
    , mtj.depart_depot, mtj.retour_depot
    , prr_new.valeur
    , prr_new.valeur/prr_old.valeur*mtj.valrem,prr_new.valeur/prr_old.valeur*mtj.valrem_moyen
    , mtj.etalon, mtj.etalon_moyen
    , mtj.duree, mtj.nbcli 
    , now(), _utilisateur_id
    from modele_tournee_jour mtj
    inner join modele_tournee mt on mtj.tournee_id=mt.id
    inner join groupe_tournee gt on mt.groupe_id=gt.id
    -- ATTENTION , mt.flux_id=rtt.id Pas trés propre
    inner join ref_typetournee rtt on gt.flux_id=rtt.id
    inner join pai_ref_remuneration prr_new on rtt.societe_id=prr_new.societe_id AND rtt.population_id=prr_new.population_id AND _date_debut=prr_new.date_debut
    inner join pai_ref_remuneration prr_old on rtt.societe_id=prr_old.societe_id AND rtt.population_id=prr_old.population_id AND date_add(_date_debut,interval -1 day)=prr_old.date_fin
    -- 22/06/2016 On applique à tous les modèles du futur
--    where mtj.date_debut<_date_debut and mtj.date_fin>=_date_debut
    where _date_debut<=mtj.date_fin
    and mtj.tauxhoraire=prr_old.valeur
    ;
    set rowcount=row_count();
    call int_logrowcount_C(_idtrt,5,'alim_trn_maj_tauxhoraire', 'Création des modèles avec le nouveau taux horaire');
    if rowcount=0 then
      leave this_proc;
    end if;
    CALL mod_valide_tournee(@id,NULL,NULL,NULL);
    CALL mod_valide_tournee_jour(@id,NULL,NULL,NULL,NULL);

    update modele_tournee_jour mtj
    inner join modele_tournee mt on mtj.tournee_id=mt.id
    -- ATTENTION , mt.flux_id=rtt.id Pas trés propre
    inner join groupe_tournee gt on mt.groupe_id=gt.id
    inner join ref_typetournee rtt on gt.flux_id=rtt.id
    inner join pai_ref_remuneration prr_old on rtt.societe_id=prr_old.societe_id AND rtt.population_id=prr_old.population_id AND date_add(_date_debut,interval -1 day)=prr_old.date_fin
    set mtj.date_fin=date_add(_date_debut,interval -1 day)
    -- 22/06/2016 On applique à tous les modèles du futur
--    where mtj.date_debut<_date_debut and mtj.date_fin>=_date_debut
    where _date_debut<=mtj.date_fin
    and mtj.tauxhoraire=prr_old.valeur
    ;
    call int_logrowcount_C(_idtrt,5,'alim_trn_maj_tauxhoraire', 'Maj de la date de fin des anciens modèles');
/*    
    update ref_population rp
    inner join pai_ref_remuneration pol on rp.id=pol.population_id and pol.societe_id=1 and pol.date_debut=_date_debut
    inner join pai_ref_remuneration por on por.population_id=1 and por.societe_id=1 and por.date_debut=_date_debut
    set majoration=(pol.valeur/por.valeur-1)*100
    where rp.id in (3,4);
    call int_logrowcount_C(_idtrt,5,'alim_trn_maj_tauxhoraire', 'Maj de la majoration des polyvalents Proximy.');
    */
    update ref_population rp
    inner join pai_ref_remuneration pol on rp.id=pol.population_id and pol.societe_id=2 and pol.date_debut=_date_debut
    inner join pai_ref_remuneration por on por.population_id=5 and por.societe_id=2 and por.date_debut=_date_debut
    set majoration=(pol.valeur/por.valeur-1)*100
    where rp.id in (7,8,11,12);
    call int_logrowcount_C(_idtrt,5,'alim_trn_maj_tauxhoraire', 'Maj de la majoration des polyvalents Mediapresse.');

    update client_a_servir_logist csl
    inner join modele_tournee_jour mtj1 on csl.tournee_jour_id=mtj1.id
    inner join modele_tournee_jour mtj2 on mtj1.code=mtj2.code and csl.date_distrib between mtj2.date_debut and mtj2.date_fin
    set csl.tournee_jour_id=mtj2.id
    where csl.date_distrib>=_date_debut
    and (csl.tournee_jour_id<>mtj2.id)
    ;
    update feuille_portage fp
    inner join modele_tournee_jour mtj1 on fp.tournee_jour_id=mtj1.id
    inner join modele_tournee_jour mtj2 on mtj1.code=mtj2.code and fp.date_distrib between mtj2.date_debut and mtj2.date_fin
    set fp.tournee_jour_id=mtj2.id
    where fp.date_distrib>=_date_debut
    and (fp.tournee_jour_id<>mtj2.id)
    ;
    update crm_detail c
    inner join modele_tournee_jour mtj1 on c.modele_tournee_jour_id=mtj1.id
    inner join modele_tournee_jour mtj2 on mtj1.code=mtj2.code and c.date_imputation_paie between mtj2.date_debut and mtj2.date_fin
    set c.modele_tournee_jour_id=mtj2.id
    where c.date_imputation_paie>=_date_debut
    and (c.modele_tournee_jour_id<>mtj2.id)
    ;
    update pai_tournee pt
    inner join modele_tournee_jour mtj on substr(pt.code,1,11)=mtj.code and pt.date_distrib between mtj.date_debut and mtj.date_fin
    set pt.modele_tournee_jour_id=mtj.id
    where pt.date_extrait is null
    and pt.date_distrib>=_date_debut
    and pt.modele_tournee_jour_id<>mtj.id 
    ;
    set rowcount=row_count();
    call int_logrowcount_C(_idtrt,5,'alim_trn_maj_tauxhoraire', 'Maj de la valrem dans les tournées.');
    
    if (rowcount>0) then
      call int_logger(_idtrt,'alim_trn_maj_tauxhoraire','Recalcul des horaires et des majorations');
      call recalcul_tournee_date_distrib(null,null,null);
    end if;
end;  

/*
-- relance si le taux horaire est augmenté dans Pleiades après la date de distrib
create table modele_tournee_jour_20160106 as select * from modele_tournee_jour
call alim_trn_maj_tauxhoraire(15,1,'2016-01-01')
select * from pai_int_log where idtrt=1 order by id desc

     update pai_tournee pt
    inner join modele_tournee_jour mtj on substr(pt.code,1,11)=mtj.code and pt.date_distrib between mtj.date_debut and mtj.date_fin
    set pt.modele_tournee_jour_id=mtj.id
    ,   pt.valrem=mtj.valrem
    where pt.date_extrait is null
    and pt.date_distrib>='2016-01-01'
    and (pt.modele_tournee_jour_id<>mtj.id or pt.valrem<>mtj.valrem)
    ;

 create table client_a_servir_logist_20160101 as select * from client_a_servir_logist csl  where csl.date_distrib>='2016-01-01'
  update client_a_servir_logist csl
    inner join modele_tournee_jour mtj1 on csl.tournee_jour_id=mtj1.id
    inner join modele_tournee_jour mtj2 on mtj1.code=mtj2.code and csl.date_distrib between mtj2.date_debut and mtj2.date_fin
    set csl.tournee_jour_id=mtj2.id
    where csl.date_distrib>='2016-01-01'
    and (csl.tournee_jour_id<>mtj2.id)
    ;

*/
-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS alim_trn_maj_tauxhoraire_remplacement;
create procedure alim_trn_maj_tauxhoraire_remplacement(
    IN 		_utilisateur_id INT,
    IN    _idtrt		      INT,
    IN    _date_debut DATE
) 
this_proc: begin
declare rowcount INTEGER DEFAULT 0;
declare _validation_id INTEGER;
	INSERT INTO modele_remplacement
	(depot_id, flux_id, contrattype_id, utilisateur_id, date_debut, date_fin, date_creation, employe_id, actif) 
	SELECT DISTINCT mr.depot_id, mr.flux_id, mr.contrattype_id, _utilisateur_id, _date_debut, mr.date_fin, now(), mr.employe_id, mr.actif
	FROM modele_remplacement mr
	INNER JOIN modele_remplacement_jour mrj ON mrj.remplacement_id=mr.id
    inner join ref_typetournee rtt on mr.flux_id=rtt.id
    inner join pai_ref_remuneration prr_old on rtt.societe_id=prr_old.societe_id AND rtt.population_id=prr_old.population_id AND date_add(_date_debut,interval -1 day)=prr_old.date_fin
    where _date_debut<=mr.date_fin
    and mrj.tauxhoraire=prr_old.valeur
    ;
    set rowcount=row_count();
    call int_logrowcount_C(_idtrt,5,'alim_trn_maj_tauxhoraire_remplacement', 'Création des remplacements avec le nouveau taux horaire');
    if rowcount=0 then
      leave this_proc;
    end if;
	
    update modele_remplacement mr
	INNER JOIN modele_remplacement_jour mrj ON mrj.remplacement_id=mr.id
    inner join ref_typetournee rtt on mr.flux_id=rtt.id
    inner join pai_ref_remuneration prr_old on rtt.societe_id=prr_old.societe_id AND rtt.population_id=prr_old.population_id AND date_add(_date_debut,interval -1 day)=prr_old.date_fin
    set mr.date_fin=date_add(_date_debut,interval -1 day)
    -- 22/06/2016 On applique à tous les modèles du futur
--    where mtj.date_debut<_date_debut and mtj.date_fin>=_date_debut
    where _date_debut<=mr.date_fin
    and mrj.tauxhoraire=prr_old.valeur
    ;
	-- On supprimer les remplacement du futur à l'ancien taux
    delete mrj from modele_remplacement mr
	INNER JOIN modele_remplacement_jour mrj ON mrj.remplacement_id=mr.id
    inner join ref_typetournee rtt on mr.flux_id=rtt.id
    inner join pai_ref_remuneration prr_old on rtt.societe_id=prr_old.societe_id AND rtt.population_id=prr_old.population_id AND date_add(_date_debut,interval -1 day)=prr_old.date_fin
    where _date_debut<=mr.date_debut and mr.date_debut>mr.date_fin
    and mrj.tauxhoraire=prr_old.valeur
    ;
    delete mr from modele_remplacement mr
    where _date_debut<=mr.date_debut and mr.date_debut>mr.date_fin
    and not exists(select null from modele_remplacement_jour mrj where mrj.remplacement_id=mr.id)
    ;

	INSERT INTO modele_remplacement_jour
	(remplacement_id, jour_id, modele_tournee_id, utilisateur_id, pai_tournee_id, date_distrib, duree, nbcli, tauxhoraire, valrem, etalon, valrem_moyen, etalon_moyen, date_creation) 
	SELECT mr.id, mrj.jour_id, mrj.modele_tournee_id, _utilisateur_id, mrj.pai_tournee_id, mrj.date_distrib, mrj.duree, mrj.nbcli, prr_new.valeur, prr_new.valeur/prr_old.valeur*mrj.valrem, etalon, prr_new.valeur/prr_old.valeur*mrj.valrem_moyen, mrj.etalon_moyen, now()
	FROM modele_remplacement mr
    inner join ref_typetournee rtt on mr.flux_id=rtt.id
    inner join pai_ref_remuneration prr_new on rtt.societe_id=prr_new.societe_id AND rtt.population_id=prr_new.population_id AND _date_debut=prr_new.date_debut
    inner join pai_ref_remuneration prr_old on rtt.societe_id=prr_old.societe_id AND rtt.population_id=prr_old.population_id AND date_add(_date_debut,interval -1 day)=prr_old.date_fin
	INNER JOIN modele_remplacement mr_old on mr.contrattype_id=mr_old.contrattype_id and mr_old.date_fin=date_add(_date_debut,interval -1 day)
	INNER JOIN modele_remplacement_jour mrj on mrj.remplacement_id=mr_old.id and mrj.tauxhoraire=prr_old.valeur
	WHERE mr.date_debut=_date_debut;
	
    set rowcount=row_count();
    call int_logrowcount_C(_idtrt,5,'alim_trn_maj_tauxhoraire_remplacement', 'Création des modèles avec le nouveau taux horaire');
    if rowcount=0 then
      leave this_proc;
    end if;
    CALL mod_valide_remplacement(_validation_id,NULL,NULL,NULL);
	  call int_logger(_idtrt,'alim_trn_maj_tauxhoraire','Recalcul des horaires et des majorations');
	  call recalcul_tournee_date_distrib(null,null,null);
end;  

/*
-- relance si le taux horaire est augmenté dans Pleiades après la date de distrib
create table modele_remplacement_20170106 as select * from modele_remplacement
create table modele_remplacement_jour_20170106 as select * from modele_remplacement_jour
call alim_trn_maj_tauxhoraire_remplacement(15,1,'2017-01-01')
select * from pai_int_log where idtrt=1 order by id desc

delete from modele_remplacement_jour
delete from modele_journal where remplacement_id is not null
delete from modele_remplacement
insert into modele_remplacement select * from modele_remplacement_20170106
insert into modele_remplacement_jour select * from modele_remplacement_jour_20170106
    CALL mod_valide_remplacement(@validation_id,NULL,NULL,NULL);

     update pai_tournee pt
    inner join modele_tournee_jour mtj on substr(pt.code,1,11)=mtj.code and pt.date_distrib between mtj.date_debut and mtj.date_fin
    set pt.modele_tournee_jour_id=mtj.id
    ,   pt.valrem=mtj.valrem
    where pt.date_extrait is null
    and pt.date_distrib>='2016-01-01'
    and (pt.modele_tournee_jour_id<>mtj.id or pt.valrem<>mtj.valrem)
    ;

 create table client_a_servir_logist_20160101 as select * from client_a_servir_logist csl  where csl.date_distrib>='2016-01-01'
  update client_a_servir_logist csl
    inner join modele_tournee_jour mtj1 on csl.tournee_jour_id=mtj1.id
    inner join modele_tournee_jour mtj2 on mtj1.code=mtj2.code and csl.date_distrib between mtj2.date_debut and mtj2.date_fin
    set csl.tournee_jour_id=mtj2.id
    where csl.date_distrib>='2016-01-01'
    and (csl.tournee_jour_id<>mtj2.id)
    ;

*/
-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS alim_trn_insert_heure;
CREATE PROCEDURE alim_trn_insert_heure(
    IN    _utilisateur_id	INT,
    IN    _idtrt		      INT,
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT
) BEGIN
    insert into pai_heure(
      utilisateur_id,date_creation
      ,date_distrib
      ,groupe_id
      ,heure_debut_theo
      ,duree_attente
--      ,heure_debut
    ) select distinct
      _utilisateur_id,sysdate()
      ,_date_distrib
      ,gt.id
      ,gt.heure_debut
      ,'00:00'
--      ,gt.heure_debut
    from modele_tournee_jour mtj
    inner join modele_tournee mt on mt.id=mtj.tournee_id and mt.actif
    inner join groupe_tournee gt on mt.groupe_id=gt.id
    where mtj.jour_id=DAYOFWEEK(_date_distrib)
    and _date_distrib between mtj.date_debut and mtj.date_fin
    and (gt.depot_id=_depot_id or _depot_id is null) and (gt.flux_id=_flux_id or _flux_id is null)
    and not exists(select null from pai_heure ph where ph.date_distrib=_date_distrib and ph.groupe_id=gt.id) -- tournees extraites
    group by 
       gt.id
      ,gt.heure_debut
    ;
    call int_logrowcount_C(_idtrt,5,'alim_trn_insert_heure', 'Insertion des heures');
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS alim_trn_insert_tournee;
CREATE PROCEDURE alim_trn_insert_tournee(
    IN    _utilisateur_id	INT,
    IN    _idtrt		      INT,
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT
) BEGIN
    insert into pai_tournee(
      utilisateur_id,date_creation
      ,date_distrib
      ,heure_id,groupe_id,depot_id,flux_id
      ,modele_tournee_jour_id,code
      ,employe_id
      ,transport_id,nbkm,nbkm_paye
    ) select distinct 
      _utilisateur_id,sysdate()
      ,_date_distrib
      ,ph.id,gt.id,gt.depot_id,gt.flux_id
      ,mtj.id,mtj.code
      ,coalesce(mtj.remplacant_id,mtj.employe_id)
      ,mtj.transport_id,mtj.nbkm,mtj.nbkm_paye
    from modele_tournee_jour mtj
    inner join modele_tournee mt on mt.id=mtj.tournee_id and mt.actif
    inner join groupe_tournee gt on mt.groupe_id=gt.id
    inner join pai_heure ph on ph.groupe_id=mt.groupe_id and ph.date_distrib=_date_distrib
    where _date_distrib between mtj.date_debut and mtj.date_fin
    and mtj.jour_id=DAYOFWEEK(_date_distrib)
    and (gt.depot_id=_depot_id or _depot_id is null) and (gt.flux_id=_flux_id or _flux_id is null)
    and not exists(select null from pai_tournee pt where pt.date_distrib=_date_distrib and pt.modele_tournee_jour_id=mtj.id) -- tournees extraites
  ;
    call int_logrowcount_C(_idtrt,5,'alim_trn_insert_tournee', 'Insertion des tournees');
    
    update pai_tournee pt
    inner join modele_tournee_jour mtj on pt.modele_tournee_jour_id=mtj.id
    inner join modele_remplacement_jour mrj on mrj.modele_tournee_id=mtj.tournee_id and mrj.jour_id=mtj.jour_id
    inner join modele_remplacement mr on mrj.remplacement_id=mr.id and pt.date_distrib between mr.date_debut and mr.date_fin and mr.actif
    set pt.employe_id=mr.employe_id
    where pt.date_extrait is null
    and pt.date_distrib=_date_distrib
    and (pt.depot_id=_depot_id or _depot_id is null) and (pt.flux_id=_flux_id or _flux_id is null)
    ;
END;
/*
 select distinct 
      0,sysdate()
      ,'2017-01-23'
      ,ph.id,gt.id,gt.depot_id,gt.flux_id
      ,mtj.id,mtj.code
      ,coalesce(mtj.remplacant_id,mtj.employe_id)
      ,mtj.transport_id,mtj.nbkm,mtj.nbkm_paye
    from modele_tournee_jour mtj
    inner join modele_tournee mt on mt.id=mtj.tournee_id and mt.actif
    inner join groupe_tournee gt on mt.groupe_id=gt.id
    inner join pai_heure ph on ph.groupe_id=mt.groupe_id and ph.date_distrib='2017-01-23'
    where '2017-01-23' between mtj.date_debut and mtj.date_fin
    and mtj.jour_id=DAYOFWEEK('2017-01-23')
    and (gt.depot_id=20 or 20 is null) and (gt.flux_id=1 or 1 is null)
    and not exists(select null from pai_tournee pt where pt.date_distrib='2017-01-23' and pt.modele_tournee_jour_id=mtj.id) -- tournees extraites
  ;
  */
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS alim_trn_maj_client;
CREATE PROCEDURE alim_trn_maj_client(
    IN    _idtrt		      INT,
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT
) BEGIN
    update client_a_servir_logist l
    inner join pai_tournee pt on l.tournee_jour_id=pt.modele_tournee_jour_id and l.date_distrib=pt.date_distrib and l.depot_id=pt.depot_id and l.flux_id=pt.flux_id -- on ne prend que les bien affect�s
    inner join modele_tournee_jour mtj ON l.tournee_jour_id=mtj.id AND l.date_distrib between mtj.date_debut and mtj.date_fin and DAYOFWEEK(l.date_distrib)=mtj.jour_id
    set l.pai_tournee_id=pt.id
    where pt.date_distrib=_date_distrib and (pt.depot_id=_depot_id or _depot_id is null) and (pt.flux_id=_flux_id or _flux_id is null)
    and l.pai_tournee_id is null -- tournees extraites
    ;
    call int_logrowcount_C(_idtrt,5,'alim_trn_maj_client', 'Maj des clients à servir');
  /*  
    update reperage r
    inner join pai_tournee pt on r.tournee_id=pt.modele_tournee_jour_id and r.date_demar=pt.date_distrib and r.depot_id=pt.depot_id -- and l.flux_id=pt.flux_id
    set r.pai_tournee_id=pt.id
    where pt.date_distrib=_date_distrib and (pt.depot_id=_depot_id or _depot_id is null) and (pt.flux_id=_flux_id or _flux_id is null)
    and r.pai_tournee_id is null -- tournees extraites
    ;
    call int_logrowcount_C(_idtrt,5,'alim_trn_maj_client', 'Maj des repérages');
    */
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS alim_trn_maj_crm;
CREATE PROCEDURE alim_trn_maj_crm(
    IN    _idtrt		      INT,
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT
) BEGIN
    SET @TRIGGER_CRM_RECALCUL_MAJORATION=FALSE;
    update crm_detail cd
    inner join pai_tournee pt on cd.date_imputation_paie=pt.date_distrib and cd.modele_tournee_jour_id=pt.modele_tournee_jour_id
    set cd.pai_tournee_id=pt.id
    where cd.pai_tournee_id is null
    and pt.date_distrib=_date_distrib and (pt.depot_id=_depot_id or _depot_id is null) and (pt.flux_id=_flux_id or _flux_id is null)
    ;
    SET @TRIGGER_CRM_RECALCUL_MAJORATION=TRUE;
    call int_logrowcount_C(_idtrt,5,'alim_trn_maj_crm', 'Maj du crm');
    
    update crm_detail_tmp cd
    inner join pai_tournee pt on cd.date_imputation_paie=pt.date_distrib and cd.modele_tournee_jour_id=pt.modele_tournee_jour_id
    set cd.pai_tournee_id=pt.id
    where cd.pai_tournee_id is null
    and pt.date_distrib=_date_distrib and (pt.depot_id=_depot_id or _depot_id is null) and (pt.flux_id=_flux_id or _flux_id is null)
    ;
    SET @TRIGGER_CRM_RECALCUL_MAJORATION=TRUE;
    call int_logrowcount_C(_idtrt,5,'alim_trn_maj_crm', 'Maj du crm');
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS alim_trn_insert_produit;
CREATE PROCEDURE alim_trn_insert_produit(
    IN    _utilisateur_id	INT,
    IN    _idtrt		      INT,
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT
) BEGIN
    insert into pai_prd_tournee(
      utilisateur_id,date_creation
      ,tournee_id,produit_id,natureclient_id
    ) select 
      _utilisateur_id
      ,sysdate()
      ,l.pai_tournee_id
      ,l.produit_id
      ,l.client_type
    from client_a_servir_logist l
    inner join pai_tournee pt on l.pai_tournee_id=pt.id
    where pt.date_distrib=_date_distrib and (pt.depot_id=_depot_id or _depot_id is null) and (pt.flux_id=_flux_id or _flux_id is null)
    and pt.date_extrait is null
    group by 
      l.pai_tournee_id
      ,l.produit_id
      ,l.client_type
/*    union
      select
      _utilisateur_id
      ,sysdate()
      ,r.pai_tournee_id
      ,r.produit_id
      ,0
    from reperage r
    inner join pai_tournee pt on r.pai_tournee_id=pt.id
    where pt.date_distrib=_date_distrib and (pt.depot_id=_depot_id or _depot_id is null) and (pt.flux_id=_flux_id or _flux_id is null)
    and pt.date_extrait is null
    group by 
      r.pai_tournee_id
      ,r.produit_id*/
    ;            
    call int_logrowcount_C(_idtrt,5,'alim_trn_insert_produit', 'Insertion des produits');
    call alim_trn_maj_qte(_idtrt,_date_distrib, _depot_id, _flux_id);
    call alim_trn_maj_nbcli(_idtrt,_date_distrib, _depot_id, _flux_id);
    call alim_trn_maj_nbcli_unique(_idtrt,_date_distrib, _depot_id, _flux_id);
    call alim_trn_maj_adr(_idtrt,_date_distrib, _depot_id, _flux_id);
--    call alim_trn_maj_reperage(_idtrt,_date_distrib, _depot_id, _flux_id);
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS alim_trn_maj_qte;
CREATE PROCEDURE alim_trn_maj_qte(
    IN    _idtrt		      INT,
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT
) BEGIN
        --  pour la quantite
        --  pour SDVP on prend le nombre de clients
        --  pour Neo/Media on prend le nombre d'exemplaires
    update pai_prd_tournee ppt
    inner join pai_tournee pt on ppt.tournee_id=pt.id
    inner join produit p on ppt.produit_id=p.id
    set ppt.qte = (select case when p.type_id in (2,3) then count(distinct l.abonne_soc_id) else sum(l.qte) end
                from client_a_servir_logist l
                where l.pai_tournee_id=ppt.tournee_id
                and l.produit_id=ppt.produit_id
                and l.client_type=ppt.natureclient_id
                AND l.type_service='L'
                )
    where pt.date_distrib=_date_distrib and (pt.depot_id=_depot_id or _depot_id is null) and (pt.flux_id=_flux_id or _flux_id is null)
    and pt.date_extrait is null
    ;
    call int_logrowcount_C(_idtrt,5,'alim_trn_maj_qte', 'Maj des quantités');
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS alim_trn_maj_nbcli;
CREATE PROCEDURE alim_trn_maj_nbcli(
    IN    _idtrt		      INT,
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT
) BEGIN
    update pai_prd_tournee ppt
    inner join pai_tournee pt on ppt.tournee_id=pt.id
    inner join produit p on ppt.produit_id=p.id
    set ppt.nbcli = (select count(distinct l.abonne_soc_id)
                from client_a_servir_logist l
                where l.pai_tournee_id=ppt.tournee_id
                and l.produit_id=ppt.produit_id
                and l.client_type=ppt.natureclient_id
                AND l.type_service='L'
                -- Si un client a plusieurs produit, on ne le compte qu'une fois
                -- on ne tient pas compte des magazine
                and not exists(select null 
                                from client_a_servir_logist l3
                              inner join produit p3 on l3.produit_id=p3.id
                                where l3.pai_tournee_id=ppt.tournee_id
--                              and p3.soc_code_ext=p.soc_code_ext
                              and l3.client_type=ppt.natureclient_id
                              AND l3.type_service='L'
                              and l.abonne_soc_id=l3.abonne_soc_id
                              and p3.code<p.code
                              and p3.type_id not in (2,3)
                              ))
    where pt.date_distrib=_date_distrib and (pt.depot_id=_depot_id or _depot_id is null) and (pt.flux_id=_flux_id or _flux_id is null)
    and p.type_id not in (2,3)
    and pt.date_extrait is null
    ;
    call int_logrowcount_C(_idtrt,5,'alim_trn_maj_nbcli', 'Maj du nombre de clients');
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS alim_trn_maj_nbcli_unique;
CREATE PROCEDURE alim_trn_maj_nbcli_unique(
    IN    _idtrt		      INT,
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT
) BEGIN
    update pai_prd_tournee ppt
    inner join pai_tournee pt on ppt.tournee_id=pt.id
    inner join produit p on ppt.produit_id=p.id
    set ppt.nbcli_unique = (select count(distinct l.abonne_unique_id)
                from client_a_servir_logist l
                where l.pai_tournee_id=ppt.tournee_id
                and l.produit_id=ppt.produit_id
                and l.client_type=ppt.natureclient_id
                AND l.type_service='L'
                and not exists(select null 
                                from client_a_servir_logist l3
                              inner join produit p3 on l3.produit_id=p3.id
                                where l3.pai_tournee_id=ppt.tournee_id
--                              and p3.soc_code_ext=p.soc_code_ext
                              and l3.client_type=ppt.natureclient_id
                              AND l3.type_service='L'
                              and l.abonne_unique_id=l3.abonne_unique_id
                              and p3.code<p.code
                              and p3.type_id not in (2,3)
                              ))
    where pt.date_distrib=_date_distrib and (pt.depot_id=_depot_id or _depot_id is null) and (pt.flux_id=_flux_id or _flux_id is null)
    and p.type_id not in (2,3)
    and pt.date_extrait is null
    ;
    call int_logrowcount_C(_idtrt,5,'alim_trn_maj_nbcli_unique', 'Maj du nombre de clients uniques');
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS alim_trn_maj_adr;
CREATE PROCEDURE alim_trn_maj_adr(
    IN    _idtrt		      INT,
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT
) BEGIN
    update pai_prd_tournee ppt
    inner join pai_tournee pt on ppt.tournee_id=pt.id
    inner join produit p on ppt.produit_id=p.id
    set ppt.nbadr = (select count(distinct l.adresse_id)
                from client_a_servir_logist l
                where l.pai_tournee_id=ppt.tournee_id
                and l.produit_id=ppt.produit_id
                and l.client_type=ppt.natureclient_id
                AND l.type_service='L'
                and not exists(select null 
                                from client_a_servir_logist l3
                              inner join produit p3 on l3.produit_id=p3.id
                                where l3.pai_tournee_id=ppt.tournee_id
--                              and p3.soc_code_ext=p.soc_code_ext
                              and l3.client_type=ppt.natureclient_id
                              AND l3.type_service='L'
                              and l.adresse_id=l3.adresse_id
                              and p3.code<p.code
                              and p3.type_id not in (2,3)
                              ))
    where pt.date_distrib=_date_distrib and (pt.depot_id=_depot_id or _depot_id is null) and (pt.flux_id=_flux_id or _flux_id is null)
    and p.type_id not in (2,3)
    and pt.date_extrait is null
    ;
    call int_logrowcount_C(_idtrt,5,'alim_trn_maj_adr', 'Maj du nombre d''adresses');
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS alim_trn_insert_supplement;
CREATE PROCEDURE alim_trn_insert_supplement(
    IN    _utilisateur_id	INT,
    IN    _idtrt		      INT,
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT
) BEGIN
    insert into pai_prd_tournee(
      utilisateur_id,date_creation
      ,tournee_id,produit_id,natureclient_id
      ,qte,nbcli,nbcli_unique,nbadr
    ) select 
      _utilisateur_id
      ,sysdate()
      ,pt.id
      ,ms.supplement_id
      ,ppt.natureclient_id
      ,sum(ppt.nbcli),0,0,0
    from modele_supplement ms
    inner join pai_prd_tournee ppt on ms.natureclient_id=ppt.natureclient_id and ms.produit_id=ppt.produit_id
    inner join pai_tournee pt on ppt.tournee_id=pt.id and ms.depot_id=pt.depot_id and ms.flux_id=pt.flux_id
    where pt.date_distrib=_date_distrib and (pt.depot_id=_depot_id or _depot_id is null) and (pt.flux_id=_flux_id or _flux_id is null)
    and pt.date_extrait is null
    and dayofweek(_date_distrib)=ms.jour_id
    and _date_distrib between ms.date_debut and ms.date_fin
    group by 
      pt.id
      ,ms.supplement_id
      ,ppt.natureclient_id
    ;            
    call int_logrowcount_C(_idtrt,5,'alim_trn_insert_supplement', 'Ajout suppléments');
END;
-- ------------------------------------------------------------------------------------------------------------------------------------------------
-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS ALIM_TOURNEE_FROM_LAST_WEEK;
CREATE PROCEDURE ALIM_TOURNEE_FROM_LAST_WEEK(
    IN 		_utilisateur_id INT,
    INOUT _idtrt		      INT,
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT
) BEGIN
    CALL ALIM_TOURNEE_FROM_DATE(_utilisateur_id, _idtrt, _date_distrib, _depot_id, _flux_id, date_add(_date_distrib,interval -7 day));
END;

-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS ALIM_TOURNEE_FROM_DATE;
CREATE PROCEDURE ALIM_TOURNEE_FROM_DATE(
    IN 		_utilisateur_id INT,
    INOUT _idtrt		      INT,
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT,
    IN 		_date_org       DATE
) BEGIN
    DECLARE CONTINUE  HANDLER FOR SQLWARNING    CALL int_logwarning(_idtrt);
    DECLARE EXIT      HANDLER FOR SQLEXCEPTION  CALL int_logerreur(_idtrt);
        
    CALL int_logdebut(_utilisateur_id,_idtrt,'ALIM_TOURNEE_FROM_DATE',_date_distrib,_depot_id,_flux_id);
    CALL int_logger(_idtrt,'ALIM_TOURNEE_FROM_DATE',_date_org);

    CALL alim_trn_nettoyage(_utilisateur_id,_idtrt, _date_distrib, _depot_id, _flux_id);
    CALL alim_trn_insert_heure_from_date(_utilisateur_id, _idtrt, _date_distrib, _depot_id, _flux_id, _date_org);
    CALL alim_trn_insert_tournee_from_date(_utilisateur_id, _idtrt, _date_distrib, _depot_id, _flux_id, _date_org);
    CALL alim_trn_insert_produit_from_date(_utilisateur_id, _idtrt, _date_distrib, _depot_id, _flux_id, _date_org);
    CALL int_logfin2(_idtrt,'ALIM_TOURNEE_FROM_DATE');
END;
-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS alim_trn_insert_heure_from_date;
CREATE PROCEDURE alim_trn_insert_heure_from_date(
    IN    _utilisateur_id	INT,
    IN    _idtrt		      INT,
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT,
    IN 		_date_org       DATE
) BEGIN
    insert into pai_heure(
      utilisateur_id,date_creation
      ,date_distrib
      ,groupe_id
      ,heure_debut_theo
      ,duree_attente
      ,heure_debut
    ) select distinct
      _utilisateur_id,sysdate()
      ,_date_distrib
      ,ph.groupe_id
      ,ph.heure_debut_theo
      ,ph.duree_attente
      ,ph.heure_debut
    from pai_heure ph
    inner join groupe_tournee gt on ph.groupe_id=gt.id
    where ph.date_distrib=_date_org
    and (gt.depot_id=_depot_id or _depot_id is null) and (gt.flux_id=_flux_id or _flux_id is null)
    and not exists(select null from pai_heure ph where ph.date_distrib=_date_distrib and ph.groupe_id=gt.id) -- tournees extraites
    ;
    call int_logrowcount_C(_idtrt,5,'alim_trn_insert_heure_from_date', 'Insertion des heures');
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS alim_trn_insert_tournee_from_date;
CREATE PROCEDURE alim_trn_insert_tournee_from_date(
    IN    _utilisateur_id	INT,
    IN    _idtrt		      INT,
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT,
    IN 		_date_org       DATE
) BEGIN
    insert into pai_tournee(
      utilisateur_id,date_creation
      ,date_distrib
      ,heure_id,groupe_id,depot_id,flux_id
      ,modele_tournee_jour_id,code
      ,employe_id
      ,duree_retard,duree_attente
      ,transport_id,nbkm,nbkm_paye
      ,tournee_org_id,split_id
    ) select distinct
	  _utilisateur_id,sysdate()
	  ,_date_distrib
	  ,ph.id,pt.groupe_id,pt.depot_id,pt.flux_id
	  ,pt.modele_tournee_jour_id,pt.code
	  ,pt.employe_id
      ,pt.duree_retard,pt.duree_attente
      ,pt.transport_id,pt.nbkm,pt.nbkm_paye
      ,pt.tournee_org_id,pt.split_id
    from pai_tournee pt
    inner join modele_tournee_jour mtj on pt.modele_tournee_jour_id=mtj.id
    inner join pai_heure ph on ph.groupe_id=pt.groupe_id and ph.date_distrib=_date_distrib
    where _date_distrib between mtj.date_debut and mtj.date_fin
    and pt.date_distrib=_date_org
    and (pt.depot_id=_depot_id or _depot_id is null) 
    and (pt.flux_id=_flux_id or _flux_id is null)
    and not exists(select null from pai_tournee pt2 where pt2.date_distrib=_date_distrib and pt2.modele_tournee_jour_id=pt.modele_tournee_jour_id) -- tournees extraites
  ;
    call int_logrowcount_C(_idtrt,5,'alim_trn_insert_tournee_from_date', 'Insertion des tournees');
    update pai_tournee pt
    inner join pai_tournee pt2 on pt.date_distrib=pt2.date_distrib and left(pt.code,11)=left(pt2.code,11) and length(pt2.code)=12
    set pt.tournee_org_id=pt2.id
    where pt.date_distrib=_date_distrib
    and (pt.depot_id=_depot_id or _depot_id is null) 
    and (pt.flux_id=_flux_id or _flux_id is null)
    and pt.tournee_org_id is not null
    ;
END;
-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS alim_trn_insert_produit_from_date;
CREATE PROCEDURE alim_trn_insert_produit_from_date(
    IN    _utilisateur_id	INT,
    IN    _idtrt		      INT,
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT,
    IN 		_date_org       DATE
) BEGIN
    insert into pai_prd_tournee(
      utilisateur_id,date_creation
      ,tournee_id,produit_id,natureclient_id,
      qte, nbcli, nbadr, nbcli_unique, nbrep,
      pai_qte, pai_taux, pai_mnt
    ) select 
      _utilisateur_id
      ,sysdate()
      ,pt2.id, ppt.produit_id, ppt.natureclient_id,
      ppt.qte, ppt.nbcli, ppt.nbadr, ppt.nbcli_unique, ppt.nbrep,
      ppt.pai_qte, ppt.pai_taux, ppt.pai_mnt
    from pai_prd_tournee ppt
    inner join pai_tournee pt on ppt.tournee_id=pt.id
    inner join pai_tournee pt2 on pt2.date_distrib=_date_distrib and pt2.code=pt.code
    where pt.date_distrib=_date_org
    and (pt.depot_id=_depot_id or _depot_id is null) and (pt.flux_id=_flux_id or _flux_id is null)
    and pt2.date_extrait is null
    ;            
    call int_logrowcount_C(_idtrt,5,'alim_trn_insert_produit_from_date', 'Insertion des produits');
    call int_logger(_idtrt, 'alim_trn_insert_produit_from_date', 'Recalcul des produits');
    call recalcul_produit_date_distrib(_date_distrib, _depot_id, _flux_id);
END;
