<?php

namespace Ams\AdresseBundle\Repository;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\DBALException;

/**
 * Description of ImportRepository
 *
 * @author madelise
 */
class ImportGeoconceptRepository extends EntityRepository implements ContainerAwareInterface {

    private $container;

    public function setContainer(ContainerInterface $container = null) {
        $this->container = $container;
    }
    
    /**
     * 
     * @param type $requeteExportId
     * @return boolean
     */
    public function deleteDoubloonByRequeteExportId($requeteExportId,$sJourId) {
        $connection = $this->getEntityManager()->getConnection();
        $connection->executeQuery("SET SESSION group_concat_max_len = 1000000");
        $q = " 
            SELECT 
                GROUP_CONCAT(td.id) as list_id
            FROM
                import_geoconcept im
                    JOIN 
                tournee_detail td ON td.num_abonne_id = im.abonne_soc_id 
                    AND td.jour_id IN ($sJourId)
                    AND im.flux_id = td.flux_id
            WHERE requete_export_id = $requeteExportId
            ";
        $aTourneeDetailId = $connection->executeQuery($q)->fetch();
        $sTourneeDetailId = $aTourneeDetailId['list_id'];
        
        /** DELETE TOURNEE DETAIL DOUBLOON **/
        if($sTourneeDetailId != NULL){
            $query =" DELETE FROM tournee_detail WHERE id IN($sTourneeDetailId)";
            $connection->executeQuery($query);
            return true;
        }
        return false;
    }

    /**
     * Méthode qui permet de détecter les points de livraison qui ont changé de tournée lors de l'optimisation
     * @param int $iRequeteExportId L'ID de la requete d'export
     * @param array Le tableau des points de livraison ayant changé de tournée, avec leur nouveau code de tournée
     */
    public function detecterChangementTournee($iRequeteExportId) {
        $connection = $this->getEntityManager()->getConnection();
        $query = "SELECT 
	point_livraison_id,
        abonne_soc_id,
	code_tournee
	FROM import_geoconcept
	WHERE
		requete_export_id = $iRequeteExportId
		AND ROW(point_livraison_id, code_tournee) NOT IN (
SELECT 
	point_livraison_id,
	code_tournee
	FROM export_geoconcept
	WHERE
		requete_export_id = $iRequeteExportId		
		);";
        $stmt = $connection->executeQuery($query);
        return $stmt->fetchAll();
    }

    /**
     * Méthode qui liste les tournées comprises dans un export Geoconcept
     * @param int $iRequeteExportId L'ID de la requete d'export
     * @return array Le tableau contenant la liste des tournées de l'import
     */
    public function listerTournees($iRequeteExportId) {
        $connection = $this->getEntityManager()->getConnection();
        $query = "SELECT DISTINCT code_tournee AS nb_tournees FROM import_geoconcept WHERE requete_export_id = $iRequeteExportId;";
        $stmt = $connection->executeQuery($query);
        $result = $stmt->fetchAll();
        return $result;
    }

    /**
     * Méthode qui permet de détecter les abonnés ayant changé de point de livraison lors de l'optimisation
     * @param int $iRequeteExportId L'ID de la requete d'export
     * @return array Le tableau des abonnés ayant changé de point de livraison avec leur nouveau point de livraison ID
     */
    public function detecterChangementPointLivraison($iRequeteExportId) {
        $connection = $this->getEntityManager()->getConnection();
        $query = "SELECT 	
	abonne_soc_id,
        code_tournee,
	point_livraison_id
	FROM import_geoconcept
	
	WHERE
		requete_export_id = $iRequeteExportId
		AND (abonne_soc_id, point_livraison_id) NOT IN (
		SELECT 
			abonne_soc_id,
			point_livraison_id
				FROM export_geoconcept
	
				WHERE
				requete_export_id = $iRequeteExportId		
		);";
        $stmt = $connection->executeQuery($query);
        return $stmt->fetchAll();
    }

