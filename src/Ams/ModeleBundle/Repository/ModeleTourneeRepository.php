<?php

namespace Ams\ModeleBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class ModeleTourneeRepository extends GlobalRepository {

    function select($depot_id, $flux_id, $sqlCondition = '') {
        $sql = "SELECT 
                    mt.id,
                    gt.depot_id,
                    gt.flux_id,
                    mt.groupe_id,
                    mt.numero,
                    mt.code,
                    mt.libelle,
                    mt.employe_id,
                    mt.codeDCS,
                    mt.actif,
                    -- journal
                    coalesce(min(me.valide),true) as valide,
                    group_concat(me.msg order by me.level,me.rubrique,me.code separator '<br/>') as msg,
                    min(mj.id) as journal_id,
                    min(me.level) as level
                FROM modele_tournee mt
                INNER JOIN groupe_tournee gt ON gt.id = mt.groupe_id
                left outer join modele_journal mj on mt.id=mj.tournee_id
                LEFT OUTER JOIN modele_ref_erreur me ON mj.erreur_id=me.id
                WHERE gt.depot_id=" . $depot_id . " AND gt.flux_id=" . $flux_id . " " .
                ($sqlCondition != '' ? $sqlCondition : "") . "
                GROUP BY mt.id"
        ;
        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function insert(&$msg, &$msgException, $param, $user, &$id) {
        try {
            $sql = "INSERT INTO modele_tournee SET
                    groupe_id = " . $param['groupe_id'] . ",
                    numero = " . $this->sqlField->sqlQuote($param['numero']) . ",
--                    code = " . $this->sqlField->sqlQuote($param['code']) . ",
                    libelle = " . $this->sqlField->sqlQuote($param['libelle']) . ",
                    employe_id = " . $this->sqlField->sqlTrim($param['employe_id']) . ",
                    codeDCS = " . $this->sqlField->sqlQuote($param['codeDCS']) . ",
                    actif = " . $param['actif'] . ",
                    utilisateur_id = " . $user . ",
                    date_creation = NOW()";
            $this->_em->getConnection()->prepare($sql)->execute();
            $id = $this->_em->getConnection()->lastInsertId();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "La tournée doit être unique.", "UNIQUE", "");
        }
        return true;
    }

