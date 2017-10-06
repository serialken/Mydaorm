<?php

namespace Ams\EmployeBundle\Services;

use Ams\SilogBundle\Lib\SqlField;

class Octime {

    private $_em;
    private $log;
    private $connMROAD;
    private $connOctime;
    private $sqlField;
    private $directory;
    private $sous_directory_badge;
    private $sous_directory_horaire;
    private $sous_directory_hgaranties;

    function __construct($doctrine, $directory, $sous_directory_badge, $sous_directory_horaire, $sous_directory_hgaranties) {
        $this->_em = $doctrine->getManager();
        $this->log = $doctrine->getManager()->getRepository('AmsPaieBundle:PaiIntLog');
        $this->connOctime = $doctrine->getManager('octime')->getConnection();
        $this->connMROAD = $doctrine->getManager('mroad')->getConnection();
        $this->sqlField =  new SqlField();
        $this->directory =  $directory;
        $this->sous_directory_badge =  $sous_directory_badge;
        $this->sous_directory_horaire =  $sous_directory_horaire;
        $this->sous_directory_hgaranties =  $sous_directory_hgaranties;
    }

    function alimentation(&$idtrt, $utilisateur_id=0, $depot_id=0, $flux_id=0) {
        try {
            $this->log->debut($idtrt,$utilisateur_id,'ALIM_OCTIME');

            $this->log->log($idtrt,'OCTIME','alimentation');
            $results = $this->connOctime->exec("BEGIN mroad.exec(); END;");

            $sql="set @idtrt=".$this->sqlField->sqlIdOrNull($idtrt).";";
            $sql.="call pai_valide_octime(@validation_id,".$this->sqlField->sqlIdOrNull($depot_id).",".$this->sqlField->sqlIdOrNull($flux_id).",null)";
            $proc = $this->connMROAD->exec($sql);
            $this->log->fin($idtrt,'ALIM_OCTIME');
        } catch (\Exception $ex) {
            $this->log->logErreur($idtrt,'ERREUR',$ex->getMessage());
            $this->log->erreur($idtrt,'ALIM_OCTIME');
        }
        return $this->_em->getRepository('AmsPaieBundle:PaiIntTraitement')->getstatut($idtrt);
    }

    function generation(&$idtrt, $utilisateur_id=0, $isStc=false) {
        try {
            // sFicBadge est une chaîne =  wHorodatagePepp+"_"+wUserPepp+"_Badge.csv"
            // sFicBadgeErreur est une chaîne =  wHorodatagePepp+"_"+wUserPepp+"_Badge_erreur.csv"
            //2014070421001132_Automate_Badge_erreur.csv
            $this->log->debut($idtrt,$utilisateur_id,'GENERE_OCTIME');

            $user=$this->_em->getRepository('AmsSilogBundle:Utilisateur')->getLogin($utilisateur_id);

            $sql="set @idtrt=".$this->sqlField->sqlIdOrNull($idtrt).";";
            $sql.="call INT_MROAD2BADGE(" . $utilisateur_id . ",@idtrt,".($isStc?"true":"false").")";
            $proc = $this->connMROAD->exec($sql);

            $d = new \DateTime();
            $statut=$this->_em->getRepository('AmsPaieBundle:PaiIntTraitement')->getstatut($idtrt);
            if ($statut!='E'){
                $prefix = $d->format("YmdHis") . "00_" . $user . "_";
                $directory = $this->directory . "/" . $this->sous_directory_badge;
                $sql = "SELECT ligne FROM pai_int_oct_pointage";
                $this->generationFichier($idtrt, $sql, $directory . "/" . $prefix . "Badge.csv");
                $sql = "SELECT ligne FROM pai_int_oct_erreur where idtrt=" . $idtrt;
                $this->generationFichier($idtrt, $sql, $directory . "/" . $prefix . "Badge_erreur.csv");
            }
            
            $sql="set @idtrt=".$this->sqlField->sqlIdOrNull($idtrt).";";
            $sql.="call INT_MROAD2CYCLE(" . $utilisateur_id . ",@idtrt,".($isStc?"true":"false").")";
            $proc = $this->connMROAD->exec($sql);

            $d = new \DateTime();
            $statut=$this->_em->getRepository('AmsPaieBundle:PaiIntTraitement')->getstatut($idtrt);
            if ($statut!='E'){
                $prefix = $d->format("YmdHis") . "00_" . $user . "_";
                $directory = $this->directory . "/" . $this->sous_directory_horaire;
                $sql = "SELECT ligne FROM pai_int_oct_cjexp";
                $this->generationFichier($idtrt, $sql, $directory . "/" . $prefix . "Horaire.csv");
                $sql = "SELECT ligne FROM pai_int_oct_erreur where idtrt=" . $idtrt;
                $this->generationFichier($idtrt, $sql, $directory . "/" . $prefix . "Horaire_erreur.csv");
            }
            $this->log->fin($idtrt,'GENERE_OCTIME');
        } catch (\Exception $ex) {
            $this->log->log($idtrt,'ERREUR',$ex->getMessage());
            $this->log->erreur($idtrt,'GENERE_OCTIME');
        }
        return $this->_em->getRepository('AmsPaieBundle:PaiIntTraitement')->getstatut($idtrt);
    }

