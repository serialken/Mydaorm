<?php

namespace Ams\AdresseBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Ams\AdresseBundle\Entity\RequeteExport as RequeteExport;

/**
 */
class RequeteExportRepository extends EntityRepository {

    public function getMaxRequeteId() {
        try {

            $query = " SELECT MAX(id) as idMax FROM requete_export";

            //$this->_em->getConnection()->fetchArray($query);
            return $this->_em->getConnection()->fetchArray($query);
        } catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

    public function execQueryById($id) {
        $connection = $this->getEntityManager()->getConnection();
        $q = " 
                SELECT requete FROM requete_export
                WHERE id = $id 
                ";
        $query = $connection->executeQuery($q)->fetch();
        return $this->_em->getConnection()->fetchAll($query['requete']);
    }

    public function getQueryByUser($iUser) {
        $sql = 'SELECT *,
                    (
                        SELECT COUNT(*) FROM import_geoconcept 
                        WHERE requete_export_id = re.id 
                    )as reponse,
                    (
                        SELECT DISTINCT date_optim FROM import_geoconcept 
                        WHERE requete_export_id = re.id GROUP BY requete_export_id
                    ) as date_optim
                FROM requete_export re
                WHERE utilisateur_id = ' . $iUser
        ;

        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function getPointLivraisonByQueryExportId($iRequeteExport) {
        $sql = 'SELECT point_livraison_id
                FROM export_geoconcept eg
                WHERE eg.requete_export_id = ' . $iRequeteExport
        ;

        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function deleteQuery($iQuery) {
        $sql = ' DELETE FROM export_geoconcept ' .
                ' WHERE requete_export_id =' . $iQuery . ';'
        ;
        $sql.= ' DELETE FROM import_geoconcept ' .
                ' WHERE requete_export_id =' . $iQuery . ';'
        ;
        $sql.= ' DELETE FROM requete_export ' .
                ' WHERE id =' . $iQuery . ';'
        ;
        $this->_em->getConnection()->exec($sql);
    }

    /**
     * Retourne les informations sur le statut d'une requete
     * @param int $iReqId L'identifiant de la requête
     * @return array $aReturn Le tableau contenant les informations
     */
    public function getStatutInfos($iReqId) {
        $connection = $this->getEntityManager()->getConnection();
        $q = "
        SELECT 
	req_exp.id
	, nb_resultat
	, statut
	, nb_imports 
        , optim_info
        , jour_type
        , liste_tournees
        , date_import
	, date_application
	, COUNT(*) AS nb_lignes_import
	FROM requete_export AS req_exp
	JOIN import_geoconcept AS imp ON imp.requete_export_id = req_exp.id
	WHERE req_exp.id = " . (int) $iReqId . ";
        ";

        // Init du tableau de retour
        $aReturn = array(
            'returnCode' => NULL,
            'msg' => NULL,
            'errCode' => NULL,
            'errMsg' => NULL,
            'datas' => NULL,
        );

        $aResults = $this->_em->getConnection()->fetchAll($q);

        // Pas de résultat
        if (empty($aResults)) {
            $aReturn['errCode'] = 1;
            $aReturn['errMsg'] = 'Aucune information retournée pour cette requête d\'export';
        } else {
            // Aucune date d'application fixée
            if (is_null($aResults[0]['date_application'])) {
                $aReturn['returnCode'] = 1;
                $aReturn['datas'] = array(
                    'statut' => 'Non appliquée',
                    'info' => 'Aucune date spécifiée',
                    'nb_lignes_import' => $aResults[0]['nb_lignes_import'],
                    'statusCode' => RequeteExport::STATUT_NONAPPLIQUEE, // Non appliquée
                );

                return $aReturn;
            }
            
            // Requete inaplicable car ancienne version de requête d'export
            if (is_null($aResults[0]['jour_type'])) {
                $aReturn['returnCode'] = 1;
                $aReturn['datas'] = array(
                    'statut' => 'Non applicable',
                    'info' => 'Ancienne version de requête',
                    'nb_lignes_import' => $aResults[0]['nb_lignes_import'],
                    'statusCode' => RequeteExport::STATUT_ANCIENNE_VERSION, // Non applicable car ancienne version 
                );

                return $aReturn;
            }

            // Aucune ligne dans l'import
            if ($aResults[0]['nb_imports'] > 0 && $aResults[0]['nb_lignes_import'] == 0) {
                $aReturn['returnCode'] = 1;
                $aReturn['datas'] = array(
                    'statut' => 'En attente d\'import',
                    'statusCode' => RequeteExport::STATUT_NONAPPLIQUEE, // Appliquée
                    'nb_lignes_import' => $aResults[0]['nb_lignes_import'],
                    'info' => '0',
                );
            } else {
                $aReturn['returnCode'] = 1;
                
                // Récupération de l'historique d'application
                if (empty($aResults[0]['nb_lignes_import'])){
                    $aReturn['datas'] = array(
                        'statut' => 'Pas encore appliquée',
                        'info' => '0',
                        'statusCode' => RequeteExport::STATUT_PASENCORE, // Pas encore appliquée,
                        'nb_lignes_import' => $aResults[0]['nb_lignes_import'],
                    );
                } else {
                    $aJoursLog = \Ams\AdresseBundle\Controller\ExportController::getDaysListFromOptimInfo($aResults[0]['optim_info']);
                        if (empty($aJoursLog)){
                            $aReturn['datas'] = array(
                            'statut' => 'Pas encore appliquée',
                            'info' => '0',
                            'statusCode' => RequeteExport::STATUT_PASENCORE, // Pas encore appliquée,
                            'nb_lignes_import' => $aResults[0]['nb_lignes_import'],
                        );
                    }
                    else{
                        // Requête partiellement appliquée
                        // Nombre de tournées à optimiser
                        $aJoursType = json_decode($aResults[0]['jour_type']);
                        
                        // Nombre de tournées déjà optimisées
                        $iNbTourneesOptim = \Ams\AdresseBundle\Controller\ExportController::compterTourneesOptimisees($aResults[0]['optim_info']);
                        $iNbTourneesVides = \Ams\AdresseBundle\Controller\ExportController::compterTourneesVides($aResults[0]['optim_info']);
                        
                        $aListeTourneeJour =  unserialize(base64_decode($aResults[0]['liste_tournees']));
                        $aListeTournees = array();
                        if (!empty($aListeTourneeJour)){
                            foreach ($aListeTourneeJour as $sTourneeJour){
                                $sMtCode = substr($sTourneeJour, 0, strlen($sTourneeJour) - 2);
                                if (!in_array($sMtCode, $aListeTournees)){
                                    $aListeTournees[] = $sMtCode;
                                }
                            }
                        }
                        $iNbTourneesTot = count($aListeTournees)* count($aJoursType);
                        $fRatio = ($iNbTourneesOptim + $iNbTourneesVides) / $iNbTourneesTot;
                        $sPourcent = 100 * number_format($fRatio, 2);
                        
                        $aReturn['datas'] = array(
                            'statut' => 'En cours d\'application',
                            'info' => $sPourcent,
                            'statusCode' => RequeteExport::STATUT_ENCOURS, // En cours d'application
                            'nb_lignes_import' => $aResults[0]['nb_lignes_import'],
                        );
                        
                        // Changement du statut de l'application si complète
                        if ($sPourcent == 100){
                            $aReturn['datas']['statut'] = 'Appliquée';
                            $aReturn['datas']['statusCode'] = RequeteExport::STATUT_APPLIQUEE;
                        }
                    }
                }
            }

            // Mise à jour du statut dans la base
            if ($aReturn['datas']['statusCode']) {
                $sUpSql = "UPDATE requete_export SET statut='" . $aReturn['datas']['statusCode'] . "' WHERE id=" . (int) $iReqId;
                $connection->exec($sUpSql);
            }
        }

        return $aReturn;
    }

    /**
     * Retourne la liste des requetes d'export dont l'application est à évaluer
     * @param int $iReqId L'ID d'une requête d'export qui serait forcée (optionnel)
     * @return array $aListe La liste des requetes d'exports à évaluer
     */
    public function listeReqAEvaluer($iReqId = null) {
        $connection = $this->getEntityManager()->getConnection();

        if ($iReqId) {
            $q = "
                SELECT *  FROM requete_export
	WHERE id = $iReqId
        ;";
        } else {
            $q = "
        SELECT *  FROM requete_export
	WHERE 
	date_application IS NOT NULL
	AND
	liste_tournees IS NOT NULL
	AND
	nb_imports IS NOT NULL
	AND
	jour_type IS NOT NULL
	AND
	valid = 1
        AND (statut <> 'A' OR statut IS NULL)
        ";
        }

        $aListe = $this->_em->getConnection()->fetchAll($q);

        return $aListe;
    }

}
