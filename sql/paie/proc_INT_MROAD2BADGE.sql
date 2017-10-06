/*

SET @idtrt=null;
call INT_MROAD2BADGE(0,@idtrt,false);
select * from pai_int_erreur where idtrt=@idtrt;
select * from pai_oct_contprev where pers_mat='7000199420'

select * from pai_int_traitement order by id desc;
select * from pai_int_log where idtrt in(select max(id) from pai_int_traitement) order by id desc;
select * from pai_int_log where idtrt=1 order by id desc;

select * from pai_int_oct_badge where matricule='7000379620' order by matricule,date_distrib;
select * from pai_int_oct_badge_mroad where matricule='7000379620' order by matricule,date_distrib;
  SELECT  * FROM pai_oct_heure h where matricule='7000462520' and date_distrib='2015-12-19' order by matricule,date_distrib;
  select * from pai_tournee where employe_id=7324 and date_distrib='2015-12-19'
  select * from employe where id=8767
  select * from emp_pop_depot where employe_id=7324

select * from pai_int_oct_badge_mroad where matricule='7000367320' order by matricule,date_distrib;
select * from pai_int_oct_badge_mroad where matricule like '7000367320' order by matricule,date_distrib;
select * from v_employe where matricule='7000367320';
select etr.depot_org_id,epd.depot_id ,epd.contrat_id
from emp_pop_depot epd 
  left outer join emp_transfert etr ON epd.contrat_id=etr.contrat_id and '2017-01-21' between etr.date_debut and etr.date_fin
  INNER JOIN depot d ON coalesce(etr.depot_org_id,epd.depot_id)=d.id
  where  7077=epd.employe_id and '2017-01-21' between epd.date_debut and epd.date_fin 
  select * from emp_transfert where contrat_id=3013
  select * from emp_contrat where id=3013
select * from pai_int_oct_badge where matricule like '7000367320' order by matricule,date_distrib;
select * from pai_int_oct_pointage where matricule='MET0166520' order by matricule,date_distrib;
select * from pai_oct_pointage where pers_mat='MET0166520' order by pers_mat,bad_dat;
select * from pai_int_oct_pointage order by 1;
select distinct matricule from pai_int_oct_pointage;
select * from pai_oct_pointage where pers_mat='7000360120'
SHOW WARNINGS;
*/
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS INT_MROAD2BADGE;
CREATE PROCEDURE INT_MROAD2BADGE(
    IN 		_utilisateur_id INT,
    INOUT _idtrt		      INT,
    IN 		_isStc        BOOLEAN
) BEGIN
    DECLARE _validation_id int;
    DECLARE CONTINUE  HANDLER FOR SQLWARNING    CALL int_logwarning(_idtrt);
    DECLARE EXIT      HANDLER FOR SQLEXCEPTION  CALL int_logerreur(_idtrt);
        
    CALL int_logdebut(_utilisateur_id,_idtrt,'MROAD2BADGE',null,null,null);
    CALL INT_mroad2badge_exec(_idtrt, _isStc);
    
    call pai_valide_octime(_validation_id , null, null, null);
    CALL int_logfin2(_idtrt,'MROAD2BADGE');
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS INT_mroad2badge_exec;
-- Rafraichir les tables pai_poct avant de d'appeler cette procédure
CREATE PROCEDURE INT_mroad2badge_exec(
    IN    _idtrt		    INT,
    IN 		_isStc        BOOLEAN
) BEGIN
DECLARE _date_debut DATE;
DECLARE _date_fin DATE;
-- Attention, dans la vue materialisée pai_oct_Pointage, il y a le bornage des dates
-- En cas de problème, modifier dernierepaie dans gen_pepp
--  SELECT date_debut INTO _date_debut FROM pai_mois;
--  SET _date_fin:=CURDATE();
 -- mettre date_debut_octime dans pai_mois au lieu de date_debut_string
