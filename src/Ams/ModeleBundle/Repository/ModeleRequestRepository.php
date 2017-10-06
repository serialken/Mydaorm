<?php
namespace Ams\ModeleBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class ModeleRequestRepository extends GlobalRepository {

    private function execParameter($routine, $session) {
        $parameters=$this->executeSelect("call information_schema_parameters('mroad','$routine')");
        $p='';
        foreach ($parameters as $parameter) {
//            var_dump(substr($parameter["parameter_name"],1));
            $s=$session->get(substr($parameter["parameter_name"],1));
            if (!isset($s)) {
                $p.='null';
            } else {
                switch ($parameter["data_type"]) {
                        case 'int' : 
                            $p.=$this->sqlField->sqlIdOrNull($s);
                            break;
                        case 'varchar' : 
                            $p.=$this->sqlField->sqlQuote($s);
                            break;
                        default:
                            $p.='null';
                }
            }
            $p.="," ;
        }
        return substr($p,0,-1);
    }
    
    function exec($routine, &$heads, &$rows, $session) {
        $p = $this->execParameter($routine, $session);
        $sql = "call $routine($p)" ;
//        var_dump($sql);
        $rows = $this->executeSelect($sql);

        $heads = array();
//        var_dump($rows);
        if (isset($rows[0])) {
            foreach($rows[0] as $name =>$val){
                //"Code__ro_false__80__left__str"
                $attributes = explode('__', $name);
    //                var_dump($key);
                $head["id"]=$name;
                $head["libelle"]=str_replace('_',' ',$attributes[0]); // Remplace les _ par un blanc
                $head["type"]=(isset($attributes[1])?$attributes[1]:"ro"); // ro / ch
                $head["hidden"]=(isset($attributes[2])?$attributes[2]:"false"); // true / false
                $head["width"]=(isset($attributes[3])?$attributes[3]:"*");
                $head["align"]=(isset($attributes[4])?$attributes[4]:"center"); // left / center right
                $head["sort"]=(isset($attributes[5])?$attributes[5]:"str");
                $head["search"]=(isset($attributes[6])?$attributes[6]:"#text_filter"); // #text_filter / #select_filter / #numeric_filter
                $heads[]=$head;
                }
        }
    }
    
    function selectCombo($prefix) {
        return $this->executeSelect("call information_schema_routines('mroad','$prefix')");
    }
}