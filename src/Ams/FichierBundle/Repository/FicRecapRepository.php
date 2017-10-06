<?php 

namespace Ams\FichierBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\DBALException;

class FicRecapRepository extends EntityRepository
{
    public function getRecapFicTraite($ficCode, $ficNom, $ficSrc)
    {
        try {
            $qb = $this->createQueryBuilder('r');
            $qb->select($qb->expr()->max('r.id').' AS id' , 'r.socCodeExt AS socCodeExt', 'r.checksum AS checksum', 'e.code AS ficEtat')
                    ->leftJoin('r.ficEtat', 'e')
                    ->where('r.code = :code', 'r.nom = :nom', 'r.ficSource = :src', '(e.code < :codeetat_ok or e.code = :codeetat_a_retraiter)')
                    ->setParameters(array(':code' => $ficCode, ':nom' => $ficNom, ':src' => $ficSrc, ':codeetat_ok' => 9, ':codeetat_a_retraiter' => 99))
                    ->groupBy('socCodeExt')
                    ->addGroupBy('checksum')
                    ->addGroupBy('ficEtat')
                    ->orderBy('id', 'DESC'); // codeetat = 0 veut dire que le fichier precedent a ete bien traite
            // On n'a pas mis de GROUP BY afin de ne recuperer qu'une seule ligne correspondant a MAX(id)
            $aResult    = $qb->getQuery()->getResult();
            return ( isset($aResult[0]) && isset($aResult[0]['id']) && !is_null($aResult[0]['id']) ) ? $aResult[0] : array();
        } 
        catch (DBALException $ex) {
            throw $ex;
        }
    }
    
    public function insert(\Ams\FichierBundle\Entity\FicRecap $ficRecap)
    {
        try {
        
            $dateTraitement = new \DateTime();
            $aInsertVar = array();
            if(!is_null($ficRecap->getCode()))
            {
                $aInsertVar['code']     = $ficRecap->getCode();
            }
            if(!is_null($ficRecap->getFlux()))
            {
                $aInsertVar['flux_id']     = $ficRecap->getFlux()->getId();
            }
            if(!is_null($ficRecap->getNom()))
            {
                $aInsertVar['nom']     = $ficRecap->getNom();
            }
            if(!is_null($ficRecap->getSocCodeExt()))
            {
                $aInsertVar['soc_code_ext']     = $ficRecap->getSocCodeExt();
            }
            if(!is_null($ficRecap->getDateDistrib()))
            {
                $aInsertVar['date_distrib']     = $ficRecap->getDateDistrib()->format('Y-m-d');
            }
            if(!is_null($ficRecap->getDateParution()))
            {
                $aInsertVar['date_parution']     = $ficRecap->getDateParution()->format('Y-m-d');
            }
            if(!is_null($ficRecap->getChecksum()))
            {
                $aInsertVar['checksum']     = $ficRecap->getChecksum();
            }
            if(!is_null($ficRecap->getNbLignes()))
            {
                $aInsertVar['nb_lignes']     = $ficRecap->getNbLignes();
            }       
            if(!is_null($ficRecap->getNbExemplaires()))
            {
                $aInsertVar['nb_exemplaires']     = $ficRecap->getNbExemplaires();
            }
            $aInsertVar['date_traitement']     = $dateTraitement->format('Y-m-d H:i:s');
            if(!is_null($ficRecap->getFicSource()))
            {
                $aInsertVar['fic_source_id']     = $ficRecap->getFicSource()->getId();
            }
            if(!is_null($ficRecap->getSociete()))
            {
                $aInsertVar['societe_id']     = $ficRecap->getSociete()->getId();
            }
            if(!is_null($ficRecap->getFicEtat()))
            {
                $aInsertVar['fic_etat_id']     = $ficRecap->getFicEtat()->getId();
            }
            if(!is_null($ficRecap->getEtaMsg()))
            {
                $aInsertVar['eta_msg']     = $ficRecap->getEtaMsg();
            }
            $this->_em->getConnection()->insert('fic_recap', $aInsertVar);
            return $this->_em->getConnection()->lastInsertId();
        } 
        catch (DBALException $ex) {
            throw $ex;
        }
    }
    
    public function updateEtat($iDernierFicRecap, $aEtatCode)
    {
        try {
            $this->_em->getConnection()->update("fic_recap", $aEtatCode, array("id" => $iDernierFicRecap));
        } 
        catch (DBALException $ex) {
            throw $ex;
        }
    }
    
    /**
     * Récupère les fichiers recap dont 
     *  - l'état est soit Ok, soit Copié
     *  - le flux la table fic_flux est égale à "Import Clients à servir"
     * 
     * @param int $mois
     * @return array
     * 
     */
    public function getCalendrier($mois = null)
    {
        $sql = '
            select * from
            (
                select 
                    if ( etat.code < 9 , 1,10) as ordre,
                    fic_recap.*,
                    soc.code as soc_code, 
                    soc.libelle as soc_libelle,
                    etat.couleur as etat_couleur, 
                    file.path as soc_img_url
                from fic_recap     
                inner join societe soc on fic_recap.societe_id = soc.id
                inner join fic_etat etat on fic_recap.fic_etat_id = etat.id
                left join fichier file on soc.image_id = file.id
                WHERE fic_recap.flux_id=10
                order by ordre asc,
                date_traitement desc
            )t1
            group by societe_id,date_distrib
        '; 
        
        return $this->_em->getConnection()->fetchAll($sql);
    }

