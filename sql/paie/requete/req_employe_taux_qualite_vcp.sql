/*
call req_employe_taux_qualite_vcp(22,1,'201611');
select * from depot
*/
drop procedure req_employe_taux_qualite_vcp;
create procedure req_employe_taux_qualite_vcp(_depot_id int, _flux_id int, _anneemois_id varchar(6))
comment 'Taux de qualité VCP'
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
    e.nom as "Nom__ro__false__120__left__str__#text_filter"
  , e.prenom1 as "Prénom"
  , date_format(prm.date_debut,'%d/%m/%Y') as "Date_Début__ro__false__80__center__date__#text_filter"
  , date_format(prm.date_fin,'%d/%m/%Y') as "Date_Fin__ro__false__80__center__date__#text_filter"
  , sum(pt.nbtitre) as "Quantités livrées__ro__false__80__right__int__#numeric_filter"
  , coalesce(sum(pr.nbrec_abonne+pr.nbrec_diffuseur),0) as "Nombre Réclamations__ro__false__80__right__int__#numeric_filter"
  , coalesce(sum(pr.nbrec_abonne+pr.nbrec_diffuseur),0)*1000/sum(pt.nbtitre) as "Taux Qualité__ro__false__80__right__int__#numeric_filter"
  from pai_ref_mois prm
  inner join pai_tournee pt on  pt.date_distrib between prm.date_debut and prm.date_fin
--    inner join pai_prd_tournee ppt on  pt.id=ppt.tournee_id
--    inner join produit p on ppt.produit_id=p.id and p.type_id in (1,2,3)
    left outer join pai_reclamation pr on pr.tournee_id=pt.id
  inner join emp_pop_depot epd on pt.employe_id=epd.employe_id and epd.population_id=-1 and pt.date_distrib between epd.date_debut and epd.date_fin
  inner join employe e on pt.employe_id=e.id
  where prm.anneemois=_anneemois_id
  and pt.flux_id=_flux_id
  and pt.depot_id=_depot_id
    and (pt.tournee_org_id is null or pt.split_id is not null)
    and not exists(SELECT NULL FROM pai_journal pj,pai_ref_erreur pe where pt.id=pj.tournee_id and pj.erreur_id=pe.id and pe.valide=0)
    group by e.id,epd.id,prm.date_debut,prm.date_fin
  ;
end;

