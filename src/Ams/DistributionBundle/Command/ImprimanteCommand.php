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
 * TEST SUR LES IMPRIMANTES
 * 
 */
class ImprimanteCommand extends GlobalCommand
{
    CONST SUCCES = 0;
    CONST ERROR_FTP = 1;
    CONST ERROR_PING = 2;
    protected function configure()
    {
    	// Pour executer, faire : php app/console monitoring_imprimante --env=dev
    	$this->sNomCommande	= 'monitoring_imprimante';
    	$this->setName($this->sNomCommande);
        $this->setDescription('Test sur les imprimantes.')
             ->addOption('mode',null, InputOption::VALUE_OPTIONAL)
          
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
        $mode = $input->getOption('mode');
        $env = $input->getOption('env');
        $em = $this->getContainer()->get('doctrine')->getManager(); 
        $oImprimantes = $em->getRepository('AmsDistributionBundle:Imprimante')->findBy(array('etat'=> true));
        $this->traitement($oImprimantes,$mode,$env);
        return;
    }
    
    private function traitement($oImprimantes,$sMode,$sEnv){
        if(!$sMode) $this->oLog->info("TEST IMPRIMANTE");
        $result = array();
        foreach($oImprimantes as $oImprimante){
            $ip = $oImprimante->getIpImprimante();
            $sNameImprimante = $oImprimante->getLibelleImprimante();
            /** TEST DU PING **/
            $bPing = $this->ping($ip,$sEnv);
            if($bPing == 0){
                /** TEST FTP **/
                $bFtp = $this->ftp($ip);
                if($bFtp)
                    $this->result(ImprimanteCommand::SUCCES,$sNameImprimante,$sMode);
                else
                    $result[] = $this->result(ImprimanteCommand::ERROR_FTP,$sNameImprimante,$sMode);
            }
            else
                $result[] = $this->result(ImprimanteCommand::ERROR_PING,$sNameImprimante,$sMode);
        }
        if($sMode == 'nagios') echo $this->getResultNagios($result);
        else $this->oLog->info("FIN TEST IMPRIMANTE");
    }
    
    private function getResultNagios($result){
        if(empty($result))
            $sReponse = 'OK|Toutes les imprimantes sont ok';
        else 
            $sReponse = 'NOK|'.implode(',', $result);
        return $sReponse;
    }
    
    private function result($result,$nameImprimante,$mode){
        if($mode == 'nagios'){
            if($result != 0) 
                return $this->getStatus($nameImprimante, $result);
        }
        else 
            $this->oLog->info($this->getStatus($nameImprimante, $result));
    }
    
    private function ping($ipAdress,$sEnv){
        if($sEnv == 'local')
            exec("ping -n 1 $ipAdress -w 2",$output, $status);
        else
            exec("ping -c 1 $ipAdress -w 2",$output, $status);
        return $status;
    }
    
    private function ftp($ipAdress){
        $conn_id = ftp_connect($ipAdress, 21);
        $result = ftp_login($conn_id, '', '');
        ftp_close($conn_id);
        return $result;
    }
    
    private function getStatus($sNameImprimante,$iReturnCode){
        switch($iReturnCode){
            case ImprimanteCommand::SUCCES : 
                return 'L\'imprimante '.strtoupper($sNameImprimante).' est disponible.';
            case ImprimanteCommand::ERROR_FTP : 
                return 'L\'imprimante '.strtoupper($sNameImprimante).' est disponible via le Ping, mais n\'est pas accessible via FTP.';
            case ImprimanteCommand::ERROR_PING : 
                return 'L\'imprimante "'.strtoupper($sNameImprimante).'" ne repond pas au Ping.';
        }
        
    }
    
}
