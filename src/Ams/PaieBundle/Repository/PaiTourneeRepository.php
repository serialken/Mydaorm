<?php
namespace Ams\PaieBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class PaiTourneeRepository extends GlobalRepository {

    function select($depot_id, $flux_id, $date_distrib, $sqlCondition='') {
        $sql = "SELECT
                    pt.id,
                    pt.date_distrib,
                    pt.depot_id,
                    pt.flux_id,
                    pt.groupe_id,
                    pt.employe_id,
                    pt.valrem_org,
                    @valrem_calculee:=case when epd.population_id<0 then 'N/A' else pai_valrem_calculee(pt.date_distrib,pt.flux_id,mtj.duree,pt.nbcli) end as valrem_calculee,
                    case when epd.population_id<0 then 'N/A' else pt.valrem_paie end as valrem,
                    case when epd.population_id<0 then '' else round(abs(1-@valrem_calculee/pt.valrem_paie)*100) end as ecart,
                    pt.majoration,
                    ph.heure_debut_theo debut_theorique,   
                    date_format(pa.duree,'%H:%i') as duree_attente,
                    date_format(pt.duree_retard,'%H:%i') as duree_retard,
                    date_format(pt.heure_debut,'%H:%i') as heure_debut,
                    pt.heure_debut_calculee,
                    pt.duree,
                    addtime(pt.duree_nuit,coalesce(pa.duree_nuit,'00:00')) as duree_nuit,
                    pt.duree_tournee,
                    pt.duree_reperage,
                    pt.duree_supplement,
                    pt.duree as duree_totale,
                    pt.nbkm,
                    pt.nbkm_paye,
                    pt.transport_id,
                    pt.nbtitre,
                    pt.nbcli,
                    pt.nbrep,
                    pt.nbspl,
                    pt.nbprod,
                    pt.nbadr,
                    pt.poids/1000 poids,
                    (pt.tournee_org_id is null or pt.split_id is not null) and pt.date_extrait is null isModif,
                    pt.tournee_org_id is null and pt.date_extrait is null isDelete,
                    IF(pt.tournee_org_id = pt.id,'O', 'N') tournee_mere,
                    -- journal
                    coalesce(min(pe.valide),true) as valide,
                    group_concat(pe.msg order by pe.level,pe.rubrique,pe.code separator '<br/>') as msg,
                    min(pj.id) as journal_id,
                    min(pe.level) as level
                FROM pai_tournee pt
                INNER JOIN pai_heure ph ON pt.groupe_id=ph.groupe_id AND pt.date_distrib=ph.date_distrib
                LEFT OUTER JOIN pai_activite pa on pa.tournee_id=pt.id and pa.activite_id=-2
                left outer JOIN modele_tournee_jour mtj on pt.modele_tournee_jour_id=mtj.id
                LEFT OUTER JOIN pai_journal pj ON pt.id=pj.tournee_id
                LEFT OUTER JOIN pai_ref_erreur pe ON pj.erreur_id=pe.id
                LEFT OUTER JOIN emp_pop_depot epd on pt.employe_id=epd.employe_id and pt.date_distrib between epd.date_debut and epd.date_fin
                WHERE  pt.depot_id=$depot_id  AND  pt.flux_id=$flux_id AND pt.date_distrib = '$date_distrib'
                ".($sqlCondition!='' ? $sqlCondition : "")."
                GROUP BY pt.id,ph.id
                ORDER BY pt.code
               ";
        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function selectPlanning($employe_id, $start, $end) {
        $sql = "SELECT
                    pt.id,
                    pt.code as title,
                    concat(pt.date_distrib,'T',pt.heure_debut_calculee) as start,
                    concat(pt.date_distrib,'T',addtime(pt.heure_debut_calculee,pt.duree)) as end,
                    concat('liste-pai-tournee?depot_id=',pt.depot_id, '&flux_id=',pt.flux_id, '&date_distrib=',pt.date_distrib, '&tournee_id=',pt.id) as url,
                    if(coalesce(min(pe.valide),true),'','#FAD9DD') as backgroundColor
                FROM pai_tournee pt
                LEFT OUTER JOIN pai_journal pj ON pt.id=pj.tournee_id
                LEFT OUTER JOIN pai_ref_erreur pe ON pj.erreur_id=pe.id
                WHERE pt.employe_id = '$employe_id'
                AND pt.date_distrib between '$start' and '$end'
                AND (pt.tournee_org_id is null or pt.split_id is not null)
    --            AND pt.duree<>0
                GROUP BY pt.id
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function ajouter(&$msg, &$msgException, $user, $date_distrib, $modele_tournee_jour_id) {
        try {
            // On ajoute le groupe si il n'existe pas 
            $this->_em->getRepository("AmsPaieBundle:PaiHeure")->ajouter($msg, $msgException, $user, $date_distrib, $modele_tournee_jour_id);
            
            $sql = "
                insert into pai_tournee(
                    date_distrib
                    ,modele_tournee_jour_id
                    ,heure_id, groupe_id, depot_id, flux_id
                    ,code, employe_id
                    ,transport_id, nbkm, nbkm_paye
                    ,duree_attente, duree_retard
                    ,majoration
                    ,utilisateur_id, date_creation
                  )
                select distinct
                    '$date_distrib'
                    ,mtj.id
                    ,ph.id ,gt.id ,gt.depot_id ,gt.flux_id
                    ,mtj.code ,coalesce(mtj.remplacant_id,mtj.employe_id) 
                    ,mtj.transport_id ,mtj.nbkm ,mtj.nbkm_paye
                    ,'00:00' ,'00:00'
                    ,0
                    ," . $user . " ,sysdate()
                from modele_tournee_jour mtj
                inner join modele_tournee mt on mt.id=mtj.tournee_id
                inner join groupe_tournee gt on mt.groupe_id=gt.id
                inner join pai_heure ph on ph.date_distrib='$date_distrib' and ph.groupe_id=mt.groupe_id
                where mtj.id=$modele_tournee_jour_id
                ";
            $this->_em->getConnection()->executeQuery($sql);
            $id = $this->_em->getConnection()->lastInsertId();
            $sql = "
                update pai_tournee pt
                inner join modele_tournee_jour mtj on pt.modele_tournee_jour_id=mtj.id
                inner join modele_remplacement_jour mrj on mrj.modele_tournee_id=mtj.tournee_id and mrj.jour_id=mtj.jour_id
                inner join modele_remplacement mr on mrj.remplacement_id=mr.id and pt.date_distrib between mr.date_debut and mr.date_fin and mr.actif
                set pt.employe_id=mr.employe_id
                where pt.date_extrait is null
                and pt.date_distrib='$date_distrib'
                and mtj.id=$modele_tournee_jour_id
                ";
            $this->_em->getConnection()->executeQuery($sql);
            // La validation n'est pas appelée par DhtmlxController car appel via combo/bouton), on le fait à la mano
            $this->validate($msg, $msgException, $id, "insert");
            //$this->recalcul_horaire_tournee($msg, $msgException, $validation_id, $id);

        } catch (DBALException $ex) {
          return $this->sqlField->sqlError($msg, $msgException, $ex, "La tournée doit être unique.","UNIQUE","");
        } 
        return true;
    }

    public function update(&$msg, &$msgException, $param, $user, &$id) {
        try {
            $sql = "UPDATE pai_tournee pt
                SET pt.employe_id = " . $this->sqlField->sqlTrim($param['employe_id']) . "
                ,   pt.majoration = " . $this->sqlField->sqlTrim($param['majoration']) . "
                ,   pt.duree_attente = " . $this->sqlField->sqlDureeNotNull($param['duree_attente']) . "
                ,   pt.duree_retard = " . $this->sqlField->sqlDuree($param['duree_retard']) . "
                ,   pt.heure_debut = " . $this->sqlField->sqlHeureOrNull($param['heure_debut']) . "
                ,   pt.transport_id = " . $this->sqlField->sqlTrim($param['transport_id']) . "
                ,   pt.nbkm = " . $this->sqlField->sqlTrim($param['nbkm']) . "
                ,   pt.nbkm_paye = " . $this->sqlField->sqlTrim($param['nbkm_paye']) . "
                ,   pt.utilisateur_id = $user
                ,   pt.date_modif = NOW()
                WHERE pt.id = " . $param['gr_id'];
            // if ($param["old_employe_id"] != $param["employe_id"])
            // if ($param["old_employe_id"] != '') ==> réorganiser toutes les tournées de old_employe_id
            // if ($param["employe_id"] != '') ==> réorganiser toutes les tournées de employe_id
            $this->_em->getConnection()->prepare($sql)->execute();
            //04/01/2017
            /*if ($param["old_employe_id"] != $param["employe_id"]) {
                $this->_em->getRepository("AmsPaieBundle:PaiPrdTournee")->recalcul_tournee($param['gr_id']);
            }*/
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "La tournée doit être unique.", "UNIQUE", "");
        }
        return true;
    }

    public function delete(&$msg, &$msgException, $param) {
        try {
            $this->_em->getConnection()->beginTransaction();
            $this->_em->getConnection()->executeQuery("delete from pai_journal where tournee_id=" . $param['gr_id']);
//            $this->_em->getConnection()->executeQuery("delete from pai_prd_tournee where produit_id in (select id from pai_prd_tournee where tournee_id=" . $param['gr_id'] . ")");
            $this->_em->getConnection()->executeQuery("delete from pai_prd_tournee where tournee_id=" . $param['gr_id']);
//            $this->_em->getConnection()->executeQuery("delete from pai_incident where tournee_id=" . $param['gr_id']);
            $this->_em->getConnection()->executeQuery("delete from pai_reclamation where tournee_id=" . $param['gr_id']);
            $this->_em->getConnection()->executeQuery("update client_a_servir_logist l set pai_tournee_id=null where pai_tournee_id=" . $param['gr_id']);
            $this->_em->getConnection()->prepare("DELETE FROM pai_tournee WHERE id = " . $param['gr_id'])->execute();
            $this->_em->getConnection()->commit();
        } catch (DBALException $ex) {
            $error= $this->sqlField->sqlError($msg, $msgException, $ex, "Suppression impossible.","FOREIGN", "");
            $this->_em->getConnection()->rollBack();
            return $error;
        }
        return true;
    }
    public function splitter(&$msg, &$msgException, $user, $date_distrib, $depot_id, $flux_id, $tournee_org_id, $nb_tournee_dst) {
        try {
             if ($nb_tournee_dst >= 2 and $nb_tournee_dst <= 9) {
                $this->_em->getConnection()->beginTransaction();
               // Met à jour la tournee d'origine
                $this->_em->getConnection()->executeQuery("
                update pai_tournee pt
                set utilisateur_id=" . $user . "
                ,   date_modif=sysdate()
                ,   tournee_org_id=pt.id
                ,   code=concat(code,'-')
                where pt.id=" . $tournee_org_id . " and pt.tournee_org_id is null
                and pt.date_extrait is null
                ;");
                $this->_em->getRepository("AmsPaieBundle:PaiPrdTournee")->recalcul_tournee($tournee_org_id);
                // On ajoute les tournées filles
                for ($i = 0; $i < 0 + $nb_tournee_dst; $i++) {
                    $sql = "insert into pai_tournee(
                        utilisateur_id,date_creation
                        ,date_distrib,jour_id,typejour_id
                        ,heure_id,groupe_id,depot_id,flux_id
                        ,modele_tournee_jour_id,code
                        ,employe_id,duree_retard
                        ,heure_debut,nbkm,nbkm_paye,transport_id
                        ,majoration
                        ,tournee_org_id,split_id
                     )
                    select
                    " . $user . "
                    ,sysdate()
                    ,pt.date_distrib,pt.jour_id,pt.typejour_id
                    ,pt.heure_id,pt.groupe_id,pt.depot_id,pt.flux_id
                    ,pt.modele_tournee_jour_id
                    ,concat(pt.code,char(" . (65 + $i) . "))";
                    if ($i == 0) {
                        $sql .= ",pt.employe_id,pt.duree_retard";
                    }else{
                        $sql .= ",null,'00:00'";
                    }
                    $sql .= ",pt.heure_debut,pt.nbkm,0,pt.transport_id";
                    $sql .=" ,pt.majoration , pt.id," . $i . "
                    from pai_tournee pt
                    where pt.id=" . $tournee_org_id . "
                    and pt.date_extrait is null
                    and pt.tournee_org_id=pt.id";

                    $this->_em->getConnection()->executeQuery($sql);
                    $tournee_dst_id = $this->_em->getConnection()->lastInsertId();

                    $sql = "
                    insert into pai_prd_tournee(
                        utilisateur_id,date_creation
                        ,tournee_id,produit_id,natureclient_id
                        ,qte,nbcli,nbcli_unique,nbadr,nbrep
                      )
                    select 
                        " . $user . ",sysdate()
                        ," . $tournee_dst_id . ",ppt.produit_id,ppt.natureclient_id";
                    if ($i == 0) {
                        $sql .= ",ppt.qte,ppt.nbcli,ppt.nbcli_unique,ppt.nbadr,ppt.nbrep";
                    } else {
                        $sql .= ",0,0,0,0,0";
                    }
                    $sql .= " from pai_prd_tournee ppt
                    where ppt.tournee_id=" . $tournee_org_id . " 
                    and ppt.date_extrait is null
                    and ppt.produit_id<>163
                    ;";
                    $this->_em->getConnection()->executeQuery($sql);
                    //10/03/2016 La validation est appelée dans le recalcul
//                    $this->_em->getRepository("AmsPaieBundle:PaiPrdTournee")->validate_tournee($msg, $msgException, $tournee_dst_id);
                    // recalcul des totaux dans tournée
                    //$this->_em->getRepository("AmsPaieBundle:PaiPrdTournee")->recalcul_tournee($tournee_dst_id);
                    $this->validate($msg, $msgException, $tournee_dst_id, "splitter");
                }
                $this->validate($msg, $msgException, $tournee_org_id, "splitter");
                $this->_em->getConnection()->commit();
            }
        } catch (DBALException $ex) {
            $error= $this->sqlField->sqlError($msg, $msgException, $ex, "", "", "");
            $this->_em->getConnection()->rollBack();
            return $error;
        }
    }

    public function deSplitter(&$msg, &$msgException, $user, $tournee_org_id) {
        try {
            $this->_em->getConnection()->beginTransaction();
            $this->_em->getConnection()->executeQuery("
                delete from pai_journal
                where tournee_id in (select id 
                                  from pai_tournee pt
                                  where pt.tournee_org_id=$tournee_org_id and split_id is not null);
                ");
            $this->_em->getConnection()->executeQuery("
                delete from pai_prd_tournee
                where tournee_id in (select id 
                                  from pai_tournee pt
                                  where pt.tournee_org_id=$tournee_org_id and split_id is not null);
                ");
            $this->_em->getConnection()->executeQuery("
                delete from pai_tournee
                where tournee_org_id=$tournee_org_id and split_id is not null;
                ");
            // Met à jour la tournee d'origine
            $this->_em->getConnection()->executeQuery("
                update pai_tournee pt
                set utilisateur_id=$user
                ,   date_modif=sysdate()
                ,   tournee_org_id=null
                ,   code=substr(code,1,11)
                where pt.id=$tournee_org_id
                ;");
                $this->_em->getConnection()->commit();
                $this->_em->getRepository("AmsPaieBundle:PaiPrdTournee")->recalcul_tournee($tournee_org_id);
                $this->validate($msg, $msgException, $tournee_org_id, "splitter");
        } catch (DBALException $ex) {
            $error= $this->sqlField->sqlError($msg, $msgException, $ex, "", "", "");
            $this->_em->getConnection()->rollBack();
            return $error;
        }
    }

    public function validate(&$msg, &$msgException, $id, $action="", $param=null) {
        try {
            $validation_id=null;
            if ($action != "delete"){
                $sql = "call pai_valide_tournee(@validation_id,null,null,null," . $this->sqlField->sqlId($id) . ")";
                $validation_id = $this->executeProc($sql, "@validation_id");
                $this->_em->getRepository("AmsPaieBundle:PaiPrdTournee")->recalcul_tournee($id);
            }
            if ($param['old_employe_id'] != '' && ($action == "delete" || $param['old_employe_id'] != $param['employe_id'])) {
                $this->_em->getRepository("AmsPaieBundle:PaiHeure")->recalcul_horaire($validation_id,null,null,$param['date_distrib'],$param['old_employe_id']);
            }
            return $validation_id;
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "Erreur inconnue.", "", "");
        }
     }
    
    public function alimentation(&$msg, &$msgException, &$idtrt, $user, $date_distrib, $depot_id = 0, $flux_id = 0) {
        try {
            $sql="set @idtrt=".$this->sqlField->sqlIdOrNull($idtrt).";";
            $sql.="call alim_tournee(".$user.",@idtrt,".$this->sqlField->sqlTrimQuote($date_distrib).",".$this->sqlField->sqlIdOrNull($depot_id).",".$this->sqlField->sqlIdOrNull($flux_id).")";
            $idtrt = $this->executeProc($sql, "@idtrt");
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "", "", "");
        }
        return true;
    }

    public function suppression(&$msg, &$msgException, &$idtrt, $user, $date_distrib, $depot_id = 0, $flux_id = 0) {
        try {
            $sql="set @idtrt=".$this->sqlField->sqlIdOrNull($idtrt).";";
            $sql.="call SUPPRIM_TOURNEE(".$user.",@idtrt,".$this->sqlField->sqlTrimQuote($date_distrib).",".$this->sqlField->sqlIdOrNull($depot_id).",".$this->sqlField->sqlIdOrNull($flux_id).")";
            $idtrt = $this->executeProc($sql, "@idtrt");
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "", "", "");
        }
        return true;
    }

    public function recalcul_id($id) {
        try {
            $sql = "call recalcul_tournee_id(".$this->sqlField->sqlIdOrNull($id).")";
            $this ->executeProc($sql);
        } catch (DBALException $ex) {
            throw $ex;
        }
    }

    function selectCombo($depot_id, $flux_id, $date_distrib) {
        $sql = "SELECT
                    pt.id,
                    CONCAT_WS (' - ', pt.code, md.libelle ) as libelle
                    FROM pai_tournee pt
                    INNER JOIN modele_tournee_jour mdj ON mdj.id=pt.modele_tournee_jour_id
                    INNER JOIN modele_tournee md ON md.id=mdj.tournee_id
                    WHERE pt.depot_id=" . $depot_id . " AND pt.flux_id=" . $flux_id . "
                    AND pt.date_distrib='" . $date_distrib . "'
                    ORDER BY pt.code"
        ;
        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function selectComboAjout($depot_id, $flux_id, $date_distrib) {
        $sql = "SELECT DISTINCT
                    mtj.id,    
                    CONCAT_WS (' - ', mtj.code, mt.libelle) as libelle
                FROM modele_tournee_jour mtj
                INNER JOIN  modele_tournee mt ON mt.id =  mtj.tournee_id
                INNER JOIN groupe_tournee gt ON gt.id = mt.groupe_id
                WHERE gt.depot_id = " . $depot_id . " AND gt.flux_id = " . $flux_id . "
                AND mt.actif=true
                AND '" . $date_distrib . "' between mtj.date_debut and mtj.date_fin
                AND mtj.jour_id=DAYOFWEEK('" . $date_distrib . "')
                AND mtj.id not in (SELECT
                    pt.modele_tournee_jour_id
                    FROM pai_tournee pt
                    WHERE pt.depot_id=" . $depot_id . " AND pt.flux_id=" . $flux_id . "
                    AND pt.date_distrib='" . $date_distrib . "')
                ORDER BY 2
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }

    function selectComboSplit($depot_id, $flux_id, $date_distrib) {
        $sql = "SELECT
                    pt.id,
                    CONCAT_WS (' - ', pt.code, md.libelle ) as libelle
                    FROM pai_tournee pt
                    INNER JOIN modele_tournee_jour mdj ON mdj.id=pt.modele_tournee_jour_id
                    INNER JOIN modele_tournee md ON md.id=mdj.tournee_id
                    WHERE pt.depot_id=" . $depot_id . " AND pt.flux_id=" . $flux_id . "
                    AND pt.date_distrib='" . $date_distrib . "'
                    AND pt.tournee_org_id is null
                    AND pt.date_extrait is null
                    ORDER BY pt.code"
        ;
        return $this->_em->getConnection()->fetchAll($sql);
    }

    function selectComboDeSplit($depot_id, $flux_id, $date_distrib) {
        $sql = "SELECT
                    pt.id,
                    CONCAT_WS (' - ', pt.code, md.libelle ) as libelle
                    FROM pai_tournee pt
                    INNER JOIN modele_tournee_jour mdj ON mdj.id=pt.modele_tournee_jour_id
                    INNER JOIN modele_tournee md ON md.id=mdj.tournee_id
                    WHERE pt.depot_id=" . $depot_id . " AND pt.flux_id=" . $flux_id . "
                    AND pt.date_distrib='" . $date_distrib . "'
                    AND pt.id=pt.tournee_org_id
                    AND pt.date_extrait is null
                    ORDER BY pt.code"
        ;
        return $this->_em->getConnection()->fetchAll($sql);
    }

    function selectComboDate($depot_id, $flux_id, $date_distrib) {
        $sql = "SELECT
                    pt.id,
                    CONCAT_WS (' - ', pt.code, md.libelle ) as libelle
                FROM pai_tournee pt
                INNER JOIN modele_tournee_jour mdj ON mdj.id=pt.modele_tournee_jour_id
                INNER JOIN modele_tournee md ON md.id=mdj.tournee_id
                WHERE pt.depot_id=" . $depot_id . " AND pt.flux_id=" . $flux_id . "
                AND pt.date_distrib='" . $date_distrib . "'
                ORDER BY pt.code"
        ;
        return $this->_em->getConnection()->fetchAll($sql);
    }

    function selectComboGroupeDate($depot_id, $flux_id, $date_distrib) {
        $sql = "SELECT DISTINCT
                    gt.id,
                    gt.code as libelle
                    FROM pai_tournee pt
                    inner join groupe_tournee gt on pt.groupe_id=gt.id
                    WHERE pt.depot_id=" . $depot_id . " AND pt.flux_id=" . $flux_id . "
                    AND pt.date_distrib='" . $date_distrib . "'
                    ORDER BY pt.code"
        ;
        return $this->_em->getConnection()->fetchAll($sql);
    }

    function selectComboJournal($anneemois, $depot_id=0, $flux_id=0) {
        $sql = "SELECT
                    pt.id,
                    pt.code as libelle
                    FROM pai_journal pj
                    INNER JOIN pai_tournee pt on pj.tournee_id=pt.id
                    WHERE pj.anneemois='" . $anneemois . "'
                    ".($depot_id!=0?"AND pj.depot_id=" . $depot_id:"")."
                    ".($flux_id!=0?"AND pj.flux_id=" . $flux_id:"")."
                    ORDER BY pt.code"
        ;
        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function getAnnexeEmploye($employe_depot_hst_id) {
        $sql = "select e.matricule,e.nom,e.prenom1,e.prenom2,d.code as depot
                    , date_format(ped.d,'%d/%m/%Y') as date_debut
                    , date_format(least(ped.f,coalesce(pit.date_distrib,'2999-01-01')),'%d/%m/%Y') as date_fin
                    , date_format(pit.date_debut,'%d/%m/%Y %H:%i:%S') as date_calcul
                    , q.nbabo as nbabo
                    , q.taux as taux_qualite
                    , q.nbabo_DF as nbabo_DF
                    , q.taux_DF as taux_qualite_DF
                    , q.societe_id
                    , (pit.typetrt!='GENERE_PLEIADES_CLOTURE' and pit.typetrt!='GENERE_PLEIADES_STC') as provisoire
                from pai_ev_emp_depot_hst ped
                inner join pai_int_traitement pit on pit.id=ped.idtrt
                inner join employe e on ped.employe_id=e.id
                inner join depot d on ped.depot_id=d.id or ped.depot_id between -121 and -101 and d.id=18
                left outer join pai_ev_emp_pop_depot_hst q on pit.id=q.idtrt and ped.employe_id=q.employe_id and q.d not like '%-05-01' -- attention potentielement il pourrait y en avoir plusieurs !!!!
                where ped.id=abs('" . $employe_depot_hst_id . "')
                group by e.id
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function getAnnexeDetail($employe_depot_hst_id) {
        $sql = "select   
                pah.date_distrib
                , pai_typejour_employe(pah.date_distrib,ped.employe_id) as typejour_id
                , pah.libelle
                , TRIM(TRAILING '.' FROM TRIM(TRAILING '0' from pah.qte)) as qte
                , TRIM(TRAILING '.' FROM TRIM(TRAILING '0' from pah.taux)) as taux
                , round(pah.val,2) as val
/*                , date_format(pah.duree_tournee,'%H:%i') as duree_tournee
                , date_format(pah.duree_activite,'%H:%i') as duree_activite
                , date_format(pah.duree_autre,'%H:%i') as duree_autre
                , date_format(pah.duree_nuit,'%H:%i') as duree_nuit
                , date_format(pah.duree_totale,'%H:%i') as duree_totale
                */
                , time_format(sec_to_time(round(time_to_sec(pah.duree_tournee)/60)*60),'%H:%i') as duree_tournee
                , time_format(sec_to_time(round(time_to_sec(pah.duree_activite)/60)*60),'%H:%i') as duree_activite
                , time_format(sec_to_time(round(time_to_sec(pah.duree_autre)/60)*60),'%H:%i') as duree_autre
                , time_format(sec_to_time(round(time_to_sec(pah.duree_nuit)/60)*60),'%H:%i') as duree_nuit
                , time_format(sec_to_time(round(time_to_sec(pah.duree_totale)/60)*60),'%H:%i') as duree_totale
                , pah.nb_reclamation, pah.nbrec_abonne, pah.nbrec_diffuseur, pah.nb_incident
                , pah.nbkm_paye
                from pai_ev_emp_depot_hst ped
                inner join  pai_ev_annexe_hst pah on pah.idtrt=ped.idtrt and ped.id=pah.employe_depot_hst_id -- and pah.date_distrib between ped.d and ped.f
                where ped.id=abs('" . $employe_depot_hst_id . "')
                order by pah.date_distrib,pah.libelle;
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function getAnnexeEv($employe_depot_hst_id) {
        $sql = "select
                    prpg.annexe as poste,prpg.libelle,peh.datev,
                    sum(peh.qte) as qte,
                    peh.taux,
                    sum(peh.val) as val
                from pai_ev_emp_depot_hst ped
                inner join employe e on ped.employe_id=e.id
                inner join pai_ev_hst peh on peh.idtrt=ped.idtrt and e.matricule=peh.matricule and peh.datev between ped.d and ped.f
                inner join pai_ref_postepaie_general prpg on peh.poste=prpg.poste
                where ped.id=abs('" . $employe_depot_hst_id . "')
                and prpg.annexe<>'----'
                group by prpg.annexe,prpg.libelle,peh.datev,peh.taux
                order by peh.datev,prpg.annexe;
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }

    function getEmargement($depot_id, $flux_id, $date_distrib) {
        $sql = "SELECT 
                    pt.groupe_id,
                    group_concat(pt.code order by pt.code separator ' ') as code,
                    coalesce(pt.employe_id,-pt.id) as emp_id,
                    CONCAT_WS(' ', e.nom , e.prenom1, e.prenom2) as nom_prenom,
                    sum(pt.nbrep) nb_reperage,
                    sum(pt.nbcli) qte,
                    sum(IF(rt.km_paye,pt.nbkm_paye,0)) nbkm_paye,
                    TIME_FORMAT(sec_to_time(sum(time_to_sec(pt.duree))), '%H:%i') as duree
                FROM  pai_tournee pt
                LEFT OUTER JOIN employe e ON e.id = pt.employe_id
                LEFT OUTER JOIN emp_pop_depot epd on pt.employe_id=epd.employe_id and pt.date_distrib between epd.date_debut and epd.date_fin
                INNER JOIN ref_transport rt on pt.transport_id=rt.id
                WHERE pt.depot_id=" . $depot_id . " AND pt.flux_id=" . $flux_id . " AND  pt.date_distrib='" . $date_distrib . "'
                AND (pt.tournee_org_id IS NULL OR pt.split_id IS NOT NULL )
                AND coalesce(epd.typetournee_id,1) in (1,2) -- Seulement Porteur et polyvalent
                group by coalesce(pt.employe_id,-pt.id)
                order by code
        ";
        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function getEmargementReclamation($depot_id, $flux_id, $date_distrib) {

        $sql= " select 
                r.employe_id,
                group_CONCAT(date_format(r.date_distrib,'%d/%m'),'(', r.nbrec ,':', r.code,') ' order by r.date_distrib desc) as reclamation
                from(
                    select 
                    pt.date_distrib,
                    pt.employe_id,
                    s.code,
                    sum(pr.nbrec_abonne)+sum(pr.nbrec_diffuseur) as nbrec
                    from pai_reclamation pr
                    INNER JOIN pai_tournee pt ON  pr.tournee_id = pt.id 
                    INNER JOIN societe s on pr.societe_id=s.id
                    INNEr JOIN pai_ref_mois prm on '" . $date_distrib . "' between prm.date_debut and prm.date_fin and pr.anneemois=prm.anneemois
                    WHERE pt.depot_id=" . $depot_id . " AND pt.flux_id=" . $flux_id . "
                    AND (pt.tournee_org_id IS NULL OR pt.split_id IS NOT NULL )
                    group by date_distrib,employe_id,s.code
                    having sum(pr.nbrec_abonne)+sum(pr.nbrec_diffuseur)<>0
                  ) as r
                  group by r.employe_id
                  ";
       return $this->_em->getConnection()->fetchAll($sql);
    }
    
     public function getEmargementProduit($depot_id, $flux_id, $date_distrib) {

       $sql= " SELECT  
                SUM(if(p.type_id=1,ppt.nbcli,ppt.qte)) as nbClient, 
                pt.groupe_id, 
                pt.employe_id, 
                concat(p.soc_code_ext,if(p.type_id in (2,3),' Sup','')) code_prd,
                1
                FROM pai_prd_tournee ppt
                INNER JOIN pai_tournee pt ON ppt.tournee_id=pt.id
                LEFT JOIN produit p ON p.id = ppt.produit_id
                WHERE pt.depot_id=" . $depot_id . " AND pt.flux_id=" . $flux_id . " AND pt.date_distrib='" . $date_distrib . "'
                AND (pt.tournee_org_id IS NULL OR pt.split_id IS NOT NULL )
                AND ppt.natureclient_id=0
                AND p.type_id in (1,2,3)
                GROUP BY pt.groupe_id,pt.employe_id, concat(p.soc_code_ext,if(p.type_id in (2,3),'Sup ',''))
                HAVING SUM(if(p.type_id=1,ppt.nbcli,ppt.qte))<>0
                
                Union
                
                SELECT  
                SUM(if(p.type_id=1,ppt.nbcli,ppt.qte)) as nbClient, 
                pt.groupe_id, 
                pt.employe_id, 
                if(p.type_id in (2,3),'Dif sup','Dif') code_prd,
                2
                FROM pai_prd_tournee ppt
                INNER JOIN pai_tournee pt ON ppt.tournee_id=pt.id
                INNER JOIN produit p ON ppt.produit_id=p.id
                WHERE pt.depot_id=" . $depot_id . " AND pt.flux_id=" . $flux_id . " AND pt.date_distrib='" . $date_distrib . "'
                AND (pt.tournee_org_id IS NULL OR pt.split_id IS NOT NULL )
                AND ppt.natureclient_id=1
                AND p.type_id in (1,2,3)
                GROUP BY pt.groupe_id,pt.employe_id,if(p.type_id in (2,3),'Sup Dif','Dif')
                HAVING SUM(if(p.type_id=1,ppt.nbcli,ppt.qte))<>0
                
                Union
                
                SELECT  
                SUM(ppt.qte) as nbClient, 
                pt.groupe_id, 
                pt.employe_id, 
                concat(p.soc_code_ext,' HP') code_prd,
                3
                FROM pai_prd_tournee ppt
                INNER JOIN pai_tournee pt ON ppt.tournee_id=pt.id
                INNER JOIN produit p ON ppt.produit_id=p.id
                INNER JOIN produit_type t ON p.type_id=t.id and t.hors_presse
                WHERE pt.depot_id=" . $depot_id . " AND pt.flux_id=" . $flux_id . " AND pt.date_distrib='" . $date_distrib . "'
                AND (pt.tournee_org_id IS NULL OR pt.split_id IS NOT NULL )
                AND p.type_id not in (1,2,3)
                GROUP BY pt.groupe_id,pt.employe_id,concat(p.soc_code_ext,' HP')
                HAVING SUM(ppt.qte)<>0
                
                Union
                
                SELECT  
                SUM(ppt.qte) as nbClient, 
                pt.groupe_id, 
                pt.employe_id, 
                concat(p.soc_code_ext,' FR') code_prd,
                4
                FROM pai_prd_tournee ppt
                INNER JOIN pai_tournee pt ON ppt.tournee_id=pt.id
                INNER JOIN produit p ON ppt.produit_id=p.id
                INNER JOIN produit_type t ON p.type_id=t.id and not t.hors_presse
                WHERE pt.depot_id=" . $depot_id . " AND pt.flux_id=" . $flux_id . " AND pt.date_distrib='" . $date_distrib . "'
                AND (pt.tournee_org_id IS NULL OR pt.split_id IS NOT NULL )
                AND p.type_id not in (1,2,3)
                GROUP BY pt.groupe_id,pt.employe_id,concat(p.soc_code_ext,' FR')
                HAVING SUM(ppt.qte)<>0
                ORDER BY 5,4,3
        ";
       return $this->_em->getConnection()->fetchAll($sql);
    }

}