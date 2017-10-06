  -- DANS MYSQL
  DROP TABLE tmp_pai_reclamation;
  CREATE TABLE tmp_pai_reclamation
   (	datepaiefm VARCHAR(8) NOT NULL , 
	datetr VARCHAR(8) NOT NULL , 
	codetr VARCHAR(11) NOT NULL , 
	codetitre VARCHAR(2) NOT NULL , 
	nbrecab numeric(4,0) NOT NULL , 
	nbannab numeric(4,0) NOT NULL , 
	nbrecdif numeric(4,0) NOT NULL , 
	nbanndif numeric(4,0) NOT NULL , 
	indicinc numeric(1,0) NOT NULL , 
	anninc numeric(1,0) NOT NULL , 
	nomuser VARCHAR(10) DEFAULT 'DCS' NOT NULL , 
	datemaj VARCHAR(19) NOT NULL , 
	codetitredcs VARCHAR(5), 
  eta VARCHAR(3),
  depot_id int(11),
  groupe_id int(11),
  tournee_id int(11),
	 CONSTRAINT pktmp_reclamation PRIMARY KEY (datetr, codetr, datepaiefm, codetitre)
   ) ;

  -- DANS ORACLE
    begin
      for r in (
        select r.datepaiefm,r.datetr,r.codetitre,r.codetr,r.nbrecab,r.nbannab,r.nbrecdif,r.nbanndif,r.indicinc,r.anninc,r.nomuser,r.datemaj,r.codetitredcs,t.eta
        from  reclamations r
        left outer join tournees t on r.datetr=t.datetr and r.codetr=t.codetr
        where r.datepaiefm>'201409' or r.datetr>='20140821'
        ) loop
        insert into "tmp_pai_reclamation"@MROAD("datepaiefm","datetr","codetitre","codetr","nbrecab","nbannab","nbrecdif","nbanndif","indicinc","anninc","nomuser","datemaj","codetitredcs","eta") 
        values(r.datepaiefm,r.datetr,r.codetitre,r.codetr,r.nbrecab,r.nbannab,r.nbrecdif,r.nbanndif,r.indicinc,r.anninc,r.nomuser,r.datemaj,r.codetitredcs,r.eta);
      end loop;
      commit;
    end;
  /  
  
