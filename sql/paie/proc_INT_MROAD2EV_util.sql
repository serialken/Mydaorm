/*
 select * from pai_int_traitement order by id desc
   update pai_int_traitement set statut='E' where id=9010;

 call int_mroad2ev_reinit(10552);
 
UPDATE pai_mois 
SET anneemois = '201705' , annee = '2017', mois = '01', libelle = 'Janvier 2017', 
date_debut = '2016-12-21', date_fin = '2017-01-20', date_debut_string = '20161221', date_fin_string = '20170120', 
date_blocage = '2017-01-25', anneemois_reclamation = '201702'; 

201702	2017	02	Février 2017	21/01/2017 00:00:00	20/02/2017 00:00:00	20170121	20170220	1		201702
;

 */
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_reinit;
CREATE PROCEDURE int_mroad2ev_reinit(
    IN 	  _idtrt		INT)
BEGIN
DECLARE _date_extrait DATETIME;
  SELECT date_debut INTO _date_extrait FROM pai_int_traitement WHERE id=_idtrt;

  update pai_int_traitement set statut='E' where id=_idtrt;
  
  call int_loglevel(_idtrt,4,'int_loglevel','Réinitalisation des informations extraites');
  update pai_tournee set date_extrait=null where date_extrait=_date_extrait;
  call int_logrowcount_C(_idtrt,4,'int_mroad2ev_reinit','Réinitalisation des tournées');
  update pai_prd_tournee set date_extrait=null where date_extrait=_date_extrait;
  call int_logrowcount_C(_idtrt,4,'int_mroad2ev_reinit','Réinitalisation des produits');
  update pai_activite set date_extrait=null where date_extrait=_date_extrait;
  call int_logrowcount_C(_idtrt,4,'int_mroad2ev_reinit','Réinitalisation des activités');
  
  update pai_reclamation set date_extrait=null where date_extrait=_date_extrait;
  call int_logrowcount_C(_idtrt,4,'int_mroad2ev_reinit','Réinitalisation des réclamations');
  update pai_incident set date_extrait=null where date_extrait=_date_extrait;
  call int_logrowcount_C(_idtrt,4,'int_mroad2ev_reinit','Réinitalisation des incidents');
  update pai_majoration set date_extrait=null where date_extrait=_date_extrait;
  call int_logrowcount_C(_idtrt,4,'int_mroad2ev_reinit','Réinitalisation des majorations');
  
  update pai_journal set date_extrait=null where date_extrait=_date_extrait;
  call int_logrowcount_C(_idtrt,4,'int_mroad2ev_reinit','Réinitalisation du journal ');
  
  update pai_hg set date_extrait=null where date_extrait=_date_extrait;
  call int_logrowcount_C(_idtrt,4,'int_mroad2ev_reinit','Réinitalisation des heures garanties');
  update pai_hchs set date_extrait=null where date_extrait=_date_extrait;
  call int_logrowcount_C(_idtrt,4,'int_mroad2ev_reinit','Réinitalisation des HCHS');
  
  update pai_stc set date_extrait=null where date_extrait=_date_extrait;
  call int_logrowcount_C(_idtrt,4,'int_mroad2ev_reinit','Réinitalisation des STC');

  delete from pai_activite where date_extrait=_date_extrait and activite_id=-10;
  call int_logrowcount_C(_idtrt,4,'int_mroad2ev_reinit','Suppression complément heures garanties');
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 /*
 select * from employe where matricule='7000508800'
 select * from pai_stc where employe_id=8206 order by date_extrait desc
 call int_mroad2ev_reinit_employe('2016-05-10 11:08:04',8206);
 select * from pai_tournee where employe_id=8206 order by date_distrib desc
 */
DROP PROCEDURE IF EXISTS INT_MROAD2EV_ANNULE_STC;
CREATE PROCEDURE INT_MROAD2EV_ANNULE_STC(
    INOUT _idtrt		      INT,
    IN 		_utilisateur_id	INT,
    IN 	  _rcoid          varchar(36))
