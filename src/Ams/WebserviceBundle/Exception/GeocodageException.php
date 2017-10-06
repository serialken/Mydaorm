<?php

namespace Ams\WebserviceBundle\Exception;

use \Exception;

/**
 * Exception de Geocodage
 *
 * @author aandrianiaina
 */
class GeocodageException extends Exception
{
    public static function formatTableau()
    {
        return new self("Adresse a geocoder : les donnees doivent etre inclues dans un tableau (cles du tableau : 'City', 'PostalCode', 'AddressLine')");
    }
    
    public static function erreurWebservice($msgErreur, $codeErreurPHP)
    {
        return new self($msgErreur, $codeErreurPHP);
    } 
}
