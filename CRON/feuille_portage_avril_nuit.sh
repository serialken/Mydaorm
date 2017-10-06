#!/bin/bash

########## Generation de feuille de portage pour le flux nuit



# ---------- Parametrage global sh

# environnement
env='prod'

# identifiant du sh
id_sh='feuille_portage_nuit'


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


#*-*-*-*-*- Feuilles de portage
echo '<h2>Feuilles de portage - Nuit</h2>'
echo '<pre>'

#php ./app/console feuille_portage --flux=1 J+1 J+1 --cd=000 --exclude_stop=000 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=1 J-75 J-46 --cd=010 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=1 J-75 J-46 --cd=013 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=1 J-75 J-46 --cd=014 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=1 J-75 J-46 --cd=018 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=1 J-75 J-46 --cd=023 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=1 J-75 J-46 --cd=024 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=1 J-75 J-46 --cd=028 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=1 J-75 J-46 --cd=029 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=1 J-75 J-46 --cd=031 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=1 J-75 J-46 --cd=033 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=1 J-75 J-46 --cd=034 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=1 J-75 J-46 --cd=035 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=1 J-75 J-46 --cd=036 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=1 J-75 J-46 --cd=039 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=1 J-75 J-46 --cd=040 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=1 J-75 J-46 --cd=041 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=1 J-75 J-46 --cd=042 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=1 J-75 J-46 --cd=045 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=1 J-75 J-46 --cd=051 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1

# --------------- Fin SH -------------------
php app/console end_sh $id_ai --env=$env