BEGIN
-- ATTENTION, si plusieurs STC pour le même individus à la même date, tous sont annulés !!!!
DECLARE _date_extrait		DATETIME;
DECLARE  _employe_id     INTEGER;
  SELECT date_extrait,employe_id INTO _date_extrait,_employe_id from pai_stc where rcoid=_rcoid;
  
  CALL int_logdebut(_utilisateur_id,_idtrt,'INT_MROAD2EV_ANNULE_STC',null, null, null);
  call int_logrowcount(_idtrt,4,'INT_MROAD2EV_ANNULE_STC',concat('Annulation du STC pour la RC ',_rcoid));

  update pai_journal set date_extrait=null where date_extrait=_date_extrait and employe_id=_employe_id;
  call int_logrowcount_C(_idtrt,4,'INT_MROAD2EV_ANNULE_STC','Réinitalisation du journal employé');
  update pai_journal set date_extrait=null where date_extrait=_date_extrait and produit_id in (select ppt.id from pai_tournee pt inner join pai_prd_tournee ppt on ppt.tournee_id=pt.id where pt.date_extrait=_date_extrait and pt.employe_id=_employe_id);
  call int_logrowcount_C(_idtrt,4,'INT_MROAD2EV_ANNULE_STC','Réinitalisation du journal tournée');
  update pai_journal set date_extrait=null where date_extrait=_date_extrait and tournee_id in (select id from pai_tournee where date_extrait=_date_extrait and employe_id=_employe_id);
  call int_logrowcount_C(_idtrt,4,'INT_MROAD2EV_ANNULE_STC','Réinitalisation du journal tournée');
  update pai_journal set date_extrait=null where date_extrait=_date_extrait and activite_id in (select id from pai_activite where date_extrait=_date_extrait and employe_id=_employe_id);
  call int_logrowcount_C(_idtrt,4,'INT_MROAD2EV_ANNULE_STC','Réinitalisation du journal activité');
  
  update pai_incident set date_extrait=null where date_extrait=_date_extrait and employe_id=_employe_id;
  call int_logrowcount_C(_idtrt,4,'INT_MROAD2EV_ANNULE_STC','Réinitalisation des incidents');
  update pai_reclamation set date_extrait=null where tournee_id in (select id from pai_tournee where date_extrait=_date_extrait and employe_id=_employe_id);
  call int_logrowcount_C(_idtrt,4,'INT_MROAD2EV_ANNULE_STC','Réinitalisation des réclamations');
  update pai_prd_tournee set date_extrait=null where tournee_id in (select id from pai_tournee where date_extrait=_date_extrait and employe_id=_employe_id);
  call int_logrowcount_C(_idtrt,4,'INT_MROAD2EV_ANNULE_STC','Réinitalisation des produits');
  update pai_tournee set date_extrait=null where date_extrait=_date_extrait and employe_id=_employe_id;
  call int_logrowcount_C(_idtrt,4,'INT_MROAD2EV_ANNULE_STC','Réinitalisation des tournées');
  update pai_activite set date_extrait=null where date_extrait=_date_extrait and employe_id=_employe_id;
  call int_logrowcount_C(_idtrt,4,'INT_MROAD2EV_ANNULE_STC','Réinitalisation des activités');
  update pai_majoration set date_extrait=null where date_extrait=_date_extrait and employe_id=_employe_id;
  call int_logrowcount_C(_idtrt,4,'INT_MROAD2EV_ANNULE_STC','Réinitalisation des majorations');
  
  UPDATE pai_hchs set date_extrait=null where date_extrait=_date_extrait and employe_id=_employe_id;
  call int_logrowcount_C(_idtrt,4,'INT_MROAD2EV_ANNULE_STC','Réinitalisation des HCHS');
  UPDATE pai_hg set date_extrait=null where date_extrait=_date_extrait and employe_id=_employe_id;
  call int_logrowcount_C(_idtrt,4,'INT_MROAD2EV_ANNULE_STC','Réinitalisation des HCHS');

  delete from pai_stc where date_extrait=_date_extrait and employe_id=_employe_id;
  call int_logrowcount_C(_idtrt,4,'INT_MROAD2EV_ANNULE_STC','Réinitalisation des STC');
  delete from pai_activite where date_extrait=_date_extrait and activite_id=-10 and employe_id=_employe_id;
  call int_logrowcount_C(_idtrt,4,'INT_MROAD2EV_ANNULE_STC','Suppression complément heures garanties');

  CALL int_logfin(_idtrt);
END;

