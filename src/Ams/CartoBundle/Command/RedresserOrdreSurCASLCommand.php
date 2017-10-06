<?php

namespace Ams\CartoBundle\Command;

//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ijanki\Bundle\FtpBundle\Exception\FtpException;
use Ams\SilogBundle\Lib\StringLocal;
use Ams\SilogBundle\Command\GlobalCommand;
use Ams\CartoBundle\Controller\CartoController;

class RedresserOrdreSurCASLCommand extends GlobalCommand {

    protected function configure() {
        $this->setName('update_ordre_tournee_casl');
        /** php app/console update_ordre_tournee_casl  * */
        $this
                ->setDescription("Mise a jour de l'ordre d'une tournée ClientAServirLogist")
                ->addArgument('mtj_code', InputArgument::REQUIRED, 'Code Modèle Tournee Jour')
                ->addArgument('date', InputArgument::REQUIRED, 'La date a partir de laquelle appliquer les changements')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
        $time_start = microtime(true);
        $this->oLog->info("Debut de mise a jour de tournee CASL");

        $em = $this->getContainer()->get('doctrine')->getManager();

        $sMTJCode = $input->getArgument('mtj_code'); // ex: 034NND001JE
        $sDate = $input->getArgument('date'); // Ex : 2014-10-01 pour le 1er Oct 2014
        $aTournee = $em->getRepository('AmsModeleBundle:ModeleTourneeJour')->findIdByCodeDateValid($sMTJCode,$sDate);
        $iTourneeJourId = (int)current($aTournee);
        
        // Code de tournée invalide
        if (empty($aTournee)) {
            $this->oLog->info("Le code de tournee est invalide, fin d'execution.");
            exit();
        }
        else{
            $this->oLog->info("Recalcul de l'ordre des points d'arrets de ".$sMTJCode.' ...');
        }

        // Date invalide
        if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $sDate)) {
            $this->oLog->info("La date fournie est mal formatee, fin d'execution.");
            exit();
        }

        $aListeCASL = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->listerPointsTourneeOrdonnes((int) $iTourneeJourId, $sDate);

        if (!empty($aListeCASL)) {
            $sNomTable = $sMTJCode . '_' . $sDate;
            $aChampsSyntaxe = array(
                "`casl_id` INT unsigned NOT NULL",
                "`point_livraison_id` INT NOT NULL",
                "`nouvel_ordre` INT NOT NULL",
                "`coords` VARCHAR(20) NULL",
                "`abonne_soc_id` INT NULL",
                "PRIMARY KEY (`casl_id`)"
            );

            $createRequest = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->creerTableTemp($sNomTable, $aChampsSyntaxe, $aListeCASL);

            if ($createRequest['creation'] && $createRequest['insertion']) {
                $aCASLRetourOpe = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->redresserOrdresurTourneeRelle($createRequest['nom_table'], $sDate, (int) $iTourneeJourId);
            } else {
                $this->oLog->info("Erreur detectee lors de la création ou de l'alimentation de la table temporaire ".$sNomTable.", fin d'execution.");
                exit();
            }
        }

//        $t = $this->getContainer()->get('ams_util.geoservice');
//        $time_start = microtime(true);
//        $t->insertPointTournee('2014-08-31');
        $time_2 = microtime(true);
        $time = $time_2 - $time_start;
        $this->oLog->info("Temps de requete " . sprintf("%.2f", $time) . ' sec');
        $this->oLog->info("Fin de mise a jour de tournee CASL");
        return;
    }

}
