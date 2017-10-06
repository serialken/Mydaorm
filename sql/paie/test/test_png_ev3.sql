-- create table tmp_evfactoryw1 as select * from pai_png_ev_factoryw

call ALIM_EMP_MROAD(1,null,1)
call ALIM_EMP_MROAD(1,null,2)
call ALIM_EMP_MROAD(0,null,1)
call ALIM_EMP_MROAD(0,null,2)
select * from emp_mroad

set @idtrt=null;
call INT_MROAD2EV_STC(@idtrt,0,1);
SELECT ligne FROM pai_int_ev_hst where idtrt=@idtrt;
  call int_mroad2ev_diff(@idtrt); -- jour

 select * from pai_png_ev_factoryw e1

  select d.* FROM pai_ev_diff d WHERE d.diff<>'=' and idtrt1=@idtrt and idtrt2=@idtrt ORDER BY d.matricule,d.datev,d.poste;
  select d.* FROM pai_ev_diff d WHERE idtrt1=@idtrt and idtrt2=@idtrt ORDER BY d.matricule,d.datev,d.poste;
SELECT ligne FROM pai_int_ev_hst where idtrt=@idtrt

