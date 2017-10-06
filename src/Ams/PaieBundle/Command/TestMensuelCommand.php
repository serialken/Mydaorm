<?php
namespace Ams\PaieBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Ams\SilogBundle\Command\GlobalCommand;
use Doctrine\DBAL\DBALException;

/**
 * 
 * Génération des fichiers pour Pleiades.
 *
 */
class TestMensuelCommand extends GlobalCommand {

    protected function configure() {
        $this->sNomCommande = 'test_mensuel';
        $this->setName($this->sNomCommande);
        $this->setDescription('Generation mensuelle des éléments variables pour Pleiades NG.')
             ->addArgument('utilisateur_id', InputArgument::OPTIONAL, 'Identifiant utilisateur', 0);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
        $this->oLog->sRecipientMailLogErr = "jhodenc@gmail.com";

        $utilisateur_id = $input->getArgument('utilisateur_id');

        $dateDebut = new \DateTime();
        $this->oLog->info($dateDebut->format('H:i:s') . "   \tD&eacute;but de la g&eacute;n&eacute;ration mensuelle des ev pour Pleiades NG.");
        try {
            $idtrt=1065;
       
            $anneeMois = '201401';     
           
           // On lance la génération des annexes tournées 
            if($anneeMois !='') {
                $this->getContainer()->get('doctrine')->getRepository('AmsPaieBundle:PaiIntLog')->fin($idtrt,'ANNEXE_TOURNEE','Nuit');
                exec("php app/console annexe_tournee all 1 ".$anneeMois."  --env=".$this->getContainer()->get('kernel')->getEnvironment()); 
            }else {
                 $this->oLog->info("Une erreur s'est produite lors de la génération des annexes anneeMois est vide!");
            }
        } catch (DBALException $DBALException) {
            $this->oLog->erreur($DBALException->getMessage(), $DBALException->getCode(), $DBALException->getFile(), $DBALException->getLine());
        }
        
        $dateDebut = new \DateTime();
        $this->oLog->info($dateDebut->format('H:i:s') . "   \tFin de la g&eacute;n&eacute;ration mensuelle des ev pour Pleiades NG.");

        return;
    }

}
