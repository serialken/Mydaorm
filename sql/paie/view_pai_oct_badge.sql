 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 -- select * from pai_int_oct_pointage
 -- select * from emp_mroad
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP TABLE IF EXISTS pai_oct_heure;
DROP VIEW IF EXISTS pai_oct_heure;
CREATE VIEW  pai_oct_heure (date_distrib,employe_id,matricule,depot_id,flux_id,eta,heure_debut,duree,duree_nuit) AS
  SELECT t.date_distrib,t.employe_id,e.matricule,t.depot_id,t.flux_id,d.code,t.heure_debut_calculee,t.duree,t.duree_nuit
  FROM pai_tournee t
  inner join employe e on e.id=t.employe_id
  inner join emp_pop_depot epd on t.employe_id=epd.employe_id and t.date_distrib between epd.date_debut and epd.date_fin -- jointure sur depot/flux ???
  inner join ref_population rp on epd.population_id=rp.id and rp.est_badge
  INNER JOIN depot d ON t.depot_id=d.id
  WHERE (t.tournee_org_id is null or split_id is not null)
  AND NOT exists(SELECT NULL FROM pai_journal pj inner join pai_ref_erreur pe on pj.erreur_id=pe.id WHERE pj.tournee_id=t.id and not pe.valide and pe.rubrique<>'OC')
  AND t.duree<>'00:00:00'
  AND t.date_distrib NOT LIKE '%-05-01' -- On n'envoie pas les badges le 1er mai
--  and d.id in (10,11,18,19,24)
  UNION ALL
  -- On prend dépot/flux du contrat car les activité hors-presse peuvent-être gérées sur un autre centre
--  SELECT a.date_distrib,a.employe_id,e.matricule,a.depot_id,a.flux_id,d.code,a.heure_debut_calculee,a.duree,a.duree_nuit
  SELECT a.date_distrib,a.employe_id,e.matricule,epd.depot_id,epd.flux_id,d.code,a.heure_debut_calculee,a.duree,a.duree_nuit
  FROM pai_activite a
  inner join ref_activite ra on a.activite_id=ra.id and ra.est_badge -- on met les heures de délégation hors temps de travail dans les heures garanties ==> pas de badge
  inner join employe e on e.id=a.employe_id
  inner join emp_pop_depot epd on a.employe_id=epd.employe_id and a.date_distrib between epd.date_debut and epd.date_fin
  inner join ref_population rp on epd.population_id=rp.id and rp.est_badge
--  INNER JOIN depot d ON a.depot_id=d.id
  INNER JOIN depot d ON epd.depot_id=d.id
  WHERE NOT exists(SELECT NULL FROM pai_journal pj inner join pai_ref_erreur pe on pj.erreur_id=pe.id WHERE pj.activite_id=a.id and not pe.valide)
  AND NOT exists(SELECT NULL FROM pai_journal pj inner join pai_ref_erreur pe on pj.erreur_id=pe.id WHERE pj.tournee_id=a.tournee_id and not pe.valide and pe.rubrique<>'OC')
  AND a.duree<>'00:00:00'
  AND a.date_distrib NOT LIKE '%-05-01' -- On n'envoie pas les badges le 1er mai
--  and d.id in (10,11,18,19,24)
  ;
  
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP TABLE IF EXISTS pai_int_oct_pointage;
DROP VIEW IF EXISTS pai_int_oct_pointage;
CREATE VIEW  pai_int_oct_pointage (matricule,date_distrib,ligne) AS 
  select 
    i.matricule,
    i.date_distrib,
    concat_ws(
      ';',
      i.matricule,
      convert(date_format(i.date_distrib,'%Y%m%d') using utf8),
      convert(coalesce(time_format(i.heure_debut_mroad,'%H%i'),'') using utf8),
      convert(coalesce(time_format(i.heure_fin_mroad,'%H%i'),'') using utf8),
      ';;;;'
    ) AS ligne 
  from pai_int_oct_badge i 
--  INNER JOIN emp_mroad em on i.matricule=em.matricule and i.date_distrib between convert(em.date_debut,date) and convert(em.date_fin,date)
  where (coalesce(i.heure_debut_mroad,'*') <> coalesce(i.heure_debut_oct,'*') or coalesce(i.heure_fin_mroad,'*') <> coalesce(i.heure_fin_oct,'*')) 
  order by matricule,date_distrib,ligne
  ;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP TABLE IF EXISTS pai_int_oct_cjexp;
DROP VIEW IF EXISTS pai_int_oct_cjexp;
CREATE VIEW  pai_int_oct_cjexp (matricule,date_distrib,ligne) AS 
    SELECT DISTINCT 
      i.matricule,
      i.date_distrib,
      CONCAT_ws(';',
        '00ENT',
        convert(date_format(`i`.`date_distrib`,'%Y%m%d') using utf8),
        convert(date_format(`i`.`date_distrib`,'%Y%m%d') using utf8),
        'P;O;N;N;O',
        i.matricule
      ) collate utf8_unicode_ci as ligne
      FROM pai_int_oct_horaire i
--      INNER JOIN emp_mroad em on i.matricule=em.matricule and i.date_distrib between convert(em.date_debut,date) and convert(em.date_fin,date)
      WHERE i.hor_cod_imp IS NOT NULL
    UNION ALL
    SELECT DISTINCT 
      i.matricule,
      i.date_distrib,
      CONCAT_WS(';',
        '15EXP',
        i.matricule,
        convert(date_format(`i`.`date_distrib`,'%Y%m%d') using utf8),
        i.hor_cod_imp,
        ';;;;;'
      ) collate utf8_unicode_ci as ligne
      FROM pai_int_oct_horaire i
--      INNER JOIN emp_mroad em on i.matricule=em.matricule and i.date_distrib between convert(em.date_debut,date) and convert(em.date_fin,date)
      WHERE i.hor_cod_imp IS NOT NULL AND i.hor_cod_imp<>0
    ORDER BY 1,2,3;
 
 -- ------------------------------------------------------------------------------------------------------------------------------------------------

DROP VIEW IF EXISTS pai_int_oct_heuresgaranties;
CREATE VIEW  pai_int_oct_heuresgaranties (idtrt,matricule,date_distrib,ligne) AS 
  select
  i.idtrt,
  e.matricule,
  i.date_distrib,
  CONCAT_ws(';',
      e.matricule,
      date_format(i.date_distrib,'%Y%m%d'),
      if(time_to_sec(i.hgaranties)>0,TIME_FORMAT(i.hgaranties,'%H:%i'),'00:00'),
      TIME_FORMAT(i.hdelegation,'%H:%i'),
      TIME_FORMAT(i.hhorspresse,'%H:%i')
    )
  from pai_int_oct_hgaranties i
  inner join employe e on i.employe_id=e.id
--  where  time_to_sec(i.hgaranties)>0 or time_to_sec(i.hdelegation)<>0 or time_to_sec(i.hhorspresse)<>0
  order by matricule,date_distrib;
  
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 /*
DROP TABLE IF EXISTS pai_int_oct_erreur;
DROP VIEW IF EXISTS pai_int_oct_erreur;
CREATE VIEW  pai_int_oct_erreur (idtrt,date_distrib,matricule,ligne) AS 
    select i.idtrt,i.date_distrib,i.matricule,i.msg
    from pai_int_erreur i
    order by i.idtrt,i.date_distrib,i.matricule,i.eta
    ;
    */