ALTER TABLE pai_ref_ferie ADD societe_id INT NOT NULL;
ALTER TABLE pai_ref_ferie ADD CONSTRAINT FK_F20995B3FCF77503 FOREIGN KEY (societe_id) REFERENCES ref_emp_societe (id);
update pai_ref_ferie set societe_id=1;
select * from pai_ref_ferie
insert into pai_ref_ferie(jfdate,societe_id) select jfdate,2 from pai_ref_ferie
delete from pai_ref_ferie where societe_id=2 and jfdate='2016-11-01'
alter table pai_ref_calendrier drop typejour_id

-- DROP INDEX IDX_460ECA8101629AE ON etalon_tournee;
ALTER TABLE etalon_tournee DROP modele_tournee_jour_id;
ALTER TABLE pai_prd_tournee DROP date_validation;
DROP INDEX pai_ref_ferie_idx1 ON pai_ref_ferie;
CREATE UNIQUE INDEX pai_ref_ferie_uni1 ON pai_ref_ferie (societe_id, jfdate);
-- ALTER TABLE pai_tournee DROP typetournee_id;
ALTER TABLE ref_emp_societe CHANGE id id INT AUTO_INCREMENT NOT NULL;
ALTER TABLE ref_emp_societe AUTO_INCREMENT = 3,  CHANGE id id INT(11) AUTO_INCREMENT NOT NULL;
select * from ref_emp_societe

update pai_ref_erreur set rubrique='AO' where id=50

/*
INSERT INTO `page` (`ID`, `ID_ROUTE`, `DESC_COURT`, `DESCRIPTION`, `MENU`, `PAG_DEFAUT`, `SS_CAT_ID`) 
values (9115, 'liste_pai_refferie', 'Jours Feriés', 'Référentiel - Jours Feriés', '1', NULL, 910);

INSERT INTO `page_element` (`id`, `pag_id`, `desc_court`, `libelle`, `oblig`) VALUES
(91150, 9115, 'VISU', 'Visualisation', 1),
(91151, 9115, 'MODIF', 'Modification', 0);


INSERT INTO `page` (`ID`, `ID_ROUTE`, `DESC_COURT`, `DESCRIPTION`, `MENU`, `PAG_DEFAUT`, `SS_CAT_ID`)  VALUES
(9390, 'liste_pai_int_alimentation', 'Alimentation', 'Interface - Alimentation', '1', NULL, 930);

INSERT INTO `page_element` (`id`, `pag_id`, `desc_court`, `libelle`, `oblig`) VALUES
(93900, 9390, 'VISU', 'Visualisation', 1);

*/

select * from pai_tournee where date_distrib='2016-11-01' and flux_id=2