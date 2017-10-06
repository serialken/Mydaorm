#!/bin/bash

########## Integration des clients a servir



# ---------- Parametrage global sh

# environnement
env='prod'

# identifiant du sh
id_sh='integration_cas_1'


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


#*-*-*-*-*- Integration des clients a servir
echo '<h3><u>Integration des clients a servir</u></h3>'
echo '<pre>'
php ./app/console clients_a_servir_integration JADE_CAS --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'


#*-*-*-*-*- Suppression de tournee_detail des classements des abonnes qui changent d adresse
echo '<h3><u>Suppression de tournee_detail des classements des abonnes qui changent d adresse</u></h3>'
echo '<pre>'
php ./app/console chgt_adresse_suppr_tournees J+1 --fic_resume=1 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
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



#*-*-*-*-*- Tournees SDVP
#echo '<h3><u>Tournees SDVP a partir des fichiers de DCS</u></h3>'
#echo '<pre>'
#php ./app/console sdvp_traitement J+1 J+2 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
#cat $fic_log
#echo '</pre>'
#echo '<br /><br />'



#*-*-*-*-*- Tournees Neopress
# on ne prend en compte que les tournees de Dispress concernant les produits jours
#echo '<h3><u>Prise en compte des tournees Neopress pour le flux jour</u></h3>'
#echo '<pre>'
#php ./app/console neopress_traitement J+1 J+1 --flux=C_A_S --jn=jour --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
#cat $fic_log
#echo '</pre>'
#echo '<br /><br />'

#*-*-*-*-*- Mise a jour des tournees de client_a_servir_logist pour les clients deja classes
#echo '<h3><u>Mise a jour des tournees de client_a_servir_logist pour les clients deja classes</u></h3>'
#echo '<pre>'
#php ./app/console mise_a_jour_tournee J+1 J+1 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
#cat $fic_log
#echo '</pre>'
#echo '<br /><br />'


#*-*-*-*-*- Classement automatique
echo '<h3><u>Classement automatique des nouveaux abonnes - Flux nuit</u></h3>'
echo '<pre>'
php ./app/console classement_auto J+1 J+1 --flux=C_A_S --jn=nuit --nbmax=10000 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
#*-*-*-*-*- Classement automatique relance pour une 2eme fois afin de reclasser les abonnes rencontrant une erreur de SOAP lors de son classement
# Erreur souvent rencontre lors du classement automatique : SOAP Fault: (faultcode: SOAP-ENV:Server, faultstring: Internal Error : )
php ./app/console classement_auto J+1 J+1 --flux=C_A_S --jn=nuit --nbmax=10000 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
php ./app/console classement_auto J+2 J+2 --flux=C_A_S --jn=nuit --nbmax=10000 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'

#*-*-*-*-*- Classement automatique jour
echo '<h3><u>Classement automatique des nouveaux abonnes - Flux jour</u></h3>'
echo '<pre>'
php ./app/console classement_auto J+1 J+1 --flux=C_A_S --jn=jour --nbmax=10000 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
#*-*-*-*-*- Classement automatique relance pour une 2eme fois afin de reclasser les abonnes rencontrant une erreur de SOAP lors de son classement
# Erreur souvent rencontre lors du classement automatique : SOAP Fault: (faultcode: SOAP-ENV:Server, faultstring: Internal Error : )
php ./app/console classement_auto J+1 J+1 --flux=C_A_S --jn=jour --nbmax=10000 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'


#*-*-*-*-*- Calcul des recap de produits par depot
echo '<h3><u>Calcul des recap de produits par depot</u></h3>'
echo '<pre>'
php ./app/console produit_recap_depot J+1 J+2 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'



#*_*_*_*_*_ Intégration des produits additionnels
echo '<h3><u>Intégration des produits additionnels</u></h3>'
echo '<pre>'
php app/console distrib_prod_additionnel --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'


# ---------- suppression le fichier temporaire de log
rm -f $fic_log


# --------------- Fin SH -------------------
php app/console end_sh $id_ai --env=$env