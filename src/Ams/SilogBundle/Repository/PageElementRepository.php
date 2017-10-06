<?php

namespace Ams\SilogBundle\Repository;
use Doctrine\ORM\EntityRepository;


/**
 * 
 * Tous les droits pour une page
 * Pour instancier cette classe, faire : PagesElements::getInstance($this->get('doctrine.dbal.mroad_connection') 
 * 
 * @author aandrianiaina
 *
 */
class PageElementRepository extends EntityRepository {

    
      public function getPageElementByRoute($routeId){
        
        $qb =$this->_em->createQueryBuilder()
                ->select('pel','pag')
                ->from('AmsSilogBundle:PageElement', 'pel')
                ->leftJoin('pel.page', 'pag')
                ->addSelect('pag')
                ->andWhere('pag.idRoute =:route')
                ->setParameter('route', $routeId)
                ;
        
        return $qb->getQuery()
                ->getResult();
        
    }
    
    
   /**
   * Liste des element d'une page accessible a un profil
   * @param type $profilId
   * @param type $routeId
   * @return type
   * 
   */  
  public function getElementAcessible($profilId, $routeId) {

        $sql = "SELECT pel.desc_court FROM page_element pel
                 LEFT JOIN profil_page_element ppe ON pel.id = ppe.page_elem_id 
                 LEFT JOIN page p ON p.id = pel.pag_id 
                 WHERE p.id_route = '" . $routeId . "' AND ppe.profil_id = '" . $profilId . "'";

        return $this->_em->getConnection()->fetchAll($sql);
    }

    
    	
  
}
