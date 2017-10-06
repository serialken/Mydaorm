  -- passe le nombre de client à 0 pour les suppléments.
  select * from pai_prd_tournee ppt inner join produit p on ppt.produit_id=p.id where type_id in (2,3);
/*
  update pai_prd_tournee ppt inner join produit p on ppt.produit_id=p.id set ppt.nbcli=0,ppt.nbcli_unique=0,ppt.nbadr=0 where type_id in (2,3);
UPDATE pai_tournee pt
                SET pt.nbcli	=COALESCE((SELECT SUM(ppt.nbcli) FROM pai_prd_tournee ppt INNER JOIN produit p ON ppt.produit_id=p.id WHERE ppt.tournee_id=pt.id AND p.type_id not in (2,3)),0)
                , pt.nbcli_unique=COALESCE((SELECT SUM(ppt.nbcli_unique) FROM pai_prd_tournee ppt INNER JOIN produit p ON ppt.produit_id=p.id WHERE ppt.tournee_id=pt.id AND p.type_id not in (2,3)),0)
                , pt.nbadr	=COALESCE((SELECT SUM(ppt.nbadr) FROM pai_prd_tournee ppt INNER JOIN produit p ON ppt.produit_id=p.id WHERE ppt.tournee_id=pt.id AND p.type_id not in (2,3)),0)
                , pt.nbtitre	=COALESCE((SELECT SUM(ppt.qte) FROM pai_prd_tournee ppt INNER JOIN produit p ON ppt.produit_id=p.id WHERE ppt.tournee_id=pt.id AND p.type_id IN (1)),0)
                , pt.nbspl	=COALESCE((SELECT SUM(ppt.qte) FROM pai_prd_tournee ppt INNER JOIN produit p ON ppt.produit_id=p.id WHERE ppt.tournee_id=pt.id AND p.type_id IN (2,3)),0)
                , pt.nbprod	=COALESCE((SELECT SUM(ppt.qte) FROM pai_prd_tournee ppt INNER JOIN produit p ON ppt.produit_id=p.id WHERE ppt.tournee_id=pt.id AND p.type_id NOT IN (1,2,3)),0)
                , pt.duree_supplement=COALESCE((select sum(ppt.duree_supplement) from pai_prd_tournee ppt INNER JOIN produit p ON ppt.produit_id=p.id WHERE ppt.tournee_id=pt.id AND p.type_id IN (2,3)),0)
                , pt.poids	=COALESCE((SELECT SUM(ppt.poids) FROM pai_prd_tournee ppt WHERE ppt.tournee_id=pt.id),0);
               */   
               
  select * from prd_caract
  
  select * 
  from prd_caract_constante pcc
  inner join produit p on pcc.produit_id=p.id
  where prd_caract_id=14;
  
  select p.id,libelle,flux_id,valeur_string
  from produit p
  left outer join prd_caract_constante pcc on pcc.produit_id=p.id and prd_caract_id=14;
  
  -- produit sans type urssaf
  select * 
  from produit p
  inner join produit_type pt on p.type_id=pt.id and not pt.hors_presse
  where not exists(select null from prd_caract_constante pcc
  inner join prd_caract pc on p.type_id=pc.produit_type_id and pc.code='URSSAF'
  where pcc.prd_caract_id=pc.id and pcc.produit_id=p.id);
  -- Ajout des type urssaf manquant
  insert into prd_caract_constante(produit_id,prd_caract_id,utilisateur_id,valeur_string,date_creation)
  select p.id,14,0,'R',sysdate()
  from produit p
  inner join produit_type pt on p.type_id=pt.id and not pt.hors_presse
  where not exists(select null from prd_caract_constante pcc
  where prd_caract_id=14 and pcc.produit_id=p.id);
  -- liste des type urssaf incorrects
  select * 
  from prd_caract_constante pcc
  inner join produit p on pcc.produit_id=p.id
  where prd_caract_id=14 and valeur_string not in ('F','R');
  -- liste des type urssaf incorrects
  update prd_caract_constante pcc
  set valeur_string='R'
  where prd_caract_id=14 and valeur_string not in ('F','R');

-- delete from prd_caract_constante where prd_caract_id=21;