/*
call create_tmp_pai_horaire(11,1,null,null,'2014-11-21');
  call create_tmp_pai_suivi_horaire(null,null,null,null,nu_depot_idll);
call create_tmp_pai_suivi_horaire(null,null,6133,null, '2014-12-02');
select * from tmp_pai_suivi_horaire;
*/

-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS create_tmp_pai_horaire_create;
CREATE PROCEDURE create_tmp_pai_horaire_create()
BEGIN
  DROP TEMPORARY TABLE IF EXISTS tmp_pai_horaire;
  CREATE TEMPORARY TABLE tmp_pai_horaire(
  date_extrait  date
  ,depot_id     int
  ,flux_id      int
  ,typejour_id      int
  ,anneemois    varchar(6)
  ,date_distrib date
  ,tournee_id   int
  ,activite_id  int
  ,ref_activite_id  int
  ,xaoid        varchar(36)
  ,employe_id   int
  ,heure_debut  time
  ,duree        time
  ,duree_nuit   time
  ,duree_garantie        time
  ,nbkm_paye	  decimal(5,1)
  ) engine=memory
  DEFAULT CHARSET=utf8
  COLLATE=utf8_unicode_ci;
END;


-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS create_tmp_pai_horaire;
CREATE PROCEDURE create_tmp_pai_horaire(IN _depot_id INT, IN _flux_id INT, IN _employe_id INT, IN _anneemois varchar(6), IN _date_distrib DATE)
BEGIN
  call create_tmp_pai_horaire_create;

  call pai_valide_logger('create_tmp_pai_horaire', concat_ws(' ',_depot_id , _flux_id, _employe_id, _anneemois , _date_distrib));
  
  insert into tmp_pai_horaire(date_extrait ,depot_id, flux_id ,typejour_id ,anneemois ,date_distrib ,tournee_id ,activite_id ,employe_id ,heure_debut ,duree ,duree_nuit ,nbkm_paye) 
  SELECT pt.date_extrait,pt.depot_id,pt.flux_id,pt.typejour_id,prm.anneemois,pt.date_distrib,pt.id as tournee_id,null as activite_id,pt.employe_id,pt.heure_debut_calculee,pt.duree,pt.duree_nuit,pt.nbkm_paye
  FROM pai_tournee pt
  INNER JOIN pai_ref_mois prm on pt.date_distrib between prm.date_debut and prm.date_fin
  INNER JOIN pai_mois pm ON pt.flux_id=pm.flux_id
  WHERE pt.employe_id is not null
  AND (pt.tournee_org_id is null or split_id is not null)
--  AND NOT exists(SELECT NULL FROM pai_journal pj INNER JOIN pai_ref_erreur pe on pj.erreur_id=pe.id WHERE pt.id=pj.tournee_id AND NOT pe.valide)
--  AND pt.duree<>'00:00:00'
--	AND (pt.depot_id=_depot_id OR _depot_id IS NULL)
	AND (pt.flux_id=_flux_id OR _flux_id IS NULL)
	AND (pt.employe_id=_employe_id OR _employe_id IS NULL)
	AND (pt.date_distrib=_date_distrib OR _date_distrib IS NULL)
	AND (prm.anneemois=_anneemois OR _anneemois IS NULL and pt.date_distrib>=pm.date_debut)
  ;
  call pai_valide_logger('create_tmp_pai_horaire', 'create tournee');

  insert into tmp_pai_horaire(date_extrait ,depot_id, flux_id ,typejour_id, anneemois ,date_distrib ,tournee_id ,activite_id ,ref_activite_id, employe_id ,heure_debut ,duree ,duree_nuit ,duree_garantie ,nbkm_paye) 
  SELECT pa.date_extrait,pa.depot_id,pa.flux_id,pa.typejour_id,prm.anneemois,pa.date_distrib,pa.tournee_id,pa.id as activite_id,pa.activite_id as ref_activite_id,pa.employe_id,pa.heure_debut_calculee,pa.duree,pa.duree_nuit,pa.duree_garantie,pa.nbkm_paye
  FROM pai_activite pa
  left outer join pai_tournee pt on pa.tournee_id=pt.id
  INNER JOIN pai_ref_mois prm on pa.date_distrib between prm.date_debut and prm.date_fin
  INNER JOIN pai_mois pm ON pa.flux_id=pm.flux_id
  WHERE pa.employe_id is not null
  and (pt.tournee_org_id is null or split_id is not null)
