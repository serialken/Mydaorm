/*
call req_employe_carte_sejour(10,1,'201612');
*/
drop procedure req_modele_etalon_attente;
create procedure req_modele_etalon_attente(_depot_id int, _flux_id int)
comment 'Etalon en attente de validation'
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
  -- libelle__type__hidden__width__align__sort__search"

  SELECT 
      concat('<a href="javascript:goLinkGrid(''../etalon/liste-etalon?'',''depot_id=',me.depot_id,'&flux_id=',me.flux_id,'&etalon_id=',me.id,''',true);" data-content="lien" onMouseOver="affPopoverLien(this)"><img src="/images/dhtmlx_contrat.png"></a>') as "Lien__ro__false__50__center__str",
     rte.libelle as "Type__ro__false__80__left__str",
      d.code as "Dépot__ro__false__60__center__str",
      f.libelle as "Flux__ro__false__60__center__str",
      concat_ws(' ',e.nom,e.prenom1,e.prenom2) as "Employé__ro__false__*__left__str",
      group_concat(distinct mt.code order by mt.code separator ' ') as "Tournée__ro__false__*__left__str",
      date_format(me.date_requete,'%d/%m/%Y') as "Date de requête__ro__false__80__center__sortDate",
      date_format(me.date_application,'%d/%m/%Y') as "Date d'application__ro__false__80__center__sortDate",
      concat_ws(' ',u.nom,u.prenom) as  "Demandeur__ro__false__*__left__str",
      date_format(me.date_demande,'%d/%m/%Y %H:%i:%s') as "Date de Demande__ro__false__120__center__sortDate",
      me.commentaire
  FROM etalon me
  LEFT OUTER JOIN employe e on me.employe_id=e.id
  LEFT OUTER JOIN etalon_tournee et on me.id=et.etalon_id
  inner join depot d on me.depot_id=d.id
  inner join ref_flux f on me.flux_id=f.id
  inner join ref_typeetalon rte on me.type_id=rte.id
  LEFT OUTER JOIN modele_tournee mt on et.modele_tournee_id=mt.id
  LEFT OUTER JOIN utilisateur u ON me.demandeur_id=u.id
  WHERE me.date_demande is not null and me.date_validation is null and me.date_refus is null
  GROUP BY me.id
  ORDER BY me.date_requete desc,me.date_modif desc
  ;
end;
