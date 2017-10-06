<?php 
namespace Ams\AdresseBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Command\GlobalCommand;

/**
 * 
 * "Command" permettant de sauvegarder les adresses (VOL1-2-3 & CP & VILLE) livrees
 *      Expl :  php app/console sauvegarde_adr_vol123_livrees J-7 J+0    --env=dev
 *              php app/console sauvegarde_adr_vol123_livrees J-7 J+0    --env=prod
 * 
 * @author aandrianiaina
 *
 */
class SauvegardeAdresseVol123LivreeCommand extends GlobalCommand
{
    
    protected function configure()
    {
    	$this->sNomCommande	= 'sauvegarde_adr_vol123_livrees';
        $this->sJourATraiterMinParDefaut = "J-7";
        $this->sJourATraiterMaxParDefaut = "J+0";
        
        $this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console sauvegarde_adr_vol123_livrees <J+0> <J+0> --force=<1> Expl : php app/console sauvegarde_adr_vol123_livrees J+0 J+0 --force=1
        $this
            ->setDescription('Sauvegarde des adresses (VOL1-2-3 & CP & VILLE) livrees')
            ->addArgument('jour_a_traiter_min', InputArgument::OPTIONAL, 'Jour a traiter Min. Format : J<+Numerique> (Expl : J-21 ou J+2 ou J ...)', $this->sJourATraiterMinParDefaut)
            ->addArgument('jour_a_traiter_max', InputArgument::OPTIONAL, 'Jour a traiter Max. Format : J<+Numerique> (Expl : J+1 ou J+2 ou J ...)', $this->sJourATraiterMaxParDefaut)
            ->addOption('id_sh',null, InputOption::VALUE_REQUIRED, 'Libelle du CRON')
            ->addOption('id_ai',null, InputOption::VALUE_REQUIRED, 'Id du CRON')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
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
        $sJourATraiterMin  = $input->getArgument('jour_a_traiter_min');
        $sJourATraiterMax  = $input->getArgument('jour_a_traiter_max');
               
        $this->oLog->info(date("d/m/Y H:i:s : ")."Debut Sauvegarde des adresses livrees (VOL1-2-3 & CP & VILLE) - Commande : ".$this->sNomCommande." $sJourATraiterMin $sJourATraiterMax");    
        
        $repoAdresseVol123Livree   = $this->getContainer()->get('doctrine')->getRepository('AmsAdresseBundle:AdresseVol123Livree');        
    	
        if(preg_match('/^J([\-\+][0-9]+)?$/', $sJourATraiterMin, $aiJourATraiterMin) && preg_match('/^J([\-\+][0-9]+)?$/', $sJourATraiterMax, $aiJourATraiterMax))
        {
            try {
                $this->oLog->info("Debut Sauvegarde des adresses livrees (VOL1-2-3 & CP & VILLE)");
                $repoAdresseVol123Livree->insert(str_replace("J", "", $sJourATraiterMin), str_replace("J", "", $sJourATraiterMax));
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
        }
        else
        {
            $this->suiviCommand->setMsg("Jour a traiter. Format : J<+Numerique> (Expl : J-1 ou J-2 ou J ...)");
            $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_WARNING));
            $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
            $this->suiviCommand->setEtat("OK");
            $this->oLog->erreur("Jour a traiter. Format : J<+Numerique> (Expl : J-1 ou J-2 ou J ...)", E_USER_WARNING);
        }
    	$this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
        $this->endTraitement();
    	$this->oLog->info(date("d/m/Y H:i:s : ")."Fin Sauvegarde des adresses (VOL1-2-3 & CP & VILLE) livrees - Commande : ".$this->sNomCommande." $sJourATraiterMin $sJourATraiterMax");    
        return;
    }
}
