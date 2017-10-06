#!/bin/bash

########## Historisation



# ---------- Parametrage global sh

# environnement
env='prod'

# identifiant du sh
id_sh='historisation'


#.............racine scripts
racine_projet='/var/www/html/MRoad'

#.............racine fichiers a traiter
racine_fic_a_traiter='/var/www/html/MRoad'

# sous repertoire des fichiers de logs sh
ssrep_log='../MRoad_Fichiers/Logs'

# fichier de log
fic_log=$racine_fic_a_traiter'/'$ssrep_log'/sh_'$id_sh'_'$(date '+%Y%m%d_%H%M%S')'.sh.log'

cd $racine_projet

# Initialisation et identifiant en BDD du sh
php app/console init_sh $id_sh --id_sh=$id_sh --id_ai=$id_ai --env=$env 
id_ai=$?



echo '<h1>Historisation</h1>'


# ---------- Liste des taches a executer

#*-*-*-*-*- Historisation des tables "client_a_servir_logist" et "client_a_servir_src" -> "client_a_servir_histo"
echo '<h3><u>Historisation des tables client_a_servir_logist et client_a_servir_src -> client_a_servir_histo</u></h3>'
echo '<pre>'
php ./app/console cas_histo J-190 J-180 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
#php ./app/console cas_histo J-230 J-210 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
#cat $fic_log
echo '</pre>'
echo '<br /><br />'


echo '<h3><u>Suppression des donnees de la table client_a_servir_histo trop anciennes</u></h3>'
echo '<pre>'
php ./app/console cas_histo_suppr J-370 J-365 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'



# ---------- suppression le fichier temporaire de log
rm -f $fic_log

# --------------- Fin SH -------------------
php app/console end_sh $id_ai --env=$env