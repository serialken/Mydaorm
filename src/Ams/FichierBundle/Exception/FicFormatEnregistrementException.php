<?php

namespace Ams\FichierBundle\Exception;

use Exception;

/**
 * Description of FicFormatEnregistrementException
 *
 * @author aandrianiaina
 */
class FicFormatEnregistrementException extends Exception
{
    public static function methodesObligatoires($methodeCourante, $methodes)
    {
        return new self("Il est necessaire d'appeler ".((count($methodes)>1) ?  "les methodes " : "la methode "). '"'.implode('", "', $methodes).'"'." avant la methode ".'"'.$methodeCourante.'"');
    }
    
    public static function chargeDansTableTmp($msg)
    {
        return new self($msg);
    }
}
