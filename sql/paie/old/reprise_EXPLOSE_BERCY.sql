DELIMITER |

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS mt_dupliquer|
create procedure mt_dupliquer(
    IN		_libelle		VARCHAR(128),
    IN		_groupe_id	INT,
    IN		_numero			VARCHAR(3),
    IN    _date_debut DATE
) begin
declare new_id int;
declare _validation_id INT;
DECLARE EXIT      HANDLER FOR SQLEXCEPTION  ROLLBACK;

START TRANSACTION;
  insert into modele_tournee(groupe_id,utilisateur_id,actif,typetournee_id,employe_id,numero,codeDCS,libelle)
  select _groupe_id,0,true,mt.typetournee_id,mt.employe_id,_numero,mt.codeDCS,mt.libelle
  from modele_tournee mt
  where mt.libelle=_libelle;

  set new_id:=LAST_INSERT_ID();
  call mod_valide_tournee(_validation_id,new_id);
  
  update modele_tournee_jour mtj
  inner join modele_tournee mt on mtj.tournee_id=mt.id
  set mtj.date_fin=adddate(_date_debut,interval -1 day)
  where mt.libelle=_libelle;
  
  insert into modele_tournee_jour(tournee_id,jour_id,employe_id,transport_id,utilisateur_id,date_debut,date_fin,duree_geo,dureehp_debut_geo,dureehp_fin_geo,qte_geo,nbcli_geo,nbkm_geo,nbadr_geo,valrem,duree,dureeHP,nbkm,nbkm_paye,nbkm_hp_debut_geo,nbkm_hp_fin_geo)
  select new_id,mtj.jour_id,mtj.employe_id,mtj.transport_id,0,_date_debut,'2999-01-01',mtj.duree_geo,mtj.dureehp_debut_geo,mtj.dureehp_fin_geo,mtj.qte_geo,mtj.nbcli_geo,mtj.nbkm_geo,mtj.nbadr_geo,mtj.valrem,mtj.duree,mtj.dureeHP,mtj.nbkm,mtj.nbkm_paye,mtj.nbkm_hp_debut_geo,mtj.nbkm_hp_fin_geo
  from modele_tournee_jour mtj
  inner join modele_tournee mt on mtj.tournee_id=mt.id
  where mt.libelle=_libelle;

  -- call mod_valide_tournee_jour(_validation_id,new_id);
COMMIT;
end|

/*
DELIMITER ;
select distinct groupe_id from modele_tournee where libelle like '93S%';
select distinct depot_id from groupe_tournee where id in (424);
select * from ReferentialTour$ where tournee like '93S%';


select * from modele_tournee where libelle='93LSN001A0';
select * from modele_tournee_jour mtj inner join modele_tournee mt on mtj.tournee_id=mt.id where libelle='93LSN001A0';
select * from modele_tournee where libelle='93LSN001AD';
select * from modele_tournee_jour mtj inner join modele_tournee mt on mtj.tournee_id=mt.id where libelle='93LSN001AD';
select * from modele_tournee where libelle='93LSN002BD';
select * from modele_tournee_jour mtj inner join modele_tournee mt on mtj.tournee_id=mt.id where libelle='93LSN002BD';

update modele_tournee set numero='042' where id=1953;
update modele_tournee_jour set date_fin='2014-09-30' where tournee_id=1762;
update modele_tournee_jour set date_debut='2014-10-01' where tournee_id>1952;
update modele_tournee set code=code;

select * from modele_tournee_jour mtj where tournee_id=1760;
select adddate('2014-10-01',interval -1 day);
*/

