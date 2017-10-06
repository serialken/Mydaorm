/*
call req_employe_taux_qualite_salarie(10,1,'201612');
*/
drop procedure req_employe_taux_qualite_salarie;
create procedure req_employe_taux_qualite_salarie(_depot_id int, _flux_id int, _anneemois_id varchar(6))
comment 'Taux de qualité salarié'
begin
/*
           <column id="{{h.libelle}}"     
                    type="{{h.type}}"   
                    hidden="{{h.hidden}}"
                    width="{{h.width}}"
                    align="{{h.align}}"
                    sort="{{h.sort}}"
                    >{{h.libelle}}</column>

*/
  select 
  -- libelle__type__hidden__width__align__sort__search"
    concat_ws(' ',e.nom,e.prenom1,e.prenom2) as "Employé__ro__false__200__left__str__#select_filter"
  , peep.nbabo as "Nombre Abonnés Semaine__ro__false__80__right__int__#numeric_filter"
  , peep.nbrec_brut as "Nombre Réclamations Brut Semaine__ro__false__80__right__int__#numeric_filter"
  , peep.taux_brut as "Taux Qualité Brut Semaine__ro__false__80__right__int__#numeric_filter"
  , peep.nbrec as "Nombre Réclamations Semaine__ro__false__80__right__int__#numeric_filter"
  , peep.taux as "Taux Qualité Semaine__ro__false__80__right__int__#numeric_filter"
  , peep.nbabo_DF as "Nombre Abonnés Dimanche/Férié__ro__false__80__right__int__#numeric_filter"
  , peep.nbrec_DF_brut as "Nombre Réclamations Brut Dimanche/Férié__ro__false__80__right__int__#numeric_filter"
  , peep.taux_DF_brut as "Taux Qualité Brut Dimanche/Férié__ro__false__80__right__int__#numeric_filter"
  , peep.nbrec_DF as "Nombre Réclamations Dimanche/Férié__ro__false__80__right__int__#numeric_filter"
  , peep.taux_DF as "Taux Qualité Dimanche/Férié__ro__false__80__right__int__#numeric_filter"
  , peep.nbrec_dif_brut as "Nombre Réclamations Diffuseur Brut Semaine__ro__false__80__right__int__#numeric_filter"
  , peep.nbrec_dif as "Nombre Réclamations Diffuseur Semaine__ro__false__80__right__int__#numeric_filter"
  , peep.nbrec_dif_DF as "Nombre Réclamations Diffuseur Brut Dimanche/Férié__ro__false__80__right__int__#numeric_filter"
  , peep.nbrec_dif_df_brut as "Nombre Réclamations Diffuseur Brut Dimanche/Férié__ro__false__80__right__int__#numeric_filter"
  , count(distinct pi.id) as "Nombre Incident__ro__false__80__right__int__#numeric_filter"
  , date_format(peep.d,'%d/%m/%Y') as "Date_Début__ro__false__80__center__date__#text_filter"
  , date_format(peep.f,'%d/%m/%Y') as "Date_Fin__ro__false__80__center__date__#text_filter"
  , date_format(peep.dRC,'%d/%m/%Y') as "Date_Début_RC__ro__false__80__center__date__#text_filter"
  , date_format(peep.fRC,'%d/%m/%Y') as "Date_Fin_RC__ro__false__80__center__date__#text_filter"
  from pai_int_traitement pit
  inner join pai_ref_mois prm on prm.anneemois=pit.anneemois
  inner join pai_ev_emp_pop_depot_hst peep on pit.id=peep.idtrt
  inner join employe e on peep.employe_id=e.id
  left outer join pai_incident pi on pi.employe_id=peep.employe_id and pi.date_distrib between prm.date_debut and prm.date_fin
  where pit.anneemois=_anneemois_id
  and pit.flux_id=_flux_id
  and peep.depot_id=_depot_id
