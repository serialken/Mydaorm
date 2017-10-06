update client_a_servir_logist set pai_tournee_id=null where pai_tournee_id is not null and depot_id=24;
update client_a_servir_logist set pai_tournee_id=null where pai_tournee_id  in (select id from pai_tournee where depot_id=24);
delete from pai_journal where tournee_id in (select id from pai_tournee where depot_id=24);
delete from pai_journal where depot_id=24;
delete from pai_prd_tournee where tournee_id in (select id from pai_tournee where depot_id=24);
delete from pai_tournee where depot_id=24;

     select *  from pai_journal pj where tournee_id in ( select id from pai_tournee pt where date_distrib='2014-08-22'
             and pt.depot_id=24 and pt.flux_id=2 and pj.depot_id<>24);
             
select p.libelle,sum(l.qte) 
from client_a_servir_logist l
inner join produit p on l.produit_id=p.id
where l.date_distrib='2014-08-22' and l.depot_id=24 and l.flux_id=1
group by p.libelle;

select p.libelle,sum(l.qte) 
from client_a_servir_logist l
inner join produit p on l.produit_id=p.id
inner join modele_tournee_jour mtj on l.tournee_jour_id=mtj.id
inner join modele_tournee mt on mtj.tournee_id=mt.id
inner join groupe_tournee gt on mt.groupe_id=gt.id
where l.date_distrib='2014-08-22' and l.depot_id=24 and l.flux_id=1
and gt.depot_id=24 and gt.flux_id=1
group by p.libelle;

select p.libelle,sum(l.qte) 
from client_a_servir_logist l
inner join produit p on l.produit_id=p.id
inner join pai_tournee pt on l.pai_tournee_id=pt.id
where l.date_distrib='2014-08-22' and l.depot_id=24 and l.flux_id=1
and pt.depot_id=24 and pt.flux_id=1
group by p.libelle;

select p.id,p.libelle,sum(ppt.qte) 
from pai_tournee pt 
inner join pai_prd_tournee ppt on ppt.tournee_id=pt.id 
inner join produit p on ppt.produit_id=p.id
where pt.date_distrib='2014-08-22' and pt.depot_id=24 and pt.flux_id=1
group by p.id,p.libelle;

select pt.*
from pai_tournee pt
inner join pai_prd_tournee ppt on ppt.tournee_id=pt.id 
where pt.date_distrib='2014-08-22' and pt.depot_id=24 and pt.flux_id=1
;


select 
             /*   l.pai_tournee_id
                ,*/l.produit_id
                ,l.client_type
                ,pt.typetournee_id
                ,sum(l.qte)
                ,case when pt.typetournee_id=1 then count(distinct l.abonne_soc_id) else sum(l.qte) end
                ,case when p.type_id in (2,3) then 0 else count(distinct l.abonne_soc_id) end
                ,case when p.type_id in (2,3) then 0 else count(distinct l.abonne_unique_id) end
                ,case when p.type_id in (2,3) then 0 else count(distinct l.adresse_id) end
            from client_a_servir_logist l
            inner join pai_tournee pt on l.pai_tournee_id=pt.id
            inner join produit p on l.produit_id=p.id
            where l.date_distrib='2014-08-22'
             and pt.depot_id=24 and pt.flux_id=1
            AND l.pai_tournee_id IS NOT NULL
            AND l.type_service='L'
            and l.produit_id=101
            group by 
                /*l.pai_tournee_id
                ,*/l.produit_id
                ,l.client_type
                ,pt.typetournee_id
                ;
                select 
             /*   l.pai_tournee_id
                ,*/l.produit_id
                ,l.client_type
                ,sum(l.qte)
            from client_a_servir_logist l
            inner join pai_tournee pt on l.pai_tournee_id=pt.id
            inner join produit p on l.produit_id=p.id
            where l.date_distrib='2014-08-22'
             and pt.depot_id=24 and pt.flux_id=1
            AND l.pai_tournee_id IS NOT NULL
            AND l.type_service='L'
            and l.produit_id=101
            group by 
                /*l.pai_tournee_id
                ,*/l.produit_id
                ,l.client_type
                ,pt.typetournee_id
                ;
                
           