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
 *
 */
class TourneeDetailRepairOrderCommand extends GlobalCommand
{
    
    protected function configure()
    {
        //php app/console tournee_detail_repair_order --code_tournee 042NXK017VE --env local
    	$this->sNomCommande	= 'tournee_detail_repair_order';
    	$this->setName($this->sNomCommande);
        $this
            ->setDescription('Initialise l\'heure de debut dans la table "tournee_detail"')
            ->addOption('code_tournee',null, InputOption::VALUE_REQUIRED)
//            ->addOption('date',null, InputOption::VALUE_REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
        $codeTournee = $input->getOption('code_tournee');
//        $date = $input->getOption('date');
    	$this->oLog->info('Debut ordonnancement tournee detail pour la tournee '.$codeTournee);
        $em = $this->getContainer()->get('doctrine')->getManager(); 
        
        $oAlertLogService = $this->getContainer()->get('alertlog');
        /* @var $oAlertLogService \Ams\SilogBundle\Services\Alerts */
        
        /** RECUPERATION DES ABONNES BIEN CLASSER **/
        $aTournee = $em->getRepository('AmsAdresseBundle:TourneeDetail')->getAboProperlyClassify($codeTournee);
        /** RECUPERATION DES ABONNES A REINTEGRER**/
        $aAboReintegrate = $em->getRepository('AmsAdresseBundle:TourneeDetail')->getAboToReintegrate($codeTournee);
        
        foreach($aAboReintegrate as $abo){
            /** TEST SI LE POINT DE LIVRAISON EXISTE DEJA DANS LA TOURNEE**/
            $pointLivraisonExist = $this->in_array_r($abo['point_livraison_id'],$aTournee);
            if($pointLivraisonExist !== false){
                $em->getRepository('AmsAdresseBundle:TourneeDetail')->updateOrderByPointLivraison($codeTournee,$pointLivraisonExist['ordre'],$abo['point_livraison_id']);
            }
            /** SINON ON PASSE PAR LE CLASSEMENT AUTO **/
            else{
                
                $aPoints = array( 'longitude' => $abo['geox'],'latitude' =>  $abo['geoy']);
                $aCritere = array('tournee' => $codeTournee,'rayon_max'=>10, 'nb_pts_proches_max'=>1,'ordre'=>1);
                $aNearPoints = $em->getRepository('AmsAdresseBundle:TourneeDetail')->ptsCandidatsProches($aPoints,$aCritere);

                $aNearPoints = current($aNearPoints);
                /** RECUPERATION DU POINT QUI VIENT AVANT**/
                
                if (!empty($aNearPoints['ordre'])){
                    $aBeforePoints = $em->getRepository('AmsAdresseBundle:TourneeDetail')->getAboOrderBefore($codeTournee,$aNearPoints['ordre']);
                    /** RECUPERATION DU POINT QUI VIENT APRES**/
                    $aAfterPoints = $em->getRepository('AmsAdresseBundle:TourneeDetail')->getAboOrderAfter($codeTournee,$aNearPoints['ordre']);
                    $route_1 = $this->calculRoute($aBeforePoints,$aPoints,$aNearPoints,$aAfterPoints);
                    $route_2 = $this->calculRoute($aBeforePoints,$aNearPoints,$aPoints,$aAfterPoints);
                    $nouvelOrdre = ($route_1 <= $route_2) ? intval($aNearPoints['ordre']) : intval($aNearPoints['ordre']) + 1;
                    $em->getRepository('AmsAdresseBundle:TourneeDetail')->updateOrderById($abo['id'],$nouvelOrdre,$codeTournee);
                }
                else{
                    $sErrCalculMsg = 'PB de calcul de l\'ordre dans la commande de réintégration ';
                    // On loggue le nombre de tournées récupérées
                    $oAlertLogService->logEvent(
                            'carto', // Peut être une de ces valeurs: carto|alim|envt|docs|paie|crm
                            'info', // Peut être une de ces valeurs: debug|info|notice|warning|error|critical|alert|emergency
                            $sErrCalculMsg, // Le message d'erreur
                            $oAlertLogService->getErrorData("REPRISE_TD_CMD_INFO", 'CARTO/REPRISE_COMMANDE_REPAIR', __FILE__, __LINE__, array(
                                '$aNearPoints' => $aNearPoints,
                                '$aCritere' => $aCritere,
                                '$aPoints' => $aPoints,
                                '$codeTournee' => $codeTournee,
                    )));
                }
            }
        }
    	$this->oLog->info('Fin ordonnancement tournee detail pour la tournee '.$codeTournee);
        return;
    }
    
    private function calculRoute($step1,$step2,$step3,$step4){
        $aCoordinate = array();
        $aReplace_key = array('longitude' => 'X','latitude' => 'Y','x'=>'X','y'=>'Y');
        $step2 = $this->change_key($step2, $aReplace_key);
        $step3 = $this->change_key($step3, $aReplace_key);
        if(isset($step1['ordre'])) 
            $aCoordinate[] = $step1;
        $aCoordinate[] = $step2;
        $aCoordinate[] = $step3;
        if(isset($step4['ordre'])) 
            $aCoordinate[] = $step4;
        
        $serv = $this->getContainer()->get('ams_carto.geoservice');
        $classement = $serv->wsRouteService($aCoordinate);
        return $classement->ROUTE->Time; 
        
    }
        
    private function change_key( $array, $aReplace_key) {
        $json_array = json_encode($array);
        foreach($aReplace_key as $old_key => $new_key){
            $json_array = str_replace($old_key,$new_key,$json_array);
        }
        return (array)json_decode($json_array);
    }
    
    
    private function in_array_r($needle, $haystack, $strict = false) {
        foreach ($haystack as $item) {
            if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->in_array_r($needle, $item, $strict))) {
                return $item;
            }
        }
        return false;
    }
}
