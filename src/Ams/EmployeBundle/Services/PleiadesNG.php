<?php

namespace Ams\EmployeBundle\Services;

use Ams\SilogBundle\Lib\SqlField;

class PleiadesNG {

    private $_em;
    private $log;
    private $connMROAD;
    private $connPleiadesNG;
    private $sqlField;
    private $directory;
    private $sous_directory;
    private $octime;

    function __construct($doctrine, $directory, $sous_directory, $octime) {
        $this->_em = $doctrine->getManager();
        $this->log = $doctrine->getManager()->getRepository('AmsPaieBundle:PaiIntLog');
        $this->connPleiadesNG = $doctrine->getManager('pleiadesng')->getConnection();
        $this->connMROAD = $doctrine->getManager('mroad')->getConnection();
        $this->sqlField =  new SqlField();
        $this->directory =  $directory;
        $this->sous_directory =  $sous_directory;
        $this->octime =  $octime;
    }

    function alimentation(&$idtrt, $utilisateur_id=0, $depot_id=0, $flux_id=0) {
        try {
            $this->log->debut($idtrt,$utilisateur_id,'ALIM_EMPLOYE','',$depot_id, $flux_id);
            $batchactif=$this->_em->getRepository('AmsPaieBundle:PaiIntTraitement')->alimemploye_en_cours($idtrt);
            if (isset($batchactif)){
                $this->log->logErreur($idtrt,'ERREUR',"Une procédure d'alimentation est déjà en cours déxécution.");
                $this->log->erreur($idtrt,'ne sert à rien');
            } else {
                $this->log->log($idtrt,'PLEIADESNG','alimentation');
                $results = $this->connPleiadesNG->exec("BEGIN mroad.exec(); END;");

                $sql="set @idtrt=".$this->sqlField->sqlIdOrNull($idtrt).";";
                $sql.="call INT_PNG2MROAD(".$utilisateur_id.",@idtrt,".$this->sqlField->sqlIdOrNull($depot_id).",".$this->sqlField->sqlIdOrNull($flux_id).")";
                $proc = $this->connMROAD->exec($sql);
                $this->log->fin($idtrt,'ALIM_EMPLOYE');
            }
        } catch (\Exception $ex) {
            $this->log->logErreur($idtrt,'ERREUR',$ex->getMessage());
            $this->log->erreur($idtrt,'ALIM_EMPLOYE');
        }
        return $this->_em->getRepository('AmsPaieBundle:PaiIntTraitement')->getstatut($idtrt);
    }

    function rafraichirEv(&$idtrt, $utilisateur_id, $flux_id, $isStc , $alim_employe=true, $alim_octime=true, $alim_pleiades=true) {
        try {
            $batchactif=$this->_em->getRepository('AmsPaieBundle:PaiIntTraitement')->paie_en_cours($idtrt);
            if (isset($batchactif)){
                $this->log->logErreur($idtrt,'ERREUR',"Une procédure de paie est déjà en cours déxécution.");
                $this->log->erreur($idtrt,'ne sert à rien');
            } else {
                if ($alim_employe) {
                    // Raffraichissement des employes
                    $this->alimentation($idtrt,$utilisateur_id);
                }
                if ($alim_octime) {
                    // Generation des badges et des cycles
                    $this->octime->alimentation($idtrt,$utilisateur_id);
                    $this->octime->generation($idtrt,$utilisateur_id,$isStc);
                }
                if ($alim_pleiades) {
                    $this->log->log($idtrt,'PLEIADESNG','recupere ev');
                    $results = $this->connPleiadesNG->exec("BEGIN mroad.exec_ev(".$idtrt.",".($isStc?"1":"0").",null,".$this->sqlField->sqlIdOrNull($flux_id)."); END;");
                } else {
                    $sql="delete from pai_png_ev_factoryw";
                    $proc = $this->connMROAD->exec($sql);
                }
            }
        } catch (\Exception $ex) {
            throw($ex);
        }
        return $this->_em->getRepository('AmsPaieBundle:PaiIntTraitement')->getstatut($idtrt);
    }

    function genererIndividuel(&$idtrt, $utilisateur_id, $flux_id, $anneeMois) {
        try {
            $this->log->debutAnneeMois($idtrt,$utilisateur_id,'GENERE_PLEIADES_STC','',0,$flux_id,$anneeMois);

            if ($this->rafraichirEv($idtrt,$utilisateur_id, $flux_id,true)!='E'){
                $sql="set @idtrt=".$this->sqlField->sqlIdOrNull($idtrt).";";
                $sql.="call INT_MROAD2EV_STC(@idtrt,".$utilisateur_id.",".$flux_id.")";
                $proc = $this->connMROAD->exec($sql);

                if ($this->_em->getRepository('AmsPaieBundle:PaiIntTraitement')->getstatut($idtrt)!='E'){
                    $this->genererFichiers($idtrt, $utilisateur_id);
                    $this->octime->generationHeuresGaranties($idtrt,$utilisateur_id, $flux_id,true);
                }
            }
            $this->log->fin($idtrt,'GENERE_PLEIADES_STC');
        } catch (\Exception $ex) {
            $this->log->logErreur($idtrt,'ERREUR',$ex->getMessage());
            $this->log->erreur($idtrt,'GENERE_PLEIADES_STC');
        }
        return $this->_em->getRepository('AmsPaieBundle:PaiIntTraitement')->getstatut($idtrt);
    }

