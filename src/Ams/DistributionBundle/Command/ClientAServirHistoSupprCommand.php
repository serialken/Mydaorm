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
 * Suppression des donnees de la table "client_a_servir_histo" trop anciennes.
 * 
 * Par defaut, la plage des dates a historiser est entre J-150 et J-120.
 * Parametre : 
 *          Jour minimum a calculer. C'est optionnel
 *          Jour maximum a calculer. C'est optionnel
 *          Environnement [--env=..]
 * Expl : J-150 J-120
 * Si les parametres sont renseignes, le traitement concerne les jours de distribution de "jour minimum" a "jour maximum"
 * Expl : J-100 J-95 => historisation a faire concernant J-100, J-99, J-98, J-97, J-96 & J-95
 * 
 * Exemple de commande : 
 *                      php app/console cas_histo_suppr J-150 J-120 --id_sh=cron_test --id_ai=1  --env=dev
 * 
 * 
 * @author aandrianiaina
 *
 */
class ClientAServirHistoSupprCommand extends GlobalCommand
{
    protected function configure()
    {
    	$this->sNomCommande	= 'cas_histo_suppr';
        $sJourATraiterMinParDefaut = "J-150";
        $sJourATraiterMaxParDefaut = "J-120";
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console cas_histo_suppr Expl : php app/console cas_histo_suppr J+1 J+3 --env=prod
        $this
            ->setDescription('Suppression des donnees de la table "client_a_servir_histo" trop anciennes.')
            ->addArgument('jour_a_traiter_min', InputArgument::OPTIONAL, 'Jour a traiter Min. Format : J<+Numerique> (Expl : J-150)', $sJourATraiterMinParDefaut)
            ->addArgument('jour_a_traiter_max', InputArgument::OPTIONAL, 'Jour a traiter Max. Format : J<+Numerique> (Expl : J-120)', $sJourATraiterMaxParDefaut)
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
        $sEnvironnement = "prod";
        
        if ($input->getOption('env')) {
            $sEnvironnement   = $input->getOption('env');
        }
        
        $sJourATraiterMin  = $input->getArgument('jour_a_traiter_min');
        $sJourATraiterMax  = $input->getArgument('jour_a_traiter_max');
        
        if($input->getOption('id_ai') && $input->getOption('id_sh')){
            $this->oLog->info(date("d/m/Y H:i:s : ")."Debut Suppression des donnees de la table client_a_servir_histo trop anciennes - Commande : ".$this->sNomCommande." ".$sJourATraiterMin." ".$sJourATraiterMax." --id_sh=".$idSh." --id_ai=".$idAi."  --env=".$sEnvironnement." ");    
        }else{
            $this->oLog->info(date("d/m/Y H:i:s : ")."Debut Suppression des donnees de la table client_a_servir_histo trop anciennes - Commande : ".$this->sNomCommande." ".$sJourATraiterMin." ".$sJourATraiterMax." --env=".$sEnvironnement." ");
        }
        
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
                $repoClientAServirHisto->suppr_historique_date($oDateDistribATraiter);
            }
        }
        
        $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
        $this->endTraitement();  
        if($input->getOption('id_ai') && $input->getOption('id_sh')){
            $this->oLog->info(date("d/m/Y H:i:s : ")."Fin Suppression des donnees de la table client_a_servir_histo trop anciennes - Commande : ".$this->sNomCommande." ".$sJourATraiterMin." ".$sJourATraiterMax." --id_sh=".$idSh." --id_ai=".$idAi."  --env=".$sEnvironnement." ");    
        }else{
            $this->oLog->info(date("d/m/Y H:i:s : ")."Fin Suppression des donnees de la table client_a_servir_histo trop anciennes - Commande : ".$this->sNomCommande." ".$sJourATraiterMin." ".$sJourATraiterMax." --env=".$sEnvironnement." ");
        }
        return;
    }
    
}
