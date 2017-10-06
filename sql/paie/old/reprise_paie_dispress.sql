
rollback
-- --------------------------------------------------------------------------
-- calcul des bases de cotisation
-- --------------------------------------------------------------------------
update pai_ev_dispress e 
set 
e.qte=round(e.qte/100,2)
where e.postepaie in ('38000','38100')
and coalesce(e.taux,0)<>6.23;

update pai_ev_dispress e 
set 
e.taux=6.23
,e.mnt=round(e.qte*6.23,2)
where e.postepaie in ('38000');
-- --------------------------------------------------------------------------
-- valorisation des heures
-- --------------------------------------------------------------------------
update pai_ev_dispress e 
set 
e.taux=9.53
,e.mnt=round(e.qte*9.53,2)
where e.postepaie in ('95054','95055','95060','95061','95037','95040');
-- valorisation des heures de nuit
update pai_ev_dispress e 
set 
e.taux=0.095
,e.mnt=round(e.qte*0.095,2)
where e.postepaie in ('95027');
-- --------------------------------------------------------------------------
-- remuneration anciennet�
-- ----------------------------------------------------------------------------
-- select t.emp_dst,a.ancCtrMaladieW,emp.*,epd.rc,c.oid,e.* 
 /*select e.matricule
from pai_ev_dispress e
left outer join emp_transco t on concat('NE',e.matricule,'00')=t.mat_org
left outer join employe emp on coalesce(t.mat_dst,concat('NE',e.matricule,'00'))=emp.matricule
left outer join emp_pop_depot epd on emp.id=epd.employe_id and '2014-10-01' between epd.date_debut and epd.date_fin
left outer join pai_png_contrat c on epd.rcoid=c.ctrrelation
left outer join pai_png_anciennetectr a on c.oid=a.ancctrmatricule
where e.postepaie in ('51315')
and ancCtrMaladieW is null
order by e.matricule;*/

 select e.matricule,t.*,epd.*
from pai_ev_dispress e
left outer join emp_transco t on concat('ME',e.matricule,'20')=t.mat_org
left outer join employe emp on coalesce(t.mat_dst2,concat('ME',e.matricule,'20'))=emp.matricule
left outer join emp_pop_depot epd on emp.id=epd.employe_id and '2014-10-01' between epd.date_debut and epd.date_fin
left outer join pai_png_contrat c on epd.rcoid=c.ctrrelation
left outer join pai_png_anciennetectr a on c.oid=a.ancctrmatricule
where e.postepaie in ('51315')
and ancCtrMaladieW is null
order by e.matricule;

delete from pai_ev_dispress where postepaie like '30040';
insert into pai_ev_dispress(matricule,postepaie,qte,taux,mnt)
select e.matricule,'30040',null
,case 
  when ancCtrMaladieW between '2012-10-01' and '2013-10-01' then 0.152
  when ancCtrMaladieW < '2012-10-01' then 0.434
  else 0
  end
,case 
  when ancCtrMaladieW between '2012-10-01' and '2013-10-01' then round(0.152*e.qte,2)
  when ancCtrMaladieW < '2012-10-01' then round(0.434*e.qte,2)
  else 0
  end
