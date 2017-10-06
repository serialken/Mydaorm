-- ----------------------------------------------------------------------------
-- INDIVIDU
-- ----------------------------------------------------------------------------
 -- individu sans RC
  select * from employe where  matricule=saloid;
 
  select * from employe e
  where not exists(select null from emp_pop_depot epd where e.id=epd.employe_id)
  ;
  /*
  select * from modele_tournee_jour where employe_id=10381
  delete from employe where id=1042
  delete e from employe e
  where not exists(select null from emp_pop_depot epd where e.id=epd.employe_id)
  ;
*/
  select e.matricule,e.saloid,e.nom,mtj.code,mtj.date_debut,mtj.date_fin
  from employe e
  inner join modele_tournee_jour mtj on mtj.employe_id=e.id
  inner join modele_tournee mt on mtj.tournee_id=mt.id
  where not exists(select null from emp_pop_depot epd where e.id=epd.employe_id)
  ;
/*
  update employe e
  inner join modele_tournee_jour mtj on mtj.employe_id=e.id
  inner join modele_tournee mt on mtj.tournee_id=mt.id
  set mtj.employe_id=null
  where not exists(select null from emp_pop_depot epd where e.id=epd.employe_id)
  and (mtj.date_fin<now() or not mt.actif)
  ;
*/
  select * from employe e
  inner join emp_pop_depot epd on e.id=epd.employe_id
  where  matricule<>saloid and e.id not in (select employe_id from emp_cycle);
/*
  delete e from employe e
--  delete pj from employe e
  select * from employe e
  inner join pai_tournee pt on e.id=pt.employe_id
  where not exists(select null from emp_pop_depot epd where e.id=epd.employe_id)
  and  not exists(select null from modele_tournee_jour mtj where e.id=mtj.employe_id)
  and  not exists(select null from pai_tournee pt where e.id=pt.employe_id)
  and  not exists(select null from pai_ev_qualite pj where e.id=pj.employe_id)
  and  not exists(select null from pai_activite pj where e.id=pj.employe_id)
  ;
  delete from employe
  where id not in (select distinct epd.employe_id from emp_pop_depot epd)
  and id not in (select distinct ec.employe_id from emp_cycle ec)
  and id not in (select distinct mtj.employe_id from modele_tournee_jour mtj)

  select * from employe
  where id not in (select distinct epd.employe_id from emp_pop_depot epd)
  and id not in (select distinct mtj.employe_id from modele_tournee_jour mtj)
*/
-- population non autorisées
select * from emp_pop_depot where societe_id=1 and population_id not in (1,2,3,4,13,14,15,16,17,18,19);
select * from emp_pop_depot where societe_id=2 and population_id not in (5,6,7,8,9,10,11,12,13,14,15,16,17,18,19);

 -- chevauchement emp_pop_depot
  select epd1.employe_id,epd1.date_debut,epd1.date_fin,epd2.date_debut,epd2.date_fin
  from emp_pop_depot epd1
  inner join emp_pop_depot epd2 on epd2.id<>epd1.id and epd1.employe_id=epd2.employe_id
  where coalesce(epd1.date_fin,'2999-01-01')>=epd2.date_debut and epd1.date_debut<=coalesce(epd2.date_fin,'2999-01-01')
  and epd1.date_debut<=epd2.date_debut
  order by epd1.employe_id,epd1.date_debut;

-- select * from employe where id=674

