<?php 

namespace Ams\ProduitBundle\Repository;

use Doctrine\ORM\EntityRepository;

class PrdCaractConstanteRepository extends EntityRepository
{
    public function getConstByProduitAndCaract($prdId, $caractId){
        $qb = $this->createQueryBuilder('PrdCaractConst')
                    ->join('prd_type.caracts', 'caracts')
                    ->join('caracts.saisie','saisie')
                    ->where('prd_type.id = :id')
                    ->andWhere('saisie.code = :code')
                    ->setParameter('id', $id)
                    ->setParameter('code', $saisieCode)
                    ->addSelect("caracts")
            ;
         return $qb->getQuery()->getOneOrNullResult();
    }
    
    public function getConstByProduitId($id)
    {
       
        
//        $qb = $this->createQueryBuilder('prdCaractConst')
//                ->where('prdCaractConst.produit_id = :id')
//                ->setParameter('id', $id);
//        return $qb->getQuery()->getResult();
        $sql = "SELECT 
                   prdCaractConst.id as prdCaractConstId,
                   prdCaractConst.produit_id as produitId,
                   prdCaractConst.prd_caract_id as prdCaractId,
                   prdCaractConst.utilisateur_id as userId,
                   prdCaractConst.valeur_int as valInt,
                   prdCaractConst.valeur_float as valFloat,
                   prdCaractConst.valeur_string as valString,
                   caract.libelle as constLibelle,
                   caract.code as constCode,
                   caract.caract_type_id as caractTypeId,
                   champ.libelle as constType
                   
                FROM 
                     prd_caract_constante prdCaractConst
                INNER JOIN
                    prd_caract caract ON caract.id = prdCaractConst.prd_caract_id
                INNER JOIN
                    prd_caract_type champ ON champ.id = caract.caract_type_id
                WHERE 
                prdCaractConst.produit_id = " .$id ." ";
        return $this->_em->getConnection()->fetchAll($sql);
        
    }
    
    public function getExistingData($prdCaractId, $produitId)
    {
        $sql = "SELECT
                    *
                FROM
                    prd_caract_constante
                WHERE
                produit_id = " . $produitId . " AND prd_caract_id = " . $prdCaractId . "";
        
        return $this->_em->getConnection()->fetchAll($sql);
        
    }
    
    public function getAllByProduitId($id)
    {
         $sql = "SELECT
                    *
                FROM
                    prd_caract_constante
                WHERE
                produit_id = " . $id . "";
        
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    function delDataByProduitId($produitId)
    {
        $sql = "DELETE FROM prd_caract_constante WHERE produit_id = " . $produitId ;
        return $this->_em->getConnection()->prepare($sql)->execute();
    }
    
    
    
    
//    public function getDataByProduitId()
//    {
//         $sql = "SELECT 
//                        prdCaract.id as id,
//                        prdCaract.libelle as constLibelle,
//                        type.libelle as titreLibelle,
//                        prdCaract.code as constCode,
//                        champ.libelle as constType
//                  FROM 
//                        prd_caract_constante prdConstante
//                  INNER JOIN
//                        produit_type type ON type.id = prdCaract.produit_type_id
//                  INNER JOIN 
//                        prd_caract_type champ ON champ.id = prdCaract.caract_type_id
//                  WHERE 
//                        prdCaract.saisie_id = 1";
//         
//         return $this->_em->getConnection()->fetchAll($sql);
//    }
}