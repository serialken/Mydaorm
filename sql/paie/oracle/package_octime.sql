
      /*
select * from pers where pers_mat like 'ME%';
select * from theme;
select * from varprev where pers_mat like 'ME%' order by 2;
select * from varprev where pers_mat like 'ME%' order by 2;
select distinct par_cod from varprev where pers_mat like 'ME%' order by 1;
select par_cod,count(distinct pers_mat) from varprev where par_cod in ('PORT','POLY','POR2','POL2','POLT','POS2','POST','POT2') group by par_cod;
select * from varprev where pers_mat like 'ME%' and par_cod in ('POR2') order by 1;
select * from varprev where pers_mat='MEM7833220';
select * from varprev where pers_mat='MEM5950520';

select distinct par_cod from varprev where pers_mat like 'NE%' order by 1;
*/
/*
begin
mroad.exec();
end;
/

select * from horprev h
where not exists(select null from contprev c where h.pers_mat=c.pers_mat and c.cont_datf>=h.hor_dat)
;
Z004028500      21/10/2012 00:00:00        106          7          0          0 31/12/2099 00:00:00 
;
select * from contprev c where c.pers_mat='Z004028500';
commit;
delete horprev where pers_mat='Z004028500' and hor_dat=to_date('20121021','YYYYMMDD');
*/
create or replace package MROAD as 
  procedure exec;
end;
/

create or replace package body MROAD AS 
--create TABLE int_mroad(pers_mat VARCHAR2(15 BYTE));
--create TABLE int_emp_mroad(pers_mat VARCHAR2(15 BYTE),date_debut date,date_fin date);
--create TABLE int_mois(societe char(1),date_debut date,date_fin date);


