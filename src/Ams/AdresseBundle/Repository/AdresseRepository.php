<?php

namespace Ams\AdresseBundle\Repository;

use Ams\AbonneBundle\Entity\AbonneSoc;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityRepository;

class AdresseRepository extends EntityRepository {

    /**
     * 
     * @param type $depots liste des depots 
     * @return array liste des adresse rejeté accessible à l'utilisateur connecté
     */
    public function getListeRejets($depots = array()) {

        $sql = "SELECT
            adr.id as adresseId,
            adr.vol3,
            adr.vol4,
            adr.vol5,
            adr.cp,
            adr.ville,
            adr.commune_id,
            adr.adresse_rnvp_etat_id,
            rnvp.id as rnvp_id,
            dep.libelle as cd,
            if( adr.adresse_rnvp_etat_id > 2 , etat.libelle ,  'Rejet geocodage') as etat,
            if( adr.adresse_rnvp_etat_id > 2 , 'RNVP' ,  'GEO') as type_rejet
            FROM  
                adresse adr
            INNER JOIN
                adresse_rnvp rnvp ON rnvp.id = adr.rnvp_id
            INNER JOIN 
                 adresse_rnvp_etat etat ON adr.adresse_rnvp_etat_id = etat.id
            INNER JOIN 
                depot_commune dc ON dc.commune_id =  rnvp.commune_id
            INNER JOIN
                depot dep ON dc.depot_id = dep.id
            WHERE 
               (adr.adresse_rnvp_etat_id NOT IN (1,2) OR rnvp.geo_etat = 0) ";

        if (count($depots) > 0) {
            $sql .= " AND   dc.depot_id IN ( " . implode(',', $depots);
            $sql .= ") AND ( dc.date_fin IS NULL OR (adr.date_fin > CURDATE() AND CURDATE() between dc.date_debut AND dc.date_fin))";
        }
        
        $sql .= " GROUP BY  adr.vol3, adr.vol4, adr.vol5, adr.cp, adr.ville  ORDER BY depot_id, cp";

        return $this->_em->getConnection()->fetchAll($sql);
    }

    /**
     * Liste des  abonnés actives qui ont la même adresse .
     * On pourra retrouver un abonnee plusieur fois dans cette liste avec des
     * date de début et de fin différente
     * @param $adresse tableau avec les vol3 à vol5 cp et ville
     * 
     */
    public function getListeAbonneByAdresse($adresse = array('vol3' => '', 'vol4' => '', 'vol5' => '', 'cp' => '', 'ville' => '')) {

        $qb = $this->_em->createQueryBuilder()
                ->select('a')
                ->from('AmsAdresseBundle:Adresse', 'a')
                ->where('a.vol3 = :vol3 AND a.vol4 = :vol4 AND a.vol5 = :vol5 AND a.cp = :cp AND a.ville =:ville')
                ->andWhere('a.dateFin > :today')
                ->setParameters($adresse)
                ->setParameter(":today", new \DateTime());
        return $qb->getQuery()->getResult();
    }

