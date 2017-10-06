<?php

namespace Ams\ExtensionBundle\Twig;

class Combo extends \Twig_Extension
{
    public function getFunctions(){
        return array('combo' => new \Twig_Function_Method($this,'combo'));
    }
    
    public function getName() {
        return 'AmsCombo';
    }
    
    public function combo( $curseur, $withBlank=false){        
        if ($withBlank){
            $combo='<option value =""></option>';
        }else{
            $combo='';
        }
        foreach($curseur as $row){
            $combo.='<option value ="'.$row['id'].'">'.$row['libelle'].'</option>';
        }
        return $combo;
    }
}

