<?php

namespace Ams\DistributionBundle\Exception;

use Exception;

/**
 * Description of ReperageSQLException
 *
 * @author aandrianiaina
 */
class ReperageSQLException extends Exception
{
    private static $ficRecapId;
    
    /**
     * Exception lors du transfert des donnees de la table TMP vers Reperage
     * 
     * @param integer $ficRecapId
     * @param string $msg
     * @return \self
     */
    public static function transfertReperage($ficRecapId, $msg)
    {
        self::setFicRecapId($ficRecapId);
        return new self('TMP vers reperage : '.$msg);
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
