<?php
/**
 * Created by PhpStorm.
 * User: ydieng
 * Date: 12/05/2017
 * Time: 17:04
 */

namespace Ams\DistributionBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Ams\SilogBundle\Command\GlobalCommand;
use Symfony\Component\Validator\Constraints\Null;

/**
 *
 *  "Command" integration des suivi de production
 *
 * Pour executer, faire :
 *      Expl :  php app/console suivi_de_production_integration --id_sh=cron_test --id_ai=1 --env=dev
 *
 *
 * Class SuiviProductionIntegrationCommand
 * @package Ams\DistributionBundle\Command
 */
class SuiviProductionIntegrationCommand extends GlobalCommand
{

    private $repTmp;
    private $repBkpLocal;
    private $repBkpAlreadyTreat;
    private $dataBaseNom;


    protected function configure()
    {
        $this->sNomCommande = 'suivi_de_production_integration';
        $this->setName($this->sNomCommande);
        $this->setDescription('Integration des fichiers de suivi de production')
                ->addOption('id_sh',null, InputOption::VALUE_REQUIRED, 'Libelle du CRON')
                ->addOption('id_ai',null, InputOption::VALUE_REQUIRED, 'Id du CRON');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs et le suivi de la commande
        //association de la commande avec le cron
        if($input->getOption('id_sh')){
            $idSh = $input->getOption('id_sh');
        }
        if($input->getOption('id_ai')){
            $idAi = $input->getOption('id_ai');
        }
        if($input->getOption('id_ai') && $input->getOption('id_sh')){
            $this->associateToCron($idAi,$idSh);
        }
        
        $this->oLog->info(date("d/m/Y H:i:s : ") . "Debut Integration du Suivi De Production - Commande : " . $this->sNomCommande);

        //Recuperation des différents  repository
        $suiviDeProductionRepo = $this->getContainer()->get('doctrine')->getRepository('AmsDistributionBundle:SuiviDeProduction');
        $ficChrgtFichiersBddRepo = $this->getContainer()->get('doctrine')->getRepository('AmsFichierBundle:FicChrgtFichiersBdd');
//        $ficFormatEnregistrementRepo = $this->getContainer()->get('doctrine')->getRepository('AmsFichierBundle:FicFormatEnregistrement');
        $ficRecapRepo = $this->getContainer()->get('doctrine')->getRepository('AmsFichierBundle:FicRecap');
//        $ficSourceRepo = $this->getContainer()->get('doctrine')->getRepository('AmsFichierBundle:FicSource');
        $ficEtatRepo = $this->getContainer()->get('doctrine')->getRepository('AmsFichierBundle:FicEtat');
        $societeRepo = $this->getContainer()->get('doctrine')->getRepository('AmsProduitBundle:Societe');

        $ficCode = "SUIVI_PRODUCTION";
        $socCode = "LP";
        $this->dataBaseNom = "suivi_de_production";

        // Identifiant de la societe  societe 'LP'
        $tabSocId = $societeRepo->getIdsocByCode($socCode);
        // Recuperation des parameters concernant le FTP et les fichiers a recuperer
        $ficChrgtFichiersBdd = $ficChrgtFichiersBddRepo->findOneByCode($ficCode);

        if (is_null($ficChrgtFichiersBdd)) {
            $e = new \Exception("Identification de flux introuvable dans la table 'fic_chrgt_fichiers_bdd'");
            $this->suiviCommand->setMsg("Le flux " . $ficCode . " n'est pas un parametre dans 'fic_chrgt_fichiers_bdd'");
            $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
            $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
            $this->suiviCommand->setEtat("KO");
            $this->oLog->erreur("Le flux " . $ficCode . " n'est pas un parametre dans 'fic_chrgt_fichiers_bdd'", E_USER_ERROR);
            $this->registerError();
            if($input->getOption('id_ai') && $input->getOption('id_sh')){
                $this->registerErrorCron($idAi);
            }
            throw $e;
        }

        // Repertoire ou l'on recupere les fichiers a traiter qui ont été déposé aprés import FTP
        $this->repTmp = $this->sRepFichiersPrinc . '/' . $this->getContainer()->getParameter('SOUSREP_FICHIERS_TMP') . '/' . $ficChrgtFichiersBdd->getSsRepTraitement() . '/' . $ficCode;
        // Repertoire de Backup Local aprés intégration
        $this->repBkpLocal = $this->sRepFichiersPrinc . '/' . $this->getContainer()->getParameter('SOUSREP_FICHIERS_BKP') . '/' . $ficChrgtFichiersBdd->getSsRepTraitement() . '/' . $ficCode;
        // Repertoire ou l'on sauvegarde des fichiers qui on été importés mais qui existait deja en base
        $this->repBkpAlreadyTreat =  $this->cree_repertoire( $this->repTmp . '/' . $this->getContainer()->getParameter('SOUSREP_FICHIERS_DEJA_INTEG'));
        // Identifiant du Flux des fichiers
        $ficFlux = $ficChrgtFichiersBdd->getFlux();
        //preparation du flux pour load data in file
        $suiviDeProductionRepo->getSQLLoadDataInFile($this->oLog, $ficCode, $ficChrgtFichiersBdd->getFormatFic(), $ficChrgtFichiersBdd->getTrimVal(), $ficChrgtFichiersBdd->getNbCol(), 'latin1');

        // Les fichiers à traiter
        $aFicIterator = new \FilesystemIterator($this->repTmp);
        $aFic = array();
        foreach ($aFicIterator as $oFic) {
            if ($oFic->isFile()) {
                $aFic[$oFic->getFilename()] = $oFic;
            }
        }
        
        ksort($aFic);//liste des fichiersà traiter trié par ordre alphabétique

        /** Verification de l'etat du fichier, IntÃ©gration et Backup */
        foreach ($aFic as $oFic) {
            $ficNom = $oFic->getFilename();
            $ficInfo = $oFic->getFileInfo();

            $this->oLog->info(date("d/m/Y H:i:s : ") . ' - Debut integration du fichier "' . $ficNom . '"');
            $this->oLog->info('Debut verification du contenu du fichier "' . $ficNom . '"');

            // recuperation de la date de parution du fichier actuelement en traitement
            $myfic = fopen($ficInfo, 'r');
            $lineHeader = fgets($myfic);
            $line = fgets($myfic);
            fclose($myfic);
            $tabLine = explode(";", $line);
            $dateParutionCourant = substr($tabLine[0],0,10);
            
            // Verification du format et de la validite de la date de parution du fichier - resultat attendu 2017-08-07
            if(preg_match('#^([0-9]{4})([-])([0-9]{2})\2([0-9]{2})$#', $dateParutionCourant, $matches) == 1 ){
                if(checkdate($matches[3], $matches[4], $matches[1])){
                    //ok
                    $ficGood = true;
                }else{
                    //bon format de date mais date pas valide
                    $ficGood = false;
                    $dateParutionCourant = date("Y-m-d");
                }
            }else{
                //mauvais format de date ou date inexistante
                $ficGood = false;
                $dateParutionCourant = date("Y-m-d");
            }
            
            // on associe un checksum au fichier courant
            $checksumFichierCourant = md5_file($ficInfo);

            // ICI ON VA METTRE TOUTES LES VARIABLES UTILISÃ©S PLUISIEURS FOIS
            // Preparation des variables
            $socId = intval($tabSocId[0]["id"]);
            $ficSourceId = $ficChrgtFichiersBdd->getFicSource()->getId();
            $ficNbLines= substr_count(file_get_contents($ficInfo), "\n");
            $dateCrea = date("Y-m-d H:i:s");
            $ficFluxId = $ficFlux->getId();
            $ficOrigine = 0;//voir le fichier mroad.ini

            $res = $ficRecapRepo->getFicRecapByCodeAndDate($ficCode, $dateParutionCourant);
            if (count($res) > 0)
            {
                // Arrive ICI - la date de parution existe deja dans fic_recap
                if($this->isChecksumInTab($checksumFichierCourant, $res) === true){
                    // le fichier a deja ete traitÃ©  - pas de nouveau traitement on archive direct dans deja traitÃ©
                    rename($this->repTmp . "/" . $ficNom,$this->repBkpAlreadyTreat . "/" . $ficNom);
                    $this->oLog->info(date("d/m/Y H:i:s : ") . ' - Le fichier "' . $ficNom . '" a deja ete integre, il est maintenant archive dans ('.$this->repBkpAlreadyTreat.')');
                }else{
                    $ficNbLinesUpdate = 0;

                    // 1 - on insere une nouvelle ligne dans fic_recap
                    $ficRecapId = intval($ficRecapRepo->enregistreNewFicRecap($socId,$ficSourceId,$ficCode,$ficNom,$socCode,Null,$dateParutionCourant,$checksumFichierCourant,$ficNbLines,0,$dateCrea,$ficOrigine,$ficFluxId));
                    $this->oLog->info(date("d/m/Y H:i:s : ") . ' - Les donnees du  fichier "' . $ficNom . '" ont ete enregistres dans la table "FIC_RECAP"');

                    // 2 - on met a jour les donnÃ©es dans la table suivi_de_production si la date de parution est valide
                    if ($ficGood){
                        $datasByDateEdition = $suiviDeProductionRepo->getSuiviDeProductionByDateEdition($dateParutionCourant);
                        $ficInTab = $this->getFileInTabIndexed($ficInfo);
                        $dataToUpdate= array();
                        foreach($ficInTab as $ficVal){
                            foreach($datasByDateEdition as $data ){
                                if(($ficVal['libelle_edi'] == $data['libelle_edi']) && ($ficVal['code_route'] == $data['code_route'])){
                                    $delta = false;
                                    if($ficVal['pqt_prev'] != $data['pqt_prev']){
                                        $delta = true;
                                    }
                                    elseif ($ficVal['pqt_eject'] != $data['pqt_eject']){
                                        $delta = true;
                                    }
                                    elseif ($ficVal['ex_prev'] != $data['ex_prev']){
                                        $delta = true;
                                    }
                                    elseif ($ficVal['ex_eject'] != $data['ex_eject']){
                                        $delta = true;
                                    }
                                    if($delta == true){
                                        $ficVal['id'] = $data['id'];
                                        $dataToUpdate[] = $ficVal;
                                    }
                                }
                            }
                        }
                        foreach ($dataToUpdate as $params){
                            $suiviDeProductionRepo->updateDatasAndFicRecap($ficRecapId, $params);
                            $ficNbLinesUpdate++;
                        }
                        $this->oLog->info(date("d/m/Y H:i:s : ") . ' - Les donnees du  fichier "' . $ficNom . '" ont ete mise a jour. '.$ficNbLinesUpdate.' ligne(s) ont ete mise a jour');
                    }
                    
                    // 3 - on MAJ Fic recap
                    if($ficGood){
                        $val['fic_etat_id'] = $ficEtatId = $ficEtatRepo->findOneByCode(0)->getId();//ok
                        $val['eta_msg'] = $ficEtatMsg = $ficEtatRepo->findOneByCode(0)->getLibelle();
                    }else{
                        $val['fic_etat_id'] = $ficEtatId = $ficEtatRepo->findOneByCode(51)->getId();//Fichier vide
                        $val['eta_msg'] = $ficEtatMsg = $ficEtatRepo->findOneByCode(51)->getLibelle();
                    }
                    
                    $ficRecapRepo->updateEtatAndMsgAndDateUpdate($val, $ficRecapId);

                    // 4 -on backup au mm niveau que Tmp
                    rename($this->repTmp . "/" . $ficNom, $this->repBkpLocal. "/" . $ficNom);
                    $this->oLog->info(date("d/m/Y H:i:s : ") . ' - Le fichier "' . $ficNom . '" a ete archive ( '.$this->repBkpLocal.' ) apres avoir ete integre par MAJ "');
                }
            }else{
                /** la date de parution n'est pas  dans fic_recap */
                // 1 - on insert une nouvelle ligne dans fic_recap
                $ficRecapId = intval($ficRecapRepo->enregistreNewFicRecap($socId,$ficSourceId,$ficCode,$ficNom,$socCode,Null,$dateParutionCourant,$checksumFichierCourant,$ficNbLines,0,$dateCrea,$ficOrigine,$ficFluxId));
                $this->oLog->info(date("d/m/Y H:i:s : ") . ' - Les donnees du  fichier "' . $ficNom . '" ont ete enregistres dans la table "FIC_RECAP"');

                // 2 - on insere le fichier  dans suivi de production si la date de parution est valide
                if ($ficGood){
                    $parametres    = array(
                                        '%%NOM_FICHIER%%'		    => $this->repTmp.'/'.$ficNom,
                                        '%%NOM_TABLE%%'		        => $this->dataBaseNom,
                                        '%%SEPARATEUR_CSV%%'	    => ($ficChrgtFichiersBdd->getFormatFic()=='CSV' ? $ficChrgtFichiersBdd->getSeparateur() : ''),
                                        '%%NB_LIGNES_IGNOREES%%'    => $ficChrgtFichiersBdd->getNbLignesIgnorees(),
                    );
                    try {
                    $this->oLog->info('Debut chargement du fichier dans la table "'.$this->dataBaseNom.'"');
                    $chargementFichier  = $suiviDeProductionRepo->loadDataInTable($parametres);
                    if($chargementFichier !== true)
                    {
                        $val['fic_etat_id'] = $ficEtatId = $ficEtatRepo->findOneByCode(99)->getId();//a retraiter a nouveau
                        $val['eta_msg'] = $ficEtatMsg = $ficEtatRepo->findOneByCode(99)->getLibelle();
                        $this->oLog->erreur("Parametres avant Load Data Infile : ".print_r($parametres, true), E_NOTICE, __FILE__, __LINE__);
                        $this->oLog->erreur("Erreur SQL chargement de fichier : ".print_r($chargementFichier['sql'], true), E_USER_WARNING, __FILE__, __LINE__);
                    }else{
                        $val['fic_etat_id'] = $ficEtatId = $ficEtatRepo->findOneByCode(0)->getId();//ok
                        $val['eta_msg'] = $ficEtatMsg = $ficEtatRepo->findOneByCode(0)->getLibelle();
                    }
                    //on set  le fic_recap_id dans suivi de production
                    $suiviDeProductionRepo->setFicRecapId($ficRecapId, $dateParutionCourant);

                    // 3 - on met Ã  jour l'etat
                    $ficRecapRepo->updateEtatAndMsgAndDateUpdate($val, $ficRecapId);
                    }
                    catch (\Exception $ex){
                        $val['fic_etat_id'] = $ficEtatId = $ficEtatRepo->findOneByCode(70)->getId();//traitement arrÃ©tÃ© problÃ©me de requete
                        $val['eta_msg'] = $ficEtatMsg = $ficEtatRepo->findOneByCode(70)->getLibelle();
                        $this->suiviCommand->setMsg($ex->getMessage());
                        $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type($ex->getCode()));
                        $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                        $this->suiviCommand->setEtat("KO");
                        $ficRecapRepo->updateEtatAndMsgAndDateUpdate($val, $ficRecapId);
                        $this->oLog->erreur($ex->getMessage(), $ex->getCode(), $ex->getFile(), $ex->getLine());
                        $this->registerError();
                        if($input->getOption('id_ai') && $input->getOption('id_sh')){
                            $this->registerErrorCron($idAi);
                        }
                    }
                }else{
                    $val['fic_etat_id'] = $ficEtatId = $ficEtatRepo->findOneByCode(51)->getId();//Fichier vide
                    $val['eta_msg'] = $ficEtatMsg = $ficEtatRepo->findOneByCode(51)->getLibelle();
                    
                    // 3 Bis - on met Ã  jour l'etat
                    $ficRecapRepo->updateEtatAndMsgAndDateUpdate($val, $ficRecapId);
                }

                // 4 - on archive un niveau au dessus de tmp
                rename($this->repTmp . "/" . $ficNom, $this->repBkpLocal. "/" . $ficNom);
                $this->oLog->info(date("d/m/Y H:i:s : ") . ' - Le fichier "' . $ficNom . '" a ete archive ( '.$this->repBkpLocal.' ) apres avoir ete integre par insertion "');
            }
            $this->oLog->info(' - Fin integration du fichier "' . $ficNom . '"');
        }
        $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
        $this->endTraitement();
        $this->oLog->info(date("d/m/Y H:i:s : ") . "Fin Integration du Suivi De Production - Commande : " . $this->sNomCommande);
        
