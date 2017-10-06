<?php 

namespace Ams\ProduitBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ProduitTypeRepository extends EntityRepository
{
    public function getCaractsBySaisie($id, $saisieCode){
        $qb = $this->createQueryBuilder('prd_type')
                    ->join('prd_type.caracts', 'caracts')
                    ->join('caracts.saisie','saisie')
                    ->where('prd_type.id = :id')
                    ->andWhere('saisie.code = :code')
                    ->andWhere('caracts.actif = 1')
                    ->setParameter('id', $id)
                    ->setParameter('code', $saisieCode)
                    ->addSelect("caracts")
            ;
         return $qb->getQuery()->getOneOrNullResult();
    }
    
    public function getProduitsArray(){
        $qb = $this->createQueryBuilder('d')
            ->orderBy('d.libelle','ASC')
            ;
        return $qb->getQuery()->getArrayResult();
    }

    public function getProduitType(){
//        $qb = $this ->createQueryBuilder('p')
//                    ->from('produit_type', 'p')
//                    ->orderBy('p.id', 'ASC');
//            ;
//         return $qb->getQuery()->getArrayResult();
         
         $sql = "SELECT
                id,
                libelle,
                hors_presse
                FROM produit_type p
                ORDER BY id"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    function selectCombo() {
        $sql = "SELECT
                id,
                libelle
                FROM produit_type p
                ORDER BY id"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
    }

}