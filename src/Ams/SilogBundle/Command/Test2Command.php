<?php 
namespace Ams\SilogBundle\Command;

//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Ijanki\Bundle\FtpBundle\Exception\FtpException;
use Ams\SilogBundle\Command\GlobalCommand;

class Test2Command extends GlobalCommand
{
    protected function configure()
    {
    	$this->sNomCommande	= 'test2';
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console test2 --maj 10.151.93.3 sdvp sdvp "/FTPJADE/fic_test.txt" "./fic_test.txt"
        $this
            ->setDescription('Transfert FTP')
            ->addArgument('ip', InputArgument::REQUIRED, 'Adresse IP')
            ->addArgument('login', InputArgument::REQUIRED, 'Login')
            ->addArgument('mdp', InputArgument::REQUIRED, 'Mot de passe')
            ->addArgument('fic_a_recuperer', InputArgument::REQUIRED, 'Fichier a recuperer')
            ->addArgument('fic_en_local', InputArgument::REQUIRED, 'Fichier recupere')
            ->addOption('maj', null, InputOption::VALUE_NONE, 'Si définie, Affichage message en majuscule')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output);
    	echo "*******";
    	print_r($this->getContainer()->getParameter('REP_FICHIERS_CMD'));
    	
    	$ip = $input->getArgument('ip');
        if ($ip) {
            $sRetour = 'IP : '.$ip."\n";
        }
    	$login = $input->getArgument('login');
        if ($login) {
            $sRetour .= 'login : '.$login."\n";
        }
    	$mdp = $input->getArgument('mdp');
        if ($mdp) {
            $sRetour .= 'mdp : '.$mdp."\n";
        }
    	$fic_a_recuperer = $input->getArgument('fic_a_recuperer');
        if ($fic_a_recuperer) {
            $sRetour .= 'fic_a_recuperer : '.$fic_a_recuperer."\n";
        }
    	$fic_en_local = $input->getArgument('fic_en_local');
    	$sRepFichiers	= "../../../../../Symfony_Fichiers/";
        if ($fic_en_local) {
        	$fic_en_local	= __DIR__."/".$sRepFichiers.$fic_en_local;
            $sRetour .= 'fic_en_local : '.$fic_en_local."\n";
        } 
        
   	 	if ($input->getOption('maj')) {
            $sRetour = strtoupper($sRetour);
        }
        
    	try {
	        $ftp = $this->getContainer()->get('ijanki_ftp');
	        $ftp->connect($ip);
	        $ftp->login($login, $mdp);
	        $ftp->get($fic_en_local, $fic_a_recuperer, FTP_BINARY);
	
	    } catch (FtpException $e) {
	        echo '****Error: ', $e->getMessage();
	    }
	    
	    echo "__DIR__ : ".__DIR__;
        
//        $chiffre = $input->getArgument('chiffre');
//   	 	if ($chiffre) {
//            $sRetour .= ' ----- chiffre : '.$chiffre;
//        } else {
//            $sRetour .= ' .... ';
//        }

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
