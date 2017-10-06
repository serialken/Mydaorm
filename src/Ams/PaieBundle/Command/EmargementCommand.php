<?php

namespace Ams\PaieBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Ams\SilogBundle\Command\GlobalCommand;
use Doctrine\DBAL\DBALException;
use HTML2PDF;

/**
 * 
 * Pouvoir générer les feuilles d'émargement
 * Parametre : 
 *          date_distrib. :  obligatoire( J, J+1, J-1 ...)
 *          depot_id obligatoire 
 *          flux_id obligatoire
 */
class EmargementCommand extends GlobalCommand {

    protected function configure() {
        $this->sNomCommande = 'feuille_emargement';
        $this->setName($this->sNomCommande);
        $sJourATraiter = "J+1";
  
        // Pour executer pour un depot, faire : php app/console   feuille_emargement code_depot flux_id J+1
        // Pour executer pour tous les depot, faire : php app/console   feuille_emargement all flux_id J+1
        $this
                ->setDescription("Génération des feuilles d'émargement pour le depot  et le flux.")
                ->addArgument('code', InputArgument::REQUIRED, "Entrer le code du depot ou le code")
                ->addArgument('flux_id', InputArgument::REQUIRED, "Entrer le flux")
                ->addArgument('date_distrib', InputArgument::REQUIRED, "Entrer la date de distribution")
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
        $rep_emargement = $this->getContainer()->getParameter('REP_EMARGEMENT');
  
        $dateDebut = new \DateTime();
        $this->oLog->info( " Debut Generation feuille emargement: ". $dateDebut->format('H:i:s')."\n");

        $input_date_distrib = $input->getArgument('date_distrib');
        $flux = $input->getArgument('flux_id');
        $code = $input->getArgument('code');
        
       if(preg_match('/^J([\-\+][0-9]+)?$/', $input_date_distrib, $jour) ) {
            $nbjour= 0;
            if(isset($jour[1]))
            {
                $nbJour  = intval($jour[1]);
                $oDateDuJour    = new \DateTime();
                $oDateDuJour->setTime(0, 0, 0);
                $dateDistribATraiter   = $oDateDuJour;
                if($nbJour<0) {
                    $dateDistribATraiter   = $oDateDuJour->sub(new \DateInterval('P'.abs($nbJour).'D'));
                }
                else {
                    $dateDistribATraiter   = $oDateDuJour->add(new \DateInterval('P'.$nbJour.'D'));
                }
                $date_distrib = $dateDistribATraiter->format('Y-m-d');
            } else {
                 $date_distrib = $dateDebut->format('Y-m-d');
            }
        }else{
            $this->suiviCommand->setMsg("Une erreur s'est produit le jour a traiter doit etre au  Format : J<+Numerique> (Expl : J-1 ou J-2 ou J ...)");
            $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
            $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
            $this->suiviCommand->setEtat("KO");
            $this->oLog->erreur("Une erreur s'est produit le jour a traiter doit etre au  Format : J<+Numerique> (Expl : J-1 ou J-2 ou J ...)", E_USER_ERROR);
            $this->registerError();
            if($input->getOption('id_ai') && $input->getOption('id_sh')){
                $this->registerErrorCron($idAi);
            }
            return;
        }

        $arrDepotId = array();
        if ($code != 'all') {
            $depot = $this->getContainer()->get('doctrine')->getRepository('AmsSilogBundle:Depot')->findOneByCode($input->getArgument('code'));
            if (!$depot) {
                $this->suiviCommand->setMsg("Le code dépot: ".$input->getArgument('code')." n'existe pas.");
                $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
                $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                $this->suiviCommand->setEtat("KO");
                $this->oLog->erreur("Le code dépot: ".$input->getArgument('code')." n'existe pas.", E_USER_ERROR);
                $this->registerError();
                if($input->getOption('id_ai') && $input->getOption('id_sh')){
                    $this->registerErrorCron($idAi);
                }
                return;
            }
           $arrDepotId = array($depot->getCode()=>$depot->getId());
        }else {
             $depots = $this->getContainer()->get('doctrine')->getRepository('AmsSilogBundle:Depot')->getListeDepot();
             foreach ($depots as $depot){
                 $arrDepotId[$depot->getCode()] =  $depot->getId();
             }    
        }

        foreach ($arrDepotId as $code=>$depot_id) {
            $data = $this->getContainer()->get('ams.pai.emargement')->getData($depot_id, $flux, $date_distrib);
            $data['meta'] = true;
            if (isset($data['employes']) && count($data['employes']) > 0) {
                $flux_lib = ($flux == 1)?"Nuit":"Jour";    
                $libelle = $code.'_'.$depot_id.'_'.$date_distrib.'_'.$flux.'_Emargement_'. $flux_lib ; 
                $this->generate($rep_emargement. $date_distrib.'/' , $libelle, 'AmsPaieBundle:PaiEmargement:emargement.html.twig', $data);
                $this->oLog->info("\n Feuille emargement du " . $date_distrib . " depot:" . $depot->getLibelle() . " , flux :" . $flux);
            }
         }
        $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
        $this->endTraitement();
        $dateDebut = new \DateTime();
        $this->oLog->info("\n Fin Generation feuille emargement:" .$dateDebut->format('H:i:s'));
        return;
    }
}
