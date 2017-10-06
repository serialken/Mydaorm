<?php

namespace Ams\WebserviceBundle\Exception;

use \Exception;

/**
 * Description of RnvpLocalException
 *
 * @author aandrianiaina
 */
class RnvpLocalException extends Exception
{
    private static $codeRetourRnvp;
    
    public static function formatTableau()
    {
        return new self("Adresse a normaliser : les donnees doivent etre inclues dans un tableau");
    }
    
    public static function attributsManquants($attrManquants)
    {
        return new self("Adresse a normaliser : les attributs suivants sont aussi obligatoires : ".'"'.implode('", "', $attrManquants).'"');
    }
    
    public static function erreurWebservice($codeRetourRnvp, $msgErreur, $codeErreurPHP)
    {
        return self::erreurNormalisation($codeRetourRnvp, $msgErreur, $codeErreurPHP);
    } 
    
    public static function erreurNormalisation($codeRetourRnvp, $msgErreur, $codeErreurPHP)
    {
        self::setCodeErreurRnvp($codeRetourRnvp);
        
        return new self($msgErreur, $codeErreurPHP);
    }  
    
    public function setCodeErreurRnvp($codeRetourRnvp)
    {
        self::$codeRetourRnvp = $codeRetourRnvp;
    }   
    public function getCodeErreurRnvp()
    {
        return self::$codeRetourRnvp;
    }
}