    function calculCollectif(&$idtrt, $utilisateur_id, $flux_id, $anneeMois,$jour_a_traiter) {
        try {
            $this->log->debutAnneeMois($idtrt,$utilisateur_id,'CALCUL_PLEIADES_MENSUEL',$jour_a_traiter,0,$flux_id,$anneeMois);

            $sql="set @idtrt=".$this->sqlField->sqlIdOrNull($idtrt).";";
            $sql.="call INT_MROAD2EV_QUOTIDIEN(@idtrt,".$utilisateur_id.",".$flux_id.")";
            $proc = $this->connMROAD->exec($sql);
            if ($this->_em->getRepository('AmsPaieBundle:PaiIntTraitement')->getstatut($idtrt)!='E'){
                $this->octime->generationHeuresGaranties($idtrt,$utilisateur_id, $flux_id,false);
            }

            $this->log->fin($idtrt,'CALCUL_PLEIADES_MENSUEL');
        } catch (\Exception $ex) {
            $this->log->logErreur($idtrt,'ERREUR',$ex->getMessage());
            $this->log->erreur($idtrt,'CALCUL_PLEIADES_MENSUEL');
        }
        return $this->_em->getRepository('AmsPaieBundle:PaiIntTraitement')->getstatut($idtrt);
    }

    function genererCollectif(&$idtrt, $utilisateur_id, $flux_id, $anneeMois , $alim_employe, $alim_octime, $alim_pleiades) {
        try {
            $this->log->debutAnneeMois($idtrt,$utilisateur_id,'GENERE_PLEIADES_MENSUEL','',0,$flux_id,$anneeMois);

            if ($this->rafraichirEv($idtrt,$utilisateur_id, $flux_id,false , $alim_employe, $alim_octime, $alim_pleiades)!='E'){
                $sql="set @idtrt=".$this->sqlField->sqlIdOrNull($idtrt).";";
                $sql.="call INT_MROAD2EV_MENSUEL(@idtrt,".$utilisateur_id.",".$flux_id.",true)";
                $proc = $this->connMROAD->exec($sql);

                if ($this->_em->getRepository('AmsPaieBundle:PaiIntTraitement')->getstatut($idtrt)!='E'){
                    if ($alim_pleiades) {
                        $this->genererFichiers($idtrt, $utilisateur_id);
                    }
                    if ($alim_octime) {
                        $this->octime->generationHeuresGaranties($idtrt,$utilisateur_id, $flux_id,false);
                    }
                }
            }
            $this->log->fin($idtrt,'GENERE_PLEIADES_MENSUEL');
        } catch (\Exception $ex) {
            $this->log->logErreur($idtrt,'ERREUR',$ex->getMessage());
            $this->log->erreur($idtrt,'GENERE_PLEIADES_MENSUEL');
        }
        return $this->_em->getRepository('AmsPaieBundle:PaiIntTraitement')->getstatut($idtrt);
    }

    function genererCloture(&$idtrt, $utilisateur_id, $flux_id, $anneeMois) {
        try {
            $this->log->debutAnneeMois($idtrt,$utilisateur_id,'GENERE_PLEIADES_CLOTURE','',0,$flux_id,$anneeMois);

            if ($this->rafraichirEv($idtrt,$utilisateur_id, $flux_id,false)!='E'){
                $sql="set @idtrt=".$this->sqlField->sqlIdOrNull($idtrt).";";
                $sql.="call INT_MROAD2EV_CLOTURE(@idtrt,".$utilisateur_id.",".$flux_id.")";
                $proc = $this->connMROAD->exec($sql);

                if ($this->_em->getRepository('AmsPaieBundle:PaiIntTraitement')->getstatut($idtrt)!='E'){
                    $this->genererFichiers($idtrt, $utilisateur_id);
                    $this->octime->generationHeuresGaranties($idtrt,$utilisateur_id, $flux_id,false);
                }
            }
            $this->log->fin($idtrt,'GENERE_PLEIADES_CLOTURE');
        } catch (\Exception $ex) {
            $this->log->logErreur($idtrt,'ERREUR',$ex->getMessage());
            $this->log->erreur($idtrt,'GENERE_PLEIADES_CLOTURE');
        }
        return $this->_em->getRepository('AmsPaieBundle:PaiIntTraitement')->getstatut($idtrt);
    }

    function genererFichiers($idtrt, $utilisateur_id) {
        $user=$this->_em->getRepository('AmsSilogBundle:Utilisateur')->getLogin($utilisateur_id);
        $d = new \DateTime();
        $prefix = $d->format("YmdHis") . "_" . $user . "_";
        $directory = $this->directory . "/" . $this->sous_directory;

        $sql = "SELECT ligne FROM pai_int_ev_diff_NG where idtrt=".$idtrt." order by ordre";
        $this->generationFichier($idtrt, $sql, $directory . "/" . $prefix . "Ev.txt");

        $sql = "SELECT concat(date_log,' : ',level,' : ',' ',rpad(module,32,' '),' ',msg,'') as ligne FROM pai_int_log where idtrt=".$idtrt." order by id";
        $this->generationFichier($idtrt, $sql, $directory . "/" . $prefix . "Ev.log");
        
    }
    function generationFichier($idtrt, $sql, $fileName) {
        try{
            $this->log->logLevel($idtrt,'PLEIADESNG','Génération du fichier '.$fileName,4);
            $csvFile = fopen($fileName, 'w');
            if ($csvFile) {
                $lignes = $this->connMROAD->fetchAll($sql);
                foreach ($lignes as $ligne) {
                    fwrite($csvFile, $ligne["ligne"] . "\r\n");
                }
                fclose($csvFile);
                } else {
                    $this->log->log($idtrt,'ERREUR',"Erreur d'ouverture du fichier");
                    $this->log->erreur($idtrt,'');
                }
        } catch (\Exception $ex) {
            $this->log->logErreur($idtrt,'ERREUR',$ex->getMessage());
            $this->log->erreur($idtrt,'');
        }
    }

}
