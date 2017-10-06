<?php 
namespace Ams\DistributionBundle\Command;

//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Ijanki\Bundle\FtpBundle\Exception\FtpException;

use Ams\SilogBundle\Command\GlobalCommand;
use Doctrine\DBAL\DBALException;

/**
 * 
 * Classement automatique des nouveaux abonnes dans une tournee HORS PRESSE
 * 
 * 
 * Par defaut, on ne fait le calcul que le jour J+1.
 * Parametre : 
 *          Jour minimum a calculer. C'est optionnel
 *          Jour maximum a calculer. C'est optionnel
 *          Flux "jour" ou "nuit" [--jn=..]
 *          Nombre max a traiter [--nbmax=..]
 *          Environnement [--env=..]
 * Expl : J+1 J+5
 * Si les parametres sont renseignes, le traitement concerne les jours de distribution de "jour minimum" a "jour maximum"
 * Expl : J-1. => calculs a faire concernent J-1, J, J+1, J+2 & J+3
 * 
 * Exemple de commande : 
 *                      php app/console classement_auto_hors_presse J+1 J+3  --jn=jour --nbmax=30 --env=dev
 * 
 * 
 * @author aandrianiaina
 *
 */
class ClassementAutoHorsPresseCommand extends GlobalCommand
{
    protected function configure()
    {
    	$this->sNomCommande	= 'classement_auto_hors_presse';
        $sJourATraiterMinParDefaut = "J+1";
        $sJourATraiterMaxParDefaut = "J+2";
        $sFluxDefaut = "CAS";
        $sJourOuNuitDefaut = "jour";
        $iNbMaxATraiterDefaut = 10000;
        $this->jourATraiterMaxRef   = 3;
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console classement_auto Expl : php app/console classement_auto J+1 J+3 --env=prod
        $this
            ->setDescription('Classement automatique des nouveaux abonnes dans une tournee.')
            ->addArgument('jour_a_traiter_min', InputArgument::OPTIONAL, 'Jour a traiter Min. Format : J<+Numerique> (Expl : J+1 ou J+2 ou J ...)', $sJourATraiterMinParDefaut)
            ->addArgument('jour_a_traiter_max', InputArgument::OPTIONAL, 'Jour a traiter Max. Format : J<+Numerique> (Expl : J+1 ou J+2 ou J ...)', $sJourATraiterMaxParDefaut)
            ->addOption('jn',null, InputOption::VALUE_REQUIRED, 'jour ou nuit ?', $sJourOuNuitDefaut)
            ->addOption('nbmax',null, InputOption::VALUE_REQUIRED, 'nombre d adresse max a traiter ?', $iNbMaxATraiterDefaut)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
    	$sJourOuNuit    = "jour";
        $sEnvironnement = "prod";
        $iNbMaxATraiterDefaut = 25000;
        $iNbMaxATraiter = $iNbMaxATraiterDefaut;
        
        if ($input->getOption('jn')) {
            $sJourOuNuit   = $input->getOption('jn');
        }
        if ($input->getOption('env')) {
            $sEnvironnement   = $input->getOption('env');
        }
        if ($input->getOption('nbmax')) {
            $iNbMaxATraiter   = $input->getOption('nbmax');
            $iNbMaxATraiter = intval($iNbMaxATraiter);
        }
        $sJourOuNuit    = strtoupper($sJourOuNuit);
        $sCodeJourOuNuit    = substr($sJourOuNuit, 0, 1);
        
        $sJourATraiterMin  = $input->getArgument('jour_a_traiter_min');
        $sJourATraiterMax  = $input->getArgument('jour_a_traiter_max');
        
    	$this->oLog->info(date("d/m/Y H:i:s : ")."Debut Classement automatique des nouveaux abonnes dans une tournee - Commande : ".$this->sNomCommande." $sJourATraiterMin $sJourATraiterMax --jn=$sJourOuNuit --env=$sEnvironnement");    
        
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
        
        
        $repoClientAServirLogist   = $this->getContainer()->get('doctrine')->getRepository('AmsDistributionBundle:ClientAServirLogist');
        $repoRefFlux   = $this->getContainer()->get('doctrine')->getRepository('AmsReferentielBundle:RefFlux');
        $oJourOuNuit    = $repoRefFlux->findOneByCode($sCodeJourOuNuit);
        $srv_ams_carto_geoservice = $this->getContainer()->get('ams_carto.geoservice');
                
        //echo "\r\nFlux:";
        //print_r($sFlux);
        //echo "\r\nJour ou nuit:";
        //print_r($sJourOuNuit);
        //echo "\r\nJour ou nuit:";
        //print_r($oJourOuNuit);
        //echo "\r\nJours a traiter:";
        //print_r($aoJourATraiter);
        //echo "\r\nEnvironnement:";
        //print_r($sEnvironnement);
        
        $aPointsAClasser    = array();
        
        // Recuperation des points a classer
        $this->oLog->info('Recuperation des points a classer');
        if($oJourOuNuit)
        {                
            $this->oLog->info(date("d/m/Y H:i:s : ")."Mise a jour de tournee des abonnes connus - Hors REPER devenus C_A_S");  
            $repoClientAServirLogist->miseAJourTournee($aoJourATraiter);

            // Suppression des lignes dans tournee_detail qui n'a plus rien a voir avec le flux et/ou depot_commune
            //$this->oLog->info(date("d/m/Y H:i:s : ")."Suppression des lignes de tournee_detail avec incoherence flux/depot");    
            //$repoClientAServirLogist->supprIncoherenceTournee($aoJourATraiter, $oJourOuNuit->getId());

            // Les clients a servir non classes pour ce jour de distrib mais classes pour d'autres jours
            $aPointsAClasserNonClassesLeJDistrib    = $repoClientAServirLogist->clientsAServirNonClassesLeJDistrib($iNbMaxATraiter, $aoJourATraiter,$oJourOuNuit->getId());
            $this->oLog->info(date("d/m/Y H:i:s : ")."Classement automatique de ".count($aPointsAClasserNonClassesLeJDistrib)." abonnes (Abonnes classes les autres jours)");
            $srv_ams_carto_geoservice->classementAuto($aPointsAClasserNonClassesLeJDistrib);

            $this->oLog->info(date("d/m/Y H:i:s : ")."Mise a jour de la colonne tournee");
            $repoClientAServirLogist->miseAJourTournee($aoJourATraiter);

            $aPointsAClasser    = $repoClientAServirLogist->clientsAServirNonClasses($iNbMaxATraiter, $aoJourATraiter,$oJourOuNuit->getId());
        }
        else
        {
            $this->oLog->erreur("Pour les clients a servir, le flux (nuit ou jour) doit etre defini et connu", E_USER_WARNING);
            return;
        }
        
        //echo "\r\n";print_r($aPointsAClasser);echo "\r\n";        
        
        
        $this->oLog->info(date("d/m/Y H:i:s : ")."Classement automatique de ".count($aPointsAClasser)." abonnes");    
        
        $srv_ams_carto_geoservice->classementAuto($aPointsAClasser);
        
        $this->oLog->info(date("d/m/Y H:i:s : ")."Mise a jour de la colonne tournee");
        
        $repoClientAServirLogist->miseAJourTournee($aoJourATraiter); // Mise a jour de la colonne tournee de la table client_a_servir_logist
                
        $this->oLog->info(date("d/m/Y H:i:s : ")."Fin Classement automatique des nouveaux abonnes dans une tournee - Commande : ".$this->sNomCommande." $sJourATraiterMin $sJourATraiterMax --flux=$sFlux --jn=$sJourOuNuit --env=$sEnvironnement");    
        
        return;
    }
    
    
}
