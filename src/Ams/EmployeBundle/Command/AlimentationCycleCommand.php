<?php 
namespace Ams\EmployeBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Ams\SilogBundle\Command\GlobalCommand;
use Doctrine\DBAL\DBALException;

/**
 * 
 * Alimentation des cycle à partir de Octime.
 *
 */
class AlimentationCycleCommand extends GlobalCommand
{
    protected function configure()
    {
    	$this->sNomCommande	= 'alimentation_cycle';
    	$this->setName($this->sNomCommande);
        $this->setDescription('Alimentation Octime.')
            ->addArgument('utilisateur_id', InputArgument::OPTIONAL, 'Identifiant utilisateur', 0)
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
        
        $this->oLog->sRecipientMailLogErr = "jhodenc@gmail.com";

        $utilisateur_id = $input->getArgument('utilisateur_id');

        $dateDebut = new \DateTime();
    	$this->oLog->info($dateDebut->format('H:i:s')."   \tD&eacute;but de l'alimentation Octime.");    
        
    	try {
            $this->getContainer()->get('Octime')->alimentation($idtrt, $utilisateur_id);
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
    	$this->oLog->info($dateDebut->format('H:i:s')."   \tFin de l'alimentation Octime.");
    	
        return;
    }
}
