<?php

namespace Ams\DistributionBundle\Exception;

use Exception;

/**
 * Description of CrmSQLException
 *
 * @author aandrianiaina
 */
class CrmSQLException extends Exception
{
    private static $ficRecapId;
    
    /**
     * Exception lors du transfert des donnees de la table TMP vers CrmDetail
     * 
     * @param integer $ficRecapId
     * @param string $msg
     * @return \self
     */
    public static function transfertReperage($ficRecapId, $msg)
    {
        self::setFicRecapId($ficRecapId);
        return new self('TMP vers CrmDetail : '.$msg);
    }
    
 
    private static function setFicRecapId($ficRecapId)
    {
        self::$ficRecapId    = $ficRecapId;
    }
    
    public function getFicRecapId()
    {
        return self::$ficRecapId;
    }      
}
