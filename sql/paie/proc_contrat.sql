/*
select * from v_employe where nom='LANVIN' order by nom;
select * from v_employe where matricule like 'MR%' and date_fin='2999-01-01' order by nom;
select * from employe where matricule like 'MR%' order by nom;

call emp_contrat_fermeture(10325,'2016-12-04')
Mr Sella 9 /02/17
call emp_contrat_fermeture(10311,'2017-02-09')
Mr Diarra 9/02/17
call emp_contrat_fermeture(10328,'2017-02-09')
Mr Noui Samir 01/11/16
call emp_contrat_fermeture(10319,'2016-11-01')
Mr Rortais 20 /02/17
call emp_contrat_fermeture(10330,'2017-02-20')
Mme NADER Leila 03/03/17
call emp_contrat_fermeture(10309,'2017-03-03')
Mme MANAI Mohamed Jihad 21/04/17
call emp_contrat_fermeture(10326,'2017-04-20')
Mr LANVIN 13/05/2017
call emp_contrat_fermeture(10298,'2017-05-13')
Mr COULIBALY Gay 27/05/2017
call emp_contrat_fermeture(10327,'2017-05-27')
select * from pai_tournee where employe_id=10327 order by date_distrib desc

set @id=null;
call pai_valide_activite(@id,null,null,null,null);
set @id=null;
call pai_valide_tournee(@id,null,null,null,null);
*/
/*
Cr?ation contrat VCP 
select rcoid,concat(substr(rcoid,1,8),'01') from  emp_pop_depot where societe_id=0;
update emp_contrat set  rcoid=concat(substr(rcoid,1,8),'01')    where societe_id=0;
select * from emp_contrat where societe_id=0
delete from emp_contrat_type where contrat_id=0

update emp_pop_depot epd inner join employe e on epd.employe_id=e.id set epd.rcoid=e.matricule where societe_id=0;
update emp_pop_depot epd inner join emp_contrat eco on epd.employe_id=eco.employe_id set epd.contrat_id=eco.id where epd.societe_id=0;

insert into emp_contrat(employe_id,societe_id,date_debut,date_fin,rcoid,rc,date_creation)
select employe_id,societe_id,date_debut,date_fin,rcoid,rc,date_creation 
from emp_pop_depot where societe_id=0;

insert into emp_contrat_type(contrat_id,typecontrat_id,date_debut,date_fin,date_fin_prevue,date_creation)
select epd.contrat_id,epd.typecontrat_id,epd.date_debut,epd.date_fin,'2999-01-01',epd.date_creation 
from emp_pop_depot epd
inner join emp_contrat eco on epd.employe_id=eco.employe_id
where epd.societe_id=0;
*/

drop procedure if exists emp_contrat_fermeture;
create procedure emp_contrat_fermeture(
IN _employe_id int,
IN _date_fin date
) begin
  update emp_contrat eco
  set eco.date_fin=_date_fin 
  ,   eco.date_modif=now()
  where eco.employe_id=_employe_id 
  and eco.date_fin='2999-01-01'
  ;
  update emp_contrat eco
  inner join emp_contrat_type ect on ect.contrat_id=eco.id
  set ect.date_fin=_date_fin 
  ,   ect.date_modif=now()
  where eco.employe_id=_employe_id 
  and ect.date_fin='2999-01-01'
  ;
  update emp_pop_depot epd
  set epd.date_fin=_date_fin 
  ,   epd.fRC=_date_fin 
  ,   epd.date_modif=now()
  where epd.employe_id=_employe_id 
  and epd.date_fin='2999-01-01'
  ;
  update emp_cycle ecy
  set ecy.date_fin=_date_fin 
  ,   ecy.date_modif=now()
  where ecy.employe_id=_employe_id 
  and ecy.date_fin='2999-01-01'
  ;
end;
