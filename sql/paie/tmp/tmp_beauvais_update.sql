update modele_tournee_jour mtj
inner join tmp_etalon_51 te on mtj.code like concat('051','NVA0',substring(te.tournee,2,2),'%')
set mtj.valrem=te.etalon
where te.etalon is not null

update modele_tournee_jour mtj
inner join tmp_etalon_51 te on mtj.code like concat('051','NVA0',substring(te.tournee,2,2),'DI')
set mtj.nbkm=te.dim, mtj.nbkm_paye=te.dim
where te.dim is not null

update modele_tournee_jour mtj
inner join tmp_etalon_51 te on mtj.code like concat('051','NVA0',substring(te.tournee,2,2),'LU')
set mtj.nbkm=te.lun, mtj.nbkm_paye=te.lun
where te.lun is not null

update modele_tournee_jour mtj
inner join tmp_etalon_51 te on mtj.code like concat('051','NVA0',substring(te.tournee,2,2),'MA')
set mtj.nbkm=te.mar, mtj.nbkm_paye=te.mar
where te.mar is not null

update modele_tournee_jour mtj
inner join tmp_etalon_51 te on mtj.code like concat('051','NVA0',substring(te.tournee,2,2),'ME')
set mtj.nbkm=te.mer, mtj.nbkm_paye=te.mer
where te.mer is not null

update modele_tournee_jour mtj
inner join tmp_etalon_51 te on mtj.code like concat('051','NVA0',substring(te.tournee,2,2),'JE')
set mtj.nbkm=te.jeu, mtj.nbkm_paye=te.jeu
where te.jeu is not null

update modele_tournee_jour mtj
inner join tmp_etalon_51 te on mtj.code like concat('051','NVA0',substring(te.tournee,2,2),'VE')
set mtj.nbkm=te.ven, mtj.nbkm_paye=te.ven
where te.ven is not null

update modele_tournee_jour mtj
inner join tmp_etalon_51 te on mtj.code like concat('051','NVA0',substring(te.tournee,2,2),'SA')
set mtj.nbkm=te.sam, mtj.nbkm_paye=te.sam
where te.sam is not null


alter table tmp_etalon_51 add employe_id int

update tmp_etalon_51 te
inner join employe e on upper(te.nom)=upper(concat(e.nom,' ',e.prenom1))
set employe_id=e.id
update tmp_etalon_51 te set employe_id=9105 where tournee='A04'
update tmp_etalon_51 te set employe_id=9112 where tournee='A05'
update tmp_etalon_51 te set employe_id=9116 where tournee='A09'
update tmp_etalon_51 te set employe_id=9106 where tournee='A18'
update tmp_etalon_51 te set employe_id=9108 where tournee='A19'
update tmp_etalon_51 te set employe_id=9118 where tournee='A33'
update tmp_etalon_51 te set employe_id=9106 where tournee='A42'
update tmp_etalon_51 te set employe_id=9165 where tournee='A07'
update tmp_etalon_51 te set employe_id=9171 where tournee='A08'
update tmp_etalon_51 te set employe_id=9169 where tournee='A15'
update tmp_etalon_51 te set employe_id=9167 where tournee='A37'
update tmp_etalon_51 te set employe_id=9163 where tournee='A35'
update tmp_etalon_51 te set employe_id=9167 where tournee='A17'
update tmp_etalon_51 te set employe_id=9166 where tournee='A06'
update tmp_etalon_51 te set employe_id=9113 where tournee='A17'

select * from tmp_etalon_51 where employe_id is null and nom is not null
select * from employe where date_creation>'2016-01-14' order by nom
select * from employe where date_creation>'2016-01-14' order by prenom1
select * from employe where nom='TOMAZ'
where upper(nom)='BEAURAIN'

select te.tournee,te.nom,e.nom,e.prenom1
from tmp_etalon_51 te
inner join employe e on te.employe_id=e.id
where tournee in ('A06','A17')


update modele_tournee_jour mtj
inner join tmp_etalon_51 te on mtj.code like concat('051','NVA0',substring(te.tournee,2,2),'%')
set mtj.employe_id=te.employe_id
where te.employe_id is not null and mtj.employe_id is null

update pai_tournee mtj
inner join tmp_etalon_51 te on mtj.code like concat('051','NVA0',substring(te.tournee,2,2),'%')
set mtj.employe_id=te.employe_id
where te.employe_id is not null and mtj.employe_id is null
