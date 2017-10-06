<?php 
namespace Ams\ModeleBundle\Command;

//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Ams\SilogBundle\Command\GlobalCommand;
use Doctrine\DBAL\DBALException;

/**
 * 
 * Mise a jour de "tournee_detail" lors d'un transfert d'une tournee vers une autre
 * 
 * Par defaut: 
 *      Le traitement automatique tourne entre "J_appl - 6" et "J_appl - 0" inclus. (J_appl etant le jour d'application du transfert)
 *      Exemple : DImanche - LUndi - MArdi - MErcredi - JEudi - VEndredi - SAmedi 
 *              Si le "jour type" du J_appl est ME
 *                  . Le JE (jeudi) avant le jour d'application, on traite le "jour type" ME
 *                  . Le VE (vendredi) avant le jour d'application, on traite : JE (+ eventuellement ME)
 *                  . Le SA (samedi) avant le jour d'application, on traite : VE (+ eventuellement ME+JE)
 *                  . Le DI (dimanche) avant le jour d'application, on traite : SA (+ eventuellement ME+JE+VE)
 *                  . Le LU (lundi) avant le jour d'application, on traite : DI (+ eventuellement ME+JE+VE+SA)
 *                  . Le MA (mardi) avant le jour d'application, on traite : LU (+ eventuellement ME+JE+VE+SA+DI)
 *                  . le jour d'application ME (mercredi), on traite : MA (+ eventuellement ME+JE+VE+SA+DI+LU)
 *      Avec l'exemple decrit ci-dessus, on peut definir que :
 *          . les "jour type" a exclure du traitement : "jour type" present entre le jour de traitement (inclus) et le jour d'application (non inclus) + "jour type" deja traite 
 *          
 * 
 * Parametre : 
 *          Les "jour type" a transferer. Optionnel. [--j=1,2,3] . Ces chiffres correspondent a l'"id" de la table "ref_jour".
 *                  Si ce parametre est defini, on force le transfert.
 *          Le code de la tournee a transferer. Optionnel. [--t=040JTC003]
 *          La date minimum d'application a traiter. Optionnel. [--date_min=16/10/2015]. Date au format JJ/MM/AAAA
 *          La date maximum d'application a traiter. Optionnel. [--date_max=17/10/2015]. Date au format JJ/MM/AAAA
 * 
 * Exemple de commande : 
 *                      php app/console transfert_tournee --j=1,2,7 --id_sh=cron_test --id_ai=1  --env=dev
 *                      php app/console transfert_tournee --j=5 --t=040JTC007 --id_sh=cron_test --id_ai=1  --env=prod
 *                      php app/console transfert_tournee --j=2,3,7 --t=040JTC002 --date_min=16/10/2015 --id_sh=cron_test --id_ai=1  --env=prod
 *                      php app/console transfert_tournee --j=1,2,3,7 --t=040JTC002 --date_min=16/10/2015 --date_max=16/10/2015 --id_sh=cron_test --id_ai=1  --env=prod
 * 
 * 
 * @author aandrianiaina
 *
 */
