<?php 
namespace Ams\AdresseBundle\Command;

//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Doctrine\DBAL\DBALException;

use Ams\SilogBundle\Command\GlobalCommand;

/**
 * 
 * "Command" de calcul automatique de point (stop) de livraison d'une adresse
 * 
 * Pour executer, faire : 
 *                  php app/console pt_livraison
 * @author aandrianiaina
 *
 */
class PtLivraisonCommand extends GlobalCommand
{
    
    protected function configure()
    {
    	$this->sNomCommande	= 'pt_livraison';
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console pt_livraison  
        $this
            ->setDescription("Calcul automatique de point (stop) de livraison d'une adresse")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
    	$this->oLog->info("Debut Calcul automatique de point (stop) de livraison d'une adresse ".$this->sNomCommande);
        
        $repoAdresse = $this->getContainer()->get('doctrine')->getRepository('AmsAdresseBundle:Adresse');
        try {
            $repoAdresse->miseAJourAutomatiquePtLivraison();
        } catch (DBALException $DBALException) {
            $this->oLog->erreur($DBALException->getMessage(), $DBALException->getCode(), $DBALException->getFile(), $DBALException->getLine());
        }
    	
    	$this->oLog->info("Fin Calcul automatique de point (stop) de livraison d'une adresse ".$this->sNomCommande);
    	
        return;
    }
}
