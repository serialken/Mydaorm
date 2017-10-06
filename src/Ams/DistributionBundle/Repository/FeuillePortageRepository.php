<?php

namespace Ams\DistributionBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\DBALException;

class FeuillePortageRepository extends EntityRepository {

  public function exportPdf($sDate_distrib,$sCode_depot,$sCode_society,$sCode_tournee,$flux){
    $conditionsArr = array(
                            'date_distrib'   => $sDate_distrib, 
                            'code_depot'     => $sCode_depot,   
                            'code_society'   => $sCode_society, 
                            'code_tournee'   => $sCode_tournee, 
                            'flux_id'           => $flux  
                          );
      $connection = $this->getEntityManager()->getConnection();
      $q = "SELECT casl.vol1,casl.vol2,casl.qte,d.libelle as depot_libelle,d.code as depot_code,p.id as produit_id,p.code as produit_code,"
          ."p.libelle as produit_libelle,mtj.code as tournee_jour_code,e.nom as nom_porteur,e.prenom1 as prenom_porteur,"
          ."arnvp.adresse,arnvp.cp,arnvp.ville,asoc.numabo_ext as num_abonne,s.code as societe_code,f.path,casl.point_livraison_id,casl.point_livraison_ordre,"
          ."asoc.date_service_1,asoc.date_stop,GROUP_CONCAT(DISTINCT info_p.valeur SEPARATOR ',') AS valeur,"
          ."(SELECT valeur FROM info_portage where id = info_p_l.info_portage_id) as info_portage_livraison "
          ."FROM client_a_servir_logist casl "
          ."LEFT JOIN infos_portages_livraisons info_p_l ON info_p_l.livraison_id = casl.point_livraison_id "
          ."LEFT JOIN infos_portages_abonnes info_p_a ON info_p_a.abonne_id = casl.abonne_soc_id "
          ."LEFT JOIN info_portage info_p ON info_p.id = info_p_a.info_portage_id "
          ."AND info_p.date_debut <= now()+1 "
          ."AND info_p.date_fin > now() "
          ."AND info_p.active = 1 "
          ."LEFT JOIN depot d ON d.id = casl.depot_id "
          ."LEFT JOIN produit p ON p.id = casl.produit_id "
          ."LEFT JOIN modele_tournee_jour mtj ON mtj.id = casl.tournee_jour_id "
          ."LEFT JOIN modele_tournee mt ON mtj.tournee_id = mt.id "
          ."LEFT JOIN employe e ON e.id = mtj.employe_id "
          ."LEFT JOIN adresse_rnvp arnvp ON arnvp.id = casl.rnvp_id "
          ."LEFT JOIN abonne_soc asoc ON asoc.id = casl.abonne_soc_id "
          ."LEFT JOIN societe s ON s.id = casl.societe_id "
          ."LEFT JOIN fichier f ON f.id = p.image_id  "
          ."WHERE casl.tournee_jour_id is not null "
          ."AND casl.date_distrib IN ('$sDate_distrib') ";
      
          if(!empty($sCode_depot)){
            $var = explode(',',$sCode_depot);
            $sCode_depot = array_map(function($str) {
              return "'".$str."'";
            }, $var);
            $var = implode(',',(array)$sCode_depot);
            $q .= "AND d.code = $var ";
          }
          
          if(!empty($sCode_society)){
            $var = explode(',',$sCode_society);
            $sCode_society = array_map(function($str) {
              return "'".$str."'";
            }, $var);
            $var = implode(',',$sCode_society);
            $q .= "AND s.code IN ($var) ";
          }
          
          if(!empty($flux)){
            $q .= "AND casl.flux_id = $flux ";
          }
          
          if(!empty($sCode_tournee)){
            $var = explode(',',$sCode_tournee);
            $sCode_tournee = array_map(function($str) {
              return "'".$str."'";
            }, $var);
            $var = implode(',',$sCode_tournee);
            $q .= "AND mt.code IN ($var) ";
          }
          
          $q .="
                GROUP BY casl.vol1,casl.vol2,casl.qte,d.libelle,d.code,p.id,p.code,p.libelle,mtj.code,
                e.nom,e.prenom1,arnvp.adresse,arnvp.cp,arnvp.ville,asoc.numabo_ext,s.code,f.path,casl.point_livraison_id,
                casl.point_livraison_ordre,asoc.date_service_1,asoc.date_stop
              ";
          $q .="ORDER BY mtj.code,casl.point_livraison_ordre "
              .";";
          
      $stmt = $connection->executeQuery($q);
      return $stmt->fetchAll();
  }
  
  
  public function insertMultiple($values){
    $query = "
        INSERT INTO feuille_portage (tournee_jour_id,depot_id,flux_id,date_distrib,date_parution,numabo_ext,vol1,vol2,num_voie,nom_voie,cp,ville,info_portage,qte,produit_id,produit_libelle,ordre)
        VALUES $values
    ";
    $this->_em->getConnection()->prepare($query)->execute();
  }
          
