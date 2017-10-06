<?php

namespace Ams\DistributionBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\DBALException;

class ClientAServirSrcTmp2Repository extends EntityRepository {
    
    private $typeInfoPortageFichier;
    
    /**
     * Relation "code TypeInfoPortage" et le nom de la colonne de "client_a_servir_src_tmp2"
     * @return array
     */
    private function initTypeInfoPortageFichier()
    {
        if(empty($this->typeInfoPortageFichier))
        {
            $this->typeInfoPortageFichier   = array();
            // La cle de la table suivante = "code TypeInfoPortage". Sa valeur = nom de la colonne de "client_a_servir_src_tmp2"
            $codeTypeInfoPortageFichier = array(
                                                'DIVERS1' => 'divers1'
                                                , 'INFO_COMP1' => 'info_comp1'
                                                , 'INFO_COMP2' => 'info_comp2'
                                                , 'DIVERS2' => 'divers2'
                                            );
            $repoTypeInfoPortage = $this->_em->getRepository('AmsDistributionBundle:TypeInfoPortage');
            foreach($codeTypeInfoPortageFichier as $k => $v)
            {
                $aTypeInfoPortage   = $repoTypeInfoPortage->findByCode($k);
                if(!is_null($aTypeInfoPortage) && isset($aTypeInfoPortage[0]))
                {
                    $this->typeInfoPortageFichier[$aTypeInfoPortage[0]->getId()] = $v;
                }
            }
        }
        return $this->typeInfoPortageFichier;
    }
    
    /**
     * Verifie si des adresses futures existent
     * @param int $iAbonneSocId
     * @param string $sDateRef Date au format Y-m-d
     * @param string $sCASLouReper
     * @return boolean
     */
    public function existeAdressesUlterieures($iAbonneSocId, $sDateRef, $sCASLouReper='CASL')
    {
        $sSlctAdrFutures    = " SELECT COUNT(*) nb FROM adresse a WHERE a.abonne_soc_id = ".$iAbonneSocId." AND date_debut>'".$sDateRef."' ";
        if($sCASLouReper=='REPER')
        {
            $sSlctAdrFutures    .= " AND a.type_adresse = 'R' ";
        }
        else 
        {
            $sSlctAdrFutures    .= " AND (a.type_adresse IS NULL OR a.type_adresse = 'L') ";
        }
        $rSlctAdrFutures  = $this->_em->getConnection()->fetchAll($sSlctAdrFutures);
        foreach($rSlctAdrFutures as $aArr)
        {
            if($aArr['nb']==0)
            {
                return false;
            }
            else 
            { 
                return true; 
            }
        }
        return true;
    }
    
    /**
     * Suppression des adresses futures non utilisees
     * @param integer $iAbonneSocId
     * @param string $sDateRef Date au format Y-m-d
     * @param type $sCASLouReper
     */
    public function supprAdrFuturesNonUtilisees($iAbonneSocId, $sDateRef, $sCASLouReper='CASL')
    {
        $sSlctAdrFutures    = " SELECT a.id FROM adresse a WHERE a.abonne_soc_id = ".$iAbonneSocId." AND date_debut>'".$sDateRef."' ";
        if($sCASLouReper=='REPER')
        {
            $sSlctAdrFutures    .= " AND a.type_adresse = 'R' ";
        }
        else 
        {
            $sSlctAdrFutures    .= " AND (a.type_adresse IS NULL OR a.type_adresse = 'L') ";
        }
        $rSlctAdrFutures  = $this->_em->getConnection()->fetchAll($sSlctAdrFutures);
        foreach($rSlctAdrFutures as $aTab)
        {
            if($this->isAdresseIdUtilisee($aTab['id'])===false)
            {
                $sDeleteAdr = " DELETE FROM adresse WHERE id = ".$aTab['id'];
                $this->_em->getConnection()->executeQuery($sDeleteAdr);
                $this->_em->clear();
            }
        }
    }
    
    /**
     * Verifie si un adresse_id est utilise dans client_a_servir_logist ou reperage
     * @param int $aAdresseId
     * @return boolean
     */
    public function isAdresseIdUtilisee($aAdresseId)
    {
        $iNbCASL    = 0;
        $iNbReper    = 0;
        // Si l'adresse n'est pas utilise, on la suprime
        $sVerifUtilisationAdrCASL = " SELECT COUNT(*) nb FROM client_a_servir_logist WHERE adresse_id = ".$aAdresseId;
        $rVerifUtilisationAdrCASL  = $this->_em->getConnection()->fetchAll($sVerifUtilisationAdrCASL);
        foreach($rVerifUtilisationAdrCASL as $aTab2)
        {
            $iNbCASL    = $aTab2['nb'];
        }
        $sVerifUtilisationAdrReper = " SELECT COUNT(*) nb FROM reperage WHERE adresse_id = ".$aAdresseId;
        $rVerifUtilisationAdrReper  = $this->_em->getConnection()->fetchAll($sVerifUtilisationAdrReper);
        foreach($rVerifUtilisationAdrReper as $aTab2)
        {
            $iNbReper    = $aTab2['nb'];
        }
        if($iNbCASL==0 && $iNbReper==0)
        {
            return false;
        }
        return true;
    }