    /**
     * Permet de récupérer une liste d'abonne en se basant 
     * sur une adresse editeur
     * 
     * $criteres est un tableau associatif dont 
     * les clefs sont des attributs de la table adresse_ext
     * @param Array $criteres Les critères de la table adresse
     * @param Array $exclus liste des critères à exclure
     * @param Array $casl_criteres Les critères de la table client_a_servir_logist
     * @return l'adresse de l'abonne et son point de livraison
     */
    public function getAdresseByCritere($criteres = array(), $exclus = array(), $casl_criteres = array(), $gpsCoords = false) {

        $andWhere = '';

        // Modifications liées au filtre sur la tournée.
        if (!empty($casl_criteres)) {
            foreach ($casl_criteres as $key => $value) {
                if ($value != '') {
                    $andWhere .=" AND casl." . $key . " = '" . $value . "'";
                }
            }

            // Suppression du critère sur le code postal
            if (isset($criteres['cp'])) {
                unset($criteres['cp']);
            }
        }

        if (!empty($criteres)) {
            foreach ($criteres as $key => $value) {
                if ($value != '')
                    $andWhere .=" AND adr." . $key . " = '" . $value . "'";
            }
        }

        if (!empty($exclus)) {
            foreach ($exclus as $key => $value) {
                $andWhere .=" AND adr." . $key . " <> '" . $value . "' ";
            }
        }

        $sql = " SELECT DISTINCT 
                    adr.id,
                    adr.vol1,
                    adr.vol2,
                    adr.vol3,
                    adr.vol4,
                    adr.vol5,
                    adr.cp,
                    adr.ville,
                    adr.date_debut,
                    adr.date_fin,
                    adr.point_livraison_id AS pointLivraisonId,
                    arnvp_pt.cadrs as pointLivraisonCadrs,
                    arnvp_pt.adresse as pointLivraisonAdresse,
                    arnvp_pt.lieudit as pointLivraisonLieuDit,
                    arnvp_pt.cp as pointLivraisonCp,
                    arnvp_pt.ville as pointLivraisonVille,
                    adr.rnvp_id,
                    abs.numabo_ext,
                    abs.id as abo_id,
                    arnvp.geox,
                    arnvp.geoy
                ";
        if ($gpsCoords)
            $sql .=",get_distance_kilometre(" . $gpsCoords['y'] . "," . $gpsCoords['x'] . ", arnvp.geoy, arnvp.geox) as distance_km ";
        $sql .="
             FROM  adresse adr
             LEFT JOIN abonne_soc abs ON adr.abonne_soc_id = abs.id
             LEFT JOIN adresse_rnvp arnvp ON adr.rnvp_id = arnvp.id
             LEFT JOIN adresse_rnvp arnvp_pt ON adr.point_livraison_id = arnvp_pt.id
             /*INNER JOIN client_a_servir_logist casl ON casl.adresse_id = adr.id AND casl.abonne_soc_id = abs.id*/
             WHERE adr.date_fin > CURDATE() 
        ";
        $sql .= $andWhere;
        if ($gpsCoords)
            $sql .=" ORDER BY distance_km ASC LIMIT 100";
        return $this->_em->getConnection()->fetchAll($sql);
    }

