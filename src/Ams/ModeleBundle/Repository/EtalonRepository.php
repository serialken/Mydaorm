<?php

namespace Ams\ModeleBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class EtalonRepository extends GlobalRepository {

    function select($depot_id, $flux_id, $sqlCondition = '') {
        $sql = "SELECT 
                    me.id,
                    me.type_id,
                    me.depot_id,
                    me.flux_id,
                    coalesce(me.employe_id,0) as employe_id,
                    group_concat(distinct mt.code order by mt.code separator ' ') as tournee,
                    date_format(me.date_requete,'%d/%m/%Y') as date_requete,
                    date_format(me.date_application,'%d/%m/%Y') as date_application,
                    me.demandeur_id,
                    date_format(me.date_demande,'%d/%m/%Y %H:%i:%s') as date_demande,
                    me.valideur_id,
                    date_format(me.date_validation,'%d/%m/%Y %H:%i:%s') as date_validation,
                    date_format(me.date_refus,'%d/%m/%Y %H:%i:%s') as date_refus,
                    me.commentaire,
                    me.date_demande is null and me.date_validation is null and me.date_refus is null as isModif
                 --   me.date_demande is null and me.date_validation is null as isDelete
                FROM etalon me
                LEFT OUTER JOIN etalon_tournee et on me.id=et.etalon_id
                LEFT OUTER JOIN modele_tournee mt on et.modele_tournee_id=mt.id
                WHERE me.depot_id=$depot_id AND me.flux_id=$flux_id " .
                ($sqlCondition != '' ? $sqlCondition : "") . " 
                GROUP BY me.id
                ORDER BY me.date_modif desc"
        ;
        return $this->_em->getConnection()->fetchAll($sql);
    }

    function selectOne($etalon_id, $sqlCondition = '') {
//        $this->updateApplication($msg, $msgException, $param, 0, $etalon_id);
        $this->updateApplication($etalon_id);
        $sql = "SELECT 
                    me.id,
                    me.type_id,
                    rte.libelle as type,
                    me.depot_id,
                    d.libelle as depot,
                    me.flux_id,
                    f.libelle as flux,
                    concat(u.nom,' ',u.prenom) as utilisateur,
                    me.utilisateur_id,
                    date_format(me.date_creation,'%d/%m/%Y %H:%i:%s') as date_creation,
                    date_format(me.date_modif,'%d/%m/%Y %H:%i:%s') as date_modif,
                    me.employe_id,
                    concat(e.nom,' ',e.prenom1) as employe,
                    if(me.employe_id is null,'LMMJVSD',ecy.cycle) as cycle,
                    group_concat(distinct mt.code order by mt.code separator ' ') as tournee,
                    me.commentaire,
                    date_format(pm.date_debut,'%d/%m/%Y') as min_date_application,
                    date_format(me.date_application,'%d/%m/%Y') as date_application,
                    date_format(me.date_requete,'%d/%m/%Y') as date_requete,
                    me.demandeur_id,
                    concat(de.nom,' ',de.prenom) as demandeur,
                    de.email as demandeur_mail,
                    date_format(me.date_demande,'%d/%m/%Y %H:%i:%s') as date_demande,
                    me.valideur_id,
                    concat(v.nom,' ',v.prenom) as valideur,
                    v.email as valideur_mail,
                    date_format(me.date_validation,'%d/%m/%Y %H:%i:%s') as date_validation,
                    date_format(me.date_refus,'%d/%m/%Y %H:%i:%s') as date_refus,
                    concat('etalon/liste-etalon-tournee?depot_id=',me.depot_id,'&flux_id=',me.flux_id,'&etalon_id=',me.id) as url
                FROM etalon me
                INNER JOIN ref_typeetalon rte on me.type_id=rte.id
                INNER JOIN depot d on me.depot_id=d.id
                INNER JOIN ref_flux f on me.flux_id=f.id
                INNER JOIN utilisateur u ON me.utilisateur_id=u.id
                INNER JOIN pai_mois pm ON pm.flux_id=me.flux_id
                LEFT OUTER JOIN employe e on me.employe_id=e.id
                LEFT OUTER JOIN utilisateur de on me.demandeur_id=de.id
                LEFT OUTER JOIN utilisateur v on me.valideur_id=v.id
                LEFT OUTER JOIN emp_cycle ecy on ecy.employe_id=me.employe_id and me.date_application between ecy.date_debut and ecy.date_fin
                LEFT OUTER JOIN etalon_tournee et on me.id=et.etalon_id
                LEFT OUTER JOIN modele_tournee mt on et.modele_tournee_id=mt.id
                WHERE me.id=$etalon_id " .
                ($sqlCondition != '' ? $sqlCondition : "") . "
                GROUP BY me.id,d.id,f.id,u.id,pm.date_debut,e.id,de.id,v.id,ecy.id"
        ;
        return $this->_em->getConnection()->fetchAssoc($sql);
    }