    /**
     * Insertion de changement d'adresse pour celles ou des changements dans le futur ont ete deja enregistres
     * @param string $date_fin Date au format Y-m-d
     * @throws DBALException
     */
    public function insertChangementAdresse($date_fin='2078-12-31') {
        try {
            $dateCourant    = new \Datetime();
            $repoTypeChangement = $this->_em->getRepository('AmsAdresseBundle:TypeChangement');
            $aTypeChangement   = $repoTypeChangement->findByCode('CHGT_ADR');
            
            $qb = $this->createQueryBuilder('t');
            $qb->select('t')
                ->where('t.adresse IS NULL');            
            $aTmp2   = $qb->getQuery()->getResult();
            foreach($aTmp2 as $oTmp2)
            {
                $iAbonneSocId   = $oTmp2->getAbonneSoc()->getId();
                $sDateDistribYmd    = $oTmp2->getDateDistrib()->format("Y-m-d");
                
                // Supprimer toutes les adresses futures non utilisees
                if($this->existeAdressesUlterieures($iAbonneSocId, $sDateDistribYmd, 'CASL'))
                {
                    $this->supprAdrFuturesNonUtilisees($iAbonneSocId, $sDateDistribYmd, 'CASL');
                }                
                
                // Recuperation de l'adresse stockee de l'abonne courant
                $qb2 = $this->_em->createQueryBuilder()->select('a')
                    ->from('AmsAdresseBundle:Adresse', 'a')
                    ->where('a.abonneSoc = :abonneSoc')
                    ->andWhere(":dateDistrib BETWEEN a.dateDebut AND a.dateFin AND (a.typeAdresse IS NULL OR a.typeAdresse = 'L')")
                    ->setParameters(array(':abonneSoc' => $oTmp2->getAbonneSoc(), ':dateDistrib' => $oTmp2->getDateDistrib()));
                $aAdresseStockee   = $qb2->getQuery()->getResult();

                if(!empty($aAdresseStockee))
                {
                    // Si date_debut et date_fin de l'adresse stockee sont pareilles
                    if(
                        $aAdresseStockee[0]->getDateDebut()->format("Y-m-d")==$aAdresseStockee[0]->getDateFin()->format("Y-m-d")
                        )
                    {                        
                        $this->_em->getConnection()
                                ->executeQuery("UPDATE
                                                    client_a_servir_src_tmp2 t
                                                    LEFT JOIN adresse a ON a.abonne_soc_id=t.abonne_soc_id AND t.date_distrib BETWEEN a.date_debut AND a.date_fin AND (a.type_adresse IS NULL OR a.type_adresse = 'L')
                                                    LEFT JOIN adresse_rnvp AS r ON a.rnvp_id=r.id
                                                SET
                                                    t.vol4_anc = r.adresse
                                                    , t.insee_anc = r.insee
                                                    , t.chgt_adr = 1
                                                WHERE
                                                    t.id = ".$oTmp2->getId()." ")
                        ;
                        $this->_em->clear();
                        
                        $delete = " DELETE FROM adresse WHERE id = ".$aAdresseStockee[0]->getId()." ";
                        $this->_em->getConnection()->executeQuery($delete);
                        $this->_em->clear();
                        
                        $insert = " INSERT INTO adresse 
                                        (abonne_soc_id, vol1, vol2, vol3, vol4, vol5, cp, ville, type_changement_id, date_debut, date_fin, date_modif, type_adresse)
                                    SELECT DISTINCT abonne_soc_id, vol1, vol2, vol3, vol4, vol5, cp, ville, ".$aTypeChangement[0]->getId().", date_distrib, ".( ($this->existeAdressesUlterieures($iAbonneSocId, $sDateDistribYmd, 'CASL')==true) ? "date_distrib" : "'".$date_fin."'" ).", '".$dateCourant->format('Y-m-d H:i:s')."', 'L' 
                                    FROM client_a_servir_src_tmp2 
                                    WHERE id = ".$oTmp2->getId()."   
                                 ";
                        $this->_em->getConnection()->executeQuery($insert);
                        $this->_em->clear();
                        
                    }
                    // Si date_debut de l'adresse stockee est pareille que la date_distrib du fichier courant, on insere une nouvelle ligne d'adresse et on repousse la date_debut de l'adresse stockee a date_distrib+1
                    else if(
                        $aAdresseStockee[0]->getDateDebut()->format("Y-m-d")==$oTmp2->getDateDistrib()->format("Y-m-d")
                        )
                    {
                        $iAdresseIdStocke = $aAdresseStockee[0]->getId();
                        $sDateDistribCourant    = $oTmp2->getDateDistrib()->format("Y-m-d");
                        
                        $this->_em->getConnection()
                                ->executeQuery("UPDATE
                                                    client_a_servir_src_tmp2 t
                                                    LEFT JOIN adresse a ON a.abonne_soc_id=t.abonne_soc_id AND t.date_distrib BETWEEN a.date_debut AND a.date_fin AND (a.type_adresse IS NULL OR a.type_adresse = 'L')
                                                    LEFT JOIN adresse_rnvp AS r ON a.rnvp_id=r.id
                                                SET
                                                    t.vol4_anc = r.adresse
                                                    , t.insee_anc = r.insee
                                                    , t.chgt_adr = 1
                                                WHERE
                                                    t.id = ".$oTmp2->getId()." ")
                        ;
                        $this->_em->clear();
                        
                        $bAdresseUtilisee   = false;
                        
                        if($this->isAdresseIdUtilisee($iAdresseIdStocke)===false)
                        {
                            $sDeleteAdr = " DELETE FROM adresse WHERE id = ".$iAdresseIdStocke;
                            $this->_em->getConnection()->executeQuery($sDeleteAdr);
                            $this->_em->clear();
                            $bAdresseUtilisee   = false;
                        }
                        else
                        {
                            $bAdresseUtilisee   = true;
                            $dateDistribCourant    = clone $oTmp2->getDateDistrib();
                            $dateDistrib1 = $dateDistribCourant->add(new \DateInterval('P1D')); // Date a distrib + 1
                            $this->_em->getConnection()
                                    ->executeQuery("UPDATE
                                                        adresse 
                                                    SET 
                                                        date_debut='".$dateDistrib1->format('Y-m-d H:i:s')."'
                                                        , date_modif='".$dateCourant->format('Y-m-d H:i:s')."'
                                                    WHERE 
                                                        id = ".$aAdresseStockee[0]->getId()." ")
                            ;
                            $this->_em->clear();
                        }
                        
                        $this->_em->getConnection()
                                ->executeQuery("INSERT INTO adresse (abonne_soc_id, vol1, vol2, vol3, vol4, vol5, cp, ville, type_changement_id, date_debut, date_fin, date_modif, type_adresse) "
                                        . " SELECT DISTINCT abonne_soc_id, vol1, vol2, vol3, vol4, vol5, cp, ville, ".$aTypeChangement[0]->getId().", date_distrib, ".( ($bAdresseUtilisee==true || $this->existeAdressesUlterieures($iAbonneSocId, $sDateDistribYmd, 'CASL')==true) ? "date_distrib" : "'".$date_fin."'" ).", '".$dateCourant->format('Y-m-d H:i:s')."', 'L' FROM client_a_servir_src_tmp2 WHERE id = ".$oTmp2->getId()."");
                        
                    }
                    // Si date_fin de l'adresse stockee est pareille que la date_distrib du fichier courant, on insere une nouvelle ligne d'adresse et on repousse la date_fin de l'adresse stockee a date_distrib-1
                    else if(
                        $aAdresseStockee[0]->getDateDebut()->format("Y-m-d")==$oTmp2->getDateDistrib()->format("Y-m-d")
                        )
                    {
                        $this->_em->getConnection()
                                ->executeQuery("UPDATE
                                                    client_a_servir_src_tmp2 t
                                                    LEFT JOIN adresse a ON a.abonne_soc_id=t.abonne_soc_id AND t.date_distrib BETWEEN a.date_debut AND a.date_fin AND (a.type_adresse IS NULL OR a.type_adresse = 'L')
                                                    LEFT JOIN adresse_rnvp AS r ON a.rnvp_id=r.id
                                                SET
                                                    t.vol4_anc = r.adresse
                                                    , t.insee_anc = r.insee
                                                    , t.chgt_adr = 1
                                                WHERE
                                                    t.id = ".$oTmp2->getId()." ")
                        ;
                        $this->_em->clear();
                        
                        $this->_em->getConnection()
                                ->executeQuery("INSERT INTO adresse (abonne_soc_id, vol1, vol2, vol3, vol4, vol5, cp, ville, type_changement_id, date_debut, date_fin, date_modif, type_adresse) "
                                        . " SELECT DISTINCT abonne_soc_id, vol1, vol2, vol3, vol4, vol5, cp, ville, ".$aTypeChangement[0]->getId().", date_distrib, ".( ($this->existeAdressesUlterieures($iAbonneSocId, $sDateDistribYmd, 'CASL')==true) ? "date_distrib" : "'".$date_fin."'" ).", '".$dateCourant->format('Y-m-d H:i:s')."', 'L' FROM client_a_servir_src_tmp2 WHERE id = ".$oTmp2->getId()."");
                        
                        $dateDistribCourant    = clone $oTmp2->getDateDistrib();
                        $dateDistrib_1 = $dateDistribCourant->sub(new \DateInterval('P1D')); // Date a distrib + 1
            
                        $this->_em->getConnection()
                                ->executeQuery("UPDATE
                                                    adresse 
                                                SET 
                                                    date_fin='".$dateDistrib_1->format('Y-m-d H:i:s')."'
                                                    , date_modif='".$dateCourant->format('Y-m-d H:i:s')."'
                                                WHERE 
                                                    id = ".$aAdresseStockee[0]->getId()." ")
                        ;
                        $this->_em->clear();
                    }
                    else {    
                        $this->_em->getConnection()
                                ->executeQuery("UPDATE
                                                    client_a_servir_src_tmp2 t
                                                    LEFT JOIN adresse a ON a.abonne_soc_id=t.abonne_soc_id AND t.date_distrib BETWEEN a.date_debut AND a.date_fin AND (a.type_adresse IS NULL OR a.type_adresse = 'L')
                                                    LEFT JOIN adresse_rnvp AS r ON a.rnvp_id=r.id
                                                SET
                                                    t.vol4_anc = r.adresse
                                                    , t.insee_anc = r.insee
                                                    , t.chgt_adr = 1
                                                WHERE
                                                    t.id = ".$oTmp2->getId()." ")
                        ;
                        $this->_em->clear();
                        
                        
                        $dateDistribCourant    = clone $oTmp2->getDateDistrib();
                        $dateDistribCourantTmp1    = clone $oTmp2->getDateDistrib();
                        $dateDistribCourantTmp2    = clone $oTmp2->getDateDistrib();
                        $dateDistrib_1 = $dateDistribCourantTmp1->sub(new \DateInterval('P1D')); // Date a distrib - 1
                        $dateDistrib1 = $dateDistribCourantTmp2->add(new \DateInterval('P1D')); // Date a distrib + 1
                        
                        
                        $iAdresseStockeeId = $aAdresseStockee[0]->getId();                        
                        
                        
                        // Insertion d'une nouvelle ligne de meme contenu que celle deja inseree
                        $this->_em->getConnection()
                                ->executeQuery("INSERT INTO adresse 
                                                    (abonne_soc_id, vol1, vol2, vol3, vol4, vol5, cp, ville, rnvp_id, point_livraison_id, commune_id, adresse_rnvp_etat_id, type_changement_id, date_debut, date_fin, date_modif, type_adresse)
                                                SELECT 
                                                    abonne_soc_id, vol1, vol2, vol3, vol4, vol5, cp, ville, rnvp_id, point_livraison_id, commune_id, adresse_rnvp_etat_id, type_changement_id, '".$dateDistrib1->format('Y-m-d')."', date_fin, '".$dateCourant->format('Y-m-d H:i:s')."', 'L'
                                                FROM adresse
                                                WHERE 
                                                    id = ".$aAdresseStockee[0]->getId()." ")
                        ;
                        $adresse_id = $this->_em->getConnection()->lastInsertId();
                        
                        // mise a jour des adresse_id de client_a_servir_logist et client_a_servir_src
                        $this->_em->getConnection()
                                ->executeQuery("UPDATE
                                                    client_a_servir_logist 
                                                SET 
                                                    adresse_id=".$adresse_id." 
                                                WHERE 
                                                    abonne_soc_id = ".$aAdresseStockee[0]->getAbonneSoc()->getId()." 
                                                    AND adresse_id = ".$iAdresseStockeeId."
                                                    AND date_distrib BETWEEN '".$dateDistrib1->format('Y-m-d H:i:s')."' AND '".$aAdresseStockee[0]->getDateFin()->format('Y-m-d H:i:s')."' ")
                        ;
                        $this->_em->getConnection()
                                ->executeQuery("UPDATE
                                                    client_a_servir_src 
                                                SET 
                                                    adresse_id=".$adresse_id." 
                                                WHERE 
                                                    abonne_soc_id = ".$aAdresseStockee[0]->getAbonneSoc()->getId()." 
                                                    AND adresse_id = ".$iAdresseStockeeId." 
                                                    AND date_distrib BETWEEN '".$dateDistrib1->format('Y-m-d H:i:s')."' AND '".$aAdresseStockee[0]->getDateFin()->format('Y-m-d H:i:s')."' ")
                        ;
            
                        // Mettre a date_distrib-1 la date_fin de l'adresse stockee avant l'arrivee du fichier courant
                        $this->_em->getConnection()
                                ->executeQuery("UPDATE
                                                    adresse 
                                                SET 
                                                    date_fin='".$dateDistrib_1->format('Y-m-d')."'
                                                    , date_modif='".$dateCourant->format('Y-m-d H:i:s')."'
                                                WHERE 
                                                    id = ".$aAdresseStockee[0]->getId()." ")
                        ;
                        
                        // Supprimer toutes les adresses futures non utilisees
                        if($this->existeAdressesUlterieures($iAbonneSocId, $sDateDistribYmd, 'CASL'))
                        {
                            $this->supprAdrFuturesNonUtilisees($iAbonneSocId, $sDateDistribYmd, 'CASL');
                        }
                        
                        // Insertion de la nouvelle adresse
                        $this->_em->getConnection()
                                ->executeQuery("INSERT INTO adresse (abonne_soc_id, vol1, vol2, vol3, vol4, vol5, cp, ville, type_changement_id, date_debut, date_fin, date_modif, type_adresse) "
                                        . " SELECT DISTINCT abonne_soc_id, vol1, vol2, vol3, vol4, vol5, cp, ville, ".$aTypeChangement[0]->getId().", date_distrib, ".( ($this->existeAdressesUlterieures($iAbonneSocId, $sDateDistribYmd, 'CASL')==true) ? "date_distrib" : "'".$date_fin."'" ).", '".$dateCourant->format('Y-m-d H:i:s')."', 'L' FROM client_a_servir_src_tmp2 WHERE id = ".$oTmp2->getId()."");
                        
                        $this->_em->clear();
                    }
                }
            }
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Truncate table client_a_servir_src_tmp2
     */
    public function truncate() {
        try {
            $this->_em->getConnection()
                    ->executeQuery("TRUNCATE TABLE client_a_servir_src_tmp2");
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
                    ->executeQuery("INSERT INTO abonne_soc (numabo_ext, soc_code_ext, client_type, vol1, vol2) SELECT DISTINCT numabo_ext, ".(($clientType==0)?"soc_code_ext":"IFNULL(soc_code_ext, '')").", ".$clientType.", vol1, vol2 FROM client_a_servir_src_tmp2 WHERE abonne_soc_id IS NULL");
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
            // Mise a "0" du champs "chgt_adr"
            $this->_em->getConnection()->executeQuery("UPDATE client_a_servir_src_tmp2 SET chgt_adr = 0 ");
            $this->_em->clear();
                
            // Si abonne
            if($clientType==0) {
                $this->_em->getConnection()
                        ->executeQuery("UPDATE
                                                client_a_servir_src_tmp2 AS t
                                                LEFT JOIN  abonne_soc AS a ON t.soc_code_ext=a.soc_code_ext AND t.numabo_ext=a.numabo_ext AND a.client_type = ".$clientType." 
                                        SET t.abonne_soc_id=a.id
                                        WHERE t.abonne_soc_id IS NULL AND a.id IS NOT NULL ")
                ;
                $this->_em->clear();
                
                // Mise a jour de la date de 1er service
                $this->_em->getConnection()
                        ->executeQuery("UPDATE 
                                            client_a_servir_src_tmp2 AS t
                                            LEFT JOIN  abonne_soc AS a ON t.soc_code_ext=a.soc_code_ext AND t.numabo_ext=a.numabo_ext AND a.client_type = ".$clientType." 
                                        SET 
                                            a.date_service_1=t.date_distrib
                                        WHERE 
                                            (t.abonne_soc_id IS NULL 
                                            OR (t.abonne_soc_id IS NOT NULL AND t.date_distrib<a.date_service_1)) ")
                ;
                $this->_em->clear();
            }
            // Si lieu de vente
            else {
                $this->_em->getConnection()
                        ->executeQuery("UPDATE
                                                client_a_servir_src_tmp2 AS t
                                                LEFT JOIN abonne_soc AS a ON t.numabo_ext=a.numabo_ext AND a.client_type = ".$clientType." 
                                        SET t.abonne_soc_id=a.id
                                        WHERE t.abonne_soc_id IS NULL AND a.id IS NOT NULL ")
                ;
                $this->_em->clear();
                
                // Mise a jour de la date de 1er service
                $this->_em->getConnection()
                        ->executeQuery("UPDATE 
                                            client_a_servir_src_tmp2 AS t
                                            LEFT JOIN abonne_soc AS a ON t.numabo_ext=a.numabo_ext AND a.client_type = ".$clientType." 
                                        SET 
                                            a.date_service_1=t.date_distrib
                                        WHERE 
                                            (t.abonne_soc_id IS NULL 
                                            OR (t.abonne_soc_id IS NOT NULL AND t.date_distrib<a.date_service_1)) ")
                ;
                $this->_em->clear();
            }            
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
                    ->executeQuery("UPDATE
                                            client_a_servir_src_tmp2 AS t
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
     * Mise a jour des champs tournees
     */
    public function updateTourneeAbosConnus() {
        try {
            $this->_em->getConnection()
                    ->executeQuery(" UPDATE
                                        client_a_servir_src_tmp2 t
                                        , adresse_rnvp ar
                                        , tournee_detail td
                                        , modele_tournee mt
                                        , modele_tournee_jour mtj
                                    SET
                                        t.modele_tournee_jour_id = mtj.id
                                        , t.point_livraison_ordre = td.ordre
                                        , t.ordre_dans_arret = td.ordre_stop
                                    WHERE
                                        t.modele_tournee_jour_id IS NULL
                                        AND t.point_livraison_id = ar.id
                                        AND t.abonne_soc_id = td.num_abonne_id	
                                        AND td.latitude = ar.geoy
                                        AND td.longitude = ar.geox
                                        AND mt.id = mtj.tournee_id
                                        AND mt.code = td.modele_tournee_jour_code
                                        AND mtj.jour_id=CAST(DATE_FORMAT(t.date_distrib, '%w') AS SIGNED)+1 ")
            ;
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

    /**
     * Mise a jour des champs tournees
     */
    public function updateTournee() {
        try {
            // Mise a jour des champs tournee
            $this->updateTourneeAbosConnus();
            
            
            
            
            
            // Mise a jour des champs tournee
            //$this->updateTourneeAbosConnus();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

    /**
     * Suppression des lignes de tournee_detail suite a un changement d adresse
     * @throws \Doctrine\DBAL\DBALException
     */
    public function supprTourneeDetailChgtAdr() {
        try {
            // Mettre a "2" le champs "chgt_adr" pour les cas des vrais changement d adresse
            //      cas adresse non normalise auparavant
            $this->_em->getConnection()
                    ->executeQuery(" UPDATE
                                        client_a_servir_src_tmp2 t
                                    SET
                                        t.chgt_adr = 2
                                    WHERE
                                        t.chgt_adr = 1
                                        AND (t.vol4_anc IS NULL OR t.insee_anc IS NULL OR t.rnvp_id IS NULL) ")
            ;
            $this->_em->clear();
            
            //      cas changement d adresse 
            $this->_em->getConnection()
                    ->executeQuery(" UPDATE
                                        client_a_servir_src_tmp2 t
                                        LEFT JOIN adresse_rnvp r ON t.rnvp_id = r.id
                                    SET
                                        t.chgt_adr = 2
                                    WHERE
                                        t.chgt_adr = 1
                                        AND r.id IS NOT NULL
                                        AND (r.adresse <> t.vol4_anc OR r.insee <> t.insee_anc) ")
            ;
            $this->_em->clear();
            
            // Suppression des lignes de tournee_detail suite a un changement d adresse
            $select = " SELECT DISTINCT abonne_soc_id FROM client_a_servir_src_tmp2 WHERE chgt_adr = 2 AND abonne_soc_id IS NOT NULL ";
            $aRes   = $this->_em->getConnection()->fetchAll($select);
            foreach($aRes as $aArr)
            {
                $sDelete    = " DELETE FROM tournee_detail WHERE num_abonne_id = ".$aArr['abonne_soc_id']." ";
                $this->_em->getConnection()->executeQuery($sDelete);
                $this->_em->clear();
            }
            
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
                    ->executeQuery("UPDATE
                                        client_a_servir_src_tmp2 AS t
                                        LEFT JOIN adresse AS a ON t.abonne_soc_id=a.abonne_soc_id AND t.vol1=a.vol1 AND t.vol2=a.vol2 AND t.vol3=a.vol3 AND t.vol4=a.vol4 AND t.vol5=a.vol5 AND t.cp=a.cp AND t.ville=a.ville AND t.date_distrib BETWEEN a.date_debut AND a.date_fin AND (a.type_adresse IS NULL OR a.type_adresse = 'L')
                                    SET t.adresse_id=a.id
                                        , t.rnvp_id=a.rnvp_id
                                        , t.point_livraison_id=a.point_livraison_id
                                        , t.commune_id=a.commune_id
                                    WHERE (t.adresse_id IS NULL OR t.commune_id IS NULL) AND a.id IS NOT NULL ")
            ;
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

    /**
     * Fermeture des infos adresses d'un abonne qui change d'infos adresse (vol1-5, cp, ville)
     * @param \DateTime $dateDistrib
     * @throws \Doctrine\DBAL\DBALException
     */
    public function fermetureAncienneAdresse(\DateTime $dateDistrib) {
        try {
            $dateCourant    = new \Datetime();
            $dateDistrib    = clone $dateDistrib; // Force la copie de this->object, sinon il pointera vers le même objet.
            $dateDistrib_1 = $dateDistrib->sub(new \DateInterval('P1D')); // Date a distrib - 1
            $this->_em->getConnection()
                    ->executeQuery("UPDATE
                                        client_a_servir_src_tmp2 AS t
                                        LEFT JOIN adresse AS a ON t.abonne_soc_id=a.abonne_soc_id AND t.date_distrib BETWEEN a.date_debut AND a.date_fin AND (a.type_adresse IS NULL OR a.type_adresse = 'L')
                                        LEFT JOIN adresse_rnvp AS r ON a.rnvp_id=r.id
                                    SET
                                        a.date_fin = '".$dateDistrib_1->format('Y-m-d')."'
                                        , a.date_modif = '".$dateCourant->format('Y-m-d H:i:s')."' 
                                        , t.vol4_anc = r.adresse
                                        , t.insee_anc = r.insee
                                        , t.chgt_adr = 1
                                    WHERE 
                                        t.adresse_id IS NULL AND a.id IS NOT NULL ")
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
            $this->_em->getConnection()
                                ->executeQuery("UPDATE
                                                    client_a_servir_src_tmp2 t
                                                SET
                                                    t.chgt_adr = 1
                                                WHERE
                                                    t.adresse_id IS NULL ")
                        ;
            $this->_em->clear();
            
            $dateCourant    = new \Datetime();
            $repoTypeChangement = $this->_em->getRepository('AmsAdresseBundle:TypeChangement');
            $aTypeChangement   = $repoTypeChangement->findByCode('CHGT_ADR');
            $this->_em->getConnection()
                    ->executeQuery("INSERT INTO adresse (abonne_soc_id, vol1, vol2, vol3, vol4, vol5, cp, ville, type_changement_id, date_debut, date_fin, date_modif, type_adresse) SELECT DISTINCT abonne_soc_id, vol1, vol2, vol3, vol4, vol5, cp, ville, ".$aTypeChangement[0]->getId().", date_distrib, '".$date_fin."', '".$dateCourant->format('Y-m-d H:i:s')."', 'L' FROM client_a_servir_src_tmp2 WHERE adresse_id IS NULL");
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

    /**
     * Mise a jour de l'attribut commune
     */
    public function updateCommune() {
        try {
            /*
            $this->_em->getConnection()
                    ->executeQuery("UPDATE
                                        client_a_servir_src_tmp2 AS t
                                        LEFT JOIN adresse_rnvp AS r ON t.rnvp_id=r.id
                                        LEFT JOIN commune c ON r.insee=c.insee
                                        LEFT JOIN depot_commune dc ON dc.commune_id=c.id AND t.date_distrib BETWEEN dc.date_debut AND dc.date_fin 
                                    SET 
                                        t.commune_id=c.id
                                        , t.depot_id=dc.depot_id
                                    WHERE 
                                        t.adresse_id IS NOT NULL
                                        AND c.id IS NOT NULL
                                         ")
            ;
            */
            
            // Mise a jour de commune
            $this->_em->getConnection()
                    ->executeQuery("UPDATE
                                        client_a_servir_src_tmp2 AS t
                                        LEFT JOIN adresse_rnvp AS r ON t.rnvp_id=r.id
                                        LEFT JOIN commune c ON r.insee=c.insee
                                    SET 
                                        t.commune_id=c.id
                                    WHERE 
                                        t.adresse_id IS NOT NULL
                                        AND c.id IS NOT NULL
                                         ")
            ;
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    
    
    
    
    //------------------------------ Infos portages ------------------------------//
    
    
    
    
    
    
    /**
     * Mise en table temporaire des infos portage deja inserees pour les abonnes du fichier en cours
     * 
     * @throws \Doctrine\DBAL\DBALException
     */
    private function initInfoPortageTmp()
    {
        try {
            
            // Relation "code TypeInfoPortage" et le nom de la colonne de "client_a_servir_src_tmp2"
            $this->initTypeInfoPortageFichier();
            
            $typeInfoPortageIds = array_keys($this->typeInfoPortageFichier);    // Tous les id de TypeInfoPortage 
            
            // Mise en table temporaire des infos portage deja inserees 
            $this->_em->getConnection()
                    ->executeQuery("TRUNCATE TABLE info_portage_tmp");
            $this->_em->getConnection()
                    ->executeQuery("INSERT INTO info_portage_tmp 
                                        (info_portage_id, abonne_soc_id, type_info_id, valeur, date_debut, date_fin)
                                    SELECT
                                        i.id, t.abonne_soc_id, i.type_info_id, i.valeur, i.date_debut, i.date_fin
                                    FROM
                                        client_a_servir_src_tmp2 t
                                        LEFT JOIN infos_portages_abonnes ipa ON t.abonne_soc_id=ipa.abonne_id
                                        LEFT JOIN info_portage i ON ipa.info_portage_id=i.id
                                    WHERE
                                        i.type_info_id IN (".implode(",", $typeInfoPortageIds).")"
                                        );
            
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Insertion d'une nouvelle ligne d'info portage
     * 
     * @param integer $abonneSocId
     * @param integer $typeInfoPortageId
     * @param string $valeurInfoPortage
     * @param \DateTime $dateDistrib
     * @param string $date_fin
     * @throws \Doctrine\DBAL\DBALException
     */
    private function insertUneNouvelleInfo($abonneSocId=0, $typeInfoPortageId=1, $valeurInfoPortage='', \DateTime $dateDistrib, $date_fin='2078-12-31') {
        try {
            $dateDistribCourant    = clone $dateDistrib; 
            
            $insert = "INSERT INTO info_portage 
                                            (type_info_id, valeur, origine, date_debut, date_fin, date_modif)
                                        VALUES 
                                            (".$typeInfoPortageId.", '".addslashes($valeurInfoPortage)."', 0, '".$dateDistribCourant->format('Y-m-d')."', '".$date_fin."', NOW()) ";
            $this->_em->getConnection()->executeQuery($insert);
            $lastInfoPortageId = $this->_em->getConnection()->lastInsertId();
            $this->_em->getConnection()
                        ->executeQuery("INSERT INTO infos_portages_abonnes 
                                            (info_portage_id, abonne_id)
                                        VALUES 
                                            (".$lastInfoPortageId.", ".$abonneSocId.") ")
                ;
            
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }        
    }
    
    /**
     * Mise a jour du tag pour les infos deja integrees et encore valides pour la date courante ou NULL des deux cotes
     * @param integer $typeInfoPortageId
     * @param string $colonneTmp    nom d'une colonne d'info portage de la table "client_a_servir_src_tmp2"
     * @throws \Doctrine\DBAL\DBALException
     */
    private function traiteInfoSansChangement($typeInfoPortageId, $colonneTmp) {
        try {
            $this->_em->getConnection()
                    ->executeQuery("UPDATE
                                        client_a_servir_src_tmp2 t
                                        LEFT JOIN info_portage_tmp i ON t.abonne_soc_id=i.abonne_soc_id AND t.date_distrib BETWEEN i.date_debut AND i.date_fin AND i.type_info_id = ".$typeInfoPortageId." 
                                    SET 
                                        info_traitee=1
                                    WHERE
                                        t.id IS NOT NULL
                                        AND ((t.".$colonneTmp." <> '' AND i.valeur IS NOT NULL AND t.".$colonneTmp." = i.valeur) OR (t.".$colonneTmp." = '' AND i.valeur IS NULL))
                                         ")
            ;
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }        
    }
    
    /**
     * Stockage des infos portage - Cas ou aucun fichier de date future n'est pas encore traite
     * 
     * @param \DateTime $dateDistrib
     * @param string $date_fin
     * @throws \Doctrine\DBAL\DBALException
     */
    public function infoPortage0DateFuture(\DateTime $dateDistrib, $date_fin='2078-12-31') {
        try {
            $dateDistribCourant    = clone $dateDistrib; 
            $dateDistribCourantTmp    = clone $dateDistrib;
            $dateDistrib_1 = $dateDistribCourantTmp->sub(new \DateInterval('P1D')); // Date a distrib - 1
            
            // Mise en table temporaire des infos portage deja inserees pour les abonnes du fichier en cours
            $this->initInfoPortageTmp();
            
            // initialise le tag servant a suivre les infos portage traitees
            $this->_em->getConnection()
                    ->executeQuery(" UPDATE client_a_servir_src_tmp2 SET info_traitee=NULL ");
            
            // La cle $this->typeInfoPortageFichier = "id TypeInfoPortage". Sa valeur = nom de la colonne de "client_a_servir_src_tmp2"
            foreach($this->typeInfoPortageFichier as $typeInfoPortageId => $tmpCol)
            {
                // Mise a NULL du tag "info_traitee" de client_a_servir_src_tmp2
                $this->_em->getConnection()
                        ->executeQuery("UPDATE client_a_servir_src_tmp2 SET info_traitee=NULL ");
                
                // Mise a jour du tag pour les infos deja integrees et encore valides pour la date courante ou NULL des deux cotes
                $this->traiteInfoSansChangement($typeInfoPortageId, $tmpCol);
                
                // Traitement des infos portages existantes dans BDD mais absent du Tmp ou existantes des deux cotes mais avec un changement
                $slct   = " SELECT
                                t.id AS tmp_id
                                , t.abonne_soc_id
                                , i.info_portage_id
                                , t.".$tmpCol." AS nouv_valeur
                                , CASE WHEN t.".$tmpCol." = '' THEN 'nouv_info_null' 
                                                ELSE 'nouvelle_info' 
                                        END AS type_chgmt
                                , DATE_FORMAT(i.date_debut, '%Y-%m-%d') AS date_debut
                                , DATE_FORMAT(i.date_fin, '%Y-%m-%d') AS date_fin
                            FROM
                                client_a_servir_src_tmp2 t
                                LEFT JOIN info_portage_tmp i ON t.abonne_soc_id=i.abonne_soc_id AND t.date_distrib BETWEEN i.date_debut AND i.date_fin AND i.type_info_id = ".$typeInfoPortageId." 
                            WHERE
                                t.id IS NOT NULL
                                AND ((t.".$tmpCol." = '' AND i.valeur IS NOT NULL) OR (t.".$tmpCol." <> '' AND i.valeur IS NOT NULL AND t.".$tmpCol." <> i.valeur))
                                AND t.info_traitee IS NULL
                            ";
                $res    = $this->_em->getConnection()->fetchAll($slct);
                foreach($res as $aArr) {
                    // Si une info est deja integree dans BDD mais aucune n'est trouvee dans le fichier courant
                    if($aArr['type_chgmt']=='nouv_info_null') {
                        if($aArr['date_debut']==$dateDistribCourant->format('Y-m-d')) {
                            $this->_em->getConnection()
                                        ->executeQuery("DELETE FROM infos_portages_abonnes WHERE info_portage_id = ".$aArr['info_portage_id']." AND abonne_id = ".$aArr['abonne_soc_id']." ");
                            // Verifie si cette info portage est utilise pour les adresses ou les points de livraisons
                            $iInfosUtiliseesAilleurs    = 0;
                            $sSlctInfosUtiliseesAilleurs    = " SELECT COUNT(*) AS nb FROM infos_portages_adresses WHERE info_portage_id = ".$aArr['info_portage_id']." ";
                            $resInfosUtiliseesAilleurs    = $this->_em->getConnection()->fetchAll($sSlctInfosUtiliseesAilleurs);
                            foreach($resInfosUtiliseesAilleurs as $aArrInfosUtiliseesAilleurs) {
                                $iInfosUtiliseesAilleurs    += $aArrInfosUtiliseesAilleurs['nb'];
                            }
                            $sSlctInfosUtiliseesAilleurs    = " SELECT COUNT(*) AS nb FROM infos_portages_livraisons WHERE info_portage_id = ".$aArr['info_portage_id']." ";
                            $resInfosUtiliseesAilleurs    = $this->_em->getConnection()->fetchAll($sSlctInfosUtiliseesAilleurs);
                            foreach($resInfosUtiliseesAilleurs as $aArrInfosUtiliseesAilleurs) {
                                $iInfosUtiliseesAilleurs    += $aArrInfosUtiliseesAilleurs['nb'];
                            }
                            
                            if($iInfosUtiliseesAilleurs==0)
                            {
                                $this->_em->getConnection()
                                            ->executeQuery("DELETE FROM info_portage WHERE id = ".$aArr['info_portage_id']." ");
                            }
                        }
                        else
                        {
                            $this->_em->getConnection()
                                        ->executeQuery("UPDATE
                                                            info_portage 
                                                        SET 
                                                            date_fin='".$dateDistrib_1->format('Y-m-d')."'
                                                            , date_modif = NOW()
                                                        WHERE
                                                            id = ".$aArr['info_portage_id']."")
                                ;
                        }
                    }
                    // Si des deux cotes, l'info est renseigne mais differente, on stocke la nouvelle info
                    else {
                        if($aArr['date_debut']==$dateDistribCourant->format('Y-m-d')) {
                            $this->_em->getConnection()
                                        ->executeQuery("UPDATE
                                                            info_portage 
                                                        SET 
                                                            valeur='".addslashes($aArr['nouv_valeur'])."'
                                                            , date_modif = NOW()
                                                        WHERE
                                                            id = ".$aArr['info_portage_id']."")
                                ;
                        }
                        else
                        {
                            $this->_em->getConnection()
                                        ->executeQuery("UPDATE
                                                            info_portage 
                                                        SET 
                                                            date_fin='".$dateDistrib_1->format('Y-m-d')."'
                                                            , date_modif = NOW()
                                                        WHERE
                                                            id = ".$aArr['info_portage_id']."")
                                ;
                            // Insere la nouvelle info
                            $this->insertUneNouvelleInfo($aArr['abonne_soc_id'], $typeInfoPortageId, $aArr['nouv_valeur'], $dateDistribCourant, $date_fin);
                            
                        }
                    }                    
                    
                    $this->_em->getConnection()
                                ->executeQuery("UPDATE client_a_servir_src_tmp2 SET info_traitee=1 WHERE id = ".$aArr['tmp_id']."")
                        ;
                }
                
                
                //  Traitement des infos portages present dans le fichier courant mais absentes de la BDD                
                $slct   = " SELECT abonne_soc_id, ".$tmpCol." AS nouv_valeur
                            FROM
                                client_a_servir_src_tmp2
                            WHERE
                                info_traitee IS NULL 
                                AND ".$tmpCol." <> ''
                            ";
                $res    = $this->_em->getConnection()->fetchAll($slct);
                foreach($res as $aArr) {
                    $this->insertUneNouvelleInfo($aArr['abonne_soc_id'], $typeInfoPortageId, $aArr['nouv_valeur'], $dateDistribCourant, $date_fin);
                }
            }
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    
    /**
     * Stockage des infos portage - Cas ou des fichiers de date future ont ete deja traites
     * 
     * @param \DateTime $dateDistrib
     * @param string $date_fin
     * @throws \Doctrine\DBAL\DBALException
     */
    public function infoPortageAvecDateFutureInseree(\DateTime $dateDistrib, $date_fin='2078-12-31') {
        try {
            $dateDistribCourant    = clone $dateDistrib; 
            $dateDistribCourantTmp1    = clone $dateDistrib;
            $dateDistribCourantTmp2    = clone $dateDistrib; 
            $dateDistrib_1 = $dateDistribCourantTmp1->sub(new \DateInterval('P1D')); // Date a distrib - 1
            $dateDistrib1 = $dateDistribCourantTmp2->add(new \DateInterval('P1D')); // Date a distrib + 1
            
            // Mise en table temporaire des infos portage deja inserees pour les abonnes du fichier en cours
            $this->initInfoPortageTmp();
            
            // initialise le tag servant a suivre les infos portage traitees
            $this->_em->getConnection()
                    ->executeQuery(" UPDATE client_a_servir_src_tmp2 SET info_traitee=NULL ");
            
            
            // La cle $this->typeInfoPortageFichier = "id TypeInfoPortage". Sa valeur = nom de la colonne de "client_a_servir_src_tmp2"
            foreach($this->typeInfoPortageFichier as $typeInfoPortageId => $tmpCol)
            {
                // Mise a NULL du tag "info_traitee" de client_a_servir_src_tmp2
                $this->_em->getConnection()
                        ->executeQuery("UPDATE client_a_servir_src_tmp2 SET info_traitee=NULL ");
                
                // Mise a jour du tag pour les infos deja integrees et enore valides pour la date courante ou NULL des deux cotes
                $this->traiteInfoSansChangement($typeInfoPortageId, $tmpCol);
                
                // Traitement des infos portages existantes dans BDD mais absentes du Tmp ou existantes des deux cotes mais avec un changement
                $slct   = " SELECT
                                t.id AS tmp_id
                                , t.abonne_soc_id
                                , i.info_portage_id
                                , t.".$tmpCol." AS nouv_valeur
                                , i.valeur AS anc_valeur
                                , CASE WHEN t.".$tmpCol." = '' THEN 'nouv_info_null' 
                                                ELSE 'nouvelle_info' 
                                        END AS type_chgmt
                                , DATE_FORMAT(i.date_debut, '%Y-%m-%d') AS date_debut
                                , DATE_FORMAT(i.date_fin, '%Y-%m-%d') AS date_fin
                            FROM
                                client_a_servir_src_tmp2 t
                                LEFT JOIN info_portage_tmp i ON t.abonne_soc_id=i.abonne_soc_id AND t.date_distrib BETWEEN i.date_debut AND i.date_fin AND i.type_info_id = ".$typeInfoPortageId." 
                            WHERE
                                t.id IS NOT NULL
                                AND ((t.".$tmpCol." = '' AND i.valeur IS NOT NULL) OR (t.".$tmpCol." <> '' AND i.valeur IS NOT NULL AND t.".$tmpCol." <> i.valeur))
                                AND t.info_traitee IS NULL
                            ";
                $res    = $this->_em->getConnection()->fetchAll($slct);
                foreach($res as $aArr) {
                    // Si une info est deja integree dans BDD mais aucune n'est trouvee dans le fichier courant
                    if($aArr['type_chgmt']=='nouv_info_null') {
                        if($aArr['date_debut']==$aArr['date_fin'] && $aArr['date_debut']==$dateDistribCourant->format('Y-m-d')) {
                            $this->_em->getConnection()
                                        ->executeQuery("DELETE FROM infos_portages_abonnes WHERE info_portage_id = ".$aArr['info_portage_id']." AND abonne_id = ".$aArr['abonne_soc_id']." ");
                            // Verifie si cette info portage est utilise pour les adresses ou les points de livraisons
                            $iInfosUtiliseesAilleurs    = 0;
                            $sSlctInfosUtiliseesAilleurs    = " SELECT COUNT(*) AS nb FROM infos_portages_adresses WHERE info_portage_id = ".$aArr['info_portage_id']." ";
                            $resInfosUtiliseesAilleurs    = $this->_em->getConnection()->fetchAll($sSlctInfosUtiliseesAilleurs);
                            foreach($resInfosUtiliseesAilleurs as $aArrInfosUtiliseesAilleurs) {
                                $iInfosUtiliseesAilleurs    += $aArrInfosUtiliseesAilleurs['nb'];
                            }
                            $sSlctInfosUtiliseesAilleurs    = " SELECT COUNT(*) AS nb FROM infos_portages_livraisons WHERE info_portage_id = ".$aArr['info_portage_id']." ";
                            $resInfosUtiliseesAilleurs    = $this->_em->getConnection()->fetchAll($sSlctInfosUtiliseesAilleurs);
                            foreach($resInfosUtiliseesAilleurs as $aArrInfosUtiliseesAilleurs) {
                                $iInfosUtiliseesAilleurs    += $aArrInfosUtiliseesAilleurs['nb'];
                            }
                            
                            if($iInfosUtiliseesAilleurs==0)
                            {
                                $this->_em->getConnection()
                                        ->executeQuery("DELETE FROM info_portage WHERE id = ".$aArr['info_portage_id']." ");
                            }
                        }
                        else if($aArr['date_debut']!=$aArr['date_fin'] && $aArr['date_fin']==$dateDistribCourant->format('Y-m-d'))
                        {
                            $this->_em->getConnection()
                                        ->executeQuery("UPDATE
                                                            info_portage 
                                                        SET 
                                                            date_fin='".$dateDistrib_1->format('Y-m-d')."'
                                                            , date_modif = NOW()
                                                        WHERE
                                                            id = ".$aArr['info_portage_id']."")
                                ;
                        }
                        else if($aArr['date_debut']!=$aArr['date_fin'] && $aArr['date_debut']==$dateDistribCourant->format('Y-m-d'))
                        {
                            $this->_em->getConnection()
                                        ->executeQuery("UPDATE
                                                            info_portage 
                                                        SET 
                                                            date_debut='".$dateDistrib1->format('Y-m-d')."'
                                                            , date_modif = NOW()
                                                        WHERE
                                                            id = ".$aArr['info_portage_id']."")
                                ;
                        }
                        // Date courante est entre date_debut et date_fin
                        else
                        {
                            $this->_em->getConnection()
                                        ->executeQuery("UPDATE
                                                            info_portage 
                                                        SET 
                                                            date_fin='".$dateDistrib_1->format('Y-m-d')."'
                                                            , date_modif = NOW()
                                                        WHERE
                                                            id = ".$aArr['info_portage_id']."")
                                ;                            
                            // Insertion de l'ancienne valeur d'info mais avec date_debut $dateDistrib+1
                            $this->insertUneNouvelleInfo($aArr['abonne_soc_id'], $typeInfoPortageId, $aArr['anc_valeur'], $dateDistrib1, $aArr['date_fin']);
                        }
                    }
                    // Changement d'info
                    else {
                        if($aArr['date_debut']==$aArr['date_fin'] && $aArr['date_debut']==$dateDistribCourant->format('Y-m-d')) {
                            $this->_em->getConnection()
                                        ->executeQuery("UPDATE
                                                            info_portage 
                                                        SET 
                                                            valeur='".addslashes($aArr['nouv_valeur'])."'
                                                            , date_modif = NOW()
                                                        WHERE
                                                            id = ".$aArr['info_portage_id']."")
                                ;
                        }
                        else if($aArr['date_debut']!=$aArr['date_fin'] && $aArr['date_fin']==$dateDistribCourant->format('Y-m-d'))
                        {
                            $this->_em->getConnection()
                                        ->executeQuery("UPDATE
                                                            info_portage 
                                                        SET 
                                                            date_fin='".$dateDistrib_1->format('Y-m-d')."'
                                                            , date_modif = NOW()
                                                        WHERE
                                                            id = ".$aArr['info_portage_id']."")
                                ;
                            // Insere la nouvelle info
                            $this->insertUneNouvelleInfo($aArr['abonne_soc_id'], $typeInfoPortageId, $aArr['nouv_valeur'], $dateDistribCourant, $aArr['date_fin']);
                        }
                        else if($aArr['date_debut']!=$aArr['date_fin'] && $aArr['date_debut']==$dateDistribCourant->format('Y-m-d'))
                        {
                            $this->_em->getConnection()
                                        ->executeQuery("UPDATE
                                                            info_portage 
                                                        SET 
                                                            date_debut='".$dateDistrib1->format('Y-m-d')."'
                                                            , date_modif = NOW()
                                                        WHERE
                                                            id = ".$aArr['info_portage_id']."")
                                ;
                            // Insere la nouvelle info
                            $this->insertUneNouvelleInfo($aArr['abonne_soc_id'], $typeInfoPortageId, $aArr['nouv_valeur'], $dateDistribCourant, $aArr['date_debut']);
                        }
                        // Date courante est entre date_debut et date_fin
                        else
                        {
                            $this->_em->getConnection()
                                        ->executeQuery("UPDATE
                                                            info_portage 
                                                        SET 
                                                            date_fin='".$dateDistrib_1->format('Y-m-d')."'
                                                            , date_modif = NOW()
                                                        WHERE
                                                            id = ".$aArr['info_portage_id']."")
                                ;                            
                            // Insertion de l'ancienne valeur d'info mais avec date_debut $dateDistrib+1
                            $this->insertUneNouvelleInfo($aArr['abonne_soc_id'], $typeInfoPortageId, $aArr['anc_valeur'], $dateDistrib1, $aArr['date_fin']);
                            
                            // Insertion de la nouvelle info
                            $this->insertUneNouvelleInfo($aArr['abonne_soc_id'], $typeInfoPortageId, $aArr['nouv_valeur'], $dateDistribCourant, $dateDistribCourant->format("Y-m-d"));
                        }
                        
                    }                  
                    
                    $this->_em->getConnection()
                                ->executeQuery("UPDATE client_a_servir_src_tmp2 SET info_traitee=1 WHERE id = ".$aArr['tmp_id']."")
                        ;
                }
                
                
                //  Traitement des infos portages presentes dans le fichier courant mais absentes de la BDD                
                $slct   = " SELECT abonne_soc_id, ".$tmpCol." AS nouv_valeur, DATE_FORMAT(date_distrib, '%Y-%m-%d') AS date_distrib
                            FROM
                                client_a_servir_src_tmp2
                            WHERE
                                info_traitee IS NULL 
                                AND ".$tmpCol." <> ''
                            ";
                $res    = $this->_em->getConnection()->fetchAll($slct);
                foreach($res as $aArr) {
                    // verification si une info portage est deja integree pour cet abonne dans la date future
                    $min_date_debut_future_1  = '';
                    $slctDateMinFuture_1  = " SELECT DATE_FORMAT(DATE_SUB(MIN(date_debut), INTERVAL 1 DAY), '%Y-%m-%d') AS min_date_debut_future FROM info_portage_tmp WHERE abonne_soc_id = ".$aArr['abonne_soc_id']." AND date_debut > '".$aArr['date_distrib']."' ";
                    $res1    = $this->_em->getConnection()->fetchAll($slctDateMinFuture_1);
                    foreach($res1 as $aArr1) {
                        $min_date_debut_future_1  = $aArr1['min_date_debut_future'];
                    }
                                        
                    $this->insertUneNouvelleInfo($aArr['abonne_soc_id'], $typeInfoPortageId, $aArr['nouv_valeur'], $dateDistribCourant, (($min_date_debut_future_1=='')?$date_fin:$min_date_debut_future_1));
                }
            }           
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
}