    function generationHeuresGaranties(&$idtrt, $utilisateur_id=0, $flux_id, $isStc=false) {
        try {
            // sFicBadge est une chaîne =  wHorodatagePepp+"_"+wUserPepp+"_Badge.csv"
            // sFicBadgeErreur est une chaîne =  wHorodatagePepp+"_"+wUserPepp+"_Badge_erreur.csv"
            //2014070421001132_Automate_Badge_erreur.csv
            $this->log->debut($idtrt,$utilisateur_id,'GENERE_HEURESGARANTIES');

            $user=$this->_em->getRepository('AmsSilogBundle:Utilisateur')->getLogin($utilisateur_id);

//            $sql="set @idtrt=".$this->sqlField->sqlIdOrNull($idtrt).";";
//            $sql.="call INT_MROAD2HEURESSGARANTIES(" . $utilisateur_id . ",@idtrt)";
//            $proc = $this->connMROAD->exec($sql);

            $d = new \DateTime();
            $statut=$this->_em->getRepository('AmsPaieBundle:PaiIntTraitement')->getstatut($idtrt);
            if ($statut!='E'){
                $prefix = $d->format("YmdHis") . "00_" . $user . "_";
                $directory = $this->directory . "/" . $this->sous_directory_hgaranties;
                $sql = "SELECT ligne FROM pai_int_oct_heuresgaranties where idtrt=" . $idtrt;
                $this->generationFichier($idtrt, $sql, $directory . "/" . $prefix . "HGaranties.csv");
                }
            $this->log->fin($idtrt,'GENERE_HEURESGARANTIES');
        } catch (\Exception $ex) {
            $this->log->log($idtrt,'ERREUR',$ex->getMessage());
            $this->log->erreur($idtrt,'GENERE_HEURESGARANTIES');
        }
        return $this->_em->getRepository('AmsPaieBundle:PaiIntTraitement')->getstatut($idtrt);
    }

    function generationFichier($idtrt, $sql, $fileName) {
        try {
            $this->log->logLevel($idtrt,'OCTIME','Génération du fichier '.$fileName,4);
            $csvFile = fopen($fileName, 'w');
            if ($csvFile) {
                $lignes = $this->connMROAD->fetchAll($sql);
                foreach ($lignes as $ligne) {
                    fwrite($csvFile, $ligne["ligne"] . "\r\n");
                }
                fclose($csvFile);
            } else {
                $this->log->logErreur($idtrt,'ERREUR',"Erreur d'ouverture du fichier");
                $this->log->erreur($idtrt,'');
            }
        } catch (\Exception $ex) {
            $this->log->logErreur($idtrt,'ERREUR',$ex->getMessage());
            $this->log->erreur($idtrt,'');
        }
    }

}