/*
-- chapelle -> bercy 24 408
call mt_dupliquer('93LSN001A0',408,'040','2014-10-01');
call mt_dupliquer('93LSN001AD',408,'041','2014-10-01');
call mt_dupliquer('93LSN002BD',408,'042','2014-10-01');
call mt_dupliquer('93LSN003FA',408,'043','2014-10-01');
call mt_dupliquer('93LSN003FC',408,'044','2014-10-01');
call mt_dupliquer('93LSN003FD',408,'045','2014-10-01');
call mt_dupliquer('93LSN004EA',408,'046','2014-10-01');
call mt_dupliquer('93LSN004EC',408,'047','2014-10-01');
call mt_dupliquer('93LSN004ED',408,'048','2014-10-01');

-- chapelle -> st ouen
call mt_dupliquer('93LSN009GA',387,'023','2014-10-01');
call mt_dupliquer('93LSN009GB',387,'024','2014-10-01');
call mt_dupliquer('93LSN009GC',387,'025','2014-10-01');
call mt_dupliquer('93LSN009GD',387,'026','2014-10-01');
call mt_dupliquer('93LSN010HA',387,'027','2014-10-01');
call mt_dupliquer('93LSN010HB',387,'028','2014-10-01');
call mt_dupliquer('93LSN010HC',387,'029','2014-10-01');
call mt_dupliquer('93LSN010HD',387,'030','2014-10-01');
call mt_dupliquer('93LSN017RA',387,'031','2014-10-01');
call mt_dupliquer('93LSN017RB',387,'032','2014-10-01');
call mt_dupliquer('93LSN017RC',387,'033','2014-10-01');
call mt_dupliquer('93LSN017RD',387,'034','2014-10-01');
call mt_dupliquer('93LSN017RE',387,'035','2014-10-01');
call mt_dupliquer('93LSN018SA',387,'036','2014-10-01');
call mt_dupliquer('93LSN018SB',387,'037','2014-10-01');
call mt_dupliquer('93LSN018SC',387,'038','2014-10-01');
call mt_dupliquer('93LSN018SD',387,'039','2014-10-01');
call mt_dupliquer('93LSN018SF',387,'040','2014-10-01');
call mt_dupliquer('93LSN018SG',387,'041','2014-10-01');
call mt_dupliquer('93LSN018SH',387,'042','2014-10-01');
call mt_dupliquer('93LSN019TA',387,'043','2014-10-01');
call mt_dupliquer('93LSN019TB',387,'044','2014-10-01');
call mt_dupliquer('93LSN019TC',387,'045','2014-10-01');
call mt_dupliquer('93LSN019TD',387,'046','2014-10-01');
call mt_dupliquer('93LSN019TE',387,'047','2014-10-01');
call mt_dupliquer('93LSN019TF',387,'048','2014-10-01');
call mt_dupliquer('93LSN019TH',387,'049','2014-10-01');
call mt_dupliquer('93LSN019TI',387,'050','2014-10-01');
call mt_dupliquer('93LSN019TJ',387,'051','2014-10-01');
call mt_dupliquer('93LSND92CL',387,'052','2014-10-01');

-- bercy -> bondy
call mt_dupliquer('75PSN020UA',393,'017','2014-10-01');
call mt_dupliquer('75PSN020UB',393,'018','2014-10-01');
call mt_dupliquer('75PSN020UC',393,'019','2014-10-01');
call mt_dupliquer('75PSN020UD',393,'020','2014-10-01');
call mt_dupliquer('75PSN020UE',393,'021','2014-10-01');
call mt_dupliquer('75PSN020UF',393,'022','2014-10-01');
call mt_dupliquer('75PSN020UG',393,'023','2014-10-01');
call mt_dupliquer('75PSN020UH',393,'024','2014-10-01');
-- chapelle -> nanterre
call mt_dupliquer('93LSN008DA',364,'015','2014-10-01');
call mt_dupliquer('93LSN008DB',364,'016','2014-10-01');
call mt_dupliquer('93LSN008DC',364,'017','2014-10-01');
call mt_dupliquer('93LSN008DD',364,'018','2014-10-01');
call mt_dupliquer('93LSN016QA',364,'019','2014-10-01');
call mt_dupliquer('93LSN016QB',364,'020','2014-10-01');
call mt_dupliquer('93LSN016QC',364,'021','2014-10-01');
call mt_dupliquer('93LSN016QD',364,'022','2014-10-01');
call mt_dupliquer('93LSN016QE',364,'023','2014-10-01');
call mt_dupliquer('93LSN016QF',364,'024','2014-10-01');
call mt_dupliquer('93LSN016QG',364,'025','2014-10-01');
call mt_dupliquer('93LSND92BL',364,'026','2014-10-01');
call mt_dupliquer('93LSND92BN',364,'027','2014-10-01');
call mt_dupliquer('93LSND92BO',364,'028','2014-10-01');
call mt_dupliquer('93LSND92CA',364,'029','2014-10-01');
call mt_dupliquer('93LSND92CB',364,'030','2014-10-01');
call mt_dupliquer('93LSND92NE',364,'031','2014-10-01');
call mt_dupliquer('93LSND92NY',364,'032','2014-10-01');
call mt_dupliquer('93LSND92SU',364,'033','2014-10-01');
call mt_dupliquer('93LSND92DF',364,'034','2014-10-01');
-- bercy -> arceuil
call mt_dupliquer('75PSND92MV',431,'001','2014-10-01');
call mt_dupliquer('75PSN015VA',431,'002','2014-10-01');
call mt_dupliquer('75PSN015VB',431,'003','2014-10-01');
call mt_dupliquer('75PSN015VC',431,'004','2014-10-01');
call mt_dupliquer('75PSN015VD',431,'005','2014-10-01');
call mt_dupliquer('75PSN015VE',431,'006','2014-10-01');
call mt_dupliquer('75PSN015VF',431,'007','2014-10-01');
call mt_dupliquer('75PSN015VG',431,'008','2014-10-01');
call mt_dupliquer('75PSN015VH',431,'009','2014-10-01');
-- chapelle -> arceuil
call mt_dupliquer('93LSND92IM',430,'001','2014-10-01');
call mt_dupliquer('93LSND92IS',430,'002','2014-10-01');
*/

