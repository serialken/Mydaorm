
alter table employe add nom_patronymique varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL;

select * from employe where matricule like 'MR%' order by nom;
update employe set nom_patronymique='BELAIZI' where id=10487;
update employe set nom='M''BODJI' where id=10317;
update employe set prenom1='OUMAR' where id=10317;
update employe set nom_patronymique='CHBEIR' where id=10309;
update employe set nom_patronymique='SEEL' where id=10318;


