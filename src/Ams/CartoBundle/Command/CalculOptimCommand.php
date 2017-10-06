<?php 
namespace Ams\CartoBundle\Command;

//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Ijanki\Bundle\FtpBundle\Exception\FtpException;
use Ams\SilogBundle\Lib\StringLocal;

use Ams\SilogBundle\Command\GlobalCommand;

class CalculOptimCommand extends GlobalCommand
{

    protected function configure()
    {
      $this->setName('calcul_optim');
      /** php app/console calcul_optim --tournee=034NND001LU  **/
      $this->setDescription("Mise à jour d'une tournée spécifié via le calcul d'optimisation")
           ->addOption('tournee',null, InputOption::VALUE_REQUIRED, 'Code Tournee Jour ',false)
//           ->addOption('date',null, InputOption::VALUE_REQUIRED, 'date au format(Y-m-d)', false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
    	$this->oLog->info("Debut traitement");
        $time_start = microtime(true);
        if($input->getOption('tournee')!=''){
          $t = $this->getContainer()->get('ams_carto.geoservice');
          $em = $this->getContainer()->get('doctrine.orm.entity_manager');
          $tournees = $em->getRepository('AmsAdresseBundle:TourneeDetail')->getDataByTourneeJour($input->getOption('tournee'));
          $classement = $t->callRouteService(TRUE, FALSE, $tournees);
          $aDataWAYPOINT = $classement->ROUTE->WAYPOINT;
          $nb_stop = count($aDataWAYPOINT);
          $visitTime = gmdate("H:i:s",count($aDataWAYPOINT) * 30);
          $driveTime = $aDataWAYPOINT[$nb_stop -1]->FoundTime;
          $driveTimeTmp = explode(':', $driveTime);
          $totalTime = gmdate("H:i:s", count($aDataWAYPOINT) * 30 + ($driveTimeTmp[0] * 3600 + $driveTimeTmp[1] * 60 + $driveTimeTmp[2]));
          $distance = 0;
          $em->getRepository('AmsAdresseBundle:TourneeDetail')->upOptimTournee($input->getOption('tournee'),$nb_stop,$visitTime,$driveTime,$totalTime);
          
          foreach($tournees as $key=>$tournee){
            if($key){
              $t = $em->getRepository('AmsAdresseBundle:TourneeDetail')->find($tournee['id']);
              $hoursBase = $t->getDebutPlageHoraire()->format('H:i:s');
              $beginTime = explode(':', $hoursBase);
              $beginTime = $beginTime[0] * 3600 + $beginTime[1] * 60 + $beginTime[2];
              $foundTime = $aDataWAYPOINT[$key]->FoundTime;
              $foundTime = explode(':', $foundTime);
              $foundTime = $foundTime[0] * 3600 + $foundTime[1] * 60 + $foundTime[2] + ($key * 30) ;
              $beginTime = gmdate("H:i:s",$beginTime + $foundTime );
              $distance = $aDataWAYPOINT[$key]->TOTAL_DIST - $aDataWAYPOINT[($key - 1)]->TOTAL_DIST;
            }
            else {
              $t = $em->getRepository('AmsAdresseBundle:TourneeDetail')->find($tournee['id']);
              $beginTime = $t->getDebutPlageHoraire()->format('H:i:s');
            }
            
            $aTourneeDetail = array(
              'TOURNEE_DETAIL_ID' => $tournee['id'],
              'DRIVE_TIME' => $driveTime,
              'VISIT_TIME' => $visitTime,
              'TOTAL_TIME' => $totalTime,
              'BEGIN_TIME' => $beginTime,
              'DISTANCE_CUMUL' => $aDataWAYPOINT[$key]->TOTAL_DIST,
              'DISTANCE' => $distance,
              'NB_STOP' => $nb_stop,
            );
            $em->getRepository('AmsAdresseBundle:TourneeDetail')->updateTournee($aTourneeDetail);
          }
          
        }
        $end_time = microtime(true);
        $time = $end_time - $time_start;
    	$this->oLog->info("Temps de requete ".sprintf("%.2f", $time).' sec');
    	$this->oLog->info("Fin traitement");
     
    }

}
