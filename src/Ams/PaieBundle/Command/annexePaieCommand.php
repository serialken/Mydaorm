<?php

namespace Ams\PaieBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Ams\SilogBundle\Command\GlobalCommand;
use Doctrine\DBAL\DBALException;

/**
 * 
 * Génération des annnexes tournées 
 * Parametre : 
 *         
 *          depot_id obligatoire 
 *          flux_id obligatoire
 *          input_date_traitement 
 *          date_fin obligatoire

 * Exemple de commande : php app/console annexe_paie depot_id, flux_id ,input_date_traitement
 * input_date_traitement : M, M-1, M-2 .... ou anneemois 201411, 201501 ... --id_sh=cron_test --id_ai=1 --env=prod
 *
 */
class AnnexePaieCommand extends GlobalCommand {

    protected function configure() {
        $this->sNomCommande = 'annexe_paie';
        $this->setName($this->sNomCommande);
        $this->setDescription("Génération des annexes paie pour le depot  et le flux.")
            ->addArgument('code', InputArgument::REQUIRED, "Le code du depot ou le code")
            ->addArgument('flux_id', InputArgument::REQUIRED, "Le flux")
            ->addArgument('input_date_traitement', InputArgument::REQUIRED, "Date de début")
            ->addOption('id_sh',null, InputOption::VALUE_REQUIRED, 'Libelle du CRON')
            ->addOption('id_ai',null, InputOption::VALUE_REQUIRED, 'Id du CRON')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
        if($input->getOption('id_sh')){
            $idSh = $input->getOption('id_sh');
        }
        if($input->getOption('id_ai')){
            $idAi = $input->getOption('id_ai');
        }
        if($input->getOption('id_ai') && $input->getOption('id_sh')){
            $this->associateToCron($idAi,$idSh);
        }
        $now = new \DateTime();
        $this->oLog->info(" Debut Generation des annexes paie: " . $now->format('H:i:s') . "\n");
        $input_date_traitement = $input->getArgument('input_date_traitement');
        $flux_id = $input->getArgument('flux_id');
        $code = $input->getArgument('code');
        $mois = $this->getContainer()->get('doctrine')->getRepository('AmsPaieBundle:PaiRefMois')->findOneByAnneemois($input_date_traitement);
        $now->setTime(0, 0, 0);
        $dateTraitement = '';
        
        // Appelé à la cloture
        if (isset($mois)) {
            $moisId = $input_date_traitement;
            $date_debut = $mois->getDateDebut()->format('Y-m-d');
            $date_fin = $mois->getDateFin()->format('Y-m-d');
            $provisoire=false;
            $annexetype='ANNEXE_CLOTURE';
            
        // Appelé par annexe_paie_hebdo.sh
        } elseif (preg_match('/^J([\-\+][0-9]+)?$/', $input_date_traitement, $jour)) { 
            if (isset($jour[1])) {
                $nbJour = intval($jour[1]);
                if ($nbJour < 0) {
                    $dateDistribATraiter = $now->sub(new \DateInterval('P' . abs($nbJour) . 'D'));
                } else {
                    $dateDistribATraiter = $now->add(new \DateInterval('P' . $nbJour . 'D'));
                }
            } else {
                $dateDistribATraiter = $now;
            }
            $dateTraitement = $dateDistribATraiter->format('Y-m-d');
            $date_fin = $dateDistribATraiter->format('Y-m-d');
            $mois = $this->getContainer()->get('doctrine')->getRepository('AmsPaieBundle:PaiRefMois')->getAnneemoisByDate($date_fin);
            $date_debut = $mois['date_debut'];
            $moisId = $mois['anneemois'];            
            $provisoire=true;
            $annexetype='ANNEXE_HEBDO';
            
        // Appelé par annexe_paie_mensuelle.sh
        } else if (preg_match('/^M([\-][0-9]+)?$/', $input_date_traitement, $mois_traitement)) { 
            $mois = $this->getContainer()->get('doctrine')->getRepository('AmsPaieBundle:PaiIntTraitement')->getGenereMensuel($flux_id);
            $moisId = $mois['anneemois'];            
            if (is_array($mois) && isset($mois['anneemois'])){
                $moisId = $mois['anneemois'];            
                $date_debut = $mois['date_debut'];
                $date_fin = $mois['date_fin'];
                $provisoire=true;
                $annexetype='ANNEXE_MENSUELLE';
            } else {
                $this->oLog->info(" Rien à générer\n");
                return;
            }
        } else {
             $this->suiviCommand->setMsg("Une erreur s'est produit le 3eme parametre doit etre aux formats M-1, 201401, 201402 .....");
            $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
            $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
            $this->suiviCommand->setEtat("KO");
            $this->oLog->erreur("Une erreur s'est produit le 3eme parametre doit etre aux formats M-1, 201401, 201402 .....", E_USER_ERROR);
            $this->registerError();
            if($input->getOption('id_ai') && $input->getOption('id_sh')){
                $this->registerErrorCron($idAi);
            }
            return;
        }
        $this->getContainer()->get('doctrine')->getRepository('AmsPaieBundle:PaiIntLog')->debutAnneeMois($idtrt,0,$annexetype,$dateTraitement,0,$flux_id,$moisId);

        $arrDepotId = array();
        if ($code != 'all') {
            $depot = $this->getContainer()->get('doctrine')->getRepository('AmsSilogBundle:Depot')->findOneByCode($code);
            if (!$depot) {
                $this->suiviCommand->setMsg("Le code dépot n'existe pas: '".$code."'  .");
                $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
                $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                $this->suiviCommand->setEtat("KO");
                $this->oLog->erreur("Le code dépot n'existe pas: ", E_USER_ERROR);
                $this->registerError();
                if($input->getOption('id_ai') && $input->getOption('id_sh')){
                    $this->registerErrorCron($idAi);
                }
                $this->getContainer()->get('doctrine')->getRepository('AmsPaieBundle:PaiIntLog')->logLevel($idtrt,'ERREUR',"Le code dépot n'existe pas : $code",0);
                $this->getContainer()->get('doctrine')->getRepository('AmsPaieBundle:PaiIntLog')->erreur($idtrt,'');
                return;
            }
            $arrDepotId = array($depot->getCode() => $depot->getId());
        } else {
            $depots = $this->getContainer()->get('doctrine')->getRepository('AmsSilogBundle:Depot')->getListeDepot();
            foreach ($depots as $depot) {
                $arrDepotId[$depot->getCode()] = $depot->getId();
            }
        }

        $rep_annexe_paie = $this->getContainer()->getParameter('REP_ANNEXE_PAIE');
        $this->oLog->info("\n  Sauvegarde dans le répertoire : $rep_annexe_paie\n");
        $this->oLog->info("\n  Annexe paie du $date_debut au $date_fin \n");
        $this->getContainer()->get('doctrine')->getRepository('AmsPaieBundle:PaiIntLog')->logLevel($idtrt,'ANNEXE',"Sauvegarde dans le répertoire : $rep_annexe_paie",4);
        $this->getContainer()->get('doctrine')->getRepository('AmsPaieBundle:PaiIntLog')->logLevel($idtrt,'ANNEXE',"Annexe paie du $date_debut au $date_fin",4);

        foreach ($arrDepotId as $code => $depot_id) {
            $this->getContainer()->get('doctrine')->getRepository('AmsPaieBundle:PaiIntLog')->logLevel($idtrt,'ANNEXE',"Dépôt : $code",4);
            $dir = $rep_annexe_paie . $code . '/';
            $dirTmp = $rep_annexe_paie . $code . '/TMP_' . $flux_id . '/';
            $this->cree_repertoire($dirTmp);
            $employes = $this->getContainer()->get('doctrine')->getRepository('AmsEmployeBundle:Employe')->selectComboAnnexe($depot_id, $flux_id, $moisId, $dateTraitement);
            foreach ($employes as $employe) {
                $now = new \DateTime();
                $this->oLog->info($now->format('H:i:s') . "\t" . str_replace("/","",$employe["libelle"]) . "\n");
                $this->getContainer()->get('doctrine')->getRepository('AmsPaieBundle:PaiIntLog')->log($idtrt,'ANNEXE',str_replace("/","",$employe["libelle"]));
                ///ABDEREMANE_AHMED_(_900001_21/03/2015_->_20/04/2015_).pdf
                $filename = $dirTmp . str_replace("/","",$employe["libelle"]) . ".pdf";
                $this->getContainer()->get('ams.pai.annexe_paie')->writePDF($depot_id, $flux_id, $employe["id"], $filename, $provisoire);
            }
            $prefix = $code . '_' . $depot_id . '_' . $flux_id . '_' . $date_debut . '_au_' . $date_fin . '_annexe_paie.pdf';
            if ($flux_id == 1) {
                $filename = "proximy_" . $prefix;
            } else {
                $filename = "mediapresse_" . $prefix;
            }
            
            if (is_dir($dirTmp)) {
                // Un seul fichier, on recopie
                if (count(array_diff(scandir($dirTmp), array('.', '..'))) == 1) {
                    $this->getContainer()->get('doctrine')->getRepository('AmsPaieBundle:PaiIntLog')->logLevel($idtrt,'ANNEXE',"Génération du fichier : $dir$filename",4);
                    exec("cp -f ".$dirTmp.array_diff(scandir($dirTmp), array('.', '..'))[2] ." ". $dir . $filename);
                // Pleusieurs fichiers, on merge
                } elseif (count(array_diff(scandir($dirTmp), array('.', '..'))) > 1) {
                    unlink($dir . $filename);
                    exec("cd ".$dirTmp." && pdfunite * " . $dir . $filename);
                    //exec("pdftk " . $dirTmp . "* cat output " . $dir . $filename);
                }
                $this->delTree($dirTmp);
            }
           $this->oLog->info("\n Fin generation des annexes paie pour le depot:" . $code . "\n");
        }
        $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
        $this->endTraitement();
        $fin = new \DateTime();
        $this->oLog->info("\n Fin generation des annexes paie :" . $fin->format('H:i:s'));
        $this->getContainer()->get('doctrine')->getRepository('AmsPaieBundle:PaiIntLog')->fin($idtrt,$annexetype);
        return;
    }

}
