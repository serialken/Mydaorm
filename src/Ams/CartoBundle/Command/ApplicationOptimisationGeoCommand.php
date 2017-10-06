<?php

namespace Ams\CartoBundle\Command;

//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ams\SilogBundle\Lib\StringLocal;
use Ams\SilogBundle\Command\GlobalCommand;
use Ams\CartoBundle\Controller\CartoController;

class ApplicationOptimisationGeoCommand extends GlobalCommand {

    protected function configure() {
        $this->setName('application_optimisation_geo');
        /** php app/console application_optimisation_geo  * */
        
        $this
                ->setDescription("Application des optimisations de tournées par Geoconcept")
                ->addOption('req',NULL, InputOption::VALUE_OPTIONAL, "L'ID de requete optimisee a appliquer.",'')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
        $time_start = microtime(true);
        $this->oLog->info("Debut d'application des optimisations de tournees");

        $em = $this->getContainer()->get('doctrine')->getManager();
        $iReq = $input->getOption('req');
        
         // Numéro de requête invalide
        if (!preg_match("/^[0-9]*$/", $iReq)) {
            $this->oLog->info("ID de requete invalide: ".$iReq);
            exit();
        }
        
        // ID positif ?
        $iReqId = (int)$iReq;
        if ($iReqId <= 0){
            $this->oLog->info("ID de requete invalide: ".$iReqId);
            exit();
        }
        
        // Récupération de la requête
        $oReq = $em->getRepository('AmsAdresseBundle:RequeteExport')->findOneById($iReqId);
        if (empty($oReq)){
            $this->oLog->info("Requete non trouvée: ".$iReqId);
            exit();
        }
        
        // La requête a t-elle été optimisée ?
//        if ($oReq->getDateApplication() > new \DateTime()){
//            $this->oLog->info("Date d'application non atteinte: ".$oReq->getDateApplication()->format('d/m/Y'));
//            exit();
//        }
        
        $importRep = $em->getRepository('AmsAdresseBundle:ImportGeoconcept');
        /* @var $importRep ImportGeoconceptRepository */
        $aOptim = $importRep->getOptim($iReqId);
        
        if (empty($aOptim)){
            $this->oLog->info("Aucune donnee a importer trouvée pour la requete numero ".$iReqId);
            exit();
        }
        
        $iNbLignes = count($aOptim);
        $sLib = $oReq->getLibelle();
        $this->oLog->info($iNbLignes." ligne(s) a importer pour la requete numero ".$iReqId.' : '.$sLib);
        
        // Détection des points qui changent de tournée
        $aChangementsTournee = $importRep->detecterChangementTournee($iReqId);
        $iNbChangementsTournee = count($aChangementsTournee);
        $this->oLog->info($iNbChangementsTournee." changement(s) de tournee detectes pour la requete numero ".$iReqId);
        
        if ($iNbChangementsTournee > 0){
            // Préparation des informations en vue du basculement de tournée dans TD
            $aCritBasculTD = $importRep->recupererInfosChangementTourneeOptim($iReqId, $aChangementsTournee);
            if (!empty($aCritBasculTD)){
                foreach ($aCritBasculTD as $aBasculTD){
                    // Changement de tournée dans tournee_detail
            //        $em->getRepository('AmsAdresseBundle:TourneeDetail')->changePointDeTournee($aBasculTD);
                }
            }
            var_dump($aCritBasculTD);
        }
        
        // Détection des points à regrouper
        $aChangementsPoint = $importRep->detecterChangementPointLivraison($iReqId);
        $iNbChangementsPoint = count($aChangementsPoint);
        $this->oLog->info($iNbChangementsPoint." basculement(s) de points de livraison pour la requete numero ".$iReqId);
        
        if ($iNbChangementsPoint > 0){
            // Basculements de points
            // Dans TD
            
            // Dans CASL
            var_dump($aChangementsPoint);
        }
        
        exit($sLib . '-> '.$iNbLignes);
        
        // On récupère toutes les tournées disponibles dans CASL
        $aTournees = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getTourneesDuJour($sDate);
        
        if (!empty($aTournees)){
            $command = $this->getApplication()->find('update_ordre_tournee_casl');
            foreach ($aTournees as $tournee){
                $aArgs = array(
                    'command' => 'update_ordre_tournee_casl',
                    'mtj_code' => $tournee['code'],
                    'date' => $sDate
                );
                
                $input = new ArrayInput($aArgs);
                $returnCode = $command->run($input, $output);
            }
        }
        else{
            $this->oLog->info("Aucune tournee n'a ete trouvee dans CASL pour la date du ".$sDate);
            exit("Fin d'execution.");
        }
      
        $time_2 = microtime(true);
        $time = $time_2 - $time_start;
        $this->oLog->info("Temps de requete " . sprintf("%.2f", $time) . ' sec');
        $this->oLog->info("Fin de mise a jour des tournees CASL");
        return;
    }

}
