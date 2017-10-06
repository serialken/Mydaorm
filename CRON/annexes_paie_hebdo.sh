#!/bin/bash

########## Integration des clients a servir



# ---------- Parametrage global sh

# environnement
env='prod'

# identifiant du sh
id_sh='annexes_paie_hebdo'


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

date1=`date +%Y%m%d`

echo '<h1>Annexes paie</h1>'
# ---------- Liste des taches a executer

echo '<h2>Annexes paie</h2>'
echo '<h3>Annexes paie - Flux Nuit</h3>'
echo '<pre>'
php ./app/console annexe_paie all 1 J --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'

date2=`date +%Y%m%d`

echo '<h3>Annexes paie - Flux Jour</h3>'
echo '<pre>'
if [ $date1 -eq $date2 ]
then
 php ./app/console annexe_paie all 2 J --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
else	
 php ./app/console annexe_paie all 2 J-1 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
fi
cat $fic_log
echo '</pre>'

# ---------- suppression le fichier temporaire de log
rm -f $fic_log


# --------------- Fin SH -------------------
php app/console end_sh $id_ai --env=$env