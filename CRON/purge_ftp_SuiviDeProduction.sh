#!/bin/bash

########## Purge des fichiers sur le ftp de  suivi de production
############################### Ce CRON doit tourner en moyenne toutes les 2 semaines
################################################# Jamais entre 20h00 et 10h00 - periode de production



# ---------- Parametrage global sh

# Environnement
env='prod'

# identifiant du sh
id_sh='purge_ftp_SuiviDeProduction'


# racine fichiers a traiter
racine_fic_a_traiter='/var/www/html/MRoad'

# repertoire des CRON
fic_cron='/var/www/MRoad_Cron'

# sous repertoires des fichiers de log
ssrep_log='../MRoad_Fichiers/Logs'

# Fichiers de log
fic_log=$racine_fic_a_traiter'/'$ssrep_log'/sh_'$id_sh'_'$(date '+%Y%m%d_%H%M%S')'.sh.log'

cd $racine_projet

# Initialisation et identifiant en BDD du sh
php app/console init_sh $id_sh --env=$env 
id_ai=$?

echo "Purge des fichiers sur le FTP SIMGAM"

# ---------- Liste des taches a effectuer ----------

#*-*-*-*-*- Purge des fichiers
echo "Purge des fichiers sur le FTP ... "
php $fic_cron/MyFlushFtpSuiviProd.php --file=$fic_cron/flushFtpSuiviProd_prod.ini > $fic_log 2>&1
cat $fic_log

# --------------- Fin SH -------------------
php app/console end_sh $id_ai --env=$env
