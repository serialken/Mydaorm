 select duree, pai_duree(date_distrib,employe_id,valrem,nbcli,nbrep),valrem_majoree,valrem,pt.* 
                from pai_tournee pt
                where duree<>pai_duree(date_distrib,employe_id,valrem,nbcli,nbrep)
                and (tournee_org_id is null or split_id is not null)
                and date_extrait is null
              
 select duree, pai_duree(date_distrib,employe_id,valrem,nbcli,qte),valrem_majoree,valrem,pt.* 
                from pai_ev_tournee_hst pt
                where duree<>pai_duree(date_distrib,employe_id,valrem,nbcli,nbrep)
                and (tournee_org_id is null or split_id is not null)

                select duree, pai_duree(date_distrib,employe_id,valrem_majoree,nbcli,nbrep),valrem_majoree,valrem,pt.* 
                from pai_tournee pt
                where pt.id=690996
                
01:00:06	00:57:45	0,20870	0,20052	681185	6	18	417	6503	3	139	20/02/2015 00:00:00	0,20052	01:00:06	4	4	0	20/02/2015 17:29:39	10/03/2015 17:44:38	21/02/2015 12:48:49	1	2	2	040JTG016VE-B	0,00	24411	0	00:00:00	00:13:15	00:00:00	48	48	48	0	0	14:30:00	53296	680405	1	6336	0,20870	14:30:00	1
select * from pai_ev_tournee_hst where id=681185
/*
update pai_tournee set valrem_majoree=0.34422 where id=690996 -- 0,34422
update pai_tournee set valrem_majoree=0.16091 where valrem_majoree=0.16092 and date_extrait is null
select * from pai_tournee pt
inner join pai_ev_tournee_hst pet on pt.id=pet.id
where pt.duree<>pet.duree
*/
select * from modele_tournee_jour where id=11351
select * from pai_tournee where valrem_majoree=0.16092

                update pai_tournee pt
                set duree=pai_duree(date_distrib,employe_id,valrem,nbcli,nbtitre)
                where duree<>pai_duree(date_distrib,employe_id,valrem,nbcli,nbtitre)
                and (tournee_org_id is null or split_id is not null)
                and date_extrait is null
/*
    UPDATE pai_tournee t
    LEFT OUTER JOIN emp_pop_depot e ON t.employe_id=e.employe_id AND t.date_distrib BETWEEN e.date_debut AND e.date_fin
    LEFT OUTER JOIN pai_majoration pm ON t.date_distrib=pm.date_distrib and t.employe_id=pm.employe_id
  	SET t.valrem_majoree=
      CASE
        WHEN pm.id is null THEN  t.valrem
  	    WHEN pm.typetournee_id=2 THEN round(t.valrem*(1+t.majoration/100)*(1+pm.majoration_poly/100)*(1+pm.majoration_df/100),4)
        WHEN t.nbcli*t.valrem*(1+t.majoration/100)*(1+pm.majoration_poly/100)*(1+pm.majoration_df/100)+(TIME_TO_SEC(t.duree_nuit)/3600)*(pm.majoration_nuit/100)*pm.remuneration>t.nbcli*t.valrem*2 THEN
              round((t.nbcli*t.valrem*(1+t.majoration/100)*(1+pm.majoration_poly/100)*2-(TIME_TO_SEC(t.duree_nuit)/3600)*(pm.majoration_nuit/100)*pm.remuneration)/t.nbcli, 5)
        ELSE
              round(t.valrem*(1+t.majoration/100)*(1+pm.majoration_poly/100)*(1+pm.majoration_df/100), 5)
        END
        where t.valrem_majoree<>
      CASE
        WHEN pm.id is null THEN  t.valrem
  	    WHEN pm.typetournee_id=2 THEN round(t.valrem*(1+t.majoration/100)*(1+pm.majoration_poly/100)*(1+pm.majoration_df/100),4)
        WHEN t.nbcli*t.valrem*(1+t.majoration/100)*(1+pm.majoration_poly/100)*(1+pm.majoration_df/100)+(TIME_TO_SEC(t.duree_nuit)/3600)*(pm.majoration_nuit/100)*pm.remuneration>t.nbcli*t.valrem*2 THEN
              round((t.nbcli*t.valrem*(1+t.majoration/100)*(1+pm.majoration_poly/100)*2-(TIME_TO_SEC(t.duree_nuit)/3600)*(pm.majoration_nuit/100)*pm.remuneration)/t.nbcli, 5)
        ELSE
              round(t.valrem*(1+t.majoration/100)*(1+pm.majoration_poly/100)*(1+pm.majoration_df/100), 5)
        END
        */