#!/bin/bash

########## Integration des clients a servir



# ---------- Parametrage global sh

# environnement
env='prod'

# identifiant du sh
id_sh='integration_cas_2'


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

#*-*-*-*-*- Recuperation des fichiers de clients a servir LP J+2
echo '<h3><u>Recuperation des fichiers de clients a servir LP J+2</u></h3>'
echo '<pre>'
php ./app/console import_fic_ftp JADE_CAS --regex_fic='/^(\.\/)?LP`date_Ymd_2_2`\.txt$/i' --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'

#*-*-*-*-*- Transformation des fichiers de Art & Deco, Capital, Elle Deco, T2S, T7J 
#--------- Les transformations suivantes ont ete laissees pour securite
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

#*-*-*-*-*- Tournees Neopress
echo '<h3><u>Reprise historique depot, tournee Neopress + Prise en compte des depot-commune Neopress pour le flux de jour</u></h3>'
echo '<pre>'
php ./app/console fusion_reprise_histo J+1 J+1 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
php ./app/console fusion_reprise_histo J+2 J+2 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'


#*-*-*-*-*- Classement automatique relance pour une 2eme fois afin de reclasser les abonnes rencontrant une erreur de SOAP lors de son classement
# Erreur souvent rencontre lors du classement automatique : SOAP Fault: (faultcode: SOAP-ENV:Server, faultstring: Internal Error : )
echo '<h3><u>Classement automatique des nouveaux abonnes - Flux nuit - J+2</u></h3>'
echo '<pre>'
#php ./app/console classement_auto J+1 J+1 --flux=C_A_S --jn=nuit --nbmax=10000 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
#cat $fic_log
php ./app/console classement_auto J+2 J+2 --flux=C_A_S --jn=nuit --nbmax=10000 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
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
echo '</pre>'
echo '<br /><br />'




#*-*-*-*-*- Calcul des recap de produits par depot
echo '<h3><u>Calcul des recap de produits par depot - J+2</u></h3>'
echo '<pre>'
php ./app/console produit_recap_depot J+1 J+1 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
php ./app/console produit_recap_depot J+2 J+2 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'




#*-*-*-*-*- Export vers DCS des tournees M-ROAD
echo '<h3><u>Export vers DCS des tournees MROAD</u></h3>'
echo '<pre>'
php ./app/console dcs_tournee_mroad_export DCS_EXP_TOURNEE_MROAD --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'



#*-*-*-*-*- Export vers DCS des clients a servir LP enrichis de leur tournee
echo '<h3><u>Export vers DCS des clients a servir LP enrichis de leur tournee</u></h3>'
echo '<pre>'
#php ./app/console dcs_tournee_cas_lp_export DCS_EXP_TOURNEE_CAS_LP J+1 J+1 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
#cat $fic_log
php ./app/console dcs_tournee_cas_lp_export DCS_EXP_TOURNEE_CAS_LP J+2 J+2 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
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