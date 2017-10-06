#!/bin/bash

########## 



# ---------- Classement des nouveaux abo jour  J+1  et nuit J+2

# environnement
env='prod'

# identifiant du sh
id_sh='classement_nuit_jour'


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