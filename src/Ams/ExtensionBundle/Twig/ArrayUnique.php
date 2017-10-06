<?php

namespace Ams\ExtensionBundle\Twig;

class ArrayUnique extends \Twig_Extension
{
    public function getFunctions(){
        return array('arrayUnique' => new \Twig_Function_Method($this,'arrayUnique'));
    }
    
    public function getName() {
        return 'AmsArrayUnique';
    }
    
    public function arrayUnique($array){
        $return = array_unique($array);
        return $return;
    }
}
?>
