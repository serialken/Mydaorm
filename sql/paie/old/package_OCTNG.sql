--------------------------------------------------------
--  Fichier créé - mercredi-septembre-17-2014   
--------------------------------------------------------
--------------------------------------------------------
--  DDL for Package OCTNG
--------------------------------------------------------

  CREATE OR REPLACE PACKAGE "OCTNG" AS

PROCEDURE PNG_refresh;

PROCEDURE automatique(
    Wlogin			  octng_traitement.nomuser%type
,   Wdatetrt			octng_traitement.datetrt%type default systimestamp
);
  
 PROCEDURE individuel(
    Wlogin			  octng_traitement.nomuser%type
,   WTypetrt      octng_traitement.typetrt%type default 'CAa'
,   WMois         octng_traitement.anneemois%type
,   WLib          octng_traitement.lib%type default 'Interface Individuelle'
,   Wdatetrt			octng_traitement.datetrt%type default systimestamp
,   Wdateretro		octng_traitement.dateretro%type
,   WClause       octng_traitement.clause%type
);
PROCEDURE clone(
    Wlogin			  octng_traitement.nomuser%type
,   Widtrt_org	  octng_traitement.idtrt%type
,   Wdatetrt			octng_traitement.datetrt%type default systimestamp
);

PROCEDURE clone_debug(
    Wlogin			  octng_traitement.nomuser%type
,   Widtrt_org	  octng_traitement.idtrt%type
,   Wdatetrt			octng_traitement.datetrt%type default systimestamp
);

procedure actualise(
    Wlogin			  octng_traitement.nomuser%type
,   Widtrt        octng_traitement.idtrt%type
);

procedure actualise_NG(
    Wlogin			  octng_traitement.nomuser%type
,   Widtrt        octng_traitement.idtrt%type
);

procedure integre_NG(
    Wlogin			  octng_traitement.nomuser%type
,   Widtrt        octng_traitement.idtrt%type
);
end;

/
--------------------------------------------------------
--  DDL for Package OCTNG_ABS
--------------------------------------------------------

  CREATE OR REPLACE PACKAGE "OCTNG_ABS" AS

procedure init_pers_select(
  Widtrt      octng_traitement.idtrt%type
, Wdatetrt		octng_traitement.datetrt%type
);
  
procedure init(
  Widtrt      octng_traitement.idtrt%type
,	Wmois       octng_traitement.anneemois%type
, WabsenceSS  char
);

procedure exec(
  Widtrt      octng_traitement.idtrt%type
,	Wmois       octng_traitement.anneemois%type
, WabsenceSS  char
);

procedure dif(
  Widtrt      octng_traitement.idtrt%type,
  Widtrt_org  octng_traitement.idtrt%type
);

procedure dif_NG(
  Widtrt      octng_traitement.idtrt%type,
  WabsenceSS    char
);

procedure regroupe_nonSS_XCP(
  Widtrt        octng_traitement.idtrt%type
,  Wabs_cod     varchar2
,  Wcpt         varchar2
);
end;

/
--------------------------------------------------------
--  DDL for Package OCTNG_EV
--------------------------------------------------------

  CREATE OR REPLACE PACKAGE "OCTNG_EV" AS

procedure calcul(
  Widtrt        octng_traitement.idtrt%type
,	Wmois         octng_traitement.anneemois%type
, Wcodepaie       char default NULL
);

procedure init(
  Widtrt      octng_traitement.idtrt%type
,	Wmois       octng_traitement.anneemois%type,
  WabsenceSS    char default 'N'
);

procedure exec(
  Widtrt      octng_traitement.idtrt%type
,	Wmois       octng_traitement.anneemois%type,
  WabsenceSS    char default 'N'
);

procedure dif(
  Widtrt      octng_traitement.idtrt%type,
  Widtrt_org  octng_traitement.idtrt%type
);

procedure dif_NG(
  Widtrt      octng_traitement.idtrt%type,
  WabsenceSS    char default 'N'
);

end;

/
--------------------------------------------------------
--  DDL for Package OCTNG_SPECIFIC
--------------------------------------------------------

  CREATE OR REPLACE PACKAGE "OCTNG_SPECIFIC" AS

  function getEnv return varchar2;
  function getPQD return varchar2;
  function DateDeb return date;
end;

/
--------------------------------------------------------
--  DDL for Package OCTNG_TRT
--------------------------------------------------------

  CREATE OR REPLACE PACKAGE "OCTNG_TRT" AS

procedure debut(
    Widtrt			  IN OUT octng_traitement.idtrt%type
,   Wlogin			  octng_traitement.nomuser%type
,   Wdatetrt			octng_traitement.datetrt%type
,	  Wmois         octng_traitement.anneemois%type
,   WTypetrt      octng_traitement.typetrt%type
,   WLib          octng_traitement.lib%type
,   Wdateretro		octng_traitement.dateretro%type
,   WClause       octng_traitement.clause%type
);
/*
procedure fin(
    Widtrt			  octng_traitement.idtrt%type
);
*/
procedure succes(
    Widtrt			  octng_traitement.idtrt%type
,   Wstatut       octng_traitement.statut%type default 'S'
);

procedure erreur(
    Widtrt			  octng_traitement.idtrt%type
);

procedure raise_erreur(
    Widtrt octng_traitement.idtrt%type
,   Wmodule varchar2
,   Werreur varchar2
);

procedure logger(
    Widtrt			 octng_traitement.idtrt%type
,   Wmodule      octng_log.module%type
,   Wmsg         octng_log.msg%type
,   Wniveau      octng_log.niveau%type default 5
);

procedure logsql(
    Widtrt			  octng_traitement.idtrt%type
,   Wcodepaie     octng_sql.codepaie%type
,   Wsql          octng_sql.sqlW%type
);

function logsql_nb(
    Widtrt			  octng_traitement.idtrt%type
,   Wcodepaie     octng_sql.codepaie%type
,   Wsql          octng_sql.sqlW%type
) return number;

procedure nettoyage;
procedure supprime(
    Wlogin			  octng_traitement.nomuser%type
,   Widtrt			  octng_traitement.idtrt%type
);
procedure supprime_last(
    Wlogin			  octng_traitement.nomuser%type
);

procedure bloque_paie(
  Wanneemois      octng_traitement.anneemois%type
);
procedure change_paie(
  Wanneemois      octng_traitement.anneemois%type
);

procedure cloture(
  Wanneemois      octng_traitement.anneemois%type
);

end;

/
--------------------------------------------------------
--  DDL for Package Body OCTNG
--------------------------------------------------------

  CREATE OR REPLACE PACKAGE BODY "OCTNG" AS

--==============================================================================
-- Procédures privées
--==============================================================================
  PROCEDURE PNG_REFRESH IS 
  BEGIN
    DBMS_MVIEW.REFRESH('PNG_CONTRAT');
--    DBMS_MVIEW.REFRESH('PNG_EMPLOI');
--    DBMS_MVIEW.REFRESH('PNG_ETABLISSEMENT');
--    DBMS_MVIEW.REFRESH('PNG_ETABLISSREL');
    DBMS_MVIEW.REFRESH('PNG_RELATIONCONTRAT');
--    DBMS_MVIEW.REFRESH('PNG_RCPOPULATIONW');
--    DBMS_MVIEW.REFRESH('PNG_TA_POPULATIONW');
 
    DBMS_MVIEW.REFRESH('PNG_SALPOPULATIONW');
    DBMS_MVIEW.REFRESH('PNG_RCINTRFOCTIMEWW');
    DBMS_MVIEW.REFRESH('PNG_AFFGESTIONNAIRE');
  
    DBMS_MVIEW.REFRESH('PNG_SALARIE');
    DBMS_MVIEW.REFRESH('PNG_SUSPENSION');
--    DBMS_MVIEW.REFRESH('PNG_TA_EMPLOI');
--    DBMS_MVIEW.REFRESH('PNG_TA_MOTIFEVT');
--    DBMS_MVIEW.REFRESH('PNG_TA_CONTRATTYPE');

  --  DBMS_MVIEW.REFRESH('EVSDVP.PNG_EVFACTORYW');
  --  DBMS_MVIEW.REFRESH('EVSDVP.PNG_ENGACP');
  --  DBMS_MVIEW.REFRESH('EVSDVP.PNG_ENGACT');
  --  DBMS_MVIEW.REFRESH('EVSDVP.PNG_ENGAMA');
  --  DBMS_MVIEW.REFRESH('EVSDVP.PNG_ENGANP');
  --  DBMS_MVIEW.REFRESH('EVSDVP.PNG_ENGART');
  --  DBMS_MVIEW.REFRESH('EVSDVP.PNG_ENGAUP');
  --  DBMS_MVIEW.REFRESH('EVSDVP.PNG_ENGMAT');

  --  DBMS_MVIEW.REFRESH('EVSDVP.PNG_TA_NATUREEVGENW');
  --  DBMS_MVIEW.REFRESH('EVSDVP.PNG_TP_INTERFACES1W');
  --  DBMS_MVIEW.REFRESH('EVSDVP.PNG_TP_INTERFACESW');
  end;
  
--==============================================================================
  procedure MajStatut is
  begin
--  raise_application_error(-20001,'debug');
 
   update pers p set (fin,statut,eta,etat)=(select 
        least(ctrfin,em.end_date)
        , case 
          when g.gpecode='AME' then 'M'
          when g.gpecode='EQP' then 'E'
          when g.gpecode='SDV' then
            case 
              when te.emploicode in ('000219','000220','000287','000288','000289','000291','000292','000293','000294','000295','000297','000062') then 'P' 
              else 'A' 
            end
          when s.societecode='LPM' then 'l'
          when s.societecode='S3A' then 'a'
--          when g.gpecode='PAR' then 't'
          else
            case 
             when ct.contrattypecode='CDU' then 'p'
             else 't'
             end
          end
        ,et.etabcode
        -- On met en non valide les individu qui ne font pas parti de l'environnement Octime
        ,case
          when OCTNG_SPECIFIC.getEnv<>'PPS' and OCTNG_SPECIFIC.getEnv<>g.gpecode then 'N'
          when OCTNG_SPECIFIC.getEnv='PPS' and g.gpecode not in ('PAR','CPA','SIC','S3A') then 'N'
          else p.etat
        end
        from DBL_PNG_Emploi em,DBL_PNG_ta_Emploi te,DBL_PNG_etablissrel er,DBL_PNG_etablissement et,DBL_PNG_rcpopulationw rcp,DBL_PNG_ta_populationw pop,DBL_PNG_TA_CONTRATTYPE ct
        ,DBL_PNG_ETABSOCIETE  es,DBL_PNG_SOCIETE  s,     DBL_PNG_socgroupe sg,     DBL_PNG_groupe g
        where p.rcoid=em.emploirelation and em.emploi=te.oid and p.debut between em.begin_date and em.end_date
        -- Etablissement en fin de période
        and p.rcoid=er.etabrelation and least(ctrfin,em.end_date) between er.begin_date and er.end_date and er.etabrel=et.oid and et.oid=es.etabsoccode and es.etabsoc=s.oid and s.oid= sg.socgpesociete  and	 sg.socgpegroupe =g.oid
        -- population pour contrat CDD/CDI/CDU
       and p.rcoid=rcp.relationcontrat and rcp.population= pop.oid and pop.TA_contratType=ct.oid and ctrfin between rcp.begin_date and rcp.end_date
        );  
  end;

--==============================================================================
  procedure select_contrat(
  Widtrt      octng_traitement.idtrt%type
,	Wmois          char
) as
WDateDeb Date;
begin
OCTNG_TRT.logger(Widtrt,'select_contrat','Remplit les tables Contrat');
  execute immediate 'truncate table pers';
  execute immediate 'truncate table pers_contrat';
 
  insert into pers_contrat (mat,rc,rcoid,relatdatedeb,relatdatefin,ctroid,ctrdeb,ctrfin)
  select    ps.mat,rc.relatnum,rc.oid,rc.relatdatedeb ,rc.relatdatefinW,c.oid,rc.relatdatedeb ,min(su.begin_date)-1
  from pers_select ps,PNG_salarie sal,PNG_relationcontrat rc,PNG_contrat c,PNG_suspension su, PNG_SalPopulationW sp
  where ps.mat = sal.matricule||sp.noCarrierealpha and sal.oid=rc.relatmatricule  
--  and (rc.relatprincipal=1 or OCTNG_SPECIFIC.getEnv<>'SDV')
  and rc.relatdatefinW>=OCTNG_SPECIFIC.DateDeb
  and rc.oid=c.ctrrelation and c.oid=su.suspcontrat 
  and	 sal.oid=sp.salarie and rc.relatnum=sp.numrc and rc.relatdatedeb between sp.begin_date and sp.end_date
  group by ps.mat, rc.relatnum, rc.oid, rc.relatdatedeb, rc.relatdatefinW, c.oid, rc.relatdatedeb
  union
  select    ps.mat,rc.relatnum,rc.oid,rc.relatdatedeb ,rc.relatdatefinW,c.oid,su1.end_date+1 ,su2.begin_date-1
  from pers_select ps,PNG_salarie sal,PNG_relationcontrat rc,PNG_contrat c,PNG_suspension su1,PNG_suspension su2, PNG_SalPopulationW sp
  where ps.mat = sal.matricule||sp.noCarrierealpha and sal.oid=rc.relatmatricule  
--  and (rc.relatprincipal=1 or OCTNG_SPECIFIC.getEnv<>'SDV')
  and rc.relatdatefinW>=OCTNG_SPECIFIC.DateDeb
  and rc.oid=c.ctrrelation and c.oid=su1.suspcontrat  and c.oid=su2.suspcontrat 
  and su1.end_date<=su2.begin_date and su1.end_date+1<=su2.begin_date-1
  and not exists(select null from PNG_suspension su3 where c.oid=su3.suspcontrat  and su1.end_date<=su3.begin_date and su3.end_date<=su2.begin_date)
  and	 sal.oid=sp.salarie and rc.relatnum=sp.numrc and su1.end_date+1 between sp.begin_date and sp.end_date
  union
  select    ps.mat,rc.relatnum,rc.oid,rc.relatdatedeb ,rc.relatdatefinW,c.oid,max(su.end_date)+1,rc.relatdatefinW
  from pers_select ps,PNG_salarie sal,PNG_relationcontrat rc,PNG_contrat c,PNG_suspension su, PNG_SalPopulationW sp
  where ps.mat = sal.matricule||sp.noCarrierealpha and sal.oid=rc.relatmatricule  
--  and (rc.relatprincipal=1 or OCTNG_SPECIFIC.getEnv<>'SDV')
  and rc.relatdatefinW>=OCTNG_SPECIFIC.DateDeb
  and rc.oid=c.ctrrelation and c.oid=su.suspcontrat 
  and	 sal.oid=sp.salarie and rc.relatnum=sp.numrc and su.end_date+1 between sp.begin_date and sp.end_date
  group by ps.mat,rc.relatnum,rc.oid,rc.relatdatedeb,rc.relatdatefinW,c.oid
  union
  select    ps.mat,rc.relatnum,rc.oid,rc.relatdatedeb ,rc.relatdatefinW,c.oid,rc.relatdatedeb ,rc.relatdatefinW
  from pers_select ps,PNG_salarie sal,PNG_relationcontrat rc,PNG_contrat c, PNG_SalPopulationW sp
  where ps.mat = sal.matricule||sp.noCarrierealpha and sal.oid=rc.relatmatricule  
--  and (rc.relatprincipal=1 or OCTNG_SPECIFIC.getEnv<>'SDV')
  and rc.relatdatefinW>=OCTNG_SPECIFIC.DateDeb
  and rc.oid=c.ctrrelation
  and not exists(select null from PNG_suspension su3 where c.oid=su3.suspcontrat)
  and	 sal.oid=sp.salarie and rc.relatnum=sp.numrc and rc.relatdatedeb between sp.begin_date and sp.end_date
  ; 


  insert into pers (mat,rc,etat,rcoid,relatdatedeb,relatdatefin,ctroid,ctrdeb,ctrfin,debut)
  select    pc.mat,pc.rc,'S',pc.rcoid,pc.relatdatedeb ,pc.relatdatefin,pc.ctroid,pc.ctrdeb,pc.ctrfin,pc.ctrdeb
  from pers_contrat pc
  union
  select    pc.mat,pc.rc,'S',pc.rcoid,pc.relatdatedeb ,pc.relatdatefin,pc.ctroid,pc.ctrdeb,pc.ctrfin,em.begin_date
  from pers_contrat pc,DBL_PNG_Emploi em,DBL_PNG_ta_Emploi te
  where rcoid=em.emploirelation and em.emploi=te.oid and em.begin_date between pc.ctrdeb and pc.ctrfin
  ;

    -- On supprime ceux qui ne sont pas dans OCTIME
    delete pers p where not exists(select null from OCTIME_pers op where p.mat=op.pers_mat);
    
    -- On supprime ceux qui ne sont pas affecté à un gestionnaire et ceux qui n'ont pas la coche "Octime"
    if OCTNG_SPECIFIC.getEnv<>'SDV' then
      update pers p set etat='N'
      where  not exists(
      select null from 	PNG_RelationContrat rc, PNG_RCINTRFOCTIMEWW oct ,  PNG_AffGestionnaire aff
      where p.rcoid=rc.oid and rc.oid = oct.relationcontrat and oct.AFFECTOCTIME='1'
      and rc.oid=aff.afgstrelation 
      );
    end if;
    MajStatut;

