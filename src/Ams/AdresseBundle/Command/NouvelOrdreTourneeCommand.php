<?php

namespace Ams\AdresseBundle\Command;

//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ams\SilogBundle\Command\GlobalCommand;
use Doctrine\DBAL\DBALException;

/**
 * 
 * Réinitialisation de l'ordre des tournées contenues dans "tournee_detail"
 * Parametre : 
 *          Le code de la tournee visee
 * 
 * Exemple de commande : php app/console reset_ordre_model_tournee <CODE_DE_TOURNEE_JOUR> --env=dev
 * 
 * @author maadelise
 *
 */
class NouvelOrdreTourneeCommand extends GlobalCommand {

    protected function configure() {
        $this->sNomCommande = 'reset_ordre_model_tournee';

        $this->setName($this->sNomCommande);
        // Pour executer, faire : php app/console reset_ordre_model_tournee  <CODE_DE_TOURNEE_JOUR>  Expl : php app/console reset_ordre_model_tournee 029NKB013DI
        $this
                ->setDescription('Réinitialise l\'ordre des arrets dans un model de tournee de la table tournee_detail')
                ->addArgument('mtj', InputArgument::REQUIRED, 'Le code Modele Tournee Jour de la tournee visee', NULL)
                ->addArgument('livres_uniquement', InputArgument::OPTIONAL, 'Si = abos_reels calcule le nouvel ordre sans prendre en compte les abonnés jamais livres (cas des reperages)', FALSE)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs

        $oAlertLogService = $this->getContainer()->get('alertlog');
        /* @var $oAlertLogService \Ams\SilogBundle\Services\Alerts */

        $sMTJCode = $input->getArgument('mtj');
        $bUniquementLivres = $input->getArgument('livres_uniquement') == 'abos_reels' ? TRUE : FALSE;

        $em = $this->getContainer()->get('doctrine')->getManager();

        // Vérification du MTJ Code
        $sDate = date("Y-m-d H:i:s");
        try {
            
            $sLogMsg = 'Début de réinitialisation de l\'ordre de la tournée ' . $sMTJCode;
            $oAlertLogService->logEvent(
                'carto', // Peut être une de ces valeurs: carto|alim|envt|docs|paie|crm
                'info', // Peut être une de ces valeurs: debug|info|notice|warning|error|critical|alert|emergency
                $sLogMsg, // Le message d'erreur
                array(date("d/m/Y H:i:s : ")));
        
            $oMtj = $em->getRepository('AmsModeleBundle:ModeleTourneeJour')->findByCodeDateValid($sMTJCode, $sDate);

            // Redressement du modele dans TD
            if ($em->getRepository('AmsAdresseBundle:TourneeDetail')->resetOrdreTournee($sMTJCode, $bUniquementLivres)) {
                $sMsg = "L'ordre de la tournée $sMTJCode a été redresse.";

                $oAlertLogService->logEvent(
                        'carto', // Peut être une de ces valeurs: carto|alim|envt|docs|paie|crm
                        'info', // Peut être une de ces valeurs: debug|info|notice|warning|error|critical|alert|emergency
                        $sMsg, // Le message d'erreur
                        array(date("d/m/Y H:i:s : ")));

                echo $sLogMsg;
            } else {
                $sErrMsg = 'Aucune modification d\'ordre n\'a ete faite sur la tournee ' . $sMTJCode;
                 $oAlertLogService->logEvent(
                    'carto', // Peut être une de ces valeurs: carto|alim|envt|docs|paie|crm
                    'error', // Peut être une de ces valeurs: debug|info|notice|warning|error|critical|alert|emergency
                    $sErrMsg, // Le message d'erreur
                    $oAlertLogService->getErrorData("DB_ERR_NOT_UPDATED", 'COMMAND/reset_ordre_model_tournee', __FILE__, __LINE__, array()));
                 
                echo $sErrMsg;
            }
        } catch (\Doctrine\ORM\NoResultException $e) {
//            var_dump($e);
            $sErrMsg = 'Aucune tournee en cours de validite trouvee avec le code de tournee ' . $sMTJCode;
            $oAlertLogService->logEvent(
                    'carto', // Peut être une de ces valeurs: carto|alim|envt|docs|paie|crm
                    'error', // Peut être une de ces valeurs: debug|info|notice|warning|error|critical|alert|emergency
                    $sErrMsg, // Le message d'erreur
                    $oAlertLogService->getErrorData("DB_ERR_NOT_FOUND", 'COMMAND/reset_ordre_model_tournee', __FILE__, __LINE__, array()));
        }

        $sLogMsg = 'Fin de réinitialisation de l\'ordre de la tournée ' . $sMTJCode;
        $oAlertLogService->logEvent(
                'carto', // Peut être une de ces valeurs: carto|alim|envt|docs|paie|crm
                'info', // Peut être une de ces valeurs: debug|info|notice|warning|error|critical|alert|emergency
                $sLogMsg, // Le message d'erreur
                array(date("d/m/Y H:i:s : ")));

        echo $sLogMsg;

        return;
    }

}
