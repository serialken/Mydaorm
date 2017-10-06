<?php

namespace Ams\SilogBundle\Repository;

use Doctrine\ORM\EntityRepository;
use \Doctrine\Common\Collections\ArrayCollection;

/**
 * 
 *
 * 
 */
class CategorieRepository extends EntityRepository {

    public function getArboresence() {
        $qb = $this->_em->createQueryBuilder()
                ->select('cat')
                ->from('AmsSilogBundle:Categorie', 'cat')
                ->leftJoin('cat.ssCategories', 'scat')
                ->addSelect('scat')
                ->leftJoin('scat.pages', 'pag')
                ->addSelect('pag')
                ->leftJoin('pag.pageElements', 'pel')
                ->addSelect('pel');
        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Recuperation des categories 
     * auxquelles le profil a accés
     * @param type $profilId
     * @return type
     */
    public function getCategorieByProfil($profilId, $categorieId = 0) {

        $sql = " SELECT 
                      cat.id as CAT_ID, cat.libelle as CAT_LIB, cat.class_image as IMG_CAT, cat.page_defaut AS PAGE_DEFAUT,
                      scat.id as SCAT_ID, scat.libelle as LIB_SOUS_CAT, scat.class_image as IMG_SCAT, scat.page_defaut as PAGE_DEFAUT_SCAT
                    FROM categorie cat 
                    LEFT JOIN sous_categorie 
                        scat ON cat.id = scat.cat_id
                    LEFT JOIN page p
                        ON scat.id = p.ss_cat_id
                    LEFT JOIN page_element pel
                        ON p.id = pel.pag_id
                    LEFT JOIN profil_page_element ppe
                        ON pel.id = ppe.page_elem_id
                    WHERE ppe.profil_id = '" . $profilId . "'";
        
         if($categorieId > 0) {
             $sql .=" AND cat.id= '".$categorieId."'";
         }
                    
         $sql .= " GROUP BY scat.id";
        return $this->_em->getConnection()->fetchAll($sql);
    }

}