--  and exists(select null from pai_int_traitement pit2 where 
  and pit.id in (select max(pit2.id) 
                from pai_int_traitement pit2
                inner join pai_ev_emp_pop_depot_hst peep2 on pit2.id=peep2.idtrt
                where pit2.flux_id=_flux_id and pit2.anneemois=_anneemois_id and peep2.depot_id=_depot_id and peep2.employe_id=peep.employe_id
                and (pit2.typetrt like '%_PLEIADES_MENSUEL' or pit2.typetrt like '%CLOTURE%' or pit2.typetrt like '%STC%')
                )
  group by e.id,peep.employe_id,peep.dRC,peep.depot_id,peep.population_id
  
  union
  
   select 
  -- libelle__type__hidden__width__align__sort__search"
    'Total Dépôt' as "Employé__ro__false__200__left__str__#select_filter"
  , sum(peep.nbabo) as "Nombre Abonnés Semaine__ro__false__80__right__int__#numeric_filter"
  , sum(peep.nbrec_brut) as "Nombre Réclamations Brut Semaine__ro__false__80__right__int__#numeric_filter"
  , sum(peep.nbrec_brut)*1000/sum(coalesce(peep.nbabo,0)) as "Taux Qualité Brut Semaine__ro__false__80__right__int__#numeric_filter"
  , sum(peep.nbrec) as "Nombre Réclamations Semaine__ro__false__80__right__int__#numeric_filter"
  , sum(peep.nbrec)*1000/sum(coalesce(peep.nbabo,0)) as "Taux Qualité Semaine__ro__false__80__right__int__#numeric_filter"
  , sum(peep.nbabo_DF) as "Nombre Abonnés Semaine__ro__false__80__right__int__#numeric_filter"
  , sum(peep.nbrec_DF_brut) as "Nombre Réclamations Brut Dimanche/Férié__ro__false__80__right__int__#numeric_filter"
  , sum(peep.nbrec_DF_brut)*1000/sum(coalesce(peep.nbabo_DF,0)) as "Taux Qualité Brut Dimanche/Férié__ro__false__80__right__int__#numeric_filter"
  , sum(peep.nbrec_DF) as "Nombre Réclamations Dimanche/Férié__ro__false__80__right__int__#numeric_filter"
  , sum(peep.nbrec_DF)*1000/sum(coalesce(peep.nbabo_DF,0)) as "Taux Qualité Dimanche/Férié__ro__false__80__right__int__#numeric_filter"
  , sum(peep.nbrec_dif_brut) as "Nombre Réclamations Diffuseur Brut Semaine__ro__false__80__right__int__#numeric_filter"
  , sum(peep.nbrec_dif) as "Nombre Réclamations Diffuseur Semaine__ro__false__80__right__int__#numeric_filter"
  , sum(peep.nbrec_dif_DF) as "Nombre Réclamations Diffuseur Brut Dimanche/Férié__ro__false__80__right__int__#numeric_filter"
  , sum(peep.nbrec_dif_df_brut) as "Nombre Réclamations Diffuseur Brut Dimanche/Férié__ro__false__80__right__int__#numeric_filter"
  , count(distinct pi.id) as "Nombre Incident__ro__false__80__right__int__#numeric_filter"
  , null as "Date_Début__ro__false__80__center__date__#text_filter"
  , null as "Date_Fin__ro__false__80__center__date__#text_filter"
  , null as "Date_Début_RC__ro__false__80__center__date__#text_filter"
  , null as "Date_Fin_RC__ro__false__80__center__date__#text_filter"
  from pai_int_traitement pit
  inner join pai_ref_mois prm on prm.anneemois=pit.anneemois
  inner join pai_ev_emp_pop_depot_hst peep on pit.id=peep.idtrt
  inner join employe e on peep.employe_id=e.id
  left outer join pai_incident pi on pi.employe_id=peep.employe_id and pi.date_distrib between prm.date_debut and prm.date_fin
  where pit.anneemois=_anneemois_id
  and pit.flux_id=_flux_id
  and peep.depot_id=_depot_id
--  and exists(select null from pai_int_traitement pit2 where 
  and pit.id in (select max(pit2.id) 
                from pai_int_traitement pit2
                inner join pai_ev_emp_pop_depot_hst peep2 on pit2.id=peep2.idtrt
                where pit2.flux_id=_flux_id and pit2.anneemois=_anneemois_id and peep2.depot_id=_depot_id and peep2.employe_id=peep.employe_id
                and (pit2.typetrt like '%_PLEIADES_MENSUEL' or pit2.typetrt like '%CLOTURE%' or pit2.typetrt like '%STC%')
                )
 
  ;
end;
