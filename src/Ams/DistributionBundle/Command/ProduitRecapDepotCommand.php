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
 * Calcul des infos produits servis par chaque depot.
 * Par defaut, on ne fait le calcul que le jour J+1.
 * Parametre : 
 *          Jour minimum a calculer. C'est optionnel
 *          Jour maximum a calculer. C'est optionnel
 * Expl : J+1 J+5
 * Si les parametres sont renseignes, le traitement concerne les jours de distribution de "jour minimum" a "jour maximum"
 * Expl : J-1. => calculs a faire concernent J-1, J, J+1, J+2 & J+3
 * 
 * Exemple de commande : php app/console produit_recap_depot J+0 J+3 --id_sh=cron_test --id_ai=1  
 * 
 * @author aandrianiaina
 *
 */
class ProduitRecapDepotCommand extends GlobalCommand
{
    protected function configure()
    {
    	$this->sNomCommande	= 'produit_recap_depot';
        $sJourATraiterMinParDefaut = "J+1";
        $sJourATraiterMaxParDefaut = "J+2";
        $this->jourATraiterMaxRef   = 3;
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console produit_recap_depot Expl : php app/console produit_recap_depot J+1 J+3
        $this
            ->setDescription('Calcul des infos produits servis par chaque depot.')
            ->addArgument('jour_a_traiter_min', InputArgument::OPTIONAL, 'Jour a traiter Min. Format : J<+Numerique> (Expl : J+1 ou J+2 ou J ...)', $sJourATraiterMinParDefaut)
            ->addArgument('jour_a_traiter_max', InputArgument::OPTIONAL, 'Jour a traiter Max. Format : J<+Numerique> (Expl : J+1 ou J+2 ou J ...)', $sJourATraiterMaxParDefaut)
            ->addOption('id_sh',null, InputOption::VALUE_REQUIRED, 'Libelle du CRON')
            ->addOption('id_ai',null, InputOption::VALUE_REQUIRED, 'Id du CRON')
        
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
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
    	$this->oLog->info(date("d/m/Y H:i:s : ")."Debut Calcul des infos produits servis par chaque depot - Commande : ".$this->sNomCommande);    
        
        $em    = $this->getContainer()->get('doctrine')->getManager();
        
    	$sJourATraiterMin  = $input->getArgument('jour_a_traiter_min');
        $sJourATraiterMax  = $input->getArgument('jour_a_traiter_max');
        $iJourATraiter  = 0;
        if(preg_match('/^J([\-\+][0-9]+)?$/', $sJourATraiterMin, $aJourATraiterMin) && preg_match('/^J([\-\+][0-9]+)?$/', $sJourATraiterMax, $aJourATraiterMax))
        {
            $iJourATraiterMin = 0;
            $iJourATraiterMax = 0;
            if(isset($aJourATraiterMin[1]))
            {
                $iJourATraiterMin  = intval($aJourATraiterMin[1]);
            }
            
            if(isset($aJourATraiterMax[1]))
            {
                $iJourATraiterMax  = intval($aJourATraiterMax[1]);
            }
            
            if($iJourATraiterMax >= $iJourATraiterMin)
            {
                $aJourATraiter   = array();
                for($i=$iJourATraiterMin; $i<=$iJourATraiterMax; $i++)
                {
                    $aJourATraiter[]    = $i;
                }
                
                $produitRecapDepotRepo   = $this->getContainer()->get('doctrine')->getRepository('AmsDistributionBundle:ProduitRecapDepot');
                foreach($aJourATraiter as $iJourATraiter)
                {
                    $this->oLog->info("- Debut Traitement jour J".(($iJourATraiter<0)?"-":'+').abs($iJourATraiter));
                    try {
                        // supprime des donnees dont la date de Distrib est J+$iJourATraiter
                        $produitRecapDepotRepo->init($iJourATraiter);
                    
                        // Nombre d'exemplaire de produit par depot
                        $produitRecapDepotRepo->produitParDepot($iJourATraiter);
                    } 
                    catch (DBALException $DBALException) {
                        $this->suiviCommand->setMsg($DBALException->getMessage());
                        $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type($DBALException->getCode()));
                        $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                        $this->suiviCommand->setEtat("KO");
                        $this->oLog->erreur($DBALException->getMessage(), $DBALException->getCode(), $DBALException->getFile(), $DBALException->getLine());
                        $this->registerError();
                        if($input->getOption('id_ai') && $input->getOption('id_sh')){
                            $this->registerErrorCron($idAi);
                        }
                    }
                }
            }
            else
            {
                $this->suiviCommand->setMsg("Le jour MAX est anterieur au Jour MIN (Jour min : J".(($iJourATraiterMin>=0)?"+":"-").abs($iJourATraiterMin).". Jour max : J".(($iJourATraiterMax>=0)?"+":"-").abs($iJourATraiterMax).").");
                $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_WARNING));
                $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                $this->suiviCommand->setEtat("OK");
                $this->oLog->erreur("Le jour MAX est anterieur au Jour MIN (Jour min : J".(($iJourATraiterMin>=0)?"+":"-").abs($iJourATraiterMin).". Jour max : J".(($iJourATraiterMax>=0)?"+":"-").abs($iJourATraiterMax).").", E_USER_WARNING);
            }
        }
        else
        {
            $this->suiviCommand->setMsg("Jour a traiter. Format : J<+Numerique> (Expl : J-1 ou J-2 ou J ...)");
            $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_WARNING));
            $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
            $this->suiviCommand->setEtat("OK");
            $this->oLog->erreur("Jour a traiter. Format : J<+Numerique> (Expl : J-1 ou J-2 ou J ...)", E_USER_WARNING);
        }
        
        $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
        $this->endTraitement();
    	$this->oLog->info(date("d/m/Y H:i:s : ")."Fin Calcul des infos produits servis par chaque depot - Commande : ".$this->sNomCommande);    
    	
        return;
    }

    
}
