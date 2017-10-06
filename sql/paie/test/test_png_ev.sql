call INT_MROAD2EV_TEST(28,'2014-08-21','2014-08-27');
select * from pai_int_log where idtrt=28 order by id desc;

select * from pai_ev_tournee;
select * from pai_tournee where date_distrib between '2014-08-21' and '2014-08-27' and employe_id is null and employe_id not in (select id from employe);
select * from pai_tournee where  date_extrait is not null;
select * from pai_prd_tournee order by date_creation desc;
update pai_tournee set date_extrait=null where date_extrait is not null;

-- Lancement oracle
call int_mroad2ev_diff(28,128);
select diff,typev,d.matricule,e.nom,e.prenom1,poste,datev,ordre,libelle,rc,qte_PEPP,qte_MROAD,taux_PEPP,taux_MROAD,val_PEPP,val_MROAD
from pai_ev_diffv d
left outer join employe e on d.matricule=e.matricule
where idtrt1=28 
order by d.matricule,d.datev,d.typev,d.poste;


explain extended
  select *
  FROM pai_ev_produit ppt
  INNER JOIN pai_journal pj on ppt.id=pj.produit_id
  INNER JOIN pai_ref_erreur pe on pj.erreur_id=pe.id 
  WHERE not pe.valide
  ;
explain extended
    select *
  FROM pai_tournee ppt
  INNER JOIN pai_journal pj on ppt.id=pj.tournee_id
  INNER JOIN pai_ref_erreur pe on pj.erreur_id=pe.id 
  WHERE not pe.valide
  ;
rollback;
1	SIMPLE	pe	ALL	PRIMARY,idx1_pai_ref_erreur				24	100	Using where
1	SIMPLE	pj	ref	IDX_8B8D62F7F661D013,IDX_8B8D62F7544202C9,idx1_pai_journal	idx1_pai_journal	5	silog.pe.id	1008	100	Using where
1	SIMPLE	ppt	eq_ref	PRIMARY	PRIMARY	4	silog.pj.tournee_id	1	100	

select * from pai_ev_heure where matricule='7000225300';
select * from pai_tournee where date_distrib between '2014-08-21' and '2014-08-27' and depot_id=14 and employe_id is null;
select * from pai_tournee where date_distrib between '2014-08-21' and '2014-08-27' and depot_id=14 and employe_id not in (select id from employe);
select * from pai_ev_hst where idtrt=128;
-- historisation pour comparaison intra mroad
delete from pai_ev_hst where idtrt in (31,32,33,34,35,36,37);
insert into pai_ev_hst(typev,matricule,rc,poste,datev,ordre,qte,taux,val,libelle,res,idtrt)
select typev,matricule,rc,poste,datev,ordre,qte,taux,val,libelle,res,idtrt+10
from pai_ev_hst  where idtrt in (128);
call ev_diff(31,21);
call ev_diff(32,22);
call ev_diff(33,23);
call ev_diff(34,24);
call ev_diff(35,25);
call int_mroad2ev_diff(38,28);
select diff,typev,matricule,poste,datev,ordre,libelle,rc,qte_PEPP,qte_MROAD,taux_PEPP,taux_MROAD,val_PEPP,val_MROAD from pai_ev_diffv where idtrt1=31 order by matricule,datev,poste;
select diff,typev,matricule,poste,datev,ordre,libelle,rc,qte_PEPP,qte_MROAD,taux_PEPP,taux_MROAD,val_PEPP,val_MROAD from pai_ev_diffv where idtrt1=32 order by matricule,datev,poste;
select diff,typev,matricule,poste,datev,ordre,libelle,rc,qte_PEPP,qte_MROAD,taux_PEPP,taux_MROAD,val_PEPP,val_MROAD from pai_ev_diffv where idtrt1=33 order by matricule,datev,poste;
select diff,typev,matricule,poste,datev,ordre,libelle,rc,qte_PEPP,qte_MROAD,taux_PEPP,taux_MROAD,val_PEPP,val_MROAD from pai_ev_diffv where idtrt1=34 order by matricule,datev,poste;
select diff,typev,matricule,poste,datev,ordre,libelle,rc,qte_PEPP,qte_MROAD,taux_PEPP,taux_MROAD,val_PEPP,val_MROAD from pai_ev_diffv where idtrt1=35 order by matricule,datev,poste;
select diff,typev,matricule,poste,datev,ordre,libelle,rc,qte_PEPP,qte_MROAD,taux_PEPP,taux_MROAD,val_PEPP,val_MROAD from pai_ev_diffv where idtrt1=38 order by matricule,datev,poste;


