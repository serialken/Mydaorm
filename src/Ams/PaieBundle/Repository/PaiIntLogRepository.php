<?php
namespace Ams\PaieBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class PaiIntLogRepository extends GlobalRepository {

    function select($idtrt) {
        $sql = "select 
                    pil.id,
                    date_format(pil.date_log,'%d/%m/%Y %H:%i:%s') as date_log,
                    pil.module,
                    pil.msg,
                    pil.level,
                    pil.count
                    FROM pai_int_log pil
                    WHERE pil.idtrt=".$idtrt."
                    ORDER BY pil.id desc
                    ";
        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function debut(&$idtrt, $utilisateur_id, $typetrt, $date_distrib='', $depot_id=0, $flux_id=0) {
        $sql = "set @idtrt=".$this->sqlField->sqlIdOrNull($idtrt).";";
        $sql.="call int_logdebut(".$utilisateur_id.",@idtrt,"
                .$this->sqlField->sqlTrimQuote($typetrt).","
                .$this->sqlField->sqlTrimQuote($date_distrib).","
                .$this->sqlField->sqlIdOrNull($depot_id).","
                .$this->sqlField->sqlIdOrNull($flux_id).")";
        $idtrt = $this->executeProc($sql, "@idtrt");
    }
    
    public function debutAnneeMois(&$idtrt, $utilisateur_id, $typetrt, $date_distrib='', $depot_id=0, $flux_id=0, $anneemois='') {
        $sql = "set @idtrt=".$this->sqlField->sqlIdOrNull($idtrt).";";
        $sql.="call int_logdebutanneemois(".$utilisateur_id.",@idtrt,"
                .$this->sqlField->sqlTrimQuote($typetrt).","
                .$this->sqlField->sqlTrimQuote($date_distrib).","
                .$this->sqlField->sqlIdOrNull($depot_id).","
                .$this->sqlField->sqlIdOrNull($flux_id).","
                .$this->sqlField->sqlTrimQuote($anneemois).")";
        $idtrt = $this->executeProc($sql, "@idtrt");
    }
    
    public function fin($idtrt, $typetrt) {
        $sql = "call int_logfin2(" . $idtrt . ",".$this->sqlField->sqlTrimQuote($typetrt).")";
        $this->executeProc($sql);
    }
    
    public function erreur($idtrt, $typetrt) {
        $sql = "UPDATE pai_int_traitement set date_fin=SYSDATE(),statut='E' WHERE id=". $idtrt." AND statut='C'";
        $this->_em->getConnection()->prepare($sql)->execute();
    }
    
    public function log($idtrt,$module,$msg) {
        try {
            $sql = "call int_loglevel(" . $idtrt . ",5," . $this->sqlField->sqlTrimQuote($module).",".$this->sqlField->sqlTrimQuote($msg).")";
            $this->executeProc($sql);
        } catch (DBALException $ex) {
            return false;
        }
        return true;
    }

     public function logLevel($idtrt,$module,$msg,$level) {
        try {
            $sql = "call int_loglevel(" . $idtrt . ",".$level."," . $this->sqlField->sqlTrimQuote($module).",".$this->sqlField->sqlTrimQuote($msg).")";
            $this->executeProc($sql);
        } catch (DBALException $ex) {
            return false;
        }
        return true;
    }

    public function logErreur($idtrt,$module,$msg) {
        try {
            $sql = "call int_loglevel(" . $idtrt . ",0," . $this->sqlField->sqlTrimQuote($module).",".$this->sqlField->sqlTrimQuote($msg).")";
            $this->executeProc($sql);
        } catch (DBALException $ex) {
            return false;
        }
        return true;
    }
}
