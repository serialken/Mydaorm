create or replace PACKAGE      EVTEST AS

procedure test_jour(
    Wdatetrt			ev_log.datetrt%type
,	dpaie gen_pepp.dernierepaie%type
,	ppaie gen_pepp.dernierepaie%type
,    Widtrt int
);

PROCEDURE mensuel(
    Wdatetrt			ev_log.datetrt%type
,	dpaie gen_pepp.dernierepaie%type
,	ppaie gen_pepp.dernierepaie%type
  );
  
procedure historique(
  Wlogin			    profils.NomUser%TYPE
, Wdatetrt        ev_log.datetrt%type
,	Wdatetrt_org    ev_log.datetrt%type
,	dpaie           gen_pepp.dernierepaie%type
,	ppaie           gen_pepp.dernierepaie%type
);

procedure ev_exec(
  Wdatetrt      ev_log.datetrt%type
,	Wdatetrt_org  ev_log.datetrt%type
, WStc          char
,	dpaie         gen_pepp.dernierepaie%type
,	ppaie         gen_pepp.dernierepaie%type
);

procedure ev_logger(
   Wdatetrt     ev_log.datetrt%type
,  Wnum         ev_log.num%type
,  Wmodule      ev_log.module%type
,  Wmsg         ev_log.msg%type
);

procedure ev_select_contrat_CS(
  Wdatetrt      ev_log.datetrt%type
,	Wdatetrt_org  ev_log.datetrt%type
, WStc          char
,	dpaie         gen_pepp.dernierepaie%type
,	ppaie         gen_pepp.dernierepaie%type
);

procedure ev_select_tournee(
  Wdatetrt      ev_log.datetrt%type
,	Wdatetrt_org  ev_log.datetrt%type
, WStc          char
,	dpaie         gen_pepp.dernierepaie%type
,	ppaie         gen_pepp.dernierepaie%type
, W1M           char
);

procedure ev_maj_qualite_DF(
  Wdatetrt      ev_log.datetrt%type
,	dpaie         gen_pepp.dernierepaie%type
,	ppaie         gen_pepp.dernierepaie%type
, W1M           char
);

procedure ev_maj_contrat(
  Wdatetrt      ev_log.datetrt%type
, W1M           char
);

procedure ev_erreur(
  Wdatetrt      ev_log.datetrt%type
,	dpaie         gen_pepp.dernierepaie%type
,	ppaie         gen_pepp.dernierepaie%type
);

procedure ev_calcul(
  Wdatetrt      ev_log.datetrt%type
, WStc          char
,	dpaie         gen_pepp.dernierepaie%type
,	ppaie         gen_pepp.dernierepaie%type
, W1M           char
);

procedure ev_calcul_heure_jour(
  Wcodepaie poste_paie.codepaieple%type
, Wtypejour tournees.typejour%type
);

procedure ev_calcul_heure_nuit(
  Wcodepaie poste_paie.codepaieple%type
, Wtypejour tournees.typejour%type
, Wtyp4pc tournees.typejour%type
);

procedure ev_corrige_heure_nuit(
  premierMai char
);

procedure ev_calcul_serpentin(
  Wdatetrt ev_log.datetrt%type
, Wcodepaie poste_paie.codepaieple%type
, Wtyp4pc   tournees_detail.typ4pc%type
, Wtypejour tournees.typejour%type
);

procedure ev_calcul_titre(
  Wdatetrt ev_log.datetrt%type
, Wcodepaie poste_paie.codepaie%type
, Wtyp4pc   tournees_detail.typ4pc%type
);

procedure ev_calcul_supplement(
  Wdatetrt ev_log.datetrt%type
, Wcodepaie poste_paie.codepaie%type
, Wtyp4pc   tournees_detail.typ4pc%type
);

procedure ev_calcul_kilometre(
  Wdatetrt ev_log.datetrt%type
, Wcodepaie poste_paie.codepaie%type
, Wtypejour tournees.typejour%type
);

procedure ev_calcul_activite_temps1(Wdatetrt ev_log.datetrt%type);
procedure ev_calcul_activite_temps2(Wdatetrt ev_log.datetrt%type);
procedure ev_calcul_activite_quantite(Wdatetrt ev_log.datetrt%type);

procedure ev_calcul_majoration_HT;
procedure ev_calcul_majoration_HJ;
procedure ev_calcul_majoration_JT;

procedure ev_calcul_majoration_JO(
  Wdatedebut gen_pepp.dernierepaie%type
, Wdatefin gen_pepp.dernierepaie%type
);

procedure ev_calcul_prime(Wdatetrt ev_log.datetrt%type);

procedure ev_calcul_bonus(
  WStc char
, Wdatedeb gen_pepp.dernierepaie%type
, Wdatefin gen_pepp.dernierepaie%type
);

procedure ev_diff(Wdatetrt ev_log.datetrt%type);
end;
/
create or replace PACKAGE BODY EVTEST AS

/*
declare
  Wdatetrt char(15);
begin
  Wdatetrt:='20110427 120001';
  update gen_pepp set dernierepaie='20110320',prochainepaie='20110420';
  update tournees set dextrait=null,extrait='N' where dextrait=Wdatetrt;
  update heures set dextrait=null,extrait='N' where dextrait=Wdatetrt;
  ev30.mensuel('jlouis','20110427 120002');
end;
*/
-- create table ev_mat as select distinct "idtrt","matricule" from "pai_ev_hst"@MROAD where "idtrt"=85;

procedure nettoyage(
    Wdatetrt			ev_log.datetrt%type
,   Widtrt int
) is
begin
  execute immediate('delete from ev_mat where "idtrt"='||to_char(Widtrt));
  execute immediate('insert into ev_mat select distinct "idtrt","matricule" from "pai_ev_hst"@MROAD where "idtrt"='||to_char(Widtrt));
  COMMIT;

  delete from ev_hst where datetrt=Wdatetrt;
  delete from ev_log where datetrt=Wdatetrt;
  delete from ev_qualite where datetrt=Wdatetrt;
  insert into ev_traitement(datetrt,nomuser,typetrt) select Wdatetrt,'jlouis','Interface test' from dual where not exists(select null from ev_traitement where datetrt=Wdatetrt);
  COMMIT;
end;


procedure transfert(
    Wdatetrt			ev_log.datetrt%type
,   Widtrt int
) is
ret INTEGER;
begin
  ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_ev_hst where "idtrt"='||to_char(100+Widtrt));
  COMMIT;
  FOR e IN (SELECT  e.typev,e.mat,e.rc,e.codepaie,to_char(to_date(e.datev,'YYYYMMDD'),'YYYY-MM-DD') datev,e.ordre,to_char(e.qte,'9999990.00') qte,to_char(e.taux,'99990.000') taux,to_char(e.val,'9999990.00') val,e.lib
            FROM ev_hst e
            INNER JOIN ev_mat m on e.mat=m."matricule"
            WHERE e.datetrt=Wdatetrt
    ) LOOP
    INSERT INTO "pai_ev_hst"@MROAD("typev","matricule","rc","poste","datev","ordre","qte","taux","val","libelle","res","idtrt") VALUES(e.typev,e.mat,e.rc,e.codepaie,e.datev,e.ordre,e.qte,e.taux,e.val,e.lib,'',100+Widtrt);
  END LOOP;
  COMMIT;
end;

procedure test_jour(
    Wdatetrt			ev_log.datetrt%type
,	dpaie gen_pepp.dernierepaie%type
,	ppaie gen_pepp.dernierepaie%type
,    Widtrt int
) IS
begin
  nettoyage(Wdatetrt,Widtrt);
  ev_exec(Wdatetrt,Wdatetrt,'N',dpaie,ppaie);
  COMMIT;
  transfert(Wdatetrt,Widtrt);

  update tournees set dextrait=null,extrait='N' where dextrait=Wdatetrt;
  update heures set dextrait=null,extrait='N' where dextrait=Wdatetrt;
end;

procedure mensuel(
    Wdatetrt			ev_log.datetrt%type
,	dpaie gen_pepp.dernierepaie%type
,	ppaie gen_pepp.dernierepaie%type
) IS
begin
  insert into ev_traitement(datetrt,nomuser,typetrt) values(Wdatetrt,'jlouis','Interface test');
  ev_logger(Wdatetrt,1,'ev_exec','Début de l''interface test');
--	select to_char(to_date(dernierepaie,'YYYYMMDD')+1,'YYYYMMDD'),prochainepaie into dpaie,ppaie from gen_pepp;
  ev_exec(Wdatetrt,Wdatetrt,'N',dpaie,ppaie);
  COMMIT;

  update tournees set dextrait=null,extrait='N' where dextrait=Wdatetrt;
  update heures set dextrait=null,extrait='N' where dextrait=Wdatetrt;
end;
  
procedure historique(
  Wlogin			    profils.NomUser%TYPE
, Wdatetrt        ev_log.datetrt%type
,	Wdatetrt_org    ev_log.datetrt%type
,	dpaie           gen_pepp.dernierepaie%type
,	ppaie           gen_pepp.dernierepaie%type
) as
Wtypetrt  ev_traitement.typetrt%type;
begin
  insert into ev_traitement(datetrt,nomuser,typetrt) values(Wdatetrt,Wlogin,'Interface Historique');
  ev_logger(Wdatetrt,1,'ev_exec','Début de l''interface historique lancée par '||Wlogin);

  select typetrt into Wtypetrt from ev_traitement where datetrt=Wdatetrt_org;
  if (Wtypetrt='Interface test') then
    ev_exec(Wdatetrt,Wdatetrt_org,'N',dpaie,ppaie);
  elsif (Wtypetrt='Interface Individuelle') then
    ev_exec(Wdatetrt,Wdatetrt_org,'O',dpaie,ppaie);
  end if;
  ev_diff(Wdatetrt_org);
end;


procedure ev_exec(
  Wdatetrt      ev_log.datetrt%type
,	Wdatetrt_org  ev_log.datetrt%type
, WStc          char
,	dpaie         gen_pepp.dernierepaie%type
,	ppaie         gen_pepp.dernierepaie%type
) as
	paie_1M char(8);
