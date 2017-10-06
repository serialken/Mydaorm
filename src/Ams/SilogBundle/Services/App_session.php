<?php 

namespace Ams\SilogBundle\Services;

use Symfony\Component\Config\Definition\Exception\Exception;

class App_session
{    
    function get($attr)
    {
    	return ( isset($_SESSION[$attr]) ? $_SESSION[$attr] : '' );
    }
}


