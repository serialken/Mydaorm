<?php 
namespace Ams\AdresseBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\DBAL\DBALException;
// include_once 'vendor/geophp/geoPHP.inc';

use Ams\SilogBundle\Command\GlobalCommand;

/**
 * 
 * @author KJeanBaptiste
 *
 */
class TourneeDetailSanteCommand extends GlobalCommand
{
    
    protected function configure()
    {
        //php app/console sante_tournee_detail --env local --no-interactive
    	$this->sNomCommande	= 'sante_tournee_detail';
    	$this->setName($this->sNomCommande);
        $this
            ->setDescription('Execute une batterie de test sur la table tournee detail et ses tables dependantes ')
            ->addOption('no-interactive',null, InputOption::VALUE_NONE,'(des)active le mode interactif')
            ->addOption('crm_detail-incoherence',null, InputOption::VALUE_NONE, "Verifie si le modele tournee jour respecte le 'jour' de la date d'imputation")
            ->addOption('doubloon',null, InputOption::VALUE_NONE,'Verifie les doublons dans tournee_detail')
            ->addOption('order_zero',null, InputOption::VALUE_NONE,'Verifie les tournee qui comporte un/des ordre à zero')
            ->addOption('order_zero_active',null, InputOption::VALUE_NONE,'Verifie les tournee actives qui comporte un/des ordre à zero')
            ->addOption('order-casl_td',null, InputOption::VALUE_NONE,'Verifie la cohérence des ordre entre tournee_detail et casl')
            ->addOption('incoherence_td_nuit',null, InputOption::VALUE_NONE,'Verifie la cohérence entre le modele_tournee_jour(N) et le flux(2) dans tournee_detail')
            ->addOption('incoherence_td_jour',null, InputOption::VALUE_NONE,'Verifie la cohérence entre le modele_tournee_jour(J) et le flux(1) dans tournee_detail')
            ->addOption('code_tournee',null, InputOption::VALUE_REQUIRED)
            ->addOption('without_idf',null, InputOption::VALUE_NONE,'Verifie si les coordonnées sont en ile de france')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
        $interactive = $input->getOption('no-interactive');
        $nbParam = $this->getNbParam($input);
        $steps = ($nbParam == 0)? 8 : $nbParam;
        $em = $this->getContainer()->get('doctrine')->getManager(); 
        $dialog = $this->getHelperSet()->get('dialog');
        $aReponse = array('Non', 'Oui');
        $progress = $this->getHelperSet()->get('progress');
        $progress->start($output, $steps + 1);
        $logs = "**************** SANTE TOURNEE DETAIL **************** (".date('l').") ".date('Y-m-d H:i:s')."\n";
        $logs .= 'Sur tournee detail il y a'."\n";
        
        /** RECUPERATION DES DOUBLONS (NUM_ABONNE_ID,JOUR_ID) **/
        if($input->getOption('doubloon') || $nbParam == 0){
            $aDoubloon = $em->getRepository('AmsAdresseBundle:TourneeDetail')->getDoubloon();
            $logs .= '- '.count($aDoubloon).' doublon(s) sur l environnement "'.$this->getContainer()->get('kernel')->getEnvironment().'"'."\n";
            $progress->advance();
        }
        
        /** INCOHERENCE MTJ => NUIT ,FLUX => JOUR **/
        if($input->getOption('incoherence_td_nuit') || $nbParam == 0){
            $aNbIncoherenceNuit = $em->getRepository('AmsAdresseBundle:TourneeDetail')->getIncoherenceFluxMTJ('N',2);
            $logs .= '- '.count($aNbIncoherenceNuit).' incoherence(s) modele_tournee_jour => N ,Flux => 2 '."\n";
            $progress->advance();
        }
        
        /** INCOHERENCE MTJ => JOUR ,FLUX => NUIT **/
        if($input->getOption('incoherence_td_jour') || $nbParam == 0){
            $aNbIncoherenceJour = $em->getRepository('AmsAdresseBundle:TourneeDetail')->getIncoherenceFluxMTJ('J',1);
            $logs .= '- '.count($aNbIncoherenceJour).' incoherence(s) modele_tournee_jour => J ,Flux => 1 '."\n";
            $progress->advance();
        }
        
        /** NB TOURNEE POSSEDANT DES ORDRES A 0 **/
        if($input->getOption('order_zero') || $nbParam == 0){
            $aTourneeOrderZero = $em->getRepository('AmsAdresseBundle:TourneeDetail')->getTourneeOrderZero('N');
            $logs .= '- '.current($aTourneeOrderZero).' tournee(s) qui ont au moins un abonne possédant un ordre de point de livraison egal a 0 sur le flux de nuit'."\n";
            $progress->advance();
        }
        /** NB TOURNEE ACTIVE POSSEDANT DES ORDRES A 0 **/
        if($input->getOption('order_zero_active') || $nbParam == 0){
            $aActiveTourneeOrderZero = $em->getRepository('AmsAdresseBundle:TourneeDetail')->getActiveTourneeOrderZero('N');
            $logs .= '- '.current($aActiveTourneeOrderZero).' tournee(s) active(s) qui ont au moins un abonne possédant un ordre de point de livraison egal a 0 sur le flux de nuit'."\n";
            $progress->advance();
        }
        
        /** INCOHERENCE ORDRE CROISSANT TD => CASL**/
        if($input->getOption('order-casl_td') || $nbParam == 0 ){
            $incoherenceOrder = $this->getIncoherenceOrderByTournee($em,$interactive,$aReponse,$dialog,$output);
            $logs .= $incoherenceOrder;
            $progress->advance();
        }
        
        /** INCOHERENCE MTJ/DATE CRM_DETAIL**/
        if($input->getOption('crm_detail-incoherence') || $nbParam == 0){
            $date = date('Y-m-d',strtotime("-14 day"));
            $incoherenceCrmDetail = $em->getRepository('AmsDistributionBundle:CrmDetail')->verifTourneeByDateImputation($date);
            $logs .= '- '.count($incoherenceCrmDetail).' tournee(s) ne correspondent pas avec le "jourId" pour les reclamations'."\n";
            $progress->advance();
        }
        
        /** COORDOONEES HORS ILE DE FRANCE **/
        // if($input->getOption('without_idf') || $nbParam == 0){
        //     $trash = $this->withoutIdf($em);
        //     $logs .= '- '.count($trash).' coordonnee(s) qui sont situe hors ile de france'."\n";
        //     $progress->advance();
        // }
        
        $this->writeLogs($logs);
        $progress->advance();
        $progress->finish();
    }
    
