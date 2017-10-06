/*
DROP TABLE IF EXISTS `pai_ref_mois`;
CREATE TABLE IF NOT EXISTS `pai_ref_mois` (
  `anneemois` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  `annee` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  `mois` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `libelle` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `date_debut` datetime NOT NULL,
  `date_fin` datetime NOT NULL,
  date_extrait timestamp null,
  PRIMARY KEY (`anneemois`))
 ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `pai_mois`;
CREATE TABLE IF NOT EXISTS `pai_mois` (
  `anneemois` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  `annee` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  `mois` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `libelle` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `date_debut` datetime NOT NULL,
  `date_fin` datetime NOT NULL,
  PRIMARY KEY (`anneemois`))
 ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
*/

 DROP PROCEDURE IF EXISTS init_ref_mois;
create procedure `init_ref_mois`()
 begin
 declare annee int default 2014;
 declare mois int default 1;
 delete from pai_mois;
  while annee<=2029 do
     while mois<=12 do
      insert into pai_ref_mois(anneemois,annee,mois,libelle,date_debut,date_fin,date_extrait) values (
      concat(annee,lpad(mois,2,'00'))
      ,annee
      ,lpad(mois,2,'00')
      ,case mois
      when '01' then 'Janvier'
      when '02' then 'Février'
      when '03' then 'Mars'
      when '04' then 'Avril'
      when '05' then 'Mai'
      when '06' then 'Juin'
      when '07' then 'Juilet'
      when '08' then 'Aout'
      when '09' then 'Septembre'
      when '10' then 'Octobre'
      when '11' then 'Novembre'
      when '12' then 'Décembre'
      end
      ,date_add(date(str_to_date(concat(annee,'-',lpad(mois,2,'00'),'-21'),'%Y-%m-%d')),interval -1 month)
      ,str_to_date(concat(annee,'-',lpad(mois,2,'00'),'-20 23:59:59'),'%Y-%m-%d %H:%i:%s')
      ,null
      );
      set mois=mois+1;
    end while;
    set mois=1;
    set annee=annee+1;
  end while
  ;
  update pai_ref_mois m
  set libelle=concat(m.libelle,concat(' ',m.annee))
  ;
  update pai_ref_mois m
  set date_extrait=sysdate()
  where date_fin<curdate()
  ;
  delete from pai_mois
  ;
  insert into pai_mois(anneemois,annee,mois,libelle,date_debut,date_fin)
  select m.anneemois,m.annee,m.mois,m.libelle,m.date_debut,m.date_fin
  from pai_ref_mois m
  where curdate() between date_debut and date_fin;
end;
/*
truncate table pai_ref_mois;
call init_ref_mois();
select * from pai_ref_mois;
select * from pai_mois;
truncate table pai_mois;
  insert into pai_mois(anneemois,annee,mois,libelle,date_debut,date_fin)
  select m.anneemois,m.annee,m.mois,m.libelle,m.date_debut,m.date_fin
  from pai_ref_mois m
  where '2014-08-21' between date_debut and date_fin;
*/
 