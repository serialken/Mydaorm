#!/bin/bash

########## Export vers DCS des tournees M-ROAD & des clients a servir LP enrichis de leur tournee



# ---------- Parametrage global sh

# environnement
env='prod'

# identifiant du sh
id_sh='export_vers_DCS'


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
php app/console init_sh $id_sh --env=$env 
id_ai=$?



echo '<h1>Export vers DCS des tournees M-ROAD & des clients a servir LP enrichis de leur tournee</h1>'


# ---------- Liste des taches a executer

#*-*-*-*-*- Export vers DCS des tournees M-ROAD
echo '<h3><u>Export vers DCS des tournees M-ROAD</u></h3>'
echo '<pre>'
php ./app/console dcs_tournee_mroad_export DCS_EXP_TOURNEE_MROAD --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'



#*-*-*-*-*- Export vers DCS des clients a servir LP enrichis de leur tournee
echo '<h3><u>Export vers DCS des clients a servir LP enrichis de leur tournee</u></h3>'
echo '<pre>'
php ./app/console dcs_tournee_cas_lp_export DCS_EXP_TOURNEE_CAS_LP J+2 J+2 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'



# ---------- suppression le fichier temporaire de log
rm -f $fic_log


# --------------- Fin SH -------------------
php app/console end_sh $id_ai --env=$env