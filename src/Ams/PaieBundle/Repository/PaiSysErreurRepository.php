<?php
namespace Ams\PaieBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class PaiSysErreurRepository extends GlobalRepository {

    function insert($utilisateur_id,$depot_id,$flux_id,$session,$request,$post,$msg, $msgException) {
        $sql = "insert into sys_erreur(utilisateur_id,depot_id,flux_id,session,request,post,msg, msgException,date_creation)
                values($utilisateur_id,$depot_id,$flux_id,'".mysql_real_escape_string(serialize($session))."','".mysql_real_escape_string(serialize($request))."','".mysql_real_escape_string(serialize($post))."','".mysql_real_escape_string($msg)."','".mysql_real_escape_string($msgException)."', now())
               ";
        $this->_em->getConnection()->prepare($sql)->execute();
    }
}
