drop procedure req_modele_tournee_modification;
create procedure req_modele_tournee_modification(_depot_id int, _flux_id int)
comment 'modification des modèles de tournée'
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
    mt.id as "id__ro__true"
  , mt.code as "Code__ro__false__80__left__str__#select_filter"
  , u.nom as "Nom__ro__false__120__left__str__#text_filter"
  , u.prenom
  , date_format(min(mtj.date_debut),'%d/%m/%Y') as "date_debut__ro__false__80__center__date__#text_filter"
  , date_format(max(mtj.date_fin),'%d/%m/%Y') as "date_fin__ro__false__80__center__date__#text_filter"
  , date_format(min(mtj.date_creation),'%d/%m/%Y %H:%i:%s') as "min_date_creation__ro__false__120__center__date__#text_filter"
  , date_format(max(mtj.date_creation),'%d/%m/%Y %H:%i:%s') as "max_date_creation__ro__false__120__center__date__#text_filter"
  , date_format(min(mtj.date_modif),'%d/%m/%Y %H:%i:%s') as "min_date_modif__ro__false__120__center__date__#text_filter"
  , date_format(max(mtj.date_modif),'%d/%m/%Y %H:%i:%s') as "max_date_modif__ro__false__120__center__date__#text_filter"
  from modele_tournee_jour mtj
  inner join modele_tournee mt on mt.id=mtj.tournee_id
  inner join groupe_tournee gt on mt.groupe_id=gt.id
  inner join utilisateur u on mtj.utilisateur_id=u.id
  where (depot_id=_depot_id or _depot_id is null)
  and (flux_id=_flux_id or _flux_id is null)
  -- and mtj.utilisateur_id=134
  and mtj.date_fin='2999-01-01'
  /*
  and( mtj.code like '%B021%'
  or mtj.code like '%B013%'
  or mtj.code like '%C001%'
  or mtj.code like '%C002%'
  or mtj.code like '%C003%'
  or mtj.code like '%C005%'
  or mtj.code like '%C006%'
  or mtj.code like '%C014%'
  or mtj.code like '%D012%'
  or mtj.code like '%D027%'
  )*/
  group by mt.code,u.nom,u.prenom
  order by mt.code;
end;
