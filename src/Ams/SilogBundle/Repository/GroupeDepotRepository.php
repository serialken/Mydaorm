<?php

namespace Ams\SilogBundle\Repository;

use Doctrine\ORM\EntityRepository;

class GroupeDepotRepository extends EntityRepository {

    /**
     *  liste des dépots actives
     * @return liste des depots
     */
    public function getGroupeAvecDepot($grpDepotId = '', \DateTime $date = null,$order = 'd.libelle') {

        $qb = $this->createQueryBuilder('g')
                ->leftJoin('g.depots', 'd')
                ->addSelect('d');
        $qb->orderBy($order,'ASC');

        $qb->where($qb->expr()->between(':today', 'd.dateDebut', 'd.dateFin'))
                ->orWhere($qb->expr()->isNull('d.dateFin'))
                ->setParameter(":today", (is_null($date) ? new \DateTime() : $date));
        

        if(isset($grpDepotId) && ($grpDepotId > 0)) {
           $qb->andWhere('g.id = :grpDepotId')
              ->setParameter('grpDepotId', $grpDepotId);
            return $qb->getQuery()->getSingleResult();
        }

        return $qb->getQuery()->getResult();
    }

}
