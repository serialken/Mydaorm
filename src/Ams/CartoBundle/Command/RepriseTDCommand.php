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

class RepriseTDCommand extends GlobalCommand {

    protected function configure() {
        $this->setName('reprise_td');
        /** php app/console update_ordre_tournee_casl  * */
        $this
                ->setDescription("Reprise des donnees de Tournee Detail")
                ->addArgument('mt_code', InputArgument::OPTIONAL, 'Racine du Code Modèle Tournee')
                ->addArgument('date', InputArgument::OPTIONAL, 'La date a partir de laquelle appliquer les changements')
                ->addArgument('dcs_code', InputArgument::OPTIONAL, 'Racine du code DCS')
                ->addArgument('flux', InputArgument::OPTIONAL, 'ID du flux a traiter')
                ->addOption('phase', 'p' , InputOption::VALUE_OPTIONAL, 'La phase de reprise a traiter')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
        $time_start = microtime(true);

        $oAlertLogService = $this->getContainer()->get('alertlog');
        /* @var $oAlertLogService \Ams\SilogBundle\Services\Alerts */

        $em = $this->getContainer()->get('doctrine')->getManager();

        $sPhase = $input->getOption('phase'); // ex: phase_40
        
        if (!isset($sPhase) || empty($sPhase)){
            $sDate = $input->getArgument('date'); // Ex : 2014-10-01 pour le 1er Oct 2014
            $sMTCode = $input->getArgument('mt_code'); // ex: 028
            $sDCSCode = $input->getArgument('dcs_code'); // ex: K
            $iFlux = $input->getArgument('flux'); // ex: 1
            
            $aLogInfo = array(
                    'mt_code' => $sMTCode,
                    'date' => $sDate,
                    'dcs_code' => $sDCSCode,
                    'flux' => $iFlux,
            );
            
            // Récupération des tournées à partir des arguments
            $aListeTournes = $em->getRepository('AmsModeleBundle:ModeleTournee')->listerTourneesAReprendre($sMTCode, $sDate, $iFlux, $sDCSCode);
        }
        else{
            $aLogInfo = array(
                    'phase' => $sPhase,
            );
            switch ($sPhase){
                case 'phase_40':
                    $aListeTournes = array();
                    $aListeTourneesCodes = array(
                        '028NJT054',
                        '028NJT058',
                        '028NJT064',
                    );
                    // Tournées 029NKT001  à 029NKT017
                    for ($a=1; $a<=17; $a++){
                        $a = ($a < 10) ? $a = '0'.$a : $a;
                        $aListeTourneesCodes[] = '029NKT0'.$a;
                    }
                    
                    // Tournées 040NRQ051 à 040NRQ80
                    for ($a=51; $a<=80; $a++){
                        $aListeTourneesCodes[] = '040NRQ0'.$a;
                    }
                    
                    // Tournées 041NSQ001  à 041NSQ008
                    for ($a=1; $a<=8; $a++){
                        $aListeTourneesCodes[] = '041NSQ00'.$a;
                    }
                    
                    // Tournées 042NXK001 à 042NXK038
                    for ($a=1; $a<=38; $a++){
                        $a = ($a < 10) ? $a = '0'.$a : $a;
                        $aListeTourneesCodes[] = '042NXK0'.$a;
                    }
                    
                    $aJours = array('LU', 'MA', 'ME', 'JE', 'VE', 'SA');
                    
                    foreach ($aListeTourneesCodes as $sCodeTournee){
                        foreach ($aJours as $sJour){
                            $sCode = $sCodeTournee.$sJour;
                            
                            // Définition de la date
                            switch ($sJour){
                                case 'LU':
                                    $sDate = '2015-12-22';
                                    break;
                                case 'MA':
                                    $sDate = '2015-12-23';
                                    break;
                                case 'ME':
                                    $sDate = '2015-12-24';
                                    break;
                                case 'JE':
                                    $sDate = '2015-12-25';
                                    break;
                                case 'VE':
                                    $sDate = '2015-12-26';
                                    break;
                                case 'SA':
                                    $sDate = '2015-12-27';
                                    break;
                            }
                            
                            $aListeTournes[] = array('code' => $sCode, 'date' => $sDate);   
                        }
                    }
                    
                    break;
            }
        }
        // TODO: Intégrer la date pour la phase_40
        $sInfoMsg = 'Debut de reprise des donnees de TD...';
        echo $sInfoMsg;
        $oAlertLogService->logEvent(
                'carto', // Peut être une de ces valeurs: carto|alim|envt|docs|paie|crm
                'info', // Peut être une de ces valeurs: debug|info|notice|warning|error|critical|alert|emergency
                $sInfoMsg, // Le message d'erreur
                $oAlertLogService->getErrorData("REPRISE_TD_CMD_INFO", 'CARTO/REPRISE_TD_START', __FILE__, __LINE__, $aLogInfo));

//        $sDernDate = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->recupererDerniereDate($sMTJCode);
        // TEST
        
        $sInfoTourneesMsg = count($aListeTournes) . ' tournée(s) ont été trouvée(s) ';
        // On loggue le nombre de tournées récupérées
        $oAlertLogService->logEvent(
                'carto', // Peut être une de ces valeurs: carto|alim|envt|docs|paie|crm
                'info', // Peut être une de ces valeurs: debug|info|notice|warning|error|critical|alert|emergency
                $sInfoTourneesMsg, // Le message d'erreur
                $oAlertLogService->getErrorData("REPRISE_TD_CMD_INFO", 'CARTO/REPRISE_TD_FETCH', __FILE__, __LINE__, $aLogInfo));

        // On ne reprend pas les tournées précédemment optimisées
            $aTourneesOptim = array(
                '028NJT070ME'
                , '028NJN030JE'
                , '028NJN029JE'
                , '028NJN032JE'
                , '028NJN030VE'
                , '028NJN029VE'
                , '028NJN032VE'
                , '028NJN030SA'
                , '028NJN029SA'
                , '028NJN032SA'
                , '028NJN029MA'
                , '028NJN030MA'
                , '028NJN032MA'
                , '028NJN030LU'
                , '028NJN029LU'
                , '028NJN032LU'
                , '028NJN029ME'
                , '028NJN030ME'
                , '028NJN032ME'
            );
            
        // On traite maintenant le problème tournée par tournée
        foreach ($aListeTournes as $sTourneeJCode) {
            
            // Récupération de la date lors des traitements de phase.
            if (!is_null($sTourneeJCode['date'])){
                $sDate = $sTourneeJCode['date'];
            }
            
            if (in_array($sTourneeJCode['code'], $aTourneesOptim)) {
                $sInfoOptimMsg =  'Non prise en compte de la tournée ' . $sTourneeJCode['code'] . ' car elle a été optimisée sur la production.';
                echo $sInfoOptimMsg;
                $aLogInfo['mtj_code'] = $sTourneeJCode['code'];
                $oAlertLogService->logEvent(
                        'carto', // Peut être une de ces valeurs: carto|alim|envt|docs|paie|crm
                        'info', // Peut être une de ces valeurs: debug|info|notice|warning|error|critical|alert|emergency
                        $sInfoOptimMsg, // Le message d'erreur
                        $oAlertLogService->getErrorData("REPRISE_TD_CMD_INFO", 'CARTO/REPRISE_TD_OPTIM_IGNORE', __FILE__, __LINE__, $aLogInfo));
                continue;
            }

            // Marquage des non-livrés dans Tournee_Detail
            $iNonLivres = $em->getRepository('AmsAdresseBundle:TourneeDetail')->marquerNonLivresTournee($sTourneeJCode['code'], $sDate);

            // On loggue le nombre de non livrés pour cette tournée
            $sInfoNonLivresMsg = $iNonLivres . ' points non livrés pour la tournée ' . $sTourneeJCode['code'] . ' dans TD';
            echo $sInfoNonLivresMsg;
            $aLogInfo['mtj_code'] = $sTourneeJCode['code'];
            $oAlertLogService->logEvent(
                    'carto', // Peut être une de ces valeurs: carto|alim|envt|docs|paie|crm
                    'info', // Peut être une de ces valeurs: debug|info|notice|warning|error|critical|alert|emergency
                    $sInfoNonLivresMsg, // Le message d'erreur
                    $oAlertLogService->getErrorData("REPRISE_TD_CMD_INFO", 'CARTO/REPRISE_TD_NON_LIVRES', __FILE__, __LINE__, $aLogInfo));

            // On marque les lignes que l'on va ré-intégrer par la suite
            $iFinalementLivres = $em->getRepository('AmsAdresseBundle:TourneeDetail')->marquerLignesAIntegrer($sTourneeJCode['code'], $sDate);
            // On loggue le nombre de lignes à réintégrer
            $sInfoAIntegrerMsg = $iFinalementLivres . ' points à réintégrer dans la tournée ' . $sTourneeJCode['code'];
            echo $sInfoAIntegrerMsg;
            $oAlertLogService->logEvent(
                    'carto', // Peut être une de ces valeurs: carto|alim|envt|docs|paie|crm
                    'info', // Peut être une de ces valeurs: debug|info|notice|warning|error|critical|alert|emergency
                    $sInfoAIntegrerMsg, // Le message d'erreur
                    $oAlertLogService->getErrorData("REPRISE_TD_CMD_INFO", 'CARTO/REPRISE_TD_A_INTEGRER', __FILE__, __LINE__, $aLogInfo));

            // On supprime finalement les repérages
            $iSuppressions = $em->getRepository('AmsAdresseBundle:TourneeDetail')->supprimerReperages($sTourneeJCode['code']);
            // On loggue le nombre de lignes supprimées
            $sInfoSupReperagesMsg = $iSuppressions . ' repérages supprimés dans la tournée ' . $sTourneeJCode['code'];
            echo $sInfoSupReperagesMsg;
            $oAlertLogService->logEvent(
                    'carto', // Peut être une de ces valeurs: carto|alim|envt|docs|paie|crm
                    'info', // Peut être une de ces valeurs: debug|info|notice|warning|error|critical|alert|emergency
                    $sInfoSupReperagesMsg, // Le message d'erreur
                    $oAlertLogService->getErrorData("REPRISE_TD_CMD_INFO", 'CARTO/REPRISE_TD_SUP_REPERAGES', __FILE__, __LINE__, $aLogInfo));

            // On copie l'ordre de CASL sur TD
            echo 'Copie de l\'ordre de CASL en cours...';
            $iPointsModif = $em->getRepository('AmsAdresseBundle:TourneeDetail')->copierOrdreDeCASL($sTourneeJCode['code'], $sDate);
            // On loggue le nombre de lignes dont l'ordre a été modifié dans TD
            $sInfoLignesOrdreCopieMsg = $iPointsModif . ' modifications d\'ordre dans la tournée ' . $sTourneeJCode['code'];
            echo $sInfoLignesOrdreCopieMsg;
            $oAlertLogService->logEvent(
                    'carto', // Peut être une de ces valeurs: carto|alim|envt|docs|paie|crm
                    'info', // Peut être une de ces valeurs: debug|info|notice|warning|error|critical|alert|emergency
                    $sInfoLignesOrdreCopieMsg, // Le message d'erreur
                    $oAlertLogService->getErrorData("REPRISE_TD_CMD_INFO", 'CARTO/REPRISE_TD_ORDRE_COPIE', __FILE__, __LINE__, $aLogInfo));

            // On compresse l'ordre de TD
            $em->getRepository('AmsAdresseBundle:TourneeDetail')->resetOrdreTourneeApresMarquage($sTourneeJCode['code']);
            // On loggue l'opération
            $sInfoCompressionOrdreMsg = 'Compression de l\'ordre de la tournée TD ' . $sTourneeJCode['code'];
            echo $sInfoCompressionOrdreMsg;
            $oAlertLogService->logEvent(
                    'carto', // Peut être une de ces valeurs: carto|alim|envt|docs|paie|crm
                    'info', // Peut être une de ces valeurs: debug|info|notice|warning|error|critical|alert|emergency
                    $sInfoCompressionOrdreMsg, // Le message d'erreur
                    $oAlertLogService->getErrorData("REPRISE_TD_CMD_INFO", 'CARTO/REPRISE_TD_ORDRE_COMPRIME', __FILE__, __LINE__, $aLogInfo));

            // Lancement de la commande de ré-intégration des points dans TD
            echo "Lancement de la commande de ré-intégration dans TD";
            $command = new TourneeDetailRepairOrderCommand();
            $command->setContainer($this->getContainer());
            $input = new ArrayInput(array('--code_tournee' => $sTourneeJCode['code']));
            $output = new NullOutput();
            $command->run($input, $output);

            // On loggue le résultat final
            $sInfoIntegrationAbosMsg = 'Ré-intégration des abonnés dans ' . $sTourneeJCode['code'];
            echo $sInfoIntegrationAbosMsg;
            $oAlertLogService->logEvent(
                    'carto', // Peut être une de ces valeurs: carto|alim|envt|docs|paie|crm
                    'info', // Peut être une de ces valeurs: debug|info|notice|warning|error|critical|alert|emergency
                    $sInfoIntegrationAbosMsg, // Le message d'erreur
                    $oAlertLogService->getErrorData("REPRISE_TD_CMD_INFO", 'CARTO/REPRISE_TD_REINTEGRATION', __FILE__, __LINE__, $aLogInfo));
        }

        $time_2 = microtime(true);
        $time = $time_2 - $time_start;

        // On loggue le résultat final
        $sInfoResultatMsg = 'Traitement de ' . $sTourneeJCode['code'] . ' effectué en ' . sprintf("%.2f", $time) . ' sec';
        echo $sInfoResultatMsg;
        $oAlertLogService->logEvent(
                'carto', // Peut être une de ces valeurs: carto|alim|envt|docs|paie|crm
                'info', // Peut être une de ces valeurs: debug|info|notice|warning|error|critical|alert|emergency
                $sInfoResultatMsg, // Le message d'erreur
                $oAlertLogService->getErrorData("REPRISE_TD_CMD_INFO", 'CARTO/REPRISE_TD_END', __FILE__, __LINE__, $aLogInfo));

        return;
    }

}
