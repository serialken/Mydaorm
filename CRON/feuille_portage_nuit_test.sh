#!/bin/bash

########## Generation de feuille de portage pour le flux nuit



# ---------- Parametrage global sh

# environnement
env='prod'

# identifiant du sh
id_sh='test'


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



# Generation des feuilles de portage
for CD_COURANT in "${CENTRE_DISTRIB[@]}"
do
	FICHIER=$REP_FEUILLE_PORTAGE'/'$DATE_A_TRAITER_Y_M_D'/'$CD_COURANT'_'$FLUX'_'$DATE_A_TRAITER_YMD'_feuilleportage.pdf'
	echo $(date "+%d/%m/%Y %H:%M:%S")" : Debut Generation du fichier "$FICHIER >> $fic_log 2>&1
	echo $racine_projet'/app/console feuille_portage --flux='$FLUX' J'$JOUR' J'$JOUR' --cd='$CD_COURANT' --id_sh='$id_sh' --id_ai='$id_ai' --env='$env
	# php $racine_projet'/app/console feuille_portage --flux='$FLUX' J'$JOUR' J'$JOUR' --cd='$CD_COURANT' --id_sh='$id_sh' --id_ai='$id_ai' --env='$env > $fic_log 2>&1
	echo $(date "+%d/%m/%Y %H:%M:%S")" : Fin Generation du fichier "$FICHIER >> $fic_log 2>&1
done


# 1ere verification si les fichiers ont ete bien generes - Sinon, generation des feuilles de portage absentes
echo "--------------------" >> $fic_log 2>&1
echo "1ere verification si les fichiers des feuilles de portage ont ete bien generes - Sinon, generation des feuilles de portage absentes" >> $fic_log 2>&1
echo "--------------------" >> $fic_log 2>&1
for CD_COURANT in "${CENTRE_DISTRIB[@]}"
do
	FICHIER=$REP_FEUILLE_PORTAGE'/'$DATE_A_TRAITER_Y_M_D'/'$CD_COURANT'_'$FLUX'_'$DATE_A_TRAITER_YMD'_feuilleportage.pdf'
	
	if [ -f $FICHIER ]; then
		echo "$FICHIER OK"
	else
		echo $(date "+%d/%m/%Y %H:%M:%S")" : $FICHIER absent -> Nouvelle generation de ce fichier" >> $fic_log 2>&1
		echo $racine_projet'/app/console feuille_portage --flux='$FLUX' J'$JOUR' J'$JOUR' --cd='$CD_COURANT' --id_sh='$id_sh' --id_ai='$id_ai' --env='$env
		php $racine_projet/app/console feuille_portage --flux=$FLUX J$JOUR J$JOUR --cd=$CD_COURANT --id_sh=$id_sh --id_ai=$id_ai --env=$env
		echo $(date "+%d/%m/%Y %H:%M:%S")" : Fin Generation du fichier "$FICHIER >> $fic_log 2>&1
	fi	
done


# 2eme verification si les fichiers ont ete bien generes - Sinon, generation des feuilles de portage absentes
echo "--------------------" >> $fic_log 2>&1
echo "2eme verification si les fichiers des feuilles de portage ont ete bien generes - Sinon, generation des feuilles de portage absentes" >> $fic_log 2>&1
echo "--------------------" >> $fic_log 2>&1
for CD_COURANT in "${CENTRE_DISTRIB[@]}"
do
	FICHIER=$REP_FEUILLE_PORTAGE'/'$DATE_A_TRAITER_Y_M_D'/'$CD_COURANT'_'$FLUX'_'$DATE_A_TRAITER_YMD'_feuilleportage.pdf'
	
	if [ -f $FICHIER ]; then
		echo "$FICHIER OK"
	else
		echo $(date "+%d/%m/%Y %H:%M:%S")" : $FICHIER absent -> Nouvelle generation de ce fichier" >> $fic_log 2>&1
		echo $racine_projet'/app/console feuille_portage --flux='$FLUX' J'$JOUR' J'$JOUR' --cd='$CD_COURANT' --id_sh='$id_sh' --id_ai='$id_ai' --env='$env
		php $racine_projet/app/console feuille_portage --flux=$FLUX J$JOUR J$JOUR --cd=$CD_COURANT --id_sh=$id_sh --id_ai=$id_ai --env=$env
		echo $(date "+%d/%m/%Y %H:%M:%S")" : Fin Generation du fichier "$FICHIER >> $fic_log 2>&1
	fi	
done


#*-*-*-*-*- Verification de la presence des feuilles de portage 
echo '<h3><u>Verification de la presence des feuilles de portage Flux '$FLUX' et J'$JOUR'</u></h3>'
echo '<pre>'
#php $racine_projet/app/console feuille_portage_suivi J+$FLUX J+$FLUX --jn=$JOUR_NUIT --dest="andry.andrianiaina@amaury.com" --id_sh=$id_sh --id_ai=$id_ai --env=$env >> $fic_log 2>&1
php $racine_projet/app/console feuille_portage_suivi J+$FLUX J+$FLUX --jn=$JOUR_NUIT --id_sh=$id_sh --id_ai=$id_ai --env=$env >> $fic_log 2>&1
cat $fic_log
echo '</pre>'
echo '<br /><br />'



# --------------- Fin SH -------------------
php app/console end_sh $id_ai --env=$env