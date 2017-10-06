#!/bin/bash

########## Integration des clients a servir



# ---------- Parametrage global sh




env='prod'

# identifiant du sh
id_sh='rattrapage_integration_nuit'


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


#*-*-*-*-*- Classement automatique relance pour une 2eme fois afin de reclasser les abonnes rencontrant une erreur de SOAP lors de son classement
# Erreur souvent rencontre lors du classement automatique : SOAP Fault: (faultcode: SOAP-ENV:Server, faultstring: Internal Error : )
echo '<h3><u>Classement automatique des nouveaux abonnes - Flux nuit - J+2</u></h3>'
echo '<pre>'

php ./app/console classement_auto J+1 J+1 --flux=C_A_S --jn=nuit --nbmax=10000 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
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




# Liste de codes des centres
CENTRE_DISTRIB=(004 007 010 013 014 018 023 024 028 029 031 033 034 035 036 039 040 041 042 045 050 051)

# Le jour a traiter _ Si "+0", on traite le jour J. Si "-1", on traite le J-1. Si "+1", on traite le J+1
JOUR="+1"

# Le moment de distribution [flux] - Si 1 , c'est NUIT. Si 2 , c'est JOUR
FLUX=1
JOUR_NUIT='nuit'

# Le repertoire ou les feuilles de portage devront etre generes
REP_FEUILLE_PORTAGE='/var/www/html/Feuille_Portage_Fichiers'

# Jour a traiter au format AAAA-MM-JJ
DATE_A_TRAITER_Y_M_D=$(date +%Y-%m-%d --date=$JOUR" day")

# Jour a traiter au format AAAAMMJJ
DATE_A_TRAITER_YMD=$(date +%Y%m%d --date=$JOUR" day")


# FTP intranet
HOST_1='10.150.0.18'
USER_1='anonymous'
PASSWD_1='anonymous'

# FTP MROAD
HOST_2='10.150.10.11'
USER_2='mroadftp'
PASSWD_2='Tgj453r'



# Generation des feuilles de portage
for CD_COURANT in "${CENTRE_DISTRIB[@]}"
do
	FICHIER=$REP_FEUILLE_PORTAGE'/'$DATE_A_TRAITER_Y_M_D'/'$CD_COURANT'_'$FLUX'_'$DATE_A_TRAITER_YMD'_feuilleportage.pdf'
	echo $(date "+%d/%m/%Y %H:%M:%S")" : Debut Generation du fichier "$FICHIER >> $fic_log 2>&1
	echo $racine_projet'/app/console feuille_portage --flux='$FLUX' J'$JOUR' J'$JOUR' --cd='$CD_COURANT' --id_sh='$id_sh' --id_ai='$id_ai' --env='$env
	php $racine_projet/app/console feuille_portage --flux=$FLUX J$JOUR J$JOUR --cd=$CD_COURANT --id_sh=$id_sh --id_ai=$id_ai --env=$env
	echo $(date "+%d/%m/%Y %H:%M:%S")" : Fin Generation du fichier "$FICHIER >> $fic_log 2>&1
done








#*-*-*-*-*- Tournees Neopress
echo '<h3><u>Reprise historique depot, tournee Neopress + Prise en compte des depot-commune Neopress pour le flux de jour</u></h3>'
echo '<pre>'
php ./app/console fusion_reprise_histo J+1 J+1 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
php ./app/console fusion_reprise_histo J+2 J+2 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'

# --------------- Fin SH -------------------
php app/console end_sh $id_ai --env=$env










# ---------- suppression le fichier temporaire de log
rm -f $fic_log




# environnement
env='prod'

# identifiant du sh
id_sh='rattrapage_integration_jour'


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


# Liste de codes des centres
CENTRE_DISTRIB=(028 029 040 042 049)

# Le jour a traiter _ Si "+0", on traite le jour J. Si "-1", on traite le J-1. Si "+1", on traite le J+1
JOUR="+1"

# Le moment de distribution [flux] - Si 1 , c'est NUIT. Si 2 , c'est JOUR
FLUX=2
JOUR_NUIT='jour'

# Le repertoire ou les feuilles de portage devront etre generes
REP_FEUILLE_PORTAGE='/var/www/html/Feuille_Portage_Fichiers'

# Jour a traiter au format AAAA-MM-JJ
DATE_A_TRAITER_Y_M_D=$(date +%Y-%m-%d --date=$JOUR" day")

# Jour a traiter au format AAAAMMJJ
DATE_A_TRAITER_YMD=$(date +%Y%m%d --date=$JOUR" day")


# FTP intranet
HOST_1='py-ch-prdtranet.adonis.mediapole.info'
USER_1='anonymous'
PASSWD_1='anonymous'

# FTP MROAD
HOST_2='10.150.10.11'
USER_2='mroadftp'
PASSWD_2='Tgj453r'



# Generation des feuilles de portage
for CD_COURANT in "${CENTRE_DISTRIB[@]}"
do
	FICHIER=$REP_FEUILLE_PORTAGE'/'$DATE_A_TRAITER_Y_M_D'/'$CD_COURANT'_'$FLUX'_'$DATE_A_TRAITER_YMD'_feuilleportage.pdf'
	echo $(date "+%d/%m/%Y %H:%M:%S")" : Debut Generation du fichier "$FICHIER >> $fic_log 2>&1
	echo $racine_projet'/app/console feuille_portage --flux='$FLUX' J'$JOUR' J'$JOUR' --cd='$CD_COURANT' --id_sh='$id_sh' --id_ai='$id_ai' --env='$env
	php $racine_projet/app/console feuille_portage --flux=$FLUX J$JOUR J$JOUR --cd=$CD_COURANT --id_sh=$id_sh --id_ai=$id_ai --env=$env
	echo $(date "+%d/%m/%Y %H:%M:%S")" : Fin Generation du fichier "$FICHIER >> $fic_log 2>&1
done

# --------------- Fin SH -------------------
php app/console end_sh $id_ai --env=$env