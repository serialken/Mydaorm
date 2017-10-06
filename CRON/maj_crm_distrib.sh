#!/bin/bash

########## Génération du Reporting
##############Envoi par mail du fichier

# ---------- Parametrage global sh

# Environnement
env='prod'

# identifiant du sh
id_sh='maj_crm_distrib'


SYMFONY_ENV='prod'
DATE_EXEC=$(date +%Y-%m-%d --date="+1 day")
DOSSIER_LOG='/var/www/html/MRoad_Fichiers/Logs/'
FICHIER_LOG='maj_crm_distrib-'$DATE_EXEC'.log'

#.............racine scripts
racine_projet='/var/www/html/MRoad'

cd $racine_projet

# Initialisation et identifiant en BDD du sh
php app/console init_sh $id_sh --env=$env 
id_ai=$?

# Génération du reporting sur les 30 derniers jours (MAJ de la table + dépose du fichier batch)
echo "Génération du reporting sur les 30 derniers jours (MAJ de la table + dépose du fichier batch)" > $DOSSIER_LOG$FICHIER_LOG
php app/console sendmail_recap_tournees --no_email 1 --id_sh=$id_sh --id_ai=$id_ai --env=$env >> $DOSSIER_LOG$FICHIER_LOG 

# Envoi du fichier complet (à partir de la date spécifiée) par email
echo "\nEnvoi du fichier complet (à partir de la date spécifiée) par email" >> $DOSSIER_LOG$FICHIER_LOG
php app/console sendmail_recap_tournees --date_debut 20/12/2014  --no_update 1 --no_copy 1 --id_sh=$id_sh --id_ai=$id_ai --env=$env >> $DOSSIER_LOG$FICHIER_LOG

# --------------- Fin SH -------------------
php app/console end_sh $id_ai --env=$env