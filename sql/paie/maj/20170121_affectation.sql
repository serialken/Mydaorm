INSERT INTO `page` (`ID`, `ID_ROUTE`, `DESC_COURT`, `DESCRIPTION`, `MENU`, `PAG_DEFAUT`, `SS_CAT_ID`) VALUES
(6950, 'liste_employe_transfert', 'Transfert', 'RH - Transfert', '1', NULL, 690);

INSERT INTO `page_element` (`id`, `pag_id`, `desc_court`, `libelle`, `oblig`) VALUES
(69500, 6950, 'VISU', 'Visualisation', 1),
(69501, 6950, 'MODIF', 'Modification', 0);

update profil_page_element set page_elem_id=69500 where page_elem_id=68720;
update profil_page_element set page_elem_id=69501 where page_elem_id=68721;
delete from page_element where pag_id=6872;
delete from page where id=6872;
update page set id_route='liste_employe_transfert' where id=6950;

INSERT INTO `page` (`ID`, `ID_ROUTE`, `DESC_COURT`, `DESCRIPTION`, `MENU`, `PAG_DEFAUT`, `SS_CAT_ID`) VALUES
(6960, 'liste_employe_affectation', 'Affectation', 'RH - Affectation', '1', NULL, 690);

INSERT INTO `page_element` (`id`, `pag_id`, `desc_court`, `libelle`, `oblig`) VALUES
(69600, 6960, 'VISU', 'Visualisation', 1),
(69601, 6960, 'MODIF', 'Modification', 0);

CREATE TABLE emp_affectation (id INT AUTO_INCREMENT NOT NULL, contrat_id INT NOT NULL, flux_id INT NOT NULL, depot_org_id INT NOT NULL, depot_dst_id INT NOT NULL, utilisateur_id INT NOT NULL, date_debut DATE NOT NULL, date_fin DATE NOT NULL, commentaire VARCHAR(1024) DEFAULT NULL, date_creation DATETIME NOT NULL, date_modif DATETIME DEFAULT NULL, INDEX IDX_F60C6E3B1823061F (contrat_id), INDEX IDX_F60C6E3BC85926E (flux_id), INDEX IDX_F60C6E3BBC57BB03 (depot_org_id), INDEX IDX_F60C6E3BA95C9A01 (depot_dst_id), INDEX IDX_F60C6E3BFB88E14F (utilisateur_id), UNIQUE INDEX un_emp_affectation (contrat_id, date_debut), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE emp_affectation ADD CONSTRAINT FK_F60C6E3B1823061F FOREIGN KEY (contrat_id) REFERENCES emp_contrat (id);
ALTER TABLE emp_affectation ADD CONSTRAINT FK_F60C6E3BC85926E FOREIGN KEY (flux_id) REFERENCES ref_flux (id);
ALTER TABLE emp_affectation ADD CONSTRAINT FK_F60C6E3BBC57BB03 FOREIGN KEY (depot_org_id) REFERENCES depot (id);
ALTER TABLE emp_affectation ADD CONSTRAINT FK_F60C6E3BA95C9A01 FOREIGN KEY (depot_dst_id) REFERENCES depot (id);
ALTER TABLE emp_affectation ADD CONSTRAINT FK_F60C6E3BFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id);

ALTER TABLE emp_affectation drop index un_emp_affectation

select * from emp_transfert;
select * from emp_transfert;
select * from emp_contrat where id=223
select * from emp_pop_depot where contrat_id not in (select id from emp_contrat)
select * from emp_contrat

insert into emp_transfert(contrat_id,flux_id,depot_org_id,depot_dst_id,date_debut) values(223,1,1,23,'2010_06_01')
insert into emp_transfert(contrat_id,flux_id,depot_org_id,depot_dst_id,date_debut) values(223,1,1,23,'2010-06-02')
UPDATE emp_transfert et 
                    
                    INNER JOIN (select min_et.id,coalesce((select date_add(min(min_et2.date_debut),interval -1 day) 
                                                    from emp_transfert min_et2
                                                    where min_et.contrat_id=min_et2.contrat_id 
                                                    and min_et2.date_debut>min_et.date_debut),eco.date_fin
                                                    ) as date_fin
                                from emp_transfert min_et
                                inner join emp_contrat eco on min_et.contrat_id=eco.id
                                where min_et.contrat_id=223) as et2 on et.id=et2.id
                    SET et.date_fin=et2.date_fin
                    WHERE et.contrat_id=223;
                    
update emp_transfert et
inner join emp_contrat eco on et.contrat_id=eco.id
left outer join emp_transfert et2 on et.contrat_id=et2.contrat_id and et2.date_debut=eco.date_debut
set et.date_debut=eco.date_debut
where et.date_debut<eco.date_debut
and et2.id is null