from pai_ev_dispress e
left outer join emp_transco t on concat('ME',e.matricule,'20')=t.mat_org
left outer join employe emp on coalesce(t.mat_dst2,concat('ME',e.matricule,'20'))=emp.matricule
left outer join emp_pop_depot epd on emp.id=epd.employe_id and '2014-10-01' between epd.date_debut and epd.date_fin
left outer join pai_png_contrat c on epd.rcoid=c.ctrrelation
left outer join pai_png_anciennetectr a on c.oid=a.ancctrmatricule
where e.postepaie in ('51315');
-- select * from pai_ev_dispress e where e.postepaie in ('51315');
-- --------------------------------------------------------------------------
-- 10eme des cong�s pay�s BF
-- --------------------------------------------------------------------------
delete from pai_ev_dispress where postepaie like '40000';
insert into pai_ev_dispress(matricule,postepaie,qte,taux,mnt)
select e.matricule,'40000',null,null,round(sum(mnt)/10,2)
from pai_ev_dispress e 
where  e.postepaie in ('95027','95050','95052','95054','96100','30040')
group by e.matricule;
;
-- --------------------------------------------------------------------------
-- 10eme des cong�s pay�s BR
-- --------------------------------------------------------------------------
delete from pai_ev_dispress where postepaie like '40020';
insert into pai_ev_dispress(matricule,postepaie,qte,taux,mnt)
select e.matricule,'40020',null,null,round(sum(mnt)/10,2)
from pai_ev_dispress e 
where  e.postepaie in ('95046','95056','95058','95060')
group by e.matricule;
-- --------------------------------------------------------------------------
-- calcul du brut
-- --------------------------------------------------------------------------
delete from pai_ev_dispress where postepaie like '59900';
insert into pai_ev_dispress(matricule,postepaie,mnt)
select e.matricule,'59900',round(sum(e.mnt),2) 
from pai_ev_dispress e 
where  e.postepaie in (
'95052','95058','95050','95056','95046','96100',
'95054','95055','95060','95061','95037','95040', -- valorisation des heures
'95027', -- valorisation des heures de nuit
'30040', -- remuneration anciennet�
'40000','40020'
)
group by e.matricule;
-- --------------------------------------------------------------------------
-- calcul des bases de cotisation
-- --------------------------------------------------------------------------
delete from pai_ev_dispress where postepaie like '90000';
insert into pai_ev_dispress(matricule,postepaie,qte,taux,mnt)
select matricule,'90000',sum(e.mnt),0.158,-round(sum(e.mnt)*0.158,2)
from pai_ev_dispress e 
where postepaie in ('38000')
group by e.matricule;

delete from pai_ev_dispress where postepaie like '90010';
insert into pai_ev_dispress(matricule,postepaie,qte,taux,mnt)
select matricule,'90010',sum(e.mnt),0.158,-round(sum(e.mnt)*0.158,2)
from pai_ev_dispress e 
where postepaie in ('95046','95056','95058','95060','10BR')
group by e.matricule;

delete from pai_ev_dispress where postepaie like '90020';
insert into pai_ev_dispress(matricule,postepaie,qte,taux,mnt)
select matricule,'90020',sum(e.mnt),0.067,-round(sum(e.mnt)*0.067,2)
from pai_ev_dispress e 
where postepaie in ('59900')
group by e.matricule;
-- --------------------------------------------------------------------------
-- calcul du net
-- --------------------------------------------------------------------------
delete from pai_ev_dispress where postepaie like '99999';
insert into pai_ev_dispress(matricule,postepaie,mnt)
select ebrut.matricule,'99999',round(ebrut.mnt+e64200.mnt+ebasebr.mnt+ebasebf.mnt+ebasebrut.mnt,2)
from pai_ev_dispress ebrut 
left outer join pai_ev_dispress ebasebr on ebrut.matricule=ebasebr.matricule and ebasebr.postepaie in ('90000')
left outer join pai_ev_dispress ebasebf on ebrut.matricule=ebasebf.matricule and ebasebf.postepaie in ('90010')
left outer join pai_ev_dispress ebasebrut on ebrut.matricule=ebasebrut.matricule and ebasebrut.postepaie in ('90020')
left outer join pai_ev_dispress e64200 on ebrut.matricule=e64200.matricule and e64200.postepaie in ('64200')
where ebrut.postepaie in ('59900')
;

-- --------------------------------------------------------------------------
select e.matricule,concat(coalesce(r.prefix,0),e.postepaie) as Code,r.libelle as Rubrique,e.qte as "Quantite Base",e.taux as "Taux",e.mnt as "Montant"
from pai_ev_dispress e
left outer join pai_ref_postepaie_dispress r on e.postepaie=r.poste
-- where e.postepaie  not in ('38000','38100')
order by matricule,2;

/*
select 'Ratio total',coalesce(e1.matricule,e2.matricule) as matricule, round(coalesce(e1.qte,0)*100/(e1.qte+e2.qte),2) as "Taux BF", round(coalesce(e2.qte,0)*100/(e1.qte+e2.qte),2) as "Taux BR"
from pai_ev_dispress e1
left join pai_ev_dispress e2 on e1.matricule=e2.matricule and e2.postepaie in ('38100')
where e1.postepaie in ('38000')
;

*/
select count(*) from pai_ev_dispress