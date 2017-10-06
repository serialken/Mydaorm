#!/bin/bash

########## Integration des clients a servir



# ---------- Parametrage global sh

# environnement
env='prod'

# identifiant du sh
id_sh='integration_cas_0'


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


echo '<h1>1ere integration des clients a servir venant de Jade</h1>'


# ---------- Liste des taches a executer

#*-*-*-*-*- Prise en compte des transferts de tournees
echo '<h3><u>Prise en compte des transferts de tournees</u></h3>'
echo '<pre>'
php ./app/console transfert_tournee --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'

#*-*-*-*-*- Recuperation des fichiers de clients a servir venant de JADE
echo '<h3><u>Recuperation des fichiers de clients a servir venant de DAN JADE</u></h3>'
echo '<pre>'
php ./app/console import_fic_ftp JADE_CAS --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'

#*-*-*-*-*- Transformation des fichiers de Art & Deco, Elle Deco, T2S, T7J 
echo '<h3><u>Transformation du fichier de Art & Deco</u></h3>'
echo '<pre>'
php ./app/console clients_a_servir_transformation TRANSFO_JADE_CAS_AR --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'  
echo '<h3><u>Transformation du fichier de CAPITAL</u></h3>'
echo '<pre>'
php ./app/console clients_a_servir_transformation TRANSFO_JADE_CAS_CA --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'
#echo '<h3><u>Transformation du fichier de ELLE</u></h3>'
#echo '<pre>'
#php ./app/console clients_a_servir_transformation TRANSFO_JADE_CAS_EL --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
#cat $fic_log
#echo '</pre>'
#echo '<br /><br />'
echo '<h3><u>Transformation du fichier de L EXPANSION</u></h3>'
echo '<pre>'
php ./app/console clients_a_servir_transformation TRANSFO_JADE_CAS_EX --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'
echo '<h3><u>Transformation du fichier de GEO</u></h3>'
echo '<pre>'
php ./app/console clients_a_servir_transformation TRANSFO_JADE_CAS_GO --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'
echo '<h3><u>Transformation du fichier de Elle Deco</u></h3>'
echo '<pre>'
php ./app/console clients_a_servir_transformation TRANSFO_JADE_CAS_LD --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'
echo '<h3><u>Transformation du fichier de Tele 2 Semaines</u></h3>'
echo '<pre>'
php ./app/console clients_a_servir_transformation TRANSFO_JADE_CAS_T2 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'
echo '<h3><u>Transformation du fichier de T7J</u></h3>'
echo '<pre>'
php ./app/console clients_a_servir_transformation TRANSFO_JADE_CAS_T7 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'






#*-*-*-*-*- Recuperation des fichiers de clients a servir LP J+2
echo '<h3><u>Recuperation des fichiers de clients a servir LP J+2</u></h3>'
echo '<pre>'
php ./app/console import_fic_ftp JADE_CAS --regex_fic='/^(\.\/)?LP`date_Ymd_2_2`\.txt$/i' --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'

#*-*-*-*-*- Integration des clients a servir
echo '<h3><u>Integration des clients a servir</u></h3>'
echo '<pre>'
php ./app/console clients_a_servir_integration JADE_CAS --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'

#*-*-*-*-*- Tournees Neopress
echo '<h3><u>Reprise historique depot, tournee Neopress + Prise en compte des depot-commune Neopress pour le flux de jour</u></h3>'
echo '<pre>'
php ./app/console fusion_reprise_histo J+1 J+1 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
php ./app/console fusion_reprise_histo J+2 J+2 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log

echo '</pre>'
echo '<br /><br />'

#*-*-*-*-*- Stockage des adresses livrees de ce jour
echo '<h3><u>Stockage des adresses livrees de ce jour</u></h3>'
echo '<pre>'
php ./app/console sauvegarde_adr_livrees J+0 J+0 --force=1 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log

echo '</pre>'
echo '<br /><br />'

# ---------- suppression le fichier temporaire de log
rm -f $fic_log


# --------------- Fin SH -------------------
php app/console end_sh $id_ai --env=$env