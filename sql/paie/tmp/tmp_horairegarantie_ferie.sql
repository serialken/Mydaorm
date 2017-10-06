select pit.anneemois
,pih.employe_id
,e.depot
,e.matricule
,e.nom
,e.prenom1
,pt.date_distrib
,epd.nbheures_garanties
,pai_nbjours_cycle(epd.employe_id,epd.date_debut,prm.date_debut,prm.date_fin) as nbjours_cycle
,time_to_sec(pih.hgaranties)/3600 as hgaranties_payees
,pai_horaire_moyen_float(epd.employe_id,epd.date_debut,epd.date_fin,epd.nbheures_garanties,prm.date_debut,prm.date_fin) as horaire_moyen
,pt.duree
,round(pai_horaire_moyen_float(epd.employe_id,epd.date_debut,epd.date_fin,epd.nbheures_garanties,prm.date_debut,prm.date_fin)-pt.duree,2) as a_payer
from pai_int_traitement pit
inner join pai_int_oct_hgaranties pih on pit.id=pih.idtrt
inner join pai_ref_mois prm on prm.anneemois=pit.anneemois
inner join (
    select pt.idtrt,pt.employe_id,pt.date_distrib,sum(time_to_sec(pt.duree))/3600 as duree
    from (
    select pt.idtrt,pt.employe_id,pt.date_distrib,pt.duree from pai_ev_tournee_hst pt where pt.date_distrib in ('20151225','20160101','20160328','20160505','20160815')
    union
    select pt.idtrt,pt.employe_id,pt.date_distrib,pt.duree from pai_ev_activite_hst pt where pt.date_distrib in ('20151225','20160101','20160328','20160505','20160815')
    and pt.activite_id not in (-1,-10)
    ) as pt
    group by pt.idtrt,pt.employe_id,pt.date_distrib
) as pt on pit.id=pt.idtrt and pih.employe_id=pt.employe_id
inner join emp_pop_depot epd on epd.employe_id=pih.employe_id and pt.date_distrib between epd.date_debut and epd.date_fin
inner join v_employe e on epd.employe_id=e.employe_id and epd.date_debut=e.date_debut
where pit.typetrt like '%CLOTURE%'
and pit.anneemois>='201512' and pit.flux_id=2
and pih.hgaranties>'00:00:00'
and pt.duree<pai_horaire_moyen_float(epd.employe_id,epd.date_debut,epd.date_fin,epd.nbheures_garanties,prm.date_debut,prm.date_fin)
order by e.depot,e.nom,pt.date_distrib

drop table tmp_ev;
create table tmp_ev as
select e.matricule,e.rc,least('2016-08-20',epd.fRC) as datev
,round(sum(pai_horaire_moyen_float(epd.employe_id,epd.date_debut,epd.date_fin,epd.nbheures_garanties,prm.date_debut,prm.date_fin)-pt.duree),2) as qte
,0 as taux
,0 as val
, null as ordre
,'CPHG' as poste
-- ,e.depot,e.nom
from pai_int_traitement pit
inner join pai_int_oct_hgaranties pih on pit.id=pih.idtrt
inner join pai_ref_mois prm on prm.anneemois=pit.anneemois
inner join (
    select pt.idtrt,pt.employe_id,pt.date_distrib,sum(time_to_sec(pt.duree))/3600 as duree
    from (
    select pt.idtrt,pt.employe_id,pt.date_distrib,pt.duree from pai_ev_tournee_hst pt where pt.date_distrib in ('20151225','20160101','20160328','20160505','20160815')
    union
    select pt.idtrt,pt.employe_id,pt.date_distrib,pt.duree from pai_ev_activite_hst pt where pt.date_distrib in ('20151225','20160101','20160328','20160505','20160815')
    and pt.activite_id not in (-1,-10)
    ) as pt
    group by pt.idtrt,pt.employe_id,pt.date_distrib
) as pt on pit.id=pt.idtrt and pih.employe_id=pt.employe_id
inner join emp_pop_depot epd on epd.employe_id=pih.employe_id and pt.date_distrib between epd.date_debut and epd.date_fin
inner join v_employe e on epd.employe_id=e.employe_id and epd.date_debut=e.date_debut
where pit.typetrt like '%CLOTURE%'
and pit.anneemois>='201512' and pit.flux_id=2
and pih.hgaranties>'00:00:00'
and pt.duree<pai_horaire_moyen_float(epd.employe_id,epd.date_debut,epd.date_fin,epd.nbheures_garanties,prm.date_debut,prm.date_fin)
group by e.matricule,least('2016-08-20',epd.fRC)
order by e.matricule



  select
	concat_ws('|'
		,'XEvFactory     '
		,'C'
		,'MROAD          '
		,rpad(trim(e.matricule),8,'Z')
		,coalesce(e.rc,'900001')
		,rpad(e.poste,10,' ')
		,date_format(e.datev,'%Y%m%d')        			-- dateEffet
		,coalesce(concat(' ',lpad(e.ordre,2,'0')),'   ')	-- noOrdre
		,'        '   			-- dateFin
		,'EVPORTAGE '  			-- TA_RgpNatEvGenW
		,'1|0|0|0|0|0'
		,concat(' ',lpad(round(e.qte ,2),11,'0'))
		,concat(' ',lpad(if(prpg.taux,round(e.taux,3),0),11,'0'))
		,concat(' ',lpad(if(prpg.montant,round(e.val ,2),0),11,'0'))
		,' '          			-- signe
		-- ||'|'||rpad(coalesce(lib,' '),30,' ') 		--lib
		,rpad(' ',30,' ') 			-- lib
		,'      ||      | |    |   |          |0|5|      |   |      |   |      | | ||'
		) as ligne
	from tmp_ev e
  inner join pai_ref_postepaie_general prpg on 'CPHG'=prpg.poste
	-- and (rc<>'900001' or rc is null)
  where datev='2016-08-20'
	order by e.matricule,coalesce(e.rc,'900001'),e.poste,e.datev,coalesce(e.ordre,0)
  
  
select * from pai_int_traitement where typetrt like '%CLOTURE%' order by id desc
select * from pai_ev_hst where idtrt=6949 and poste='CPHG'