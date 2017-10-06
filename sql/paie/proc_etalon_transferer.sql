/*
select * from utilisateur
set @id=null;
call etalon_transferer(@id,8,52)
select * from modele_tournee_jour  where code like '004NAM064%'
select * from pai_tournee where modele_tournee_jour_id in (41435,49222,63736) order by date_distrib desc

select * from etalon order by id desc
select * from etalon_tournee where id=57 order by id desc
select * from pai_int_traitement where typetrt='etalon_transferer'
select * from pai_int_log where idtrt=7628
set id
call etalon_transferer_pai_tournee(7580,8,34);
call etalon_transferer_pai_tournee(7582,8,25);
call etalon_transferer_pai_tournee(7628,8,35);

  
DROP TABLE `etalon_transferer`;
CREATE TABLE `etalon_transferer` (
  `etalon_id` int(11) NOT NULL,
  `date_application` date NOT NULL,
  `old_modele_tournee_jour_id` int(11) NOT NULL,
  `new_modele_tournee_jour_id` int(11) DEFAULT NULL,
  KEY `etalon_transferer_idx` (`etalon_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1618 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

select * from pai_int_traitement order by id desc
select * from pai_int_log where idtrt=7768
*/
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS etalon_transferer;
create procedure etalon_transferer(
  INOUT _idtrt              INT, 
  IN		_utilisateur_id     INT,
  IN		_etalon_id          INT
) begin
declare _depot_id int;
declare _flux_id int;
declare _validation_id int;
DECLARE EXIT      HANDLER FOR SQLEXCEPTION  
  BEGIN
    ROLLBACK;
    RESIGNAL;
  END;
  select e.depot_id,e.flux_id into _depot_id,_flux_id 
  from etalon e
  where e.id=_etalon_id;

  CALL int_logdebut(_utilisateur_id,_idtrt,'ETALON_TRANSFERER',null,_depot_id,_flux_id);
  CALL int_logger(_idtrt,'etalon_transferer',concat('etalon_id=',_etalon_id));
START TRANSACTION;
  insert into etalon_transferer(etalon_id,date_application,old_modele_tournee_jour_id)
  select e.id,e.date_application,mtj.id
  from etalon e
  inner join etalon_tournee et ON et.etalon_id=e.id
  inner join modele_tournee_jour mtj on et.modele_tournee_id=mtj.tournee_id and et.jour_id=mtj.jour_id
  where e.id=_etalon_id
  and mtj.date_fin>=e.date_application
  ;
  INSERT INTO pai_int_log(idtrt,date_log,module,msg) 
  SELECT distinct _idtrt,now(),'etalon_transferer',CONCAT_WS(' ','Suppression des modèles inutilisés ',mtj.code,mtj.date_debut,mtj.date_fin)
  FROM etalon e
  inner join etalon_tournee et ON et.etalon_id=e.id
  inner join modele_tournee_jour mtj on et.modele_tournee_id=mtj.tournee_id and et.jour_id=mtj.jour_id
  where e.id=_etalon_id
  and mtj.date_debut>=e.date_application
  ;
  -- on supprime les modeles de tournee jour qui ne sont/seront pas utilisés
  update etalon e
  inner join etalon_tournee et ON et.etalon_id=e.id
  inner join modele_tournee_jour mtj on et.modele_tournee_id=mtj.tournee_id and et.jour_id=mtj.jour_id
  set mtj.date_debut='2999-01-01'
  ,   mtj.date_fin='2999-01-01'
  where e.id=_etalon_id
  and mtj.date_debut>=e.date_application
  ;
