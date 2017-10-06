SET @id=0;
CALL mod_valide_activite(@id,NULL);
COMMIT;


 
 SET @id=NULL;
CALL mod_valide_activite(@id,NULL);
CALL mod_valide_tournee(@id,NULL);
CALL mod_valide_tournee_jour(@id,NULL);
SELECT @id;
COMMIT;
CALL mod_valide_rh(@id);
COMMIT;
SELECT * FROM modele_validation;
SELECT * FROM modele_journal;
SELECT DISTINCT validation_id,msg FROM modele_journal;

  
SET @id=0;
CALL mod_valide_tournee(@id,NULL);
COMMIT;


 SELECT * FROM modele_journal;
 SET @id=NULL;
CALL mod_valide_tournee_jour(@id,400);
SELECT MAX(validation_id) FROM modele_journal;
SELECT * FROM modele_journal WHERE validation_id=@id;
SELECT * FROM modele_journal WHERE validation_id=80;
COMMIT;

 
 SET @id=0;
CALL mod_valide_tournee(@id,NULL);
COMMIT;

SELECT dureeHP FROM modele_tournee_jour WHERE id=401;
UPDATE modele_tournee_jour SET          dureeHP=NULL WHERE id = 401;
COMMIT;