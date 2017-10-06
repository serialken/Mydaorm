<?php

namespace Ams\DistributionBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\DBALException;
use Ams\DistributionBundle\Entity\InfoPortage;
use Ams\DistributionBundle\Exception\ReperageSQLException;

class ReperageRepository extends EntityRepository {

    public function getReperages() {
        $qb = $this->createQueryBuilder('r')
                ->leftJoin('r.commune', 'c')
                ->addSelect('c')
        ;

        return $qb->getQuery()
                        ->getResult();
    }
    
    public function getTotalReperagesByDepot($depodId, $dateMin, $dateMax) {
        
        $qb = $this->createQueryBuilder('r')
                ->select('COUNT(r)')
                ->where('r.depot = :depot')
                ->setParameter('depot', $depodId)
                ->andWhere('r.dateDemar >= :date_min')
                ->setParameter('date_min', $dateMin)
                ->andWhere('r.dateDemar <= :date_max')
                ->setParameter('date_max', $dateMax)
                ;
        
        return $qb  ->getQuery()
                    ->getSingleResult() ;
    }
    public function getTotalReperagesBySociety($idSociety, $dateMin, $dateMax) {
        
        $qb = $this->createQueryBuilder('r')
                ->select('COUNT(r)')
                ->where('r.societe= :society')
                ->setParameter('society', $idSociety)
                ->andWhere('r.dateDemar >= :date_min')
                ->setParameter('date_min', $dateMin)
                ->andWhere('r.dateDemar <= :date_max')
                ->setParameter('date_max', $dateMax)
                ;
        
        return $qb  ->getQuery()
                    ->getSingleResult() ;
    }
    
    public function getTotalReponseReperagesByDepot($depodId, $dateMin, $dateMax) {
        
        $qb = $this->createQueryBuilder('r');
        
            $qb->select('COUNT(r)')
                ->where('r.depot = :depot')
                ->setParameter('depot', $depodId)
                ->andWhere('r.dateDemar >= :date_min')
                ->setParameter('date_min', $dateMin)
                ->andWhere('r.dateDemar <= :date_max')
                ->setParameter('date_max', $dateMax)
                ->andWhere($qb->expr()->isNotNull('r.dateReponse'))
                ;
        
        return $qb  ->getQuery()
                    ->getSingleResult() ;
    }

    public function getReperagesBySociety() {

        $qb = $this->createQueryBuilder('r')
                ->LeftJoin('r.societe', 's')
                ->LeftJoin('r.depot', 'd')
                ->LeftJoin('d.commune', 'c')
                ->addSelect('s', 'd', 'c')
        ;

        return $qb->getQuery()
                        ->getResult();
    }

    public function reperageCount($idDepot, $idSociety, $dateMin, $dateMax) {

        $count = $this->createQueryBuilder('r')
                ->select('COUNT(r)')
                ->where('r.depot = :depot')
                ->setParameter('depot', $idDepot)
                ->andWhere('r.societe= :society')
                ->setParameter('society', $idSociety)
                ->andWhere('r.dateDemar >= :date_min')
                ->setParameter('date_min', $dateMin)
                ->andWhere('r.dateDemar <= :date_max')
                ->setParameter('date_max', $dateMax)
                ->getQuery()
                ->getSingleScalarResult();

        return $count;

    }

    public function numberResponse($idDepot, $idSociety, $dateMin, $dateMax) {

        $dateMin = $this->transformDateToDataBaseFormatReverse($dateMin);
        $dateMax = $this->transformDateToDataBaseFormatReverse($dateMax);

        $countReperage = $this->reperageCount($idDepot, $idSociety, $dateMin, $dateMax);
        if (!$countReperage)
            return $countReperage;

        $count = $this->createQueryBuilder('r')
                ->select('COUNT(r)')
                ->where('r.depot = :depot')
                ->setParameter('depot', $idDepot)
                ->andWhere('r.societe= :society')
                ->setParameter('society', $idSociety)
                ->andWhere('r.topage IS NOT NULL')
                ->andWhere('r.dateDemar >= :date_min')
                ->setParameter('date_min', $dateMin)
                ->andWhere('r.dateDemar <= :date_max')
                ->setParameter('date_max', $dateMax)
                ->getQuery()
                ->getSingleScalarResult();

        return $countReperage . '(' . $count . ')';
    }

