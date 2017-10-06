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
class GenerationMensuelCommand extends GlobalCommand {

    protected function configure() {
        $this->sNomCommande = 'generation_mensuel';
        $this->setName($this->sNomCommande);
        $this->setDescription('Generation mensuelle des éléments variables pour Pleiades NG.')
                ->addArgument('flux_id', InputArgument::REQUIRED, "Le flux")
                ->addArgument('utilisateur_id', InputArgument::OPTIONAL, 'Identifiant utilisateur', 0)
                ->addArgument('alim_employe', InputArgument::OPTIONAL, 'Alimentation employé', false)
                ->addArgument('alim_octime', InputArgument::OPTIONAL, 'Envoi des badges à Octime', false)
                ->addArgument('alim_pleiades', InputArgument::OPTIONAL, 'Envoi des ev à Pléiades', false)
                ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
        $this->oLog->sRecipientMailLogErr = "jhodenc@gmail.com";

        $flux_id = $input->getArgument('flux_id');
        $utilisateur_id = $input->getArgument('utilisateur_id');
        $alim_employe = $input->getArgument('alim_employe');
        $alim_octime = $input->getArgument('alim_octime');
        $alim_pleiades = $input->getArgument('alim_pleiades');

        $dateDebut = new \DateTime();
        $this->oLog->info($dateDebut->format('H:i:s') . "   \tDebut de la generation mensuelle des ev pour Pleiades NG.");
        try {
            $anneeMois = $this->getContainer()->get('doctrine')->getRepository('AmsPaieBundle:PaiMois')->getAnneemois($flux_id);     
            $this->oLog->info($dateDebut->format('H:i:s') . "   \t".$anneeMois);

            $this->getContainer()->get('PleiadesNG')->genererCollectif($idtrt,$utilisateur_id,$flux_id,$anneeMois, $alim_employe, $alim_octime, $alim_pleiades);
        } catch (DBALException $DBALException) {
            $this->oLog->erreur($DBALException->getMessage(), $DBALException->getCode(), $DBALException->getFile(), $DBALException->getLine());
        }
        
        $dateDebut = new \DateTime();
        $this->oLog->info($dateDebut->format('H:i:s') . "   \tFin de la generation mensuelle des ev pour Pleiades NG.");

        return;
    }

}
