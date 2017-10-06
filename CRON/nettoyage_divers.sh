#!/bin/bash

################################### Suppression de divers fichiers anciens


# ---------- Parametrage global sh

# environnement
env='prod'

# identifiant du sh
id_sh='feuilles_quotidiennes'


#.............racine scripts
racine_projet='/var/www/html/MRoad'

# Libelle Environnement de l'application
LIB_APP_ENV='MROAD PROD'

#Repertoire des fichiers de traitement MROAD
REP_FIC_MROAD=/var/www/html/MRoad_Fichiers/

# Droits des repertoires a creer
REP_DROITS_MOD=770
REP_DROITS_PROP='mroad.apache'

# Parametres mail
MAIL_CMD='/usr/sbin/sendmail -t'
MAIL_SH='/var/www/MRoad_Scripts/mailHMTLContent.sh'
MAIL_EXPEDITEUR='LOG_IT@proximy.fr'
MAIL_DESTINATAIRES='LOG_IT@proximy.fr'
#MAIL_DESTINATAIRES='andry.andrianiaina@amaury.com'
MAIL_PREFIX_OBJET='!!! Suppression quotidienne de fichiers datés -'

# REGEX identifiant erreur dans les fichiers de Logs
REGEX_ERROR='|error        '

# Sous repertoire Logs
# -- Chemins vers les Logs
FIC_SORTIE=$REP_FIC_MROAD'nettoyage_divers_sh.txt'


cd $racine_projet

# Initialisation et identifiant en BDD du sh
php app/console init_sh $id_sh --env=$env 
id_ai=$?


# find /var/www/html -type f -iname "file.pdf" -mtime +90 -print -delete
# Suppression des fichiers de feuilles de portage superieurs a 90 jours
#find /var/www/html/Feuille_Portage_Fichiers -type f -iname "*feuilleportage.pdf" -mtime +90 -print | more
#find /var/www/html/Feuille_Portage_Fichiers -type f -iname "*feuilleportage.pdf" -mtime +90 -print -delete




FEUILLES_PORTAGES_SS_REP='/var/www/html/Feuille_Portage_Fichiers'
FEUILLES_PORTAGES_AGE_A_SUPPR=+10
FEUILLES_PORTAGES_REGEX_A_SUPPR='feuilleportage.pdf'

# La purge des fichiers du '/var/www/mroadftp/Fportage' necessite le compte "root" ou "mroadftp"
FTP_FEUILLES_PORTAGES_SS_REP='/var/www/mroadftp/Fportage'
FTP_FEUILLES_PORTAGES_AGE_A_SUPPR=+10
FTP_FEUILLES_PORTAGES_REGEX_A_SUPPR='feuilleportage.pdf'

FEUILLES_PORTAGES_BACKGROUND_SS_REP='/var/www/html/background'
FEUILLES_PORTAGES_BACKGROUND_AGE_A_SUPPR=+10
FEUILLES_PORTAGES_BACKGROUND_REGEX_A_SUPPR='file.pdf'

FIC_TXT_BKP_SS_REP='/var/www/html/MRoad_Fichiers/Bkp'
FIC_TXT_BKP_AGE_A_SUPPR=+7
FIC_TXT_BKP_REGEX_A_SUPPR='.txt'

FIC_TXT_TMP_SS_REP='/var/www/html/MRoad_Fichiers/Tmp'
FIC_TXT_TMP_AGE_A_SUPPR=+5
FIC_TXT_TMP_REGEX_A_SUPPR="*.txt"

FIC_LOGS='/var/www/html/MRoad_Fichiers/Logs'
FIC_LOGS_AGE_A_SUPPR=+5
FIC_LOG_REGEX_A_SUPPR='.log'



#----- Supprimer les fichiers ages
find $FEUILLES_PORTAGES_SS_REP -type f -iname \*$FEUILLES_PORTAGES_REGEX_A_SUPPR -mtime $FEUILLES_PORTAGES_AGE_A_SUPPR -print > $FIC_SORTIE
find $FEUILLES_PORTAGES_SS_REP -type f -iname \*$FEUILLES_PORTAGES_REGEX_A_SUPPR -mtime $FEUILLES_PORTAGES_AGE_A_SUPPR -print -delete 


