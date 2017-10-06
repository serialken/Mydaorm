#!/bin/bash

########## Export reponses reclams / remontees d info / comptes rendus de reception 



# ---------- Parametrage global sh

# environnement
env='prod'

# identifiant du sh
id_sh='export_crm'


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

echo '<h1>Export CRM vers DAN JADE</h1>'


# ---------- Liste des taches a executer

#*-*-*-*-*- Export des reponses reclamations vers DAN JADE
echo '<h3><u>Export des reponses reclamations vers DAN JADE</u></h3>'
echo '<pre>'
php ./app/console reclam_export JADE_EXP_REP_RECLAM --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'

#*-*-*-*-*- Export des remontees d information vers DAN JADE
echo '<h3><u>Export des remontees d information vers DAN JADE</u></h3>'
echo '<pre>'
php ./app/console rem_info_export JADE_EXP_REM_INFO --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'

#*-*-*-*-*- Export des comptes rendus de reception vers DAN JADE
#echo '<h3><u>Export des comptes rendus de reception vers DAN JADE</u></h3>'
#echo '<pre>'
#php ./app/console cr_reception_export JADE_EXP_CR_RECEPTION J+0 J+0 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
#cat $fic_log
#echo '</pre>'
#echo '<br /><br />'


# ---------- suppression le fichier temporaire de log
rm -f $fic_log

# --------------- Fin SH -------------------
php app/console end_sh $id_ai --env=$env