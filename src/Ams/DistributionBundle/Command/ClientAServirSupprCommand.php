<?php 
namespace Ams\DistributionBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ijanki\Bundle\FtpBundle\Exception\FtpException;
use Ams\SilogBundle\Command\GlobalCommand;
use Doctrine\DBAL\DBALException;

/**
 * 
 * Suppression de CASL des donnees de certains depots
 * 
 * 
 * 
 * 
 * 
 * @author aandrianiaina
 *
 */
class ClientAServirSupprCommand extends GlobalCommand
{
    protected function configure()
    {
    	$this->sNomCommande	= 'casl_suppr';
        $sJourATraiterMinParDefaut = "J+183";
        $sJourATraiterMaxParDefaut = "J+184";
        $sDepotAGarderDefaut = "004,007,010,013,014,015,018,023,024,028,029,031,033,034,035,036,039,040,041,042,045,049,050,051";
        $this->jourATraiterMaxRef   = 3;
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console casl_suppr depot_a_garder=035,039 Expl : php app/console casl_suppr J+183 J+184 --depot_a_garder=004,007,014,034,035 --env=prod
        $this
            ->setDescription('Suppression de CASL des donnees de certains depots.')
            ->addArgument('jour_a_traiter_min', InputArgument::OPTIONAL, 'Jour a traiter Min. Format : J<+Numerique> (Expl : J+1 ou J+2 ou J ...)', $sJourATraiterMinParDefaut)
            ->addArgument('jour_a_traiter_max', InputArgument::OPTIONAL, 'Jour a traiter Max. Format : J<+Numerique> (Expl : J+1 ou J+2 ou J ...)', $sJourATraiterMaxParDefaut)
            ->addOption('depot_a_garder',null, InputOption::VALUE_REQUIRED, 'Depots a ne pas supprimer ? Codes des depots a ne pas supprimer separes par ","', $sDepotAGarderDefaut)
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
    	$this->oLog->info(date("d/m/Y H:i:s : ")."Debut Suppression de CASL des donnees de certains depots - Commande : ".$this->sNomCommande);    
                
        $em    = $this->getContainer()->get('doctrine')->getManager();
        
        $sDepotAGarder = "";
        $aDepotAGarder  = array();
        $aIdDepotAGarder    = array();
        $sSeparateur    = ',';
        if ($input->getOption('depot_a_garder')) {
            $sDepotAGarder   = $input->getOption('depot_a_garder');
        }
        
    	$sJourATraiterMin  = $input->getArgument('jour_a_traiter_min');
        $sJourATraiterMax  = $input->getArgument('jour_a_traiter_max');
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
            
            $aoJourATraiter[$iJourATraiter] = $dateDistribATraiter;
        }
        
        
        
        if(trim($sDepotAGarder)!='')
        {
            $aDepotAGarder  = explode($sSeparateur, $sDepotAGarder);
            foreach($aDepotAGarder as $iIK => $sD)
            {
                $aDepotAGarder[$iIK]    = trim($sD);
            }
            if(!empty($aDepotAGarder))
            {
                $sSlctDepotAGarder  = " SELECT id FROM depot WHERE code IN ('".implode("', '", $aDepotAGarder)."') ";
                $rSlctDepotAGarder  = $em->getConnection()->fetchAll($sSlctDepotAGarder);
                foreach($rSlctDepotAGarder as $aArr)
                {
                    $aIdDepotAGarder[]  = $aArr['id'];
                }
            }
        }
        
        if(!empty($aIdDepotAGarder))
        {
            foreach ($aoJourATraiter as $iJourATraiter => $oDateATraiterV)
            {
                //$aDatesYmdARecuperer[]  = $oDateATraiterV->format('Ymd');
                try {
                    
                    $sDelete    = " DELETE FROM client_a_servir_logist WHERE date_distrib = '".$oDateATraiterV->format('Y-m-d')."' AND ( depot_id NOT IN (".implode(',', $aIdDepotAGarder).") OR depot_id IS NULL ) ";
                    $this->oLog->info(date("d/m/Y H:i:s : ")."Debut suppression de CASL : ".$sDelete);
                    //echo "\r\n";print_r($sDelete);echo "\r\n";
                    $em->getConnection()->executeQuery($sDelete);
                    $em->clear();

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
             $this->suiviCommand->setMsg("Aucun depot a ne pas supprimer trouve");
            $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_WARNING));
            $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
            $this->suiviCommand->setEtat("OK");
            $this->oLog->erreur("Aucun depot a ne pas supprimer trouve", E_USER_WARNING);
        }
        
        $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
        $this->endTraitement();        
        $this->oLog->info(date("d/m/Y H:i:s : ")."Fin Suppression de CASL des donnees de certains depots - Commande : ".$this->sNomCommande);
        return;
    }
}
