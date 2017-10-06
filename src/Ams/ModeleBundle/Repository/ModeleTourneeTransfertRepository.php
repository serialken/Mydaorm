<?php
namespace Ams\ModeleBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\DBALException;

class ModeleTourneeTransfertRepository extends EntityRepository {
    
    /**
     * Appliquer les transferts parametres
     * @param array $aParam
     * @throws DBALException
     */
    public function appliquer($aParam)
    {
        try {
            //print_r($aParam);
            //$aParam = array(
            //                'separateur_param' => $sSeparateurParam,
            //                'jour_type' => $sJourTypeATraiter,
            //                'tournee' => $sTourneeATraiter,
            //                'date_min' => $oDateMin,
            //                'date_max' => $oDateMax,
            //                    );
            
            $sPrefixeJ_Traites    = 'JOURS_TRAITES:'; // Prefixe a mettre dans le champ modele_tournee_transfert.detail pour informer les jours types deja traites
                        
            $aIdJoursTypesDemanderATraiter  = ( (trim($aParam['jour_type'])!='') ? explode($aParam['separateur_param'], $aParam['jour_type']) : array() );
            
            // Recup parametre
            $sSlct  = " SELECT 
                            mtt.id, DATE_FORMAT(mtt.date_application, '%Y-%m-%d') AS date_application, IFNULL(mtt.detail, '') AS detail_traitement
                        FROM 
                            modele_tournee_transfert mtt
                            INNER JOIN modele_tournee mt ON mtt.tournee_id_init = mt.id ";
            if(trim($aParam['tournee'])!='')
            {
                $sSlct  .= " AND mt.code IN ('".str_replace($aParam['separateur_param'], "', '", $aParam['tournee'])."') ";
            }
            $sSlct  .= " WHERE
                            mtt.date_application BETWEEN '".$aParam['date_min']->format('Y-m-d')."' AND '".$aParam['date_max']->format('Y-m-d')."'
                        ";
            $rSlct  = $this->_em->getConnection()->fetchAll($sSlct);
            foreach($rSlct as $aArr)
            {
                //echo "\r\n------------------\r\n"."-- ID -----> ".$aArr['id']."\r\n";
                $sIdJoursTypesTraites  = '';
                $aIdJoursTypesTraites  = array(); // Id des jours types deja traites
                $aIdJoursTypesATraiter  = array(); // Id des jours types a traiter
                $aIdJoursTypesATraiterDefaut  = array(); // Id des jours types a traiter par defaut
                $sIdJoursTypesTraites  = trim(str_replace($sPrefixeJ_Traites, '', $aArr['detail_traitement']));
                $aIdJoursTypesTraites  = ( ($sIdJoursTypesTraites!='') ? explode($aParam['separateur_param'], $sIdJoursTypesTraites) : array() );
                $aIdJoursTypesTraites  = array_map('trim', $aIdJoursTypesTraites);
                //echo "\r\n"."Jours types deja traiter :"."\r\n";print_r($aIdJoursTypesTraites);echo "\r\n";
                
                $sSlctIdJoursATraiterDefaut = " SELECT
                                                    mtt.id, CURDATE() AS jour_courant, DATE_FORMAT(mtt.date_application, '%Y-%m-%d') AS date_application
                                                    , DATE_ADD(CURDATE(), INTERVAL rj.id DAY) AS jour_a_traiter, CAST(DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL rj.id DAY), '%w') AS SIGNED)+1 AS jour_id_a_traiter
                                                    , mt1.code AS tournee_init, mt2.code AS tournee_future
                                                FROM
                                                    modele_tournee_transfert mtt 
                                                    INNER JOIN modele_tournee mt1 ON mtt.tournee_id_init = mt1.id
                                                    INNER JOIN modele_tournee mt2 ON mtt.tournee_id_future = mt2.id
                                                    INNER JOIN ref_jour rj
                                                WHERE
                                                    mtt.id = ".$aArr['id']."
                                                    AND 
                                                        ( 
                                                            (   IF( 
                                                                    (DATE_ADD(CURDATE(), INTERVAL rj.id DAY) >= mtt.date_application) 
                                                                    AND DATE_ADD(CURDATE(), INTERVAL rj.id DAY) < DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                                                                    AND mtt.date_application >= CURDATE()
                                                                    , 1, 0) = 1 )
                                                                OR IF (mtt.date_application < CURDATE(), 1, 0) = 1
                                                        )
                                                ORDER BY
                                                    mtt.id, jour_id_a_traiter ";
                $rSlctIdJoursATraiterDefaut  = $this->_em->getConnection()->fetchAll($sSlctIdJoursATraiterDefaut);
                foreach($rSlctIdJoursATraiterDefaut as $aArrV)
                {
                    $aIdJoursTypesATraiterDefaut[]  = $aArrV['jour_id_a_traiter'];
                }
                $aIdJoursTypesATraiter  = array_diff($aIdJoursTypesATraiterDefaut, $aIdJoursTypesTraites); // Id des jours types a traiter par defaut
                //echo "\r\n"."Jours types a traiter par defaut :"."\r\n";print_r($aIdJoursTypesATraiter);echo "\r\n";
                if(!empty($aIdJoursTypesDemanderATraiter))
                {
                    $aIdJoursTypesATraiter  = $aIdJoursTypesDemanderATraiter;
                }
                //echo "\r\n"."Jours types a traiter :"."\r\n";print_r($aIdJoursTypesATraiter);echo "\r\n";
                
                
                if(!empty($aIdJoursTypesATraiter))
                {
                    $aTousIdJoursTypesTraites   = array();
                    $sUpdateTd  = " UPDATE
                                        modele_tournee_transfert mtt
                                        INNER JOIN modele_tournee mt1 ON mtt.tournee_id_init = mt1.id
                                        INNER JOIN modele_tournee mt2 ON mtt.tournee_id_future = mt2.id
                                        INNER JOIN tournee_detail td ON mt1.code = LEFT(td.modele_tournee_jour_code, LENGTH(td.modele_tournee_jour_code)-2)
                                    SET 
                                        td.modele_tournee_jour_code = CONCAT(mt2.code, RIGHT(td.modele_tournee_jour_code, 2))
                                        , td.date_modification = NOW()
                                        , td.source_modification = CONCAT('Transfert ', mt1.code, RIGHT(td.modele_tournee_jour_code, 2), ' > ', mt2.code, RIGHT(td.modele_tournee_jour_code, 2))
                                    WHERE
                                        mtt.id = ".$aArr['id']."
                                        AND td.jour_id IN (".(implode(', ', $aIdJoursTypesATraiter)).") ";
                    $this->_em->getConnection()->prepare($sUpdateTd)->execute();
                    // echo "\r\n";print_r($sUpdateTd);
                    
                    $aTousIdJoursTypesTraites   = array_unique(array_merge($aIdJoursTypesTraites, $aIdJoursTypesATraiter));
                    sort($aTousIdJoursTypesTraites);
                    
                    $sUpdateMtt = " UPDATE modele_tournee_transfert SET detail = '".$sPrefixeJ_Traites.implode($aParam['separateur_param'], $aTousIdJoursTypesTraites)."' WHERE id = ".$aArr['id']." ";
                    $this->_em->getConnection()->prepare($sUpdateMtt)->execute();
                    // echo "\r\n";print_r($sUpdateMtt);echo "\r\n";
                    
                }
            }
            
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
}