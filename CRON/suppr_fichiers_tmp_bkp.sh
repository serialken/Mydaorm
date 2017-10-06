#!/bin/bash
# Environnement
env='prod'

# identifiant du sh
id_sh='suppr_fichiers_tmp_bkp'

# racine scripts
racine_projet='/var/www/html/MRoad'

cd $racine_projet

# Initialisation et identifiant en BDD du sh
php app/console init_sh $id_sh --env=$env 
id_ai=$?

# Suppression des fichiers ages des repertoires "/var/www/html/MRoad_Fichiers/Bkp" et "/var/www/html/MRoad_Fichiers/Tmp"

# Libelle Environnement de l'application
LIB_APP_ENV='MROAD PROD'

#Repertoire des fichiers de traitement MROAD
REP_FIC_MROAD=/var/www/html/MRoad_Fichiers/

# Droits des repertoires a creer
REP_DROITS_MOD=770
REP_DROITS_PROP='mroad.apache'

# Sous repertoire 
# -- Chemins vers les fichiers de Backup
SS_REP_BKP=$REP_FIC_MROAD'Bkp/'

# -- Chemins vers les fichiers temporaires 
SS_REP_TMP=$REP_FIC_MROAD'Tmp/'

# REGEX des fichiers a supprimer
REGEX_BKP_A_SUPPR='\(.*\.csv\|.*\.log\|.*\.txt\)'
REGEX_TMP_A_SUPPR='\(.*\.csv\|.*\.log\|.*\.txt\)'


# Age des fichiers a supprimer
AGE_BKP_A_SUPPR=+125 # On supprime les fichiers plus de 65 jours
AGE_TMP_A_SUPPR=+60 # On supprime les fichiers plus de 65 jours

FIC_BKP_SORTIE=$REP_FIC_MROAD'fic_bkp_a_suppr.txt'
FIC_TMP_SORTIE=$REP_FIC_MROAD'fic_tmp_a_suppr.txt'


#----- Supprimer les fichiers ages 
#find $SS_REP_BKP -type f -mtime $AGE_BKP_A_SUPPR -regex $REGEX_BKP_A_SUPPR -print > $FIC_BKP_SORTIE
find $SS_REP_BKP -type f -mtime $AGE_BKP_A_SUPPR -regex $REGEX_BKP_A_SUPPR -print -delete

#find $SS_REP_TMP -type f -mtime $AGE_TMP_A_SUPPR -regex $REGEX_TMP_A_SUPPR -print > $FIC_TMP_SORTIE
find $SS_REP_TMP -type f -mtime $AGE_TMP_A_SUPPR -regex $REGEX_TMP_A_SUPPR -print -delete

# --------------- Fin SH -------------------
php app/console end_sh $id_ai --env=$env