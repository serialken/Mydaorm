select * from pai_tournee where date_distrib='2014-09-04' and depot_id=14;

select * from client_a_servir_logist where date_distrib='2014-09-04' and depot_id=14;

select * from employe where matricule='MEM7833220';
select * from employe where matricule='Z004024400';
select * from emp_pop_depot where employe_id in (1755,70);
select * from pai_int_traitement;

select * from pai_oct_pers where pers_mat like 'Z%20' or  pers_mat like '7%20';
select * from employe e
left outer join emp_pop_depot epd on e.id=epd.employe_id
left outer join emp_cycle ec on e.id=ec.employe_id
where matricule like 'Z%20' or  matricule like '7%20';

select * from pai_oct_pers where pers_mat like 'Z%20' or  pers_mat like '7%20';
select * from employe e
inner join emp_delete d on e.id=d.id
left outer join emp_pop_depot epd on e.id=epd.employe_id
left outer join emp_cycle ec on e.id=ec.employe_id
;

-- Corriger alim doublon sur le 04/09 !!!
select * from pai_tournee where code='034NND022JE';

drop table emp_delete;
create table emp_delete as select id from employe where matricule=saloid;
delete from modele_journal where employe_id in (select id from emp_delete);
delete from pai_journal where employe_id in (select id from emp_delete);
select distinct e.*,gt.depot_id from modele_tournee mt inner join groupe_tournee gt on mt.groupe_id=gt.id inner join emp_delete d on mt.employe_id=d.id inner join employe e on d.id=e.id;

delete e from employe e inner join emp_delete d on e.id=d.id;
select * from employe where nom='SOSSOU';

 select *
from employe e1
inner join employe e2 on e1.nom=e2.nom and e1.prenom1=e2.prenom1 and e1.matricule<e2.matricule and substr(e1.matricule,1,8)<>substr(e2.matricule,1,8)
-- and e1.matricule like 'Z%' and (e2.matricule like 'M%' or e2.matricule like 'N%')
/*and ((e1.matricule like '%00' and e2.matricule like '%00')
or (e1.matricule like '%20' and e2.matricule like '%20'))*/
order by e1.nom;


delete from emp_pop_depot
where employe_id in (select id from emp_delete);
delete from pai_journal
where employe_id in (select id from emp_delete);
delete from employe where id in (select id from emp_delete);
select * from emp_delete e where exists(select null from pai_activite a where e.id=a.employe_id);

-- employe sans contrat
select e.*
from employe e
left outer join pai_png_relationcontrat rc on e.saloid=rc.relatmatricule
-- left outer join pai_png_rejet r on e.matricule=r.matricule
left outer join pai_png_info i on e.matricule=i.matricule
where  e.matricule<>e.saloid and e.id not in (select employe_id from emp_pop_depot);

delete e
from employe e
where  e.matricule<>e.saloid 
and not exists(select null from emp_pop_depot f where f.employe_id=e.id)
and not exists(select null from modele_tournee f where f.employe_id=e.id)
and not exists(select null from modele_tournee_jour f where f.employe_id=e.id)
and not exists(select null from modele_activite f where f.employe_id=e.id)
and not exists(select null from pai_tournee f where f.employe_id=e.id);

select *
from employe e
where  e.matricule<>e.saloid 
and e.id  not in (select employe_id from emp_pop_depot)
and e.id  in (select employe_id from modele_tournee)
and e.id  in (select employe_id from modele_tournee_jour)
and e.id  in (select employe_id from modele_activite)
and e.id  in (select employe_id from pai_tournee);

select employe_id from emp_pop_depot where employe_id=509;
select employe_id from modele_tournee where employe_id=509;
select * from modele_tournee_jour where employe_id=509;
select employe_id from modele_activite where employe_id=509;
select * from pai_tournee where employe_id=509;

-- employe sans cycle
select * from employe where  matricule<>saloid and id not in (select employe_id from emp_pop_depot);
select * from employe where  matricule<>saloid and id not in (select employe_id from emp_cycle);
select * from emp_pop_depot where employe_id=6792;
delete from employe where id=149;
select * from employe where id=54;
select * from employe where nom='KAMARA';
select * from emp_pop_depot where employe_id=891;