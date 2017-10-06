call mt_transferer(@validation_id,7,3604,474,'079','2017-02-26')
select * from modele_tournee where id=474
select * from modele_tournee where code='039NQD001'
select * from modele_tournee_transfert where tournee_id_init in (3602,3604) or tournee_id_future in (3602,3604)

select * from modele_tournee_transfert where tournee_id_init in (3602,3604) or tournee_id_future in (3602,3604)

create table modele_tournee_transfert_tmp as select * from modele_tournee_transfert where tournee_id_init in (3602,3604) or tournee_id_future in (3602,3604)
delete from modele_tournee_transfert where tournee_id_init in (3602,3604) or tournee_id_future in (3602,3604)

call mt_transferer(@validation_id,7,3602,474,'078','2017-02-26')
select * from modele_tournee where code='039NQD001'