        return;
    }

    /**
     * Verifie si un checksum appartient au tableau passÃ©es en param
     * @param $checksum
     * @param $tab
     * @return bool
     */
    public function isChecksumInTab($checksum, $tab)
    {
        foreach ($tab as $elem){
            if($elem['checksum'] == $checksum){
                return true;
            }
        }
        return false;
    }

    /**
     * met le fichier a traiter dans un tableau uindexer avec pour cle le nom des champs e base
     * @param $ficPath
     * @return array
     */
    public function getFileInTabIndexed($ficPath)
    {
        $res = array();
        $handle = fopen($ficPath, "r");
        if ($handle){
            $i = 0;
            while(!feof($handle)){
                $buffer = fgets($handle);
                if (strlen($buffer) > 0){
                    $tmp = explode(";", $buffer);
//                    $res[$i]['date_edi'] = substr($tmp[0], 0,10);
                    $res[$i]['date_edi'] = $tmp[0];
                    $res[$i]['libelle_edi'] = $tmp[1];
                    $res[$i]['code_route'] = $tmp[2];
                    $res[$i]['pqt_prev'] = $tmp[3];
                    $res[$i]['pqt_eject'] = $tmp[4];
                    $res[$i]['ex_prev'] = $tmp[5];
                    $res[$i]['ex_eject'] = trim($tmp[6]);
                    $i++;
                    unset($tmp);
                }
            }
            fclose($handle);
        }
        return $res;
    }

}
