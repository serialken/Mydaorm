#!/bin/bash

########## Generation de feuille de portage pour le flux jour



# ---------- Parametrage global sh

# environnement
env='prod'

# identifiant du sh
id_sh='feuille_portage_jour_0'


#.............racine scripts
racine_projet='/var/www/html/MRoad'

#.............racine fichiers a traiter
racine_fic_a_traiter='/var/www/html/MRoad'

# sous repertoire des fichiers de logs sh
ssrep_log='../MRoad_Fichiers/Logs'

# fichier de log
fic_log=$racine_fic_a_traiter'/'$ssrep_log'/sh_'$id_sh'_'$(date '+%Y%m%d_%H%M%S')'.sh.log'

PY-CH-PRDTRANET = 'py-ch-prdtranet.adonis.mediapole.info'

cd $racine_projet

# Initialisation et identifiant en BDD du sh
php app/console init_sh $id_sh --env=$env 
id_ai=$?


#*-*-*-*-*- Feuilles de portage
echo '<h2>Feuilles de portage - Jour</h2>'
echo '<pre>'
#php ./app/console feuille_portage --flux=2 J+0 J+0 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1

php ./app/console feuille_portage --flux=2 J+0 J+0 --cd=029 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=2 J+0 J+0 --cd=033 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=2 J+0 J+0 --cd=040 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
#php ./app/console feuille_portage --flux=2 J+0 J+0 --cd=041 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=2 J+0 J+0 --cd=042 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console feuille_portage --flux=2 J+0 J+0 --cd=049 --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1


# Export des fichiers de portage generes vers le FTP de 10.150.0.18
php ./app/console transfert_ftp /var/www/html/Feuille_Portage_Fichiers/$(date +%Y-%m-%d --date="+0 day")/029_2_$(date +%Y%m%d --date="+0 day")_feuilleportage.pdf $PY-CH-PRDTRANET --dest_dir=/Fportage/029_portage --auth_mode=anonymous --pingtest=n --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console transfert_ftp /var/www/html/Feuille_Portage_Fichiers/$(date +%Y-%m-%d --date="+0 day")/033_2_$(date +%Y%m%d --date="+0 day")_feuilleportage.pdf $PY-CH-PRDTRANET --dest_dir=/Fportage/033_portage --auth_mode=anonymous --pingtest=n --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console transfert_ftp /var/www/html/Feuille_Portage_Fichiers/$(date +%Y-%m-%d --date="+0 day")/040_2_$(date +%Y%m%d --date="+0 day")_feuilleportage.pdf $PY-CH-PRDTRANET --dest_dir=/Fportage/040_portage --auth_mode=anonymous --pingtest=n --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console transfert_ftp /var/www/html/Feuille_Portage_Fichiers/$(date +%Y-%m-%d --date="+0 day")/042_2_$(date +%Y%m%d --date="+0 day")_feuilleportage.pdf $PY-CH-PRDTRANET --dest_dir=/Fportage/042_portage --auth_mode=anonymous --pingtest=n --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console transfert_ftp /var/www/html/Feuille_Portage_Fichiers/$(date +%Y-%m-%d --date="+0 day")/049_2_$(date +%Y%m%d --date="+0 day")_feuilleportage.pdf $PY-CH-PRDTRANET --dest_dir=/Fportage/049_portage --auth_mode=anonymous --pingtest=n --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1


# Export des fichiers de portage generes vers le FTP de 10.150.10.11
php ./app/console transfert_ftp /var/www/html/Feuille_Portage_Fichiers/$(date +%Y-%m-%d --date="+0 day")/029_2_$(date +%Y%m%d --date="+0 day")_feuilleportage.pdf 10.150.10.11 --dest_dir=/Fportage/029_portage --auth_mode=login --user=mroadftp --pwd=Tgj453r --pingtest=n --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console transfert_ftp /var/www/html/Feuille_Portage_Fichiers/$(date +%Y-%m-%d --date="+0 day")/033_2_$(date +%Y%m%d --date="+0 day")_feuilleportage.pdf 10.150.10.11 --dest_dir=/Fportage/033_portage --auth_mode=login --user=mroadftp --pwd=Tgj453r --pingtest=n --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console transfert_ftp /var/www/html/Feuille_Portage_Fichiers/$(date +%Y-%m-%d --date="+0 day")/040_2_$(date +%Y%m%d --date="+0 day")_feuilleportage.pdf 10.150.10.11 --dest_dir=/Fportage/040_portage --auth_mode=login --user=mroadftp --pwd=Tgj453r --pingtest=n --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console transfert_ftp /var/www/html/Feuille_Portage_Fichiers/$(date +%Y-%m-%d --date="+0 day")/042_2_$(date +%Y%m%d --date="+0 day")_feuilleportage.pdf 10.150.10.11 --dest_dir=/Fportage/042_portage --auth_mode=login --user=mroadftp --pwd=Tgj453r --pingtest=n --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1
php ./app/console transfert_ftp /var/www/html/Feuille_Portage_Fichiers/$(date +%Y-%m-%d --date="+0 day")/049_2_$(date +%Y%m%d --date="+0 day")_feuilleportage.pdf 10.150.10.11 --dest_dir=/Fportage/049_portage --auth_mode=login --user=mroadftp --pwd=Tgj453r --pingtest=n --id_sh=$id_sh --id_ai=$id_ai --env=$env > $fic_log 2>&1


cat $fic_log
echo '</pre>'
echo '<br /><br />'

# ---------- suppression le fichier temporaire de log
rm -f $fic_log


# --------------- Fin SH -------------------
php app/console end_sh $id_ai --env=$env