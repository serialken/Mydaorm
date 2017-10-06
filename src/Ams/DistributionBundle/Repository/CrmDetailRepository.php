<?php

namespace Ams\DistributionBundle\Repository;
use Doctrine\ORM\Query;
use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\DBALException;

class CrmDetailRepository extends EntityRepository {
    
    /**
     * METHODE QUI RETOURNE LES TOURNEES DES RECLAMATIONS EN SE BASANT SUR LE DEPOT ET LA DATE 
     * @param type $depot
     * @param type $date
     */
    public function getTourneeIdByDepotDate($depot,$startDate,$endDate){
        $sql = 'SELECT pt.code,pt.modele_tournee_jour_id as id FROM crm_detail crm
                RIGHT JOIN pai_tournee pt ON pt.id = crm.pai_tournee_id
                WHERE crm.date_creat BETWEEN "'.$startDate.' 00:00:00" AND "'.$endDate.' 23:59:59"
                AND crm.depot_id = '.$depot.' ORDER BY pt.code'
            ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
   
   /**
    * [getCountRecBySocieteAndDepot calcule le nombre de reclamaton/remonté info par couple(societe,depot)]
    * @param  [integer] $categorieId     [1 si reclamation 2 si remontée d'info]
    * @param  [string] $ar               [index du tableau :countRec si reclamation /countRem si remonté dinfo]
    * @param  [type] $dateMin [date min de création]
    * @param  [type] $dateMax [date max de création]
    * @return [array]                  [tableau des objet crmDetail]
    */

    public function getCountRecBySocieteAndDepot($categorieId,$ar, $dateMin, $dateMax){
     
        $qb = $this->createQueryBuilder('c')
        ->addSelect('count(c.id) as '."$ar")
            ->join('c.societe', 'societe')
            ->join('c.depot', 'depot')
            ->join('c.crmDemande', 'crmDemande')
            ->where('crmDemande.crmCategorie =:categorieId')
            ->andWhere('c.dateCreat >:dateMin')
            ->andWhere('c.dateCreat <=:dateMax')
            ->setParameters(array(':categorieId'=>$categorieId, ':dateMin'=>$dateMin, ':dateMax'=>$dateMax))
            ->groupBy('societe')
            ->addGroupBy('depot')
            ->orderBy('societe.libelle','ASC')
            ->addOrderBy('depot.libelle','ASC');
        
        return $qb->getQuery()->getResult();
            
    }

    
    /**
     * [getTotalRecByDepot calcule le nombre  total des reclamation/remontée info par dépot]
     * @param  [type] $categorieId     [1 si reclamation 2 si remontée d'info]
     * @param  [type] $ar              [index du tableau :countRec si reclamation /countRem si remonté dinfo]
     * @param  [type] $dateMin [date min de création]
     * @param  [type] $dateMax [date max de création]
     * @return [array]                  [tableau des objet crmDetail]
     */
    public function getTotalRecByDepot($categorieId,$ar, $dateMin, $dateMax){

        $qb = $this->createQueryBuilder('c')
            ->addSelect('count(c.id) as '."$ar")

           ->join('c.societe', 'societe')
            ->join('c.depot', 'depot')
            ->join('c.crmDemande', 'crmDemande')
            ->where('crmDemande.crmCategorie =:categorieId')
            ->andWhere('c.dateCreat >:dateMin')
            ->andWhere('c.dateCreat <=:dateMax')
           /* ->andWhere('c.societe in (:soc_id)')*/
            ->setParameters(array(':categorieId'=>$categorieId, ':dateMin'=>$dateMin, ':dateMax'=>$dateMax,/*':soc_id'=>$soc_id*/))
            ->groupBy('depot')     
            ->orderBy('depot.libelle','ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * [getSocietsFromCrmByDate description]
     * @param  [type] $dateMin [description]
     * @param  [type] $dateMax   [description]
     * @return [type]            [description]
     */
    public function getSocietsFromCrmByDate($dateMin, $dateMax){

         $qb = $this->createQueryBuilder('c');
                $qb->select('distinct c')
                ->innerJoin('c.societe', 'societe')
                ->innerJoin('c.depot', 'depot')
                ->where('c.dateCreat > :dateMin')
                ->AndWhere('c.dateCreat <= :dateMax')   
               // ->AndWhere($qb->expr()->between('c.dateParution', ':startDate', ':endDate'))
                ->setParameters(array(':dateMin'=>$dateMin,':dateMax'=>$dateMax))
                ->groupBy('societe')
                ->orderBy('societe.libelle');
               
        return $qb->getQuery()->getResult();
   }
    
    /**
     * [getTotalRecResponseByDepot calcule le nombre  total des réponse pour les reclamation/remontée info par dépot]]
     * @param  [type] $categorieId     [1 si reclamation 2 si remontée d'info]
     * @param  [type] $ar              [index du tableau :countRecResponse si reclamation /countRemResponse si remonté dinfo]
     * @param  [type] $dateMin [date min de création]
     * @param  [type] $dateMax [date max de création]
     * @return [array]                  [tableau des objet crmDetail]
     */
    public function getTotalRecResponseByDepot($categorieId,$ar, $dateMin, $dateMax){
  
        $qb = $this->createQueryBuilder('c')
                   ->addSelect('count(c.id) as '."$ar")
                   ->join('c.societe', 'societe')
                   ->join('c.depot', 'depot')
                   ->join('c.crmDemande', 'crmDemande')
                   ->where('crmDemande.crmCategorie =:categorieId')
                   ->andWhere('c.dateCreat >:dateMin')
                   ->andWhere('c.dateCreat <=:dateMax')
                   /*->andWhere('c.societe in (:soc_id)')*/
                   ->setParameters(array(':categorieId'=>$categorieId, ':dateMin'=>$dateMin, ':dateMax'=>$dateMax/*,':soc_id'=>$soc_id*/))
                ;
                  //création de l'expression OR
                  $orExpression = $qb->expr()->orx();
                  $orExpression->add($qb->expr()->isNotNull('c.crmReponse'));
                  $orExpression->add($qb->expr()->isNotNull('c.cmtReponse'));

                  //Ajout de l'expression à la requête			
                  $qb->andWhere($orExpression)
        
                   ->groupBy('depot')     
                   ->orderBy('depot.libelle','ASC');
//var_dump($qb->getQuery()->getResult());
        return $qb->getQuery()->getResult();
    }
    
  
     /**
      * [getCountRecResponseBySocieteAndDepot calcule le nombre de réponse pour les reclamaton/remonté info par couple(societe,depot)]]
      * @param  [integer] $categorieId     [1 si reclamation 2 si remontée d'info]
      * @param  [string] $ar               [index du tableau :countRecRes si reclamation /countRemRes si remonté dinfo]
      * @param  [type] $dateMin    [date min de création]
      * @param  [type] $dateMax    [date max de création]
      * @return [array]                    [tableau des objet crmDetail]
      */
    public function getCountRecResponseBySocieteAndDepot($categorieId,$ar, $dateMin, $dateMax){
  
        $qb = $this->createQueryBuilder('c')
        ->addSelect('count(c.id) as '."$ar")
            ->join('c.societe', 'societe')
            ->join('c.depot', 'depot')
            ->join('c.crmDemande', 'crmDemande')
            ->where('crmDemande.crmCategorie =:categorieId')
            ->andWhere('c.dateCreat >:dateMin')
            ->andWhere('c.dateCreat <=:dateMax')
            ->setParameters(array(':categorieId'=>$categorieId, ':dateMin'=>$dateMin, ':dateMax'=>$dateMax))
            ->andWhere('c.crmReponse is not null')
            ->groupBy('societe')
            ->addGroupBy('depot')    
            ->orderBy('societe.libelle','ASC')
            ->addOrderBy('depot.libelle','ASC');
 
        return $qb->getQuery()->getResult();
    }

    //nombre total des reclamation/rem d'info par depot
    /**
     * [getCrmBySocieteAndDepot recherche des reclamation/rem d'info selon plusieurs critéres optionnelle]
     * @param  [type] $dateMin    [date min de création]
     * @param  [type] $dateMax    [date max de création]
     * @param  [integer] $societeId       [id societe]
     * @param  [integer] $depotId         [id dépot]
     * @param  [integer] $isWithResponse  [1/2 si on checheche des réclam avec ou sans réponse]
     * @param  [integer] $crmDemandeId    [id motif de la reclamation/remonté dinfo]
     * @return [array]                    [tableau des objets crmdetail]
     */
    public function getCrmBySocieteAndDepot($categorieId,$dateMin, $dateMax,$societeId, $depotId, $isWithResponse, $tourneeJour, $flux=NULL,$imputationPaie= false){
  
        $subReq = empty($societeId) ? "c.societe is not null" : "c.societe =:societeId";
        
       
        $parameters = array(':categorieId'=>$categorieId,':dateMin'=>$dateMin,':dateMax'=>$dateMax,':depotId'=>$depotId,);
        if(!empty($societeId)){
            $parameters = array_merge($parameters,array(':societeId'=>$societeId) );
        }
        
      
       /* if(!empty($crmDemandeId)){
            $parameters = array_merge($parameters,array(':crmDemandeId'=>$crmDemandeId) );
        }*/

        if(!empty($isWithResponse) and  $isWithResponse == 1)
        {
            $subReq2 = "c.crmReponse is not null"; //recherche que les reclam avec réponse
        }
        else if(!empty($isWithResponse) and $isWithResponse == 2)
        {
            $subReq2 = "c.crmReponse is  null"; //recherche que les reclam sans réponse
        }
        else
        {
            $subReq2 = "c.id is not null";//recherche tt les reclam 
        }
        
        if($tourneeJour != NULL ) {
            $subReq3= "c.tourneeJour =:tourneeJour";
            $parameters = array_merge($parameters,array(':tourneeJour'=>$tourneeJour) );
        }

          if($flux > 0 ) {
                $subReq4 = "tournee.flux =:flux";
                $parameters = array_merge($parameters,array(':flux'=>$flux) );
          }

            $qb = $this->createQueryBuilder('c')
                ->leftjoin('c.societe', 'societe')
                ->leftjoin('c.depot', 'depot')
                ->leftjoin('c.crmDemande', 'crmDemande')
                ->leftjoin('c.tournee', 'tournee')
                ->where('crmDemande.crmCategorie =:categorieId');
                if($imputationPaie){
                    $qb->andWhere('c.dateImputationPaie >= :dateMin')->andWhere('c.dateImputationPaie <=:dateMax');
                }
                else{
                    $qb->andWhere('c.dateCreat >:dateMin')->andWhere('c.dateCreat <=:dateMax');
                }
                    
                $qb->andWhere('c.depot =:depotId')
                ->andWhere($subReq)
                ->andWhere($subReq2);
              if(isset($subReq3))
                 $qb ->andWhere($subReq3);
               if(isset($subReq4))
                 $qb ->andWhere($subReq4);
              $qb->setParameters($parameters)
                ->orderBy('c.dateCreat','DESC')
               
                ;
              //echo $qb;
        return $qb->getQuery()->getResult();
            
    }
    
    // exclusion de la societe Le Parisien
    public function getCrmBySocieteAndDepotDiv($categorieId,$dateMin, $dateMax,$societeId, $depotId, $isWithResponse, $tourneeJour, $flux=NULL){
  
        $subReq = empty($societeId) ? "c.societe is not null" : "c.societe =:societeId";
        
       
        $parameters = array(':categorieId'=>$categorieId,':dateMin'=>$dateMin,':dateMax'=>$dateMax,':depotId'=>$depotId,);
        if(!empty($societeId)){
            $parameters = array_merge($parameters,array(':societeId'=>$societeId) );
        }
        
      
       /* if(!empty($crmDemandeId)){
            $parameters = array_merge($parameters,array(':crmDemandeId'=>$crmDemandeId) );
        }*/

        if(!empty($isWithResponse) and  $isWithResponse == 1)
        {
            $subReq2 = "c.crmReponse is not null"; //recherche que les reclam avec réponse
        }
        else if(!empty($isWithResponse) and $isWithResponse == 2)
        {
            $subReq2 = "c.crmReponse is  null"; //recherche que les reclam sans réponse
        }
        else
        {
            $subReq2 = "c.id is not null";//recherche tt les reclam 
        }
        
        if($tourneeJour != NULL ) {
            $subReq3= "c.tourneeJour =:tourneeJour";
            $parameters = array_merge($parameters,array(':tourneeJour'=>$tourneeJour) );
        }

          if($flux > 0 ) {
                $subReq4 = "tournee.flux =:flux";
                $parameters = array_merge($parameters,array(':flux'=>$flux) );
          }

            $qb = $this->createQueryBuilder('c')
                ->leftjoin('c.societe', 'societe')
                ->leftjoin('c.depot', 'depot')
                ->leftjoin('c.crmDemande', 'crmDemande')
                ->leftjoin('c.tournee', 'tournee')
                ->where('crmDemande.crmCategorie =:categorieId')
                ->andWhere('c.dateCreat >:dateMin')
                ->andWhere('c.dateCreat <=:dateMax')
                ->andWhere('c.depot =:depotId')
                ->andWhere('c.societe != 29')
                ->andWhere($subReq)
                ->andWhere($subReq2);
              if(isset($subReq3))
                 $qb ->andWhere($subReq3);
               if(isset($subReq4))
                 $qb ->andWhere($subReq4);
              $qb->setParameters($parameters)
                ->orderBy('c.dateCreat','DESC')
               
                ;
        return $qb->getQuery()->getResult();
            
    }
   /**
    * 
    * [serachReclam recheche que les reclamation selon plusieur critere]
    * @param  [integer] $categorieId     [1 si reclamation 2 si remontée d'info]
    * @param  [datetime] $dateMin        [date min de création]
    * @param  [datetime] $dateMax        [date max de création]
    * @param  [integer] $societeId       [id societe]
    * @param  [integer] $depotId         [id dépot]
    * @param  [integer] $isWithResponse  [1/0 si on checheche des réclam avec ou sans réponse]
    * @param  [integer] $crmDemandeId    [id de motif de  la demande ]
    * @param  [integer] $tourneeId       [id tournée]
    * @return [array]                    [tableau des objete]
    */
    public function serachReclam($categorieId, $dateMin, $dateMax, $societeId, $depotId, $isWithResponse, $crmDemandeId, $tourneeId, $aData = false){
      
        if($isWithResponse == 1){
            $subCondition = "and ((crm.crm_reponse_id is not null) OR (crm.utl_reponse_id is not null) )"; // pour demande client pas de crm_reponse_id
        }else if($isWithResponse == 2){
            $subCondition = "and crm.crm_reponse_id is null and crm.cmt_reponse is null ";
        }else{
            $subCondition = "and crm.id is not null";
        }

        $sql = "SELECT 
                        DISTINCT /*s.id as societe_id,*/
                        d.id as depot_id,
                        mtj.code as tournee_code,
                        crm.id as crm_id,
                        crm.crm_id_editeur as crm_id_editeur,
                        crmD.id as demande_id,
                        crm.date_creat as date_creat,
                        crm.pai_tournee_id as pai_tournee,
                        crm.date_debut as date_debut,
                        crm.date_fin as date_fin,
                        crm.numabo_ext as numabo_ext ,
                        s.libelle as societe_libelle,
                        s.id as societe_id,
                        crmC.libelle as cat_libelle,
                        crm.vol1,
                        crm.vol2,
                        crm.vol3,
                        crm.vol4,crm.vol5, crm.cp,crm.ville,
                        crmD.libelle as demande_libelle ,
                        crmD.code as demande_code ,
                        crmD.crm_categorie_id as crm_categorie_id,
                        crmR.libelle as response_libelle,
                        crm.cmt_demande as cmt_demande,
                        crm.cmt_reponse as cmt_response,
                        img.path as soc_image,
                        pt.code as pai_tournee_code
                       
                 FROM crm_detail crm 
                 INNER JOIN depot d ON d.id = crm.depot_id
                 LEFT JOIN societe s ON s.id = crm.societe_id
                 LEFT JOIN modele_tournee_jour mtj ON mtj.id = crm.modele_tournee_jour_id
                 LEFT JOIN modele_tournee mt ON mt.id = mtj.tournee_id
                 LEFT JOIN groupe_tournee gt ON gt.id = mt.groupe_id
                 LEFT JOIN pai_tournee pt ON pt.id = crm.pai_tournee_id
                                
                 LEFT JOIN crm_demande crmD ON crmD.id = crm.crm_demande_id
                 INNER JOIN crm_categorie crmC ON crmC.id = crmD.crm_categorie_id
                 LEFT JOIN crm_reponse crmR ON crmR.id = crm.crm_reponse_id
                 LEFT JOIN fichier  img on s.image_id = img.id
                 LEFT JOIN adresse a ON a.id = crm.adresse_id
              
                 WHERE 
                        crmD.crm_categorie_id = $categorieId ".$subCondition."
                        AND crm.date_creat <='". $dateMax."'and crm.date_creat >'". $dateMin ."'";

                if($depotId) $sql .= " AND d.id = $depotId ";
                if($tourneeId >0){  $sql .= " and crm.modele_tournee_jour_id = $tourneeId";}
                if(!empty($aData)) { 
                    if(!empty($aData['flux_id'])) $sql .= " and gt.flux_id = ".$aData['flux_id'];
                    if(!empty($aData['demandeArbitrage'])) 
                        $sql .= ($aData['demandeArbitrage'] == 1 ) ? 'AND utl_demande_arbitrage is not null' : 'AND utl_demande_arbitrage is null';
                }
                !empty($societeId) ?     $sql .= "  and s.id = $societeId" : "  and s.id is not null" ;
                !empty($crmDemandeId) ?  $sql .= "   and crmD.id = $crmDemandeId" : "  and crmD.id is not null" ;
                $sql .= " ORDER BY crm.modele_tournee_jour_id,crm.date_creat DESC";
//                echo $sql;die;
                return $this->_em->getConnection()->fetchAll($sql);
    }
    
    /**
     * [fetchAllReclamation récupération de toutes les réclamations]
     * @return [type] [description]
     */

    public function fetchAllReclamation($sDepots,$dLastPayDate,$dateRange = false){

        $qb =  $this->createQueryBuilder('crm_detail')
                    ->join('crm_detail.crmDemande', 'crm_demande')
                    ->addSelect('crm_demande')
                    ->leftJoin('crm_detail.societe', 'societe')
                    ->addSelect('societe')
                    ->leftJoin('crm_detail.utlSaisie', 'utlSaisie')
                    ->addSelect('utlSaisie')
                    ->leftJoin('crm_detail.utlReponse', 'utlReponse')
                    ->addSelect('utlReponse')
                    ->join('crm_detail.depot', 'depot')
                    ->addSelect('depot')
                    ->where('crm_demande.crmCategorie = :id_demande')
                    ->andWhere('crm_detail.dateDemandeArbitrage  is not null')
                    ->andWhere('depot.id  IN(:depots)')
                    ->setParameter('depots',  explode(',',$sDepots))
                    ->orderBy('crm_detail.dateDemandeArbitrage','DESC')
              
                  ->setParameter('id_demande',1);
      
        if($dateRange){
            $dateRange = explode('_', $dateRange);
            $dateMin = $this->transformDateToDataBaseFormatReverse($dateRange[0]) ;
            $dateMax = $this->transformDateToDataBaseFormatReverse($dateRange[1],true) ;
            $qb->andWhere('crm_detail.dateDemandeArbitrage < :dateDemArbitrageMax')
               ->setParameter('dateDemArbitrageMax', $dateMax)
               ->andWhere('crm_detail.dateDemandeArbitrage >= :dateDemArbitrageMin')
               ->setParameter('dateDemArbitrageMin', $dateMin);
        }
        else{
            $qb->andWhere('crm_detail.dateDemandeArbitrage >= :dateDemArbitrageMin')
               ->setParameter('dateDemArbitrageMin', $dLastPayDate);
        }
       //var_dump($qb->getQuery()->getResult()); die();
//        var_dump(count($qb->getQuery()->getResult()));exit;
      return $qb->getQuery()->getResult();


    }
    
    public function getMaxDateArbitrage() {
      $query = $this->createQueryBuilder('crm_detail');
      $query->select('crm_detail, MAX(crm_detail.dateDemandeArbitrage) AS date');
      //      $query->select('crm_detail, MAX(crm_detail.dateReponseArbitrage) AS date');
      $date = $query->getQuery()->getResult();
      return $this->transformDateToDataBaseFormat($date[0]['date']);
    }
    
    public function getMinDateArbitrage() {
      $query = $this->createQueryBuilder('crm_detail');
      $query->select('crm_detail, MIN(crm_detail.dateDemandeArbitrage) AS date');
      $date = $query->getQuery()->getResult();
      return $this->transformDateToDataBaseFormat($date[0]['date']);
    }
    
    public function getBeginDate() {
      $query = $this->createQueryBuilder('crm_detail');
      $query->select('crm_detail, Min(crm_detail.dateDebut) AS date');
      $date = $query->getQuery()->getResult();
      return $this->transformDateToDataBaseFormat($date[0]['date']);
    }
    
    public function getEndDate() {
      $query = $this->createQueryBuilder('crm_detail');
      $query->select('crm_detail, MAX(crm_detail.dateFin) AS date');
      $date = $query->getQuery()->getResult();
      return $this->transformDateToDataBaseFormat($date[0]['date']);
    }
    
    
    /**
     * Transfert des donnees de la table temporaire crm_detail_tmp vers la table principale crm_detail
     */
    public function tmpVersCrmDetail($ficRecapId, $tableName)
    {
        try {
            
            $sInsert = "INSERT INTO crm_detail 
                            (fic_recap_id, commune_id, crm_demande_id, depot_id, societe_id, abonne_soc_id, 
                            adresse_id, rnvp_id, crm_id_ext, crm_id_editeur, date_creat, date_debut, date_fin, numabo_ext, 
                            vol1, vol2, vol3, vol4, vol5, cp, ville, soc_code_ext, code_demande, cmt_demande, 
                            origine, annule, client_type ";
            if($tableName!='crm_detail_neopress_tmp')
            {
                $sInsert .= "   , date_imputation_paie, pai_tournee_id, modele_tournee_jour_id ";
            }            
            $sInsert .= "   )
                        SELECT 
                            t.fic_recap_id, t.commune_id, t.crm_demande_id, t.depot_id, t.societe_id, t.abonne_soc_id, 
                            t.adresse_id, t.rnvp_id, t.crm_id_ext, t.crm_id_editeur, t.date_creat, t.date_debut, t.date_fin, t.numabo_ext, 
                            t.vol1, t.vol2, t.vol3, t.vol4, t.vol5, t.cp, t.ville, t.soc_code_ext, t.code_demande, t.cmt_demande, 
                            t.origine, t.annule, t.client_type ";
            if($tableName!='crm_detail_neopress_tmp')
            {
                $sInsert .= "   , t.date_imputation_paie, t.pai_tournee_id, t.modele_tournee_jour_id ";
            }
           $sInsert .= "   FROM
                           $tableName t
                            LEFT JOIN crm_detail c ON t.soc_code_ext=c.soc_code_ext AND t.crm_id_editeur = c.crm_id_editeur 
                        WHERE
                            t.soc_code_ext IS NOT NULL AND t.crm_id_editeur IS NOT NULL AND c.id IS NULL
                            AND t.crm_demande_id IS NOT NULL
                    ";
            $sInsert .= "   AND t.abonne_soc_id IS NOT NULL";
            
            $this->_em->getConnection()->prepare($sInsert)->execute();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    } 
    
    
    /**
     * Mise a jour Reponse Remontee d information
     * @throws \Doctrine\DBAL\DBALException
     */
    public function updateRepRemInfo()
    {
        try {
            
            $sUpdate = " UPDATE 
                            crm_detail c
                            INNER JOIN crm_rep_reminfo_tmp t ON c.id = t.crm_id_int AND c.abonne_soc_id = t.abonne_soc_id
                         SET
                            c.crm_reponse_id = t.crm_reponse_id
                            , c.cmt_reponse = t.cmt_reponse
                            , c.date_reponse = t.date_reponse
                            
                         WHERE
                            c.crm_reponse_id IS NULL  ";            
            $this->_em->getConnection()->prepare($sUpdate)->execute();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    } 

    /**
     * [getTournee description]
     * @param  [type] $depot      [description]
     * @param  [type] $numabo_ext [description]
     * @return [type]             [description]
     */
    public function getTournee($depot, $numabo_ext){

        $sql = "SELECT DISTINCT cpt.date_cpt_rendu, cpt.heure_reception
                FROM `client_a_servir_logist` as cla
                /*LEFT JOIN  modele_tournee mt ON mt.id = mtj.tournee_id*/
                 LEFT JOIN abonne_soc ab ON ab.id = cla.abonne_soc_id 
                LEFT JOIN  cptr_reception cpt ON cpt.depot_id = cla.depot_id 
                LEFT JOIN  crm_detail crmd ON crmd.depot_id = cla.depot_id   
                LEFT JOIN  modele_tournee_jour mtj ON mtj.id = cla.tournee_jour_id
               /* LEFT JOIN  group_tournee gt ON gt.id = mt.group_id  
                WHERE date_debut BETWEEN current_date()-7 AND current_date()*/
                AND cpt.depot_id = $depot
                AND ab.numabo_ext = $numabo_ext 
                ";
              
            return $this->_em->getConnection()->fetchAll($sql);

    }

    /**
     * [transformDateToDataBaseFormat description]
     * @param  [type] $date [description]
     * @return [type]       [description]
     */
    private function transformDateToDataBaseFormat($date){
      if($date === null) return null;
      $dateItems = explode(' ', $date);
      $dateItems = explode('-', $dateItems[0]);
      return  $dateItems[2].'/'.$dateItems[1].'/'.$dateItems[0];
    }
    
    /**
     * [transformDateToDataBaseFormatReverse description]
     * @param  [type]  $date [description]
     * @param  boolean $max  [description]
     * @return [type]        [description]
     */
    private function transformDateToDataBaseFormatReverse($date,$max= false){
      $dateItems = explode('/', $date);
      if($max)
        return $dateItems[2].'-'.$dateItems[1].'-'.($dateItems[0] + 1);
      return $dateItems[2].'-'.$dateItems[1].'-'.$dateItems[0];
    }
    
    /**
     * [getRepReclamToExport récupération des réponse au données (reclamation) a exporter vers Jade]
     * on récupere que les réponse sur j-30 jour
     * @return [type] [description]
     */
     public function getRepReclamToExport(){
        // $rep = $rep.$name;
         //$rep = str_replace( '/', '\\',$rep);echo $rep;
         $sql = "SELECT distinct 
                                 crm.id     as id_info_soc_distrib,
                                 crm.societe_id as societe_id,
                                 crm_id_ext as id_demande_jade,
                                 crm_id_editeur as crm_id_editeur,
                                 numabo_ext  as num_abonne,
                                 crm.soc_code_ext as code_societe,
                                 crmR.code as code_reponse,
                                REPLACE(REPLACE(REPLACE(REPLACE(TRIM(cmt_reponse), '\t', ''), '\r', ''), '\n', ''),'|','') as commentaire_reponse,
                                 date_reponse as date_reponse, 
                                 IF(u.id IS NULL, '', CONCAT_WS(' ', u.prenom, u.nom, CONCAT('(', login, ')'))) AS repondue_par
                 from crm_detail crm 
                    LEFT JOIN crm_reponse crmR ON crmR.id = crm.crm_reponse_id  
                    LEFT JOIN utilisateur u ON crm.utl_reponse_id = u.id
                 WHERE  crmR.crm_categorie_id ='1'
                        AND crm.date_export is null
                        AND crm.crm_reponse_id IS NOT NULL
                        AND crm.date_reponse > DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                        ORDER BY crm.societe_id  asc ";

            return $this->_em->getConnection()->fetchAll($sql);

 
     }
  /**
   * [getRemInformationToExport récupération des données (remontée dinformation) a exporter vers Jade]
   * @return [type] [description]
   */
    public function getRemInformationToExport(){
         $sql = "SELECT  DISTINCT /*s.id as societe_id,*/
                         crm.id as id_info_soc_distrib,
                         societe_id as societe_id,
                         numabo_ext  as num_abonne,
                         vol1  as volet1,
                         vol2  as volet2,
                         vol3  as volet3,
                         vol4  as volet4,
                         vol5  as volet5,
                         crm.cp    as code_postal,
                         ville  as ville,
                         cm.insee as insee,
                         crm.soc_code_ext as code_societe,
                         crmD.code  as  code_remonte_info,
                         REPLACE(REPLACE(REPLACE(REPLACE(TRIM(cmt_demande), '\t', ''), '\r', ''), '\n', ''),'|','') as commentaire_demande,
                         crm.date_creat as date_creation,
                         date_reponse as date_reponse,
                         IF(crm.date_debut IS NULL, DATE_FORMAT(crm.date_creat, '%Y%m%d'), DATE_FORMAT(crm.date_debut, '%Y%m%d')) AS date_debut, 
                         IF(crm.date_fin IS NULL, IF(crm.date_debut IS NULL, DATE_FORMAT(crm.date_creat, '%Y%m%d'), DATE_FORMAT(crm.date_debut, '%Y%m%d')), DATE_FORMAT(crm.date_fin, '%Y%m%d')) AS date_fin,
                         IF(u.id IS NULL, '', CONCAT_WS(' ', u.prenom, u.nom, CONCAT('(', login, ')'))) AS saisie_par
                     from crm_detail crm 
                         INNER JOIN crm_demande  crmD ON crmD.id = crm.crm_demande_id
                         INNER JOIN commune cm ON cm.id = crm.commune_id   
                         LEFT JOIN utilisateur u ON crm.utl_saisie_id = u.id
                     WHERE  crmD.crm_categorie_id = '2' 
                         AND date_export is null
                        AND crm.date_creat > DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                         ORDER BY crm.societe_id  asc ";
            return $this->_em->getConnection()->fetchAll($sql);
 
    }
    
    /**
     * [UpdateDateExoptInCrmDetail mettre à jour le champ date export qui spécifie la date de l'export des données(reminfo/réclamation) vers jade]
     * @param [type] $data [description]
     */
    public function UpdateDateExoptInCrmDetail($data){

       // $em = $this->getContainer()->get('doctrine')->getManager();
        $now = new \DateTime();
        foreach ($data as $key => $row) {
            $crm = $this->_em->getRepository('AmsDistributionBundle:CrmDetail')->findOneById($row['id_info_soc_distrib']);
            $crm->setDateExport($now);
           
        }
        $this->_em->flush();
       
    }

    /**
     * [getHourReception description]
     * @param  [type] $tourneeId [description]
     * @param  [type] $depotId   [description]
     * @return [type]            [description]
     */
    public function getHourReception($tourneeId, $depotId){
         $sql = "SELECT distinct heure_reception 
                from crm_detail crm 
                INNER JOIN cptr_reception cptr  ON cptr.depot_id = crm.depot_id       
                WHERE  cptr.tournee_jour_id = $tourneeId
                AND cptr.depot_id = $depotId ";
                $result = $this->_em->getConnection()->fetchAll($sql);
             //  echo $sql;die();
            return !empty($result) ? $result[0]['heure_reception'] : null ;

    }


    /**
     * [updateTourneeJourId description]
     * @return [type] [description]
     */
    public function updateTourneeJourId($crmDetailId, $date_imputation_paie)
    {
        try {
            $this->_em->getConnection()
                    ->executeQuery("UPDATE
                                            crm_detail  tmp
                                    /*LEFT JOIN modele_tournee mt ON t.neo_tournee = mt.libelle*/
                                    LEFT JOIN tournee_detail td ON td.num_abonne_id = tmp.abonne_soc_id 
                                    LEFT JOIN modele_tournee_jour mtj ON td.modele_tournee_jour_code = mtj.code AND CURDATE() BETWEEN  mtj.date_debut AND mtj.date_fin
                                    AND mtj.jour_id =CAST(DATE_FORMAT(tmp.date_imputation_paie, '%w') AS SIGNED)+1
                                    SET tmp.tournee_jour_id = mtj.id
                                    WHERE crm.id = $crmDetailId");
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

      /**
     * [updateTourneeJourId description]
     * @return [type] [description]
     */
    public function getPaieTourneeId($crmDetailId, $dateImputationPaie, $abonneSocId, $depotId)
    {

        $sql = "SELECT distinct pt.id, pt.code , pt.depot_id
                FROM pai_tournee as pt
                inner join client_a_servir_logist as c on pt.id = c.pai_tournee_id  and c.abonne_soc_id = $abonneSocId 
                /*inner join modele_tournee_jour as mtj on pt.modele_tournee_jour_id = mtj.id*/
                where pt.jour_id =CAST(DATE_FORMAT('".$dateImputationPaie."', '%w') AS SIGNED)+1 AND pt.date_distrib = '".$dateImputationPaie."' AND pt.depot_id =  $depotId";
      // echo $sql;die();
             //  var_dump($this->_em->getConnection()->fetchAll($sql)) ;die('5');
                $result =  $this->_em->getConnection()->fetchAll($sql);
                if(!empty($result)){
                      return $result[0];
                }else{
                      return $result;
                }
    }
    
    /**
     * [isExistDitrib ]
     * @param  [type]  $abonneSocId        [description]
     * @param  [type]  $dateImputationPaie [description]
     * @return boolean                     [description]
     */
    public function isExistDitrib($abonneSocId, $dateImputationPaie){
       $sql = "SELECT cs.id
                FROM  client_a_servir_logist cs
                where cs.abonne_soc_id = $abonneSocId 
                AND cs.date_distrib = '".$dateImputationPaie."'";//echo $sql;die();

                $result =  $this->_em->getConnection()->fetchAll($sql);
                if(!empty($result)){
                      return $result[0];
                }else{
                      return $result;
                }
        

    }
    
    /**
     * [updateCrmTourneeJourId description]
     * @param  [type] $crmDetailId [description]
     * @param  [type] $trnJourId   [description]
     * @return [type]              [description]
     */
     public function updateCrmTourneeJourId($crmDetailId, $trnJourId)
    {
        if(empty($trnJourId)){
          $trnJourId = NULL;
        }
        $sql = "UPDATE  crm_detail SET pai_tournee_id = ?  WHERE id = ? "; 
        try {
            $this->_em->getConnection()->executeUpdate($sql,array($trnJourId, $crmDetailId));
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

    public function getCrmDemandeListe($crmCategorieId)
    {
     $sql = "SELECT DISTINCT cd.id  , cd.libelle
                    FROM crm_demande as cd 
                    WHERE cd.crm_categorie_id = $crmCategorieId
                    order by cd.libelle asc";

                  
            return $this->_em->getConnection()->fetchAll($sql);

    }
    
    
    /**
     * Mise à jour de l'adresse dans la table crm_detail
     * Utiliser lorsqu'on corrige une adresse rejeté par exemple.
     * @param int $abonne_id
     * 
     */
    public function updateCrmAdresse ($adresse_id) {
         try {
             /*
            $this->_em->getConnection()
                    ->executeQuery(" UPDATE crm_detail dest, 
                     (SELECT  a.rnvp_id, a.commune_id, dc.depot_id , a.abonne_soc_id
                         FROM adresse a
                         INNER JOIN depot_commune dc USING(commune_id)
                         WHERE a.id ='".$adresse_id."'
                     ) as src
                 SET 
                     dest.depot_id = src.depot_id,
                     dest.commune_id = src.commune_id,
                     dest.adresse_id = '".$adresse_id."',
                     dest.rnvp_id = src.rnvp_id
                 WHERE dest.abonne_soc_id = src.abonne_soc_id 
                    AND date_creat > date_sub(now(), INTERVAL 7 DAY)
                ");*/
            $sUpdate    = " UPDATE crm_detail cd
                                INNER JOIN adresse a ON cd.adresse_id = a.id
                                LEFT JOIN repar_glob rg ON rg.commune_id=a.commune_id AND cd.date_creat BETWEEN rg.date_debut AND rg.date_fin
                            SET
                                cd.depot_id = IFNULL(rg.depot_id, cd.depot_id)
                                , cd.commune_id = a.commune_id
                                , cd.rnvp_id = a.rnvp_id
                            WHERE
                                cd.adresse_id = '".$adresse_id."'
                                AND cd.date_creat > date_sub(now(), INTERVAL 7 DAY)
                                AND cd.crm_reponse_id IS NULL
                             ";
            $this->_em->getConnection()
                    ->executeQuery($sUpdate);
            
            /* Correction depot selon repar_soc */
            $sUpdate    = " UPDATE crm_detail cd
                                INNER JOIN adresse a ON cd.adresse_id = a.id
                                INNER JOIN repar_soc rs ON rs.commune_id=a.commune_id AND cd.societe_id = rs.societe_id AND cd.date_creat BETWEEN rs.date_debut AND rs.date_fin
                            SET
                                cd.depot_id = rs.depot_id 
                            WHERE
                                cd.adresse_id = '".$adresse_id."'
                                AND cd.date_creat > date_sub(now(), INTERVAL 7 DAY)
                                AND cd.crm_reponse_id IS NULL
                             ";
            $this->_em->getConnection()
                    ->executeQuery($sUpdate);
            
         } catch (DBALException $DBALException) {
            throw $DBALException;
        }
  }
  
  
  /**
   * Verifie si le modele tournee jour respecte le "jour" de la date d'imputation
   * @return type array
   */
   public function verifTourneeByDateImputation($date = false){
        $dateCreat = ($date) ? $date : date('Y-m-d') ;
        $sql = 
               "
                SELECT * FROM (
                    SELECT 
                        numabo_ext,societe_id,date_creat,cd.date_debut,cd.date_fin,date_imputation_paie,modele_tournee_jour_id,mtj.code,
                        if(mtj.jour_id = DAYOFWEEK(date_imputation_paie),'OK','NOK') as incoherence
                    FROM crm_detail cd
                        JOIN crm_demande dem ON dem.id = cd.crm_demande_id
                        JOIN modele_tournee_jour mtj ON mtj.id = cd.modele_tournee_jour_id
                    WHERE dem.crm_categorie_id = 1
                    AND date_creat >= '$dateCreat'
                ) as reclam
                WHERE incoherence = 'NOK'
                ";

        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    
    /**
     * calcul les indicateur des reclam
     * @param type $societe_id
     * @param type $date_debut
     * @param type $date_fin
     * @param type $depotId
     * @return type
     */
    
    public function getIndicateurReclamation ($societe_id, $date_debut, $date_fin, $depotId ="") {
        
        $sql = "SELECT count(*) nb_reclam,
                    CASE 
                        WHEN  DATEDIFF(date_format(c.date_reponse, '%Y-%m-%d'),date_format(c.date_creat, '%Y-%m-%d') )  IN (0,1,2) THEN 'J_J2'
                        WHEN  DATEDIFF(date_format(c.date_reponse, '%Y-%m-%d'),date_format(c.date_creat, '%Y-%m-%d') )  IN (3,4) THEN 'J3_J4'
                        WHEN  DATEDIFF(date_format(c.date_reponse, '%Y-%m-%d'),date_format(c.date_creat, '%Y-%m-%d') )  IN (5,6) THEN 'J5_J6'
                        WHEN  DATEDIFF(date_format(c.date_reponse, '%Y-%m-%d'),date_format(c.date_creat, '%Y-%m-%d') )  > 6 THEN 'J7_'
                        ELSE 'NON_REPONDU'
                    END  as delai ";
                if($depotId >  0 ) $sql. ", depot_id";
                  
            $sql .= "  FROM  crm_detail c
                    INNER JOIN crm_demande d ON c.crm_demande_id =  d.id 
                    WHERE d.crm_categorie_id = 1  AND societe_id = ".$societe_id."
                    AND date_creat between '".$date_debut."' AND '".$date_fin."' AND c.depot_id IS NOT NULL" ;       
                    if ($depotId > 0) {
                        $sql .= " AND depot_id = ".$depotId."
                                  GROUP BY  delai, depot_id";
                    }else {
                        $sql .=" group by  delai ";
                    }               
              
         return $this->_em->getConnection()->fetchAll($sql);
    }

    
}