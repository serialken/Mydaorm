<?php

/**
 * Created by PhpStorm.
 * User: ydieng
 * Date: 08/08/2017
 * Time: 14:05
 */

namespace Ams\DistributionBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Ams\SilogBundle\Command\GlobalCommand;
use Doctrine\DBAL\DBALException;



/**
 *  Class SuiviDeProductionMailRepository
 *  @package Ams\DistributionBundle\Repository
 */
class SuiviDeProductionMailRepository extends EntityRepository
{
    /**
     * 
     * @param type $date
     * @return type
     */
    public function getDatasMailSentByDateEdition($date){
        $sql= "SELECT * FROM suivi_de_production_mail WHERE date_edition = '".$date."' AND  envoyer = 1  ";
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    /**
     * 
     * @param type $nom
     * @param type $dateParution
     * @param type $envoyer
     * @param type $dateEnvoi
     * @param type $etat
     * @return type
     * @throws DBALException
     */
    public function insertValues($nom, $dateParution, $envoyer, $dateEnvoi, $etat){
        $initVal = array();
        try{
            $initVal['nom'] = $nom;
            $initVal['date_edition'] = $dateParution ;
            $initVal['envoyer'] = $envoyer;
            $initVal['date_envoi'] = $dateEnvoi;
            $initVal['etat'] = $etat;
            $this->_em->getConnection()->insert('suivi_de_production_mail', $initVal);
            return $this->_em->getConnection()->lastInsertId();
        } catch (DBALException $ex) {
            throw $ex;
        }
    }
}
