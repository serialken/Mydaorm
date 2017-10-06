<?php
namespace Ams\EmployeBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class RechercheRepository extends GlobalRepository {

    function select($nom, $prenom) {
        $sql = "SELECT
                concat(epd.id,'-',ecy.id) as id,
                e.matricule,
                concat(e.nom,' ',e.prenom1) as nom,
                greatest(epd.date_debut,coalesce(ecy.date_debut,'1900-01-01')) as date_debut,
                least(epd.date_fin,coalesce(ecy.date_fin,'2999-01-01')) as date_fin,
                ps.date_extrait as date_stc,
                epd.societe_id,
                epd.depot_id,
--                epd.flux_id,
                epd.rc,
                epd.emploi_id,
                epd.typetournee_id,
                epd.typecontrat_id,
                ecy.cycle,
                date_format(epd.heure_debut,'%H:%i') as heure_debut,
                epd.nbheures_garanties,
                peh.qte as nbheures_reelles,
                peepdh.taux,
                pptmf.relationlibcfin as motif_fin,
                now() between greatest(epd.date_debut,coalesce(ecy.date_debut,'1900-01-01')) and date_add(least(epd.date_fin,coalesce(ecy.date_fin,'2999-01-01')),interval +1 day) as actif
                FROM emp_pop_depot epd
                INNER JOIN employe e on epd.employe_id=e.id
                INNER JOIN ref_emploi re on re.id = epd.emploi_id
                INNER JOIN pai_png_relationcontrat pprc on epd.rcoid=pprc.oid
                LEFT OUTER JOIN pai_png_ta_relamotiffin pptmf on pprc.relatmotiffin=pptmf.oid
                LEFT OUTER JOIN emp_cycle ecy on epd.employe_id=ecy.employe_id and ecy.date_debut<=epd.date_fin and ecy.date_fin>=epd.date_debut
                LEFT OUTER JOIN pai_stc ps on epd.rcoid=ps.rcoid
                LEFT OUTER JOIN pai_int_traitement pit on pit.typetrt = 'GENERE_PLEIADES_CLOTURE'  and pit.statut='T' and pit.flux_id=epd.flux_id
                    AND pit.anneemois = (select max(prm.anneemois) from pai_ref_mois prm,pai_mois pm 
                            where pm.flux_id=epd.flux_id and prm.anneemois<pm.anneemois 
                            and prm.date_debut<=least(epd.date_fin,coalesce(ecy.date_fin,'2999-01-01'))
                            )
                LEFT OUTER JOIN pai_ev_hst peh on pit.id=peh.idtrt and e.matricule=peh.matricule and peh.poste='HTPX'
                LEFT OUTER JOIN pai_ev_emp_pop_depot_hst peepdh on pit.id=peepdh.idtrt and epd.employe_id=peepdh.employe_id and epd.depot_id=peepdh.depot_id and re.code=peepdh.emploi_code
                WHERE e.nom like '%".$nom."%' AND '".$nom."'<>'' AND '".$prenom."'=''
                OR e.prenom1 like '%".$prenom."%' AND '".$prenom."'<>'' AND '".$nom."'=''
                OR e.nom like '%".$nom."%' AND e.prenom1 like '%".$prenom."%'AND '".$nom."'<>'' AND '".$prenom."'<>''
                ORDER BY e.nom,e.prenom1,e.prenom2,e.matricule,epd.rc,greatest(epd.date_debut,coalesce(ecy.date_debut,'1900-01-01'))
            ";
        return $this->_em->getConnection()->fetchAll($sql);
    }
}