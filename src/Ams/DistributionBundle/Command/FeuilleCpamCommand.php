<?php 
namespace Ams\DistributionBundle\Command;

//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Ams\SilogBundle\Command\GlobalCommand;
use Doctrine\DBAL\DBALException;

/**
 * 
 * Generation des collecte CPAM
 * 
 * 
 * Par defaut, on ne fait le calcul que pour le jour J+1 et pour tous les centres, pour toutes les tournees, pour tous les flux .
 * Parametre : 
 *          Jour minimum a calculer. C'est optionnel
 *          Jour maximum a calculer. C'est optionnel
 *          Liste des depots a traiter. C'est optionnel. Pour les renseigner : --cd=code_cd1,code_cd2,code_cd3,code_cd4,...
 *          Liste des codes tournees (Attention !!!! PAS de TOURNEE JOUR) a traiter. C'est optionnel. Pour les renseigner : --trn=code_tournee1,code_tournee2,code_tournee3,code_tournee4,...
 *          Liste des societes a traiter. C'est optionnel. Pour les renseigner : --soc=code_soc1,code_soc2,code_soc3,code_soc4,...
 *          Le flux a traiter. C'est optionnel. Pour les renseigner : --flux=N ou --flux=J ou --flux=N,J
 * Expl : J+1 J+5 
 * Si les parametres sont renseignes, le traitement concerne les jours de distribution de "jour minimum" a "jour maximum"
 * Expl : J-1. => calculs a faire concernent J-1, J, J+1, J+2 & J+3
 * 
 * Exemple de commande : php app/console feuille_cpam J+0 J+3 --cd=007,010 --trn=007NBF001,010NCA001 --soc=FI,AF --flux=N --env=dev
 * Exemple de commande : php app/console feuille_cpam J-4 J-4 --flux=1 --env=local
 * 
 * 
 * 
 * 
 * @author tcamara
 *
 */
class FeuilleCpamCommand extends GlobalCommand
{
      protected function configure() {
        $this->sNomCommande = 'feuille_cpam';
        $this->setName($this->sNomCommande);
        $sJourATraiter = "J+1";
  
        // Pour executer pour un depot, faire : php app/console   feuille_cpam code_depot flux_id J+1
        // Pour executer pour tous les depot, faire : php app/console   feuille_cpam all flux_id J+1
        $this
                ->setDescription("Génération des feuilles de collecte cpam")
                ->addArgument('code', InputArgument::REQUIRED, "Entrer le code du depot ou le code")
                ->addArgument('flux_id', InputArgument::REQUIRED, "Entrer le flux")
                ->addArgument('date_distrib', InputArgument::REQUIRED, "Entrer la date de distribution")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        parent::execute($input, $output); 

        $rep_collecte = $this->getContainer()->getParameter('REP_FEUILLE_CPAM');
  
        $dateDebut = new \DateTime();
        $this->oLog->info( " Debut Generation feuilles cpam: ". $dateDebut->format('H:i:s')."\n");

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
            $this->oLog->erreur("Une erreur s'est produit le jour a traiter doit etre au  Format : J<+Numerique> (Expl : J-1 ou J-2 ou J ...)", E_USER_ERROR);
            return;
        }

        $arrDepotId = array();
        if ($code != 'all') {
            $depot = $this->getContainer()->get('doctrine')->getRepository('AmsSilogBundle:Depot')->findOneByCode($input->getArgument('code'));
            if (!$depot) {
                $this->oLog->info("Le code dépot n'existe pas.");
                $this->oLog->erreur("Le code dépot n'existe pas: ", E_USER_ERROR);
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
            //$data = $this->getContainer()->get('ams.pai.emargement')->getData($depot_id, $flux, $date_distrib);
            $data = array();
            if (isset($data['employes']) && count($data['employes']) > 0) {
                $flux_lib = ($flux == 1)?"Nuit":"Jour";    
                $libelle = $code.'_'.$depot_id.'_'.$date_distrib.'_'.$flux.'_Emargement_'. $flux_lib ; 
                $this->generate($rep_feuille_cpam. $date_distrib.'/' , $libelle, 'AmsDistributionBundle:FeuilleCpam:feuille_cpam.html.twig', $data);
                $this->oLog->info("\n Feuille CPAM du " . $date_distrib . " depot:" . $depot->getLibelle() . " , flux :" . $flux);
            }
         }

        $dateDebut = new \DateTime();
        $this->oLog->info("\n Fin Generation feuille CPAM:" .$dateDebut->format('H:i:s'));
        return;
    }
}
