-- Modele actif
 drop table tmp_modele_actif;
 create table tmp_modele_actif as
		SELECT mn2.*
			FROM PEPP.MODELES_NOMS  mn2
    inner join "depot"@MROAD d on lpad(trim(eta),3,'0')=d."code"
			WHERE NUMMODELE = (SELECT MAX(mn1.NUMMODELE)
							FROM PEPP.MODELES_NOMS mn1
							WHERE mn1.ETA 	= mn2.ETA
							AND 	mn1.CODMOD 	= mn2.CODMOD)
    and trim(mn2.eta) in (select distinct substr(C04,4,2) from perzprpl p where CF=(select max(cf) from perzprpl p2 where p.cb=p2.cb) and p.C05='OUI' and p.C04 not like 'SDV%')
    AND eta in ('10','39','45')
     order by eta;
alter table tmp_modele_actif add lu char(1);
alter table tmp_modele_actif add ma char(1);
alter table tmp_modele_actif add me char(1);
alter table tmp_modele_actif add je char(1);
alter table tmp_modele_actif add ve char(1);
alter table tmp_modele_actif add sa char(1);
alter table tmp_modele_actif add di char(1);
update tmp_modele_actif set lu='O' where codmod='LUN';
update tmp_modele_actif set ma='O' where codmod='MAR';
update tmp_modele_actif set me='O' where codmod='MER';
update tmp_modele_actif set je='O' where codmod='JEU';
update tmp_modele_actif set ve='O' where codmod='VEN';
update tmp_modele_actif set sa='O' where codmod='SAM';
update tmp_modele_actif set di='O' where codmod='DIM';
update tmp_modele_actif set lu='O' where upper(trim(nommodele)) ='LUNDI';
update tmp_modele_actif set ma='O' where upper(trim(nommodele))='MARDI';
update tmp_modele_actif set me='O' where upper(trim(nommodele))='MERCREDI';
update tmp_modele_actif set je='O' where upper(trim(nommodele))='JEUDI';
update tmp_modele_actif set ve='O' where upper(trim(nommodele))='VENDREDI';
update tmp_modele_actif set sa='O' where upper(trim(nommodele))='SAMEDI';
update tmp_modele_actif set di='O' where upper(trim(nommodele))='DIMANCHE';
update tmp_modele_actif set lu='O' where upper(trim(nommodele)) like 'LUNDI (%';
update tmp_modele_actif set ma='O' where upper(trim(nommodele)) like 'MARDI (%';
update tmp_modele_actif set me='O' where upper(trim(nommodele)) like 'MERCREDI (%';
update tmp_modele_actif set je='O' where upper(trim(nommodele)) like 'JEUDI (%';
update tmp_modele_actif set ve='O' where upper(trim(nommodele)) like 'VENDREDI (%';
update tmp_modele_actif set sa='O' where upper(trim(nommodele)) like 'SAMEDI (%';
update tmp_modele_actif set di='O' where upper(trim(nommodele)) like 'DIMANCHE (%';
update tmp_modele_actif set lu='O' where upper(trim(nommodele)) like 'LUNDI  (%';
update tmp_modele_actif set ma='O' where upper(trim(nommodele)) like 'MARDI  (%';
update tmp_modele_actif set me='O' where upper(trim(nommodele)) like 'MERCREDI  (%';
update tmp_modele_actif set je='O' where upper(trim(nommodele)) like 'JEUDI  (%';
update tmp_modele_actif set ve='O' where upper(trim(nommodele)) like 'VENDREDI  (%';
update tmp_modele_actif set sa='O' where upper(trim(nommodele)) like 'SAMEDI  (%';
update tmp_modele_actif set di='O' where upper(trim(nommodele)) like 'DIMANCHE  (%';
update tmp_modele_actif set lu='O',ma='O',me='O',je='O',ve='O',sa='O' where codmod='SEM';
update tmp_modele_actif set di='O' where codmod='GMS';
update tmp_modele_actif set ve='O' where codmod='VNA';
update tmp_modele_actif set lu='O' where codmod='LNA';
update tmp_modele_actif set ma='O' where codmod='MNA';
update tmp_modele_actif set me='O' where codmod='MER';
update tmp_modele_actif set je='O' where codmod='MJS';
update tmp_modele_actif set sa='O' where codmod='SNA';
update tmp_modele_actif set ma='O',ve=null where codmod='AU2' and eta=40;
update tmp_modele_actif set ma='O',ve='O' where codmod='AU2' and eta=31;
update tmp_modele_actif set lu=null,me='O',je=null,sa=null where codmod='AU3' and eta=40;
update tmp_modele_actif set lu='O',me='O',je='O',sa='O' where codmod='AU3' and eta=31;
update tmp_modele_actif set di=null where codmod='AU6' and eta=40;
update tmp_modele_actif set di='O' where codmod='AU6' and eta=31;
update tmp_modele_actif set me='O',je='O' where codmod='60J';
update tmp_modele_actif set lu=null,ma=null,je=null,ve=null,sa=null where codmod='SEM' and eta=10;
update tmp_modele_actif set lu=null,ma=null,je=null,ve=null,sa=null where codmod='SEM' and eta=07;
update tmp_modele_actif set me=null where codmod='MER' and eta=18;
select * from TMP_MODELE_ACTIF where lu is null and ma is null and me is null and je is null and ve is null and sa is null and di is null;
select * from TMP_MODELE_ACTIF where eta='29';
select * from TMP_MODELE_ACTIF where eta='10'; 
select * from TMP_MODELE_ACTIF order by eta,codmod; 

