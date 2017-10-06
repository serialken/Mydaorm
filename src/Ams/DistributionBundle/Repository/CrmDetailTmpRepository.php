<?php

namespace Ams\DistributionBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\DBALException;
use Ams\WebserviceBundle\Exception\RnvpLocalException;

class CrmDetailTmpRepository extends EntityRepository {
    
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
                                            crm_detail_tmp 
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
            $update = " UPDATE crm_detail_tmp 
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
                                            crm_detail_tmp t, crm_demande d
                                    SET t.crm_demande_id = d.id 
                                    WHERE 
                                        t.code_demande=d.code AND d.crm_categorie_id IN (1, 3)
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
            $repoProduit = $this->_em->getRepository('AmsProduitBundle:Produit');
            $aSocCodeExt    = $this->getCodeSocExt();
            foreach($aSocCodeExt as $aSoc)
            {
                $update = " UPDATE crm_detail_tmp 
                            SET societe_id = ".$repoProduit->findOneBy(array('socCodeExt' => $aSoc['socCodeExt']))->getSociete()->getId()." 
                            WHERE soc_code_ext = '".$aSoc['socCodeExt']."' ";
                $this->_em->getConnection()
                    ->executeQuery($update);
                $this->_em->clear();
            }
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
                                            crm_detail_tmp t, abonne_soc a
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
     * Mise a jour des champs adresse, rnvp et commune pour les adresses connues
     * 
     * @throws \Doctrine\DBAL\DBALException
     */
    public function updateAdresse()
    {
        try {
            // on met a jour d'abord les infos autour d'adresse a partir de l'adresse connue de l'abonne
            //      par rapport a la date de debut de prejudice 
            //      , par rapport a la date de fin de prejudice 
            //      , par rapport a la date de creation 
            //      , puis par rapport a la derniere ligne d'adresse 
            $aAdrCandidatTmp   = array();
            /*$slct   = "     SELECT
                                t.id, a.id AS adresse_id, IFNULL(a.commune_id, 0) AS commune_id, IFNULL(a.rnvp_id, 0) AS rnvp_id
                                , CASE 
                                    WHEN DATE_FORMAT(t.date_debut, '%Y%m%d')<>'00000000' AND t.date_debut BETWEEN a.date_debut AND a.date_fin THEN 9
                                    WHEN DATE_FORMAT(t.date_fin, '%Y%m%d')<>'00000000' AND t.date_fin BETWEEN a.date_debut AND a.date_fin THEN 7
                                    WHEN t.date_creat BETWEEN a.date_debut AND a.date_fin THEN 5
                                    ELSE 0			
                                    END AS poids
                        FROM
                            crm_detail_tmp t, adresse a
                        WHERE
                            t.vol1=a.vol1 AND t.vol2=a.vol2
                            AND t.vol3=a.vol3 AND t.vol4=a.vol4 AND t.vol5=a.vol5
                            AND t.cp=a.cp AND t.ville=a.ville
                            AND t.abonne_soc_id=a.abonne_soc_id
                            AND t.adresse_id IS NULL
                        ORDER BY 
                            t.id ASC, poids DESC, a.id DESC 
                        ";*/
            
            $slct   = "     SELECT
                                t.id, a.id AS adresse_id, IFNULL(a.commune_id, 0) AS commune_id, IFNULL(a.rnvp_id, 0) AS rnvp_id
                                , CASE 
                                    WHEN DATE_FORMAT(t.date_debut, '%Y%m%d')<>'00000000' AND t.date_debut BETWEEN a.date_debut AND a.date_fin THEN 9
                                    WHEN DATE_FORMAT(t.date_fin, '%Y%m%d')<>'00000000' AND t.date_fin BETWEEN a.date_debut AND a.date_fin THEN 7
                                    WHEN t.date_creat BETWEEN a.date_debut AND a.date_fin THEN 5
                                    ELSE 0			
                                    END AS poids
                        FROM
                            crm_detail_tmp t, adresse a 
                        WHERE
                            t.abonne_soc_id=a.abonne_soc_id
                            AND t.adresse_id IS NULL AND (a.type_adresse IS NULL OR a.type_adresse = 'L')
                        ORDER BY 
                            t.id ASC, poids DESC, a.id DESC 
                        ";
            $res    = $this->_em->getConnection()->fetchAll($slct);
            foreach($res as $aArr) {
                if(!isset($aAdrCandidatTmp[$aArr['id']]))
                {
                    $aTmp   = array();
                    $aTmp['adresse_id']     = $aArr['adresse_id'];
                    $aTmp['commune_id']     = $aArr['commune_id'];
                    $aTmp['rnvp_id']        = $aArr['rnvp_id'];
                }
                $aAdrCandidatTmp[$aArr['id']]    = $aTmp;
            }
            foreach($aAdrCandidatTmp as $idTmp => $aAdrV)
            {
                $sUpdate    = " UPDATE crm_detail_tmp SET 
                                    adresse_id = ".$aAdrV['adresse_id']."
                                    , commune_id = ".(($aAdrV['commune_id']==0)?'NULL':$aAdrV['commune_id'])."
                                    , rnvp_id = ".(($aAdrV['rnvp_id']==0)?'NULL':$aAdrV['rnvp_id'])."
                                WHERE
                                    id = ".$idTmp."";
                $this->_em->getConnection()->executeQuery($sUpdate);
            }
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Mise a jour du champ commune vide 
     * 
     * @throws \Doctrine\DBAL\DBALException
     */
    public function updateCommune($srv_rnvp)
    {
        try {
            $sUpdate    = " UPDATE crm_detail_tmp t, commune c 
                            SET 
                                t.commune_id = c.id
                            WHERE
                                t.ville=c.libelle AND t.cp=c.cp 
                                AND t.commune_id IS NULL ";
            $this->_em->getConnection()
                    ->executeQuery($sUpdate);
            
            
            // Appel du service RNVP pour les couples commune/CP non connus
            $slct   = " SELECT
                            id, cp, ville
                        FROM
                            crm_detail_tmp 
                        WHERE
                            commune_id IS NULL
                        ";
            $res    = $this->_em->getConnection()->fetchAll($slct);
            foreach($res as $aArr) {
                $aEntreeRNVP = array(
                        "volet1" => '',
                        "volet2" => '',
                        "volet3" => '',
                        "volet4" => '',
                        "volet5" => '',
                        "cp"    => $aArr['cp'],
                        "ville" => $aArr['ville']
                );
                $rnvp  = $srv_rnvp->normalise($aEntreeRNVP);
                if($rnvp!==false && $rnvp->Elfyweb_RNVP_ExpertResult == 0)
                {
                    $update = " UPDATE crm_detail_tmp t, commune c SET t.commune_id = c.id 
                                WHERE 
                                    t.id = ".$aArr['id']." 
                                    AND c.insee = '".$rnvp->po_insee."' ";
                    $this->_em->getConnection()
                                ->executeQuery($update);
                }
            }
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
        catch (RnvpLocalException $rnvpLocalException) {
            throw $rnvpLocalException;
        }        
    }

    /**
     * Mise a jour de l'attribut de depot
     */
    public function updateDepot() {
        try {
            // Depot en fonction de la date debut de prejudice
            $this->_em->getConnection()
                    ->executeQuery("UPDATE
                                        crm_detail_tmp AS t
                                        LEFT JOIN depot_commune dc ON t.commune_id=dc.commune_id AND t.date_debut BETWEEN dc.date_debut AND dc.date_fin 
                                    SET 
                                        t.depot_id=dc.depot_id
                                    WHERE 
                                        t.depot_id IS NULL
                                        AND dc.commune_id IS NOT NULL
                                         ")
            ;
            // Depot en fonction de la date de creation
            $this->_em->getConnection()
                    ->executeQuery("UPDATE
                                        crm_detail_tmp AS t
                                        LEFT JOIN depot_commune dc ON t.commune_id=dc.commune_id AND t.date_creat BETWEEN dc.date_debut AND dc.date_fin 
                                    SET 
                                        t.depot_id=dc.depot_id
                                    WHERE 
                                        t.depot_id IS NULL
                                        AND dc.commune_id IS NOT NULL
                                         ")
            ;
            // Depot en fonction de la date du jour
            $this->_em->getConnection()
                    ->executeQuery("UPDATE
                                        crm_detail_tmp AS t
                                        LEFT JOIN depot_commune dc ON t.commune_id=dc.commune_id AND CURRENT_DATE() BETWEEN dc.date_debut AND dc.date_fin 
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
     * Mise a jour de l'attribut de depot
     */
    public function insertNouvReclams() {
        try {
            // Depot en fonction de la date debut de prejudice
            $this->_em->getConnection()
                    ->executeQuery("
                                    UPDATE
                                        crm_detail_tmp AS t
                                        LEFT JOIN depot_commune dc ON t.commune_id=dc.commune_id AND t.date_debut BETWEEN dc.date_debut AND dc.date_fin 
                                    SET 
                                        t.depot_id=dc.depot_id
                                    WHERE 
                                        t.depot_id IS NULL
                                        AND dc.commune_id IS NOT NULL
                                         ")
            ;
            // Depot en fonction de la date de creation
            $this->_em->getConnection()
                    ->executeQuery("UPDATE
                                        crm_detail_tmp AS t
                                        LEFT JOIN depot_commune dc ON t.commune_id=dc.commune_id AND t.date_creat BETWEEN dc.date_debut AND dc.date_fin 
                                    SET 
                                        t.depot_id=dc.depot_id
                                    WHERE 
                                        t.depot_id IS NULL
                                        AND dc.commune_id IS NOT NULL
                                         ")
            ;
            // Depot en fonction de la date du jour
            $this->_em->getConnection()
                    ->executeQuery("UPDATE
                                        crm_detail_tmp AS t
                                        LEFT JOIN depot_commune dc ON t.commune_id=dc.commune_id AND CURRENT_DATE() BETWEEN dc.date_debut AND dc.date_fin 
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
     * Selection "societe id"
     * @return type
     */
    public function getSocieteId()
    {
        try {
            $select = "SELECT DISTINCT societe_id AS SOCIETE_ID FROM crm_detail_tmp ";
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
            $select = "SELECT COUNT(*) AS NB FROM crm_detail_tmp WHERE societe_id IS NULL";
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

      /**
     * mise a jour de la date imputation paie 
     * 
     */
    public function updateDateImputation()
    {
        try {
            // date_imputation_paie 
            // Par defaut, c'est la "date_debut"
            // Si non renseigne, c'est "date_creat"
            // Si a la date_imputation_paie ainsi obtenue, l'abonne n'est pas livre, on la remplace par la derniere date de sa livraison avant cette date_imputation_paie (voir la methode $this->updateAutresChamps())
            // Encore, si a la date_imputation_paie ainsi obtenue, l'abonne n'est pas livre (absent du client_a_servir_logist), on la remplace par la premiere date de sa livraison avant cette date_imputation_paie (voir la methode $this->updateAutresChamps())
            $this->_em->getConnection()
                    ->executeQuery("UPDATE  crm_detail_tmp 
                                    SET     date_imputation_paie = date_debut
                                    WHERE 
                                        date_imputation_paie IS NULL AND date_debut IS NOT NULL AND DATE_FORMAT(date_debut,'%Y/%m/%d')<>'0000/00/00'");
            
            $this->_em->getConnection()
                    ->executeQuery("UPDATE  crm_detail_tmp 
                                    SET     date_imputation_paie = date_creat
                                    WHERE 
                                        date_imputation_paie IS NULL ");
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

    /**
     * mise a jour du tournee_jour_id 
     */ 
    public function updateTourneeJourId()
    {
        try {
            $update = " UPDATE
                            crm_detail_tmp tmp
                            INNER JOIN tournee_detail td ON td.num_abonne_id = tmp.abonne_soc_id  
                            LEFT JOIN modele_tournee_jour mtj ON td.modele_tournee_jour_code = mtj.code AND mtj.jour_id=CAST(DATE_FORMAT(tmp.date_imputation_paie, '%w') AS SIGNED)+1
                        SET
                            tmp.tournee_jour_id = mtj.id
                        WHERE
                            tmp.date_imputation_paie IS NOT NULL
                            AND mtj.id IS NOT NULL ";
            $update = " UPDATE
                            crm_detail_tmp tmp
                            INNER JOIN tournee_detail td ON td.num_abonne_id = tmp.abonne_soc_id  
                            LEFT JOIN pai_tournee pt ON td.modele_tournee_jour_code = pt.code AND tmp.date_imputation_paie = pt.date_distrib 
                        SET
                            tmp.pai_tournee_id = pt.id
                        WHERE
                            tmp.date_imputation_paie IS NOT NULL
                            AND pt.id IS NOT NULL ";
            $this->_em->getConnection()->executeQuery($update);
            //$this->_em->getConnection()
            //        ->executeQuery("UPDATE
            //                                crm_detail_tmp  tmp
            //                        /*LEFT JOIN modele_tournee mt ON t.neo_tournee = mt.libelle*/
            //                        LEFT JOIN tournee_detail td ON td.num_abonne_id = tmp.abonne_soc_id 
            //                        LEFT JOIN modele_tournee_jour mtj ON td.modele_tournee_jour_code = mtj.code 
            //                        AND mtj.jour_id =CAST(DATE_FORMAT(tmp.date_imputation_paie, '%w') AS SIGNED)+1
            //                        SET tmp.tournee_jour_id = mtj.id
            //                        WHERE tmp.date_imputation_paie is not null");
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    private function sqlInsertIntoTmpTableCrmReclam($sMinMax, $sSQLComp, $sAscDesc)
    { 
        $sSQL  = " 
                INSERT INTO autres_infos_tmp 
                    (tmp_id, date_ref, abonne_soc_id, commune_id, depot_id, adresse_id, rnvp_id, pai_tournee_id, tournee_jour_id)
                SELECT
                    tmp.tmp_id, ".$sMinMax."(tmp.date_distrib) AS date_ref, tmp.abonne_soc_id, tmp.commune_id, tmp.depot_id, tmp.adresse_id, tmp.rnvp_id, tmp.pai_tournee_id, tmp.tournee_jour_id
                FROM
                (
                    SELECT
                        csl.date_distrib, t.id AS tmp_id, csl.abonne_soc_id, csl.commune_id, csl.depot_id, csl.adresse_id, csl.rnvp_id, csl.pai_tournee_id, csl.tournee_jour_id
                    FROM
                        crm_detail_tmp t
                        INNER JOIN client_a_servir_logist csl ON t.abonne_soc_id = csl.abonne_soc_id AND t.date_imputation_paie ".$sSQLComp." csl.date_distrib 
                                                                    AND csl.commune_id IS NOT NULL AND csl.depot_id IS NOT NULL AND csl.adresse_id IS NOT NULL 
                                                                    AND csl.rnvp_id IS NOT NULL
                    ORDER BY
                        csl.date_distrib ".$sAscDesc."
                ) tmp
                GROUP BY
                    tmp.tmp_id, tmp.abonne_soc_id; ";
        return $sSQL;
    }


    /**
     * mise a jour du tournee_jour_id 
     */ 
    public function updateAutresChamps()
    {
        try {
            $sSQLDropTmpTable = " DROP TEMPORARY TABLE IF EXISTS autres_infos_tmp; ";
            $sSQLCreateTmpTable = " 
                        CREATE TEMPORARY TABLE autres_infos_tmp (
                                tmp_id int(11) NULL,
                                date_ref date NOT NULL,
                                abonne_soc_id int(11) NULL,
                                commune_id int(11) NULL,
                                depot_id int(11) NULL,
                                adresse_id int(11) NULL,
                                rnvp_id int(11) NULL,
                                pai_tournee_id int(11) NULL,
                                tournee_jour_id int(11) NULL,
                                
                                KEY IDX_TMP_ID (tmp_id)
                            ); ";
            
            /*if (!function_exists('sqlInsertIntoTmpTableCrmReclam')) {
                function sqlInsertIntoTmpTableCrmReclam($sMinMax, $sSQLComp, $sAscDesc)
                { 
                    $sSQL  = " 
                            INSERT INTO autres_infos_tmp 
                                (tmp_id, date_ref, abonne_soc_id, commune_id, depot_id, adresse_id, rnvp_id, pai_tournee_id, tournee_jour_id)
                            SELECT
                                tmp.tmp_id, ".$sMinMax."(tmp.date_distrib) AS date_ref, tmp.abonne_soc_id, tmp.commune_id, tmp.depot_id, tmp.adresse_id, tmp.rnvp_id, tmp.pai_tournee_id, tmp.tournee_jour_id
                            FROM
                            (
                                SELECT
                                    csl.date_distrib, t.id AS tmp_id, csl.abonne_soc_id, csl.commune_id, csl.depot_id, csl.adresse_id, csl.rnvp_id, csl.pai_tournee_id, csl.tournee_jour_id
                                FROM
                                    crm_detail_tmp t
                                    INNER JOIN client_a_servir_logist csl ON t.abonne_soc_id = csl.abonne_soc_id AND t.date_imputation_paie ".$sSQLComp." csl.date_distrib 
                                                                                AND csl.commune_id IS NOT NULL AND csl.depot_id IS NOT NULL AND csl.adresse_id IS NOT NULL 
                                                                                AND csl.rnvp_id IS NOT NULL
                                ORDER BY
                                    csl.date_distrib ".$sAscDesc."
                            ) tmp
                            GROUP BY
                                tmp.tmp_id, tmp.abonne_soc_id; ";
                    return $sSQL;
                }
            }
             
             */
            
            $sSQLUpdateAutresChamps = " UPDATE crm_detail_tmp t 
                                        INNER JOIN autres_infos_tmp tmp ON t.id = tmp.tmp_id    
                                        SET
                                            t.commune_id = tmp.commune_id
                                            , t.depot_id = tmp.depot_id
                                            , t.adresse_id = tmp.adresse_id
                                            , t.rnvp_id = tmp.rnvp_id
                                            , t.pai_tournee_id = tmp.pai_tournee_id
                                            , t.modele_tournee_jour_id = tmp.tournee_jour_id
                                            , t.date_imputation_paie = tmp.date_ref
                                        WHERE 
                                            t.commune_id IS NULL; ";
            $sSQLUpdatePaieTournee = " UPDATE
                                            crm_detail_tmp t
                                            LEFT JOIN pai_tournee pt ON t.modele_tournee_jour_id = pt.modele_tournee_jour_id AND t.date_imputation_paie = pt.date_distrib
                                        SET
                                            t.pai_tournee_id = pt.id
                                        WHERE
                                            t.pai_tournee_id IS NULL AND t.modele_tournee_jour_id IS NOT NULL AND t.date_imputation_paie IS NOT NULL
                                            AND pt.id IS NOT NULL ; ";
            
            
            
            // Mettre dans la table temporaire les derniers infos livraisons de l'abonne avant la date_imputation_paie
            // Afin de ne pas avoir de contradiction entre la date_imputation_paie et pai_tournee, si l'abonne n'etait pas livre, on modifie la date_imputation_paie 
            $sMinMax  = "MAX";
            $sSQLComp = " >= ";
            $sAscDesc = " DESC ";
            $this->_em->getConnection()->executeQuery($sSQLDropTmpTable);
            $this->_em->getConnection()->executeQuery($sSQLCreateTmpTable);
            $this->_em->getConnection()->executeQuery($this->sqlInsertIntoTmpTableCrmReclam($sMinMax, $sSQLComp, $sAscDesc));
            $this->_em->getConnection()->executeQuery($sSQLUpdateAutresChamps);
            $this->_em->getConnection()->executeQuery($sSQLUpdatePaieTournee);
            
            
            // Mettre dans la table temporaire les PREMIERS infos livraisons de l'abonne avant la date_imputation_paie au cas ou il n'etait pas livre avant
            // Afin de ne pas avoir de contradiction entre la date_imputation_paie et pai_tournee, si l'abonne n'etait pas livre, on modifie la date_imputation_paie 
            $sMinMax  = "MIN";
            $sSQLComp = " <= ";
            $sAscDesc = " ASC ";
            $this->_em->getConnection()->executeQuery($sSQLDropTmpTable);
            $this->_em->getConnection()->executeQuery($sSQLCreateTmpTable);
            $this->_em->getConnection()->executeQuery($this->sqlInsertIntoTmpTableCrmReclam($sMinMax, $sSQLComp, $sAscDesc));
            $this->_em->getConnection()->executeQuery($sSQLUpdateAutresChamps);
            $this->_em->getConnection()->executeQuery($sSQLUpdatePaieTournee);
            
                        
            /*
             * Les requetes mises en commentaires suivantes ont ete ramplacees par les etapes ci-dessus
            $update = " UPDATE	
                            crm_detail_tmp t
                            LEFT JOIN client_a_servir_logist csl ON t.abonne_soc_id = csl.abonne_soc_id AND t.date_imputation_paie = csl.date_distrib
                        SET
                            t.commune_id = csl.commune_id
                            , t.depot_id = csl.depot_id
                            , t.adresse_id = csl.adresse_id
                            , t.rnvp_id = csl.rnvp_id
                            , t.pai_tournee_id = csl.pai_tournee_id
                            , t.modele_tournee_jour_id = csl.tournee_jour_id
                        WHERE
                            csl.id IS NOT NULL ";
            $this->_em->getConnection()->executeQuery($update);
            $this->_em->clear();
            
            // Mise a jour champs adresse si abonnes non livres le jour de la date_imputation_paie
            $update = " UPDATE
                            crm_detail_tmp t
                            LEFT JOIN adresse a ON t.abonne_soc_id = a.abonne_soc_id AND t.date_imputation_paie BETWEEN a.date_debut AND a.date_fin AND (a.type_adresse IS NULL OR a.type_adresse<>'R')
                        SET
                            t.commune_id = a.commune_id
                            , t.adresse_id = a.id
                            , t.rnvp_id = a.rnvp_id
                        WHERE
                            1=1
                            AND (t.commune_id IS NULL OR t.adresse_id IS NULL) ";
            $this->_em->getConnection()->executeQuery($update);
            $this->_em->clear();
            
            // Mise a jour champs adresse si abonnes non livres que maintenant
            $update = " UPDATE
                            crm_detail_tmp t
                            LEFT JOIN adresse a ON t.abonne_soc_id = a.abonne_soc_id AND curdate() BETWEEN a.date_debut AND a.date_fin AND (a.type_adresse IS NULL OR a.type_adresse<>'R')
                        SET
                            t.commune_id = a.commune_id
                            , t.adresse_id = a.id
                            , t.rnvp_id = a.rnvp_id
                        WHERE
                            1=1
                            AND (t.commune_id IS NULL OR t.adresse_id IS NULL) ";
            $this->_em->getConnection()->executeQuery($update);
            $this->_em->clear();
            
            // Mise a jour du champs depot
            $update = " UPDATE
                            crm_detail_tmp t
                            LEFT JOIN depot_commune dc ON t.commune_id = dc.commune_id AND t.date_imputation_paie BETWEEN dc.date_debut AND dc.date_fin 
                        SET
                            t.depot_id = dc.depot_id
                        WHERE
                            1=1
                            AND t.depot_id IS NULL AND t.commune_id IS NOT NULL ";
            $this->_em->getConnection()->executeQuery($update);
            $this->_em->clear();
            
            // Mise a jour du champs modele_tournee_jour_id en fonction de ce que l'on trouve dants tournee_detail
            $update = " UPDATE
                            crm_detail_tmp t
                            LEFT JOIN tournee_detail td ON t.abonne_soc_id = td.num_abonne_id AND td.jour_id=CAST(DATE_FORMAT(t.date_imputation_paie, '%w') AS SIGNED)+1
                            LEFT JOIN modele_tournee_jour mtj ON td.modele_tournee_jour_code = mtj.code AND t.date_imputation_paie BETWEEN mtj.date_debut AND mtj.date_fin 
                        SET
                            t.modele_tournee_jour_id = mtj.id
                        WHERE
                            1=1
                            AND t.depot_id IS NOT NULL
                            AND t.commune_id IS NOT NULL
                            AND t.depot_id IS NOT NULL
                            AND t.adresse_id IS NOT NULL
                            AND t.rnvp_id IS NOT NULL
                            AND t.modele_tournee_jour_id IS NULL ";
            $this->_em->getConnection()->executeQuery($update);
            $this->_em->clear();
            
            // Mise a jour du champs pai_tournee_id en fonction des champs modele_tournee_jour_id et date_imputation_paie sur abonne 
            $update = " UPDATE
                            crm_detail_tmp t
                            LEFT JOIN pai_tournee pt ON t.modele_tournee_jour_id = pt.modele_tournee_jour_id AND t.date_imputation_paie = pt.date_distrib
                        SET
                            t.pai_tournee_id = pt.id
                        WHERE
                            t.pai_tournee_id IS NULL AND t.modele_tournee_jour_id IS NOT NULL AND t.date_imputation_paie IS NOT NULL ";
            $this->_em->getConnection()->executeQuery($update);
            $this->_em->clear();
            */
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
}
