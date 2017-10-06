<?php 

namespace Ams\DistributionBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\DBALException;
use Ams\WebserviceBundle\Exception\RnvpLocalException;

class FranceRoutageClientsAServirTmpRepository extends EntityRepository
{
    
    public function getDatesNblignesNbex()
    {
        try {
            $qb = $this->createQueryBuilder('t');
            $qb->select('t.dateDistrib', 't.dateParution', $qb->expr()->count('t').' AS nbLignes', 'SUM(t.qte) AS nbEx')
               ->groupBy('t.dateDistrib', 't.dateParution');
            
            return $qb->getQuery()->getResult();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Checksum du contenu de la table 
     * @return integer
     */
    public function getChecksum()
    {
        try {
            $iCheckSum	= 0;
            $sCheckSum	= "CHECKSUM TABLE france_routage_c_a_s_tmp";
            $aCheksum   = $this->_em->getConnection()->executeQuery($sCheckSum)->fetchAll();
            foreach($aCheksum as $aArr)
            {
                    $iCheckSum	= $aArr['Checksum'];
            }
            return $iCheckSum;
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Selection des "code societe exterieur"
     * @return type
     */
    public function getCodeSocExt()
    {
        try {
            return $this->createQueryBuilder('t')
                        ->select('DISTINCT t.socCodeExt')
                        ->getQuery()->getResult();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Selection "societe id"
     * @return type
     */
    public function getSocieteId()
    {
        try {
            $select = "SELECT DISTINCT societe_id AS SOCIETE_ID FROM france_routage_c_a_s_tmp ";
            return $this->_em->getConnection()->fetchAll($select);
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Verifie si la societe est inconnue
     * @return type
     */
    public function isSocieteInconnue()
    {
        try {
            $select = "SELECT COUNT(*) AS NB FROM france_routage_c_a_s_tmp WHERE societe_id IS NULL";
            $aRes = $this->_em->getConnection()->fetchAll($select);
            foreach($aRes as $aArr)
            {
                return ($aArr['NB']>0)? true : false;
            }
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    
    /**
     * Marquer des abonnes hors Ile de France
     * 
     * @param string $sDepartementIdf
     * @throws \Doctrine\DBAL\DBALException
     */
    public function marqueAbosHorsIDF($sDepartementIdf)
    {
        try {
            $aDepartements  = explode(",", $sDepartementIdf);
            $aWhere = array();
            foreach($aDepartements as $sDep)
            {
                $aWhere[]   = " cp_ext NOT LIKE '".$sDep."%' ";
            }
            $this->_em->getConnection()
                    ->executeQuery("UPDATE france_routage_c_a_s_tmp SET type_probl = 'HORS_IDF' WHERE (".implode(' AND ', $aWhere).") ")
                    ;
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    
    /**
     * Suppression des abonnes hors Ile de France
     * 
     * @param string $sDepartementIdf
     * @throws \Doctrine\DBAL\DBALException
     */
    public function supprAbosHorsIDF($sDepartementIdf)
    {
        try {
            $aDepartements  = explode(",", $sDepartementIdf);
            $aWhere = array();
            foreach($aDepartements as $sDep)
            {
                $aWhere[]   = " cp_ext NOT LIKE '".$sDep."%' ";
            }
            $this->_em->getConnection()
                    ->executeQuery("DELETE FROM france_routage_c_a_s_tmp WHERE (".implode(' AND ', $aWhere).") ")
                    ;
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Mise a jour des attributs sousProduit & produit & societe si le sous produit est parametre
     * @throws \Doctrine\DBAL\DBALException
     */
    public function updateInfosProduit()
    {
        try {
            $this->_em->getConnection()
                    ->executeQuery("UPDATE france_routage_c_a_s_tmp AS t, produit AS p, societe s SET t.produit_id=p.id, t.societe_id=s.id WHERE t.soc_code_ext = p.soc_code_ext AND t.prd_code_ext = p.prd_code_ext AND t.spr_code_ext = p.spr_code_ext AND p.societe_id=s.id")
                    ;
            $this->_em->clear();
            
            // Flag KO des lignes dont le produit est inconnu
            $this->_em->getConnection()
                    ->executeQuery(" UPDATE france_routage_c_a_s_tmp SET type_probl = 'PRD_INCONNU' WHERE (produit_id IS NULL OR societe_id IS NULL) AND type_probl IS NULL ")
                    ;
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Definit le type de client (abonne ou LV)
     * 
     * @param integer $clientType
     * @throws \Doctrine\DBAL\DBALException
     */
    public function updateClientType($clientType=0)
    {
        try {
            $this->_em->getConnection()
                    ->executeQuery("UPDATE
                                            france_routage_c_a_s_tmp 
                                    SET client_type = ".$clientType." ")
                    ;
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Par defaut, mettre a 1 la colonne "adr_ok" 
     * 
     * @param integer $qteParDefaut
     * @throws \Doctrine\DBAL\DBALException
     */
    public function initFlag()
    {
        try {
            $this->_em->getConnection()
                    ->executeQuery("UPDATE france_routage_c_a_s_tmp SET adr_ok = 0, chgt_adr = 0, livrable = 0 ");
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Mise a jour quantite si pas definie
     * 
     * @param integer $qteParDefaut
     * @throws \Doctrine\DBAL\DBALException
     */
    public function updateQuantite($qteParDefaut=1)
    {
        try {
            $this->_em->getConnection()
                    ->executeQuery("UPDATE
                                            france_routage_c_a_s_tmp 
                                    SET qte = ".$qteParDefaut." 
                                    WHERE 
                                        qte IS NULL ")
                    ;
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Suppression des "\r" et "\n" pour tous les champs
     * @throws \Doctrine\DBAL\DBALException
     */
    public function supprCaracteresSpeciaux()
    {
        try {
            $update = " UPDATE france_routage_c_a_s_tmp 
                        SET 
                            num_parution = REGEX_REPLACE_1('[\r\n]', '', TRIM(num_parution))
                            , numabo_ext = REGEX_REPLACE_1('[\r\n]', '', TRIM(numabo_ext))
                            , vol1_ext = REGEX_REPLACE_1('[\r\n]', '', TRIM(vol1_ext))
                            , vol2_ext = REGEX_REPLACE_1('[\r\n]', '', TRIM(vol2_ext))
                            , vol3_ext = REGEX_REPLACE_1('[\r\n]', '', TRIM(vol3_ext))
                            , vol4_ext = REGEX_REPLACE_1('[\r\n]', '', TRIM(vol4_ext))
                            , vol5_ext = REGEX_REPLACE_1('[\r\n]', '', TRIM(vol5_ext))
                            , cp_ext = REGEX_REPLACE_1('[\r\n]', '', TRIM(cp_ext))
                            , ville_ext = REGEX_REPLACE_1('[\r\n]', '', TRIM(ville_ext))
                            , type_portage = REGEX_REPLACE_1('[\r\n]', '', TRIM(type_portage))
                            , qte = REGEX_REPLACE_1('[\r\n]', '', TRIM(qte))
                            , divers1 = REGEX_REPLACE_1('[\r\n]', '', TRIM(divers1))
                            , info_comp1 = REGEX_REPLACE_1('[\r\n]', '', TRIM(info_comp1))
                            , info_comp2 = REGEX_REPLACE_1('[\r\n]', '', TRIM(info_comp2))
                            , divers2 = REGEX_REPLACE_1('[\r\n]', '', TRIM(divers2))
                            , soc_code_ext = REGEX_REPLACE_1('[\r\n]', '', TRIM(soc_code_ext))
                            , prd_code_ext = REGEX_REPLACE_1('[\r\n]', '', TRIM(prd_code_ext))
                            , spr_code_ext = REGEX_REPLACE_1('[\r\n]', '', TRIM(spr_code_ext))
                        ";
            $this->_em->getConnection()->executeQuery($update);
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        } 
    }
    
    /**
     * Mise en majuscule des champs adresse
     */
    public function miseEnMajusculeAdresses()
    {
        try {
            $this->createQueryBuilder('t')->update()
                    ->set('t.vol1Ext', 'UPPER(t.vol1Ext)')
                    ->set('t.vol2Ext', 'UPPER(t.vol2Ext)')
                    ->set('t.vol3Ext', 'UPPER(t.vol3Ext)')
                    ->set('t.vol4Ext', 'UPPER(t.vol4Ext)')
                    ->set('t.vol5Ext', 'UPPER(t.vol5Ext)')
                    ->set('t.cpExt', 'UPPER(t.cpExt)')
                    ->set('t.villeExt', 'UPPER(t.villeExt)')
                    ->getQuery()->execute();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Pour les adresses connues, mise a jour des champs adresses rnvp
     * @throws \Doctrine\DBAL\DBALException
     */
    public function miseAJourAdrConnue()
    {
        try {
            $update = " UPDATE 
                            france_routage_c_a_s_tmp t
                            INNER JOIN france_routage_adresse a ON t.vol1_ext=a.vol1_ext AND t.vol2_ext=a.vol2_ext AND t.vol3_ext=a.vol3_ext AND t.vol4_ext=a.vol4_ext AND t.vol5_ext=a.vol5_ext AND t.cp_ext=a.cp_ext AND t.ville_ext=a.ville_ext 
                        SET 
                            t.rnvp_vol1 = a.rnvp_vol1
                            , t.rnvp_vol2 = a.rnvp_vol2
                            , t.rnvp_vol3 = a.rnvp_vol3
                            , t.rnvp_vol4 = a.rnvp_vol4
                            , t.rnvp_vol5 = a.rnvp_vol5
                            , t.rnvp_cp = a.rnvp_cp
                            , t.rnvp_ville = a.rnvp_ville
                            , t.rnvp_insee = a.rnvp_insee
                            , t.adr_ok = 1
                     ";
            $this->_em->getConnection()->executeQuery($update);
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Pour les adresses connues, mise a jour des champs adresses rnvp
     * @throws \Doctrine\DBAL\DBALException
     */
    public function rnvpAutresAdresses($srvRnvp)
    {
        try {
            $sSlct  = " SELECT 
                            id, vol1_ext, vol2_ext, vol3_ext, vol4_ext, vol5_ext, cp_ext, ville_ext
                        FROM
                            france_routage_c_a_s_tmp 
                        WHERE
                            adr_ok = 0 AND type_probl IS NULL
                        ";
            $res    = $this->_em->getConnection()->fetchAll($sSlct);
            foreach($res as $aArr) {
                $aAdrRnvp   = array();
                // On met en :
                //    - volet 1 pour RNVP : volet 1 
                //    - volet 2 pour RNVP : volet 2 
                //    - volet 3 pour RNVP : volet 3 + volet 4 
                //    - volet 4 pour RNVP : volet 5 
                //    - volet 5 pour RNVP : rien 
                
                
                // Dans un premier temps, a l'entree de l'appel du service de RNVP, on classe les volets 1 a 5 :
                // Dans le cas ou 1, 2 ou 5 volets sont renseignes, il n'y a pas de souci.
                // Dans les autres cas (3 ou4 volets renseignes)
                //      . Le premier champs trouve est considere comme le "volet 1"
                //      . Les restes sont a concatener dans le "volet 4"
                
                
                $aVolAdresseTmp   = array();
                if(trim($aArr['vol1_ext'])!="")
                {
                    $aVolAdresseTmp[]  = trim($aArr['vol1_ext']);
                }
                if(trim($aArr['vol2_ext'])!="")
                {
                    $aVolAdresseTmp[]  = trim($aArr['vol2_ext']);
                }
                if(trim($aArr['vol3_ext'])!="")
                {
                    $aVolAdresseTmp[]  = trim($aArr['vol3_ext']);
                }
                if(trim($aArr['vol4_ext'])!="")
                {
                    $aVolAdresseTmp[]  = trim($aArr['vol4_ext']);
                }
                if(trim($aArr['vol5_ext'])!="")
                {
                    $aVolAdresseTmp[]  = trim($aArr['vol5_ext']);
                }
                
                $iNbVolAdresseTmp   = count($aVolAdresseTmp);
                                
                if($iNbVolAdresseTmp >0)
                {
                    /*$aAdrRnvp = array(  "volet1" 	=> $aArr['vol1_ext'],
                                        "volet2" 	=> $aArr['vol2_ext'],
                                        "volet3" 	=> trim(implode(" ", array($aArr['vol3_ext'], $aArr['vol4_ext']))),
                                        "volet4" 	=> $aArr['vol5_ext'],
                                        "volet5" 	=> "",
                                        "cp" 	=> $aArr['cp_ext'],
                                        "ville" 	=> $aArr['ville_ext']
                                        );*/
                    $regexAdresse = "/^[0-9]+\s/"; // On considere comme adresse si commence par numerique, puis "espace"
                    switch($iNbVolAdresseTmp)
                    {
                        case 1:
                            $aAdrRnvp['volet1']  = '';
                            $aAdrRnvp['volet2']  = '';
                            $aAdrRnvp['volet3']  = '';
                            $aAdrRnvp['volet4']  = $aVolAdresseTmp[0];
                            $aAdrRnvp['volet5']  = '';
                            break;
                        
                        case 2:
                            $aAdrRnvp['volet1']  = $aVolAdresseTmp[0];
                            $aAdrRnvp['volet2']  = '';
                            $aAdrRnvp['volet3']  = '';
                            $aAdrRnvp['volet4']  = $aVolAdresseTmp[1];
                            $aAdrRnvp['volet5']  = '';
                            break;
                        
                        case 3:
                            // 0-1-2
                            $aAdrRnvp['volet1']  = $aVolAdresseTmp[0];
                            $aAdrRnvp['volet2']  = '';
                            
                            if(preg_match($regexAdresse, $aVolAdresseTmp[2]))
                            {
                                $aAdrRnvp['volet3']  = $aVolAdresseTmp[1];
                                $aAdrRnvp['volet4']  = $aVolAdresseTmp[2];
                                $aAdrRnvp['volet5']  = '';
                            }
                            elseif(preg_match($regexAdresse, $aVolAdresseTmp[1]))
                            {
                                $aAdrRnvp['volet3']  = '';
                                $aAdrRnvp['volet4']  = $aVolAdresseTmp[1];
                                $aAdrRnvp['volet5']  = $aVolAdresseTmp[2];
                            }
                            else
                            {
                                $aAdrRnvp['volet3']  = $aVolAdresseTmp[1];
                                $aAdrRnvp['volet4']  = $aVolAdresseTmp[2];
                                $aAdrRnvp['volet5']  = '';
                            }
                            
                            break;
                        
                        case 4:
                            // 0-1-2-3
                            $aAdrRnvp['volet1']  = $aVolAdresseTmp[0];
                            $aAdrRnvp['volet2']  = '';
                            
                            if(preg_match($regexAdresse, $aVolAdresseTmp[3]))
                            {
                                $aAdrRnvp['volet3']  = $aVolAdresseTmp[1].' '.$aVolAdresseTmp[2];
                                $aAdrRnvp['volet4']  = $aVolAdresseTmp[3];
                                $aAdrRnvp['volet5']  = '';
                            }
                            elseif(preg_match($regexAdresse, $aVolAdresseTmp[2]))
                            {
                                $aAdrRnvp['volet3']  = $aVolAdresseTmp[1];
                                $aAdrRnvp['volet4']  = $aVolAdresseTmp[2];
                                $aAdrRnvp['volet5']  = $aVolAdresseTmp[3];
                            } 
                            else
                            {
                                $aAdrRnvp['volet3']  = $aVolAdresseTmp[1].' '.$aVolAdresseTmp[2];
                                $aAdrRnvp['volet4']  = $aVolAdresseTmp[3];
                                $aAdrRnvp['volet5']  = '';
                            }
                            
                            break;
                        
                        case 5:
                            $aAdrRnvp['volet1']  = $aVolAdresseTmp[0];
                            $aAdrRnvp['volet2']  = $aVolAdresseTmp[1];
                            $aAdrRnvp['volet3']  = $aVolAdresseTmp[2];
                            $aAdrRnvp['volet4']  = $aVolAdresseTmp[3];
                            $aAdrRnvp['volet5']  = $aVolAdresseTmp[4];
                            break;
                    }
                    
                    $aAdrRnvp['cp']     = $aArr['cp_ext'];
                    $aAdrRnvp['ville']  = $aArr['ville_ext'];
                
                    $oResRNVP = $srvRnvp->normalise($aAdrRnvp);
                    if($oResRNVP!==false && $oResRNVP->Elfyweb_RNVP_ExpertResult == 0)
                    {
                        $aResRNVP   = array();
                        $aVol1RnvpTmp   = array();
                        $sVol1Rnvp   = "";
                        $sVol2Rnvp   = "";
                        $sVol3Rnvp   = "";
                        if($oResRNVP->pio_civ) $aVol1RnvpTmp[] = $oResRNVP->pio_civ;
                        if($oResRNVP->pio_nom) $aVol1RnvpTmp[] = $oResRNVP->pio_nom;
                        if($oResRNVP->pio_prenom) $aVol1RnvpTmp[] = $oResRNVP->pio_prenom;
                        if(!empty($aVol1RnvpTmp)) $sVol1Rnvp   = implode(" ", $aVol1RnvpTmp);
                        if($oResRNVP->pio_cnom) $sVol2Rnvp   = $oResRNVP->pio_cnom;
                        if($oResRNVP->pio_cadrs) $sVol3Rnvp   = $oResRNVP->pio_cadrs;

                        if($sVol1Rnvp=="" && $sVol2Rnvp=="" && $sVol3Rnvp!="")
                        {
                            $sVol1Rnvp   = $sVol3Rnvp;
                            $sVol2Rnvp   = "";
                            $sVol3Rnvp   = "";
                        }
                        else if($sVol1Rnvp=="" && $sVol2Rnvp!="" && $sVol3Rnvp!="")
                        {
                            $sVol1Rnvp   = $sVol2Rnvp;
                            $sVol2Rnvp   = "";
                            $sVol3Rnvp   = $sVol3Rnvp;
                        }
                        $aResRNVP["id"]   = $aArr['id'];
                        $aResRNVP["vol1"]   = strtoupper($sVol1Rnvp);
                        $aResRNVP["vol2"]   = strtoupper($sVol2Rnvp);
                        $aResRNVP["vol3"]   = strtoupper($sVol3Rnvp);
                        $aResRNVP["vol4"]   = strtoupper($oResRNVP->pio_adresse);
                        $aResRNVP["vol5"]   = strtoupper($oResRNVP->pio_lieudit);
                        $aResRNVP["cp"]     = $oResRNVP->po_cp;
                        $aResRNVP["ville"]   = strtoupper($oResRNVP->po_ville);
                        $aResRNVP["insee"]   = $oResRNVP->po_insee;

                        if(in_array($oResRNVP->etatRetourRnvp, array('RNVP_OK', 'RNVP_INFO_VILLE_VOIE_INCOMPLET'))
                                || ($oResRNVP->etatRetourRnvp == 'RNVP_AVEC_RISQUE' && $oResRNVP->po_cqadrs <=2)
                                )
                        {
                            $this->createQueryBuilder('t')->update()
                                ->set('t.rnvpVol1', ':vol1')
                                ->set('t.rnvpVol2', ':vol2')
                                ->set('t.rnvpVol3', ':vol3')
                                ->set('t.rnvpVol4', ':vol4')
                                ->set('t.rnvpVol5', ':vol5')
                                ->set('t.rnvpCp', ':cp')
                                ->set('t.rnvpVille', ':ville')
                                ->set('t.rnvpInsee', ':insee')
                                ->set('t.adrOk', 1)
                                ->where('t.id = :id')
                                ->setParameters($aResRNVP)
                                ->getQuery()->execute();
                        }
                        else
                        {
                            $aResRNVP["type_probl"]   = 'ERR_RNVP'.'_'.$oResRNVP->etatRetourRnvp;
                            $this->createQueryBuilder('t')->update()
                                ->set('t.rnvpVol1', ':vol1')
                                ->set('t.rnvpVol2', ':vol2')
                                ->set('t.rnvpVol3', ':vol3')
                                ->set('t.rnvpVol4', ':vol4')
                                ->set('t.rnvpVol5', ':vol5')
                                ->set('t.rnvpCp', ':cp')
                                ->set('t.rnvpVille', ':ville')
                                ->set('t.rnvpInsee', ':insee')
                                ->set('t.typeProbl', ':type_probl')
                                ->where('t.id = :id')
                                ->setParameters($aResRNVP)
                                ->getQuery()->execute();
                        }
                    }
                    else
                    {
                        trigger_error("Webservice non passe pour nom : ".$aArr["volet1"]." - cplt nom : ".$aArr["volet2"]." - cplt adr : ".$aArr["volet3"]." - adr : ".$aArr["volet4"]." - lieu dit : ".$aArr["volet5"]." - cp : ".$aArr["cp"]." - ville : ".$aArr["ville"], E_USER_WARNING);
                    }
                }
            }
            
            // Si on arrete la, on ne sert pas les abonnes dont l'adresse n'est pas normalisee meme si l'abonne est deja connu 
            
            
        } 
        //catch (RnvpLocalException $rnvpLocalException) {
        //    throw $rnvpLocalException;
        //}
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    
    
    
    
    
    
    
    
    /**
     * Mise a jour du champ jour_id
     * @throws \Doctrine\DBAL\DBALException
     */
    public function updateJourId()
    {
        try {
            $update = " UPDATE france_routage_c_a_s_tmp 
                        SET 
                            jour_id = CAST(DATE_FORMAT(date_distrib, '%w') AS SIGNED)+1
                        ";
            $this->_em->getConnection()->executeQuery($update);
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        } 
    }

    /**
     * Mise a jour de l'attribut AbonneSoc
     * @throws \Doctrine\DBAL\DBALException
     */
    private function updateAbonneSoc($clientType=0) {
        try {
            // Si abonne
            if($clientType==0) {
                $this->_em->getConnection()
                        ->executeQuery("UPDATE
                                            france_routage_c_a_s_tmp AS t
                                            LEFT JOIN abonne_soc AS a ON t.soc_code_ext=a.soc_code_ext AND t.numabo_ext=a.numabo_ext AND a.client_type = ".$clientType." 
                                        SET t.abonne_soc_id=a.id
                                        WHERE t.abonne_soc_id IS NULL AND a.id IS NOT NULL ")
                ;
                $this->_em->clear();
            }
            // Si lieu de vente
            else {
                $this->_em->getConnection()
                        ->executeQuery("UPDATE
                                                france_routage_c_a_s_tmp AS t
                                                LEFT JOIN abonne_soc AS a ON t.numabo_ext=a.numabo_ext AND a.client_type = ".$clientType." 
                                        SET t.abonne_soc_id=a.id
                                        WHERE t.abonne_soc_id IS NULL AND a.id IS NOT NULL ")
                ;
                $this->_em->clear();
            }            
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

    /**
     * Insertion des nouveaux AbonneSoc
     */
    public function insertNouveauAbonneSoc($clientType=0) {
        try {
            $sInsert    = " INSERT INTO abonne_soc (numabo_ext, soc_code_ext, client_type, vol1, vol2) SELECT DISTINCT numabo_ext, '', ".$clientType.", rnvp_vol1, rnvp_vol2 FROM france_routage_c_a_s_tmp WHERE abonne_soc_id IS NULL AND livrable = 1 ";
            if($clientType==0)
            {
                $sInsert    = " INSERT INTO abonne_soc (numabo_ext, soc_code_ext, client_type, vol1, vol2, societe_id) SELECT DISTINCT numabo_ext, soc_code_ext, ".$clientType.", rnvp_vol1, rnvp_vol2, societe_id FROM france_routage_c_a_s_tmp WHERE abonne_soc_id IS NULL AND societe_id IS NOT NULL AND livrable = 1 ";
            }
            $this->_em->getConnection()
                    ->executeQuery($sInsert);
            $this->_em->clear();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Mise a jour des tournees des abonnes connus et deja classes
     * @param string $quelleAdr
     * @throws \Doctrine\DBAL\DBALException
     */
    private function updateTourneeAbosConnus($quelleAdr = 'toute')
    {
        try {
            $update = " UPDATE france_routage_c_a_s_tmp t
                                , tournee_detail td
                        SET 
                            t.modele_tournee_jour_code = td.modele_tournee_jour_code
                            , t.ordre = td.ordre
                            , t.livrable = 1
                        WHERE
                            t.abonne_soc_id = td.num_abonne_id
                            AND t.jour_id = td.jour_id
                            AND t.modele_tournee_jour_code IS NULL
                        ";
            if($quelleAdr=='sans_chgt_adr')
            {
                $update .= " AND t.chgt_adr <> 1 ";
            }
            $this->_em->getConnection()->executeQuery($update);
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        } 
    }
    
    /**
     * Mise a jour des champs produits selon le flux
     * @throws \Doctrine\DBAL\DBALException
     */
    private function transfoProduitByFlux()
    {
        try {
            $update = " UPDATE
                            france_routage_c_a_s_tmp t
                            INNER JOIN ref_flux f ON f.code = SUBSTR(t.modele_tournee_jour_code, 4, 1)
                            INNER JOIN produit_transfo_flux ptf ON t.produit_id = ptf.produit_id_init AND f.id = ptf.flux_id
                            INNER JOIN produit p ON ptf.produit_id_transfo = p.id
                        SET
                            t.produit_id = p.id
                            , t.soc_code_ext = p.soc_code_ext
                            , t.prd_code_ext = p.prd_code_ext
                            , t.spr_code_ext = p.spr_code_ext
                        ";
            $this->_em->getConnection()->executeQuery($update);
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        } 
    }
    
    /**
     * Mise a jour du champ depot_id selon le champ modele_tournee_jour_code
     * @throws \Doctrine\DBAL\DBALException
     */
    private function transfoDepotByTournee()
    {
        try {
            $update = " UPDATE
                            france_routage_c_a_s_tmp t
                            INNER JOIN depot d ON d.code = SUBSTR(t.modele_tournee_jour_code, 1, 3) AND (CURDATE() BETWEEN d.date_debut AND d.date_fin OR d.date_fin IS NULL)
                        SET
                            t.depot_id = d.id
                        WHERE
                            (t.depot_id IS NULL OR t.depot_id <> d.id)
                            AND t.modele_tournee_jour_code IS NOT NULL
                        ";
            $this->_em->getConnection()->executeQuery($update);
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        } 
    }
    
    /**
     * Mise a jour des pts de livraisons des adresses qui ne sont pas encore classees
     * @throws \Doctrine\DBAL\DBALException
     */
    private function updatePtLivraisonPourNonClasses()
    {
        try {
            $update = " UPDATE france_routage_c_a_s_tmp t
                            LEFT JOIN adresse_rnvp ar ON t.rnvp_vol4 = ar.adresse AND t.rnvp_insee = ar.insee AND ar.stop_livraison_possible='1'
                        SET
                            t.point_livraison_id = ar.id
                        WHERE
                            t.modele_tournee_jour_code IS NULL
                            AND ar.id IS NOT NULL ";
            $this->_em->getConnection()->prepare($update)->execute();
        }
        catch (DBALException $DBALException) {
            throw $DBALException;
        } 
    }
    
    /**
     * Les points a classer
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    private function ptsAClasser()
    {
        try {
            $sSlctPtsAClasser  = "  SELECT
                                        DATE_FORMAT(t.date_distrib, '%Y/%m/%d') AS jour, DATE_FORMAT(t.date_distrib, '%w')+1 AS id_jour, t.rnvp_insee AS insee, t.abonne_soc_id, ar.geox, ar.geoy, t.point_livraison_id, p.flux_id, t.numabo_ext, t.soc_code_ext, t.prd_code_ext, t.depot_id
                                    FROM
                                        france_routage_c_a_s_tmp t
                                        LEFT JOIN produit p ON t.produit_id = p.id
                                        LEFT JOIN adresse_rnvp ar ON t.point_livraison_id = ar.id
                                    WHERE
                                        t.modele_tournee_jour_code IS NULL
                                        AND t.depot_id IS NOT NULL
                                        AND ar.id IS NOT NULL
                                        AND p.id IS NOT NULL
                                        AND ar.geox IS NOT NULL AND ar.geoy IS NOT NULL
                                        AND (t.type_probl IS NULL OR t.type_probl NOT LIKE 'ERR_RNVP%')
                                        AND t.abonne_soc_id IS NOT NULL
                                     ";
            return $this->_em->getConnection()->executeQuery($sSlctPtsAClasser)->fetchAll();
        }
        catch (DBALException $DBALException) {
            throw $DBALException;
        } 
    }
    
    /**
     * Mise a jour du champ depot 
     * @throws \Doctrine\DBAL\DBALException
     */
    private function updateDepot()
    {
        try {
            $update = " UPDATE france_routage_c_a_s_tmp t
                            INNER JOIN commune c ON t.rnvp_insee=c.insee
                            INNER JOIN produit p ON t.produit_id = p.id 
                            INNER JOIN france_routage_commune frc ON c.id = frc.commune_id 
                                                                        AND t.societe_id = frc.societe_id AND p.flux_id = frc.flux_id
                                                                        AND t.date_distrib BETWEEN frc.date_debut AND frc.date_fin
                            INNER JOIN depot_commune dc ON dc.commune_id=c.id AND t.date_distrib BETWEEN dc.date_debut AND dc.date_fin 
                        SET
                            t.commune_id=c.id
                            , t.depot_id=dc.depot_id
                        WHERE
                            dc.commune_id IS NOT NULL ";
            $this->_em->getConnection()->prepare($update)->execute();
        }
        catch (DBALException $DBALException) {
            throw $DBALException;
        } 
    }
    
    /**
     * Mise a jour de tout changement d'adresse
     * @throws \Doctrine\DBAL\DBALException
     */
    private function updateChgtAdr()
    {
        try {
            // Marquage de changement d'adresse
            $update = " UPDATE
                            france_routage_c_a_s_tmp t
                            INNER JOIN adresse a ON t.abonne_soc_id = a.abonne_soc_id AND t.date_distrib BETWEEN a.date_debut AND a.date_fin AND (a.type_adresse IS NULL OR a.type_adresse = 'L')
                            INNER JOIN adresse_rnvp ar ON a.rnvp_id = ar.id
                        SET
                            t.chgt_adr = 1
                        WHERE
                            t.abonne_soc_id IS NOT NULL
                            AND (t.rnvp_vol4 <> ar.adresse OR t.rnvp_insee <>insee)
                            AND (type_probl IS NULL OR (type_probl NOT LIKE 'ERR_RNVP%' AND type_probl NOT IN ('HORS_IDF','PRD_INCONNU')))
                        ";
            $this->_em->getConnection()->executeQuery($update);
            
            // jour_id 
            $iJourId    = 0;
            $sSlctJourId    = " SELECT DISTINCT jour_id FROM france_routage_c_a_s_tmp WHERE chgt_adr = 1 ";
            $res    = $this->_em->getConnection()->fetchAll($sSlctJourId);
            foreach($res as $aArr) {
                $iJourId = $aArr['jour_id'];
            }
            
            // Abonnes qui ont change
            $aAbosChangeAdr = $this->abosChangeAdr();
            
            // Suppression dans tournee_detail des lignes des abos qui ont change d'adresse
            if(!empty($aAbosChangeAdr))
            {
                $delete = " DELETE FROM tournee_detail WHERE jour_id = $iJourId AND num_abonne_id IN (".implode(", ", $aAbosChangeAdr).") ";
                $this->_em->getConnection()->executeQuery($delete);
            }            
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        } 
    }
    
    /**
     * Liste des abonne_soc_id qui ont change d'adresse
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    private function abosChangeAdr()
    {
        try {
            $aRetour    = array();
            $sSlct  = " SELECT DISTINCT abonne_soc_id FROM france_routage_c_a_s_tmp WHERE chgt_adr = 1 ";
            $res    = $this->_em->getConnection()->fetchAll($sSlct);
            foreach($res as $aArr) {
                $aRetour[] = $aArr['abonne_soc_id'];
            }
            return $aRetour;
        } catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

    /**
     * Verification des adresses livrables
     * Livrables == abonnes ou adresses connus
     * 
     * @param integer $clientType
     * @param service $srvRnvp
     * @param service $srvAmsCartoGeoservice
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Ams\WebserviceBundle\Exception\RnvpLocalException
     */
    public function verifAdresssesLivrables($clientType=0, $srvRnvp, $srvAmsCartoGeoservice) {
        try {
            // Mise a jour de l'attribut AbonneSoc [abonnes deja connus]
            $this->updateAbonneSoc($clientType);
            
            // Mise a jour de tout changement d'adresse
            $this->updateChgtAdr();
            
            // Mise a jour des tournees des abonnes connus, deja classes le jour de la date_distrib courant et qui n'a pas change d'adresse
            $this->updateTourneeAbosConnus('sans_chgt_adr');
            
            // Mise a jour des pts de livraisons des adresses qui ne sont pas encore classees
            $this->updatePtLivraisonPourNonClasses();
            
            // Mise a jour du champ depot 
            $this->updateDepot();
            
            // Pour les abonnes connus, classement de ceux qui ne sont pas encore classes le jour de la date_distrib courant
            $aPointsAClasser    = $this->ptsAClasser();
            
            $srvAmsCartoGeoservice->classementAuto($aPointsAClasser);
            
            // Mise a jour des tournees des abonnes connus, qui vient d'etre classes le jour de la date_distrib courant 
            $this->updateTourneeAbosConnus();
            
            // Pour le reste ....
            // Pour chaque ligne dont on ne connait si c'est livrable ou pas, on change le point de livraison en pt deja classe
            $sSlct  = " SELECT 
                            t.id, MAX(ar.id) AS rnvp_id, count(*) AS nb 
                        FROM 
                            france_routage_c_a_s_tmp t
                            INNER JOIN adresse_rnvp ar ON t.rnvp_vol4 = ar.adresse AND t.rnvp_insee = ar.insee
                            INNER JOIN adresse a ON ar.id = a.rnvp_id
                            INNER JOIN tournee_detail td ON a.abonne_soc_id = td.num_abonne_id AND t.date_distrib BETWEEN a.date_debut AND a.date_fin AND (a.type_adresse IS NULL OR a.type_adresse = 'L') AND CAST(DATE_FORMAT(CURRENT_DATE(), '%w') AS SIGNED)+1 = td.jour_id
                        WHERE
                            t.livrable = 0 
                            AND t.adr_ok = 1
                        GROUP BY 
                            t.id
                        HAVING nb > 0
                        ";
            $res    = $this->_em->getConnection()->fetchAll($sSlct);
            foreach($res as $aArr) {
                $update = " UPDATE france_routage_c_a_s_tmp SET point_livraison_id = ".$aArr['rnvp_id']." WHERE id = ".$aArr['id']." ";
                $this->_em->getConnection()->prepare($update)->execute();
            }
            
            // Enregistrement des nouveaux abonnes livrables
            $this->insertNouveauAbonneSoc($clientType);
            
            // Mise a jour de l'attribut AbonneSoc [abonnes deja connus]
            $this->updateAbonneSoc($clientType);
            
            // Classement des points qui ne sont pas encore dans une tournee
            $aPointsAClasser    = $this->ptsAClasser();
            echo "\nOn compte ".count($aPointsAClasser)." nouveaux points a classer\n";
            $srvAmsCartoGeoservice->classementAuto($aPointsAClasser);
            
            // Mise a jour des tournees des abonnes connus et deja classes
            $this->updateTourneeAbosConnus();
            
            // Mise a jour des champs produits selon le flux
            $this->transfoProduitByFlux();
            
            // Mise a jour du champ depot_id selon le champ modele_tournee_jour_code
            $this->transfoDepotByTournee();
            
            
            // Mise a jour des pts de livraisons des adresses qui ne sont pas encore classees
            //$this->updatePtLivraisonPourNonClasses();
            
            // Mise a jour du champ depot des adresses qui ne sont pas encore classees
            //$update = " UPDATE france_routage_c_a_s_tmp t
            //                LEFT JOIN commune c ON t.rnvp_insee=c.insee
            //                LEFT JOIN depot_commune dc ON dc.commune_id=c.id AND t.date_distrib BETWEEN dc.date_debut AND dc.date_fin 
            //            SET
            //                t.commune_id=c.id
            //                , t.depot_id=dc.depot_id
            //            WHERE
            //                dc.commune_id IS NOT NULL ";
            //$this->_em->getConnection()->prepare($update)->execute();
            
            
                        
            // Supprimer les lignes d'abonnes qui ne sont pas livrables
            //$delete = " DELETE FROM france_routage_c_a_s_tmp WHERE modele_tournee_jour_code IS NULL ";
            //$this->_em->getConnection()->prepare($delete)->execute();
            
        } 
        catch (RnvpLocalException $rnvpLocalException) {
            throw $rnvpLocalException;
        }
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    } 
    
    /**
     * Mise a jour des champs infos portage
     * @throws \Doctrine\DBAL\DBALException
     */
    public function updateInfosPortage()
    {
        try {
            $aCorrespondanceChps    = array();
            $aCorrespondanceChps['DIVERS1']['col']  = 'divers1'; /*Divers - 40*/
            $aCorrespondanceChps['DIVERS1']['long']  = 40; 
            
            $aCorrespondanceChps['INFO_COMP1']['col']  = 'info_comp1'; /*Digicode - 120*/
            $aCorrespondanceChps['INFO_COMP1']['long']  = 120; 
            
            $aCorrespondanceChps['INFO_COMP2']['col']  = 'info_comp2'; /*Consigne portage - 32*/
            $aCorrespondanceChps['INFO_COMP2']['long']  = 32; 
            
            $aCorrespondanceChps['DIVERS2']['col']  = 'divers2'; /*Message pour le porteur - 32*/
            $aCorrespondanceChps['DIVERS2']['long']  = 32;
            
            $aUpdate    = array();
            $aUpdateTmp    = array();
            // Infos portage Abonnes
            $sSlct  = " SELECT
                            t.id, tip.id AS tip_id, tip.code, ip.valeur
                        FROM
                            france_routage_c_a_s_tmp t
                            INNER JOIN infos_portages_abonnes ipa ON t.abonne_soc_id = ipa.abonne_id
                            INNER JOIN info_portage ip ON ipa.info_portage_id = ip.id  AND ip.active = 1 AND (t.date_distrib BETWEEN ip.date_debut AND ip.date_fin OR (t.date_distrib >= ip.date_debut AND ip.date_fin IS NULL))
                            INNER JOIN type_info_portage tip ON ip.type_info_id = tip.id
                        WHERE
                            ip.valeur IS NOT NULL
                        ORDER BY
                            t.id, tip.id
                         ";
            $res    = $this->_em->getConnection()->fetchAll($sSlct);
            foreach($res as $aArr) {
                if(isset($aCorrespondanceChps[$aArr['code']]))
                {
                    $aUpdateTmp[$aArr['id']][$aArr['code']][] = trim(str_replace(array("\r\n", "\r", "\n", "\t"), ' - ', $aArr['valeur'])); 
                }
            }
            
            
            // Infos portage Pts Livraison
            $sSlct  = " SELECT
                            t.id, tip.id AS tip_id, tip.code, ip.valeur
                        FROM
                            france_routage_c_a_s_tmp t
                            INNER JOIN infos_portages_livraisons ipl ON t.point_livraison_id = ipl.livraison_id
                            INNER JOIN info_portage ip ON ipl.info_portage_id = ip.id AND (t.date_distrib BETWEEN ip.date_debut AND ip.date_fin OR (t.date_distrib >= ip.date_debut AND ip.date_fin IS NULL))
                            INNER JOIN type_info_portage tip ON ip.type_info_id = tip.id
                        WHERE
                            ip.valeur IS NOT NULL
                        ORDER BY
                            t.id, tip.id
                         ";
            $res    = $this->_em->getConnection()->fetchAll($sSlct);
            foreach($res as $aArr) {
                if(isset($aCorrespondanceChps[$aArr['code']]))
                {
                    $aUpdateTmp[$aArr['id']][$aArr['code']][] = trim(str_replace(array("\r\n", "\r", "\n", "\t"), ' - ', $aArr['valeur'])); 
                }
            }
            if(!empty($aUpdateTmp))
            {
                foreach($aUpdateTmp as $iId => $aArrChps)
                {
                    foreach($aArrChps as $sCodeInfosPortage => $aInfosVal)
                    {
                        $aUpdate[$iId][$aCorrespondanceChps[$sCodeInfosPortage]['col']]  = substr(implode(' - ', $aInfosVal), 0, $aCorrespondanceChps[$sCodeInfosPortage]['long']);
                    }
                }
            }            
            
            if(!empty($aUpdate))
            {
                foreach($aUpdate as $sId => $aArrV)
                {
                    $sUpdate = "    UPDATE france_routage_c_a_s_tmp
                                    SET ";
                    $aA = array();
                    $aParams = array();
                    foreach($aArrV as $sCol => $sVal)
                    {
                        $sUpdate .= " ".$sCol." = :".$sCol." ";
                        $aParams[":".$sCol] = $sVal;
                    }
                    $sUpdate .= "   WHERE
                                        id = ".$sId."
                                 ";
                    $this->_em->getConnection()->executeQuery($sUpdate, $aParams);
                }
            }
        } catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Stockage des donnees du fichier en cours de traitement
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function enregistre()
    {
        try {        
            $aSocDatesATraiter  = array();
            $sSlctDatesSoc  = " SELECT DISTINCT 
                                    DATE_FORMAT(date_distrib, '%Y/%m/%d') AS date_distrib, soc_code_ext
                                FROM
                                    france_routage_c_a_s_tmp 
                                WHERE
                                    societe_id IS NOT NULL AND produit_id IS NOT NULL AND abonne_soc_id IS NOT NULL
                                    AND commune_id IS NOT NULL AND depot_id IS NOT NULL 
                                    AND modele_tournee_jour_code IS NOT NULL
                                 ";
            $res    = $this->_em->getConnection()->fetchAll($sSlctDatesSoc);
            foreach($res as $aArr) {
                $aSocDatesATraiter[]    = array("date_distrib" => $aArr['date_distrib'], "soc_code_ext" => $aArr['soc_code_ext']);
                        
                $sDelete    = " DELETE FROM france_routage_c_a_s WHERE date_distrib='".$aArr['date_distrib']."' AND soc_code_ext='".$aArr['soc_code_ext']."' ";
                $this->_em->getConnection()->executeQuery($sDelete);
                
                // Insertion dans "france_routage_c_a_s"
                $sInsert    = " INSERT INTO france_routage_c_a_s 
                                    (date_distrib, date_parution, num_parution, fic_recap_id, numabo_ext, 
                                    vol1_ext, vol2_ext, vol3_ext, vol4_ext, vol5_ext, cp_ext, ville_ext,
                                    commune_id, depot_id, type_portage, qte, 
                                    divers1, info_comp1, info_comp2, divers2, 
                                    abonne_soc_id, client_type, 
                                    rnvp_vol1, rnvp_vol2, rnvp_vol3, rnvp_vol4, rnvp_vol5, rnvp_cp, rnvp_ville, rnvp_insee,
                                    point_livraison_id, modele_tournee_jour_code, ordre, jour_id, 
                                    societe_id, produit_id, soc_code_ext, prd_code_ext, spr_code_ext)
                                SELECT
                                    date_distrib, date_parution, num_parution, fic_recap_id, numabo_ext, 
                                    vol1_ext, vol2_ext, vol3_ext, vol4_ext, vol5_ext, cp_ext, ville_ext,
                                    commune_id, depot_id, type_portage, qte, 
                                    divers1, info_comp1, info_comp2, divers2, 
                                    abonne_soc_id, client_type, 
                                    rnvp_vol1, rnvp_vol2, rnvp_vol3, rnvp_vol4, rnvp_vol5, rnvp_cp, rnvp_ville, rnvp_insee,
                                    point_livraison_id, modele_tournee_jour_code, ordre, jour_id, 
                                    societe_id, produit_id, soc_code_ext, prd_code_ext, spr_code_ext
                                FROM
                                    france_routage_c_a_s_tmp
                                WHERE
                                    date_distrib='".$aArr['date_distrib']."' AND soc_code_ext='".$aArr['soc_code_ext']."'
                                    AND societe_id IS NOT NULL AND produit_id IS NOT NULL AND abonne_soc_id IS NOT NULL 
                                    AND commune_id IS NOT NULL AND depot_id IS NOT NULL 
                                    AND modele_tournee_jour_code IS NOT NULL
                                    ";
                $this->_em->getConnection()->executeQuery($sInsert);
                
                // Suppression des lignes d'abonnes de "france_routage_adresse" en cas de changement d'adresse
                $aIdChgtAdresse = array();
                $iNbASuppr  = 100;
                $sSlctIdChgtAdresse   = " SELECT DISTINCT a.id
                                        FROM
                                            france_routage_c_a_s_tmp t
                                            INNER JOIN france_routage_adresse a ON t.numabo_ext = a.numabo_ext AND t.soc_code_ext = a.soc_code_ext
                                        WHERE
                                            t.date_distrib='".$aArr['date_distrib']."' AND t.soc_code_ext='".$aArr['soc_code_ext']."'
                                            AND t.societe_id IS NOT NULL AND t.produit_id IS NOT NULL AND t.abonne_soc_id IS NOT NULL 
                                            AND t.commune_id IS NOT NULL AND t.depot_id IS NOT NULL 
                                            AND (t.vol1_ext<>a.vol1_ext OR t.vol2_ext<>a.vol2_ext OR t.vol3_ext<>a.vol3_ext OR t.vol4_ext<>a.vol4_ext
                                                                                     OR t.vol5_ext<>a.vol5_ext OR t.cp_ext<>a.cp_ext OR t.ville_ext<>a.ville_ext)
                                            AND t.modele_tournee_jour_code IS NOT NULL
                                            ";
                $rIdChgtAdresse    = $this->_em->getConnection()->fetchAll($sSlctIdChgtAdresse);
                foreach($rIdChgtAdresse as $aChgtAdresse)
                {
                    $aIdChgtAdresse[]   = $aChgtAdresse['id'];
                    if(count($aIdChgtAdresse)%$iNbASuppr==0 && !empty($aIdChgtAdresse))
                    {
                        $sDelete    = " DELETE FROM france_routage_adresse WHERE id IN (".implode(",", $aIdChgtAdresse).") ";
                        $this->_em->getConnection()->executeQuery($sDelete);
                    }
                }
                if(!empty($aIdChgtAdresse))
                {
                    $sDelete    = " DELETE FROM france_routage_adresse WHERE id IN (".implode(",", $aIdChgtAdresse).") ";
                    $this->_em->getConnection()->executeQuery($sDelete);
                }
                
                // Insertion dans "france_routage_adresse"
                $sInsertAdr    = " INSERT INTO france_routage_adresse 
                                        (numabo_ext, soc_code_ext, societe_id, 
                                        vol1_ext, vol2_ext, vol3_ext, vol4_ext, vol5_ext, cp_ext, ville_ext, client_type,                                        
                                        rnvp_vol1, rnvp_vol2, rnvp_vol3, rnvp_vol4, rnvp_vol5, rnvp_cp, rnvp_ville, rnvp_insee)
                                    SELECT
                                        t.numabo_ext, t.soc_code_ext, t.societe_id, 
                                        t.vol1_ext, t.vol2_ext, t.vol3_ext, t.vol4_ext, t.vol5_ext, t.cp_ext, t.ville_ext, t.client_type,
                                        t.rnvp_vol1, t.rnvp_vol2, t.rnvp_vol3, t.rnvp_vol4, t.rnvp_vol5, t.rnvp_cp, t.rnvp_ville, t.rnvp_insee
                                    FROM
                                        france_routage_c_a_s_tmp t
                                        LEFT JOIN france_routage_adresse a ON t.vol1_ext=a.vol1_ext AND t.vol2_ext=a.vol2_ext
                                                                                 AND t.vol3_ext=a.vol3_ext AND t.vol4_ext=a.vol4_ext
                                                                                 AND t.vol5_ext=a.vol5_ext AND t.cp_ext=a.cp_ext AND t.ville_ext=a.ville_ext
                                    WHERE
                                        t.date_distrib='".$aArr['date_distrib']."' AND t.soc_code_ext='".$aArr['soc_code_ext']."'
                                        AND t.societe_id IS NOT NULL AND t.produit_id IS NOT NULL AND t.abonne_soc_id IS NOT NULL 
                                        AND t.commune_id IS NOT NULL AND t.depot_id IS NOT NULL 
                                        AND a.id IS NULL
                                        AND t.modele_tournee_jour_code IS NOT NULL
                                        ";
                $this->_em->getConnection()->executeQuery($sInsertAdr);
                
            }
            
            return $aSocDatesATraiter;
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        } 
    }
    
    
    /**
     * Mise a jour de la colonne "ficRecap". 
     * @param integer $iDernierFicRecap
     */
    public function updateFicRecap($iDernierFicRecap)
    {
        try {
            $this->createQueryBuilder('t')->update()->set('t.ficRecap', $iDernierFicRecap)->getQuery()->execute();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    
}