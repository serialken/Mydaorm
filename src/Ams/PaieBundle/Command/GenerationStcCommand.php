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
class GenerationStcCommand extends GlobalCommand {

    protected function configure() {
        $this->sNomCommande = 'generation_stc';
        $this->setName($this->sNomCommande);
        $this->setDescription('Generation des éléments variables pour Pleiades NG.')
                ->addArgument('flux_id', InputArgument::REQUIRED, "Le flux")
                ->addArgument('utilisateur_id', InputArgument::OPTIONAL, 'Identifiant utilisateur', 0);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
        $this->oLog->sRecipientMailLogErr = "jhodenc@gmail.com";
        
        $flux_id = $input->getArgument('flux_id');
        $utilisateur_id = $input->getArgument('utilisateur_id');

        $dateDebut = new \DateTime();
        $this->oLog->info($dateDebut->format('H:i:s') . "   \tD&eacute;but de la g&eacute;n&eacute;ration des ev pour Pleiades NG.");
        try {
            $anneeMois = $this->getContainer()->get('doctrine')->getRepository('AmsPaieBundle:PaiMois')->getAnneemois($flux_id);     

            $this->getContainer()->get('PleiadesNG')->genererIndividuel($idtrt,$utilisateur_id,$flux_id,$anneeMois);
        } catch (DBALException $DBALException) {
            $this->oLog->erreur($DBALException->getMessage(), $DBALException->getCode(), $DBALException->getFile(), $DBALException->getLine());
        }
        
        $dateDebut = new \DateTime();
        $this->oLog->info($dateDebut->format('H:i:s') . "   \tFin de la g&eacute;n&eacute;ration des ev pour Pleiades NG.");

        return;
    }

}
