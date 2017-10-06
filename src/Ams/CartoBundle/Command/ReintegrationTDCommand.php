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
use Ams\AdresseBundle\Command\TourneeDetailRepairOrderCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class ReintegrationTDCommand extends GlobalCommand {

    protected function configure() {
        $this->setName('reintegration_td');
        /** php app/console update_ordre_tournee_casl  * */
        $this
                ->setDescription("Re-integration des abonnes exclus lors de la reprise de Tournee Detail")
//                ->addArgument('mt_code', InputArgument::REQUIRED, 'Racine du Code Modèle Tournee')
//                ->addArgument('date', InputArgument::REQUIRED, 'La date a partir de laquelle appliquer les changements')
//                ->addArgument('dcs_code', InputArgument::REQUIRED, 'Racine du code DCS')
//                ->addArgument('flux', InputArgument::REQUIRED, 'ID du flux a traiter')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
        $time_start = microtime(true);

        $oAlertLogService = $this->getContainer()->get('alertlog');
        /* @var $oAlertLogService \Ams\SilogBundle\Services\Alerts */

        $em = $this->getContainer()->get('doctrine')->getManager();

//        $sDate = $input->getArgument('date'); // Ex : 2014-10-01 pour le 1er Oct 2014
//        $sMTCode = $input->getArgument('mt_code'); // ex: 028
//        $sDCSCode = $input->getArgument('dcs_code'); // ex: K
//        $iFlux = $input->getArgument('flux'); // ex: 1

        $sInfoMsg = 'Debut de re-integration des abonnes exclus lors de la reprise des donnees de TD...';
        echo $sInfoMsg;
        $oAlertLogService->logEvent(
                'carto', // Peut être une de ces valeurs: carto|alim|envt|docs|paie|crm
                'info', // Peut être une de ces valeurs: debug|info|notice|warning|error|critical|alert|emergency
                $sInfoMsg, // Le message d'erreur
                $oAlertLogService->getErrorData("REINTEGRATION_SUITE_REPRISE_TD_CMD_INFO", 'CARTO/REPRISE_TD_START', __FILE__, __LINE__, array(
        )));

//        $sDernDate = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->recupererDerniereDate($sMTJCode);
        // TEST
        // Récupération des tournées concernées
        $aListeTournes = $em->getRepository('AmsAdresseBundle:TourneeDetail')->tourneesAvecAbosAReintegrer();
        
        if (empty($aListeTournes)){
             $sInfoTourneesMsg = 'Aucune tournée concernée par la réintégration de données.';
             echo $sInfoTourneesMsg; 
            // On loggue le nombre de tournées récupérées
            $oAlertLogService->logEvent(
                    'carto', // Peut être une de ces valeurs: carto|alim|envt|docs|paie|crm
                    'info', // Peut être une de ces valeurs: debug|info|notice|warning|error|critical|alert|emergency
                    $sInfoTourneesMsg, // Le message d'erreur
                    $oAlertLogService->getErrorData("REINTEGRATION_SUITE_REPRISE_TD_CMD_INFO", 'CARTO/REPRISE_TD_FETCH', __FILE__, __LINE__, array(
            )));
        }
        else{
            foreach ($aListeTournes as $aTournee){
                $sInfoTourneesReintegrationMsg = 'Traitement de la reintegration de poins sur '.$aTournee['modele_tournee_jour_code'];
                // On loggue le nombre de tournées récupérées
                $oAlertLogService->logEvent(
                        'carto', // Peut être une de ces valeurs: carto|alim|envt|docs|paie|crm
                        'info', // Peut être une de ces valeurs: debug|info|notice|warning|error|critical|alert|emergency
                        $sInfoTourneesReintegrationMsg, // Le message d'erreur
                        $oAlertLogService->getErrorData("REPRISE_TD_CMD_INFO", 'CARTO/REPRISE_TD_FETCH', __FILE__, __LINE__, array(
                )));
                
                // Appel de la commande de ré-intégration
                $command = new TourneeDetailRepairOrderCommand();
                $command->setContainer($this->getContainer());
                $input = new ArrayInput(array('--code_tournee' => $aTournee['modele_tournee_jour_code']));
                $output = new NullOutput();
                $command->run($input, $output);

                // On loggue le résultat final
                $sInfoIntegrationAbosMsg = 'Ré-intégration des abonnés dans ' . $aTournee['modele_tournee_jour_code'];
                echo $sInfoIntegrationAbosMsg;
                $oAlertLogService->logEvent(
                        'carto', // Peut être une de ces valeurs: carto|alim|envt|docs|paie|crm
                        'info', // Peut être une de ces valeurs: debug|info|notice|warning|error|critical|alert|emergency
                        $sInfoIntegrationAbosMsg, // Le message d'erreur
                        $oAlertLogService->getErrorData("REPRISE_TD_CMD_INFO", 'CARTO/REPRISE_TD_REINTEGRATION', __FILE__, __LINE__, array(
                )));
            }
        }

        $time_2 = microtime(true);
        $time = $time_2 - $time_start;

        // On loggue le résultat final
        $sInfoResultatMsg = 'Ré-intégration des lignes de TD effectuée en ' . sprintf("%.2f", $time) . ' sec';
        echo $sInfoResultatMsg;
        $oAlertLogService->logEvent(
                'carto', // Peut être une de ces valeurs: carto|alim|envt|docs|paie|crm
                'info', // Peut être une de ces valeurs: debug|info|notice|warning|error|critical|alert|emergency
                $sInfoResultatMsg, // Le message d'erreur
                $oAlertLogService->getErrorData("REPRISE_TD_CMD_INFO", 'CARTO/REPRISE_TD_END', __FILE__, __LINE__, array(
        )));

        return;
    }

}
