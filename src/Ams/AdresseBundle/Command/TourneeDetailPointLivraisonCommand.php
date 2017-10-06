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
 * RENSEIGNE LES POINTS DE LIVRAISON DANS TOURNEE_DETAIL A CEUX QUI N'EN POSSEDENT PAS
 * @author KJeanBaptiste
 */
class TourneeDetailPointLivraisonCommand extends GlobalCommand
{
    
    protected function configure()
    {
        // Pour executer, faire : php app/console tournee_detail_pointlivraison --codeTournee 042NXK009VE --env local
    	$this->sNomCommande	= 'tournee_detail_pointlivraison';
    	$this->setName($this->sNomCommande);
        $this
            ->setDescription('Met à jour les points de livraison null dans la table "tournee_detail"')
            ->addOption('depot',null, InputOption::VALUE_OPTIONAL)
            ->addOption('codeTournee',null, InputOption::VALUE_REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
    	$this->oLog->info('Debut mise a jour point de livraison "tournee_detail"');
        $depot = $input->getOption('depot');
        $codeTournee = $input->getOption('codeTournee');
        $em = $this->getContainer()->get('doctrine')->getManager();
        $data = array();
        if(!empty($codeTournee)){
            $data['modele_tournee_jour_code'] = $codeTournee;
            $em->getRepository('AmsAdresseBundle:TourneeDetail')->updatePointLivraison($data);
            $this->oLog->info('FIN mise a jour point de livraison "tournee_detail"');
        }
        else
            $this->oLog->info('Veuillez renseigner une tournee');
        
        return;
    }
}
