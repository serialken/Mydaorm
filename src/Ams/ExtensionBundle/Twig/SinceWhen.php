<?php

namespace Ams\ExtensionBundle\Twig;
use \DateTime;

class SinceWhen extends \Twig_Extension
{
    public function getFunctions(){
        return array('sinceWhen' => new \Twig_Function_Method($this,'sinceWhen'));
    }
    
    public function getName() {
        return 'AmsSinceWhen';
    }
    
    public function sinceWhen(DateTime $date){
        $now = new DateTime();
        $diff = $now->diff($date);
        if($diff->d > 0){
            $message = $diff->d.' jours ';
        } elseif($diff->h > 0){
            $message = $diff->h.' heures ';
        } elseif($diff->i > 0){
            $message = $diff->i.' minutes ';
        } elseif($diff->s > 0){
            $message = $diff->s.' secondes';
        }
        
        
        return $message;
        //return '10 secondes.';
    }
}
?>
