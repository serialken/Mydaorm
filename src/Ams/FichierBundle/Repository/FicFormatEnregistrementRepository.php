<?php 

namespace Ams\FichierBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Ams\FichierBundle\Exception\FicFormatEnregistrementException;

class FicFormatEnregistrementRepository extends EntityRepository
{
    private $sqlLoadDataInFile;
    
    
    /**
     * Genere la requete d'integration d'un fichier dans une table (LOAD DATA INFILE)
     * 
     * @param string $ficCode
     * @param string $formatFic
     * @param integer $trim
     * @param integer $nbChampsCSV
     * @param string $charset
     * @return string
     */
    public function getSQLLoadDataInFile($ficCode, $formatFic='CSV', $trim=1, $nbChampsCSV=25, $charset='')
    {
        $trim = intval($trim);
        $oFormatColonneListe    = $this->findByFicCode($ficCode);
        $this->sqlLoadDataInFile     = " LOAD DATA LOCAL INFILE '%%NOM_FICHIER%%' INTO TABLE %%NOM_TABLE%%";
        
        if($charset!='')
        {
            $this->sqlLoadDataInFile	.= " CHARACTER SET ".$charset." ";
        }
        
        if($formatFic=='CSV')
        {
                $this->sqlLoadDataInFile	.= " FIELDS TERMINATED BY '%%SEPARATEUR_CSV%%'  ESCAPED BY '\\\\' ";
        }
        $this->sqlLoadDataInFile	.= " LINES TERMINATED BY '\\n' ";
        $this->sqlLoadDataInFile	.= " IGNORE %%NB_LIGNES_IGNOREES%% LINES ";
        $this->sqlLoadDataInFile	.= " ( ";
        switch($formatFic)
        {
            case 'CSV':
                $aTmp	= array();
                for($iI=1; $iI<=$nbChampsCSV; $iI++)
                {
                        $aTmp[]	= '@COL_'.$iI;
                }
                $this->sqlLoadDataInFile	.= implode(', ', $aTmp);
                break;

            case 'LONG_FIXE':
                $this->sqlLoadDataInFile	.= '@ligne';
                break;
        }
        $this->sqlLoadDataInFile	.= " ) ";
        $this->sqlLoadDataInFile	.= " SET ";
        switch($formatFic)
        {
            case 'CSV':
                $aTmp	= array();
                foreach($oFormatColonneListe as $oFormatColonne)
                {
                        $aTmp[]	= $oFormatColonne->getAttribut().'='.((is_null($oFormatColonne->getColValRplct()))? (($trim==1)?'TRIM(REPLACE(@COL_'.$oFormatColonne->getColVal().", '\r', ''))":'REPLACE(@COL_'.$oFormatColonne->getColVal().", '\r', '')") : $oFormatColonne->getColValRplct());
                }
                $this->sqlLoadDataInFile	.= implode(', ', $aTmp);
                break;

            case 'LONG_FIXE':
                $aTmp	= array();
                foreach($oFormatColonneListe as $oFormatColonne)
                {
                        $aTmp[]	= $oFormatColonne->getAttribut().'='.((is_null($oFormatColonne->getColValRplct()))? (($trim==1)?'TRIM(REPLACE('.'SUBSTR(@ligne, '.$oFormatColonne->getColDebut().', '.$oFormatColonne->getColLong().')'.", '\r', ''))":'REPLACE(SUBSTR(@ligne, '.$oFormatColonne->getColDebut().', '.$oFormatColonne->getColLong()."), '\r', '')") : $oFormatColonne->getColValRplct());
                }
                $this->sqlLoadDataInFile	.= implode(', ', $aTmp);
                break;
        }
	$this->sqlLoadDataInFile	.= " ; "; 
          //print_r($this->sqlLoadDataInFile);
          //die();
        return $this->sqlLoadDataInFile;
    }
    
    /**
     * 
     * @param type $variables
     * @return type
     */
    private function setVariableSQLLoadDataInFile($variables=array())
    {
        if(!isset($this->sqlLoadDataInFile) || is_null($this->sqlLoadDataInFile))
        {
            FicFormatEnregistrementException::methodesObligatoires(__METHOD__, array("getSQLLoadDataInFile"));
        }
        /*
        // Exemple de $variables 
        $variables    = array(
                                '%%NOM_FICHIER%%'		=> $this->sRepTmp.'/'.$sFicV,
                                '%%NOM_TABLE%%'			=> 'TMP_SRC_DETAILS_CAS',
                                '%%SEPARATEUR_CSV%%'	=> (isset($this->aFichierFluxParam['SEPARATEUR_CSV']) ? $this->aFichierFluxParam['SEPARATEUR_CSV'] : ''),
                                '%%NB_LIGNES_IGNOREES%%' => $this->aFichierFluxParam['NB_LIGNES_IGNOREES'],
                                );
        */
        return str_replace(array_keys($variables), array_values($variables), $this->sqlLoadDataInFile);
    }
    
    
    /**
     * Chargement du fichier dans la table $variables["%%NOM_TABLE%%"]
     * 
     * @param array $variables
     * @throws \Exception
     */
    public function chargeDansTableTmp($variables=array())
    {
        if(!isset($variables["%%NOM_TABLE%%"]))
        {
            throw new \Exception("Chargement de fichier dans la table temporaire. La table temporaire n'est pas connu (Absence de la variable '%%NOM_TABLE%%').");
        }
        
        $conn    = $this->_em->getConnection();

        $sTruncate  = str_replace(array_keys($variables), array_values($variables), "TRUNCATE TABLE %%NOM_TABLE%%");
        $Truncate    = $conn->prepare($sTruncate);
        $Truncate->execute();

        $sSQLLoad    = $this->setVariableSQLLoadDataInFile($variables);  

        // Mise a "TRUE" du parametre "\PDO::MYSQL_ATTR_LOCAL_INFILE"
        $aConnParam = $conn->getParams();
        $nouveauPdoConn = new \PDO('mysql:host=' . $aConnParam['host'] . ';dbname=' .  $aConnParam['dbname'] , $aConnParam['user'], $aConnParam['password'], array(
                        \PDO::MYSQL_ATTR_LOCAL_INFILE => true
                    ));   
        $Load    = $nouveauPdoConn->prepare($sSQLLoad);
        $retour = $Load->execute(); 
        if($retour===true)
        {
            return $retour;
        }
        return array(
            'erreur'    => 1,
            'sql'       => $sSQLLoad
        );
    }
}