commit;

-- Groupes existants
select distinct 
  d."id" as "depot_id"
  ,1 as "flux_id"
  ,chr(d."id"+64)||substr(codetr,3,1) as "code"
  ,'00:00:00' as "heure_debut"
  ,0 as "utilisateur_id"
  ,'2015-11-17' as "date_creation"
  from modeles_tournees t
  inner join "depot"@MROAD d on lpad(trim(eta),3,'0')=d."code"
  left outer join "groupe_tournee"@MROAD g on g."depot_id"=d."id" and g."code"=chr(64+d."id")||substr(t.codetr,3,1)
   where g."id" is null
  --and datetr>'2014'
--union select 3,1,'CB','00:00:00',0,'2015-10-21' from dual--10B
--union select 11,1,'GG','00:00:00',0,'2015-10-21' from dual--29G
--union select 14,1,'NW','00:00:00',0,'2015-10-21' from dual--34W
and eta in ('10','39','45')
  order by 1,2;


--- Mettre � jour l'heure de d�but de groupe � partir du fichier reprise_groupe_tournee_update.sql


-- controle tournee sans groupe
  select t.*
  from modeles_tournees t
  inner join tmp_modele_actif a on a.eta=t.eta and a.codmod=t.codmod and a.nummodele=t.nummodele
  left outer join "depot"@MROAD d on lpad(trim(t.eta),3,'0')=d."code"
  left outer join "groupe_tournee"@MROAD g on g."flux_id"=1 and g."depot_id"=d."id" and g."code"=chr(64+d."id")||substr(t.codetr,3,1)
  where g."id" is null or d."id" is null;
  
--modele_tournee
  select distinct
  g."id" as "groupe_id"
  ,0 as "actif"
  ,d."code"||'N'||lpad(g."code",2,' ')||'0'||substr(t.codetr,4,2) as "code"
  ,d."code"||'N'||lpad(g."code",2,' ')||'0'||substr(t.codetr,4,2) as "libelle"
  ,0 as "utilisateur_id"
  ,'2015-11-17' as "date_creation"
  ,1 as "typetournee_id"
  ,'0'||substr(t.codetr,4,2) as "numero"
  ,chr(d."id"+64)||substr(t.codetr,4,2) as "codeDCS"
  from modeles_tournees t
  inner join tmp_modele_actif a on a.eta=t.eta and a.codmod=t.codmod and a.nummodele=t.nummodele
  left outer join "depot"@MROAD d on lpad(trim(t.eta),3,'0')=d."code"
  left outer join "groupe_tournee"@MROAD g on g."flux_id"=1 and g."depot_id"=d."id" and g."code"=chr(64+d."id")||substr(t.codetr,3,1)