    public function recupererInfosChangementTourneeOptim($iReqId, $aPoints) {
        $bRetour = FALSE;
        if (empty($aPoints)) {
            return $bRetour;
        }

        $aAbosDansP2L = array();
        $aListeP2LId = array();
        foreach ($aPoints as $point) {
            $sListeAbos = '';
            $aListeAbosId = array();

            if (!in_array($point['point_livraison_id'], $aListeP2LId)) {
                // On créé un tableau contenant les ID des points de livraison et la concaténation des ID d'abonnés qui s'y trouvent
                foreach ($aPoints as $point2) {
                    if ($point['point_livraison_id'] == $point2['point_livraison_id']) {
                        if (!in_array($point2['abonne_soc_id'], $aListeAbosId)) {
                            $aListeAbosId[] = $point2['abonne_soc_id'];
                        }
                    }
                }
                $aAbosDansP2L[$point['point_livraison_id']] = implode(',', $aListeAbosId);
            }
        }

        $connection = $this->getEntityManager()->getConnection();
        $aResult = array();

        foreach ($aAbosDansP2L as $aPointDeplace) {
            $query = "SELECT DISTINCT
	export.point_livraison_id, 
	import.point_livraison_id, 
	import.abonne_soc_id,
	import.code_tournee AS tournee_destination_jour_code ,
	export.code_tournee AS tournee_source_jour_code,
	import.point_livraison_ordre AS nouvel_ordre,
	import.duree,
	import.temps_visite AS duree_visite_fixe,
	'04:00:00' AS debut_plage_horaire,
	'11:00:00' AS fin_plage_horaire
				FROM import_geoconcept AS import, export_geoconcept AS export
				WHERE import.requete_export_id = $iReqId
				AND export.requete_export_id = $iReqId
				AND import.abonne_soc_id = export.abonne_soc_id
				AND import.abonne_soc_id IN ($aPointDeplace)
				;";
            $stmt = $connection->executeQuery($query);
            $aResultTmp = $stmt->fetchAll();
            $aResultTmp[0]['liste_abonne_id'] = $aPointDeplace;
            $aResult[] = $aResultTmp[0];
        }

        return $aResult;
    }

    /**
     * Méthode qui compte le nombre de points par tournée
     * @param int $iRequeteExportId L'ID de la requete d'export
     * @return array Le résultat de la requête
     */
    public function compterPointsParTournee($iRequeteExportId) {
        $connection = $this->getEntityManager()->getConnection();
        $query = "SELECT code_tournee, COUNT(DISTINCT point_livraison_id) AS nb_points FROM export_geoconcept WHERE requete_export_id = $iRequeteExportId GROUP BY code_tournee;";
        $stmt = $connection->executeQuery($query);
        return $stmt->fetchAll();
    }

    /**
     * Méthode qui compte le nombre de points distincts dans l'import
     * * @param int $iRequeteExportId L'ID de la requete d'export
     * * @return int Le nombre total de points distincts dans les données de l'export
     */
    public function compterPointsDansImport($iRequeteExportId, $bDistinct = TRUE) {
        $connection = $this->getEntityManager()->getConnection();
        $sDistinctQuery = $bDistinct == TRUE ? 'DISTINCT' : '';
        $query = "SELECT COUNT($sDistinctQuery point_livraison_id) AS total FROM import_geoconcept WHERE requete_export_id = $iRequeteExportId";
        $stmt = $connection->executeQuery($query);
        $result = $stmt->fetchAll();

        return (int) $result[0]['total'];
    }
    
    /**
     * Retourne le nombre de lignes à importer
     * @param int $iRequeteExportId L'ID de la requete d'export
     * @return int Le nombre total de lignes à importer pour une requête
     */
    public function compterLignesAImporter($iRequeteExportId){
        $connection = $this->getEntityManager()->getConnection();
        $query = "SELECT COUNT(*) AS total FROM import_geoconcept WHERE requete_export_id = $iRequeteExportId";
        $stmt = $connection->executeQuery($query);
        $result = $stmt->fetchAll();

        return (int) $result[0]['total'];
    }

    /**
     * Méthode qui compte le nombre de clients distincts dans l'import
     * * @param int $iRequeteExportId L'ID de la requete d'export
     * * @return int Le nombre total de points distincts dans les données de l'export
     */
    public function compterClientsDansImport($iRequeteExportId) {
        $connection = $this->getEntityManager()->getConnection();
        $query = "SELECT COUNT(DISTINCT abonne_soc_id) AS total FROM import_geoconcept WHERE requete_export_id = $iRequeteExportId";
        $stmt = $connection->executeQuery($query);
        $result = $stmt->fetchAll();

        return (int) $result[0]['total'];
    }

    /**
     * Méthode qui retourne les lignes de l'import envoyées par Géoconcept
     * @param int $iRequeteExportId L'identifiant de la requête d'export
     * @return array Le résultat de la requête
     */
    public function getOptim($iRequeteExportId) {
        $connection = $this->getEntityManager()->getConnection();
        $query = "SELECT * FROM import_geoconcept WHERE requete_export_id = $iRequeteExportId AND date_optim IS NOT NULL AND date_import IS NULL;";
        $stmt = $connection->executeQuery($query);
        return $stmt->fetchAll();
    }

    
    /** 
     * RECUPERATION DU JEU DE DONNER DE L'IMPORT POUR LES JOURS TYPES
     * DEFINIT DANS UN PERIMETRE PREALABLEMENT CALCULER
     * @param type $reqId         => id de la table requete_export à appliquer
     * @param type $dateApplique  => date a laquelle le code tournée doit être valide
     * @param type $sCodeTournees => liste de tournee à appliquer
     * @return type array
     */

