CALL mod_valide_activite(@id,NULL);
CALL mod_valide_tournee(@id,NULL);
CALL mod_valide_tournee_jour(@id,NULL);
CALL mod_valide_rh(@id,null,null);

call pai_valide_activite(@id,null,null);
call pai_valide_tournee(@id,null,null);
call pai_valide_produit(@id,null,null);
call pai_valide_rh(@id,null,null);

commit;

explain
select *
  FROM pai_journal pj
	WHERE pj.date_extrait is null
  AND pj.rubrique='AL' AND pj.code='AM';
	AND (pj.date_distrib=_date_distrib OR _date_distrib IS NULL)
	AND ((_produit_id IS NULL AND pj.produit_id IS NOT NULL) OR produit_id=_produit_id);
  
  
  select count(*) from pai_journal;
  select * 
  from pai_tournee pt
  where pt.tournee_org_id is not null and pt.split_id is null
  and (pt.nbtitre,pt.nbspl,pt.nbprod,pt.nbcli,pt.nbrep) not in (select sum(ptf.nbtitre),sum(ptf.nbspl),sum(ptf.nbprod),sum(ptf.nbcli),sum(ptf.nbrep)
                                          from pai_tournee ptf
                                          where pt.tournee_org_id=ptf.tournee_org_id and ptf.split_id is not null
                                          )
  ;
  select * 
  from pai_prd_tournee ppt
  inner join pai_tournee pt on ppt.tournee_id=pt.id
  where pt.tournee_org_id is not null and pt.split_id is null
  and (ppt.qte,ppt.nbcli,ppt.nbrep) not in (select sum(pptf.qte),sum(pptf.nbcli),sum(pptf.nbrep)
                                          from pai_prd_tournee pptf
                                          inner join pai_tournee ptf on pptf.tournee_id=ptf.id
                                          where pt.tournee_org_id=ptf.tournee_org_id and ptf.split_id is not null
                                          and ppt.produit_id=pptf.produit_id and ppt.natureclient_id=pptf.natureclient_id
                                          )
  ;
  select * from pai_tournee where tournee_org_id = 58062;