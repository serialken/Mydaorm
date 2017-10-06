<?php
namespace Ams\ModeleBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class ModeleTourneeJourRepository extends GlobalRepository {

    public function select($depot_id, $flux_id, $modele_tournee_id,$sqlCondition='') {
        /*
                    @valrem_moyenne:=(SELECT round(AVG(mtj2.valrem_calculee),5)
                                      FROM modele_tournee_jour mtj2 
                                      WHERE mtj.date_debut between mtj2.date_debut and mtj2.date_fin  
                                      AND mtj.employe_id=mtj2.employe_id and mtj.tournee_id=mtj2.tournee_id
                                      GROUP BY mtj2.employe_id,mtj2.tournee_id
                                  ) as valrem_moyenne,
         
                    CASE WHEN mt.typetournee_id=1 THEN
                        round(abs(1-@valrem_moyen/mtj.valrem)*100)
                    ELSE
                        round(abs(1-mtj.valrem_calculee/mtj.valrem)*100)
                    END ecart,
         */
            $sql = "SELECT 
                    mtj.id,
                    mt.groupe_id,
                    mt.code as codeTournee,
                    mtj.jour_id,
                    mtj.tournee_id,
                    mtj.employe_id,
                    mtj.remplacant_id,
                    date_format(g.heure_debut,'%H:%i') as heure_debut,
                    mtj.duree, -- date_format(mtj.duree,'%H:%i') as duree,
                    mtj.nbcli,
                    mtj.nbkm,
                    mtj.nbkm_paye,
                    mtj.transport_id,
                    mtj.date_debut,
                    mtj.date_fin,
                    mtj.code,
                    mtj.depart_depot,
                    mtj.retour_depot,
                    -- valrem
                    mtj.valrem,
                    mtj.valrem_moyen,
                    mtj.tauxhoraire,
                    -- journal
                    (g.flux_id=2 and mtj.date_fin>curdate()) as isDelete,
                    mt.actif and (mtj.date_fin>curdate()) as isModif,
                    -- journal
                    coalesce(min(me.valide),true) as valide,
                    group_concat(me.msg order by me.level,me.rubrique,me.code separator '<br/>') as msg,
                    min(mj.id) as journal_id,
                    min(me.level) as level
                FROM modele_tournee_jour mtj
                INNER JOIN  modele_tournee mt ON mt.id =  mtj.tournee_id
                INNER JOIN groupe_tournee g ON g.id = mt.groupe_id
                LEFT OUTER JOIN modele_journal mj on mtj.id=mj.tournee_jour_id
                LEFT OUTER JOIN modele_ref_erreur me ON mj.erreur_id=me.id
                WHERE g.depot_id=$depot_id AND g.flux_id=$flux_id ".
                (isset($modele_tournee_id) && $modele_tournee_id!=0?"AND mtj.tournee_id=$modele_tournee_id ":" ").
                ($sqlCondition!='' ? $sqlCondition : "")."
                GROUP BY mtj.id,mt.id,g.id
                ORDER BY mt.actif desc,mtj.date_fin desc,g.code,mt.code,mtj.jour_id"
                ;
            return $this->_em->getConnection()->fetchAll($sql);
    }

    public function selectPlanning($depot_id, $flux_id, $employe_id) {
        $sql = "SELECT
                    mtj.id,
                    mtj.tournee_id,
                    gt.depot_id,
                    gt.flux_id,
                    mtj.jour_id,
                    coalesce(gt.heure_debut,'00:00') as heure_debut,
                    coalesce(addtime(gt.heure_debut,mtj.duree),'24:00') as heure_fin,
                    mtj.code,
                    -- journal
                    coalesce(min(me.valide),true) as valide,
                    group_concat(me.msg order by me.level,me.rubrique,me.code separator '<br/>') as msg,
                    min(mj.id) as journal_id,
                    min(me.level) as level
                FROM modele_tournee_jour mtj
                INNER JOIN  modele_tournee mt ON mt.id =  mtj.tournee_id
                INNER JOIN groupe_tournee gt ON gt.id = mt.groupe_id
                left outer join modele_journal mj on mtj.id=mj.tournee_jour_id
                LEFT OUTER JOIN modele_ref_erreur me ON mj.erreur_id=me.id
                WHERE gt.depot_id=$depot_id AND gt.flux_id=$flux_id
                AND coalesce(mtj.employe_id,mtj.remplacant_id) = ".$this->sqlField->sqlIdOrNull($employe_id)."
                AND mt.actif=true
                AND curdate() between mtj.date_debut and mtj.date_fin
                GROUP BY mtj.id,gt.id
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    public function update(&$msg, &$msgException, $param, $user, &$id) {
            if ($param['super_modif']==1){
                return $this->update_mediapresse($msg, $msgException, $param, $user, $id);
            } else {
                return $this->update_proximy($msg, $msgException, $param, $user, $id);
            }
    }
    
    public function update_proximy(&$msg, &$msgException, $param, $user, &$id) {
        try {
            $sql = "UPDATE modele_tournee_jour mtj
                left outer join ref_transport rt on rt.id=" . $this->sqlField->sqlTrim($param['transport_id']) . "
                SET
--                mtj.employe_id = " . $this->sqlField->sqlIdOrNull($param['employe_id']) . ",
                mtj.remplacant_id = " . $this->sqlField->sqlIdOrNull($param['remplacant_id']) . ",
                mtj.depart_depot = " . $param['depart_depot'] . ",
                mtj.retour_depot = " . $param['retour_depot'] . ",
                mtj.transport_id = " . $this->sqlField->sqlIdOrNull($param['transport_id']) . ",
                mtj.nbkm = " . $this->sqlField->sqlTrim($param['nbkm']) . ",
                mtj.nbkm_paye = case when coalesce(rt.km_paye,1) then " . $this->sqlField->sqlTrim($param['nbkm_paye']) . " else 0 end,
                mtj.date_modif = NOW()
                WHERE mtj.id = " .$param['gr_id'];
            $this->_em->getConnection()->prepare($sql)->execute();
            
            return $this->_maj_pai_tournee_data_proximy($msg, $msgException, $param, $user, $id)
            &&  $this->_maj_modele_proximy($msg, $msgException, $param, $user, $id,$this->sqlField->sqlDate($param['date_debut']));
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "","","");
        }
        return true;
    }
    
