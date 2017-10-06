/*
call req_employe_visite_medicale(10,1,'201612');
*/
drop procedure req_employe_visite_medicale;
create procedure req_employe_visite_medicale(_depot_id int, _flux_id int, _anneemois_id varchar(6))
comment 'Visites médicales'
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
  e.matricule as "Matricule__ro__false__100__center__str__#text_filter"
  , concat_ws(' ',e.nom,e.prenom1,e.prenom2) as "Employé__ro__false__200__left__str__#select_filter"
  , date_format(ppm.bgndate,'%d/%m/%Y') as "Dernière visite__ro__false__80__center__sortDate"
  , date_format(ppm.medicprochain,'%d/%m/%Y') as "Prochaine visite__ro__false__80__center__sortDate"
  FROM employe e
  INNER JOIN emp_pop_depot epd ON e.id = epd.employe_id
  INNER JOIN pai_ref_mois prm ON prm.anneemois=_anneemois_id
  LEFT OUTER JOIN pai_png_medicalvisite ppm ON e.saloid=ppm.medicmatricule and ppm.medicprochain in (select max(ppm2.medicprochain) from pai_png_medicalvisite ppm2 where e.saloid=ppm2.medicmatricule)
  WHERE epd.depot_id =_depot_id AND epd.flux_id =_flux_id
  AND epd.date_fin>=prm.date_debut and epd.date_debut<=prm.date_fin
  ORDER BY e.nom,e.prenom1,e.prenom2
  ;
end;