    public function getReperagesBySocietyDepot($idDepot, $idSociety = 0, $dateMin, $dateMax, $filter = false) {

        $dateMin = $this->transformDateToDataBaseFormatReverse($dateMin);
        $dateMax = $this->transformDateToDataBaseFormatReverse($dateMax);

        $qb = $this->createQueryBuilder('r')
                ->leftJoin('r.qualif', 'q')
                ->addSelect('q')
                ->leftJoin('r.tournee', 't')
                ->addSelect('t')
                ->leftJoin('r.societe', 's')
                ->addSelect('s')
                ->leftJoin('r.depot', 'd')
                ->addSelect('d')
                ->leftJoin('r.adresse', 'a')
                ->addSelect('a');
                
        
        if($idDepot != 0)
        {
            $qb->andWhere('r.depot = :depot')
                ->setParameter('depot', $idDepot);
        }

        if ($idSociety != 0) {
            $qb->andWhere('r.societe= :society')
                    ->setParameter('society', $idSociety);
        }

        $qb->andWhere('r.dateDemar >= :date_min')
                ->setParameter('date_min', $dateMin)
                ->andWhere('r.dateDemar <= :date_max')
                ->setParameter('date_max', $dateMax)
        ;
        $filter = $this->topageFilter($filter);

        if ($filter) {
            if ($filter == 'topes') {
                $qb->andWhere('r.topage = :val_1 OR r.topage = :val_2 OR r.topage = :val_3 ')
                        ->setParameter('val_1', 'A')
                        ->setParameter('val_2', 'B')
                        ->setParameter('val_3', 'C')
                        ->orderBy('r.topage', 'ASC');
            } else if ($filter == 'aucun') {
                $qb->andWhere('r.topage IS NULL');
            } else {
                $qb->andWhere('r.topage = :filter')
                        ->setParameter('filter', $filter);
            }
        }

        return $qb->getQuery()->getResult();
    }

//    public function getReperagesForPdf($idDepot, $idSociety = 0, $date) {
    public function getReperagesForPdf($idDepot, $idSociety = 0, $dateMin,$dateMax,$aReperageId = false,$filter = false) {

//        $date = $this->transformDateToDataBaseFormatReverse($date);
        $dateMin = $this->transformDateToDataBaseFormatReverse($dateMin);
        $dateMax = $this->transformDateToDataBaseFormatReverse($dateMax);

        $qb = $this->createQueryBuilder('r')
                ->leftJoin('r.qualif', 'q')
                ->addSelect('q')
                ->leftJoin('r.tournee', 't')
                ->addSelect('t')
                ->leftJoin('r.societe', 's')
                ->addSelect('s')
                ->leftJoin('r.depot', 'd')
                ->addSelect('d')
                ->leftJoin('r.adresse', 'a')
                ->addSelect('a')
                ->where('r.depot = :depot')
                ->setParameter('depot', $idDepot)
//                ->andWhere('r.dateDemar = :date')
//                ->setParameter('date',$date)
                ->andWhere('r.dateDemar >= :date_min')
                ->setParameter('date_min',$dateMin)
                ->andWhere('r.dateDemar <= :date_max')
                ->setParameter('date_max',$dateMax)
                ;
        if ($idSociety != 0) {
            $qb->andWhere('r.societe= :society')
                    ->setParameter('society', $idSociety);
        }
        if ($aReperageId != false) {
            $qb->andWhere('r.id IN(:reperageId)')
                    ->setParameter('reperageId', $aReperageId);
        }

        if ($filter) {
            if ($filter == 2) {
                $qb->andWhere('r.topage IS NULL');
            }
            else if ($filter == 'A' OR $filter == 'B' OR $filter == 'C') {
                $qb->andWhere('r.topage = :topage')
                ->setParameter('topage', $filter);
            }
            else if ($filter == 1){
                $qb->andWhere('r.topage = :val_1 OR r.topage = :val_2 OR r.topage = :val_3 ')
                ->setParameter('val_1', 'A')
                ->setParameter('val_2', 'B')
                ->setParameter('val_3', 'C');
            }
        }

        $qb->orderBy('t.code', 'ASC');
        return $qb->getQuery()->getResult();
    }

    public function geMaxDate() {

        $query = $this->createQueryBuilder('r');
        $query->select('r, MAX(r.dateDemar) AS max_date');

        $date = $query->getQuery()->getResult();
        return $this->transformDateToDataBaseFormat($date[0]['max_date']);
    }

    public function geMinDate() {

        $query = $this->createQueryBuilder('r');
        $query->select('r, Min(r.dateDemar) AS min_date');

        $date = $query->getQuery()->getResult();
        return $this->transformDateToDataBaseFormat($date[0]['min_date']);
    }

    public function getReperageQualif() {
        $qb = $this->createQueryBuilder('r')
                ->leftJoin('r.topage', 'rq')
                ->addSelect('rq')
        ;

        return $qb->getQuery()->getResult();
    }

    private function transformDateToDataBaseFormat($date) {
        $date = str_replace('-', '/', $date);
        $dateItems = explode('/', $date);

        return $dateInDataBaseFormat = $dateItems[2] . '/' . $dateItems[1] . '/' . $dateItems[0];
    }

    private function transformDateToDataBaseFormatReverse($date) {
        $dateItems = explode('/', $date);
        return $dateItems[2] . '-' . $dateItems[1] . '-' . $dateItems[0];
    }

    private function topageFilter($filter) {
        switch ($filter) {
            case 'A':
                return 'A';
                break;
            case 'B':
                return 'B';
                break;
            case 'C':
                return 'C';
                break;
            case 0:
                return false;
                break;
            case 1:
                return 'topes';
                break;
            case 2:
                return 'aucun';
                break;
        }
    }

    /**
     * [getReperageRespenseToExport récupération des données :réponse au répérage sur j -10 pour l'exporter vers Jade]
     * @return [type] [description]
     */
    public function getReperageRespenseToExport() {
        $sql = "SELECT  DISTINCT /*s.id as societe_id,*/
                        rep.id as rep_id,
                        rep.num_parution as num_parution,
                        rep.date_demar   as date_parution,
                        numabo_ext  as num_abonne,
                        societe_id as societe_id,
                        vol1  as volet1,
                        vol2  as volet2,
                        vol3  as volet3,
                        vol4  as volet4,
                        vol5  as volet5,
                        rep.cp    as code_postal,
                        ville  as ville,
                        type_portage as type_de_portage,
                        soc_code_ext as code_societe,
                        prd_code_ext  as  code_titre,
                        spr_code_ext  as num_edition,
                        qte  as nb_exemplaire,
                        rep.topage  as topage,
                        divers1 as divers,
                        info_comp1 as digicode,
                       info_comp2 as consigne_de_portage,
                        divers2 as msg_pour_porteur,
                        cm.insee as insee,
                        repq.id as qualif_id,
                        rep_id_ext as id_demande_jade,
                        REPLACE(REPLACE(REPLACE(REPLACE(cmt_reponse, '\t', ''), '\r', ''), 'n', ''),'|','') as commentaire_reponse 
            
                FROM reperage rep 
                    LEFT JOIN commune 
                        cm ON cm.id = rep.commune_id 
                    LEFT JOIN reperage_qualif 
                         repq ON repq.id = rep.qualif_id
                WHERE rep.date_reponse is not null
                AND   rep.date_export is null 
                AND rep.topage IS NOT NULL
               /* AND  rep.date_demar > current_date() - 40*/
                 ORDER BY rep.societe_id";

        return $this->_em->getConnection()->fetchAll($sql);
    }