delete from emp_pop_depot where date_debut>fRC;
select * from emp_pop_depot where date_fin  is null;
select * from emp_pop_depot where societe_id>0 and contrat_id=0;
select * from emp_pop_depot where societe_id>0 and contrattype_id=0;
select * from emp_pop_depot where societe_id>0 and contrattype_id=0 and typecontrat_id=1;
select * from emp_pop_depot where societe_id>0 and contrattype_id=0 and typecontrat_id=2;
select * from emp_pop_depot where societe_id>0 and contrattype_id<>0 and typecontrat_id=2;
select * from emp_pop_depot where societe_id>0 and cycle_id=0;
select * from emp_pop_depot where societe_id>0 and cycle_id<>0 and cycle='-------';
-- ----------------------------------------------------------------------------
-- MODELE_TOURNEE_JOUR
-- ----------------------------------------------------------------------------
 -- chevauchement modele_tournee_jour
  select mtj1.id,mtj2.id,mtj1.code,mtj1.date_debut,mtj1.date_fin,mtj2.date_debut,mtj2.date_fin,mtj1.date_modif,mtj2.date_modif
  from modele_tournee_jour mtj1
  inner join modele_tournee_jour mtj2 on mtj1.id<>mtj2.id and mtj1.code=mtj2.code
  where coalesce(mtj1.date_fin,'2999-01-01')>=mtj2.date_debut and mtj1.date_debut<=coalesce(mtj2.date_fin,'2999-01-01')
  and mtj1.date_debut<=mtj2.date_debut
  order by mtj1.code,mtj1.date_debut
  ;
  /*
  select * from modele_tournee_jour where code='039NQD048DI' order by date_debut desc;
  update modele_tournee_jour set date_fin='2017-01-22' where id=84479
  update modele_tournee_jour set date_debut='2017-01-23' where id=85082
  select * from pai_tournee where modele_tournee_jour_id=84479
  select * from pai_tournee where modele_tournee_jour_id=85082
  delete from modele_journal where tournee_jour_id=66735
  delete from modele_tournee_jour where id=85845
  */
  -- modele_tournee_jour pb date
  select mtj.id,mtj.code,mtj.date_debut,mtj.date_fin,mtj2.id,mtj2.code,mtj2.date_debut,mtj2.date_fin
  from modele_tournee_jour mtj
  left outer join modele_tournee_jour mtj2 on mtj.tournee_id=mtj2.tournee_id and mtj.jour_id=mtj2.jour_id and mtj2.date_debut>=mtj.date_debut and mtj.id<>mtj2.id
  where mtj.date_fin is null 
  or mtj.date_debut is null
  or mtj.date_fin<mtj.date_debut
  ;
  -- update modele_tournee_jour set date_fin='2999-01-01' where date_fin is null

  -- on borne la date du modele � la date de r�mun�ration pour les modeles inactifs
  select mtj.code,mtj.date_debut,mtj.date_fin,prr.date_debut,prr.date_fin,mtj.tauxhoraire,prr.valeur, mt.actif
  from modele_tournee_jour mtj
  inner join modele_tournee mt on mt.id=mtj.tournee_id -- and mt.actif
  inner join groupe_tournee gt on gt.id=mt.groupe_id
  inner join ref_typetournee rtt on gt.flux_id=rtt.id
  inner join pai_ref_remuneration prr on rtt.societe_id=prr.societe_id AND rtt.population_id=prr.population_id and  mtj.date_debut between prr.date_debut and prr.date_fin
  where mtj.tauxhoraire<>prr.valeur
  and mtj.date_fin>prr.date_fin
  order by mt.actif desc,mtj.code,mtj.date_debut
  ;
  /*
  update modele_tournee_jour mtj
  inner join modele_tournee mt on mt.id=mtj.tournee_id
  inner join groupe_tournee gt on gt.id=mt.groupe_id
  inner join ref_typetournee rtt on gt.flux_id=rtt.id
  inner join pai_ref_remuneration prr on rtt.societe_id=prr.societe_id AND rtt.population_id=prr.population_id and  mtj.date_debut between prr.date_debut and prr.date_fin
  set mtj.date_fin=prr.date_fin
  where mtj.tauxhoraire<>prr.valeur
  and mtj.date_fin>prr.date_fin
  and not mt.actif
  ;
*/
  -- Valeur de rémunération incorrecte à l'interieure d'un taux horaire
  select mtj.code,mtj.date_debut,mtj.date_fin,prr.date_debut,prr.date_fin,mtj.tauxhoraire,prr.valeur, mt.actif
  from modele_tournee_jour mtj
  inner join modele_tournee mt on mt.id=mtj.tournee_id -- and mt.actif
  inner join groupe_tournee gt on gt.id=mt.groupe_id
  inner join ref_typetournee rtt on gt.flux_id=rtt.id
  inner join pai_ref_remuneration prr on rtt.societe_id=prr.societe_id AND rtt.population_id=prr.population_id 
  and  mtj.date_debut between prr.date_debut and prr.date_fin
  and  mtj.date_fin between prr.date_debut and prr.date_fin
  where mtj.tauxhoraire<>prr.valeur
  order by mt.actif desc,mtj.code,mtj.date_debut
  ;
