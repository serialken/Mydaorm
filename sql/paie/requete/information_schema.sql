/*
call information_schema_routines('mroad','req_modele')
call information_schema_parameters('mroad','req_modele_tournee_modification')
call information_schema_parameters('mroad','req_modele_vcp')
call req_modele_vcp(22,2)
*/
drop procedure information_schema_routines;
create procedure information_schema_routines($_schema varchar(256),$_prefix varchar(256))
begin
  select routine_name as id
  , routine_comment as libelle
  from information_schema.routines r
  where r.routine_type='PROCEDURE'
  and r.routine_name like concat($_prefix,'%') collate utf8_unicode_ci
--  and r.routine_schema=$_schema
  ;
end;  

drop procedure information_schema_parameters;
create procedure information_schema_parameters($_schema varchar(256), $_routine varchar(256))
begin
  select 
    parameter_mode,
    parameter_name,
    data_type
  from information_schema.parameters p
  where p.specific_name=$_routine collate utf8_unicode_ci
--  and p.specific_schema=$_schema collate utf8_unicode_ci
  order by p.ordinal_position;
end;  