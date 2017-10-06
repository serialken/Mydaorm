<?php

namespace Ams\ModeleBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class RemplacementJourRepository extends GlobalRepository {

    function select($remplacement_id, $sqlCondition = '') {
        $sql = "SELECT 
                    mrj.id,
                    CONCAT_WS(' ', er.nom, er.prenom1, er.prenom2) remplacant_id,
                    ect.date_fin_prevue,
                    mr.date_debut,
                    mr.date_fin,
                    mrj.jour_id,
                    coalesce(pt.code,mt.code) as code,
                    date_format(mrj.date_distrib,'%d/%m/%Y') as date_distrib,
                    CONCAT_WS(' ', et.nom, et.prenom1, et.prenom2) employe_id,
                    mrj.duree,
                    mrj.nbcli,
                    mrj.valrem,
                    mrj.valrem_moyen,
                    true as valide,
                    0 as level
                FROM modele_remplacement mr
                INNER JOIN employe er on mr.employe_id=er.id
                INNER JOIN emp_contrat_type ect on mr.contrattype_id=ect.id
                LEFT OUTER JOIN modele_remplacement_jour mrj on mr.id=mrj.remplacement_id
                LEFT OUTER JOIN pai_tournee pt ON mrj.pai_tournee_id=pt.id
                LEFT OUTER JOIN modele_tournee mt on mrj.modele_tournee_id=mt.id
                LEFT OUTER JOIN employe et on pt.employe_id=et.id
                WHERE mrj.remplacement_id=".$this->sqlField->sqlIdOrNull($remplacement_id)."
                 " . ($sqlCondition != '' ? $sqlCondition : "")
        ;
        return $this->_em->getConnection()->fetchAll($sql);
    }

    public function insert(&$msg, &$msgException, $remplacement_id, $jour, $jour_id, $user, &$id) {
        try {
            $sql = "INSERT INTO modele_remplacement_jour(remplacement_id,jour_id,utilisateur_id,date_creation)
                    select r.id,$jour_id,$user,now()
                    from modele_remplacement r
                    inner join emp_pop_depot epd on r.employe_id=epd.employe_id and r.date_debut between epd.date_debut and epd.date_fin
                    where r.id=$remplacement_id
                    AND epd.$jour
                    ";
            $this->_em->getConnection()->prepare($sql)->execute();
            $id = $this->_em->getConnection()->lastInsertId();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "", "", "");
        }
        return true;
    }

    public function update(&$msg, &$msgException, $remplacement_id, $tournee_id, $jour_id, $user) {
        try {
            $sql = "UPDATE modele_remplacement_jour mrj
                    INNER JOIN modele_remplacement mr ON mrj.remplacement_id=mr.id
--                    inner join modele_tournee_jour mtj on mtj.id=mtj.tournee_id=" . $this->sqlField->sqlIdOrNull($tournee_id) . " and mrj.jour_id=mtj.jour_id
                    LEFT OUTER JOIN pai_tournee pt ON pt.id=(select max(pt2.id) 
                                                            from pai_tournee pt2 
                                                            inner join modele_tournee_jour mtj on pt2.modele_tournee_jour_id=mtj.id
                                                            where mtj.tournee_id=" . $this->sqlField->sqlIdOrNull($tournee_id) . " and mrj.jour_id=mtj.jour_id
                                                            and pt2.date_distrib<=mr.date_debut)
                    SET
                    mrj.modele_tournee_id = " . $this->sqlField->sqlIdOrNull($tournee_id) . ",
                    mrj.pai_tournee_id = pt.id,
                    mrj.date_distrib = pt.date_distrib,
                    mrj.valrem = pt.valrem,
                    mrj.etalon=cal_modele_etalon(sec_to_time(pt.duree_tournee),pt.nbcli),
                    mrj.duree = pt.duree_tournee,
                    mrj.nbcli = pt.nbcli,
                    mrj.utilisateur_id = $user,
                    mrj.date_modif = NOW()
                    WHERE mrj.remplacement_id=$remplacement_id
                    AND mrj.jour_id=$jour_id
                    ";
            $this->_em->getConnection()->prepare($sql)->execute();
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "", "", "");
        }
        return true;
    }

    public function updateValrem(&$msg, &$msgException, $remplacement_id) {
        try {
            $sql = "call mod_remplacement_update_valrem(0,null,null,$remplacement_id)";
            $this->executeProc($sql);
/*
            $sql = "UPDATE modele_remplacement_jour mrj
                    INNER JOIN modele_remplacement mr ON mrj.remplacement_id=mr.id
                    inner join ref_typetournee rtt on mr.flux_id=rtt.id
                    inner join pai_ref_remuneration prr_new on rtt.societe_id=prr_new.societe_id AND rtt.population_id=prr_new.population_id AND mr.date_debut between prr_new.date_debut and prr_new.date_fin
                    INNER JOIN (select mr.id
                        ,   modele_valrem(mrj.date_distrib,mr.flux_id,sec_to_time(sum(time_to_sec(mrj.duree))),sum(mrj.nbcli)) as valrem_moyen
                        ,   cal_modele_etalon(sec_to_time(sum(mrj.duree)),sum(mrj.nbcli)) as etalon_moyen
                        from modele_remplacement_jour mrj
                        INNER JOIN modele_remplacement mr ON mrj.remplacement_id=mr.id
                        WHERE mrj.remplacement_id=$remplacement_id and mrj.jour_id=1
                        group by mr.id) as mrm on mrm.id=mr.id
                    SET mrj.tauxhoraire=prr_new.valeur
                    ,   mrj.valrem_moyen=mrm.valrem_moyen
                    ,   mrj.etalon_moyen=mrm.etalon_moyen
                    WHERE mrj.remplacement_id=$remplacement_id
                    AND mrj.jour_id=1";
            $this->_em->getConnection()->prepare($sql)->execute();

            $sql = "UPDATE modele_remplacement_jour mrj
                    INNER JOIN modele_remplacement mr ON mrj.remplacement_id=mr.id
                    inner join ref_typetournee rtt on mr.flux_id=rtt.id
                    inner join pai_ref_remuneration prr_new on rtt.societe_id=prr_new.societe_id AND rtt.population_id=prr_new.population_id AND mr.date_debut between prr_new.date_debut and prr_new.date_fin
                    INNER JOIN (select mr.id
                        ,   modele_valrem(mrj.date_distrib,mr.flux_id,sec_to_time(sum(time_to_sec(mrj.duree))),sum(mrj.nbcli)) as valrem_moyen
                        ,   cal_modele_etalon(sec_to_time(sum(mrj.duree)),sum(mrj.nbcli)) as etalon_moyen
                        from modele_remplacement_jour mrj
                        INNER JOIN modele_remplacement mr ON mrj.remplacement_id=mr.id
                        WHERE mrj.remplacement_id=$remplacement_id and mrj.jour_id<>1
                        group by mr.id) as mrm on mrm.id=mr.id
                    SET mrj.tauxhoraire=prr_new.valeur
                    ,   mrj.valrem_moyen=mrm.valrem_moyen
                    ,   mrj.etalon_moyen=mrm.etalon_moyen
                    WHERE mrj.remplacement_id=$remplacement_id
                    AND mrj.jour_id<>1";
            $this->_em->getConnection()->prepare($sql)->execute();
 *
 */
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "", "", "");
        }
        return true;
    }

}
