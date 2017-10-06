<?php 
namespace Ams\AdresseBundle\Command;

//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\DBAL\DBALException;
use Ams\WebserviceBundle\Exception\GeocodageException;
use Ams\SilogBundle\Command\GlobalCommand;

/**
 * 
 * "Command" permettant de sauvegarder les adresses livrees 
 * 
 * Nombre de jours entre deux dates ==> SELECT DATEDIFF(CURDATE(), '2015-03-28');
 * 
 * @author aandrianiaina
 *
 */
class SauvegardeAdresseLivreeCommand extends GlobalCommand
{
    
    protected function configure()
    {
    	$this->sNomCommande	= 'sauvegarde_adr_livrees';
        $this->sJourATraiterMinParDefaut = "J+0";
        $this->sJourATraiterMaxParDefaut = "J+0";
        $this->sForceParDefaut = 0; // Si "1" -> On initialise les adresses livrees deja stockees auparavant
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console sauvegarde_adr_livrees <J+0> <J+0> --force=<1> Expl : php app/console sauvegarde_adr_livrees J+0 J+0 --force=1
        $this
            ->setDescription('Sauvegarde des adresses livrees')
            ->addArgument('jour_a_traiter_min', InputArgument::OPTIONAL, 'Jour a traiter Min. Format : J<+Numerique> (Expl : J+1 ou J+2 ou J ...)', $this->sJourATraiterMinParDefaut)
            ->addArgument('jour_a_traiter_max', InputArgument::OPTIONAL, 'Jour a traiter Max. Format : J<+Numerique> (Expl : J+1 ou J+2 ou J ...)', $this->sJourATraiterMaxParDefaut)
            ->addOption('force',null, InputOption::VALUE_REQUIRED, 'Flux ? Clients a servir (C_A_S) ou reperage (REPER)', $this->sForceParDefaut)
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
        $sJourATraiterMin  = $input->getArgument('jour_a_traiter_min');
        $sJourATraiterMax  = $input->getArgument('jour_a_traiter_max');
        $bForce = $this->sForceParDefaut;
        if ($input->getOption('force')) {
            $bForce   = $input->getOption('force');
            $bForce   = intval($bForce);
        }
        
        // Si $bForce=1; // -> On initialise (supprime, puis remplace) les adresses livrees deja stockees auparavant
                
        $this->oLog->info(date("d/m/Y H:i:s : ")."Debut Sauvegarde des adresses livrees - Commande : ".$this->sNomCommande." $sJourATraiterMin $sJourATraiterMax --force=$bForce");    
        
        $repoAdresseLivree   = $this->getContainer()->get('doctrine')->getRepository('AmsAdresseBundle:AdresseLivree');        
    	
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
        
        foreach($aoJourATraiter as $sJourATraiter => $oDateATraiterV)
        {
            $this->oLog->info("Debut Sauvegarde des adresses livrees du ".$oDateATraiterV->format("d/m/Y"));
            $repoAdresseLivree->sauvegarde($oDateATraiterV, $bForce);
        }
    	$this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
        $this->endTraitement();
    	$this->oLog->info(date("d/m/Y H:i:s : ")."Fin Sauvegarde des adresses livrees - Commande : ".$this->sNomCommande." $sJourATraiterMin $sJourATraiterMax --force=$bForce");    
        return;
    }
}