--  left outer join "modele_tournee"@MROAD mt on mt."code"=d."code"||'N'||lpad(g."code",2,' ')||'0'||substr(t.codetr,4,2)
  where g."id" not in (408,433,434,435,437) 
  and d."code"||'N'||lpad(g."code",2,' ')||'0'||substr(t.codetr,4,2) not in (select "code" from "modele_tournee"@MROAD)
  -- groupe XB sur BERCY a jour dans mroad
  -- groupe SQ sur BONDY a jour dans mroad
  -- groupe JT sur 28 a jour dans mroad
  -- groupe KT sur 29 a jour dans mroad
  -- groupe RQ sur 40 a jour dans mroad
;
--- Mettre � jour l'heure de d�but de groupe � partir du fichier reprise_modele_tournee_update_codeDCS.sql

/* Dans MROAD

delete mtj
from modele_tournee_jour mtj
inner join modele_tournee mt on mtj.tournee_id=mt.id
inner join groupe_tournee gt on mt.groupe_id=gt.id
where gt.flux_id=1 and gt.depot_id in (11,19) and not mt.actif
-- and not exists(select null from pai_tournee pt where mtj.id=pt.modele_tournee_jour_id)
-- and not exists(select null from feuille_portage pt where mtj.id=pt.tournee_jour_id);
*/

  --modele_tournee_jour
   select mt."id" as "tournee_id"
  ,1 as "ordre"
  ,1 as "jour_id" 
  ,e."id" as "employe_id"
  ,3 as "transport_id"
  ,'2015-11-25' as "date_debut"
  ,'2999-01-01' as "date_fin"
  ,to_char(t.valrem,'FM90.00000') as "valrem"
  ,lpad(trunc(t.temps),2,'0')||':'||lpad(round((t.temps-trunc(t.temps))*60),2,'0') as "duree"
  ,t.nbkm2 as "nbkm"
  ,t.nbkm as "nbkm_paye"
  ,0 as "utilisateur_id"
  ,'2015-11-17' as "date_creation"
  ,mt."code"||'DI' as "code"
  ,lpad(trunc(t.temps),2,'0')||':'||lpad(round((t.temps-trunc(t.temps))*60),2,'0') "duree_geo"
  ,t.tnbrcli "qte_geo"
  ,t.tnbrcli "nbcli_geo"
  ,nbkm "nbkm_geo"
  ,t.tnbrcli "nbadr_geo"
  ,'9.61' as "tauxhoraire"
  from modeles_tournees t
  inner join tmp_modele_actif a on a.eta=t.eta and a.codmod=t.codmod and a.nummodele=t.nummodele
  inner join "depot"@MROAD d on lpad(trim(t.eta),3,'0')=d."code"
  left outer join "groupe_tournee"@MROAD g on g."flux_id"=1 and g."depot_id"=d."id" and trim(g."code")=trim(chr(64+d."id")||substr(t.codetr,3,1))
  left outer join "modele_tournee"@MROAD mt on mt."code"=d."code"||'N'||g."code"||'0'||substr(t.codetr,4,2)
  left outer join "employe"@MROAD e on t.mat=e."matricule"
  where a.di='O' and  g."id" not in (408,433,434,435,437) 
union  
   select mt."id" as "tournee_id"
  ,1 as "ordre"
  ,2 as "jour_id" 
  ,e."id" as "employe_id"
  ,3 as "transport_id"
  ,'2015-11-25' as "date_debut"
  ,'2999-01-01' as "date_fin"
  ,to_char(t.valrem,'FM90.00000') as "valrem"
  ,lpad(trunc(t.temps),2,'0')||':'||lpad(round((t.temps-trunc(t.temps))*60),2,'0') as "duree"
  ,t.nbkm2 as "nbkm"
  ,t.nbkm as "nbkm_paye"
  ,0 as "utilisateur_id"
  ,'2015-11-17' as "date_creation"
  ,mt."code"||'LU' as "code"
  ,lpad(trunc(t.temps),2,'0')||':'||lpad(round((t.temps-trunc(t.temps))*60),2,'0') "duree_geo"
  ,t.tnbrcli "qte_geo"
  ,t.tnbrcli "nbcli_geo"
  ,nbkm "nbkm_geo"
  ,t.tnbrcli "nbadr_geo"
  ,'9.61' as "tauxhoraire"
  from modeles_tournees t
  inner join tmp_modele_actif a on a.eta=t.eta and a.codmod=t.codmod and a.nummodele=t.nummodele
  inner join "depot"@MROAD d on lpad(trim(t.eta),3,'0')=d."code"
  left outer join "groupe_tournee"@MROAD g on g."flux_id"=1 and g."depot_id"=d."id" and g."code"=chr(64+d."id")||substr(t.codetr,3,1)
  left outer join "modele_tournee"@MROAD mt on mt."code"=d."code"||'N'||g."code"||'0'||substr(t.codetr,4,2)
  left outer join "employe"@MROAD e on t.mat=e."matricule"
  where a.lu='O' and  g."id" not in (408,433,434,435,437) 
