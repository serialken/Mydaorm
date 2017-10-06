<?php 
namespace Ams\CartoBundle\Command;

//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Ijanki\Bundle\FtpBundle\Exception\FtpException;
use Ams\SilogBundle\Lib\StringLocal;

use Ams\SilogBundle\Command\GlobalCommand;

class ClientTourneeCommand extends GlobalCommand
{

    protected function configure()
    {
			$this->setName('casl_cleaning');
			/** php app/console casl_cleaning  **/
        $this
            ->setDescription("Mise à jour des tournées aux abonnés qui n'en possèdent pas dans client_a_servir_logistic")
//             ->addArgument('fic_code', InputArgument::REQUIRED, 'Code source de donnees')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
    	$this->oLog->info("Debut traitement");
       
    	$t = $this->getContainer()->get('ams_util.geoservice');
    	$time_start = microtime(true);
    	$t->insertPointTournee('2014-08-31');
      $time_2 = microtime(true);
      $time = $time_2 - $time_start;
    	$this->oLog->info("Temps de requete ".sprintf("%.2f", $time).' sec');
    	$this->oLog->info("Fin traitement");
			return;
    }
}
