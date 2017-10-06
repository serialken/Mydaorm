<?php
/**
 * Description of GeneralController
 * Rassemble des méthodes de tests liés à la connectivité des Web Services
 * @author madelise
 */

namespace Ams\WebserviceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Ams\SilogBundle\Controller\GlobalController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\HttpException;

class GeneralController extends GlobalController{
    /**
     * Teste la connexion à une URL
     * @param string $sUrl
     * @param string $codeExpected Permet de faire le test sur un code de retour attendu
     * @return bool $bReturn TRUE si le code de retour est le code attendu
     */
    public static function testUrl($sUrl, $sCodeExpected = "200 OK"){
        $bReturn = FALSE;
        if (empty($sCodeExpected)){
            return $bReturn;
        }
        
        $aFileHeaders = @get_headers($sUrl);
        preg_match('/'.$sCodeExpected.'/', $aFileHeaders[0], $aMatches);
        if (!empty($aMatches)){
            $bReturn = TRUE;
        }
        return $bReturn;
    }
    
    /**
     * Permet de tester la réponse au ping
     * @param string $sAdress L'adresse IP ou le nom de l'hote
     * @return bool $bStatus TRUE si la cible répond au ping
     */
    public static function pingHost($sAdress){
        $sOsName = strtoupper(substr(PHP_OS, 0, 3));
        $bStatus = FALSE;
        switch (strtolower($sOsName)){
            case 'lin':
                exec("ping -c 1 $sAdress -w 2",$output, $iCodeRetour);
                break;            
            case 'win':
                exec("ping -n 1 $sAdress -w 2",$output, $iCodeRetour);
                break;            
        }
        
        $bStatus = $iCodeRetour == 0 ? TRUE : FALSE;
        
        return $bStatus;
    }
    
    
}