  public function getDepotBydate($date){
    $connection = $this->getEntityManager()->getConnection();
    $query = "
       SELECT d.code ,d.libelle FROM client_a_servir_logist casl
       LEFT JOIN depot d ON d.id = casl.depot_id
       WHERE date_distrib = '$date'
       AND d.code is not null
       GROUP BY depot_id;
    ";
    
    $stmt = $connection->executeQuery($query);
    return $stmt->fetchAll();
  }
          
          
  public function deleteDoublon($flux,$depot,$date){
    $delete = " DELETE FROM feuille_portage
                WHERE date_distrib ='$date'
                AND depot_id = $depot
                AND flux_id = $flux           
               ";
    $this->_em->getConnection()->executeQuery($delete);
  }
  
  public function findClient($product,$tournee,$date, $sSeparateur=' '){
    $connection = $this->getEntityManager()->getConnection();
    $q = "SELECT CONCAT(fp.vol1, '$sSeparateur' ,fp.vol2) as nom, fp.vol1, fp.vol2, produit_libelle as nom_produit, qte as nb_ex, adr.vol4 as adresse,
                 adr.vol3,adr.vol5,CONCAT(adr.cp,' ',adr.ville )  as cp_ville,GROUP_CONCAT(REPLACE(ip.valeur,'|',' ') SEPARATOR ' -- ') as infoportage,fp.numabo_ext as numero,
                 CONCAT( e.prenom1,' ',e.nom ) as nom_porteur,
                 mtj.code as code_tournee, fp.ordre,DATE_FORMAT(date_distrib,'%d/%m/%Y') as date_distrib FROM feuille_portage fp "
         ."JOIN produit p ON p.id = produit_id "
         ."JOIN societe s ON p.societe_id = s.id "
         ."LEFT JOIN modele_tournee_jour mtj ON mtj.id = fp.tournee_jour_id "
         ."LEFT JOIN employe e ON e.id = mtj.employe_id "
         ."LEFT JOIN  abonne_soc asoc ON asoc.numabo_ext = fp.numabo_ext AND asoc.societe_id = s.id "
         ."JOIN adresse adr ON adr.abonne_soc_id = asoc.id AND '$date' BETWEEN adr.date_debut AND adr.date_fin AND (adr.type_adresse = 'L' OR adr.type_adresse IS NULL) "
         ."RIGHT JOIN etiquette et ON et.abonne_soc_id = asoc.id "
         ."LEFT JOIN infos_portages_abonnes ipa ON ipa.abonne_id = asoc.id "
         ."LEFT JOIN info_portage ip ON ip.id = ipa.info_portage_id AND '$date' BETWEEN ip.date_debut AND ip.date_fin  AND ip.active = 1 "
         ."LEFT JOIN type_info_portage tip ON tip.id = ip.type_info_id AND tip.code IN ('DIVERS1', 'INFO_COMP1', 'INFO_COMP2', 'DIVERS2') /* uniquement les infos éditeur */ "
         ."WHERE date_distrib = '$date' "
         ."AND tournee_jour_id ='$tournee' "
         ."AND produit_id IN($product) "
         ."GROUP BY fp.numabo_ext,produit_id "
         ."ORDER BY fp.ordre"
         ;
      $stmt = $connection->executeQuery($q);
      return $stmt->fetchAll();
  }
  
}
