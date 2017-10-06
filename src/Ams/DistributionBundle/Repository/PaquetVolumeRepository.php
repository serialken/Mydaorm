<?php

namespace Ams\DistributionBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * PaquetVolumeRepository
 *
 * 
 */
class PaquetVolumeRepository extends EntityRepository
{
    
    /**
     * Retourne le volume (nombre d'exemplaires) de produits par paquet
     * @param \DateTime $date
     * @return type
     * @throws \Ams\DistributionBundle\Repository\DBALException
     */
    public function getPaquetVolume(\DateTime $date)
    {
        try {
            $dateCourant    = clone $date;
            $dateCourant->setTime(0, 0, 0); // Suppression des heure, minute & seconde
            $qb = $this->createQueryBuilder('c');
            $qb->where('c.dateDistrib = :dateDistrib')
                    ->setParameter(':dateDistrib', $dateCourant);
            return $qb->getQuery()->getResult();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
}
