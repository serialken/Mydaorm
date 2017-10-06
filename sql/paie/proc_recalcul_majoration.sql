/*
select max(date_modif) from pai_tournee where date_extrait is not null;
select max(date_modif) from pai_activite where date_extrait is not null;
select max(date_creation) from pai_majoration where date_extrait is not null;

truncate table pai_majoration;
call recalcul_majoration(null, null, null, null);
call recalcul_majoration_hn(null, null, null, null);
call recalcul_majoration('2014-09-21', 14, 1, null);
select * from pai_tournee where valrem_majoree=0 and valrem_paie is not null and valrem_paie<>0;
select * from pai_tournee where valrem_majoree is null;


update pai_majoration set date_extrait='2014-11-20 16:19' where date_distrib<'2014-11-21';
  select * from pai_int_log where idtrt=1 order by id desc limit 100;
  select * from pai_majoration where date_extrait is not null;
  select * from pai_majoration where taux_service_abonne<>0 order by employe_id,date_distrib;
  select * from pai_majoration where taux_service_diffuseur<>0 order by employe_id,date_distrib;
  select * from pai_incident;
  select * from pai_reclamation pr inner join pai_tournee pt on pr.tournee_id=pt.id

    */

/* Calculé par date_distrib / depot_id / flux_id/ employe_id
Dépend de
- valrem_paie
- nbcli
- nombre d'heures de nuit du modele
- réclamation
- incident

Met à jour
- valrem_majoree
- duree (par trigger)
*/
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_majoration_tournee;
CREATE PROCEDURE recalcul_majoration_tournee(
    IN 		_pai_tournee_id     INT
) BEGIN
declare _employe_id     int;
declare _date_distrib   DATE;

  call recalcul_logger('recalcul_majoration',concat('pai_tournee_id=',_pai_tournee_id));
  select date_distrib,employe_id into _date_distrib,_employe_id from pai_tournee where id=_pai_tournee_id;
  call recalcul_majoration(_date_distrib,null,null,_employe_id);
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_majoration;
CREATE PROCEDURE recalcul_majoration(
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT,
    IN 		_employe_id     INT
) BEGIN
  call recalcul_logger('recalcul_majoration',concat_ws('*',_date_distrib,_depot_id,_flux_id,_employe_id));
  call recalcul_majoration_create(_date_distrib,_depot_id,_flux_id,_employe_id);
  call recalcul_majoration_df(_date_distrib,_depot_id,_flux_id,_employe_id);
  call recalcul_majoration_hn(_date_distrib,_depot_id,_flux_id,_employe_id);
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_majoration_create;
CREATE PROCEDURE recalcul_majoration_create(
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT,
    IN 		_employe_id     INT
) BEGIN
    DELETE pm 
    FROM pai_majoration pm
--    INNER JOIN pai_mois m on pm.date_distrib>=m.date_debut and pm.flux_id=m.flux_id
    INNER JOIN emp_pop_depot epd ON pm.employe_id=epd.employe_id and pm.date_distrib BETWEEN epd.date_debut AND epd.date_fin
    WHERE pm.date_extrait is null
    AND (pm.date_distrib=_date_distrib or _date_distrib is null)
    AND (pm.depot_id=_depot_id or epd.depot_id=_depot_id or _depot_id is null)
    AND (pm.flux_id=_flux_id or epd.flux_id=_flux_id or _flux_id is null)
    AND (pm.employe_id=_employe_id or _employe_id is null)
    ;
    call recalcul_logger('recalcul_majoration_create','delete');

    INSERT INTO pai_majoration(date_creation,date_distrib,depot_id,flux_id,employe_id,typetournee_id,majoration_poly,majoration_nuit,majoration_df,duree_nuit_modele,remuneration)     
    SELECT distinct now(),pt.date_distrib,epd.depot_id,epd.flux_id,epd.employe_id,epd.typetournee_id,
    case -- passage à 10€ pour les polyvalents Mediapresse
    when rp.code in ('EMIDTB','EMIDTE','EMDDTB','EMDDTE') and pt.date_distrib<'2015-01-01' then 4.93
    when rp.code in ('EMIDTB','EMIDTE','EMDDTB','EMDDTE') and pt.date_distrib between '2015-01-01' and '2015-12-31' then 4.06
    when rp.code in ('EMIDTB','EMIDTE','EMDDTB','EMDDTE') and pt.date_distrib between '2016-01-01' and '2016-12-31' then 3.41
    when rp.code in ('EMIDTB','EMIDTE','EMDDTB','EMDDTE') and pt.date_distrib between '2017-01-01' and '2017-12-31' then 2.46
    else rp.majoration
    end
    ,rp.majoration_nuit,0,'00:00',r.valeur
    FROM pai_tournee pt
    INNER JOIN emp_pop_depot epd ON pt.employe_id=epd.employe_id and pt.date_distrib BETWEEN epd.date_debut AND epd.date_fin
    INNER JOIN ref_population rp ON epd.population_id=rp.id
	  LEFT OUTER JOIN pai_ref_remuneration r ON epd.societe_id=r.societe_id AND epd.population_id=r.population_id AND pt.date_distrib BETWEEN r.date_debut AND r.date_fin
