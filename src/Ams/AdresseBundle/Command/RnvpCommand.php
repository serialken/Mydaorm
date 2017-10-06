<?php 
namespace Ams\AdresseBundle\Command;

//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Doctrine\DBAL\DBALException;
use Ams\WebserviceBundle\Exception\RnvpLocalException;

use Ams\SilogBundle\Command\GlobalCommand;

/**
 * 
 * "Command" de RNVP des adresses non encore normalisees (de la table "adresse")
 * 
 * Pour executer, faire : 
 *                  php app/console rnvp
 * @author aandrianiaina
 *
 */
class RnvpCommand extends GlobalCommand
{
    
    protected function configure()
    {
    	$this->sNomCommande	= 'rnvp';
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console rnvp  
        $this
            ->setDescription('RNVP des adresses non encore normalisees (de la table "adresse")')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
    	$this->oLog->info('Debut RNVP des adresses non encore normalisees (de la table "adresse") '.$this->sNomCommande);
        
    	$srvAdresseRnvp    = $this->getContainer()->get('adresse_rnvp');
        try {
            $srvAdresseRnvp->normaliseTouteAdresse($this->getContainer()->getParameter('DATE_FIN'));
        } catch (RnvpLocalException $rnvpLocalException) {
            $this->oLog->erreur($rnvpLocalException->getMessage(), $rnvpLocalException->getCode(), $rnvpLocalException->getFile(), $rnvpLocalException->getLine());
        } catch (DBALException $DBALException) {
            $this->oLog->erreur($DBALException->getMessage(), $DBALException->getCode(), $DBALException->getFile(), $DBALException->getLine());
        }
    	
    	$this->oLog->info('Fin RNVP des adresses non encore normalisees (de la table "adresse") '.$this->sNomCommande);
    	
        return;
    }
}
