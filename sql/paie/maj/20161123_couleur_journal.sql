alter table pai_ref_erreur add couleur varchar(7);
update pai_ref_erreur set couleur='#F78181' where valide=0;
update pai_ref_erreur set couleur='#F5D5A2' where valide=1 and level in (5,6);
update pai_ref_erreur set couleur='#FFFFFF' where couleur is null;
alter table pai_ref_erreur change couleur couleur varchar(7) not null default '#FFFFFF';
update pai_ref_erreur set level=4,valide=0 where id=50;

alter table modele_ref_erreur add couleur varchar(7);
update modele_ref_erreur set couleur='#F78181' where valide=0;
update modele_ref_erreur set couleur='#F5D5A2' where valide=1 and level in (5,6);
update modele_ref_erreur set couleur='#FFFFFF' where couleur is null;
alter table modele_ref_erreur change couleur couleur varchar(7) not null default '#FFFFFF';