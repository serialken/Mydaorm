#!/bin/bash
SYMFONY_ENV='prod'
DOSSIER_LOG='/var/www/html/MRoad_Fichiers/Logs/'
FICHIER_LOG='IK.log'

#.............racine scripts
racine_projet='/var/www/html/MRoad'

cd $racine_projet
# Chargement des IKS
php app/console IK --env $SYMFONY_ENV >> $DOSSIER_LOG$FICHIER_LOG 