-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_historise_employe;
CREATE PROCEDURE int_mroad2ev_historise_employe(
    IN 		_idtrt		  INT
) BEGIN
  INSERT INTO pai_ev_emp_depot_hst(employe_id, depot_id, flux_id, matricule, dRC, fRC, dCtr, fCtr, d, f, rc, idtrt) 
  SELECT employe_id, depot_id, flux_id, matricule, dRC, fRC, dCtr, fCtr, d, f, rc, _idtrt
  FROM pai_ev_emp_depot;
  call int_logrowcount_C(_idtrt,5,'int_mroad2ev_historise_employe','pai_ev_emp_depot_hst');
  INSERT INTO pai_ev_emp_pop_hst(employe_id, emploi_code, societe_id, matricule, dRC, fRC, dCtr, fCtr, d, f, rc, nbabo, nbrec, taux, nbrec_brut, taux_brut, nbabo_DF, nbrec_DF, taux_DF, nbrec_DF_brut, taux_DF_brut, nbrec_dif, nbrec_dif_brut, nbrec_dif_DF, nbrec_dif_DF_brut, qualite, idtrt) 
  SELECT employe_id, emploi_code, societe_id, matricule, dRC, fRC, dCtr, fCtr, d, f, rc, nbabo, nbrec, taux, nbrec_brut, taux_brut, nbabo_DF, nbrec_DF, taux_DF, nbrec_DF_brut, taux_DF_brut, nbrec_dif, nbrec_dif_brut, nbrec_dif_DF, nbrec_dif_DF_brut, qualite, _idtrt
  FROM pai_ev_emp_pop;
  call int_logrowcount_C(_idtrt,5,'int_mroad2ev_historise_employe','pai_ev_emp_pop_hst');
  INSERT INTO pai_ev_emp_pop_depot_hst(employe_id, depot_id, flux_id, population_id, typetournee_id, societe_id, matricule, dRC, fRC, dCtr, fCtr, d, f, rc, idtrt, nbabo, nbrec, taux, nbrec_brut, taux_brut, nbabo_DF, nbrec_DF, taux_DF, nbrec_DF_brut, taux_DF_brut, nbrec_dif, nbrec_dif_brut, nbrec_dif_DF, nbrec_dif_DF_brut, qualite, emploi_code) 
  SELECT employe_id, depot_id, flux_id, population_id, typetournee_id, societe_id, matricule, dRC, fRC, dCtr, fCtr, d, f, rc, _idtrt, nbabo, nbrec, taux, nbrec_brut, taux_brut, nbabo_DF, nbrec_DF, taux_DF, nbrec_DF_brut, taux_DF_brut, nbrec_dif, nbrec_dif_brut, nbrec_dif_DF, nbrec_dif_DF_brut, qualite, emploi_code
  FROM pai_ev_emp_pop_depot;
  call int_logrowcount_C(_idtrt,5,'int_mroad2ev_historise_employe','pai_ev_emp_pop_depot_hst');
END;

-- ------------------------------------------------------------------------------------------------------------------------------------------------
-- call int_mroad2ev_historise_ev(4849);
DROP PROCEDURE IF EXISTS int_mroad2ev_historise_ev;
CREATE PROCEDURE int_mroad2ev_historise_ev(
    IN 		_idtrt		  INT
) BEGIN
  INSERT INTO pai_ev_hst(typev, matricule, rc, poste, datev, ordre, qte, taux, val, libelle, res, idtrt, rcoid) 
  SELECT pe.typev,pe.matricule,pe.rc,pe.poste,pe.datev,pe.ordre,pe.qte,pe.taux,pe.val,pe.libelle,pe.res,_idtrt, epd.rcoid
  FROM pai_ev pe
  inner join employe e on pe.matricule=e.matricule
  inner join emp_pop_depot epd on e.id=epd.employe_id and pe.datev between epd.date_debut and epd.date_fin;
  call int_logrowcount_C(_idtrt,5,'int_mroad2ev_historise_ev','pai_ev_hst');
  INSERT INTO pai_ev_activite_hst(id, depot_id, flux_id, employe_id, activite_id, typejour_id, jour_id, date_distrib, heure_debut, duree, duree_nuit, duree_garantie, nbkm_paye, qte, ouverture, idtrt, transport_id) 
  SELECT id, depot_id, flux_id, employe_id, activite_id, typejour_id, jour_id, date_distrib, heure_debut, duree, duree_nuit, duree_garantie, nbkm_paye, qte, ouverture, _idtrt, transport_id
  FROM pai_ev_activite;
  call int_logrowcount_C(_idtrt,5,'int_mroad2ev_historise_ev','pai_ev_activite_hst');
  INSERT INTO pai_ev_tournee_hst(id, depot_id, flux_id, employe_id, typejour_id, jour_id, date_distrib, code, nbkm_paye, heure_debut, duree, duree_nuit, duree_tournee, duree_reperage, duree_supplement, nbcli, nbrep, valrem, valrem_corrigee, majoration, majoration_nuit, duree_nuit_modele, idtrt, transport_id) 
  SELECT id, depot_id, flux_id, employe_id, typejour_id, jour_id, date_distrib, code, nbkm_paye, heure_debut, duree, duree_nuit, duree_tournee, duree_reperage, duree_supplement, nbcli, nbrep, valrem, valrem_corrigee, majoration, majoration_nuit, duree_nuit_modele, _idtrt, transport_id
  FROM pai_ev_tournee;
  call int_logrowcount_C(_idtrt,5,'int_mroad2ev_historise_ev','pai_ev_tournee_hst');
  INSERT INTO pai_ev_produit_hst(id, tournee_id, produit_id, natureclient_id, typeurssaf_id, typeproduit_id, qte, nbcli, nbrep, duree_supplement, duree_reperage, pai_qte, pai_taux, pai_val, idtrt) 
  SELECT id, tournee_id, produit_id, natureclient_id, typeurssaf_id, typeproduit_id, qte, nbcli, nbrep, duree_supplement, duree_reperage, pai_qte, pai_taux, pai_val, _idtrt
  FROM pai_ev_produit;
  call int_logrowcount_C(_idtrt,5,'int_mroad2ev_historise_ev','pai_ev_produit_hst');
  INSERT INTO pai_ev_reclamation_hst(id, tournee_id, nbrec_abonne, nbrec_diffuseur, nbrec_abonne_brut, nbrec_diffuseur_brut, idtrt) 
  SELECT id, tournee_id, nbrec_abonne, nbrec_diffuseur, nbrec_abonne_brut, nbrec_diffuseur_brut, _idtrt
  FROM pai_ev_reclamation;
  call int_logrowcount_C(_idtrt,5,'int_mroad2ev_historise_ev','pai_ev_reclamation_hst');
