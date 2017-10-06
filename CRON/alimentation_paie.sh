#!/bin/bash

########## Alimentation Paie
# ---------- Parametrage global sh

# environnement
env='prod'

# identifiant du sh
id_sh='alimentation_paie'

#.............racine scripts
racine_projet='/var/www/html/MRoad'

#.............racine fichiers a traiter
racine_fic_a_traiter='/var/www/html/MRoad'

# sous repertoire des fichiers de logs sh
ssrep_log='../MRoad_Fichiers/Logs'

# fichier de log
fic_log=$racine_fic_a_traiter'/'$ssrep_log'/sh_'$id_sh'_'$(date '+%Y%m%d_%H%M%S')'.sh.log'

aujourdhui=`date +"%Y-%m-%d"`
demain=`TZ=MET-24 date +"%Y-%m-%d"`

cd $racine_projet

# Initialisation et identifiant en BDD du sh
php app/console init_sh $id_sh --env=$env 
id_ai=$?

echo '<h1>Alimentation Paie</h1>'

# ---------- Liste des taches a executer
#*-*-*-*-*- Alimentation Pleiades N
echo '<h3><u>Alimentation Pleiades NG</u></h3>'
echo '<pre>'
php ./app/console alimentation_employe --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'

#*-*-*-*-*- Alimentation Octime
echo '<h3><u>Alimentation Octime</u></h3>'
echo '<pre>'
php ./app/console alimentation_cycle --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'

#*-*-*-*-*- Generation de badges
echo '<h3><u>G&eacute;n&eacute;ration des badges et horaires exceptionnels</u></h3>'
echo '<pre>'
php ./app/console generation_octime --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'

#*-*-*-*-*- Calcul des ev
echo '<h3><u>Calcul dev ev flux nuit</u></h3>'
echo '<pre>'
php ./app/console calcul_mensuel 1 $aujourdhui --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'

#*-*-*-*-*- Calcul des ev
echo '<h3><u>Calcul dev ev flux jour</u></h3>'
echo '<pre>'
php ./app/console calcul_mensuel 2 $aujourdhui --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'

#*-*-*-*-*- Alimentation paie
echo '<h3><u>Alimentation paie</u></h3>'
echo '<pre>'
php ./app/console alimentation_paie $demain --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'




# ---------- suppression le fichier temporaire de log
rm -f $fic_log

# --------------- Fin SH -------------------
php app/console end_sh $id_ai --env=$env