    public function _maj_pai_tournee_data_proximy(&$msg, &$msgException, $param, $user, $id) {
        try {
            // Met à jour la valeur de rémunération dans la paie
//            if ($param['valrem']<>$param['old_valrem']) {
                $sql = "update pai_tournee pt
                inner join pai_mois pm on pt.flux_id=pm.flux_id
                inner join modele_tournee_jour mtj on mtj.id=$id
                set pt.transport_id=mtj.transport_id,
                pt.nbkm=mtj.nbkm,
                pt.nbkm_paye=mtj.nbkm_paye,
                mtj.date_modif = NOW()
                where pt.modele_tournee_jour_id = mtj.id
                and pt.date_distrib between  mtj.date_debut and mtj.date_fin
                and pt.date_extrait is null
                and (pt.date_distrib>=pm.date_debut and pm.date_blocage is null or pt.date_distrib>pm.date_fin and pm.date_blocage is not null)
                ";
                $this->_em->getConnection()->prepare($sql)->execute();
//            }
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "","","");
        }
        return true;
    }

    public function _maj_modele_proximy(&$msg, &$msgException, $param, $user, $id,$date_debut) {
        try {
            // Validation des tournees
            $sql = "call pai_valide_modele(@validation_id,$date_debut,$id)";
            $validation_id = $this->executeProc($sql, "@validation_id");

            $sql="set @validation=".$this->sqlField->sqlId($validation_id).";";
            $sql.="call recalcul_tournee_modele(@validation_id,$date_debut,$id)";
            $this->executeProc($sql, "@validation_id");
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "","","");
        }
        return true;
    }
    
    public function _maj_casl_mediapresse(&$msg, &$msgException, $param, $user, $id, $old_id) {
        try {
            $sql = "update client_a_servir_logist casl
                inner join modele_tournee_jour mtj on mtj.id=".$id."
                -- left outer join pai_tournee pt on casl.pai_tournee_id=pt.id
                set casl.tournee_jour_id=".$id."
                where casl.tournee_jour_id = ".$old_id."
                and casl.date_distrib between mtj.date_debut and mtj.date_fin
                -- and pt.date_extrait is null
                ";
            $this->_em->getConnection()->prepare($sql)->execute();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "","","");
        }
        return true;
    }

    public function _maj_feuille_portage_mediapresse(&$msg, &$msgException, $param, $user, $id, $old_id) {
        try {
            $sql = "update feuille_portage fp
                inner join modele_tournee_jour mtj on mtj.id=".$id."
                set fp.tournee_jour_id=".$id."
                where fp.tournee_jour_id = ".$old_id."
                and fp.date_distrib between mtj.date_debut and mtj.date_fin
                ";
            $this->_em->getConnection()->prepare($sql)->execute();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "","","");
        }
        return true;
    }

    public function _maj_crm_mediapresse(&$msg, &$msgException, $param, $user, $id, $old_id) {
        try {
            $sql = "set @TRIGGER_CRM_RECALCUL_MAJORATION=false;"; 
            // On recalcul les majorations à la fin de _update_valrem avec recalcul_horaire_modele
            $sql .= "update crm_detail c
                inner join modele_tournee_jour mtj on mtj.id=".$id."
                -- left outer join pai_tournee pt on c.pai_tournee_id=pt.id
                set c.modele_tournee_jour_id=".$id."
                where c.modele_tournee_jour_id = ".$old_id."
                and c.date_imputation_paie between mtj.date_debut and mtj.date_fin
                -- and pt.date_extrait is null
                ";
            $sql .= "set @TRIGGER_CRM_RECALCUL_MAJORATION=true;"; 
            $this->_em->getConnection()->prepare($sql)->execute();

            $sql = "update crm_detail_tmp c
                inner join modele_tournee_jour mtj on mtj.id=".$id."
                -- left outer join pai_tournee pt on c.pai_tournee_id=pt.id
                set c.modele_tournee_jour_id=".$id."
                where c.modele_tournee_jour_id = ".$old_id."
                and c.date_imputation_paie between  mtj.date_debut and mtj.date_fin
                -- and pt.date_extrait is null
                ";
//            $sql .= "set @TRIGGER_CRM_RECALCUL_MAJORATION=true;"; 
            $this->_em->getConnection()->prepare($sql)->execute();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "","","");
        }
        return true;
    }
    public function _maj_pai_tournee_modele_mediapresse(&$msg, &$msgException, $param, $user, $id, $old_id) {
        try {
            $sql = "update pai_tournee pt
                inner join modele_tournee_jour mtj on mtj.id=".$id."
                set pt.modele_tournee_jour_id=".$id."
                where pt.modele_tournee_jour_id = ".$old_id."
                and pt.date_distrib between  mtj.date_debut and mtj.date_fin
                -- and pt.date_extrait is null
                ";
            $this->_em->getConnection()->prepare($sql)->execute();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "","","");
        }
        return true;
    }

    public function _maj_pai_tournee_data_mediapresse(&$msg, &$msgException, $param, $user, $id) {
        try {
            // Met à jour la valeur de rémunération dans la paie
//            if ($param['valrem']<>$param['old_valrem']) {
                $sql = "update pai_tournee pt
                inner join pai_mois pm on pt.flux_id=pm.flux_id
                inner join modele_tournee_jour mtj on mtj.id=pt.modele_tournee_jour_id and pt.date_distrib between  mtj.date_debut and mtj.date_fin
                set   pt.transport_id=mtj.transport_id
                  ,   pt.nbkm=mtj.nbkm
                  ,   pt.nbkm_paye=mtj.nbkm_paye
                  ,   mtj.date_modif = NOW()
                where pt.modele_tournee_jour_id = $id
                and pt.date_extrait is null
                and (pt.date_distrib>=pm.date_debut and pm.date_blocage is null or pt.date_distrib>pm.date_fin and pm.date_blocage is not null)
                ";
                $this->_em->getConnection()->prepare($sql)->execute();
//            }
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "","","");
        }
        return true;
    }

    public function _maj_modele_mediapresse(&$msg, &$msgException, $param, $user, $id,$date_debut) {
        try {
            // Validation des tournees
            $sql = "call pai_valide_modele(@validation_id,$date_debut,$id)";
            $validation_id = $this->executeProc($sql, "@validation_id");

            $sql="set @validation=".$this->sqlField->sqlId($validation_id).";";
            $sql.="call recalcul_tournee_modele(@validation_id,$date_debut,$id)";
            $this->executeProc($sql, "@validation_id");
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "","","");
        }
        return true;
    }

    public function _insert1_mediapresse(&$msg, &$msgException, $param, $user, &$id, $old_id, $new_date_debut) {
        try {
            $sql = "UPDATE modele_tournee_jour SET
                date_fin = '".$new_date_debut."' + INTERVAL -1 DAY,
                utilisateur_id=".$user.",
                date_modif = NOW()
                WHERE id = ".$old_id;         
//            $this->_em->getRepository("AmsPaieBundle:PaiIntLog")->log(1,'_insert',$sql);
            $this->_em->getConnection()->prepare($sql)->execute();

            $sql = "DELETE mj 
                FROM modele_journal mj
                INNER JOIN modele_tournee_jour mtj on mj.tournee_jour_id=mtj.id
                WHERE mj.tournee_jour_id = ".$old_id."
                AND NOW()<=mtj.date_fin";                
//            $this->_em->getRepository("AmsPaieBundle:PaiIntLog")->log(1,'_insert',$sql);
            $this->_em->getConnection()->prepare($sql)->execute();

            $sql = "INSERT INTO modele_tournee_jour 
                    (tournee_id,jour_id,
                    date_debut,date_fin,
                    employe_id,remplacant_id,
                    valrem,valrem_moyen,etalon,etalon_moyen,tauxhoraire,
                    nbcli,duree,
                    transport_id,nbkm,nbkm_paye,
                    depart_depot,retour_depot,
                    utilisateur_id,date_creation
                    )
                SELECT
                    " . $this->sqlField->sqlIdOrNull($param['tournee_id']) . ",
                    " . $this->sqlField->sqlIdOrNull($param['jour_id']) . ",
                    " . $this->sqlField->sqlQuote($new_date_debut) .",'2999-01-01',
                    " . $this->sqlField->sqlIdOrNull($param['employe_id']) . ",
                    " . $this->sqlField->sqlIdOrNull($param['remplacant_id']) . ",
                    " . $param['valrem'] . ",
                    " . $param['valrem'] . ",
                    cal_modele_etalon(".$this->sqlField->sqlQuote($param['duree'])."," . $param['nbcli'] . "),
                    cal_modele_etalon(".$this->sqlField->sqlQuote($param['duree'])."," . $param['nbcli'] . "),
                    prr.valeur,
                    " . $param['nbcli'] . ",
                    " . $this->sqlField->sqlQuote($param['duree']) . ",
                    " . $this->sqlField->sqlIdOrNull($param['transport_id']) . ",
                    " . $this->sqlField->sqlTrim($param['nbkm']) . ",
                    case when coalesce(rt.km_paye,1) then " . $this->sqlField->sqlTrim($param['nbkm_paye']) . " else 0 end,
                    " . $param['depart_depot'] . ",
                    " . $param['retour_depot'] . ",    
                    " . $user . ",
                    NOW()
                FROM modele_tournee mt
                inner join groupe_tournee gt on mt.groupe_id=gt.id
                inner join ref_typetournee rtt on gt.flux_id=rtt.id
                inner join pai_ref_remuneration prr on rtt.societe_id=prr.societe_id AND rtt.population_id=prr.population_id AND '".$new_date_debut."' between prr.date_debut and prr.date_fin
                LEFT OUTER JOIN modele_tournee_jour mtj on mt.id=mtj.tournee_id and mtj.id=".$old_id."
                left outer join ref_transport rt on rt.id=" . $this->sqlField->sqlTrim($param['transport_id']) . "
                WHERE mt.id=".$this->sqlField->sqlIdOrNull($param['tournee_id']);            
//            $this->_em->getRepository("AmsPaieBundle:PaiIntLog")->log(1,'_insert',$sql);
            $this->_em->getConnection()->prepare($sql)->execute();
            $id = $this->_em->getConnection()->lastInsertId();
//            $this->validate($msg, $msgException, $id);
            
            return $this->_maj_casl_mediapresse($msg, $msgException, $param, $user, $id, $old_id)
            && $this->_maj_feuille_portage_mediapresse($msg, $msgException, $param, $user, $id, $old_id)
            && $this->_maj_crm_mediapresse($msg, $msgException, $param, $user, $id, $old_id)
            && $this->_maj_pai_tournee_modele_mediapresse($msg, $msgException, $param, $user, $id, $old_id)
            && $this->_maj_pai_tournee_data_mediapresse($msg, $msgException, $param, $user, $id)
            && $this->_maj_modele_mediapresse($msg, $msgException, $param, $user, $id, $new_date_debut);
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "","","");
        }
        return true;
    }

    public function _insert2_mediapresse(&$msg, &$msgException, $param, $user, $id, $new_date_debut, $old_date_debut) {
        // Ici $new_date_debut<$old_date_debut
        try {
            $sql = "SELECT id FROM modele_tournee_jour
                WHERE tournee_id = ".$param['tournee_id']."
                and jour_id = ".$param['jour_id']."
                and date_fin = " . $this->sqlField->sqlQuote($old_date_debut) ." + INTERVAL -1 DAY";         
            $old_id=$this->_em->getConnection()->fetchColumn($sql);;

            if (isset($old_id) && $old_id) {
                $sql = "UPDATE modele_tournee_jour mtj SET
                    mtj.date_fin = " . $this->sqlField->sqlQuote($new_date_debut) . " + INTERVAL -1 DAY,
                    mtj.utilisateur_id=".$user.",
                    mtj.date_modif = NOW()
                    WHERE mtj.id = ".$old_id;         
                $this->_em->getConnection()->prepare($sql)->execute();
            }
            $sql = "UPDATE modele_tournee_jour mtj
                left outer join ref_transport rt on rt.id=" . $this->sqlField->sqlTrim($param['transport_id']) . "
                SET
                mtj.date_debut = " . $this->sqlField->sqlQuote($new_date_debut) .",
                mtj.employe_id = " . $this->sqlField->sqlIdOrNull($param['employe_id']) . ",
                mtj.remplacant_id = " . $this->sqlField->sqlIdOrNull($param['remplacant_id']) . ",
                mtj.valrem = " . $param['valrem'] . ",
                mtj.valrem_moyen = " . $param['valrem'] . ",
                mtj.etalon = cal_modele_etalon(".$this->sqlField->sqlQuote($param['duree'])."," . $param['nbcli'] . "),
                mtj.etalon_moyen = cal_modele_etalon(".$this->sqlField->sqlQuote($param['duree'])."," . $param['nbcli'] . "),
                mtj.nbcli = " . $param['nbcli'] . ",
                mtj.duree = " . $this->sqlField->sqlQuote($param['duree']) . ",
                mtj.depart_depot = " . $param['depart_depot'] . ",
                mtj.retour_depot = " . $param['retour_depot'] . ",
                mtj.transport_id = " . $this->sqlField->sqlIdOrNull($param['transport_id']) . ",
                mtj.nbkm = " . $this->sqlField->sqlTrim($param['nbkm']) . ",
                mtj.nbkm_paye = case when coalesce(rt.km_paye,1) then " . $this->sqlField->sqlTrim($param['nbkm_paye']) . " else 0 end,
                mtj.date_modif = NOW()
                WHERE mtj.id = " .$id;
            $this->_em->getConnection()->prepare($sql)->execute();

            if (isset($old_id) && $old_id) {
                return $this->_maj_casl_mediapresse($msg, $msgException, $param, $user, $id, $old_id)
                && $this->_maj_feuille_portage_mediapresse($msg, $msgException, $param, $user, $id, $old_id)
                && $this->_maj_crm_mediapresse($msg, $msgException, $param, $user, $id, $old_id)
                && $this->_maj_pai_tournee_modele_mediapresse($msg, $msgException, $param, $user, $id, $old_id)
                && $this->_maj_pai_tournee_data_mediapresse($msg, $msgException, $param, $user, $id)
                && $this->_maj_modele_mediapresse($msg, $msgException, $param, $user, $id, $new_date_debut);
            } else {
                return $this->_maj_pai_tournee_data_mediapresse($msg, $msgException, $param, $user, $id)
                && $this->_maj_modele_mediapresse($msg, $msgException, $param, $user, $id, $new_date_debut);
            }
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

    public function _update_mediapresse(&$msg, &$msgException, $param, $user, &$id) {
        try {
            $sql = "UPDATE modele_tournee_jour mtj
                left outer join ref_transport rt on rt.id=" . $this->sqlField->sqlTrim($param['transport_id']) . "
                SET
                mtj.employe_id = " . $this->sqlField->sqlIdOrNull($param['employe_id']) . ",
                mtj.remplacant_id = " . $this->sqlField->sqlIdOrNull($param['remplacant_id']) . ",
                mtj.valrem = " . $param['valrem'] . ",
                mtj.valrem_moyen = " . $param['valrem'] . ",
                mtj.etalon = cal_modele_etalon(".$this->sqlField->sqlQuote($param['duree'])."," . $param['nbcli'] . "),
                mtj.etalon_moyen = cal_modele_etalon(".$this->sqlField->sqlQuote($param['duree'])."," . $param['nbcli'] . "),
                mtj.nbcli = " . $param['nbcli'] . ",
                mtj.duree = " . $this->sqlField->sqlQuote($param['duree']) . ",
                mtj.depart_depot = " . $param['depart_depot'] . ",
                mtj.retour_depot = " . $param['retour_depot'] . ",
                mtj.transport_id = " . $this->sqlField->sqlIdOrNull($param['transport_id']) . ",
                mtj.nbkm = " . $this->sqlField->sqlTrim($param['nbkm']) . ",
                mtj.nbkm_paye = case when coalesce(rt.km_paye,1) then " . $this->sqlField->sqlTrim($param['nbkm_paye']) . " else 0 end,
                mtj.date_modif = NOW()
                WHERE mtj.id = " .$param['gr_id'];
            $this->_em->getConnection()->prepare($sql)->execute();
            
            return $this->_maj_pai_tournee_data_mediapresse($msg, $msgException, $param, $user, $id)
            &&  $this->_maj_modele_mediapresse($msg, $msgException, $param, $user, $id,$this->sqlField->sqlDate($param['date_debut']));
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "","","");
        }
        return true;
    }

    public function _maj_mediapresse(&$msg, &$msgException, $param, $user, &$id, $old_id, $old_date_debut, $old_valrem) {
        try {
            $dateDeb = explode('/', trim($param['date_debut']));
            $date_debut = $dateDeb[2] . '-' . $dateDeb[1] . '-' . $dateDeb[0];
            $new_date_debut= $date_debut;
            
            if ($param['valrem']<>$old_valrem || $date_debut<>$old_date_debut) {
                // On ramène la date de début au minimum au premier jour du mois de paie si le mois n'est pas bloqué
                //                                       au dernier jour du mois de paie +1 si le mois est bloqué
                $sql = "SELECT greatest(".$this->sqlField->sqlDate($param['date_debut'])."
                                        ,if(pm.date_blocage is null,pm.date_debut,date_add(pm.date_fin, interval +1 day))
                                        ,coalesce(date_add(max(pt.date_distrib), interval +1 day),'2000-01-01')) as date_debut
                FROM modele_tournee mt
                inner join groupe_tournee gt on mt.groupe_id=gt.id
                inner join pai_mois pm on gt.flux_id=pm.flux_id
                LEFT OUTER JOIN pai_tournee pt on pt.modele_tournee_jour_id = ".$old_id." AND pt.date_extrait is not null
                WHERE mt.id = ".$this->sqlField->sqlIdOrNull($param['tournee_id'])."
                GROUP BY pm.date_debut,pt.modele_tournee_jour_id";  
//                $this->_em->getRepository("AmsPaieBundle:PaiIntLog")->log(1,'insert',$sql);
                $new_date_debut=$this->_em->getConnection()->fetchColumn($sql);
            }
            $this->_em->getConnection()->beginTransaction();
            // Le modèle a une date de début posterieur au modele existant
            // On crée un nouveau modèle, et on change la date de fin de l'ancien modele
            if ($new_date_debut>$old_date_debut) {
                $return = $this->_insert1_mediapresse($msg, $msgException, $param, $user, $id, $old_id, $new_date_debut);
            // Le modèle a une date de début anterieure au modele existant
            // On change la date de début du nouveau modèle, et on change la date de fin de l'ancien modele
            }else if ($new_date_debut<$old_date_debut) {
                $return = $this->_insert2_mediapresse($msg, $msgException, $param, $user, $old_id, $new_date_debut, $old_date_debut);
            }else {
                $return = $this->_update_mediapresse($msg, $msgException, $param, $user, $id);
            }
            if ($return) {
                $this->_em->getConnection()->commit();
                if ($new_date_debut!=$date_debut){
                    $msg="La date de début a été ramenée au ".$this->sqlField->sqlFrenchDate($new_date_debut);
                    $msgException="La date de début a été ramenée au ".$this->sqlField->sqlFrenchDate($new_date_debut);
                }
            }
            return $return;
        } catch (DBALException $ex) {
            $this->_em->getConnection()->rollBack();
            return $this->sqlField->sqlError($msg, $msgException, $ex, "","","");
        }
        return true;
    }

    public function insert(&$msg, &$msgException, $param, $user, &$id) { //mediapresse
        try {
            // On regarde si il existe un modele tournee jour pour le meme modèle et le même jour
            $sql = "SELECT id,date_debut,valrem
                FROM modele_tournee_jour
                WHERE tournee_id = ".$this->sqlField->sqlIdOrNull($param['tournee_id'])."
                AND   jour_id = ".$this->sqlField->sqlIdOrNull($param['jour_id'])."
                AND   date_fin='2999-01-01'
                ";
            $old_modele=$this->_em->getConnection()->fetchAssoc($sql);
            if (count($old_modele)>1){ 
                $old_id=$old_modele["id"];
                $old_date_debut=$old_modele["date_debut"];
                $old_valrem=$old_modele["valrem"];
            } else {
                $old_id=0;
                $old_date_debut='2000-01-01';
                $old_valrem=0;
            }
            // On ne peut pas rajouter un modèle avant celui qui existe déjà
            // if $param['date_debut']<$old_date_debut then
            return $this->_maj_mediapresse($msg, $msgException, $param, $user, $id, $old_id, $old_date_debut, $old_valrem);
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "","","");
        }
        return true;
    }

    public function update_mediapresse(&$msg, &$msgException, $param, $user, &$id) {
        return $this->_maj_mediapresse($msg, $msgException, $param, $user, $id, $param['gr_id'], $param['old_date_debut'], $param['old_valrem']);
    }

    public function delete(&$msg, &$msgException, $param, $user, &$id) { //mediapresse
        try {
            $this->_em->getConnection()->beginTransaction();
            // 29/01/2016 On met la date de fin du modèle supprimé sur le modèle précédent
            $sql = "SELECT id,date_debut
                    FROM modele_tournee_jour 
                    WHERE tournee_id = ".$param['tournee_id']."
                    AND jour_id = ".$param['jour_id']."
                    AND date_fin = ".$this->sqlField->sqlDate($param['date_debut'])." + INTERVAL -1 DAY";
            $modele=$this->_em->getConnection()->fetchAssoc($sql);
            if (count($modele)>=1){ 
                $old_id=$param['gr_id'];
                $id=$modele["id"];
                $new_date_debut=$modele["date_debut"];
                
                $sql = "UPDATE modele_tournee_jour 
                        SET date_fin=" .$this->sqlField->sqlDate($param['date_fin']) . "
                        WHERE id = ".$id;
                $this->_em->getConnection()->prepare($sql)->execute();

                $this->_maj_casl_mediapresse($msg, $msgException, $param, $user, $id, $old_id)
                && $this->_maj_crm_mediapresse($msg, $msgException, $param, $user, $id, $old_id)
                && $this->_maj_pai_tournee_modele_mediapresse($msg, $msgException, $param, $user, $id, $old_id)
                && $this->_maj_pai_tournee_data_mediapresse($msg, $msgException, $param, $user, $id)
                && $this->_maj_modele_mediapresse($msg, $msgException, $param, $user, $id, $new_date_debut);
            }
            $this->_em->getConnection()->prepare("DELETE FROM modele_journal WHERE tournee_jour_id = " . $param['gr_id'])->execute();
            $this->_em->getConnection()->prepare("DELETE FROM modele_tournee_jour WHERE id = " . $param['gr_id'])->execute();
            $this->_em->getConnection()->commit();
        } catch (DBALException $ex) {
            $error=$this->sqlField->sqlError($msg, $msgException, $ex,"Le modèle de tournée jour est utilisé.<br/>Suppression impossible.","FOREIGN","");
            $this->_em->getConnection()->rollBack();
            return $error;
        }
        return true;
    }


    public function validate(&$msg, &$msgException, $id, $action="", $param=null) {
        try {
            if ($action=='delete') { return true; }
            $sql = "call mod_valide_tournee_jour(@validation_id,null,null,null," . $this->sqlField->sqlId($id) . ")";
            return $this->executeProc($sql, "@validation_id");
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "Erreur inconnue.", "", "");
       }
     }
    
    public function selectComboModele($depot_id, $flux_id) {
        $sql = "SELECT DISTINCT
                mtj.id,
                mtj.code as libelle
                FROM modele_tournee_jour mtj
                INNER JOIN  modele_tournee mt ON mt.id =  mtj.tournee_id
                INNER JOIN groupe_tournee gt ON gt.id = mt.groupe_id
                WHERE gt.depot_id = $depot_id AND gt.flux_id = $flux_id
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    public function selectComboPaie($depot_id, $flux_id, $date_distrib) {
        $sql = "SELECT DISTINCT
                mtj.id,    
                CONCAT_WS (' - ', mtj.code, mt.libelle ) as libelle
                FROM modele_tournee_jour mtj
                INNER JOIN  modele_tournee mt ON mt.id =  mtj.tournee_id
                INNER JOIN groupe_tournee gt ON gt.id = mt.groupe_id
                WHERE gt.depot_id = $depot_id AND gt.flux_id = $flux_id
                AND mt.actif=true
                AND '$date_distrib' between mtj.date_debut and mtj.date_fin
                AND mtj.jour_id=DAYOFWEEK('$date_distrib')
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    public function selectComboRemplacement($depot_id, $flux_id, $anneemois, $jour_id) {
        $sql = "SELECT DISTINCT
                mt.id,
                concat(mtj.code,' - ',coalesce(e.nom,''),' ',coalesce(e.prenom1,'')) as libelle
                FROM modele_tournee_jour mtj
                INNER JOIN  modele_tournee mt ON mt.id =  mtj.tournee_id
                INNER JOIN pai_ref_mois prm ON prm.anneemois='".$anneemois."'
                INNER JOIN groupe_tournee gt ON gt.id = mt.groupe_id
                LEFT OUTER JOIN employe e on mtj.employe_id=e.id
                WHERE gt.depot_id = $depot_id AND gt.flux_id = $flux_id
                AND mtj.jour_id=$jour_id
                AND mtj.date_fin>=prm.date_debut and mtj.date_debut<=prm.date_fin
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }
    

    
    /*
     * Retourne les tournées par depots et flux
     */
    public function getTourneeByDepotFlux($depot_id, $flux_id,$jour_id = false) {
            $sql = "SELECT 
                        mtj.id,mtj.code 
                    FROM modele_tournee_jour mtj
                        INNER JOIN  modele_tournee mt ON mt.id =  mtj.tournee_id
                        INNER JOIN groupe_tournee g ON g.id = mt.groupe_id
                    WHERE 
                        g.depot_id= $depot_id 
                        AND g.flux_id=$flux_id ";
            if($jour_id != '')
                $sql.= " AND mtj.jour_id = $jour_id ";
            $sql.= " AND curdate() between mtj.date_debut and mtj.date_fin
                    ORDER BY mtj.code"
                ;
            return $this->_em->getConnection()->fetchAll($sql);
    }

    public function getTourneeInLast7dayByDepot($depot){

        $sql = "SELECT DISTINCT mtj.id, mtj.code
                FROM `modele_tournee_jour` as mtj
                LEFT JOIN  modele_tournee mt ON mt.id = mtj.tournee_id
                LEFT JOIN  group_tournee gt ON gt.id = mt.group_id  
                WHERE date_debut BETWEEN current_date()-7 AND current_date()
                AND gt.depot_id = $depot
                ";
            return $this->_em->getConnection()->fetchAll($sql);
    }
    

    public function findIdByModeleTourneeRefJour($iTournee, $iRefJour,$dDateFin) {
        $connection = $this->getEntityManager()->getConnection();
        $sql ='
                SELECT id FROM modele_tournee_jour
                WHERE tournee_id = '.$iTournee.'
                AND jour_id = '.$iRefJour.'
                AND date_debut <= CURDATE()
                AND date_fin >= "'.$dDateFin.'"
               ';
        $stmt = $connection->executeQuery($sql);
        return $stmt->fetch();
    }
    
    
    public function findIdByCodeDateValid($sCodeTournee, $date) {
        $connection = $this->getEntityManager()->getConnection();
        $sql ='
                SELECT id FROM modele_tournee_jour
                WHERE code = "'.$sCodeTournee.'"
                AND date_debut <=  "'.$date.'"
                AND date_fin >= "'.$date.'"
               ';
        $stmt = $connection->executeQuery($sql);
        return $stmt->fetch();
    }    

    public function findAllByCodeDateValid($sCodeTournee, $date) {
        $connection = $this->getEntityManager()->getConnection();
        $sql ='
                SELECT * FROM modele_tournee_jour
                WHERE code = "'.$sCodeTournee.'"
                AND date_debut <=  "'.$date.'"
                AND date_fin >= "'.$date.'"
               ';
        $stmt = $connection->executeQuery($sql);
        return $stmt->fetch();
    }
    
    public function findByCode($sCodeTournee) {
        $connection = $this->getEntityManager()->getConnection();
        $sql ="
               SELECT * FROM modele_tournee_jour 
               WHERE code = '$sCodeTournee' 
                    AND CURDATE() BETWEEN date_debut AND date_fin
             ";
        $stmt = $connection->executeQuery($sql);
        return $stmt->fetch();
    }
    
    public function findByCodeDateValid($sCodeTournee, $date) {

        $qb = $this->createQueryBuilder('mtj')
        ->where('mtj.code = :sCodeTournee')
        ->setParameter('sCodeTournee', $sCodeTournee)
        ->andWhere('mtj.date_debut <= :date_debut')
        ->setParameter('date_debut', $date)
        ->andWhere('mtj.date_fin >= :date_fin')
        ->setParameter('date_fin', $date)
        ;
        
        return $qb->getQuery()->getSingleResult();
    }

    /**
     * Méthode qui retourne la liste des IDs de modeles tournées jour pour un jour donné et un dépot, optionnellement filtrée
     * @param string $sDate La date du jour en question (pour déduir un jour type)
     * @param string $sDepotCode Le code du dépot concerné
     * @param int $iFluxId L'ID du flux concerné
     * @param array $aExcludeIds La liste des Ids exclues (peut servir à trouver les MTJ des tournées vides)
     * @return array $aResults Le jeu d'enregistrements
     */
    public function listeMTJIds($sDate, $sDepotCode, $iFluxId, $aExcludeIds = NULL ){
        
        $connection = $this->getEntityManager()->getConnection();
        $sSql ="
                SELECT mtj.id
		FROM modele_tournee_jour AS mtj
		JOIN modele_tournee AS mt ON mt.id = mtj.tournee_id
		JOIN groupe_tournee AS gt ON gt.id = mt.groupe_id
		JOIN depot AS d ON d.id = gt.depot_id
			WHERE ('".$sDate."' BETWEEN mtj.date_debut AND mtj.date_fin)
			AND mtj.jour_id =  DAYOFWEEK('".$sDate."')
			AND d.code = '".$sDepotCode."'
                        AND gt.flux_id = $iFluxId
               ";
        
        // Exclusion des IDs
        if (!is_null($aExcludeIds) && !empty($aExcludeIds)){
            $sSql .= " AND mtj.id NOT IN(".implode(',',$aExcludeIds).")";
        }
        
        $aResultsTmp = $this->_em->getConnection()->fetchAll($sSql);
        $aResults =  array();
        if (!empty($aResultsTmp)){
             foreach ($aResultsTmp as $aResult){
                 $aResults[] = (int)$aResult['id'];
             }
        }
        return $aResults;
    }
    
    
    
    public function getTourneeParDateCode($date,$code_tournne) {
        $connection = $this->getEntityManager()->getConnection();
        $sql="select * from modele_tournee_jour where code='$code_tournne' 
                and '$date' between date_debut and date_fin";
        $stmt = $connection->executeQuery($sql);
        return $stmt->fetch();
    }
    
    
}