    /**
     * Transfert des donnees de la table temporaire reperage vers la table Reperage
     * 
     * On n'integre que les reperages 
     * Il y a 2 cas de reperages :
     *  - reperages de nouveaux clients
     *  - reperages de clients connus 
     *      . mais avec changement d'adresse et/ou info portage
     *      . sans changement d'adresse mais avec changement de date de 1er service
     * 
     * @param type $ficRecapId
     * @throws ReperageSQLException
     */
    public function tmpVersReperage($ficRecapId) {
        try {
            // creation table temporaire qui sert pour accueillir les derniers reperages deja dans M-ROAD des clients presents dans le fichier courant
            $createTblTmp   = " CREATE TEMPORARY TABLE IF NOT EXISTS reperage_ancien_tmp (
                                    id_reperage int(11) NOT NULL,
                                    date_demar date NOT NULL,
                                    adresse_id int(11) DEFAULT NULL,
                                    abonne_soc_id int(11) DEFAULT NULL,
                                    numabo_ext varchar(20) COLLATE utf8_unicode_ci NOT NULL,
                                    vol1 varchar(38) COLLATE utf8_unicode_ci DEFAULT NULL,
                                    vol2 varchar(38) COLLATE utf8_unicode_ci DEFAULT NULL,
                                    vol3 varchar(38) COLLATE utf8_unicode_ci DEFAULT NULL,
                                    vol4 varchar(38) COLLATE utf8_unicode_ci DEFAULT NULL,
                                    vol5 varchar(38) COLLATE utf8_unicode_ci DEFAULT NULL,
                                    cp varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
                                    ville varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
                                    date_export datetime DEFAULT NULL,
                                    PRIMARY KEY (id_reperage)
                                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
                                ";
            $this->_em->getConnection()->prepare($createTblTmp)->execute();
            
            
            // Remplissage de la table temporaire des derniers reperages deja dans M-ROAD des clients presents dans le fichier courant
            // On ne prend que le dernier reperage d un client
            $insertTblTmp   = " INSERT INTO reperage_ancien_tmp
                                    (id_reperage, date_demar, adresse_id, abonne_soc_id, numabo_ext, vol1, vol2, vol3, vol4, vol5, cp, ville, date_export)
                                SELECT
                                    MAX(r.id) AS id_reperage, r.date_demar, r.adresse_id, r.abonne_soc_id, r.numabo_ext, r.vol1, r.vol2, r.vol3, r.vol4, r.vol5, r.cp, r.ville, r.date_export
                                FROM
                                    reperage_tmp t
                                    INNER JOIN reperage r ON t.abonne_soc_id = r.abonne_soc_id
                                WHERE
                                    r.id IS NOT NULL
                                GROUP BY
                                    r.date_demar, r.abonne_soc_id
                                ";
            $this->_em->getConnection()->prepare($insertTblTmp)->execute();
            
            
            // Mise a jour 'type_reperage' pour Nouveau client => insert 
            $update = " UPDATE
                            reperage_tmp t
                            LEFT JOIN reperage_ancien_tmp r ON t.abonne_soc_id = r.abonne_soc_id 
                        SET
                            t.type_reperage = 'N'
                        WHERE
                            t.type_reperage IS NULL
                            AND t.adresse_id IS NOT NULL
                            AND r.id_reperage IS NULL
                        ";
            $this->_em->getConnection()->prepare($update)->execute();
            
            
            // Mise a jour 'type_reperage' pour Client connu & Reponse non exporte => update 
            $update = " UPDATE
                            reperage_tmp t
                            , reperage_ancien_tmp r  
                        SET
                            t.type_reperage = '0R'
                            , t.id_reperage = r.id_reperage
                        WHERE
                            t.abonne_soc_id = r.abonne_soc_id
                            AND t.type_reperage IS NULL
                            AND t.adresse_id IS NOT NULL
                            AND r.date_export IS NULL
                        ";
            $this->_em->getConnection()->prepare($update)->execute();
            
            
            // Mise a jour 'type_reperage' pour Client connu & Reponse deja exporte & pas de changement adresse => mise a jour date demarrage si necessaire 
            $update = " UPDATE
                            reperage_tmp t
                            INNER JOIN reperage_ancien_tmp r ON t.abonne_soc_id = r.abonne_soc_id AND t.adresse_id=r.adresse_id
                        SET
                            t.type_reperage = 'C0'
                            , t.id_reperage = r.id_reperage
                        WHERE
                            t.type_reperage IS NULL
                            AND t.adresse_id IS NOT NULL
                            AND r.date_export IS NOT NULL
                        ";
            $this->_em->getConnection()->prepare($update)->execute();
            
            
            // Mise a jour 'type_reperage' pour Client connu & Reponse deja exporte & avec changement adresse => insert
            $update = " UPDATE
                            reperage_tmp t
                            INNER JOIN reperage_ancien_tmp r ON t.abonne_soc_id = r.abonne_soc_id 
                        SET
                            t.type_reperage = 'C1'
                        WHERE
                            t.type_reperage IS NULL
                            AND r.date_export IS NOT NULL
                            AND t.adresse_id IS NOT NULL AND r.adresse_id IS NOT NULL
                            AND t.adresse_id <> r.adresse_id
                        ";
            $this->_em->getConnection()->prepare($update)->execute();
            
            
            // Suppression de la table temporaire
            $drop = " DROP TABLE reperage_ancien_tmp ";
            $this->_em->getConnection()->prepare($drop)->execute();
            
            
            
            
            
            
            // ----- Insertion
            $insertTblTmp= "    INSERT INTO reperage
                                    (fic_recap1_id, fic_recap_n_id, commune_id, depot_id, abonne_soc_id, 
                                    adresse_id, rnvp_id, point_livraison_id, tournee_id, societe_id, 
                                    produit_id, date_demar, num_parution, numabo_ext, 
                                    vol1, vol2, vol3, vol4, vol5, cp, ville,  
                                    rep_id_ext, soc_code_ext, prd_code_ext, spr_code_ext, type_portage, 
                                    qte, divers1, info_comp1, info_comp2, divers2, client_type, date_creation)
                                SELECT
                                    t.fic_recap1_id, t.fic_recap_n_id, t.commune_id, t.depot_id, t.abonne_soc_id, 
                                    t.adresse_id, t.rnvp_id, t.point_livraison_id, t.tournee_id, t.societe_id, 
                                    t.produit_id, t.date_demar, t.num_parution, t.numabo_ext, 
                                    t.vol1, t.vol2, t.vol3, t.vol4, t.vol5, t.cp, t.ville,  
                                    t.rep_id_ext, t.soc_code_ext, t.prd_code_ext, t.spr_code_ext, t.type_portage, 
                                    t.qte, t.divers1, t.info_comp1, t.info_comp2, t.divers2, t.client_type, NOW() as date_creation
                                FROM
                                    reperage_tmp t
                                WHERE
                                    t.type_reperage IN ('N', 'C1')
                                ";
            $this->_em->getConnection()->prepare($insertTblTmp)->execute();
            
            // Mise a jour de tous les champs pour les clients connus et qu'aucune reponse n'est pas encore exportee
            $update = " UPDATE
                            reperage_tmp t
                            LEFT JOIN reperage r ON t.id_reperage=r.id
                        SET
                            r.fic_recap_n_id = t.fic_recap_n_id
                            , r.commune_id = t.commune_id
                            , r.depot_id = t.depot_id
                            , r.abonne_soc_id = t.abonne_soc_id
                            , r.adresse_id = t.adresse_id
                            , r.rnvp_id = t.rnvp_id
                            , r.point_livraison_id = t.point_livraison_id
                            , r.tournee_id = t.tournee_id 
                            , r.societe_id = t.societe_id
                            , r.produit_id = t.produit_id
                            , r.date_demar = t.date_demar
                            , r.num_parution = t.num_parution
                            , r.numabo_ext = t.numabo_ext
                            , r.vol1 = t.vol1
                            , r.vol2 = t.vol2
                            , r.vol3 = t.vol3
                            , r.vol4 = t.vol4
                            , r.vol5 = t.vol5
                            , r.cp = t.cp
                            , r.ville = t.ville
                            , r.rep_id_ext = t.rep_id_ext
                            , r.soc_code_ext = t.soc_code_ext
                            , r.prd_code_ext = t.prd_code_ext
                            , r.spr_code_ext = t.spr_code_ext
                            , r.type_portage = t.type_portage
                            , r.qte = t.qte
                            , r.divers1 = t.divers1
                            , r.info_comp1 = t.info_comp1
                            , r.info_comp2 = t.info_comp2
                            , r.divers2 = t.divers2
                            , r.client_type = t.client_type 
                        WHERE
                            t.type_reperage IN ('0R')
                            AND r.id IS NOT NULL
                        ";
            $this->_em->getConnection()->prepare($update)->execute();
            
            
            // Mise a jour de la date de demarrage pour les clients connus dont une reponse au reperage est dejaexportee & qui ne connait pas de changement d'adresse
            $update= "  UPDATE
                            reperage_tmp t
                            LEFT JOIN reperage r ON t.id_reperage=r.id
                        SET
                            r.fic_recap_n_id = t.fic_recap_n_id
                            , r.date_demar = t.date_demar 
                            , r.tournee_id = t.tournee_id
                        WHERE
                            t.type_reperage IN ('C0')
                            AND r.id IS NOT NULL
                        ";
            $this->_em->getConnection()->prepare($update)->execute();
            
            
            
            // Suppression des tournees incoherentes
            $update= "  UPDATE
                            reperage r 
                            INNER JOIN modele_tournee mt ON r.tournee_id=mt.id 
                            LEFT JOIN repar_glob rg ON rg.commune_id = r.commune_id AND (CURDATE() BETWEEN rg.date_debut AND rg.date_fin OR (CURDATE() >= rg.date_debut AND rg.date_fin IS NULL))
                            LEFT JOIN repar_soc rs ON r.societe_id = rs.societe_id AND rs.commune_id = r.commune_id AND (CURDATE() BETWEEN rs.date_debut AND rs.date_fin OR (CURDATE() >= rs.date_debut AND rs.date_fin IS NULL))
                            LEFT JOIN ref_flux rf1 ON rg.flux_id = rf1.id
                            LEFT JOIN ref_flux rf2 ON rs.flux_id = rf2.id
                        SET
                            r.tournee_id = NULL
                        WHERE 
                            1 = 1
                            AND 'KO' = IF ( ((rf2.code IS NOT NULL AND SUBSTRING(mt.code, 4, 1) = rf2.code) OR (rf2.code IS NULL AND rf1.code IS NOT NULL AND SUBSTRING(mt.code, 4, 1) = rf1.code) OR (rf2.code IS NULL AND rf1.code IS NULL)), 'OK', 'KO' )
                        ";
            $this->_em->getConnection()->prepare($update)->execute();
            
            
            
            
            /*
            $sInsert = "INSERT INTO reperage"
                    . " (fic_recap1_id, fic_recap_n_id, commune_id, depot_id, abonne_soc_id, adresse_id, rnvp_id, point_livraison_id,
                        societe_id, produit_id, date_demar, num_parution, numabo_ext, vol1, vol2, vol3, vol4, vol5, 
                        cp, ville,  rep_id_ext, soc_code_ext, prd_code_ext, spr_code_ext, type_portage, qte, divers1,
                        info_comp1, info_comp2, divers2, client_type, topage)"
                    . "SELECT "
                    . "tmp.fic_recap1_id, tmp.fic_recap_n_id, tmp.commune_id, tmp.depot_id, tmp.abonne_soc_id, tmp.adresse_id, tmp.rnvp_id, tmp.point_livraison_id,
                        tmp.societe_id, tmp.produit_id, tmp.date_demar, tmp.num_parution, tmp.numabo_ext, tmp.vol1, tmp.vol2, tmp.vol3, tmp.vol4, tmp.vol5, 
                        tmp.cp, tmp.ville,  tmp.rep_id_ext, tmp.soc_code_ext, tmp.prd_code_ext, tmp.spr_code_ext, tmp.type_portage, tmp.qte, tmp.divers1,
                        tmp.info_comp1, tmp.info_comp2, tmp.divers2, tmp.client_type,tmp.topage"
                    . "     FROM reperage_tmp tmp"
                    // . "         LEFT JOIN reperage rep ON tmp.numabo_ext = rep.numabo_ext AND tmp.soc_code_ext = rep.soc_code_ext"
                    . "         LEFT JOIN produit prd ON tmp.produit_id = prd.id "
                    . " WHERE "
                    . "     tmp.fic_recap_n_id IS NULL 
                            AND  tmp.numabo_ext not in (SELECT numabo_ext FROM reperage r where r.soc_code_ext = tmp.soc_code_ext) ";

            $this->_em->getConnection()->prepare($sInsert)->execute();
            */
        } catch (DBALException $ex) {
            throw ReperageSQLException::transfertReperage($ficRecapId, $ex->getMessage());
        }
    }
    
