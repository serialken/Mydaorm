/*
call req_employe_carte_sejour(10,1,'201612');
*/
drop procedure req_employe_carte_sejour;
create procedure req_employe_carte_sejour(_depot_id int, _flux_id int, _anneemois_id varchar(6))
comment 'Cartes de séjour'
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
  , rn.libelle as "Nationalité__ro__false__120__left__str"
  , date_format(se.begin_date,'%d/%m/%Y') as "Date d'effet__ro__false__80__center__sortDate"
  , date_format(se.end_date,'%d/%m/%Y') as "Date de fin de validité__ro__false__80__center__sortDate"
  , se.sejourcarte as "Type de carte__ro__false__200__left__str"
  , se.etrangnumero as "Numéro du document__ro__false__100__left__str"
  , se.administration as "Autorité de délivrance__ro__false__120__left__str"
  , se.etranglieu as "Lieu de délivrance__ro__false__120__left__str"
  , se.etrangduree as "Durée du titre de séjour__ro__false__70__left__str"
  , se.etrangactivite as "Activités autorisées__ro__false__*__left__str"
  FROM employe e
  INNER JOIN ref_nationalite rn on e.nationalite_id=rn.id
  INNER JOIN emp_pop_depot epd ON e.id = epd.employe_id
  INNER JOIN pai_ref_mois prm ON prm.anneemois=_anneemois_id
  LEFT OUTER JOIN pai_png_saletranger se ON e.saloid=se.etrangmatricule -- and se.end_date>=prm.date_debut
  WHERE epd.depot_id =_depot_id AND epd.flux_id =_flux_id
  AND epd.date_fin>=prm.date_debut and epd.date_debut<=prm.date_fin
  AND rn.appartenanceue=0 and rn.code not in ('IS','NO','LI','CH')
  ORDER BY e.nom,e.prenom1,e.prenom2,se.end_date desc
  ;
end;
