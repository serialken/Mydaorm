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

class IndicateurReperageCommand extends GlobalCommand {

    protected function configure() {
        //php app/console indicateur_reperage --date_debut="2017-01-01" --date_fin="2017-01-31" --env=dev
        $this->sNomCommande = 'indicateur_reperage';
        $this->setName($this->sNomCommande);
        $this->setDescription('Envoie par e-mail des indicateurs de reperage.')
                ->addOption('debut', null, InputOption::VALUE_REQUIRED)
                ->addOption('fin', null, InputOption::VALUE_REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
        $date_debut = $input->getOption('debut');
        $date_fin = $input->getOption('fin');
        $this->oLog->info('Lancement de la generation des indicateurs de reperage pour liberation entre le ' . $date_debut . ' et ' . $date_fin);
        $this->container = $this->getContainer();
        
        try {
            $oEmailService = $this->container->get('email');
            $sUrlBase = $this->container->getParameter('MROAD_VERSION_' . strtoupper($this->container->get('kernel')->getEnvironment()) . '_URL');
            $sUrlBase .= (substr($sUrlBase, -1) == '/') ? '' : '/';
            $em = $this->container->get('doctrine')->getManager();
            $sql = "SELECT count(*) nb_reperage, date_format(f.date_traitement, '%Y-%m-%d') date_creation ,
                    DATEDIFF(date_format(r.date_reponse, '%Y-%m-%d'),date_format(f.date_traitement, '%Y-%m-%d') )  as diff,
                    CASE 
                        WHEN  DATEDIFF(date_format(r.date_reponse, '%Y-%m-%d'),date_format(f.date_traitement, '%Y-%m-%d') )  < 6 THEN 'J_J5'
                        WHEN  DATEDIFF(date_format(r.date_reponse, '%Y-%m-%d'),date_format(f.date_traitement, '%Y-%m-%d') )  IN (6,7,8) THEN 'J6_J8'
                        WHEN  DATEDIFF(date_format(r.date_reponse, '%Y-%m-%d'),date_format(f.date_traitement, '%Y-%m-%d') )  IN (9,10) THEN 'J9_J10'
                        WHEN  DATEDIFF(date_format(r.date_reponse, '%Y-%m-%d'),date_format(f.date_traitement, '%Y-%m-%d') )  > 10 THEN 'J10_'
                        ELSE 'NON_REPONDU'
                    END  as delai
                    FROM  reperage r INNER JOIN fic_recap f ON r.fic_recap1_id = f.id
                    WHERE  r.societe_id = 1 
                    AND date_format(f.date_traitement, '%Y-%m-%d') between '".$date_debut."' AND '".$date_fin."'
                    group by f.date_traitement, delai ";

            $reperages = $em->getConnection()->fetchAll($sql);

            $reperageJours = array();
            for ($i = strtotime($date_debut); $i <= strtotime($date_fin); $i = strtotime(date('Y-m-d', $i) . ' +1 day')) {
                $jour = date('Y-m-d', $i);
                foreach ($reperages as $reperage) {
                    if ($reperage['date_creation'] == $jour) {
                        if ($reperage['delai'] == 'J_J5')
                            $reperageJours[$jour]['J_J5'] = $reperage['nb_reperage'];
                        if ($reperage['delai'] == 'J6_J8')
                            $reperageJours[$jour]['J6_J8'] = $reperage['nb_reperage'];
                        if ($reperage['delai'] == 'J9_J10')
                            $reperageJours[$jour]['J9_J10'] = $reperage['nb_reperage'];
                        if ($reperage['delai'] == 'J10_')
                            $reperageJours[$jour]['J10_'] = $reperage['nb_reperage'];
                        if ($reperage['delai'] == 'NON_REPONDU')
                            $reperageJours[$jour]['NON_REPONDU'] = $reperage['nb_reperage'];
                    }
                } 
            }
            
            // on construit le tableau complet des indicateurs
            $indicateurReperage = array();
            foreach ($reperageJours as $key => $reperageJour) {
                $indicateurReperage[$key]['Date'] = $key;
                $indicateurReperage[$key]['J_J5'] = key_exists('J_J5', $reperageJour) ? $reperageJour['J_J5'] : 0;
                $indicateurReperage[$key]['J6_J8'] = key_exists('J6_J8', $reperageJour) ? $reperageJour['J6_J8'] : 0;
                $indicateurReperage[$key]['J9_J10'] = key_exists('J9_J10', $reperageJour) ? $reperageJour['J9_J10'] : 0;
                $indicateurReperage[$key]['J10_'] = key_exists('J10_', $reperageJour) ? $reperageJour['J10_'] : 0;
                $indicateurReperage[$key]['NON_REPONDU'] = key_exists('NON_REPONDU', $reperageJour) ? $reperageJour['NON_REPONDU'] : 0;
                $indicateurReperage[$key]['TOTAL'] = $indicateurReperage[$key]['J_J5'] + $indicateurReperage[$key]['J6_J8'] + $indicateurReperage[$key]['J9_J10'] + $indicateurReperage[$key]['J10_'] + $indicateurReperage[$key]['NON_REPONDU'];
            }
 
            $indicateurReperage['totaux']['libelle'] = 'TOTAUX'; 
            $indicateurReperage['totaux']['J_J5'] = array_sum($this->array_column($indicateurReperage, 'J_J5'));
            $indicateurReperage['totaux']['J6_J8'] = array_sum($this->array_column($indicateurReperage, 'J6_J8'));
            $indicateurReperage['totaux']['J9_J10'] = array_sum($this->array_column($indicateurReperage, 'J9_J10'));
            $indicateurReperage['totaux']['J10_'] = array_sum($this->array_column($indicateurReperage, 'J10_'));
            $indicateurReperage['totaux']['NON_REPONDU'] = array_sum($this->array_column($indicateurReperage, 'NON_REPONDU'));
            $indicateurReperage['totaux']['TOTAL'] = array_sum($this->array_column($indicateurReperage, 'TOTAL'));
 
            $indicateurReperage['taux']['libelle'] = 'Taux (%)'; 
            $indicateurReperage['taux']['J_J5'] = number_format($indicateurReperage['totaux']['J_J5']/$indicateurReperage['totaux']['TOTAL'],4)*100 ;
            $indicateurReperage['taux']['J6_J8'] = number_format($indicateurReperage['totaux']['J6_J8']/$indicateurReperage['totaux']['TOTAL'],4)*100 ;
            $indicateurReperage['taux']['J9_J10'] = number_format($indicateurReperage['totaux']['J9_J10']/$indicateurReperage['totaux']['TOTAL'],4)*100 ;
            $indicateurReperage['taux']['J10_'] = number_format($indicateurReperage['totaux']['J10_']/$indicateurReperage['totaux']['TOTAL'],4)*100 ;
            $indicateurReperage['taux']['NON_REPONDU'] = number_format($indicateurReperage['totaux']['NON_REPONDU']/$indicateurReperage['totaux']['TOTAL'],4)*100 ;
            $indicateurReperage['taux']['TOTAL'] = 100;
            

            if (!empty($indicateurReperage)) {
                $codeSociete = "LI";
                $sFileName = "indicateur_reperage_".$codeSociete."_".$date_debut."_au_".$date_fin.".csv";
                $sBaseFolder = $this->container->getParameter('REP_FICHIERS_CMD');
                $sTmpFolder = (substr($sBaseFolder, -1)) == DIRECTORY_SEPARATOR ? $sBaseFolder . $this->container->getParameter('SOUSREP_FICHIERS_TMP') : $sBaseFolder . DIRECTORY_SEPARATOR . $this->container->getParameter('SOUSREP_FICHIERS_TMP')."/INDICATEURS";
                
                $sCsvFile = (substr($sTmpFolder, -1)) == DIRECTORY_SEPARATOR ? $sTmpFolder . $sFileName : $sTmpFolder . DIRECTORY_SEPARATOR . $sFileName;
                // Ecriture du fichier
                $fp = fopen($sCsvFile, 'w');
                // Titres de colonnes
                $aTitres = array(
                    'Date',
                    'J à J+5',
                    'J+6 à J+8',
                    'J+9 à J+10',
                    '> J10',
                    'Non répondu',
                    'Nombre de repérage',
                );
                
                array_unshift($indicateurReperage, $aTitres);
                foreach ($indicateurReperage as $sField) {
                    $sField = array_map('utf8_decode', $sField);
                    $sField = str_replace($this->container->getParameter('CRON_MAIL_RECAP_PAIE_TOURNEE_CSV_SEPARATOR'), '-', $sField);
                    $sLigne = implode($this->container->getParameter('CRON_MAIL_RECAP_PAIE_TOURNEE_CSV_SEPARATOR'), $sField);
                    $sLigne = str_replace("\n", ' ', $sLigne);
                    $sLigne = str_replace("\r", ' ', $sLigne);
                    $sLigne .= "\n";
                    fputs($fp, $sLigne);
                }
                
                fclose($fp);

                // Envoi du mail
                if (file_exists($sCsvFile)) {
                    $aMailDatas = array(
                        //'sMailDest' => $this->container->getParameter('CRON_MAIL_RECAP_PAIE_TOURNEE_DEST_ADRESSES'),
                        'sMailDest' => 'tidiane.camara@amaury.com',
                        'sSubject' => 'Indicateur reperage',
                        'cc' => array($this->container->getParameter('EMAIL_SERVICE_DEFAULT_REPLYTO')),
                        'sContentHTML' => '<strong>Indicateurs des repérages pour </strong><br/><br/>
                        Bonjour <BR> Veuillez trouver ci-joint les indicateurs de repérage pour Libération.<br/>
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
                // @Todo: Envoyer un e-mail pour dire qu'aucun enregistrement n'a été trouvé
                $this->oLog->info('Aucun enregistrement disponible.');
            }
        } catch (DBALException $DBALException) {
            $this->oLog->erreur($DBALException->getMessage(), $DBALException->getCode(), $DBALException->getFile(), $DBALException->getLine());
        }

        $this->oLog->info('Fin de la generation des indicateurs de repérage');
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