    public function selectCombo($depot_id, $flux_id, $etalon_id) {
        $sql = "SELECT 
                me.id,
                CONCAT_ws(' ',date_format(me.date_application,'%d/%m/%Y'),'-', if(me.employe_id is null,'Nouvelle tournée',CONCAT_ws(' ',e.nom, e.prenom1 , e.prenom2)),'-',group_concat(distinct mt.code order by mt.code separator ' ')) libelle,
                me.date_modif
                FROM etalon me
                LEFT OUTER JOIN etalon_tournee et on me.id=et.etalon_id
                LEFT OUTER JOIN modele_tournee mt on et.modele_tournee_id=mt.id
                LEFT OUTER JOIN employe e on me.employe_id=e.id
                WHERE me.depot_id=$depot_id and me.flux_id=$flux_id
                GROUP BY me.id,e.id
                ";
        if (isset($etalon_id) && $etalon_id == 0) {
            $sql .= "UNION
                SELECT 
                0 as id,
                'Nouveau' libelle,
                '2999-01-01' as date_modif
                ";
        }
        $sql .= "ORDER BY 3 desc
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function insert(&$msg, &$msgException, $param, $user, &$id) {
        try {
            $sql = "INSERT INTO etalon SET
                    depot_id = " . $param['depot_id'] . ",
                    flux_id = " . $param['flux_id'] . ",
                    type_id = " . $this->sqlField->sqlTrim($param['type_id']) . ",
                    employe_id = " . $this->sqlField->sqlTrim($param['employe_id']) . ",
                    commentaire = " . $this->sqlField->sqlTrimQuote($param['commentaire']) . ",
                    date_application = " . $this->sqlField->sqlDate($param['date_application']) . ",
                    date_requete = " . $this->sqlField->sqlDateOrNull($param['date_requete']) . ",
                    demandeur_id = $user,
                    utilisateur_id = $user,
                    date_creation = NOW()";
            $this->_em->getConnection()->prepare($sql)->execute();
            $id = $this->_em->getConnection()->lastInsertId();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "", "", "");
        }
        return true;
    }

