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
 * @author KJeanBaptiste
 *
 */
class TourneeDetailManagementDoublonCommand extends GlobalCommand
{
    
    protected function configure()
    {
        //php app/console management_doublon --id_sh=cron_test --id_ai=1 --env local --n 5
        //php app/console management_doublon --id_sh=cron_test --id_ai=1 --env local --abonneSoc 21 --jourId 2
        //php app/console management_doublon --id_sh=cron_test --id_ai=1 --env local --reqExpId 184 --jourId 2,3
    	$this->sNomCommande	= 'management_doublon';
    	$this->setName($this->sNomCommande);
        $this
            ->setDescription('Gestion des doublons dans tournee detail')
            ->addOption('n',null, InputOption::VALUE_REQUIRED,'Nombre de resultat attendu')
            ->addOption('abonneSoc',null, InputOption::VALUE_REQUIRED,'abonneSocId')
            ->addOption('jourId',null, InputOption::VALUE_REQUIRED,'JourId')
            ->addOption('reqExpId',null, InputOption::VALUE_REQUIRED,'requete export id')
            ->addOption('id_sh',null, InputOption::VALUE_REQUIRED, 'Libelle du CRON')
            ->addOption('id_ai',null, InputOption::VALUE_REQUIRED, 'Id du CRON')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output);
        if($input->getOption('id_sh')){
            $idSh = $input->getOption('id_sh');
        }
        if($input->getOption('id_ai')){
            $idAi = $input->getOption('id_ai');
        }
        if($input->getOption('id_ai') && $input->getOption('id_sh')){
            $this->associateToCron($idAi,$idSh);
        }
        $this->oLog->info("Debut traitement " . $this->sNomCommande);
        
        $em = $this->getContainer()->get('doctrine')->getManager(); 
        /** RECUPERATION DES DOUBLONS (NUM_ABONNE_ID,JOUR_ID) **/
        $maxResult = ($input->getOption('n') != NULL) ? $input->getOption('n') : 1;
        $abonneSoc = ($input->getOption('abonneSoc') != NULL) ? $input->getOption('abonneSoc') : NULL;
        $jourId = ($input->getOption('jourId') != NULL) ? $input->getOption('jourId') : NULL;
        $reqExpId = ($input->getOption('reqExpId') != NULL) ? $input->getOption('reqExpId') : NULL;
        
        if($maxResult > 0){
            $aDoubloon = $em->getRepository('AmsAdresseBundle:TourneeDetail')->getDoubloon($maxResult);
        }
        
        if($abonneSoc != NULL && $jourId != NULL){
            $aDoubloon = $em->getRepository('AmsAdresseBundle:TourneeDetail')->getAllAbonneJourId($abonneSoc,$jourId);
            if(count($aDoubloon) > 0){
                $aData[reset($aDoubloon)['num_abonne_id']] = $aDoubloon;
            }
            return false;
        }
        
        /** SUPPRESSION ID EN DOUBLON AVANT IMPORT GEOCONCEPT**/
        if($reqExpId != NULL && $jourId != NULL){
            $data = $em->getRepository('AmsAdresseBundle:ImportGeoconcept')->deleteDoubloonByRequeteExportId($reqExpId,$jourId);
        }
        $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
        $this->endTraitement();
         $this->oLog->info("Fin traitement " . $this->sNomCommande);
        exit;
    }

    
}
