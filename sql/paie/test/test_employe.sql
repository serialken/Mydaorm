-- individu sans RC
select * from employe e
where not exists(select null from emp_pop_depot epd where e.id=epd.employe_id);

delete e from employe e
where not exists(select null from emp_pop_depot epd where e.id=epd.employe_id)
and e.id not in(
  select distinct mtj.employe_id
  from modele_tournee_jour mtj
  where not exists(select null from emp_pop_depot epd where mtj.employe_id=epd.employe_id)
)