    /**
     * @param $startDate
     * @param $endDate
     * @return array
     */
    public function getSocieteFromFicRecapByDate($startDate, $endDate)
    {
   
        $qb = $this->createQueryBuilder('c');
                $qb->select('distinct c')
                ->leftJoin('c.ficEtat', 'ficEtat')
                ->leftJoin('c.societe', 'societe')
                ->where('c.flux = 10')
                ->AndWhere('ficEtat.code = 0')
                ->AndWhere('c.dateParution >= :startDate')
                ->AndWhere('c.dateParution <= :endDate')   
               // ->AndWhere($qb->expr()->between('c.dateParution', ':startDate', ':endDate'))
                ->setParameters(array(':startDate'=>$startDate,':endDate'=>$endDate))
                ->groupBy('societe')
                ->orderBy('societe.libelle');
        return $qb->getQuery()->getResult();
    }

    /**
     * @param \Ams\ProduitBundle\Entity\Societe $societe
     * @param \DateTime $date
     * @return mixed
     */
    public function getLastFicBySocieteAndDate(\Ams\ProduitBundle\Entity\Societe $societe, \DateTime $date)
    {
        $qb = $this->createQueryBuilder('fic');
        $qb ->select('fic')
            ->join('fic.ficEtat', 'ficEtat')
                ->addSelect('ficEtat.code')
            ->where('fic.flux = 10')
            ->AndWhere('fic.dateDistrib = :dateDistrib')
                ->setParameter(':dateDistrib', $date)
            ->AndWhere('fic.societe = :societe')
                ->setParameter(':societe', $societe)
            ->orderBy('fic.dateTraitement DESC, ficEtat.code', 'ASC') // Petit trick pour pouvoir mettre deux order by
            ->setMaxResults(1);
        
        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Récupére les informations(..........) des fichiers dont le code et la date de parution sont passées en paramétre
     * @param $code
     * @param $dateParution
     * @return array
     */
    public function getFicRecapByCodeAndDate($code, $dateParution)
    {
        $sql = "select nom, date_parution, checksum from fic_recap where code = '".$code."' And date_parution = '".$dateParution."'";

        return $this->_em->getConnection()->fetchAll($sql);
    }

    /**
     *              enregistreNewFicRecap
     * Insere une nouvelle ligne dans fic recap avant l'insertion du fichier pour le suivi de production
     * @param $socId
     * @param $ficSource
     * @param $code
     * @param $nom
     * @param $socCode
     * @param $dateDistrib
     * @param $dateParution
     * @param $checksum
     * @param $nbLines
     * @param $nbEx
     * @param $dateTraitement
     * @param $origine
     * @param $ficFluxId
     * @return string
     * @throws DBALException
     */
    public function enregistreNewFicRecap($socId,$ficSource, $code, $nom,$socCode,$dateDistrib,$dateParution,$checksum,$nbLines,$nbEx,$dateTraitement, $origine, $ficFluxId)
    {
        $initVal = array();
//        $dateParution = date_format($dateParution, 'Y-m-d H:i:s');
//        var_dump($dateParution);
        try{
            $initVal['societe_id'] = $socId;
            $initVal['fic_source_id'] = $ficSource ;
            $initVal['code'] = $code;
            $initVal['nom'] = $nom;
            $initVal['soc_code_ext'] = $socCode;
            $initVal['date_distrib'] = $dateDistrib;
            $initVal['date_parution'] = $dateParution;
            $initVal['checksum'] = $checksum;
            $initVal['nb_lignes'] = $nbLines;
            $initVal['nb_exemplaires'] = $nbEx;
            $initVal['date_traitement'] = $dateTraitement ;
            $initVal['origine'] = $origine;
            $initVal['flux_id'] = $ficFluxId;
            $this->_em->getConnection()->insert('fic_recap', $initVal);
            return $this->_em->getConnection()->lastInsertId();
        } catch (DBALException $ex) {
            throw $ex;
        }
    }

    /**
     * MAJ la ligne préalablement inserer pour le suivi de production
     * @param $param
     * @param $ficRecapId
     * @throws DBALException
     */
    public function updateEtatAndMsgAndDateUpdate($param, $ficRecapId){
        try {
            $this->_em->getConnection()->update("fic_recap", $param, array("id" => $ficRecapId));
        }
        catch (DBALException $ex) {
            throw $ex;
        }
    }
    
    /**
     * renvoi le nom du premier fichier (non vide ) intégré pour le suivi de production
     * @param type $code
     * @param type $date
     * @param type $etatId
     * @return type
     */
    public function getFirstFicNameByCodeAndDateParutionAndEtat($code, $date, $etatId = 1)
    {
        $sql= "SELECT id, nom FROM fic_recap WHERE code = '".$code."' AND date_parution = '".$date."' AND fic_etat_id = ".$etatId." and nb_lignes > 2 ORDER BY id ASC LIMIT 1";
        return $this->_em->getConnection()->fetchAll($sql);
    }
}