    public function getApplicationOptim($reqId,$dateApplique,$sCodeTournees) {
        $connection = $this->getEntityManager()->getConnection();
        $query = 
            "
            SELECT 
                mtj.code, mtj.jour_id as mtj_jour_id, mtj.id as mtj_id, im . *
            FROM
                import_geoconcept im
                    JOIN
                modele_tournee_jour mtj ON LEFT(mtj.code, 9) = LEFT(im.code_tournee, 9)
                    AND '$dateApplique' BETWEEN mtj.date_debut AND mtj.date_fin
            WHERE
                requete_export_id = $reqId
             AND mtj.code IN ($sCodeTournees)
            ORDER BY mtj.code,im.requete_export_id
            ";
        $stmt = $connection->executeQuery($query);
        return $stmt->fetchAll();
    }
    
    /**
     * Méthode de récupération des imports de Géoconcept dans le cadre d'un passge d'optimisation forcée
     * @param int $iReq L'ID de la requête à prendre en compte
     */
    public function getApplicationOptimForcee($iReq) {
        $connection = $this->getEntityManager()->getConnection();
        $query = "SELECT 
    im.id AS import_id,
    im.point_livraison_ordre AS ordre,
    mtj.id AS modele_tournee_jour_id,
    arnvp.geox AS longitude,
    arnvp.geoy AS latitude,
    temps_conduite AS duree_conduite,
    heure_debut,
    im.duree,
    etat,
    im.distance_trajet,
    im.trajet_cumule,
    '1' AS a_traiter,
    im.code_tournee AS modele_tournee_jour_code,
    asoc.numabo_ext AS num_abonne_soc,
    abonne_soc_id AS num_abonne_id,
    temps_conduite,
    asoc.soc_code_ext AS soc,
    p.prd_code_ext AS titre,
    arnvp.insee AS insee,
    im.flux_id,
    im.jour_id,
    trajet_total_tournee,
    point_livraison_id,
    p.id AS produit_id,
    im.requete_export_id AS req_exp_id,
    re.date_application
    , CURDATE() AS jour_applique
FROM
    import_geoconcept im
        LEFT JOIN
    produit AS p ON p.id = im.produit_id
        LEFT JOIN
    requete_export AS re ON re.id = im.requete_export_id
        LEFT JOIN
    adresse_rnvp arnvp ON arnvp.id = im.point_livraison_id
        LEFT JOIN
    abonne_soc asoc ON asoc.id = im.abonne_soc_id
        LEFT JOIN
    modele_tournee_jour mtj ON mtj.code = im.code_tournee
        AND mtj.date_fin >= CURDATE()
        AND mtj.date_debut >= CURDATE()
WHERE
    re.id =".(int)$iReq;
        $stmt = $connection->executeQuery($query);
        return $stmt->fetchAll();
    }

    /**
     * Méthode de suppression des tournées importées
     * @param int $iQuery L'identifiant de la requête d'export
     * @param array $aIds Un tableau contenant les identifiants à supprimer
     * @param bool $bTrueDeletion Supprime réellement l'enregistrement si vrai
     * @return int Le nombre d'enregistrements supprimés
     */
    public function clearImports($iQuery, $aIds = NULL, $bTrueDeletion = FALSE) {
        $qb = $this->createQueryBuilder('i');
        
        // Suppression ou désactivation ?
        if ($bTrueDeletion){
            $qb->delete('AmsAdresseBundle:ImportGeoconcept', 'i');
        }
        else{
            $qb->update('AmsAdresseBundle:ImportGeoconcept', 'i');
            $now = date_format(new \DateTime(), 'Y-m-d H:i:s');
            $qb->set('i.dateAppliq', "'".$now."'");
        }
        
        // Restrictions
        if (!is_null($iQuery)){
            $qb->where('i.requeteExport = :requete_export_id')
                    ->setParameter('requete_export_id', $iQuery);
        }
        else{
            if (!empty($aIds)){
                $qb->where('i.id IN (:import_ids)')
                ->setParameter('import_ids', $aIds);
            }
        }
                
        $query = $qb->getQuery();

        return $query->execute();
    }

}
