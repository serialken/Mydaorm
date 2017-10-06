<?php

namespace Ams\SilogBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

class DepotRepository extends EntityRepository {

    /**
     * 
     * @return $qb Query
     * @$depotIds liste d'ids depots
     */
    public function QueryDepotActive($depotIds = array()) {

        $qb = $this->createQueryBuilder('d');
        $qb->where($qb->expr()->between(':today', 'd.dateDebut', 'd.dateFin'))
                ->orWhere($qb->expr()->isNull('d.dateFin'))
                ->setParameter(":today", new \DateTime());
        if (isset($depotIds) && count($depotIds) > 0) {
            $qb->andWhere($qb->expr()->in('d.id', ':depotIds'))
                    ->setParameter('depotIds', $depotIds);
        }
        return $qb;
    }

    /**
     * 
     * @return la liste des depots active 
     */
    public function getListeDepot() {
        $qb = $this->QueryDepotActive();
        return $qb->getQuery()->getResult();
    }

    public function getDepotsAvecCommunes() {
        $qb = $this->createQueryBuilder('depot')
                ->leftJoin('depot.depotCommunes', 'DC')
                ->leftJoin('DC.commune', 'C')
                ->addSelect('DC')
                ->addSelect('C')
                ->join('depot.commune', 'commune')
                ->addSelect('commune')
        ;
        return $qb->getQuery()->getArrayResult();
    }

    public function getDepots(array $depotIds = array()) {
        $qb = $this->createQueryBuilder('depot')
                ->leftJoin('depot.commune', 'c')
                ->addSelect('c')
        ;
        if (!empty($depotIds)) {
            $qb->where('depot.id IN(:depotIds)')
                    ->setParameter('depotIds', $depotIds);
        }
        return $qb->getQuery()->getResult();
    }

    function selectCombo() {
        $sql = "SELECT
                id,
                libelle
                FROM depot
                ORDER BY code"
        ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    function getDepotOrderByOrdre($listDepot = false) {
        $sql = "SELECT code,libelle
                FROM depot ";
        if($listDepot)
            $sql .= "WHERE id IN($listDepot) ";
            $sql .= "ORDER BY libelle ASC"
        ;
        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function getDepotsAllowFromSession(array $depotIds) {
        $qb = $this->createQueryBuilder('depot')
                ->where('depot.id IN(:depotIds)')
                ->setParameter('depotIds', $depotIds)
                ->orderBy('depot.libelle', 'ASC');

        return $qb;
    }

    public function getCommunesAvecDepotsId($id) {
        $qb = $this->createQueryBuilder('depot')
                ->leftJoin('depot.depotCommunes', 'DC')
                ->leftJoin('DC.commune', 'C')
                ->addSelect('DC')
                ->addSelect('C')
                ->where('depot.id = :id')
                ->andWhere('DC.dateFin > :today')
                ->setParameters(array(
            'id' => $id,
            'today' => new \DateTime()
                ))
        ;
        return $qb->getQuery()->getArrayResult();
    }

    public function deleteDepotCommune($depot, $commune) {
        $sql = "DELETE FROM depot_commune 
                WHERE 
                    depot_id =  " . $depot . "
                AND
                    commune_id = " . $commune . "  
                AND 
                    date_fin > now()";

        return $this->_em->getConnection()->prepare($sql)->execute();
    }

    /**
     * Méthode qui retourne les depots qui ne sont pas comme point de départ (ptDepart = 0)
     * @return array Les enregistrements correspondants aux critères
     * @throws DBALException
     */
    public function getDepotNotStartPoint() {
        try {
            $select = "SELECT  d.id AS depotId,
                            d.`commune_id` AS communeId,
                            d.libelle AS libelle,
                            d.adresse AS adresse,
                            c.insee AS cp,
                            c.libelle AS ville,
                            d.`pt_depart` AS ptDepart
                        FROM depot  d 
                        INNER JOIN commune c ON  d.commune_id = c.id
                        WHERE pt_depart = 0 ;";
            return $this->_em->getConnection()->fetchAll($select);
        } catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

    /**
     * 
     * @param int $id L'ID du depot à modifier
     * @param int $x coordonées x de l'adresse à utiliser pour la mise à jour
     * @param int $y coordonées y de l'adresse à utiliser pour la mise à jour
     * @param int $iPtDepart Si égal à 1, défini le dépot comme point de départ
     * @throws DBALException
     */
    public function updateCoords($id, $x, $y, $iPtDepart = 1) {
        try {
            $update = "UPDATE depot "
                    . "SET pt_depart = $iPtDepart, "
                    . "date_modif=NOW(),  "
                    . "geox=" . $x . ", "
                    . "geoy=" . $y . " "
                    . "WHERE id=" . $id . ";";

            $this->_em->getConnection()->prepare($update)->execute();
        } catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

    /**
     * Méthode qui retourne le dépot qui couvre un code INSEE
     * @param string $sInsee Le code INSEE
     * @return object L'objet Depot correspondant
     */
    public function getDepotFromInsee($sInsee) {
        $rsmb = new ResultSetMappingBuilder($this->_em);
        $rsmb->addRootEntityFromClassMetadata(
                'Ams\SilogBundle\Entity\Depot', 'depot'
        );
        
        $rsmb->addIndexBy('depot', 'id');

        $select = "SELECT depot.id, depot.pt_depart, depot.geox, depot.geoy FROM depot 
	LEFT JOIN depot_commune ON depot.id = depot_commune.depot_id
	LEFT JOIN commune ON commune.id = depot_commune.commune_id
	WHERE commune.insee = " . (int) $sInsee . ";
        ";
        
        $query = $this->_em->createNativeQuery($select, $rsmb);
        return $result = $query->getOneOrNullResult();
    }

}
