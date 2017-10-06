<?php
/**
 * Created by PhpStorm.
 * User: ydieng
 * Date: 16/05/2017
 * Time: 14:05
 */

namespace Ams\DistributionBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Ams\DistributionBundle\Exception\SuiviDeProductionException;
use Ams\SilogBundle\Command\GlobalCommand;

/**
 * Class SuiviDeProductionRepository
 * @package Ams\DistributionBundle\Repository
 */
class SuiviDeProductionRepository extends EntityRepository
{
    private $sqlLoadDataInFile;

    /**
     * @param $logFile
     * @param $ficCode
     * @param string $formatFic
     * @param int $trim
     * @param int $nbChampsCSV
     * @param string $charset
     * @return string
     * @throws \Exception
     */
    public function getSQLLoadDataInFile($logFile,$ficCode, $formatFic='CSV', $trim=1, $nbChampsCSV=25, $charset='')
    {
        $trim = intval($trim);
        $oFormatColonneListe    = $this->getDataByFicCode($ficCode);
        if (count($oFormatColonneListe) == 0) {
            $logFile->erreur("Il n'y a pas de parametrage pour '" . $ficCode . "' dans la table 'fic_format_enregistrement''", E_USER_ERROR);
            throw new \Exception("Identification de code introuvable dans la table 'fic_format_enregistrement'");
        }
//        var_dump($oFormatColonneListe);
//        die();
        $this->sqlLoadDataInFile     = " LOAD DATA LOCAL INFILE '%%NOM_FICHIER%%' INTO TABLE %%NOM_TABLE%%";

        if($charset != '')
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

//            case 'LONG_FIXE':
//                $this->sqlLoadDataInFile	.= '@ligne';
//                break;
        }
        $this->sqlLoadDataInFile	.= " ) ";
        $this->sqlLoadDataInFile	.= " SET ";
        switch($formatFic)
        {
            case 'CSV':
                $aTmp	= array();
                foreach($oFormatColonneListe as $oFormatColonne)
                {
//                    var_dump($oFormatColonne);
//                    die();
//                    $aTmp[]	= $oFormatColonne->getAttribut().'='.((is_null($oFormatColonne->getColValRplct()))? (($trim==1)?'TRIM(REPLACE(@COL_'.$oFormatColonne->getColVal().", '\r', ''))":'REPLACE(@COL_'.$oFormatColonne->getColVal().", '\r', '')") : $oFormatColonne->getColValRplct());
                    $aTmp[]	= $oFormatColonne['attribut'].'='.((is_null($oFormatColonne['col_val_rplct']))? (($trim==1)?'TRIM(REPLACE(@COL_'.$oFormatColonne['col_val'].", '\r', ''))":'REPLACE(@COL_'.$oFormatColonne['col_val'].", '\r', '')") : $oFormatColonne['col_val_rplct']);
                }
                $this->sqlLoadDataInFile	.= implode(', ', $aTmp);
                break;

//            case 'LONG_FIXE':
//                $aTmp	= array();
//                foreach($oFormatColonneListe as $oFormatColonne)
//                {
//                    $aTmp[]	= $oFormatColonne->getAttribut().'='.((is_null($oFormatColonne->getColValRplct()))? (($trim==1)?'TRIM(REPLACE('.'SUBSTR(@ligne, '.$oFormatColonne->getColDebut().', '.$oFormatColonne->getColLong().')'.", '\r', ''))":'REPLACE(SUBSTR(@ligne, '.$oFormatColonne->getColDebut().', '.$oFormatColonne->getColLong()."), '\r', '')") : $oFormatColonne->getColValRplct());
//                    $aTmp[]	= $oFormatColonne['attribut'].'='.((is_null($oFormatColonne['col_val_rplct']))? (($trim==1)?'TRIM(REPLACE('.'SUBSTR(@ligne, '.$oFormatColonne['col_debut'].', '.$oFormatColonne['col_long'].')'.", '\r', ''))":'REPLACE(SUBSTR(@ligne, '.$oFormatColonne['col_debut'].', '.$oFormatColonne['col_long']."), '\r', '')") : $oFormatColonne['col_val_rplct']);
//                }
//                $this->sqlLoadDataInFile	.= implode(', ', $aTmp);
//                break;
        }
        $this->sqlLoadDataInFile	.= " ; ";
        //print_r($this->sqlLoadDataInFile);
        //die();
        return $this->sqlLoadDataInFile;
    }

    /**
     * @param array $variables
     * @return mixed
     */
    private function setVariableSQLLoadDataInFile($variables=array())
    {
        if(!isset($this->sqlLoadDataInFile) || is_null($this->sqlLoadDataInFile))
        {
            SuiviDeProductionException::methodesObligatoires(__METHOD__, array("getSQLLoadDataInFile"));
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
     * Charge un fichier en base de données
     * @param array $params
     * @return array|bool
     * @throws \Exception
     */
    public function loadDataInTable($params = array())
    {
        $databaseName = $params['%%NOM_TABLE%%'];

        if (!isset($databaseName)){
            throw new \Exception("Chargement de fichier dans la table. La table n'est pas connu (Absence de la variable '%%NOM_TABLE%%').");
        }

        $sSQLLoad    = $this->setVariableSQLLoadDataInFile($params);

        // Mise a "TRUE" du parametre "\PDO::MYSQL_ATTR_LOCAL_INFILE"
        $ConnexionParam = $this->_em->getConnection()->getParams();
//        var_dump($ConnexionParam);
        $newPdoConnexion = new \PDO('mysql:host=' . $ConnexionParam['host'] . ';dbname=' .  $ConnexionParam['dbname'] , $ConnexionParam['user'], $ConnexionParam['password'], array(
            \PDO::MYSQL_ATTR_LOCAL_INFILE => true
        ));

        $Load    = $newPdoConnexion->prepare($sSQLLoad);
        $res = $Load->execute();
        if($res === true)
        {
            return $res;
        }
        return array('erreur' => 1, 'sql' => $sSQLLoad);
    }

    /**
     * renvoie les données du format d'enregitrement ayant le code 'SUIVI_PRODUCTION'
     * @param $code
     * @return array
     */
    public function getDataByFicCode($code){
        $sql= "select * from fic_format_enregistrement where fic_code = '".$code."'" ;
        return $this->_em->getConnection()->fetchAll($sql);
    }

    /**
     * @param $dateEdition
     * @param $depots
     * @return array
     */
    public function getSuiviDeProductionByDateEditionAndDepots($dateEdition, $depots){
        $sql = "SELECT s.id, s.date_edi, s.libelle_edi, s.code_route, d.libelle_route,"
                . " d.libelle_centre, d.code_centre,s.pqt_prev, s.pqt_eject, s.ex_prev, s.ex_eject,"
                . " s.date_up FROM suivi_de_production s INNER JOIN depot_route_suivi_prod d ON d.code_route = s.code_route"
                . " WHERE  date_edi = '".$dateEdition."' AND d.code_centre IN (".$depots.")";
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    /**
     * @param $dateEdition
     * @return array
     */
    public function getSuiviDeProductionByDateEdition($dateEdition){
        $sql = "SELECT s.id, s.date_edi, s.libelle_edi, s.code_route, s.pqt_prev, s.pqt_eject, s.ex_prev, s.ex_eject, s.date_up FROM suivi_de_production s WHERE  date_edi = '".$dateEdition."'";
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    public function getAllDepotsActive(){
        $sql = "SELECT id,code,libelle FROM `depot` ";
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    
    public function getCodeDepotsActiveById($depots){
        $sql = "SELECT code FROM `depot` where id IN (".$depots.") ";
        return $this->_em->getConnection()->fetchAll($sql);
    }

    /**
     * @return array
     */
    public function getIdLibelleAllDepots(){
        $sql = "SELECT id,libelle FROM depot ORDER BY id";
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    public function getIdCodeAllDepots(){
         $sql = "SELECT id,code FROM depot ORDER BY id";
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    /**
     * 
     * @return array
     */
    public function getAllDepotsRoutes(){
        $sql = "SELECT * FROM depot_route_suivi_prod ";
        return $this->_em->getConnection()->fetchAll($sql);
    }
    
    /**
     * link un fic_recap_id avec les enregistrements
     * @param $id
     * @param $dateEdition
     * @throws \Exception
     */
    public function setFicRecapId($id, $dateEdition){
        $param['fic_recap_id'] = $id;
        try {
            $this->_em->getConnection()->update("suivi_de_production", $param, array("date_edi" => $dateEdition));
        }
        catch (DBALException $ex) {
            throw $ex;
        }
    }

    /**
     *
     * @param $ficRecapId
     * @param $values
     * @throws \Exception
     */
    public function updateDatasAndFicRecap($ficRecapId, $values){
        $param['fic_recap_id'] = $ficRecapId;
        $param['pqt_prev'] = $values['pqt_prev'];
        $param['pqt_eject'] = $values['pqt_eject'];
        $param['ex_prev'] = $values['ex_prev'];
        $param['ex_eject'] = $values['ex_eject'];
        $param['date_up'] = date("Y-m-d H:i:s");
        $id = $values['id'];
        try {
            $this->_em->getConnection()->update("suivi_de_production", $param, array("id" => $id));
        }
        catch (DBALException $ex) {
            throw $ex;
        }
    }

    /**
     * @return array
     */
    public function getAllSuiviDeProduction(){
        $sql = "select * from suivi_de_production ";
        return $this->_em->getConnection()->fetchAll($sql);
    }
}