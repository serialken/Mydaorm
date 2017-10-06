<?php

namespace Ams\DistributionBundle\Command;

//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ams\SilogBundle\Command\GlobalCommand;
use Doctrine\DBAL\DBALException;
use Ams\DistributionBundle\Entity\FeuillePortage;
use Ams\AdresseBundle\Controller\ExportController;
use Symfony\Component\Process\Process;

/**
 * Traitement import geoconcept
 * @author kjean-baptiste
 *
 */
class ImportGeoconceptCommand extends GlobalCommand {

    private $donnees;
    protected $idAi;
    protected $idSh;

    protected function configure() {
        $this->sNomCommande = 'import_geoconcept';
        $sJourATraiterMinParDefaut = "J-6";
        $sJourATraiterMaxParDefaut = "J-1";
        $this->setName($this->sNomCommande);
        // Pour executer, faire : php app/console import_geoconcept --id_sh=cron_test --id_ai=1 --env=dev 
        $this->setDescription('Traitement import geoconcept.')
            ->addOption('force_req', NULL, InputOption::VALUE_OPTIONAL, "L'ID de requete optimisee a appliquer.", '')
            ->addOption('id_sh',null, InputOption::VALUE_REQUIRED, 'Libelle du CRON')
            ->addOption('id_ai',null, InputOption::VALUE_REQUIRED, 'Id du CRON')
//             ->addArgument('jour_a_traiter_min', InputArgument::OPTIONAL, 'Jour a traiter Min. Format : J<+Numerique> (Expl : J+1 ou J+2 ou J ...)', $sJourATraiterMinParDefaut)
//             ->addArgument('jour_a_traiter_max', InputArgument::OPTIONAL, 'Jour a traiter Max. Format : J<+Numerique> (Expl : J+1 ou J+2 ou J ...)', $sJourATraiterMaxParDefaut)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->donnees = array();
        parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
        if($input->getOption('id_sh')){
            $this->idSh = $input->getOption('id_sh');
        }
        if($input->getOption('id_ai')){
            $this->idAi = $input->getOption('id_ai');
        }
        if($input->getOption('id_ai') && $input->getOption('id_sh')){
            $this->associateToCron($this->idAi,$this->idSh);
        }
        $this->oLog->info("Debut traitement " . $this->sNomCommande);
        $em = $this->getContainer()->get('doctrine')->getManager();
        $iReq = $input->getOption('force_req');

        $this->oLog->info("*************************** DEMARRAGE APPLICATION OPTIM ***************************");
        $this->oLog->info("*************************** " . date('Y-m-d H:i:s') . " ***************************");

        if ($iReq > 0) {
            $this->oLog->info("Demande d'optimisation forcee de la requete " . $iReq);
            // Récupération des requetes d'exports à évaluer pour l'application
            $aListeExports = $em->getRepository('AmsAdresseBundle:RequeteExport')->listeReqAEvaluer($iReq);
            $iDelai = 0;
            
            // Mode d'opération
            $sMode = 'manuel';
        } else {
            $iDelai = - 2;

            // Récupération des requetes d'exports à évaluer pour l'application
            $aListeExports = $em->getRepository('AmsAdresseBundle:RequeteExport')->listeReqAEvaluer();
            
            // Mode d'opération
            $sMode = 'auto';
        }
        if (empty($aListeExports)) {
            $this->oLog->info("Pas de requete d'optimisation à appliquer.");
            return true;
        }

        // On boucle dans la liste des requete d'export à éventuellement appliquer
        foreach ($aListeExports as $data) {

            $sCodeTournee = "";
            $aImportIds = array();
            $aImportTmp = array();
            $aListeCodeTournees = $aReqExpId = $aImportTD = array(); // Contient la liste des tournées qui seront supprimées de TD
            // Récupération de la liste des tournées pour suppression dans TD
            // Détermination du périmètre effectif
            $sJoursType = $data['jour_type']; // Tous les jours sélectionnés par l'opérateur
            $sDateTime = strtotime($data['date_application']);
            $sDateApplication = date('Y-m-d', $sDateTime);

            if (!empty($sJoursType)) {
                $aPermimEffectInfo = ExportController::defPerimetreEffectif(
                                (int) $data['id'], $data['liste_tournees'], $sJoursType, $data['optim_info'], $sDateApplication, $em, $iDelai
                );

                $aListeTourneesJour = $aPermimEffectInfo['tournees_jour'];
                $iReqExpId = $aPermimEffectInfo['req_exp_id'];
                
                // On loggue les jours qui ne pourront être appliqués
                $aHistJoursExclus = array();
                if (!empty($aPermimEffectInfo['jours_exclus_id'])) {
                    foreach ($aPermimEffectInfo['jours_exclus_id'] as $iJourExcluId) {
                        $oJourExclu = $em->getRepository('AmsReferentielBundle:RefJour')->find($iJourExcluId);
                        $aHistJoursExclus[] = $oJourExclu->getCode();
                        $sMsgLogJourExclu = 'Le ' . $oJourExclu->getLibelle() . ' devra etre applique mais ne pourra pas l\'etre aujourd\'hui pour la requete export ';
                        $this->oLog->info($sMsgLogJourExclu . $data['id']);
                    }
                }

                // On prépare la liste des tournées (mtj code) pour l'utilisation dans SQL 
                if (!empty($aListeTourneesJour)) {
                    foreach ($aListeTourneesJour as $sTourneeJourCode) {
                        if (!in_array($sTourneeJourCode, $aListeCodeTournees)) {
                            $sCodeTournee .= ($sCodeTournee == "") ? '"' . $sTourneeJourCode . '"' : ',"' . $sTourneeJourCode . '"';
                            $aListeCodeTournees[] = $sTourneeJourCode;
                        }
                    }
                } else {
                    $this->oLog->info("Pas d'application de a passer pour la requete export " . $data['id']);
                    continue;
                }
            }
            // Récupérations des lignes de l'import correspondant aux points des tournées optimisées
            $aImport = $em->getRepository('AmsAdresseBundle:ImportGeoconcept')->getApplicationOptim($iReqExpId, $sDateApplication, $sCodeTournee);
            $aTourneesPb = $this->tourneeNonExistente($aImport, $aPermimEffectInfo['tournees_jour']);
            if (!empty($aTourneesPb)) {
                $this->oLog->info("La requete d'export " . $data['id'] . " compte " . count($aTourneesPb) . " tournees non creees dans le referentiel ou simplement vides.");
                foreach ($aTourneesPb as $sTourneeVide) {
                    $this->oLog->info("La tournee " . $sTourneeVide . " est vide ou non creee.");
                }
            }
            $aStats = $this->getApplicationStats($aPermimEffectInfo, $aTourneesPb);

            /** RECUPERATION DES "JOUR ID" PAR REQUETE_EXPORT_ID * */
            $aReqExpId[$iReqExpId] = $aPermimEffectInfo['jours_id'];

            // Aucune ligne correspondante dans l'import
            if (empty($aImport)) {
                $this->oLog->info("Aucune ligne a importer trouvee aujourd'hui.");
                return true;
            }

            // On parcourt les lignes à importer.
            foreach ($aImport as $aImpData) {
                // Ligne déjà traitée ?
                if (!in_array($aImpData['id'], $aImportIds)) {
                    $aImportIds[] = $aImpData['id'];
                }

                $ordre = !is_null($aImpData['point_livraison_ordre']) ? $aImpData['point_livraison_ordre'] : "NULL";

                /** RECUPERATION DATA POUR TOUTES LES TOURNEES A INSERER DANS TOURNEE DETAIL * */
                $this->seedInsertDataTourneeDetail($aImportTD, $aImpData);

                $aImportTmp[] = array(
                    'abonne_soc_id' => is_null($aImpData['abonne_soc_id']) ? "NULL" : (int) $aImpData['abonne_soc_id'],
                    'point_livraison_id' => is_null($aImpData['point_livraison_id']) ? "NULL" : (int) $aImpData['point_livraison_id'],
                    'produit_id' => is_null($aImpData['produit_id']) ? "NULL" : $aImpData['produit_id'],
                    'jour_id' => is_null($aImpData['jour_id']) ? "NULL" : $aImpData['jour_id'],
                    'ordre' => $ordre,
                    'tournee_jour_id' => is_null($aImpData['mtj_id']) ? "NULL" : (int) $aImpData['mtj_id'],
                    'date_application' => "'" . $data['date_application'] . "'"
                );
            }

            /**  SUPRESSION DANS TOURNEE DETAIL  * */
            if (!empty($sCodeTournee)) {
                $delete = $em->getRepository('AmsAdresseBundle:TourneeDetail')->deleteTourneeByModeleTournee($sCodeTournee);
                if ($delete)
                    $this->oLog->info("Suppression data tournee detail - requete export " . $data['id'] . " : SUCCESS");
            }
            else {
                $this->oLog->info("Pas de suppression des tournées dans TD");
            }

            // Suppression des doublons en dehors du périm_tre effectif
            $this->deleteDoubloonByAboSocJourId($aReqExpId);


            // Insertion des enregistrement dans Tournee Detail
            foreach ($aImportTD as $aInsertTD) {
                $em->getRepository('AmsAdresseBundle:TourneeDetail')->insertTourneeDetail($aInsertTD);
            }
            $this->oLog->info("Insertion data tournee detail requete export " . $data['id'] . " : SUCCESS");

            /**  MODIFICATION CLIENT A SERVIR  * */
            // CREATION TABLE TEMPORAIRE
            $aChampsSyntaxe = array(
                "`abonne_soc_id` INT unsigned NOT NULL",
                "`point_livraison_id` INT NOT NULL",
                "`produit_id` INT NOT NULL",
                "`jour_id` INT NULL",
                "`ordre` INT NULL",
                "`tournee_jour_id` INT NULL",
                "`date_application` date NULL",
                "`import_id` INT unsigned NOT NULL",
            );

            $retour = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->creerTableTemp('import_geoconcept', $aChampsSyntaxe, $aImportTmp);

            $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->updateCaslOptim($retour['nom_table']);
            $this->oLog->info("Modification client a servir requete export " . $data['id'] . ": SUCCESS");

            // Suppression des lignes importées
            if (!empty($aImportIds)) {
                $em->getRepository('AmsAdresseBundle:ImportGeoconcept')->clearImports(NULL, $aImportIds);
            }

            // Enregistrement de l'historique
            $sOptimInfo = $this->updateHistorique($data['optim_info'], $aStats, $aTourneesPb, $aHistJoursExclus, $sMode, $retour);
            $oReqExp = $em->getRepository('AmsAdresseBundle:RequeteExport')->find($data['id']);
            $oReqExp->setOptimInfo($sOptimInfo);

            // Changement de statut pour la requete
            if (count(json_decode($sOptimInfo, true)) == count(json_decode($sJoursType, true))) {
                $oReqExp->setStatut('A');
            } else {
                $oReqExp->setStatut('E');
            }

            $em->flush();
        }
        
        $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
        $this->endTraitement();
        $this->oLog->info("Fin traitement " . $this->sNomCommande);
    }

