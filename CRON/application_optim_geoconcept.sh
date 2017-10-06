#!/bin/bash

id=sh='import_geoconcept'
DATE_EXEC=$(date +%Y-%m-%d)
SYMFONY_ENV='prod'
DOSSIER_LOG='/var/www/html/MRoad_Fichiers/Logs/'
FICHIER_LOG='application_optim_geoconcept-'$DATE_EXEC'.log'

#.............racine scripts
racine_projet='/var/www/html/MRoad'

cd $racine_projet

# Initialisation et identifiant en BDD du sh
php app/console init_sh $id_sh --env=$env 
id_ai=$?


php app/console import_geoconcept --id_sh=$id_sh --id_ai=$id_ai --env $SYMFONY_ENV >> $DOSSIER_LOG$FICHIER_LOG

# --------------- Fin SH -------------------
php app/console end_sh $id_ai --env=$env
