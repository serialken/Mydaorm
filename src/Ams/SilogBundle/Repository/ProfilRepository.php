<?php

namespace Ams\SilogBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * 
 * Tous les profils
 * Pour instancier cette classe, faire : Profils::getInstance($this->get('doctrine.dbal.mroad_connection')) 
 * 
 * @author aandrianiaina
 *
 */
class ProfilRepository extends EntityRepository {

    public function getPageElementByProfilAndRoute($profilId, $routeId) {

        $qb = $this->_em->createQueryBuilder()
                ->select('pel', 'pag', 'prf')
                ->from('AmsSilogBundle:Profil', 'prf')
                ->leftJoin('prf.pageElements', 'pel')
                ->addSelect('pel')
                ->leftJoin('pel.page', 'pag')
                ->addSelect('pag')
                ->where('prf.id =:profilId')
                ->andWhere('pag.idRoute =:route')
                ->setParameter('profilId', $profilId)
                ->setParameter('route', $routeId)
        ;

        return $qb->getQuery()
                        ->getSingleResult();
    }

    /**
     *  mise à jour d'un profil
     */
    public function updateProfil($profilId, $pageElements) {
        
        $sql = " DELETE 
                 FROM
                     profil_page_element   
                WHERE 
                     profil_id = '" . $profilId . "'";
        $this->_em->getConnection()->exec($sql);
        
        if(count($pageElements) > 0) {
                $insert = "INSERT INTO profil_page_element  (profil_id, page_elem_id) VALUES ";
                foreach ($pageElements as $pageElement) {
                    $insert .= '(' . $profilId . ',' . $pageElement . '),';
                }
                $insert = substr($insert, 0, -1);
                $this->_em->getConnection()->exec($insert);
        }
    }
}
