#!/bin/bash

########## Integration des reperages



# ---------- Parametrage global sh

# environnement
env='prod'

# identifiant du sh
id_sh='integration_reperage'


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

echo '<h1>Integration des reperages & export des reponses reperages</h1>'


# ---------- Liste des taches a executer

#*-*-*-*-*- Recuperation des fichiers de reperages venant de JADE
echo '<h3><u>Recuperation des fichiers de reperages venant de DAN JADE</u></h3>'
echo '<pre>'
php ./app/console import_fic_ftp JADE_REPERAGE --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'

#*-*-*-*-*- Integration des reperages
echo '<h3><u>Integration des reperages</u></h3>'
echo '<pre>'
php ./app/console reperage_integration JADE_REPERAGE --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'

#*-*-*-*-*- Classement auto des reperages non encore classes
echo '<h3><u>Classement auto des reperages non encore classes</u></h3>'
echo '<pre>'
php ./app/console reperage_classement_auto J+1 J+10 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'

#*-*-*-*-*- Tournees Neopress
#echo '<h3><u>Integration des tournees Neopress pour le flux jour</u></h3>'
#echo '<pre>'
#php ./app/console neopress_traitement J+1 J+1 --flux=REPER --jn=jour --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
#cat $fic_log
#echo '</pre>'
#echo '<br /><br />'

#*-*-*-*-*- Export des reponses aux reperages vers DAN JADE
#echo '<h3><u>Export des reponses aux reperages vers DAN JADE</u></h3>'
#echo '<pre>'
#php ./app/console rep_reperage_export JADE_EXP_REP_REPERAGE --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
#cat $fic_log
#echo '</pre>'
#echo '<br /><br />'




# ---------- suppression le fichier temporaire de log
rm -f $fic_log

# --------------- Fin SH -------------------
php app/console end_sh $id_ai --env=$env