<?php 
namespace Ams\SilogBundle\Repository;
use Doctrine\ORM\EntityRepository;

/**
 * 
 */
class PageRepository extends EntityRepository
{
	
    public function getPage($idRoute){

        $qb = $this->_em->createQueryBuilder()
                ->select('pag', 'scat', 'cat')
                ->from('AmsSilogBundle:Page', 'pag')
                ->leftJoin('pag.ssCategorie', 'scat')
                ->addSelect('scat')
                ->leftJoin('scat.categorie', 'cat')
                ->addSelect('cat')
                ->where('pag.idRoute = :idRoute' )
                ->setParameter('idRoute', $idRoute);

     
        return $qb->getQuery()
                        ->getResult();
        
    }
    
    
   /**
   * Liste des pages d'une sous categorieaccessibles a un profil
   * @param type $profilId
   * @param int $sousCategorieId 
   * @return type
   * 
   */  
  public function getPageAcessible($profilId, $sousCategorieId) {

        $sql = "SELECT DISTINCT pag.desc_court, pag.id_route,pag.menu FROM page pag
                 LEFT JOIN sous_categorie ssc ON pag.ss_cat_id = ssc.id
                 LEFT JOIN page_element pel ON pag.id = pel.pag_id
                 LEFT JOIN profil_page_element ppe ON pel.id = ppe.page_elem_id 
                 
                 WHERE ssc.id = '" . $sousCategorieId . "' AND ppe.profil_id = '" . $profilId . "'";

        return $this->_em->getConnection()->fetchAll($sql);
    }

}
