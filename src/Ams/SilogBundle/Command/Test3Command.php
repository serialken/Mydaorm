<?php 
namespace Ams\SilogBundle\Command;

//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Ijanki\Bundle\FtpBundle\Exception\FtpException;
use Ams\SilogBundle\Command\GlobalCommand;

use Ams\SilogBundle\Resources\Lib\RnvpLocal;

class Test3Command extends GlobalCommand
{
    protected function configure()
    {
    	$this->sNomCommande	= 'test3';
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console test3
        $this
            ->setDescription('Test RNVP')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output);
    	
    	if(!isset($oRNVP))
		{
			$oRNVP = new RnvpLocal($this->srv_conn, $this->srv_param);
		}
						
		$aArr = array( 	"volet1" 	=> "SDVP",
						"volet2" 	=> "",
						"volet3" 	=> "",
						"volet4" 	=> "69/73 bd Victor Hugo",
						"volet5" 	=> "",
						"cp" 		=> "93558",
						"ville" 	=> "St Ouen cedex"
						);
						
		$aArr = array( 	"volet1" 	=> "ôté",
						"volet2" 	=> "",
						"volet3" 	=> "SDVP 1er étage bat E.",
						"volet4" 	=> "69/73 bd Victor Hugo",
						"volet5" 	=> "",
						"cp" 		=> "93558",
						"ville" 	=> "St Ouen cedex"
						);
    	$oResRNVP = $oRNVP->normalise($aArr);
		if($oResRNVP!==false && $oResRNVP->Elfyweb_RNVP_ExpertResult == 0)
		{
			echo "\n\nRNVP PRET A ETRE UTILISE\n\n";
			print_r($oResRNVP);
		}
		else
		{
			trigger_error("Webservice non passe pour nom : ".$aArr["volet1"]." - cplt nom : ".$aArr["volet2"]." - cplt adr : ".$aArr["volet3"]." - adr : ".$aArr["volet4"]." - lieu dit : ".$aArr["volet5"]." - cp : ".$aArr["cp"]." - ville : ".$aArr["ville"], E_USER_WARNING);
		}
					

        $output->writeln($sRetour);
    }
	/*
    protected function configure()
    {
    	// Pour lancer, faire : php app/console test_command --yell nom
        $this
            ->setName('test_command')
            ->setDescription('Saluer une personne')
            ->addArgument('name', InputArgument::OPTIONAL, 'Qui voulez vous saluer??')
            ->addArgument('chiffre', InputArgument::OPTIONAL, 'Chiffre ....')
            ->addOption('yell', null, InputOption::VALUE_NONE, 'Si définie, la tâche criera en majuscules')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        if ($name) {
            $text = 'Bonjour '.$name;
        } else {
            $text = 'Bonjour';
        }

        if ($input->getOption('yell')) {
            $text = strtoupper($text);
        }
        
        $chiffre = $input->getArgument('chiffre');
   	 	if ($chiffre) {
            $text .= ' ----- chiffre : '.$chiffre;
        } else {
            $text .= ' .... ';
        }

        $output->writeln($text);
    }
    */
}
