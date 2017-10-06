select * from client_a_servir_logist where pai_tournee_id is not null;
update client_a_servir_logist set pai_tournee_id=null where pai_tournee_id is not null;
truncate table prd_caract_tournee;
delete from pai_prd_tournee;
delete from pai_reclamation;
delete from pai_incident;
delete from pai_journal;
delete from pai_activite;
update pai_tournee set tournee_org_id=null where tournee_org_id is not null;
delete from pai_tournee;
delete from pai_heure;

create table sav_modele_activite as select * from modele_activite;
create table sav_modele_tournee as select * from modele_tournee;
create table sav_modele_tournee_jour as select * from modele_tournee_jour;
create table sav_groupe_tournee as select * from groupe_tournee;

delete from modele_journal;
delete from modele_activite;
delete from modele_tournee_jour;
delete from modele_tournee;
delete from groupe_tournee;

commit;


update modele_tournee set date_creation='2014-08-01';
update modele_tournee_jour set date_creation='2014-08-01';

create table sav_ReferentialTour as select * from ReferentialTour$;