class ModeleTourneeTransfertCommand extends GlobalCommand
{
    private $joursParDefaut;
    private $tourneesParDefaut;
    private $dateMinParDefaut;
    private $dateMaxParDefaut;
    protected function configure()
    {
    	$this->sNomCommande	= 'transfert_tournee';
        $this->joursParDefaut = 'tout';
        $this->tourneesParDefaut = 'toute';
        $this->dateMinParDefaut = '-';
        $this->dateMaxParDefaut = '-';
        
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console transfert_tournee [--j=2,3,7] [--t=040JTC002] [--date_min=16/10/2015] Expl : php app/console transfert_tournee --env=prod
        $this
            ->setDescription('Transfert des tournees vers une autre.')
            ->addOption('j',null, InputOption::VALUE_REQUIRED, 'jour type ? Liste des "jour type" a traiter par ","', $this->joursParDefaut)
            ->addOption('t',null, InputOption::VALUE_REQUIRED, 'tournee ? Liste des "code tournee" a traiter separees par ","', $this->tourneesParDefaut)
            ->addOption('date_min',null, InputOption::VALUE_REQUIRED, 'date MIN d application', $this->dateMinParDefaut)
            ->addOption('date_max',null, InputOption::VALUE_REQUIRED, 'date MAX d application', $this->dateMaxParDefaut)
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
        $aLimiteJoursTypes  = array();
        $aLimiteTournees  = array();
        $aLimiteDate  = array();
        $aErreursParam   = array();
        
        $this->oLog->info(date("d/m/Y H:i:s : ")."Debut Transfert d une tournee vers une autre - Commande : ".$this->sNomCommande);   
        
        $sSeparateurParam    = ',';
        $repoRefJour   = $this->getContainer()->get('doctrine')->getRepository('AmsReferentielBundle:RefJour');
        
        $aToutJourType  = array();
        foreach($repoRefJour->getAllId() as $aK)
        {
            $aToutJourType[] = $aK['id'];
        }
        
        // Verification des parametres
        // --- Jour type a traiter
        $sJourTypeATraiter  = '';
        if ($input->getOption('j') && $input->getOption('j')!=$this->joursParDefaut) {
            $aJTmp  = explode($sSeparateurParam, $input->getOption('j'));
            foreach($aJTmp as $iJ)
            {
                if(!in_array(intval($iJ), $aToutJourType))
                {
                    $this->suiviCommand->setMsg("Jour type a traiter. Format : J<+Numerique> (Expl : J-1 ou J-2 ou J ...)");
                    $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_WARNING));
                    $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                    $this->suiviCommand->setEtat("OK");
                    $this->oLog->erreur("Jour type a traiter. Format : J<+Numerique> (Expl : J-1 ou J-2 ou J ...)", E_USER_WARNING);
                    $aErreursParam[]    = 'Param "--t"';
                }
            }
            if(empty($aErreursParam))
            {
                $sJourTypeATraiter  = $input->getOption('j');
            }
        }
        
        // --- Dates MIN et MAX afin de cibler les date_application a traiter
        $sRegexDate_dmY = '/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/';
        $oDateMin   = new \DateTime(); $oDateMin->setTime(0, 0, 0);
        $oDateMax   = new \DateTime(); $oDateMax->setTime(0, 0, 0);
        $oDateMin   = $oDateMin->sub(new \DateInterval('P5D')); // Date du jour - 5;
        $oDateMax   = $oDateMax->add(new \DateInterval('P6D')); // Date du jour + 6;
        
        if ($input->getOption('date_min') && $input->getOption('date_min')!=$this->dateMinParDefaut) {
            if(preg_match($sRegexDate_dmY, $input->getOption('date_min'), $matches))
            {
                if(isset($matches[1]) && isset($matches[2]) && isset($matches[3]))
                {
                    $oDateMin   = new \DateTime();
                    $oDateMin->setTime(0, 0, 0);
                    $oDateMin->setDate(intval($matches[3]), intval($matches[2]), intval($matches[1]));
                }
            }
            else
            {
                $this->suiviCommand->setMsg("date MIN. Format : JJ/MM/AAAA");
                $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_WARNING));
                $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                $this->suiviCommand->setEtat("OK");
                $this->oLog->erreur("date MIN. Format : JJ/MM/AAAA", E_USER_WARNING);
                $aErreursParam[]    = 'Param "--date_min"'.' : '.$input->getOption('date_min');
            }                
        }
        
        if ($input->getOption('date_max') && $input->getOption('date_max')!=$this->dateMaxParDefaut) {
            if(preg_match($sRegexDate_dmY, $input->getOption('date_max'), $matches))
            {
                if(isset($matches[1]) && isset($matches[2]) && isset($matches[3]))
                {
                    $oDateMax   = new \DateTime();
                    $oDateMax->setTime(0, 0, 0);
                    $oDateMax->setDate(intval($matches[3]), intval($matches[2]), intval($matches[1]));
                }
            }
            else
            {
                $this->suiviCommand->setMsg("date MAX. Format : JJ/MM/AAAA");
                $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_WARNING));
                $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                $this->suiviCommand->setEtat("OK");
                $this->oLog->erreur("date MAX. Format : JJ/MM/AAAA", E_USER_WARNING);
                $aErreursParam[]    = 'Param "--date_max"'.' : '.$input->getOption('date_max');
            }                
        }
        
        // --- Tournees a traiter
        $sTourneeATraiter   = '';
        if ($input->getOption('t') && $input->getOption('t')!=$this->tourneesParDefaut) {
            $sTourneeATraiter   = $input->getOption('t');
        }
        
        if(empty($aErreursParam))
        {
            echo "\r\n---> jour type a traiter : ".$sJourTypeATraiter."\r\n";
            echo "\r\n---> tournee a traiter : ".$sTourneeATraiter."\r\n";
            print_r($oDateMin);
            print_r($oDateMax);
            echo "\r\n"."\r\n";
            
            $aParam = array(
                            'separateur_param' => $sSeparateurParam,
                            'jour_type' => $sJourTypeATraiter,
                            'tournee' => $sTourneeATraiter,
                            'date_min' => $oDateMin,
                            'date_max' => $oDateMax,
                                );
            $repoModeleTourneeTransfert   = $this->getContainer()->get('doctrine')->getRepository('AmsModeleBundle:ModeleTourneeTransfert');
            $repoModeleTourneeTransfert->appliquer($aParam);
            
        }
        else
        {
            echo "\r\n"."\r\n";
            echo "ERRREEEUUUURRSSS"."\r\n";
            print_r($aErreursParam);
            echo "\r\n"."\r\n";
        }
        $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
        $this->endTraitement();
        $this->oLog->info(date("d/m/Y H:i:s : ")."Fin Transfert d une tournee vers une autre - Commande : ".$this->sNomCommande);
        return;
    }
}
