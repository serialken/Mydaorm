<?php
/**
 * Created by PhpStorm.
 * User: ydieng
 * Date: 19/05/2017
 * Time: 17:06
 */

namespace Ams\DistributionBundle\Exception;

use Exception;

/**
 * Class SuiviDeProductionException
 * @package Ams\DistributionBundle\Exception
 */
class SuiviDeProductionException extends Exception
{
    public static function methodesObligatoires($methodeCourante, $methodes)
    {
        return new self("Il est necessaire d'appeler ".((count($methodes)>1) ?  "les methodes " : "la methode "). '"'.implode('", "', $methodes).'"'." avant la methode ".'"'.$methodeCourante.'"');
    }

}