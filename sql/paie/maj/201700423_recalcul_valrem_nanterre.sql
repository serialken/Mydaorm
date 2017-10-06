select * from depot;
-- 10	1771		028	CD28 NANTERRE	1 BD DES BOUVETS	01/01/2013 00:00:00		28/10/2014 17:38:06	2,2205900	48,8979790	1	20
select * from pai_tournee where depot_id=10 and flux_id=2 and date_extrait is not null order by date_distrib desc;
select distinct date_extrait from pai_tournee where depot_id=10 and flux_id=2 and date_extrait is not null order by date_distrib desc;
-- date_extrait=24/03/2017 14:12:48
create table tmp_pai_tournee_10_2 as select * from pai_tournee where depot_id=10 and flux_id=2 and date_extrait='2017-03-24 14:12:48';
drop table tmp_pai_activite_10_2;
create table tmp_pai_activite_10_2 as select * from pai_activite where depot_id=10 and flux_id=2 and date_extrait='2017-03-24 14:12:48';
create table tmp_pai_majoration_10_2 as select * from pai_majoration where depot_id=10 and flux_id=2 and date_extrait='2017-03-24 14:12:48';


update pai_tournee pt inner join tmp_pai_tournee_10_2 tpt on tpt.id=pt.id set pt.date_extrait=null;
update pai_activite pt inner join tmp_pai_activite_10_2 tpt on tpt.id=pt.id set pt.date_extrait=null;
update pai_majoration pt inner join tmp_pai_majoration_10_2 tpt on tpt.id=pt.id set pt.date_extrait=null;

update pai_tournee pt
inner join modele_tournee_jour mtj on pt.modele_tournee_jour_id=mtj.id
inner join tmp_pai_tournee_10_2 tpt on tpt.id=pt.id
set pt.nbkm=mtj.nbkm
,   pt.nbkm_paye=mtj.nbkm_paye;

call recalcul_tournee_date_distrib(null,10,2);

update pai_tournee pt inner join tmp_pai_tournee_10_2 tpt on tpt.id=pt.id set pt.date_extrait='2017-03-24 14:12:48';
update pai_activite pt inner join tmp_pai_activite_10_2 tpt on tpt.id=pt.id set pt.date_extrait='2017-03-24 14:12:48';
update pai_majoration pt set pt.date_extrait='2017-03-24 14:12:48' where depot_id=10 and flux_id=2 and date_distrib between '2017-02-21' and '2017-03-20';

select * from tmp_pai_majoration_10_2 tpm
where not exists(select null from pai_majoration pm where tpm.employe_id=pm.employe_id and tpm.date_distrib=pm.date_distrib);

select pt.code,pt.date_distrib,pt.valrem,tpt.valrem,tpt.valrem_majoree,tpt.valrem_majoree,pt.valrem_paie,tpt.valrem_paie,pt.valrem_logistique,tpt.valrem_logistique,pt.valrem_org,tpt.valrem_org,pt.duree,tpt.duree,pt.nbkm_paye,tpt.nbkm_paye
from tmp_pai_tournee_10_2 tpt
inner join pai_tournee pt on tpt.id=pt.id
order by left(pt.code,9),2

    set @idtrt=null;
    call INT_MROAD2EV_HISTORIQUE(@idtrt,9795);
    select @idtrt;

select * from v_employe

select ped.*,null as difference,e.nom,e.prenom1,e.prenom2,epd.nbheures_garanties
from pai_ev_diff ped 
inner join employe e on ped.matricule=e.matricule
left outer join emp_pop_depot epd on e.id = epd.employe_id and ped.datev between epd.date_debut and epd.date_fin
where ped.idtrt1=9795 and ped.idtrt2=10090  and diff<>'='
union
select ped.idtrt1,ped.idtrt2,null,ped.typev,ped.matricule,ped.rc,ped.poste,ped.datev,998,concat(ped.libelle,' TOTAL'),sum(ped.qte1),sum(ped.qte2),null,null,sum(ped.val1),sum(ped.val2),sum(ped.val2)-sum(ped.val1) as difference,e.nom,e.prenom1,e.prenom2,epd.nbheures_garanties
from pai_ev_diff ped
inner join employe e on ped.matricule=e.matricule
left outer join emp_pop_depot epd on e.id = epd.employe_id and ped.datev between epd.date_debut and epd.date_fin
where ped.idtrt1=9795 and ped.idtrt2=10090  and diff<>'=' 
and ped.poste not in ('0560','HJPX','HTPX')
group by ped.idtrt1,ped.idtrt2,ped.matricule,ped.rc,ped.datev,e.id,epd.id,ped.libelle 
union
select ped.idtrt1,ped.idtrt2,null,'TOTAL',ped.matricule,ped.rc,null,ped.datev,999,'TOTAL',null,null,null,null,sum(ped.val1) as avant,sum(ped.val2) as apres,sum(ped.val2)-sum(ped.val1) as difference,e.nom,e.prenom1,e.prenom2,epd.nbheures_garanties
from pai_ev_diff ped
inner join employe e on ped.matricule=e.matricule
left outer join emp_pop_depot epd on e.id = epd.employe_id and ped.datev between epd.date_debut and epd.date_fin
where ped.idtrt1=9795 and ped.idtrt2=10090  and diff<>'=' 
and ped.poste not in ('0560','HJPX','HTPX')
group by ped.idtrt1,ped.idtrt2,ped.matricule,ped.rc,ped.datev,e.id,epd.id
order by 5,8,10,9

select e.matricule,e.nom,e.prenom1,e.prenom2,ped.rc,datev,sum(val1) as avant,sum(val2) as apres,sum(val2)-sum(val1) as difference ,epd.nbheures_garanties
from pai_ev_diff ped
inner join employe e on ped.matricule=e.matricule
left outer join emp_pop_depot epd on e.id = epd.employe_id and ped.datev between epd.date_debut and epd.date_fin
where idtrt1=9795 and idtrt2=10090  and diff<>'=' 
and poste not in ('0560','HJPX','HTPX')
group by ped.idtrt1,ped.idtrt2,ped.matricule,ped.rc,ped.datev,e.id,epd.id
order by 1,5,6