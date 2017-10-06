#!/bin/bash

########## Generation de feuille de portage pour le flux nuit



# ---------- Parametrage global sh

# environnement
env='prod'

# identifiant du sh
id_sh='test_ftp'


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

HOST='10.150.0.18'
USER='anonymous'
PASSWD='anonymous'
LOCALE_DIR='/var/www/html/Feuille_Portage_Fichiers/2016-06-21'
FILE='051_1_20160621_feuilleportage.pdf'
REMOTE_FILE='ooo.pdf'



# Liste de codes des centres
#CENTRE_DISTRIB=(004 007 010 013 014 018 023 024 028 029 031 033 034 035 036 039 040 041 042 045 051)
CENTRE_DISTRIB=(004 007 010 013 014 018 023 024 028 029 031 033 034 035 036 039 040 041 042 045 051)

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


#*-*-*-*-*- Feuilles de portage
echo '<h2>Test export FTP vers 10.150.0.18 - Fichiers de clients a servir - Nuit</h2>'
echo '<pre>'

REP_LOCAL=$REP_FEUILLE_PORTAGE/$DATE_A_TRAITER_Y_M_D

for CD_COURANT in "${CENTRE_DISTRIB[@]}"
do

	# Export vers le FTP de INTRANET (10.150.0.18)
	FIC_A_EXPORTER=$CD_COURANT'_'$FLUX'_'$DATE_A_TRAITER_YMD'_feuilleportage.pdf'
	REP_DISTANT_1='/Fportage/'$CD_COURANT'_portage'
	FIC_EXPORTE=$FIC_A_EXPORTER
	ftp -i -n $HOST_1 <<END_SCRIPT
quote USER $USER_1
quote PASS $PASSWD_1
cd $REP_DISTANT_1
lcd $REP_LOCAL
binary
put $FIC_A_EXPORTER $FIC_EXPORTE
quit
END_SCRIPT

	# Export vers le FTP de MROAD (10.150.10.11)
	FIC_A_EXPORTER=$CD_COURANT'_'$FLUX'_'$DATE_A_TRAITER_YMD'_feuilleportage.pdf'
	REP_DISTANT_2='/Fportage/'$CD_COURANT'_portage'
	FIC_EXPORTE=$FIC_A_EXPORTER
	ftp -i -n $HOST_2 <<END_SCRIPT
quote USER $USER_2
quote PASS $PASSWD_2
cd $REP_DISTANT_2
lcd $REP_LOCAL
binary
put $FIC_A_EXPORTER $FIC_EXPORTE
quit
END_SCRIPT

done

exit 0

#ftp -i -n $HOST <<END_SCRIPT
#quote USER $USER
#quote PASS $PASSWD
#cd /Fportage
#lcd $LOCALE_DIR
#binary
#put $FILE $REMOTE_FILE
#quit
#END_SCRIPT
#exit 0


cat $fic_log
echo '</pre>'
echo '<br /><br />'

# ---------- suppression le fichier temporaire de log
rm -f $fic_log


# --------------- Fin SH -------------------
php app/console end_sh $id_ai --env=$env