--  and mt."id"=1709
union  
   select mt."id" as "tournee_id"
  ,1 as "ordre"
  ,3 as "jour_id" 
  ,e."id" as "employe_id"
  ,3 as "transport_id"
  ,'2015-11-25' as "date_debut"
  ,'2999-01-01' as "date_fin"
  ,to_char(t.valrem,'FM90.00000') as "valrem"
  ,lpad(trunc(t.temps),2,'0')||':'||lpad(round((t.temps-trunc(t.temps))*60),2,'0') as "duree"
  ,t.nbkm2
  ,t.nbkm
  ,0 as "utilisateur_id"
  ,'2015-11-17' as "date_creation"
  ,mt."code"||'MA'
  ,lpad(trunc(t.temps),2,'0')||':'||lpad(round((t.temps-trunc(t.temps))*60),2,'0') "duree_geo"
  ,t.tnbrcli "qte_geo"
  ,t.tnbrcli "nbcli_geo"
  ,nbkm "nbkm_geo"
  ,t.tnbrcli "nbadr_geo"
  ,'9.61' as "tauxhoraire"
  from modeles_tournees t
  inner join tmp_modele_actif a on a.eta=t.eta and a.codmod=t.codmod and a.nummodele=t.nummodele
  inner join "depot"@MROAD d on lpad(trim(t.eta),3,'0')=d."code"
  left outer join "groupe_tournee"@MROAD g on g."flux_id"=1 and g."depot_id"=d."id" and g."code"=chr(64+d."id")||substr(t.codetr,3,1)
  left outer join "modele_tournee"@MROAD mt on mt."code"=d."code"||'N'||g."code"||'0'||substr(t.codetr,4,2)
  left outer join "employe"@MROAD e on t.mat=e."matricule"
  where a.ma='O' and  g."id" not in (408,433,434,435,437) 
union  
   select mt."id" as "tournee_id"
  ,1 as "ordre"
  ,4 as "jour_id" 
  ,e."id" as "employe_id"
  ,3 as "transport_id"
  ,'2015-11-25' as "date_debut"
  ,'2999-01-01' as "date_fin"
  ,to_char(t.valrem,'FM90.00000') as "valrem"
  ,lpad(trunc(t.temps),2,'0')||':'||lpad(round((t.temps-trunc(t.temps))*60),2,'0') as "duree"
  ,t.nbkm2
  ,t.nbkm
  ,0 as "utilisateur_id"
  ,'2015-11-17' as "date_creation"
  ,mt."code"||'ME'
  ,lpad(trunc(t.temps),2,'0')||':'||lpad(round((t.temps-trunc(t.temps))*60),2,'0') "duree_geo"
  ,t.tnbrcli "qte_geo"
  ,t.tnbrcli "nbcli_geo"
  ,nbkm "nbkm_geo"
  ,t.tnbrcli "nbadr_geo"
  ,'9.61' as "tauxhoraire"
  from modeles_tournees t
  inner join tmp_modele_actif a on a.eta=t.eta and a.codmod=t.codmod and a.nummodele=t.nummodele
  inner join "depot"@MROAD d on lpad(trim(t.eta),3,'0')=d."code"
  left outer join "groupe_tournee"@MROAD g on g."flux_id"=1 and g."depot_id"=d."id" and g."code"=chr(64+d."id")||substr(t.codetr,3,1)
  left outer join "modele_tournee"@MROAD mt on mt."code"=d."code"||'N'||g."code"||'0'||substr(t.codetr,4,2)
  left outer join "employe"@MROAD e on t.mat=e."matricule"
  where a.me='O' and  g."id" not in (408,433,434,435,437) 
  and t.codetr<>'13Z20B'
