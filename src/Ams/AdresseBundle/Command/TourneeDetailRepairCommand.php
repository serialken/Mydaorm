<?php 
namespace Ams\AdresseBundle\Command;

//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Doctrine\DBAL\DBALException;
use Ams\WebserviceBundle\Exception\GeocodageException;

use Ams\SilogBundle\Command\GlobalCommand;

/**
 * 
 * @author KJeanBaptiste
 * REATRIBUTION DE TOURNEE POUR UN ABONNEE SUR UNE
 * ANCIENNE TOURNEE
 *
 */
class TourneeDetailRepairCommand extends GlobalCommand
{
    
    protected function configure()
    {
        //php app/console tournee_detail_repair_order --code_tournee 042NXK017VE --env local
    	$this->sNomCommande	= 'tournee_detail_repair_incoherence';
    	$this->setName($this->sNomCommande);
        $this
            ->setDescription('changement vers une ancienne tournee pour les abonnes dans une tournee incoherente')
            ->addArgument('tourneeJourId', InputArgument::REQUIRED)
            ->addArgument('date', InputArgument::REQUIRED)
            ->addArgument('week', InputArgument::REQUIRED,'nombre de semaine a compter de la date donné ou l\'abonné etait sur une tournée correct')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
        $tourneeJourId = $input->getArgument('tourneeJourId');
        $date = $input->getArgument('date');
        $week = $input->getArgument('week');
        $em = $this->getContainer()->get('doctrine')->getManager();
        $aNewAbonne = array();
        
        /** RECUPERATION DES ABONNES AFFILIES A DES TOURNEES INCOHERENTES **/
        $aTournee = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getAbonneInconsistencyTournee($tourneeJourId,$date,$week);
        foreach($aTournee as $data){
            $abonneSocId = $data['abonne_soc_id'];
            $hasTournee = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getTourneeJourByAbonneSocDate($abonneSocId,$date,$week);
            /** UPDATE TOURNEE DETAIL **/
            if($hasTournee){
                $em->getRepository('AmsAdresseBundle:TourneeDetail')->updateCodeTourneeAbonneJourId($abonneSocId,$hasTournee['code'],$data['jour_id']);
            }
            else $aNewAbonne[] = $abonneSocId;
        }
        exit;
        
        
        /** RECUPERATION DES ABONNES BIEN CLASSER **/
        $aTournee = $em->getRepository('AmsAdresseBundle:TourneeDetail')->getAboProperlyClassify($codeTournee);
        /** RECUPERATION DES ABONNES A REINTEGRER**/
        
    	$this->oLog->info('Fin ordonnancement tournee detail pour la tournee '.$codeTournee);
        return;
    }
    
}
