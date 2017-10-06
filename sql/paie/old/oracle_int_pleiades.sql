create or replace package MROAD as 
  procedure exec;
end;
/

create or replace package body MROAD as 
--create table int_mroad(rcoid varchar(36));

/*
DROP DATABASE LINK MROAD;
CREATE DATABASE LINK MROAD CONNECT TO "oracle" identified by "qmroad" using 'QMROAD';
CREATE DATABASE LINK MROAD CONNECT TO "oracle" identified by "qmroad" using 'DMROAD';
*/

    procedure nettoyage is
      ret integer;
    begin
      ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_png_xhorporpol');
      ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_png_vehiculew');
      ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_png_etablissrel');
      ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_png_emploi');
    
      ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_png_suspension');
      ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_png_contrat');
      ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_png_rcpopulationw');
      ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_png_relationcontrat');
      ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_png_salpopulationw');
      ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_png_salarie');
    
      ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_png_tarifhorairew');
    
      ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_png_etablissement');
      ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_png_societe');
    
      ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_png_ta_populationw');
      ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_png_ta_proprietaire');
      ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_png_ta_emploi');
      ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from pai_png_legalferie');

      execute immediate('truncate table int_mroad');
      commit;
    end;

    procedure init is
    begin
      insert into int_mroad(rcoid)
      select distinct rc.oid
        from relationcontrat rc
        where exists (select null from rcpopulationw rcw
                    inner join ta_populationw tap on rcw.POPULATION=tap.oid
                    where rcw.relationcontrat=rc.oid and rcw.end_date>=to_date('20140101','YYYYMMDD')
                    and tap.code in ('EMDDPO','EMDDPP','EMIDPO','EMIDPP','EMIDRB','EMIDRE','EMIDTB','EMIDTE','EMDDRB','EMDDRE','EMDDTB','EMDDTE','CAIDLF','CAIDLG','CADDLF','CADDLG'))
        ;
      commit;
    end;

    procedure exec_ref is
    begin
