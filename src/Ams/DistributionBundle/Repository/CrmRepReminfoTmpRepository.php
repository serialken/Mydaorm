<?php

namespace Ams\DistributionBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\DBALException;

class CrmRepReminfoTmpRepository extends EntityRepository {
    
    /**
     * Definit le type de client (abonne ou LV)
     * 
     * @param integer $clientType
     * @throws \Doctrine\DBAL\DBALException
     */
    public function updateClientType($clientType=0)
    {
        try {
            $this->_em->getConnection()
                    ->executeQuery("UPDATE
                                            crm_rep_reminfo_tmp 
                                    SET client_type = ".$clientType." ")
                    ;
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Mise a jour du champ crm_reponse_id
     * 
     * @throws \Doctrine\DBAL\DBALException
     */
    public function updateReponseId()
    {
        try {
            $this->_em->getConnection()
                    ->executeQuery("UPDATE
                                        crm_rep_reminfo_tmp t
                                        INNER JOIN crm_reponse r ON t.code_reponse = r.code AND r.crm_categorie_id IN (2)
                                    SET t.crm_reponse_id = r.id 
                            ")
                    ;
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Mise a jour du champ societe_id
     * 
     * @throws \Doctrine\DBAL\DBALException
     */
    public function updateSocieteId()
    {
        try {
            $update = " UPDATE crm_rep_reminfo_tmp t
                            INNER JOIN societe s ON t.soc_code_ext = s.code
                        SET
                            t.societe_id = s.id ";
            $this->_em->getConnection()
                    ->executeQuery($update);
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Mise a jour du champ abonne_soc_id
     * 
     * @throws \Doctrine\DBAL\DBALException
     */
    public function updateAbonneSoc()
    {
        try {
            $this->_em->getConnection()
                    ->executeQuery("UPDATE
                                            crm_rep_reminfo_tmp t, abonne_soc a
                                    SET t.abonne_soc_id = a.id 
                                    WHERE 
                                        t.numabo_ext=a.numabo_ext AND t.soc_code_ext=a.soc_code_ext
                            ")
                    ;
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Selection "societe id"
     * @return type
     */
    public function getSocieteId()
    {
        try {
            $select = "SELECT DISTINCT societe_id AS SOCIETE_ID FROM crm_rep_reminfo_tmp ";
            return $this->_em->getConnection()->fetchAll($select);
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Verifie si la societe est inconnue
     * @return type
     */
    public function isSocieteInconnue()
    {
        try {
            $select = "SELECT COUNT(*) AS NB FROM crm_rep_reminfo_tmp WHERE societe_id IS NULL";
            $aRes = $this->_em->getConnection()->fetchAll($select);
            foreach($aRes as $aArr)
            {
                return ($aArr['NB']>0)? true : false;
            }
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Mise a jour de la colonne "ficRecap". 
     * @param integer $iDernierFicRecap
     */
    public function updateFicRecap($iDernierFicRecap)
    {
        try {
            $this->createQueryBuilder('t')->update()->set('t.ficRecap', $iDernierFicRecap)->getQuery()->execute();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    
    
}
