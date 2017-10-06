<?php
namespace Ams\SilogBundle\Lib;

class SqlField
{

    public function sqlId($param) {
        return trim($param);
    }
    public function sqlIdOrNull($param) {
        if (!isset($param) || $param == 0)
            return 'NULL';
        else
            return trim($param);
    }

    public function sqlTrim($param) {
        if (trim($param) == '')
            return 'NULL';
        else
            return trim($param);
    }

    public function sqlTrimQuote($param) {
        if (trim($param) == '')
            return 'NULL';
        else
            return "'" . str_replace("'","''",trim($param)) . "'";
    }

    public function sqlQuote($param) {
        return "'" . str_replace("'","''",trim($param)) . "'";
    }

    public function sqlQuantite($param) {
        if (!isset($param) || $param == '')
            return 0;
        else
            return trim($param);
    }

    public function sqlInt($param) {
        return trim($param);
    }

    public function sqlDureeOrNull($param) {
        if (trim($param) == '')
            return 'NULL';
        else
            return "'" . trim($param) . "'";
    }

    public function sqlDureeNotNull($param) {
        if (trim($param) == '')
            return "'00:00:00'";
        else
            return "'" . trim($param) . "'";
    }

    public function sqlDuree($param) {
        return "'" . trim($param) . "'";
    }

    public function sqlHeureOrNull($param) {
        if (trim($param) == '')
            return 'NULL';
        else
            return "'" . trim($param) . "'";
    }

    public function sqlHeure($param) {
        return "'" . trim($param) . "'";
    }

    public function sqlFrenchDate($param) {
        // changeFormatDate('JJ/MM/AAAA', 'Y-m-d', $param);
        $dateInput = explode('-', trim($param));
        return $dateInput[2] . '/' . $dateInput[1] . '/' . $dateInput[0];
    }

    public function sqlDate($param) {
        // changeFormatDate('JJ/MM/AAAA', 'Y-m-d', $param);
        $dateInput = explode('/', trim($param));
        return "'" . $dateInput[2] . '-' . $dateInput[1] . '-' . $dateInput[0] . "'";
    }

    public function sqlDateOr2999($param) {
        if (trim($param) == '')
            return "'2999-01-01'";
        else {
            $dateInput = explode('/', trim($param));
            return "'" . $dateInput[2] . '-' . $dateInput[1] . '-' . $dateInput[0] . "'";
        }
    }

    public function sqlDateOrNull($param) {
        if (trim($param) == '')
            return "NULL";
        else {
            $dateInput = explode('/', trim($param));
            return "'" . $dateInput[2] . '-' . $dateInput[1] . '-' . $dateInput[0] . "'";
        }
    }

    public function sqlError(&$msgReturn, &$msgException, $ex, $msgPerso, $code, $pattern) {
        if (strpos($ex->getMessage(),'Integrity constraint violation: 1062')!==FALSE){
            if ($code=='UNIQUE' && ($pattern=='' || strpos($ex->getMessage(),$pattern)!==FALSE)){
                $msgReturn .= $msgPerso;
            }else{
                $msgReturn .="L'enregistrement doit être unique.";
            }
        }elseif (strpos($ex->getMessage(),'Integrity constraint violation: 1451')!==FALSE){
            if ($code=='FOREIGN' && ($pattern=='' || strpos($ex->getMessage(),$pattern)!==FALSE)){
                $msgReturn .= $msgPerso;
            }else{
                $msgReturn .="L'enregistrement est utilisé.<br/>Suppression impossible.";
            }
        }elseif ($pattern!='' && strpos($ex->getMessage(),$pattern)!==FALSE){
            $msgReturn .= $msgPerso;
        }elseif ($code=='' && $pattern=='' && $msgPerso!=''){
            $msgReturn .= $msgPerso;
        }else{
            $msgReturn .= 'Erreur inconnue.';
        }
/*        
        if ($code=='UNIQUE' && ($pattern=='' || strpos($ex->getMessage(),$pattern)>0 && strpos($ex->getMessage(),'Integrity constraint violation: 1062')>0)){
            $msgReturn .= $msgPerso;
        }elseif ($code=='FOREIGN' && ($pattern=='' || strpos($ex->getMessage(),$pattern)>0 && strpos($ex->getMessage(),'Integrity constraint violation: 1451'))){
            $msgReturn .= $msgPerso;
        }elseif ($code=='' && ($pattern=='' || strpos($ex->getMessage(),$pattern)>0)){
            $msgReturn .= $msgPerso;
        }else{
            $msgReturn .= 'Erreur inconnue.';
        }
 */
        $msgReturn .= '<br/>';
        // str_replace pour affichage xml lorsque le message est généré par trigger (ex : jourFerie)
        $msgException .= str_replace('>>','',str_replace('<<','',$ex->getMessage()));

        return false;
//Integrity constraint violation: 1062 Duplicate entry '2-1-BA' for key 'un_groupe_tournee'
//Integrity constraint violation: 1451 Cannot delete or update a parent row: a foreign key constraint fails (`silog`.`modele_tournee`, CONSTRAINT `FK_EE7B87AC7A45358C` FOREIGN KEY (`groupe_id`) REFERENCES `groupe_tournee` (`id`))
    }
}