find $FTP_FEUILLES_PORTAGES_SS_REP -type f -iname \*$FTP_FEUILLES_PORTAGES_REGEX_A_SUPPR -mtime $FTP_FEUILLES_PORTAGES_AGE_A_SUPPR -print > $FIC_SORTIE
find $FTP_FEUILLES_PORTAGES_SS_REP -type f -iname \*$FTP_FEUILLES_PORTAGES_REGEX_A_SUPPR -mtime $FTP_FEUILLES_PORTAGES_AGE_A_SUPPR -print -delete


find $FEUILLES_PORTAGES_BACKGROUND_SS_REP -type f -iname \*$FEUILLES_PORTAGES_BACKGROUND_REGEX_A_SUPPR -mtime $FEUILLES_PORTAGES_BACKGROUND_AGE_A_SUPPR -print >> $FIC_SORTIE
find $FEUILLES_PORTAGES_BACKGROUND_SS_REP -type f -iname \*$FEUILLES_PORTAGES_BACKGROUND_REGEX_A_SUPPR -mtime $FEUILLES_PORTAGES_BACKGROUND_AGE_A_SUPPR -print -delete


find $FIC_TXT_BKP_SS_REP -type f -iname \*$FIC_TXT_BKP_REGEX_A_SUPPR -mtime $FIC_TXT_BKP_AGE_A_SUPPR -print >> $FIC_SORTIE
find $FIC_TXT_BKP_SS_REP -type f -iname \*$FIC_TXT_BKP_REGEX_A_SUPPR -mtime $FIC_TXT_BKP_AGE_A_SUPPR -print -delete


find $FIC_TXT_TMP_SS_REP -type f -iname \*$FIC_TXT_TMP_REGEX_A_SUPPR -mtime $FIC_TXT_TMP_AGE_A_SUPPR -print >> $FIC_SORTIE
find $FIC_TXT_TMP_SS_REP -type f -iname \*$FIC_TXT_TMP_REGEX_A_SUPPR -mtime $FIC_TXT_TMP_AGE_A_SUPPR -print -delete



find $FIC_LOGS -type f -iname \*$FIC_LOG_REGEX_A_SUPPR -mtime $FIC_LOGS_AGE_A_SUPPR -print >> $FIC_SORTIE
find $FIC_LOGS -type f -iname \*$FIC_LOG_REGEX_A_SUPPR -mtime $FIC_LOGS_AGE_A_SUPPR -print -delete


MAIL_TMP=$REP_FIC_MROAD'tmp_mail_'`date +%Y%m%d%H%M%S`'_'$RANDOM'.txt'
printf '<html>' >> $MAIL_TMP
printf '<head>' >> $MAIL_TMP
printf '<meta http-equiv="content-type" content="text/html; charset=utf-8" />' >> $MAIL_TMP
printf '</head>' >> $MAIL_TMP
printf '<body>' >> $MAIL_TMP
printf "<p>Bonjour,</p>" >> $MAIL_TMP
printf "<p>Ci-après la liste des fichiers supprimés ce jour dans le cadre de nettoyage quotidien :</p>" >> $MAIL_TMP
printf '<div style="color: #8B4513;margin-left:4em;">' >> $MAIL_TMP
cat $FIC_SORTIE >> $MAIL_TMP
printf "</div>" >> $MAIL_TMP
printf "<p>Cordialement,</p><p>"$MAIL_EXPEDITEUR"</p>" >> $MAIL_TMP
printf '</body>' >> $MAIL_TMP
printf '</html>' >> $MAIL_TMP

$MAIL_SH "$MAIL_DESTINATAIRES" "$MAIL_PREFIX_OBJET `date '+%d/%m/%Y %H:%M:%S'`" $MAIL_TMP | $MAIL_CMD
#printf "$MAIL_SH "$MAIL_DESTINATAIRES" "$MAIL_PREFIX_OBJET "`date '+%d/%m/%Y %H:%M:%S'` $MAIL_TMP | $MAIL_CMD"

rm -v $MAIL_TMP

printf "\n"


# --------------- Fin SH -------------------
php app/console end_sh $id_ai --env=$env