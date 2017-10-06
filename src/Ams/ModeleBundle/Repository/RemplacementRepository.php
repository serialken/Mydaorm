<?php

namespace Ams\ModeleBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class RemplacementRepository extends GlobalRepository {

    function select($depot_id, $flux_id, $anneemois_id, $sqlCondition = '') {
        $sql = "SELECT 
                    mr.id,
                    mr.depot_id,
                    mr.flux_id,
                    mr.contrattype_id,
                    mr.employe_id,
                    CONCAT_WS(' ', r.nom, r.prenom1, r.prenom2) as remplace_id,
                    mr.actif,
                    ect.date_fin_prevue,
                    mr.date_debut,
                    mr.date_fin,
                    epd.cycle,
                    greatest(coalesce(mrjl.valrem_moyen,0),coalesce(mrjma.valrem_moyen,0),coalesce(mrjme.valrem_moyen,0),coalesce(mrjj.valrem_moyen,0),coalesce(mrjv.valrem_moyen,0),coalesce(mrjs.valrem_moyen,0)) as valrem_moyen_semaine,
                    mrjd.valrem_moyen as valrem_moyen_dimanche,
                    epd.lundi,
                    mrjl.modele_tournee_id as tournee_lundi_id,
                    epd.mardi,
                    mrjma.modele_tournee_id as tournee_mardi_id,
                    epd.mercredi,
                    mrjme.modele_tournee_id as tournee_mercredi_id,
                    epd.jeudi,
                    mrjj.modele_tournee_id as tournee_jeudi_id,
                    epd.vendredi,
                    mrjv.modele_tournee_id as tournee_vendredi_id,
                    epd.samedi,
                    mrjs.modele_tournee_id as tournee_samedi_id,
                    epd.dimanche,
                    mrjd.modele_tournee_id as tournee_dimanche_id,
                    -- journal
                    coalesce(min(me.valide),true) as valide,
                    group_concat(me.msg order by me.level,me.rubrique,me.code separator '<br/>') as msg,
                    min(mj.id) as journal_id,
                    min(me.level) as level,
                    (mr.date_fin>=if(pm.date_blocage is null,pm.date_debut,date_add(pm.date_fin, interval +1 day))) as isModif, -- ATTENTION au blocage
                    (mr.date_debut<>ect.date_debut) and prr.id is null as isDelete
                FROM modele_remplacement mr
                INNER JOIN emp_contrat_type ect on mr.contrattype_id=ect.id
                INNER JOIN emp_pop_depot epd on mr.contrattype_id=epd.contrattype_id and mr.date_debut between epd.date_debut and epd.date_fin
                INNER JOIN employe e on mr.employe_id=e.id
                LEFT OUTER JOIN employe r on ect.remplace_id=r.id
                LEFT OUTER JOIN modele_remplacement_jour mrjd on mr.id=mrjd.remplacement_id and mrjd.jour_id=1
                LEFT OUTER JOIN modele_remplacement_jour mrjl on mr.id=mrjl.remplacement_id and mrjl.jour_id=2
                LEFT OUTER JOIN modele_remplacement_jour mrjma on mr.id=mrjma.remplacement_id and mrjma.jour_id=3
                LEFT OUTER JOIN modele_remplacement_jour mrjme on mr.id=mrjme.remplacement_id and mrjme.jour_id=4
                LEFT OUTER JOIN modele_remplacement_jour mrjj on mr.id=mrjj.remplacement_id and mrjj.jour_id=5
                LEFT OUTER JOIN modele_remplacement_jour mrjv on mr.id=mrjv.remplacement_id and mrjv.jour_id=6
                LEFT OUTER JOIN modele_remplacement_jour mrjs on mr.id=mrjs.remplacement_id and mrjs.jour_id=7
                INNER JOIN pai_ref_mois prm on prm.anneemois=$anneemois_id
                INNER JOIN pai_mois pm on pm.flux_id=mr.flux_id
                left outer join modele_journal mj on mr.id=mj.remplacement_id
                LEFT OUTER JOIN modele_ref_erreur me ON mj.erreur_id=me.id
  -- Pour ne pas supprimer la rupture à chaque chgt de taux horaire
                LEFT OUTER join ref_typetournee rtt on epd.flux_id=rtt.id
                LEFT OUTER join pai_ref_remuneration prr on rtt.societe_id=prr.societe_id AND rtt.population_id=prr.population_id AND mr.date_debut=prr.date_debut
                WHERE mr.depot_id=$depot_id AND mr.flux_id=$flux_id
                AND mr.date_debut<=prm.date_fin and mr.date_fin>=prm.date_debut
                 " .($sqlCondition != '' ? $sqlCondition : "")."
                GROUP BY mr.id,ect.id,epd.id,mrjl.id,mrjma.id,mrjme.id,mrjj.id,mrjv.id,mrjs.id,mrjd.id"
        ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    function selectCombo($anneemois_id, $depot_id, $flux_id) {
        $sql = "SELECT 
                    mr.id,
                    CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2,mr.date_debut,'-',mr.date_fin) libelle
                FROM modele_remplacement mr
                INNER JOIN employe e on mr.employe_id=e.id
                INNER JOIN pai_ref_mois prm on prm.anneemois=$anneemois_id
                WHERE mr.depot_id=$depot_id AND mr.flux_id=$flux_id
                AND mr.date_debut<=prm.date_fin and mr.date_fin>=prm.date_debut
                "
        ;
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    function selectComboJournal($depot_id, $flux_id) {
        $sql = "SELECT 
                    mr.id,
                    CONCAT_WS(' ', e.nom, e.prenom1, e.prenom2,mr.date_debut,'-',mr.date_fin) libelle
                FROM modele_remplacement mr
                INNER JOIN employe e on mr.employe_id=e.id
                INNER JOIN pai_mois pm on pm.flux_id=$flux_id
                WHERE mr.depot_id=$depot_id AND mr.flux_id=$flux_id
                AND pm.date_debut<=mr.date_fin
                "
        ;
        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function _maj_planning(&$msg, &$msgException, $param, $user, $id) {
        try {
            // Validation des tournees
//            $sql = "call pai_valide_modele(@validation_id,".$date_debut.",".$id.")";
//            $validation_id = $this->executeProc($sql, "@validation_id");

//            $sql="set @validation=".$this->sqlField->sqlId($validation_id).";";
//            $sql.="call recalcul_horaire_modele(@validation_id,".$date_debut.",".$id.")";
//            $this->executeProc($sql, "@validation_id");
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "","","");
        }
        return true;
    }

    public function _insert1(&$msg, &$msgException, $param, $user, &$id, $old_id, $new_date_debut) {
        try {
            $sql = "UPDATE modele_remplacement mr SET
                date_fin = '".$new_date_debut."' + INTERVAL -1 DAY,
                utilisateur_id=".$user.",
                date_modif = NOW()
                WHERE id = ".$old_id;         
            $this->_em->getConnection()->prepare($sql)->execute();

//            $sql = "DELETE mr 
//                FROM modele_journal mj
//                INNER JOIN modele_tournee_jour mtj on mj.tournee_jour_id=mtj.id
//                WHERE mj.tournee_jour_id = ".$old_id."
//                AND NOW()<=mtj.date_fin";                
//            $this->_em->getConnection()->prepare($sql)->execute();

            $sql = "INSERT INTO modele_remplacement 
                    (depot_id,flux_id,
                    contrattype_id,employe_id,
                    actif,
                    date_debut,date_fin,
                     utilisateur_id,date_creation
                    ) SELECT
                    ".$param['depot_id'].",".$param['flux_id'].",
                    ect.id,eco.employe_id,
                    ".$param['actif'].",
                    " . $this->sqlField->sqlQuote($new_date_debut) .",ect.date_fin,
                    $user,
                    NOW()
                    FROM emp_contrat_type ect
                    INNEr JOIN emp_contrat eco on eco.id=ect.contrat_id
                    WHERE ect.id=" . $this->sqlField->sqlIdOrNull($param['contrattype_id']);            
            $this->_em->getConnection()->prepare($sql)->execute();
            $id = $this->_em->getConnection()->lastInsertId();
            $id_jour=0;
            $this->_em->getRepository("AmsModeleBundle:RemplacementJour")->insert($msg, $msgException, $id, "dimanche", 1, $user, $id_jour);
            $this->_em->getRepository("AmsModeleBundle:RemplacementJour")->insert($msg, $msgException, $id, "lundi", 2, $user, $id_jour);
            $this->_em->getRepository("AmsModeleBundle:RemplacementJour")->insert($msg, $msgException, $id, "mardi", 3, $user, $id_jour);
            $this->_em->getRepository("AmsModeleBundle:RemplacementJour")->insert($msg, $msgException, $id, "mercredi", 4, $user, $id_jour);
            $this->_em->getRepository("AmsModeleBundle:RemplacementJour")->insert($msg, $msgException, $id, "jeudi", 5, $user, $id_jour);
            $this->_em->getRepository("AmsModeleBundle:RemplacementJour")->insert($msg, $msgException, $id, "vendredi", 6, $user, $id_jour);
            $this->_em->getRepository("AmsModeleBundle:RemplacementJour")->insert($msg, $msgException, $id, "samedi", 7, $user, $id_jour);
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "","","");
        }
        return true;
    }

