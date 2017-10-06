#!/bin/bash

# environnement
env='prod'

# identifiant du sh
id_sh='logs_traitement'

#.............racine scripts
racine_projet='/var/www/html/MRoad'

cd $racine_projet

# Initialisation et identifiant en BDD du sh
php app/console init_sh $id_sh --env=$env 
id_ai=$?




# Traitement des fichiers de LOGs du repertoire "/var/www/html/MRoad_Fichiers/Logs"

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
MAIL_EXPEDITEUR='LOG-IT-MROAD@proximy.fr'
MAIL_DESTINATAIRES_ERREUR='LOG-IT@proximy.fr,jhodenc@gmail.com'
MAIL_PREFIX_OBJET_ERREUR='!!! ERREUR MROAD - '

# REGEX identifiant erreur dans les fichiers de Logs
REGEX_ERROR='|error        '

# Sous repertoire Logs
# -- Chemins vers les Logs
SS_REP_LOG=$REP_FIC_MROAD'Logs/'

SS_REP_LOG_TRAITES=$SS_REP_LOG'Traites/'
AGE_LOG_TRAITES_A_SUPPR=+30 # On supprime les fichiers plus de 30 jours

SS_REP_LOG_TRAITES_ERR=$SS_REP_LOG_TRAITES'Err/'
AGE_LOG_TRAITES_ERR_A_SUPPR=+30 # On supprime les fichiers plus de 30 jours

SS_REP_LOG_NEPASTRAITER=$SS_REP_LOG'Ne_pas_traiter/'
REGEX_LOG_NEPASTRAITER=$SS_REP_LOG'\(_20.*\.log$\|feuille_portage.*\.log$\|maj_crm_distrib.*\.log$\|maj_ordres_tournee.*\.log$\|sendmail_recap_tournees.*\.log$\|suivi_activite.*\.log$\|test_.*\.log$\)'
AGE_LOG_NEPASTRAITER_A_SUPPR=+10 # On supprime les fichiers plus de 10 jours

# -- Chemins vers les Logs recessant les erreurs
ERR='Err/'
SS_REP_LOG_ERR=$SS_REP_LOG$ERR
SS_REP_LOG_ERR_TRAITES=$SS_REP_LOG_ERR'Traites/'
SS_REP_LOG_ERR_NEPASTRAITER=$SS_REP_LOG_ERR'Ne_pas_traiter/'
REGEX_LOG_ERR_NEPASTRAITER=$SS_REP_LOG'\('$ERR'_err_2.*\.log$\|'$ERR'feuille_portage_err.*\.log$\|'$ERR'feuille_portage_suivi_err.*\.log$\|'$ERR'monitoring_imprimante_err.*\.log$\|'$ERR'sendmail_activite_err.*\.log$\|'$ERR'sendmail_recap_tournees_err.*\.log$\|'$ERR'test_.*_err.*\.log$\)'
AGE_LOG_ERR_NEPASTRAITER_A_SUPPR=+10 # On supprime les fichiers plus de 10 jours






FIC_SORTIE=$REP_FIC_MROAD'test_sh.txt'

#................ Traitement des Logs

if [ ! -d $SS_REP_LOG_TRAITES ];then
    mkdir $SS_REP_LOG_TRAITES
	chmod -R $REP_DROITS_MOD $SS_REP_LOG_TRAITES
	chown -R $REP_DROITS_PROP $SS_REP_LOG_TRAITES
fi

if [ ! -d $SS_REP_LOG_TRAITES_ERR ];then
    mkdir $SS_REP_LOG_TRAITES_ERR
	chmod -R $REP_DROITS_MOD $SS_REP_LOG_TRAITES_ERR
	chown -R $REP_DROITS_PROP $SS_REP_LOG_TRAITES_ERR
fi

if [ ! -d $SS_REP_LOG_NEPASTRAITER ];then
    mkdir $SS_REP_LOG_NEPASTRAITER
	chmod -R $REP_DROITS_MOD $SS_REP_LOG_NEPASTRAITER
	chown -R $REP_DROITS_PROP $SS_REP_LOG_NEPASTRAITER
fi

if [ ! -d $SS_REP_LOG_ERR_TRAITES ];then
    mkdir $SS_REP_LOG_ERR_TRAITES
	chmod -R $REP_DROITS_MOD $SS_REP_LOG_ERR_TRAITES
	chown -R $REP_DROITS_PROP $SS_REP_LOG_ERR_TRAITES
fi

if [ ! -d $SS_REP_LOG_ERR_NEPASTRAITER ];then
    mkdir $SS_REP_LOG_ERR_NEPASTRAITER
	chmod -R $REP_DROITS_MOD $SS_REP_LOG_ERR_NEPASTRAITER
	chown -R $REP_DROITS_PROP $SS_REP_LOG_ERR_NEPASTRAITER
fi