;  -- DANS MYSQL
-- transco titre inexistante
update
/*select r.*,pt.id
-- ,d.*
-- ,g.id,g.code,char(64+d.id),substr(r.codetr,3,1),concat(char(64+d.id),substr(r.codetr,3,1))
, concat(substr(r.datetr,1,4),'-',substr(r.datetr,5,2),'-',substr(r.datetr,7,2))
,concat(d.code,'N',lpad(g.code,2,' '),'0',substr(r.codetr,4,2),
case 
when dayofweek(concat(substr(r.datetr,1,4),'-',substr(r.datetr,5,2),'-',substr(r.datetr,7,2)))=1 THEN 'DI'
when dayofweek(concat(substr(r.datetr,1,4),'-',substr(r.datetr,5,2),'-',substr(r.datetr,7,2)))=2 THEN 'LU'
when dayofweek(concat(substr(r.datetr,1,4),'-',substr(r.datetr,5,2),'-',substr(r.datetr,7,2)))=3 THEN 'MA'
when dayofweek(concat(substr(r.datetr,1,4),'-',substr(r.datetr,5,2),'-',substr(r.datetr,7,2)))=4 THEN 'ME'
when dayofweek(concat(substr(r.datetr,1,4),'-',substr(r.datetr,5,2),'-',substr(r.datetr,7,2)))=5 THEN 'JE'
when dayofweek(concat(substr(r.datetr,1,4),'-',substr(r.datetr,5,2),'-',substr(r.datetr,7,2)))=6 THEN 'VE'
when dayofweek(concat(substr(r.datetr,1,4),'-',substr(r.datetr,5,2),'-',substr(r.datetr,7,2)))=7 THEN 'SA'
end)
from*/ tmp_pai_reclamation r
left outer join depot d on lpad(trim(r.eta),3,'0')=d.code
left outer join groupe_tournee g on g.depot_id=d.id and g.code=concat(char(64+d.id),substr(r.codetr,3,1))
left outer join pai_tournee pt 
on concat(substr(r.datetr,1,4),'-',substr(r.datetr,5,2),'-',substr(r.datetr,7,2))=pt.date_distrib
and concat(d.code,'N',lpad(g.code,2,' '),'0',substr(r.codetr,4,2),
case 
when dayofweek(concat(substr(r.datetr,1,4),'-',substr(r.datetr,5,2),'-',substr(r.datetr,7,2)))=1 THEN 'DI'
when dayofweek(concat(substr(r.datetr,1,4),'-',substr(r.datetr,5,2),'-',substr(r.datetr,7,2)))=2 THEN 'LU'
when dayofweek(concat(substr(r.datetr,1,4),'-',substr(r.datetr,5,2),'-',substr(r.datetr,7,2)))=3 THEN 'MA'
when dayofweek(concat(substr(r.datetr,1,4),'-',substr(r.datetr,5,2),'-',substr(r.datetr,7,2)))=4 THEN 'ME'
when dayofweek(concat(substr(r.datetr,1,4),'-',substr(r.datetr,5,2),'-',substr(r.datetr,7,2)))=5 THEN 'JE'
when dayofweek(concat(substr(r.datetr,1,4),'-',substr(r.datetr,5,2),'-',substr(r.datetr,7,2)))=6 THEN 'VE'
when dayofweek(concat(substr(r.datetr,1,4),'-',substr(r.datetr,5,2),'-',substr(r.datetr,7,2)))=7 THEN 'SA'
end
)=pt.code
set r.depot_id=d.id
,r.groupe_id=g.id
,r.tournee_id=pt.id
-- where d.id is not null and g.id is not null and pt.id is not null
-- and d.id=14
-- 
;

select * from tmp_pai_reclamation
where depot_id is null or groupe_id is null or tournee_id is null;

insert into pai_reclamation (tournee_id,nbrec_abonne,nbrec_diffuseur)
select 	tournee_id,sum(nbrecab)-sum(nbannab), sum(nbrecdif)-sum(nbanndif)
from tmp_pai_reclamation
where tournee_id is not null
group by tournee_id
;

insert into pai_incident(tournee_id,incident_id,date_distrib,depot_id,flux_id,utilisateur_id,date_creation)
select 	r.tournee_id,0,pt.date_distrib,pt.depot_id,pt.flux_id,sum(r.indicinc-r.anninc),'2014-08-21'
from tmp_pai_reclamation r
inner join pai_tournee pt on r.tournee_id=pt.id
where r.tournee_id is not null
group by r.depot_id,r.tournee_id
having sum(r.indicinc-r.anninc)>0
;
select * from employe where matricule='7000310300';
select * from pai_tournee where employe_id=1051 and date_distrib='2014-08-21';
select * from pai_reclamation where tournee_id=58082;
select * from pai_ev_diff where typev='PRIME' and diff<>'';
select * from pai_ev_reclamation;
select * from pai_ev_emp_pop where employe_id=1051;
select * from pai_ev_qualite where employe_id=1051;

  SELECT DISTINCT 'PRIME',e.matricule,e.rc,p.poste,e.d,rq.valeur,0,0,p.libelle
  FROM pai_ev_emp_pop e
  INNEr JOIN pai_ev_qualite q ON e.employe_id=q.employe_id and e.d=q.datev
  INNER JOIN pai_ref_postepaie_general p ON p.code='QLT'
  INNER JOIN pai_ref_qualite rq on e.qualite=rq.qualite and e.population_id=rq.population_id
  WHERE q.idtrt=21
  -- On ne génère pas d'ev si pas de tournée semaine
  -- Pas pour les polyvalents
  AND q.qualite between rq.borne_inf AND rq.borne_sup
  and e.employe_id=1051
  ;