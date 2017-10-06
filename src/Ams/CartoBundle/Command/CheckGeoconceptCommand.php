<?php

namespace Ams\CartoBundle\Command;

//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Ams\WebserviceBundle\Controller\GeneralController as WsGeneralController;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ijanki\Bundle\FtpBundle\Exception\FtpException;
use Ams\SilogBundle\Lib\StringLocal;
use Ams\SilogBundle\Command\GlobalCommand;
use Ams\CartoBundle\Controller\CartoController;

class CheckGeoconceptCommand extends GlobalCommand {

    protected function configure() {
        $this->setName('check_geoconcept');
        /** php app/console check_geoconcept  * */
        $this
                ->setDescription("Verification de la disponibilite des services Geoconcept")
                ->addOption('scope', NULL, InputOption::VALUE_REQUIRED, "Le périmètre sur lequel porte le test ws|server", 'ws')
                ->addOption('mode', NULL, InputOption::VALUE_REQUIRED, "Le mode de d'interrogation de la commande interactif|auto", 'interactif')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
        // Récupération du périmètre
        $sScope = $input->getOption('scope');
        $sMode = $input->getOption('mode');
        $sExpectedHttpCode = '200 OK';

        if ($sMode == "interactif") {
            $time_start = microtime(true);
            $this->oLog->info("Debut de verification des services Geoconcept");
        }

        $em = $this->getContainer()->get('doctrine')->getManager();

        switch ($sScope) {
            case 'ws':
                $sOkMsg = 'Les services Geoconcept semblent être opérationnels.';
                $sSearchAroundUrl = $this->getContainer()->getParameter('GEOC_WS_SEARCHAROUND_SOAP_URL');
                $sRouteServiceUrl = $this->getContainer()->getParameter('GEOC_WS_ROUTESERVICE_SOAP_URL');
                $sGeocodeServiceUrl = $this->getContainer()->getParameter('GEOC_WS_GEOCODE_SOAP_URL');
                $sReverseGeocodeServiceUrl = $this->getContainer()->getParameter('GEOC_WS_RGEOCOD_BASE_URL');

                $aTests = array(
                    'SearchAround' => array('url' => $sSearchAroundUrl, 'type' => 'soap'),
                    'RouteService' => array('url' => $sRouteServiceUrl, 'type' => 'soap'),
                    'GeocodeService' => array('url' => $sGeocodeServiceUrl, 'type' => 'soap'),
                    'ReverseGeocodeService' => array('url' => $sReverseGeocodeServiceUrl, 'type' => 'api'),
                );

                break;
            case 'server':
                $sOkMsg = 'Le serveur Web GeoConcept semble etre operationnel.';
                $sGeoMapsUrl = 'http://' . $this->getContainer()->getParameter('GEOC_WEB_SERVER_IP_PORT') . '/geoweb-easy/maps';

                $aTests = array(
                    'maps' => array('url' => $sGeoMapsUrl, 'type' => 'web'),
                );
                break;
        }

        foreach ($aTests as $sTest => $aInfo) {
            // 1. Connectivité réseau
            // 2. Connectivité HTTP
            // 3. WSDL Opérationnel
            // Test de ping
            $aIpPortParts = explode(':', $this->getContainer()->getParameter('GEOC_WEB_SERVER_IP_PORT'));
            if (!WsGeneralController::pingHost($aIpPortParts[0])) {
                $sErrMsg = $sTest . ' ne repond pas au ping sur son adresse "' . $aIpPortParts[0] . '"';
                if ($sMode == "interactif") {
                    $this->oLog->info($sErrMsg);
                    return "Fin d'execution.";
                } else {
                    echo 'NOK|' . $sErrMsg;
                    exit(1);
                }
            }

            // On teste l'accès à l'URL
            if (!WsGeneralController::testUrl($aInfo['url'], $sExpectedHttpCode)) {
                $sErrMsg = $sTest . ' ne renvoit pas le code HTTP "' . $sExpectedHttpCode . '"';
                if ($sMode == "interactif") {
                    $this->oLog->info($sErrMsg);
                    return "Fin d'execution.";
                } else {
                    echo'NOK|' . $sErrMsg;
                    exit(1);
                }
            }

            switch ($aInfo['type']) {
                case 'soap':
                    try {
                        $oClient = @new \SoapClient($aInfo['url'], array("exceptions" => 0, "trace" => 0));
                        if (empty($oClient->sdl)) {
                            $sErrMsg = 'Le WSDL de ' . $sTest . ' semble poser un problème.';
                            if ($sMode == "interactif") {
                                $this->oLog->info($sErrMsg);
                                return "Fin d'execution.";
                            } else {
                                echo 'NOK|' . $sErrMsg;
                                exit(1);
                            }
                        }
                    } catch (\Exception $ex) {
                        $sErrMsg = 'Le WSDL de ' . $sTest . ' est inaccessible.';
                        if ($sMode == "interactif") {
                            $this->oLog->info($sErrMsg);
                            return "Fin d'execution.";
                        } else {
                            echo 'NOK|' . $sErrMsg;
                            exit(1);
                        }
                    }
                    break;
                case 'api':
                    // @TODO Mettre en place un test plus poussé ?
                    break;
                case 'web':
                    // @Todo Eventuellement faire un test poussé, mais le code 200 dans l'entete de réponse est suffisant ici
                    break;
            }
        }

        if ($sMode == "interactif") {
            $time_2 = microtime(true);
            $time = $time_2 - $time_start;
            $this->oLog->info("Temps de requete " . sprintf("%.2f", $time) . ' sec');
            $this->oLog->info("Fin de verification de la disponibilite des services Geoconcept");
        } else {
            echo 'OK|'.$sOkMsg;
        }
        return;
    }
}