    private function getNbParam($input){
        $nbParam = 0;
        $nbParam += ($input->getOption('crm_detail-incoherence'))? 1 : 0;
        $nbParam += ($input->getOption('doubloon'))? 1 : 0;
        $nbParam += ($input->getOption('order_zero'))? 1 : 0;
        $nbParam += ($input->getOption('order_zero_active'))? 1 : 0;
        $nbParam += ($input->getOption('order-casl_td'))? 1 : 0;
        $nbParam += ($input->getOption('incoherence_td_nuit'))? 1 : 0;
        $nbParam += ($input->getOption('incoherence_td_jour'))? 1 : 0;
        $nbParam += ($input->getOption('without_idf'))? 1 : 0;
        return $nbParam;
    }
    
    private function writeLogs($logs){
        exec('echo "'.addslashes($logs).'" >> /var/www/html/log.txt');   
    }
    
    private function withoutIdf($em){
        $GeoJson = file_get_contents('web/idf.json');
        $oGeom = new \geoPHP();
        $geom = $oGeom->load($GeoJson,'json');
//        $oPoint = new \Point('2.1250335','48.8369325');
//        $test = $oPoint->within($geom); var_dump($test);exit;
        $aData = $em->getRepository('AmsAdresseBundle:AdresseRnvp')->getAllCoordsWithoutOise();
        $aTrash = array();
        foreach($aData as $data){
            $oPoint = new \Point($data['geox'],$data['geoy']);
            $test = $oPoint->within($geom);
            if(!$test) {$aTrash[] = $data['id'];}
        }
        $em->getRepository('AmsAdresseBundle:AdresseRnvp')->logAdressExceptIdf($aTrash);
        return $aTrash;
    }
    