END;

-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_nettoyage;
CREATE PROCEDURE int_mroad2ev_nettoyage(
    IN 		_idtrt		  INT
) BEGIN
  DELETE FROM pai_ev_diff WHERE idtrt1=_idtrt;
  DELETE FROM pai_ev_diff WHERE idtrt2=_idtrt;
  DELETE FROM pai_ev_hst WHERE idtrt=_idtrt;
  DELETE FROM pai_ev_emp_depot_hst WHERE idtrt=_idtrt;
  DELETE FROM pai_ev_emp_pop_hst WHERE idtrt=_idtrt;
  DELETE FROM pai_ev_emp_pop_depot_hst WHERE idtrt=_idtrt;
  DELETE FROM pai_ev_activite_hst WHERE idtrt=_idtrt;
  DELETE FROM pai_ev_tournee_hst WHERE idtrt=_idtrt;
  DELETE FROM pai_ev_produit_hst WHERE idtrt=_idtrt;
  DELETE FROM pai_ev_reclamation_hst WHERE idtrt=_idtrt;
  DELETE FROM pai_ev_annexe_hst WHERE idtrt=_idtrt;
  DELETE FROM pai_ev_qualite WHERE idtrt=_idtrt;
  DELETE FROM pai_int_oct_hgaranties WHERE idtrt=_idtrt;
  DELETE FROM pai_int_log WHERE idtrt=_idtrt;
  DELETE FROM pai_int_erreur WHERE idtrt=_idtrt;
  DELETE FROM pai_int_traitement WHERE id=_idtrt;
END;

-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_nettoyage_historique;
CREATE PROCEDURE `int_mroad2ev_nettoyage_historique`(
    IN 		_flux_id		    INT
) BEGIN
  DECLARE v_finished INTEGER DEFAULT 0;
  DECLARE _id INT;
 
  DECLARE _cursor CURSOR FOR
    SELECT pit.id
    FROM pai_int_traitement pit
    inner join pai_mois pm on pit.anneemois<pm.anneemois and pit.flux_id=pm.flux_id
    where (typetrt in ('GENERE_PLEIADES_MENSUEL','MROAD2PNG_EV_MENSUEL','CALCUL_PLEIADES_MENSUEL','MROAD2PNG_EV_QUOTIDIEN'))
    and pit.flux_id=_flux_id
  union
    SELECT pit.id
    FROM pai_int_traitement pit
    WHERE pit.typetrt in ('MROAD2PNG_EV_HISTORIQUE','INT_MROAD2EV_MENSUEL_PARTIEL') OR typetrt like '%EV_TEST%'
    and pit.flux_id=_flux_id
  ;
   
  -- declare NOT FOUND handler
  DECLARE CONTINUE HANDLER
  FOR NOT FOUND SET v_finished = 1;

  OPEN _cursor;
  _loop: LOOP
    FETCH _cursor INTO _id;
    IF v_finished = 1 THEN
      LEAVE _loop;
    END IF;
    call int_mroad2ev_nettoyage(_id);
  END LOOP _loop;
  CLOSE _cursor;
  
  delete from pai_int_log where idtrt=1;
