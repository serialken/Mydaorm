<?php

namespace Ams\ModeleBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Command\GlobalCommand;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SendMailEtalonDemandeCommand extends GlobalCommand {

    protected function configure() {
        $this->sNomCommande = 'sendmail_etalon_demande';
        $this->setName($this->sNomCommande);
        $this->setDescription("Envoi par e-mail un demande d'étalonnage.")
             ->addOption('etalon_id',null, InputOption::VALUE_REQUIRED, "Identifiant de l'étalonnage", NULL)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs

        $this->oLog->info('Lancement de l\'envoi de demande d\'étalonnage via ' . $this->sNomCommande);
        $this->container = $this->getContainer();

        try {
            $_etalon_id  = $input->getOption('etalon_id');
            
/*            $sUrlBase = $this->container->getParameter('MROAD_VERSION_'.  strtoupper($this->container->get('kernel')->getEnvironment()).'_URL');
            $sUrlBase .= (substr($sUrlBase, -1) == '/') ? '' : '/';*/
            $sUrlBase = $this->container->getParameter('MROAD_URL');
            
            $em = $this->container->get('doctrine')->getManager();
            $_etalon = $em->getRepository('AmsModeleBundle:Etalon')->selectOne($_etalon_id," and date_demande is not null and date_validation is null and date_refus is null");
//            $_etalon_tournee = $em->getRepository('AmsModeleBundle:EtalonTournee')->select($_etalon_id);
            
            if (!empty($_etalon)) {
                $sTemplate = 'AmsModeleBundle:Emails:mail_etalon_demande.mail.twig';
                $aMailDatas = array(
                    'sSubject' => 'Demande d\'étalonnage',
                    'sMailDest' => $this->container->getParameter('MAIL_PAIE_ETALONNAGE_DEST_ADRESSES'),
                    'cc' => array($_etalon['demandeur_mail']),
                    'sContentHTML' => '',
                    'urlBase' => $sUrlBase,
                    'etalon' => $_etalon,
//                    'etalon_tournee' => $_etalon_tournee
                    );
            } else {
                $this->oLog->info('Aucun enregistrement disponible.');
                $sTemplate = 'AmsModeleBundle:Emails:mailerreur_etalon_demande.mail.twig';
                $aMailDatas = array(
                    'sSubject' => 'ERREUR : Demande d\'étalonnage',
                    'sMailDest' => $this->container->getParameter('MAIL_PAIE_RESP_ADRESSES'),
                    'cc' => array($_etalon['demandeur_mail'],$this->container->getParameter('MAIL_PAIE_ETALONNAGE_DEST_ADRESSES')),
                    'sContentHTML' => '',
                    'etalon_id' => $_etalon_id
                    );
            }

            $oEmailService = $this->container->get('email');
            /* @var $oEmailService \Ams\SilogBundle\Services\Amsemail */
            if ($oEmailService->send($sTemplate, $aMailDatas)) {
                $this->oLog->info("Le mail a été envoyé à " . $aMailDatas['sMailDest'] . " et " . $_etalon['demandeur_mail']);
            } else {
                $this->oLog->info("Le mail n'a pas pu etre envoyé");
            }
            
        } catch (DBALException $DBALException) {
            $this->oLog->erreur($DBALException->getMessage(), $DBALException->getCode(), $DBALException->getFile(), $DBALException->getLine());
        }

        $this->oLog->info('Fin de Lancement de l\'envoi de demande d\'étalonnage via ' . $this->sNomCommande);
        return;
    }
}
