#!/bin/bash

########## Integration des clients a servir



# ---------- Parametrage global sh

# environnement
env='prod'

# identifiant du sh
id_sh='feuilles_quotidiennes'


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

echo '<h1>Feuilles d emargement & Feuilles de portage</h1>'


# ---------- Liste des taches a executer

#*-*-*-*-*- Classement automatique
echo '<h3><u>Classement automatique des nouveaux abonnes</u></h3>'
echo '<pre>'
php ./app/console classement_auto J+1 J+2 --flux=C_A_S --jn=nuit --nbmax=10000 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
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


#*-*-*-*-*- Feuilles d emargement
#echo '<h3>Feuilles d emargement - Flux Nuit</h3>'
#echo '<pre>'
#php ./app/console feuille_emargement all 1 J+1 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
#cat $fic_log
#echo '</pre>'
#echo '<br /><br />'

#echo '<h3>Feuilles d emargement - Flux Jour</h3>'
#echo '<pre>'
#php ./app/console feuille_emargement all 2 J+1 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
#cat $fic_log
#echo '</pre>'
#echo '<br /><br />'

#*-*-*-*-*- Feuilles de portage
echo '<h2>Feuilles de portage - Nuit</h2>'
echo '<pre>'
php ./app/console feuille_portage --flux=1 J+1 J+1 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'

#*-*-*-*-*- Feuilles de portage
echo '<h2>Feuilles de portage - Jour</h2>'
echo '<pre>'
php ./app/console feuille_portage --flux=2 J+1 J+1 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'


# ---------- suppression le fichier temporaire de log
rm -f $fic_log

# --------------- Fin SH -------------------
php app/console end_sh $id_ai --env=$env