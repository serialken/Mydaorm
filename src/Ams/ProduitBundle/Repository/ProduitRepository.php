<?php 

namespace Ams\ProduitBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ProduitRepository extends EntityRepository
{
    public function getParentsForm($id){
        $qb = $this->createQueryBuilder('d')
            ->where('d.societe = :id')
            ->setParameter('id',$id)
            ->orderBy('d.libelle','ASC')
            ;
        return $qb;
    }
    
    public function getProduitsArray(){
        $qb = $this->createQueryBuilder('d')
            ->orderBy('d.libelle','ASC')
            ;
        return $qb->getQuery()->getArrayResult();
    }
    
    public function getProduitCaractsBySaisie($prdId, $prdTypeId, $prdRefSaisieCode){
        $qb = $this->createQueryBuilder('prd')
                ->leftJoin('prd.produitType', 'prdType')
                ->addSelect("prdType")
                ->leftJoin('prdType.caracts', 'caracts')
                ->addSelect('caracts')
                ->leftJoin('caracts.saisie', 'saisie')
                ->where('prd.id = :prdId')
                ->andWhere('prdType.id = :prdTypeId')
                ->andWhere('saisie.code = :prdRefSaisieCode')
                ->setParameter('prdId',$prdId)
                ->setParameter('prdTypeId',$prdTypeId)
                ->setParameter('prdRefSaisieCode',$prdRefSaisieCode)
            ;

         return $qb->getQuery()->getOneOrNullResult();
    }
    
    /**
     * Produits ordonnances en fonction du libelle
     * @param array $ids
     * @return type
     */
    public function findByIdOrderByLibelle($ids){
        $qb = $this->createQueryBuilder('p');
        $qb->where($qb->expr()->in('p.id' , ':ids'))
                ->orderBy('p.libelle','ASC')
                ->setParameter(':ids', $ids);
        return $qb->getQuery()->getResult();
    }

    function selectComboDate($date_distrib) {
        $sql = "SELECT
                id,
                concat(libelle,if (p.type_id in (2,3),' (supplément)','')) as libelle
                FROM produit p
                WHERE '".$date_distrib."' between p.date_debut and coalesce(p.date_fin,'2099-12-31')
                ORDER BY libelle"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
    }

    function selectCombo() {
        $sql = "SELECT
                id,
                concat(libelle,if (p.type_id in (2,3),' (supplément)','')) as libelle
                FROM produit p
                ORDER BY libelle"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    function selectComboSupplement() {
        $sql = "SELECT
                id,
                libelle
                FROM produit p
                WHERE p.type_id in (2,3)
                ORDER BY libelle"
                ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    function getDataByParentsId($id)
    {
        $sql = "SELECT * FROM dependance_produit WHERE parent_id =" . $id ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    function getDataByEnfantsId($id)
    {
        $sql = "SELECT * FROM dependance_produit WHERE enfant_id =" . $id ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    function getParentsByEnfants($id)
    {
        $sql = "SELECT parent_id FROM dependance_produit WHERE enfant_id =" . $id ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    function delDataByParentId($parentId, $enfantId)
    {
        $sql = "DELETE FROM dependance_produit WHERE parent_id = " . $parentId  . " AND enfant_id = " . $enfantId ;
        return $this->_em->getConnection()->prepare($sql)->execute();
    }
    
     function getEnfantsByParents($id)
    {
        $sql = "SELECT enfant_id FROM dependance_produit WHERE parent_id =" . $id ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
     function delDataByEnfantsId($enfantId, $parentId)
    {
        $sql = "DELETE FROM dependance_produit WHERE parent_id = " . $parentId  . " AND enfant_id = " . $enfantId ;
        return $this->_em->getConnection()->prepare($sql)->execute();
    }
    
    function checkExistingRecord($parentId, $enfantId)
    {
        
        $sql = "SELECT * FROM dependance_produit WHERE parent_id = " . $parentId . " AND enfant_id = " . $enfantId ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    /**
     * Retourne une liste de produits pour une société donnée
     * @param int $socId L'ID de la société
     */
    public function getProdsForCompany($socId){
        $sql = 'SELECT id, soc_code_ext, prd_code_ext, spr_code_ext, CODE AS code, libelle FROM produit WHERE societe_id = '.$socId;
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    
    /** 
     * liste de produit encours
     * @return type
     */   
    public function getProduitListe()
    {
        $fin = new \DateTime();
        $qb = $this->createQueryBuilder('produit')
                ->where('produit.dateFin > :fin')
                ->setParameter('fin',$fin)
                ->orderBy('produit.libelle', 'ASC')
            ;

        return $qb;
    }
    
     /**
     * Retourne 
     * @param int prodId L'ID du produit
     */
    public function getProduit($prodId){
        $sql = 'SELECT id,flux_id FROM produit WHERE id = '.$prodId.' Limit 1';
        return $this->_em->getConnection()->fetchAssoc($sql);
    }
    
}