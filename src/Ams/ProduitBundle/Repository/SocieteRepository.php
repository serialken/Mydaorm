<?php 

namespace Ams\ProduitBundle\Repository;

use Doctrine\ORM\EntityRepository;

class SocieteRepository extends EntityRepository
{
    public function getSocietesAvecProduits() {
        $qb = $this->createQueryBuilder('societe')
                ->leftJoin('societe.produitDefaut', 'defaut')
                ->addSelect('defaut')
                ->leftJoin('societe.image', 'image')
                ->addSelect('image')
                ->leftJoin('societe.produits', 'produits')
                ->addSelect('produits')
                ->leftJoin('produits.parents', 'parents')
                ->addSelect('parents')
                ->orderBy('societe.libelle', 'ASC')
                ->orderBy('produits.libelle', 'ASC')
                ->where('societe.active = 1')
       ;
        
        return $qb->getQuery()->getResult();
    }
    
    /**
     * Retourne la liste des sociétés ayant des produits hors-presse
     * c'est à dire les produits dont le type est différent de 1,2 ou 3
     * @return type
     */
    public function getSocietesAvecProduitsHP() {
        $qb = $this->createQueryBuilder('societe')
                ->leftJoin('societe.produitDefaut', 'defaut')
                ->addSelect('defaut')
                ->leftJoin('societe.image', 'image')
                ->addSelect('image')
                ->leftJoin('societe.produits', 'produits')
                ->addSelect('produits')
                ->leftJoin('produits.parents', 'parents')
                ->addSelect('parents')
                ->orderBy('societe.libelle', 'ASC')
                ->orderBy('produits.libelle', 'ASC')
                ->where('societe.active = 1')
                ->andWhere('produits.produitType NOT IN (1,2,3)')
       ;
        
        return $qb->getQuery()->getResult();
    }
    
     public function getOneSocieteAvecProduits($id) {
        $qb = $this->createQueryBuilder('societe')
                ->leftJoin('societe.produitDefaut', 'defaut')
                ->addSelect('defaut')
                ->leftJoin('societe.image', 'image')
                ->addSelect('image')
                ->leftJoin('societe.produits', 'produits')
                ->addSelect('produits')
                ->leftJoin('produits.parents', 'parents')
                ->addSelect('parents')
                ->where('societe.id = :id')
                ->where('societe.active = 1')
                ->setParameter('id', $id)
                ->orderBy('societe.libelle', 'ASC')
                ->orderBy('produits.libelle', 'ASC')
       ;
        
        return $qb->getQuery()->getResult();
    }
    
    public function getSocietesLibelles($execute ='')
    {
        $qb = $this->createQueryBuilder('societe')
                ->where('societe.active = 1')
                ->orderBy('societe.libelle', 'ASC');
        
        if($execute)
            return $qb->getQuery()->getResult();
        return $qb;
    }
    
    public function getSocietes()
    {
        $qb = $this->createQueryBuilder('societe')
                ->where('societe.active = 1')
                ->orderBy('societe.id', 'ASC');
       return $qb->getQuery()->getResult();
    }

    function selectCombo() {
        $sql = "SELECT
                id,
                concat(libelle,' (',code,')') as libelle
                FROM societe s
                WHERE s.active = 1
                ORDER BY libelle"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    public function societyActive() {
        $sql = "SELECT
                    *
                FROM societe s
                WHERE active = 1
                AND (NOW() between date_debut AND date_fin OR (NOW() >= date_debut AND date_fin is null))
                ORDER BY s.libelle"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    public function qbSocietyActive() {
        $date = date('Y-m-d');
        $dateMax = date('Y-m-d', strtotime("+ 30 day"));
        $qb = $this->createQueryBuilder('societe')
                ->where('societe.active = 1')
                ->andWhere('(societe.dateDebut <= :date AND societe.dateFin >= :date_max) OR (societe.dateDebut <= :date AND societe.dateFin IS NULL) ')
                ->setParameter('date', $date)
                ->setParameter('date_max', $dateMax)
                ->orderBy('societe.libelle', 'ASC');
       return $qb;
    }

    /**
     * Renvoi l'Id d'une societe par rapport a son code
     * @param $code
     * @return array
     */
    public function getIdsocByCode($code){
        $sql = "select id from societe where code = '" . $code ."' limit 1";
        return $this->_em->getConnection()->fetchAll($sql);
    }
}