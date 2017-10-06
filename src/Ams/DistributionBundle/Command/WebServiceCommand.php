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
class WebServiceCommand extends GlobalCommand
{
    CONST SUCCES = 0;
    CONST ERROR_PING = 1;
    
    protected function configure()
    {
    	// Pour executer, faire : php app/console monitoring_ws --env=dev
    	$this->sNomCommande	= 'monitoring_ws';
    	$this->setName($this->sNomCommande);
        $this->setDescription('Test sur les webservices.')
             ->addOption('mode',null, InputOption::VALUE_OPTIONAL)
          
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
        $mode = $input->getOption('mode');
        $env = $input->getOption('env');
        $em = $this->getContainer()->get('doctrine')->getManager(); 
        $aIpPortGeoc = explode(':',$this->getContainer()->getParameter("GEOC_WEB_SERVER_IP_PORT"));
        $ipAdress = $aIpPortGeoc[0];
        $port = $aIpPortGeoc[1];
        $result = $this->traitement($ipAdress,$port,$mode);
        $this->oLog->info($this->getStatus($result,$env));
        return;
    }
    
    private function traitement($ipAdress,$port,$mode){
        $bPing = $this->netcat($ipAdress,$port);
        return $bPing;
    }
    
    private function netcat($ipAdress,$port){
        exec("nc -nz $ipAdress $port  -w 5",$output, $status);
        return $status;
    }
    
    private function getStatus($iReturnCode,$env){
        if($env)
        switch($iReturnCode){
            case WebserviceCommand::SUCCES : 
                return 'Le webservice pour l\'environemment "'.$env.'" est accessible.';
            case WebServiceCommand::ERROR_PING : 
                return 'Le webservice pour l\'environemment "'.$env.'" n\'est pas accessible.';
        }
        
    }
    
    private function getResultNagios($result){
    }
    
}
