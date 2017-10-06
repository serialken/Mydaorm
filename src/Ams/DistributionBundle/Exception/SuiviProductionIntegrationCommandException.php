<?php
/**
 * Created by PhpStorm.
 * User: ydieng
 * Date: 16/05/2017
 * Time: 17:16
 */

namespace Ams\DistributionBundle\Exception;

use Exception;

/**
 * Class SuiviProductionIntegrationCommandException
 * @package Ams\DistributionBundle\Exception
 */
class SuiviProductionIntegrationCommandException extends Exception
{
    private static $ficRecapCourant;
    private static $ficNom;
    private static $ficEtatNom; // le premier "code erreur" rencontre dans le fichier

    private static function initVar($ficNom, $ficRecapCourant)
    {
        self::$ficNom  = $ficNom;
        self::$ficRecapCourant  = $ficRecapCourant;
    }

    private static function setFicNom($ficNom)
    {
        self::$ficNom    = $ficNom;
    }

    public function getFicNom()
    {
        return self::$ficNom;
    }

    public function getRecapFicCourant()
    {
        return self::$ficRecapCourant;
    }

    private static function setCodeFicEtat($ficEtatNom)
    {
        self::$ficEtatNom    = $ficEtatNom;
    }

    public function getCodeFicEtat()
    {
        return self::$ficEtatNom;
    }

    public static function fichierVide($ficNom, $ficRecapCourant)
    {
        self::initVar($ficNom, $ficRecapCourant);
        $msg   = 'Le fichier "'.$ficNom.'" est vide.';
        self::setCodeFicEtat(51);
        return new self($msg);
    }
}