    public function _insert2(&$msg, &$msgException, $param, $user, $id, $new_date_debut, $old_date_debut) {
        // Ici $new_date_debut<$old_date_debut
        try {
            $sql = "SELECT mr.id
                FROM modele_remplacement mr
                WHERE mr.contrattype_id = ".$this->sqlField->sqlIdOrNull($param['contrattype_id'])."
                and date_fin = " . $this->sqlField->sqlQuote($old_date_debut) ." + INTERVAL -1 DAY";         
            $old_id=$this->_em->getConnection()->fetchColumn($sql);;

            if (isset($old_id) && $old_id) {
                $sql = "UPDATE modele_remplacement mr
                    SET mr.date_fin = " . $this->sqlField->sqlQuote($new_date_debut) . " + INTERVAL -1 DAY,
                    mr.utilisateur_id=$user,
                    mr.date_modif = NOW()
                    WHERE mr.id = $old_id";         
                $this->_em->getConnection()->prepare($sql)->execute();
            }
            $sql = "UPDATE modele_remplacement mr
                SET
                mr.date_debut = " . $this->sqlField->sqlQuote($new_date_debut) .",
                mr.actif=".$param['actif'].",
                mr.utilisateur_id=$user,
                mr.date_modif = NOW()
                WHERE mr.id = $id";
            $this->_em->getConnection()->prepare($sql)->execute();
            // Si on insère un modèle (du 21/01/2016) avant un modèle existant (du 21/02/2016 au 01/01/2099), on supprime l'ancien
//           $sql = "DELETE modele_tournee_jour
//                WHERE id = ".$old_id."
//                and date_debut>date_fin";      
//            $this->_em->getConnection()->prepare($sql)->execute(); 
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "","","");
        }
        return true;
    }
    public function _update(&$msg, &$msgException, $param, $user, $id) {
        try {
            $sql = "UPDATE modele_remplacement mr
                SET
                mr.actif=".$param['actif'].",
                mr.utilisateur_id=$user,
                mr.date_modif = NOW()
                WHERE mr.id = $id";
            $this->_em->getConnection()->prepare($sql)->execute();

            return $this->_em->getRepository("AmsModeleBundle:RemplacementJour")->update($msg, $msgException,$id,$param['tournee_dimanche_id'],1,$user)
            && $this->_em->getRepository("AmsModeleBundle:RemplacementJour")->update($msg, $msgException,$id,$param['tournee_lundi_id'],2,$user)
            && $this->_em->getRepository("AmsModeleBundle:RemplacementJour")->update($msg, $msgException,$id,$param['tournee_mardi_id'],3,$user)
            && $this->_em->getRepository("AmsModeleBundle:RemplacementJour")->update($msg, $msgException,$id,$param['tournee_mercredi_id'],4,$user)
            && $this->_em->getRepository("AmsModeleBundle:RemplacementJour")->update($msg, $msgException,$id,$param['tournee_jeudi_id'],5,$user)
            && $this->_em->getRepository("AmsModeleBundle:RemplacementJour")->update($msg, $msgException,$id,$param['tournee_vendredi_id'],6,$user)
            && $this->_em->getRepository("AmsModeleBundle:RemplacementJour")->update($msg, $msgException,$id,$param['tournee_samedi_id'],7,$user)
            
            && $this->_em->getRepository("AmsModeleBundle:RemplacementJour")->updateValrem($msg, $msgException, $id)
                    
            && $this->_maj_planning($msg, $msgException, $param, $user, $id);
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "", "", "");
        }
        return true;
    }

    public function _maj(&$msg, &$msgException, $param, $user, &$id, $old_id, $old_date_debut) {
        try {
            $dateDeb = explode('/', trim($param['date_debut']));
            $date_debut = $dateDeb[2] . '-' . $dateDeb[1] . '-' . $dateDeb[0];
            $new_date_debut= $date_debut;
//var_dump($old_date_debut);
//var_dump($date_debut);
            if ($date_debut<>$old_date_debut) {
                // On ramène la date de début au minimum au premier jour du mois de paie si le mois n'est pas bloqué
                //                                       au dernier jour du mois de paie +1 si le mois est bloqué
                $sql = "SELECT greatest(".$this->sqlField->sqlDate($param['date_debut'])."
                                       ,ect.date_debut
                                       ,if(pm.date_blocage is null,pm.date_debut,date_add(pm.date_fin, interval +1 day))) as new_date_debut
                FROM emp_contrat_type ect
                inner join emp_contrat eco on ect.contrat_id=eco.id
                inner join emp_pop_depot epd on ect.id=epd.contrattype_id and ect.date_debut between epd.date_debut and epd.date_fin
                inner join pai_mois pm on epd.flux_id=pm.flux_id
                WHERE ect.id = ".$this->sqlField->sqlIdOrNull($param['contrattype_id']);  
                $new_date_debut=$this->_em->getConnection()->fetchColumn($sql);
            }
//            $this->_em->getConnection()->beginTransaction();
            // Le modèle a une date de début posterieur au modele existant
            // On crée un nouveau modèle, et on change la date de fin de l'ancien modele
            $return=true;
            if ($new_date_debut>$old_date_debut) {
                $return = $this->_insert1($msg, $msgException, $param, $user, $id, $old_id, $new_date_debut);
            // Le modèle a une date de début anterieure au modele existant
            // On change la date de début du nouveau modèle, et on change la date de fin de l'ancien modele
            }else if ($new_date_debut<$old_date_debut) {
                $return = $this->_insert2($msg, $msgException, $param, $user, $old_id, $new_date_debut, $old_date_debut);
            }
            $return &= $this->_update($msg, $msgException, $param, $user, $id);
            if ($return) {
//                $this->_em->getConnection()->commit();
                if ($new_date_debut!=$date_debut){
                    $msg="La date de début a été ramenée au ".$this->sqlField->sqlFrenchDate($new_date_debut);
                    $msgException="La date de début a été ramenée au ".$this->sqlField->sqlFrenchDate($new_date_debut);
                }
            }
            return $return;
        } catch (DBALException $ex) {
 //           $this->_em->getConnection()->rollBack();
            return $this->sqlField->sqlError($msg, $msgException, $ex, "","","");
        }
        return true;
    }

    public function insert(&$msg, &$msgException, $param, $user, &$id) { //mediapresse
        try {
            // On regarde si il existe un modele tournee jour pour le meme modèle et le même jour
            $sql = "SELECT mr.id,mr.date_debut
                FROM modele_remplacement mr
                WHERE mr.contrattype_id = ".$this->sqlField->sqlIdOrNull($param['contrattype_id'])."
                AND   mr.date_fin=(select max(mr2.date_fin) from modele_remplacement mr2 WHERE mr2.contrattype_id = mr.contrattype_id)
                ";
            $old_modele=$this->_em->getConnection()->fetchAssoc($sql);
            if (count($old_modele)>1){ 
                $old_id=$old_modele["id"];
                $old_date_debut=$old_modele["date_debut"];
            } else {
                $old_id=0;
                $old_date_debut='2000-01-01';
            }
            // On ne peut pas rajouter un modèle avant celui qui existe déjà
            // if $param['date_debut']<$old_date_debut then
            return $this->_maj($msg, $msgException, $param, $user, $id, $old_id, $old_date_debut);
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "","","");
        }
        return true;
    }
 
    public function update(&$msg, &$msgException, $param, $user, $id) {
        try {
            return $this->_maj($msg, $msgException,$param,$user,$id,$param['gr_id'], $param['old_date_debut']);
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "", "", "");
        }
        return true;
    }    

    public function delete(&$msg, &$msgException, $param, $user, &$id) {
        try {
            $this->_em->getConnection()->beginTransaction();
            // 29/01/2016 On met la date de fin du modèle supprimé sur le modèle précédent
            $sql = "SELECT id,date_debut
                    FROM modele_remplacement
                    WHERE contrattype_id = ".$param['contrattype_id']."
                    AND date_fin = ".$this->sqlField->sqlDate($param['date_debut'])." + INTERVAL -1 DAY";
            $remplacement=$this->_em->getConnection()->fetchAssoc($sql);
            if (count($remplacement)>=1){ 
                $new_id=$remplacement["id"];
//                $new_date_debut=$remplacement["date_debut"];
                
                $sql = "UPDATE modele_remplacement 
                        SET date_fin=" .$this->sqlField->sqlDate($param['date_fin']) . "
                        WHERE id = ".$new_id;
                $this->_em->getConnection()->prepare($sql)->execute();

//                $this->_maj_planning_remplacement($msg, $msgException, $param, $user, $id, $old_id)
//                && $this->_maj_remplacement($msg, $msgException, $param, $user, $id, $new_date_debut);
            }
//            $this->_em->getConnection()->prepare("DELETE FROM modele_journal WHERE tournee_jour_id = " . $param['gr_id'])->execute();
            $this->_em->getConnection()->prepare("DELETE FROM modele_remplacement_jour WHERE remplacement_id = $id")->execute();
            $this->_em->getConnection()->prepare("DELETE FROM modele_remplacement WHERE id = $id")->execute();
            $this->_em->getConnection()->commit();
        } catch (DBALException $ex) {
            $error=$this->sqlField->sqlError($msg, $msgException, $ex,"Le remplacement est utilisé.<br/>Suppression impossible.","FOREIGN","");
            $this->_em->getConnection()->rollBack();
            return $error;
        }
        return true;
    }
    public function validate(&$msg, &$msgException, $id, $action = "", $param = null) {
        try {
//            $sql = "call mod_valide_remplacement(@validation_id,null,null," . $this->sqlField->sqlId($id) . ")";
            $sql = "call mod_valide_remplacement(@validation_id,".$param['depot_id'].",".$param['flux_id'].",$id)";
            $retour = $this->executeProc($sql, "@validation_id");
            $sql = "call recalcul_tournee_remplacement($id)";
            $this->executeProc($sql);
            
            return $retour;
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "Erreur inconnue.", "", "");
        }
    }


        public function getAnnexeEntete($remplacement_id) {
            $sql = "SELECT 
                    concat_ws(' ',e.nom,e.prenom1,e.prenom2) as employe,
                    d.libelle as depot,
                    group_concat(distinct mt.code order by mt.code separator ' ') as tournees,
                    count(distinct mt.code) as nb_tournees,
                    ecy.cycle,
                    group_concat(distinct time_format(gt.heure_debut,'%kh%i') separator ' ') as heure_debut,
                    date_format(mr.date_debut,'%d/%m/%Y') as date_application,
                    concat(concat_ws('_','Parametres_Tournee',mr.date_debut,e.nom,e.prenom1,e.prenom2),'.pdf') as fichier
                FROM modele_remplacement mr
                INNER JOIN depot d on mr.depot_id=d.id
                LEFT OUTER JOIN modele_remplacement_jour mrj on mr.id=mrj.remplacement_id
                LEFT OUTER JOIN modele_tournee mt on mrj.modele_tournee_id=mt.id
                LEFT OUTER JOIN groupe_tournee gt on mt.groupe_id=gt.id
                LEFT OUTER JOIN emp_cycle ecy on ecy.employe_id=mr.employe_id and mr.date_debut between ecy.date_debut and ecy.date_fin
                LEFT OUTER JOIN employe e on mr.employe_id=e.id
                WHERE mr.id=$remplacement_id
                GROUP BY mr.id,e.id,ecy.id
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function getAnnexeTournees($remplacement_id) {
        $sql = "SELECT 
                    rj.libelle as jour,
                    mt.code,
                    coalesce(time_format(mrj.duree,'%kh%i'),'N/A') as duree,
                    coalesce(mrj.nbcli,'N/A') as nbcli,
                    coalesce(mtj.nbkm_paye,'N/A') as nbkm
                FROM modele_remplacement mr
                INNER JOIN ref_jour rj
                LEFT OUTER JOIN modele_remplacement_jour mrj on mr.id=mrj.remplacement_id and mrj.jour_id=rj.id
                LEFT OUTER JOIN modele_tournee mt on mrj.modele_tournee_id=mt.id
                LEFT OUTER JOIN modele_tournee_jour mtj on mt.id=mtj.tournee_id and mtj.jour_id=rj.id and mr.date_debut between mtj.date_debut and mtj.date_fin
                WHERE mr.id=$remplacement_id
                order by (rj.id+5)%7,mt.code
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function getAnnexeReference($remplacement_id) {
        $sql = "SELECT 
                    rtj.libelle as jour,
                    format(sum(mrj.nbcli)/count(*),1,'fr_FR') as nbcli,
                    time_format(sec_to_time(sum(time_to_sec(mrj.duree))/count(*)),'%Hh%i') as duree,
                    group_concat(distinct format(mrj.valrem_moyen,5,'fr_FR') separator ' ') as valrem
                FROM modele_remplacement mr
                INNER JOIN modele_remplacement_jour mrj on mr.id=mrj.remplacement_id
                INNER JOIN modele_tournee mt on mrj.modele_tournee_id=mt.id
                INNER JOIN ref_jour rj on mrj.jour_id=rj.id
                INNER JOIN ref_typejour rtj on rj.typejour_id=rtj.id
                WHERE mr.id=$remplacement_id
                group by rtj.id
                order by rtj.id
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }

}