    public function update(&$msg, &$msgException, $param, $user, &$id) {
        try {
            $sql = "UPDATE etalon SET
                    type_id = " . $this->sqlField->sqlTrim($param['type_id']) . ",
                    employe_id = " . $this->sqlField->sqlTrim($param['employe_id']) . ",
                    commentaire = " . $this->sqlField->sqlTrimQuote($param['commentaire']) . ",
                    date_application = " . $this->sqlField->sqlDate($param['date_application']) . ",
                    date_requete = " . $this->sqlField->sqlDateOrNull($param['date_requete']) . ",
                    demandeur_id = $user,
                    utilisateur_id = $user,
                    date_modif = NOW()
                    WHERE id=$id";
            $this->_em->getConnection()->prepare($sql)->execute();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "", "", "");
        }
        return true;
    }

    public function updateDemande(&$msg, &$msgException, $param, $user, $id) {
        try {
            $sql = "UPDATE etalon SET
                    type_id = " . $this->sqlField->sqlTrim($param['type_id']) . ",
                    employe_id = " . $this->sqlField->sqlTrim($param['employe_id']) . ",
                    commentaire = " . $this->sqlField->sqlTrimQuote($param['commentaire']) . ",
                    date_application = " . $this->sqlField->sqlDate($param['date_application']) . ",
                    date_requete = " . $this->sqlField->sqlDateOrNull($param['date_requete']) . ",
                    demandeur_id = $user,
                    date_demande = NOW(),
                    utilisateur_id = $user,
                    date_modif = NOW()
                    WHERE date_demande is null 
                    AND id=$id";
            $stmt = $this->_em->getConnection()->prepare($sql);
            $stmt->execute();
            if ($stmt->rowCount() != 1) {
                $msg = "L'étalonnage n'a pas été trouvé ou est déjà soumis";
            }
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "", "", "");
        }
        return ($stmt->rowCount() === 1);
    }

    public function updateValidation(&$msg, &$msgException, $param, $user, $id) {
        try {
            $sql = "UPDATE etalon SET
                    type_id = " . $this->sqlField->sqlTrim($param['type_id']) . ",
                    employe_id = " . $this->sqlField->sqlTrim($param['employe_id']) . ",
                    commentaire = " . $this->sqlField->sqlTrimQuote($param['commentaire']) . ",
                    date_application = " . $this->sqlField->sqlDate($param['date_application']) . ",
                    date_requete = " . $this->sqlField->sqlDateOrNull($param['date_requete']) . ",
                    valideur_id = $user,
                    date_validation = NOW(),
                    utilisateur_id = $user
                    WHERE date_demande is not null 
                    AND date_validation is null 
                    AND date_refus is null 
                    AND id=$id";
            $stmt = $this->_em->getConnection()->prepare($sql);
            $stmt->execute();
            if ($stmt->rowCount() != 1) {
                $msg = "L'étalonnage n'a pas été trouvé ou est déjà validé/refusé";
            }
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "", "", "");
        }
        return ($stmt->rowCount() === 1);
    }

    public function updateRefus(&$msg, &$msgException, $param, $user, $id) {
        try {
            $sql = "UPDATE etalon SET
                    type_id = " . $this->sqlField->sqlTrim($param['type_id']) . ",
                    employe_id = " . $this->sqlField->sqlTrim($param['employe_id']) . ",
                    commentaire = " . $this->sqlField->sqlTrimQuote($param['commentaire']) . ",
                    date_application = " . $this->sqlField->sqlDate($param['date_application']) . ",
                    date_requete = " . $this->sqlField->sqlDateOrNull($param['date_requete']) . ",
                    valideur_id = $user,
                    date_refus = NOW(),
                    utilisateur_id = $user
                    WHERE date_demande is not null 
                    AND date_validation is null 
                    AND date_refus is null 
                    AND id=$id";
            $stmt = $this->_em->getConnection()->prepare($sql);
            $stmt->execute();
            if ($stmt->rowCount() != 1) {
                $msg = "L'étalonnage n'a pas été trouvé ou est déjà validé/refusé";
            }
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "", "", "");
        }
        return ($stmt->rowCount() === 1);
    }

