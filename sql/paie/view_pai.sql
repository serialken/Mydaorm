 /*
  select epd.id,e.matricule,concat_ws(' ',e.nom,e.prenom1,e.prenom2) as employe,greatest(epd.date_debut,prm.date_debut),least(epd.date_fin,prm.date_fin),epd.nbheures_garanties,coalesce(sum(time_to_sec(duree))/3600,0) as nbheures_realisees
  from emp_pop_depot epd
  inner join employe e on epd.employe_id=e.id
  inner join pai_ref_mois prm on prm.anneemois='201409' 
  left outer join pai_duree pd on pd.employe_id=epd.id and pd.date_distrib between epd.date_debut and epd.date_fin
  where epd.depot_id=24 and epd.flux_id=1
  and epd.nbheures_garanties is not null
  and epd.date_debut<=prm.date_fin and epd.date_fin>=prm.date_debut
  group by epd.id,epd.date_debut,epd.date_fin
  order by employe;
  */
  
  -- select * from pai_suivi_horaire where depot_id=10 and flux_id=1 and date_distrib='2014-11-21';
  
  DROP TABLE IF EXISTS pai_suivi_horaire;
  DROP VIEW IF EXISTS pai_suivi_horaire;
  CREATE ALGORITHM = TEMPTABLE VIEW  pai_suivi_horaire (date_extrait,depot_id,flux_id,anneemois,date_distrib,tournee_id,activite_id,employe_id,heure_debut,duree,duree_nuit,nbkm_paye) AS 
  SELECT pt.date_extrait,pt.depot_id,pt.flux_id,prm.anneemois,pt.date_distrib,pt.id,null,pt.employe_id,pt.heure_debut_calculee,pt.duree,pt.duree_nuit,pt.nbkm_paye
  FROM pai_tournee pt
  INNER JOIN pai_ref_mois prm on pt.date_distrib between prm.date_debut and prm.date_fin
  WHERE pt.employe_id is not null
  AND (pt.tournee_org_id is null or split_id is not null)
  AND NOT exists(SELECT NULL FROM pai_journal pj INNER JOIN pai_ref_erreur pe on pj.erreur_id=pe.id WHERE pt.id=pj.tournee_id AND NOT pe.valide)
  AND pt.duree<>'00:00:00'
  -- AND date_format(pt.date_distrib,'%Y-%m-%d') NOT LIKE '%-05-01' -- On n'envoie pas les badges le 1er mai mais en prend en compte dans le suivi horaire
  UNION ALL -- On prend duree supplement au niveau de la tournee
  SELECT pa.date_extrait,pa.depot_id,pa.flux_id,prm.anneemois,pa.date_distrib,pa.tournee_id,pa.id,pa.employe_id,pa.heure_debut_calculee,pa.duree,pa.duree_nuit,pa.nbkm_paye
  FROM pai_activite pa
  INNER JOIN pai_ref_mois prm on pa.date_distrib between prm.date_debut and prm.date_fin
  WHERE pa.employe_id is not null
  AND not exists(select null from pai_tournee pt where pa.tournee_id=pt.id and not (pt.tournee_org_id is null or split_id is not null))
  AND NOT exists(SELECT NULL FROM pai_journal pj INNER JOIN pai_ref_erreur pe on pj.erreur_id=pe.id WHERE pa.id=pj.activite_id AND NOT pe.valide)
  AND NOT exists(SELECT NULL FROM pai_journal pj INNER JOIN pai_ref_erreur pe on pj.erreur_id=pe.id WHERE pa.tournee_id=pj.tournee_id AND NOT pe.valide)
  AND pa.duree<>'00:00:00'
  and pa.activite_id<>-1 -- on exclut les temps de retard
  and pa.activite_id<>-10 -- on exclut les heures garanties
  and pa.activite_id<>-11 -- on exclut les heures garanties
  -- AND date_format(pa.date_distrib,'%Y-%m-%d') NOT LIKE '%-05-01' -- On n'envoie pas les badges le 1er mai mais en prend en compte dans le suivi horaire
  ;
  
  DROP TABLE IF EXISTS pai_horaire;
  DROP VIEW IF EXISTS pai_horaire;
  -- Attente = 2
  -- THP = 49
  CREATE VIEW  pai_horaire (depot_id,flux_id,date_distrib,tournee_id,activite_id,employe_id,ordre,heure_debut,duree,hdc) AS 
  SELECT pt.depot_id,pt.flux_id,pt.date_distrib,pt.id as tournee_id,null as activite_id,e.id as employe_id,concat(pt.ordre,'-9'),pt.heure_debut,pt.duree,pt.heure_debut_calculee
  -- Cette vues n'est apperement pas utilisée
  -- Attention, elle retourne les temps de retard
  FROM pai_tournee pt
  INNER JOIN employe e ON pt.employe_id=e.id
  WHERE (pt.tournee_org_id is null or split_id is not null)
  AND NOT exists(SELECT NULL FROM pai_journal pj INNER JOIN pai_ref_erreur pe on pj.erreur_id=pe.id WHERE pt.id=pj.tournee_id AND NOT pe.valide)
  AND pt.duree<>'00:00:00'
  UNION ALL
  SELECT pa.depot_id,pa.flux_id,pa.date_distrib,pt.id as tournee_id,pa.id as activite_id,e.id as employe_id
  ,if(pa.heure_debut is not null,1,if( pt.id is not null,concat(pt.ordre,'-',pa.activite_id),pa.id))
  ,coalesce(pa.heure_debut,pt.heure_debut)
  ,pa.duree,pa.heure_debut_calculee
  FROM pai_activite pa
  left outer join pai_tournee pt on pa.tournee_id=pt.id
  INNER JOIN employe e ON pa.employe_id=e.id
  AND NOT exists(SELECT NULL FROM pai_journal pj INNER JOIN pai_ref_erreur pe on pj.erreur_id=pe.id WHERE pa.id=pj.activite_id AND NOT pe.valide)
  AND NOT exists(SELECT NULL FROM pai_journal pj INNER JOIN pai_ref_erreur pe on pj.erreur_id=pe.id WHERE pa.tournee_id=pj.tournee_id AND NOT pe.valide)
  AND pa.duree<>'00:00:00'
  ;
  