/*
000297	PORTEUR COLIS
000295	GRAND PORTEUR DE PRESSE (polyvalent)
000294	PORTEUR(EUSE) POLYVALENT NP
000293	PORTEUR(EUSE) POLYVALENT P
000292	PORTEUR(EUSE) NP
000291	PORTEUR(EUSE) P
000289	COLLECTEUR (que des heures)
000288	PORTEUR(EUSE) POLYVALENT(E)  N
000287	PORTEUR(EUSE) N
*/
      for te in (
        select te.oid,te.timestamp,te.emploicode,te.emploilibelle,te.emploilib
        from ta_emploi te
        where te.emploicode in ('000219','000220','000287','000288','000289','000291','000292','000293','000294','000295','000297')
        ) loop
        insert into "pai_png_ta_emploi"@MROAD("oid","timestamp","emploicode","emploilibelle","emploilib") values(te.oid,te.timestamp,te.emploicode,te.emploilibelle,te.emploilib);
      end loop;
      
     for ta in (
        select ta.oid,ta.timestamp,ta.code,ta.libelle,ta.libcourt
        from ta_propietairew ta
        ) loop
        insert into "pai_png_ta_proprietaire"@MROAD("oid","timestamp","code","libelle","libcourt") values(ta.oid,ta.timestamp,ta.code,ta.libelle,ta.libcourt);
      end loop;

      for s in (
        select s.oid,s.timestamp,s.societecode,s.societenom
        from societe s
        ) loop
        insert into "pai_png_societe"@MROAD("oid","timestamp","societecode","societenom") values(s.oid,s.timestamp,s.societecode,s.societenom);
      end loop;

      for e in (
        select distinct e.oid,e.timestamp,e.etabcode,e.etabnom,e.etabnomc,e.etabdebut,e.etabfin,e.etabadrnumero,e.etabadrnumcpt,e.etaadrvoietype,e.etabadrvoie,e.etabadrcommune,e.etabadrcp
        from etablissement e
        ) loop
        insert into "pai_png_etablissement"@MROAD("oid","timestamp","etabcode","etabnom","etabnomc","etabdebut","etabfin","etabadrnumero","etabadrnumcpt","etaadrvoietype","etabadrvoie","etabadrcommune","etabadrcp") values(e.oid,e.timestamp,e.etabcode,e.etabnom,e.etabnomc,e.etabdebut,e.etabfin,e.etabadrnumero,e.etabadrnumcpt,e.etaadrvoietype,e.etabadrvoie,e.etabadrcommune,e.etabadrcp);
      end loop;

      for ta in (
        select distinct ta.oid,ta.timestamp,ta.code,ta.libelle
        from  /*int_mroad r,
              rcpopulationw rcw,*/
              ta_populationw ta
        where  /*r.rcoid = rcw.relationcontrat
        and rcw.population = ta.oid
        and*/ ta.code in ('EMDDPO','EMDDPP','EMIDPO','EMIDPP','EMIDRB','EMIDRE','EMIDTB','EMIDTE','EMDDRB','EMDDRE','EMDDTB','EMDDTE','CAIDLF','CAIDLG','CADDLF','CADDLG')
        ) loop
        insert into "pai_png_ta_populationw"@MROAD("oid","timestamp","code","libelle") values(ta.oid,ta.timestamp,ta.code,ta.libelle);
      end loop;

      for f in (
        select distinct f.oid,f.timestamp,f.legalferiedate
        from  legalferie f
        where f.legalferiedate>=to_date('20140101','YYYYMMDD')
        ) loop
        insert into "pai_png_legalferie"@MROAD("oid","timestamp","legalferiedate") values(f.oid,f.timestamp,f.legalferiedate);
      end loop;
      commit;
    end;

    procedure exec_tarif is
    begin
      for t in (
        select distinct t.oid,t.timestamp,t.societe,t.population,t.begin_date,t.end_date,to_char(t.tauxhoraire,'999.99999') tauxhoraire
        from  int_mroad r,
              rcpopulationw rcw,
              tarifhorairew t,
              ta_populationw ta
        where r.rcoid=rcw.relationcontrat
        and   rcw.population=t.population
        and rcw.population = ta.oid
        and ta.code in ('EMDDPO','EMDDPP','EMIDPO','EMIDPP','EMIDRB','EMIDRE','EMIDTB','EMIDTE','EMDDRB','EMDDRE','EMDDTB','EMDDTE')
        ) loop
        insert into "pai_png_tarifhorairew"@MROAD("oid","timestamp","societe","population","begin_date","end_date","tauxhoraire") values(t.oid,t.timestamp,t.societe,t.population,t.begin_date,t.end_date,t.tauxhoraire);
      end loop;
      commit;
    end;

    procedure exec_salarie is
    begin
      for s in (
        select distinct s.oid,s.timestamp,s.matricule,s.prenom1,s.prenom2,s.nompatronymique,s.nom_usuel 
        from  int_mroad r,
              salarie s,
            relationcontrat rc
        where s.oid        =rc.relatmatricule
        and r.rcoid       = rc.oid 
        ) loop
        insert into "pai_png_salarie"@MROAD("oid","timestamp","matricule","prenom1","prenom2","nompatronymique","nom_usuel") values(s.oid,s.timestamp,s.matricule,s.prenom1,s.prenom2,s.nompatronymique,s.nom_usuel);
      end loop;
      commit;
    end;
    
    procedure exec_salpopulation is
    begin
      for po in (
        select distinct po.oid,po.timestamp,po.salarie,po.begin_date,po.end_date,po.numrc,po.nocarrierealpha
        from  int_mroad r,
              salpopulationw po,
              salarie s,
              relationcontrat rc
        where s.oid         = rc.relatmatricule
        and   r.rcoid       = rc.oid 
        and   s.oid         =po.salarie
--        and   end_date      >=to_date('20140101','YYYYMMDD')
        ) loop
        insert into "pai_png_salpopulationw"@MROAD("oid","timestamp","salarie","begin_date","end_date","numrc","nocarrierealpha") values(po.oid,po.timestamp,po.salarie,po.begin_date,po.end_date,po.numrc,po.nocarrierealpha);
      end loop;
      commit;
    end;
    
    procedure exec_relationcontrat is
    begin
      for rc in (
        select distinct rc.oid,rc.timestamp,rc.relatmatricule,rc.relatnum,rc.relatsociete,rc.relatdatedeb,rc.relatdatefinw
        from int_mroad r,
              relationcontrat rc
        where rc.oid       =r.rcoid
        ) loop
        insert into "pai_png_relationcontrat"@MROAD("oid","timestamp","relatmatricule","relatnum","relatsociete","relatdatedeb","relatdatefinw") values(rc.oid,rc.timestamp,rc.relatmatricule,rc.relatnum,rc.relatsociete,rc.relatdatedeb,rc.relatdatefinw);
      end loop;
      commit;
    end;
    
    procedure exec_rcpopulation is
    begin
      for po in (
        select distinct po.oid,po.timestamp,po.relationcontrat,po.begin_date,po.end_date,po.population,po.populationprec
        from  int_mroad r,
              rcpopulationw po,
              ta_populationw ta
        where r.rcoid      =po.relationcontrat
--        and   end_date      >=to_date('20140101','YYYYMMDD')
        and po.population = ta.oid
        and ta.code in ('EMDDPO','EMDDPP','EMIDPO','EMIDPP','EMIDRB','EMIDRE','EMIDTB','EMIDTE','EMDDRB','EMDDRE','EMDDTB','EMDDTE','CAIDLF','CAIDLG','CADDLF','CADDLG')
        ) loop
        insert into "pai_png_rcpopulationw"@MROAD("oid","timestamp","relationcontrat","begin_date","end_date","population","populationprec") values(po.oid,po.timestamp,po.relationcontrat,po.begin_date,po.end_date,po.population,po.populationprec);
      end loop;
      commit;
    end;
    
    procedure exec_emploi is
    begin
      for em in (
        select em.oid,em.timestamp,em.emploirelation,em.emploi,em.begin_date,em.end_date
        from  int_mroad r,
              emploi em,
              ta_emploi te
        where r.rcoid         =em.emploirelation
---        and   end_date      >=to_date('20140101','YYYYMMDD')
        and   em.emploi      =te.oid
        and   te.emploicode in ('000219','000220','000287','000288','000289','000291','000292','000293','000294','000295','000297')
        ) loop
        insert into "pai_png_emploi"@MROAD("oid","timestamp","emploirelation","emploi","begin_date","end_date") values(em.oid,em.timestamp,em.emploirelation,em.emploi,em.begin_date,em.end_date);
      end loop;
      commit;
    end;
    
    procedure exec_etablissrel is
    begin
      for er in (
        select distinct er.oid,er.timestamp,er.etabrelation,er.begin_date,er.end_date,er.etabrel
        from  int_mroad r,
              etablissrel er
        where r.rcoid           =er.etabrelation
--        and   end_date      >=to_date('20140101','YYYYMMDD')
        ) loop
        insert into "pai_png_etablissrel"@MROAD("oid","timestamp","etabrelation","begin_date","end_date","etabrel") values(er.oid,er.timestamp,er.etabrelation,er.begin_date,er.end_date,er.etabrel);
      end loop;
      commit;
    end;
    
    procedure exec_contrat is
    begin
      for c in (
        select distinct c.oid,c.timestamp,c.ctrrelation
        from  int_mroad r,
              contrat c
        where r.rcoid           =c.ctrrelation
        ) loop
        insert into "pai_png_contrat"@MROAD("oid","timestamp","ctrrelation") values(c.oid,c.timestamp,c.ctrrelation);
      end loop;
      commit;
    end;
    
    procedure exec_suspension is
    begin
      for s in (
        select distinct s.oid,s.timestamp,s.suspcontrat,s.begin_date,s.end_date
        from  int_mroad r,
              contrat c,
              suspension s
        where r.rcoid           =c.ctrrelation
        and   c.oid          =s.suspcontrat
--        and   end_date      >=to_date('20140101','YYYYMMDD')
        ) loop
        insert into "pai_png_suspension"@MROAD("oid","timestamp","suspcontrat","begin_date","end_date") values(s.oid,s.timestamp,s.suspcontrat,s.begin_date,s.end_date);
      end loop;
      commit;
    end;
    
    procedure exec_vehicule is
    begin
      for v in (
        select distinct v.oid,v.timestamp,v.salarie,v.begin_date,v.end_date,v.immatriculation,v.assureur,v.dtdebassur,v.dtfinassur,v.dtvaliditect,v.ta_proprietaire,v.utilisation,v.description
        from int_mroad r,
              relationcontrat rc,
            vehiculew v
        where rc.relatmatricule =v.salarie
        and   r.rcoid             =rc.oid
        and   end_date      >=to_date('20140101','YYYYMMDD')
        ) loop
        insert into "pai_png_vehiculew"@MROAD("oid","timestamp","salarie","begin_date","end_date","immatriculation","assureur","dtdebassur","dtfinassur","dtvaliditect","ta_proprietaire","utilisation","description") values(v.oid,v.timestamp,v.salarie,v.begin_date,v.end_date,v.immatriculation,v.assureur,v.dtdebassur,v.dtfinassur,v.dtvaliditect,v.ta_proprietaire,v.utilisation,v.description);
      end loop;
      commit;
    end;
    
    
    procedure exec_xhorporpol is
    begin
      for v in (
        select distinct v.oid,v.timestamp,v.relationcontrat,v.begin_date,v.end_date,to_char(v.horcontractuel,'999.999') as horcontractuel,v.heuredebctr
        from int_mroad r,
            xhorporpol v
        where r.rcoid             =v.relationcontrat
        ) loop
        insert into "pai_png_xhorporpol"@MROAD("oid","timestamp","relationcontrat","begin_date","end_date","horcontractuel","heuredebctr") values(v.oid,v.timestamp,v.relationcontrat,v.begin_date,v.end_date,v.horcontractuel,v.heuredebctr);
      end loop;
      commit;
    end;
    
    procedure exec is
    begin
      nettoyage;
      init;
      exec_ref;
      exec_tarif;
      exec_salarie;
      exec_salpopulation;
      exec_relationcontrat;
      exec_rcpopulation;
      exec_emploi;
      exec_etablissrel;
      exec_contrat;
      exec_suspension;
      exec_vehicule;
      exec_xhorporpol;
    end;
end;
/