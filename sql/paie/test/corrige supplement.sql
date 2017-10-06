-- select tt.date_distrib,tt.code,s.tournee_id,ps.libelle,pt.libelle,s.qte as sup,t.qte,t.nbcli
update pai_prd_tournee s
inner join pai_prd_tournee t on t.tournee_id=s.tournee_id
inner join produit ps on s.produit_id=ps.id
inner join produit pt on t.produit_id=pt.id
inner join pai_tournee tt on s.tournee_id=tt.id
set s.qte=t.nbcli
where ps.type_id in (2,3)
and pt.type_id in (1)
and ps.societe_id=pt.societe_id
and s.qte=t.qte and s.qte<>t.nbcli;



select tt.date_distrib,tt.code,s.tournee_id,ps.libelle,pt.libelle,s.qte as sup,t.qte,t.nbcli,tt.date_distrib,tt.flux_id,tt.date_extrait
from pai_prd_tournee s
inner join pai_prd_tournee t on t.tournee_id=s.tournee_id
inner join produit ps on s.produit_id=ps.id
inner join produit pt on t.produit_id=pt.id
inner join pai_tournee tt on s.tournee_id=tt.id
where ps.type_id in (2,3)
and pt.type_id in (1)
and ps.societe_id=pt.societe_id
and s.qte>=t.qte 
and s.qte<>t.nbcli
and tt.date_extrait is null