    /**
     * Permet de récupérer une liste d'abonne en se basant 
     * sur une adresse editeur
     * 
     * $criteres est un tableau associatif dont 
     * les clefs sont des attributs de la table adresse
     * @param Array $criteres
     * @param Array $exclus liste des critères à exclure
     * @return une liste d'abonne
     */
    public function rechercheAbonne($criteresAdresse = array(), $criteresAbonne = array(), $ville = null, $depot = null, $societe = null, $order = null) {

        $andWhere = '';

        foreach ($criteresAdresse as $key => $value) {
            if (trim($value) != '')
                $andWhere .=" AND adr." . $key . " LIKE'%" . str_replace("'", "%", $value) . "%'";
        }

        foreach ($criteresAbonne as $key => $value) {
            if (trim($value) != '')
                $andWhere .=" AND abs." . $key . " LIKE '%" . $value . "%' ";
        }

        if ($depot != null && $depot instanceof \Ams\SilogBundle\Entity\Depot) {
            $andWhere .=" AND dep.id = " . $depot->getId() . " ";
        }

        if ($ville != null && $ville instanceof \Ams\AdresseBundle\Entity\Commune) {
            $andWhere .=" AND adr.commune_id = " . $ville->getId() . " ";
        }

        $sql = " SELECT 
                    abs.id as abonne_id,
                    adr.id,
                    adr.vol1,
                    adr.vol2,
                    adr.vol3,
                    adr.vol4,
                    adr.vol5,
                    adr.cp,
                    adr.ville,
                    adr.adresse_rnvp_etat_id,
                    adr.date_debut,
                    adr.date_fin,
                    abs.numabo_ext,
                    adr.point_livraison_id as pointLivraisonId,
                    adrrnvp.cadrs as pointLivraisonCadrs,
                    adrrnvp.adresse as pointLivraisonAdresse,
                    adrrnvp.lieudit as pointLivraisonLieuDit,
                    adrrnvp.cp as pointLivraisonCp,
                    adrrnvp.ville as pointLivraisonVille,
                    soc.libelle as socLibelle,
                    dep.`libelle` AS depotLibelle,
                    if( adr.adresse_rnvp_etat_id > 2 , etat.libelle ,  'Rejet geocodage') as etat,
                    if( adr.adresse_rnvp_etat_id > 2 , 'RNVP' ,  'GEO') as type_rejet
                    FROM  
                        adresse adr
                    INNER JOIN
                        adresse_rnvp adrrnvp ON adrrnvp.id = adr.point_livraison_id
                    INNER JOIN 
                        adresse_rnvp_etat etat ON adr.adresse_rnvp_etat_id = etat.id
                    INNER JOIN 
                        abonne_soc abs ON adr.abonne_soc_id = abs.id
                    LEFT JOIN 
                        produit p ON p.soc_code_ext = abs.soc_code_ext 
                    INNER JOIN 
                        societe soc ON soc.id = p.societe_id 
                    INNER JOIN 
                        depot_commune dc ON dc.commune_id = adr.commune_id
                    LEFT JOIN 
                        depot dep ON dep.id = dc.depot_id
                    WHERE 
                        adr.date_fin > CURDATE() AND (adr.type_adresse IS NULL OR adr.type_adresse = 'L')
                    
        ";
        $sql .= $andWhere;
        if (!empty($societe)) {
            $sql .= " AND soc.id =$societe ";
        }
        $sql .= " GROUP BY adr.abonne_soc_id  ";
        if ($order == "pointLivraison") {
            $sql .= "ORDER BY adr.point_livraison_id ASC";
        } else {
            $sql .= "ORDER BY adr.id ASC";
        }


        // echo $sql;exit;

        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function updateRnvpId($utilisateurId, $rnvpId, $adresseId) {
        $q = "UPDATE adresse SET 
                    rnvp_id = '$rnvpId',utl_id_modif = '$utilisateurId'
                WHERE 
                    id =  '$adresseId'
            ";
        $this->_em->getConnection()->exec($q);
    }

    public function changePointLivraison($utilisateurId, $newpointLivraisonId, $oldPointLivraisonId) {

        $sql = "UPDATE  adresse SET 
                    point_livraison_id = $newpointLivraisonId,
                    utl_id_modif = $utilisateurId ,
                    date_modif = NOW()
                    WHERE point_livraison_id = $oldPointLivraisonId        
                 ";
        $this->_em->getConnection()->exec($sql);
    }

    /**
     * 
     * @param type $utilisateurId
     * @param type $pointLivraionId
     * @param type $rnvpIds tableau idRnvp
     */
    public function updatePointLivraison($utilisateurId, $pointLivraionId, $adresseIds = array()) {
        $sql = "UPDATE  adresse SET 
                    point_livraison_id = '" . $pointLivraionId . "',
                    utl_id_modif = '" . $utilisateurId . "',
                    date_modif = NOW()";

        $sql.=" WHERE id IN ( " . implode(',', $adresseIds) . ")          
                 ";
        $this->_em->getConnection()->exec($sql);

        /** avec la denormalisation tout update de point de livraison entraine une mise mise à jour du champ dans client_a_servir_logist */
        $updatClient = "UPDATE  client_a_servir_logist SET 
                    point_livraison_id = '" . $pointLivraionId . "'
                    WHERE 
                        adresse_id IN ( " . implode(',', $adresseIds) . ")   
                        AND date_distrib > NOW()
                 ";
        $this->_em->getConnection()->exec($updatClient);
    }

    /**
     * Mise a jour de la ligne de la table "adresse" identifiee par $id
     * @param integer $id
     * @param array $aInfoRNVP
     * @throws \Doctrine\DBAL\DBALException
     */
    public function updateRnvp($id, $aInfoRNVP) {
        try {
            $this->_em->getConnection()->update("adresse", $aInfoRNVP, array("id" => $id));
        } catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

    /**
     * 
     * 
     * @throws Doctrine\DBAL\DBALException
     */

    /**
     * Transfert des "adresse" qui ont deja ete normalisees vers la table temporaire adresse_tmp
     * $flux prend la valeur 'CAS' ou 'REPER'
     * 
     * @param string $flux
     * @throws \Doctrine\DBAL\DBALException
     */
    public function tmpAdresse($flux = 'CAS') {
        try {

            /*
              $sInsert = "INSERT INTO adresse_tmp "
              . " (id, rnvp_id, vol3, vol4, vol5, cp, ville, point_livraison_id, commune_id, adresse_rnvp_etat_id)"
              . " SELECT
              MAX(id) AS id, rnvp_id, vol3, vol4, vol5, cp, ville, point_livraison_id, commune_id, adresse_rnvp_etat_id
              FROM
              adresse
              WHERE
              rnvp_id IS NOT NULL
              ";
              switch($flux)
              {
              case "REPER":
              $sInsert .= " AND type_adresse = 'R' ";
              break;

              // Cas Clients a servir
              default :
              $sInsert .= " AND (type_adresse IS NULL OR type_adresse='L') ";
              break;
              }
              $sInsert .= "
              GROUP BY vol3, vol4, vol5, cp, ville ";

             */

            $sSQLDropTmpTable = " DROP TEMPORARY TABLE IF EXISTS adresse_tmp2; ";
            $sSQLCreateTmpTable = " 
                                    CREATE TEMPORARY TABLE adresse_tmp2 (
                                        id int(11) NULL,
                                        adresse_rnvp_etat_id int(11) NULL,
                                        vol3 VARCHAR(100) NULL,
                                        vol4 VARCHAR(100) NULL,
                                        vol5 VARCHAR(100) NULL,
                                        cp VARCHAR(5) NULL,
                                        ville VARCHAR(45) NULL,
                                        rnvp_id int(11) NULL,
                                        point_livraison_id int(11) NULL,
                                        commune_id int(11) NULL,

                                        INDEX IDX_TMP_ID (id), INDEX IDX_TMP_ETAT_ID (adresse_rnvp_etat_id), INDEX IDX_TMP_ADR_VOL (vol3, vol4, vol5, cp, ville)
                                    ); ";
            $sSQLInsertTmpTable = " 
                                    INSERT INTO adresse_tmp2 
                                        (id, rnvp_id, vol3, vol4, vol5, cp, ville, point_livraison_id, commune_id, adresse_rnvp_etat_id)
                                    SELECT
                                        id, rnvp_id, vol3, vol4, vol5, cp, ville, point_livraison_id, commune_id, adresse_rnvp_etat_id
                                    FROM adresse
                                    WHERE
                                        rnvp_id IS NOT NULL
                                    ORDER BY
                                        adresse_rnvp_etat_id ASC, id DESC; ";
            $sTruncate = " TRUNCATE TABLE adresse_tmp; ";
            $sInsert = " INSERT INTO adresse_tmp
                                (adresse_rnvp_etat_id, id, rnvp_id, vol3, vol4, vol5, cp, ville, point_livraison_id, commune_id)
                            SELECT
                                MIN(adresse_rnvp_etat_id) AS adresse_rnvp_etat_id, MAX(id) AS id, rnvp_id, vol3, vol4, vol5, cp, ville, point_livraison_id, commune_id
                            FROM
                                adresse_tmp2
                            GROUP BY
                            vol3, vol4, vol5, cp, ville; ";

            $this->_em->getConnection()->executeQuery($sSQLDropTmpTable);
            $this->_em->getConnection()->executeQuery($sSQLCreateTmpTable);
            $this->_em->getConnection()->executeQuery($sSQLInsertTmpTable);
            $this->_em->getConnection()->executeQuery($sTruncate);
            $this->_em->getConnection()->executeQuery($sInsert);

            $this->_em->clear();
            //$this->_em->getConnection()->prepare($sInsert)->execute();
        } catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

    /**
     * Mise a jour automatique des points de livraisons
     * @throws \Doctrine\DBAL\DBALException
     */
    public function miseAJourAutomatiquePtLivraison() {
        // C'est l'adresse complete qui est le point de livraison par defaut
        try {
            // Mise a jour automatique des pts de livraisons des adresses que l'on peut mettre comme un possible point de livraison
            $update = " UPDATE 
                            adresse a
                            LEFT JOIN adresse_rnvp r ON a.rnvp_id=r.id AND r.stop_livraison_possible='1'
                            LEFT JOIN adresse_rnvp_etat ra ON a.adresse_rnvp_etat_id=ra.id AND ra.qualite='OK'
                        SET
                            a.point_livraison_id = r.id
                        WHERE
                            a.point_livraison_id IS NULL 
                            AND r.id IS NOT NULL	
                            AND ra.id IS NOT NULL";
            $this->_em->getConnection()->prepare($update)->execute();

            // Nouveaux points de livraison calcules a partir de ceux ou cadrs et lieudit sont differents de ''
            $insertNouveauxPts = " INSERT IGNORE INTO adresse_rnvp
                                        (commune_id, cadrs, adresse, lieudit, cp, ville, insee, geox, geoy, date_modif, type_rnvp, geo_score, geo_type, geo_etat, stop_livraison_possible)
                                    SELECT
                                        r.commune_id, '' AS cadrs, r.adresse, '' AS lieudit, r.cp, r.ville, r.insee, r.geox, r.geoy, curdate() AS date_modif, 0 AS type_rnvp, geo_score, 4 AS geo_type, 1 AS geo_etat, '1' AS stop_livraison_possible
                                    FROM
                                        adresse a
                                        LEFT JOIN adresse_rnvp r ON a.rnvp_id=r.id AND r.stop_livraison_possible='0' AND r.geo_etat IN (1, 2)
                                        LEFT JOIN adresse_rnvp_etat ra ON a.adresse_rnvp_etat_id=ra.id AND ra.qualite='OK'
                                    WHERE
                                        a.point_livraison_id IS NULL
                                        AND r.id IS NOT NULL	
                                        AND ra.id IS NOT NULL";
            $this->_em->getConnection()->prepare($insertNouveauxPts)->execute();


            // Mise a jour avec ces derniers points crees
            $update = " UPDATE	
                            adresse a
                            LEFT JOIN adresse_rnvp r ON a.rnvp_id=r.id AND r.stop_livraison_possible='0' AND r.geo_etat IN (1, 2)
                            LEFT JOIN adresse_rnvp_etat ra ON a.adresse_rnvp_etat_id=ra.id AND ra.qualite='OK'
                        SET
                            a.point_livraison_id=(SELECT id FROM adresse_rnvp adresse_rnvp WHERE adresse_rnvp.cadrs='' AND adresse_rnvp.adresse=r.adresse AND adresse_rnvp.lieudit='' AND adresse_rnvp.cp=r.cp AND adresse_rnvp.ville=r.ville)
                        WHERE
                            a.point_livraison_id IS NULL
                            AND r.id IS NOT NULL	
                            AND ra.id IS NOT NULL";
            $this->_em->getConnection()->prepare($update)->execute();
        } catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

    public function UpdatePointLivraisonScriptRepair($iPointLivraisonId, $sId) {
        try {
            $slct = " UPDATE adresse
                        SET 
                            point_livraison_id = $iPointLivraisonId ,
                            date_modif = NOW()
                        WHERE CURDATE() between date_debut AND date_fin
                        AND rnvp_id IN($sId)
                    ";
            $this->_em->getConnection()->prepare($slct)->execute();
        } catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

    public function getAdressByAboSoc($aboSoc) {
        $q = "
            SELECT * FROM adresse 
            WHERE abonne_soc_id = $aboSoc 
                AND curdate() BETWEEN date_debut AND date_fin
           ";
        return $this->_em->getConnection()->fetchAll($q);
    }

    public function getSingleAdressByAboSoc(AbonneSoc $abonne) {
        $q = $this->_em->createQuery("SELECT a FROM AmsAdresseBundle:Adresse a WHERE a.abonneSoc = :abonneSoc ORDER BY a.dateModif DESC")
                ->setParameters(array(":abonneSoc" => $abonne));
        $q->setMaxResults(1);
        $r = $q->getResult();
        if (isset($r[0])) {
            return $r[0];
        } else {
            return null;
        }
    }

    public function findAdresseChangePl($adresse) {
        $qb = $this->createQueryBuilder('a'); 
        $qb->where('a.commune = :COM AND a.rnvp = :RNVP AND a.pointLivraison = :PL AND a.ville = :VIL AND a.vol1 = :V0LUn AND a.cp = :CP')->andWhere($qb->expr()->neq("a.id", ":adr"));
        $qb->andWhere($qb->expr()->between(':now', 'a.dateDebut', 'a.dateFin'));
                
         $qb ->setParameter('COM', $adresse->getCommune())
           ->setParameter('RNVP', $adresse->getRnvp())
           ->setParameter('PL', $adresse->getPointLivraison())
           ->setParameter('VIL', $adresse->getVille())
           ->setParameter('V0LUn', $adresse->getVol1())
           ->setParameter('CP', $adresse->getCp())
           ->setParameter('now', date('Y-m-d'))
           ->setParameter('adr', $adresse->getId()); 
        return $qb->getQuery()->getResult();
    }
}
