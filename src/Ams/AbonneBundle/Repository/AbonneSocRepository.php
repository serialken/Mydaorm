<?php 

namespace Ams\AbonneBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\DBALException;

class AbonneSocRepository extends EntityRepository
{
    public function getAbonnePortage($portageId){
        $qb = $this->createQueryBuilder('abonne')
                ->join('abonne.infosPortages', 'infos')
                ->addSelect('infos')
            ->where('infos.id = :id')
            ->setParameter('id',$portageId)
            ;
        return $qb;
    }
    
    /**
     * Attribution d'un numero d'abonne unique pour les nouveaux AbonneSoc
     * 
     * @throws \Doctrine\DBAL\DBALException
     */
    public function updateAbonneUnique()
    {
        try {
            // Les abonnes dont on n'a pas encore defini son abonne_unique_id
            $slct   = " SELECT
                            MAX(a.id) AS adresse_id, a.rnvp_id, ab.vol1, ab.vol2, ab.numabo_ext, ab.soc_code_ext, ab.id AS abonne_soc_id
                        FROM
                            abonne_soc ab
                            LEFT JOIN adresse a ON ab.id=a.abonne_soc_id AND ab.abonne_unique_id IS NULL
                        WHERE
                            ab.abonne_unique_id IS NULL
                            AND a.id IS NOT NULL
                        GROUP BY 
                            a.rnvp_id, ab.vol1, ab.vol2, ab.numabo_ext, ab.soc_code_ext, ab.id
                        ORDER BY
                            rnvp_id, vol1, vol2, abonne_soc_id";
            $aRes  = $this->_em->getConnection()->fetchAll($slct);
            foreach($aRes as $ResSlct) {
                // Verifie s'il existe un autre abonne de meme vol1 & vol2 & rnvp dont on connait le abonne_unique_id
                $abonne_unique_id   = 0;
                $aAbonneIdAChoisir = array();
                $slctAbonneIdAChoisir  = " SELECT DISTINCT a.abonne_soc_id
                                            FROM
                                                adresse a 
                                            WHERE
                                                a.vol1 = '".addslashes($ResSlct["vol1"])."' AND a.vol2='".addslashes($ResSlct["vol2"])."' AND a.rnvp_id = ".$ResSlct["rnvp_id"]." 
                                                AND a.abonne_soc_id <> ".$ResSlct["abonne_soc_id"]." 
                                            ";
                $aResAbonneIdAChoisir  = $this->_em->getConnection()->fetchAll($slctAbonneIdAChoisir);
                foreach($aResAbonneIdAChoisir as $ResAbonneIdAChoisir)
                {
                    $aAbonneIdAChoisir[]   = $ResAbonneIdAChoisir['abonne_soc_id'];
                }
                
                if(!empty($aAbonneIdAChoisir))
                {
                    $slctAbonneIdUnique = " SELECT MAX(abonne_unique_id) AS abonne_unique_id FROM abonne_soc WHERE id IN (".implode(",", $aAbonneIdAChoisir).") ";
                    $aResAbonneIdUnique  = $this->_em->getConnection()->fetchAll($slctAbonneIdUnique);
                    foreach($aResAbonneIdUnique as $ResAbonneIdUnique)
                    {
                        $abonne_unique_id   = $ResAbonneIdUnique['abonne_unique_id'];
                    }
                }
                // Si on ne connait pas abonne_unique_id, on le cree 
                if($abonne_unique_id==0) {
                    // creation du nouveau abonne_unique
                    $insert = " INSERT INTO abonne_unique (vol1, vol2) 
                                VALUES ('".addslashes($ResSlct["vol1"])."', '".addslashes($ResSlct["vol2"])."') ";
                    $this->_em->getConnection()->exec($insert);
                    $abonne_unique_id = $this->_em->getConnection()->lastInsertId();
                }
                // Mise a jour de abonne_unique_id de abonne_soc
                $update = " UPDATE abonne_soc SET abonne_unique_id = ".$abonne_unique_id." WHERE id = ".$ResSlct["abonne_soc_id"]." ";
                $this->_em->getConnection()->exec($update);                
            }
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    public function getAbonneByData($data){
        try {
            $slct   = 'SELECT DISTINCT asoc.id,p.libelle as produit_libelle,d.libelle,mtj.code,casl.depot_id,casl.tournee_jour_id,e.id as etiquette,CONCAT(asoc.vol1," ",asoc.vol2) as nom_abonne,CONCAT(a.vol4," ",a.cp," ",a.ville) as adresse '.
                      'FROM client_a_servir_logist casl  '.
                      'LEFT JOIN abonne_soc asoc ON casl.abonne_soc_id = asoc.id '.
                      'LEFT JOIN adresse a ON casl.adresse_id = a.id '.
                      'LEFT JOIN etiquette e ON a.abonne_soc_id = e.abonne_soc_id '.
                      'LEFT JOIN produit p ON p.id = casl.produit_id '.
                      'LEFT JOIN  modele_tournee_jour mtj ON mtj.id = casl.tournee_jour_id '.
                      'LEFT JOIN depot d ON d.id = casl.depot_id '.
                      'WHERE casl.depot_id = "'.$data['DEPOT'].'" ';
            
            if(!empty($data['CITY']))
                $slct .=' AND a.ville like "%'.$data['CITY'].'%" ';
            if(!empty($data['ADRESS']))
                 $slct .=' AND a.vol4 like "%'.$data['ADRESS'].'%" ';
            if(!empty($data['NOM']))
                     $slct .=' AND CONCAT(asoc.vol1," ",asoc.vol2) like "%'.$data['NOM'].'%" ';
            if(!empty($data['PRODUIT']))
                $slct   .= 'AND casl.produit_id = '.$data['PRODUIT'].' ';
            if(!empty($data['FLUX']))
                $slct   .= 'AND casl.flux_id = '.$data['FLUX'].' ';
            if($data['ZIPCODE'])  
                $slct   .= 'AND a.cp = '.$data['ZIPCODE'].' ';
            if($data['NUMABO'])  
                $slct   .= 'AND asoc.numabo_ext = "'.$data['NUMABO'].'" ';
            if($data['TOURNEE']) { 
                $slct   .= 'AND mtj.code LIKE "'.$data['TOURNEE'].'%"
                            AND curdate() between mtj.date_debut and mtj.date_fin ';
            }
            if(!$data['NUMABO'])  
                $slct   .= ' AND date_distrib > DATE_ADD(CURDATE(), INTERVAL - 30 DAY) ';
            
            $slct   .= 'GROUP BY asoc.id '
                    .'ORDER BY casl.point_livraison_ordre ';
            
            if(!$data['TOURNEE'] && $data['PRODUIT'] == '')
                $slct .='LIMIT 100';
                    
            return $this->_em->getConnection()->fetchAll($slct);
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    public function classementAutoByAbonneSocId($abonneSocId,$aParam){
        $q = 
        "
            SELECT 
                ".$aParam['jour_id']." as id_jour,c.insee,asoc.id,ar.geox,ar.geoy,a.point_livraison_id,
                ".$aParam['flux_id']." as flux_id,asoc.numabo_ext,asoc.soc_code_ext,
                '".$aParam['tournee_jour_code']."' as tournee_jour_code,LEFT('".$aParam['tournee_jour_code']."', 3) AS code_depot,
                '".$aParam['sourceModification']."' AS source_modification,asoc.id as abonne_soc_id
            FROM abonne_soc asoc
            LEFT JOIN adresse a ON a.abonne_soc_id = asoc.id AND CURDATE() BETWEEN a.date_debut AND a.date_fin
            JOIN adresse_rnvp ar ON a.point_livraison_id = ar.id
            JOIN commune c ON a.commune_id = c.id
            WHERE
                asoc.id = $abonneSocId;
        ";
        $connection = $this->getEntityManager()->getConnection();
        $stmt = $connection->executeQuery($q);
        return $stmt->fetch();

    }
        public function getAllBySociete($societe){
            $qb = $this->createQueryBuilder('a')
                    ->join('a.societe', 'soc')
                ->where('soc = :societ')
                ->setParameter('societ',$societe);
            return $qb->getQuery()->getResult();
        }
}