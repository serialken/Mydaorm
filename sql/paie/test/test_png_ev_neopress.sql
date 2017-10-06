select * from pai_int_traitement where id=50;
call int_mroad2ev_test(50,'2014-08-21','2014-08-27');
select * from pai_int_log where idtrt =50 order by id asc;
call int_mroad2ev_test(51,'2014-08-21','2014-08-27');
select * from pai_int_log where idtrt =51 order by id asc; -- 20140918
call int_mroad2ev_test(52,'2014-08-21','2014-08-27');
select * from pai_int_log where idtrt =52 order by id asc; -- 20140918
call int_mroad2ev_test(53,'2014-08-21','2014-08-27');
select * from pai_int_log where idtrt =53 order by id asc; -- 20140922
call int_mroad2ev_test(54,'2014-08-21','2014-08-27');
select * from pai_int_log where idtrt =54 order by id asc; -- 20140923

call int_mroad2ev_diff(50,52);
select diff,typev,d.matricule,e.nom,e.prenom1,poste,datev,ordre,libelle,rc,qte_PEPP,qte_MROAD,taux_PEPP,taux_MROAD,val_PEPP,val_MROAD from pai_ev_diffv d
left outer join employe e on d.matricule=e.matricule
where idtrt1=50
and  d.matricule='NEP0145700'
order by d.matricule,d.datev,d.typev,d.poste;

225,00	0,175 BDC
320,00	0,175 BF
154,00	0,202
321,00	0,202
;
select * from produit where id in (101,102);
update prd_caract_constante set valeur_string='F' where prd_caract_id in (14,15,16) and produit_id in (101,102);
select * from pai_ref_postepaie_general order by id desc;

select * from employe where matricule like 'NEP01457%';
select * from pai_tournee where employe_id=6679;
select * from pai_ev_hst where matricule='NEP0145700' and idtrt=51 order by poste,idtrt;
select * from pai_ev_hst where matricule='NEP0145700' and idtrt=52 order by poste,idtrt;
select * from pai_ev_hst where matricule='NEP0145700' and idtrt=53 order by poste,idtrt;
select * from pai_ev_hst where matricule='NEP0145700';


select pt.date_distrib, pt.valrem,ppt.*  ,pcj.valeur_int,pcj.id,p.*
from pai_prd_tournee ppt
inner join pai_tournee pt on ppt.tournee_id=pt.id and pt.employe_id=6679 and pt.date_distrib between '2014-08-21' and '2014-08-27'
inner join produit p on ppt.produit_id=p.id
left outer join prd_caract pc on p.type_id=pc.produit_type_id and pc.code='POIDS'
left outer join prd_caract_jour pcj on pc.id = pcj.prd_caract_id and pcj.date_distrib=pt.date_distrib and pcj.produit_id=p.id
-- where pt.valrem=0.1747
-- where pt.valrem>0.1747
where  ppt.produit_id=243; -- produit specieal lookbook sarenza 250g
;
 select * from prd_caract_constante where prd_caract_id in (14,15,16) and produit_id in (101,102);
    commit;
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
-- where matricule='NEP0145700'
-- and (rc<>'900001' or rc is null)
order by matricule,coalesce(rc,'900001'),poste,datev,coalesce(ordre,0);