union  
   select mt."id" as "tournee_id"
  ,1 as "ordre"
  ,5 as "jour_id" 
  ,e."id" as "employe_id"
  ,3 as "transport_id"
  ,'2015-11-25' as "date_debut"
  ,'2999-01-01' as "date_fin"
  ,to_char(t.valrem,'FM90.00000') as "valrem"
  ,lpad(trunc(t.temps),2,'0')||':'||lpad(round((t.temps-trunc(t.temps))*60),2,'0') as "duree"
  ,t.nbkm2
  ,t.nbkm
  ,0 as "utilisateur_id"
  ,'2015-11-17' as "date_creation"
  ,mt."code"||'JE'
  ,lpad(trunc(t.temps),2,'0')||':'||lpad(round((t.temps-trunc(t.temps))*60),2,'0') "duree_geo"
  ,t.tnbrcli "qte_geo"
  ,t.tnbrcli "nbcli_geo"
  ,nbkm "nbkm_geo"
  ,t.tnbrcli "nbadr_geo"
  ,'9.61' as "tauxhoraire"
  from modeles_tournees t
  inner join tmp_modele_actif a on a.eta=t.eta and a.codmod=t.codmod and a.nummodele=t.nummodele
  inner join "depot"@MROAD d on lpad(trim(t.eta),3,'0')=d."code"
  left outer join "groupe_tournee"@MROAD g on g."flux_id"=1 and g."depot_id"=d."id" and g."code"=chr(64+d."id")||substr(t.codetr,3,1)
  left outer join "modele_tournee"@MROAD mt on mt."code"=d."code"||'N'||g."code"||'0'||substr(t.codetr,4,2)
  left outer join "employe"@MROAD e on t.mat=e."matricule"
  where a.je='O' and  g."id" not in (408,433,434,435,437) 
  and t.codetr not in ('36H02BIS','45M19B')
union  
   select mt."id" as "tournee_id"
  ,1 as "ordre"
  ,6 as "jour_id" 
  ,e."id" as "employe_id"
  ,3 as "transport_id"
  ,'2015-11-25' as "date_debut"
  ,'2999-01-01' as "date_fin"
  ,to_char(t.valrem,'FM90.00000') as "valrem"
  ,lpad(trunc(t.temps),2,'0')||':'||lpad(round((t.temps-trunc(t.temps))*60),2,'0') as "duree"
  ,t.nbkm2
  ,t.nbkm
  ,0 as "utilisateur_id"
  ,'2015-11-17' as "date_creation"
  ,mt."code"||'VE'
  ,lpad(trunc(t.temps),2,'0')||':'||lpad(round((t.temps-trunc(t.temps))*60),2,'0') "duree_geo"
  ,t.tnbrcli "qte_geo"
  ,t.tnbrcli "nbcli_geo"
  ,nbkm "nbkm_geo"
  ,t.tnbrcli "nbadr_geo"
  ,'9.61' as "tauxhoraire"
  from modeles_tournees t
  inner join tmp_modele_actif a on a.eta=t.eta and a.codmod=t.codmod and a.nummodele=t.nummodele
  inner join "depot"@MROAD d on lpad(trim(t.eta),3,'0')=d."code"
  left outer join "groupe_tournee"@MROAD g on g."flux_id"=1 and g."depot_id"=d."id" and g."code"=chr(64+d."id")||substr(t.codetr,3,1)
  left outer join "modele_tournee"@MROAD mt on mt."code"=d."code"||'N'||g."code"||'0'||substr(t.codetr,4,2)
  left outer join "employe"@MROAD e on t.mat=e."matricule"
  where a.ve='O' and  g."id" not in (408,433,434,435,437) 
  and t.codetr not in ('36H02BIS','41O23B')