/*
DROP PUBLIC DATABASE LINK MROAD;
CREATE PUBLIC DATABASE LINK MROAD CONNECT TO "mroad" identified by "Mroad123" using 'PMROAD';
CREATE PUBLIC DATABASE LINK MROAD CONNECT TO "oracle" identified by "qmroad" using 'QMROAD';
CREATE PUBLIC DATABASE LINK MROAD CONNECT TO "oracle" identified by "qmroad" using 'DMROAD';

BEGIN
  MROAD.exec();
END;

*/

    PROCEDURE nettoyage IS
      ret INTEGER;
    BEGIN
      ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_oct_pointage');
      ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_oct_saiabs');
      --  ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_oct_saiabssav');
      ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_oct_cjexp');
      ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_oct_varprev');
      ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_oct_nivprev');
      ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_oct_horprev');
      ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_oct_cyclig');
      ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_oct_contprev');
      ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_oct_pers');
      COMMIT;

      execute immediate('truncate table int_mroad');
      execute immediate('truncate table int_emp_mroad');
      execute immediate('truncate table int_mois');
      commit;
    end;

    procedure init is
      ret INTEGER;
    begin
      ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('call INT_mroad2badge_init()');
      commit;
      insert into int_emp_mroad(pers_mat,date_debut,date_fin)
      SELECT "matricule",to_date("date_debut",'YYYYMMDD'),to_date("date_fin",'YYYYMMDD')
      FROM "emp_mroad"@MROAD
      ;
      COMMIT;
      insert into int_mroad(pers_mat)
      SELECT DISTINCT v.pers_mat
      FROM varprev v
      WHERE v.par_cod IN ('PORT','POLY','POR2','POL2','POLT','POS2','POST','POT2')
      AND v.pers_mat NOT IN ('Z003025700','Z003054900','Z003015300','Z004105200')
      AND exists(select null from contprev c where v.pers_mat=c.pers_mat and c.cont_datf>=to_date('20140821','YYYYMMDD'))
      ;
      COMMIT;
      insert into int_mois(societe,date_debut,date_fin)
      SELECT case when "flux_id"=1 then '0' else '2' end,to_date("date_debut_string",'YYYYMMDD'), to_date("date_fin_string",'YYYYMMDD')
      FROM "pai_mois"@MROAD
      ;
      COMMIT;
    end;
    
    PROCEDURE exec_pers IS
    BEGIN
      FOR p IN (SELECT  p.pers_mat ,  p.pers_nom ,  p.pers_pre
                FROM  pers p
                INNER JOIN int_mroad i ON p.pers_mat=i.pers_mat
        ) LOOP
        INSERT INTO "pai_oct_pers"@MROAD("pers_mat","pers_nom","pers_pre") VALUES(p.pers_mat , p.pers_nom , p.pers_pre);
      END LOOP;
      COMMIT;
    END;

    PROCEDURE exec_contprev IS
    BEGIN
      FOR c IN (SELECT  c.pers_mat, c.cont_datd, c.cont_datf
                FROM  contprev c
                INNER JOIN int_mroad i ON c.pers_mat=i.pers_mat
				inner join int_mois m ON substr(i.pers_mat,9,1)=m.societe
                WHERE c.cont_datd<=m.date_fin and c.cont_datf>=m.date_debut
        ) LOOP
        INSERT INTO "pai_oct_contprev"@MROAD("pers_mat","cont_datd","cont_datf") VALUES(c.pers_mat, c.cont_datd, c.cont_datf);
      END LOOP;
      COMMIT;
    END;

    PROCEDURE exec_cyclig IS
    BEGIN
      FOR c IN (SELECT  c.cyc_cod,c.cyc_num,c.hor_cod
                FROM  cyclig c
        ) LOOP
        INSERT INTO "pai_oct_cyclig"@MROAD("cyc_cod","cyc_num","hor_cod") VALUES(c.cyc_cod,c.cyc_num,c.hor_cod);
      END LOOP;
      COMMIT;
    END;

    PROCEDURE exec_horprev IS
    BEGIN
      FOR h IN (SELECT  h.pers_mat, h.hor_dat, h.cyc_cod, h.hor_datf
                FROM  horprev h
                INNER JOIN int_mroad i ON h.pers_mat=i.pers_mat
				inner join int_mois m ON substr(i.pers_mat,9,1)=m.societe
                WHERE /*h.hor_dat<=m.date_fin and*/ h.hor_datf>=m.date_debut
        ) LOOP
        INSERT INTO "pai_oct_horprev"@MROAD("pers_mat","hor_dat","cyc_cod","hor_datf") VALUES(h.pers_mat, h.hor_dat, h.cyc_cod, h.hor_datf);
      END LOOP;
      COMMIT;
    END;

    PROCEDURE exec_nivprev IS
    BEGIN
      FOR n IN (SELECT  n.pers_mat,n.niv_dat,n.niv_datf,n.niv_cod1,n.niv_cod2,n.niv_cod3
                FROM  nivprev n
                INNER JOIN int_mroad i ON n.pers_mat=i.pers_mat
				inner join int_mois m ON substr(i.pers_mat,9,1)=m.societe
                WHERE /*n.niv_dat<=m.date_fin and*/ n.niv_datf>=m.date_debut
        ) LOOP
        INSERT INTO "pai_oct_nivprev"@MROAD("pers_mat","niv_dat","niv_datf","niv_cod1","niv_cod2","niv_cod3") VALUES(n.pers_mat,n.niv_dat,n.niv_datf,n.niv_cod1,n.niv_cod2,n.niv_cod3);
      END LOOP;
      COMMIT;
    END;

    PROCEDURE exec_varprev IS
    BEGIN
      FOR v IN (SELECT  v.pers_mat,v.var_dat,v.par_cod,v.var_datf
                FROM  varprev v
                INNER JOIN int_mroad i ON v.pers_mat=i.pers_mat
				inner join int_mois m ON substr(i.pers_mat,9,1)=m.societe
                WHERE /*v.var_dat<=m.date_fin and*/ v.var_datf>=m.date_debut
                AND v.par_cod IN ('PORT','POLY','POR2','POL2','POLT','POS2','POST','POT2')
        ) LOOP
        INSERT INTO "pai_oct_varprev"@MROAD("pers_mat","var_dat","par_cod","var_datf") VALUES(v.pers_mat,v.var_dat,v.par_cod,v.var_datf);
      END LOOP;
      COMMIT;
    END;

    PROCEDURE exec_saiabs IS
    BEGIN
      FOR s IN (SELECT  DISTINCT s.pers_mat,s.abs_dat,s.abs_num,s.abs_fin,s.abs_cod
                FROM  saiabs s
                INNER JOIN int_mroad i ON s.pers_mat=i.pers_mat
				inner join int_mois m ON substr(i.pers_mat,9,1)=m.societe
                INNER JOIN int_emp_mroad e ON s.pers_mat=e.pers_mat and (s.abs_dat between e.date_debut and e.date_fin or s.abs_fin between e.date_debut and e.date_fin)
                WHERE /*s.abs_dat<=m.date_fin and*/ s.abs_fin>=m.date_debut
        ) LOOP
        INSERT INTO "pai_oct_saiabs"@MROAD("pers_mat","abs_dat","abs_num","abs_fin","abs_cod") VALUES(s.pers_mat,s.abs_dat,s.abs_num,s.abs_fin,s.abs_cod);
      END LOOP;
      COMMIT;
    END;
    
    PROCEDURE exec_pointage IS
    BEGIN
      FOR p IN (SELECT  p.pers_mat,p.bad_dat,p.bad_heure,p.bad_typ
                FROM  pointage p
                INNER JOIN int_mroad i ON p.pers_mat=i.pers_mat
				inner join int_mois m ON substr(i.pers_mat,9,1)=m.societe
                INNER JOIN int_emp_mroad e ON p.pers_mat=e.pers_mat and p.bad_dat between e.date_debut and e.date_fin
                WHERE p.bad_dat>=m.date_debut
--                WHERE p.bad_dat between m.date_debut and m.date_fin
        ) LOOP
        INSERT INTO "pai_oct_pointage"@MROAD("pers_mat","bad_dat","bad_heure","bad_typ") VALUES(p.pers_mat,p.bad_dat,p.bad_heure,p.bad_typ);
      END LOOP;
      COMMIT;
    END;

    PROCEDURE exec_cjexp IS
    BEGIN
      FOR c IN (SELECT  c.pers_mat,c.dat,c.hor_cod
                FROM  cjexp c
                INNER JOIN int_mroad i ON c.pers_mat=i.pers_mat
				inner join int_mois m ON substr(i.pers_mat,9,1)=m.societe
                INNER JOIN int_emp_mroad e ON c.pers_mat=e.pers_mat and c.dat between e.date_debut and e.date_fin
                WHERE c.dat>=m.date_debut
--                WHERE c.dat between m.date_debut and m.date_fin
        ) LOOP
        INSERT INTO "pai_oct_cjexp"@MROAD("pers_mat","dat","hor_cod") VALUES(c.pers_mat,c.dat,c.hor_cod);
      END LOOP;
      COMMIT;
    END;
    
    PROCEDURE exec IS
    BEGIN
      nettoyage;
      init;
      exec_pers;
      exec_contprev();
      exec_cyclig();
      exec_horprev();
      exec_nivprev();
      exec_varprev();
      exec_saiabs();
      exec_pointage();    
      exec_cjexp();
    END;
END;
/