UPDATE produit p
INNER JOIN prd_caract pc ON p.type_id=pc.produit_type_id
INNer JOIN prd_caract_constante pcc on p.id=pcc.produit_id and pc.id=pcc.prd_caract_id
set pcc.valeurs_string=''
where p.id=;


DELETE FROM prd_caract;
INSERT INTO prd_caract(produit_type_id,caract_type_id,saisie_id,libelle,actif,CODE)
SELECT pt.id
,1 -- entier
,2 -- jour
,'Poids'
,TRUE
,'POIDS'
FROM produit_type pt;

INSERT INTO prd_caract(produit_type_id,caract_type_id,saisie_id,libelle,actif,CODE)
SELECT pt.id
,3 -- chaine
,1 -- constante
,'Type Urssaf'
,TRUE
,'URSSAF'
FROM produit_type pt
WHERE pt.id IN (1,2,3);

DELETE FROM prd_caract_constante WHERE prd_caract_id IN (SELECT id FROM prd_caract WHERE CODE='URSSAF');
INSERT INTO prd_caract_constante(produit_id,prd_caract_id,utilisateur_id,valeur_string,date_creation)
SELECT p.id,pc.id,0,'F',CURDATE()
FROM produit p
INNER JOIN prd_caract pc ON p.type_id=pc.produit_type_id
WHERE pc.code='URSSAF'
AND p.libelle NOT IN ('Investir','News Bourse','Voici','Assimilé Parisien','(M) Gala','Gala','La Gazette Drouot','Gazette De L''hotel Drouot','VSD','Velo Magazine','Elle','(M) Elle');

INSERT INTO prd_caract_constante(produit_id,prd_caract_id,utilisateur_id,valeur_string,date_creation)
SELECT p.id,pc.id,0,'R',CURDATE()
FROM produit p
INNER JOIN prd_caract pc ON p.type_id=pc.produit_type_id
WHERE pc.code='URSSAF'
AND p.libelle IN ('Investir','News Bourse','Voici','Assimilé Parisien','(M) Gala','Gala','La Gazette Drouot','Gazette De L''hotel Drouot','VSD','Velo Magazine','Elle','(M) Elle');

INSERT INTO prd_caract(produit_type_id,caract_type_id,saisie_id,libelle,actif,CODE)
SELECT pt.id
,1 -- entier
,1 -- constante
,'Titre Dimanche'
,TRUE
,'DIMANCHE'
FROM produit_type pt
WHERE pt.id IN (1,2,3);


DELETE FROM prd_caract_constante WHERE prd_caract_id IN (SELECT id FROM prd_caract WHERE CODE='DIMANCHE');
INSERT INTO prd_caract_constante(produit_id,prd_caract_id,utilisateur_id,valeur_string,date_creation)
SELECT p.id,pc.id,0,'N',CURDATE()
FROM produit p
INNER JOIN prd_caract pc ON p.type_id=pc.produit_type_id
WHERE pc.code='DIMANCHE'
AND p.libelle NOT IN ('Journal Du Dimanche','Le Parisien Dim. Oise','Le Parisien Dim. IDF');

INSERT INTO prd_caract_constante(produit_id,prd_caract_id,utilisateur_id,valeur_string,date_creation)
SELECT p.id,pc.id,0,'O',CURDATE()
FROM produit p
INNER JOIN prd_caract pc ON p.type_id=pc.produit_type_id
WHERE pc.code='DIMANCHE'
AND p.libelle IN ('Journal Du Dimanche','Le Parisien Dim. Oise','Le Parisien Dim. IDF');

SELECT * FROM produit WHERE UPPER(libelle) LIKE '%DIMANCHE%';

select *
from produit