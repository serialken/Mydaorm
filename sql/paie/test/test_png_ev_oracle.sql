-- recuperation des matricules MROAD
drop table ev_mat;
select distinct "matricule" from "pai_ev"@MROAD;
create table ev_mat as select distinct "matricule" from "pai_ev"@MROAD;
select  distinct "matricule" from ev_mat;
-- calcul de paie
begin
  evtest.test_jour('20141010','20141002','20141008',28);
end;
/


-- envoi des ev dans mroad pour comparaison
DECLARE
  ret INTEGER;
BEGIN
  evtest.test_jour('201408000021','20140821','20140821',21);
  COMMIT;
  evtest.test_jour('201408000022','20140822','20140822',22);
  COMMIT;
  evtest.test_jour('201408000023','20140823','20140823',23);
  COMMIT;
  evtest.test_jour('201408000024','20140824','20140824',24);
  COMMIT;
  evtest.test_jour('201408000025','20140825','20140825',25);
  COMMIT;
  evtest.test_jour('201408000026','20140826','20140826',26);
  COMMIT;
  evtest.test_jour('201408000027','20140827','20140827',27);
  COMMIT;
end;
/
delete from ev_mat where "idtrt"=85;
select * from ev_mat where "idtrt"=121;
select * from ev_hst where datetrt='201408000021';
select * from ev_log where datetrt='201408000021';
select * from ev_contrat;
select * from ev_tournees;
select * from tournees where datetr='20140821';
select * from ev_traitement where datetrt like '201408%';
declare
ret INTEGER;
begin
  ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_ev_hst where "idtrt"='||to_char(100+21));
  COMMIT;
  FOR e IN (SELECT  e.typev,e.mat,e.rc,e.codepaie,to_char(to_date(e.datev,'YYYYMMDD'),'YYYY-MM-DD') datev,e.ordre,to_char(e.qte,'9999990.00') qte,to_char(e.taux,'99990.000') taux,to_char(e.val,'9999990.00') val,e.lib
            FROM ev_hst e
            INNER JOIN ev_mat m on e.mat=m."matricule"
            WHERE e.datetrt='201408000021'
    ) LOOP
    INSERT INTO "pai_ev_hst"@MROAD("typev","matricule","rc","poste","datev","ordre","qte","taux","val","libelle","res","idtrt") VALUES(e.typev,e.mat,e.rc,e.codepaie,e.datev,e.ordre,e.qte,e.taux,e.val,e.lib,'',100+21);
  END LOOP;
  COMMIT;
end;
/


select 
'EvFactoryW     '
||'|'||'C'
||'|'||'Pepp           '
||'|'||rpad(trim(mat),8,'Z')
||'|'||nvl(rc,'900001')         	--RelationContrat
||'|'||rpad(decode(codepaie,'9248','KMSEMAIJFR','9250','KMDIMANCHE',codepaie),10)
||'|'||datev        			-- dateEffet
||'|'||nvl(to_char(ordre,'00'),'   ')	-- noOrdre
||'|'||'        '   			-- dateFin
||'|'||'EVPORTAGE '  			-- TA_RgpNatEvGenW
||'|'||'1|0|0|0|0|0'
||'|'||to_char(qte ,'00000000.00')
||'|'||to_char(taux,'0000000.000')
||'|'||to_char(val ,'00000000.00')
||'|'||' '          			-- signe
--||'|'||rpad(nvl(lib,' '),30) 		--lib
||'|'||rpad(' ',30) 			--lib
||'|'||'      ||      | |    |   |          |0|5|      |   |      |   |      | | ||'
from ev e
where typev not like '% CS'
--and (rc<>'900001' or rc is null)
and rpad(trim(mat),8,'Z') in ('NEP01419','NEP01216','NEP01485','NEP01405','NEP00016','NEP01354','NEP00015','NEP01376')
and trim(mat) like 'NE%'
order by mat,nvl(rc,'900001'),codepaie,datev,nvl(ordre,0);