    public function update(&$msg, &$msgException, $param, $user, &$id) {
        try {
            $sql = "UPDATE modele_tournee SET
                    groupe_id = " . $param['groupe_id'] . ",
                    numero = " . $this->sqlField->sqlQuote($param['numero']) . ",
--                    code = " . $this->sqlField->sqlQuote($param['code']) . ",
                    libelle = " . $this->sqlField->sqlQuote($param['libelle']) . ",
                    employe_id = " . $this->sqlField->sqlTrim($param['employe_id']) . ",
                    codeDCS = " . $this->sqlField->sqlQuote($param['codeDCS']) . ",
                    actif = " . $param['actif'] . ",
                    utilisateur_id = " . $user . ",
                    date_modif = NOW()";
            $sql .= " WHERE id=" . $param['gr_id'];
            $this->_em->getConnection()->prepare($sql)->execute();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "La tournée doit être unique.", "UNIQUE", "");
        }
        return true;
    }

    public function delete(&$msg, &$msgException, $param) {
        try {
            $this->_em->getConnection()->beginTransaction();
            $this->_em->getConnection()->prepare("DELETE FROM modele_journal WHERE tournee_id = " . $param['gr_id'])->execute();
            $this->_em->getConnection()->prepare("DELETE FROM modele_tournee WHERE id=" . $param['gr_id'])->execute();
            $this->_em->getConnection()->commit();
        } catch (DBALException $ex) {
            $error = $this->sqlField->sqlError($msg, $msgException, $ex, "Le modèle de tournée est utilisé.<br/>Suppression impossible.", "FOREIGN", "");
            $this->_em->getConnection()->rollBack();
            return $error;
        }
        return true;
    }

    public function transferer(&$msg, &$msgException, $utilisateur_id, $modele_tournee_id, $groupe_id, $code, $date_debut) {
        try {
            $sql = "call mt_transferer(@validation_id," . $utilisateur_id . "," . $modele_tournee_id . "," . $groupe_id . ",'" . $code . "'," . $this->sqlField->sqlDate($date_debut) . ")";
            // Attention, il faut valider les modele de tournee jour si valide passe de actif à inactif
            return $this->executeProc($sql, "@validation_id");
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "", "", "");
        }
    }

    public function validate(&$msg, &$msgException, $id, $action = "", $param = null) {
        try {
            $sql = "call mod_valide_tournee(@validation_id,null,null," . $this->sqlField->sqlId($id) . ")";
            // Attention, il faut valider les modele de tournee jour si valide passe de actif à inactif
            return $this->executeProc($sql, "@validation_id");
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "Erreur inconnue.", "", "");
        }
    }

    function selectComboModele($depot_id, $flux_id) {
        $sql = "SELECT DISTINCT
                mt.id,
                CONCAT_WS(' ',mt.code,IF(mt.actif,'','*')) libelle
                FROM modele_tournee mt
                INNER JOIN groupe_tournee gt ON gt.id = mt.groupe_id
                WHERE gt.depot_id = $depot_id AND gt.flux_id = $flux_id
                ORDER BY mt.code";
        return $this->_em->getConnection()->fetchAll($sql);
    }

    function selectComboToutes($depot_id, $flux_id) {
        $sql = "SELECT DISTINCT
                mt.id,
                CONCAT_WS(' ',mt.code,IF(mt.actif,'','*')) libelle,
                CONCAT_WS(' ',IF(mt.actif,'','*'),mt.code) ordre
                FROM modele_tournee mt
                INNER JOIN groupe_tournee gt ON gt.id = mt.groupe_id
                WHERE gt.depot_id =". $depot_id." AND gt.flux_id =". $flux_id."
                    UNION
                SELECT
                0,
                'Toutes',
                'Toutes'
                ORDER BY 3";
        return $this->_em->getConnection()->fetchAll($sql);
    }

    function selectComboDepot($utilisateur_id) {
        $sql = "SELECT DISTINCT
                d.id,
                d.libelle
                FROM utilisateur u
                INNER JOIN dep_groupe_depot dgd on u.grp_depot_id=dgd.grd_code
                INNER JOIN depot d on dgd.dep_code=d.id
                WHERE u.id = " . $utilisateur_id . "
                ORDER by d.code";
        return $this->_em->getConnection()->fetchAll($sql);
    }

    /**
     *  liste des tournees ou queryBuilder
     * @param type $depotIds
     * @param bool $isQuery 
     * @return type
     */
    function getListeTournee($depotIds, $isQuery = false) {
        $qb = $this->createQueryBuilder('t')
                ->leftJoin('t.groupe', 'groupe_tournee')
                ->addSelect('groupe_tournee')
        ;
        $qb->where($qb->expr()->in('groupe_tournee.depot', ':depotIds'))
                ->setParameter('depotIds', $depotIds);
        if ($isQuery == true)
            return $qb;
        else
            return $qb->getQuery()->getResult();
    }

    /**
     * Export vers DCS des tournees M-ROAD
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function donneesDcsTournee() {
        try {
            $sSlct = " SELECT
                            d.code AS depotDCS
                            , mt.codeDCS
                            , mt.libelle AS tournee_MROAD
                        FROM
                            modele_tournee mt
                            INNER JOIN groupe_tournee gt ON mt.groupe_id = gt.id
                            INNER JOIN depot d ON gt.depot_id = d.id AND d.date_debut <= CURRENT_DATE() AND (d.date_fin IS NULL OR d.date_fin >= CURRENT_DATE())
                        --    INNER JOIN ref_typetournee rtt ON mt.typetournee_id=rtt.id AND rtt.code='SDV'
                        WHERE
                            mt.actif = 1
                            AND mt.codeDCS IS NOT NULL AND mt.codeDCS <> ''
                            AND mt.libelle IS NOT NULL AND mt.libelle <> ''
                            AND gt.flux_id=1
                        ORDER BY
                            depotDCS, codeDCS, tournee_MROAD
                            ";
            return $this->_em->getConnection()->executeQuery($sSlct)->fetchAll();
        } catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

    /**
     * Retourne la liste des tournées correspondants aux critères passés en entrée dans le cadre d'une reprise des données de TD
     * @param string $sTourneeCode La racine du MT Code de la tournée ex: 028
     * @param string $sDate La date sur laquelle filtrer
     * @param int $iFlux L'ID du flux
     * @param string $sDCSCode La racine du code DCS de la tournée ex: K
     * @return array $aListe La liste des codes de tournées à prendre en compte
     */
    public function listerTourneesAReprendre($sTourneeCode, $sDate, $iFlux, $sDCSCode = NULL) {
        $connection = $this->getEntityManager()->getConnection();
        $query = "SELECT DISTINCT mtj.code FROM modele_tournee AS mt
	JOIN modele_tournee_jour AS mtj ON mtj.tournee_id = mt.id
	JOIN client_a_servir_logist AS casl ON casl.tournee_jour_id = mtj.id
	WHERE mt.code LIKE '$sTourneeCode%'";

        if (!is_null($sDCSCode)) {
            $query .= "	AND codeDCS LIKE '$sDCSCode%' ";
        }

        $query .= " 
	AND
	casl.date_parution BETWEEN mtj.date_debut AND mtj.date_fin
	AND 
	casl.date_parution = '$sDate'
	AND
	casl.flux_id = $iFlux
	GROUP BY mtj.code";

        $stmt = $connection->executeQuery($query);
        $aListe = $stmt->fetchAll();
        return $aListe;
    }

}
