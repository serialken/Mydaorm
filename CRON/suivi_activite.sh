#!/bin/bash

########## Gestion du suivi d'activite



# ---------- Parametrage global sh

# environnement
env='prod'

# identifiant du sh
id_sh='suivi_activite'

#.............racine scripts
racine_projet='/var/www/html/MRoad'

SYMFONY_ENV='prod'
DATE_EXEC=$(date +%Y-%m-%d --date="+1 day")
DOSSIER_LOG='/var/www/html/MRoad_Fichiers/Logs/'
FICHIER_LOG='suivi_activite-'$DATE_EXEC'.log'



cd $racine_projet

# Initialisation et identifiant en BDD du sh
php app/console init_sh $id_sh --env=$env 
id_ai=$?

php app/console sendmail_activite --env=$env > $DOSSIER_LOG$FICHIER_LOG

# --------------- Fin SH -------------------
php app/console end_sh $id_ai --env=$env
