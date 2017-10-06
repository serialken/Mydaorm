<?php

namespace Ams\EmployeBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Ams\SilogBundle\Command\GlobalCommand;
use Doctrine\DBAL\DBALException;

/**
 * 
 * Génération des fichiers pour Octime.
 *
 */
class GenerationOctime2Command extends GlobalCommand {

    protected function configure() {
        $this->sNomCommande = 'generation_octime2';
        $this->setName($this->sNomCommande);
        $this->setDescription('Generation des fichiers pour Octime.')
             ->addArgument('utilisateur_id', InputArgument::OPTIONAL, 'Identifiant utilisateur', 0);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
        $this->oLog->sRecipientMailLogErr = "jhodenc@gmail.com";

        $utilisateur_id = $input->getArgument('utilisateur_id');
        $idtrt = null;
        $this->getContainer()->get('doctrine')->getRepository('AmsPaieBundle:PaiIntLog')->debut($idtrt, $utilisateur_id,'GENERE_OCTIME');
        $statut='E';
        
        $dateDebut = new \DateTime();
    	$this->oLog->info($dateDebut->format('H:i:s')."   \tD&eacute;but de l'alimentation Octime.");    
    	try {
            $statut=$this->getContainer()->get('Octime')->alimentation($idtrt, $utilisateur_id);
        } catch (DBALException $DBALException) {
            $this->oLog->erreur($DBALException->getMessage(), $DBALException->getCode(), $DBALException->getFile(), $DBALException->getLine());
        }
/*  Ne marche pas via l'interface graphique si on enlève les commentaires
 *         $dateDebut = new \DateTime();
    	$this->oLog->info($dateDebut->format('H:i:s')."   \tFin de l'alimentation Octime.");
*/
        if ($statut!='E'){
/*  Ne marche pas via l'interface graphique si on enlève les commentaires
 *            $dateDebut = new \DateTime();
            $this->oLog->info($dateDebut->format('H:i:s') . "   \tD&eacute;but de la g&eacute;n&eacute;ration des fichiers pour Octime.");
*/            try {
                $this->getContainer()->get('Octime')->generation($idtrt, $utilisateur_id);
            } catch (DBALException $DBALException) {
                $this->oLog->erreur($DBALException->getMessage(), $DBALException->getCode(), $DBALException->getFile(), $DBALException->getLine());
            }
            $dateDebut = new \DateTime();
            $this->oLog->info($dateDebut->format('H:i:s') . "   \tFin de la g&eacute;n&eacute;ration des fichiers pour Octime.");
        }
        
        $this->getContainer()->get('doctrine')->getRepository('AmsPaieBundle:PaiIntLog')->fin($idtrt,'GENERE_OCTIME');
        return;
    }

}