END;

-- ------------------------------------------------------------------------------------------------------------------------------------------------
 -- -----------------------------------------------------------------------------------------------------------------------------------------------
 /*
 select * from pai_int_traitement where typetrt like '%CLOTURE%' order by id desc
call int_mroad2ev_diff(5059);
select ligne from pai_int_ev_diff_NG where idtrt=5059 order by ordre
 */
DROP PROCEDURE IF EXISTS int_mroad2ev_diff;
CREATE PROCEDURE int_mroad2ev_diff(
    IN 		_idtrt		INT
) BEGIN
  DELETE FROM pai_ev_diff where idtrt1=_idtrt and  idtrt2=_idtrt;
  
  -- Supprime les ev des rc extraites
  
  delete e
  from pai_png_ev_factoryw e
  inner join pai_png_relationcontrat rc on e.relationcontrat=rc.oid and e.dateeffet between rc.relatdatedeb and rc.relatdatefinw
  inner join pai_stc s on e.relationcontrat=s.rcoid and s.date_extrait is not null;
  call int_logrowcount_C(_idtrt,5,'int_mroad2ev_diff','stc');

  INSERT INTO pai_ev_diff(idtrt1,idtrt2,diff,typev,matricule,poste,datev,ordre,libelle,rc,qte1,qte2,taux1,taux2,val1,val2)
  select _idtrt,_idtrt,'S','NG',s1.matricule,e1.poste,e1.dateeffet,e1.noordre,'NG',rc1.relatnum,e1.nb,null,e1.tx,null,e1.mtt,null 
  from pai_png_ev_factoryw e1
  inner join pai_png_relationcontrat rc1 on e1.relationcontrat=rc1.oid
  inner join pai_png_salarie s1 on rc1.relatmatricule=s1.oid
  where not exists(select null 
                  from pai_ev_hst e2 
                  where e2.idtrt=_idtrt and e1.relationcontrat=e2.rcoid and e1.dateeffet=e2.datev and e1.poste=e2.poste and coalesce(e1.noordre,0)=coalesce(e2.ordre,0)
                  )
  ;
  call int_logrowcount_C(_idtrt,5,'int_mroad2ev_diff','S');
  INSERT INTO pai_ev_diff(idtrt1,idtrt2,diff,typev,matricule,poste,datev,ordre,libelle,rc,qte1,qte2,taux1,taux2,val1,val2)
  select _idtrt,_idtrt,'C',e2.typev,left(e2.matricule,8),e2.poste,e2.datev,e2.ordre,e2.libelle,e2.rc,null,e2.qte,null,e2.taux,null,e2.val 
  from pai_ev_hst e2 
  where not exists(select null 
                  from pai_png_ev_factoryw e1 
                  where e1.relationcontrat=e2.rcoid and e1.dateeffet=e2.datev and e1.poste=e2.poste /*and e1.idtrt=_idtrt*/ and coalesce(e1.noordre,0)=coalesce(e2.ordre,0)) 
  and e2.idtrt=_idtrt
  ;
  call int_logrowcount_C(_idtrt,5,'int_mroad2ev_diff','C');
  INSERT INTO pai_ev_diff(idtrt1,idtrt2,diff,typev,matricule,poste,datev,ordre,libelle,rc,qte1,qte2,taux1,taux2,val1,val2)
  select _idtrt,_idtrt,'M',e2.typev,left(e2.matricule,8),e2.poste,e2.datev,e2.ordre,e2.libelle,e2.rc,e1.nb,e2.qte,e1.tx,e2.taux,e1.mtt,e2.val 
  from pai_png_ev_factoryw e1 
  inner join pai_ev_hst e2 on e2.idtrt=_idtrt and e1.relationcontrat=e2.rcoid and e1.poste=e2.poste and e1.dateeffet=e2.datev and coalesce(e1.noordre,0)=coalesce(e2.ordre,0)
  inner join pai_ref_postepaie_general prpg on e2.poste=prpg.poste
  where (e1.nb<>e2.qte or coalesce(e1.tx,0)<>if(prpg.taux,e2.taux,0) or coalesce(e1.mtt,0)<>if(prpg.montant,e2.val,0)) 
  ;
  call int_logrowcount_C(_idtrt,5,'int_mroad2ev_diff','M');