/*
  update modele_tournee_jour mtj
  inner join modele_tournee mt on mt.id=mtj.tournee_id
  inner join groupe_tournee gt on gt.id=mt.groupe_id
  inner join ref_typetournee rtt on gt.flux_id=rtt.id
  inner join pai_ref_remuneration prr on rtt.societe_id=prr.societe_id AND rtt.population_id=prr.population_id 
  and  mtj.date_debut between prr.date_debut and prr.date_fin
  and  mtj.date_fin between prr.date_debut and prr.date_fin
  set mtj.tauxhoraire=prr.valeur
  where mtj.tauxhoraire<>prr.valeur
  and not mt.actif
  ;
*/
  -- Modele inexistant sur rupture de taux horaire
  select mtj.code,mtj.date_debut,mtj.date_fin,mtj.date_creation,mtj.date_modif,prr.date_debut,prr.date_fin,mtj.tauxhoraire,prr.valeur, mt.actif
  from modele_tournee_jour mtj
  inner join modele_tournee mt on mt.id=mtj.tournee_id -- and mt.actif
  inner join groupe_tournee gt on gt.id=mt.groupe_id
  inner join ref_typetournee rtt on gt.flux_id=rtt.id
  inner join pai_ref_remuneration prr on rtt.societe_id=prr.societe_id AND rtt.population_id=prr.population_id 
  AND mtj.date_debut<=prr.date_fin and mtj.date_fin>=prr.date_debut
  where mtj.tauxhoraire<>prr.valeur
  order by mt.actif desc,mtj.code,mtj.date_debut
  ;
/*
  update modele_tournee_jour mtj
  inner join modele_tournee mt on mt.id=mtj.tournee_id -- and mt.actif
  inner join groupe_tournee gt on gt.id=mt.groupe_id
  inner join ref_typetournee rtt on gt.flux_id=rtt.id
  inner join pai_ref_remuneration prr on rtt.societe_id=prr.societe_id AND rtt.population_id=prr.population_id 
  AND mtj.date_debut<=prr.date_fin and mtj.date_fin>=prr.date_debut
  inner join pai_ref_remuneration prr2 on rtt.societe_id=prr2.societe_id AND rtt.population_id=prr2.population_id 
  AND mtj.date_debut between prr2.date_debut and prr2.date_fin
  set mtj.date_fin=prr2.date_fin
  where mtj.tauxhoraire<>prr.valeur
  and not mt.actif
  ;

*/
  -- Valeur de r�mun�ration incorrecte
  select mtj.code,mtj.date_debut,mtj.date_fin,mtj.date_creation,mtj.date_modif,prr.date_debut,prr.date_fin,mtj.tauxhoraire,prr.valeur, mt.actif
  from modele_tournee_jour mtj
  inner join modele_tournee mt on mt.id=mtj.tournee_id -- and mt.actif
  inner join groupe_tournee gt on gt.id=mt.groupe_id
  inner join ref_typetournee rtt on gt.flux_id=rtt.id
  inner join pai_ref_remuneration prr on rtt.societe_id=prr.societe_id AND rtt.population_id=prr.population_id 
  AND mtj.date_debut<=prr.date_fin and mtj.date_fin>=prr.date_debut
  where mtj.tauxhoraire<>prr.valeur
  order by mt.actif desc,mtj.code,mtj.date_debut
  ;
