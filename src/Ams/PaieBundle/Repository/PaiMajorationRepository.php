<?php
namespace Ams\PaieBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class PaiMajorationRepository extends GlobalRepository {


    public function recalcul_date_distrib($date_distrib, $depot_id, $flux_id) {
        try {
            $sql = "call recalcul_majoration(".$this->sqlField->sqlTrimQuote($date_distrib).",".$this->sqlField->sqlIdOrNull($depot_id).",".$this->sqlField->sqlIdOrNull($flux_id).",null)";
            $this ->executeProc($sql);
        } catch (DBALException $ex) {
            throw $ex;
        }
    }

    public function recalcul_id($id) {
        try {
            $sql = "call recalcul_majoration_tournee(".$this->sqlField->sqlIdOrNull($id).")";
            $this ->executeProc($sql);
        } catch (DBALException $ex) {
            throw $ex;
        }
    }
}