#----- Deplacer les fichiers a ne pas verifier le contenu (== a ne pas traiter lors des verifications des fichiers de logs)
#find $SS_REP_LOG -type f -regex $REGEX_LOG_NEPASTRAITER -print >> $FIC_SORTIE
find $SS_REP_LOG -maxdepth 1 -type f -regex $REGEX_LOG_NEPASTRAITER -print -exec mv {} $SS_REP_LOG_NEPASTRAITER \;

#find $SS_REP_LOG_ERR -type f -regex $REGEX_LOG_ERR_NEPASTRAITER -print >> $FIC_SORTIE
find $SS_REP_LOG_ERR -maxdepth 1 -type f -regex $REGEX_LOG_ERR_NEPASTRAITER -print -exec mv {} $SS_REP_LOG_ERR_NEPASTRAITER \;



#----- Supprimer les fichiers ages a ne pas verifier le contenu (== a ne pas traiter lors des verifications des fichiers de logs)
#find $SS_REP_LOG_TRAITES -maxdepth 1 -type f -mtime $AGE_LOG_TRAITES_A_SUPPR -print >> $FIC_SORTIE
find $SS_REP_LOG_TRAITES -maxdepth 1 -type f -mtime $AGE_LOG_TRAITES_A_SUPPR -print -delete

#find $SS_REP_LOG_TRAITES_ERR -maxdepth 1 -type f -mtime $AGE_LOG_TRAITES_ERR_A_SUPPR -print >> $FIC_SORTIE
find $SS_REP_LOG_TRAITES_ERR -maxdepth 1 -type f -mtime $AGE_LOG_TRAITES_ERR_A_SUPPR -print -delete

#find $SS_REP_LOG_NEPASTRAITER -type f -mtime $AGE_LOG_NEPASTRAITER_A_SUPPR -print >> $FIC_SORTIE
find $SS_REP_LOG_NEPASTRAITER -type f -mtime $AGE_LOG_NEPASTRAITER_A_SUPPR -print -delete

#find $SS_REP_LOG_ERR_NEPASTRAITER -type f -mtime $AGE_LOG_ERR_NEPASTRAITER_A_SUPPR -print >> $FIC_SORTIE
find $SS_REP_LOG_ERR_NEPASTRAITER -type f -mtime $AGE_LOG_ERR_NEPASTRAITER_A_SUPPR -print -delete



#LISTE_FIC_CONTENANT_ERR=`find $SS_REP_LOG -maxdepth 1 -type f -iname "*.log" -print -exec grep -l "|error        " {} \;` # -maxdepth 1 == on ne descend pas dans les sous repertoire
LISTE_FIC_LOG_A_TRAITER=`find $SS_REP_LOG -maxdepth 1 -type f -iname "*.log" -print` # -maxdepth 1 == on ne descend pas dans les sous repertoire
for fic_log in $LISTE_FIC_LOG_A_TRAITER
do
    if grep -l $REGEX_ERROR $fic_log; then
		MAIL_TMP=$REP_FIC_MROAD'tmp_mail_'`date +%Y%m%d%H%M%S`'_'$RANDOM'.txt'
		printf '<html>' >> $MAIL_TMP
		printf '<head>' >> $MAIL_TMP
		printf '<meta http-equiv="content-type" content="text/html; charset=utf-8" />' >> $MAIL_TMP
		printf '</head>' >> $MAIL_TMP
		printf '<body>' >> $MAIL_TMP
		printf "Bonjour,\n\n" >> $MAIL_TMP
		printf "Dans $LIB_APP_ENV, des erreurs ont été détectées dans le fichier de LOG \"$fic_log\".\n\n" >> $MAIL_TMP
		printf "Ci-après son contenu :\n\n" >> $MAIL_TMP
		printf '<div style="color: #8B4513;">' >> $MAIL_TMP
		cat $fic_log >> $MAIL_TMP
		printf "</div>" >> $MAIL_TMP
		printf "\n\nCordialement,\n"$MAIL_EXPEDITEUR"\n" >> $MAIL_TMP
		printf '</body>' >> $MAIL_TMP
		printf '</html>' >> $MAIL_TMP
		sed -i 's/$/<br \/>/' $MAIL_TMP # Remplace "\n" par "<br>"
		sed -i -e 's/|error/|<b style="color: #FF0000;">error<\/b>/g' $MAIL_TMP # Remplace "\n" par "<br>"
	    
		$MAIL_SH "$MAIL_DESTINATAIRES_ERREUR" "$MAIL_PREFIX_OBJET_ERREUR`basename $fic_log`" $MAIL_TMP | $MAIL_CMD
		#printf "$MAIL_SH $MAIL_DESTINATAIRES_ERREUR $MAIL_PREFIX_OBJET_ERREUR`basename $fic_log` $MAIL_TMP | $MAIL_CMD"
		
		mv $fic_log $SS_REP_LOG_TRAITES_ERR`basename $fic_log`
		
		rm -v $MAIL_TMP
		
	else
	    mv $fic_log $SS_REP_LOG_TRAITES`basename $fic_log`
		#echo '0 error : '$fic_log >> $FIC_SORTIE
	fi
done

printf "\n"



# --------------- Fin SH -------------------
php app/console end_sh $id_ai --env=$env