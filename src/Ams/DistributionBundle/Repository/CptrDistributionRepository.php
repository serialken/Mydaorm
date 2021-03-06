<?php

namespace Ams\DistributionBundle\Repository;

use Ams\SilogBundle\Repository\GlobalRepository;

/**
 * CptrDistributionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CptrDistributionRepository extends GlobalRepository
{   


    public function getCountReclamationByDepotAndTournee($date){
            $sql = "CREATE OR REPLACE VIEW cptr_nbr_reclam AS (SELECT pt.depot_id,pt.code, pt.id,COUNT(cdet.id) as nbr_reclam
                        FROM crm_detail cdet LEFT JOIN crm_demande cdem ON cdet.crm_demande_id = cdem.id
                       
                        INNER JOIN pai_tournee pt
                        WHERE   cdet.depot_id = pt.depot_id 
                        AND cdem.crm_categorie_id = '1' AND cdet.pai_tournee_id = pt.id AND '".$date."' BETWEEN cdet.date_debut AND cdet.date_fin
                        group by pt.depot_id, pt.id)";
            $this->_em->getConnection()->prepare($sql)->execute();

    }

    /**
     * [getTableCompteRenduDistribution description]
     * @param  [type] $date      [description]
     * @param  [type] $depot_ids [description]
     * @return [type]            [description]
     */
    public function getTableCompteRenduDistribution($date, $depot_ids,$flux = false)
    {
         
        $depot_ids = implode(',',$depot_ids);
     
        $sql = "SELECT DISTINCT d.id as depot_id, 
                    d.libelle AS libelle_depot, 
                 
                    nbrec.nbr_reclam as reclam,
                    mtj.id as tournee_id,
                    mtj.code, 
                    tyInci.libelle as incident_libelle,
                    tyInci.id as incident_id,
                    cd.id as cptrd_id,
                    cd.nb_abonne_non_livre,
                    cd.nb_diff_non_livre,
                    cd.heure_fin_tournee,
                    cd.cmt_incident_ab as incident_ab,
                    cd.cmt_incident_diff as incident_diff,
                    ta.libelle as anomalie_libelle,
                    ta.id as anomalie_id,
                    c.id as commune_id,
                    cd.depot_id AS depot_id_cptr,
                    GROUP_CONCAT( distinct c.libelle SEPARATOR '\n') as villes,
                    GROUP_CONCAT( distinct c.id  SEPARATOR '/') as id_villes
                FROM client_a_servir_logist as cs INNER JOIN depot as d on d.id = cs.depot_id 
                    INNER JOIN commune as c on c.id = cs.commune_id 
                    LEFT JOIN cptr_distribution cd ON cd.tournee_id = cs.tournee_jour_id AND cd.date_cpt_rendu= '".$date."'
                    LEFT JOIN cptr_type_anomalie ta ON ta.id = cd.type_anomalie_id
                    LEFT JOIN cptr_type_incident as tyInci on tyInci.id = cd.type_incident_id
                    LEFT JOIN cptrdistribution_commune as cptc on cptc.cptrdistribution_id = cd.id
                    INNER JOIN modele_tournee_jour AS mtj ON cs.tournee_jour_id = mtj.id
                    LEFT JOIN cptr_nbr_reclam as nbrec on nbrec.depot_id = cd.depot_id  and  nbrec.id = mtj.id
                WHERE d.id in ($depot_ids) ";
        if($flux != false)
            $sql .= "AND cs.flux_id = $flux ";
            $sql .= "AND cs.date_distrib ='".$date."'
                    GROUP BY cs.tournee_jour_id,d.id order by mtj.code, d.id";
            
            return $this->_em->getConnection()->fetchAll($sql);
    }
    
    /**
     * [getInfosFromDepot description]
     * @param  [type] $id   [description]
     * @param  [type] $date [description]
     * @return [type]       [description]
     */
    public function getInfosFromDepot($id, $date){
        $sql = "SELECT 
                d.libelle as libelle_depot, 
                mtj.code as libelle_tournee,
               '' as type_incident,/* cd.type_incident,*/
                cd.nb_ex_abo,
                cd.nb_ex_diff,
                cd.heure_fin_tournee,
                cd.cmt_incident_ab,
                cd.cmt_incident_diff,
                group_concat((SELECT com.libelle FROM commune com WHERE c.commune_id = com.id )) as villes
            FROM 
                commune com,
                client_a_servir_logist c
            INNER JOIN depot d ON c.depot_id = d.id
            INNER JOIN groupe_tournee AS gt ON d.id = gt.depot_id
            INNER JOIN modele_tournee_jour mtj ON c.tournee_jour_id = mtj.id
            LEFT JOIN cptr_distribution cd ON mtj.id = cd.tournee_id
            WHERE 
                c.depot_id = '".$id."'
            AND 
                com.id = c.commune_id
            AND 
                c.date_distrib = '".$date."'
            group by libelle_tournee";

        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    /**
     * [isNew description]
     * @param  [type]  $param [description]
     * @param  [type]  $date  [description]
     * @return boolean        [description]
     */
    public function isNew($param, $date){
      
        $sql = "SELECT * 
                FROM 
                    cptr_distribution
                WHERE 
                    depot_id = '".$param[$param['ids'].'_depot_id']."'
                AND
                    tournee_id = '".$param[$param['ids'].'_tournee_id']."'
                AND
                    date_cpt_rendu = '".$date."';";

        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    /**
     * [insert description]
     * @param  [type] $param [description]
     * @param  [type] $date  [description]
     * @return [type]        [description]
     */
    public function insert($param, $date) {
    
        if(empty($param[$param['ids'].'_heure_fin_tournee'])){
            $heure_fin_tournee = NULL;
        } else{
         $heure_fin_tournee = $param[$param['ids'].'_heure_fin_tournee'].'';
        }

        if(empty($param[$param['ids'].'_type_incident'])){
            $param[$param['ids'].'_type_incident'] = NULL;
        }
        if(empty($param[$param['ids'].'_retard_non_livraison'])){
            $param[$param['ids'].'_retard_non_livraison'] = NULL;
        } 

        $sql = "INSERT INTO cptr_distribution 
                        (depot_id, 
                        tournee_id, 
                        type_anomalie_id,
                        type_incident_id, 
                        nb_ex_abo, 
                        nb_ex_diff, 
                        heure_fin_tournee, 
                        cmt_incident_ab,
                        cmt_incident_diff, 
                        date_cpt_rendu)
                VALUES(?,?,?,?,?,?,?,?,?,?)";
                    $this->_em->getConnection()->executeUpdate($sql,array(
                                                            $param[$param['ids'].'_depot_id'],
                                                            $param[$param['ids'].'_tournee_id'],
                                                            $param[$param['ids'].'_retard_non_livraison'],
                                                            $param[$param['ids'].'_type_incident'],
                                                            $param[$param['ids'].'_nb_ex_abo'],
                                                            $param[$param['ids'].'_nb_ex_diff'],
                                                            $heure_fin_tournee,
                                                            $param[$param['ids'].'_incident_abo'],
                                                            $param[$param['ids'].'_incident_diff'],
                                                            $date
                                                            
                                                        ));
                $id = $this->_em->getConnection()->lastInsertId();
               
                $this->insertIntoCptrDistributionCommune($param[$param['ids'].'_id_villes'], $id);
       
        return true;
    }
    
    /**
     * [insertIntoCptrDistributionCommune description]
     * @param  [type] $aCommune_ids [description]
     * @param  [type] $id           [description]
     * @return [type]               [description]
     */
    public function insertIntoCptrDistributionCommune($aCommune_ids, $id){
        $aCommune_ids = explode('/', $aCommune_ids);
        $cptrDistrib =  $this->_em->getRepository('AmsDistributionBundle:CptrDistribution')->findOneById( $id);

        foreach ($aCommune_ids as $key => $value) {
            $commune =  $this->_em->getRepository('AmsAdresseBundle:Commune')->findOneById($value);
            $cptrDistrib->addVille( $commune );
            $this->_em->flush();
        }

         return true;

    }
    
    /**
     * [insertTocreateCptrDistribId description]
     * @param  [type] $param [description]
     * @param  [type] $date  [description]
     * @return [type]        [description]
     */
    public function insertTocreateCptrDistribId($param, $date){

            $sql = "INSERT INTO cptr_distribution 
                        (depot_id, 
                        tournee_id, 
                        date_cpt_rendu) 
                    VALUES(?,?,?)";
            $this->_em->getConnection()->executeUpdate($sql,array(
                                                        $param[$param['ids'].'_depot_id'], 
                                                        $param[$param['ids'].'_tournee_id'], 
                                                        $date));
          
            $id = $this->_em->getConnection()->lastInsertId();
       
        return $id;
    }

    /**
     * [update description]
     * @param  [type] $param [description]
     * @param  [type] $id    [description]
     * @return [type]        [description]
     */
    public function update($param, $id) {
       // var_dump($param);die();

        if(empty($param[$param['ids'].'_type_incident'])){
            $param[$param['ids'].'_type_incident'] = NULL;
        } 
        if(empty($param[$param['ids'].'_retard_non_livraison'])){
            $param[$param['ids'].'_retard_non_livraison'] = NULL;
        } 
        if(empty($param[$param['ids'].'_heure_fin_tournee'])){
            $param[$param['ids'].'_heure_fin_tournee'] = NULL;
        } 

            $sql = "UPDATE cptr_distribution SET 
                type_incident_id = ?,
                type_anomalie_id =  ?,
              
                heure_fin_tournee =  ?,
                cmt_incident_ab =  ?,
                cmt_incident_diff =  ?
                WHERE id = ?";
              
            $this->_em->getConnection()->executeUpdate($sql,array(
                                                                $param[$param['ids'].'_type_incident'],
                                                                $param[$param['ids'].'_retard_non_livraison'],
                                                               
                                                                $param[$param['ids'].'_heure_fin_tournee'] , 
                                                                $param[$param['ids'].'_incident_abo'] ,
                                                                $param[$param['ids'].'_incident_diff'],
                                                                $id));

           

        return true;
    }
    
    /**
     * [getAllType description]
     * @return [type] [description]
     */
    public function getAllType(){
        $sql = "select * from cptr_type_incident";
        
        return $this->_em->getConnection()->fetchAll($sql);
    }
}
