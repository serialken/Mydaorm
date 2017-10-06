/*
call int_mroad2ev_recalcul_annexe('201610');

select anneemois, flux_id,max(pit2.id) from pai_int_traitement pit2 where (pit2.typetrt like '%_PLEIADES_MENSUEL' or pit2.typetrt like '%_PLEIADES_CLOTURE') and pit2.statut='T' group by anneemois,flux_id
select * from pai_int_traitement where anneemois is not null order by id desc;

select * from pai_ev_annexe_hst where idtrt=1665
call int_mroad2ev_annexe(4598);
call int_mroad2ev_annexe(3176);
select * from pai_ev_annexe_hst where idtrt=2063 and employe_id=769 order by date_distrib,libelle;
select * from pai_ev_annexe_hst where idtrt=2063 and employe_id=6181 and depot_id=10 order by date_distrib,libelle;
select * from pai_ev_annexe_hst where idtrt=2063 and employe_id=1564 order by date_distrib,libelle;
select * from pai_ev_activite_hst where typejour_id<>1 and  idtrt=2063

select * from pai_int_ev_annexe_resume where anneemois='201503' and depot_id=10 and flux_id=1 and employe_id=5007 order by datev,poste;
select * from employe where nom='BRIK'
select * from employe where nom='ALOUACHE'

select max(pit2.id) from pai_int_traitement pit2 where pit2.anneemois='201504' and pit2.flux_id=1 and pit2.typetrt='GENERE_PLEIADES_MENSUEL' and pit2.statut='T'
select * from pai_ev_annexe_hst where idtrt=2275 order by employe_id,date_distrib,libelle;

select max(pit2.id) from pai_int_traitement pit2 where pit2.anneemois='201504' and pit2.flux_id=2 and pit2.typetrt='GENERE_PLEIADES_MENSUEL' and pit2.statut='T'
select * from pai_ev_annexe_hst where idtrt=2274 and employe_id=7634 order by date_distrib,libelle;

SELECT DISTINCT
                CONCAT(' ',ped.id) as id,
                CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2,'(',ped.rc, date_format(ped.d,'%d/%m/%Y'),' - ',date_format(ped.f,'%d/%m/%Y'),')') libelle,
                ped.rc,
                ped.d as date_debut,
                ped.f as date_fin
                from pai_int_traitement pit
                inner join pai_ev_emp_depot_hst ped on pit.id=ped.idtrt
                inner join pai_ev_annexe_hst a on pit.id=a.idtrt and ped.id=a.employe_depot_hst_id and a.date_distrib between ped.d and ped.f
                inner join employe e on ped.employe_id=e.id
                where pit.id in (select max(pit2.id) from pai_int_traitement pit2 where pit2.anneemois='201503' and pit2.flux_id=1 and (pit2.typetrt='GENERE_PLEIADES_MENSUEL' or pit2.typetrt='GENERE_PLEIADES_CLOTURE') and pit2.statut='T')
                and ped.depot_id=10
               ORDER BY 2

*/
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_annexe;
CREATE PROCEDURE int_mroad2ev_annexe(
    IN 	  _idtrt		INT)
BEGIN
  delete from pai_ev_annexe_hst where idtrt=_idtrt;
  call int_logrowcount_C(_idtrt,5,'int_mroad2ev_annexe', 'lignes supprimées')
  ;
  
  insert into pai_ev_annexe_hst(idtrt, employe_depot_hst_id,
    date_distrib, libelle,
    qte, taux, val,
    duree_tournee, duree_activite, duree_autre, duree_nuit, 
    nbkm_paye, nbrec_abonne, nbrec_diffuseur, nb_incident)
  SELECT
        _idtrt,
        pe.id,
        pt.date_distrib,
        pt.code,
        pt.nbcli qte, -- NULLIF(sum(ppt.nbrep),0) nbrep,
        pt.valrem_corrigee as taux,
        pt.nbcli*pt.valrem_corrigee as mnt,
        pt.duree_tournee,
        null as duree_activite,
        null as duree_autre,
        pt.duree_nuit, 
        NULLIF(pt.nbkm_paye,0) as nbkm_paye, 
        NULLIF((SELECT sum(pr.nbrec_abonne) FROM pai_ev_reclamation_hst pr WHERE pr.idtrt=pt.idtrt and  pr.tournee_id = pt.id GROUP BY pr.tournee_id),0) as nbrec_abonne,
        NULLIF((SELECT sum(pr.nbrec_diffuseur) FROM pai_ev_reclamation_hst pr WHERE pr.idtrt=pt.idtrt and  pr.tournee_id = pt.id GROUP BY pr.tournee_id),0) as nbrec_diffuseur,
