<?php

namespace Ams\ExtensionBundle\Twig;
use \Time;
use \DateTime;

class TimeAdd extends \Twig_Extension
{
    public function getFunctions(){
        return array('timeAdd' => new \Twig_Function_Method($this,'timeAdd'));
    }
    
    public function getName() {
        return 'AmsTimeAdd';
    }
    
    public function timeAdd( $heure_debut, $duree){
        $now = new DateTime();
        return $now;
 //       return $now->diff($duree);
        //return c->diff($duree);
    }
}
?>