    private function deleteDoubloonByAboSocJourId($aReqIdJourId) {
        foreach ($aReqIdJourId as $reqId => $jourId) {
            if($input->getOption('id_ai') && $input->getOption('id_sh')){
                $sCmd = 'php ' . $this->getContainer()->get('kernel')->getRootDir() . '/console management_doublon --id_sh='.$this->idSh.'  --id_ai='.$this->idAi.'    --env ' . $this->getContainer()->getParameter("kernel.environment") . ' --reqExpId ' . $reqId . ' --jourId ' . implode(',', $jourId);
            }else{
                $sCmd = 'php ' . $this->getContainer()->get('kernel')->getRootDir() . '/console management_doublon --env ' . $this->getContainer()->getParameter("kernel.environment") . ' --reqExpId ' . $reqId . ' --jourId ' . implode(',', $jourId);
            }
            $process = new Process($sCmd);
            $process->run();
            if (!$process->isSuccessful()) {
                if($input->getOption('id_ai') && $input->getOption('id_sh')){
                    $this->suiviCommand->setMsg("Une erreur s'est produite lors de la suppression des doublons en dehors du périmetre effectif");
                    $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
                    $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                    $this->suiviCommand->setEtat("KO");
                    $this->registerError();
                    if($input->getOption('id_ai') && $input->getOption('id_sh')){
                        $this->registerErrorCron($this->idAi);
                    }
                }
                throw new \RuntimeException($process->getErrorOutput());
            } else
                echo 'Suppression des doublons "num_abonne_id,jour_id" dans tournee_detail realiser avec succes pour reqId => ' . $reqId . "\n";
        }
    }

