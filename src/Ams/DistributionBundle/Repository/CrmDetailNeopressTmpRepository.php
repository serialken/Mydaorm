<?php

namespace Ams\DistributionBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\DBALException;
use Ams\WebserviceBundle\Exception\RnvpLocalException;

class CrmDetailNeopressTmpRepository extends EntityRepository {
    
    public function getNblignes()
    {
        try {
            $qb = $this->createQueryBuilder('t');
            $qb->select($qb->expr()->count('t').' AS nbLignes');
            
            return $qb->getQuery()->getResult();
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
            $select = "SELECT DISTINCT societe_id AS SOCIETE_ID FROM crm_detail_neopress_tmp ";
            return $this->_em->getConnection()->fetchAll($select);
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

    /**
     * Selection des "code societe exterieur"
     * @return type
     */
    public function getCodeSocExt()
    {
        try {
            return $this->createQueryBuilder('t')
                        ->select('DISTINCT t.socCodeExt')
                        ->getQuery()->getResult();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

    public function getDateDistribution(){
        try {
            return $this->createQueryBuilder('t')
                        ->select('DISTINCT t.dateDistribution')
                        ->getQuery()->getResult();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    
    /**
     * Selection des "code produit neopress"
     * @return type
     */
    public function getPrdCodeNeopress()
    {
        try {
            return $this->createQueryBuilder('t')
                        ->select('DISTINCT t.prdCodeNeopress')
                        ->where('t.prdCodeNeopress is not null')
                        ->andWhere("t.prdCodeNeopress != '' ")
                        ->getQuery()->getResult();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
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
                                            crm_detail_neopress_tmp 
                                    SET client_type = ".$clientType." ")
                    ;
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Suppression des "\r" et "\n" pour tous les champs
     * @throws \Doctrine\DBAL\DBALException
     */
    public function supprCaracteresSpeciaux()
    {
        try {
            $update = " UPDATE crm_detail_neopress_tmp 
                        SET 
                            numabo_ext = REGEX_REPLACE_1('[\r\n]', '', TRIM(numabo_ext))
                            , vol1 = REGEX_REPLACE_1('[\r\n]', '', TRIM(vol1))
                            , vol2 = REGEX_REPLACE_1('[\r\n]', '', TRIM(vol2))
                            , vol3 = REGEX_REPLACE_1('[\r\n]', '', TRIM(vol3))
                            , vol4 = REGEX_REPLACE_1('[\r\n]', '', TRIM(vol4))
                            , vol5 = REGEX_REPLACE_1('[\r\n]', '', TRIM(vol5))
                            , cp = REGEX_REPLACE_1('[\r\n]', '', TRIM(cp))
                            , ville = REGEX_REPLACE_1('[\r\n]', '', TRIM(ville))
                            , soc_code_ext = REGEX_REPLACE_1('[\r\n]', '', TRIM(soc_code_ext))
                            , cmt_demande = REGEX_REPLACE_1('[\r\n]', '', TRIM(cmt_demande))
                        ";
            $this->_em->getConnection()->executeQuery($update);
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        } 
    }
    
    /**
     * Mise en majuscule des champs adresse
     */
    public function miseEnMajusculeAdresses()
    {
        try {
            $this->createQueryBuilder('t')->update()
                    ->set('t.vol1', 'UPPER(t.vol1)')
                    ->set('t.vol2', 'UPPER(t.vol2)')
                    ->set('t.vol3', 'UPPER(t.vol3)')
                    ->set('t.vol4', 'UPPER(t.vol4)')
                    ->set('t.vol5', 'UPPER(t.vol5)')
                    ->set('t.cp', 'UPPER(t.cp)')
                    ->set('t.ville', 'UPPER(t.ville)')
                    ->getQuery()->execute();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Mise a jour du champ crm_demande_id
     * 
     * @throws \Doctrine\DBAL\DBALException
     */
    public function updateDemandeId()
    {
        try {
            $this->_em->getConnection()
                    ->executeQuery("UPDATE
                                            crm_detail_neopress_tmp t, crm_corresp_demande_neopress np
                                    SET t.crm_demande_id = np.crm_demande_id 
                                    WHERE t.code_demande=np.code
                                    AND np.crm_demande_id is not null
                            ")
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
                                        crm_detail_neopress_tmp t, crm_corresp_reponse_neopress np
                                    SET t.crm_reponse_id = np.crm_reponse_id 
                                    WHERE t.code_reponse=np.code
                                    AND np.crm_reponse_id is not null
                            ")
                    ;
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    /**
     * [updateCodeDemande mise a jour code demande]
     * @return [type] [description]
     */
    public function updateCodeDemande(){
        try {
            $crmDemandeRep = $this->_em->getRepository('AmsDistributionBundle:CrmDemande');
            $this->_em->getConnection()
                    ->executeQuery("UPDATE
                                            crm_detail_neopress_tmp t, crm_corresp_demande_neopress np
                                    SET t.code_demande = (select  code from crm_demande cd where t.crm_demande_id = cd.id AND t.crm_demande_id is not null)
                                    /*WHERE t.code_demande=np.code*/
                                    ")
                    ;
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }

    }
    
    /**
     * Mise a jour du champ societe_id et soc_code_ext
     * 
     * @throws \Doctrine\DBAL\DBALException
     */
    public function updateSocieteIdAndSocCodeExt()
    {
        try {
            $repoProduit = $this->_em->getRepository('AmsProduitBundle:Produit');
            $aPrdCodeNeopress    = $this->getPrdCodeNeopress();
            foreach($aPrdCodeNeopress as $prdNeopress)
            {
          
                $produit = $repoProduit->findOneBy(array('prdCodeNeopress' => $prdNeopress['prdCodeNeopress']));
                if(!empty($produit)){
                    $societe_id = $produit->getSociete()->getId();
                    $socCodeExt= $produit->getSocCodeExt();
                    /*
                    $update = " UPDATE crm_detail_neopress_tmp 
                                SET societe_id = ".$societe_id.", 
                                    soc_code_ext = '".$socCodeExt."'
                                WHERE prd_code_neopress = '".$prdNeopress['prdCodeNeopress']."' ";
                    $this->_em->getConnection()
                        ->executeQuery($update);*/
                    $update = " UPDATE crm_detail_neopress_tmp 
                                SET societe_id = ?, 
                                    soc_code_ext = ?
                                WHERE prd_code_neopress = ? ";
                     $this->_em->getConnection()->executeUpdate($update,array($societe_id,$socCodeExt,$prdNeopress['prdCodeNeopress']));   
                    $this->_em->clear();
                }
            }
            
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    

    
    /**
     * Mise a jour des champs adresse, rnvp  commune, vol1,vol2...vol5 , aboone_soc_id pour les adresses connues/inconnues
     * selon le paramaitre dateParam
     * @throws \Doctrine\DBAL\DBALException
     */
    public function updateAdresse($dateParam)
    {
       
        try {
            $sUpdate    = " UPDATE crm_detail_neopress_tmp t, adresse adr , abonne_soc abs 
                            SET 
                                t.vol1  = adr.vol1,
                                t.vol2  = adr.vol2,
                                t.vol3  = adr.vol3,
                                t.vol4  = adr.vol4,
                                t.vol5  = adr.vol5,
                                t.cp    = adr.cp,
                                t.ville = adr.ville,
                                t.commune_id = adr.commune_id,
                                t.adresse_id = adr.id,
                                t.rnvp_id = adr.rnvp_id,
                                t.abonne_soc_id = abs.id
                            WHERE
                                t.numabo_ext=abs.numabo_ext AND adr.abonne_soc_id=abs.id
                                
                                 ";
            if($dateParam){
               $sUpdate .=  " AND t.date_creat BETWEEN adr.date_debut AND adr.date_fin ";
            }
            if(!$dateParam){
               $sUpdate .=  " AND (t.vol1 is null OR t.vol1 ='')  AND adr.date_fin = (select max(adr2.date_fin) from adresse adr2 where adr2.id = adr.id) ";
            }

            $this->_em->getConnection()
                    ->executeQuery($sUpdate);
            
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
   
    /**
     * Mise a jour de l'attribut de depot
     */
    public function updateDepot() {
        try {
           
            // Depot en fonction de la date de creation
            $this->_em->getConnection()
                    ->executeQuery("UPDATE
                                        crm_detail_neopress_tmp AS t
                                        LEFT JOIN depot_commune dc ON t.commune_id=dc.commune_id AND t.date_creat BETWEEN dc.date_debut AND dc.date_fin 
                                    SET 
                                        t.depot_id=dc.depot_id
                                    WHERE 
                                        t.depot_id IS NULL
                                        AND dc.commune_id IS NOT NULL
                                         ")
            ;
          
            $this->_em->clear();
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


    /**
     * Verifie si la societe est inconnue
     * @return type
     */
    public function isSocieteInconnue()
    {
        try {
            $select = "SELECT COUNT(*) AS NB FROM crm_detail_neopress_tmp WHERE societe_id IS NULL";
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
     * [updateRecInCrmDetail description]
     * @return [type] [description]
     */
    public function updateRecInCrmDetail(){
        try {
            $this->_em->getConnection()
                    ->executeQuery("UPDATE
                                        crm_detail crm, crm_detail_neopress_tmp t , produit p
                                    SET crm.date_reponse = t.date_reponse,
                                        crm.cmt_reponse = t.cmt_reponse,
                                        crm.crm_reponse_id = t.crm_reponse_id
                                    WHERE 
                                        crm.crm_id_ext = t.crm_id_ext
                                    AND p.societe_id   =crm.societe_id
                                    AND p.prd_code_neopress = t.prd_code_neopress");
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }

    }
    
    
}
