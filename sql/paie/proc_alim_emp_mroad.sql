/*
Table utilisée pour l'envoi des badges à Octime et des ev à Pleiades NG
Elle n'est utile que tant que le Pepp est encore utilisé.
*/
/*
DROP TABLE `emp_mroad`;
CREATE TABLE `emp_mroad` (
  `id` int(11) NOT NULL,
  `depot_id` int(11) NOT NULL,
  `flux_id` int(11) NOT NULL,
  `rcoid` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `matricule` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `date_debut` varchar(8) DEFAULT NULL,
  `date_fin` varchar(8) DEFAULT NULL,
  `date_debut_mois` varchar(8) DEFAULT NULL,
  `date_fin_mois` varchar(8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
rollback;

call ALIM_EMP_MROAD(0,null,null);
call ALIM_EMP_MROAD(1,null,null);
call ALIM_EMP_MROAD_ANNEEMOIS('201502',18,2);
select * from emp_mroad order by matricule, date_debut;

select * from pai_png_ev_factoryw;
*/

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS ALIM_EMP_MROAD;
CREATE PROCEDURE ALIM_EMP_MROAD(
    IN 		_isStc      BOOLEAN,
    IN 		_depot_id		  INT,
    IN 		_flux_id		  INT
) BEGIN
  delete from emp_mroad;
  
  insert into emp_mroad(id,depot_id,flux_id,rcoid,matricule,date_debut,date_fin,date_debut_mois,date_fin_mois)
    select distinct e.id,null/*epd.depot_id*/,epd.flux_id,epd.rcoid,e.matricule,date_format(greatest(prm.date_debut,epd.dRC),'%Y%m%d'),date_format(least(prm.date_fin,epd.fRC),'%Y%m%d'),date_format(prm.date_debut,'%Y%m%d'),date_format(prm.date_fin,'%Y%m%d')
  from employe e
  inner join emp_pop_depot epd on e.id=epd.employe_id -- and pt.date_distrib between epd.date_debut and epd.date_fin  !!! On prend toutes les rc de la période
  inner join pai_mois pm on pm.flux_id=epd.flux_id
  inner join pai_ref_mois prm on  prm.date_fin>=pm.date_debut 
      and (prm.date_debut<=pm.date_fin -- cas normal
      or prm.date_debut<=sysdate() and epd.fRC<sysdate())
  where (epd.date_debut<=prm.date_fin and epd.date_fin>=prm.date_debut)
  and epd.typetournee_id in (1,2)
  and (epd.depot_id=_depot_id or _depot_id is null)
  and (epd.flux_id=_flux_id or _flux_id is null)
  and (not _isStc and not exists(SELECT null FROM pai_stc s WHERE s.employe_id=e.id and epd.rcoid=s.rcoid and date_extrait IS not NULL)
  or _isStc and exists(SELECT null FROM pai_stc s WHERE s.employe_id=e.id and epd.rcoid=s.rcoid and date_extrait IS NULL))
  ;
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS ALIM_EMP_MROAD_ANNEEMOIS;
CREATE PROCEDURE ALIM_EMP_MROAD_ANNEEMOIS(
    IN    _anneemois  varchar(6),
    IN 		_depot_id		  INT,
    IN 		_flux_id		  INT
) BEGIN
  delete from emp_mroad;
  
  insert into emp_mroad(id,depot_id,flux_id,rcoid,matricule,date_debut,date_fin,date_debut_mois,date_fin_mois)
  select distinct e.id,epd.depot_id,epd.flux_id,epd.rcoid,e.matricule,date_format(greatest(pm.date_debut,epd.date_debut),'%Y%m%d'),date_format(least(pm.date_fin,epd.date_fin),'%Y%m%d'),date_format(pm.date_debut,'%Y%m%d'),date_format(pm.date_fin,'%Y%m%d')
  from pai_tournee pt
  inner join employe e on pt.employe_id=e.id
  inner join emp_pop_depot epd on e.id=epd.employe_id -- and pt.date_distrib between epd.date_debut and epd.date_fin  !!! On prend toutes les rc de la période
  inner join pai_ref_mois pm on pm.anneemois=_anneemois
  where (epd.date_debut<=pm.date_fin and epd.date_fin>=pm.date_debut)
  and epd.typetournee_id in (1,2)
  -- Pour l'instant que ceux qui ont des tournées dans Mroad ==> selection sur individu + date_debut
  -- A partir du 21/03/2015, un dépot sera complètement sur le Pepp ou sur Mroad ==> selection sur une liste de dépot + date_de début
  -- A partir de ???? (décembre 2015), tous les dépôts auront migrés sur MRoad. ==> sélection sur date_de début
  and pt.date_distrib between epd.date_debut and epd.date_fin -- Attention au 1er mai, mais normalement requête modifiée avant !!!!
  and (epd.depot_id=_depot_id or _depot_id is null)
  and (epd.flux_id=_flux_id or _flux_id is null)
--  and (not _isStc or exists(SELECT null FROM pai_stc s WHERE s.employe_id=e.id and date_extrait IS NULL))
  union
  select distinct e.id,epd.depot_id,epd.flux_id,epd.rcoid,e.matricule,date_format(greatest(pm.date_debut,epd.date_debut),'%Y%m%d'),date_format(least(pm.date_fin,epd.date_fin),'%Y%m%d'),date_format(pm.date_debut,'%Y%m%d'),date_format(pm.date_fin,'%Y%m%d')
  from pai_activite pa
  inner join employe e on pa.employe_id=e.id
  inner join emp_pop_depot epd on e.id=epd.employe_id -- and pa.date_distrib between epd.date_debut and epd.date_fin  !!! On prend toutes les rc de la période
  inner join pai_ref_mois pm on pm.anneemois=_anneemois
  where (epd.date_debut<=pm.date_fin and epd.date_fin>=pm.date_debut)
  and pa.date_distrib between epd.date_debut and epd.date_fin -- Attention au 1er mai, mais normalement requête modifiée avant !!!!
  and (epd.depot_id=_depot_id or _depot_id is null)
  and (epd.flux_id=_flux_id or _flux_id is null)
--  and (not _isStc or exists(SELECT null FROM pai_stc s WHERE s.employe_id=e.id and date_extrait IS NULL))
  ;
END;  