    /**
     * Alimente les lignes du tableau avant insertion dans TD
     * @param array $aImportTD Le tableau des points à insérer dans TD
     * @param array $dataInsert Les données à insérer
     */
    private function seedInsertDataTourneeDetail(&$aImportTD, $dataInsert) {
        $dataInsert['modele_tournee_jour_code'] = $dataInsert['code'];
        $dataInsert['jour_id'] = $dataInsert['mtj_jour_id'];
        $dataInsert['num_abonne_id'] = $dataInsert['abonne_soc_id'];

        // Ajout des informations de traçabilité
        $dataInsert['source_modification'] = 'optim - req ID ' . $dataInsert['requete_export_id'];
        $dataInsert['date_modification'] = date_format(new \Datetime(), 'Y-m-d H:i:s');
        $dataInsert['ordre_optimisation'] = $dataInsert['ordre'] = $dataInsert['point_livraison_ordre'];

        // 
        $dataInsert['point_livraison_id'] = ((int) $dataInsert['point_livraison_id'] < 1) ? NULL : (int) $dataInsert['point_livraison_id'];

        unset($dataInsert['id']);
        unset($dataInsert['abonne_soc_id']);
        unset($dataInsert['mtj_jour_id']);
        unset($dataInsert['mtj_id']);
        unset($dataInsert['requete_export_id']);
        unset($dataInsert['point_livraison_ordre']);
        unset($dataInsert['ordre_dans_arret']);
        unset($dataInsert['code']);
        unset($dataInsert['code_tournee']);
        unset($dataInsert['duree_livraison']);
        unset($dataInsert['requete']);
        unset($dataInsert['depot']);
        unset($dataInsert['date_import']);
        unset($dataInsert['date_optim']);
        unset($dataInsert['date_application_optim']);
        unset($dataInsert['fusion_soc_id']);
        unset($dataInsert['date_appliq']);
        unset($dataInsert['modele_tournee_jour_id']);
        unset($dataInsert['produit_id']);
        unset($dataInsert['jour_applique']);
        unset($dataInsert['req_exp_id']);
        unset($dataInsert['date_application']);
        unset($dataInsert['import_id']);
        $aImportTD[] = $dataInsert;
    }

