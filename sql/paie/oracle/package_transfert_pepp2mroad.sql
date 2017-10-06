create or replace PACKAGE MROAD AS

procedure exec(
    Wdatetrt			ev_log.datetrt%type
,	dpaie gen_pepp.dernierepaie%type
,	ppaie gen_pepp.dernierepaie%type
);
end;
/

create or replace PACKAGE BODY MROAD AS
/*
begin
  mroad.exec('20141120 000000','20141021','20141120');
end;

DROP DATABASE LINK QMROAD;
CREATE DATABASE LINK QMROAD CONNECT TO "oracle" identified by "qmroad" using 'QMROAD';

drop table int_mroad;
create table int_mroad(matricule varchar(10), date_debut varchar(8), date_fin varchar(8),eta varchar(3));
insert into int_mroad values('7000350000','20141021','20141031','28');
insert into int_mroad values('7000356300','20141021','20141031','28');
insert into int_mroad values('7000339900','20141021','20990101','28');
commit;
*/
procedure init_transco_activite is
  ret integer;
begin
  ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_pepp_transco_activite');
  for a in (
    select 
      a.codeactivite,
      a.libactivite as libelle,
      a.idmroad as activite_id
    from activite a
    ) loop
    insert into "pai_pepp_transco_activite"@MROAD("codeactivite","libelle","activite_id") 
    values(a.codeactivite,a.libelle,a.activite_id);
  end loop;
end;

procedure init_transco_titre is
  ret integer;
begin
  ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_pepp_transco_titre');
  for a in (
    select 
      a.codetitre,
      a.libtitre as libelle,
      a.codetitredcs as produit_id,
      a.supplement_id
    from titre a
    ) loop
    insert into "pai_pepp_transco_titre"@MROAD("codetitre","libelle","produit_id","supplement_id") 
    values(a.codetitre,a.libelle,a.produit_id,a.supplement_id);
  end loop;
end;

procedure nettoyage(
    Wdatetrt			ev_log.datetrt%type
,	dpaie gen_pepp.dernierepaie%type
,	ppaie gen_pepp.dernierepaie%type
) is
  ret integer;
begin
  ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_pepp_heures where dextrait='''||Wdatetrt||'''');
  ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete td from pai_pepp_tournees_detail td inner join pai_pepp_tournees t on t.datetr=td.datetr and t.codetr=td.codetr where t.dextrait='''||Wdatetrt||'''');
  ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete r from pai_pepp_reclamations r inner join pai_pepp_tournees t on t.datetr=r.datetr and t.codetr=r.codetr where t.dextrait='''||Wdatetrt||'''');
  ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete t from pai_pepp_tournees t where t.dextrait='''||Wdatetrt||'''');
end;

procedure select_int(
    Wdatetrt			ev_log.datetrt%type
,	dpaie gen_pepp.dernierepaie%type
,	ppaie gen_pepp.dernierepaie%type
) is
begin
    update tournees t set extrait='O',dextrait=Wdatetrt where t.extrait='N' and t.tagbloc='N' and t.datetr<=ppaie
    and exists(select null from int_mroad m where t.mat=m.matricule and t.eta=m.eta and t.datetr between m.date_debut and m.date_fin)
    ;
    update heures h set extrait='O',dextrait=Wdatetrt where h.extrait='N' and h.tagbloc='N' and h.datetr<=ppaie
    and exists(select null from int_mroad m where h.mat=m.matricule and h.eta=m.eta and h.datetr between m.date_debut and m.date_fin)
    ;
end;

procedure deselect_int(
    Wdatetrt			ev_log.datetrt%type
,	dpaie gen_pepp.dernierepaie%type
,	ppaie gen_pepp.dernierepaie%type
) is
begin
    update tournees t set extrait='N',dextrait=null where t.dextrait=Wdatetrt
    ;
    update heures h set extrait='N',dextrait=null where h.dextrait=Wdatetrt
    ;
end;

procedure insert_activite(
    Wdatetrt			ev_log.datetrt%type
,	dpaie gen_pepp.dernierepaie%type
,	ppaie gen_pepp.dernierepaie%type
) is
begin
  for i in (
    SELECT * FROM HEURES
    WHERE DEXTRAIT=Wdatetrt
  ) loop
    insert into "pai_pepp_heures"@MROAD("datetr","mat","codeactivite","codestatut","typejour","temps1","temps2","nbkm","eta","nomuser","datemaj","codetr","commentaire","dextrait","qte") 
    values(    i.DATETR,
    i.MAT,
    i.CODEACTIVITE,
    i.CODESTATUT,
    i.TYPEJOUR,
    i.TEMPS1,
    i.TEMPS2,
    i.NBKM,
    i.ETA,
    i.NOMUSER,
    i.DATEMAJ,
    i.CODETR,
    i.COMMENTAIRE,
    i.DEXTRAIT,
    i.QTE
    );
  end loop;
