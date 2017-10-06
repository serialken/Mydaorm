#!/bin/bash

########## Integration des clients a servir HORS PRESSE



# ---------- Parametrage global sh

# environnement
env='prod'

# identifiant du sh
id_sh='integration_cas_hors_presse'


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


echo '<h1>Integration des clients a servir, classement automatique (SANS la generation de feuilles de portage)</h1>'


# ---------- Liste des taches a executer

#*-*-*-*-*- Recuperation des fichiers de clients a servir venant de JADE
echo '<h3><u>Recuperation des fichiers de clients a servir venant de DAN JADE</u></h3>'
echo '<pre>'
php ./app/console import_fic_ftp HORS_PRESSE_CAS --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'



#*-*-*-*-*- Integration des clients a servir
echo '<h3><u>Integration des clients a servir</u></h3>'
echo '<pre>'
php ./app/console clients_a_servir_integration HORS_PRESSE_CAS --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'


#*-*-*-*-*- Reprise historique
echo '<h3><u>Reprise historique depot, tournee + Prise en compte des depot-commune Neopress pour le flux de jour</u></h3>'
echo '<pre>'
#php ./app/console fusion_reprise_histo J+1 J+10 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console reprise_histo_hors_presse J+1 J+30 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log

echo '</pre>'
echo '<br /><br />'




#*-*-*-*-*- Calcul des recap de produits par depot
#echo '<h3><u>Calcul des recap de produits par depot</u></h3>'
#echo '<pre>'
#php ./app/console produit_recap_depot J+1 J+2 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
#cat $fic_log
#echo '</pre>'
#echo '<br /><br />'


#*_*_*_*_*_ Intégration des produits additionnels
#echo '<h3><u>Intégration des produits additionnels</u></h3>'
#echo '<pre>'
#php app/console distrib_prod_additionnel --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
#cat $fic_log
#echo '</pre>'
#echo '<br /><br />'


# ---------- suppression le fichier temporaire de log
rm -f $fic_log


# --------------- Fin SH -------------------
php app/console end_sh $id_ai --env=$env