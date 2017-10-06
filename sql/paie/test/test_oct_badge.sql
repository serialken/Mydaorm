CALL int_oct_badge(@_idtrt,0,FALSE);
CALL int_oct_cycle(@_idtrt,0,FALSE);
SELECT * FROM pai_int_log WHERE idtrt=@_idtrt;
SELECT * FROM pai_int_erreur WHERE idtrt=@_idtrt and eta='034';
-- journalier
select b.date_distrib,b.matricule,e.nom,e.prenom1
,SEC_TO_TIME(TIME_TO_SEC(b.heure_fin_oct)-TIME_TO_SEC(b.heure_debut_oct)) as pepp
,SEC_TO_TIME(TIME_TO_SEC(b.heure_fin_mroad)-TIME_TO_SEC(b.heure_debut_mroad)) as mroad
,SEC_TO_TIME(TIME_TO_SEC(b.heure_fin_mroad)-TIME_TO_SEC(b.heure_debut_mroad)-TIME_TO_SEC(b.heure_fin_oct)+TIME_TO_SEC(b.heure_debut_oct)) as dif
from pai_int_oct_badge b
inner join employe e on b.matricule=e.matricule
where eta='034' and date_distrib<='2014-08-27' 
order by date_distrib,nom;
-- mensuel
select b.matricule,e.nom,e.prenom1
,SEC_TO_TIME(sum(TIME_TO_SEC(b.heure_fin_oct)-TIME_TO_SEC(b.heure_debut_oct))) as pepp
,SEC_TO_TIME(sum(TIME_TO_SEC(b.heure_fin_mroad)-TIME_TO_SEC(b.heure_debut_mroad))) as mroad
,SEC_TO_TIME(sum(TIME_TO_SEC(b.heure_fin_mroad)-TIME_TO_SEC(b.heure_debut_mroad)-TIME_TO_SEC(b.heure_fin_oct)+TIME_TO_SEC(b.heure_debut_oct))) as dif
from pai_int_oct_badge b
inner join employe e on b.matricule=e.matricule
where eta='034' and date_distrib<='2014-08-27' 
group by e.id
order by nom;
-- HTPX mensuel
select b.matricule,e.nom,e.prenom1
,SEC_TO_TIME(sum(TIME_TO_SEC(b.heure_fin_oct)-TIME_TO_SEC(b.heure_debut_oct))) as pepp
,SEC_TO_TIME(sum(TIME_TO_SEC(b.heure_fin_mroad)-TIME_TO_SEC(b.heure_debut_mroad))) as mroad
,SEC_TO_TIME(sum(TIME_TO_SEC(b.heure_fin_mroad)-TIME_TO_SEC(b.heure_debut_mroad)-TIME_TO_SEC(b.heure_fin_oct)+TIME_TO_SEC(b.heure_debut_oct))) as dif
,qte as ev
,sec_to_time(qte*3600) as ev2
,qte*3600-sum(TIME_TO_SEC(b.heure_fin_mroad)-TIME_TO_SEC(b.heure_debut_mroad)) as dif
from pai_int_oct_badge b
inner join employe e on b.matricule=e.matricule
left outer join pai_ev p on p.matricule=e.matricule and p.poste='HTPX'
where eta='034' and date_distrib<='2014-08-27' 
group by e.id
order by nom;


select nom,qte,sec_to_time(qte*3600) from pai_ev p inner join employe e on p.matricule=e.matricule where poste='HTPX' order by nom;


-- badge
select date_distrib,employe_id,matricule,heure_debut,duree from pai_oct_heure where matricule='Z004957000' and date_distrib<='2014-08-27'  
union all
select null,employe_id,matricule,null,sec_to_time(sum(time_to_sec(duree))) from pai_oct_heure where matricule='Z004957000' and date_distrib<='2014-08-27' group by employe_id,matricule,eta
order by date_distrib,heure_debut;
-- ev
select date_distrib,employe_id,duree,null,null from pai_ev_heure where employe_id=666 and date_distrib<='2014-08-27'  
union all
select null,employe_id,sec_to_time(sum(time_to_sec(duree))),sum(time_to_sec(duree))/3600,sec_to_time(round(sum(time_to_sec(duree))/3600,2)*3600) from pai_ev_heure where employe_id=666 and date_distrib<='2014-08-27' group by employe_id
order by 1;