/*
-- bercy 24 408
select * from groupe_tournee where code='T3';
update modele_tournee set groupe_id=424,numero='001' where libelle='93LSN001A0';
update modele_tournee set groupe_id=425,numero='001' where libelle='93LSN001AD';
update modele_tournee set groupe_id=424,numero='002' where libelle='93LSN002BD';
update modele_tournee set groupe_id=424,numero='003' where libelle='93LSN003FA';
update modele_tournee set groupe_id=426,numero='001' where libelle='93LSN003FC';
update modele_tournee set groupe_id=426,numero='002' where libelle='93LSN003FD';
update modele_tournee set groupe_id=424,numero='004' where libelle='93LSN004EA';
update modele_tournee set groupe_id=425,numero='002' where libelle='93LSN004EC';
update modele_tournee set groupe_id=425,numero='003' where libelle='93LSN004ED';

-- la chapelle saount 387
select * from depot;
select * from groupe_tournee where code='RA'; -- 387
select * from groupe_tournee where code='KC'; -- 431
select * from groupe_tournee where code='KB'; -- 430
select * from modele_tournee where groupe_id=431;
update modele_tournee set groupe_id=425,numero='004' where libelle='93LSN009GA';
update modele_tournee set groupe_id=424,numero='009' where libelle='93LSN009GB';
update modele_tournee set groupe_id=426,numero='003' where libelle='93LSN009GC';
update modele_tournee set groupe_id=424,numero='010' where libelle='93LSN009GD';
update modele_tournee set groupe_id=424,numero='011' where libelle='93LSN010HA';
update modele_tournee set groupe_id=424,numero='012' where libelle='93LSN010HB';
update modele_tournee set groupe_id=424,numero='013' where libelle='93LSN010HC';
update modele_tournee set groupe_id=424,numero='014' where libelle='93LSN010HD';
update modele_tournee set groupe_id=427,numero='001' where libelle='93LSN017RA';
update modele_tournee set groupe_id=425,numero='008' where libelle='93LSN017RB';
update modele_tournee set groupe_id=424,numero='019' where libelle='93LSN017RC';
update modele_tournee set groupe_id=426,numero='004' where libelle='93LSN017RD';
update modele_tournee set groupe_id=425,numero='009' where libelle='93LSN017RE';
update modele_tournee set groupe_id=424,numero='020' where libelle='93LSN018SA';
update modele_tournee set groupe_id=424,numero='021' where libelle='93LSN018SB';
update modele_tournee set groupe_id=426,numero='005' where libelle='93LSN018SC';
update modele_tournee set groupe_id=425,numero='010' where libelle='93LSN018SD';
update modele_tournee set groupe_id=425,numero='011' where libelle='93LSN018SF';
update modele_tournee set groupe_id=425,numero='012' where libelle='93LSN018SG';
update modele_tournee set groupe_id=425,numero='013' where libelle='93LSN018SH';
update modele_tournee set groupe_id=424,numero='022' where libelle='93LSN019TA';
update modele_tournee set groupe_id=424,numero='023' where libelle='93LSN019TB';
update modele_tournee set groupe_id=424,numero='024' where libelle='93LSN019TC';
update modele_tournee set groupe_id=424,numero='025' where libelle='93LSN019TD';
update modele_tournee set groupe_id=425,numero='014' where libelle='93LSN019TE';
update modele_tournee set groupe_id=424,numero='026' where libelle='93LSN019TF';
update modele_tournee set groupe_id=424,numero='027' where libelle='93LSN019TH';
update modele_tournee set groupe_id=425,numero='015' where libelle='93LSN019TI';
update modele_tournee set groupe_id=424,numero='028' where libelle='93LSN019TJ';
update modele_tournee set groupe_id=424,numero='032' where libelle='93LSND92CL';

-- bercy - bondy 19 393
select * from depot;
select * from groupe_tournee where depot_id=19 and code='SP'; -- 393
update modele_tournee set groupe_id=408,numero='031' where libelle='75PSN020UA';
update modele_tournee set groupe_id=408,numero='032' where libelle='75PSN020UB';
update modele_tournee set groupe_id=408,numero='033' where libelle='75PSN020UC';
update modele_tournee set groupe_id=408,numero='034' where libelle='75PSN020UD';
update modele_tournee set groupe_id=408,numero='035' where libelle='75PSN020UE';
update modele_tournee set groupe_id=408,numero='036' where libelle='75PSN020UF';
update modele_tournee set groupe_id=408,numero='037' where libelle='75PSN020UG';
update modele_tournee set groupe_id=408,numero='038' where libelle='75PSN020UH';

-- nanterre 364
select * from groupe_tournee where depot_id=11 and code='JC';
update modele_tournee set groupe_id=424,numero='005' where libelle='93LSN008DA';
update modele_tournee set groupe_id=424,numero='006' where libelle='93LSN008DB';
update modele_tournee set groupe_id=424,numero='007' where libelle='93LSN008DC';
update modele_tournee set groupe_id=424,numero='008' where libelle='93LSN008DD';
update modele_tournee set groupe_id=424,numero='015' where libelle='93LSN016QA';
update modele_tournee set groupe_id=425,numero='005' where libelle='93LSN016QB';
update modele_tournee set groupe_id=425,numero='006' where libelle='93LSN016QC';
update modele_tournee set groupe_id=424,numero='016' where libelle='93LSN016QD';
update modele_tournee set groupe_id=425,numero='007' where libelle='93LSN016QE';
update modele_tournee set groupe_id=424,numero='017' where libelle='93LSN016QF';
update modele_tournee set groupe_id=424,numero='018' where libelle='93LSN016QG';
update modele_tournee set groupe_id=424,numero='029' where libelle='93LSND92BL';
update modele_tournee set groupe_id=425,numero='016' where libelle='93LSND92BN';
update modele_tournee set groupe_id=424,numero='030' where libelle='93LSND92BO';
update modele_tournee set groupe_id=425,numero='017' where libelle='93LSND92CA';
update modele_tournee set groupe_id=424,numero='031' where libelle='93LSND92CB';
update modele_tournee set groupe_id=424,numero='033' where libelle='93LSND92NE';
update modele_tournee set groupe_id=425,numero='018' where libelle='93LSND92NY';
update modele_tournee set groupe_id=424,numero='036' where libelle='93LSND92SU';
update modele_tournee set groupe_id=426,numero='007' where libelle='93LSND92DF';

select * from modele_tournee where libelle='93LSND92NE';


update groupe_tournee set code=code;
*/