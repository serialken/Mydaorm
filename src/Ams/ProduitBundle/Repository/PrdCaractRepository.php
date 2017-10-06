<?php 

namespace Ams\ProduitBundle\Repository;

use Doctrine\ORM\EntityRepository;

class PrdCaractRepository extends EntityRepository
{
    public function getListeConst(){

         
         $sql = "SELECT 
                        prdCaract.id as id,
                        prdCaract.libelle as constLibelle,
                        type.libelle as titreLibelle,
                        prdCaract.code as constCode,
                        champ.libelle as constType
                  FROM 
                        prd_caract prdCaract
                  INNER JOIN
                        produit_type type ON type.id = prdCaract.produit_type_id
                  INNER JOIN 
                        prd_caract_type champ ON champ.id = prdCaract.caract_type_id
                  WHERE 
                        prdCaract.saisie_id = 1";
         
         return $this->_em->getConnection()->fetchAll($sql);
    }
    
    public function getListConstById($id)
    {
//                $qb = $this->createQueryBuilder('prd')
//                    ->where('prd.produitTYpe.id = :id')
//                    ->setParameter('id', $id)
//            ;
//         return $qb->getQuery()->getResult();
        
        
        
        $sql = "SELECT 
                   prdCaract.id as id,
                   prdCaract.libelle as constLibelle,
                   type.libelle as titreLibelle,
                   prdCaract.code as constCode,
                   champ.libelle as constType
                FROM 
                     prd_caract prdCaract
                INNER JOIN
                        produit_type type ON type.id = prdCaract.produit_type_id
                INNER JOIN 
                        prd_caract_type champ ON champ.id = prdCaract.caract_type_id
                WHERE 
                prdCaract.produit_type_id = " .$id ." AND prdCaract.saisie_id = 1";
        
        return $this->_em->getConnection()->fetchAll($sql);
    }
}