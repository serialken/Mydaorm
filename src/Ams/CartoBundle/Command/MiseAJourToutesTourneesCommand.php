<?php

namespace Ams\CartoBundle\Command;

//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ijanki\Bundle\FtpBundle\Exception\FtpException;
use Ams\SilogBundle\Lib\StringLocal;
use Ams\SilogBundle\Command\GlobalCommand;
use Ams\CartoBundle\Controller\CartoController;

class MiseAJourToutesTourneesCommand extends GlobalCommand {

    protected function configure() {
        $this->setName('maj_toutes_tournees');
        /** php app/console maj_toutes_tournees  * */
        
        // Par défaut, c'est la date du jour courant qui est utilisée
        $oDate = new \DateTime();
        $sDateFormat = $oDate->format( 'Y-m-d');
        
        $this
                ->setDescription("Mise a jour de toutes les tournées ClientAServirLogist pour aujourd'hui")
                ->addOption('date',NULL, InputOption::VALUE_OPTIONAL, "La date sur laquelle on doit recalculer les ordres de tournée",$sDateFormat)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
        $time_start = microtime(true);
        $this->oLog->info("Debut de mise a jour de toutes les tournees CASL");

        $em = $this->getContainer()->get('doctrine')->getManager();
        $sDate = $input->getOption('date');
        
         // Date invalide
        if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $sDate)) {
            $this->oLog->info("La date fournie est mal formatee, fin d'execution.");
            exit();
        }
        
        // On récupère toutes les tournées disponibles dans CASL
        $aTournees = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getTourneesDuJour($sDate);
        
        if (!empty($aTournees)){
            $command = $this->getApplication()->find('update_ordre_tournee_casl');
            foreach ($aTournees as $tournee){
                $aArgs = array(
                    'command' => 'update_ordre_tournee_casl',
                    'mtj_code' => $tournee['code'],
                    'date' => $sDate
                );
                
                $input = new ArrayInput($aArgs);
                $returnCode = $command->run($input, $output);
            }
        }
        else{
            $this->oLog->info("Aucune tournee n'a ete trouvee dans CASL pour la date du ".$sDate);
            exit("Fin d'execution.");
        }
      
        $time_2 = microtime(true);
        $time = $time_2 - $time_start;
        $this->oLog->info("Temps de requete " . sprintf("%.2f", $time) . ' sec');
        $this->oLog->info("Fin de mise a jour des tournees CASL");
        return;
    }

}
