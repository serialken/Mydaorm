#!/bin/bash

########## Verification de la presence des feuilles de portage nuit


# ---------- Parametrage global sh

# environnement
env='prod'

# identifiant du sh
id_sh='feuille_portage_nuit_suivi'


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


echo '<h1>Verification de la presence des feuilles de portage</h1>'


# ---------- Liste des taches a executer

#*-*-*-*-*- Verification de la presence des feuilles de portage Nuit J+1
echo '<h3><u>Verification de la presence des feuilles de portage Nuit J+1</u></h3>'
echo '<pre>'
php ./app/console feuille_portage_suivi J+1 J+1 --jn=nuit --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'


# ---------- suppression le fichier temporaire de log
rm -f $fic_log

# --------------- Fin SH -------------------
php app/console end_sh $id_ai --env=$env