/*  INSERT INTO pai_ev_diff(idtrt1,idtrt2,diff,typev,matricule,poste,datev,ordre,libelle,rc,qte1,qte2,taux1,taux2,val1,val2)
  select _idtrt,_idtrt,'=',e2.typev,left(e2.matricule,8),e2.poste,e2.datev,e2.ordre,e2.libelle,e2.rc,e1.nb,e2.qte,e1.tx,e2.taux,e1.mtt,e2.val 
  from pai_png_ev_factoryw e1 
  inner join pai_ev_hst e2 on e2.idtrt=_idtrt and e1.relationcontrat=e2.rcoid and e1.poste=e2.poste and e1.dateeffet=e2.datev and coalesce(e1.noordre,0)=coalesce(e2.ordre,0)
  inner join pai_ref_postepaie_general prpg on e2.poste=prpg.poste
  where e1.nb=e2.qte and coalesce(e1.tx,0)=if(prpg.taux,e2.taux,0) and coalesce(e1.mtt,0)=if(prpg.montant,e2.val,0)
  ;
  call int_logrowcount_C(_idtrt,5,'int_mroad2ev_diff','=');
  */
  INSERT INTO pai_int_log(idtrt,date_log,level,module,msg)
  SELECT _idtrt,now(),1,'int_mroad2ev_diff',
  CONCAT(
    ped.poste, ' : '
    ,case when ped.diff='C' then 'Création' when ped.diff='M' then 'Modification' when ped.diff='S' then 'Suppression' end
    ,' de ',count(*),' ev'
    ,' (',coalesce(prpg.libelle,'Activité'),')'
    )
  FROM pai_ev_diff ped
  left outer join pai_ref_postepaie_general prpg on ped.poste=prpg.poste
  where idtrt1=idtrt2
  and idtrt1=_idtrt
  and ped.diff<>'='
  group by ped.poste,ped.diff
  order by ped.poste desc,ped.diff desc
  ;  
  INSERT INTO pai_int_log(idtrt,date_log,level,module,msg)
  SELECT _idtrt,now(),1,'int_mroad2ev_diff',
  CONCAT(
    case when ped.diff='C' then 'Création' when ped.diff='M' then 'Modification' when ped.diff='S' then 'Suppression' end
    ,' de ',count(*),' ev'
    )
  FROM pai_ev_diff ped
  where idtrt1=idtrt2
  and idtrt1=_idtrt
  and ped.diff<>'='
  group by ped.diff
  order by ped.diff desc
  ;  
  INSERT INTO pai_int_log(idtrt,date_log,level,module,msg)
  SELECT _idtrt,now(),1,'int_mroad2ev_diff',CONCAT(count(distinct ped.matricule),' salariés interfacés')
  FROM pai_ev_diff ped
  where idtrt1=idtrt2
  and idtrt1=_idtrt
  and ped.diff<>'='
  ;
  --   select d.* FROM pai_ev_diff d WHERE idtrt1=3206 and idtrt2=3206 and d.diff<>'=' ORDER BY d.matricule,d.datev,d.poste;
  -- delete d from pai_ev_diff d where idtrt1=_idtrt and  idtrt2=_idtrt and poste='DECLPRIQUA'; Fait dans PNG
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 /*
 select * from pai_int_traitement order by id desc
call int_mroad2ev_history_diff(5129,5059);

select d.*,e.nom,e.prenom1 FROM pai_ev_diff d inner join employe e on d.matricule=e.matricule WHERE d.diff<>'=' and idtrt1=4490 and idtrt2=4487 ORDER BY e.nom,d.matricule,d.datev,d.poste;
select d.*,e.nom,e.prenom1 FROM pai_ev_diff d inner join employe e on d.matricule=e.matricule WHERE d.diff='=' and idtrt1=4490 and idtrt2=4487 ORDER BY e.nom,d.matricule,d.datev,d.poste;
select d.*,e.nom,e.prenom1 FROM pai_ev_diff d inner join employe e on d.matricule=e.matricule WHERE d.matricule='MET0220320' and idtrt1=4491 and idtrt2=4487 ORDER BY e.nom,d.matricule,d.datev,d.poste;
select d.*,e.nom,e.prenom1 FROM pai_ev_diff d inner join employe e on d.matricule=e.matricule WHERE d.matricule='7000140120' and idtrt1=4491 and idtrt2=4487 ORDER BY e.nom,d.matricule,d.datev,d.poste;
select d.* FROM pai_ev_diff d WHERE d.diff<>'=' and idtrt1=5059 and idtrt2=5130 ORDER BY d.matricule,d.datev,d.poste;
select * from pai_ev_emp_pop_hst where idtrt=4477 and qualite='I'

select ligne from pai_int_ev_diff_history where idtrt1=4722 and idtrt2=5173 and ligne like '%|0105      |%'
*/
DROP PROCEDURE IF EXISTS int_mroad2ev_history_diff;
CREATE PROCEDURE int_mroad2ev_history_diff(
    IN 		_idtrt1		INT,
    IN 		_idtrt2		INT
) BEGIN
  DELETE FROM pai_ev_diff where idtrt1=_idtrt1 and  idtrt2=_idtrt2;
  
  INSERT INTO pai_ev_diff(idtrt1,idtrt2,diff,typev,matricule,poste,datev,ordre,libelle,rc,qte1,qte2,taux1,taux2,val1,val2)
  select _idtrt1,_idtrt2,'S',e1.typev,e1.matricule,e1.poste,e1.datev,e1.ordre,e1.libelle,e1.rc,e1.qte,null,e1.taux,null,e1.val,null from pai_ev_hst e1 where not exists(select null from pai_ev_hst e2 where e1.rcoid=e2.rcoid and e1.datev=e2.datev and e1.poste=e2.poste and e2.idtrt=_idtrt2 and coalesce(e1.ordre,0)=coalesce(e2.ordre,0)) and e1.idtrt=_idtrt1
  union
  select _idtrt1,_idtrt2,'C',e2.typev,e2.matricule,e2.poste,e2.datev,e2.ordre,e2.libelle,e2.rc,null,e2.qte,null,e2.taux,null,e2.val from pai_ev_hst e2 where not exists(select null from pai_ev_hst e1 where e1.rcoid=e2.rcoid and e1.datev=e2.datev and e1.poste=e2.poste and e1.idtrt=_idtrt1 and coalesce(e1.ordre,0)=coalesce(e2.ordre,0)) and e2.idtrt=_idtrt2
  union
  select _idtrt1,_idtrt2,'M',e1.typev,e1.matricule,e1.poste,e1.datev,e1.ordre,e1.libelle,e1.rc,e1.qte,e2.qte,e1.taux,e2.taux,e1.val,e2.val from pai_ev_hst e1, pai_ev_hst e2 where e1.rcoid=e2.rcoid and e1.poste=e2.poste and e1.datev=e2.datev and coalesce(e1.ordre,0)=coalesce(e2.ordre,0) and (e1.qte<>e2.qte or e1.taux<>e2.taux or e1.val<>e2.val) and e1.idtrt=_idtrt1 and e2.idtrt=_idtrt2
  union
  select _idtrt1,_idtrt2,'=',e1.typev,e1.matricule,e1.poste,e1.datev,e1.ordre,e1.libelle,e1.rc,e1.qte,e2.qte,e1.taux,e2.taux,e1.val,e2.val from pai_ev_hst e1, pai_ev_hst e2 where e1.rcoid=e2.rcoid and e1.poste=e2.poste and e1.datev=e2.datev and coalesce(e1.ordre,0)=coalesce(e2.ordre,0) and e1.qte=e2.qte and e1.taux=e2.taux and e1.val=e2.val and e1.idtrt=_idtrt1 and e2.idtrt=_idtrt2
  order by 3,2,4,1;
  call int_logrowcount_C(_idtrt2,4,'int_mroad2ev_history_diff','Differentiel NG');
  
  -- select * from pai_int_traitement order by id desc
  -- call int_mroad2ev_history_diff(3253,3256)
  -- select * from pai_ev where matricule='Z004687200'
  -- select * from pai_ev_hst where matricule='MET0197520' and idtrt=3253
  -- select * from pai_ev_hst where matricule='MET0197520' and idtrt=3256