--  AND not exists(select null from pai_tournee pt where pa.tournee_id=pt.id and not (pt.tournee_org_id is null or split_id is not null))
--  AND NOT exists(SELECT NULL FROM pai_journal pj INNER JOIN pai_ref_erreur pe on pj.erreur_id=pe.id WHERE pa.id=pj.activite_id AND NOT pe.valide)
--  AND NOT exists(SELECT NULL FROM pai_journal pj INNER JOIN pai_ref_erreur pe on pj.erreur_id=pe.id WHERE pa.tournee_id=pj.tournee_id AND NOT pe.valide)
--  AND pa.duree<>'00:00:00'
  and pa.activite_id not in (-1,-10) -- on exclut les temps de retard, les horaires garanties
--	AND (pa.depot_id=_depot_id OR _depot_id IS NULL) -- on prend les activités quelque soit le dépot
	AND (pa.flux_id=_flux_id OR _flux_id IS NULL)
	AND (pa.employe_id=_employe_id OR _employe_id IS NULL)
	AND (pa.date_distrib=_date_distrib OR _date_distrib IS NULL)
	AND (prm.anneemois=_anneemois OR _anneemois IS NULL and pa.date_distrib>=pm.date_debut)
  ;
  call pai_valide_logger('create_tmp_pai_horaire', 'create activite');

  delete h from tmp_pai_horaire h
  WHERE h.duree='00:00:00'
  ;
  call pai_valide_logger('create_tmp_pai_horaire', 'delete duree=0');

  delete h from tmp_pai_horaire h
  inner join pai_journal pj on  h.tournee_id=pj.tournee_id
  INNER JOIN pai_ref_erreur pe on pj.erreur_id=pe.id 
  WHERE NOT pe.valide
  ;
  call pai_valide_logger('create_tmp_pai_horaire', 'delete tournee erreur');

  delete h from tmp_pai_horaire h
  inner join pai_journal pj on  h.activite_id=pj.activite_id
  INNER JOIN pai_ref_erreur pe on pj.erreur_id=pe.id 
  WHERE NOT pe.valide
  ;
  call pai_valide_logger('create_tmp_pai_horaire', 'delete activite erreur');

  call pai_valide_logger('create_tmp_pai_horaire', 'fin');
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS create_tmp_pai_hchs;
CREATE PROCEDURE create_tmp_pai_hchs(IN _depot_id INT, IN _flux_id INT, IN _anneemois varchar(6))
BEGIN
  call create_tmp_pai_horaire_create;

  call pai_valide_logger('create_tmp_pai_hchs', concat_ws(' ',_depot_id , _flux_id, _anneemois));
  
  insert into tmp_pai_horaire(date_extrait ,depot_id, flux_id ,typejour_id, anneemois ,date_distrib ,tournee_id ,activite_id ,ref_activite_id, xaoid, employe_id ,heure_debut ,duree ,duree_nuit ,duree_garantie ,nbkm_paye) 
  SELECT pa.date_extrait,pa.depot_id,pa.flux_id,pa.typejour_id,_anneemois,pa.date_distrib,pa.tournee_id,pa.id as activite_id,pa.activite_id as ref_activite_id,pa.xaoid,pa.employe_id,pa.heure_debut_calculee,pa.duree,pa.duree_nuit,pa.duree_garantie,pa.nbkm_paye
  FROM pai_hchs ph
  INNER JOIN pai_activite pa ON ph.xaoid=pa.xaoid and pa.date_distrib between ph.date_debut and ph.date_fin
  WHERE ph.anneemois=_anneemois
	AND (ph.depot_id=_depot_id OR _depot_id IS NULL)
	AND (ph.flux_id=_flux_id OR _flux_id IS NULL)
  ;
  call pai_valide_logger('create_tmp_pai_horaire', 'create activite');

  delete h from tmp_pai_horaire h
  WHERE h.duree='00:00:00'
  ;
  call pai_valide_logger('create_tmp_pai_horaire', 'delete duree=0');

  delete h from tmp_pai_horaire h
  inner join pai_journal pj on  h.activite_id=pj.activite_id
  INNER JOIN pai_ref_erreur pe on pj.erreur_id=pe.id 
  WHERE NOT pe.valide
  ;
  call pai_valide_logger('create_tmp_pai_horaire', 'delete activite erreur');

  call pai_valide_logger('create_tmp_pai_horaire', 'fin');
END;

