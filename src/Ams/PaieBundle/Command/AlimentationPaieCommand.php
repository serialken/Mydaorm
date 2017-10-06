<?php

namespace Ams\PaieBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Ams\SilogBundle\Command\GlobalCommand;
use Doctrine\DBAL\DBALException;

class AlimentationPaieCommand extends GlobalCommand {

    protected function configure() {
        $this->sNomCommande = 'alimentation_paie';
        $this->setName($this->sNomCommande);
        // Pour executer, faire : php app/console produit_recap_depot Expl : php app/console produit_recap_depot 2016-09-03
        $this->setDescription('Alimentation de la paie a partir des modeles et des fichiers clients à servir.')
            ->addArgument('jour_a_traiter', InputArgument::REQUIRED, 'Jour a traiter')
            ->addArgument('utilisateur_id', InputArgument::OPTIONAL, 'Identifiant utilisateur', 0)
            ->addOption('id_sh',null, InputOption::VALUE_REQUIRED, 'Libelle du CRON')
            ->addOption('id_ai',null, InputOption::VALUE_REQUIRED, 'Id du CRON')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
        if($input->getOption('id_sh')){
            $idSh = $input->getOption('id_sh');
        }
        if($input->getOption('id_ai')){
            $idAi = $input->getOption('id_ai');
        }
        if($input->getOption('id_ai') && $input->getOption('id_sh')){
            $this->associateToCron($idAi,$idSh);
        }
        $this->oLog->sRecipientMailLogErr = "jhodenc@gmail.com";
  
        $dateDebut = new \DateTime();
        $this->oLog->info($dateDebut->format('H:i:s')." - Debut de l'alimentation de la paie. ");

        $utilisateur_id = $input->getArgument('utilisateur_id');
        $jour_a_traiter = $input->getArgument('jour_a_traiter');
        $date_distrib=  \DateTime::createFromFormat("Y-m-d", $jour_a_traiter);
        if ($date_distrib==false || array_sum($date_distrib->getLastErrors())) {
            $this->suiviCommand->setMsg("Erreur : Jour a traiter doit être au format : Y-m-d");
            $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_WARNING));
            $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
            $this->suiviCommand->setEtat("KO");
            $this->oLog->erreur("Erreur : Jour a traiter doit être au format : Y-m-d", E_USER_WARNING);
            $this->registerError();
            if($input->getOption('id_ai') && $input->getOption('id_sh')){
                $this->registerErrorCron($idAi);
            }
            return;
        }
        try {
            $dateDebut = new \DateTime();
            $this->oLog->info($dateDebut->format('H:i:s')." - Traitement jour : " . $date_distrib->format('Y-m-d'));

            $idtrt = null;
            $this->getContainer()->get('doctrine')->getRepository('AmsPaieBundle:PaiIntLog')->debut($idtrt, $utilisateur_id,'ALIM_PAIE',$date_distrib->format('Y-m-d'));

            $dateDebut = new \DateTime();
            $this->oLog->info($dateDebut->format('H:i:s')."   \tAlimentation des poids");
            $this->getContainer()->get('ams.repository.paipoidspco')->alimentation($msg, $msgException, $idtrt, $utilisateur_id, $date_distrib->format('Y-m-d'));

            $dateDebut = new \DateTime();
            $this->oLog->info($dateDebut->format('H:i:s')."   \tAlimentation des activit&eacute;s");
            $this->getContainer()->get('doctrine')->getRepository('AmsPaieBundle:PaiActivite')->alimentation($msg, $msgException, $idtrt, $utilisateur_id, $date_distrib->format('Y-m-d'),0,0);

            $dateDebut = new \DateTime();
            $this->oLog->info($dateDebut->format('H:i:s')."   \tAlimentation des tourn&eacute;es");
            $this->getContainer()->get('doctrine')->getRepository('AmsPaieBundle:PaiTournee')->alimentation($msg, $msgException, $idtrt, $utilisateur_id, $date_distrib->format('Y-m-d'),0,0);

            $this->getContainer()->get('doctrine')->getRepository('AmsPaieBundle:PaiIntLog')->fin($idtrt,'ALIM_PAIE');
        } catch (DBALException $DBALException) {
             $this->suiviCommand->setMsg($DBALException->getMessage());
            $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type($DBALException->getCode()));
            $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
            $this->suiviCommand->setEtat("KO");
            $this->oLog->erreur($DBALException->getMessage(), $DBALException->getCode(), $DBALException->getFile(), $DBALException->getLine());
            $this->registerError();
            if($input->getOption('id_ai') && $input->getOption('id_sh')){
                $this->registerErrorCron($idAi);
            }
        }
        $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
        $this->endTraitement();
        $dateDebut = new \DateTime();
        $this->oLog->info($dateDebut->format('H:i:s')." - Fin de la commande");
        return;
    }

}
