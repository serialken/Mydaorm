/*
  DROP DATABASE LINK "MROAD";
    CREATE DATABASE LINK "MROAD"
   CONNECT TO "oracle" IDENTIFIED BY "qmroad"
   USING 'DMROAD';
 */
CREATE OR REPLACE package body MROAD AS 
--create TABLE int_mroad(rcoid VARCHAR(36));

    PROCEDURE nettoyage IS
      ret INTEGER;
    BEGIN
  ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_oct_pointage');
  ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_oct_saiabs');
 -- ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_oct_saiabssav');
  ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_oct_cjexp');
  ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_oct_varprev');
  ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_oct_nivprev');
  ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_oct_horprev');
  ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_oct_cyclig');
  ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_oct_contprev');
  ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_oct_pers');
      COMMIT;
    END;
    
    PROCEDURE exec_pers IS
    BEGIN
      FOR p IN (SELECT  p.pers_mat ,  p.pers_nom ,  p.pers_pre
                FROM  pers p
                WHERE pers_mat IN (
                  SELECT DISTINCT v.pers_mat
                  FROM varprev v
                  WHERE v.par_cod IN ('PORT','POLY')
                  AND v.pers_mat NOT IN ('Z003025700','Z003054900','Z003015300','Z004105200')
              )
        --and rc.relatdatefinw>=to_date('20140101','YYYYMMDD')
        ) LOOP
        INSERT INTO "pai_oct_pers"@MROAD("pers_mat","pers_nom","pers_pre") VALUES(p.pers_mat , p.pers_nom , p.pers_pre);
      END LOOP;
      COMMIT;
    END;

    PROCEDURE exec_contprev IS
    BEGIN
      FOR c IN (SELECT  c.pers_mat, c.cont_datd, c.cont_datf
                FROM  contprev c
                WHERE pers_mat IN (
                  SELECT DISTINCT v.pers_mat
                  FROM varprev v
                  WHERE v.par_cod IN ('PORT','POLY')
                  AND v.pers_mat NOT IN ('Z003025700','Z003054900','Z003015300','Z004105200')
              )
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
                WHERE pers_mat IN (
                  SELECT DISTINCT v.pers_mat
                  FROM varprev v
                  WHERE v.par_cod IN ('PORT','POLY')
                  AND v.pers_mat NOT IN ('Z003025700','Z003054900','Z003015300','Z004105200')
              )
        ) LOOP
        INSERT INTO "pai_oct_horprev"@MROAD("pers_mat","hor_dat","cyc_cod","hor_datf") VALUES(h.pers_mat, h.hor_dat, h.cyc_cod, h.hor_datf);
      END LOOP;
      COMMIT;
    END;

    PROCEDURE exec_nivprev IS
    BEGIN
      FOR n IN (SELECT  n.pers_mat,n.niv_dat,n.niv_datf,n.niv_cod1,n.niv_cod2,n.niv_cod3
                FROM  nivprev n
                WHERE pers_mat IN (
                  SELECT DISTINCT v.pers_mat
                  FROM varprev v
                  WHERE v.par_cod IN ('PORT','POLY')
                  AND v.pers_mat NOT IN ('Z003025700','Z003054900','Z003015300','Z004105200')
              )
        ) LOOP
        INSERT INTO "pai_oct_nivprev"@MROAD("pers_mat","niv_dat","niv_datf","niv_cod1","niv_cod2","niv_cod3") VALUES(n.pers_mat,n.niv_dat,n.niv_datf,n.niv_cod1,n.niv_cod2,n.niv_cod3);
      END LOOP;
      COMMIT;
    END;

    PROCEDURE exec_varprev IS
    BEGIN
      FOR v IN (SELECT  v.pers_mat,v.var_dat,v.par_cod,v.var_datf
                FROM  varprev v
                WHERE v.par_cod IN ('PORT','POLY')
                AND v.pers_mat NOT IN ('Z003025700','Z003054900','Z003015300','Z004105200')
        ) LOOP
        INSERT INTO "pai_oct_varprev"@MROAD("pers_mat","var_dat","par_cod","var_datf") VALUES(v.pers_mat,v.var_dat,v.par_cod,v.var_datf);
      END LOOP;
      COMMIT;
    END;

    PROCEDURE exec_cjexp IS
    BEGIN
      FOR c IN (SELECT  c.pers_mat,c.dat,c.hor_cod
                FROM  cjexp c
                WHERE pers_mat IN (
                  SELECT DISTINCT v.pers_mat
                  FROM varprev v
                  WHERE v.par_cod IN ('PORT','POLY')
                  AND v.pers_mat NOT IN ('Z003025700','Z003054900','Z003015300','Z004105200'))
                AND c.dat>to_date('20140420','YYYYMMDD')
                AND c.dat<=SYSDATE
        ) LOOP
        INSERT INTO "pai_oct_cjexp"@MROAD("pers_mat","dat","hor_cod") VALUES(c.pers_mat,c.dat,c.hor_cod);
      END LOOP;
      COMMIT;
    END;
    
    PROCEDURE exec_saiabssav IS
    BEGIN
      FOR s IN (SELECT  s.pers_mat,s.abs_dat,s.abs_fin,s.abs_cod,s.flag
                FROM  saiabssav s
                WHERE pers_mat IN (
                  SELECT DISTINCT v.pers_mat
                  FROM varprev v
                  WHERE v.par_cod IN ('PORT','POLY')
                  AND v.pers_mat NOT IN ('Z003025700','Z003054900','Z003015300','Z004105200'))
                AND s.abs_fin>to_date('20140420','YYYYMMDD')
                AND s.abs_dat<=SYSDATE
                -- On ne prend que les absences supprimées
                AND abs_cod<>'JFER'
                AND flag    ='S'
                AND ps_jou||ps_heu=
                  (
                    SELECT MAX(ps_jou||ps_heu)
                    FROM saiabssav s2
                    WHERE s.pers_mat =s2.pers_mat
                    AND s.abs_dat=s2.abs_dat
                  )
        ) LOOP
        INSERT INTO "pai_oct_saiabssav"@MROAD("pers_mat","abs_dat","abs_fin","abs_cod","flag") VALUES(s.pers_mat,s.abs_dat,s.abs_fin,s.abs_cod,s.flag);
      END LOOP;
      COMMIT;
    END;
    
    PROCEDURE exec_saiabs IS
    BEGIN
      FOR s IN (SELECT  s.pers_mat,s.abs_dat,s.abs_num,s.abs_fin,s.abs_cod
                FROM  saiabs s
                WHERE pers_mat IN (
                  SELECT DISTINCT v.pers_mat
                  FROM varprev v
                  WHERE v.par_cod IN ('PORT','POLY')
                  AND v.pers_mat NOT IN ('Z003025700','Z003054900','Z003015300','Z004105200'))
                AND s.abs_fin>to_date('20140420','YYYYMMDD')
                AND s.abs_dat<=SYSDATE
        ) LOOP
        INSERT INTO "pai_oct_saiabs"@MROAD("pers_mat","abs_dat","abs_num","abs_fin","abs_cod") VALUES(s.pers_mat,s.abs_dat,s.abs_num,s.abs_fin,s.abs_cod);
      END LOOP;
      COMMIT;
    END;
    
    PROCEDURE exec_pointage IS
    BEGIN
      FOR p IN (SELECT  p.pers_mat,p.bad_dat,p.bad_heure,p.bad_typ
                FROM  pointage p
                WHERE pers_mat IN (
                  SELECT DISTINCT v.pers_mat
                  FROM varprev v
                  WHERE v.par_cod IN ('PORT','POLY')
                  AND v.pers_mat NOT IN ('Z003025700','Z003054900','Z003015300','Z004105200'))
                AND p.bad_dat>to_date('20140420','YYYYMMDD')
                AND p.bad_dat<=SYSDATE
        ) LOOP
        INSERT INTO "pai_oct_pointage"@MROAD("pers_mat","bad_dat","bad_heure","bad_typ") VALUES(p.pers_mat,p.bad_dat,p.bad_heure,p.bad_typ);
      END LOOP;
      COMMIT;
    END;
    
    PROCEDURE exec IS
    BEGIN
      nettoyage;
      exec_pers;
      exec_contprev;
      exec_cyclig;
      exec_horprev;
      exec_nivprev;
      exec_varprev;
      exec_cjexp;
--      exec_saiabssav;
      exec_saiabs;
      exec_pointage;    
    END;
END;