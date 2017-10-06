#!/bin/bash
SYMFONY_ENV='prod'
DOSSIER_LOG='/var/www/html/MRoad_Fichiers/Logs/'
FICHIER_LOG='IK.log'
id_sh='IK'


#.............racine scripts
racine_projet='/var/www/html/MRoad'

cd $racine_projet

# Initialisation et identifiant en BDD du sh
php app/console init_sh $id_sh --env=$env 
id_ai=$?

# Chargement des IKS
php app/console IK --id_sh=$id_sh --id_ai=$id_ai  --env $SYMFONY_ENV >> $DOSSIER_LOG$FICHIER_LOG 

# --------------- Fin SH -------------------
php app/console end_sh $id_ai --env=$env