    /**
     * Méthode de gestion de l'historique des applications au format JSON
     * @param string $sOptimInfo L'historique de base (déjà enregistré en BDD)
     * @param array $aHistorique Le tableau contenant la liste des tournées appliquées pour chaque jour
     * @param array $aTourneesPB Le tableau des tournées vides ou non créées
     * @param array $aHistJoursExclus Le tableau des jours non appliquables
     * @param string $sMode Le mode d'exécution de l'application d'optim auto|manuel
     * @param array $aTmpTable Tableau d'informations sur la table temporaire créée pour la MAJ de CAS
     * @return string Le nouvel historique au format JSON
     */
    public function updateHistorique($sOptimInfo, $aHistorique, $aTourneesPB, $aHistJoursExclus, $sMode, $aTmpTable) {
        if (!empty($sOptimInfo)) {
            $aOptimInfo = json_decode($sOptimInfo, true);
        } else {
            $aOptimInfo = array();
        }
        // On ajoute les informations des jours appliqués
        foreach ($aHistorique as $sJourCode => $aJourApplique) {
            $aOptimInfo[date('Y-m-d H:i:s')] = array(
                'mode' => $sMode,
                'tournees' => $aJourApplique,
                'tournees_inexistantes' => $aTourneesPB,
                'jours_non_appliquables' => $aHistJoursExclus,
                'table_tmp' => $aTmpTable
            );
        }

        return json_encode($aOptimInfo);
    }

    /**
     * RECUPERATION DES TOURNEES VIDEES OU NON CREEES DANS MODEL_TOURNEE
     * @param array $aImport tableau de donnée à importer
     * @param array $aCodeTournee tableau de tournee du perimètre effectif
     * @return array $aCodeTournee tabelau contenant des tournées
     */
    private function tourneeNonExistente($aImport, $aCodeTournee) {
        foreach ($aImport as $data) {
            if (in_array($data['code'], $aCodeTournee)) {
                $key = array_search($data['code'], $aCodeTournee);
                unset($aCodeTournee[$key]);
            }
        }
        return $aCodeTournee;
    }

    /**
     * Renvoit un tableau de statistiques sur l'application de l'optimisation
     * @param array $aPerimetre Le périmètre effectif
     * @param array $aTourneesPb Le tableau des tournées à problème
     * @return array $aStats Le tableau contenant les métriques
     */
    private function getApplicationStats($aPerimetre, $aTourneesPb) {
        $aStats = array();

        // Récupération des informations pour l'historique
        $aHistorique = array();
        foreach ($aPerimetre['tournees_jour'] as $sTourneeJr) {
            if (!in_array($sTourneeJr, $aTourneesPb)) {
                $sCodeJour = substr($sTourneeJr, strlen($sTourneeJr) - 2, 2);
                $aHistorique[$sCodeJour][] = $sTourneeJr;
            }
        }

        $aStats['general'] = array(
            'nbTournees' => count($aPerimetre['tournees_jour']) - count($aTourneesPb),
            'historique' => $aHistorique
        );

        return $aStats;
    }

}
