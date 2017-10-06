#!/bin/bash

########## Integration des clients a servir



# ---------- Parametrage global sh

# environnement
env='prod'

# identifiant du sh
id_sh='integration_cas_migration_test'


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


echo '<h1>Integration des clients a servir, classement automatique (SANS la generation de feuilles de portage) pour TEST MIGRATION</h1>'


# ---------- Liste des taches a executer

#*-*-*-*-*- Recuperation des fichiers de clients a servir LP J+2
echo '<h3><u>Recuperation des fichiers de clients a servir entre J+183 et J+184</u></h3>'
echo '<pre>'
php ./app/console import_fic_ftp JADE_CAS --regex_fic='/^(\.\/)?[A-Z0-9]{2}`date_Ymd_183_184`\.txt$/i' --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
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
php ./app/console fusion_reprise_histo J+183 J+183 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
php ./app/console fusion_reprise_histo J+184 J+184 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'

#*-*-*-*-*- Suppression de CASL des depots a ne pas gerer
echo '<h3><u>Suppression de CASL des depots a ne pas gerer</u></h3>'
echo '<pre>'
php ./app/console casl_suppr J+183 J+183 --depot_a_garder=010,039,045 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
php ./app/console casl_suppr J+184 J+184 --depot_a_garder=010,039,045 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
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
echo '<h3><u>Classement automatique des nouveaux abonnes - Flux nuit - J+183</u></h3>'
echo '<pre>'
php ./app/console classement_auto J+183 J+183 --flux=C_A_S --jn=nuit --nbmax=10000 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'

#*-*-*-*-*- Classement automatique relance pour une 2eme fois afin de reclasser les abonnes rencontrant une erreur de SOAP lors de son classement
echo '<h3><u>Classement automatique des nouveaux abonnes - Flux nuit - J+184</u></h3>'
echo '<pre>'
php ./app/console classement_auto J+184 J+184 --flux=C_A_S --jn=nuit --nbmax=10000 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'

#*-*-*-*-*- Classement automatique jour
#echo '<h3><u>Classement automatique des nouveaux abonnes - Flux jour</u></h3>'
#echo '<pre>'
#php ./app/console classement_auto J+1 J+1 --flux=C_A_S --jn=jour --nbmax=10000 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
#cat $fic_log
#echo '</pre>'
#echo '<br /><br />'




#*-*-*-*-*- Calcul des recap de produits par depot
echo '<h3><u>Calcul des recap de produits par depot - J+183 et J+184</u></h3>'
echo '<pre>'
php ./app/console produit_recap_depot J+183 J+183 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
php ./app/console produit_recap_depot J+184 J+184 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'






#*-*-*-*-*- Feuilles de portage
echo '<h2>Feuilles de portage - Nuit - J+183</h2>'
echo '<pre>'

php ./app/console feuille_portage --flux=1 J+183 J+183 --cd=010 --exclude_stop=010 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=1 J+183 J+183 --cd=039 --exclude_stop=039 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=1 J+183 J+183 --cd=045 --exclude_stop=045 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1


cat $fic_log
echo '</pre>'
echo '<br /><br />'


#*-*-*-*-*- Verification de la presence des feuilles de portage Nuit J+183
echo '<h3><u>Verification de la presence des feuilles de portage Nuit J+183</u></h3>'
echo '<pre>'
php ./app/console feuille_portage_suivi J+183 J+183 --jn=nuit --dest="andry.andrianiaina@amaury.com, cyril.burnacci@amaury.com" --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'



# ---------- suppression le fichier temporaire de log
rm -f $fic_log


# --------------- Fin SH -------------------
php app/console end_sh $id_ai --env=$env