--  select * from employe where matricule='NEP0014000'
--  select * from v_employe where matricule='MET0197520'
  -- select * from pai_ev_tournee_hst where employe_id=7680 and idtrt=3253
  -- select * from pai_ev_tournee_hst where employe_id=7680 and idtrt=3256
  -- select * from pai_ev_emp_pop_depot_hst where employe_id=7680 and idtrt=3253
  -- select * from pai_ev_emp_pop_depot_hst where employe_id=7680 and idtrt=3256
  -- select * from pai_tournee where employe_id=7680 order by date_distrib desc

END;

DROP PROCEDURE IF EXISTS int_mroad2oct_diff;
CREATE PROCEDURE int_mroad2oct_diff(
    IN 		_idtrt1		INT,
    IN 		_idtrt2		INT
) BEGIN
select '=' as dif,h1.employe_id,h1.date_distrib,h1.hgaranties,h2.hgaranties,h1.hdelegation,h2.hdelegation,h1.hhorspresse,h2.hhorspresse from pai_int_oct_hgaranties h1,pai_int_oct_hgaranties h2 where h1.idtrt=_idtrt1 and h2.idtrt=_idtrt2 and h1.employe_id=h2.employe_id and h1.date_distrib=h2.date_distrib and h1.hgaranties=h2.hgaranties and h1.hdelegation=h2.hdelegation and h1.hhorspresse=h2.hhorspresse
union
select '<>',h1.employe_id,h1.date_distrib,h1.hgaranties,h2.hgaranties,h1.hdelegation,h2.hdelegation,h1.hhorspresse,h2.hhorspresse from pai_int_oct_hgaranties h1,pai_int_oct_hgaranties h2 where h1.idtrt=_idtrt1 and h2.idtrt=_idtrt2 and h1.employe_id=h2.employe_id and h1.date_distrib=h2.date_distrib and (h1.hgaranties<>h2.hgaranties or h1.hdelegation<>h2.hdelegation or h1.hhorspresse<>h2.hhorspresse)
union
select '-',h1.employe_id,h1.date_distrib,h1.hgaranties,h2.hgaranties,h1.hdelegation,h2.hdelegation,h1.hhorspresse,h2.hhorspresse from pai_int_oct_hgaranties h1 left outer join pai_int_oct_hgaranties h2 on  h2.idtrt=_idtrt2 and h1.employe_id=h2.employe_id and h1.date_distrib=h2.date_distrib where h1.idtrt=_idtrt1 and h2.employe_id is null
union
select '+',h2.employe_id,h2.date_distrib,h1.hgaranties,h2.hgaranties,h1.hdelegation,h2.hdelegation,h1.hhorspresse,h2.hhorspresse from pai_int_oct_hgaranties h2 left outer join pai_int_oct_hgaranties h1 on  h1.idtrt=_idtrt1 and h1.employe_id=h2.employe_id and h1.date_distrib=h2.date_distrib where h2.idtrt=_idtrt2 and h1.employe_id is null
;
END;

