<?php

/**
 * Classe fournissant des méthodes liées aux alertes MRoad
 * @author madelise
 */

namespace Ams\SilogBundle\Services;

use \Symfony\Component\DependencyInjection\ContainerAware;
//use Ams\SilogBundle\Controller\GlobalController;

class Alerts extends ContainerAware {

    /**
     * EXEMPLE D'UTILISATION
     * @example  $oAlertLogService = $this->container->get('alertlog'); 
     * @example /* @var $oAlertLogService \Ams\SilogBundle\Services\Alerts */ //<- Conserver les commentaires pour activer l'autocomplétion dans l'IDE
     /**
      * @example  $oAlertLogService->logEvent(
                'carto', // Peut être une de ces valeurs: carto|alim|envt|docs|paie|crm
                'error', // Peut être une de ces valeurs: debug|info|notice|warning|error|critical|alert|emergency
                'Test de replace', // Le message d'erreur
                $oAlertLogService->getErrorData("CODE_D_ERREUR", 'MODULE/ACTION', __FILE__, __LINE__, TABLEAU_D_INFOS_SUPPLEMENTAIRES));
     */
    
    /**
     * Encapsule un appel à Monolog pour enregistrer les évènements importants et problémaiques
     * @param string $sCanal Le domaine lié à l'évènement (carto|alim|envt|docs|paie|crm)
     * @param string $sType La sévérité du log (debug|info|notice|warning|error|critical|alert|emergency)
     * @param string $sMsg Le message d'erreur
     * @param array $aContexte Tableau d'informations complémentaires
     */
    public function logEvent($sCanal, $sType, $sMsg, $aContexte){
        $aLogFolderPaths = $this->container->getParameter('ams_logs')['logfolders'];
        $oLogger = $this->container->get('monolog.logger.'.$sCanal);
        
        switch($sType){
            case 'error':
            case 'critical':
            case 'alert':
            case 'emergency':
                $sHandlerPref = 'erreurs_';
                break;
            case 'debug':
            case 'info':
            case 'notice':
            case 'warning':
            default:
                $sHandlerPref = 'infos_';
                break;
        }
        
        $this->createFolderIfNeeded($aLogFolderPaths[$sCanal]);
        $oLogger->$sType($sMsg, $aContexte);
    }
    
    /**
     * Formate les informations à transmettre au loggeur dans le contexte
     * @param string $sCode Le code d'erreur à remonter
     * @param string $sMode Le mode d'exécution
     * @param string $sFile Le nom du fichier déclenchant l'erreur
     * @param int $iLine Le numéro de la ligne du script
     * @param array $aExtraInfo Un tableau contenant les informations complémentaires
     * @return $aRetour Le tableau des informations formatées
     */
    public function getErrorData($sCode, $sMode, $sFile, $iLine, $aExtraInfo = NULL){
        $aRetour = array(
            'code' => $sCode,
            'mode' => $sMode,
            'file' => $sFile,
            'line' => $iLine,
        );
        
        if (!is_null($aExtraInfo)){
            $aRetour['extras'] = $aExtraInfo;
        }
        
        return $aRetour;
    }
    
    /**
     * Créé le dossier de stockage des logs si celui ci n'existe pas
     * @param string $sPath Le chemin du dossier sur le FS
     */
    private function createFolderIfNeeded($sPath){
        if (!is_dir($sPath)){
            mkdir($sPath, '0770', TRUE);
        }
    }
            
}
