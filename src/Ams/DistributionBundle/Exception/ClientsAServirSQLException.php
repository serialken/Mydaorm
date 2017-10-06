<?php

namespace Ams\DistributionBundle\Exception;

use Exception;

/**
 * Description of ClientsAServirSQLException
 *
 * @author aandrianiaina
 */
class ClientsAServirSQLException extends Exception
{
    private static $ficRecapId;
    
    /**
     * Exception lors du transfert des donnees de la table TMP vers client_a_servir_logist
     * 
     * @param integer $ficRecapId
     * @param string $msg
     * @return \self
     */
    public static function transfertLogist($ficRecapId, $msg)
    {
        self::setFicRecapId($ficRecapId);
        return new self('TMP vers client_a_servir_logist : '.$msg);
    }
    
    /**
     * Exception lors du transfert des donnees de la table TMP vers client_a_servir_src
     * 
     * @param integer $ficRecapId
     * @param string $msg
     * @return \self
     */
    public static function transfertSrc($ficRecapId, $msg)
    {
        self::setFicRecapId($ficRecapId);
        return new self('TMP vers client_a_servir_src : '.$msg);
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
