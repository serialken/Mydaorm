#!/bin/bash

########## Traitement particulier



# ---------- Parametrage global sh

# environnement
env='prod'

# identifiant du sh
id_sh='init_nettoyages_tables_export_ref'


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



echo '<h1>Initialisation de tables</h1>'


# ---------- Liste des taches a executer

#*-*-*-*-*- Export "repar_soc" LM JOUR
echo '<h3><u>Export -repar_soc- LM JOUR</u></h3>'
echo '<pre>'
php ./app/console repar_soc_export JADE_EXP_COMMUNE_JOUR --soc="MD,MP" --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log

echo '</pre>'
echo '<br /><br />'

#*-*-*-*-*- Initialisation de la table "adresse_vol123_livree"
echo '<h3><u>Initialisation de la table adresse_vol123_livree</u></h3>'
echo '<pre>'
php ./app/console sauvegarde_adr_vol123_livrees J-30 J+0 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log

echo '</pre>'
echo '<br /><br />'

# ---------- suppression le fichier temporaire de log
rm -f $fic_log


# --------------- Fin SH -------------------
php app/console end_sh $id_ai --env=$env