begin
  ev_logger(Wdatetrt,1.1,'ev_exec','Calcul de la paie pour la période du '||dpaie||' au '||ppaie);

  execute immediate 'truncate table ev';

   if substr(dpaie,5,4)<='0501' and '0501'<=substr(ppaie,5,4) then
    paie_1M:=substr(dpaie,1,4)||'0501';

    ev_select_contrat_CS(Wdatetrt,Wdatetrt_org,WStc,paie_1M,paie_1M);
    ev_select_tournee(Wdatetrt,Wdatetrt_org,WStc,paie_1M,paie_1M,'1M');
    ev_maj_qualite_DF(Wdatetrt,paie_1M,paie_1M,'1M');
    ev_maj_contrat(Wdatetrt,'1M');
    ev_erreur(Wdatetrt,paie_1M,paie_1M);
    ev_calcul(Wdatetrt,WStc,paie_1M,paie_1M,'1M');
  end if;

  ev_select_contrat_CS(Wdatetrt,Wdatetrt_org,WStc,dpaie,ppaie);
  ev_select_tournee(Wdatetrt,Wdatetrt_org,WStc,dpaie,ppaie,'__');
  ev_maj_qualite_DF(Wdatetrt,dpaie,ppaie,'__');
  ev_maj_contrat(Wdatetrt,'__');
  ev_erreur(Wdatetrt,dpaie,ppaie);
  ev_calcul(Wdatetrt,WStc,dpaie,ppaie,'__');

  insert into ev_hst select typev,mat,rc,codepaie,datev,ordre,qte,taux,val,lib,res,Wdatetrt from ev;

  update gen_pepp set batchactif='N';
  ev_logger(Wdatetrt,9.9,'ev_exec','Fin des traitements');
end;

procedure ev_logger(
   Wdatetrt ev_log.datetrt%type
,  Wnum     ev_log.num%type
,  Wmodule  ev_log.module%type
,  Wmsg     ev_log.msg%type
) as
begin
  dbms_output.put_line(systimestamp||' -- '||Wnum||' -- '||Wmodule||' -- '||Wmsg);

  insert into ev_log(datetrt,datesys,num,module,msg)
  values(Wdatetrt,systimestamp,Wnum,Wmodule,Wmsg);
end;

procedure ev_select_contrat_CS(
  Wdatetrt      ev_log.datetrt%type
,	Wdatetrt_org  ev_log.datetrt%type
, WStc          char
,	dpaie         gen_pepp.dernierepaie%type
,	ppaie         gen_pepp.dernierepaie%type
) as
begin
  -- On supprimer les STC qui ne sont plus en phase avec Pleiades NG
  insert into ev_log(datetrt,datesys,num,module,msg)
  select Wdatetrt,systimestamp,-1.1,'ev_select_contrat_CS','STC non valide pour le matricule '||mat||' le '||datestc from stc 
  where WStc='O'
  and (mat,datestc) not in (
    SELECT DISTINCT 
    CB,TO_CHAR(CF,'YYYYMMDD')
    FROM   PEPP.PERZPRPL P1
    WHERE C05 = 'NON' 
    AND    (C06 = 'O' OR C06 = 'P' OR C07 = 'O') 
    AND    CF in ( SELECT CF FROM PEPP.PERZPR0 WHERE C05 = 'NON' AND CF <= TO_DATE((select max(prochainepaie) from GEN_PEPP),'YYYYMMDD') AND CB = P1.CB)
    AND    CF > TO_DATE((select max(dernierepaie) from GEN_PEPP),'YYYYMMDD')
    );

  delete stc 
  where WStc='O'
  and (mat,datestc) not in (
    SELECT DISTINCT 
    CB,TO_CHAR(CF,'YYYYMMDD')
    FROM   PEPP.PERZPRPL P1
    WHERE C05 = 'NON' 
    AND    (C06 = 'O' OR C06 = 'P' OR C07 = 'O') 
    AND    CF in ( SELECT CF FROM PEPP.PERZPR0 WHERE C05 = 'NON' AND CF <= TO_DATE((select max(prochainepaie) from GEN_PEPP),'YYYYMMDD') AND CB = P1.CB)
    AND    CF > TO_DATE((select max(dernierepaie) from GEN_PEPP),'YYYYMMDD')
    );

ev_logger(Wdatetrt,3,'ev_select_contrat_CS','Remplit les tables Contrat');
  execute immediate 'truncate table ev_contrat_pol_eta';
  execute immediate 'truncate table ev_contrat_pol';
  execute immediate 'truncate table ev_contrat_eta';
  execute immediate 'delete ev_contrat';

ev_logger(Wdatetrt,3.1,'ev_select_contrat_CS','  Table ev_contrat');
 -- On insere toutes les dates de début de contrat pour les porteurs
