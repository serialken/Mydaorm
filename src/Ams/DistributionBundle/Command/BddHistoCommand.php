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
 * Historisation des tables :
 *      - client_a_servir_logist 
 *      - client_a_servir_src
 *      - crm_detail
 *      - reperage
 * 
 * Par defaut, la plage des dates a historiser est entre J-180 et J-200.
 * Parametre : 
 *          Jour minimum a calculer. C'est optionnel
 *          Jour maximum a calculer. C'est optionnel
 *          Environnement [--env=..]
 * Expl : J-150 J-120
 * Si les parametres sont renseignes, le traitement concerne les jours de distribution de "jour minimum" a "jour maximum"
 * Expl : J-100 J-95 => historisation a faire concernant J-100, J-99, J-98, J-97, J-96 & J-95
 * 
 * Exemple de commande : 
 *                      php app/console bdd_histo J-150 J-120 --env=dev
 * 
 * 
 * @author aandrianiaina
 *
 */
class BddHistoCommand extends GlobalCommand
{
    protected function configure()
    {
    	$this->sNomCommande	= 'bdd_histo';
        $sJourATraiterMinParDefaut = "J-200";
        $sJourATraiterMaxParDefaut = "J-180";
        $sSujetATraiterDefaut   = "CAS";
        $sHistoMaxParDefaut = "J-500";
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console bdd_histo Expl : php app/console bdd_histo J+1 J+3 --env=prod
        $this
            ->setDescription('Historisation automatique des tables "client_a_servir_logist" et "client_a_servir_src".')
            ->addArgument('jour_a_traiter_min', InputArgument::OPTIONAL, 'Jour a traiter Min. Format : J<+Numerique> (Expl : J-200)', $sJourATraiterMinParDefaut)
            ->addArgument('jour_a_traiter_max', InputArgument::OPTIONAL, 'Jour a traiter Max. Format : J<+Numerique> (Expl : J-180)', $sJourATraiterMaxParDefaut)
            ->addOption('sujet',null, InputOption::VALUE_REQUIRED, 'Sujet a historiser. "CAS", "REPER", "CRM_RECLAM", "CRM_REMINFO", "CRM_ETIQUETTE" ', $sSujetATraiterDefaut)
            ->addOption('histo_max',null, InputOption::VALUE_REQUIRED, 'Jour MAX a garder dans la table d historisation. J<+Numerique> (Expl : J-500)', $sHistoMaxParDefaut)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
    	$sEnvironnement = "prod";
        
        if ($input->getOption('env')) {
            $sEnvironnement   = $input->getOption('env');
        }
        
        $sJourATraiterMin  = $input->getArgument('jour_a_traiter_min');
        $sJourATraiterMax  = $input->getArgument('jour_a_traiter_max');
        $iNbJourMaxAGarder  = 365; // J-365 sera le MIN des date_distrib dans l historique
        
    	$this->oLog->info(date("d/m/Y H:i:s : ")."Debut Historisation automatique des tables client_a_servir_logist & client_a_servir_src - Commande : ".$this->sNomCommande." $sJourATraiterMin $sJourATraiterMax --env=$sEnvironnement");    
        
        $em    = $this->getContainer()->get('doctrine')->getManager();
            	
        $iJourATraiter  = 0;
        $aiJourATraiter   = array();
        $aoJourATraiter   = array();
        if(preg_match('/^J([\-\+][0-9]+)?$/', $sJourATraiterMin, $aiJourATraiterMin) && preg_match('/^J([\-\+][0-9]+)?$/', $sJourATraiterMax, $aiJourATraiterMax))
        {
            $iJourATraiterMin = 0;
            $iJourATraiterMax = 0;
            if(isset($aiJourATraiterMin[1]))
            {
                $iJourATraiterMin  = intval($aiJourATraiterMin[1]);
            }
            
            if(isset($aiJourATraiterMax[1]))
            {
                $iJourATraiterMax  = intval($aiJourATraiterMax[1]);
            }
            
            if($iJourATraiterMax >= $iJourATraiterMin)
            {
                for($i=$iJourATraiterMin; $i<=$iJourATraiterMax; $i++)
                {
                    $aiJourATraiter[]    = $i;
                }
            }
            else
            {
                $this->oLog->erreur("Le jour MAX est anterieur au Jour MIN (Jour min : J".(($iJourATraiterMin>=0)?"+":"-").abs($iJourATraiterMin).". Jour max : J".(($iJourATraiterMax>=0)?"+":"-").abs($iJourATraiterMax).").", E_USER_WARNING);
            }
        }
        else
        {
            $this->oLog->erreur("Jour a traiter. Format : J<+Numerique> (Expl : J-1 ou J-2 ou J ...)", E_USER_WARNING);
        }
        
        foreach($aiJourATraiter as $iJourATraiter)
        {
            $oDateDuJour    = new \DateTime();
            $oDateDuJour->setTime(0, 0, 0);
            $dateDistribATraiter   = $oDateDuJour;
            if($iJourATraiter<0)
            {
                $dateDistribATraiter   = $oDateDuJour->sub(new \DateInterval('P'.abs($iJourATraiter).'D'));
            }
            else
            {
                $dateDistribATraiter   = $oDateDuJour->add(new \DateInterval('P'.$iJourATraiter.'D'));
            }
            
            $aoJourATraiter[$dateDistribATraiter->format("Y-m-d")] = $dateDistribATraiter;
        }
        
        ksort($aoJourATraiter);
        
        if(!empty($aoJourATraiter))
        {
            $repoClientAServirHisto   = $this->getContainer()->get('doctrine')->getRepository('AmsDistributionBundle:ClientAServirHisto');
            foreach($aoJourATraiter as $oDateDistribATraiter)
            {
                $this->oLog->info(date("d/m/Y H:i:s : ")." Historisation du date ".$oDateDistribATraiter->format('d/m/Y')." ");    
                $repoClientAServirHisto->historise($oDateDistribATraiter);
            }
        }
        
        //$this->oLog->info(date("d/m/Y H:i:s : ")." Supprime l historique plus de 365 jours "); 
        //$repoClientAServirHisto->suppr_historique($iNbJourMaxAGarder);
        
                
        $this->oLog->info(date("d/m/Y H:i:s : ")."Fin Historisation automatique des tables client_a_servir_logist & client_a_servir_src - Commande : ".$this->sNomCommande." $sJourATraiterMin $sJourATraiterMax --env=$sEnvironnement");    
        
        return;
    }
    
}