--  SET _date_debut:='2014-10-21';

  call INT_mroad2badge_nettoyage(_idtrt);
--  call INT_mroad2badge_init();
  call INT_mroad2badge_select(_idtrt,_isStc);
  call INT_mroad2badge_update(_idtrt);
  call INT_mroad2badge_erreur(_idtrt);
  -- call INT_mroad2badge_filtre(_idtrt);
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS INT_mroad2badge_filtre;
-- Rafraichir les tables pai_poct avant de d'appeler cette procédure
CREATE PROCEDURE INT_mroad2badge_filtre(
    IN    _idtrt		    INT
) BEGIN
  -- envoi des temps seulement pour les mensualisés
/*  delete i
  from pai_int_oct_badge i 
  inner join employe e on i.matricule=e.matricule
  INNER JOIN emp_pop_depot epd on e.id=epd.employe_id and i.date_distrib between epd.date_debut and epd.date_fin
  where epd.nbheures_garanties is null or epd.nbheures_garanties=0 or epd.flux_id=1;*/
  -- envoi des temps seulement pour un individu
  delete i
  from pai_int_oct_badge i 
  where i.matricule not in ('7000360120')
  ;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS INT_mroad2badge_nettoyage;
CREATE PROCEDURE INT_mroad2badge_nettoyage(
    IN    _idtrt		    INT
) BEGIN
  CALL int_logger(_idtrt,'','Vide les tables de travail');
  truncate table pai_int_oct_badge;
  truncate table pai_int_oct_badge_mroad;
  truncate table pai_int_oct_badge_mroad;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS INT_mroad2badge_init;
