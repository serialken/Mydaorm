#!/bin/bash

########## CRON poour tester le suivi de CRON
############################### O Bicho Vai Pegar !!!!!!!!!!!!
################################################# 


# ---------- Parametrage global sh

# Environnement
env='local'

# identifiant du sh
id_sh='cron_bis_local'

# racine scripts
racine_projet='C:/wamp/www/mroad'

cd $racine_projet

# Initialisation et identifiant en BDD du sh
# id_ai=php app/console init_sh $id_sh --env=$env 
php app/console init_sh $id_sh --env=$env 
id_ai=$?

echo "cron de test du suivi de CRON"
echo $id_ai

# ---------- Liste des taches a effectuer ----------

#*-*-*-*-*- test suivi de prod integration
php app/console suivi_de_production_integration --id_sh=$id_sh --id_ai=$id_ai --env=$env 

#*-*-*-*-*- test import_fic_ftp
php app/console import_fic_ftp SUIVI_PRODUCTION --id_sh=$id_sh --id_ai=$id_ai --env=$env


# --------------- Fin SH -------------------
php app/console end_sh $id_ai --env=$env
