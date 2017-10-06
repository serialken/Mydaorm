#!/bin/bash

########## Sauvegarde des adresse livre pour France routage tele loisir pour la comparaison



# ---------- Parametrage global sh

# environnement
env='prod'

# identifiant du sh
id_sh='maj_adr_franceroutage'


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


echo '<h3><u>Mise à jour des adresse livrees  les 7 derniers jours</u></h3>'
echo '<pre>'
php ./app/console sauvegarde_adr_vol123_livrees J-7 J+0 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'

# ---------- suppression le fichier temporaire de log
rm -f $fic_log

# --------------- Fin SH -------------------
php app/console end_sh $id_ai --env=$env