end;

procedure insert_tournee(
    Wdatetrt			ev_log.datetrt%type
,	dpaie gen_pepp.dernierepaie%type
,	ppaie gen_pepp.dernierepaie%type
) is
begin
  for i in (
    SELECT t.* FROM TOURNEES t
    WHERE t.DEXTRAIT=Wdatetrt
  ) loop
    insert into "pai_pepp_tournees"@MROAD("datetr","codetr","mat","nbkm","temps","valrem","matchef","eta","typejour","codedev","trstatus","nomuser","datemaj","nbkm2","valremc","tnbrcli","tnuit","tatt","dextrait") 
    values(
    i.DATETR,
    i.CODETR,
    i.MAT,
    i.NBKM,
    i.TEMPS,
    i.VALREM,
    i.MATCHEF,
    i.ETA,
    i.TYPEJOUR,
    i.CODEDEV,
    i.TRSTATUS,
    i.NOMUSER,
    i.DATEMAJ,
    i.NBKM2,
    i.VALREMC,
    i.TNBRCLI,
    i.TNUIT,
    i.TATT,
    i.DEXTRAIT
    );
  end loop;
end;

procedure insert_produit(
    Wdatetrt			ev_log.datetrt%type
,	dpaie gen_pepp.dernierepaie%type
,	ppaie gen_pepp.dernierepaie%type
) is
begin
  for i in (
    SELECT td.* 
    FROM TOURNEES_DETAIL td
    INNER JOIN TOURNEES t on td.datetr=t.datetr and td.codetr=t.codetr
    WHERE t.DEXTRAIT=Wdatetrt
  ) loop
    insert into "pai_pepp_tournees_detail"@MROAD("datetr","codetr","codetitre","codenatcli","typ4pc","nbrcli","nbrex","nbrspl","trstatus","datemaj") 
    values(i.DATETR,i.CODETR,i.CODETITRE,i.CODENATCLI,i.TYP4PC,i.NBRCLI,i.NBREX,i.NBRSPL,i.TRSTATUS,i.DATEMAJ);
  end loop;
end;

procedure insert_reclamation(
    Wdatetrt			ev_log.datetrt%type
,	dpaie gen_pepp.dernierepaie%type
,	ppaie gen_pepp.dernierepaie%type
) is
begin
  for i in (
    SELECT r.* FROM RECLAMATIONS r
    INNER JOIN TOURNEES t on r.datetr=t.datetr and r.codetr=t.codetr
    WHERE t.DEXTRAIT=Wdatetrt
  ) loop
    insert into "pai_pepp_reclamations"@MROAD("datepaiefm","datetr","codetr","codetitre","nbrecab","nbannab","nbrecdif","nbanndif","indicinc","anninc","nomuser","datemaj") 
    values(i.DATEPAIEFM,i.DATETR,i.CODETR,i.CODETITRE,i.NBRECAB,i.NBANNAB,i.NBRECDIF,i.NBANNDIF,i.INDICINC,i.ANNINC,i.NOMUSER,i.DATEMAJ);
  end loop;
end;

procedure exec(
    Wdatetrt			ev_log.datetrt%type
,	dpaie gen_pepp.dernierepaie%type
,	ppaie gen_pepp.dernierepaie%type
) is
begin
    insert into int_traitement(datetrt,nomuser,typetrt) values(Wdatetrt,'jlouis','Interface MRoad '||ppaie);
    insert into int_log
    select Wdatetrt,sysdate(),m.matricule||' '||m.eta||' '||m.date_debut||' '||m.date_fin
    from int_mroad m 
    where m.date_debut<=ppaie and m.date_fin>=dpaie
    ;
    
    nettoyage(Wdatetrt,dpaie,ppaie);
    select_int(Wdatetrt,dpaie,ppaie);
    insert_activite(Wdatetrt,dpaie,ppaie);
    insert_tournee(Wdatetrt,dpaie,ppaie);
    insert_produit(Wdatetrt,dpaie,ppaie);
    insert_reclamation(Wdatetrt,dpaie,ppaie);
    deselect_int(Wdatetrt,dpaie,ppaie);
end;

end;