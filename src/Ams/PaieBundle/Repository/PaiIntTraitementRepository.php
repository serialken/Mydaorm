<?php
namespace Ams\PaieBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class PaiIntTraitementRepository extends GlobalRepository {

    function select($anneemois_id) {
        $sql = "select 
                    pit.id, 
                    date_format(pit.date_debut,'%d/%m/%Y %H:%i:%s') as date_debut, 
                    sec_to_time(to_seconds(pit.date_fin)-to_seconds(pit.date_debut)) as duree, 
                    pit.utilisateur_id, 
                    pit.typetrt, 
                    pit.depot_id, 
                    pit.flux_id, 
                    date_format(pit.date_distrib,'%d/%m/%Y') as date_distrib, 
                    prm2.libelle as anneemois,
                    pit.statut ,
                    case pit.statut when 'E' then 0 when 'C' then 1 else 5 end as level
                FROM pai_int_traitement pit
                inner join pai_ref_mois prm on pit.date_debut between prm.date_debut and date_add(prm.date_fin, INTERVAL 1 DAY)
                left outer join pai_ref_mois prm2 on pit.anneemois=prm2.anneemois
                WHERE prm.anneemois='".$anneemois_id."'
                ORDER BY pit.id desc
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }

    function paie_en_cours($idtrt="") {
    $sql = "select max(pit.id)
            FROM pai_int_traitement pit
            WHERE statut='C' 
            and (typetrt in ('PNG2MROAD','ALIM_EMPLOYE') OR typetrt like 'GENERE_PLEIADES_%') "
            .($idtrt!="" ? "and id<>" . $idtrt : "")
            ;
        return $this->_em->getConnection()->fetchColumn($sql);
    }
    
    function alimemploye_en_cours($idtrt="") {
    $sql = "select max(pit.id)
            FROM pai_int_traitement pit
            WHERE statut='C' 
            and (typetrt in ('PNG2MROAD','ALIM_EMPLOYE') OR typetrt like 'GENERE_PLEIADES_%') "
            .($idtrt!="" ? "and id<>" . $idtrt : "")
            ;
        return $this->_em->getConnection()->fetchColumn($sql);
    }
    
    public function debut($utilisateur_id,$typetrt) {
        try {
            $sql = "call int_debut(" . $user . ",@idtrt," . $this->sqlField->sqlTrimQuote($typetrt) . ")";
            $idtrt = $this->executeProc($sql, "@idtrt");
            return $idtrt;
        } catch (DBALException $ex) {
            return false;
        }
    }
    public function fin($idtrt) {
        try {
            $sql = "call int_fin(" . $idtrt . ")";
            $this->executeProc($sql);
        } catch (DBALException $ex) {
            return false;
        }
        return true;
    }

    public function getStatut($idtrt) {
        try {
            $sql = "select statut from pai_int_traitement where id=".$idtrt;
            return $this->_em->getConnection()->fetchColumn($sql);
        } catch (DBALException $ex) {
            return 'E';
        }
    }

    function selectCombo($anneemois_id) {
        $sql = "select 
                        pit.id
                        ,concat(pit.date_debut,' ',pit.typetrt) as libelle
                    FROM pai_int_traitement pit
                    inner join pai_ref_mois prm on pit.date_debut between prm.date_debut and date_add(prm.date_fin, INTERVAL 1 DAY)
                    WHERE prm.anneemois='".$anneemois_id."'
                    ORDER BY pit.id desc
                    ";
        return $this->_em->getConnection()->fetchAll($sql);
    }

    function getAnneemoisByIdtrt($idtrt) {      
       $sql ="  SELECT  
                    prm.anneemois
                FROM pai_int_traitement pit
                INNER JOIN pai_ref_mois prm 
                ON  prm.date_extrait = pit.date_debut
                WHERE pit.id ='".$idtrt."'";
         return $this->_em->getConnection()->fetchColumn($sql);
    }
    
    function getGenereMensuel($flux_id) {      
       $sql ="  select distinct prm.anneemois,prm.date_debut,prm.date_fin
                from pai_int_traitement pit
                inner join pai_ref_mois prm on pit.anneemois=prm.anneemois
                where pit.typetrt in ('GENERE_PLEIADES_MENSUEL'/*,'GENERE_PLEIADES_CLOTURE'*/) 
                and pit.statut='T'
                and date_format(pit.date_debut,'%Y-%m-%d')=date_format(date_add(now(),INTERVAL -1 DAY),'%Y-%m-%d')
                and pit.flux_id=".$flux_id;
         return $this->_em->getConnection()->fetchAssoc($sql);
    }

        
    public function alimenterPaie(&$msg, &$msgException, &$idtrt, $user, $date_distrib, $date_org, $depot_id, $flux_id, $alim_tournee, $maz_duree_attente, $maz_duree_retard, $maz_nbkm_paye_tournee, $alim_activite_presse, $maz_nbkm_paye_activite_presse, $maz_duree_activite_horspresse, $maz_nbkm_paye_activite_horspresse) {
        try {
            $sql="set @idtrt=".$this->sqlField->sqlIdOrNull($idtrt).";";
            $sql .= "call ALIM_PAIE(".$user.",@idtrt," . 
                        $this->sqlField->sqlDate($date_distrib) .",".  
                        $this->sqlField->sqlIdOrNull($depot_id) .",". $this->sqlField->sqlIdOrNull($flux_id) .",". 
                        $this->sqlField->sqlDateOrNull($date_org) .",".
                        $alim_tournee .",". $maz_duree_attente .",". $maz_duree_retard .",". $maz_nbkm_paye_tournee .",".  
                        $alim_activite_presse .",". $maz_nbkm_paye_activite_presse .",". 
                        $maz_duree_activite_horspresse .",". $maz_nbkm_paye_activite_horspresse
                    . ")";
            $idtrt = $this->executeProc($sql, "@idtrt");
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "", "", "");
        }
        return true;
    }
}