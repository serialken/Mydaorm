<?php 
namespace Ams\DistributionBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\DBAL\DBALException;
use Ams\WebserviceBundle\Exception\RnvpLocalException;
use Ams\WebserviceBundle\Exception\GeocodageException;
use Ams\SilogBundle\Command\GlobalCommand;
/**
 * 
 * "Command" classement automatique des reperages. 
 * 
 * * Par defaut, on fait le traitement sur les donnees dont la date de demarrage prevue est entre J+1 et J+300
 * Parametre : 
 *          Jour minimum a calculer. C'est optionnel
 *          Jour maximum a calculer. C'est optionnel
 *          Code depots (exemple : 028,029,042) [--cd=..]
 *          Environnement [--env=..]
 * 
 * Pour executer, faire : 
 *                  php app/console reperage_classement_auto J+1 J+300 --cd=028,029,033,040,041,042,049 --env=prod
 *      Expl : php app/console reperage_classement_auto
 *
 */
class ReperageClassementAutoCommand extends GlobalCommand
{
    private $aFichierFluxParam;
    private $sRepTmp;
    private $sRepBkpLocal;
    
    protected function configure()
    {
    	$this->sNomCommande	= 'reperage_classement_auto';
        $sJourATraiterMinParDefaut = "J+1";
        $sJourATraiterMaxParDefaut = "J+300";
        $sCDDefaut = "tout";    // Centre de distribution par defaut
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console reperage_classement_auto Expl : php app/console reperage_classement_auto
        $this
            ->setDescription('Classement automatique des reperages')
            ->addArgument('jour_a_traiter_min', InputArgument::OPTIONAL, 'Jour a traiter Min. Format : J<+Numerique> (Expl : J+1 ou J+2 ou J ...)', $sJourATraiterMinParDefaut)
            ->addArgument('jour_a_traiter_max', InputArgument::OPTIONAL, 'Jour a traiter Max. Format : J<+Numerique> (Expl : J+1 ou J+2 ou J ...)', $sJourATraiterMaxParDefaut)
            ->addOption('cd',null, InputOption::VALUE_REQUIRED, 'Liste des codes des centres de distribution separees par "," ?', $sCDDefaut)
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
        $this->oLog->info(date("d/m/Y H:i:s : ")."Debut Classement automatique des reperages - Commande : ".$this->sNomCommande);
    	
        $em    = $this->getContainer()->get('doctrine')->getManager();
        
        $sJourATraiterMin  = $input->getArgument('jour_a_traiter_min');
        $sJourATraiterMax  = $input->getArgument('jour_a_traiter_max');
        
        $aiJourATraiter   = array();
        $aoJourATraiter   = array();
        $sCDDefaut = "tout";
        $aCodesCD    = array();
        
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
                 $aiJourATraiter['min']    = $iJourATraiterMin;
                 $aiJourATraiter['max']    = $iJourATraiterMax;
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
        
        if ($input->getOption('cd')) {
            $sCD   = $input->getOption('cd');
            if(trim($sCD) != $sCDDefaut)
            {
                $aCodesCD    = explode(',', trim($sCD));
                foreach($aCodesCD as $k => $v)
                {
                    $aCodesCD[$k]    = trim($v);
                }
            }
        }
        
        if(!empty($aiJourATraiter))
        {
            $oDateDuJour    = new \DateTime();
            $oDateDuJour->setTime(0, 0, 0);
            $dateDistribATraiter   = $oDateDuJour;
            
            foreach($aiJourATraiter as $sK => $iJourATraiter)
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

                $aoJourATraiter[$sK] = $dateDistribATraiter;
            }
            
            if(!empty($aoJourATraiter))
            {
                $repoReperageTmp            = $this->getContainer()->get('doctrine')->getRepository('AmsDistributionBundle:ReperageTmp');
                $repoReperage               = $this->getContainer()->get('doctrine')->getRepository('AmsDistributionBundle:Reperage');
                $srv_ams_carto_geoservice   = $this->getContainer()->get('ams_carto.geoservice');
                
                try {
                    // Copie dans la table temporaire des reperages non encore classes
                    $this->oLog->info("Copie dans la table temporaire des reperages non encore classes");
                    $repoReperageTmp->insertReperagesNonClasses($aoJourATraiter['min'], $aoJourATraiter['max'], $aCodesCD);

                    // Classement automatique des non-classes
                    $this->oLog->info("Classement automatique des non-classes");
                    $aPointsAClasser  = $repoReperageTmp->pointsAClasser(1000);
                    $srv_ams_carto_geoservice->classementAuto($aPointsAClasser);

                    // MAJ des tournees de la table "reperage"
                    $this->oLog->info("MAJ des tournees de la table 'reperage'");
                    $repoReperageTmp->majTourneeReperage();
                    
                } catch (RnvpLocalException $rnvpLocalException) {
                    $this->suiviCommand->setMsg($rnvpLocalException->getMessage());
                    $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type($rnvpLocalException->getCode()));
                    $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                    $this->suiviCommand->setEtat("KO");
                    $this->oLog->erreur($rnvpLocalException->getMessage(), $rnvpLocalException->getCode(), $rnvpLocalException->getFile(), $rnvpLocalException->getLine());
                    $this->registerError();
                    if($input->getOption('id_ai') && $input->getOption('id_sh')){
                        $this->registerErrorCron($idAi);
                    }
                } catch (GeocodageException $GeocodageException) {
                    $this->suiviCommand->setMsg($GeocodageException->getMessage());
                    $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type($GeocodageException->getCode()));
                    $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                    $this->suiviCommand->setEtat("KO");
                    $this->oLog->erreur($GeocodageException->getMessage(), $GeocodageException->getCode(), $GeocodageException->getFile(), $GeocodageException->getLine());
                    $this->registerError();
                    if($input->getOption('id_ai') && $input->getOption('id_sh')){
                        $this->registerErrorCron($idAi);
                    }
                } catch (DBALException $DBALException) {
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
            $this->oLog->info('Aucun jour a traiter defini');
        }
        
        $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
        $this->endTraitement();
        $this->oLog->info(date("d/m/Y H:i:s : ")."Fin Classement automatique des reperages - Commande : ".$this->sNomCommande);
        $this->oLog->info("Fin commande");
        return;
    }
}
