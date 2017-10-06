<?php

/**
 * @author tcamara
 * Cette commande est provisoire et valable pour liberation
 * a terme les indicateurs seront geres dans BO
 * Aucune dependance avec le reste de l'application 
 * ce fichier pourra etre supprime une fois  BO en place  
 *  
 */

namespace Ams\ReportingBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ams\SilogBundle\Command\GlobalCommand;

class IndicateurReclamCommand extends GlobalCommand {

    protected function configure() {
        // Pour executer, faire : php app/console indicateur_reclamation --date_debut="2017-01-01" --date_fin="2017-01-31" --env=dev
        $this->sNomCommande = 'indicateur_reclamation';
        $this->setName($this->sNomCommande);
        $this->setDescription('Envoie par e-mail des indicateurs de reclamation.')
                ->addOption('debut', null, InputOption::VALUE_REQUIRED)
                ->addOption('fin', null, InputOption::VALUE_REQUIRED)

        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
        $date_debut = $input->getOption('debut');
        $date_fin = $input->getOption('fin');
        $this->oLog->info('Lancement de la generation des indicateurs de réclamation pour liberation entre le ' . $date_debut . ' et ' . $date_fin);
        $this->container = $this->getContainer();

        try {
            $oEmailService = $this->container->get('email');
            $sUrlBase = $this->container->getParameter('MROAD_VERSION_' . strtoupper($this->container->get('kernel')->getEnvironment()) . '_URL');
            $sUrlBase .= (substr($sUrlBase, -1) == '/') ? '' : '/';
            $em = $this->container->get('doctrine')->getManager();
            $sql = "SELECT count(*) nb_reclam, date_format(c.date_creat, '%Y-%m-%d') date_creation ,
                    DATEDIFF(date_format(c.date_reponse, '%Y-%m-%d'),date_format(c.date_creat, '%Y-%m-%d') )  as diff,
                    CASE 
                        WHEN  DATEDIFF(date_format(c.date_reponse, '%Y-%m-%d'),date_format(c.date_creat, '%Y-%m-%d') )  IN (0,1,2) THEN 'J_J2'
                        WHEN  DATEDIFF(date_format(c.date_reponse, '%Y-%m-%d'),date_format(c.date_creat, '%Y-%m-%d') )  IN (3,4) THEN 'J3_J4'
                        WHEN  DATEDIFF(date_format(c.date_reponse, '%Y-%m-%d'),date_format(c.date_creat, '%Y-%m-%d') )  IN (5,6) THEN 'J5_J6'
                        WHEN  DATEDIFF(date_format(c.date_reponse, '%Y-%m-%d'),date_format(c.date_creat, '%Y-%m-%d') )  > 6 THEN 'J7_'
                        ELSE 'NON_REPONDU'
                    END  as delai
                    FROM  crm_detail c
                    INNER JOIN crm_demande d ON c.crm_demande_id =  d.id 
                    WHERE d.crm_categorie_id = 1  AND societe_id = 1
                   
                    AND date_format(date_creat, '%Y-%m-%d') between'" . $date_debut . "' AND '" . $date_fin . "'
                    group by date_creation, delai";

            $reclamations = $em->getConnection()->fetchAll($sql);

            $reclamJours = array();
            for ($i = strtotime($date_debut); $i <= strtotime($date_fin); $i = strtotime(date('Y-m-d', $i) . ' +1 day')) {
                $jour = date('Y-m-d', $i);
                foreach ($reclamations as $reclamation) {
                    if ($reclamation['date_creation'] == $jour) {
                        if ($reclamation['delai'] == 'J_J2')
                            $reclamJours[$jour]['J_J2'] = $reclamation['nb_reclam'];
                        if ($reclamation['delai'] == 'J3_J4')
                            $reclamJours[$jour]['J3_J4'] = $reclamation['nb_reclam'];
                        if ($reclamation['delai'] == 'J5_J6')
                            $reclamJours[$jour]['J5_J6'] = $reclamation['nb_reclam'];
                        if ($reclamation['delai'] == 'J7_')
                            $reclamJours[$jour]['J7_'] = $reclamation['nb_reclam'];
                        if ($reclamation['delai'] == 'NON_REPONDU')
                            $reclamJours[$jour]['NON_REPONDU'] = $reclamation['nb_reclam'];
                    }
                }
            }
            

           // on construit le tableau complet des indicateurs
            $indicateurReclam = array();
            foreach ($reclamJours as $key => $reclamJour) {
                $indicateurReclam[$key]['Date'] = $key;
                $indicateurReclam[$key]['J_J2'] = key_exists('J_J2', $reclamJour) ? $reclamJour['J_J2'] : 0;
                $indicateurReclam[$key]['J3_J4'] = key_exists('J3_J4', $reclamJour) ? $reclamJour['J3_J4'] : 0;
                $indicateurReclam[$key]['J5_J6'] = key_exists('J5_J6', $reclamJour) ? $reclamJour['J5_J6'] : 0;
                $indicateurReclam[$key]['J7_'] = key_exists('J7_', $reclamJour) ? $reclamJour['J7_'] : 0;
                $indicateurReclam[$key]['NON_REPONDU'] = key_exists('NON_REPONDU', $reclamJour) ? $reclamJour['NON_REPONDU'] : 0;
                $indicateurReclam[$key]['TOTAL'] = $indicateurReclam[$key]['J_J2'] + $indicateurReclam[$key]['J3_J4'] + $indicateurReclam[$key]['J5_J6'] + $indicateurReclam[$key]['J7_'] + $indicateurReclam[$key]['NON_REPONDU'];
            }
        
            // calcul les totaux par colonnes
            $indicateurReclam['totaux']['libelle'] = 'TOTAUX'; 
            $indicateurReclam['totaux']['J_J2'] = array_sum($this->array_column($indicateurReclam, 'J_J2'));
            $indicateurReclam['totaux']['J3_J4'] = array_sum($this->array_column($indicateurReclam, 'J3_J4'));
            $indicateurReclam['totaux']['J5_J6'] = array_sum($this->array_column($indicateurReclam, 'J5_J6'));
            $indicateurReclam['totaux']['J7_'] = array_sum($this->array_column($indicateurReclam, 'J7_'));
            $indicateurReclam['totaux']['NON_REPONDU'] = array_sum($this->array_column($indicateurReclam, 'NON_REPONDU'));
            $indicateurReclam['totaux']['TOTAL'] = array_sum($this->array_column($indicateurReclam, 'TOTAL'));
            // calcul des taux de réponse par colonne
            $indicateurReclam['taux']['libelle'] = 'Taux (%)'; 
            $indicateurReclam['taux']['J_J2'] = number_format($indicateurReclam['totaux']['J_J2']/$indicateurReclam['totaux']['TOTAL'],4)*100 ;
            $indicateurReclam['taux']['J3_J4'] = number_format($indicateurReclam['totaux']['J3_J4']/$indicateurReclam['totaux']['TOTAL'],4)*100 ;
            $indicateurReclam['taux']['J5_J6'] = number_format($indicateurReclam['totaux']['J5_J6']/$indicateurReclam['totaux']['TOTAL'],4)*100 ;
            $indicateurReclam['taux']['J7_'] = number_format($indicateurReclam['totaux']['J7_']/$indicateurReclam['totaux']['TOTAL'],4)*100 ;
            $indicateurReclam['taux']['NON_REPONDU'] = number_format($indicateurReclam['totaux']['NON_REPONDU']/$indicateurReclam['totaux']['TOTAL'],4)*100 ;
            $indicateurReclam['taux']['TOTAL'] = 100;
          
            
            
            if (!empty($indicateurReclam)) { 
                $codeSociete = "LI";
                $sFileName = "indicateur_reclamation_".$codeSociete."_".$date_debut."_au_".$date_fin.".csv";
                $sBaseFolder = $this->container->getParameter('REP_FICHIERS_CMD');
                $sTmpFolder = (substr($sBaseFolder, -1)) == DIRECTORY_SEPARATOR ? $sBaseFolder . $this->container->getParameter('SOUSREP_FICHIERS_TMP') : $sBaseFolder . DIRECTORY_SEPARATOR . $this->container->getParameter('SOUSREP_FICHIERS_TMP')."/INDICATEURS";

                $sCsvFile = (substr($sTmpFolder, -1)) == DIRECTORY_SEPARATOR ? $sTmpFolder . $sFileName : $sTmpFolder . DIRECTORY_SEPARATOR . $sFileName;
                // Ecriture du fichier
                $fp = fopen($sCsvFile, 'w');
                // Titres de colonnes
                $aTitres = array(
                    'Date',
                    'J à J+2',
                    'J+3 à J+4',
                    'J+5 à J+6',
                    '> J6',
                    'Non répondu',
                    'Nombre de reclamation',
                );
                
                array_unshift($indicateurReclam, $aTitres);
                foreach ($indicateurReclam as $sField) {
                    $sField = array_map('utf8_decode', $sField);
                    $sField = str_replace($this->container->getParameter('CRON_MAIL_RECAP_PAIE_TOURNEE_CSV_SEPARATOR'), '-', $sField);
                    $sLigne = implode($this->container->getParameter('CRON_MAIL_RECAP_PAIE_TOURNEE_CSV_SEPARATOR'), $sField);
                    $sLigne = str_replace("\n", ' ', $sLigne);
                    $sLigne = str_replace("\r", ' ', $sLigne);
                    $sLigne .= "\n";
                    fputs($fp, $sLigne);
                }
                fclose($fp);
                
                if (file_exists($sCsvFile)) {
                    $aMailDatas = array(
                        //'sMailDest' => $this->container->getParameter('CRON_MAIL_RECAP_PAIE_TOURNEE_DEST_ADRESSES'),
                        'sMailDest' => 'tidiane.camara@amaury.com',
                        'sSubject' => 'Indicateur reclamation',
                      //  'cc' => array($this->container->getParameter('EMAIL_SERVICE_DEFAULT_REPLYTO')),
                        'sContentHTML' => '<strong>Indicateurs des réclamations pour </strong><br/><br/>
                        Bonjour <BR> Veuillez trouver ci-joint les indicateurs de réclamation pour Libération.<br/>
                        Cordialement<br/>'
                    );
                    
                    $sTemplate = 'AmsDistributionBundle:Emails:mail_recap_paie_tournee.mail.twig';
                    $aMailDatas['aAttachment'] = array(
                        'sFichier' => $sCsvFile,
                        'sNomFichier' => $sFileName,
                        'sMimeType' => 'text/csv',
                    );
                    
                    if ($oEmailService->send($sTemplate, $aMailDatas)) {
                        $this->oLog->info('Le fichier ' . $sFileName . ' a été envoyé à  ' . $aMailDatas['sMailDest']);
                    }

                } 
            } else {
                $this->oLog->info('Aucun enregistrement disponible.');
            }
        } catch (DBALException $DBALException) {
            $this->oLog->erreur($DBALException->getMessage(), $DBALException->getCode(), $DBALException->getFile(), $DBALException->getLine());
        }

        $this->oLog->info('Fin de la generation des indicateurs de réclamation');
        return;
    }
    
    
    
    /** 
     *  array_column en php 5.4
     * @param array $input
     * @param type $column_key
     * @param type $index_key
     * @return array
     */
    private  function array_column(array $input, $column_key, $index_key = null) {
        $result = array();
        foreach ($input as $k => $v)
            $result[$index_key ? $v[$index_key] : $k] = $v[$column_key];
        return $result;
    }


}