-- on supprime l'employé des modèles qui ne se trouve pas dans l'étalonnage
  INSERT INTO pai_int_log(idtrt,date_log,module,msg) 
  SELECT distinct _idtrt,now(),'etalon_transferer',CONCAT_WS(' ','Suppression de l''employés des modèles qui ne sont pas dans l''étalonnage ',mtj.code,mtj.date_debut,mtj.date_fin)
  FROM modele_tournee_jour mtj 
  INNER JOIN etalon e ON e.employe_id=mtj.employe_id
  INNER JOIN ref_typeetalon rte on e.type_id=rte.id and (rte.dimanche and mtj.jour_id=1 or rte.lundi and mtj.jour_id=2 or rte.mardi and mtj.jour_id=3 or rte.mercredi and mtj.jour_id=4 or rte.jeudi and mtj.jour_id=5 or rte.vendredi and mtj.jour_id=6 or rte.samedi and mtj.jour_id=7)
  left outer join (SELECT mtj.id
                      FROM etalon e
                      inner join etalon_tournee et on e.id=et.etalon_id
                      inner join modele_tournee_jour mtj on et.modele_tournee_id=mtj.tournee_id and et.jour_id=mtj.jour_id
                      where e.id=_etalon_id
                      and e.date_application<=mtj.date_fin
                      ) as tmp on mtj.id=tmp.id
  WHERE e.id=_etalon_id
  AND e.date_application<=mtj.date_fin
  AND tmp.id is null 
  ;
  UPDATE modele_tournee_jour mtj 
  INNER JOIN etalon e ON e.employe_id=mtj.employe_id
  INNER JOIN ref_typeetalon rte on e.type_id=rte.id and (rte.dimanche and mtj.jour_id=1 or rte.lundi and mtj.jour_id=2 or rte.mardi and mtj.jour_id=3 or rte.mercredi and mtj.jour_id=4 or rte.jeudi and mtj.jour_id=5 or rte.vendredi and mtj.jour_id=6 or rte.samedi and mtj.jour_id=7)
  left outer join (SELECT mtj.id
                      FROM etalon e
                      inner join etalon_tournee et on e.id=et.etalon_id
                      inner join modele_tournee_jour mtj on et.modele_tournee_id=mtj.tournee_id and et.jour_id=mtj.jour_id
                      where e.id=_etalon_id
                      and e.date_application<=mtj.date_fin
                      ) as tmp on mtj.id=tmp.id
  SET mtj.employe_id=null
  WHERE e.id=_etalon_id
  AND e.date_application<=mtj.date_fin
  AND tmp.id is null ;

  INSERT INTO pai_int_log(idtrt,date_log,module,msg) 
  SELECT distinct _idtrt,now(),'etalon_transferer',CONCAT_WS(' ','Création des modèles ',mt.code,rj.code,e.date_application,'2999-01-01')
  FROM etalon e
  inner join etalon_tournee et ON et.etalon_id=e.id
  inner join modele_tournee mt on et.modele_tournee_id=mt.id
  inner join ref_jour rj on et.jour_id=rj.id
  inner join ref_typetournee rtt on e.flux_id=rtt.id
  inner join pai_ref_remuneration prr_new on rtt.societe_id=prr_new.societe_id AND rtt.population_id=prr_new.population_id AND e.date_application between prr_new.date_debut and prr_new.date_fin
  where e.id=_etalon_id
  ;
  INSERT INTO modele_tournee_jour
  ( tournee_id, jour_id
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
  SELECT DISTINCT
  et.modele_tournee_id,et.jour_id
  , e.date_application, '2999-01-01'
  , e.employe_id, null
  , et.transport_id, et.nbkm, et.nbkm_paye
  , et.depart_depot, et.retour_depot
  , prr_new.valeur
  -- ATTENTION, ne faut-il pas recalculer la valeur etalon dans l'interface ???
  , prr_new.valeur/et.tauxhoraire*et.valrem_calculee,prr_new.valeur/et.tauxhoraire*et.valrem_moyen
  , et.etalon_calcule , et.etalon_moyen
  , subtime(subtime(et.duree,et.duree_reperage),et.duree_supplement), et.nbcli
  , now(), _utilisateur_id
  FROM etalon e
  inner join etalon_tournee et ON et.etalon_id=e.id
  inner join ref_typetournee rtt on e.flux_id=rtt.id
  inner join pai_ref_remuneration prr_new on rtt.societe_id=prr_new.societe_id AND rtt.population_id=prr_new.population_id AND e.date_application between prr_new.date_debut and prr_new.date_fin
  where e.id=_etalon_id
  ;
  update etalon_transferer ett
  inner join modele_tournee_jour old_mtj on ett.old_modele_tournee_jour_id=old_mtj.id
  inner join modele_tournee_jour new_mtj on old_mtj.tournee_id=new_mtj.tournee_id and old_mtj.jour_id=new_mtj.jour_id
  set new_modele_tournee_jour_id=new_mtj.id
  where ett.etalon_id=_etalon_id
  and new_mtj.date_debut=ett.date_application
  and new_mtj.date_fin='2999-01-01'
  ;
  -- on met à jour la date de fin
  INSERT INTO pai_int_log(idtrt,date_log,module,msg) 
  SELECT distinct _idtrt,now(),'etalon_transferer',CONCAT_WS(' ','Mise à jour de la date de fin ',mtj.code,mtj.date_debut,mtj.date_fin,'->',adddate(e.date_application,interval -1 day))
  FROM  etalon e
  inner join etalon_tournee et ON et.etalon_id=e.id
  inner join modele_tournee_jour mtj on et.modele_tournee_id=mtj.tournee_id and et.jour_id=mtj.jour_id
  where e.id=_etalon_id
  and mtj.date_debut<e.date_application
  and mtj.date_fin>=e.date_application
  ;
  update etalon e
  inner join etalon_tournee et ON et.etalon_id=e.id
  inner join modele_tournee_jour mtj on et.modele_tournee_id=mtj.tournee_id and et.jour_id=mtj.jour_id
  set mtj.date_fin=adddate(e.date_application,interval -1 day)
  where e.id=_etalon_id
  and mtj.date_debut<e.date_application
  and mtj.date_fin>=e.date_application;

  call etalon_transferer_pai_tournee(_idtrt, _utilisateur_id, _etalon_id);
  call etalon_transferer_casl(_idtrt, _utilisateur_id, _etalon_id);
  call etalon_transferer_portage(_idtrt, _utilisateur_id, _etalon_id);
  call etalon_transferer_crm(_idtrt, _utilisateur_id, _etalon_id);
  call etalon_transferer_cptr_distribution(_idtrt, _utilisateur_id, _etalon_id);
  call etalon_transferer_adresse_livree(_idtrt, _utilisateur_id, _etalon_id);

  -- on supprime les modeles de tournee jour qui ne sont/seront pas utilisés
  INSERT INTO pai_int_log(idtrt,date_log,module,msg) 
  SELECT distinct _idtrt,now(),'etalon_transferer',CONCAT_WS(' ','Suppression des modèles inutilisés',mtj.code,mtj.date_debut,mtj.date_fin,'->',adddate(e.date_application,interval -1 day))
  FROM  etalon e
  INNER JOIN etalon_tournee et ON et.etalon_id=e.id
  INNER JOIN modele_tournee_jour mtj on et.modele_tournee_id=mtj.tournee_id and et.jour_id=mtj.jour_id
  where e.id=_etalon_id
  and mtj.date_debut='2999-01-01'
  ;
  delete mj
  FROM etalon e
  INNER JOIN etalon_tournee et ON et.etalon_id=e.id
  INNER JOIN modele_tournee_jour mtj on et.modele_tournee_id=mtj.tournee_id and et.jour_id=mtj.jour_id
  INNER JOIN modele_journal mj on mj.tournee_jour_id=mtj.id
  where e.id=_etalon_id
  and mtj.date_debut='2999-01-01'
  ;
  delete mtj
  FROM etalon e
  INNER JOIN etalon_tournee et ON et.etalon_id=e.id
  INNER JOIN modele_tournee_jour mtj on et.modele_tournee_id=mtj.tournee_id and et.jour_id=mtj.jour_id
  where e.id=_etalon_id
  and mtj.date_debut='2999-01-01';
 
  CALL int_logger(_idtrt,'mod_valide_cycle','');
  call mod_valide_cycle(_validation_id,_depot_id,_flux_id);
  CALL int_logger(_idtrt,'mod_valide_tournee_jour','');
  call mod_valide_tournee_jour(_validation_id,_depot_id,_flux_id,null,null);
  CALL int_logger(_idtrt,'pai_valide_tournee','');
  call pai_valide_tournee(_validation_id,_depot_id,_flux_id,null,null);
  CALL int_logger(_idtrt,'recalcul_horaire','');
  -- 04/01/2017
--  call recalcul_horaire(_validation_id,_depot_id,_flux_id,null,null);
  call recalcul_tournee_etalon(_validation_id,_etalon_id);
COMMIT;
  CALL int_logfin2(_idtrt,'ETALON_TRANSFERER');
end;

-- ----------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS etalon_transferer_pai_tournee;
create procedure etalon_transferer_pai_tournee(
  IN    _idtrt              INT, 
  IN		_utilisateur_id     INT,
  IN		_etalon_id          INT
) begin
  update pai_tournee pt
  inner join etalon_transferer ett on ett.old_modele_tournee_jour_id=pt.modele_tournee_jour_id and pt.date_distrib>=ett.date_application
  inner join modele_tournee_jour mtj on ett.new_modele_tournee_jour_id=mtj.id
  set pt.modele_tournee_jour_id=ett.new_modele_tournee_jour_id
  ,   pt.transport_id=mtj.transport_id
  ,   pt.nbkm=mtj.nbkm
  ,   pt.nbkm_paye=mtj.nbkm_paye
   where ett.etalon_id=_etalon_id
  and pt.modele_tournee_jour_id<>ett.new_modele_tournee_jour_id
  and pt.date_extrait is null
  ;
  call int_logrowcount_C(_idtrt,5,'etalon_transferer_pai_tournee','');
end;

-- ----------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS etalon_transferer_casl;
create procedure etalon_transferer_casl(
  IN    _idtrt              INT, 
  IN		_utilisateur_id     INT,
  IN		_etalon_id          INT
) begin
  update client_a_servir_logist x
  inner join etalon_transferer ett on ett.old_modele_tournee_jour_id=x.tournee_jour_id and x.date_distrib>=ett.date_application
  set x.tournee_jour_id=ett.new_modele_tournee_jour_id
  where ett.etalon_id=_etalon_id
  and x.tournee_jour_id<>ett.new_modele_tournee_jour_id
  ;
  call int_logrowcount_C(_idtrt,5,'etalon_transferer_casl','');
end;

-- ----------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS etalon_transferer_portage;
create procedure etalon_transferer_portage(
  IN    _idtrt              INT, 
  IN		_utilisateur_id     INT,
  IN		_etalon_id          INT
) begin
  update feuille_portage x
  inner join etalon_transferer ett on ett.old_modele_tournee_jour_id=x.tournee_jour_id and x.date_distrib>=ett.date_application
  set x.tournee_jour_id=ett.new_modele_tournee_jour_id
  where ett.etalon_id=_etalon_id
  and x.tournee_jour_id<>ett.new_modele_tournee_jour_id
  ;
  call int_logrowcount_C(_idtrt,5,'etalon_transferer_portage','');
end;
-- ----------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS etalon_transferer_crm;
create procedure etalon_transferer_crm(
  IN    _idtrt              INT, 
  IN		_utilisateur_id     INT,
  IN		_etalon_id          INT
) begin
  set @TRIGGER_CRM_RECALCUL_MAJORATION=false;
  -- On recalcul les majorations à la fin de _update_valrem avec recalcul_horaire_modele
  update crm_detail x
  inner join etalon_transferer ett on ett.old_modele_tournee_jour_id=x.modele_tournee_jour_id and coalesce(x.date_imputation_paie,'2999-01-01')>=ett.date_application
  set x.modele_tournee_jour_id=ett.new_modele_tournee_jour_id
  where ett.etalon_id=_etalon_id
  and x.modele_tournee_jour_id<>ett.new_modele_tournee_jour_id
  ;
  call int_logrowcount_C(_idtrt,5,'etalon_transferer_crm','crm_detail');
  set @TRIGGER_CRM_RECALCUL_MAJORATION=true;
  
  update crm_detail_tmp x
  inner join etalon_transferer ett on ett.old_modele_tournee_jour_id=x.modele_tournee_jour_id and coalesce(x.date_imputation_paie,'2999-01-01')>=ett.date_application
  set x.modele_tournee_jour_id=ett.new_modele_tournee_jour_id
  where ett.etalon_id=_etalon_id
  and x.modele_tournee_jour_id<>ett.new_modele_tournee_jour_id
  ;
  call int_logrowcount_C(_idtrt,5,'etalon_transferer_crm','crm_detail_tmp');
end;

-- ----------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS etalon_transferer_cptr_distribution;
create procedure etalon_transferer_cptr_distribution(
  IN    _idtrt              INT, 
  IN		_utilisateur_id     INT,
  IN		_etalon_id          INT
) begin
  update cptr_distribution x
  inner join etalon_transferer ett on ett.old_modele_tournee_jour_id=x.tournee_id and x.date_cpt_rendu>=ett.date_application
  set x.tournee_id=ett.new_modele_tournee_jour_id
  where ett.etalon_id=_etalon_id
  and x.tournee_id<>ett.new_modele_tournee_jour_id
  ;
  call int_logrowcount_C(_idtrt,5,'etalon_transferer_cptr_distribution','');
end;

-- ----------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS etalon_transferer_adresse_livree;
create procedure etalon_transferer_adresse_livree(
  IN    _idtrt              INT, 
  IN		_utilisateur_id     INT,
  IN		_etalon_id          INT
) begin
  update adresse_livree x
  inner join etalon_transferer ett on ett.old_modele_tournee_jour_id=x.tournee_jour_id and x.date_distrib>=ett.date_application
  set x.tournee_jour_id=ett.new_modele_tournee_jour_id
  where ett.etalon_id=_etalon_id
  and x.tournee_jour_id<>ett.new_modele_tournee_jour_id
  ;
  call int_logrowcount_C(_idtrt,5,'etalon_transferer_adresse_livree','');
end;