/*
  update modele_tournee_jour mtj
  inner join modele_tournee mt on mt.id=mtj.tournee_id -- and mt.actif
  inner join groupe_tournee gt on gt.id=mt.groupe_id
  inner join ref_typetournee rtt on gt.flux_id=rtt.id
  inner join pai_ref_remuneration prr on rtt.societe_id=prr.societe_id AND rtt.population_id=prr.population_id 
  AND mtj.date_debut<=prr.date_fin and mtj.date_fin>=prr.date_debut
  set mtj.tauxhoraire=prr.valeur
  where mtj.tauxhoraire<>prr.valeur
  ;
*/
  -- Incoh�rence etalon / valrem
 select etalon,round(valrem/tauxhoraire,6),mtj.* from modele_tournee_jour mtj where etalon<>round(valrem/tauxhoraire,6)
 select etalon_calcule,round(valrem_calculee/9.67,6),cal_modele_etalon(et.duree_tournee,et.nbcli),et.duree_tournee,et.nbcli,et.* from etalon_tournee et where etalon_calcule<>round(valrem_calculee/9.67,6)
 select * from etalon_tournee  where modele_tournee_id=3420
 select cal_modele_etalon(et.duree_tournee,et.nbcli)
-- update modele_tournee_jour set etalon=round(valrem/tauxhoraire,6) where  etalon<>round(valrem/tauxhoraire,6)
-- Incoherence valrem/valrem moyen (SEULEMENT à l'intialisation)
select * from modele_tournee_jour where coalesce(valrem,0)<>coalesce(valrem_moyen,0)
/*
  update modele_tournee_jour mtj set valrem_moyen=valrem where valrem<>valrem_moyen ;
  update modele_tournee_jour mtj set etalon_moyen=etalon where etalon<>etalon_moyen ;
*/
 -- Incoh�rence etalon / valrem
  select coalesce(duree,'00:00') as duree
  ,sec_to_time(round(coalesce(nbcli,0)*etalon*3600)) as duree_calculee
  ,sec_to_time(round(coalesce(nbcli,0)*valrem/tauxhoraire*3600)) as duree_calculee
  ,nbcli
  ,round(time_to_sec(coalesce(duree,'00:00'))/etalon/3600) as nbcli_calcule
  ,etalon
  ,valrem
  ,date_creation,
  date_modif
  ,mtj.* 
  from modele_tournee_jour mtj  
  -- On ne peu rien y faire, l'etalon ou la valeur de rémunération n'est pas assez précise
--  where abs(time_to_sec(coalesce(duree,'00:00'))-round(coalesce(nbcli,0)*etalon*3600))>0
-- Donc on travaille sur le nombre de client
  where round(time_to_sec(coalesce(duree,'00:00'))/etalon/3600)<>coalesce(nbcli,0)
  and mtj.date_fin>=now()
  ;
/*
update modele_tournee_jour mtj set valrem=0,valrem_moyen=0, etalon=0,etalon_moyen=0 where valrem<0
update modele_tournee_jour mtj
set nbcli=round(time_to_sec(coalesce(duree,'00:00'))/etalon/3600)
where nbcli is null or round(time_to_sec(coalesce(duree,'00:00'))/etalon/3600)<>coalesce(nbcli,0);


  update modele_tournee_jour mtj 
  set duree=sec_to_time(round(coalesce(nbcli,0)*etalon*3600))
  where mtj.date_fin>=now()
  and abs(time_to_sec(coalesce(duree,'00:00'))-round(coalesce(nbcli,0)*etalon*3600))>0
  ;
*/
-- ----------------------------------------------------------------------------
-- MODELE_TOURNEE_JOUR PAI_TOURNEE
-- ----------------------------------------------------------------------------
  -- concordence valrem modele_tournee et pai_tournee
  select pt.flux_id,pt.date_distrib,mtj.id,mtj.date_debut,mtj.date_fin,pt.code,coalesce(pt.valrem,0) as paie,mtj.valrem as modele,mtj.valrem_moyen as moyen
  from pai_tournee pt
  inner join modele_tournee_jour mtj on pt.modele_tournee_jour_id=mtj.id
  where coalesce(pt.valrem,0)<>coalesce(if(pt.flux_id=1,mtj.valrem_moyen,mtj.valrem),0)
  and pt.date_distrib>'2016-01-01'
  -- and pt.date_extrait is null
  order by pt.id desc
  ;
  select * from pai_tournee where modele_tournee_jour_id=43303
  select * from modele_tournee_jour where id=43303
  select * from pai_tournee where date_distrib between '2015-11-21' and '2015-11-31' and code='45M17      '
/*
  update pai_tournee pt
  inner join modele_tournee_jour mtj on pt.modele_tournee_jour_id=mtj.id -- and pt.date_distrib between mtj.date_debut and mtj.date_fin
  left outer join emp_pop_depot epd on pt.employe_id=epd.employe_id and pt.date_distrib between epd.date_debut and epd.date_fin
  left outer join ref_transport rt on rt.id=mtj.transport_id
  set pt.transport_id=mtj.transport_id
  ,   pt.nbkm=mtj.nbkm
  ,   pt.nbkm_paye=case when coalesce(epd.km_paye,1) and coalesce(rt.km_paye,1) then mtj.nbkm_paye else 0 end
  ,   pt.valrem=if(pt.flux_id=1,mtj.valrem_moyen,mtj.valrem)
  where coalesce(pt.valrem,0)<>coalesce(if(pt.flux_id=1,mtj.valrem_moyen,mtj.valrem),0)
  and pt.date_distrib>'2016-01-01'
  and pt.date_extrait is null
*/
  -- concordence jour_id modele_tournee et pai_tournee
  select *
  from pai_tournee pt
  inner join modele_tournee_jour mtj on pt.modele_tournee_jour_id=mtj.id
  where pt.jour_id<>mtj.jour_id
  and pt.date_extrait is null
  order by pt.id desc
  ;
  -- concordence date_distrib/date_debut/date_fin modele_tournee et pai_tournee
  select pt.date_distrib,pt.code,left(pt.code,11),mtj.date_debut,mtj.date_fin,mtj.code,mtj.date_modif,pt.modele_tournee_jour_id,mtj.id,pt.*
  from pai_tournee pt
  left outer join modele_tournee_jour mtj on substr(pt.code,1,11)=mtj.code and  pt.date_distrib between mtj.date_debut and mtj.date_fin
  where not exists(select null from modele_tournee_jour mtj where pt.modele_tournee_jour_id=mtj.id and pt.date_distrib between mtj.date_debut and mtj.date_fin)
  and pt.date_distrib>'2016-01-01'
  -- and pt.date_extrait is null
  order by pt.id desc
  ;
/*
select * from modele_tournee_jour where code like '040JTI081%'
update pai_tournee pt
inner join modele_tournee_jour mtj on substr(pt.code,1,11)=mtj.code and  pt.date_distrib between mtj.date_debut and mtj.date_fin
set pt.modele_tournee_jour_id=mtj.id
where not exists(select null from modele_tournee_jour mtj where pt.modele_tournee_jour_id=mtj.id and pt.date_distrib between mtj.date_debut and mtj.date_fin)
*/
  -- concordence date_distrib/date_debut/date_fin modele_tournee et client_a_servir_logist
  select *
  from client_a_servir_logist casl
  inner join pai_mois pm on casl.flux_id=pm.flux_id
  where not exists(select null from modele_tournee_jour mtj where casl.tournee_jour_id=mtj.id and casl.date_distrib between mtj.date_debut and mtj.date_fin)
  and casl.date_distrib>=pm.date_debut
  and casl.tournee_jour_id is not null
  order by casl.id desc
  ;
  select *
  from pai_mois pm
  inner join client_a_servir_logist casl on casl.flux_id=pm.flux_id and casl.date_distrib>=pm.date_debut
  where not exists(select null from modele_tournee_jour mtj where casl.tournee_jour_id=mtj.id and casl.date_distrib between mtj.date_debut and mtj.date_fin)
  and casl.tournee_jour_id is not null
  order by casl.id desc
  ;
  -- concordence date_distrib/date_debut/date_fin modele_tournee et feuille_portage
  select *
  from feuille_portage fp
  where not exists(select null from modele_tournee_jour mtj where fp.tournee_jour_id=mtj.id and fp.date_distrib between mtj.date_debut and mtj.date_fin)
  and fp.date_distrib>'2016-01-01'
  order by fp.id desc
  ;
  /*
  update feuille_portage set tournee_jour_id=65287 where tournee_jour_id=52000 and date_distrib='2016-08-26';
  select * from modele_tournee_jour where id=52000
  select * from modele_tournee_jour where tournee_id=1540
  select * from modele_tournee where id=1540
  */
  -- concordence date_distrib/date_debut/date_fin modele_tournee et crm_detail
  select *
  from crm_detail c
  inner join modele_tournee_jour mtj on c.modele_tournee_jour_id=mtj.id
--  where not exists(select null from modele_tournee_jour mtj where c.modele_tournee_jour_id=mtj.id and c.date_imputation_paie between mtj.date_debut and mtj.date_fin)
  where c.modele_tournee_jour_id is not null
  and c.date_imputation_paie is not null
  and not c.date_imputation_paie between mtj.date_debut and mtj.date_fin
  -- and c.imputation_paie=1
  order by c.id desc
  ;

-- ----------------------------------------------------------------------------
-- KM_PAYE
-- ----------------------------------------------------------------------------
  select * from modele_activite ma
  left outer join ref_activite ra on ra.id=ma.activite_id
  left outer join ref_transport rt on rt.id=ma.transport_id
  where ma.nbkm_paye<>case when coalesce(rt.km_paye,1) and coalesce(ra.km_paye,1) then ma.nbkm_paye else 0 end
  ;
  select mtj.code,rt.libelle,mtj.nbkm_paye
  from modele_tournee_jour mtj
  left outer join ref_transport rt on rt.id=mtj.transport_id
  where mtj.nbkm_paye<>case when coalesce(rt.km_paye,1) then mtj.nbkm_paye else 0 end
  and date_fin>=now()
  order by mtj.code
  ;
  select e.depot,e.flux,ma.date_distrib,ra.libelle,rt.libelle,e.nom,e.prenom1,ma.nbkm_paye
  from pai_activite ma
  inner join v_employe e on ma.employe_id=e.employe_id and ma.date_distrib between e.date_debut and e.date_fin
  left outer join ref_activite ra on ra.id=ma.activite_id
  left outer join ref_transport rt on rt.id=ma.transport_id
  where ma.nbkm_paye<>case when coalesce(rt.km_paye,1) and coalesce(ra.km_paye,1) and coalesce(e.km_paye,1) then ma.nbkm_paye else 0 end
  and date_extrait is null
  order by 1,2,3
  ;
  select e.depot,e.flux,mtj.date_distrib,mtj.code,rt.libelle,e.nom,e.prenom1,mtj.nbkm_paye
  from pai_tournee mtj
  inner join v_employe e on mtj.employe_id=e.employe_id and mtj.date_distrib between e.date_debut and e.date_fin
  left outer join ref_transport rt on rt.id=mtj.transport_id
  where mtj.nbkm_paye<>case when coalesce(rt.km_paye,1) and coalesce(e.km_paye,1) then mtj.nbkm_paye else 0 end
  and date_extrait is null
  order by 1,2,3
  ;
-- ----------------------------------------------------------------------------
-- CARACTERISTIQUES PRODUIT
-- ----------------------------------------------------------------------------
  -- produit sans type urssaf
  select * 
  from produit p
  where type_id in (1)
  and not exists(select null from prd_caract_constante pcc where prd_caract_id=14 and pcc.produit_id=p.id)
  ;
/*
  insert into prd_caract_constante(produit_id,prd_caract_id,utilisateur_id,valeur_string,date_creation)
  select p.id,14,0,'R',sysdate()
  from produit p
  where not exists(select null from prd_caract_constante pcc
  where prd_caract_id=14 and pcc.produit_id=p.id);
  */
  -- liste des type urssaf incorrects
  select * 
  from prd_caract_constante pcc
  inner join produit p on pcc.produit_id=p.id and  type_id in (1)
  where prd_caract_id=14 and valeur_string not in ('F','R')
  ;
-- ----------------------------------------------------------------------------
-- pb sur tournee splittee
-- ----------------------------------------------------------------------------
  select * from pai_tournee
  where tournee_org_id is null
  and id in (select tournee_org_id from pai_tournee pt2 where split_id is not null)
  ;
  
-- ----------------------------------------------------------------------------
-- pb attente
-- ----------------------------------------------------------------------------
-- Temps attente manquant
select * from pai_tournee pt 
inner join pai_heure ph on pt.heure_id=ph.id
where TIME_TO_SEC(pt.duree_retard)<TIME_TO_SEC(pt.duree_attente)
    AND pt.employe_id is not null
    AND (pt.tournee_org_id is null or pt.split_id is not null)
    AND pt.ordre=1 -- On met l'attente que sur la première tournée
  and pt.duree_attente<>'00:00:00'
and pt.date_extrait is null
and not exists(select null from pai_activite pa where pa.tournee_id=pt.id and pa.activite_id=-2);

-- temps attente en trop
select * from pai_tournee pt 
inner join pai_heure ph on pt.heure_id=ph.id
inner join pai_activite pa on pa.tournee_id=pt.id and pa.activite_id=-2
where (TIME_TO_SEC(pt.duree_retard)>TIME_TO_SEC(ph.duree_attente)
    or pt.employe_id is  null
    or not (pt.tournee_org_id is null or pt.split_id is not null)
    or pt.ordre<>1 -- On met l'attente que sur la première tournée
  or ph.duree_attente='00:00:00')
and pt.date_extrait is null
;
/*
    INSERT INTO pai_activite(tournee_id,jour_id,typejour_id,depot_id,flux_id,activite_id,employe_id,transport_id,utilisateur_id,date_distrib,heure_debut,duree,nbkm_paye,date_creation)
		SELECT pt.id,pt.jour_id,pt.typejour_id,pt.depot_id,pt.flux_id,-2,pt.employe_id,pt.transport_id,pt.utilisateur_id,pt.date_distrib,null,SEC_TO_TIME(TIME_TO_SEC(ph.duree_attente)-TIME_TO_SEC(pt.duree_retard)),0,ph.date_creation
		FROM pai_tournee pt
    inner join pai_heure ph on pt.heure_id=ph.id
		WHERE TIME_TO_SEC(pt.duree_retard)<TIME_TO_SEC(ph.duree_attente)
    AND pt.employe_id is not null
    AND (pt.tournee_org_id is null or pt.split_id is not null)
    AND pt.ordre=1 -- On met l'attente que sur la première tournée
  and pt.date_extrait is null
  and not exists(select null from pai_activite pa where pa.tournee_id=pt.id and pa.activite_id=-2);
    ;

delete pj from pai_tournee pt 
inner join pai_heure ph on pt.heure_id=ph.id
inner join pai_activite pa on pa.tournee_id=pt.id and pa.activite_id=-2
inner join pai_journal pj on pa.id=pj.activite_id
where (TIME_TO_SEC(pt.duree_retard)>TIME_TO_SEC(ph.duree_attente)
    or pt.employe_id is  null
    or not (pt.tournee_org_id is null or pt.split_id is not null)
    or pt.ordre<>1 -- On met l'attente que sur la première tournée
  or ph.duree_attente='00:00:00')
and pt.date_extrait is null
;
delete pa from pai_tournee pt 
inner join pai_heure ph on pt.heure_id=ph.id
inner join pai_activite pa on pa.tournee_id=pt.id and pa.activite_id=-2
where (TIME_TO_SEC(pt.duree_retard)>TIME_TO_SEC(ph.duree_attente)
    or pt.employe_id is  null
    or not (pt.tournee_org_id is null or pt.split_id is not null)
    or pt.ordre<>1 -- On met l'attente que sur la première tournée
  or ph.duree_attente='00:00:00')
and pt.date_extrait is null
;

    UPDATE pai_tournee pt
    inner join pai_heure ph on pt.heure_id=ph.id
    SET pt.duree_attente=IF ((pt.tournee_org_id is null or pt.split_id is not null) and pt.ordre=1,ph.duree_attente,'00:00:00')
    WHERE pt.date_extrait is null
    ;
call recalcul_horaire(@validation_id,null,null,null,NULL);
*/