/*
CREATE PROCEDURE INT_mroad2badge_init(
) BEGIN
--  CALL ALIM_EMP_MROAD;
  truncate table emp_mroad;
  
  insert into emp_mroad(id,matricule,date_debut,date_fin)
  select distinct e.id,e.matricule,date_format(greatest(prm.date_debut,epd.date_debut),'%Y%m%d'),date_format(least(prm.date_fin,epd.date_fin),'%Y%m%d')
  from pai_tournee pt
  inner join employe e on pt.employe_id=e.id
  inner join emp_pop_depot epd on e.id=epd.employe_id and pt.date_distrib between epd.date_debut and epd.date_fin
  inner join pai_ref_mois prm on pt.date_distrib between prm.date_debut and prm.date_fin
  where pt.date_distrib>='2014-10-21'
  union
  select distinct e.id,e.matricule,date_format(greatest(prm.date_debut,epd.date_debut),'%Y%m%d'),date_format(least(prm.date_fin,epd.date_fin),'%Y%m%d')
  from pai_activite pa
  inner join employe e on pa.employe_id=e.id
  inner join emp_pop_depot epd on e.id=epd.employe_id and pa.date_distrib between epd.date_debut and epd.date_fin
  inner join pai_ref_mois prm on pa.date_distrib between prm.date_debut and prm.date_fin
  where pa.date_distrib>='2014-10-21'
  ;
END;  
*/
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS INT_mroad2badge_select;
CREATE PROCEDURE INT_mroad2badge_select(
    IN    _idtrt		    INT,
    IN 		_isStc        BOOLEAN
) BEGIN
--  A FAIRE : Supprimer les encadrants
  -- On met tous les temps dans pai_int_oct_badge_mroad (pour des raisons de performance)
  CALL int_logger(_idtrt,'INT_mroad2badge_select','Recupere les heures de Mroad');
  INSERT INTO pai_int_oct_badge_mroad (matricule,date_distrib,eta,heure_debut,duree,duree_nuit)
  SELECT e.matricule,t.date_distrib,d.code,t.heure_debut_calculee,t.duree,t.duree_nuit
  FROM pai_tournee t
  inner join pai_mois pm on t.flux_id=pm.flux_id
  inner join employe e on e.id=t.employe_id
  inner join emp_pop_depot epd on t.employe_id=epd.employe_id and t.date_distrib between epd.date_debut and epd.date_fin -- jointure sur depot/flux ???
  left outer join emp_transfert etr ON epd.contrat_id=etr.contrat_id and t.date_distrib between etr.date_debut and etr.date_fin and epd.depot_id=etr.depot_dst_id
  INNER JOIN depot d ON coalesce(etr.depot_org_id,t.depot_id)=d.id
  WHERE (t.tournee_org_id is null or split_id is not null)
  AND NOT exists(SELECT NULL FROM pai_journal pj inner join pai_ref_erreur pe on pj.erreur_id=pe.id WHERE pj.tournee_id=t.id and not pe.valide and pe.rubrique<>'OC')
  AND t.duree<>'00:00:00'
  AND date_format(t.date_distrib,'%Y-%m-%d') NOT LIKE '%-05-01' -- On n'envoie pas les badges le 1er mai
  and epd.typetournee_id in (1,2)
  and t.date_distrib BETWEEN pm.date_debut AND CURDATE()
  AND (NOT _isStc OR t.employe_id IN (SELECT employe_id FROM pai_stc WHERE date_extrait IS NULL))
  ;

  -- On prend dépot/flux du contrat car les activité hors-presse peuvent-être gérées sur un autre centre
  INSERT INTO pai_int_oct_badge_mroad (matricule,date_distrib,eta,heure_debut,duree,duree_nuit)
  SELECT e.matricule,a.date_distrib,d.code,a.heure_debut_calculee,a.duree,a.duree_nuit
  FROM pai_activite a
  inner join pai_mois pm on a.flux_id=pm.flux_id
  inner join ref_activite ra on a.activite_id=ra.id and ra.est_badge -- on met les heures de délégation hors temps de travail dans les heures garanties ==> pas de badge
  inner join employe e on e.id=a.employe_id
  inner join emp_pop_depot epd on a.employe_id=epd.employe_id and a.date_distrib between epd.date_debut and epd.date_fin
  left outer join emp_transfert etr ON epd.contrat_id=etr.contrat_id and a.date_distrib between etr.date_debut and etr.date_fin and epd.depot_id=etr.depot_dst_id
  INNER JOIN depot d ON coalesce(etr.depot_org_id,epd.depot_id)=d.id
  WHERE NOT exists(SELECT NULL FROM pai_journal pj inner join pai_ref_erreur pe on pj.erreur_id=pe.id WHERE pj.activite_id=a.id and not pe.valide)
  AND NOT exists(SELECT NULL FROM pai_journal pj inner join pai_ref_erreur pe on pj.erreur_id=pe.id WHERE pj.tournee_id=a.tournee_id and not pe.valide and pe.rubrique<>'OC')
  AND a.duree<>'00:00:00'
  AND date_format(a.date_distrib,'%Y-%m-%d') NOT LIKE '%-05-01' -- On n'envoie pas les badges le 1er mai
  and epd.typetournee_id in (1,2)
  and a.date_distrib BETWEEN pm.date_debut AND CURDATE()
  AND (NOT _isStc OR a.employe_id IN (SELECT employe_id FROM pai_stc WHERE date_extrait IS NULL))
  ;
    
  -- On ramène les badges de sortie >24h au jour précédent
  -- COLLATE utf8_bin permet d'être case sensitive
  update pai_oct_pointage p
  inner join pai_oct_pointage p2 on p.pers_mat=p2.pers_mat and p.bad_dat=p2.bad_dat and p2.bad_typ='E' COLLATE utf8_bin
  left outer join pai_oct_pointage p3 on p3.pers_mat=p.pers_mat and p3.bad_dat=p.bad_dat and p3.bad_heure<p.bad_heure and p3.bad_typ='e' COLLATE utf8_bin
  set p.bad_dat=date_add(p.bad_dat,interval -1 day)
  where p.bad_typ='s' COLLATE utf8_bin
  and p3.pers_mat is null
  ;
  -- On met tous les individus+ date entre dernierepaie et aujourd'hui
  -- soit qui ont des badges dans Octime (permet la suppression)
  -- soit qui ont des heures dans MRoad (permet l'ajout)
  CALL int_logger(_idtrt,'INT_mroad2badge_select','Recupere les temps de Octime');
  INSERT INTO pai_int_oct_badge (matricule,date_distrib,eta)
  SELECT  DISTINCT p.pers_mat,p.bad_dat,
  case when n.niv_cod3 between 'PA101' and 'PA120' then '040'
  when not SUBSTR(n.niv_cod3,3,3) REGEXP '^[0-9]+$' then CONCAT('0',SUBSTR(n.niv_cod3,4,2))
  else SUBSTR(n.niv_cod3,3,3)
  end
  FROM pai_oct_pointage p
  INNER JOIN pai_oct_varprev v ON v.pers_mat=p.pers_mat AND p.bad_dat BETWEEN v.var_dat AND v.var_datf
  INNER JOIN pai_oct_nivprev n ON n.pers_mat=p.pers_mat AND p.bad_dat BETWEEN n.niv_dat AND n.niv_datf
 -- INNER JOIN emp_mroad e on p.pers_mat=e.matricule and p.bad_dat BETWEEN convert(e.date_debut,date) AND convert(e.date_fin,date)
  -- Si matricule fini par 2x alors on est sur le flux 2
  inner join pai_mois pm on pm.flux_id=case left(right(p.pers_mat,2),1) when 2 then 2 else 1 end
--  WHERE p.bad_dat BETWEEN pm.date_debut AND CURDATE()
  WHERE p.bad_dat>=pm.date_debut
-- v.par_cod IN ('PORT','POLY','POR2','POL2','POLT','POS2','POST','POT2')
  AND (NOT _isStc OR v.pers_mat IN (SELECT e.matricule FROM pai_stc  s INNER JOIN employe e ON s.employe_id=e.id WHERE s.date_extrait IS NULL))
  
  UNION
  
  SELECT DISTINCT matricule,date_distrib,eta
  FROM pai_int_oct_badge_mroad
  ;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS INT_mroad2badge_update;
CREATE PROCEDURE INT_mroad2badge_update(
    IN    _idtrt		    INT
) BEGIN
  CALL int_logger(_idtrt,'INT_mroad2badge_update','Maj des horaires MRoad');

  CALL int_logger(_idtrt,'INT_mroad2badge_update','Maj des horaires MRoad debut');
  UPDATE pai_int_oct_badge i
  SET	i.heure_debut_mroad =(SELECT min(id.heure_debut) FROM pai_int_oct_badge_mroad id WHERE i.matricule=id.matricule AND i.date_distrib=id.date_distrib AND i.eta=id.eta group by id.matricule,id.date_distrib,id.eta)
  ;
  CALL int_logger(_idtrt,'INT_mroad2badge_update','Maj des horaires MRoad fin');
  UPDATE pai_int_oct_badge i
  SET	i.heure_fin_mroad =addtime(i.heure_debut_mroad,
                                  sec_to_time((SELECT sum(time_to_sec(id.duree)) FROM pai_int_oct_badge_mroad id WHERE i.matricule=id.matricule AND i.date_distrib=id.date_distrib AND i.eta=id.eta group by id.matricule,id.date_distrib,id.eta)))
  ;
  -- On arrondi a la minute la plus proche
  UPDATE pai_int_oct_badge i
  SET	i.heure_debut_mroad = IF(SECOND(heure_debut_mroad) < 30,DATE_FORMAT(heure_debut_mroad, "%H:%i:00"),addtime(DATE_FORMAT(heure_debut_mroad, "%H:%i:00"),'00:01:00'))
  ,   i.heure_fin_mroad   = IF(SECOND(heure_fin_mroad)   < 30,DATE_FORMAT(heure_fin_mroad  , "%H:%i:00"),addtime(DATE_FORMAT(heure_fin_mroad  , "%H:%i:00"),'00:01:00'))
  ;
  CALL int_logger(_idtrt,'INT_mroad2badge_update','Maj des horaires MRoad fin >24h');
  UPDATE pai_int_oct_badge i
  SET	i.heure_fin_mroad =subtime(i.heure_fin_mroad,'24:00:00')
  WHERE i.heure_fin_mroad>'24:00:00'
  ;
  -- Met a jour les horaires Octime sans tenir compte de l'etablissement
  CALL int_logger(_idtrt,'INT_mroad2badge_update','Maj des horaires Octime');
  UPDATE pai_int_oct_badge i
  LEFT OUTER JOIN pai_oct_pointage pe ON i.matricule=pe.pers_mat AND i.date_distrib=pe.bad_dat AND pe.bad_typ='e' COLLATE utf8_bin
  LEFT OUTER JOIN pai_oct_pointage ps ON i.matricule=ps.pers_mat AND i.date_distrib=ps.bad_dat AND ps.bad_typ='s' COLLATE utf8_bin
  SET 	i.heure_debut_oct = CONCAT(SUBSTR(pe.bad_heure,1,2),':',SUBSTR(pe.bad_heure,3,2))
  ,	    i.heure_fin_oct   = CONCAT(SUBSTR(ps.bad_heure,1,2),':',SUBSTR(ps.bad_heure,3,2))
  ;
  CALL int_logger(_idtrt,'INT_mroad2badge_update','Verification des contrats');
  UPDATE pai_int_oct_badge i
  LEFT OUTER JOIN pai_oct_contprev c 	ON i.matricule=c.pers_mat AND i.date_distrib BETWEEN c.cont_datd AND c.cont_datf
  LEFT OUTER JOIN pai_oct_varprev v 	ON i.matricule=v.pers_mat AND i.date_distrib BETWEEN v.var_dat AND v.var_datf AND v.par_cod IN ('PORT','POLY','POR2','POL2','POLT','POS2','POST','POT2')
  LEFT OUTER JOIN pai_oct_nivprev n 	ON i.matricule=n.pers_mat AND i.date_distrib BETWEEN n.niv_dat AND n.niv_datf 
        AND (i.eta between '001' and '099' and i.eta=CONCAT('0',SUBSTR(n.niv_cod3,4,2))
        OR  (i.eta='100' or i.eta between '121' and '999') and i.eta=SUBSTR(n.niv_cod3,3,3)
        OR   i.eta='040' and n.niv_cod3 BETWEEN 'PA101' AND 'PA120') -- Paris géré par Saint-Ouen
  SET Contrat_Ok = (c.pers_mat IS NOT NULL AND v.pers_mat IS NOT NULL AND n.pers_mat IS NOT NULL)
  ;
  CALL int_logger(_idtrt,'INT_mroad2badge_update','Verification des absences');
  UPDATE pai_int_oct_badge i
  LEFT OUTER JOIN pai_oct_saiabs s 		ON i.matricule=s.pers_mat AND i.date_distrib BETWEEN s.abs_dat AND s.abs_fin
  SET Absence_Ok = (s.pers_mat IS NULL)
  ;
  -- JFER et AT99 ne sont pas considées comme des absences
  UPDATE pai_int_oct_badge i
  INNER JOIN pai_oct_saiabs s 		ON i.matricule=s.pers_mat AND i.date_distrib BETWEEN s.abs_dat AND s.abs_fin
  SET 	i.Absence_Ok 	= (s.abs_cod IN ('JFER','AT99'))
  ,	i.abs_cod	= s.abs_cod
  WHERE NOT absence_ok
  ;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS INT_mroad2badge_erreur;
CREATE PROCEDURE INT_mroad2badge_erreur(
    INOUT _idtrt		    INT
) BEGIN
  CALL int_logger(_idtrt,'INT_mroad2badge_erreur','Log des erreurs');
-- badges hors contrat
/*
  INSERT INTO pai_int_erreur(idtrt,date_distrib,matricule,eta,msg)
  SELECT DISTINCT _idtrt,id.date_distrib,id.matricule,id.eta,
  CONCAT_WS(';',
  id.eta,
  id.date_distrib,
  id.matricule,
  p.nom,
  p.prenom1,
--  TRIM(id.code),
  '',
  'L''individu a des temps dans MRoad mais est gere dans le Pepp.'
  )
  FROM pai_int_oct_badge_mroad id
  LEFT OUTER JOIN employe p ON id.matricule=p.matricule
  WHERE NOT exists(select null from emp_mroad iem where id.matricule=iem.matricule and id.date_distrib between convert(iem.date_debut,date) and convert(iem.date_fin,date))
  ;*/
  -- badges hors contrat
  INSERT INTO pai_int_erreur(idtrt,date_distrib,matricule,eta,msg)
  SELECT DISTINCT _idtrt,i.date_distrib,i.matricule,i.eta,
  CONCAT_WS(';',
  i.eta,
  i.date_distrib,
  i.matricule,
  p.nom,
  p.prenom1,
  '',
  'L''individu a des temps dans MRoad mais pas de contrat dans Octime .'
  )
  FROM pai_int_oct_badge i
  INNER JOIN pai_int_oct_badge_mroad id ON i.matricule=id.matricule  AND i.date_distrib=id.date_distrib
  LEFT OUTER JOIN employe p ON i.matricule=p.matricule
  WHERE NOT i.contrat_ok
  ;
-- tournées ou heures sur absence
  INSERT INTO pai_int_erreur(idtrt,date_distrib,matricule,eta,msg)
  SELECT DISTINCT _idtrt,i.date_distrib,i.matricule,i.eta,
  CONCAT_WS(';',
  i.eta,
  i.date_distrib,
  i.matricule,
  p.pers_nom,
  p.pers_pre,
  i.abs_cod,
  'L''individu a des temps dans MRoad alors qu''une absence est saisie dans Octime.'
  )
  FROM pai_int_oct_badge i
  INNER JOIN pai_oct_pers p ON i.matricule=p.pers_mat
  WHERE NOT i.absence_ok AND heure_debut_mroad IS NOT NULL
  ;
-- temps >10h
  INSERT INTO pai_int_erreur(idtrt,date_distrib,matricule,eta,msg)
  SELECT DISTINCT _idtrt,id.date_distrib,id.matricule,id.eta,
  CONCAT_WS(';',
  id.eta,
  id.date_distrib,
  id.matricule,
  p.pers_nom,
  p.pers_pre,
  '',
  '',
  'L''individu a travaillé plus de 10 heures.'
  )
  FROM pai_int_oct_badge_mroad id
  INNER JOIN pai_oct_pers p ON id.matricule=p.pers_mat
  GROUP BY id.matricule, id.date_distrib,id.eta,p.pers_nom,p.pers_pre
  HAVING SUM(TIME_TO_SEC(id.duree))>10*60*60
  ;

-- temps de nuit >6h
  INSERT INTO pai_int_erreur(idtrt,date_distrib,matricule,eta,msg)
  SELECT DISTINCT _idtrt,id.date_distrib,id.matricule,id.eta,
  CONCAT_WS(';',
  id.eta,
  id.date_distrib,
  id.matricule,
  p.pers_nom,
  p.pers_pre,
  '',
  '',
  'L''individu a travaillé plus de 6 heures de nuit.'
  )
  FROM pai_int_oct_badge_mroad id
  INNER JOIN pai_oct_pers p ON id.matricule=p.pers_mat
  GROUP BY id.matricule, id.date_distrib,id.eta,p.pers_nom,p.pers_pre
  HAVING SUM(TIME_TO_SEC(id.duree_nuit))>6*60*60
  ;
END;
