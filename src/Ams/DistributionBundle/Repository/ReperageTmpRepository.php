<?php

namespace Ams\DistributionBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\DBALException;
use Ams\DistributionBundle\Exception\ReperageSQLException;


class ReperageTmpRepository extends EntityRepository {
    

    public function getDatesNblignesNbex()
    {
        try {
            $qb = $this->createQueryBuilder('t');
            $qb->select('t.dateDemar', $qb->expr()->count('t').' AS nbLignes', 'SUM(t.qte) AS nbEx')
               ->groupBy('t.dateDemar');

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
            $iCheckSum  = 0;
            $sCheckSum  = "CHECKSUM TABLE reperage_tmp";
            $aCheksum   = $this->_em->getConnection()->executeQuery($sCheckSum)->fetchAll();
            foreach($aCheksum as $aArr)
            {
                    $iCheckSum  = $aArr['Checksum'];
            }
            return $iCheckSum;
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
                    ->executeQuery("UPDATE reperage_tmp SET client_type = ".$clientType." ");
            $this->_em->clear();
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
     * Les dates ulterieures deja inserees pour le code societe exterieure courant
     * 
     * @param string $socCodeExt
     * @param \DateTime $dateDistribCourant
     * @return type
     * @throws \Doctrine\DBAL\DBALException
     */
    public function datesUlterieures($socCodeExt, \DateTime $dateDistribCourant) {
        try {
            $qb = $this->createQueryBuilder('c');
            $qb->select('DISTINCT c.dateDistrib AS date_distrib')
                    ->where('c.dateDistrib >= :dateDistrib')
                    ->andWhere('c.socCodeExt = :socCodeExt')
                    ->setParameters(array(':dateDistrib' => $dateDistribCourant, ':socCodeExt' => $socCodeExt));

            return $qb->getQuery()->getResult();
        } catch (DBALException $ex) {
            throw $ex;
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
     * Suppression des "\r" et "\n" pour tous les champs
     * @throws \Doctrine\DBAL\DBALException
     */
    public function supprCaracteresSpeciaux()
    {
        try {
            $update = " UPDATE reperage_tmp
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
     * Mise a jour des attributs sousProduit & produit & societe si le sous produit est parametre
     */
    public function updateInfosProduit()
    {
        try {
            $this->_em->getConnection()
                    ->executeQuery("UPDATE reperage_tmp AS t, produit AS p, societe s 
                                    SET t.produit_id=p.id, t.societe_id=s.id 
                                    WHERE t.soc_code_ext = p.soc_code_ext 
                                    AND t.prd_code_ext = p.prd_code_ext 
                                    AND t.spr_code_ext = p.spr_code_ext 
                                    AND p.societe_id=s.id");
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
                    ->executeQuery("UPDATE reperage_tmp AS t
                                        LEFT JOIN (SELECT MIN(id) AS id, soc_code_ext, MIN(societe_id) AS societe_id
                                        FROM produit GROUP BY soc_code_ext) sp ON t.soc_code_ext=sp.soc_code_ext 
                                        LEFT JOIN societe s ON s.id=sp.societe_id
                                        SET t.produit_id=s.produit_defaut_id, t.societe_id=sp.societe_id
                                        WHERE t.produit_id IS NULL
                                        AND s.produit_defaut_id IS NOT NULL")
                    ;
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

       /**
     * Mise a jour de l'attribut AbonneSoc
     */
    public function updateAbonneSoc($clientType=0) {
        try {
            // Si abonne
            if($clientType==0) {
                $this->_em->getConnection()
                        ->executeQuery("UPDATE reperage_tmp AS t
                                        LEFT JOIN  abonne_soc AS a ON t.soc_code_ext=a.soc_code_ext 
                                        AND t.numabo_ext = a.numabo_ext 
                                        AND a.client_type = ".$clientType." 
                                        SET t.abonne_soc_id=a.id
                                        WHERE t.abonne_soc_id IS NULL AND a.id IS NOT NULL ");
                $this->_em->clear();
            }
            // Si lieu de vente
            else {
                $this->_em->getConnection()
                        ->executeQuery("UPDATE reperage_tmp AS t
                                        LEFT JOIN abonne_soc AS a ON t.numabo_ext=a.numabo_ext 
                                        AND a.client_type = ".$clientType." 
                                        SET t.abonne_soc_id=a.id
                                        WHERE t.abonne_soc_id IS NULL AND a.id IS NOT NULL ")
                ;
                $this->_em->clear();
            }
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }


        /**
     * Insertion des nouveaux AbonneSoc
     */
    public function insertNouveauAbonneSoc($clientType=0) {
        try {
            $this->_em->getConnection()
                    ->executeQuery("INSERT INTO abonne_soc (numabo_ext, soc_code_ext, client_type, vol1, vol2, societe_id) SELECT DISTINCT numabo_ext, ".(($clientType==0)?"soc_code_ext":"''").", ".$clientType.", vol1, vol2, societe_id FROM reperage_tmp WHERE abonne_soc_id IS NULL");
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

        /**
     * Mise a jour des adresses (adresse & rnvp & commune) connues
     * @throws \Doctrine\DBAL\DBALException
     */
    public function updateAdresse() {
        try {
            $this->_em->getConnection()
                    ->executeQuery("UPDATE reperage_tmp AS t
                                    LEFT JOIN adresse AS a ON t.abonne_soc_id=a.abonne_soc_id 
                                    AND t.vol1=a.vol1 
                                    AND t.vol2=a.vol2 
                                    AND t.vol3=a.vol3 
                                    AND t.vol4=a.vol4
                                    AND t.vol5=a.vol5
                                    AND t.cp=a.cp
                                    AND t.ville=a.ville
                                    AND t.date_demar BETWEEN a.date_debut AND a.date_fin AND a.type_adresse = 'R'
                                    SET t.adresse_id=a.id
                                        , t.rnvp_id=a.rnvp_id
                                        , t.point_livraison_id=a.point_livraison_id
                                        , t.commune_id=a.commune_id
                                    WHERE t.adresse_id IS NULL AND a.id IS NOT NULL ")
            ;
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

       /**
     * Insertion des nouvelles Adresse
     */
    public function insertNouvelleAdresse($date_fin='2078-12-31') {
        try {
            $dateHeureCourant    = new \Datetime();
            $dateCourant    = new \Datetime();
            $dateCourant_1 = $dateCourant->sub(new \DateInterval('P1D')); // Date courant - 1
            
            // Fermeture des adresses de reperages qui ne sont plus d'actualite
            $sUpdate    = " UPDATE
                                reperage_tmp t
                                INNER JOIN adresse AS a ON t.abonne_soc_id=a.abonne_soc_id AND a.type_adresse = 'R' AND t.date_demar BETWEEN a.date_debut AND a.date_fin
                            SET
                                a.date_fin = '".$dateCourant_1->format('Y-m-d')."'
                            WHERE
                                t.adresse_id IS NULL
                                     ";
            $this->_em->getConnection()->executeQuery($sUpdate);
            $this->_em->clear();
            
            // Creation de nouvelles lignes 
            $repoTypeChangement = $this->_em->getRepository('AmsAdresseBundle:TypeChangement');
            $aTypeChangement   = $repoTypeChangement->findByCode('REPERAGE');
            $this->_em->getConnection()
                    ->executeQuery("INSERT INTO adresse (abonne_soc_id, vol1, vol2, vol3, vol4, vol5, cp, ville, type_changement_id, date_debut, date_fin, date_modif, type_adresse) SELECT DISTINCT abonne_soc_id, vol1, vol2, vol3, vol4, vol5, cp, ville, ".$aTypeChangement[0]->getId().", date_demar, '".$date_fin."', '".$dateHeureCourant->format('Y-m-d H:i:s')."', 'R' FROM reperage_tmp WHERE adresse_id IS NULL ");
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

        /**
     * Mise a jour de l'attribut commune & depot
     */
    public function updateCommuneDepot() {
        try {
            // Prise en compte des nouvelles tables de references depot/commune (repar_glob [produit nuit en general], repar_soc [repartition par societe -- exception])
            // On ne prend pas en compte les "exceptions produits" pour les reperages
            
            // Prise en compte des "exceptions societe"
            $sUpdateBySoc   = " UPDATE reperage_tmp AS t
                                    LEFT JOIN adresse_rnvp AS r ON t.rnvp_id=r.id
                                    LEFT JOIN commune c ON r.commune_id = c.id
                                    LEFT JOIN repar_soc dc ON t.societe_id=dc.societe_id AND dc.commune_id=c.id AND t.date_demar BETWEEN dc.date_debut AND dc.date_fin 
                                SET 
                                    t.commune_id=c.id
                                    , t.depot_id=dc.depot_id
                                WHERE 
                                    t.adresse_id IS NOT NULL
                                    AND c.id IS NOT NULL
                                    AND t.depot_id IS NULL
                                    AND dc.depot_id IS NOT NULL
                                " ;
            $this->_em->getConnection()->executeQuery($sUpdateBySoc);
            $this->_em->clear();
            
            // Prise en compte de la repartition globale. S il n y a pas de repartition specifique societe (repar_soc), on classe par defaut dans flux nuit
            $sUpdateByGlob   = " UPDATE reperage_tmp AS t
                                    LEFT JOIN adresse_rnvp AS r ON t.rnvp_id=r.id
                                    LEFT JOIN commune c ON r.commune_id = c.id
                                    LEFT JOIN repar_glob dc ON dc.commune_id=c.id AND t.date_demar BETWEEN dc.date_debut AND dc.date_fin 
                                    LEFT JOIN societe s ON t.societe_id = s.id AND s.flux_id_defaut = dc.flux_id /* s il n y a pas de repart specifique societe (repar_soc), on classe par defaut selon le flux de la societe par defaut */
                                SET 
                                    t.commune_id=c.id
                                    , t.depot_id=dc.depot_id
                                WHERE 
                                    t.adresse_id IS NOT NULL
                                    AND c.id IS NOT NULL
                                    AND t.depot_id IS NULL
                                    AND dc.depot_id IS NOT NULL
                                    AND s.id IS NOT NULL 
                                " ;
            $this->_em->getConnection()->executeQuery($sUpdateByGlob);
            $this->_em->clear();
            
            /*
             * Ancienne requete : prise en compte de la table "depot_commune". Meme depot/commune pour tous les differents flux
            $this->_em->getConnection()
                    ->executeQuery("UPDATE reperage_tmp AS t
                                        LEFT JOIN adresse_rnvp AS r ON t.rnvp_id=r.id
                                        LEFT JOIN commune c ON r.insee=c.insee
                                        LEFT JOIN depot_commune dc ON dc.commune_id=c.id AND t.date_demar BETWEEN dc.date_debut AND dc.date_fin 
                                    SET 
                                        t.commune_id=c.id
                                        , t.depot_id=dc.depot_id
                                    WHERE 
                                        t.adresse_id IS NOT NULL
                                        AND c.id IS NOT NULL
                                         ")
            ;
            $this->_em->clear();
        */
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    public function updateProduitSelonRepartition() {
        try {
            // Prise en compte des nouvelles tables de references depot/commune (repar_glob, repar_soc [repartition par societe -- exception])
            // On ne prend pas en compte les "exceptions produits" pour les reperages
            
            // Prise en compte des "exceptions societe"
            $sUpdateBySoc   = " UPDATE
                                    reperage_tmp AS t
                                    INNER JOIN adresse_rnvp AS r ON t.rnvp_id=r.id
                                    INNER JOIN commune c ON r.commune_id = c.id
                                    INNER JOIN repar_soc dc ON t.societe_id=dc.societe_id AND dc.commune_id=c.id AND t.date_demar BETWEEN dc.date_debut AND dc.date_fin 
                                    INNER JOIN produit_transfo_flux ptf ON t.produit_id = ptf.produit_id_init AND dc.flux_id = ptf.flux_id
                                SET
                                    t.produit_id = ptf.produit_id_transfo
                                    , t.produit_maj = 1
                                WHERE 
                                t.adresse_id IS NOT NULL
                                    AND t.produit_maj = 0
                                " ;
            $this->_em->getConnection()->executeQuery($sUpdateBySoc);
            $this->_em->clear();
            
            // Prise en compte de la repartition globale
            $sUpdateByGlob   = " UPDATE
                                    reperage_tmp t
                                    INNER JOIN adresse_rnvp r ON t.rnvp_id=r.id
                                    INNER JOIN commune c ON r.commune_id = c.id
                                    INNER JOIN repar_glob dc ON dc.commune_id=c.id AND t.date_demar BETWEEN dc.date_debut AND dc.date_fin
                                    INNER JOIN societe s ON t.societe_id = s.id AND s.flux_id_defaut = dc.flux_id /* s il n y a pas de repart specifique societe (repar_soc), on classe par defaut selon le flux de la societe par defaut */
                                    INNER JOIN produit_transfo_flux ptf ON t.produit_id = ptf.produit_id_init AND dc.flux_id = ptf.flux_id
                                SET
                                    t.produit_id = ptf.produit_id_transfo
                                    , t.produit_maj = 1
                                WHERE
                                    t.produit_maj = 0
                                    AND t.adresse_id IS NOT NULL
                                " ;
            $this->_em->getConnection()->executeQuery($sUpdateByGlob);
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

      /**
     * Mise a jour de l'attribut AbonneUnique
     */
    public function updateAbonneUnique() {
        try {
            $this->_em->getConnection()
                    ->executeQuery("UPDATE reperage_tmp AS t
                                    LEFT JOIN  abonne_soc AS a ON t.soc_code_ext=a.soc_code_ext AND t.numabo_ext=a.numabo_ext
                                    SET t.abonne_unique_id=a.abonne_unique_id
                                    WHERE 
                                        t.abonne_unique_id IS NULL
                                        AND a.abonne_unique_id IS NOT NULL ")
            ;
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

    /**
     * Mise a jour de tournees deja connues
     * @throws \Doctrine\DBAL\DBALException
     */
    public function updateTourneesConnues() {
        try {
            $this->_em->getConnection()
                    ->executeQuery(" UPDATE
                                        reperage_tmp t
                                        INNER JOIN adresse_rnvp rnvp_t ON t.rnvp_id = rnvp_t.id
                                        INNER JOIN reperage r ON r.tournee_id IS NOT NULL AND r.date_demar > DATE_SUB(CURDATE(), INTERVAL 60 DAY)
                                                                    /* AND t.abonne_soc_id = r.abonne_soc_id */ 
                                        INNER JOIN adresse_rnvp rnvp_r ON r.rnvp_id = rnvp_r.id
                                        INNER JOIN modele_tournee mt ON r.tournee_id = mt.id AND mt.actif = 1
                                        INNER JOIN depot d ON t.depot_id = d.id AND LEFT(mt.code, 3) = d.code AND r.date_demar > d.date_debut AND ( (d.date_fin IS NOT NULL AND r.date_demar < d.date_fin ) OR d.date_fin IS NULL)
                                    SET
                                        t.tournee_id = r.tournee_id
                                    WHERE
                                        rnvp_t.adresse = rnvp_r.adresse
                                        AND rnvp_t.commune_id = rnvp_r.commune_id
                                        AND t.tournee_id IS NULL ")
            ;
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Les reperages non classes
     * @param integer $iNbMaxATraiter
     * @param array $dates1erService tableau de dates en \Datetime 
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function pointsAClasser($iNbMaxATraiter=100)
    {
        try {
            $sSlct  = " SELECT DISTINCT 
                            r.id, DATE_FORMAT(date_demar, '%Y/%m/%d') AS jour, DATE_FORMAT(date_demar, '%w')+1 AS id_jour, c.insee
                            , r.abonne_soc_id, r.point_livraison_id, ar.geox, ar.geoy, p.flux_id, r.numabo_ext, r.soc_code_ext
                            , p.prd_code_ext, r.depot_id, 1 AS reperage
                        FROM 
                            reperage_tmp r
                            LEFT JOIN adresse_rnvp ar ON r.point_livraison_id = ar.id
                            LEFT JOIN produit p ON r.produit_id = p.id
                            LEFT JOIN commune c ON r.commune_id = c.id
                        WHERE
                            1 = 1
                            ";
            $sSlct  .= " AND r.date_demar > current_date() ";
            
            $sSlct  .= " 
                            AND r.tournee_id IS NULL
                            AND ar.id IS NOT NULL
                            AND p.id IS NOT NULL
                            AND c.id IS NOT NULL
                            AND r.depot_id IS NOT NULL
                            AND ar.geox IS NOT NULL AND ar.geoy IS NOT NULL
                        ORDER BY jour
                        LIMIT 0, $iNbMaxATraiter
                        ";
            //$sSlct  .= " AND c.insee='75115' ";
            //$sSlct  .= " AND r.numabo_ext='65542' ";
            return $this->_em->getConnection()->executeQuery($sSlct)->fetchAll();
        } catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

    /**
     * Suppression tournees incoherentes
     * @throws \Doctrine\DBAL\DBALException
     */
    public function supprTourneesIncoherentes() {
        try {
            $this->_em->getConnection()
                    ->executeQuery(" UPDATE reperage_tmp r 
                                        INNER JOIN repar_soc rs ON r.societe_id = rs.societe_id AND r.commune_id = rs.commune_id AND r.date_demar BETWEEN rs.date_debut AND rs.date_fin
                                        INNER JOIN modele_tournee mt ON r.tournee_id = mt.id
                                        INNER JOIN depot d ON r.depot_id = d.id
                                        INNER JOIN ref_flux rf ON rs.flux_id = rf.id
                                    SET
                                        r.tournee_id = NULL
                                    WHERE
                                        1 = 1
                                        AND r.tournee_id IS NOT NULL
                                        AND rf.code <> SUBSTRING(mt.code, 4, 1) ")
            ;
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

    /**
     * Mise a jour du champ tournee_id
     * @param integer $id
     * @param integer $tourneeId
     * @throws \Doctrine\DBAL\DBALException
     */
    public function updateTournee($id, $tourneeId) {
        try {
            $this->_em->getConnection()
                    ->executeQuery("UPDATE reperage_tmp SET tournee_id = ".$tourneeId." WHERE id = ".$id." ")
            ;
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }


        /**
     * Mise a jour des attributs AbonneSoc, AbonneUnique, Adresse, RNVP, Pt de livraison & Commune de CLIENT_A_SERVIR_SRC_TMP
     */
    public function updateAbonneAdresseRnvp()
    {
        try {
            $this->_em->getConnection()
                    ->executeQuery("UPDATE client_a_servir_src_tmp AS t1
                                    LEFT JOIN client_a_servir_src_tmp2 AS t2 ON t1.numabo_ext=t2.numabo_ext
                                    SET t1.abonne_soc_id = t2.abonne_soc_id,
                                        t1.abonne_unique_id = t2.abonne_unique_id,
                                        t1.adresse_id = t2.adresse_id,
                                        t1.rnvp_id = t2.rnvp_id,
                                        t1.point_livraison_id=t2.point_livraison_id,
                                        t1.commune_id=t2.commune_id,
                                        t1.depot_id=t2.depot_id
                                    ")
                    ;
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

     /**
     * Suppression des donnees de date de demarage $dateDemar et de socCodeExt $socCodeExt.
     * Cette methode est appele quand un fichier d'une societe est a traite de nouveau pour une date donnee
     * 
     * @param \DateTime $dateDistrib
     * @param type $socCodeExt
     * @return type
     */
    public function suppressionAvecDateSoc(\DateTime $dateDemar, $socCodeExt)
    {
        $qb = $this->createQueryBuilder('c');
        $qb->delete()
                ->where('c.dateDemar = :dateDemar')
                ->andWhere('c.socCodeExt = :socCodeExt')
                ->setParameters(array(':dateDemar' => $dateDemar, ':socCodeExt' => $socCodeExt));
        return $qb->getQuery()->getResult();
    }
    

    /**
     * Selection "societe id"
     * @return type
     */
    public function getSocieteId()
    {
        try {
            $select = "SELECT DISTINCT societe_id AS SOCIETE_ID FROM reperage_tmp ";
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
            $select = "SELECT COUNT(*) AS NB FROM reperage_tmp WHERE societe_id IS NULL";
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
            $this->createQueryBuilder('t')->update()->set('t.ficRecap1', $iDernierFicRecap)->getQuery()->execute();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
      /**
     * Mise a jour des donnees de la table temporaire reperage
     */

    public function updateReperage($iDernierFicRecap){
        try {
            $this->_em->getConnection()
                    ->executeQuery("UPDATE reperage AS t1, reperage_tmp AS t2
                                    SET t1.fic_recap_n_id = $iDernierFicRecap,
                                        t2.fic_recap_n_id = $iDernierFicRecap,
                                        t1.date_demar = t2.date_demar
                                    Where t1.date_demar != t2.date_demar
                                    AND t1.numabo_ext=t2.numabo_ext AND t1.adresse_id = t2.adresse_id
                                    AND t2.date_demar is not null ");
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }

    }
    
    
    /**
     * Copie dans la table temporaire des reperages non encore classes
     * 
     * @param \DateTime $oDateMin
     * @param \DateTime $oDateMax
     * @param \Array $aCodesCD
     * @throws \Doctrine\DBAL\DBALException
     */
    public function insertReperagesNonClasses($oDateMin, $oDateMax, $aCodesCD=array())
    {
        try {
            $sTruncate  = " TRUNCATE TABLE reperage_tmp ";
            $this->_em->getConnection()->executeQuery($sTruncate);
            $this->_em->clear();
            
            $sInsert    = " INSERT INTO reperage_tmp (
                                commune_id, depot_id, tournee_id, abonne_soc_id, adresse_id, rnvp_id, point_livraison_id
                                , societe_id, produit_id, date_demar, numabo_ext, vol1, vol2, vol3, vol4, vol5
                                , cp, ville, soc_code_ext, prd_code_ext, spr_code_ext, id_reperage, produit_maj
                            )
                            SELECT
                                r.commune_id, r.depot_id, r.tournee_id, r.abonne_soc_id, r.adresse_id, r.rnvp_id, r.point_livraison_id
                                , r.societe_id, r.produit_id, r.date_demar, r.numabo_ext, r.vol1, r.vol2, r.vol3, r.vol4, r.vol5
                                , r.cp, r.ville, r.soc_code_ext, r.prd_code_ext, r.spr_code_ext, r.id AS id_reperage, 0 AS produit_maj
                            FROM
                                reperage r
                                INNER JOIN adresse_rnvp ar ON r.point_livraison_id = ar.id
                                INNER JOIN produit p ON r.produit_id = p.id 
                                                        AND r.date_demar > p.date_debut AND (p.date_fin IS NULL OR r.date_demar < p.date_fin) 
							AND now() > p.date_debut AND (p.date_fin IS NULL OR now() < p.date_fin)
                                INNER JOIN societe s ON r.societe_id = s.id 
                                                        AND r.date_demar > s.date_debut AND (s.date_fin IS NULL OR r.date_demar < s.date_fin)
							AND now() > s.date_debut AND (s.date_fin IS NULL OR now() < s.date_fin)
                                INNER JOIN commune c ON r.commune_id = c.id
                                INNER JOIN depot d ON r.depot_id = d.id
                            WHERE
                                r.tournee_id IS NULL
                                AND ar.geox > 0 AND ar.geoy > 0 
                                AND r.date_demar BETWEEN '".$oDateMin->format('Y-m-d')."' AND '".$oDateMax->format('Y-m-d')."'
                               ";
            if(!empty($aCodesCD))
            {
                $sInsert    .= " AND d.code IN ('".implode("', '", $aCodesCD)."')";
            }
            //$sInsert    .= " LIMIT 0, 10 ";
            $this->_em->getConnection()->executeQuery($sInsert);
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    
    /**
     * MAJ des tournees de la table 'reperage'
     * 
     * @throws \Doctrine\DBAL\DBALException
     */
    public function majTourneeReperage()
    {
        try {            
            $sUpdate    = " UPDATE	
                                reperage_tmp t
                                INNER JOIN reperage r ON t.id_reperage = r.id
                            SET
                                r.tournee_id = t.tournee_id
                            WHERE
                                t.tournee_id IS NOT NULL
                                AND r.tournee_id IS NULL ";
            $this->_em->getConnection()->executeQuery($sUpdate);
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
}
