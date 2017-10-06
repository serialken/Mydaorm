<?php 

namespace Ams\DistributionBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\DBALException;

class ClientAServirSrcTmp1Repository extends EntityRepository
{
    
    public function getDatesNblignesNbex()
    {
        try {
            $qb = $this->createQueryBuilder('t');
            $qb->select('t.dateDistrib', 't.dateParution', $qb->expr()->count('t').' AS nbLignes', 'SUM(t.qte) AS nbEx')
               ->groupBy('t.dateDistrib', 't.dateParution');
            
            return $qb->getQuery()->getResult();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Checksum du contenu de la table 
     * @return integer
     */
    public function getChecksum()
    {
        try {
            $iCheckSum	= 0;
            $sCheckSum	= "CHECKSUM TABLE client_a_servir_src_tmp1";
            $aCheksum   = $this->_em->getConnection()->executeQuery($sCheckSum)->fetchAll();
            foreach($aCheksum as $aArr)
            {
                    $iCheckSum	= $aArr['Checksum'];
            }
            return $iCheckSum;
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
     * Selection "societe id"
     * @return type
     */
    public function getSocieteId()
    {
        try {
            $select = "SELECT DISTINCT societe_id AS SOCIETE_ID FROM client_a_servir_src_tmp1 ";
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
            $select = "SELECT COUNT(*) AS NB FROM client_a_servir_src_tmp1 WHERE societe_id IS NULL";
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
     * Insertion des infos adresse & portage dans la deuxieme table temporaire (CLIENT_A_SERVIR_SRC_TMP2)
     */
    public function infosAdressePortageTmp()
    {
        try {
            $sInsert = "INSERT INTO client_a_servir_src_tmp2 "
                    . "     (date_distrib, numabo_ext, soc_code_ext, "
                            . "vol1, vol2, vol3, vol4, vol5, "
                            . "cp, ville, divers1, info_comp1, "
                            . "info_comp2, divers2) "
                    . " SELECT "
                            . " min(date_distrib) AS date_distrib, numabo_ext, soc_code_ext, "
                            . "MIN(vol1) AS vol1, MIN(vol2) AS vol2, MIN(vol3) AS vol3, MIN(vol4) AS vol4, MIN(vol5) AS vol5, "
                            . "MIN(cp) AS cp, MIN(ville) AS ville, MIN(divers1) AS divers1, MIN(info_comp1) AS info_comp1, "
                            . "MIN(info_comp2) AS info_comp2, MIN(divers2) AS divers2 "
                    . "     FROM client_a_servir_src_tmp1"
                    . " GROUP BY numabo_ext, soc_code_ext ";
            $this->_em->getConnection()->prepare($sInsert)->execute();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Mise a jour des attributs sousProduit & produit & societe si le sous produit est parametre
     */
    public function updateInfosProduit()
    {
        try {
            $this->_em->getConnection()
                    ->executeQuery("UPDATE client_a_servir_src_tmp1 AS t, produit AS p, societe s SET t.produit_id=p.id, t.societe_id=s.id WHERE t.soc_code_ext = p.soc_code_ext AND t.prd_code_ext = p.prd_code_ext AND t.spr_code_ext = p.spr_code_ext AND p.societe_id=s.id")
                    ;
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Mise a jour Infos Produit si le produit n'est pas connu. On recherche le produit par defaut en fonction de societeExt
     */
    public function updateInfosProduitBySocieteExt()
    {
        try {
            $this->_em->getConnection()
                    ->executeQuery("UPDATE
                                            client_a_servir_src_tmp1 AS t
                                            LEFT JOIN (SELECT MIN(id) AS id, soc_code_ext, MIN(societe_id) AS societe_id FROM produit GROUP BY soc_code_ext) sp ON t.soc_code_ext=sp.soc_code_ext 
                                            LEFT JOIN societe s ON s.id=sp.societe_id
                                    SET t.produit_id=s.produit_defaut_id, t.societe_id=sp.societe_id
                                    WHERE
                                            t.produit_id IS NULL
                                            AND s.produit_defaut_id IS NOT NULL")
                    ;
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    
    
    /**
     * Mise a jour des attributs AbonneSoc, AbonneUnique, Adresse, RNVP, Pt de livraison & Commune de CLIENT_A_SERVIR_SRC_TMP1
     */
    public function updateAbonneAdresseRnvp()
    {
        try {
            $this->_em->getConnection()
                    ->executeQuery("UPDATE
                                            client_a_servir_src_tmp1 AS t1
                                            LEFT JOIN client_a_servir_src_tmp2 AS t2 ON t1.numabo_ext=t2.numabo_ext
                                    SET t1.abonne_soc_id=t2.abonne_soc_id, t1.abonne_unique_id=t2.abonne_unique_id, t1.adresse_id=t2.adresse_id, t1.rnvp_id=t2.rnvp_id, t1.point_livraison_id=t2.point_livraison_id, t1.commune_id=t2.commune_id 
                                    ")
                    ;
            $this->_em->clear();
            
            
            
            // Mise a jour de depot selon la repartition par defaut
            $this->_em->getConnection()
                    ->executeQuery("UPDATE
                                        client_a_servir_src_tmp1 AS t
                                        LEFT JOIN repar_glob rg ON rg.commune_id=t.commune_id AND t.date_distrib BETWEEN rg.date_debut AND rg.date_fin 
                                    SET 
                                        t.depot_id=rg.depot_id
                                        /* , t.flux_id = rg.flux_id */
                                    WHERE 
                                        rg.depot_id IS NOT NULL
                                        AND t.commune_id IS NOT NULL
                                         ")
            ;
            
            // Mise a jour de depot selon la repartition par societe
            $this->_em->getConnection()
                    ->executeQuery("UPDATE
                                        client_a_servir_src_tmp1 AS t
                                        LEFT JOIN repar_soc rs ON rs.commune_id=t.commune_id AND rs.societe_id=t.societe_id AND t.date_distrib BETWEEN rs.date_debut AND rs.date_fin 
                                    SET 
                                        t.depot_id=rs.depot_id
                                        /* , t.flux_id = rs.flux_id */
                                    WHERE 
                                        rs.depot_id IS NOT NULL
                                        AND t.commune_id IS NOT NULL
                                         ")
            ;
            
            // Mise a jour de depot selon la repartition par produit
            $this->_em->getConnection()
                    ->executeQuery("UPDATE
                                        client_a_servir_src_tmp1 AS t
                                        LEFT JOIN repar_prod rp ON rp.commune_id=t.commune_id AND rp.produit_id=t.produit_id AND t.date_distrib BETWEEN rp.date_debut AND rp.date_fin 
                                    SET 
                                        t.depot_id=rp.depot_id
                                        /* , t.flux_id = rp.flux_id */
                                    WHERE 
                                        rp.depot_id IS NOT NULL
                                        AND t.commune_id IS NOT NULL
                                         ")
            ;
            $this->_em->clear();
            /*
            // Mise a jour du champ "flux_id" si ce n'est pas defini
            $this->_em->getConnection()
                    ->executeQuery("UPDATE
                                            client_a_servir_src_tmp1 AS t1
                                            LEFT JOIN produit AS p ON t1.produit_id=p.produit_id
                                    SET t1.flux_id=p.flux_id 
                                    WHERE
                                        t1.flux_id IS NULL
                                    ")
                    ;
            $this->_em->clear();*/
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    
    /**
     * Mise a jour de la colonne societe_id de la table abonne_soc
     * 
     * @throws \Doctrine\DBAL\DBALException
     */
    public function updateSocAbonneSoc()
    {
        try {
            $this->_em->getConnection()
                    ->executeQuery("UPDATE
                                        client_a_servir_src_tmp1 t
                                        LEFT JOIN abonne_soc a ON a.id = t.abonne_soc_id
                                    SET
                                        a.societe_id = t.societe_id
                                    WHERE
                                        t.societe_id IS NOT NULL
                                        AND a.id IS NOT NULL
                                    ")
                    ;
            $this->_em->clear();
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
                                            client_a_servir_src_tmp1 
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
            $update = " UPDATE client_a_servir_src_tmp1 
                        SET 
                            num_parution = REGEX_REPLACE_1('[\r\n]', '', TRIM(num_parution))
                            , numabo_ext = REGEX_REPLACE_1('[\r\n]', '', TRIM(numabo_ext))
                            , vol1 = REGEX_REPLACE_1('[\r\n]', '', TRIM(vol1))
                            , vol2 = REGEX_REPLACE_1('[\r\n]', '', TRIM(vol2))
                            , vol3 = REGEX_REPLACE_1('[\r\n]', '', TRIM(vol3))
                            , vol4 = REGEX_REPLACE_1('[\r\n]', '', TRIM(vol4))
                            , vol5 = REGEX_REPLACE_1('[\r\n]', '', TRIM(vol5))
                            , cp = REGEX_REPLACE_1('[\r\n]', '', TRIM(cp))
                            , ville = REGEX_REPLACE_1('[\r\n]', '', TRIM(ville))
                            , type_portage = REGEX_REPLACE_1('[\r\n]', '', TRIM(type_portage))
                            , qte = REGEX_REPLACE_1('[\r\n]', '', TRIM(qte))
                            , divers1 = REGEX_REPLACE_1('[\r\n]', '', TRIM(divers1))
                            , info_comp1 = REGEX_REPLACE_1('[\r\n]', '', TRIM(info_comp1))
                            , info_comp2 = REGEX_REPLACE_1('[\r\n]', '', TRIM(info_comp2))
                            , divers2 = REGEX_REPLACE_1('[\r\n]', '', TRIM(divers2))
                            , soc_code_ext = REGEX_REPLACE_1('[\r\n]', '', TRIM(soc_code_ext))
                            , prd_code_ext = REGEX_REPLACE_1('[\r\n]', '', TRIM(prd_code_ext))
                            , spr_code_ext = REGEX_REPLACE_1('[\r\n]', '', TRIM(spr_code_ext))
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