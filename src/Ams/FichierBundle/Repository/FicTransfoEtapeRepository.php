<?php 

namespace Ams\FichierBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\DBALException;

class FicTransfoEtapeRepository extends EntityRepository
{
    
    public function truncateTableTmp($sTableTmp)
    {
        try {
            $sSQLTruncateTmpTable = " TRUNCATE TABLE ".$sTableTmp."; ";
            $this->_em->getConnection()->executeQuery($sSQLTruncateTmpTable);
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    public function getEtapes($sFicTransfoCode)
    {
        try {
            $aRetour    = array();
            $slct = "   SELECT 
                            te.methode_nom
                            , IFNULL(te.methode_param, '') AS methode_param
                            , IFNULL(te.commentaire, '') AS commentaire
                            , te.ordre
                        FROM fic_transfo_etape te
                            INNER JOIN fic_transfo tr ON te.fic_transfo_id = tr.id
                        WHERE
                            tr.code = '".$sFicTransfoCode."'
                            AND te.actif = 1
                        ORDER BY 
                            te.ordre
                        ";
            $res    = $this->_em->getConnection()->fetchAll($slct);
            foreach($res as $aArr) {
                $aRetour[]  = array(
                                    'methode_nom'       => $aArr['methode_nom']
                                    , 'methode_param'   => $aArr['methode_param']
                                    , 'commentaire'     => $aArr['commentaire']
                                    );
            }
            return $aRetour;
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
}