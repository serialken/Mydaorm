ALTER TABLE REF_EV_CPT  MODIFY (PAR_COD VARCHAR2(1024 BYTE) );

Insert into REF_EV_CPT (POSTE,LIBELLE,CPT,PORTEUR,ADMINISTRATIF,OPERATION,ARRONDI,TRANSFORMATION,JAV,SQL_QTE,PAR_COD,SQL_WHERE,JSTC,JP1,OPLOG,DEBUT,FIN,LASTAV) values ('MHC1','Majoration heures complémentaires 10%','172','P',null,'V',null,'/60','?',null,null,null,'?','?','O',to_date('21/08/2014 00:00:00','DD/MM/YYYY HH24:MI:SS'),to_date('31/12/2099 00:00:00','DD/MM/YYYY HH24:MI:SS'),'N');
Insert into REF_EV_CPT (POSTE,LIBELLE,CPT,PORTEUR,ADMINISTRATIF,OPERATION,ARRONDI,TRANSFORMATION,JAV,SQL_QTE,PAR_COD,SQL_WHERE,JSTC,JP1,OPLOG,DEBUT,FIN,LASTAV) values ('MHC2','Majoration heures complémentaires 25%','173','P',null,'V',null,'/60','?',null,null,null,'?','?','O',to_date('21/08/2014 00:00:00','DD/MM/YYYY HH24:MI:SS'),to_date('31/12/2099 00:00:00','DD/MM/YYYY HH24:MI:SS'),'N');
Insert into REF_EV_CPT (POSTE,LIBELLE,CPT,PORTEUR,ADMINISTRATIF,OPERATION,ARRONDI,TRANSFORMATION,JAV,SQL_QTE,PAR_COD,SQL_WHERE,JSTC,JP1,OPLOG,DEBUT,FIN,LASTAV) values ('MHS1','Majoration heures supplémentaires 25%','175','P',null,'V',null,'/60','?',null,null,null,'?','?','O',to_date('21/08/2014 00:00:00','DD/MM/YYYY HH24:MI:SS'),to_date('31/12/2099 00:00:00','DD/MM/YYYY HH24:MI:SS'),'N');
Insert into REF_EV_CPT (POSTE,LIBELLE,CPT,PORTEUR,ADMINISTRATIF,OPERATION,ARRONDI,TRANSFORMATION,JAV,SQL_QTE,PAR_COD,SQL_WHERE,JSTC,JP1,OPLOG,DEBUT,FIN,LASTAV) values ('MHS2','Majoration heures supplémentaires 50%','176','P',null,'V',null,'/60','?',null,null,null,'?','?','O',to_date('21/08/2014 00:00:00','DD/MM/YYYY HH24:MI:SS'),to_date('31/12/2099 00:00:00','DD/MM/YYYY HH24:MI:SS'),'N');
Insert into REF_EV_CPT (POSTE,LIBELLE,CPT,PORTEUR,ADMINISTRATIF,OPERATION,ARRONDI,TRANSFORMATION,JAV,SQL_QTE,PAR_COD,SQL_WHERE,JSTC,JP1,OPLOG,DEBUT,FIN,LASTAV) values ('ABSCPPORT','Congés porteurs Neo/Media ayant le paiement à la prise','155','P',null,'S',null,'/100','?','sum(cal_val155+cal_val161+cal_val164)','POL2,POR2,POS2,POT2',null,'?','?','O',to_date('21/08/2014 00:00:00','DD/MM/YYYY HH24:MI:SS'),to_date('31/12/2099 00:00:00','DD/MM/YYYY HH24:MI:SS'),'N');


/*
Ajouter dans OCTNG.init_cptres1
  union select '155' from dual -- SDV : ABSCPPORT
  union select '161' from dual -- SDV : ABSCPPORT
  union select '164' from dual -- SDV : ABSCPPORT
  
Modifier dans OCTNG_EV.calcul
      s_where:=s_where||' and r.pers_mat=v.pers_mat and instr('',''||'''||trim(ev.par_cod)||'''||'','','',''||v.par_cod||'','')>0'; 
*/

