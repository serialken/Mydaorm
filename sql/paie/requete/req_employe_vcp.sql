drop procedure req_employe_vcp;
create procedure req_employe_vcp(_depot_id int, _flux_id int)
comment 'vcp'
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
    e.id as "id__ro__true"
  , e.matricule as "Matricule__ro__false__70"
  , e.nom as "Nom__ro__false__120__left__str__#text_filter"
  , e.prenom1 as "Prénom__ro__false__120__left__str__#text_filter"
  , e.secu_numero as "Numéro sécu__ro__false__100"
  , e.secu_cle as "Clé sécu__ro__false__40"
  , date_format(e.naissance_date,'%d/%m/%Y') as "Date de naissance__ro__false__70"
--  , e.naissance_lieu
  , coalesce(pric.libelle,prip.pays) as "Lieu de naissance__ro__false__120"
  , date_format(e.sejour_date,'%d/%m/%Y') as "Date carte de sejour__ro__false__70"
  , ea.numerovoie as "Numéro__ro__false__50"
  , ea.naturevoie "Voie__ro__false__50__left"
  , ea.cpltadr1 "Complément Adresse__ro__false__120__left"
--  , ea.cpltadr2
  , ea.nomvoie1 "Adresse__ro__false__120__left"
--  , ea.nomvoie2
--  , ea.nomvoie3
  , ea.codepostal as "Code Postal__ro__false__50"
  , ea.codeinsee as "Code Insee__ro__false__50"
  , ea.ville "Ville__ro__false__120__left"
  , eb.iban "Iban__ro__false__200__left"
  , eb.bic "Bic__ro__false__100__left"
  , cycle_to_string(ec.lundi,ec.mardi,ec.mercredi,ec.jeudi,ec.vendredi,ec.samedi,ec.dimanche) as "Cycle__ro__false__70__center"
  from employe e
  inner join emp_pop_depot epd on epd.employe_id=e.id
  left outer join emp_adresse ea on e.id=ea.employe_id
  left outer join emp_banque eb on e.id=eb.employe_id
  left outer join emp_cycle ec on e.id=ec.employe_id
  left outer join pai_ref_insee_commune pric on e.naissance_lieu=pric.code
  left outer join pai_ref_insee_pays prip on e.naissance_pays=prip.code
  where epd.population_id=-1
  and (depot_id=_depot_id or _depot_id is null)
  and (flux_id=_flux_id or _flux_id is null)
  ;
end;
