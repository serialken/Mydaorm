<?php 

namespace Ams\DistributionBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\DBALException;

class ProduitRecapDepotRepository extends EntityRepository
{
    /**
     * Suppression des donnees dont la date de distrib est J+$iJourATraiter
     * @param type $iJourATraiter
     */
    public function init($iJourATraiter)
    {
        $oDateDuJour    = new \DateTime();
        $oDateDuJour->setTime(0, 0, 0);
        $dateDistribATraiter   = $oDateDuJour;
        if($iJourATraiter<0)
        {
            $dateDistribATraiter   = $oDateDuJour->sub(new \DateInterval('P'.abs($iJourATraiter).'D'));
        }
        else
        {
            $dateDistribATraiter   = $oDateDuJour->add(new \DateInterval('P'.$iJourATraiter.'D'));
        }
        // Suppression des donnees dont la date de distrib est $dateDistribATraiter
        $qb1 = $this->_em->createQueryBuilder()->delete('AmsDistributionBundle:ProduitRecapDepot', 'p')
                                            ->where('p.dateDistrib = :dateDistribATraiter')
                                            ->setParameter('dateDistribATraiter', $dateDistribATraiter);
        $qb1->getQuery()->execute(); 
    }
    
    public function produitParDepot($iJourATraiter)
    {
        try {
            $this->_em->getConnection()
                        ->executeQuery("INSERT INTO produit_recap_depot "
                                . "     (depot_id, fic_recap_id, produit_id, date_distrib, nb_exemplaires)"
                                . " SELECT"
                                . "     l.depot_id, l.fic_recap_id, l.produit_id, l.date_distrib, SUM(l.qte) AS nb_exemplaires "
                                . " FROM client_a_servir_logist l"
                                . "     LEFT JOIN fic_recap r ON l.fic_recap_id=r.id "
                                . " WHERE l.date_distrib = DATE_ADD(CURRENT_DATE(), INTERVAL ".$iJourATraiter." DAY)"
                                . " GROUP BY l.depot_id, l.fic_recap_id, l.produit_id, l.date_distrib ");
            
        }
        catch (DBALException $ex) {
            throw $ex;
        }
    }
    
    public function getCalendrier($mois = null, $depotId = null, $fluxId = null)
    {
        $qb = $this->createQueryBuilder('prd_recap')
            ->join('prd_recap.produit', 'produit')
                ->addSelect('produit')
            ->join('produit.image','image')
                ->addSelect('image')
            ->where('prd_recap.id is not null');
            
        if ($mois != null) {
            $qb ->andWhere('month(prd_recap.dateDistrib) = :mois ')
                ->setParameter('mois', $mois);
        }

        if ($fluxId != null) {
            $qb ->andWhere('produit.flux = :fluxId ')
                ->setParameter('fluxId', $fluxId);
        }

        if ($depotId != null) {
            $qb ->andWhere('prd_recap.depot = :depot')
                ->setParameter('depot', $depotId);
        }
            
        return $qb->getQuery()->getResult();
    }
    
    public function hasProductsInDate($produits, $date)
    {       
        $qb = $this->createQueryBuilder('prd_recap')
                ->where('prd_recap.dateDistrib = :date ')
                ->setParameter('date', $date)
                ->andWhere('prd_recap.produit IN (:produits)')
                ->setParameter('produits', $produits)
                ->groupBy("prd_recap.produit");

        return $qb->getQuery()->getResult();
    }
    
    public function deleteByFicRecap(\Ams\FichierBundle\Entity\FicRecap $ficRecap)
    {
        $qb = $this->createQueryBuilder('prdRecapDep');
        $qb ->delete()
            ->where('prdRecapDep.ficRecap = :ficRecap')
            ->setParameter(':ficRecap', $ficRecap);
        
        return $qb->getQuery()->getResult();
    }
}