--    LEFT OUTER JOIN pai_journal pj on pt.id=pj.tournee_id
--    LEFT OUTER JOIN pai_ref_erreur pre on pj.erreur_id=pre.id
    WHERE pt.date_extrait is null
    AND (pt.date_distrib=_date_distrib or _date_distrib is null)
    AND (pt.depot_id=_depot_id or _depot_id is null)
    AND (pt.flux_id=_flux_id or _flux_id is null)
    AND (pt.employe_id=_employe_id or _employe_id is null)
--    AND coalesce(pre.valide,true)
    AND not exists(select null from pai_journal pj inner join pai_ref_erreur pre on pj.erreur_id=pre.id and not pre.valide where pj.tournee_id=pt.id)
    AND (pt.tournee_org_id is null or pt.split_id is not null)
    ;
    call recalcul_logger('recalcul_majoration_create','insert');
    -- Attention : Arreter les calcul si rowcount()=0
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_majoration_df;
CREATE PROCEDURE recalcul_majoration_df(
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT,
    IN 		_employe_id     INT
) BEGIN    -- SDVP
-- ATTENTION (pas fait)
-- Si le 1er est un jour normal on ne majore pas la valeur de rémunération
-- Si le 1er mai est un dimanche, on majore la valeur de rémunération en prenant en compte la moyenne des réclamations sur les 4 dimanches précédents
-- si plusieurs tournées ont été validées pour un même porteur sur un même dimanche, c'est celle qui aura le plus grand nombre de clients qui devra être prise en compte

    -- Seulement les dimanches et jours feriés
    -- Majoration de 100% si aucune réclamation et aucun incident (majoration_dfq)
    -- Majoration de 50% sinon (majoration_df)
    UPDATE pai_majoration pm  
    INNER JOIN emp_pop_depot epd on pm.employe_id=epd.employe_id AND pm.date_distrib between epd.date_debut and epd.date_fin
    LEFT OUTER JOIN (select pt.employe_id,pt.date_distrib
                , SUM(pr.nbrec_abonne)/sum(pt.nbcli)     as taux_service_abonne
                , SUM(pr.nbrec_diffuseur)/sum(pt.nbcli)  as taux_service_diffuseur
                from pai_tournee pt
                inner join pai_reclamation pr on pt.id=pr.tournee_id
                WHERE (pt.date_distrib=_date_distrib or _date_distrib is null)
                AND (pt.depot_id=_depot_id or _depot_id is null)
                AND (pt.flux_id=_flux_id or _flux_id is null)
                group by  pt.employe_id,pt.date_distrib
                ) ts ON pm.employe_id=ts.employe_id AND pm.date_distrib=ts.date_distrib
    LEFT OUTER JOIN (select pi.employe_id,pi.date_distrib 
                , count(*) as nb_incident
                from pai_incident pi
				inner join emp_pop_depot epd on pi.employe_id=epd.employe_id and pi.date_distrib between epd.date_debut and epd.date_fin
                WHERE (pi.date_distrib=_date_distrib or _date_distrib is null)
                AND (epd.depot_id=_depot_id or _depot_id is null)
                AND (epd.flux_id=_flux_id or _flux_id is null)
                group by  pi.employe_id,pi.date_distrib
                ) i ON pm.employe_id=i.employe_id AND pm.date_distrib=i.date_distrib
    set pm.taux_service_abonne     = coalesce(ts.taux_service_abonne,0)
    ,   pm.taux_service_diffuseur  = coalesce(ts.taux_service_diffuseur,0)
    ,   pm.nb_incident             = coalesce(i.nb_incident,0)
    WHERE pm.date_extrait is null
    AND (pm.date_distrib=_date_distrib or _date_distrib is null)
    AND (pm.depot_id=_depot_id or _depot_id is null)
    AND (pm.flux_id=_flux_id or _flux_id is null)
    AND (pm.employe_id=_employe_id or _employe_id is null)
    AND pm.typetournee_id=1  AND pai_typejour_societe(pm.date_distrib,epd.societe_id) in (2,3)
    ;
    call recalcul_logger('recalcul_majoration_df','taux_service');
    -- Neo/Media
    -- Seulement les jours feriés
    -- Majoration de 100%
    UPDATE pai_majoration pm
 --   INNER JOIN pai_mois m ON pm.date_distrib>=m.date_debut and pm.flux_id=m.flux_id
    INNER JOIN emp_pop_depot epd on pm.employe_id=epd.employe_id AND pm.date_distrib between epd.date_debut and epd.date_fin
    INNER JOIN ref_population rp ON epd.population_id=rp.id
    set pm.majoration_df = CASE
      WHEN pm.typetournee_id=2  THEN rp.majoration_df
      WHEN pm.typetournee_id=1  AND pm.taux_service_abonne<=1/1000 AND pm.taux_service_diffuseur<=1/1000 AND pm.nb_incident=0 THEN rp.majoration_dfq
      WHEN pm.typetournee_id=1  THEN rp.majoration_df
      ELSE 0
    END
    WHERE pm.date_extrait is null
    AND (pm.date_distrib=_date_distrib or _date_distrib is null)
    AND (pm.depot_id=_depot_id or _depot_id is null)
    AND (pm.flux_id=_flux_id or _flux_id is null)
    AND (pm.employe_id=_employe_id or _employe_id is null)
    AND (pm.typetournee_id=1  AND pai_typejour_societe(pm.date_distrib,epd.societe_id) in (2,3)
    OR pm.typetournee_id=2  AND pai_typejour_societe(pm.date_distrib,epd.societe_id)=3)
    AND pm.date_distrib not like '%-05-01'
    ;
    call recalcul_logger('recalcul_majoration_df','majoration_df');
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_majoration_hn;
CREATE PROCEDURE recalcul_majoration_hn(
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT,
    IN 		_employe_id     INT
) BEGIN
  -- Recuperation du nombre d'heure de nuit du modèle
	UPDATE pai_majoration pm
    SET duree_nuit_modele = '00:00:00'
    WHERE pm.date_extrait is null
    AND (pm.date_distrib=_date_distrib or _date_distrib is null)
    AND (pm.depot_id=_depot_id or _depot_id is null)
    AND (pm.flux_id=_flux_id or _flux_id is null)
    AND (pm.employe_id=_employe_id or _employe_id is null)
    ;
    call recalcul_logger('recalcul_majoration_hn','duree_nuit_modele 0');
	UPDATE pai_majoration pm
    SET duree_nuit_modele = addtime(duree_nuit_modele,coalesce((select SEC_TO_TIME(SUM(TIME_TO_SEC(pai_heure_nuit(gt.heure_debut,mtj.duree))))
							from modele_tournee_jour mtj use index(modele_tournee_jour_idx6)
							inner join modele_tournee mt on mtj.tournee_id=mt.id AND mt.actif=1
							inner join groupe_tournee gt on mt.groupe_id=gt.id
							and not exists(select null
											from modele_remplacement mr
											inner join modele_remplacement_jour mrj on mrj.remplacement_id=mr.id 
											where pm.date_distrib between mr.date_debut and mr.date_fin and mr.actif
											and mrj.modele_tournee_id=mtj.tournee_id and mrj.jour_id=mtj.jour_id
											)							
							WHERE pm.date_distrib BETWEEN mtj.date_debut AND mtj.date_fin and mtj.remplacant_id is not null AND pm.employe_id=mtj.remplacant_id
              group by mtj.remplacant_id
							),'00:00:00'))
    WHERE pm.date_extrait is null
    AND (pm.date_distrib=_date_distrib or _date_distrib is null)
    AND (pm.depot_id=_depot_id or _depot_id is null)
    AND (pm.flux_id=_flux_id or _flux_id is null)
    AND (pm.employe_id=_employe_id or _employe_id is null)
    AND pm.typetournee_id=1 -- SDVP
    ;
    call recalcul_logger('recalcul_majoration_hn','duree_nuit_modele remplacant');
	UPDATE pai_majoration pm
    SET duree_nuit_modele = addtime(duree_nuit_modele,coalesce((select SEC_TO_TIME(SUM(TIME_TO_SEC(pai_heure_nuit(gt.heure_debut,mtj.duree))))
							from modele_tournee_jour mtj
							inner join modele_tournee mt on mtj.tournee_id=mt.id AND mt.actif=1
							inner join groupe_tournee gt on mt.groupe_id=gt.id
							and not exists(select null
											from modele_remplacement mr
											inner join modele_remplacement_jour mrj on mrj.remplacement_id=mr.id 
											where pm.date_distrib between mr.date_debut and mr.date_fin and mr.actif
											and mrj.modele_tournee_id=mtj.tournee_id and mrj.jour_id=mtj.jour_id
											)							
							WHERE pm.date_distrib BETWEEN mtj.date_debut AND mtj.date_fin and mtj.remplacant_id is null AND pm.employe_id=mtj.employe_id
              group by mtj.employe_id
							),'00:00:00'))
    WHERE pm.date_extrait is null
    AND (pm.date_distrib=_date_distrib or _date_distrib is null)
    AND (pm.depot_id=_depot_id or _depot_id is null)
    AND (pm.flux_id=_flux_id or _flux_id is null)
    AND (pm.employe_id=_employe_id or _employe_id is null)
    AND pm.typetournee_id=1 -- SDVP
    ;
    call recalcul_logger('recalcul_majoration_hn','duree_nuit_modele employe');
	UPDATE pai_majoration pm
    SET duree_nuit_modele = addtime(duree_nuit_modele,coalesce((select SEC_TO_TIME(SUM(TIME_TO_SEC(pai_heure_nuit(gt.heure_debut,mrj.duree))))
							from modele_remplacement mr
							inner join modele_remplacement_jour mrj on mrj.remplacement_id=mr.id
							inner join modele_tournee_jour mtj on mrj.modele_tournee_id=mtj.tournee_id and mrj.jour_id=mtj.jour_id 
							inner join modele_tournee mt on mtj.tournee_id=mt.id AND mt.actif=1
							inner join groupe_tournee gt on mt.groupe_id=gt.id
							where pm.date_distrib between mr.date_debut and mr.date_fin and mr.actif and mr.employe_id=pm.employe_id
							and pm.date_distrib BETWEEN mtj.date_debut AND mtj.date_fin
							),'00:00:00'))
    WHERE pm.date_extrait is null
    AND (pm.date_distrib=_date_distrib or _date_distrib is null)
    AND (pm.depot_id=_depot_id or _depot_id is null)
    AND (pm.flux_id=_flux_id or _flux_id is null)
    AND (pm.employe_id=_employe_id or _employe_id is null)
    AND pm.typetournee_id=1 -- SDVP
    ;
    call recalcul_logger('recalcul_majoration_hn','duree_nuit_modele remplacement');
	
    -- On remet à 0 la majoration de nuit lorsque le nombre d'heure nuit du modèle (sur une semaine)est inférieur à 5
    UPDATE pai_majoration pm
    SET pm.majoration_nuit = 0
    WHERE pm.date_extrait is null
    AND (pm.date_distrib=_date_distrib or _date_distrib is null)
    AND (pm.depot_id=_depot_id or _depot_id is null)
    AND (pm.flux_id=_flux_id or _flux_id is null)
    AND (pm.employe_id=_employe_id or _employe_id is null)
  	AND TIME_TO_SEC(duree_nuit_modele)<5*3600
    AND pm.typetournee_id=1 -- SDVP
    ;
    call recalcul_logger('recalcul_majoration_hn','majoration_nuit');
    --    La majoration ne doit pas dépasser 100% (avec les heures de nuit)
    UPDATE pai_tournee t
    LEFT OUTER JOIN emp_pop_depot e ON t.employe_id=e.employe_id AND t.date_distrib BETWEEN e.date_debut AND e.date_fin
    LEFT OUTER JOIN pai_majoration pm ON t.date_distrib=pm.date_distrib and t.employe_id=pm.employe_id
    SET t.valrem_majoree=
      CASE
        WHEN pm.id is null THEN  t.valrem_paie
  	WHEN pm.typetournee_id=2 THEN round(t.valrem_paie*(1+t.majoration/100)*(1+pm.majoration_poly/100)*(1+pm.majoration_df/100),5)
        WHEN t.nbcli*t.valrem_paie*(1+t.majoration/100)*(1+pm.majoration_poly/100)*(1+pm.majoration_df/100)+(TIME_TO_SEC(t.duree_nuit)/3600)*(pm.majoration_nuit/100)*pm.remuneration>t.nbcli*t.valrem_paie*2 THEN
              round((t.nbcli*t.valrem_paie*(1+t.majoration/100)*(1+pm.majoration_poly/100)*2-(TIME_TO_SEC(t.duree_nuit)/3600)*(pm.majoration_nuit/100)*pm.remuneration)/t.nbcli, 5)
        ELSE
              round(t.valrem_paie*(1+t.majoration/100)*(1+pm.majoration_poly/100)*(1+pm.majoration_df/100), 5)
        END
    WHERE t.date_extrait is null
    AND (t.date_distrib=_date_distrib or _date_distrib is null)
    AND (t.depot_id=_depot_id or _depot_id is null)
    AND (t.flux_id=_flux_id or _flux_id is null)
    AND (t.employe_id=_employe_id or _employe_id is null)
  	;
    call recalcul_logger('recalcul_majoration_hn','valrem_majoree');
END;