--        NULLIF((SELECT sum(pr.nbrec_abonne)+sum(pr.nbrec_diffuseur) FROM pai_ev_reclamation_hst pr WHERE pr.idtrt=pt.idtrt and  pr.tournee_id = pt.id GROUP BY pr.tournee_id),'') as reclamation,
        null
    FROM pai_ev_emp_depot_hst pe 
    INNER JOIN pai_ev_tournee_hst pt on pt.idtrt=pe.idtrt and pt.employe_id=pe.employe_id and pt.date_distrib between pe.d and pe.f
    WHERE  pe.idtrt=_idtrt and (pe.d not like '%-05-01' or pe.f not like '%-05-01')
    GROUP BY pe.id,pt.id
    HAVING pt.nbcli<>0
  ;    
  call int_logrowcount_C(_idtrt,5,'int_mroad2ev_annexe', 'Tournées insérées')
  ;
  insert into pai_ev_annexe_hst(idtrt, employe_depot_hst_id,
    date_distrib, libelle,
    qte, taux, val,
    duree_tournee, duree_activite, duree_autre, duree_nuit, 
    nbkm_paye, nbrec_abonne, nbrec_diffuseur, nb_incident)
  SELECT
        _idtrt,
        pe.id,
        pt.date_distrib,
        case when prd.type_id in (1) then 'Majoration poids' when prd.type_id in (2,3) then 'Supplément' else t.libelle end,
        sum(ppt.pai_qte) as qte,
        ppt.pai_taux as taux,
        SUM(ppt.pai_qte)*ppt.pai_taux as val,
        null as duree_tournee,
        null as duree_activite,
        sec_to_time(sum(time_to_sec(ppt.duree_supplement))) as duree_autre,
        null as duree_nuit, 
        null as nbkm_paye,    
        null as nbrec_abonne,
        null as nbrec_diffuseur,
        null as nb_incident
    FROM pai_ev_emp_depot_hst pe
    INNER JOIN pai_ev_tournee_hst pt on pt.idtrt=pe.idtrt and pt.employe_id=pe.employe_id and pt.date_distrib between pe.d and pe.f
    INNER JOIN pai_ev_produit_hst ppt  ON ppt.idtrt=pt.idtrt and  ppt.tournee_id = pt.id
    INNER JOIN produit prd  ON ppt.produit_id  = prd.id
    INNER JOIN produit_type t on prd.type_id=t.id
    WHERE  pe.idtrt=_idtrt and (pe.d not like '%-05-01' or pe.f not like '%-05-01')
    AND ppt.pai_taux is not null
    GROUP BY  pe.id,pt.date_distrib,ppt.pai_taux,case when prd.type_id in (1) then 'Majoration poids' when prd.type_id in (2,3) then 'Supplément' else t.libelle end
    HAVING sum(ppt.pai_qte)<>0
  ;    
  call int_logrowcount_C(_idtrt,5,'int_mroad2ev_annexe', 'Suppléments insérés')
  ;
  insert into pai_ev_annexe_hst(idtrt, employe_depot_hst_id,
    date_distrib, libelle,
    qte, taux, val,
    duree_tournee, duree_activite, duree_autre, duree_nuit, 
    nbkm_paye, nbrec_abonne, nbrec_diffuseur, nb_incident)
  SELECT
        _idtrt,
        pe.id,
        pt.date_distrib,
        'Repérage',
        SUM(ppt.nbrep) qte,
        pt.valrem_corrigee as taux,
        SUM(ppt.nbrep)*pt.valrem_corrigee as mnt,
        null as duree_tournee,
        null as duree_activite,
        pai_duree_reperage(pt.tournee_org_id,pt.split_id,epd.typetournee_id,pt.valrem_corrigee,rp.majoration,r.valeur,SUM(ppt.nbrep)) as duree_autre,
        null as duree_nuit, 
        null as nbkm_paye,    
        null as nbrec_abonne,
        null as nbrec_diffuseur,
        null as nb_incident
    FROM pai_ev_emp_depot_hst pe 
    INNER JOIN pai_ev_tournee_hst pt on pt.idtrt=pe.idtrt and pt.employe_id=pe.employe_id and pt.date_distrib between pe.d and pe.f
    INNER JOIN pai_ev_produit_hst ppt  ON ppt.idtrt=pt.idtrt and  ppt.tournee_id = pt.id
    INNER JOIN produit prd  ON ppt.produit_id  = prd.id AND prd.type_id=1
  	LEFT OUTER JOIN emp_pop_depot epd ON epd.employe_id=pt.employe_id AND pt.date_distrib BETWEEN epd.date_debut AND epd.date_fin
  	LEFT OUTER JOIN ref_population rp ON epd.population_id=rp.id
  	LEFT OUTER JOIN pai_ref_remuneration r ON coalesce(epd.societe_id,1)=r.societe_id AND coalesce(epd.population_id,1)=r.population_id AND pt.date_distrib BETWEEN r.date_debut AND r.date_fin
    WHERE  pe.idtrt=_idtrt and (pe.d not like '%-05-01' or pe.f not like '%-05-01')
    GROUP BY pe.id,pt.id,epd.id,rp.id,r.id
    HAVING SUM(ppt.nbrep)<>0
  ;    
  call int_logrowcount_C(_idtrt,5,'int_mroad2ev_annexe', 'Repérage insérés')
  ;
  insert into pai_ev_annexe_hst(idtrt, employe_depot_hst_id,
    date_distrib, libelle,
    qte, taux, val,
    duree_tournee, duree_activite, duree_autre, duree_nuit, 
    nbkm_paye, nbrec_abonne, nbrec_diffuseur, nb_incident)
  SELECT
        _idtrt,
        pe.id,
        pt.date_distrib,
        'Repérage FR',
        SUM(ppt.nbrep) qte,
        2*ppt.pai_taux as taux,
        SUM(ppt.nbrep)*2*ppt.pai_taux as mnt,
        null as duree_tournee,
        null as duree_activite,
        sec_to_time(sum(time_to_sec(ppt.duree_reperage))) as duree_autre,
        null as duree_nuit, 
        null as nbkm_paye,    
        null as nbrec_abonne,
        null as nbrec_diffuseur,
        null as nb_incident
    FROM pai_ev_emp_depot_hst pe 
    INNER JOIN pai_ev_tournee_hst pt on pt.idtrt=pe.idtrt and pt.employe_id=pe.employe_id and pt.date_distrib between pe.d and pe.f
    INNER JOIN pai_ev_produit_hst ppt  ON ppt.idtrt=pt.idtrt and  ppt.tournee_id = pt.id
    INNER JOIN produit prd  ON ppt.produit_id  = prd.id AND prd.type_id not in (1,2,3) 
    INNER JOIN produit_type t on prd.type_id=t.id and not t.est_horspresse
    WHERE  pe.idtrt=_idtrt and (pe.d not like '%-05-01' or pe.f not like '%-05-01')
    GROUP BY pe.id,pt.id,ppt.pai_taux
    HAVING SUM(ppt.nbrep)<>0
  ;    
  call int_logrowcount_C(_idtrt,5,'int_mroad2ev_annexe', 'Repérage FR insérés')
  ;
  insert into pai_ev_annexe_hst(idtrt, employe_depot_hst_id,
    date_distrib, libelle,
    qte, taux, val,
    duree_tournee, duree_activite, duree_autre, duree_nuit, 
    nbkm_paye, nbrec_abonne, nbrec_diffuseur, nb_incident)
    SELECT
        _idtrt,
        pe.id,
        pa.date_distrib,
        ra.libelle,
        time_to_sec(pa.duree)/3600 as qte,
        if(ra.est_hors_presse,if(coalesce(ech.travhorspresse,'0')='1',prr.valeurHP2,prr.valeurHP),prr.valeur)*prpg.majoration/100 as taux,
        time_to_sec(pa.duree)/3600*if(ra.est_hors_presse,if(coalesce(ech.travhorspresse,'0')='1',prr.valeurHP2,prr.valeurHP),prr.valeur)*prpg.majoration/100 as mnt,
        null as duree_tournee, 
        pa.duree as duree_activite,
        null as duree_autre, 
        pa.duree_nuit, 
        NULLIF(pa.nbkm_paye,0) as nbkm_paye,    
        null as nbrec_abonne,
        null as nbrec_diffuseur,
        null as nb_incident
    FROM pai_ev_emp_depot_hst pe
    INNER JOIN pai_ev_activite_hst pa on pa.idtrt=pe.idtrt and pa.employe_id=pe.employe_id and pa.date_distrib between pe.d and pe.f
    INNER JOIN ref_activite ra on pa.activite_id=ra.id
    INNER JOIN emp_pop_depot epd on pa.employe_id=epd.employe_id and pa.date_distrib between epd.date_debut and epd.date_fin
    LEFT OUTER JOIN emp_contrat_hp ech on pa.employe_id=ech.employe_id and pa.date_distrib between ech.date_debut and ech.date_fin and pa.activite_id=ech.activite_id
    INNER JOIN pai_ref_remuneration prr on epd.societe_id=prr.societe_id and epd.population_id=prr.population_id and pa.date_distrib between prr.date_debut and prr.date_fin
    INNER JOIN pai_ref_postepaie_activite p ON pa.activite_id=p.activite_id AND pa.typejour_id= p.typejour_id
    INNER JOIN pai_ref_postepaie_general prpg on p.poste_hj=prpg.poste
    WHERE  pe.idtrt=_idtrt  and (pe.d not like '%-05-01' or pe.f not like '%-05-01')
    GROUP BY  pe.id,pa.id
    HAVING pa.duree<>'00:00:00'
  ;    
  call int_logrowcount_C(_idtrt,5,'int_mroad2ev_annexe', 'Activités insérées')
  ;
  insert into pai_ev_annexe_hst(idtrt, employe_depot_hst_id,
    date_distrib, libelle,
    qte, taux, val,
    duree_tournee, duree_activite, duree_autre, duree_nuit, 
    nbkm_paye, nbrec_abonne, nbrec_diffuseur, nb_incident)
  SELECT 
        _idtrt,
        pe.id,
        pt.date_distrib,
        pt.code,
        null as qte,
        null as taux,
        null as mnt,
        null as duree_tournee, 
        null as duree_activite, 
        null as duree_autre, 
        null as duree_nuit, 
        null as nbkm_paye,    
        sum(pr.nbrec_abonne) as nbrec_abonne,
        sum(pr.nbrec_diffuseur) as nbrec_diffuseur,
        null as nb_incident
    FROM pai_ev_reclamation_hst pr
    INNER JOIN pai_tournee pt ON pr.tournee_id=pt.id
    INNER JOIN pai_ev_emp_depot_hst pe on pr.idtrt=pe.idtrt and pt.employe_id=pe.employe_id and (pe.d not like '%-05-01' or pe.f not like '%-05-01') -- and pt.date_distrib between pe.d and pe.f
    INNER JOIN pai_int_traitement pit on pit.id=_idtrt
    INNER JOIN pai_ref_mois prm on pit.anneemois=prm.anneemois
    WHERE  pr.idtrt=_idtrt
    AND pt.date_distrib < prm.date_debut  
    GROUP BY  pe.id,pt.date_distrib 
    HAVING sum(pr.nbrec_abonne)<>0 or sum(pr.nbrec_diffuseur)<>0
  ;    
  call int_logrowcount_C(_idtrt,5,'int_mroad2ev_annexe', 'Réclamations insérées')
  ;
  insert into pai_ev_annexe_hst(idtrt, employe_depot_hst_id,
    date_distrib, libelle,
    qte, taux, val,
    duree_tournee, duree_activite, duree_autre, duree_nuit, 
    nbkm_paye, nbrec_abonne, nbrec_diffuseur, nb_incident)
    SELECT
        _idtrt,
        pe.id,
        pi.date_distrib,
        ri.libelle,
        null as qte,
        null as taux,
        null as mnt,
        null as duree_tournee, 
        null as duree_activite, 
        null as duree_autre, 
        null as duree_nuit, 
        null as nbkm_paye,    
        null as nbrec_abonne,
        null as nbrec_diffuseur,
        1
    FROM pai_ev_emp_depot_hst pe
    INNER JOIN pai_incident pi on pi.employe_id=pe.employe_id and pi.date_distrib between pe.d and pe.f
    INNER JOIN ref_incident ri on pi.incident_id=ri.id
    WHERE  pe.idtrt=_idtrt  and (pe.d not like '%-05-01' or pe.f not like '%-05-01')
  ;    
  call int_logrowcount_C(_idtrt,5,'int_mroad2ev_annexe', 'Incidents insérées')
  ;
  insert into pai_ev_annexe_hst(idtrt, employe_depot_hst_id,
    date_distrib, libelle,
    qte, taux, val,
    duree_tournee, duree_activite, duree_autre, duree_nuit, 
    nbkm_paye, nbrec_abonne, nbrec_diffuseur, nb_incident)
  SELECT
    idtrt, employe_depot_hst_id, '2999-01-01', 'Total',
    null, null, coalesce(sum(val),0),
    coalesce(sec_to_time(sum(time_to_sec(duree_tournee))),'00:00:00'), coalesce(sec_to_time(sum(time_to_sec(duree_activite))),'00:00:00'), coalesce(sec_to_time(sum(time_to_sec(duree_autre))),'00:00:00'), coalesce(sec_to_time(sum(time_to_sec(duree_nuit))),'00:00:00'), 
    coalesce(sum(nbkm_paye),0), coalesce(sum(nbrec_abonne),0), coalesce(sum(nbrec_diffuseur),0), coalesce(sum(nb_incident),0)
  from pai_ev_annexe_hst
  where idtrt=_idtrt
  group by idtrt, employe_depot_hst_id;
  call int_logrowcount_C(_idtrt,5,'int_mroad2ev_annexe', 'Totaux insérés')
  ;
  
  update pai_ev_annexe_hst
  set duree_totale=addtime(coalesce(duree_tournee,'00:00:00'),addtime(coalesce(duree_activite,'00:00:00'),coalesce(duree_autre,'00:00:00')))
  where idtrt=_idtrt;
  call int_logrowcount_C(_idtrt,5,'int_mroad2ev_annexe', 'Maj durée totale')
  ;
