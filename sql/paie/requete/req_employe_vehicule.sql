/*
call req_employe_vehicule(10,1,'201608');
*/
drop procedure req_employe_vehicule;
create procedure req_employe_vehicule(_depot_id int, _flux_id int, _anneemois_id varchar(6))
comment 'Véhicules'
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
 SELECT DISTINCT
  e.matricule as "Matricule__ro__false__100__center__str__#text_filter"
  , concat_ws(' ',e.nom,e.prenom1,e.prenom2) as "Employé__ro__false__200__left__str__#select_filter"
  , date_format(pv.begin_date,'%d/%m/%Y') as "Début__ro__false__70__center__sortDate"
  , date_format(pv.end_date,'%d/%m/%Y') as "Fin__ro__false__70__center__sortDate"
  , pv.immatriculation as "Immatriculation__ro__false__200__center__str"
  , pv.assureur as "Assureur__ro__false__*__left__str"
  , date_format(pv.dtdebassur,'%d/%m/%Y') as "Début assurance__ro__false__70__center__sortDate"
  , date_format(pv.dtfinassur,'%d/%m/%Y') as "Fin assurance__ro__false__70__center__sortDate"
  , date_format(pv.dtvaliditect,'%d/%m/%Y') as "Contrôle technique__ro__false__70__center__sortDate"
  FROM employe e
  INNER JOIN emp_pop_depot epd ON e.id = epd.employe_id AND epd.population_id>0
  INNER JOIN pai_ref_mois prm ON prm.anneemois=_anneemois_id
  LEFT OUTER JOIN pai_png_vehiculew pv ON pv.salarie = e.saloid AND pv.end_date>=prm.date_debut and pv.begin_date<=prm.date_fin
  WHERE epd.depot_id =_depot_id AND epd.flux_id =_flux_id
  AND epd.date_fin>=prm.date_debut and epd.date_debut<=prm.date_fin
  ORDER BY e.nom,e.prenom1,e.prenom2
  ;
end;