-- Regroupe les période sans changement de statut à l'interieur d'une rc
   loop
    update pers p1 set (fin)=(select fin from pers p2 where p1.mat=p2.mat and p1.ctrdeb=p2.ctrdeb and p1.statut=p2.statut and p1.fin+1=p2.debut)
    where exists(select fin from pers p2 where p1.mat=p2.mat and p1.ctrdeb=p2.ctrdeb and p1.statut=p2.statut and p1.fin+1=p2.debut)
    and not exists(select null from pers p2 where p1.mat=p2.mat and p1.ctrdeb=p2.ctrdeb and p1.statut=p2.statut and p2.fin+1=p1.debut);
  
    delete pers p2 where exists(select null from pers p1 where p1.mat=p2.mat and p1.ctrdeb=p2.ctrdeb and p1.statut=p2.statut and p1.fin=p2.fin and p1.debut<p2.debut);
    exit when sql%rowcount=0;
   end loop;
  
   update pers p set (dretro,dretroc,fretro)=(select
                   greatest(dretro,debut)
                   ,greatest(dretro,debut)
                   ,least(fin,nvl(fretro,decode(p.statut,'t',to_date(Wmois||'15','YYYYMMDD')
                                                        ,'E',to_date(Wmois||'15','YYYYMMDD')
                                                        ,'M',to_date(Wmois||'15','YYYYMMDD') -- modifié éventuellement la période dans REF_STATUT à M ???
                                                        -- Dans ce cas, faire une jointure avec REF_STATUT pour faire le traitement automatique
                                                        ,'p',to_date(Wmois||'20','YYYYMMDD') -- il manque peut-être le statut 'p' qui finit le 20
                                                        ,'P',to_date(Wmois||'20','YYYYMMDD')
                                                        ,last_day(to_date(Wmois||'01','YYYYMMDD'))))) 
                   from pers_select ps 
                   where p.mat=ps.mat);
  OCTNG_TRT.logger(Widtrt,'select_contrat',SQL%ROWCOUNT||' rc sélectionnées',6);
  
    -- Si stc sur porteur avant la fin du mois, on va jusqu'a la date de stc
    update pers p set fretro=fin
    where p.statut in ('t','E','M') and p.fin between to_date(Wmois||'16','YYYYMMDD') and last_day(to_date(Wmois||'01','YYYYMMDD'))
    or    p.statut in ('p','P')     and p.fin between to_date(Wmois||'21','YYYYMMDD') and last_day(to_date(Wmois||'01','YYYYMMDD'));

  -- JL 18/09/2013 corrige la date de retro en cas d'anomalie (normalement en fin de contrat )
    insert into octng_log(idtrt,module,niveau,msg) 
    select Widtrt,'Avertissement',2,'Problème fin de contrat : correction de la date de retro au '||to_char(greatest(p.debut,OCTNG_SPECIFIC.DateDeb),'DD/MM/YYYY')||' ('||to_char(p.dretro,'DD/MM/YYYY')||'>'||to_char(p.fin,'DD/MM/YYYY')||'+1) pour le matricule '||p.mat
    from pers p
    where p.fin+1<p.dretroc and p.relatdatefin+1<p.dretroc
    and not exists(select null from pers p2 where  p.mat=p2.mat and p.rc<p2.rc)    
    order by mat,fin
    ;
    update pers p
    set dretro=greatest(p.debut,OCTNG_SPECIFIC.DateDeb),dretroc=greatest(p.debut,OCTNG_SPECIFIC.DateDeb)
    where p.fin+1<p.dretroc and p.relatdatefin+1<p.dretroc
    and not exists(select null from pers p2 where  p.mat=p2.mat and p.rc<p2.rc)    
    ;
  -- JL 18/09/2013 corrige la date de retro en cas de fin de periode (stc ou changement d'emploi ou suspension ...) de moins de 3 mois
  -- JL 17/09/2014 Finalement on ne revient pas au début du contrat, pose trop de problèmes
 /*   insert into octng_log(idtrt,module,niveau,msg) 
    select Widtrt,'Avertissement',2,'Fin de periode (STC,emploi,suspension ...) ('||to_char(p.fin,'DD/MM/YYYY')||') : correction de la date de retro au '||to_char(greatest(p.debut,OCTNG_SPECIFIC.DateDeb),'DD/MM/YYYY')||' pour le matricule '||p.mat
    from pers p
    where  add_months(sysdate,-3)<=p.fin and p.fin<>to_date('01/01/2999','DD/MM/YYYY')
    order by mat,fin
    ;
    update pers p
    set dretro=greatest(p.debut,OCTNG_SPECIFIC.DateDeb),dretroc=greatest(p.debut,OCTNG_SPECIFIC.DateDeb)
    where  add_months(sysdate,-3)<=p.fin and p.fin<>to_date('01/01/2999','DD/MM/YYYY')
    ;*/
  -- On les laisse que ev_pers_periode est vide
    insert into octng_log(idtrt,module,niveau,msg) 
    select Widtrt,'Avertissement',2,'Suppression du matricule '||p.mat||' (debut>fretro or fin<dretroc) ('||to_char(debut,'DD/MM/YYYY')||'>'||to_char(fretro,'DD/MM/YYYY')||' or '||to_char(fin,'DD/MM/YYYY')||'<'||to_char(dretroc,'DD/MM/YYYY')||')'
    from pers p
    where debut>fretro or fin<dretroc
    order by mat,fin
    ;
 -- On les laisse que ev_pers_periode est vide
   delete pers where debut>fretro or fin<dretroc;

  -- Sauvegarde temporaire des individus en cas de plantage
  -- La vrai sauvegarde se fait à la fin de l'interface
  insert into hst_pers(idtrt,mat,rc,statut,etat,eta,relatdatedeb,relatdatefin,ctrdeb,ctrfin,debut,fin,dretro,fretro,dretroc,dretroev,fretroev,dretroabs,fretroabs,dretrores,fretrores,rcoid,ctroid) 
  select Widtrt,mat,rc,statut,etat,eta,relatdatedeb,relatdatefin,ctrdeb,ctrfin,debut,fin,dretro,fretro,dretroc,dretroev,fretroev,dretroabs,fretroabs,dretrores,fretrores,rcoid,ctroid from pers;
end;

--==============================================================================
procedure select_contrat_erreur(
  Widtrt octng_traitement.idtrt%type
) as
begin

  OCTNG_TRT.logger(Widtrt,'select_contrat_erreur','A FAIRE : <> entre contrat NG et Octime',9);
  OCTNG_TRT.logger(Widtrt,'select_contrat_erreur','A FAIRE : ev_pers.statut=NULL',9);
  -- !!!! Confirmer qu'il ne peux pas  avoir de passage administratif/porteur dans le même contrat
  /*select * from posprev@POCT v 
  where exists(select null from  posprev@POCT v1 where v.pers_mat=v1.pers_mat and v1.pos_cod in('000219','000220'))
   and exists(select null from  posprev@POCT v1 where v.pers_mat=v1.pers_mat and v1.pos_cod not in('000219','000220'))
   order by pers_mat,pos_dat;
  */
end;

--==============================================================================
procedure init(
    Widtrt        octng_traitement.idtrt%type
,	  Wmois         octng_traitement.anneemois%type
,   WabsenceSS    char default 'N'
) as
begin
  -- On intialise d'abors la table ev_pers_periode utilisée pour récupérer les absences
  if OCTNG_SPECIFIC.getEnv<>'SDV' then
    OCTNG_EV.init(Widtrt,Wmois,WabsenceSS);
  end if;
  -- On commence par récupérer les absences qui peuvent modifier la date de début de retro à cause des absences consécutives antérieures
  OCTNG_ABS.init(Widtrt,Wmois,WabsenceSS);
  OCTNG_EV.init(Widtrt,Wmois,WabsenceSS);

  update pers  p set (dretrores,fretrores)=(select min(debut),max(fin) from ev_pers_periode pp where p.mat=pp.mat and p.rc=pp.rc group by pp.mat);
  update pers  p set dretrores=least(nvl(dretrores,to_date('20991231','YYYYMMDD')),dretroabs),fretrores=greatest(nvl(fretrores,to_date('19000101','YYYYMMDD')),fretroabs);
  -- On ne prend que les date de retro des individus valides (sert à récupérer Octime_cptres1)
  update pers_select  p set (dretrores,fretrores)=(select min(dretrores),max(fretrores) from pers pp where p.mat=pp.mat and pp.etat='S' group by pp.mat);
end;

--==============================================================================
procedure erreur(
    Widtrt      octng_traitement.idtrt%type
,	  Wmois         octng_traitement.anneemois%type
) is
  WNb number(9,0);
begin
  update pers hp set etat='a'
  where mat in (
    select pers_mat
    from OCTIME_anomalie a,OCTIME_ANOCAL ac
    where ac.pers_mat=hp.mat and ac.ano_cod=a.ano_cod and ano_type=9
    -- Anomalie postérieure ou égale à la date de rétro
--    and ac.ano_dat between hp.relatdatedeb and hp.relatdatefin 
    and ac.ano_dat<=hp.fretrores
  );
  update pers hp set etat='c'
  where mat in (
    select pers_mat
    from OCTIME_pers p
    where p.pers_mat=hp.mat
    -- date de dernier jour calculé antérieur à la date de fin de rétro
 --   and p.pers_dfcal between hp.relatdatedeb and hp.relatdatefin 
    and p.pers_dfcal<hp.fretrores
    union
    select pers_mat
    from OCTIME_CALDIF p
    where p.pers_mat=hp.mat
    -- Début de calcul différé postérieur ou égal à la date de rétro
 --   and p.cal_datd between hp.relatdatedeb and hp.relatdatefin 
    and p.cal_datd<=hp.fretrores
  );
  
  update octng_traitement t set statut='X' where idtrt=Widtrt and exists(select null from pers p where p.etat not in ('S','N'));
  -- 24/05/2013 On supprime les absences et les periodes des individus en erreurs ==> cause des problèmes dans les regroupements si indvidus non calculés
  delete abs where (mat,rc) in (select mat,rc from pers where etat<>'S');
  delete ev_pers_periode where (mat,rc) in (select mat,rc from pers where etat<>'S');

  insert into octng_log(idtrt,datesys,module,niveau,msg)
  select Widtrt,systimestamp,'ERREUR Anomalie',0,'Individu : '||hp.mat||' Rc : '||hp.rc||' Début : '||to_char(ac.ano_dat,'DD/MM/YYYY')||' Fin:'||to_char(ac.ano_datf,'DD/MM/YYYY')||' Erreur:'||a.ano_lib 
  from OCTIME_anomalie a,OCTIME_ANOCAL ac,pers hp
  where ac.pers_mat=hp.mat and ac.ano_cod=a.ano_cod and ano_type=9
  group by Widtrt, systimestamp,hp.mat,hp.rc,ac.ano_dat,ac.ano_datf,a.ano_lib 
  having ac.ano_dat<=max(hp.fretrores)
  order by hp.mat,hp.rc,ac.ano_dat;
  WNb:=SQL%ROWCOUNT;
  
  -- dates non calculées anterieur à la date de fin d'une absence
  insert into octng_log(idtrt,datesys,module,niveau,msg)
  select Widtrt,systimestamp,'ERREUR Calcul',0,'Individu : '||hp.mat||' Rc : '||hp.rc||' Début : '||to_char(p.pers_dfcal,'DD/MM/YYYY')
  from OCTIME_pers p,pers hp
  where p.pers_mat=hp.mat 
  group by Widtrt, systimestamp,hp.mat,hp.rc,p.pers_dfcal 
  having p.pers_dfcal<max(hp.fretrores)
  order by hp.mat,hp.rc;
  WNb:=WNb+SQL%ROWCOUNT;
 
  insert into octng_log(idtrt,datesys,module,niveau,msg)
  select Widtrt,systimestamp,'ERREUR Calcul Différé',0,'Individu : '||hp.mat||' Rc : '||hp.rc||' Début : '||to_char(min(p.cal_datd),'DD/MM/YYYY')
  from OCTIME_CALDIF p,pers hp
  where p.pers_mat=hp.mat
  group by hp.mat,hp.rc,p.cal_datd
  having min(p.cal_datd)<=max(hp.fretrores)
  order by hp.mat,hp.rc;
  WNb:=WNb+SQL%ROWCOUNT;
/*  if WNb>0 then
    OCTNG_TRT.raise_erreur(Widtrt,'abs','ano');
  end if;*/
end;

--==============================================================================
procedure init_cptres1(
    Widtrt			  octng_traitement.idtrt%type
,   WabsenceSS    char default 'N'
) as
cursor c is 
select distinct cpt from ref_ev_cpt where isNumeric(cpt)=1 and WabsenceSS='N'
--union select distinct cpt from ref_ev_abs where isNumeric(cpt)=1 and WabsenceSS='N'
union select distinct cpt from ref_abs where isnumeric(cpt)=1
--union select distinct cpt from ref_ast where isnumeric(cpt)=1
union select distinct nbjrstot from ref_abs where isnumeric(nbjrstot)=1
union select distinct nbjrstx1 from ref_abs where isnumeric(nbjrstx1)=1
union select distinct nbjrstx2 from ref_abs where isnumeric(nbjrstx2)=1
union select '3' from dual where WabsenceSS='N'  -- ProPrimTrp
union select '5' from dual where WabsenceSS='N'  -- ProPrimTrp
union select '237' from dual where WabsenceSS='N' -- ticket resto
union select '106' from dual where WabsenceSS='N' -- Maj100
union select '201' from dual -- code abs 1
union select '202' from dual -- code abs 2
union select '203' from dual -- code abs 3
union select '204' from dual -- tps abs 1 heure
union select '205' from dual -- tps abs 2 heure
union select '206' from dual -- tps abs 3 heure
union select '207' from dual -- tps abs 1
union select '208' from dual -- tps abs 2
union select '209' from dual -- tps abs 3
union select '258' from dual -- ??
union select '131' from dual -- AME : NDRDIF
union select '133' from dual -- AME : NDRDIF
union select '134' from dual -- AME : NDRDIF
union select '24' from dual -- PPS : 
union select '149' from dual -- PPS (DSER): 
union select '122' from dual -- SDV (DRTRTT): 
union select '155' from dual -- SDV : ABSCPPORT
union select '161' from dual -- SDV : ABSCPPORT
union select '164' from dual -- SDV : ABSCPPORT
order by 1;
s varchar2(4000);
t1  timestamp(6);
t2  timestamp(6);
Wduree varchar2(50);
begin
  
  t1:=systimestamp;
  s:='alter session enable PARALLEL ddl';
  OCTNG_TRT.logsql(Widtrt,'CPTRES1',s);

  s:='c.pers_mat,c.cal_dat,c.niv_cod1,c.cont_cod,c.cyc_cod,c.hor_cod,c.hor_theo';
  for cpt in c loop
    s:=s||',c.cal_val'||cpt.cpt;
  end loop;
  OCTNG_TRT.logger(Widtrt,'CPTRES1','Création de la table temporaire OCTIME_CPTRES1_'||Widtrt);
  
  s:='create table OCTIME_CPTRES1_'||Widtrt||' PARALLEL as select '||s||' from OCTIME_cptres1 c,pers_select p where c.pers_mat=p.mat and c.cal_dat between p.dretrores and p.fretrores';
  OCTNG_TRT.logsql(Widtrt,'CPTRES1',s);
  
  s:='alter table OCTIME_CPTRES1_'||Widtrt||' ADD CONSTRAINT OCTIME_CPTRES1_'||Widtrt||'_PK PRIMARY KEY (PERS_MAT , CAL_DAT )ENABLE';
  OCTNG_TRT.logsql(Widtrt,'CPTRES1',s);

  s:='create index OCTIME_CPTRES1_'||Widtrt||'_INDEX1 ON OCTIME_CPTRES1_'||Widtrt||' (PERS_MAT)';
  OCTNG_TRT.logsql(Widtrt,'CPTRES1',s);

  s:='create index OCTIME_CPTRES1_'||Widtrt||'_INDEX2 ON OCTIME_CPTRES1_'||Widtrt||' (CAL_DAT)';
  OCTNG_TRT.logsql(Widtrt,'CPTRES1',s);

  t2:=systimestamp;
  select to_char(t2-t1) into Wduree from dual;
  OCTNG_TRT.logger(Widtrt,'CPTRES1','Durée de création de la table : '||substr(Wduree,12,2)||'h '||substr(Wduree,15,2)||'mn '||substr(Wduree,18,2)||'s '||substr(Wduree,21,3)||'ms');
end;

--==============================================================================
procedure exec(
    Widtrt        octng_traitement.idtrt%type
,	  Wmois         octng_traitement.anneemois%type
,   WabsenceSS    char default 'N'
) as
begin
    OCTNG_ABS.exec(Widtrt,Wmois,WabsenceSS);
    OCTNG_EV.exec(Widtrt,Wmois,WabsenceSS);
end;


--==============================================================================
procedure interface(
    Wlogin			  octng_traitement.nomuser%type
,   WTypetrt      octng_traitement.typetrt%type default 'CAa'
,   WMois         octng_traitement.anneemois%type
,   WLib          octng_traitement.lib%type default 'Interface Individuelle'
,   Wdatetrt			octng_traitement.datetrt%type default systimestamp
,   Wdateretro		octng_traitement.dateretro%type
,   WClause       octng_traitement.clause%type
) IS
Widtrt octng_traitement.idtrt%type;
Wnb   number(9,0);
WabsenceSS  char(1);
begin
--  if (Widtrt is null) then  
    insert into octng_traitement(datetrt,nomuser,anneemois,typetrt,lib,module,etat,statut,dateretro,clause) values(Wdatetrt,Wlogin,Wmois,WTypetrt,WLib,'C','C','S',Wdateretro,WClause);
    select idtrt into Widtrt from octng_traitement where datetrt=wdatetrt;
--  end if;
  OCTNG_TRT.debut(Widtrt,Wlogin,Wdatetrt,Wmois,WTypetrt,WLib,Wdateretro,WClause);

  PNG_REFRESH;
/*
  delete pers_select;
  insert into pers_select select distinct pers_mat,to_date('01/01/1900','DD/MM/YYYY'),to_date('31/12/2099','DD/MM/YYYY') from OCTIME_pers;
*/
  if (WTypetrt='aut') then
    WabsenceSS:='O';
    OCTNG_ABS.init_pers_select(Widtrt,Wdatetrt);
  else
    WabsenceSS:='N';
  end if;  
  select count(*) into Wnb from pers_select;
  If Wnb=0 then
    OCTNG_TRT.succes(Widtrt,'V');
  else
    select_contrat(Widtrt,Wmois);
    select_contrat_erreur(Widtrt);
    init(Widtrt,Wmois,WabsenceSS);
    erreur(Widtrt,Wmois);
    init_cptres1(Widtrt,WabsenceSS);
    exec(Widtrt,Wmois,WabsenceSS);
    update pers hp set etat='C' where etat='S';
    delete hst_pers where idtrt=Widtrt;
    insert into hst_pers(idtrt,mat,rc,statut,etat,eta,relatdatedeb,relatdatefin,ctrdeb,ctrfin,debut,fin,dretro,fretro,dretroc,dretroev,fretroev,dretroabs,fretroabs,dretrores,fretrores,rcoid,ctroid) 
    select Widtrt,mat,rc,statut,etat,eta,relatdatedeb,relatdatefin,ctrdeb,ctrfin,debut,fin,dretro,fretro,dretroc,dretroev,fretroev,dretroabs,fretroabs,dretrores,fretrores,rcoid,ctroid from pers;
    if (WTypetrt<>'aut') then
      actualise_NG(Wlogin,Widtrt);
    end if;
    OCTNG_TRT.succes(Widtrt);
    if (WTypetrt<>'aut') then
    -- ATTENTION, si on fait une interface précédent le mois de paie en cours, la date de retro va être fausse !!!!!
      -- met à jour la date de retro dans  Octime
      -- Si dretro=le plus petit dernier jour des periodes de paie en cours+1 ==> le calcul peut-être intégré, sinon non
      -- A la cloture, dretroanc=dretro
      update OCTIME_pers_compl pc 
      set (pers_dretroanc,pers_dretro)=(select least(pc.pers_dretro,pc.pers_dretroanc)
                                              ,to_date(to_char(sysdate,'YYYYMM')||decode(p.statut,'M','15','t','15','E','15','p','20','P','20',to_char(last_day(sysdate),'DD')),'YYYYMMDD')+1
                                              from pers p 
                                              where p.mat=pc.pers_mat 
                                              and p.fin in (select max(p2.fin) from pers p2 where p.mat=p2.mat group by p2.mat)
                                              )
      where pers_mat in (select mat from pers);
    end if;
  end if;
exception
when OTHERS THEN
  OCTNG_TRT.erreur(Widtrt);
end;
  
--==============================================================================
-- Procédures publiques
--==============================================================================
PROCEDURE automatique(
    Wlogin			  octng_traitement.nomuser%type
,   Wdatetrt			octng_traitement.datetrt%type default systimestamp
) IS
Widtrt octng_traitement.idtrt%type;
WMois         octng_traitement.anneemois%type;
begin
  select p.anneemois into WMois from OCTNG_Paie p;
  OCTNG.interface(Wlogin,'aut',WMois,'AbsenceSS',Wdatetrt,null,null);
  select idtrt into Widtrt from octng_traitement where datetrt=wdatetrt;
  -- A FAIRE : tester si pas d'erreur
  integre_NG(Wlogin,Widtrt);
end;

--==============================================================================
procedure individuel(
    Wlogin			  octng_traitement.nomuser%type
,   WTypetrt      octng_traitement.typetrt%type default 'CAa'
,   WMois         octng_traitement.anneemois%type
,   WLib          octng_traitement.lib%type default 'Interface Individuelle'
,   Wdatetrt			octng_traitement.datetrt%type default systimestamp
,   Wdateretro		octng_traitement.dateretro%type
,   WClause       octng_traitement.clause%type
) IS
begin
  delete pers_select;
  If WClause is null then
    raise_application_error(-20001,'Vous devez sélectionner des individus');
  end if;
	IF Wdateretro is not null THEN
		execute immediate 'insert into pers_select(mat,dretro) select distinct v.pers_mat,'''||Wdateretro||'''  from Voct_pers_select v where '||to_char(WClause);
	ELSE
		execute immediate 'insert into pers_select(mat,dretro) select distinct v.pers_mat,dretro from Voct_pers_select v,OCTIME_pers_compl c where v.pers_mat=c.pers_mat and '||to_char(WClause);
  END IF;
  OCTNG.interface(Wlogin,WTypetrt,WMois,WLib,Wdatetrt,Wdateretro,WClause);
end;
  
--==============================================================================
PROCEDURE clone(
    Wlogin			  octng_traitement.nomuser%type
,   Widtrt_org	  octng_traitement.idtrt%type
,   Wdatetrt			octng_traitement.datetrt%type default systimestamp
) is
  Wmois         octng_traitement.anneemois%type;
  WTypetrt      octng_traitement.typetrt%type;
  Wlib			    octng_traitement.lib%type;
  Wdateretro		octng_traitement.dateretro%type;
  Wclause			  octng_traitement.clause%type;
begin
  -- Avec le mois de paie en cours  et les individus actuels
  select p.anneemois,t.typetrt,t.lib,t.dateretro,t.clause into WMois,Wtypetrt,Wlib,Wdateretro,Wclause from OCTNG_traitement t,OCTNG_Paie p where t.idtrt=Widtrt_org;
/*
  delete pers_select;
  insert into pers_select(mat,dretro) 
  select distinct pc.pers_mat,least(pc.pers_dretro,pc.pers_dretroanc)
  from OCTIME_pers_compl pc,hst_pers hp 
  where pc.pers_mat=hp.mat and hp.idtrt=Widtrt_org;
*/  
  OCTNG.individuel(Wlogin,Wtypetrt,WMois,Wlib,Wdatetrt,Wdateretro,Wclause);
end;

--==============================================================================
PROCEDURE clone_debug(
    Wlogin			  octng_traitement.nomuser%type
,   Widtrt_org	  octng_traitement.idtrt%type
,   Wdatetrt			octng_traitement.datetrt%type default systimestamp
) is
  Wmois         octng_traitement.anneemois%type;
  WTypetrt      octng_traitement.typetrt%type;
  Wlib			    octng_traitement.lib%type;
  Wdateretro		octng_traitement.dateretro%type;
  Wclause			  octng_traitement.clause%type;
begin
-- Avec le mois de paie de l'interface d'origine et les individus du comptage d'origine
  select t.anneemois,t.typetrt,t.lib,t.dateretro,t.clause into WMois,Wtypetrt,Wlib,Wdateretro,Wclause from OCTNG_traitement t,OCTNG_Paie p where t.idtrt=Widtrt_org;

  delete pers_select;
  insert into pers_select(mat,dretro) 
  select hp.mat,hp.dretro
  from hst_pers hp 
  where hp.idtrt=Widtrt_org;

  OCTNG.interface(Wlogin,Wtypetrt,WMois,Wlib,Wdatetrt,Wdateretro,Wclause);
end;

--==============================================================================
PROCEDURE actualise(
    Wlogin			  octng_traitement.nomuser%type
,   Widtrt	      octng_traitement.idtrt%type
) is
  Wmois         octng_traitement.anneemois%type;
  WTypetrt      octng_traitement.typetrt%type;
  WDatetrt      octng_traitement.datetrt%type;
  Wlib			    octng_traitement.lib%type;
  Wdateretro		octng_traitement.dateretro%type;
  Wclause			  octng_traitement.clause%type;
begin
  -- Avec le mois de paie en cours  et les individus actuels
  select p.anneemois,t.typetrt,t.lib,t.datetrt,t.dateretro,t.clause into WMois,Wtypetrt,Wlib,Wdatetrt,Wdateretro,Wclause from OCTNG_traitement t,OCTNG_Paie p where t.idtrt=Widtrt;
  -- On supprime le comptage actuel pour le remplacer
  delete hst_ev where idtrt=Widtrt;
  delete hst_abs where idtrt=Widtrt;
  delete hst_ev_erreur where idtrt=Widtrt;
  delete hst_abs_erreur where idtrt=Widtrt;
  delete hst_ev_pers_periode where idtrt=Widtrt;
  delete hst_res where idtrt=Widtrt;
  delete hst_dif where idtrt=Widtrt;
  delete hst_pers where idtrt=Widtrt;
  delete octng_log where idtrt=Widtrt;
  delete octng_sql where idtrt=Widtrt;
  delete octng_traitement where idtrt=Widtrt;
/*  
  delete pers_select;
  insert into pers_select(mat,dretro) 
  select distinct pc.pers_mat,least(pc.pers_dretro,pc.pers_dretroanc)
  from OCTIME_pers_compl pc,hst_pers hp 
  where pc.pers_mat=hp.mat and hp.idtrt=Widtrt;
*/
  OCTNG.individuel(Wlogin,Wtypetrt,WMois,Wlib,systimestamp,Wdateretro,Wclause);
end;

procedure actualise_NG(
    Wlogin			  octng_traitement.nomuser%type
,   Widtrt        octng_traitement.idtrt%type
) is
WabsenceSS  char(1);
begin
OCTNG_TRT.logger(Widtrt,'dif_NG','début',9);
  delete hst_dif where idtrt=Widtrt;
  select decode(typetrt,'aut','O','N') into WabsenceSS from octng_traitement where idtrt=Widtrt;  
  
  -- On ne change pas le statut qui peut-être à X (erreur de calcul)
  update OCTNG_TRAITEMENT set module='C',etat='C' where idtrt=WIdtrt;
  PNG_REFRESH;
  OCTNG_ABS.dif_NG(Widtrt,WabsenceSS);
  OCTNG_EV.dif_NG(Widtrt,WabsenceSS);

  INSERT INTO HST_DIF
  ( IDTRT,DIF,MAT,RC,DATECONTRAT,
    STRUCTURE,MOTIF,DATEDEB,DATEFIN, DATEFIN_HST,
    NBJRSTOT,NBJRSTOT_HST,NBJRSTX1, NBJRSTX1_HST,NBJRSTX2,NBJRSTX2_HST,NBJRSTX3,NBJRSTX3_HST,NBJRSTX4,NBJRSTX4_HST,NBJRSTX5,NBJRSTX5_HST,
    JRSIJTX1,JRSIJTX1_HST,JRSIJTX2,JRSIJTX2_HST,JRSIJTX3,JRSIJTX3_HST,JRSIJTX4,JRSIJTX4_HST,
    CODEDEMIJR,ANNEEMOIS,ANNEEMOIS_HST,PROLONGATION,PROLONGATION_HST
  )
  SELECT Widtrt,h.dif,h.mat,h.rc,h.datecontrat,
    STRUCTURE,MOTIF,DATEDEB,DATEFIN, DATEFIN_HST,
    NBJRSTOT,NBJRSTOT_HST,NBJRSTX1, NBJRSTX1_HST,NBJRSTX2,NBJRSTX2_HST,NBJRSTX3,NBJRSTX3_HST,NBJRSTX4,NBJRSTX4_HST,NBJRSTX5,NBJRSTX5_HST,
    JRSIJTX1,JRSIJTX1_HST,JRSIJTX2,JRSIJTX2_HST,JRSIJTX3,JRSIJTX3_HST,JRSIJTX4,JRSIJTX4_HST,
    CODEDEMIJR,ANNEEMOIS,ANNEEMOIS_HST,PROLONGATION,PROLONGATION_HST
  FROM abs_dif h
  ;
  INSERT INTO HST_DIF
  ( IDTRT,DIF,MAT,RC,DATECONTRAT,
    STRUCTURE,MOTIF,DATEDEB,DATEFIN, DATEFIN_HST,
    NBJRSTOT,NBJRSTOT_HST,NBJRSTX1, NBJRSTX1_HST,NBJRSTX2,NBJRSTX2_HST,NBJRSTX3,NBJRSTX3_HST,NBJRSTX4,NBJRSTX4_HST,NBJRSTX5,NBJRSTX5_HST,
    JRSIJTX1,JRSIJTX1_HST,JRSIJTX2,JRSIJTX2_HST,JRSIJTX3,JRSIJTX3_HST,JRSIJTX4,JRSIJTX4_HST,
    CODEDEMIJR,ANNEEMOIS,ANNEEMOIS_HST,PROLONGATION,PROLONGATION_HST
  )
  SELECT Widtrt,h.dif,h.mat,h.rc,null,
    'ENGWEV',codepaie,datev,NULL,NULL,
    qte,qte_hst,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL
  FROM ev_dif h
  ;

  update OCTNG_TRAITEMENT set module='C',etat='T' where idtrt=WIdtrt;
  update OCTNG_TRAITEMENT set statut='V' where idtrt=WIdtrt and statut='S' and not exists(select null from HST_DIF where idtrt=Widtrt);

  OCTNG_TRT.logger(Widtrt,'dif_NG','fin',9);  
end;  

procedure integre_NG(
    Wlogin			  octng_traitement.nomuser%type
,   Widtrt        octng_traitement.idtrt%type
) is
  WModule     octng_traitement.module%type;
  Wetat       octng_traitement.etat%type;
  WStatut     octng_traitement.statut%type;
  WMois       octng_traitement.anneemois%type;
  WTypetrt    octng_traitement.typetrt%type;
  WMoisPaie   octng_paie.anneemois%type;
  WBloquePaie octng_paie.bloque%type;
begin
  OCTNG_TRT.logger(Widtrt,'res_NG','Intégration lancée par '||Wlogin);
  select module,etat,statut,anneemois,typetrt into WModule,Wetat,WStatut,Wmois,WTypetrt from OCTNG_traitement where idtrt=Widtrt;
  -- Test du statut de l'interface avant intégration
  if Wmodule='I' then
    OCTNG_TRT.logger(Widtrt,'ERREUR Intégration','Interface déjà intégrée',0);
    return;
  elsif Wetat='T' and Wstatut='V' then
    OCTNG_TRT.logger(Widtrt,'WARNING Intégration','Interface vide, rien à intégrer',0);
    return;
  elsif WTypetrt='CAa' and (Wetat<>'T' or Wstatut<>'S') then -- Wmodule='C'
    OCTNG_TRT.logger(Widtrt,'ERREUR Intégration','Interface en erreur',0);
    raise_application_error(-20001,'Interface en erreur ou déjà intégrée');
  elsif WTypetrt='aut' and (Wetat<>'T' or Wstatut not in ('X','S')) then -- Wmodule='C'
    OCTNG_TRT.logger(Widtrt,'ERREUR Intégration','Interface en erreur',0);
    raise_application_error(-20001,'Interface en erreur ou déjà intégrée');
  end if;
/*
CAa	Manuelle	O	2
aut	Automatique	O	7
*/
  select anneemois,bloque into WMoisPaie,WBloquePaie from OCTNG_Paie;
  If WBloquePaie<>'N' then
    OCTNG_TRT.logger(Widtrt,'ERREUR Intégration','Paie en cours de validation, attendez la clôture avant intégration.',0);
    raise_application_error(-20001,'Paie en cours de validation, attendez la clôture avant intégration.');
  Elsif Wmois<WMoisPaie or OCTNG_SPECIFIC.getPQD='P' and Wmois>WMoisPaie then
    OCTNG_TRT.logger(Widtrt,'ERREUR Intégration','Le mois de paie ('||WMois||') de l''interface ne correspond pas au mois de paie ('||WMoisPaie||').',0);
    raise_application_error(-20001,'Le mois de paie ('||WMois||') de l''interface ne correspond pas au mois de paie ('||WMoisPaie||').');
  End If;
  
  -- Test du changement sur les individus si interface non automatique
  if (Wtypetrt<>'aut') then
    update hst_pers hp
    set etat='m'
    where hp.idtrt=Widtrt 
    and exists(select null from OCTIME_pers_compl p 
        where hp.mat=p.pers_mat 
        and (hp.dretro<>greatest(p.pers_dretroanc,hp.debut) or p.pers_dretro<>(select to_date(to_char(sysdate,'YYYYMM')||decode(hp2.statut,'M','15','t','15','E','15','p','20','P','20',to_char(last_day(sysdate),'DD')),'YYYYMMDD')+1
                                                                                from hst_pers hp2
                                                                                where hp.mat=hp2.mat  and hp.idtrt=hp2.idtrt 
                                                                                and hp2.fin in (select max(hp3.fin) from hst_pers hp3 where hp3.idtrt=hp2.idtrt and hp2.mat=hp3.mat group by hp3.mat))
            )
        -- correction de la date de retro en cas d'anomalie (normalement en fin de contrat )
        and not (hp.fin+1<hp.dretroc and hp.relatdatefin+1<hp.dretroc  and not exists(select null from hst_pers hp2 where hp.idtrt=hp2.idtrt and hp.mat=hp2.mat and hp.rc<hp2.rc)  and hp.dretro=greatest(hp.debut,OCTNG_SPECIFIC.DateDeb))
        -- correction de la date de retro en cas de fin de periode (stc ou changement d'emploi ou suspension ...) de moins de 3 mois
        -- and not (add_months(sysdate,-3)<=hp.fin and hp.fin<>to_date('01/01/2999','DD/MM/YYYY') and hp.dretro=greatest(hp.debut,OCTNG_SPECIFIC.DateDeb))
        );

    insert into octng_log(idtrt,datesys,module,niveau,msg)
    select Widtrt,systimestamp,'ERREUR Interface',0,'Individu : '||hp.mat||' DRetro : '||to_char(p.pers_dretro,'DD/MM/YYYY')||' DRetro_anc:'||to_char(p.pers_dretroanc,'DD/MM/YYYY')||' Erreur: Individu modifé depuis le calcul' 
    from hst_pers hp,OCTIME_pers_compl p 
    where hp.idtrt=Widtrt and hp.mat=p.pers_mat 
    and etat='m';
  
    if (SQL%RowCount<>0) then
      OCTNG_TRT.logger(Widtrt,'ERREUR Intégration','Interface modifiée depuis le calcul',0);
      if OCTNG_SPECIFIC.getEnv='SDV' then 
        update OCTNG_TRAITEMENT set statut='E' where idtrt=WIdtrt;
        raise_application_error(-20001,'Interface modifiée depuis le calcul');
      else
        update OCTNG_TRAITEMENT set statut='X' where idtrt=WIdtrt;
      end if;
    end if;
    
    update hst_pers hp
    set etat='x'
    where hp.idtrt=Widtrt 
    and exists(select null from octng_traitement t,octng_traitement t2,hst_pers hp2
        where hp.idtrt=t.idtrt and t2.dateint>t.datetrt and t2.idtrt=hp2.idtrt and hp.mat=hp2.mat and t2.module='I'
        );
  
    insert into octng_log(idtrt,datesys,module,niveau,msg)
    select Widtrt,systimestamp,'ERREUR Interface',0,'Individu : '||hp.mat||' intégré dans une interface plus récente' 
    from hst_pers hp,OCTIME_pers_compl p 
    where hp.idtrt=Widtrt and hp.mat=p.pers_mat 
    and etat='x';
  
    if (SQL%RowCount<>0) then
      OCTNG_TRT.logger(Widtrt,'ERREUR Intégration','Un ou plusieurs individus intégrés depuis le calcul',0);
      if OCTNG_SPECIFIC.getEnv='SDV' then 
        update OCTNG_TRAITEMENT set statut='E' where idtrt=WIdtrt;
        raise_application_error(-20001,'Un ou plusieurs individus intégrés depuis le calcul');
      else
        update OCTNG_TRAITEMENT set statut='X' where idtrt=WIdtrt;
      end if;
    end if;
  end if;

  -- On bloque la saisie à la date de fin de retro
  if OCTNG_SPECIFIC.getEnv<>'SDV' then
    update OCTIME_pers_compl c
    set pers_blopaye=(select max(fretro) from hst_pers p where c.pers_mat=p.mat group by p.mat)
    where exists(select null from hst_pers p where c.pers_mat=p.mat);
  end if;
  OCTNG.actualise_NG(WLogin,Widtrt);
  
  update OCTNG_TRAITEMENT set module='I',etat='A',dateint=sysdate/*,statut='S'*/ where idtrt=WIdtrt;
  
  delete hst_res where idtrt=Widtrt;
  insert into hst_res(idtrt,ordre,res)
    select distinct Widtrt,rpad(trim(e.mat),8,'Z')||'|'||e.rc||'|1','#DEBUT_TRS' 
    from ev_dif e,hst_pers p
    where e.mat=p.mat and e.rc=p.rc and p.etat='C'
    and   e.dif<>'='
    and p.idtrt=Widtrt
  union
    select distinct Widtrt,rpad(trim(a.mat),8,'Z')||'|'||a.rc||'|1','#DEBUT_TRS' 
    from abs_dif a,hst_pers p
    where a.mat=p.mat and a.rc=p.rc and p.etat='C'
    and   a.dif<>'='
    and p.idtrt=Widtrt
  union
    -- On place les suppressions avant les ajouts/modifications
    select 
    Widtrt,rpad(trim(e.mat),8,'Z')||'|'||e.rc||'|2'||decode(e.dif,'S','1','2')||to_char(e.datev,'YYYYMMDD'),
    'EvFactoryW     '
    ||'|'||e.dif
    ||'|'||'Octime         '
    ||'|'||rpad(trim(e.mat),8,'Z')
    ||'|'||e.rc         	--RelationContrat
    ||'|'||rpad(e.codepaie,10)
    ||'|'||to_char(e.datev,'YYYYMMDD')      -- dateEffet
    ||'|'||decode(e.ordre,0,'   ',to_char(e.ordre,'00'))	-- noOrdre
    ||'|'||'        |          |0|0|0|0|0|0'
    ||'|'||to_char(nvl(decode(e.dif,'S',e.qte_hst,e.qte),0) ,'00000000.00')
    ||'|'||to_char(nvl(decode(e.dif,'S',e.taux_hst,e.taux),0),'0000000.000')
    ||'|'||to_char(nvl(decode(e.dif,'S',e.val_hst,e.val),0) ,'00000000.00')
    ||'|'||' '          			-- signe
    --||'|'||rpad(nvl(lib,' '),30) 		--lib
    ||'|'||rpad(' ',30) 			--lib
    ||'|'||'      ||      | |    |   |          |0|0|      |   |      |   |      | | ||'
    from ev_dif e,hst_pers p
    where e.mat=p.mat and e.rc=p.rc and p.etat='C'
    and   e.dif<>'='
    and p.idtrt=Widtrt
  union
    select Widtrt,rpad(trim(a.mat),8,'Z')||'|'||a.rc||'|2'||decode(a.dif,'S','1','2')||to_char(a.datedeb,'YYYYMMDD'),
    rpad(decode(a.structure,'ENGXMT','ENGXMTW','ENGXMA','ENGXMAW','ENGXAT','ENGXATW',a.structure),15)||'|'||
    a.dif||'|'||
    'Octime         |'||
    rpad(trim(a.mat),8,'Z')||'|'||
    a.rc||'|'||
    to_char(a.datecontrat,'YYYYMMDD')||'|'||
    a.motif||'|'||
    to_char(a.datedeb,'YYYYMMDD')||'|'||
    to_char(decode(dif,'S',a.datefin_hst,a.datefin),'YYYYMMDD')||'|'||
    a.codedemijr||'|'||
    decode(rta.nbjrstot,null,'',to_char(nvl(decode(dif,'S',a.nbjrstot_hst,a.nbjrstot),0),rta.nbjrstot)||'|')||
    decode(dif,'S',a.anneemois_hst,a.anneemois)||'|'||
    decode(rta.nbjrstx1,null,'',to_char(nvl(decode(dif,'S',a.nbjrstx1_hst,a.nbjrstx1),0),rta.nbjrstx1)||'|')||
    decode(rta.nbjrstx2,null,'',to_char(nvl(decode(dif,'S',a.nbjrstx2_hst,a.nbjrstx2),0),rta.nbjrstx2)||'|')||
    decode(rta.nbjrstx3,null,'',to_char(nvl(decode(dif,'S',a.nbjrstx3_hst,a.nbjrstx3),0),rta.nbjrstx3)||'|')||
    decode(a.structure,'ENGXMT','|')||
    decode(rta.nbjrstx4,null,'',to_char(nvl(decode(dif,'S',a.nbjrstx4_hst,a.nbjrstx4),0),rta.nbjrstx4)||'|')||
    decode(rta.jrsijtx1,null,'',to_char(nvl(decode(dif,'S',a.jrsijtx1_hst,a.jrsijtx1),0),rta.jrsijtx1)||'|')||
    decode(rta.jrsijtx2,null,'',to_char(nvl(decode(dif,'S',a.jrsijtx2_hst,a.jrsijtx2),0),rta.jrsijtx2)||'|')||
    decode(rta.jrsijtx3,null,'',to_char(nvl(decode(dif,'S',a.jrsijtx3_hst,a.jrsijtx3),0),rta.jrsijtx3)||'|')||
    decode(rta.jrsijtx4,null,'',to_char(nvl(decode(dif,'S',a.jrsijtx4_hst,a.jrsijtx4),0),rta.jrsijtx4)||'|')||
    decode(rta.nbjrstx5,null,'',to_char(nvl(decode(dif,'S',a.nbjrstx5_hst,a.nbjrstx5),0),rta.nbjrstx5)||'|')||
    decode(rta.absenceSS,'O',decode(dif,'S',a.prolongation_hst,a.prolongation)||'|','')||
    '0|'||  
    decode(a.structure,'ENGXMT','|||||','ENGXMA','|||||','ENGXAT','|||||')
    from abs_dif a,ref_type_abs rta,hst_pers p
    where a.mat=p.mat and a.rc=p.rc and p.etat='C'
    and   dif<>'='
    and a.structure=rta.structure
    and p.idtrt=Widtrt
  union
    select distinct Widtrt,rpad(trim(a.mat),8,'Z')||'|'||a.rc||'|3','#FIN_TRS' 
    from abs_dif a,hst_pers p
    where a.mat=p.mat and a.rc=p.rc and p.etat='C'
    and   a.dif<>'='
    and p.idtrt=Widtrt
  union
    select distinct Widtrt,rpad(trim(e.mat),8,'Z')||'|'||e.rc||'|3','#FIN_TRS' 
    from ev_dif e,hst_pers p
    where e.mat=p.mat and e.rc=p.rc and p.etat='C'
    and   e.dif<>'='
    and p.idtrt=Widtrt
  ;
  if (sql%rowcount=0) then
    update OCTNG_TRAITEMENT set statut='V' where idtrt=WIdtrt and statut='S'; -- On ne change pas le statut si X
  end if;
  update OCTNG_TRAITEMENT set etat='T' where idtrt=WIdtrt; -- On ne change pas le statut si X
  OCTNG_TRT.logger(Widtrt,'fin','Fin de la génération du fichier');
exception
when OTHERS THEN
  update octng_traitement set  etat='T',statut='E' where idtrt=Widtrt;
  Log_Errors(Widtrt,DBMS_UTILITY.FORMAT_ERROR_STACK);
  Log_Errors(Widtrt,DBMS_UTILITY.FORMAT_ERROR_BACKTRACE);
  OCTNG_TRT.logger(Widtrt,'fin','Fin de l''intégration');
  raise_application_error(-20000,'Erreur !!! C''est moche !!!');
end;

end OCTNG;

/
--------------------------------------------------------
--  DDL for Package Body OCTNG_ABS
--------------------------------------------------------

  CREATE OR REPLACE PACKAGE BODY "OCTNG_ABS" AS
-- ATTENTION au rupture sur ref_abs (jointure avec abs)
--delete POCT_cptres1 r  where not exists(select null from abs_pers_periode p where r.pers_mat=p.pers_mat and p.debut<=r.cal_dat and r.cal_dat>=p.fin);

--==============================================================================
-- Procédures privées
--==============================================================================
procedure erreur(
  Widtrt    octng_traitement.idtrt%type
) as
begin
  insert into octng_log(idtrt,module,niveau,msg) select Widtrt,'Avertissement',2,'Valeur inconnue dans ref_abs pour statut='||abs.statut||' et abs_cod='||abs.abs_cod  from abs where not exists(select null from ref_abs  where abs.statut=ref_abs.statut and abs.abs_cod=ref_abs.abs_cod);

  insert into octng_log(idtrt,module,niveau,msg) select Widtrt,'Avertissement',2,'Valeur ignorée dans ref_abs.NBJRSTOT pour statut='||ref_abs.statut||' et abs_cod='||ref_abs.abs_cod||' car non renseignée dans ref_type_abs.NBJRSTOT'  from ref_abs,ref_type_abs  where ref_abs.structure=ref_type_abs.structure and ref_type_abs.NBJRSTOT is null and ref_abs.NBJRSTOT is not null;
  insert into octng_log(idtrt,module,niveau,msg) select Widtrt,'Avertissement',2,'Valeur ignorée dans ref_abs.NBJRSTX1 pour statut='||ref_abs.statut||' et abs_cod='||ref_abs.abs_cod||' car non renseignée dans ref_type_abs.NBJRSTX1'  from ref_abs,ref_type_abs  where ref_abs.structure=ref_type_abs.structure and ref_type_abs.NBJRSTX1 is null and ref_abs.NBJRSTX1 is not null;
  insert into octng_log(idtrt,module,niveau,msg) select Widtrt,'Avertissement',2,'Valeur ignorée dans ref_abs.NBJRSTX2 pour statut='||ref_abs.statut||' et abs_cod='||ref_abs.abs_cod||' car non renseignée dans ref_type_abs.NBJRSTX2'  from ref_abs,ref_type_abs  where ref_abs.structure=ref_type_abs.structure and ref_type_abs.NBJRSTX2 is null and ref_abs.NBJRSTX2 is not null;
  insert into octng_log(idtrt,module,niveau,msg) select Widtrt,'Avertissement',2,'Valeur ignorée dans ref_abs.NBJRSTX3 pour statut='||ref_abs.statut||' et abs_cod='||ref_abs.abs_cod||' car non renseignée dans ref_type_abs.NBJRSTX3'  from ref_abs,ref_type_abs  where ref_abs.structure=ref_type_abs.structure and ref_type_abs.NBJRSTX3 is null and ref_abs.NBJRSTX3 is not null;
  insert into octng_log(idtrt,module,niveau,msg) select Widtrt,'Avertissement',2,'Valeur ignorée dans ref_abs.NBJRSTX4 pour statut='||ref_abs.statut||' et abs_cod='||ref_abs.abs_cod||' car non renseignée dans ref_type_abs.NBJRSTX4'  from ref_abs,ref_type_abs  where ref_abs.structure=ref_type_abs.structure and ref_type_abs.NBJRSTX4 is null and ref_abs.NBJRSTX4 is not null;
  insert into octng_log(idtrt,module,niveau,msg) select Widtrt,'Avertissement',2,'Valeur ignorée dans ref_abs.NBJRSTX5 pour statut='||ref_abs.statut||' et abs_cod='||ref_abs.abs_cod||' car non renseignée dans ref_type_abs.NBJRSTX5'  from ref_abs,ref_type_abs  where ref_abs.structure=ref_type_abs.structure and ref_type_abs.NBJRSTX5 is null and ref_abs.NBJRSTX5 is not null;
  insert into octng_log(idtrt,module,niveau,msg) select Widtrt,'Avertissement',2,'Valeur ignorée dans ref_abs.JRSIJTX1 pour statut='||ref_abs.statut||' et abs_cod='||ref_abs.abs_cod||' car non renseignée dans ref_type_abs.JRSIJTX1'  from ref_abs,ref_type_abs  where ref_abs.structure=ref_type_abs.structure and ref_type_abs.JRSIJTX1 is null and ref_abs.JRSIJTX1 is not null;
  insert into octng_log(idtrt,module,niveau,msg) select Widtrt,'Avertissement',2,'Valeur ignorée dans ref_abs.JRSIJTX2 pour statut='||ref_abs.statut||' et abs_cod='||ref_abs.abs_cod||' car non renseignée dans ref_type_abs.JRSIJTX2'  from ref_abs,ref_type_abs  where ref_abs.structure=ref_type_abs.structure and ref_type_abs.JRSIJTX2 is null and ref_abs.JRSIJTX2 is not null;
  insert into octng_log(idtrt,module,niveau,msg) select Widtrt,'Avertissement',2,'Valeur ignorée dans ref_abs.JRSIJTX3 pour statut='||ref_abs.statut||' et abs_cod='||ref_abs.abs_cod||' car non renseignée dans ref_type_abs.JRSIJTX3'  from ref_abs,ref_type_abs  where ref_abs.structure=ref_type_abs.structure and ref_type_abs.JRSIJTX3 is null and ref_abs.JRSIJTX3 is not null;
  insert into octng_log(idtrt,module,niveau,msg) select Widtrt,'Avertissement',2,'Valeur ignorée dans ref_abs.JRSIJTX4 pour statut='||ref_abs.statut||' et abs_cod='||ref_abs.abs_cod||' car non renseignée dans ref_type_abs.NBJRSTOT'  from ref_abs,ref_type_abs  where ref_abs.structure=ref_type_abs.structure and ref_type_abs.JRSIJTX4 is null and ref_abs.JRSIJTX4 is not null;

-- on arrondi sum(e.qte) pour avoir la même précision que nbjrstot
  insert into octng_log(idtrt,module,niveau,msg)
  select Widtrt,'ATTENTION',1,'Individu : '||a.mat||' Rc : '||a.rc||' '||to_char(a.datedeb,'DD/MM/YYYY')||' '' '||ra.motif||' '||ra.ev_p||' sum(abs.nbjrstot)<>sum(ev.qte) ('||to_char(sum(a.nbjrstot))||'<>'||to_char((select sum(e.qte) from ev e where e.mat=a.mat and e.rc=a.rc and trim(e.codepaie) = trim(ra.ev_p) and e.datev between a.datedeb and a.datefin group by e.mat))||')'
  from abs a,ref_abs ra
  where   a.structure=ra.structure and a.abs_cod=ra.abs_cod and a.statut=ra.statut
  and ra.debut<=a.datedeb and a.datefin<=ra.fin
  group by a.mat, a.rc, a.datedeb, a.datefin, ra.structure,ra.motif, ra.ev_p
  having sum(a.nbjrstot)<>(select round(sum(qte),1) from ev e where e.mat=a.mat and e.rc=a.rc and trim(e.codepaie) = trim(ra.ev_p) and e.datev between a.datedeb and a.datefin   group by e.mat)
  union
  select Widtrt,'ATTENTION',1,'Individu : '||a.mat||' Rc : '||a.rc||' '||to_char(a.datedeb,'DD/MM/YYYY')||' '' '||ra.motif||' '||ra.ev_r||' sum(abs.nbjrstot)<>sum(ev.qte) ('||to_char(sum(a.nbjrstot))||'<>'||to_char((select round(sum(e.qte),1) from ev e where e.mat=a.mat and e.rc=a.rc and trim(e.codepaie) = trim(ra.ev_r) and e.datev between a.datedeb and a.datefin group by e.mat))||')'
  from abs a,ref_abs ra
  where   a.structure=ra.structure and a.abs_cod=ra.abs_cod and a.statut=ra.statut
  and ra.debut<=a.datedeb and a.datefin<=ra.fin
  group by a.mat, a.rc, a.datedeb, a.datefin, ra.structure,ra.motif, ra.ev_r
  having sum(a.nbjrstot)<>(select round(sum(qte),1) from ev e where e.mat=a.mat and e.rc=a.rc and trim(e.codepaie) = trim(ra.ev_r) and e.datev between a.datedeb and a.datefin  group by e.mat)
  order by 2,3,4
  ;
end;

--==============================================================================
procedure calcul(
  Widtrt      octng_traitement.idtrt%type
,	Wmois       octng_traitement.anneemois%type
) is
  cursor e is select distinct ra.statut,ra.abs_cod,
              decode(rta.NBJRSTOT,null,null,ra.NBJRSTOT) as NBJRSTOT,
              decode(rta.NBJRSTX1,null,null,ra.NBJRSTX1) as NBJRSTX1,
              decode(rta.NBJRSTX2,null,null,ra.NBJRSTX2) as NBJRSTX2,
              decode(rta.NBJRSTX3,null,null,ra.NBJRSTX3) as NBJRSTX3,
              decode(rta.NBJRSTX4,null,null,ra.NBJRSTX4) as NBJRSTX4,
              decode(rta.NBJRSTX5,null,null,ra.NBJRSTX5) as NBJRSTX5,
              decode(rta.JRSIJTX1,null,null,ra.JRSIJTX1) as JRSIJTX1,
              decode(rta.JRSIJTX2,null,null,ra.JRSIJTX2) as JRSIJTX2,
              decode(rta.JRSIJTX3,null,null,ra.JRSIJTX3) as JRSIJTX3,
              decode(rta.JRSIJTX4,null,null,ra.JRSIJTX4) as JRSIJTX4,
              ev_r,ev_p,cpt,
              ra.transformation,
              ra.debut,
              ra.fin
              from ref_abs ra,abs a,ref_type_abs rta 
              where a.abs_cod=ra.abs_cod and a.statut=ra.statut
              and ra.structure=rta.structure
              and ra.debut<=a.datedeb and a.datefin<=ra.fin
            union
              select distinct ra.statut,to_char(ra.ast_cod),
              decode(rta.NBJRSTOT,null,null,ra.NBJRSTOT) as NBJRSTOT,
              decode(rta.NBJRSTX1,null,null,ra.NBJRSTX1) as NBJRSTX1,
              decode(rta.NBJRSTX2,null,null,ra.NBJRSTX2) as NBJRSTX2,
              decode(rta.NBJRSTX3,null,null,ra.NBJRSTX3) as NBJRSTX3,
              decode(rta.NBJRSTX4,null,null,ra.NBJRSTX4) as NBJRSTX4,
              decode(rta.NBJRSTX5,null,null,ra.NBJRSTX5) as NBJRSTX5,
              decode(rta.JRSIJTX1,null,null,ra.JRSIJTX1) as JRSIJTX1,
              decode(rta.JRSIJTX2,null,null,ra.JRSIJTX2) as JRSIJTX2,
              decode(rta.JRSIJTX3,null,null,ra.JRSIJTX3) as JRSIJTX3,
              decode(rta.JRSIJTX4,null,null,ra.JRSIJTX4) as JRSIJTX4,
              null,null,null,
              ra.transformation,
              ra.debut,
              ra.fin
              from ref_ast ra,abs a,ref_type_abs rta 
              where a.abs_cod=to_char(ra.ast_cod) and a.statut=ra.statut
              and ra.structure=rta.structure
              and ra.debut<=a.datedeb and a.datefin<=ra.fin
              order by 2,1;
  s varchar2(4000);
  s1 varchar2(4000);
  s2 varchar2(4000);
  i integer;
  val varchar2(1024);
  col varchar2(1024);
  extracol varchar2(1024);
  Wcptres varchar2(32);
begin
  Wcptres:='OCTIME_CPTRES1_'||Widtrt;
-- A FAIRE : ne pas calculer les indvidus en erreur
/*
cal_val24=cal_val146 et cal_val146=cal_val201 sauf cal_val24=calval146=CP et cal_val201=CP+1

trim(cptres1.cal_val146)<>saiabs.abs_cod
Z000247600	09/06/2011 00:00:00	DEL     	DEL     	DEL     	RTT
Z000247600	09/06/2011 00:00:00	DEL     	DEL     	DEL     	RTT
*/
  OCTNG_TRT.logger(Widtrt,'calcul','Début des calculs',7);

  for ev in e loop
    for i in 1..10 loop
      extracol:='';
      case 
      when i=1 then val:=ev.nbjrstot;col:='NBJRSTOT';
      when i=2 then val:=ev.NBJRSTX1;col:='NBJRSTX1';
      when i=3 then val:=ev.NBJRSTX2;col:='NBJRSTX2';
      when i=4 then val:=ev.NBJRSTX3;col:='NBJRSTX3';
      when i=5 then val:=ev.NBJRSTX4;col:='NBJRSTX4';
      when i=6 then val:=ev.NBJRSTX5;col:='NBJRSTX5';
      when i=7 then val:=ev.JRSIJTX1;col:='JRSIJTX1';
      when i=8 then val:=ev.JRSIJTX2;col:='JRSIJTX2';
      when i=9 then val:=ev.JRSIJTX3;col:='JRSIJTX3';
      when i=10 then val:=ev.JRSIJTX4;col:='JRSIJTX4';
      end case;

--=====================================
-- Maj des colonnes nbjrs et jrsij

     if (isNumeric(val)=1) then
        s:='update abs a set ('||col||')=(select round(sum($$$)***,1) from '||Wcptres||' r where r.pers_mat=a.mat and r.cal_dat between a.datedeb and a.datefin and $$$<>0  ### group by r.pers_mat)';
        s:=s||' where a.abs_cod='''||ev.abs_cod||''' and a.statut='''||ev.statut||''' and a.datedeb between to_date('''||to_char(ev.debut,'YYYYMMDD')||''',''YYYYMMDD'') and to_date('''||to_char(ev.fin,'YYYYMMDD')||''',''YYYYMMDD'')';
        s:=replace(s,'$$$','r.cal_val'||val);
        s:=replace(s,'###',''); 
        s:=replace(s,'***',ev.transformation); 
        OCTNG_TRT.logsql(Widtrt,ev.abs_cod,s);
      elsif (val is not null) then
        if (ev.transformation='/100') then
          s:='update abs a set ('||col||')=(select round(sum(decode(instr('''||val||''',trim(r.cal_val201)),0,0,null,0,r.cal_val207)+decode(instr('''||val||''',trim(r.cal_val202)),0,0,null,0,r.cal_val208)+decode(instr('''||val||''',trim(r.cal_val203)),0,0,null,0,r.cal_val209))/100,1) from '||Wcptres||' r where r.pers_mat=a.mat and r.cal_dat between a.datedeb and a.datefin group by r.pers_mat)';
          s:=s||' where a.abs_cod='''||ev.abs_cod||''' and a.statut='''||ev.statut||''' and a.datedeb between to_date('''||to_char(ev.debut,'YYYYMMDD')||''',''YYYYMMDD'') and to_date('''||to_char(ev.fin,'YYYYMMDD')||''',''YYYYMMDD'')';
          OCTNG_TRT.logsql(Widtrt,ev.abs_cod,s);
        elsif (ev.transformation='/60') then
          s:='update abs a set ('||col||')=(select round(sum(decode(instr('''||val||''',trim(r.cal_val201)),0,0,null,0,r.cal_val204)+decode(instr('''||val||''',trim(r.cal_val202)),0,0,null,0,r.cal_val205)+decode(instr('''||val||''',trim(r.cal_val203)),0,0,null,0,r.cal_val206))/60,1) from '||Wcptres||' r where r.pers_mat=a.mat and r.cal_dat between a.datedeb and a.datefin group by r.pers_mat)';
          s:=s||' where a.abs_cod='''||ev.abs_cod||''' and a.statut='''||ev.statut||''' and a.datedeb between to_date('''||to_char(ev.debut,'YYYYMMDD')||''',''YYYYMMDD'') and to_date('''||to_char(ev.fin,'YYYYMMDD')||''',''YYYYMMDD'')';
          OCTNG_TRT.logsql(Widtrt,ev.abs_cod,s);
        else
          s:='update abs a set ('||col||')=(select round(sum(decode(instr('''||val||''',trim(r.cal_val201)),0,0,null,0,r.cal_val207)+decode(instr('''||val||''',trim(r.cal_val202)),0,0,null,0,r.cal_val208)+decode(instr('''||val||''',trim(r.cal_val203)),0,0,null,0,r.cal_val209))***,1) from '||Wcptres||' r where r.pers_mat=a.mat and r.cal_dat between a.datedeb and a.datefin group by r.pers_mat)';
          s:=s||' where a.abs_cod='''||ev.abs_cod||''' and a.statut='''||ev.statut||''' and a.datedeb between to_date('''||to_char(ev.debut,'YYYYMMDD')||''',''YYYYMMDD'') and to_date('''||to_char(ev.fin,'YYYYMMDD')||''',''YYYYMMDD'')';
          s:=replace(s,'***',ev.transformation); 
          OCTNG_TRT.logsql(Widtrt,ev.abs_cod,s);
        end if;
     end if;
     
    end loop;
  
--=====================================
-- Insertion des ev pour les absences non SS
-- ATTENTION, ici il faudrait également boucler sur 202 et 203
    if (ev.cpt is not null) then
      --tester ev not null et cpt not null    
      s:='insert into ev(typev,mat,rc,codepaie,datev,qte)';
      s:=s||' select ''abs'', a.mat,a.rc,''######'',r.cal_dat,($$$)***';
      s:=s||' from abs a,'||Wcptres||' r';
      s:=s||' where r.pers_mat=a.mat and r.cal_dat between a.datedeb and a.datefin';
      s:=s||' and a.abs_cod='''||ev.abs_cod||''' and a.statut='''||ev.statut||'''';
      s:=s||' and $$$<>0';
      s:=s||' and a.datedeb between to_date('''||to_char(ev.debut,'YYYYMMDD')||''',''YYYYMMDD'') and to_date('''||to_char(ev.fin,'YYYYMMDD')||''',''YYYYMMDD'')';
      if (isNumeric(ev.cpt)=1) then
        if (ev.cpt='207') then
          s:=replace(s,'$$$','get_abs_val(r.cal_val201,'''||ev.abs_cod||''',r.cal_val207)+get_abs_val(r.cal_val202,'''||ev.abs_cod||''',r.cal_val208)+get_abs_val(r.cal_val203,'''||ev.abs_cod||''',r.cal_val209)');
        elsif (ev.cpt='204') then -- ajout en heure
          s:=replace(s,'$$$','get_abs_val(r.cal_val201,'''||ev.abs_cod||''',r.cal_val204)+get_abs_val(r.cal_val202,'''||ev.abs_cod||''',r.cal_val205)+get_abs_val(r.cal_val203,'''||ev.abs_cod||''',r.cal_val206)');
        else
          s:=replace(s,'$$$','r.cal_val'||ev.cpt);
        end if;
      else
        s:=replace(s,'$$$',ev.cpt);
      end if;
      s:=replace(s,'***',ev.transformation); 
      
      if  (ev.ev_r is not null) then
        s1:=replace(s,'######',ev.ev_r);
        OCTNG_TRT.logsql(Widtrt,ev.abs_cod,s1);
      end if;
      
      if  (ev.ev_p is not null) then
        s2:=replace(s,'######',ev.ev_p);
        OCTNG_TRT.logsql(Widtrt,ev.abs_cod,s2);
      end if;
    end if;
  end loop;
end;


procedure traite_nonSS_JFER(
  Widtrt      octng_traitement.idtrt%type
) as
WnbU  Integer;
WnbD  Integer;
begin
  -- Regroupe les absences non SS séparées par une JFER
  -- p1.rc=p2.rc est garanti par p1.rc=p3.rc
    loop
    update abs p1
    set (datefin,codedemijr)=(
            select distinct p3.datefin
            , case
            when p1.codedemijr='1' and p3.codedemijr='1' then '1'
            when p1.codedemijr='2' and p3.codedemijr='1' then '2'
            when p1.codedemijr='1' and p3.codedemijr='3' then '3'
            when p1.codedemijr='2' and p3.codedemijr='3' then '4'
            end
            from sdv.saiabs p2,abs p3 where p1.mat=p2.pers_mat and p2.abs_cod='JFER'
                                                          and  p1.datefin+1=p2.abs_dat and p2.abs_fin+1=p3.datedeb
                                                          and  p1.mat=p3.mat and p1.rc=p3.rc and p1.abs_cod=p3.abs_cod
                                                          and p1.codedemijr in ('1','2') and p3.codedemijr in ('1','3'))
    -- pour prendre que le premier
    where not exists(select null from sdv.saiabs p2,abs p3 where p1.mat=p2.pers_mat and p2.abs_cod='JFER'
                                                          and  p3.datefin+1=p2.abs_dat and p2.abs_fin+1=p1.datedeb
                                                          and  p1.mat=p3.mat and p1.rc=p3.rc and p1.abs_cod=p3.abs_cod
                                                          and p3.codedemijr in ('1','2') and p1.codedemijr in ('1','3'))
    and     exists(select null from sdv.saiabs p2,abs p3  where p1.mat=p2.pers_mat and p2.abs_cod='JFER'
                                                          and  p1.datefin+1=p2.abs_dat and p2.abs_fin+1=p3.datedeb
                                                          and  p1.mat=p3.mat and p1.rc=p3.rc and p1.abs_cod=p3.abs_cod
                                                          and p1.codedemijr in ('1','2') and p3.codedemijr in ('1','3'))
    and exists(select null from ref_type_abs rta where p1.structure=rta.structure and rta.absenceSS='N')
    ;
    WnbU:=sql%rowcount;
    delete abs p2
    where exists(select null from abs p1 where p1.mat=p2.mat and p1.rc=p2.rc and p1.datedeb<p2.datedeb and p1.datefin=p2.datefin and p1.abs_cod=p2.abs_cod
                                                          and p1.codedemijr||p2.codedemijr in ('11','21','33','43'))
    and exists(select null from ref_type_abs rta where p2.structure=rta.structure and rta.absenceSS='N')
    ;
    WnbD:=sql%rowcount;
    if WnbU<>wnbD then
      OCTNG_TRT.raise_erreur(Widtrt,'OCTNG_ABS.traite JFER','NbU<>Nbd');
    end if;
    exit when WnbD=0;
    OCTNG_TRT.logger(Widtrt,'traite',WnbD||' absences non SS séparées par JFER regroupées',8);
  end loop;
end;

-- Jours non travaillés
procedure traite_nonSS_JNT(
  Widtrt      octng_traitement.idtrt%type
) as
WnbU  Integer;
WnbD  Integer;
begin  
  --raise_application_error(-20001,'debug');
  -- p1.rc=p2.rc est garanti par p1.rc=p3.rc
    loop
    update abs p1
    set (datefin,codedemijr)=(
    -- A FAIRE : Enlever le min qui fait planter sur log nord
            select p3.datefin
            , case
            when p1.codedemijr='1' and p3.codedemijr='1' then '1'
            when p1.codedemijr='2' and p3.codedemijr='1' then '2'
            when p1.codedemijr='1' and p3.codedemijr='3' then '3'
            when p1.codedemijr='2' and p3.codedemijr='3' then '4'
            end
            from abs p3
                                        where p1.mat=p3.mat and p1.rc=p3.rc and p1.abs_cod=p3.abs_cod and p1.datefin<p3.datedeb
                                        and p1.codedemijr in ('1','2') and p3.codedemijr in ('1','3')
                                        and not exists(select null from sdv.cptres1 p2 
                                        where p1.mat=p2.pers_mat and p2.hor_theo not in (51,0)/*and p2.cal_val3<>0*/ 
                                        and p2.cal_dat between p1.datefin+1 and p3.datedeb-1))
    -- pour prendre que le premier
    where not exists(select null from abs p3
                                        where p1.mat=p3.mat and p1.rc=p3.rc and p1.abs_cod=p3.abs_cod and p3.datefin<p1.datedeb
                                        and p3.codedemijr in ('1','2') and p1.codedemijr in ('1','3')
                                        and not exists(select null from sdv.cptres1 p2 
                                        where p1.mat=p2.pers_mat and p2.hor_theo not in (51,0)/* and p2.cal_val3<>0 */
                                        and p2.cal_dat between p3.datefin+1 and p1.datedeb-1))
    and     exists(select null from abs p3
                                        where p1.mat=p3.mat and p1.rc=p3.rc and p1.abs_cod=p3.abs_cod and p1.datefin<p3.datedeb
                                        and p1.codedemijr in ('1','2') and p3.codedemijr in ('1','3')
                                        and not exists(select null from sdv.cptres1 p2 
                                        where p1.mat=p2.pers_mat and p2.hor_theo not in (51,0)/* and p2.cal_val3<>0 */
                                        and p2.cal_dat between p1.datefin+1 and p3.datedeb-1))
    and exists(select null from ref_type_abs rta where p1.structure=rta.structure and rta.absenceSS='N')
    ;
    WnbU:=sql%rowcount;
    delete abs p2
    where exists(select null from abs p1
                                        where p1.mat=p2.mat and p1.rc=p2.rc and p1.datedeb<p2.datedeb and p1.datefin=p2.datefin 
                                        and p1.abs_cod=p2.abs_cod
                                        and p1.codedemijr||p2.codedemijr in ('11','21','33','43')
                                        and p1.motif=p2.motif)
    and exists(select null from ref_type_abs rta where p2.structure=rta.structure and rta.absenceSS='N')
    ;
    WnbD:=sql%rowcount;
    if WnbU<>wnbD then
      OCTNG_TRT.raise_erreur(Widtrt,'OCTNG_ABS.traite JNT','NbU<>Nbd');
    end if;
    exit when WnbD=0;
    OCTNG_TRT.logger(Widtrt,'traite',WnbD||' absences non SS séparées par jours non travaillés regroupées',8);
  end loop;
end;

--==============================================================================
procedure traite_nonSS_XCP(
    Widtrt      octng_traitement.idtrt%type
,  Wabs_cod     varchar2
,  Wcpt         varchar2
) is
  Wcptres   varchar2(32);
  s         octng_sql.sqlw%type;
WnbI  Integer;
WnbU  Integer;
begin
  Wcptres:='OCTIME_CPTRES1_'||Widtrt;

  s:='insert into abs(statut,abs_cod,mat,rc,datecontrat,structure,motif,datedeb,datefin,prolongation,anneemois,codedemijr)';
  -- Attention, ici on met MA dans abs_cod (ra.abs_cod) et non MA00 !!!!
  s:=s||' SELECT a.statut,ra.abs_cod,a.mat,a.rc,a.datecontrat,ra.structure,ra.motif,r.cal_dat,a.datefin,null,null,1'; -- SDVP
  s:=s||' from abs a,ref_abs ra, ref_type_abs rta,'||Wcptres||' r';
  s:=s||' where ';
     -- selection des tables de ref
  s:=s||' a.abs_cod=''XCP'' and '''||Wabs_cod||'''=ra.abs_cod and a.statut=ra.statut';
  s:=s||' and ra.structure=rta.structure';
  s:=s||' and a.mat=r.pers_mat and r.cal_dat between a.datedeb and a.datefin';
  s:=s||' and r.cal_val'||Wcpt||'<>0';
  -- On ajoute seulement si on est en début d'absence ou si le cpt=0 le jour précédent
  s:=s||' and (r.cal_dat=a.datedeb';
  s:=s||' or exists(select null from '||Wcptres||' r2 where r2.pers_mat=r.pers_mat and r2.cal_dat+1=r.cal_dat and r2.cal_val'||Wcpt||'=0))';
  WnbI:=OCTNG_TRT.logsql_nb(Widtrt,'CP -> '||Wabs_cod,s);


  -- On met à jour la date de fin avec la plus petite date-1 ou la valeur du compteur=0
  s:='update abs a set datefin=nvl((';
  s:=s||' select min(r.cal_dat)-1';
  s:=s||' from  '||Wcptres||' r';
  s:=s||' where r.pers_mat=a.mat and r.cal_dat between a.datedeb and a.datefin';
  s:=s||' and r.cal_val'||Wcpt||'=0';
  s:=s||' group by r.pers_mat';
  s:=s||' ),a.datefin)';
  s:=s||' where a.abs_cod='''||Wabs_cod||'''';
  OCTNG_TRT.logsql(Widtrt,'CP -> '||Wabs_cod,s); 
end;
--==============================================================================
procedure regroupe_nonSS_XCP(
  Widtrt        octng_traitement.idtrt%type
,  Wabs_cod     varchar2
,  Wcpt         varchar2
) is
  Wcptres   varchar2(32);
  s         octng_sql.sqlw%type;
WnbI  Integer;
WnbU  Integer;
begin
 --     OCTNG_TRT.raise_erreur(Widtrt,'traite_nonSS_paie','NbU<>Nbd');
  Wcptres:='OCTIME_CPTRES1_'||Widtrt;
  Wcptres:='sdv.cptres1';
  -- On tire la date de debut vers le debut de XCP
  s:='update abs a';
  s:=s||' set a.datedeb=';
  s:=s||' nvl((select  XCP.datedeb';
  s:=s||' from abs XCP';
  s:=s||' where XCP.mat=a.mat and XCP.rc=a.rc and XCP.abs_cod=''XCP''';
  s:=s||' and a.datedeb between XCP.datedeb and XCP.datefin';
  s:=s||' and a.datefin between XCP.datedeb and XCP.datefin';
  -- il n'exists pas d'autre cp entre le debut de XCP et le début de la période en cours
  s:=s||' and not exists(select null from abs a2 where a2.abs_cod in (''CPR'',''CP+1'',''CSUP'') and a2.mat=a.mat and a2.rc=a.rc and a2.datedeb between XCP.datedeb and a.datedeb and a.rowid<>a2.rowid)';
  -- il n'existe pas de jour où cptCP<>0  entre le debut de XCP et le début de la période en cours
  s:=s||' and not exists(select null from '||Wcptres||' r where r.pers_mat=a.mat and r.cal_dat between XCP.datedeb and a.datedeb and r.cal_val'||Wcpt||'<>0)';
  s:=s||'   ),a.datedeb)';
  s:=s||' where a.abs_cod in (''CPR'',''CP+1'',''CSUP'')';
  OCTNG_TRT.logsql(Widtrt,'CP -> '||Wabs_cod,s); 

  -- On tire la date de fin vers la fin de XCP
  s:='update abs a';
  s:=s||' set a.datefin=';
  s:=s||' nvl((select  XCP.datefin';
  s:=s||' from abs XCP';
  s:=s||' where XCP.mat=a.mat and XCP.rc=a.rc and XCP.abs_cod=''XCP''';
  s:=s||' and a.datedeb between XCP.datedeb and XCP.datefin';
  s:=s||' and a.datefin between XCP.datedeb and XCP.datefin';
  s:=s||' and not exists(select null from abs a2 where a2.abs_cod in (''CPR'',''CP+1'',''CSUP'') and a2.mat=a.mat and a2.rc=a.rc and a2.datedeb between a.datedeb and XCP.datefin and a.rowid<>a2.rowid)';
  -- il n'existe pas de jour où cptCP<>0  entre le debut de XCP et le début de la période en cours
  s:=s||' and not exists(select null from '||Wcptres||' r where r.pers_mat=a.mat and r.cal_dat between a.datedeb and XCP.datefin and r.cal_val'||Wcpt||'<>0)';
  s:=s||'   ),a.datefin)';
  s:=s||' where a.abs_cod in (''CPR'',''CP+1'',''CSUP'')';
  OCTNG_TRT.logsql(Widtrt,'CP -> '||Wabs_cod,s); 
 
    --  OCTNG_TRT.raise_erreur(Widtrt,'traite_nonSS_paie','NbU<>Nbd');
     -- On regroupe 2 période de CP s'il n'existe pas de jour où cptCP<>0
    s:='update abs a';
    s:=s||' set a.datefin=';
    s:=s||' nvl((select  max(a2.datefin)';
    s:=s||' from abs XCP,abs a2';
    s:=s||' where a2.mat=a.mat and a2.rc=a.rc and a2.abs_cod=a.abs_cod and a2.datedeb>a.datedeb';
    s:=s||' and XCP.mat=a.mat and XCP.rc=a.rc and XCP.abs_cod=''XCP''';
    s:=s||' and a.datedeb between XCP.datedeb and XCP.datefin';
    s:=s||' and a.datefin between XCP.datedeb and XCP.datefin';
    s:=s||' and a2.datedeb between XCP.datedeb and XCP.datefin';
    s:=s||' and a2.datefin between XCP.datedeb and XCP.datefin';
    -- il n'existe pas de jour où cptCP<>0 entre la date de fin de a et la fin de a2
    s:=s||' and not exists(select null from '||Wcptres||' r where r.pers_mat=a.mat and r.cal_dat between a.datefin and a2.datefin and r.cal_val'||Wcpt||'<>0)';
    -- il n'exists pas d'autre cp entre le debut de a et la fin de a2
    s:=s||' and not exists(select null from abs a3 where a3.abs_cod<>a.abs_cod and a3.mat=a.mat and a3.rc=a.rc and a.datedeb<a3.datedeb and  a3.datedeb<a2.datedeb)';
    s:=s||'   ),a.datefin)';
    s:=s||' where a.abs_cod in (''CPR'',''CP+1'',''CSUP'')';
    -- il n'existe pas de jour où cptCP<>0  entre le debut de XCP et le début de la période en cours
    OCTNG_TRT.logsql(Widtrt,'CP -> '||Wabs_cod,s); 
  
    -- Supprime la période réunifiée
    s:='delete abs a';
    s:=s||' where a.abs_cod in (''CPR'',''CP+1'',''CSUP'')';
    s:=s||' and exists(select null from abs a2 where a.mat=a2.mat and a.rc=a2.rc and a.abs_cod=a2.abs_cod and a.datedeb>a2.datedeb  and a.datefin=a2.datefin)';
    OCTNG_TRT.logsql(Widtrt,'CP -> '||Wabs_cod,s); 
end;
--==============================================================================
procedure traite_nonSS_CP(
    Widtrt      octng_traitement.idtrt%type
,  Wabs_cod     varchar2
,  Wcpt         varchar2
) is
  Wcptres   varchar2(32);
  s         octng_sql.sqlw%type;
WnbI  Integer;
WnbU  Integer;
begin
  Wcptres:='OCTIME_CPTRES1_'||Widtrt;

-- On met dans date de debut de CP la date de début de période XCP union les dates de fin ou le lendemain  de toutes les periodes crées
  s:='insert into abs(statut,abs_cod,mat,rc,datecontrat,structure,motif,datedeb,datefin,prolongation,anneemois,codedemijr)';
  s:=s||' SELECT XCP.statut,ra.abs_cod,XCP.mat,XCP.rc,XCP.datecontrat,ra.structure,ra.motif,XCP.datedeb,XCP.datefin,null,null,1'; -- SDVP
  s:=s||' from abs XCP,ref_abs ra';
  s:=s||' where ';
  s:=s||' XCP.abs_cod=''XCP'' and '''||Wabs_cod||'''=ra.abs_cod and XCP.statut=ra.statut';
  s:=s||' union';
  -- On ajoute la date de fin d'un 'CP+1','CSUP','CPR' si cptCPT<>0 ce jour, le lendemain sinon (si encore dans la periode XCP)
  s:=s||' SELECT a.statut,ra.abs_cod,a.mat,a.rc,a.datecontrat,ra.structure,ra.motif,decode(r.cal_val'||Wcpt||',0,a.datefin+1,a.datefin),XCP.datefin,''O'',null,1';
  s:=s||' from abs XCP,abs a,ref_abs ra,'||Wcptres||' r';
  s:=s||' where XCP.abs_cod=''XCP'' and '''||Wabs_cod||'''=ra.abs_cod and XCP.statut=ra.statut';
  s:=s||' and a.abs_cod in (''CP+1'',''CSUP'',''CPR'') and a.mat=XCP.mat and a.rc=XCP.rc';
  s:=s||' and a.datedeb between XCP.datedeb and XCP.datefin';
  s:=s||' and a.datefin between XCP.datedeb and XCP.datefin';
  s:=s||' and a.mat=r.pers_mat and r.cal_dat=a.datefin';
  s:=s||' and not exists(select null from abs a2 where a.mat=a2.mat and a.rc=a2.rc and decode(r.cal_val'||Wcpt||',0,a.datefin+1,a.datefin) between a2.datedeb and a2.datefin and a2.abs_cod in (''CP+1'',''CSUP'',''CPR''))';
  WnbI:=OCTNG_TRT.logsql_nb(Widtrt,'CP -> '||Wabs_cod,s);

  -- On met à jour la date de fin avec la plus petite date-1 ou la valeur du compteur=0
  s:='update abs CP set datefin=nvl((';
  s:=s||' select decode(r.cal_val'||Wcpt||',0,r.cal_dat-1,r.cal_dat)';
  s:=s||' from '||Wcptres||' r';
  s:=s||' where CP.mat=r.pers_mat and r.cal_dat in (';
  s:=s||'   select min(a.datedeb)';
  s:=s||'   from abs a';
  s:=s||'   where a.abs_cod in (''CP+1'',''CSUP'',''CPR'') and a.mat=CP.mat and a.rc=CP.rc';
  s:=s||'   and a.datedeb between CP.datedeb and CP.datefin';
  s:=s||'   and a.datefin between CP.datedeb and CP.datefin';
  s:=s||' )),CP.datefin)';
  s:=s||' where CP.abs_cod='''||Wabs_cod||'''';
  OCTNG_TRT.logsql(Widtrt,'CP -> '||Wabs_cod,s); 
  
  -- Permet de nettoyer les enregistrements inséré hors periode XCP
  delete abs a where a.abs_cod=Wabs_cod and a.datedeb>a.datefin and a.abs_cod=Wabs_cod;
end;

--==============================================================================
procedure traite_nonSS_paie(
  Widtrt      octng_traitement.idtrt%type
) as
WnbU  Integer;
WnbD  Integer;
begin  
  -- Sépare les absences sur rupture de période de paie
    --   OCTNG_TRT.raise_erreur(Widtrt,'traite_nonSS_paie','NbU<>Nbd');
  loop
    insert into abs(statut,abs_cod,mat,rc,datecontrat,structure,motif,datedeb,datefin,codedemijr,prolongation,anneemois)
    SELECT a.statut,a.abs_cod,a.mat,a.rc,a.datecontrat,a.structure,a.motif
    ,decode(a.statut,'P',to_date(to_char(last_day(a.datedeb-20)+1,'YYYYMM')||'21','YYYYMMDD'),last_day(a.datedeb)+1)
    ,a.datefin,
        case a.codedemijr -- on prend en compte la fin
    when '1' then '1' -- début et fin journée ==> journée
    when '2' then '1' -- début am, fin journée ==> journée
    when '3' then '3' -- début journée, fin matinée ==> matinée
    when '4' then '3' -- début am, fin matinée ==> matinée
    end
    ,a.prolongation,a.anneemois
    from abs a,ref_type_abs rta
    where (a.statut='P' and to_char(a.datedeb-20,'YYYYMM')<>to_char(a.datefin-20,'YYYYMM')
    or    a.statut='A' and to_char(a.datedeb,'YYYYMM')<>to_char(a.datefin,'YYYYMM'))
    and a.structure=rta.structure and rta.absenceSS='N'
    ;
    WnbU:=sql%rowcount;
    update abs a
    set datefin=decode(a.statut,'P',to_date(to_char(last_day(a.datedeb-20)+1,'YYYYMM')||'20','YYYYMMDD'),last_day(a.datedeb)),
    codedemijr=case a.codedemijr -- on prend en compte le début
    when '1' then '1' -- début et fin journée ==> journée
    when '2' then '2' -- début am, fin journée ==> am
    when '3' then '1' -- début journée, fin matinée ==> journée
    when '4' then '2' -- début am, fin matinée ==> am
    end    
    where exists(select null from abs p2 where a.mat=p2.mat and a.rc=p2.rc and a.datedeb<p2.datedeb and a.datefin=p2.datefin 
          and a.abs_cod=p2.abs_cod and a.codedemijr||p2.codedemijr in ('11','21','33','43'))
    and exists(select null from ref_type_abs rta where a.structure=rta.structure and rta.absenceSS='N')
    ;
    WnbD:=sql%rowcount;
    if WnbU<>wnbD then
      OCTNG_TRT.raise_erreur(Widtrt,'OCTNG_ABS.traite_nonSS_paie','NbU<>Nbd');
    end if;
    exit when WnbD=0;
    OCTNG_TRT.logger(Widtrt,'traite',SQL%ROWCOUNT||' absences non SS découpée par période de paie',8);
  end loop;
end;

--==============================================================================
procedure traite_nonSS(
  Widtrt      octng_traitement.idtrt%type
,	Wmois       octng_traitement.anneemois%type
) as
begin

  delete abs a  
  where a.structure in (select rta.structure from ref_type_abs rta where rta.absenceSS='N') 
  and a.datedeb>(select max(p.fretro) from pers p where a.mat=p.mat and a.rc=p.rc group by p.mat,p.rc);
  OCTNG_TRT.logger(Widtrt,'traite_absence',SQL%ROWCOUNT||' absences non SS supprimées commençant après la fin du mois de paie',8);
  
  update abs a 
  set datefin=(select max(fretro) from pers p where a.mat=p.mat and a.rc=p.rc group by p.mat,p.rc)
  , codedemijr=case a.codedemijr -- on prend en compte le début
    when '1' then '1' -- début et fin journée ==> journée
    when '2' then '2' -- début am, fin journée ==> am
    when '3' then '1' -- début journée, fin matinée ==> journée
    when '4' then '2' -- début am, fin matinée ==> am
    end      
  where a.structure in (select rta.structure from ref_type_abs rta where rta.absenceSS='N')
  and a.datefin>(select max(fretro) from pers p where a.mat=p.mat and a.rc=p.rc group by p.mat,p.rc);
  OCTNG_TRT.logger(Widtrt,'traite_absence',SQL%ROWCOUNT||' date de fin d''absences non SS repositionnées à la fin du mois de paie',8);

  traite_nonSS_JFER(Widtrt);  
  traite_nonSS_JNT(Widtrt); 

  -- Laisser le redécoupage en période de paie à la fin !!!
  traite_nonSS_paie(Widtrt);  

  -- L'idée est que toute la période saisie soie remplie par des CP pour pouvoir permettre le regroupement
  -- ==> il faut donc traiter les CP en dernier pour tout remplir
  update abs set abs_cod='XCP' where abs_cod='CP';
  traite_nonSS_XCP(Widtrt,'CPR','161');
  traite_nonSS_XCP(Widtrt,'CSUP','266');
  traite_nonSS_XCP(Widtrt,'CP+1','260');
  regroupe_nonSS_XCP(Widtrt,'CP','262');
  traite_nonSS_CP(Widtrt,'CP','262');
  delete abs where abs_cod='XCP';

--On est en début de période, on s'occupe de la date de début
--  update abs a set (codedemijr)=(select decode(min(codedemijr),3,1,4,2,a.codedemijr) from abs a2 where a.mat=a2.mat and a.rc=a2.rc and a.datedeb=a2.datedeb and a2.abs_cod='XCP')
  update abs a set (codedemijr)=(select min(codedemijr) from abs a2 where a.mat=a2.mat and a.rc=a2.rc and a.datedeb=a2.datedeb and a2.abs_cod='XCP')
   where a.abs_cod in ('CPR','CP','CP+1','CSUP')
   and exists(select null from abs a2 where a.mat=a2.mat and a.rc=a2.rc and a.datedeb=a2.datedeb and a2.abs_cod='XCP')
  ;
--On n'est pas en début période, On s'occupe de la date de début (on peut la déplacer vers du matin vers midi, on ne touche pas au soir)
   update abs a set codedemijr=decode(codedemijr,1,2,3,4,codedemijr) -- il existe un autre enregistrement qui fini le matin
   where a.abs_cod in ('CPR','CP','CP+1','CSUP')
   and not exists(select null from abs a2 where a.mat=a2.mat and a.rc=a2.rc and a.datedeb=a2.datedeb and a2.abs_cod='XCP')
   and exists(select null from abs a2 where a.mat=a2.mat and a.rc=a2.rc 
              and a2.datefin=a.datedeb and (a2.datedeb<a.datedeb or a2.datefin<a.datefin or  (a2.datefin=a.datefin and a2.rowid<a.rowid)) 
              and a2.abs_cod in ('CPR','CP','CP+1','CSUP')
              )
  ;
 -- On n'est pas en fin de période, On s'occupe de la date de fin (on peut la déplacer du soir vers midi, on ne touche pas au matin)
  update abs a set codedemijr= decode(codedemijr,1,3,2,4,codedemijr) -- il existse un autre enregistrement qui commence l'après-midi
   where a.abs_cod in ('CPR','CP','CP+1','CSUP')
   and not exists(select null from abs a2 where a.mat=a2.mat and a.rc=a2.rc and a.datefin=a2.datefin and a2.abs_cod='XCP')
   and exists(select null from abs a2 where a.mat=a2.mat and a.rc=a2.rc 
            and a.datefin=a2.datedeb and (a.datedeb<a2.datedeb or a.datefin<a2.datefin or  (a.datefin=a2.datefin and a.rowid<a2.rowid))
              and a2.abs_cod in ('CPR','CP','CP+1','CSUP')
            )
  ;
--On est en fin de période, on s'occupe de la date de fin (on peut la déplacer du soir vers midi, on ne touche pas au matin)
  update abs a set (codedemijr)=(select decode(a.codedemijr||min(codedemijr),'14',3,'23',4,'24',4,'31',1,'32',1,'41',2,'42',2,a.codedemijr) from abs a2 where a.mat=a2.mat and a.rc=a2.rc and a.datefin=a2.datefin and a2.abs_cod='XCP')
   where a.abs_cod in ('CPR','CP','CP+1','CSUP')
   and exists(select null from abs a2 where a.mat=a2.mat and a.rc=a2.rc and a.datefin=a2.datefin and a2.abs_cod='XCP')
  ;
--On n'est pas en ?? période, On s'occupe de la date de fin (on peut la déplacer vers le jour suivant)
/*  update abs a 
  set datefin=(select 
   where a.abs_cod in ('CPR','CP','CP+1','CSUP')
   -- plus petite date de début d'un periode XCP-1 si codedemijr=1,?  , date de début sinon > datefin
   and not exists(select null from abs a2 where a.mat=a2.mat and a.rc=a2.rc and a.datefin=a2.datefin and a2.abs_cod='XCP')
   and exists(select null from abs a2 where a.mat=a2.mat and a.rc=a2.rc 
            and a.datefin=a2.datedeb and (a.datedeb<a2.datedeb or a.datefin<a2.datefin or  (a.datefin=a2.datefin and a.rowid<a2.rowid))
              and a2.abs_cod in ('CPR','CP','CP+1','CSUP')
            )
  and a.codedemijr in ('1','?')
  ;*/

/* update abs a set (codedemijr)=(select 
   where a.abs_cod in ('CPR','CP','CP+1','CSUP')
   and exists(select null from abs a2 where p.mat=a2.mat and p.rc=a2.rc and (a.datedeb=a2.datedeb or a.datefin=a2.datefin) and a2.abs_cod='XCP')
  ;*/
  
end;

--==============================================================================
procedure regroupe_demijournee(
  Widtrt      octng_traitement.idtrt%type
,	Wmois       octng_traitement.anneemois%type
) as
WnbU  Integer;
WnbD  Integer;
begin
   -- Regroupe les absences 2x1/2 journées sur une journée
  loop
    update abs p1
    set (datefin,codedemijr)=(
            select p2.datefin
            , case
            when p1.codedemijr='3' and p2.codedemijr='2' then '1' -- début  journée, fin journée
            when p1.codedemijr='3' and p2.codedemijr='4' then '3' -- début journée, fin matin
            when p1.codedemijr='4' and p2.codedemijr='2' then '2' -- début am, fin journée
            when p1.codedemijr='4' and p2.codedemijr='4' then '4' -- début am, fin journée
            end
            from abs p2 where p1.mat=p2.mat and p1.rc=p2.rc 
 --           and (p1.abs_cod=p2.abs_cod and p2.prolongation='O' or p1.motif='FOR' and p2.motif='FOR')
            and p1.abs_cod=p2.abs_cod and p2.prolongation='O'
            -- fin 1/2 journée ou journée suivie de début 1/2 journée ou journée
             and p1.datefin=p2.datedeb and p1.codedemijr||p2.codedemijr in ('32','34','42','44')
            )
    where   exists(select null from abs p2 where p1.mat=p2.mat and p1.rc=p2.rc 
            and p1.abs_cod=p2.abs_cod and p2.prolongation='O'
             and p1.datefin=p2.datedeb and p1.codedemijr||p2.codedemijr in ('32','34','42','44')
            )
    ;
    WnbU:=sql%rowcount;
    delete abs p2
    where exists(select null from abs p1 where p1.mat=p2.mat and p1.rc=p2.rc and p1.datefin=p2.datefin 
            and p1.abs_cod=p2.abs_cod
             and p1.datefin=p2.datedeb and p1.codedemijr||p2.codedemijr in ('12','34','22','44') 
             and p1.rowid<>p2.rowid -- pour éviter de prendre 2 fois le même enregistrement à cause des code 22 et 44
            )
     and p2.prolongation='O'
    ;
    WnbD:=sql%rowcount;
    if WnbU<>wnbD then
      OCTNG_TRT.raise_erreur(Widtrt,'OCTNG_ABS.regroupe_demijournee','NbU<>Nbd ('||WnbU||'<>'||WnbD||')');
    end if;
    exit when WnbD=0;
    OCTNG_TRT.logger(Widtrt,'traite',WnbD||' absences consécutives regroupées 1/2 journée',8);
  end loop; 
end;

--==============================================================================
procedure regroupe_journee(
  Widtrt      octng_traitement.idtrt%type
,	Wmois       octng_traitement.anneemois%type
) as
WnbU  Integer;
WnbD  Integer;
begin
--raise_application_error(-20001,'debug');
  -- Regroupe les absences consécutives
  loop
    update abs p1
    set (datefin,codedemijr)=(
            select p2.datefin
            , case
            when p1.codedemijr='1' and p2.codedemijr='1' then '1'
            when p1.codedemijr='2' and p2.codedemijr='1' then '2'
            when p1.codedemijr='1' and p2.codedemijr='3' then '3'
            when p1.codedemijr='2' and p2.codedemijr='3' then '4'
            end
            from abs p2 where p1.mat=p2.mat and p1.rc=p2.rc 
            and p1.abs_cod=p2.abs_cod and p2.prolongation='O'
            -- fin 1/2 journée ou journée suivie de début 1/2 journée ou journée
             and p1.datefin+1=p2.datedeb and p1.codedemijr||p2.codedemijr in ('11','21','13','23')
            )
    where   exists(select null from abs p2 where p1.mat=p2.mat and p1.rc=p2.rc 
            and p1.abs_cod=p2.abs_cod and p2.prolongation='O'
             and p1.datefin+1=p2.datedeb and p1.codedemijr||p2.codedemijr in ('11','21','13','23')
            )
    -- pour prendre que le premier
    and not exists(select null from abs p2 where p1.mat=p2.mat and p1.rc=p2.rc 
            and p1.abs_cod=p2.abs_cod and p1.prolongation='O'
             and p2.datefin+1=p1.datedeb and  p2.codedemijr||p1.codedemijr in ('11','21','13','23')
            )
--    and p1.prolongation='O'
    ;
    WnbU:=sql%rowcount;
    delete abs p2
    where exists(select null from abs p1 where p1.mat=p2.mat and p1.rc=p2.rc and p1.datefin=p2.datefin 
            and p1.abs_cod=p2.abs_cod 
             and p1.datedeb<p2.datedeb and p1.codedemijr||p2.codedemijr in ('11','21','33','43')
            )
    and p2.prolongation='O'
    ;
    WnbD:=sql%rowcount;
    if WnbU<>wnbD then
      OCTNG_TRT.raise_erreur(Widtrt,'OCTNG_ABS.regroupe_journee','NbU<>NbD ('||WnbU||'<>'||WnbD||')');
    end if;
    exit when WnbD=0;
    OCTNG_TRT.logger(Widtrt,'traite',WnbD||' absences consécutives regroupées journée',8);
  end loop;
end;

--==============================================================================
procedure regroupe_demijournee_motif(
  Widtrt      octng_traitement.idtrt%type
,	Wmois       octng_traitement.anneemois%type
) as
WnbU  Integer;
WnbD  Integer;
begin
   -- Regroupe les absences 2x1/2 journées sur une journée
  loop
    update abs p1
    set (datefin,codedemijr,NBJRSTOT,NBJRSTX1,NBJRSTX2,NBJRSTX3,NBJRSTX4,NBJRSTX5,JRSIJTX1,JRSIJTX2,JRSIJTX3,JRSIJTX4)=(
            select p2.datefin
            , case
            when p1.codedemijr='3' and p2.codedemijr='2' then '1' -- début  journée, fin journée
            when p1.codedemijr='3' and p2.codedemijr='4' then '3' -- début journée, fin matin
            when p1.codedemijr='4' and p2.codedemijr='2' then '2' -- début am, fin journée
            when p1.codedemijr='4' and p2.codedemijr='4' then '4' -- début am, fin journée
            end
            , p1.NBJRSTOT+p2.NBJRSTOT
            , p1.NBJRSTX1+p2.NBJRSTX1
            , p1.NBJRSTX2+p2.NBJRSTX2
            , p1.NBJRSTX3+p2.NBJRSTX3
            , p1.NBJRSTX4+p2.NBJRSTX4
            , p1.NBJRSTX5+p2.NBJRSTX5
            , p1.JRSIJTX1+p2.JRSIJTX1
            , p1.JRSIJTX2+p2.JRSIJTX2
            , p1.JRSIJTX3+p2.JRSIJTX3
            , p1.JRSIJTX4+p2.JRSIJTX4
            from abs p2 where p1.mat=p2.mat and p1.rc=p2.rc 
            and p1.motif=p2.motif and p2.motif in ('FOR')
            -- fin 1/2 journée ou journée suivie de début 1/2 journée ou journée
             and p1.datefin=p2.datedeb and p1.codedemijr||p2.codedemijr in ('32','34','42','44')
            )
    where   exists(select null from abs p2 where p1.mat=p2.mat and p1.rc=p2.rc 
            and p1.motif=p2.motif and p2.motif in ('FOR')
             and p1.datefin=p2.datedeb and p1.codedemijr||p2.codedemijr in ('32','34','42','44')
            )
    ;
    WnbU:=sql%rowcount;
    delete abs p2
    where exists(select null from abs p1 where p1.mat=p2.mat and p1.rc=p2.rc and p1.datefin=p2.datefin 
             and p1.motif=p2.motif and p2.motif in ('FOR')
             and p1.datefin=p2.datedeb and p1.codedemijr||p2.codedemijr in ('12','34','22','44') 
             and p1.rowid<>p2.rowid -- pour éviter de prendre 2 fois le même enregistrement à cause des code 22 et 44
            )
    ;
    WnbD:=sql%rowcount;
    if WnbU<>wnbD then
      OCTNG_TRT.raise_erreur(Widtrt,'OCTNG_ABS.traite consécutif 1/2 journée','NbU<>Nbd ('||WnbU||'<>'||WnbD||')');
    end if;
    exit when WnbD=0;
    OCTNG_TRT.logger(Widtrt,'traite',WnbD||' absences consécutives regroupées 1/2 journée',8);
  end loop; 
end;
--==============================================================================
procedure regroupe_journee_motif(
  Widtrt      octng_traitement.idtrt%type
,	Wmois       octng_traitement.anneemois%type
) as
WnbU  Integer;
WnbD  Integer;
begin
--raise_application_error(-20001,'debug');
  -- Regroupe les absences consécutives
  loop
    update abs p1
    set (datefin,codedemijr,NBJRSTOT,NBJRSTX1,NBJRSTX2,NBJRSTX3,NBJRSTX4,NBJRSTX5,JRSIJTX1,JRSIJTX2,JRSIJTX3,JRSIJTX4)=(
            select p2.datefin
            , case
            when p1.codedemijr='1' and p2.codedemijr='1' then '1'
            when p1.codedemijr='2' and p2.codedemijr='1' then '2'
            when p1.codedemijr='1' and p2.codedemijr='3' then '3'
            when p1.codedemijr='2' and p2.codedemijr='3' then '4'
            end
            , p1.NBJRSTOT+p2.NBJRSTOT
            , p1.NBJRSTX1+p2.NBJRSTX1
            , p1.NBJRSTX2+p2.NBJRSTX2
            , p1.NBJRSTX3+p2.NBJRSTX3
            , p1.NBJRSTX4+p2.NBJRSTX4
            , p1.NBJRSTX5+p2.NBJRSTX5
            , p1.JRSIJTX1+p2.JRSIJTX1
            , p1.JRSIJTX2+p2.JRSIJTX2
            , p1.JRSIJTX3+p2.JRSIJTX3
            , p1.JRSIJTX4+p2.JRSIJTX4
            from abs p2 where p1.mat=p2.mat and p1.rc=p2.rc 
            and p1.motif=p2.motif and p2.motif in ('FOR')
            -- fin 1/2 journée ou journée suivie de début 1/2 journée ou journée
             and p1.datefin+1=p2.datedeb and p1.codedemijr||p2.codedemijr in ('11','21','13','23')
            )
    where   exists(select null from abs p2 where p1.mat=p2.mat and p1.rc=p2.rc 
            and p1.motif=p2.motif and p2.motif in ('FOR')
             and p1.datefin+1=p2.datedeb and p1.codedemijr||p2.codedemijr in ('11','21','13','23')
            )
    -- pour prendre que le premier
    and not exists(select null from abs p2 where p1.mat=p2.mat and p1.rc=p2.rc 
            and p1.motif=p2.motif and p2.motif in ('FOR')
             and p2.datefin+1=p1.datedeb and  p2.codedemijr||p1.codedemijr in ('11','21','13','23')
            )
    ;
    WnbU:=sql%rowcount;
    delete abs p2
    where exists(select null from abs p1 where p1.mat=p2.mat and p1.rc=p2.rc and p1.datefin=p2.datefin 
            and p1.motif=p2.motif and p2.motif in ('FOR')
             and p1.datedeb<p2.datedeb and p1.codedemijr||p2.codedemijr in ('11','21','33','43')
            )
    ;
    WnbD:=sql%rowcount;
    if WnbU<>wnbD then
      OCTNG_TRT.raise_erreur(Widtrt,'OCTNG_ABS.traite consécutif journée','NbU<>NbD ('||WnbU||'<>'||WnbD||')');
    end if;
    exit when WnbD=0;
    OCTNG_TRT.logger(Widtrt,'traite',WnbD||' absences consécutives regroupées journée',8);
  end loop;
end;

--==============================================================================
procedure decoupe_999(
  Widtrt      octng_traitement.idtrt%type
,	Wmois       octng_traitement.anneemois%type
) as
WnbU  Integer;
WnbD  Integer;
begin
  -- découpe les absences de plus de 999 jours
  loop
    insert into abs(statut,abs_cod,mat,rc,datecontrat,structure,motif,datedeb,datefin,codedemijr,prolongation,anneemois)
    SELECT a.statut,a.abs_cod,a.mat,a.rc,a.datecontrat,a.structure,a.motif
    ,a.datedeb+999,a.datefin,
    case a.codedemijr -- on prend en compte la fin
    when '1' then '1' -- début et fin journée ==> journée
    when '2' then '1' -- début am, fin journée ==> journée
    when '3' then '3' -- début journée, fin matinée ==> matinée
    when '4' then '3' -- début am, fin matinée ==> matinée
    end
    ,'P',a.anneemois
    from abs a
    where a.datefin-a.datedeb>999;
    WnbU:=sql%rowcount;
    
    update abs a
    set datefin=datedeb+998,
    codedemijr=case a.codedemijr -- on prend en compte le début
    when '1' then '1' -- début et fin journée ==> journée
    when '2' then '2' -- début am, fin journée ==> am
    when '3' then '1' -- début journée, fin matinée ==> journée
    when '4' then '2' -- début am, fin matinée ==> am
    end
    where a.datefin-a.datedeb>999;
    WnbD:=sql%rowcount;
    
    if WnbU<>wnbD then
      OCTNG_TRT.raise_erreur(Widtrt,'OCTNG_ABS.traite période','NbU<>Nbd');
    end if;
    exit when WnbD=0;
    OCTNG_TRT.logger(Widtrt,'traite',SQL%ROWCOUNT||' absences découpée par période de 999 jours',8);
  end loop; 
end;

--==============================================================================
procedure traite(
  Widtrt      octng_traitement.idtrt%type
,	Wmois       octng_traitement.anneemois%type
, WabsenceSS  char
) as
WnbU  Integer;
WnbD  Integer;
begin
  regroupe_demijournee(Widtrt,Wmois);
  regroupe_journee(Widtrt,Wmois);
   
  decoupe_999(Widtrt,Wmois);
  
  if (WabsenceSS='N') then
    traite_nonSS(Widtrt,Wmois);
  end if;

  -- Supprime les codes prolongation
  update abs set prolongation=null;
end;

--==============================================================================
procedure historise(
  Widtrt      octng_traitement.idtrt%type
) is
begin
  OCTNG_TRT.logger(Widtrt,'historise','Historisation des absences');
  
  insert into hst_abs_erreur 
        select distinct Widtrt,a.*,'Rc=Null' from abs a where rc is null
  union select distinct Widtrt,a.*,'datecontrat=Null' from abs a where datecontrat is null
  union select distinct Widtrt,a.*,'structure=Null' from abs a where structure is null
  union select distinct Widtrt,a.*,'motif=Null' from abs a where motif is null
  union select distinct Widtrt,a.*,'datedeb=Null' from abs a where datedeb is null
  union select distinct Widtrt,a.*,'codedemijr=Null' from abs a where codedemijr is null
  union select distinct Widtrt,a.*,'codedemijr <> (1,2,3,4)' from abs a where codedemijr not in ('1','2','3','4')
  union select distinct Widtrt,a.*,'nbjrstot=Null' from abs a where nbjrstot is null
  union select distinct Widtrt,a.*,'nbjrstot=0' from abs a where nbjrstot=0
  union select distinct Widtrt,a.*,'Doublon' from abs a where (mat,rc,datecontrat,structure,motif,datedeb,codedemijr)  in  (select mat,rc,datecontrat,structure,motif,datedeb,codedemijr from abs group by mat, rc, datecontrat, structure, motif, datedeb,codedemijr having count(*)>1)
  ;
  OCTNG_TRT.logger(Widtrt,'historise',SQL%ROWCOUNT||' absences rejetées',6);
/*  if SQL%ROWCOUNT>0 then
    OCTNG_TRT.raise_erreur(Widtrt,'abs','hst');
  end if;
*/  
  delete abs a where rc is null;
  delete abs a where datecontrat is null;
  delete abs a where structure is null;
  delete abs a where motif is null;
  delete abs a where datedeb is null;
  delete abs a where codedemijr is null;
  delete abs a where codedemijr not in ('1','2','3','4');
  delete abs a where nbjrstot is null;
  delete abs a where nbjrstot=0;
  delete abs a where (mat,rc,datecontrat,structure,motif,datedeb,codedemijr)  in  (select mat,rc,datecontrat,structure,motif,datedeb,codedemijr from abs group by mat, rc, datecontrat, structure, motif, datedeb,codedemijr having count(*)>1);
  
  insert into hst_abs
  select distinct Widtrt,a.*
  from abs a;
  OCTNG_TRT.logger(Widtrt,'historise',SQL%ROWCOUNT||' absences historisées',6);
end;


--==============================================================================
-- Procédures publiques
--==============================================================================
PROCEDURE init_pers_select(
    Widtrt			octng_traitement.idtrt%type
,   Wdatetrt		octng_traitement.datetrt%type
) IS
Wdatetrt_last			octng_traitement.datetrt%type;
begin
  execute immediate 'truncate table pers_select';
  execute immediate 'truncate table abs_pers_select_retro';

  -- Récupère la date et heure de dernière éxécution
  begin
    select max(datetrt) into Wdatetrt_last 
    from octng_traitement 
    where idtrt<Widtrt and typetrt='aut' and module='I' and etat in ('T') and statut in ('S','V')
    ;
  exception
  when others then 
    Wdatetrt_last:=systimestamp();
    execute immediate 'delete Octime_VARPREV';
  end;
  OCTNG_TRT.logger(Widtrt,'init_pers_select','Extration des absences SS entre le '||to_char(Wdatetrt_last,'DD/MM/YYYY HH24:MI:SS')||' et le '||to_char(Wdatetrt,'DD/MM/YYYY HH24:MI:SS'));
  
  execute immediate 'delete Octime_VARPREVSAV';
  insert into Octime_VARPREVSAV select * from Octime_VARPREV;
  execute immediate 'delete Octime_VARPREV';
  insert into Octime_VARPREV  select Widtrt,v.* from sdv.VARPREV v;

  insert into abs_pers_select_retro
  -- ici on ne connait pas ra.statut, on met donc un distinct
  select distinct s.pers_mat,decode(s.flag,'M',least(s.abs_dat,s.aabs_dat),s.abs_dat),to_date('31/12/2099','DD/MM/YYYY')
  from SDV.saiabssav s,ref_abs ra,ref_type_abs rta
  where to_char(s.ps_jou,'YYYYMMDD')||s.ps_heu>to_char(Wdatetrt_last,'YYYYMMDDHH24MISSFF')
  and   to_char(s.ps_jou,'YYYYMMDD')||s.ps_heu<=to_char(Wdatetrt,'YYYYMMDDHH24MISSFF')
  and   get_abs_cod(s.abs_cod)=ra.abs_cod and ra.structure=rta.structure and rta.absenceSS='O'
  -- On prend toutes les absences entre les dates de la table de ref
  and decode(s.flag,'M',least(s.abs_fin,s.aabs_fin),s.abs_fin)>=ra.debut and decode(s.flag,'M',least(s.abs_dat,s.aabs_dat),s.abs_dat)<=ra.fin
  union
  -- Ceci est vrai si les calculs sont relancés !!!
  -- Ici, on tient compte également des absences supprimées pour déterminer
  -- la date de retro
  select pers_mat,least(cont_datd,acont_datd),to_date('31/12/2099','DD/MM/YYYY')
  from SDV.contprevsav
  where to_char(ps_jou,'YYYYMMDD')||ps_heu>to_char(Wdatetrt_last,'YYYYMMDDHH24MISSFF')
  and   to_char(ps_jou,'YYYYMMDD')||ps_heu<=to_char(Wdatetrt,'YYYYMMDDHH24MISSFF')
  union
  -- un nouveau
  select pers_mat,var_dat,to_date('31/12/2099','DD/MM/YYYY')
  from Octime_VARPREV
  where (pers_mat,var_dat,par_cod) not in (select pers_mat,var_dat,par_cod from Octime_VARPREVSAV)
  union
  -- un supprimé
  select pers_mat,var_dat,to_date('31/12/2099','DD/MM/YYYY')
  from Octime_VARPREVSAV
  where (pers_mat,var_dat,par_cod) not in (select pers_mat,var_dat,par_cod from Octime_VARPREV)
  union
  -- un modifié
  select v.pers_mat,least(v.var_datf,vs.var_datf),to_date('31/12/2099','DD/MM/YYYY')
  from Octime_VARPREV v,Octime_VARPREVSAV vs
  where v.pers_mat=vs.pers_mat and v.var_dat=vs.var_dat and v.par_cod=vs.par_cod and v.var_datf<>vs.var_datf
  ;

  insert into pers_select(mat,dretro,fretro)
  select mat,min(dretro),max(fretro)
  from abs_pers_select_retro
  group by mat;
  OCTNG_TRT.logger(Widtrt,'init_pers_select',SQL%ROWCOUNT||' individus sélectionnés',6);
end;


--==============================================================================
procedure init(
  Widtrt      octng_traitement.idtrt%type
,	Wmois       octng_traitement.anneemois%type
, WabsenceSS  char
) as
begin
  execute immediate 'truncate table abs';
 -- raise_application_error(-20001,'Debug');

  insert into abs(statut,abs_cod,mat,rc,datecontrat,structure,motif,datedeb,datefin,codedemijr,prolongation,anneemois)
  -- Attention, ici on met MA dans abs_cod (ra.abs_cod) et non MA00 !!!!
  SELECT p.statut,ra.abs_cod,p.mat,p.rc,p.relatdatedeb,ra.structure,ra.motif,greatest(s.abs_dat,p.debut,ra.debut),least(s.abs_fin,p.fin,ra.fin)
      ,case when s.abs_typ='J' then '1' 
            -- heure de début d'absence = heure de début de journée
            when s.abs_typ='F' and s.abs_dh=(select nvl(min(l2.hor_deb),min(l.hor_deb)) 
                        from sdv.horprev h,sdv.cyclig c,sdv.cjourlig l ,sdv.hcarte h2,sdv.cjourlig l2,sdv.saiabs s2,ref_abs ra2
                        where s.pers_mat=h.pers_mat and s.abs_dat between h.hor_dat and h.hor_datf
                        and h.cyc_cod=c.cyc_cod and c.hor_cod=l.hor_cod and c.cyc_num=to_char(s.abs_dat,'D') 
                        and h.pers_mat=h2.pers_mat(+) and s.abs_dat=h2.hc_date(+) and h2.hor_cod=l2.hor_cod(+)
                        and l.hor_pla not in ('P','I') and (l2.hor_pla not in ('P','I') or l2.hor_pla is null)
                        -- Il existe une absence corespondant à l'horaire exceptionnel qui existe dans ref_absence (on ne prend pas les DEL)
                        and s.abs_dat=s2.abs_dat(+) and l2.hor_deb=s2.abs_dh(+) and (h2.hc_date is  null or s2.abs_cod=ra2.abs_cod)
                        )                        
             then '3' 
            -- heure de fin d'absence = heure de fin de journée
            when s.abs_typ='F' and s.abs_fh=(select nvl(max(l2.hor_fin),max(l.hor_fin))
                        from sdv.horprev h,sdv.cyclig c,sdv.cjourlig l ,sdv.hcarte h2,sdv.cjourlig l2,sdv.saiabs s2,ref_abs ra2
                        where s.pers_mat=h.pers_mat and s.abs_dat between h.hor_dat and h.hor_datf
                        and h.cyc_cod=c.cyc_cod and c.hor_cod=l.hor_cod and c.cyc_num=to_char(s.abs_dat,'D') 
                        and h.pers_mat=h2.pers_mat(+) and s.abs_dat=h2.hc_date(+) and h2.hor_cod=l2.hor_cod(+)
                        and l.hor_pla not in ('P','I') and (l2.hor_pla not in ('P','I') or l2.hor_pla is null)
                        -- Il existe une absence corespondant à l'horaire exceptionnel qui existe dans ref_absence (on ne prend pas les DEL)
                        and s.abs_dat=s2.abs_dat(+) and l2.hor_deb=s2.abs_dh(+) and (h2.hc_date is  null or s2.abs_cod=ra2.abs_cod)
                        )                        
            then '2' 
            else '0' end
      ,case when rta.absenceSS='O' and exists(select null from sdv.saiabs s2 where s.pers_mat=s2.pers_mat and s.abs_ind=s2.abs_ind and get_abs_cod(s.abs_cod)=get_abs_cod(s2.abs_cod) and s2.abs_fin<=s.abs_dat and s.rowid<>s2.rowid) then 'O'
            when rta.absenceSS='O' then s.abs_pro 
            else 'O' end
      ,case when rta.absenceSS='O' then to_char(s.abs_dat,'YYMM') 
            when ra.structure='ENGART' then to_char(s.abs_dat,'YYYY') 
            else null end
  from SDV.saiabs s,pers p,ref_abs ra, ref_type_abs rta
  where 
  -- Si rc à cheval sur 2 pèriodes, elle se retrouve dans les deux 
  s.pers_mat=p.mat and s.abs_fin>=p.debut and s.abs_dat<=p.fin
  -- On prend toutes les absences entre les dates de la table de ref
  and s.abs_fin>=ra.debut and s.abs_dat<=ra.fin
  -- On prend toutes les absences postérieure à la date de retro
  and s.abs_fin>=p.dretro and (s.abs_dat<=p.fretro or rta.absenceSS='O')
    -- selection des tables de ref
  and get_abs_cod(s.abs_cod)=ra.abs_cod and p.statut=ra.statut
  and ra.structure=rta.structure
  -- On ne prend que les absences SS si WabsenceSS='O'
  and (rta.absenceSS=WabsenceSS or WabsenceSS='N')
  ;
  OCTNG_TRT.logger(Widtrt,'select_absence',SQL%ROWCOUNT||' absences sélectionnées',6);
 
  OCTNG_TRT.logger(Widtrt,'traite','Ajout des absences consécutives antérieure à la date de rétro et les absences simultanées',7);
  loop
    insert into abs(statut,abs_cod,mat,rc,datecontrat,structure,motif,datedeb,datefin,codedemijr,prolongation,anneemois)
    SELECT p.statut,ra.abs_cod,p.mat,p.rc,p.relatdatedeb,ra.structure,ra.motif,greatest(s.abs_dat,p.debut,ra.debut),least(s.abs_fin,p.fin,ra.fin)
      ,case when s.abs_typ='J' then '1' 
            -- heure de début d'absence = heure de début de journée
            when s.abs_typ='F' and s.abs_dh=(select nvl(min(l2.hor_deb),min(l.hor_deb))
                        from sdv.horprev h,sdv.cyclig c,sdv.cjourlig l ,sdv.hcarte h2,sdv.cjourlig l2,sdv.saiabs s2,ref_abs ra2
                        where s.pers_mat=h.pers_mat and s.abs_dat between h.hor_dat and h.hor_datf
                        and h.cyc_cod=c.cyc_cod and c.hor_cod=l.hor_cod and c.cyc_num=to_char(s.abs_dat,'D') 
                        and h.pers_mat=h2.pers_mat(+) and s.abs_dat=h2.hc_date(+) and h2.hor_cod=l2.hor_cod(+)
                        and l.hor_pla not in ('P','I') and (l2.hor_pla not in ('P','I') or l2.hor_pla is null)
                        -- Il existe une absence corespondant à l'horaire exceptionnel qui existe dans ref_absence (on ne prend pas les DEL)
                        and s.abs_dat=s2.abs_dat(+) and l2.hor_deb=s2.abs_dh(+) and (h2.hc_date is  null or s2.abs_cod=ra2.abs_cod)
                        )
             then '3' 
            -- heure de fin d'absence = heure de fin de journée
            when s.abs_typ='F' and s.abs_fh=(select nvl(max(l2.hor_fin),max(l.hor_fin))
                        from sdv.horprev h,sdv.cyclig c,sdv.cjourlig l ,sdv.hcarte h2,sdv.cjourlig l2,sdv.saiabs s2,ref_abs ra2
                        where s.pers_mat=h.pers_mat and s.abs_dat between h.hor_dat and h.hor_datf
                        and h.cyc_cod=c.cyc_cod and c.hor_cod=l.hor_cod and c.cyc_num=to_char(s.abs_dat,'D') 
                        and h.pers_mat=h2.pers_mat(+) and s.abs_dat=h2.hc_date(+) and h2.hor_cod=l2.hor_cod(+)
                        and l.hor_pla not in ('P','I') and (l2.hor_pla not in ('P','I') or l2.hor_pla is null)
                        -- Il existe une absence corespondant à l'horaire exceptionnel qui existe dans ref_absence (on ne prend pas les DEL)
                        and s.abs_dat=s2.abs_dat(+) and l2.hor_deb=s2.abs_dh(+) and (h2.hc_date is  null or s2.abs_cod=ra2.abs_cod)
                        )
            then '2' 
            else '0' end
      ,case when rta.absenceSS='O' and exists(select null from sdv.saiabs s2 where s.pers_mat=s2.pers_mat and s.abs_ind=s2.abs_ind and get_abs_cod(s.abs_cod)=get_abs_cod(s2.abs_cod) and s2.abs_fin<=s.abs_dat and s.rowid<>s2.rowid) then 'O'
            when rta.absenceSS='O' then s.abs_pro 
            else 'O' end
      ,case when rta.absenceSS='O' then to_char(s.abs_dat,'YYMM') 
            when ra.structure='ENGART' then to_char(s.abs_dat,'YYYY') 
            else null end
    from SDV.saiabs s ,pers p,ref_abs ra,ref_type_abs rta,abs a
    where s.pers_mat=p.mat and p.mat=a.mat and p.rc=a.rc
    -- on prend les absences dans la rc
    and s.abs_fin>=p.debut and s.abs_dat<=p.fin
    -- On prend toutes les absences entre les dates de la table de ref
    and s.abs_fin>=ra.debut and s.abs_dat<=ra.fin
    --absences identiques consécutives
    and (s.abs_fin+1=a.datedeb and get_abs_cod(s.abs_cod)=a.abs_cod
    --absences différentes(?) simultanées
    or  s.abs_fin>=a.datedeb and s.abs_dat<=a.datefin)
    -- selection des tables de ref
    and get_abs_cod(s.abs_cod)=ra.abs_cod and p.statut=ra.statut
    and ra.structure=rta.structure --and rta.absenceSS='O'
    and (rta.absenceSS=WabsenceSS or WabsenceSS='N')
    -- Attention, on met greatest(s.abs_dat,p.debut) dans le cas d'une rupture sur rc
    and not exists(select null from abs a2 where a.mat=a2.mat and a.rc=a2.rc and greatest(s.abs_dat,p.debut,ra.debut)=a2.datedeb and ra.abs_cod=a2.abs_cod)
    ;
    exit when sql%rowcount=0
    ;
    OCTNG_TRT.logger(Widtrt,'traite',SQL%ROWCOUNT||' absences SS simultanées ou consécutives antérieurement à la date de rétro ajoutées',8);
  end loop;

  insert into abs(statut,abs_cod,mat,rc,datecontrat,structure,motif,datedeb,datefin,codedemijr,prolongation,anneemois)
  -- On ajoute les periodes exceptionnelles
  SELECT p.statut,ra.ast_cod,p.mat,p.rc,p.relatdatedeb,ra.structure,ra.motif,greatest(s.ast_datd,p.debut,ra.debut),least(s.ast_datf,p.fin,ra.fin)
      ,case -- heure de début d'absence = heure de début de journée et heure de fin d'absence = heure de fin de journée
            when s.ast_heud=(select nvl(min(l2.hor_deb),min(l.hor_deb)) 
                        from sdv.horprev h,sdv.cyclig c,sdv.cjourlig l ,sdv.hcarte h2,sdv.cjourlig l2,sdv.saiast s2,ref_ast ra2
                        where s.pers_mat=h.pers_mat and s.ast_datd between h.hor_dat and h.hor_datf
                        and h.cyc_cod=c.cyc_cod and c.hor_cod=l.hor_cod and c.cyc_num=to_char(s.ast_datd,'D') 
                        and h.pers_mat=h2.pers_mat(+) and s.ast_datd=h2.hc_date(+) and h2.hor_cod=l2.hor_cod(+)
                        and l.hor_pla not in ('P','I') and (l2.hor_pla not in ('P','I') or l2.hor_pla is null)
                        -- Il existe une absence corespondant à l'horaire exceptionnel qui existe dans ref_absence (on ne prend pas les DEL)
                        and s.ast_datd=s2.ast_datd(+) and l2.hor_deb=s2.ast_heud(+) and (h2.hc_date is  null or s2.tast_cod=ra2.ast_cod)
                        )                        
            and s.ast_heuf=(select nvl(max(l2.hor_fin),max(l.hor_fin))
                        from sdv.horprev h,sdv.cyclig c,sdv.cjourlig l ,sdv.hcarte h2,sdv.cjourlig l2,sdv.saiast s2,ref_ast ra2
                        where s.pers_mat=h.pers_mat and s.ast_datd between h.hor_dat and h.hor_datf
                        and h.cyc_cod=c.cyc_cod and c.hor_cod=l.hor_cod and c.cyc_num=to_char(s.ast_datd,'D') 
                        and h.pers_mat=h2.pers_mat(+) and s.ast_datd=h2.hc_date(+) and h2.hor_cod=l2.hor_cod(+)
                        and l.hor_pla not in ('P','I') and (l2.hor_pla not in ('P','I') or l2.hor_pla is null)
                        -- Il existe une absence corespondant à l'horaire exceptionnel qui existe dans ref_absence (on ne prend pas les DEL)
                        and s.ast_datd=s2.ast_datd(+) and l2.hor_deb=s2.ast_heud(+) and (h2.hc_date is  null or s2.tast_cod=ra2.ast_cod)
                        )                        
            then '1'
            when s.ast_heud=(select nvl(min(l2.hor_deb),min(l.hor_deb)) 
                        from sdv.horprev h,sdv.cyclig c,sdv.cjourlig l ,sdv.hcarte h2,sdv.cjourlig l2,sdv.saiast s2,ref_ast ra2
                        where s.pers_mat=h.pers_mat and s.ast_datd between h.hor_dat and h.hor_datf
                        and h.cyc_cod=c.cyc_cod and c.hor_cod=l.hor_cod and c.cyc_num=to_char(s.ast_datd,'D') 
                        and h.pers_mat=h2.pers_mat(+) and s.ast_datd=h2.hc_date(+) and h2.hor_cod=l2.hor_cod(+)
                        and l.hor_pla not in ('P','I') and (l2.hor_pla not in ('P','I') or l2.hor_pla is null)
                        -- Il existe une absence corespondant à l'horaire exceptionnel qui existe dans ref_absence (on ne prend pas les DEL)
                        and s.ast_datd=s2.ast_datd(+) and l2.hor_deb=s2.ast_heud(+) and (h2.hc_date is  null or s2.tast_cod=ra2.ast_cod)
                        )                        
             then '3' 
            -- heure de fin d'absence = heure de fin de journée
            when s.ast_heuf=(select nvl(max(l2.hor_fin),max(l.hor_fin))
                        from sdv.horprev h,sdv.cyclig c,sdv.cjourlig l ,sdv.hcarte h2,sdv.cjourlig l2,sdv.saiast s2,ref_ast ra2
                        where s.pers_mat=h.pers_mat and s.ast_datd between h.hor_dat and h.hor_datf
                        and h.cyc_cod=c.cyc_cod and c.hor_cod=l.hor_cod and c.cyc_num=to_char(s.ast_datd,'D') 
                        and h.pers_mat=h2.pers_mat(+) and s.ast_datd=h2.hc_date(+) and h2.hor_cod=l2.hor_cod(+)
                        and l.hor_pla not in ('P','I') and (l2.hor_pla not in ('P','I') or l2.hor_pla is null)
                        -- Il existe une absence corespondant à l'horaire exceptionnel qui existe dans ref_absence (on ne prend pas les DEL)
                        and s.ast_datd=s2.ast_datd(+) and l2.hor_deb=s2.ast_heud(+) and (h2.hc_date is  null or s2.tast_cod=ra2.ast_cod)
                        )                        
            then '2' 
            else '0' end
      ,'O'
      ,to_char(s.ast_datd,'YYMM')
  from SDV.saiast s,pers p,ref_ast ra, ref_type_abs rta
  where 
  -- Si rc à cheval sur 2 pèriodes, elle se retrouve dans les deux 
  s.pers_mat=p.mat and s.ast_datf>=p.debut and s.ast_datd<=p.fin
  -- On prend toutes les absences entre les dates de la table de ref
  and s.ast_datf>=ra.debut and s.ast_datd<=ra.fin
  -- On prend toutes les absences postérieure à la date de retro
  and s.ast_datf>=p.dretro and (s.ast_datd<=p.fretro or rta.absenceSS='O')
    -- selection des tables de ref
  and s.tast_cod=ra.ast_cod and p.statut=ra.statut
  and ra.structure=rta.structure
  -- On ne prend que les absences SS si 'O'='O'
  and (rta.absenceSS='O' or WabsenceSS='N')
  ;
 
  
  update pers  p set (dretroc,dretroabs,fretroabs)=
      (select least(p2.dretro,nvl(min(a.datedeb),to_date('31/12/2099','DD/MM/YYYY')))
        ,     nvl(min(a.datedeb),to_date('31/12/2099','DD/MM/YYYY'))
        ,     nvl(max(a.datefin),to_date('01/01/1900','DD/MM/YYYY'))
      from pers p2,abs a--,ref_abs ra,ref_type_abs rta 
      where p2.mat=p.mat and p2.rc=p.rc and p2.debut=p.debut and p2.mat=a.mat(+)  and p2.rc=a.rc(+) 
      --and a.abs_cod=ra.abs_cod (+) and ra.structure=rta.structure (+) and 'N'=rta.absenceSS (+)
      group by p2.mat,p2.rc,p2.debut,p2.dretro
  );
end;

--==============================================================================
procedure exec(
  Widtrt        octng_traitement.idtrt%type
,	Wmois         octng_traitement.anneemois%type
, WabsenceSS    char
) as
begin  
  traite(Widtrt,Wmois,WabsenceSS);

  calcul(Widtrt,Wmois);
  erreur(Widtrt);
  
  regroupe_demijournee_motif(Widtrt,Wmois);
--  regroupe_journee_motif(Widtrt,Wmois);
  
  historise(Widtrt);
end;

--==============================================================================
procedure dif(
  Widtrt      octng_traitement.idtrt%type,
  Widtrt_org  octng_traitement.idtrt%type
) as 
begin
  execute immediate 'truncate table abs_dif';
  
  insert into abs_dif(idtrt,dif,mat,rc,datecontrat,structure,motif,datedeb,datefin,datefin_hst,CODEDEMIJR,NBJRSTOT,NBJRSTOT_hst,ANNEEMOIS,ANNEEMOIS_hst,NBJRSTX1,NBJRSTX1_hst,NBJRSTX2,NBJRSTX2_hst,NBJRSTX3,NBJRSTX3_hst,NBJRSTX4,NBJRSTX4_hst,NBJRSTX5,NBJRSTX5_hst,JRSIJTX1,JRSIJTX1_hst,JRSIJTX2,JRSIJTX2_hst,JRSIJTX3,JRSIJTX3_hst,JRSIJTX4,JRSIJTX4_hst,PROLONGATION,PROLONGATION_hst)
  select Widtrt,'C',e1.mat,e1.rc,e1.datecontrat,e1.structure,e1.motif,e1.datedeb,e1.datefin,null,e1.CODEDEMIJR,e1.NBJRSTOT,null,e1.ANNEEMOIS,null,e1.NBJRSTX1,null,e1.NBJRSTX2,null,e1.NBJRSTX3,null,e1.NBJRSTX4,null,e1.NBJRSTX5,null,e1.JRSIJTX1,null,e1.JRSIJTX2,null,e1.JRSIJTX3,null,e1.JRSIJTX4,null,e1.PROLONGATION,null 
  from hst_abs e1 where e1.idtrt=Widtrt 
  and not exists(select null from hst_abs e2 where e2.idtrt=Widtrt_org and e1.mat=e2.mat and e1.rc=e2.rc and e1.datecontrat=e2.datecontrat and e1.structure=e2.structure and e1.motif=e2.motif and e1.datedeb=e2.datedeb and e1.codedemijr=e2.codedemijr)
  union
  select Widtrt,'S',e2.mat,e2.rc,e2.datecontrat,e2.structure,e2.motif,e2.datedeb,null,e2.datefin,e2.CODEDEMIJR,null,e2.NBJRSTOT,null,e2.ANNEEMOIS,null,e2.NBJRSTX1,null,e2.NBJRSTX2,null,e2.NBJRSTX3,null,e2.NBJRSTX4,null,e2.NBJRSTX5,null,e2.JRSIJTX1,null,e2.JRSIJTX2,null,e2.JRSIJTX3,null,e2.JRSIJTX4,null,e2.PROLONGATION 
  from hst_abs e2 where e2.idtrt=Widtrt_org
  and not exists(select null from hst_abs e1 where e1.idtrt=Widtrt and e1.mat=e2.mat and e1.rc=e2.rc and e1.datecontrat=e2.datecontrat and e1.structure=e2.structure and e1.motif=e2.motif and e1.datedeb=e2.datedeb and e1.codedemijr=e2.codedemijr)
  union
  select Widtrt,'M',e1.mat,e1.rc,e1.datecontrat,e1.structure,e1.motif,e1.datedeb,e1.datefin,e2.datefin,e1.CODEDEMIJR,e1.NBJRSTOT,e2.NBJRSTOT,e1.ANNEEMOIS,e2.ANNEEMOIS,e1.NBJRSTX1,e2.NBJRSTX1,e1.NBJRSTX2,e2.NBJRSTX2,e1.NBJRSTX3,e2.NBJRSTX3,e1.NBJRSTX4,e2.NBJRSTX4,e1.NBJRSTX5,e2.NBJRSTX5,e1.JRSIJTX1,e2.JRSIJTX1,e1.JRSIJTX2,e2.JRSIJTX2,e1.JRSIJTX3,e2.JRSIJTX3,e1.JRSIJTX4,e2.JRSIJTX4,e1.PROLONGATION,e2.PROLONGATION 
  from hst_abs e1, hst_abs e2 where e1.idtrt=Widtrt and e2.idtrt=Widtrt_org
  and e1.mat=e2.mat and e1.rc=e2.rc and e1.datecontrat=e2.datecontrat and e1.structure=e2.structure and e1.motif=e2.motif and e1.datedeb=e2.datedeb and e1.codedemijr=e2.codedemijr and (nvl(e1.DATEFIN,to_date('01/01/2000','DD/MM/YYYY'))<>nvl(e2.DATEFIN,to_date('01/01/2000','DD/MM/YYYY')) or nvl(e1.NBJRSTOT,0)<>nvl(e2.NBJRSTOT,0) or nvl(e1.ANNEEMOIS,' ')<>nvl(e2.ANNEEMOIS,' ') or nvl(e1.NBJRSTX1,0)<>nvl(e2.NBJRSTX1,0) or nvl(e1.NBJRSTX2,0)<>nvl(e2.NBJRSTX2,0) or nvl(e1.NBJRSTX3,0)<>nvl(e2.NBJRSTX3,0) or nvl(e1.NBJRSTX4,0)<>nvl(e2.NBJRSTX4,0) or nvl(e1.NBJRSTX5,0)<>nvl(e2.NBJRSTX5,0) or nvl(e1.JRSIJTX1,0)<>nvl(e2.JRSIJTX1,0) or nvl(e1.JRSIJTX2,0)<>nvl(e2.JRSIJTX2,0) or nvl(e1.JRSIJTX3,0)<>nvl(e2.JRSIJTX3,0) or nvl(e1.JRSIJTX4,0)<>nvl(e2.JRSIJTX4,0) or nvl(e1.PROLONGATION,' ')<>nvl(e2.PROLONGATION,' '))
  union
  select Widtrt,'=',e1.mat,e1.rc,e1.datecontrat,e1.structure,e1.motif,e1.datedeb,e1.datefin,e2.datefin,e1.CODEDEMIJR,e1.NBJRSTOT,e2.NBJRSTOT,e1.ANNEEMOIS,e2.ANNEEMOIS,e1.NBJRSTX1,e2.NBJRSTX1,e1.NBJRSTX2,e2.NBJRSTX2,e1.NBJRSTX3,e2.NBJRSTX3,e1.NBJRSTX4,e2.NBJRSTX4,e1.NBJRSTX5,e2.NBJRSTX5,e1.JRSIJTX1,e2.JRSIJTX1,e1.JRSIJTX2,e2.JRSIJTX2,e1.JRSIJTX3,e2.JRSIJTX3,e1.JRSIJTX4,e2.JRSIJTX4,e1.PROLONGATION,e2.PROLONGATION 
  from hst_abs e1, hst_abs e2 where e1.idtrt=Widtrt  and e2.idtrt=Widtrt_org
  and e1.mat=e2.mat and e1.rc=e2.rc and e1.datecontrat=e2.datecontrat and e1.structure=e2.structure and e1.motif=e2.motif and e1.datedeb=e2.datedeb and nvl(e1.DATEFIN,to_date('01/01/2000','DD/MM/YYYY'))=nvl(e2.DATEFIN,to_date('01/01/2000','DD/MM/YYYY')) and nvl(e1.CODEDEMIJR,' ')=nvl(e2.CODEDEMIJR,' ') and nvl(e1.NBJRSTOT,0)=nvl(e2.NBJRSTOT,0) and nvl(e1.ANNEEMOIS,' ')=nvl(e2.ANNEEMOIS,' ') and nvl(e1.NBJRSTX1,0)=nvl(e2.NBJRSTX1,0) and nvl(e1.NBJRSTX2,0)=nvl(e2.NBJRSTX2,0) and nvl(e1.NBJRSTX3,0)=nvl(e2.NBJRSTX3,0) and nvl(e1.NBJRSTX4,0)=nvl(e2.NBJRSTX4,0) and nvl(e1.NBJRSTX5,0)=nvl(e2.NBJRSTX5,0) and nvl(e1.JRSIJTX1,0)=nvl(e2.JRSIJTX1,0) and nvl(e1.JRSIJTX2,0)=nvl(e2.JRSIJTX2,0) and nvl(e1.JRSIJTX3,0)=nvl(e2.JRSIJTX3,0) and nvl(e1.JRSIJTX4,0)=nvl(e2.JRSIJTX4,0) and nvl(e1.PROLONGATION,' ')=nvl(e2.PROLONGATION,' ')
  ;
  
  --select d.* from hst_abs_dif d where d.diff<>' ' order by d.mat,d.datev,d.codepaie;
end;

--==============================================================================
procedure dif_NG(
  Widtrt      octng_traitement.idtrt%type,
  WabsenceSS    char
) as 
begin
  execute immediate 'truncate table abs_dif';
  execute immediate 'truncate table abs_ng';
 
if (WabsenceSS='N') then 
OCTNG_TRT.logger(Widtrt,'dif_NG','PNG_ENGANP',9);
  insert into abs_ng(mat,rc,datecontrat,structure,motif,datedeb,datefin,codedemijr,nbjrstot,nbjrstx1,nbjrstx2)
  select hp.mat,hp.rc,hp.relatdatedeb,'ENGANP',n.motif,e.dateeffet,e.datefin,e.codedemijr,e.nbjrsabs,e.nbjrstx1,e.nbjrstx2
  from ENGANP@PNG e, TA_MOTIFEVT@PNG n, hst_pers hp
--  from PNG_ENGANP e, PNG_TA_MOTIFEVT n, hst_pers hp
  where e.motifevt=n.oid
  and hp.idtrt=Widtrt and hp.ctroid=e.contrat and e.dateeffet between hp.dretroc and hp.fretro -- remplacer par  and e.dateeffet >= hp.dretroc ???
  ;
--  raise_application_error(-20001,'debug');
OCTNG_TRT.logger(Widtrt,'dif_NG','PNG_ENGACP',9);
  insert into abs_ng(mat,rc,datecontrat,structure,motif,datedeb,datefin,codedemijr,nbjrstot,nbjrstx1,nbjrstx2)
  select hp.mat,hp.rc,hp.relatdatedeb,'ENGACP',n.motif,e.dateeffet,e.datefin,e.codedemijr,e.nbjrsabs,e.nbjrsindem,e.nbjrsnonindem
  from ENGACP@PNG e,TA_MOTIFEVT@PNG n, hst_pers hp
--  from PNG_ENGACP e,PNG_TA_MOTIFEVT n, hst_pers hp
  where  e.xmotifevt=n.oid
  and hp.idtrt=Widtrt and hp.ctroid=e.contrat and e.dateeffet between hp.dretroc and hp.fretro -- remplacer par  and e.dateeffet >= hp.dretroc ???
  ;
OCTNG_TRT.logger(Widtrt,'dif_NG','PNG_ENGAUP',9);
  insert into abs_ng(mat,rc,datecontrat,structure,motif,datedeb,datefin,codedemijr,nbjrstot,nbjrstx1,nbjrstx2)
  select hp.mat,hp.rc,hp.relatdatedeb,'ENGAUP',n.motif,e.dateeffet,e.datefin,e.codedemijr,e.nbjrsabs,e.nbjrstx1,e.nbjrstx2
  from ENGAUP@PNG e,TA_MOTIFEVT@PNG n, hst_pers hp
--  from PNG_ENGAUP e,PNG_TA_MOTIFEVT n, hst_pers hp
  where e.motifevt=n.oid
  and hp.idtrt=Widtrt and hp.ctroid=e.contrat and e.dateeffet between hp.dretroc and hp.fretro -- remplacer par  and e.dateeffet >= hp.dretroc ???
  ;
OCTNG_TRT.logger(Widtrt,'dif_NG','PNG_ENGART',9);
  insert into abs_ng(mat,rc,datecontrat,structure,motif,datedeb,datefin,codedemijr,nbjrstot,anneemois)
  select hp.mat,hp.rc,hp.relatdatedeb,'ENGART',n.motif,e.dateeffet,e.datefin,e.codedemijr,e.nbjrsabs,anneeref
  from ENGART@PNG e,TA_MOTIFEVT@PNG n, hst_pers hp
--  from PNG_ENGART e,PNG_TA_MOTIFEVT n, hst_pers hp
  where e.motifevt=n.oid
  and hp.idtrt=Widtrt and hp.ctroid=e.contrat and e.dateeffet between hp.dretroc and hp.fretro -- remplacer par  and e.dateeffet >= hp.dretroc ???
  ;
end if;
OCTNG_TRT.logger(Widtrt,'dif_NG','PNG_ENGMAT',9);
  insert into abs_ng(mat,rc,datecontrat,structure,motif,datedeb,datefin,codedemijr,nbjrstot,nbjrstx1,nbjrstx2,nbjrstx3,nbjrstx4,jrsijtx1,jrsijtx2,jrsijtx3,jrsijtx4,anneemois,prolongation)
  select hp.mat,hp.rc,hp.relatdatedeb,'ENGMAT',n.motif,e.dateeffet,e.datefin,e.codedemijr,e.nbjrscalen,e.nbjrsmaintenu,e.nbjrsnonmainten,e.nbjrstx3,e.nbjrstx4,e.jrsijtx1,e.jrsijtx2,e.jrsijtx3,e.jrsijtx4,e.anneemois,e.prolongation
  from ENGMAT@PNG e,TA_MOTIFEVT@PNG n, hst_pers hp
--  from PNG_ENGMAT e,PNG_TA_MOTIFEVT n, hst_pers hp
  where e.motifevt=n.oid
  and hp.idtrt=Widtrt and hp.ctroid=e.contrat and e.dateeffet >= hp.dretroc
  ;
OCTNG_TRT.logger(Widtrt,'dif_NG','PNG_ENGACT',9);
  insert into abs_ng(mat,rc,datecontrat,structure,motif,datedeb,datefin,codedemijr,nbjrstot,nbjrstx1,nbjrstx2,nbjrstx3,nbjrstx4,nbjrstx5,jrsijtx1,jrsijtx2,jrsijtx3,jrsijtx4,anneemois,prolongation)
  select hp.mat,hp.rc,hp.relatdatedeb,'ENGACT',n.motif,e.dateeffet,e.datefin,e.codedemijr,e.nbjrscalen,e.nbjrstx1,e.nbjrstx2,e.nbjrstx3,e.nbjrstx4,e.nbjrstx5,e.jrsijtx1,e.jrsijtx2,e.jrsijtx3,e.jrsijtx4,e.anneemois,e.prolongation
  from ENGACT@PNG e, TA_MOTIFEVT@PNG n, hst_pers hp
--  from PNG_ENGACT e, PNG_TA_MOTIFEVT n, hst_pers hp
  where  e.motifevt=n.oid
  and hp.idtrt=Widtrt and hp.ctroid=e.contrat  and e.dateeffet >= hp.dretroc
  ;
OCTNG_TRT.logger(Widtrt,'dif_NG','PNG_ENGAMA',9);
  insert into abs_ng(mat,rc,datecontrat,structure,motif,datedeb,datefin,codedemijr,nbjrstot,nbjrstx1,nbjrstx2,nbjrstx3,nbjrstx4,nbjrstx5,jrsijtx1,jrsijtx2,jrsijtx3,jrsijtx4,anneemois,prolongation)
  select hp.mat,hp.rc,hp.relatdatedeb,'ENGAMA',n.motif,e.dateeffet,e.datefin,e.codedemijr,e.nbjrscalen,e.nbjrstx1,e.nbjrstx2,e.nbjrstx3,e.nbjrstx4,e.nbjrstx5,e.jrsijtx1,e.jrsijtx2,e.jrsijtx3,e.jrsijtx4,e.anneemois,e.prolongation
  from ENGAMA@PNG e,TA_MOTIFEVT@PNG n, hst_pers hp
--  from PNG_ENGAMA e,PNG_TA_MOTIFEVT n, hst_pers hp
  where e.motifevt=n.oid
  and hp.idtrt=Widtrt and hp.ctroid=e.contrat  and e.dateeffet >= hp.dretroc
  ;
  
OCTNG_TRT.logger(Widtrt,'dif_NG','abs_dif',9);
  insert into abs_dif(idtrt,dif,mat,rc,datecontrat,structure,motif,datedeb,datefin,datefin_hst,CODEDEMIJR,NBJRSTOT,NBJRSTOT_hst,ANNEEMOIS,ANNEEMOIS_hst,NBJRSTX1,NBJRSTX1_hst,NBJRSTX2,NBJRSTX2_hst,NBJRSTX3,NBJRSTX3_hst,NBJRSTX4,NBJRSTX4_hst,NBJRSTX5,NBJRSTX5_hst,JRSIJTX1,JRSIJTX1_hst,JRSIJTX2,JRSIJTX2_hst,JRSIJTX3,JRSIJTX3_hst,JRSIJTX4,JRSIJTX4_hst,PROLONGATION,PROLONGATION_hst)
  select Widtrt,'C',e1.mat,e1.rc,e1.datecontrat,e1.structure,e1.motif,e1.datedeb,e1.datefin,null,e1.CODEDEMIJR,e1.NBJRSTOT,null,e1.ANNEEMOIS,null,e1.NBJRSTX1,null,e1.NBJRSTX2,null,e1.NBJRSTX3,null,e1.NBJRSTX4,null,e1.NBJRSTX5,null,e1.JRSIJTX1,null,e1.JRSIJTX2,null,e1.JRSIJTX3,null,e1.JRSIJTX4,null,e1.PROLONGATION,null 
  from hst_abs e1 where e1.idtrt=Widtrt 
  and not exists(select null from abs_ng e2 where e1.mat=e2.mat and e1.rc=e2.rc and e1.datecontrat=e2.datecontrat and e1.structure=e2.structure and e1.motif=e2.motif and e1.datedeb=e2.datedeb and e1.codedemijr=e2.codedemijr)
  union
  select Widtrt,'S',e2.mat,e2.rc,e2.datecontrat,e2.structure,e2.motif,e2.datedeb,null,e2.datefin,e2.CODEDEMIJR,null,e2.NBJRSTOT,null,e2.ANNEEMOIS,null,e2.NBJRSTX1,null,e2.NBJRSTX2,null,e2.NBJRSTX3,null,e2.NBJRSTX4,null,e2.NBJRSTX5,null,e2.JRSIJTX1,null,e2.JRSIJTX2,null,e2.JRSIJTX3,null,e2.JRSIJTX4,null,e2.PROLONGATION 
  from abs_ng e2 where not exists(select null from hst_abs e1 where e1.idtrt=Widtrt and e1.mat=e2.mat and e1.rc=e2.rc and e1.datecontrat=e2.datecontrat and e1.structure=e2.structure and e1.motif=e2.motif and e1.datedeb=e2.datedeb and e1.codedemijr=e2.codedemijr)
  union
  select Widtrt,'M',e1.mat,e1.rc,e1.datecontrat,e1.structure,e1.motif,e1.datedeb,e1.datefin,e2.datefin,e1.CODEDEMIJR,e1.NBJRSTOT,e2.NBJRSTOT,e1.ANNEEMOIS,e2.ANNEEMOIS,e1.NBJRSTX1,e2.NBJRSTX1,e1.NBJRSTX2,e2.NBJRSTX2,e1.NBJRSTX3,e2.NBJRSTX3,e1.NBJRSTX4,e2.NBJRSTX4,e1.NBJRSTX5,e2.NBJRSTX5,e1.JRSIJTX1,e2.JRSIJTX1,e1.JRSIJTX2,e2.JRSIJTX2,e1.JRSIJTX3,e2.JRSIJTX3,e1.JRSIJTX4,e2.JRSIJTX4,e1.PROLONGATION,e2.PROLONGATION 
  from hst_abs e1, abs_ng e2 where e1.idtrt=Widtrt
  and e1.mat=e2.mat and e1.rc=e2.rc and e1.datecontrat=e2.datecontrat and e1.structure=e2.structure and e1.motif=e2.motif and e1.datedeb=e2.datedeb and e1.codedemijr=e2.codedemijr and (nvl(e1.DATEFIN,to_date('01/01/2000','DD/MM/YYYY'))<>nvl(e2.DATEFIN,to_date('01/01/2000','DD/MM/YYYY')) or nvl(e1.NBJRSTOT,0)<>nvl(e2.NBJRSTOT,0) or nvl(e1.ANNEEMOIS,' ')<>nvl(e2.ANNEEMOIS,' ') or nvl(e1.NBJRSTX1,0)<>nvl(e2.NBJRSTX1,0) or nvl(e1.NBJRSTX2,0)<>nvl(e2.NBJRSTX2,0) or nvl(e1.NBJRSTX3,0)<>nvl(e2.NBJRSTX3,0) or nvl(e1.NBJRSTX4,0)<>nvl(e2.NBJRSTX4,0) or nvl(e1.NBJRSTX5,0)<>nvl(e2.NBJRSTX5,0) or nvl(e1.JRSIJTX1,0)<>nvl(e2.JRSIJTX1,0) or nvl(e1.JRSIJTX2,0)<>nvl(e2.JRSIJTX2,0) or nvl(e1.JRSIJTX3,0)<>nvl(e2.JRSIJTX3,0) or nvl(e1.JRSIJTX4,0)<>nvl(e2.JRSIJTX4,0) or nvl(e1.PROLONGATION,' ')<>nvl(e2.PROLONGATION,' '))
  union
  select Widtrt,'=',e1.mat,e1.rc,e1.datecontrat,e1.structure,e1.motif,e1.datedeb,e1.datefin,e2.datefin,e1.CODEDEMIJR,e1.NBJRSTOT,e2.NBJRSTOT,e1.ANNEEMOIS,e2.ANNEEMOIS,e1.NBJRSTX1,e2.NBJRSTX1,e1.NBJRSTX2,e2.NBJRSTX2,e1.NBJRSTX3,e2.NBJRSTX3,e1.NBJRSTX4,e2.NBJRSTX4,e1.NBJRSTX5,e2.NBJRSTX5,e1.JRSIJTX1,e2.JRSIJTX1,e1.JRSIJTX2,e2.JRSIJTX2,e1.JRSIJTX3,e2.JRSIJTX3,e1.JRSIJTX4,e2.JRSIJTX4,e1.PROLONGATION,e2.PROLONGATION 
  from hst_abs e1, abs_ng e2 where e1.idtrt=Widtrt
  and e1.mat=e2.mat and e1.rc=e2.rc and e1.datecontrat=e2.datecontrat and e1.structure=e2.structure and e1.motif=e2.motif and e1.datedeb=e2.datedeb and nvl(e1.DATEFIN,to_date('01/01/2000','DD/MM/YYYY'))=nvl(e2.DATEFIN,to_date('01/01/2000','DD/MM/YYYY')) and nvl(e1.CODEDEMIJR,' ')=nvl(e2.CODEDEMIJR,' ') and nvl(e1.NBJRSTOT,0)=nvl(e2.NBJRSTOT,0) and nvl(e1.ANNEEMOIS,' ')=nvl(e2.ANNEEMOIS,' ') and nvl(e1.NBJRSTX1,0)=nvl(e2.NBJRSTX1,0) and nvl(e1.NBJRSTX2,0)=nvl(e2.NBJRSTX2,0) and nvl(e1.NBJRSTX3,0)=nvl(e2.NBJRSTX3,0) and nvl(e1.NBJRSTX4,0)=nvl(e2.NBJRSTX4,0) and nvl(e1.NBJRSTX5,0)=nvl(e2.NBJRSTX5,0) and nvl(e1.JRSIJTX1,0)=nvl(e2.JRSIJTX1,0) and nvl(e1.JRSIJTX2,0)=nvl(e2.JRSIJTX2,0) and nvl(e1.JRSIJTX3,0)=nvl(e2.JRSIJTX3,0) and nvl(e1.JRSIJTX4,0)=nvl(e2.JRSIJTX4,0) and nvl(e1.PROLONGATION,' ')=nvl(e2.PROLONGATION,' ')
  ;
  
  --select d.* from hst_abs_dif d where d.diff<>' ' order by d.mat,d.datev,d.codepaie;
end;


end;

/
--------------------------------------------------------
--  DDL for Package Body OCTNG_EV
--------------------------------------------------------

  CREATE OR REPLACE PACKAGE BODY "OCTNG_EV" AS

--==============================================================================
-- Procédures privées
--==============================================================================
procedure erreur(
  Widtrt octng_traitement.idtrt%type
) as
begin
null;
  --OCTNG_TRT.logger(Widtrt,'erreur','Log des erreurs bloquantes');
  -- ev_pers.statut=NULL 
end;

--==============================================================================
procedure calcul(
  Widtrt        octng_traitement.idtrt%type
,	Wmois         octng_traitement.anneemois%type
, Wcodepaie       char default NULL
) as
--cursor e is select * from ref_ev_cpt where cpt_octime is not null and (poste=Wcodepaie or Wcodepaie is null);---JL20131017
cursor e is select * from ref_ev_cpt where cpt is not null and (poste=Wcodepaie or Wcodepaie is null);---JL20131017
s  varchar2(1024);
s_select  varchar2(1024);
s_qte  varchar2(1024);
s_from  varchar2(1024);
s_where  varchar2(1024);
s_wherej  varchar2(1024);
s_group  varchar2(1024);
s_having  varchar2(1024);
Wcptres varchar2(32);   -- table cptres1 à utiliser
Wdebut varchar2(32);
begin
  Wcptres:='OCTIME_CPTRES1_'||Widtrt;
  if (Wcodepaie is not null) then
    execute immediate('truncate table ev');
    delete hst_ev where idtrt=Widtrt and codepaie=Wcodepaie;
--    Wcptres:='sdv.cptres1';
  end if;
-- A FAIRE : ne pas calculer les indvidus en erreur
-- A FAIRE : mettre à 0 les compteurs de cptres1 si avenant='O'
  --Wcptres:='sdv.cptres1';
  OCTNG_TRT.logger(Widtrt,'calcul','calcul des ev');
--=====================================
--=====================================
  for ev in e loop
    if (ev.lastav='O') then
      Wdebut:='greatest(p.debut,p.dlastav)';
    else
      Wdebut:='p.debut';
    end if;
    if ev.sql_qte is not null then
      s_qte:=ev.sql_qte;
    else
--      s_qte:='r.cal_val'||ev.cpt_octime; ---JL20131017
      s_qte:='r.cal_val'||ev.cpt;
      if ev.operation in ('S','s','t') then
        s_qte:='sum('||s_qte||')';
      end if;
    end if;
--=====================================
    if ev.transformation is not null then
      s_qte:=s_qte||ev.transformation;
    end if;
       if ev.arrondi='S' then               s_qte:='ceil('||s_qte||')';
    elsif ev.arrondi='I' then               s_qte:='floor('||s_qte||')';
    elsif ev.arrondi='i' then               s_qte:='round(('||s_qte||'-0.01)/0.5)*0.5';
    elsif ev.arrondi='s' then               s_qte:='round(('||s_qte||')/0.5)*0.5';
    elsif IsNumeric(ev.arrondi)=1 then      s_qte:='round('||s_qte||','||ev.arrondi||')';
/*    else
      OCTNG_TRT.raise_erreur()*/
    end if;
--=====================================
    if (ev.operation='V') then
      s_select:='''cpt'',r.pers_mat,p.rc,'''||ev.POSTE||''',r.cal_dat,'||s_qte;
    else
      s_select:='''cpt'',r.pers_mat,p.rc,'''||ev.POSTE||''',p.dpaie,'||s_qte;
    end if;

--=====================================
    s_from:=Wcptres||' r,ev_pers_periode p';

--=====================================
    s_where:='r.pers_mat=p.mat';
    s_where:=s_where||' and (p.statut=''P'' and p.type='''||ev.porteur||'''';
    s_where:=s_where||' or   p.statut=''A'' and p.type='''||ev.administratif||''')';
/*    if ev.retro='N' then
      s_where:=s_where||' and (p.mois='''||Wmois||''' or p.mois=to_char(relatdatefin,''YYYYMM''))';
    end if;*/
    if ev.operation='D' then -- valeur du dernier jour de la periode
      s_where:=s_where||' and r.cal_dat=p.fin';
    elsif ev.operation='O' then -- dernieres valeur non nulle de la periode
      s_where:=s_where||' and r.cal_dat=(select max(r2.cal_dat) from '||Wcptres||' r2 where r2.pers_mat=p.mat and r2.cal_dat between '||Wdebut||' and p.fin and '||s_qte||'<>0)';
    else -- toutes les valeurs de la periode
      s_where:=s_where||' and r.cal_dat between '||Wdebut||' and p.fin';
    end if;
    -- La date de paie de l'ev doit être postérieure à la date de retro
    -- La date de paie de l'ev doit être comprise entre la date de début et de fin d'application de la règle
    if (ev.operation='V') then
      s_where:=s_where||' and r.cal_dat>=p.dretroc';
      s_where:=s_where||' and r.cal_dat between to_date('''||to_char(ev.debut,'YYYYMMDD')||''',''YYYYMMDD'') and to_date('''||to_char(ev.fin,'YYYYMMDD')||''',''YYYYMMDD'')';
    else
      --s_where:=s_where||' and p.dpaie>=p.dretroc'; -- Normalement fait à la construction de la table ev_pers_periode
      s_where:=s_where||' and p.dpaie between to_date('''||to_char(ev.debut,'YYYYMMDD')||''',''YYYYMMDD'') and to_date('''||to_char(ev.fin,'YYYYMMDD')||''',''YYYYMMDD'')';
    end if;
  --  s_where:=s_where||' and p.pers_mat=''Z000006600''';
    -- L'individu doit appartenir à une certaine population
    if (ev.par_cod is not null) then
      s_from:=s_from||',SDV.varprev v';
      s_where:=s_where||' and r.pers_mat=v.pers_mat and instr('',''||'''||trim(ev.par_cod)||'''||'','','',''||v.par_cod||'','')>0'; 
      s_where:=s_where||' and r.cal_dat between v.var_dat and v.var_datf';
      -- On envoie plus en dehors de la période de la population
      s_where:=s_where||' and v.var_datf>=p.debut';
    end if;
    
--=====================================
    s_wherej:='';
    if (ev.jav<>'?') then
      if (s_wherej is not null)then
        if (ev.oplog='O')then s_wherej:=s_wherej||' or ';
        else                  s_wherej:=s_wherej||' and ';
        end if;
      end if;
      if (ev.jav='E') then    s_wherej:=s_wherej||' not '; end if;
      s_wherej:=s_wherej||'(r.cal_dat in (select c.cont_datd from SDV.contprev c where c.pers_mat=r.pers_mat and c.cont_av=''O''))';
    end if;
    if (ev.jstc<>'?') then
      if (s_wherej is not null)then
        if (ev.oplog='O')then s_wherej:=s_wherej||' or ';
        else                  s_wherej:=s_wherej||' and ';
        end if;
      end if;
      if (ev.jstc='E') then    s_wherej:=s_wherej||' not '; end if;
      s_wherej:=s_wherej||'(r.cal_dat=p.relatdatefin)';
    end if;
    if (ev.jp1<>'?') then
      if (s_wherej is not null)then
        if (ev.oplog='O')then s_wherej:=s_wherej||' or ';
        else                  s_wherej:=s_wherej||' and ';
        end if;
      end if;
      if (ev.jp1='E') then    s_wherej:=s_wherej||' not '; end if;
      s_wherej:=s_wherej||'(r.cal_dat= '||Wdebut||' )';
    end if;
    if (s_wherej is not null) then      s_where:=s_where||' and ('||s_wherej||')'; end if;

--    if (ev.avenant='E') then -- permet de ne pas prendre les valeurs répétées des droit CP et CP-1 dans le cas d'un avenant
--      s_where:=s_where||' and (r.cal_dat= '||Wdebut||' or r.cal_dat not  in (select c.cont_datd from SDV.contprev c where c.pers_mat=r.pers_mat and c.cont_av=''O''))';
--    end if;

    if (ev.sql_where is not null) then
      s_where:=s_where||' and ('||ev.sql_where||')';
    end if;
  
--=====================================
    if ev.operation='S' then -- Somme en fin de période (y compris les jours de STC)
      s_group:=' group by r.pers_mat,p.rc,'''||ev.POSTE||''',p.dpaie';
      s_having:=' having '||s_qte||'<>0';
    elsif ev.operation='s' then -- Somme seulement les jours de STC
      s_group:=' group by r.pers_mat,p.rc,'''||ev.POSTE||''',p.dpaie,p.relatdatefin';
      s_having:=' having '||s_qte||'<>0 and  p.dpaie=p.relatdatefin';
    elsif ev.operation='t' then -- Somme sauf les jours de STC
      s_group:=' group by r.pers_mat,p.rc,'''||ev.POSTE||''',p.dpaie,p.relatdatefin';
      s_having:=' having '||s_qte||'<>0 and  p.dpaie<>p.relatdatefin';
    else
      s_where:=s_where||' and '||s_qte||'<>0';
      s_group:='';
      s_having:='';
    end if;

--=====================================
    s:='SELECT '||s_select||' from '||s_from||' where '||s_where||' '||s_group||' '||s_having;
    s:='insert into ev(typev,mat,rc,codepaie,datev,qte) '|| s;
    OCTNG_TRT.logsql(Widtrt,ev.poste,s);
  end loop;
--=====================================
--=====================================
  if (Wcodepaie is not null) then
      insert into hst_ev select Widtrt,e.* from ev e;
  end if;
end;

--==============================================================================
procedure historise(
  Widtrt        octng_traitement.idtrt%type
) is
begin
  OCTNG_TRT.logger(Widtrt,'historise','Historisation des ev');
  
  insert into hst_ev_erreur 
        select distinct Widtrt,e.*,'Rc=Null' from ev e where rc is null
  union select distinct Widtrt,e.*,'codepaie=Null' from ev e where codepaie is null
  union select distinct Widtrt,e.*,'datev=Null' from ev e where datev is null
  union select distinct Widtrt,e.*,'ordre=Null' from ev e where ordre is null
  union select distinct Widtrt,e.*,'qte=0' from ev e where qte=0 
  union select distinct Widtrt,e.*,'Doublon' from ev e where (mat,rc,codepaie,datev,ordre)  in  (select mat,rc,codepaie,datev,ordre from ev group by mat,rc,codepaie,datev,ordre having count(*)>1)
  ;
  OCTNG_TRT.logger(Widtrt,'historise',SQL%rowcount||' ev rejetés',6);
/*  if SQL%ROWCOUNT>0 then
    OCTNG_TRT.raise_erreur(Widtrt,'ev','hst');
  end if;
*/  
  delete ev e where rc is null;
  delete ev e where codepaie is null;
  delete ev e where datev is null;
  delete ev e where ordre is null;
  delete ev e where qte=0;
  delete ev e where (mat,rc,codepaie,datev,ordre)  in  (select mat,rc,codepaie,datev,ordre from ev group by mat,rc,codepaie,datev,ordre having count(*)>1);
  
  insert into hst_ev
  select Widtrt,e.*
  from ev e
  ;
  OCTNG_TRT.logger(Widtrt,'historise',SQL%rowcount||' ev historisés',6);
end;

--==============================================================================
-- Procédures publiques
--==============================================================================
procedure init(
  Widtrt        octng_traitement.idtrt%type
,	Wmois         octng_traitement.anneemois%type
, WabsenceSS    char default 'N'
) as
begin
  execute immediate 'truncate table ev';
  execute immediate 'truncate table ev_pers_periode';
  if WabsenceSS='O' then
    return;
  end if;
-- Pour sélectionner, dretroc doit être avant la date de paie !!!
-- Dans tous les cas, dretroc est antérieur à relatdatefin !!! (donc on enlève le least(p.relatdatefin,xxx))
OCTNG_TRT.logger(Widtrt,'select_periode','Remplit les tables Periode',7);
  -- periode du 1 au 31 (debut decontrat, fin de contrat)
  -- utilisés pour JOURTRAVAI, HRPRESENCE, HEURPAYEES
  --               QUOJRSSLD
  --               MONTASTREI, PROPRIMTRP, TICKRESTAU
  -- daté au 31 du mois m
  -- pour les administritfs
  insert into ev_pers_PERIODE(mat,rc,statut,type,mois,relatdatedeb,relatdatefin,dretroc,debut,fin,dpaie)
  select p.mat,p.rc,p.statut,c.type,c.mois,p.debut,p.fin,p.dretroc
  ,greatest(c.debut,p.debut)
  -- date de fin de contrat <= dernier jour mois de paie en cours (période administrative pour tout le monde)
  ,least(p.fin,c.fin)
  -- date de fin de contrat <= dernier jour période de paie en cours
  ,least(p.fin,c.fin)
  from pers p,ref_periode c
  where c.fin>=p.debut and c.debut<=p.fin
  and c.mois<=Wmois and p.dretroc<=c.fin 
  -- seulement pour les administratifs
  and c.type='A' and statut='A'
  order by p.mat,c.mois;
  
  -- periode du 21 du mois m-1 au 20 du mois m
  -- utilisé pour JOURTRAVAI, HRPRESENCE, HEURPAYEES
  -- daté au 31 du mois m
  -- pout les porteurs
  insert into ev_pers_PERIODE(mat,rc,statut,type,mois,relatdatedeb,relatdatefin,dretroc,debut,fin,dpaie)
  select p.mat,p.rc,p.statut,c.type,c.mois,p.debut,p.fin,p.dretroc
  ,greatest(c.debut,p.debut)
  ,least(p.fin,c.fin)
  ,least(p.fin,c.fin)
  from pers p,ref_periode c
  where c.fin>=p.debut and c.debut<=p.fin
  and c.mois<=Wmois and p.dretroc<=c.fin 
  -- seulement pour les porteurs
  and c.type='P' and p.statut='P' 
  order by p.mat,c.mois;
  
  --periode du 1er lundi du mois m-1 au dimanche suivant le dernier lundi du mois du mois m-1
  --  utilisés pouHC, HC125, HN, HNORMALTEP, HRNUIT, HSUPTEP125, HSUPTEP150, HS125, HS150
  --                  MAJO50, MAJO75, MAJ100
  -- daté au 31 du mois m
  -- pour les administratifs 
  insert into ev_pers_PERIODE(mat,rc,statut,type,mois,relatdatedeb,relatdatefin,dretroc,debut,fin,dpaie)
  select p.mat,p.rc,p.statut,c.type,c.mois,p.debut,p.fin,p.dretroc
  ,greatest(c.debut,p.debut)
  -- date de fin de contrat <= dernier jour mois de paie en cours (période administrative pour tout le monde)
  ,case when p.fin<=last_day(to_date(c.mois||'01','YYYYMMDD')) then p.fin else least(p.fin,c.fin) end
  -- date de fin de contrat <= dernier jour période de paie en cours
  ,least(p.fin,last_day(to_date(c.mois||'01','YYYYMMDD')))
  from pers p,ref_periode c
  where last_day(to_date(c.mois||'01','YYYYMMDD'))>=p.debut and to_date(c.mois||'01','YYYYMMDD')<=p.fin
  and greatest(c.debut,p.debut)<=case when p.fin<=last_day(to_date(c.mois||'01','YYYYMMDD')) then p.fin else least(p.fin,c.fin) end
  and c.mois<=Wmois and p.dretroc<=last_day(to_date(c.mois||'01','YYYYMMDD')) 
  -- seulement pour les administratifs
  and c.type='V' and p.statut='A' 
  order by p.mat,c.mois;
  
  -- Si STC avant la fin du mois,
  -- periode du 1er janvier au dernier jour du mois m
  -- utilisés pour DRTRCN, DRTRTT, PRIRCN, PRIRTT, SLDRCN
  -- daté au 31 du mois m
  -- pour les administratifs 
  insert into ev_pers_PERIODE(mat,rc,statut,type,mois,relatdatedeb,relatdatefin,dretroc,debut,fin,dpaie)
--  select p.mat,p.statut,c.type,c.mois,greatest(c.debut,p.relatdatedeb),least(decode(p.statut,'P',to_date(c.mois||'20','YYYYMMDD'),last_day(to_date(c.mois||'01','YYYYMMDD'))),p.relatdatefin),p.relatdatedeb,p.relatdatefin,p.dretroc,least(p.relatdatefin,decode(p.statut,'P',to_date(c.mois||'20','YYYYMMDD'),last_day(to_date(c.mois||'01','YYYYMMDD'))))
  select p.mat,p.rc,p.statut,c.type,c.mois,p.debut,p.fin,p.dretroc
  -- début de période <= début de contrat
  ,greatest(c.debut,p.debut)
  -- date de fin de contrat <= dernier jour du mois en cours (période administrative pour tout le monde)
  ,least(p.fin,c.fin)
  -- date de fin de contrat <= dernier jour période de paie en cours
  ,least(p.fin,c.fin)
  from pers p,ref_periode c
  where c.fin>=p.debut and c.mois<=to_char(p.fin,'YYYYMM')
  and c.mois<=Wmois and p.dretroc<=c.fin 
  -- seulement pour les administratifs
  and c.type='1' and p.statut='A' 
  order by p.mat,c.mois;
  
  -- periode du (1er juin ou du dernier avenant) au dernier jour du mois m
  -- ==> les compteurs sont dupliqués le jour de l'avenant : sum(DRT), sum(PRI), SLD
  -- utilisée pour DRTRELIQUA, PRIRELIQUA, SLDRELIQUA, DRTCPACQ, PRICPACQ, SLDCPACQ, DRTCPENC, PRICPENC, SLDCPENC
  --          QUOJRSDUS, QUOJRSREAL
  -- daté au 31 du mois m pour les administratifs
  -- daté au 20 du mois m pour les porteurs
  -- date de stc si stc dans le mois
  insert into ev_pers_PERIODE(mat,rc,statut,type,mois,relatdatedeb,relatdatefin,dretroc,debut,fin,dpaie)
--  select p.pers_mat,p.statut,c.type,c.mois,greatest(c.debut,p.relatdatedeb),least(decode(p.statut,'P',to_date(c.mois||'20','YYYYMMDD'),last_day(to_date(c.mois||'01','YYYYMMDD'))),p.relatdatefin),p.relatdatedeb,p.relatdatefin,p.pers_dretro,least(p.relatdatefin,decode(p.statut,'P',to_date(c.mois||'20','YYYYMMDD'),last_day(to_date(c.mois||'01','YYYYMMDD'))))
  select p.mat,p.rc,p.statut,c.type,c.mois,p.debut,p.fin,p.dretroc
  -- début de période <= début de contrat
  ,greatest(c.debut,p.debut)
  -- date de fin de contrat <= dernier jour mois de paie en cours (période administrative pour tout le monde)
  ,least(p.fin,c.fin)
  -- date de fin de contrat <= dernier jour période de paie en cours
  ,least(p.fin,decode(p.statut,'P',to_date(c.mois||'20','YYYYMMDD'),last_day(to_date(c.mois||'01','YYYYMMDD'))))
  from pers p,ref_periode c
  where c.fin>=p.debut and c.mois<=to_char(p.fin,'YYYYMM')
  and c.mois<=Wmois and p.dretroc<=decode(p.statut,'P',to_date(c.mois||'20','YYYYMMDD'),last_day(to_date(c.mois||'01','YYYYMMDD'))) 
  and c.type='J'
  order by p.mat,c.mois;

-- Pour les porteurs ayant un STC entre le 20 et le 31 du mois m, on envoie également les compteurs le jour de STC
   insert into ev_pers_PERIODE(mat,rc,statut,type,mois,relatdatedeb,relatdatefin,dretroc,dlastav,debut,fin,dpaie)
  select p.mat,p.rc,p.statut,p.type,p.mois,p.relatdatedeb,p.relatdatefin,p.dretroc,p.dlastav,p.debut,p.fin,p.relatdatefin
  from ev_pers_PERIODE p
  where p.relatdatefin>p.dpaie and to_char(p.relatdatefin,'YYYYMM')=to_char(p.dpaie,'YYYYMM')
  and p.type='J' and p.statut='P'
  ;

  -- periode du 1er mai au au dernier jour du mois m + date de stc
  -- utilisée pour DRTDIF, PRIDIF, SLDDIF
  -- daté au 31 du mois m pour les administratifs
  -- daté au 20 du mois m pour les porteurs
  insert into ev_pers_PERIODE(mat,rc,statut,type,mois,relatdatedeb,relatdatefin,dretroc,debut,fin,dpaie)
  -- Les individus présent au 01/05
  select p.mat,p.rc,p.statut,c.type,c.mois,p.debut,p.fin,p.dretroc
  ,greatest(p.debut,c.debut)
  ,least(p.fin,c.fin)
  -- date de fin de contrat <= dernier jour période de paie en cours
  ,least(p.fin,c.fin)
  from pers p,ref_periode c
  where c.fin>=p.debut and c.debut<=p.fin
  and p.dretroc<=p.fretro  and p.dretroc<=c.fin
  -- date de paie antérieure à la fin du mois de paie
  and least(p.fin,c.fin)<=decode(p.statut,'P',to_date(Wmois||'20','YYYYMMDD'),last_day(to_date(Wmois||'01','YYYYMMDD'))) 
  and c.type='D' 
/*union
  -- Les stc avant le mois de paie
    select p.mat,p.rc,p.statut,c.type,c.mois,p.debut,p.fin,p.dretroc
  ,greatest(p.debut,c.debut)
  ,least(p.fin,c.fin,to_date(Wmois||'20','YYYYMMDD'),last_day(to_date(Wmois||'01','YYYYMMDD')))
  -- date de fin de contrat <= dernier jour période de paie en cours
  ,p.fin
  from pers p,ref_periode c
  where c.fin>=p.debut and c.debut<=p.fin
  and c.mois<=Wmois and p.fin<=decode(p.statut,'P',to_date(Wmois||'20','YYYYMMDD'),last_day(to_date(Wmois||'01','YYYYMMDD'))) 
  and p.fin between c.debut and c.fin
  and c.type='D' */
  ;

  -- on met à jour la date du dernier avenant compris entre le début et la fin de la période
  update ev_pers_periode p set dlastav=nvl((select max(cc.cont_datd) from sdv.contprev cc where cc.pers_mat=p.mat and cc.cont_datd between p.debut and p.fin),p.debut);
  
  insert into hst_ev_pers_periode select Widtrt,e.* from ev_pers_periode e;

  update pers  p set (dretroev,fretroev)=(select min(dpaie),max(dpaie) from ev_pers_periode pp where p.mat=pp.mat and p.rc=pp.rc group by pp.mat,pp.rc);
end;
  
--==============================================================================
procedure exec(
  Widtrt        octng_traitement.idtrt%type
,	Wmois         octng_traitement.anneemois%type
, WabsenceSS    char default 'N'
) as
begin
  if WabsenceSS='O' then
    return;
  end if;
  calcul(Widtrt,Wmois);
  erreur(Widtrt);
  historise(Widtrt);
end;

--==============================================================================
procedure dif(
Widtrt      octng_traitement.idtrt%type,
Widtrt_org  octng_traitement.idtrt%type
) as 
begin
  execute immediate 'truncate table ev_dif';
  
  insert into ev_dif(idtrt,dif,typev,mat,rc,codepaie,datev,ordre,qte,qte_hst,taux,taux_hst,val,val_hst)
  select Widtrt,'C',e1.typev,e1.mat,e1.rc,e1.codepaie,e1.datev,e1.ordre,e1.qte,0,e1.taux,0,e1.val,0 from hst_ev e1 where e1.idtrt=Widtrt and not exists(select null from hst_ev e2 where e2.idtrt=Widtrt_org and e1.mat=e2.mat and e1.datev=e2.datev and e1.codepaie=e2.codepaie and e1.ordre=e2.ordre)
  union
  select Widtrt,'S',e2.typev,e2.mat,e2.rc,e2.codepaie,e2.datev,e2.ordre,0,e2.qte,0,e2.taux,0,e2.val from hst_ev e2 where e2.idtrt=Widtrt_org and not exists(select null from hst_ev e1 where e1.idtrt=Widtrt and e1.mat=e2.mat and e1.datev=e2.datev and e1.codepaie=e2.codepaie and e1.ordre=e2.ordre)
  union
  select Widtrt,'M',e1.typev,e1.mat,e1.rc,e1.codepaie,e1.datev,e1.ordre,e1.qte,e2.qte,e1.taux,e2.taux,e1.val,e2.val from hst_ev e1, hst_ev e2 where e1.idtrt=Widtrt and e2.idtrt=Widtrt_org and e1.mat=e2.mat and e1.codepaie=e2.codepaie and e1.datev=e2.datev and e1.ordre=e2.ordre and (nvl(e1.qte,0)<>nvl(e2.qte,0) or nvl(e1.taux,0)<>nvl(e2.taux,0) or nvl(e1.val,0)<>nvl(e2.val,0))
  union
  select Widtrt,'=',e1.typev,e1.mat,e1.rc,e1.codepaie,e1.datev,e1.ordre,e1.qte,e2.qte,e1.taux,e2.taux,e1.val,e2.val from hst_ev e1, hst_ev e2 where e1.idtrt=Widtrt and e2.idtrt=Widtrt_org and e1.mat=e2.mat and e1.codepaie=e2.codepaie and e1.datev=e2.datev and e1.ordre=e2.ordre and nvl(e1.qte,0)=nvl(e2.qte,0) and nvl(e1.taux,0)=nvl(e2.taux,0) and nvl(e1.val,0)=nvl(e2.val,0)
  ;
end;

--==============================================================================
procedure dif_NG(
Widtrt      octng_traitement.idtrt%type,
WabsenceSS    char default 'N'
) as 
begin
  execute immediate 'truncate table ev_dif';
  if WabsenceSS='O' then 
    return;
  end if;
OCTNG_TRT.logger(Widtrt,'dif_NG','png_evfactory',9);
  execute immediate 'truncate table ev_ng';
/*  execute immediate 'truncate table png_evfactoryw';
  insert into png_evfactoryw
  select dateeffet,e.noordre,e.nb,e.relationcontrat,e.ta_natureevgenw,n.code
  FROM EVFACTORYW@png e,ta_natureevgenw@png n,hst_pers hp
  where e.INTERFACEW='ccd8ea7c-61a5-11df-baaa-d35a2f707f95' and e.ta_natureevgenw=n.oid
  and e.relationcontrat=hp.rcoid and hp.idtrt=Widtrt
  ;
  
OCTNG_TRT.logger(Widtrt,'dif_NG','evfactory',9);
  insert into ev_ng(mat,rc,codepaie,datev,ordre,qte)
  select hp.mat,hp.rc,e.code,e.dateeffet,nvl(e.noordre,0),e.nb
  from PNG_evfactoryw e,hst_pers hp
  where e.relationcontrat=hp.rcoid
  and nvl(e.nb,0)<>0
  and hp.idtrt=Widtrt and e.dateeffet between hp.dretroc and hp.fretro
  ;*/
  insert into ev_ng(mat,rc,codepaie,datev,ordre,qte)
  select hp.mat,hp.rc,n.code,e.dateeffet,nvl(e.noordre,0),e.nb
  from evfactoryw@PNG e, ta_natureevgenw@PNG n, hst_pers hp
  where e.INTERFACEW='ccd8ea7c-61a5-11df-baaa-d35a2f707f95' and e.ta_natureevgenw=n.oid
  and e.relationcontrat=hp.rcoid and hp.idtrt=Widtrt
  and nvl(e.nb,0)<>0
  and e.dateeffet between hp.dretroc and hp.fretro
  ;
OCTNG_TRT.logger(Widtrt,'dif_NG','ev_dif',9);
  
  insert into ev_dif(idtrt,dif,typev,mat,rc,codepaie,datev,ordre,qte,qte_hst,taux,taux_hst,val,val_hst)
  select Widtrt,'C',e1.typev,e1.mat,e1.rc,e1.codepaie,e1.datev,e1.ordre,e1.qte,0,e1.taux,0,e1.val,0 from hst_ev e1 where e1.idtrt=Widtrt and not exists(select null from ev_NG e2 where e1.mat=e2.mat and e1.datev=e2.datev and e1.codepaie=e2.codepaie and e1.ordre=e2.ordre)
  union
  select Widtrt,'S',e2.typev,e2.mat,e2.rc,e2.codepaie,e2.datev,e2.ordre,0,e2.qte,0,e2.taux,0,e2.val from ev_NG e2 where not exists(select null from hst_ev e1 where e1.idtrt=Widtrt and e1.mat=e2.mat and e1.datev=e2.datev and e1.codepaie=e2.codepaie and e1.ordre=e2.ordre)
  union
  select Widtrt,'M',e1.typev,e1.mat,e1.rc,e1.codepaie,e1.datev,e1.ordre,e1.qte,e2.qte,e1.taux,e2.taux,e1.val,e2.val from hst_ev e1, ev_NG e2 where e1.idtrt=Widtrt  and e1.mat=e2.mat and e1.codepaie=e2.codepaie and e1.datev=e2.datev and e1.ordre=e2.ordre and (nvl(e1.qte,0)<>nvl(e2.qte,0) or nvl(e1.taux,0)<>nvl(e2.taux,0) or nvl(e1.val,0)<>nvl(e2.val,0))
  union
  select Widtrt,'=',e1.typev,e1.mat,e1.rc,e1.codepaie,e1.datev,e1.ordre,e1.qte,e2.qte,e1.taux,e2.taux,e1.val,e2.val from hst_ev e1, ev_NG e2 where e1.idtrt=Widtrt  and e1.mat=e2.mat and e1.codepaie=e2.codepaie and e1.datev=e2.datev and e1.ordre=e2.ordre and nvl(e1.qte,0)=nvl(e2.qte,0) and nvl(e1.taux,0)=nvl(e2.taux,0) and nvl(e1.val,0)=nvl(e2.val,0)
  ;
end;



end;

/
--------------------------------------------------------
--  DDL for Package Body OCTNG_SPECIFIC
--------------------------------------------------------

  CREATE OR REPLACE PACKAGE BODY "OCTNG_SPECIFIC" AS
  function getEnv return varchar2 is
  begin
    return 'SDV';
  end;

  function getPQD return varchar2 is
  begin
    return 'P';
  end;

  function DateDeb return date is
  begin
    case 
      when getEnv='SDV' then return to_date('01/12/2010','DD/MM/YYYY');
      when getEnv='AME' then return to_date('16/10/2013','DD/MM/YYYY');--to_date('01/06/2009','DD/MM/YYYY');
      when getEnv='EQP' then return to_date('01/01/2013','DD/MM/YYYY');--to_date('01/06/2009','DD/MM/YYYY');
      when getEnv='PPS' then return to_date('01/01/2013','DD/MM/YYYY');--to_date('01/12/2008','DD/MM/YYYY');
    end case;
  end;
end;

/
--------------------------------------------------------
--  DDL for Package Body OCTNG_TRT
--------------------------------------------------------

  CREATE OR REPLACE PACKAGE BODY "OCTNG_TRT" AS

--==============================================================================
-- Procédures privées
--==============================================================================
procedure fin(
    Widtrt			octng_traitement.idtrt%type
) is
Wduree varchar2(50);
begin
  logger(Widtrt,'fin','Fin de l''interface');
  select to_char(max(datesys)-min(datesys)) into Wduree from octng_log where idtrt=Widtrt group by idtrt;
  logger(Widtrt,'fin','Durée de l''interface : '||substr(Wduree,12,2)||'h '||substr(Wduree,15,2)||'mn '||substr(Wduree,18,2)||'s '||substr(Wduree,21,3)||'ms');
  update OCTNG_traitement set etat='T' where idtrt=Widtrt;

  logger(Widtrt,'CPTRES1','Supprime la table temporaire OCTIME_CPTRES1_'||Widtrt,6);
  begin
    execute immediate 'drop table OCTIME_CPTRES1_'||Widtrt;
  exception
  when others then
    null;
  end;
  
  logger(Widtrt,'historise','Sauvegarde des individus',6);
  delete hst_pers where idtrt=Widtrt;
    insert into hst_pers(idtrt,mat,rc,statut,etat,eta,relatdatedeb,relatdatefin,ctrdeb,ctrfin,debut,fin,dretro,fretro,dretroc,dretroev,fretroev,dretroabs,fretroabs,dretrores,fretrores,rcoid,ctroid) 
    select Widtrt,mat,rc,statut,etat,eta,relatdatedeb,relatdatefin,ctrdeb,ctrfin,debut,fin,dretro,fretro,dretroc,dretroev,fretroev,dretroabs,fretroabs,dretrores,fretrores,rcoid,ctroid from pers;
end;

--==============================================================================
-- Procédures publiques
--==============================================================================
procedure debut(
    Widtrt			  IN OUT octng_traitement.idtrt%type
,   Wlogin			  octng_traitement.nomuser%type
,   Wdatetrt			octng_traitement.datetrt%type
,	  Wmois         octng_traitement.anneemois%type
,   WTypetrt      octng_traitement.typetrt%type
,   WLib          octng_traitement.lib%type
,   Wdateretro		octng_traitement.dateretro%type
,   WClause       octng_traitement.clause%type
) as 
WLibTrt ref_type_trt.lib%type;
begin
  
  logger(Widtrt,'debut','Interface lancée par '||Wlogin);
  logger(Widtrt,'debut','Calcul pour le mois '||substr(Wmois,5,2)||'/'||substr(Wmois,1,4));
  logger(Widtrt,'debut','Identifiant : '||Widtrt);
  logger(Widtrt,'debut','Libellé : '||WLib);
  select lib into WLibTrt from REF_Type_trt where typetrt=WTypetrt;
  logger(Widtrt,'debut','Type : '||WLibTrt);
  logger(Widtrt,'debut','Rétro : '||Wdateretro);
  logger(Widtrt,'debut','Clause : '||substr(WClause,1,1000));

  execute immediate 'truncate table abs';
  execute immediate 'truncate table ev';
end;

--==============================================================================
procedure succes(
    Widtrt			octng_traitement.idtrt%type
,   Wstatut       octng_traitement.statut%type default 'S'
) is
begin
  -- Si le statut est déjà passé à X à cause d'individus en erreur, on ne change rien
  update octng_traitement set statut=Wstatut where idtrt=Widtrt and statut<>'X';
  fin(Widtrt);
end;

--==============================================================================
procedure erreur(
    Widtrt			octng_traitement.idtrt%type
) as 
begin
  delete hst_ev where idtrt=Widtrt;
  delete hst_abs where idtrt=Widtrt;
  update octng_traitement set statut='E' where idtrt=Widtrt;
  Log_Errors(Widtrt,DBMS_UTILITY.FORMAT_ERROR_STACK);
  Log_Errors(Widtrt,DBMS_UTILITY.FORMAT_ERROR_BACKTRACE);
  fin(Widtrt);
  raise_application_error(-20000,'Erreur !!! C''est moche !!!');
end;

--==============================================================================
procedure raise_erreur(
    Widtrt octng_traitement.idtrt%type
,   Wmodule varchar2    -- ev ou abs
,   Werreur varchar2    -- hst ou ano
) as
begin
  raise_application_error(-20001,'Erreur dans module '||Wmodule||' Msg : '||Werreur);
  OCTNG_TRT.erreur(Widtrt);
end;

--==============================================================================
procedure logger(
   Widtrt   octng_traitement.idtrt%type
,  Wmodule  octng_log.module%type
,  Wmsg     octng_log.msg%type
,  Wniveau  octng_log.niveau%type default 5
) as
begin
--  dbms_output.put_line(systimestamp||' -- '||' -- '||Wmodule||' -- '||Wmsg);
  insert into octng_log(idtrt,datesys,module,msg,niveau) values(Widtrt,systimestamp,Wmodule,Wmsg,Wniveau);
  commit;
end;

--==============================================================================
procedure logsql(
    Widtrt			  octng_traitement.idtrt%type
,   Wcodepaie     octng_sql.codepaie%type
,   Wsql          octng_sql.sqlW%type
) is
  Wnb           octng_sql.nb%type;
begin
  Wnb:=logsql_nb(Widtrt,Wcodepaie,Wsql);
end;

function logsql_nb(
    Widtrt			  octng_traitement.idtrt%type
,   Wcodepaie     octng_sql.codepaie%type
,   Wsql          octng_sql.sqlW%type
) return number is
  Wnb           octng_sql.nb%type;
begin
  insert into octng_sql(idtrt,codepaie,sqlW,nb) values(Widtrt,Wcodepaie,Wsql||';',Wnb);
  commit;
  execute immediate(Wsql);
  Wnb:=SQL%ROWCOUNT;
--  if (upper(substr(Wsql,1,))='INSERT' or upper(substr(Wsql,1,))='UPDATE' or upper(substr(Wsql,1,))='CREATE TABLE') then
  update octng_sql set nb=Wnb where idtrt=Widtrt and codepaie=Wcodepaie and sqlW=Wsql||';';
  commit;
  return Wnb;
end;

--==============================================================================
procedure clean is
  the_date timestamp;
begin
  the_date:=sysdate;
  delete hst_abs where idtrt in (select idtrt from octng_traitement where datetrt<the_date-90);
  delete hst_abs_erreur where idtrt in (select idtrt from octng_traitement where datetrt<the_date-90);
  delete hst_dif where idtrt in (select idtrt from octng_traitement where datetrt<the_date-90);
  delete hst_ev where idtrt in (select idtrt from octng_traitement where datetrt<the_date-90);
  delete hst_ev_erreur where idtrt in (select idtrt from octng_traitement where datetrt<the_date-90);
  delete hst_ev_pers_periode where idtrt in (select idtrt from octng_traitement where datetrt<the_date-90);
  delete hst_pers where idtrt in (select idtrt from octng_traitement where datetrt<the_date-90);
  
  delete octng_log where idtrt in (select idtrt from octng_traitement where datetrt<the_date-90);
  delete octng_sql where idtrt in (select idtrt from octng_traitement where datetrt<the_date-90);
  delete octng_traitement where idtrt in (select idtrt from octng_traitement where datetrt<the_date-90);
end;

--==============================================================================
procedure nettoyage is
begin
  delete Octime_varprev;
  delete Octime_varprevsav;
  delete hst_abs_erreur;
  delete hst_ev_erreur;
  delete hst_ev_pers_periode;
  delete hst_abs;
  delete hst_ev;
  delete hst_dif;
  delete hst_pers;
  delete octng_log;
  delete octng_sql;
  delete octng_traitement;
end;

--==============================================================================
procedure supprime(
    Wlogin			  octng_traitement.nomuser%type
,   Widtrt			  octng_traitement.idtrt%type
) is
  WModule octng_traitement.module%type;
--Wdatetrt='13/09/11 10:35:46,210000000'
begin
  select module into WModule from octng_traitement where idtrt=Widtrt;
  if WModule<>'C' then
    raise_application_error(-20001,'Impossible de supprimer une interface intégrée.');
  end if;
  delete hst_abs_erreur where idtrt=Widtrt;
  delete hst_ev_erreur where idtrt=Widtrt;
  delete hst_ev_pers_periode where idtrt=Widtrt;
  delete hst_abs where idtrt=Widtrt;
  delete hst_ev where idtrt=Widtrt;
  delete hst_dif where idtrt=Widtrt;
  delete hst_pers where idtrt=Widtrt;
  delete octng_log where idtrt=Widtrt;
  delete octng_sql where idtrt=Widtrt;
  delete octng_traitement where idtrt=Widtrt;
end;

--==============================================================================
procedure supprime_last(
    Wlogin			  octng_traitement.nomuser%type
) is
Widtrt_last   octng_traitement.idtrt%type;
begin
    select max(idtrt) into Widtrt_last from octng_traitement;
    supprime(Wlogin,Widtrt_last);
end;

procedure bloque_paie(
  Wanneemois      octng_traitement.anneemois%type
) is
BEGIN
  execute immediate('create table OCTIME_pers_compl_'||Wanneemois||' as select * from sdv.pers_compl');
  if OCTNG_SPECIFIC.getEnv not in ('EQP','PPS') then
    update OCTIME_pers_compl set pers_dretroanc=pers_dretro;
  end if;

  update octng_paie
  set anneemois=to_char(to_date(anneemois||'01','YYYYMMDD')+32,'YYYYMM')
  ,   bloque='O'
  where anneemois=Wanneemois;
  
  if sql%rowcount<>1 then
    raise_application_error(-20001,'Période de paie incorrecte.');
  end if;
end;

procedure change_paie(
  Wanneemois      octng_traitement.anneemois%type
) is
begin
  update octng_paie
  set bloque='N'
--  where anneemois=Wanneemois
  ;
  
  if sql%rowcount<>1 then
    raise_application_error(-20001,'Période de paie incorrecte.');
  end if;
end;

procedure cloture(
  Wanneemois      octng_traitement.anneemois%type
) is
BEGIN
  execute immediate('create table OCTIME_pers_compl_'||Wanneemois||' as select * from sdv.pers_compl');
  update sdv.pers_compl set pers_dretroanc=pers_dretro;

  update octng_paie
  set anneemois=to_char(to_date(anneemois||'01','YYYYMMDD')+32,'YYYYMM')
  where anneemois=Wanneemois;
  
  if sql%rowcount<>1 then
    raise_application_error(-20001,'Période de paie incorrecte.');
  end if;
end;


end;

/