end;

-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_recalcul_annexe;
CREATE PROCEDURE `int_mroad2ev_recalcul_annexe`(
_anneemois varchar(6)
) BEGIN
  DECLARE v_finished INTEGER DEFAULT 0;
  DECLARE _id INT;
 
  -- declare cursor for employee email
  DEClARE _cursor CURSOR FOR
  SELECT pit.id
  FROM pai_int_traitement pit
  where typetrt in ('GENERE_PLEIADES_CLOTURE','GENERE_PLEIADES_MENSUEL','CALCUL_PLEIADES_MENSUEL')
  and anneemois>=_anneemois
  order by id
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
    call int_mroad2ev_annexe(_id);
  END LOOP _loop;
  CLOSE _cursor;
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 /*
drop view pai_int_ev_annexe_resume;
create view pai_int_ev_annexe_resume as
select 
prpg.annexe as poste,prpg.libelle,peh.datev,sum(peh.qte) as qte,peh.taux,sum(peh.val) as val
          from pai_int_traitement pit
          inner join pai_ev_hst peh on pit.id=peh.idtrt
          left outer join pai_ref_postepaie_general prpg on peh.poste=prpg.poste
          inner join employe e on peh.matricule=e.matricule
          inner join emp_pop_depot epd on e.id=epd.employe_id and peh.datev between epd.date_debut and epd.date_fin
          where pit.id in (select max(id) from pai_int_traitement pit2 where pit2.typetrt like 'GENERE_PLEIADE_%' and pit2.anneemois=pit.anneemois and pit2.flux_id=pit.flux_id and pit2.statut='T')
          and e.id=5007 and epd.depot_id=10
          and pit.anneemois='201503'
          and pit.flux_id=1
          and prpg.annexe<>'----'
          group by peh.typev,pit.anneemois,epd.depot_id,pit.flux_id,epd.employe_id,prpg.annexe,prpg.libelle,peh.datev,peh.taux
          ;
 */         
