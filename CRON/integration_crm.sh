#!/bin/bash

########## Integration des Reclams 



# ---------- Parametrage global sh

# environnement
env='prod'

# identifiant du sh
id_sh='integration_crm'


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


echo '<h1>Integration des reclamations</h1>'


# ---------- Liste des taches a executer

#*-*-*-*-*- Recuperation des fichiers de reclamations venant de DAN JADE
echo '<h3><u>Recuperation des fichiers de reclamations venant de DAN JADE</u></h3>'
echo '<pre>'
php ./app/console import_fic_ftp JADE_RECLAM --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'

#*-*-*-*-*- Integration des reclamations
echo '<h3><u>Integration des reclamations</u></h3>'
echo '<pre>'
php ./app/console reclam_integration JADE_RECLAM --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'

#*-*-*-*-*- Recuperation des fichiers d etiquettes venant de DCS LP
echo '<h3><u>Recuperation des fichiers d etiquettes venant de DCS LP</u></h3>'
echo '<pre>'
php ./app/console import_fic_ftp DCS_IMPORT_ETIQUETTE --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'

#*-*-*-*-*- Integration des fichiers d etiquettes
echo '<h3><u>Integration des fichiers d etiquettes</u></h3>'
echo '<pre>'
php ./app/console reclam_integration DCS_IMPORT_ETIQUETTE --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'

#*-*-*-*-*- Recuperation des fichiers de remontees d infos Neopress venant de leur FTP 
#echo '<h3><u>Recuperation des fichiers de remontees d infos Neopress venant de leur FTP</u></h3>'
#echo '<pre>'
#php ./app/console import_fic_ftp NEO_IMPORT_REM_INFO --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
#cat $fic_log
#echo '</pre>'
#echo '<br /><br />'

#*-*-*-*-*- Integration des remontees d infos Neopress
#echo '<h3><u>Integration des remontees d infos Neopress</u></h3>'
#echo '<pre>'
#php ./app/console reminfo_neopress_integration NEO_IMPORT_REM_INFO --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
#cat $fic_log
#echo '</pre>'
#echo '<br /><br />'




# ---------- suppression le fichier temporaire de log
rm -f $fic_log


# --------------- Fin SH -------------------
php app/console end_sh $id_ai --env=$env

