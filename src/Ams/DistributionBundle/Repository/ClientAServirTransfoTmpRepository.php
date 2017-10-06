<?php 

namespace Ams\DistributionBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\DBALException;

class ClientAServirTransfoTmpRepository extends EntityRepository
{    
    public function truncate()
    {
        try {
            $sTruncate = " TRUNCATE TABLE client_a_servir_transfo_tmp ";
            $this->_em->getConnection()->executeQuery($sTruncate);
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Decalage du jour de distrib a "date_distrib + $decalDate"
     * @param type $decalDate
     * @throws \Doctrine\DBAL\DBALException
     */
    public function updateDateDistrib($decalDate)
    {
        try {
            $sUpdate = " UPDATE client_a_servir_transfo_tmp SET 
                            date_distrib = DATE_ADD(date_distrib, INTERVAL ".$decalDate." DAY)
                    ";
            $this->_em->getConnection()->executeQuery($sUpdate);
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Suppression des lignes dont date_distrib $comp "J+$nbJourRef" (expl : <= J+1)
     * @param string $nbJourRef
     * @param string $comp
     * @param string $uniteJour
     * @throws \Doctrine\DBAL\DBALException
     */
    public function deleteDate($nbJourRef, $comp, $uniteJour="DAY")
    {
        try {
            $sDelete = " DELETE FROM client_a_servir_transfo_tmp 
                        WHERE
                            date_distrib ".$comp." DATE_ADD(CURDATE(), INTERVAL ".$nbJourRef." ".$uniteJour.")
                    ";
            $this->_em->getConnection()->executeQuery($sDelete);
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    
    // Modification du flux (nuit|jour|...) selon l'historique de distribution de l'abonne
    /**
     * 
     * @param type $societeId       Liste des societe_id de l'historique a recuperer. societe_id a separer par ",". Expl : 3,26 (correspondant a MD et LM)
     * @param type $produitId       Liste des produit_id de l'historique a recuperer. produit_id a separer par ",". Expl : 153,202 (153 -> (N) Elle Decoration ,  202 -> [J] Elle Decoratio)
     * @param type $flux_id         flux_id de l'historique a recuperer
     * @param type $dateMinRef      Date limite MIN de l'historique a recuperer
     * @param string $dateMaxRef    Date limite MAX de l'historique a recuperer
     * @throws \Doctrine\DBAL\DBALException
     */
    public function transfoFluxSelonHisto($societeId='', $produitId='', $flux_id=2, $dateMinRef='', $dateMaxRef='')
    {
        try {
            $dateMinRefDefaut = '2015-02-01';
            $dateMaxRefDefaut = date('Y-m-d');
            $dateMinRef = ( (trim($dateMinRef)!='') ? trim($dateMinRef) : $dateMinRefDefaut ) ;
            $dateMaxRef = ( (trim($dateMaxRef)!='') ? trim($dateMaxRef) : $dateMaxRefDefaut ) ;
            
            // Recuperation des historiques des abonnes de la societe en cours de traitement concernant le flux $flux_id
            
            $sSQLDropTmpTable = " DROP TEMPORARY TABLE IF EXISTS tmp_abos_a_changer_de_flux; ";
            $sSQLCreateTmpTable = " 
                                    CREATE TEMPORARY TABLE tmp_abos_a_changer_de_flux (
                                                                    numabo_ext VARCHAR(50) NOT NULL,
                                                                    flux_id SMALLINT NOT NULL,	
                                                                    PRIMARY KEY (numabo_ext),
                                                                    INDEX t_idx_flux_id (flux_id)
                                                            ); ";
            $this->_em->getConnection()->executeQuery($sSQLDropTmpTable);
            $this->_em->getConnection()->executeQuery($sSQLCreateTmpTable);
            $sInsertTmp = " INSERT INTO tmp_abos_a_changer_de_flux
                                (numabo_ext, flux_id)
                            SELECT
                                numabo_ext, flux_id
                            FROM
                                (
                                SELECT
                                    MAX(csl.date_distrib) AS date_distrib, a.numabo_ext, csl.flux_id AS flux_id
                                FROM
                                    (
                                    SELECT
                                        *
                                    FROM 
                                        client_a_servir_logist csl0
                                    WHERE
                                        1 = 1 ";
            if(trim($societeId)!='')
            {
                $sInsertTmp .= " AND csl0.societe_id IN (".trim($societeId).") ";
            }
            if(trim($produitId)!='')
            {
                $sInsertTmp .= " AND csl0.produit_id IN (".trim($produitId).") ";
            }
            $sInsertTmp .= " 
                                        AND csl0.date_distrib BETWEEN '".$dateMinRef."' AND '".$dateMaxRef."'
                                    ORDER BY csl0.date_distrib DESC
                                    ) csl
                                    INNER JOIN abonne_soc a ON csl.abonne_soc_id = a.id										
                                    INNER JOIN produit p ON csl.produit_id = p.id 
                                    INNER JOIN adresse_rnvp ar ON csl.rnvp_id = ar.id
                                GROUP BY
                                    a.numabo_ext
                                ) t
                            WHERE
                                t.flux_id = ".$flux_id." ";
            
            
            $this->_em->getConnection()->executeQuery($sInsertTmp);
            
            
            // Initialisation des colonnes client_a_servir_transfo_tmp.societe_id_init et client_a_servir_transfo_tmp.produit_id_init
            $sUpdatePrdInit = " UPDATE
                                    client_a_servir_transfo_tmp cas_tmp
                                    INNER JOIN produit p ON cas_tmp.soc_code_ext = p.soc_code_ext AND cas_tmp.prd_code_ext = p.prd_code_ext AND cas_tmp.spr_code_ext = p.spr_code_ext
                                SET
                                    cas_tmp.produit_id_init = p.id
                                    , cas_tmp.societe_id_init = p.societe_id
                                ";
            $this->_em->getConnection()->executeQuery($sUpdatePrdInit);
            
            
            // Initialisation des colonnes client_a_servir_transfo_tmp.produit_id_transfo
            $sUpdatePrdTransfo = " UPDATE
                                    client_a_servir_transfo_tmp cas_tmp
                                    INNER JOIN tmp_abos_a_changer_de_flux t ON cas_tmp.numabo_ext = t.numabo_ext
                                    INNER JOIN produit_transfo_flux ptf ON cas_tmp.produit_id_init = ptf.produit_id_init AND ptf.flux_id = 2
                                SET
                                    cas_tmp.produit_id_transfo = ptf.produit_id_transfo
                                WHERE
                                    cas_tmp.modifie = 0
                                ";
            $this->_em->getConnection()->executeQuery($sUpdatePrdTransfo);
            
            
            // Mise a jour des champs de produits ext. ("soc|prd|spr"_code_ext)
            $sUpdatePrdCodes = " UPDATE
                                    client_a_servir_transfo_tmp cas_tmp
                                    INNER JOIN produit p ON cas_tmp.produit_id_transfo = p.id
                                SET
                                    cas_tmp.soc_code_ext = p.soc_code_ext 
                                    , cas_tmp.prd_code_ext = p.prd_code_ext 
                                    , cas_tmp.spr_code_ext = p.spr_code_ext
                                    , cas_tmp.modifie = 1
                                WHERE
                                    cas_tmp.modifie = 0
                                    AND cas_tmp.produit_id_transfo IS NOT NULL
                                ";
            $this->_em->getConnection()->executeQuery($sUpdatePrdCodes);
            
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    /**
     * Recupere les infos resumant les fichiers a generer depuis le contenu de la table temporaire apres toutes les transformations
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getInfosFicAGenerer()
    {
        try {
            $aRetour    = array();
            $slct = "   SELECT 
                            soc_code_ext
                            , DATE_FORMAT(date_distrib, '%Y/%m/%d') AS date_distrib_Y_m_d
                            , DATE_FORMAT(date_distrib, '%Y%m%d') AS date_distrib_Ymd
                            , DATE_FORMAT(date_distrib, '%d/%m/%Y') AS date_distrib_d_m_Y
                            , COUNT(*) AS nb
                        FROM client_a_servir_transfo_tmp
                        WHERE
                            1 = 1
                        GROUP BY 
                            soc_code_ext, DATE_FORMAT(date_distrib, '%Y%m%d')
                        ";
            $res    = $this->_em->getConnection()->fetchAll($slct);
            foreach($res as $aArr) {
                $aRetour[$aArr['soc_code_ext']][$aArr['date_distrib_Y_m_d']]  = array(
                                                                                    'soc_code_ext'          => $aArr['soc_code_ext']
                                                                                    , 'date_distrib_Y_m_d'  => $aArr['date_distrib_Y_m_d']
                                                                                    , 'date_distrib_Ymd'    => $aArr['date_distrib_Ymd']
                                                                                    , 'date_distrib_d_m_Y'  => $aArr['date_distrib_d_m_Y']
                                                                                    , 'nb'                  => $aArr['nb']
                                                                                    );
            }
            return $aRetour;
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Retourne le nombre de lignes du fichier initial
     * @return integer
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getNbLignesTmp()
    {
        try {
            $nb    = 0;
            $slct = "   SELECT 
                            COUNT(*) AS nb
                        FROM client_a_servir_transfo_tmp
                        WHERE
                            1 = 1
                        ";
            $res    = $this->_em->getConnection()->fetchAll($slct);
            foreach($res as $aArr) {
                $nb = $aArr['nb'];
            }
            return $nb;
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Recupere les donnees de chaque fichier a generer
     * @param string $sSocCodeExt
     * @param string $sDateDistribYmd
     * @return type
     * @throws \Doctrine\DBAL\DBALException
     */
    private function getDonneesFicAGenerer($sSocCodeExt, $sDateDistribYmd)
    {
        try {
            $slct = "   SELECT 
                            id
                            , num_parution, DATE_FORMAT(date_distrib, '%Y/%m/%d') AS date_distrib
                            , numabo_ext, vol1, vol2, vol3, vol4, vol5
                            , cp, ville
                            , type_portage, soc_code_ext, prd_code_ext, spr_code_ext, qte
                            , divers1, info_comp1, info_comp2, divers2
                        FROM client_a_servir_transfo_tmp
                        WHERE
                            date_distrib = '".$sDateDistribYmd."' AND soc_code_ext = '".$sSocCodeExt."'
                        ORDER BY id
                        ";
            return $this->_em->getConnection()->fetchAll($slct);
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    
    /**
     * Nom du fichier a partir du format du nom de fichier. Le format est generalement recupere de la colonne fic_transfo.nom_fic_genere
     * @param type $sFormatNomFicGenere
     * @param type $aInfosFic
     * @return type
     */
    private function setNomFic($sFormatNomFicGenere, $aInfosFic)
    {
        $sRetour    = '';
        if(trim($sFormatNomFicGenere)!='')
        {
            $aAChanger  = array_keys($aInfosFic);
            $aValRplct  = array_values($aInfosFic);
            foreach($aAChanger as $iK => $sV)
            {
                $aAChanger[$iK] = "`".$sV."`";
            }
            $sRetour = str_replace($aAChanger, $aValRplct, $sFormatNomFicGenere);
        }
        return $sRetour;
    }
    
    
    /**
     * Generation des fichiers transformes
     * @param string $sRepTransfoTmp
     * @param string $sFormatNomFicGenere
     * @return int
     */
    public function genereFic($sRepTransfoTmp, $sFormatNomFicGenere='')
    {
        $aFicGeneres    = array();
        
        $aInfosFicAGenerer = $this->getInfosFicAGenerer();
        
        if(!empty($aInfosFicAGenerer))
        {
            foreach($aInfosFicAGenerer as $sSocCodeExt => $aArr0)
            {
                foreach($aArr0 as $sDateDistribV => $aInfosFic)
                {
                    if($aInfosFic['nb']>0)
                    {
                        $sFichierSortie    = $this->setNomFic($sFormatNomFicGenere, $aInfosFic);
                        $sFichierSortie    = (trim($sFichierSortie)=='') ? $sSocCodeExt.$aInfosFic['date_distrib_Ymd'].".txt" : trim($sFichierSortie);
                        
                        if(file_exists($sRepTransfoTmp.'/'.$sFichierSortie))
                        {
                            unlink($sRepTransfoTmp.'/'.$sFichierSortie);
                        }
                        
                        if ($oFichierSortie = fopen($sRepTransfoTmp.'/'.$sFichierSortie,"w+"))
                        {
                            $iI = 0;
                            foreach($this->getDonneesFicAGenerer($sSocCodeExt, $sDateDistribV) as $aA)
                            {
                                $aLigne = array();
                                /*
                            id
                            , num_parution, DATE_FORMAT(date_distrib, '%Y/%m/%d') AS date_distrib
                            , numabo_ext, vol1, vol2, vol3, vol4, vol5
                            , cp, ville
                            , type_portage, soc_code_ext, prd_code_ext, spr_code_ext, qte
                            , divers1, info_comp1, info_comp2, divers2
                                 */
                                $aLigne[]   = $aA['num_parution'];
                                $aLigne[]   = $aA['date_distrib'];
                                $aLigne[]   = $aA['numabo_ext'];
                                $aLigne[]   = $aA['vol1'];
                                $aLigne[]   = $aA['vol2'];
                                $aLigne[]   = $aA['vol3'];
                                $aLigne[]   = $aA['vol4'];
                                $aLigne[]   = $aA['vol5'];
                                $aLigne[]   = $aA['cp'];
                                $aLigne[]   = $aA['ville'];
                                $aLigne[]   = $aA['type_portage'];
                                $aLigne[]   = $aA['soc_code_ext'];
                                $aLigne[]   = $aA['prd_code_ext'];
                                $aLigne[]   = $aA['spr_code_ext'];
                                $aLigne[]   = $aA['qte'];
                                $aLigne[]   = $aA['divers1'];
                                $aLigne[]   = $aA['info_comp1'];
                                $aLigne[]   = $aA['info_comp2'];
                                $aLigne[]   = $aA['divers2'];
                                fwrite($oFichierSortie, utf8_decode(implode('|', $aLigne))."\n");
                                $iI++;
                            }
                            
                            fclose($oFichierSortie);
                            $aFicGeneres[$sFichierSortie]['nbLignes']   = $iI;
                        }
                    }
                }
            }
        }
        
        return $aFicGeneres;
    }
}