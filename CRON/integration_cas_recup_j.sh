#!/bin/bash

########## Integration des clients a servir



# ---------- Parametrage global sh

# environnement
env='prod'

# identifiant du sh
id_sh='integration_cas_recup_j'


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


echo '<h1>Integration des clients a servir</h1>'


# ---------- Liste des taches a executer

#*-*-*-*-*- Recuperation des fichiers de clients a servir venant de JADE
echo '<h3><u>Recuperation des fichiers de clients a servir venant de DAN JADE</u></h3>'
echo '<pre>'
php ./app/console import_fic_ftp JADE_CAS --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
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

#*-*-*-*-*- Suppression des produits SDVP sur le depot CD42 BERCY
#echo '<h3><u>Finalisation d integration de donnees</u></h3>'
#echo '<pre>'
#php ./app/console nettoyage_donnees --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
#cat $fic_log
#echo '</pre>'
#echo '<br /><br />'

#*-*-*-*-*- Tournees Neopress
# on ne prend en compte que les tournees de Dispress concernant les produits jours
echo '<h3><u>Les tournees Neopress</u></h3>'
echo '<pre>'
php ./app/console neopress_traitement J+0 J+0 --flux=C_A_S --jn=jour --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'

#*-*-*-*-*- Mise a jour des tournees de client_a_servir_logist pour les clients deja classes
echo '<h3><u>Mise a jour des tournees de client_a_servir_logist pour les clients deja classes</u></h3>'
echo '<pre>'
php ./app/console mise_a_jour_tournee J+0 J+0 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'

#*-*-*-*-*- Calcul des recap de produits par depot
echo '<h3><u>Calcul des recap de produits par depot</u></h3>'
echo '<pre>'
php ./app/console produit_recap_depot J+0 J+0 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'



# ---------- suppression le fichier temporaire de log
rm -f $fic_log

# --------------- Fin SH -------------------
php app/console end_sh $id_ai --env=$env