union  
   select mt."id" as "tournee_id"
  ,1 as "ordre"
  ,7 as "jour_id" 
  ,e."id" as "employe_id"
  ,3 as "transport_id"
  ,'2015-11-25' as "date_debut"
  ,'2999-01-01' as "date_fin"
  ,to_char(t.valrem,'FM90.00000') as "valrem"
  ,lpad(trunc(t.temps),2,'0')||':'||lpad(round((t.temps-trunc(t.temps))*60),2,'0') as "duree"
  ,t.nbkm2
  ,t.nbkm
  ,0 as "utilisateur_id"
  ,'2015-11-17' as "date_creation"
  ,mt."code"||'SA'
  ,lpad(trunc(t.temps),2,'0')||':'||lpad(round((t.temps-trunc(t.temps))*60),2,'0') "duree_geo"
  ,t.tnbrcli "qte_geo"
  ,t.tnbrcli "nbcli_geo"
  ,nbkm "nbkm_geo"
  ,t.tnbrcli "nbadr_geo"
  ,'9.61' as "tauxhoraire"
  from modeles_tournees t
  inner join tmp_modele_actif a on a.eta=t.eta and a.codmod=t.codmod and a.nummodele=t.nummodele
  inner join "depot"@MROAD d on lpad(trim(t.eta),3,'0')=d."code"
  left outer join "groupe_tournee"@MROAD g on g."flux_id"=1 and g."depot_id"=d."id" and g."code"=chr(64+d."id")||substr(t.codetr,3,1)
  left outer join "modele_tournee"@MROAD mt on mt."code"=d."code"||'N'||g."code"||'0'||substr(t.codetr,4,2)
  left outer join "employe"@MROAD e on t.mat=e."matricule"
  where a.sa='O' and  g."id" not in (408,433,434,435,437) 
;
------------------------------------------------ ACTIVITE ----------------

drop table tmp_heure_debut;
create table tmp_heure_debut as
select distinct  mtj."employe_id", mtj."jour_id",to_number(substr(min(g."heure_debut"),1,2))+to_number(substr(min(g."heure_debut"),4,2))/60 "heure_debut"
from "modele_tournee_jour"@MROAD mtj
inner join "modele_tournee"@MROAD mt on mt."id"=mtj."tournee_id"
inner join "groupe_tournee"@MROAD g on g."id"=mt."groupe_id"
where mtj."employe_id" is not null
group by mtj."employe_id", mtj."jour_id";

select * from tmp_heure_debut where "heure_debut" is null;
select
d."id" as "depot_id"
,1 as "flux_id"
,mod(to_number(to_char(to_date(datetr,'YYYYMMDD'), 'D')),7)+1 as "jour_id"
,a."id" as "activite_id"
,e."id" as "employe_id"
,3 as "transport_id"
,'2014-01-01' as "date_debut"
,'2999-01-01' as "date_fin"
,lpad(trunc(coalesce(h."heure_debut",6)-temps1),2,'0')||':'||lpad(round(((coalesce(h."heure_debut",6)-temps1)-trunc(coalesce(h."heure_debut",6)-temps1))*60),2,'0')||':00' as "heure_debut"
--        ,to_char(cast(sum(temps1)/count(*) as number(4,2)),'FM90.00') as "duree"
,lpad(trunc(sum(temps1)/count(*)),2,'0')||':'||lpad(round((sum(temps1)/count(*)-trunc(sum(temps1)/count(*)))*60),2,'0') as "duree"
,cast(sum(nbkm)/count(*) as number(4,0)) as "nbkm_paye"
,1 as "valide"
,'2015-11-17' as "date_creation"
--,h."heure_debut"
from heures h
inner join perzprpl p on h.mat=p.CB and CF=(select max(cf) from perzprpl p2 where p.cb=p2.cb and p.cf<to_date(h.datetr,'YYYYMMDD') and p.C05='OUI')
inner join "depot"@MROAD d on '0'||substr(p.C04,4,2)=d."code"
left outer join "employe"@MROAD e on cb=e."matricule"
left outer join "ref_activite"@MROAD a on h.CODEACTIVITE=a."code"
left outer join tmp_heure_debut h on e."id"=h."employe_id" and h."jour_id"=mod(to_number(to_char(to_date(datetr,'YYYYMMDD'), 'D')),7)+1
where codeactivite not in ('AT','AR','FO','EL','GA') 
and datetr>='2014'
group by d."id",mod(to_number(to_char(to_date(datetr,'YYYYMMDD'), 'D')),7)+1,a."id",e."id"
,lpad(trunc(coalesce(h."heure_debut",6)-temps1),2,'0')||':'||lpad(round(((coalesce(h."heure_debut",6)-temps1)-trunc(coalesce(h."heure_debut",6)-temps1))*60),2,'0')||':00'
--,h."heure_debut"
having count(*)>15
--        and (a."id" is null or e."id" is null)
;