/*
-- Differentiel sur les heures garanties envoyées à Octime
select '=' as dif,h1.employe_id,h1.date_distrib,h1.hgaranties,h2.hgaranties,h1.hdelegation,h2.hdelegation,h1.hhorspresse,h2.hhorspresse from pai_int_oct_hgaranties h1,pai_int_oct_hgaranties h2 where h1.idtrt=4631 and h2.idtrt=4633 and h1.employe_id=h2.employe_id and h1.date_distrib=h2.date_distrib and h1.hgaranties=h2.hgaranties and h1.hdelegation=h2.hdelegation and h1.hhorspresse=h2.hhorspresse
union
select '<>',h1.employe_id,h1.date_distrib,h1.hgaranties,h2.hgaranties,h1.hdelegation,h2.hdelegation,h1.hhorspresse,h2.hhorspresse from pai_int_oct_hgaranties h1,pai_int_oct_hgaranties h2 where h1.idtrt=4631 and h2.idtrt=4633 and h1.employe_id=h2.employe_id and h1.date_distrib=h2.date_distrib and (h1.hgaranties<>h2.hgaranties or h1.hdelegation<>h2.hdelegation or h1.hhorspresse<>h2.hhorspresse)
union
select '-',h1.employe_id,h1.date_distrib,h1.hgaranties,h2.hgaranties,h1.hdelegation,h2.hdelegation,h1.hhorspresse,h2.hhorspresse from pai_int_oct_hgaranties h1 left outer join pai_int_oct_hgaranties h2 on  h2.idtrt=4633 and h1.employe_id=h2.employe_id and h1.date_distrib=h2.date_distrib where h1.idtrt=4631 and h2.employe_id is null
union
select '+',h2.employe_id,h2.date_distrib,h1.hgaranties,h2.hgaranties,h1.hdelegation,h2.hdelegation,h1.hhorspresse,h2.hhorspresse from pai_int_oct_hgaranties h2 left outer join pai_int_oct_hgaranties h1 on  h1.idtrt=4631 and h1.employe_id=h2.employe_id and h1.date_distrib=h2.date_distrib where h2.idtrt=4633 and h1.employe_id is null

*/