    /**
     * Les reperages non classes
     * @param integer $iNbMaxATraiter
     * @param array $dates1erService tableau de dates en \Datetime 
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function reperagesNonClasses($iNbMaxATraiter=100, $dates1erService=array())
    {
        try {
            $sSlct  = " SELECT DISTINCT 
                            DATE_FORMAT(date_demar, '%Y/%m/%d') AS jour, DATE_FORMAT(date_demar, '%w')+1 AS id_jour, c.insee
                            , r.abonne_soc_id, r.point_livraison_id, ar.geox, ar.geoy, p.flux_id, r.numabo_ext, r.soc_code_ext
                            , p.prd_code_ext, r.depot_id, 1 AS reperage
                        FROM 
                            reperage r
                            LEFT JOIN adresse_rnvp ar ON r.point_livraison_id = ar.id
                            LEFT JOIN produit p ON r.produit_id = p.id
                            LEFT JOIN commune c ON r.commune_id = c.id
                        WHERE
                            1 = 1
                            ";
            if(empty($date1erService))
            {
                $sSlct  .= " AND r.date_demar > current_date() ";
            }
            else
            {
                $dates = array();
                foreach($dates1erService as $date)
                {
                    $dates[]   = $date->format("Y-m-d");
                }
                $sSlct  .= " AND r.date_demar IN ('".implode("', '", $dates)."') ";
            }
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
     * Mise a jour tournee
     * @param array $dates1erService tableau de dates en \Datetime 
     * @throws \Doctrine\DBAL\DBALException
     */
    public function miseAJourTournee($dates1erService=array())
    {
        try {
            $update = " UPDATE
                            reperage r
                            LEFT JOIN tournee_detail td ON r.abonne_soc_id = td.num_abonne_id AND td.reperage = 1
                            LEFT JOIN modele_tournee_jour mtj ON td.modele_tournee_jour_code = mtj.code AND r.date_demar BETWEEN mtj.date_debut AND mtj.date_fin
                            LEFT JOIN modele_tournee mt ON mtj.tournee_id = mt.id AND mt.actif = 1
                            LEFT JOIN commune c ON r.commune_id = c.id AND c.insee = td.insee
                        SET
                            r.tournee_id = mt.id
                        WHERE
                            1 = 1
                            ";
            if(empty($date1erService))
            {
                $update  .= " AND r.date_demar > current_date() ";
            }
            else
            {
                $dates = array();
                foreach($dates1erService as $date)
                {
                    $dates[]   = $date->format("Y-m-d");
                }
                $update  .= " AND r.date_demar IN ('".implode("', '", $dates)."') ";
            }
            $update  .= " 
                            AND td.id IS NOT NULL
                            AND r.tournee_id IS NULL
                            AND mt.id IS NOT NULL
                            AND c.id IS NOT NULL
                     ";
            $this->_em->getConnection()->prepare($update)->execute();
        } catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Retourne les points candidats les plus proches selon les criteres definis par $aCritere 
     * 
     * @param array $aCritere
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function ptsCandidatsProches($aCritere=array())
    {
        $aPtsCandidats = array();
        try {
            $sSlct  = " SELECT DISTINCT 
                            MAX(td.id) AS id, td.longitude, td.latitude
                        FROM 
                            tournee_detail td
                        WHERE
                            1 = 1
                        AND 
                            td.reperage <> 1
                        ";
            if(isset($aCritere['insee'])) // si insee defini, on se limite par rapport a la ville
            {
                $sSlct  .= " AND td.insee = '".$aCritere['insee']."' ";
            }
            if(isset($aCritere['jour_id'])) // si id_jour defini, on se limite par au jour
            {
                $sSlct  .= " AND td.jour_id = '".$aCritere['jour_id']."' ";
            }
            $sSlct  .= " GROUP BY td.longitude, td.latitude ";
            $aRes   = $this->_em->getConnection()->executeQuery($sSlct)->fetchAll();
            foreach($aRes as $aArr)
            {
                $aPtsCandidats[]    = array(
					'id' => $aArr['id'],
					'x'  => $aArr['longitude'],
					'y'  => $aArr['latitude']
                                    );
            }
            return $aPtsCandidats;
        } catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    
    
    public function classementAuto($srv_ams_carto_geoservice)
    {
        try {
            // Mise a jour tournee si tournee connue dans tournee_detail quelque soit le jour de 1er demarrage
            $this->miseAJourTournee();
            
            $repoTourneeDetail  = $this->_em->getRepository('AmsAdresseBundle:TourneeDetail');
            $aOptionsSearchAround   = array('Projection' => 'WGS84');
            // !!!!!!! A voir, adresses classes pour un autre jour dans tournee_detail
            
            $aReperagesNonClasses = $this->reperagesNonClasses(); // Les reperages non classes
            $iI=0;
            foreach($aReperagesNonClasses as $data)
            {
                echo "\n".date("d/m/Y H:i:s")." - Insertion automatique dans tournee_detail : "."id_jour:".$data['id_jour']."   INSEE:".$data['insee']."   numabo_ext:".$data['numabo_ext']."   soc_code_ext:".$data['soc_code_ext']."   longitude:".$data['geox']."   latitude:".$data['geoy']."\n";
                //DATE_FORMAT(date_demar, '%Y/%m/%d') AS jour, DATE_FORMAT(date_demar, '%w')+1 AS id_jour, c.insee, r.abonne_soc_id, ar.geox, ar.geoy, p.flux_id, r.numabo_ext, r.soc_code_ext, p.prd_code_ext
                // Les pts candidats les plus proches
                $aCriteresPtsCandidatsProches   = array();
                $aCriteresPtsCandidatsProches['insee']  = $data['insee'];
                $aCriteresPtsCandidatsProches['id_jour']  = $data['id_jour'];
                
                $aPtsCandidatsProches   = $this->ptsCandidatsProches($aCriteresPtsCandidatsProches);
                
                $aPtAClasser    = array(
                                    'id'    => 'target',
                                    'x'     => $data['geox'],
                                    'y'     => $data['geoy'],
                                ); 
                $oMinDistance   = "";
                
                if(!empty($aPtsCandidatsProches))
                {
                    /**  DETERMINATION DU POINT LE PLUS PROCHE **/
                    $classement = $srv_ams_carto_geoservice->callSearchAround($aPtAClasser, $aPtsCandidatsProches, $aOptionsSearchAround);
                    //$oMinDistance = $classement->SearchAroundResult[0];
                    if(!is_array($classement->SearchAroundResult))
                    {
                        $oMinDistance   = $classement->SearchAroundResult;
                    }
                    else
                    {
                        foreach($classement->SearchAroundResult as $result)
                        {
                            if($result->Distance!=-1)
                            {
                                if($oMinDistance=="")
                                {
                                    $oMinDistance = $result; // Le point le plus proche est le premier point du $classement->SearchAroundResult
                                    break;                            
                                }
                            }
                        }
                    }
                }
                
                if($oMinDistance!="")
                {
                    // Tournee et ordre du le plus proche
                    $sSlct  = " SELECT 
                                    td.ordre, td.modele_tournee_jour_code, mtj.id AS tournee_jour_id, mt.id AS tournee_id, mt.code AS tournee_code
                                    , DATE_FORMAT(debut_plage_horaire, '%H:%i:%s') AS debut_plage_horaire
                                    , DATE_FORMAT(fin_plage_horaire, '%H:%i:%s') AS fin_plage_horaire
                                    , DATE_FORMAT(duree_viste_fixe, '%H:%i:%s') AS duree_viste_fixe
                                FROM
                                   tournee_detail td
                                   LEFT JOIN modele_tournee_jour mtj ON td.modele_tournee_jour_code = mtj.code
                                   LEFT JOIN modele_tournee mt ON mtj.tournee_id = mt.id
                                WHERE 
                                    td.id = '".$oMinDistance->Id."' ";
                    $aRes   = $this->_em->getConnection()->executeQuery($sSlct)->fetchAll();
                    foreach($aRes as $aArr)
                    {
                        $ordre  = $aArr['ordre'];
                        $modele_tournee_jour_code  = $aArr['modele_tournee_jour_code'];
                        $tournee_jour_id  = $aArr['tournee_jour_id'];
                        $tournee_code  = $aArr['tournee_code'];
                        
                        //echo "\n";print_r($oMinDistance);
                        //echo "\nordre : "."     modele_tournee_jour_code : $modele_tournee_jour_code      tournee_code : $tournee_code    \n";
                        //echo "\nAvant recup contenu tournee : ".date("H:i:s")."\n";
                        $oTournee = $repoTourneeDetail->findTourneeIdByTourneeCode($modele_tournee_jour_code);
                        
                        //echo "\nAvant recup detail tournee 1 : ".date("H:i:s")."\n";
                        // Duree tournee $modele_tournee_jour_code si on met le nouveau point devant le point identifie dans $oMinDistance
                        $param  = array('Before' => $oMinDistance->Id, 'Longitude'=> $data['geox'], 'Latitude' => $data['geoy']);
                        $geoservice_time_1= $srv_ams_carto_geoservice->callRouteService($oTournee, $param);
                        
                        //echo "\nAvant recup detail tournee 2 : ".date("H:i:s")."\n";
                        // Duree tournee $modele_tournee_jour_code si on met le nouveau point devant le point identifie dans $oMinDistance
                        $param  = array('After' => $oMinDistance->Id, 'Longitude'=> $data['geox'], 'Latitude' => $data['geoy']);
                        $geoservice_time_2= $srv_ams_carto_geoservice->callRouteService($oTournee, $param);
                        
                        //echo "\nAvant recup nb pts et tps tournee : ".date("H:i:s")."\n";
                        //print_r($geoservice_time_1);
                        //print_r($geoservice_time_2);
                        
                        $min = array($geoservice_time_1->ROUTE->Time,$geoservice_time_2->ROUTE->Time);
                        $key = array_keys($min,min($min));
                        $geoservice = ($key[0] == 0)? $geoservice_time_1 : $geoservice_time_2;
                        $ordre = ($key[0] == 0)? $ordre : $ordre + 1;

                        $nb_stop = count($geoservice->ROUTE->WAYPOINT);
                        $sTourneeTime = $geoservice->ROUTE->WAYPOINT[($nb_stop -1)]->FoundTime;
                        
                        
                        
                        // Mise a jour de l'ordre de la tournee ou est insere le nouveau point
                        $repoTourneeDetail->UpOrderCascade($ordre, $modele_tournee_jour_code);
                        
                        
                        // Inserer une nouvelle ligne de tournee_detail
                        $aInsertTourneeDetail = array(
                                                    'ordre'                 => $ordre,
                                                    'longitude'             => $data['geox'],
                                                    'latitude'              => $data['geoy'],
                                                    'debut_plage_horaire'   => $aArr['debut_plage_horaire'],
                                                    'fin_plage_horaire'     => $aArr['fin_plage_horaire'],
                                                    'duree_viste_fixe'      => $aArr['duree_viste_fixe'],
                                                    'modele_tournee_jour_code'  => $modele_tournee_jour_code,
                                                    'num_abonne_soc'        => $data['numabo_ext'],
                                                    'num_abonne_id'         => $data['abonne_soc_id'],
                                                    'soc'                   => $data['soc_code_ext'],
                                                    'titre'                   => $data['prd_code_ext'],
                                                    'insee'                 => $data['insee'],
                                                    'flux_id'               => $data['flux_id'],
                                                    'jour_id'               => $data['id_jour'],
                                                    'reperage'              => 1,
                                                );
                        if(isset($data['jour'])) $aInsertTourneeDetail['jour'] = $data['jour'];
                        $repoTourneeDetail->insertTourneeDetail($aInsertTourneeDetail);
                        
                    }
                    
                }
                echo "\n".date("d/m/Y H:i:s")." - Fin Insertion automatique dans tournee_detail : "."id_jour:".$data['id_jour']."   INSEE:".$data['insee']."   numabo_ext:".$data['numabo_ext']."   soc_code_ext:".$data['soc_code_ext']."   longitude:".$data['geox']."   latitude:".$data['geoy']."\n";
                
                $iI++;
                if($iI==100)
                {
                    //die("\nFin Traitement de $iI adresses : ".date("d/m/Y H:i:s")."\n");
                }
            }
            
            // Mise a jour tournee si tournee connue dans tournee_detail quelque soit le jour de 1er demarrage
            $this->miseAJourTournee();
            
        } catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    public function UpdatePointLivraisonScriptRepair($iPointLivraisonId,$sId)
    {
        try {
            $slct   = " UPDATE reperage
                        SET 
                            point_livraison_id = $iPointLivraisonId 
                        WHERE date_demar > CURDATE() - INTERVAL 30 DAY 
                        AND rnvp_id IN($sId)
                    ";
            $this->_em->getConnection()->prepare($slct)->execute();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    public function getDateDistribution($depotId, $societeId) {
       try {
            $slct   = " SELECT casl.date_distrib FROM reperage r
                        LEFT JOIN client_a_servir_logist casl ON r.abonne_soc_id = casl.abonne_soc_id
                        WHERE r.depot_id = '".$depotId."'
                    ";
            $this->_em->getConnection()->prepare($slct)->execute();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
     }
    
    /**
     * Stockage des Infos portage pour les topes "A" lors de l'export vers JADE
     * @param integer $reperageId
     * @param string $date_fin
     * @throws DBALException
     */
    public function integrationInfoPortage($reperageId, $date_fin="2078-12-31") {
       try {
            $iDecalageJour   = 2; // Par rapport a aujourd'hui, jour de debut d'application de l'info portage Reperage
            
            $sCodeInfoReper = 'INFO_REPER';
            $repoInfoPortage  = $this->_em->getRepository('AmsDistributionBundle:InfoPortage');
            $repoTypeInfoPortage  = $this->_em->getRepository('AmsDistributionBundle:TypeInfoPortage');
            $oTypeInfoPortageReperage   = $repoTypeInfoPortage->findOneByCode($sCodeInfoReper);
            $repoAbonneSoc  = $this->_em->getRepository('AmsAbonneBundle:AbonneSoc');
            $repoUtilisateur  = $this->_em->getRepository('AmsSilogBundle:Utilisateur');
            
            $dateCourant    = new \Datetime();
            $dateDebut      = $dateCourant->add(new \DateInterval('P'.$iDecalageJour.'D'));
            $dateFin    = new \DateTime($date_fin);
            
            // Fermer a partir de (reperage.date_demar-1) l'info portage Reperage de l'abonne en cours
            $sUpdate    = " UPDATE
                                reperage r
                                INNER JOIN infos_portages_abonnes ipa ON r.abonne_soc_id = ipa.abonne_id AND r.id = ".$reperageId." 
                                INNER JOIN info_portage ip ON ipa.info_portage_id = ip.id
                                INNER JOIN type_info_portage tip ON ip.type_info_id = tip.id AND tip.code = '".$sCodeInfoReper."' 
                            SET
                                ip.date_fin = DATE_ADD(r.date_demar, INTERVAL -1 day)
                                , ip.date_modif = NOW(), ip.active = 1
                                
                            WHERE
                                r.date_demar BETWEEN ip.date_debut AND ip.date_fin ";
            $this->_em->getConnection()->prepare($sUpdate)->execute();
            
            // Pour les topes "A", stocker le commentaire de reponse comme "info portage reperage"
            $slctInfo   = " SELECT
                                abonne_soc_id, cmt_reponse, utl_reponse_id
                            FROM
                                reperage
                            WHERE
                                topage = 'A'
                                AND cmt_reponse <> '' AND cmt_reponse IS NOT NULL
                                AND id = ".$reperageId." ";
            $aRes   = $this->_em->getConnection()->executeQuery($slctInfo)->fetchAll();
            foreach($aRes as $aArr)
            {
                // Si le champs commentaire est renseigne, on le stocke comme une nouvelle info portage
                
                // Fermer les anciennes info portage Reperage de l'abonne courant
                $sUpdate    = " UPDATE
                                    infos_portages_abonnes ipa
                                    INNER JOIN info_portage ip ON ipa.info_portage_id = ip.id AND ipa.abonne_id = ".$aArr['abonne_soc_id']." 
                                    INNER JOIN type_info_portage tip ON ip.type_info_id = tip.id AND tip.code = '".$sCodeInfoReper."' 
                                SET
                                    ip.date_fin = DATE_ADD(CURDATE(), INTERVAL ".($iDecalageJour-1)." day)
                                    , ip.date_modif = NOW(), ip.active = 1
                                WHERE
                                    DATE_ADD(CURDATE(), INTERVAL ".$iDecalageJour." day) BETWEEN ip.date_debut AND ip.date_fin ";
                $this->_em->getConnection()->prepare($sUpdate)->execute();
                
                // Inserer la nouvelle info portage                
                if(!is_null($oTypeInfoPortageReperage))
                {
                    $oInfoPortage = new InfoPortage();
                    $oInfoPortage->addAbonne($repoAbonneSoc->find($aArr['abonne_soc_id']));
                    $oInfoPortage->setTypeInfoPortage($oTypeInfoPortageReperage);
                    if($aArr['utl_reponse_id'])
                    {
                        $oInfoPortage->setUtilisateur($repoUtilisateur->find($aArr['utl_reponse_id']));
                    }
                    $oInfoPortage->setValeur($aArr['cmt_reponse']);
                    $oInfoPortage->setDateDebut($dateDebut);
                    $oInfoPortage->setDateFin($dateFin);
                    $oInfoPortage->setDateModif(new \DateTime());
                    $oInfoPortage->setOrigine(0);
                    
                    $this->_em->persist($oInfoPortage);
                    $this->_em->flush();
                }                
            }
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Mise a jour de la date de l'adresse
     * @throws DBALException
     */
    public function majDateFinAdresse() 
    {
       try {
            $iJourAMAJ   = '-15'; // Par rapport a aujourd'hui, nom de jour MAX de reference dont la date fin de l'adresse est a mettre a jour
            
            // Fermer a partir de (reperage.date_demar-1) l'info portage Reperage de l'abonne en cours
            $sUpdate    = " UPDATE 
                                reperage r
                                INNER JOIN adresse a ON r.adresse_id = a.id AND a.type_adresse = 'R' AND r.date_export IS NOT NULL AND r.date_export < CURDATE()
                            SET 
                                a.date_fin = IF(a.date_fin>r.date_export, DATE_FORMAT(r.date_export, '%Y-%m-%d'), a.date_fin)
                            WHERE
                                r.date_demar < DATE_ADD(CURDATE(), INTERVAL ".$iJourAMAJ." day) ";
            $this->_em->getConnection()->prepare($sUpdate)->execute();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }


    /**
     * 
     * @param type $sociteId
     * @param type $dateDebut
     * @param type $dateFin
     * @param type $depotId
     */
    public function getIndicateurReperage($sociteId, $dateDebut, $dateFin, $depotId) {
        
        $sql = "SELECT count(*) nb_reperage, date_format(f.date_traitement, '%Y-%m-%d') date_creation ,
                    DATEDIFF(date_format(r.date_reponse, '%Y-%m-%d'),date_format(f.date_traitement, '%Y-%m-%d') )  as diff,
                    CASE 
                        WHEN  DATEDIFF(date_format(r.date_reponse, '%Y-%m-%d'),date_format(f.date_traitement, '%Y-%m-%d') )  < 6 THEN 'J_J5'
                        WHEN  DATEDIFF(date_format(r.date_reponse, '%Y-%m-%d'),date_format(f.date_traitement, '%Y-%m-%d') )  IN (6,7,8) THEN 'J6_J8'
                        WHEN  DATEDIFF(date_format(r.date_reponse, '%Y-%m-%d'),date_format(f.date_traitement, '%Y-%m-%d') )  IN (9,10) THEN 'J9_J10'
                        WHEN  DATEDIFF(date_format(r.date_reponse, '%Y-%m-%d'),date_format(f.date_traitement, '%Y-%m-%d') )  > 10 THEN 'J10_'
                        ELSE 'NON_REPONDU'
                    END  as delai
                    FROM  reperage r INNER JOIN fic_recap f ON r.fic_recap1_id = f.id
                    WHERE  r.societe_id = 1 
                    AND f.date_traitement between '".$dateDebut."' AND '".$dateFin."'";
                        
                    if ($depotId > 0) {
                        $sql .= " AND depot_id = ".$depotId."
                                  GROUP BY f.date_traitement, delai, depot_id";
                    }else {
                        $sql .="    group by f.date_traitement, delai ";
                    }
                    
          return $this->_em->getConnection()->fetchAll($sql);
        
    }

}
