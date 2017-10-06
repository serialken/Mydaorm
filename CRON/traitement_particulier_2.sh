#!/bin/bash

########## Traitement particulier



# ---------- Parametrage global sh

# environnement
env='prod'

# identifiant du sh
id_sh='traitement_particulier_2'


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
php app/console init_sh $id_sh --id_sh=$id_sh --id_ai=$id_ai --env=$env 
id_ai=$?

echo '<h1>Traitement ELLE - Decalage jour de distribution</h1>'


# ---------- Liste des taches a executer

#*-*-*-*-*- Reprise historique depot, tournee Neopress + Prise en compte des depot-commune Neopress pour le flux de jour
echo '<h3><u>Reprise historique depot, tournee Neopress + Prise en compte des depot-commune Neopress pour le flux de jour</u></h3>'
echo '<pre>'
php ./app/console fusion_reprise_histo J+2 J+2 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log

echo '</pre>'
echo '<br /><br />'


#*-*-*-*-*- Classement automatique
echo '<h3><u>Classement automatique des nouveaux abonnes - Flux nuit</u></h3>'
echo '<pre>'
php ./app/console classement_auto J+2 J+2 --flux=C_A_S --jn=nuit --nbmax=10000 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'


#*-*-*-*-*- Generation des feuilles de portage ELLE
echo '<h3><u>Generation des feuilles de portage ELLE</u></h3>'
echo '<pre>'
php ./app/console feuille_portage --flux=1 J+2 J+2 --soc=EL --cd=004 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=1 J+2 J+2 --soc=EL --cd=007 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=1 J+2 J+2 --soc=EL --cd=010 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=1 J+2 J+2 --soc=EL --cd=013 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=1 J+2 J+2 --soc=EL --cd=014 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=1 J+2 J+2 --soc=EL --cd=018 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=1 J+2 J+2 --soc=EL --cd=023 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=1 J+2 J+2 --soc=EL --cd=024 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=1 J+2 J+2 --soc=EL --cd=028 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=1 J+2 J+2 --soc=EL --cd=029 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=1 J+2 J+2 --soc=EL --cd=031 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=1 J+2 J+2 --soc=EL --cd=033 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=1 J+2 J+2 --soc=EL --cd=034 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=1 J+2 J+2 --soc=EL --cd=035 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=1 J+2 J+2 --soc=EL --cd=036 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=1 J+2 J+2 --soc=EL --cd=039 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=1 J+2 J+2 --soc=EL --cd=040 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=1 J+2 J+2 --soc=EL --cd=041 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=1 J+2 J+2 --soc=EL --cd=042 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=1 J+2 J+2 --soc=EL --cd=045 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'


# ---------- suppression le fichier temporaire de log
rm -f $fic_log

# --------------- Fin SH -------------------
php app/console end_sh $id_ai --env=$env