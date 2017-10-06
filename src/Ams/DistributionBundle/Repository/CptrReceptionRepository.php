<?php

namespace Ams\DistributionBundle\Repository;

use Ams\SilogBundle\Repository\GlobalRepository;

/**
 * CptrReceptionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CptrReceptionRepository extends GlobalRepository
{
    /*
     * Requête effectuant un check sur la base pour voir si la prochaine requête a effectuer sera un update ou un insert
     */
    public function isNew($param, $date, $produit_id) {
        
        $sql = "SELECT * 
                FROM 
                    cptr_reception
                WHERE 
                    depot_id LIKE '".$param[$param['ids'].'_depots']."'
                AND
                    groupe LIKE '".$param[$param['ids'].'_groupes']."'
                AND
                    code LIKE '".$param[$param['ids'].'_code']."'
                AND
                    product_id LIKE '".$produit_id."'
                AND
                    DATE(date_cpt_rendu) LIKE '" . $date . "';";

        return $this->_em->getConnection()->fetchAll($sql);
    }
    /*
     * Insertion de la ligne pour la table cptr_recepetion 
     */
    public function insert($param, $product_id, $date, $dateCptrReception) {
        try {
            $sql = "INSERT INTO cptr_reception 
                        (depot_id, 
                        product_id,
                        groupe, 
                        code, 
                        qte_prevue, 
                        qte_recue, 
                        heure_reception, 
                        commentaires,
                        date_cpt_rendu) 
                    VALUES 
                        ('".$param[$param['ids'].'_depots']."', 
                        '".$product_id."',
                        '".$param[$param['ids'].'_groupes']."',
                        '".$param[$param['ids'].'_code']."', 
                        '".$param[$param['ids'].'_qte_prevues']."', 
                        '".$param[$param['ids'].'_qte_recues']."', 
                        '".$dateCptrReception."', 
                        '".$param[$param['ids'].'_commentaires']."',
                        '".$date."');";
            
            $this->_em->getConnection()->prepare($sql)->execute();
            $id = $this->_em->getConnection()->lastInsertId();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "L'activite doit être unique.","UNIQUE","");
        }
        return true;
    }
    /*
     * Update de la ligne pour la table cptr_reception
     */
    public function update($param, $product_id, $id, $dateCptrReception) {   
        
        try {

            $sql = "UPDATE cptr_reception 
                SET 
                    qte_recue = '" . $param[$param['ids'].'_qte_recues'] . "',
                    heure_reception = '" . $dateCptrReception . "',
                    commentaires = '" . $param[$param['ids'].'_commentaires'] . "'
                WHERE 
                    id = " . $id . "
                AND 
                    product_id = '".$product_id."'";
            $this->_em->getConnection()->prepare($sql)->execute();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "L'activite doit être unique.","UNIQUE","");
        }
        return true;
    }
    /*
     * Récupération de la liste des produits pour le filtre
     */
    public function getallproducts($date){
        $sql = "SELECT DISTINCT 
                    p.id, 
                    p.libelle
                FROM client_a_servir_logist AS c
                INNER JOIN produit AS p ON p.id = c.produit_id
                WHERE 
                        DATE(now()) BETWEEN p.date_debut AND p.date_fin
                AND 
                        c.date_distrib = '".$date."'
                ORDER BY p.id";

        return $this->_em->getConnection()->fetchAll($sql);
    }

        public function getCptrRecptionByDateAndDepot($date, $depot){
        $sql = "SELECT distinct cptr.id as cptr_id,
                        qte_prevue,
                        qte_recue ,
                        DATE_FORMAT(heure_reception,'%H:%i') as heure_reception,
                        commentaires,
                        prd.libelle as libelle_produit,
                        fc.path as imgPath,
                        product_id as produit_id
               
                FROM 
                    cptr_reception as cptr
                
                INNER JOIN 
                     produit AS prd ON prd.id = cptr.product_id
                LEFT JOIN 
                fichier AS fc ON fc.id = prd.image_id 
                      
                WHERE 
                    cptr.depot_id = $depot
                AND
                    date_cpt_rendu =  '".$date."'";
              return $this->_em->getConnection()->fetchAll($sql);      

    }


    /**
   * [getCptrReceptionTo récupération des données (compte rendu de reception) a exporter vers Jade]
     * 
     * @param array $aDate
     * @param string $sFluxCode
     * @param string $sSoc
   * @return [type] [description]
   */
    public function getCptrReceptionToExport($aDate, $sFluxCode = 'N', $sSoc='tout'){
         $sql = "SELECT   /*s.id as societe_id,*/
                        DATE_FORMAT(cptr.date_cpt_rendu,'%Y/%m/%d') as date_distrib,/*= date de distribution*/
                        d.code as depot_id ,
                        p.soc_code_ext as code_societe,
                        p.prd_code_ext as code_produit,
                        p.spr_code_ext as code_edition,
                        p.flux_id as prd_flux_id,
                        cptr.id  as cptr_id,
                        DATE_FORMAT(cptr.heure_reception,'%Y/%m/%d %H:%i') as date_reception,
                        cptr.qte_recue as qte_recue,
                        REPLACE(REPLACE(REPLACE(REPLACE(TRIM(IFNULL(cptr.commentaires, '')), '\t', ''), '\r', ''), '\n', ''),'|','') AS commentaires 
                    from cptr_reception cptr 
                        LEFT JOIN produit  p ON p.id = cptr.product_id 
                        LEFT JOIN ref_flux f ON p.flux_id = f.id 
                        LEFT JOIN depot  d ON d.id = cptr.depot_id 
                    WHERE  cptr.date_export is null 
                        ".(($sFluxCode!="tout") ? " AND f.code = '".$sFluxCode."' " : "")."
                        ".(($sSoc!="tout") ? " AND p.soc_code_ext IN ('".str_replace(",", "', '", str_replace(' ', '', $sSoc))."')" : "")." 
                    AND cptr.date_cpt_rendu  IN('".implode("','",$aDate)."')
                        ORDER BY  p.flux_id,  d.code ,p.soc_code_ext,p.prd_code_ext,  p.spr_code_ext,cptr.date_cpt_rendu  asc";
            return $this->_em->getConnection()->fetchAll($sql);
 
    }

    /**
     * [UpdateDateExoptInCrmDetail mettre à jour le champ date export qui spécifie la date de l'export des données(compte rendu  reception) vers jade]
     * @param [type] $data [description]
     */
    public function UpdateDateExopt($data){

       // $em = $this->getContainer()->get('doctrine')->getManager();
        $now = new \DateTime();
        foreach ($data as $key => $row) {
            $crm = $this->_em->getRepository('AmsDistributionBundle:CptrReception')->findOneById($row['cptr_id']);
            $crm->setDateExport($now);
           
        }
        $this->_em->flush();
       
    }

    /**
     * [getAllProduitFlux description]
     * @return [type] [description]
     */
    public function getAllProduitFlux(){
        $sql = "SELECT id, libelle
                FROM ref_flux";
        return $this->_em->getConnection()->fetchAll($sql);

    }

    public function getProductPcoNonRecu($date,$depot){
        $sql = "
                SELECT product_id 
                FROM 
                    cptr_reception AS c
                WHERE 
                    c.depot_id = $depot 
                AND 
                    c.date_cpt_rendu = '".$date."'
                AND 
                    c.qte_recue = 0
                ";

        return $this->_em->getConnection()->fetchAll($sql);
    }
    
}
