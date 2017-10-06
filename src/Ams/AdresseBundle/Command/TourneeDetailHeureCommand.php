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
 * "Command" de geocodage des adresses RNVP
 * 
 * @author KJeanBaptiste
 *
 */
class TourneeDetailHeureCommand extends GlobalCommand
{
    
    protected function configure()
    {
    	$this->sNomCommande	= 'tournee_detail_init_hour';
    	$this->setName($this->sNomCommande);
        $this
            ->setDescription('Initialise l\'heure de debut dans la table "tournee_detail"')
            ->addOption('depot',null, InputOption::VALUE_OPTIONAL)
            ->addOption('codeTournee',null, InputOption::VALUE_REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
    	$this->oLog->info('Debut (Re)initialisation heure de debut "tournee_detail"');
        $depot = $input->getOption('depot');
        $codeTournee = $input->getOption('codeTournee');
        $em = $this->getContainer()->get('doctrine')->getManager(); 
        if(!empty($codeTournee)){
            $em->getRepository('AmsAdresseBundle:TourneeDetail')->updateBeginHour($codeTournee);
            $this->oLog->info('FIN (Re)initialisation heure de debut "tournee_detail"');
        }
        else
            $this->oLog->info('Veuillez renseigner une tournee');
        
        return;
    }
}
