<?php

namespace Ams\DistributionBundle\Exception;

use Exception;

/**
 * Description of FranceRoutageClientsAServirCommandException
 *
 * @author aandrianiaina
 */
class FranceRoutageClientsAServirCommandException extends Exception
{
    private static $recapFicCourant;
    private static $ficNom;
    private static $codeFicEtat; // le premier "code erreur" rencontre dans le fichier 
    // Si "0" => OK, "99" => Fichier a retraiter, "5"=>Plusieurs dates, "6"=>Fichier vide, "7"=>Plusieurs "code societe", "8"=>"Code societe non defini"
    public static function plusieursDates($ficNom, $recapFicCourant)
    {
        self::initVar($ficNom, $recapFicCourant);
        $msg   = 'Plusieurs dates sont trouvees dans le fichier "'.$ficNom.'".';
        if(\count($recapFicCourant['dateDistrib'])>1)
        {
            $aDate  = array();
            foreach($recapFicCourant['dateDistrib'] as $oDate)
            {
                $aDate[]    = $oDate->format('d/m/Y');
            }
            $msg   = ' - Dates de distribution : '.'"'.\implode('", "', $aDate).'"';
        }
        if(\count($recapFicCourant['dateParution'])>1)
        {
            $aDate  = array();
            foreach($recapFicCourant['dateParution'] as $oDate)
            {
                $aDate[]    = $oDate->format('d/m/Y');
            }
            $msg   = ' - Dates de parution : '.'"'.\implode('", "', $aDate).'"';
        }
        self::setCodeFicEtat(50);
        return new self($msg);
    }
    
    public static function fichierVide($ficNom, $recapFicCourant)
    {
        self::initVar($ficNom, $recapFicCourant);
        $msg   = 'Le fichier "'.$ficNom.'" est vide.';
        self::setCodeFicEtat(51);
        return new self($msg);
    }
    
    public static function plusieursSocCode($ficNom, $recapFicCourant)
    {
        self::initVar($ficNom, $recapFicCourant);
        $msg   = 'Plusieurs "code societe" sont trouves dans le fichier "'.$ficNom.'" : '.'"'.\implode('", "', $recapFicCourant['socCodeExt']).'"';
        self::setCodeFicEtat(52);
        return new self($msg);
    }
    
    public static function socCodeNonDefini($ficNom, $recapFicCourant)
    {
        self::initVar($ficNom, $recapFicCourant);
        $msg   = 'Le "code societe" est vide dans le fichier "'.$ficNom.'".';
        self::setCodeFicEtat(53);
        return new self($msg);
    }
    
    public static function societeInconnue($ficNom)
    {
        self::setFicNom($ficNom);
        $msg   = 'Le "code societe" du fichier'.'"'.$ficNom.'"'." n'est pas parametree";
        self::setCodeFicEtat(54);
        return new self($msg);
    }
    
    public static function plusieursSocieteId($ficNom, $sSocIdTmp)
    {
        self::setFicNom($ficNom);
        $msg   = 'Plusieurs "societe ID (sens M-ROAD)" trouvees. Fichier concerne "'.$ficNom.'" : ID societe -> '.'"'.$sSocIdTmp.'"';
        self::$codeFicEtat = 55;
        return new self($msg);
    }
    
    private static function initVar($ficNom, $recapFicCourant)
    {
        self::$ficNom  = $ficNom;
        self::$recapFicCourant  = $recapFicCourant;
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
        return self::$recapFicCourant;
    }
    
    private static function setCodeFicEtat($codeFicEtat)
    {
        self::$codeFicEtat    = $codeFicEtat;
    }
    
    public function getCodeFicEtat()
    {
        return self::$codeFicEtat;
    }
            
}