/*  insert into ev_contrat(mat,dRC)
  select distinct p.cb,to_char(p.cf,'YYYYMMDD')
  from perzprpl p
  where p.cf<=to_date(ppaie,'YYYYMMDD')
  and p.c05='OUI' and p.c06 in ('O','P')
  and (WStc='N' and not exists(select null from stc s where s.mat=p.cb and to_char(p.cf,'YYYYMMDD')<s.datestc and s.dextrait<Wdatetrt_org)
                and not exists(select null from stc_hst s where s.mat=p.cb and to_char(p.cf,'YYYYMMDD')<s.datestc and s.dextrait<Wdatetrt_org)
  -- On supprime les contrats antérieurs à une date de STC (ATTENTION, le STC se fait eta/eta ou globalement ???)
  or WStc='O' and (exists(select null from stc s where s.mat=p.cb and to_char(p.cf,'YYYYMMDD')<s.datestc and s.dextrait=Wdatetrt_org)
                or exists(select null from stc_hst s where s.mat=p.cb and to_char(p.cf,'YYYYMMDD')<s.datestc and s.dextrait=Wdatetrt_org))
  );
--  and exists(select null from ev_heures_recap t where p.cb=t.mat);
*/
  insert into ev_contrat(mat,dRC)
  select distinct p.cb,to_char(p.cf,'YYYYMMDD')
  from perzprpl_intdcs p
  where p.cf<=to_date(ppaie,'YYYYMMDD')
  and p.c05='OUI' and p.c06 in ('O','P')
  /*and (WStc='N' and not exists(select null from stc s where s.mat=p.cb and to_char(p.cff+1,'YYYYMMDD')=s.datestc and s.dextrait<Wdatetrt_org)
                and not exists(select null from stc_hst s where s.mat=p.cb and to_char(p.cff+1,'YYYYMMDD')=s.datestc and s.dextrait<Wdatetrt_org)
  or WStc='O' and (exists(select null from stc s where s.mat=p.cb and to_char(p.cff+1,'YYYYMMDD')=s.datestc and s.dextrait=Wdatetrt_org)
                or exists(select null from stc_hst s where s.mat=p.cb and to_char(p.cff+1,'YYYYMMDD')=s.datestc and s.dextrait=Wdatetrt_org))
  )*/;


  -- On positionne les dates de fin de contrat
  update ev_contrat e1 set fRC=(select to_char(min(e2.cf)-1,'YYYYMMDD') from perzprpl e2 where e1.mat=e2.cb and to_char(e2.cf,'YYYYMMDD')>e1.dRC and e2.c05='NON');
  update ev_contrat e set fRC=ppaie where fRC is null;
  -- On supprime les contrats ayant une date de fin antérieure au début de la période de paie (on ne peut pas mettre d'ev sur ce contrat)
  delete ev_contrat e where e.fRC<dpaie;

  -- On supprime les contrats en doublon (2 OUI à des dates consécutives ==> changement de CODE_DR)
  delete ev_contrat e1 where exists(select null from ev_contrat e2 where e1.mat=e2.mat and e1.dRC>e2.dRC and e1.fRC=e2.fRC);
  -- On supprime les contrats antérieurs à une date de STC (ATTENTION, le STC se fait eta/eta ou globalement ???)
--  delete ev_contrat e where exists(select null from stc s where s.mat=e.mat and e.fRC<s.datestc and s.extrait='O');
  delete ev_contrat e
  where (WStc='N' and (exists(select null from stc s where s.mat=e.mat and to_char(to_date(e.fRc,'YYYYMMDD')+1,'YYYYMMDD')=s.datestc and s.dextrait<Wdatetrt_org)
                or exists(select null from stc_hst s where s.mat=e.mat and to_char(to_date(e.fRc,'YYYYMMDD')+1,'YYYYMMDD')=s.datestc and s.dextrait<Wdatetrt_org))
  or WStc='O' and not exists(select null from stc s where s.mat=e.mat and to_char(to_date(e.fRc,'YYYYMMDD')+1,'YYYYMMDD')=s.datestc and s.dextrait=Wdatetrt_org)
                and not exists(select null from stc_hst s where s.mat=e.mat and to_char(to_date(e.fRc,'YYYYMMDD')+1,'YYYYMMDD')=s.datestc and s.dextrait=Wdatetrt_org)
  )
;
 
  update ev_contrat e set rc=(select rc.relatnum from PNG_salarie sal,PNG_relationcontrat rc  
  where rpad(trim(e.mat),8,'Z') = sal.matricule
    and sal.oid=rc.relatmatricule  
     and rc.relatdatedeb <= to_date(e.dRC,'YYYYMMDD')  and to_date(e.dRC,'YYYYMMDD')<=rc.relatdatefinW and rc.relatprincipal=1);

--------------------------------------------------------------------------------------------------------------------------------------------------
 ev_logger(Wdatetrt,3.2,'ev_select_contrat_CS','  Table ev_contrat_eta');
  insert into ev_contrat_eta(mat,eta,dRC,fRC,dCtr,rc)
  -- changement en cours de periode
  select distinct e.mat,trim(p.eta),e.dRC,e.fRC,to_char(p.cf,'YYYYMMDD'),e.rc
  from ev_contrat e,perzprpl p
  where e.mat=p.cb
  and e.dRC<=to_char(p.cf,'YYYYMMDD') and to_char(p.cf,'YYYYMMDD')<=e.fRC
  and p.c05='OUI' and c06 in ('O','P');

  -- On positionne les dates de fin de contrat
  update ev_contrat_eta e1 set fCtr=(select to_char(min(p.cf)-1,'YYYYMMDD') from perzprpl p where e1.mat=p.cb and to_char(p.cf,'YYYYMMDD')>e1.dCtr and (p.c05='NON' or trim(e1.eta)<>trim(p.eta)));
  update ev_contrat_eta e set fCtr=ppaie where fCtr is null;
  -- On supprime les contrats ayant une date de fin antérieure au début de la période de paie
  delete ev_contrat_eta e where e.fCtr<dpaie;

    -- On supprime les contrats en doublon (2 OUI à des dates consécutives ==> changement de CODE_DR)
  delete ev_contrat_eta e1 where exists(select null from ev_contrat_eta e2 where e1.mat=e2.mat and trim(e1.eta)=trim(e2.eta) and e1.dCtr>e2.dCtr and e1.fCtr=e2.fCtr);

  -- On maj la date de début de contrat avec la date de début de période
  update ev_contrat_eta e set d=greatest(dpaie,dCtr),f=least(ppaie,fCtr);


--------------------------------------------------------------------------------------------------------------------------------------------------
 ev_logger(Wdatetrt,3.3,'ev_select_contrat_CS','  Table ev_contrat_pol');
  insert into ev_contrat_pol(mat,pol,dRC,fRC,dCtr,rc)
  -- changement en cours de periode
  select distinct e.mat,decode(p.c06,'P','O','N'),e.dRC,e.fRC,to_char(p.cf,'YYYYMMDD'),e.rc
  from ev_contrat e,perzprpl p
  where e.mat=p.cb
  and e.dRC<=to_char(p.cf,'YYYYMMDD') and to_char(p.cf,'YYYYMMDD')<=e.fRC
  and p.c05='OUI' and c06 in ('O','P');

  -- On positionne les dates de fin de contrat
  update ev_contrat_pol e1 set fCtr=(select to_char(min(p.cf)-1,'YYYYMMDD') from perzprpl p where e1.mat=p.cb and to_char(p.cf,'YYYYMMDD')>e1.dCtr and (p.c05='NON' or e1.pol<>decode(p.c06,'P','O','N')));
  update ev_contrat_pol e set fCtr=ppaie where fCtr is null;
  -- On supprime les contrats ayant une date de fin antérieure au début de la période de paie
  delete ev_contrat_pol e where e.fCtr<dpaie;

    -- On supprime les contrats en doublon (2 OUI à des dates consécutives ==> changement de CODE_DR)
  delete ev_contrat_pol e1 where exists(select null from ev_contrat_pol e2 where e1.mat=e2.mat and e1.pol=e2.pol and e1.dCtr>e2.dCtr and e1.fCtr=e2.fCtr);

  -- On maj la date de début de contrat avec la date de début de période
  update ev_contrat_pol e set d=greatest(dpaie,dCtr),f=least(ppaie,fCtr);

--------------------------------------------------------------------------------------------------------------------------------------------------
 ev_logger(Wdatetrt,3.4,'ev_select_contrat_CS','  Table ev_contrat_pol_eta');
  insert into ev_contrat_pol_eta(mat,eta,pol,dRC,fRC,dCtr,rc)
  -- changement en cours de periode
  select distinct e.mat,trim(eta),decode(p.c06,'P','O','N'),e.dRC,e.fRC,to_char(p.cf,'YYYYMMDD'),e.rc
  from ev_contrat e,perzprpl p
  where e.mat=p.cb
  and e.dRC<=to_char(p.cf,'YYYYMMDD') and to_char(p.cf,'YYYYMMDD')<=e.fRC
  and p.c05='OUI' and c06 in ('O','P');

  -- On positionne les dates de fin de contrat
  update ev_contrat_pol_eta e1 set fCtr=(select to_char(min(p.cf)-1,'YYYYMMDD') from perzprpl p where e1.mat=p.cb and to_char(p.cf,'YYYYMMDD')>e1.dCtr and (p.c05='NON' or e1.pol<>decode(p.c06,'P','O','N') or trim(e1.eta)<>trim(p.eta)));
  update ev_contrat_pol_eta e set fCtr=ppaie where fCtr is null;
  -- On supprime les contrats ayant une date de fin antérieure au début de la période de paie
  delete ev_contrat_pol_eta e where e.fCtr<dpaie;

    -- On supprime les contrats en doublon (2 OUI à des dates consécutives ==> changement de CODE_DR)
  delete ev_contrat_pol_eta e1 where exists(select null from ev_contrat_pol_eta e2 where e1.mat=e2.mat and e1.pol=e2.pol and trim(e1.eta)=trim(e2.eta) and e1.dCtr>e2.dCtr and e1.fCtr=e2.fCtr);

  -- On maj la date de début de contrat avec la date de début de période
  update ev_contrat_pol_eta e set d=greatest(dpaie,dCtr),f=least(ppaie,fCtr);
end;

procedure ev_select_tournee(
  Wdatetrt ev_log.datetrt%type
,	Wdatetrt_org ev_log.datetrt%type
, WStc char
,	dpaie gen_pepp.dernierepaie%type
,	ppaie gen_pepp.dernierepaie%type
, W1M   char
) as
begin
  ev_logger(Wdatetrt,2,'ev_select_tournee'||W1M,'Sélection des tournées');
  ev_logger(Wdatetrt,2.1,'ev_select_tournee'||W1M,'Vide les tables de travail');
  execute immediate 'truncate table ev_tournees_detail';
  execute immediate 'delete ev_tournees';
  execute immediate 'truncate table ev_heures';
  execute immediate 'truncate table ev_reclamations';

  ev_logger(Wdatetrt,2.2,'ev_select_tournee'||W1M,'Extraction des tournées');
  if (W1M='1M') then
    if (WStc='N') then
      update tournees t set extrait='O',dextrait=Wdatetrt_org where t.extrait='N' and t.tagbloc='N' and t.datetr<=ppaie and t.datetr like '%0501';
      update heures h set extrait='O',dextrait=Wdatetrt_org where h.extrait='N' and h.tagbloc='N' and h.datetr<=ppaie and h.datetr like '%0501';
    else
      update tournees t set extrait='O',dextrait=Wdatetrt_org where t.extrait='N' and t.tagbloc='N' and t.datetr like '%0501' and exists(select null from stc s where t.mat=s.mat and s.dextrait=Wdatetrt_org and t.datetr<s.datestc);
      update heures h set extrait='O',dextrait=Wdatetrt_org where h.extrait='N' and h.tagbloc='N' and h.datetr like '%0501' and exists(select null from stc s where h.mat=s.mat and s.dextrait=Wdatetrt_org and h.datetr<s.datestc);
    end if;

    ev_logger(Wdatetrt,2.3,'ev_select_tournee_1M','Remplit les tables de travail');
    insert into ev_tournees(datetr,codetr,mat,nbkm,temps,valrem,eta,typejour,nbkm2,tnbrcli,tnuit)  select t.datetr,t.codetr,t.mat,t.nbkm,t.temps,t.valrem,t.eta,t.typejour,t.nbkm2,t.tnbrcli,t.tnuit from tournees t where t.dextrait=Wdatetrt_org and t.datetr like '%0501';
    update ev_tournees set typejour='S' where typejour=' ';
    insert into ev_tournees_detail(datetr,codetr,codetitre,codenatcli,typ4pc,nbrcli,nbrex,nbrspl,mat,eta,typejour)  select d.datetr,d.codetr,d.codetitre,d.codenatcli,d.typ4pc,d.nbrcli,d.nbrex,d.nbrspl,t.mat,t.eta,t.typejour from tournees t,tournees_detail d where t.dextrait=Wdatetrt_org and t.datetr like '%0501' and d.codetr=t.codetr and d.datetr=t.datetr ;

    insert into ev_heures(datetr,mat,codeactivite,codestatut,typejour,temps1,temps2,nbkm,qte,eta,codetr) select h.datetr,h.mat,h.codeactivite,h.codestatut,h.typejour,h.temps1,h.temps2,h.nbkm,h.qte,h.eta,h.codetr from heures h where h.dextrait=Wdatetrt_org and h.datetr like '%0501';

    ev_logger(Wdatetrt,2.4,'ev_select_tournee_1M','Supprime le type de jour F');
    -- Normalement déjà fait via les triggers
    update ev_tournees set typejour=(select decode(LIBJOUR,'Dimanche','D','S') from calendrier where datetr=datecal);
    update ev_heures set typejour=(select decode(LIBJOUR,'Dimanche','D','S') from calendrier where datetr=datecal);
    
    ev_logger(Wdatetrt,2.4,'ev_select_tournee_1M','Supprime les kilomètre');
    update ev_tournees set nbkm=0,nbkm2=0;
    update ev_heures set nbkm=0;

    ev_logger(Wdatetrt,2.4,'ev_select_tournee_1M','Supprime les heures d''attente');
    delete ev_heures where codeactivite='AT';
  else
    if (WStc='N') then
      update tournees t set extrait='O',dextrait=Wdatetrt_org where t.extrait='N' and t.tagbloc='N' and t.datetr between dpaie and ppaie;
      update heures h set extrait='O',dextrait=Wdatetrt_org where h.extrait='N' and h.tagbloc='N' and h.datetr between dpaie and ppaie;
    else
      update tournees t set extrait='O',dextrait=Wdatetrt_org where t.extrait='N' and t.tagbloc='N' and exists(select null from stc s where t.mat=s.mat and s.dextrait=Wdatetrt_org and t.datetr<s.datestc);
      update heures h set extrait='O',dextrait=Wdatetrt_org where h.extrait='N' and h.tagbloc='N' and exists(select null from stc s where h.mat=s.mat and s.dextrait=Wdatetrt_org and h.datetr<s.datestc);
    end if;

    ev_logger(Wdatetrt,2.3,'ev_select_tournee','Remplit les tables de travail');
    insert into ev_tournees(datetr,codetr,mat,nbkm,temps,valrem,eta,typejour,nbkm2,tnbrcli,tnuit)  select t.datetr,t.codetr,t.mat,t.nbkm,t.temps,t.valrem,t.eta,t.typejour,t.nbkm2,t.tnbrcli,t.tnuit from tournees t where t.dextrait=Wdatetrt_org and t.datetr not like '%0501';
    update ev_tournees set typejour='S' where typejour=' ';
    insert into ev_tournees_detail(datetr,codetr,codetitre,codenatcli,typ4pc,nbrcli,nbrex,nbrspl,mat,eta,typejour)  select d.datetr,d.codetr,d.codetitre,d.codenatcli,d.typ4pc,d.nbrcli,d.nbrex,d.nbrspl,t.mat,t.eta,t.typejour from tournees t,tournees_detail d where t.dextrait=Wdatetrt_org and t.datetr not like '%0501' and d.codetr=t.codetr and d.datetr=t.datetr ;
 
    insert into ev_heures(datetr,mat,codeactivite,codestatut,typejour,temps1,temps2,nbkm,qte,eta,codetr) select h.datetr,h.mat,h.codeactivite,h.codestatut,h.typejour,h.temps1,h.temps2,h.nbkm,h.qte,h.eta,h.codetr from heures h where h.dextrait=Wdatetrt_org and h.datetr not like '%0501';
    -- ATTENTION, normalement, il faut prendre avec datepaiefm=ppaie
    -- Comment faire si la reclamation n'est pas rattachée à une tournée ???
    insert into ev_reclamations(datepaiefm,datetr,codetr,codetitre,nbrecab,nbannab,nbrecdif,nbanndif,indicinc,anninc,nomuser,datemaj,codetitredcs,mat,eta,typejour)    
    select r.datepaiefm,r.datetr,r.codetr,r.codetitre,r.nbrecab,r.nbannab,r.nbrecdif,r.nbanndif,r.indicinc,r.anninc,r.nomuser,r.datemaj,r.codetitredcs,t.mat,t.eta,t.typejour
    from ev_tournees t, reclamations r
    where t.codetr=r.codetr and t.datetr=r.datetr
    ;
  end if;
/*
  ev_logger(Wdatetrt,2.5,'ev_select_tournee','Erreur sur matricule chef');
  delete ev_log where datetrt=Wdatetrt and module='ev_select_tournee'||W1M and num<0;

  insert into ev_log(datetrt,datesys,num,module,msg)
  select Wdatetrt,systimestamp,-2.1,'ev_select_tournee'||W1M,'Tournée Chef '||t.codetr||' pour le matricule '||t.mat||' le '||t.datetr from ev_tournees t where t.mat like 'ZZZ%';
  insert into ev_log(datetrt,datesys,num,module,msg)
  select Wdatetrt,systimestamp,-2.2,'ev_select_tournee'||W1M,'Heure Chef '||h.codetr||' pour le matricule '||h.mat||' le '||h.datetr from ev_heures h where h.mat like 'ZZZ%';
*/
  delete ev_reclamations    where mat like 'ZZZ%';
  delete ev_tournees_detail where mat like 'ZZZ%';
  delete ev_tournees        where mat like 'ZZZ%';
  delete ev_heures          where mat like 'ZZZ%';
end;

procedure ev_deselect_tournee(
  Wdatetrt ev_log.datetrt%type
,	Wdatetrt_org ev_log.datetrt%type
, WStc char
,	dpaie gen_pepp.dernierepaie%type
,	ppaie gen_pepp.dernierepaie%type
, W1M   char
) as
begin
  ev_logger(Wdatetrt,2,'ev_deselect_tournee'||W1M,'DeSélection des tournées');
      update tournees t set extrait='N',dextrait=Null where dextrait=Wdatetrt_org;
      update heures h set extrait='N',dextrait=Null where dextrait=Wdatetrt_org;

end;

procedure ev_maj_qualite_DF(
  Wdatetrt      ev_log.datetrt%type
,	dpaie         gen_pepp.dernierepaie%type
,	ppaie         gen_pepp.dernierepaie%type
, W1M           char
) as
Wlibjour calendrier.libjour%type;
Wdimanche calendrier.datecal%type;
begin
  ev_logger(Wdatetrt,2.4,'ev_maj_qualite_DF','Table de travail pour la qualite Dimanche/Férié');
  execute immediate 'truncate table ev_qualite_DF';

  select libjour into Wlibjour  from calendrier where datecal=dpaie;
  if (W1M='1M' and Wlibjour='Dimanche') then
    execute immediate 'truncate table ev_qualite_1M';

    select max(datecal) into Wdimanche  from calendrier where datecal<=dpaie and libjour='Dimanche';

    insert into ev_qualite_1M
    select t.eta,t.datetr,t.codetr,t.mat,t.tnbrcli,
    case when r.datetr is null then 1
    when sum(r.nbrecab-r.nbannab)<=0 and sum(r.nbrecdif-r.nbanndif)<=0 and sum(r.indicinc-r.anninc)<=0 then 1
    else 0.5
    end as qualite
    from tournees t, reclamations r
    where t.mat not like 'ZZZ%'
    and t.codetr=r.codetr(+) and t.datetr=r.datetr(+)
    and   t.typejour in ('D','F')
    and  (t.datetr=to_char(to_date(Wdimanche,'YYYYMMDD'),'YYYYMMDD')
    or  t.datetr=to_char(to_date(Wdimanche,'YYYYMMDD')-7,'YYYYMMDD')
    or   t.datetr=to_char(to_date(Wdimanche,'YYYYMMDD')-14,'YYYYMMDD')
    or   t.datetr=to_char(to_date(Wdimanche,'YYYYMMDD')-21,'YYYYMMDD'))
    group by t.eta, t.datetr, t.codetr, t.mat, t.tnbrcli,r.datetr;
    
    -- si plusieurs tournées ont été validées pour un même porteur sur un même dimanche, c'est celle qui aura le plus grand nombre de clients qui devra être prise en compte
    delete ev_qualite_1M 
    where (mat,datetr) in (select e.mat,e.datetr from ev_qualite_1M e group by e.mat, e.datetr having count(*)>1)
    and (mat,datetr,tnbrcli) not in (select e.mat,e.datetr,max(tnbrcli) from ev_qualite_1M e group by e.mat, e.datetr having count(*)>1);

    -- Table utilisée dans la vue ev_taux pour le calcul de la majoration dans le serpentin
    insert into ev_qualite_DF(datetr,mat/*,codetr*/,qualite)     
    select ppaie,t.mat/*,t.codetr*/,
    -- l'individu n'apas de tournées sur le mois précédent
    case when count(q.datetr)=0 then g.TxnPort
    else 1+sum(nvl(q.qualite,0))/count(q.datetr)
    end
    from ev_tournees t,ev_qualite_1M q, gen_pepp g
    where t.mat=q.mat(+)
    and   t.typejour in ('D','F')
    group by ppaie, t.mat,g.TxnPort;
    
    -- sauvegarde de la table
    delete ev_qualite_DF_1M;
    insert into ev_qualite_DF_1M select * from ev_qualite_DF;
/*
select p.eta,q.mat,m1.qualite,m2.qualite,m3.qualite,m4.qualite,q.qualite 
from ev_qualite_DF_1M q,perzprpl_intdcs p,ev_qualite_1M m1 ,ev_qualite_1M m2 ,ev_qualite_1M m3 ,ev_qualite_1M m4 
where q.mat=p.cb and p.cf<=to_date('20110501','YYYYMMDD') and p.cff>=to_date('20110501','YYYYMMDD') 
and q.mat=m1.mat(+) and '20110327'=m1.datetr (+)
and q.mat=m2.mat(+) and '20110403'=m2.datetr (+)
and q.mat=m3.mat(+) and '20110410'=m3.datetr (+)
and q.mat=m4.mat(+) and '20110417'=m4.datetr (+)
order by eta,p.cb;
*/
  else
    -- Table utilisée dans la vue ev_taux pour le calcul de la majoration dans le serpentin
    insert into ev_qualite_DF(datetr,mat/*,codetr*/,qualite)     
    select t.datetr,t.mat/*,t.codetr*/,
    case
    -- Il n'y a pas de réclamation
    when count(r.datetr)=0 then g.TxqPort
    -- Toutes lesréclamations sont annulées
    when sum(r.nbrecab-r.nbannab)<=0 and sum(r.nbrecdif-r.nbanndif)<=0 and sum(r.indicinc-r.anninc)<=0  then g.TxqPort
    -- Il y a des réclamations
    else g.TxnPort
    end
    from ev_tournees t,ev_reclamations r,gen_pepp g
    where t.codetr=r.codetr(+) and t.datetr=r.datetr(+) 
    and   t.typejour in ('D','F')
    group by t.datetr, t.mat, /*t.codetr,*/g.TxqPort,g.TxnPort;
  end if;
  delete ev_qualite_DF      where mat like 'ZZZ%';
end;

procedure ev_maj_contrat(
  Wdatetrt      ev_log.datetrt%type
, W1M           char
) as
begin
  if (W1M='1M') then
    ev_logger(Wdatetrt,3.5,'ev_maj_contrat_1M','  Colonne tnbkm et tnbrcli sur Table ev_contrat_eta');
      update ev_contrat_eta e set (tnbrcli)=(select nvl(sum(tnbrcli),0) from ev_tournees t where e.dCtr<=t.datetr and t.datetr<=e.f and t.mat=e.mat and t.eta=e.eta group by e.mat);
      update ev_contrat_eta e set tnbkm=0;
  else
    ev_logger(Wdatetrt,3.5,'ev_maj_contrat','  Colonne tnbkm et tnbrcli sur Table ev_contrat_eta');
      update ev_contrat_eta e set (tnbkm,tnbrcli)=(select nvl(sum(nbkm),0),nvl(sum(tnbrcli),0) from ev_tournees t where e.dCtr<=t.datetr and t.datetr<=e.f and t.mat=e.mat and t.eta=e.eta group by e.mat);
    end if;
end;

procedure ev_erreur(
  Wdatetrt ev_log.datetrt%type
, dpaie gen_pepp.dernierepaie%type
,	ppaie gen_pepp.dernierepaie%type
) as
begin
ev_logger(Wdatetrt,4,'ev_erreur','Log des erreurs bloquantes');
/*
ev_log('1 Titre Hors ferié non ventilé (4%=X)');
ev_log('2 Titre Ferié non ventilé (4%=X)');
ev_log('3 Supplément non ventilé (4%=X)');
ev_log('4 Kilomètre non ventilé pour le titre');
ev_log('5 Kilomètre non ventilé pour l''activité');
ev_log('11 Tournées Hors Contrat');
ev_log('12 Détail Tournée Hors Contrat');
ev_log('13 Réclamaion Hors Contrat');
ev_log('14 Heure Hors Contrat');
ev_log('21 Tournée Chef');
ev_log('22 Heure Chef');
*/
ev_logger(Wdatetrt,4.1,'ev_erreur','Erreur sur tournée hors contrat');
  insert into ev_log(datetrt,datesys,num,module,msg)
  select distinct Wdatetrt,systimestamp,-4.1,'ev_erreur','Tournée hors contrat '||t.codetr||' pour le matricule '||t.mat||' le  '||t.datetr from ev_tournees t
  where not exists (select null from ev_contrat_eta e where e.dCtr<=t.datetr and t.datetr<=e.f and e.mat=t.mat and e.eta=t.eta);

  insert into ev_log(datetrt,datesys,num,module,msg)
  select distinct Wdatetrt,systimestamp,-4.2,'ev_erreur','Détail tournée hors contrat '||d.codetr||' pour le matricule '||d.mat||' le  '||d.datetr from ev_tournees_detail d
  where not exists (select null from ev_contrat_eta e where e.dCtr<=d.datetr and d.datetr<=e.f and e.mat=d.mat and e.eta=d.eta);

  insert into ev_log(datetrt,datesys,num,module,msg)
  select distinct Wdatetrt,systimestamp,-4.3,'ev_erreur','Réclamation hors contrat '||r.codetr||' pour le matricule '||r.mat||' le  '||r.datetr from ev_reclamations r
  where not exists (select null from ev_contrat_eta e where e.dCtr<=r.datetr and r.datetr<=e.f and e.mat=r.mat and e.eta=r.eta);

  insert into ev_log(datetrt,datesys,num,module,msg)
  select distinct Wdatetrt,systimestamp,-4.4,'ev_erreur','Heure hors contrat '||t.codetr||' pour le matricule '||t.mat||' le  '||t.datetr from ev_heures t
  where not exists (select null from ev_contrat_eta e where e.dCtr<=t.datetr and t.datetr<=e.f and e.mat=t.mat and e.eta=t.eta);

  delete ev_tournees_detail d where not exists (select null from ev_contrat_eta e where e.dCtr<=d.datetr and d.datetr<=e.f and e.mat=d.mat and e.eta=d.eta);
  delete ev_reclamations    r where not exists (select null from ev_contrat_eta e where e.dCtr<=r.datetr and r.datetr<=e.f and e.mat=r.mat and e.eta=r.eta);
  delete ev_tournees        t where not exists (select null from ev_contrat_eta e where e.dCtr<=t.datetr and t.datetr<=e.f and e.mat=t.mat and e.eta=t.eta);
  delete ev_heures          t where not exists (select null from ev_contrat_eta e where e.dCtr<=t.datetr and t.datetr<=e.f and e.mat=t.mat and e.eta=t.eta);

  delete ev_log where datetrt=Wdatetrt and module='ev_warning';
  insert into ev_log(datetrt,datesys,num,module,msg)
  select Wdatetrt,systimestamp,-4.5,'ev_warning','Mutation du matricule '||mat||' dans l''établissement '||eta||', le '||substr(dctr,7,2)||'/'||substr(dctr,5,2)||'/'||substr(dctr,1,4)  from ev_contrat_eta where dctr<>drc and dctr>dpaie;

  insert into ev_log(datetrt,datesys,num,module,msg)
  select Wdatetrt,systimestamp,-4.6,'ev_warning','Passage au statut '||decode(pol,'O','Polyvalent','Porteur')||' du matricule '||mat||', le '||substr(dctr,7,2)||'/'||substr(dctr,5,2)||'/'||substr(dctr,1,4)  from ev_contrat_pol where dctr<>drc and dctr>dpaie;
end;

procedure ev_calcul(
  Wdatetrt ev_log.datetrt%type
, WStc char
, dpaie gen_pepp.dernierepaie%type
,	ppaie gen_pepp.dernierepaie%type
, W1M char
) as
begin
  ev_logger(Wdatetrt,5.1,'ev_calcul'||W1M,'Heures Semaine Porteur');
  ev_calcul_heure_jour('HRS','S');
  ev_logger(Wdatetrt,5.2,'ev_calcul'||W1M,'Heures Dimanche Porteur');
  ev_calcul_heure_jour('HRD','D');
  ev_logger(Wdatetrt,5.3,'ev_calcul'||W1M,'Heures JF Porteur');
  ev_calcul_heure_jour('HRF','F');
  ev_logger(Wdatetrt,5.4,'ev_calcul'||W1M,'Heures Nuit Dimanche  4%');
  ev_calcul_heure_nuit('TD4','D','O');
  ev_logger(Wdatetrt,5.5,'ev_calcul'||W1M,'Heures Nuit Dimanche  non 4%');
  ev_calcul_heure_nuit('TDN','D','N');
  ev_logger(Wdatetrt,5.6,'ev_calcul'||W1M,'Heures Nuit Sem+Ferié 4%');
  ev_calcul_heure_nuit('TS4','S','O');
  ev_logger(Wdatetrt,5.7,'ev_calcul'||W1M,'Heures Nuit Sem+Ferié non 4%');
  ev_calcul_heure_nuit('TSN','S','N');
  ev_logger(Wdatetrt,5.8,'ev_calcul'||W1M,'Correction des heures de nuit');

  ev_corrige_heure_nuit('N');

  ev_logger(Wdatetrt,6.2,'ev_calcul'||W1M,'NA1	Serpentins Sem N4pc 1	0500');
  ev_calcul_serpentin(Wdatetrt,'NA1','N','S');
  ev_logger(Wdatetrt,6.3,'ev_calcul'||W1M,'N41	Serpentins Sem 4pc 1	0503');
  ev_calcul_serpentin(Wdatetrt,'N41','O','S');
  ev_logger(Wdatetrt,6.4,'ev_calcul'||W1M,'DA1	Serpentins Dim N4pc 1	0506');
  ev_calcul_serpentin(Wdatetrt,'DA1','N','D');
  ev_logger(Wdatetrt,6.5,'ev_calcul'||W1M,'D41	Serpentins Dim 4pc 1	0509');
  ev_calcul_serpentin(Wdatetrt,'D41','O','D');
  ev_logger(Wdatetrt,6.6,'ev_calcul'||W1M,'FA1	Serpentins Fer N4pc 1	0512');
  ev_calcul_serpentin(Wdatetrt,'FA1','N','F');
  ev_logger(Wdatetrt,6.7,'ev_calcul'||W1M,'F41	Serpentins Fer 4pc 1	0515');
  ev_calcul_serpentin(Wdatetrt,'F41','O','F');

  ev_logger(Wdatetrt,7.4,'ev_calcul'||W1M,'titre NG');
  ev_calcul_titre(Wdatetrt,'NB4','O');
  ev_calcul_titre(Wdatetrt,'NBN','N');
  ev_logger(Wdatetrt,7.5,'ev_calcul'||W1M,'supplément NG');
  ev_calcul_supplement(Wdatetrt,'SP4','O');
  ev_calcul_supplement(Wdatetrt,'SPN','N');
  ev_logger(Wdatetrt,7.6,'ev_calcul'||W1M,'kilometre NG');
  ev_calcul_kilometre(Wdatetrt,'KMS','S');
  ev_calcul_kilometre(Wdatetrt,'KMD','D');

  ev_logger(Wdatetrt,7.4,'ev_calcul'||W1M,'activite');
  ev_calcul_activite_temps1(Wdatetrt);
  ev_calcul_activite_temps2(Wdatetrt);
  ev_calcul_activite_quantite(Wdatetrt);

  if (W1M<>'1M') then
    ev_logger(Wdatetrt,7.5,'ev_calcul'||W1M,'majoration heures travaillées');
    ev_calcul_majoration_HT();
    ev_logger(Wdatetrt,7.6,'ev_calcul'||W1M,'majoration heures de jour');
    ev_calcul_majoration_HJ();
    ev_logger(Wdatetrt,7.7,'ev_calcul'||W1M,'majoration jours ouvrables travaillés');
    ev_calcul_majoration_JT();
    ev_logger(Wdatetrt,7.8,'ev_calcul'||W1M,'majoration jours ouvrables periode');
    ev_calcul_majoration_JO(dpaie,ppaie);
  -- majoration des heures complementaires / supplemenaires
    --ev_majoration();

    ev_logger(Wdatetrt,8.1,'ev_calcul'||W1M,'prime');
    ev_calcul_prime(Wdatetrt);
    ev_logger(Wdatetrt,8.1,'ev_calcul'||W1M,'bonus');
    ev_calcul_bonus(WStc,dpaie,ppaie);
  end if;
end;

procedure ev_calcul_heure_jour(
  Wcodepaie poste_paie.codepaieple%type
, Wtypejour tournees.typejour%type
) as
begin
  --delete from ev where codepaie in (select p.codepaieple from poste_paie p where p.codepaie=Wcodepaie);

  insert into ev(typev,mat,rc,codepaie,datev,qte,taux,val,lib)
  select 'HJOUR',e.mat,e.rc,p.codepaieple,e.d,sum(t.temps),0,0,p.libcodepaie
  from ev_contrat_eta e,ev_tournees t,poste_paie p
  where e.mat=t.mat and e.eta=t.eta and e.dCtr<=t.datetr and t.datetr<=e.f
  and p.codepaie=Wcodepaie
  and t.typejour=Wtypejour
  group by 'HJOUR', e.mat, e.rc, p.codepaieple, e.d, 0, 0, p.libcodepaie having sum(t.temps)<>0;
end;

procedure ev_calcul_heure_nuit(
  Wcodepaie poste_paie.codepaieple%type
, Wtypejour tournees.typejour%type
, Wtyp4pc tournees.typejour%type
) as
begin
  --delete from ev where codepaie in (select p.codepaieple from poste_paie p where p.codepaie=Wcodepaie);

  insert into ev(typev,mat,rc,codepaie,datev,qte,taux,val,lib)
  select 'HNUIT',e.mat,e.rc,p.codepaieple,e.d,sum(d.nbrcli*t.tnuit/t.tnbrcli),0,0,p.libcodepaie
  from ev_contrat_eta e,ev_tournees t,ev_tournees_detail d,poste_paie p,calendrier c
  where e.mat=t.mat and e.eta=t.eta and e.dCtr<=t.datetr and t.datetr<=e.f
  and d.codetr=t.codetr and d.datetr=t.datetr
  and p.codepaie=Wcodepaie
  and d.typ4pc=Wtyp4pc
  -- On passe par le calendrier pour ne pas tenir compte du typeJour Ferié se trouvant dans d.typeJour
  and c.datecal=t.datetr and ((Wtypejour='D' and c.libjour='Dimanche') or (Wtypejour<>'D' and c.libjour<>'Dimanche'))
  group by 'HNUIT', e.mat, e.rc, p.codepaieple, e.d, 0, 0, p.libcodepaie having sum(t.tnuit*d.nbrcli/t.tnbrcli)<>0;
end;

procedure ev_corrige_heure_nuit(
  premierMai char
) as
begin
 update ev ek
  set qte=qte+(select sum(tnuit) 
              from ev_contrat_eta e,ev_tournees t 
              where e.mat=ek.mat and e.d=ek.datev and e.mat=t.mat and e.eta=t.eta and e.dCtr<=t.datetr and t.datetr<=e.f 
              group by e.mat,e.d)
             -(select sum(qte) 
              from ev ev3 
              where ev3.mat=ek.mat and ev3.datev=ek.datev and ev3.typev=ek.typev 
              group by ev3.mat,ev3.datev,ev3.typev)
  where (qte) in (select max(qte) 
                  from ev ev2 
                  where ek.mat=ev2.mat and ev2.datev=ek.datev and ev2.typev=ek.typev 
                  )
  and ek.typev='HNUIT'  -- Si on est le 1er mai, on corrige que les ev en 0501, sinon toutes les autres
  and ((ek.datev like '%0501' and premierMai='O') or (ek.datev not like '%0501' and premierMai<>'O'));
end;

procedure ev_calcul_serpentin(
  Wdatetrt ev_log.datetrt%type
, Wcodepaie poste_paie.codepaieple%type
, Wtyp4pc   tournees_detail.typ4pc%type
, Wtypejour tournees.typejour%type
) as
 Wcodepaieple char(10);
 WdateSmic char(8);
 Wordre numeric(3,0);
 Wmat char(10);
 Wdatetr char(8);
 cursor m is select distinct mat from ev_serpentin where codepaie=Wcodepaieple;
 cursor e is select * from ev_serpentin where mat=Wmat and codepaie=Wcodepaieple order by d,datesmic,datetr,codetr;
begin
  select codepaieple into Wcodepaieple from poste_paie where codepaie= wcodepaie;

  insert into ev_log(datetrt,datesys,num,module,msg)
  select distinct Wdatetrt,systimestamp,-6.1,'ev_calcul_serpentin '||Wcodepaie,'Tournée '||t.codetr||' Matricule '||t.mat||' Date '||to_char(to_date(t.datetr,'YYYYMMDD'),'DD/MM/YYYY')||' Valrem '||to_char(t.valrem,'0.00000')||' Valrem2 '||to_char(t.valrem2,'0.00000')||' Rejetée'
  from ev_taux t,poste_paie p
  where p.codepaie=Wcodepaie
  and t.typ4pc=Wtyp4pc and t.typejour=Wtypejour
  and t.valrem2<=0;

  --delete ev_serpentin where codepaie=Wcodepaieple;
  execute immediate 'truncate table ev_serpentin';
  
  -- ATTENTION, ne pas mettre les coefficients en dur
  -- Taux de majoraiton dans genzmj
  -- MPY  = 10%   = majoration porteur polyvalent
  -- NAU  = ??
  -- NCE  = 3.18% = majoration heure de nuit chef (inutilisé)
  -- NPO  = 5%    = majoration heure de nuit porteur

  -- La majoration ne doit pas dépasser 100% (avec les heures de nuit)
  -- Impact-hnuit rounded =     (H-temps2 *  TxNuit)  /  H-Temps1.
  -- Si Taux + impact-hnuit  > 1.00 Alors compute  Taux rounded = 1.00 - Impact-hnuit.

  insert into ev_serpentin(mat,rc,codepaie,datetr,codetr,datesmic,d,f,qte,taux,val,lib)
  -- ATTENTION, on regroupe le serpention sur la periode de changement de statut PORTEUR/POLYVALENT, et non sur la periode contractuelle
  -- on regroupe avec la valeur de rem ramenée à 2 décimales
  select t.mat,t.rc,p.codepaieple,min(t.datetr),min(t.codetr),s.datedeb,t.d,t.f
  ,sum(t.nbrcli)
  ,round(avg(t.valrem2),3)
  ,round(sum(t.nbrcli*t.valrem2),2)
  ,p.libcodepaie
  from ev_taux t,poste_paie p,gensmic s
  where p.codepaie=Wcodepaie
  and t.typ4pc=Wtyp4pc and t.typejour=Wtypejour
  and s.datedeb<=t.datetr and t.datetr<=s.datefin
  and t.valrem2>0
  group by t.mat, t.rc, p.codepaieple, s.datedeb, t.d, t.f, p.libcodepaie,to_char(t.valrem2*1000,'FM0000000000')
  having sum(t.nbrcli)<>0;

  for jm in m loop
   Wmat:=jm.mat;
   Wdatetr:=null;
   for je in e loop
    if Wdatetr<je.d or Wdatetr is null then
      Wdatetr:=je.d;
      Wordre:=1;
    end if;
    if Wdatetr<je.datesmic then
      Wdatetr:=je.datesmic;
      Wordre:=1;
    end if;
    --dbms_output.put_line(je.mat||' '||WcodepaieMat||' '||Wdatetr||' '||je.val||' '||je.qte||' '||je.taux||' '||je.res);
     insert into ev(typev,mat,rc,codepaie,datev,ordre,qte,taux,val,lib,res) values('SERPENT NG',je.mat,je.rc,Wcodepaieple,Wdatetr,Wordre,je.qte,je.taux,je.val,je.lib,je.res);
     Wordre:=Wordre+1;
     if Wordre>99 then
        ev_logger(Wdatetrt,-5.1,'ev_calcul_serpentin','Numéro d''ordre maximum atteind ('||Wcodepaieple||') pour '||je.mat||' le '||Wdatetr);
        raise_application_error(-20001,'Numéro d''ordre maximum atteind ('||Wcodepaieple||') pour '||je.mat||' le '||Wdatetr);
     end if;
    end loop;
  end loop;
end;

procedure ev_calcul_titre(
  Wdatetrt ev_log.datetrt%type
, Wcodepaie poste_paie.codepaie%type
, Wtyp4pc   tournees_detail.typ4pc%type
) as
begin
  --delete from ev where codepaie in (select codepaieple2 from poste_paie where codepaie=Wcodepaie);

  insert into ev(typev,mat,rc,codepaie,datev,qte,taux,val,lib)
  select 'TITRE NG',e.mat,e.rc,p.codepaieple,e.d,sum(d.nbrex),0,0,min(p.LIBCODEPAIE)
  from ev_contrat_eta e,ev_tournees_detail d,poste_paie p
  where e.mat=d.mat and e.eta=d.eta and e.dCtr<=d.datetr and d.datetr<=e.f
  and d.typ4pc=Wtyp4pc
  and p.codepaie=Wcodepaie
  group by 'TITRE NG', e.mat, e.rc, p.codepaieple, e.d, 0, 0 having sum(d.nbrex)<>0;
end;

procedure ev_calcul_supplement(
  Wdatetrt ev_log.datetrt%type
, Wcodepaie poste_paie.codepaie%type
, Wtyp4pc   tournees_detail.typ4pc%type
) as
begin
  --delete from ev where codepaie in (select codepaieple2 from poste_paie_titre where codepaie2=Wcodepaie);

  insert into ev_log(datetrt,datesys,num,module,msg) 
  select Wdatetrt,systimestamp,0,'ev_calcul_supplement','Poste de paie introuvable pour ' || Wcodepaie || ' et ' || d.codetitre
  from ev_contrat_eta e,ev_tournees_detail d
  where e.mat=d.mat and e.eta=d.eta and e.dCtr<=d.datetr and d.datetr<=e.f
  and d.typ4pc=Wtyp4pc
  and not exists(select null from poste_paie_titre p where p.codepaie2=Wcodepaie and p.codetitre=d.codetitre)
  and d.nbrspl<>0
  ;
  insert into ev(typev,mat,rc,codepaie,datev,qte,taux,val,lib)
  select 'SUP NG',e.mat,rc,p.codepaieple2,e.d,sum(d.nbrspl),0,0,max(p.LIBECODEPAIE2)
  from ev_contrat_eta e,ev_tournees_detail d,poste_paie_titre p
  where e.mat=d.mat and e.eta=d.eta and e.dCtr<=d.datetr and d.datetr<=e.f
  and d.typ4pc=Wtyp4pc
  and p.codepaie2=Wcodepaie and p.codetitre=d.codetitre
  group by 'SUP NG', e.mat, e.rc, p.codepaieple2, e.d, 0, 0 
  having sum(d.nbrspl)<>0;
/*
  insert into ev(typev,mat,rc,codepaie,datev,qte,taux,val,lib)
  select 'SUP NG',e.mat,rc,p.codepaieple,e.d,sum(d.nbrspl),0,0,max(p.LIBCODEPAIE)
  from ev_contrat_eta e,ev_tournees_detail d,poste_paie p
  where e.mat=d.mat and e.eta=d.eta and e.dCtr<=d.datetr and d.datetr<=e.f
  and d.typ4pc=Wtyp4pc
  and p.codepaie=Wcodepaie
  group by 'SUP NG', e.mat, e.rc, p.codepaieple, e.d, 0, 0 having sum(d.nbrspl)<>0;
*/
end;

procedure ev_calcul_kilometre(
  Wdatetrt ev_log.datetrt%type
, Wcodepaie poste_paie.codepaie%type
, Wtypejour tournees.typejour%type
)
as
begin
  --delete from ev where codepaie in (select codepaieple2 from poste_paie where codepaie=Wcodepaie);

  delete ev_km;
  insert into ev_km(mat,rc,datev,codepaie,qte,lib)
  select e.mat,e.rc,e.d,p.codepaieple,sum(t.nbkm),p.LIBCODEPAIE
  from ev_contrat_eta e,ev_tournees t,poste_paie p,calendrier c
  where e.mat=t.mat and e.eta=t.eta and e.dCtr<=t.datetr and t.datetr<=e.f
  and   p.codepaie=Wcodepaie
  and   c.datecal=t.datetr and ((Wtypejour='D' and c.libjour='Dimanche') or (Wtypejour<>'D' and c.libjour<>'Dimanche'))
  group by e.mat, e.rc, e.d, p.codepaieple, p.LIBCODEPAIE
  having sum(t.nbkm)<>0;

  -- On ajoute les activités
  insert into ev_km(mat,rc,datev,codepaie,qte,lib)
  select e.mat,e.rc,e.d,p.codepaieple,sum(h.nbkm),p.LIBCODEPAIE
  from ev_contrat_eta e,ev_heures h,poste_paie p,calendrier c
  where e.mat=h.mat and e.eta=h.eta and e.dCtr<=h.datetr and h.datetr<=e.f
  and   p.codepaie=Wcodepaie
  and   c.datecal=h.datetr and ((Wtypejour='D' and c.libjour='Dimanche') or (Wtypejour<>'D' and c.libjour<>'Dimanche'))
  group by e.mat, e.rc, e.d, p.codepaieple, p.LIBCODEPAIE
  having sum(h.nbkm)<>0;

  insert into ev(typev,mat,rc,codepaie,datev,qte,taux,val,lib)
  select 'KM NG',e.mat,e.rc,e.codepaie,e.datev,sum(e.qte),0,0,min(e.lib)
  from ev_km e
  group by 'KM NG', e.mat, e.rc, e.codepaie, e.datev, 0, 0
  having sum(e.qte)<>0;
end;

procedure ev_calcul_activite_temps1(Wdatetrt ev_log.datetrt%type) as
begin
--  delete from ev where codepaie in (select codepaiepletemps1 from poste_paie_activite);
  insert into ev_log(datetrt,datesys,num,module,msg) 
  select Wdatetrt,systimestamp,0,'ev_calcul_activite_temps1','Poste de paie introuvable pour ' || h.codeactivite || ' et ' || h.typejour || ' et ' || h.codestatut
  from ev_contrat_eta e,ev_heures h
  where e.mat=h.mat and e.eta=h.eta and e.dCtr<=h.datetr and h.datetr<=e.f
  and not exists(select null from poste_paie_activite p where h.codeactivite=p.codeactivite and h.typejour= p.codetypejour and h.codestatut= p.codestatut and trim(p.codepaiepletemps1) is not null)
  and h.temps1<>0
  ;
  insert into ev(typev,mat,rc,codepaie,datev,qte,taux,val,lib)
  select 'TEMPS1 NG',e.mat,e.rc,p.codepaiepletemps1,e.d,sum(h.temps1),0,0,min(LIBECODEPAIE3)
  from ev_contrat_eta e,ev_heures h,poste_paie_activite p
  where e.mat=h.mat and e.eta=h.eta and e.dCtr<=h.datetr and h.datetr<=e.f
  and h.codeactivite=p.codeactivite and h.typejour= p.codetypejour and h.codestatut= p.codestatut
  and trim(p.codepaiepletemps1) is not null
  group by 'TEMPS1 NG', e.mat, e.rc, p.codepaiepletemps1, e.d, 0, 0 having sum(h.temps1)<>0;
end;

procedure ev_calcul_activite_temps2(Wdatetrt ev_log.datetrt%type) as
begin
--  delete from ev where codepaie in (select codepaiepletemps2 from poste_paie_activite);
  insert into ev_log(datetrt,datesys,num,module,msg) 
  select Wdatetrt,systimestamp,0,'ev_calcul_activite_temps2','Poste de paie introuvable pour ' || h.codeactivite || ' et ' || h.typejour || ' et ' || h.codestatut
  from ev_contrat_eta e,ev_heures h
  where e.mat=h.mat and e.eta=h.eta and e.dCtr<=h.datetr and h.datetr<=e.f
  and not exists(select null from poste_paie_activite p where h.codeactivite=p.codeactivite and h.typejour= p.codetypejour and h.codestatut= p.codestatut and trim(p.codepaiepletemps2) is not null)
  and h.temps2<>0
  ;
  insert into ev(typev,mat,rc,codepaie,datev,qte,taux,val,lib)
  select 'TEMPS2',e.mat,e.rc,p.codepaiepletemps2,e.d,sum(h.temps2),0,0,min(LIBECODEPAIE3)
  from ev_contrat_eta e,ev_heures h,poste_paie_activite p
  where e.mat=h.mat and e.eta=h.eta and e.dCtr<=h.datetr and h.datetr<=e.f
  and h.codeactivite=p.codeactivite and h.typejour= p.codetypejour and h.codestatut= p.codestatut
  and trim(p.codepaiepletemps2) is not null
  group by 'TEMPS2', e.mat, e.rc, p.codepaiepletemps2, e.d, 0, 0 having sum(h.temps2)<>0;
end;

procedure ev_calcul_activite_quantite(Wdatetrt ev_log.datetrt%type) as
begin
--  delete from ev where codepaie in (select codepaiepleqte from poste_paie_activite);
  insert into ev_log(datetrt,datesys,num,module,msg) 
  select Wdatetrt,systimestamp,0,'ev_calcul_activite_quantite','Poste de paie introuvable pour ' || h.codeactivite || ' et ' || h.typejour || ' et ' || h.codestatut
  from ev_contrat_eta e,ev_heures h
  where e.mat=h.mat and e.eta=h.eta and e.dCtr<=h.datetr and h.datetr<=e.f
  and not exists(select null from poste_paie_activite p where h.codeactivite=p.codeactivite and h.typejour= p.codetypejour and h.codestatut= p.codestatut and trim(p.codepaiepleqte) is not null)
  and h.qte<>0
  ;

  insert into ev(typev,mat,rc,codepaie,datev,qte,taux,val,lib)
  select 'QTE',e.mat,e.rc,p.codepaiepleqte,e.d,sum(h.qte),0,0,min(LIBECODEPAIE3)
  from ev_contrat_eta e,ev_heures h,poste_paie_activite p
  where e.mat=h.mat and e.eta=h.eta and e.dCtr<=h.datetr and h.datetr<=e.f
  and h.codeactivite=p.codeactivite and h.typejour= p.codetypejour and h.codestatut= p.codestatut
  and trim(p.codepaiepleqte) is not null
  group by 'QTE', e.mat, e.rc, p.codepaiepleqte, e.d, 0, 0 having sum(h.qte)<>0;
end;

procedure ev_calcul_majoration_HT
as
-- majoration heures travaillées
begin
  --delete from ev where codepaie in (select codepaieple4 from poste_paie_majoration where codemajo='HT');

  insert into ev(typev,mat,rc,codepaie,datev,qte,taux,val,lib)
  select 'MAJO',e.mat,e.rc,p.codepaieple4,e.d,sum(h.temps1),0,0,e.eta||'2'
  from ev_contrat_eta e, ev_heures_recap h,poste_paie_majoration p
  where e.mat=h.mat and e.eta=h.eta and e.dCtr<=h.datetr and h.datetr<=e.f
  and h.codestatut= p.codestatut
  and p.codemajo='HT'
  group by 'MAJO', e.mat, e.rc, p.codepaieple4, e.d, 0, 0, e.eta||'2' having sum(h.temps1)<>0;
end;

procedure ev_calcul_majoration_JT
as
-- majoration jours ouvrables travaillés
-- pour les porteurs, seulement les tournées
-- pour les polyvalents, tournées+heures
-- ==> utiliser ev_contrat_pol_eta pour prendre en compte le changement de statut
begin
  --delete from ev where codepaie in (select codepaieple4 from poste_paie_majoration where codemajo='JT');

  insert into ev(typev,mat,rc,codepaie,datev,qte,taux,val,lib)
  select 'MAJO',e.mat,e.rc,p.codepaieple4,e.d,count(distinct t.datetr),0,0,'JOURS TRAVAILLES'
  from ev_contrat_pol e,ev_tournees t,poste_paie_majoration p
  where e.mat=t.mat and e.dCtr<=t.datetr and t.datetr<=e.f
  and t.typejour='S'
  and p.codestatut='P' and p.codemajo='JT'
  and e.pol='N'
  group by 'MAJO', e.mat, e.rc, p.codepaieple4, e.d, 0, 0, 'JOURS TRAVAILLES'
  union
  select 'MAJO',e.mat,e.rc,p.codepaieple4,e.d,count(distinct h.datetr),0,0,'JOURS TRAVAILLES'
  from ev_contrat_pol_eta e, ev_heures_recap h,poste_paie_majoration p
  where e.mat=h.mat and e.dCtr<=h.datetr and h.datetr<=e.f
  and h.typejour='S'
  and p.codestatut='P' and p.codemajo='JT'
  and e.pol='O'
  group by e.mat,e.rc,p.codepaieple4,e.d ;
end;

procedure ev_calcul_majoration_HJ
as
-- majoration heures de jour
begin
  --delete from ev where codepaie in (select codepaieple4 from poste_paie_majoration where codemajo='HJ');

  insert into ev(typev,mat,rc,codepaie,datev,qte,taux,val,lib)
  select 'MAJO',e.mat,e.rc,p.codepaieple4,e.d,sum(h.temps1-h.temps2),0,0,e.eta||'2'
  from ev_contrat_eta e, ev_heures_recap h,poste_paie_majoration p
  where e.mat=h.mat and e.eta=h.eta and e.dCtr<=h.datetr and h.datetr<=e.f
  and h.codestatut= p.codestatut
  and p.codemajo='HJ'
  group by 'MAJO', e.mat, e.rc, p.codepaieple4, e.d, 0, 0, e.eta||'2' having sum(h.temps1-h.temps2)<>0;
end;

procedure ev_calcul_majoration_JO(
  Wdatedebut gen_pepp.dernierepaie%type
,  Wdatefin gen_pepp.dernierepaie%type
) as
-- majoration jours ouvrables periode
begin
  --delete from ev where codepaie in (select codepaieple4 from poste_paie_majoration where codemajo='JO');

  insert into ev(typev,mat,rc,codepaie,datev,qte,taux,val,lib)
  select distinct 'MAJO',e.mat,e.rc,codepaieple4,e.d,count(distinct c.datecal),0,0,'JOURS OUVRABLES'
  from ev_contrat_pol e,poste_paie_majoration p,calendrier c
  where Wdatedebut<=c.datecal and c.datecal<=Wdatefin and c.libjour not in ('Dimanche')
  and  p.codestatut='P'
  and p.codemajo='JO'
  and not exists(select null from jour_ferie where c.datecal=jfdate)
  and exists(select null from ev_heures_recap t where e.mat=t.mat)
  group by 'MAJO', e.mat, e.rc, codepaieple4, e.d, 0, 0, 'JOURS OUVRABLES' ;
end;

procedure ev_calcul_prime(
  Wdatetrt ev_log.datetrt%type
) as
begin
  --delete from ev where codepaie in (select codepaieple from poste_paie where codepaie='QLT');


    --------------------------------------------------------------------------------------------------------------------------------------------------
    ev_logger(Wdatetrt,3.5,'ev_calcul_prime','  Colonne tnbrabo sur Table ev_contrat_pol');
      update ev_contrat_pol e set (tnbrabo)=(select nvl(sum(d.nbrcli),0) from ev_tournees_detail d where e.dCtr<=d.datetr and d.datetr<=e.f and d.mat=e.mat and d.codenatcli='A' and d.typejour='S' group by d.mat);
      -- Si pas d'abonnés, tnbrabo reste à null
      update ev_contrat_pol e set tnbrabo=0 where tnbrabo is null;

    ev_logger(Wdatetrt,3.5,'ev_calcul_prime','  Colonne Qualite sur Table ev_contrat_pol');
      update ev_contrat_pol e set qualite='O';
      
     -- Si pas de tournée semaine, on met un code particulier
      update ev_contrat_pol e set qualite='S'
      where not exists(select null from ev_tournees t 
                      where e.mat=t.mat and e.dCtr<=t.datetr and t.datetr<=e.f and t.typejour='S')
      and e.qualite='O'
      ;
      --si incident<>0 ==> pas de prime
      update ev_contrat_pol e set qualite='I'
      where exists(select null from ev_reclamations r 
                  where r.mat=e.mat and e.dCtr<=r.datetr and r.datetr<=e.f and r.typejour='S' 
                  and (nvl(INDICINC,0)-nvl(ANNINC,0)>0))
      and e.qualite='O'
      ;
      -- réclamation diffuseur
      update ev_contrat_pol e set qualite='D'
      where exists(select null from ev_reclamations r,gen_pepp g 
                  where r.mat=e.mat and e.dCtr<=r.datetr and r.datetr<=e.f and r.typejour='S' 
                  group by e.mat,e.dCtr,g.nbrecdiffj having sum(nvl(NBRECDIF,0)-nvl(NBANNDIF,0))>g.nbrecdiffj)
      and e.qualite='O'
      ;

      --si ratio<TXREC..J ==> pas de prime
/*     update ev_contrat_pol e set qualite='A'
      where nvl(e.tnbrabo,0)<>0 
      and exists(select null from ev_reclamations r,gen_pepp g where e.mat=r.mat and e.dCtr<=r.datetr and r.datetr<=e.f and r.typejour='S' group by e.mat,e.dCtr,g.TXRECABJ having sum(nvl(NBRECAB,0)-nvl(NBANNAB,0))*1000/nvl(e.tnbrabo,0)>g.TXRECABJ)
      and e.qualite='O'
      ;
*/
      -- qualite=1 au lieu de 2 si seulement abonné ou seulement diffuseur (mono client)
      update ev_contrat_pol e set qualite='U'
      where not exists(select null from ev_tournees_detail d where e.dCtr<=d.datetr and d.datetr<=e.f and e.mat=d.mat and d.codenatcli='A' and d.typejour='S')
      and qualite='O'
      ;
      update ev_contrat_pol e set qualite='U'
      where not exists(select null from ev_tournees_detail d where e.dCtr<=d.datetr and d.datetr<=e.f and e.mat=d.mat and d.codenatcli='D' and d.typejour='S')
      and qualite='O'
      ;
    --------------------------------------------------------------------------------------------------------------------------------------------------
    ev_logger(Wdatetrt,3.5,'ev_calcul_prime','  Table ev_qualite');
      insert into ev_qualite(datetrt,mat,datev,qualite)
      select Wdatetrt,e.mat,e.d,
      case when e.tnbrabo<>0 then 
          (select sum(nvl(NBRECAB,0)-nvl(NBANNAB,0))*1000/nvl(e.tnbrabo,0) 
          from ev_reclamations r where e.mat=r.mat and e.dCtr<=r.datetr and r.datetr<=e.f and r.typejour='S' 
          group by r.mat)
      else 0
      end
      from ev_contrat_pol e
      where e.pol='N'
      ;
      -- Si pas de réclamation, qualite reste à null
      update ev_qualite set qualite=0 where qualite is null;
      
    --------------------------------------------------------------------------------------------------------------------------------------------------
  insert into ev(typev,mat,rc,codepaie,datev,qte,taux,val,lib)
--  select distinct 'PRIME',e.mat,e.rc,p.codepaieple,e.d,decode(e.qualite,'O',2,'U',1,0),0,0,p.libcodepaie
  select distinct 'PRIME',e.mat,e.rc,p.codepaieple,e.d,g.code,0,0,p.libcodepaie
  from ev_contrat_pol e,poste_paie p,ev_qualite q,gen_qualite g
  where p.codepaie='QLT'
  -- On ne génère pas d'ev si pas de tournée semaine
--  and e.qualite in ('O','U','N')
  and e.qualite=g.qualite
  -- Pas pour les polyvalents
  and e.mat=q.mat and e.d=q.datev and q.datetrt=Wdatetrt
  and g.borne_inf<=q.qualite and q.qualite<=g.borne_sup
  ;
end;

procedure ev_calcul_bonus(
  WStc char
, Wdatedeb gen_pepp.dernierepaie%type
, Wdatefin gen_pepp.dernierepaie%type
) as
begin
  --delete from ev where codepaie in (select codepaieple from poste_paie where codepaie='SBQ');

  if (WStc='N' and substr(Wdatefin,5,2) mod 3=0) then
    insert into ev(typev,mat,rc,codepaie,datev,qte,taux,val,lib)
    select distinct 'BONUS',e.mat,e.rc,p.codepaieple,Wdatedeb,count(distinct t.datetr),0,to_date(Wdatefin,'YYYYMMDD')-ADD_MONTHS(to_date(Wdatedeb,'YYYYMMDD'),-2)+1,'SUPER BONUS'
    from ev_contrat_eta e,tournees t, reclamations r,poste_paie p
    where p.codepaie='SBQ'
    -- Le contrat est superieur à 3 mois
    and e.dRc<=to_char(ADD_MONTHS(to_date(Wdatedeb,'YYYYMMDD'),-2),'YYYYMMDD')
    -- L'individu est la en fin de periode de paie
    and e.fRc>=Wdatefin
    -- On regarde toutes les réclamations sur les 3 derniers mois
    and e.mat=t.mat(+) and e.eta=t.eta(+)
    and to_char(ADD_MONTHS(to_date(Wdatedeb,'YYYYMMDD'),-2),'YYYYMMDD')<=t.datetr and t.datetr<=Wdatefin
    and t.codetr=r.codetr(+) and t.datetr=r.datetr(+)
    group by 'BONUS', e.mat, e.rc, p.codepaieple, Wdatedeb, 0, to_date(Wdatefin,'YYYYMMDD')-ADD_MONTHS(to_date(Wdatedeb,'YYYYMMDD'),-2)+1, 'SUPER BONUS'
    having  sum (greatest(nvl(r.NBRECAB,0)- nvl(r.NBANNAB,0),0))<=0
    and     sum(greatest(nvl(r.NBRECDIF,0)- nvl(r.NBANNDIF,0),0))<=0
    and     sum(greatest(nvl(r.INDICINC,0)- nvl(r.ANNINC,0),0))<=0
    -- On génre seulement s'il y a au moins une tournée
    and    exists(select null from tournees t2 where e.mat=t2.mat and t2.typejour in(' ','S') and to_char(ADD_MONTHS(to_date(Wdatedeb,'YYYYMMDD'),-2),'YYYYMMDD')<=t2.datetr and t2.datetr<=to_char(ADD_MONTHS(to_date(Wdatefin,'YYYYMMDD'),-2),'YYYYMMDD'))
    and    exists(select null from tournees t2 where e.mat=t2.mat and t2.typejour in(' ','S') and to_char(ADD_MONTHS(to_date(Wdatedeb,'YYYYMMDD'),-1),'YYYYMMDD')<=t2.datetr and t2.datetr<=to_char(ADD_MONTHS(to_date(Wdatefin,'YYYYMMDD'),-1),'YYYYMMDD'))
    and    exists(select null from tournees t2 where e.mat=t2.mat and t2.typejour in(' ','S') and Wdatedeb<=t2.datetr and t2.datetr<=Wdatefin);
  end if;
end;

procedure ev_diff(
Wdatetrt ev_log.datetrt%type
) as 
begin
  execute immediate 'truncate table ev_diff';
  
  insert into ev_diff(datetrt,diff,typev,mat,codepaie,datev,ordre,lib,rc,res_hst,res,qte_hst,qte,taux_hst,taux,val_hst,val)
  select Wdatetrt,'-',e1.typev,e1.mat,e1.codepaie,e1.datev,e1.ordre,e1.lib,e1.rc,e1.res,'',e1.qte,0,e1.taux,0,e1.val,0 from ev_hst e1 where not exists(select null from ev e2 where e1.mat=e2.mat and e1.datev=e2.datev and e1.codepaie=e2.codepaie and nvl(e1.ordre,0)=nvl(e2.ordre,0)) and e1.datetrt=Wdatetrt
  union
  select Wdatetrt,'+',e2.typev,e2.mat,e2.codepaie,e2.datev,e2.ordre,e2.lib,e2.rc,'',e2.res,0,e2.qte,0,e2.taux,0,e2.val from ev e2 where not exists(select null from ev_hst e1 where e1.mat=e2.mat and e1.datev=e2.datev and e1.codepaie=e2.codepaie and e1.datetrt=Wdatetrt and nvl(e1.ordre,0)=nvl(e2.ordre,0))
  union
  select Wdatetrt,'<',e1.typev,e1.mat,e1.codepaie,e1.datev,e1.ordre,e1.lib,e1.rc,e1.res,e2.res,e1.qte,e2.qte,e1.taux,e2.taux,e1.val,e2.val from ev_hst e1, ev e2 where e1.mat=e2.mat and e1.codepaie=e2.codepaie and e1.datev=e2.datev and nvl(e1.ordre,0)=nvl(e2.ordre,0) and (e1.qte<>e2.qte or e1.taux<>e2.taux or e1.val<>e2.val) and e1.datetrt=Wdatetrt
  union
  select Wdatetrt,' ',e1.typev,e1.mat,e1.codepaie,e1.datev,e1.ordre,e1.lib,e1.rc,e1.res,e2.res,e1.qte,e2.qte,e1.taux,e2.taux,e1.val,e2.val from ev_hst e1, ev e2 where e1.mat=e2.mat and e1.codepaie=e2.codepaie and e1.datev=e2.datev and nvl(e1.ordre,0)=nvl(e2.ordre,0) and e1.qte=e2.qte and e1.taux=e2.taux and e1.val=e2.val and e1.datetrt=Wdatetrt
  order by 2,3,4,5;
  
  --select d.* from ev_diff d where d.diff<>' ' order by d.mat,d.datev,d.codepaie;
end;

/*
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
order by mat,nvl(rc,'900001'),codepaie,datev,nvl(ordre,0);
*/
END;
/