select * from pai_ev order by matricule,datev;
select * from pai_ev_emp_pop pep
inner join employe e on pep.employe_id=e.id;
select trim(null);


select distinct matricule from pai_ev;
select * from pai_ev_hst where idtrt=85 order by matricule,datev,poste;

call ev_maj_tournee(189,'2014-06-21','2014-07-20');
call ev_calcul_serpentin(110,'N41');

select * from pai_tournee where employe_id=870 order by employe_id;
select * from employe where matricule='7000203300';
select * from pai_ev_produit;
select * from employe where matricule='7000225300';
select * from pai_ev_heure where employe_id=936;
select * from pai_ev_tournee where employe_id=1001;
select * from pai_ev_tournee where typejour_id=3;
select * from pai_tournee where typejour_id=3;
select * from pai_ev_tournee where duree_nuit_modele is not null;
select * from emp_pop_depot where employe_id=197;
select * from pai_ev_activite;

delete from pai_ev where typev='REMUN';
select * from pai_ev where matricule='7000310300';
select * from pai_majoration  where employe_id=870;


diff	typev	matricule	nom	prenom1	poste	datev	ordre	libelle	rc	qte_PEPP	qte_MROAD	taux_PEPP	taux_MROAD	val_PEPP	val_MROAD
<	REMUN	7000203300	DOUSSOT	Nicolas	0500	21/08/2014 00:00:00	2	Rémunération Semaine BDC	900001	9,00	  9,00	  0,131	0,119	1,18	1,07
<	REMUN	7000203300	DOUSSOT	Nicolas	0500	21/08/2014 00:00:00	1	Rémunération Semaine BDC	900001	2,00	  2,00	  0,218	0,199	0,44	0,40
<	REMUN	7000203300	DOUSSOT	Nicolas	0500	21/08/2014 00:00:00	3	Rémunération Semaine BDC	900001	6,00	  6,00	  0,200	0,182	1,20	1,09
<	REMUN	7000203300	DOUSSOT	Nicolas	0503	21/08/2014 00:00:00	3	Rémunération Semaine BF	  900001	216,00	216,00	0,200	0,182	43,28	39,34
<	REMUN	7000203300	DOUSSOT	Nicolas	0503	21/08/2014 00:00:00	2	Rémunération Semaine BF	  900001	952,00	952,00	0,131	0,119	124,56	113,24
<	REMUN	7000203300	DOUSSOT	Nicolas	0503	21/08/2014 00:00:00	1	Rémunération Semaine BF	  900001	199,00	199,00	0,218	0,199	43,46	39,51  
  select 
  concat_ws('|'
,'EvFactoryW     '
,'C'
,'Pepp           '
,rpad(trim(matricule),8,'Z')
,coalesce(rc,'900001')
,rpad(case when poste='9248' THEN 'KMSEMAIJFR' when poste='9250' THEN 'KMDIMANCHE' else poste end,10,' ')
,date_format(datev,'%Y%m%d')        			-- dateEffet
,coalesce(concat(' ',lpad(ordre,2,'0')),'   ')	-- noOrdre
,'        '   			-- dateFin
,'EVPORTAGE '  			-- TA_RgpNatEvGenW
,'1|0|0|0|0|0'
,concat(' ',lpad(round(qte ,2),11,'0'))
,concat(' ',lpad(round(taux,3),11,'0'))
,concat(' ',lpad(round(val ,2),11,'0'))
,' '          			-- signe
-- ||'|'||rpad(coalesce(lib,' '),30,' ') 		--lib
,rpad(' ',30,' ') 			-- lib
,'      ||      | |    |   |          |0|5|      |   |      |   |      | | ||'
)
from pai_ev e
-- and (rc<>'900001' or rc is null)
order by matricule,coalesce(rc,'900001'),poste,datev,coalesce(ordre,0);

-- supplement