//    public function updateApplication(&$msg, &$msgException, $param, $user, $id) {
    public function updateApplication($id) {
        try {
//                    INNER JOIN (SELECT pm.flux_id,min(prm.date_debut) as min_date_application 
//                                FROM pai_mois pm 
//                                INNER JOIN pai_ref_mois prm ON pm.date_blocage is null AND prm.date_debut>=pm.date_debut OR pm.date_blocage is not null AND prm.date_debut>pm.date_debut
//                                GROUP BY pm.flux_id) as pm ON pm.flux_id=me.flux_id
            $sql = "UPDATE etalon me
                INNER JOIN pai_mois pm ON pm.flux_id=me.flux_id
                    SET
                    commentaire = concat('La date d\'application a été ramené au ',date_format(pm.date_debut,'%d/%m/%Y'),' par le système.\n',commentaire),
                    date_application = pm.date_debut
                    WHERE me.id=" . $id . "
                    AND me.date_validation is null
                    AND me.date_application<pm.date_debut";
            $this->_em->getConnection()->prepare($sql)->execute();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "", "", "");
        }
        return true;
    }

    public function delete(&$msg, &$msgException, $param) {
        try {
            $this->_em->getConnection()->beginTransaction();
            $this->_em->getConnection()->prepare("DELETE FROM etalon_tournee WHERE etalon_id = " . $param['gr_id'])->execute();
            $this->_em->getConnection()->prepare("DELETE FROM etalon WHERE id=" . $param['gr_id'])->execute();
            $this->_em->getConnection()->commit();
        } catch (DBALException $ex) {
            $error = $this->sqlField->sqlError($msg, $msgException, $ex, "L'etalonnage est utilisé.<br/>Suppression impossible.", "FOREIGN", "");
            $this->_em->getConnection()->rollBack();
            return $error;
        }
        return true;
    }

    public function transfert(&$msg, &$msgException, $user, $id, &$idtrt) {
        try {
            $idtrt = null;
            $sql = "call etalon_transferer(@idtrt," . $user . "," . $id . ")";
            $idtrt = $this->executeProc($sql, "@idtrt");
            return true;
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "", "", "");
        }
        }

        public function getAnnexeEntete($etalon_id) {
            $sql = "SELECT 
                    concat_ws(' ',e.nom,e.prenom1,e.prenom2) as employe,
                    d.libelle as depot,
                    group_concat(distinct mt.code order by mt.code separator ' ') as tournees,
                    count(distinct mt.code) as nb_tournees,
                    ecy.cycle,
                    group_concat(distinct time_format(gt.heure_debut,'%kh%i') separator ' ') as heure_debut,
                    date_format(me.date_application,'%d/%m/%Y') as date_application,
                    concat(concat_ws('_','Parametres_Tournee',me.date_application,e.nom,e.prenom1,e.prenom2),'.pdf') as fichier
                FROM etalon me
                INNER JOIN depot d on me.depot_id=d.id
                LEFT OUTER JOIN etalon_tournee et on me.id=et.etalon_id
                LEFT OUTER JOIN modele_tournee mt on et.modele_tournee_id=mt.id
                LEFT OUTER JOIN groupe_tournee gt on mt.groupe_id=gt.id
                LEFT OUTER JOIN employe e on me.employe_id=e.id
                LEFT OUTER JOIN emp_cycle ecy on ecy.employe_id=me.employe_id and me.date_application between ecy.date_debut and ecy.date_fin
                WHERE me.id=$etalon_id
                GROUP BY me.id,e.id,ecy.id
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function getAnnexeTournees($etalon_id) {
        $sql = "SELECT 
                    rj.libelle as jour,
                    mt.code,
                    coalesce(time_format(subtime(subtime(et.duree,et.duree_reperage),et.duree_supplement),'%kh%i'),'N/A') as duree,
                    coalesce(et.nbcli,'N/A') as nbcli,
                    coalesce(et.nbkm,'N/A') as nbkm
                FROM etalon me
                INNER JOIN ref_jour rj
                LEFT OUTER JOIN etalon_tournee et on me.id=et.etalon_id and et.jour_id=rj.id
                LEFT OUTER JOIN modele_tournee mt on et.modele_tournee_id=mt.id
                WHERE me.id=$etalon_id
                order by (rj.id+5)%7,mt.code
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function getAnnexeReference($etalon_id) {
        $sql = "SELECT 
                    rtj.libelle as jour,
                    format(sum(et.nbcli)/count(*),1,'fr_FR') as nbcli,
                    time_format(sec_to_time(sum(time_to_sec(subtime(subtime(et.duree,et.duree_reperage),et.duree_supplement)))/count(*)),'%Hh%i') as duree,
                    group_concat(distinct format(et.valrem_moyen,5,'fr_FR') separator ' ') as valrem
                FROM etalon me
                LEFT OUTER JOIN etalon_tournee et on me.id=et.etalon_id
                LEFT OUTER JOIN modele_tournee mt on et.modele_tournee_id=mt.id
                LEFT OUTER JOIN ref_jour rj on et.jour_id=rj.id
                LEFT OUTER JOIN ref_typejour rtj on rj.typejour_id=rtj.id
                WHERE me.id=$etalon_id
                group by rtj.id
                order by rtj.id
                ";
        return $this->_em->getConnection()->fetchAll($sql);
    }

}