    private function getIncoherenceOrderByTournee($em,$interactive,$aReponse,$dialog,$output){
        $date = date('Y-m-d');
//        $date = '2015-08-05';
        $aTourneeIncoherences = array();
        $aTourneeJour = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getTourneesDuJour($date);
        foreach($aTourneeJour as $TourneeJour){
            $aNbIncoherence = $em->getRepository('AmsAdresseBundle:TourneeDetail')->getNbOrdreIncoherenceCasl($TourneeJour['code'],$date);
            if($aNbIncoherence['nb_incoherence'] > 1) $aTourneeIncoherences[] = array('tournee' => $TourneeJour['code'], 'incoherence' => $aNbIncoherence['nb_incoherence']);
        }
        if(!empty($aTourneeIncoherences)){
            $message = '- Il y a '.count($aTourneeIncoherences).' incoherences au niveau de l ordre sur les tournées :'."\n";
            if(!$interactive){
                $reponse = $dialog->select($output,"\n".'Voulez-vous logguez le detail des incoherences au niveau de l ordre',$aReponse, 0);
                if ($reponse == '1'){
                    foreach($aTourneeIncoherences as $data){
                        $message .= $data['tournee']."     |      ".$data['incoherence']."\n";
                    }
                }
            }
        }
        else $message = "Pour $date il n y a pas d incoherence au niveau de l ordre"."\n";
        return $message;
    }
    
    private function deleteDoubloon($aDoubloon,$em){
        foreach($aDoubloon as $doubloon){
            /** 1) SOURCE_MODIFICATION => optim  **/
            $isOptim = $this->isOptimSourceModification($doubloon['num_abonne_id'],$doubloon['jour_id']);
            if($isOptim){
                $em->getRepository('AmsAdresseBundle:TourneeDetail')->deleteDoubloon($doubloon['num_abonne_id'],$doubloon['jour_id'],$isOptim);
                continue;
            }
            /** 2) POSSEDENT DES COORDONEES + INSEE  **/
            $hasLongLatInsee = $this->hasLongLatInsee($doubloon['num_abonne_id'],$doubloon['jour_id']);
            if($hasLongLatInsee){
                $em->getRepository('AmsAdresseBundle:TourneeDetail')->deleteDoubloon($doubloon['num_abonne_id'],$doubloon['jour_id'],$hasLongLatInsee);
                continue;
            }
            /** 3) PREND LE MAX ID DU DOUBLON  **/
            $maxId = $this->maxId($doubloon['num_abonne_id'],$doubloon['jour_id']);
            if($maxId)
                $em->getRepository('AmsAdresseBundle:TourneeDetail')->deleteDoubloon($doubloon['num_abonne_id'],$doubloon['jour_id'],$maxId);
        }
    }
    
    private function isOptimSourceModification($numAbonneId,$jourId){
        if(!$jourId) return false;
        $em = $this->getContainer()->get('doctrine')->getManager(); 
        $result = $em->getRepository('AmsAdresseBundle:TourneeDetail')->isOptimSourceModification($numAbonneId,$jourId);
        return ($result['id'])? $result['id'] : false;
    }
    
    private function hasLongLatInsee($numAbonneId,$jourId){
        if(!$jourId) return false;
        $em = $this->getContainer()->get('doctrine')->getManager(); 
        $result = $em->getRepository('AmsAdresseBundle:TourneeDetail')->hasLongLatInsee($numAbonneId,$jourId);
        return ($result['id'])? $result['id'] : false;
    }
    
    private function maxId($numAbonneId,$jourId){
        if(!$jourId) return false;
        $em = $this->getContainer()->get('doctrine')->getManager(); 
        $result = $em->getRepository('AmsAdresseBundle:TourneeDetail')->maxIdDoubloon($numAbonneId,$jourId);
        return ($result['id'])? $result['id'] : false;
    }
    
}
