<?php 
namespace Ams\DistributionBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ams\SilogBundle\Command\GlobalCommand;
use Doctrine\DBAL\DBALException;

/**
 * 
 * Mise a jour des tournees de la table "client_a_servir_logist"
 * Par defaut, on ne fait le calcul que le jour J+1.
 * Parametre : 
 *          Jour minimum a calculer. C'est optionnel
 *          Jour maximum a calculer. C'est optionnel
 * Expl : J+1 J+5
 * Si les parametres sont renseignes, le traitement concerne les jours de distribution de "jour minimum" a "jour maximum"
 * Expl : J-1. => calculs a faire concernent J-1, J, J+1, J+2 & J+3
 * 
 * Exemple de commande : php app/console mise_a_jour_tournee J+0 J+3 0 --id_sh=cron_test --id_ai=1  --env=dev
 * 
 * @author aandrianiaina
 *
 */
class MiseAJourTourneeCommand extends GlobalCommand
{
    protected function configure()
    {
    	$this->sNomCommande	= 'mise_a_jour_tournee';
        $sJourATraiterMinParDefaut = "J+1";
        $sJourATraiterMaxParDefaut = "J+2";
        $bMAJ_Toute          = "0";
        $this->jourATraiterMaxRef   = 3;
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console mise_a_jour_tournee Expl : php app/console mise_a_jour_tournee J+1 J+3
        $this
            ->setDescription('Mise a jour des tournees de la table "client_a_servir_logist"')
            ->addArgument('jour_a_traiter_min', InputArgument::OPTIONAL, 'Jour a traiter Min. Format : J<+Numerique> (Expl : J+1 ou J+2 ou J ...)', $sJourATraiterMinParDefaut)
            ->addArgument('jour_a_traiter_max', InputArgument::OPTIONAL, 'Jour a traiter Max. Format : J<+Numerique> (Expl : J+1 ou J+2 ou J ...)', $sJourATraiterMaxParDefaut)
            ->addArgument('m_a_j_toute', InputArgument::OPTIONAL, 'Parametre de recalcul de toutes les tournees. Valeur : 0 ou 1 . Si 0 => on ne met a jour que les clients dont la tournee n est pas renseignee', $bMAJ_Toute)
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
        $this->oLog->info(date("d/m/Y H:i:s : ").'Debut Mise a jour des tournees de la table "client_a_servir_logist" - Commande : '.$this->sNomCommande);    
        
        $em    = $this->getContainer()->get('doctrine')->getManager();
        
    	$sJourATraiterMin  = $input->getArgument('jour_a_traiter_min');
        $sJourATraiterMax  = $input->getArgument('jour_a_traiter_max');
        $bMAJ_Toute     = $input->getArgument('m_a_j_toute');
        if($bMAJ_Toute!="0") 
        {
            $this->oLog->info('On met a jour toutes les tournees de la table "client_a_servir_logist"');  
        }
        $iJourATraiter  = 0;
        if(preg_match('/^J([\-\+][0-9]+)?$/', $sJourATraiterMin, $aJourATraiterMin) && preg_match('/^J([\-\+][0-9]+)?$/', $sJourATraiterMax, $aJourATraiterMax))
        {
            $iJourATraiterMin = 0;
            $iJourATraiterMax = 0;
            if(isset($aJourATraiterMin[1]))
            {
                $iJourATraiterMin  = intval($aJourATraiterMin[1]);
            }
            
            if(isset($aJourATraiterMax[1]))
            {
                $iJourATraiterMax  = intval($aJourATraiterMax[1]);
            }
            
            if($iJourATraiterMax >= $iJourATraiterMin)
            {
                $aJourATraiter   = array();
                for($i=$iJourATraiterMin; $i<=$iJourATraiterMax; $i++)
                {
                    $aJourATraiter[]    = $i;
                }
                
                foreach($aJourATraiter as $iJourATraiter)
                {
                    $this->oLog->info("- Debut Traitement jour J".(($iJourATraiter<0)?"-":'+').abs($iJourATraiter));
                    try {
                        $oDateDuJour    = new \DateTime();
                        $oDateDuJour->setTime(0, 0, 0);
                        if($iJourATraiter<0){
                            $date_distrib   = $oDateDuJour->sub(new \DateInterval('P'.abs($iJourATraiter).'D'));
                        }else{
                            $date_distrib   = $oDateDuJour->add(new \DateInterval('P'.$iJourATraiter.'D'));
                        }
                        
                        $update = " UPDATE
                                            client_a_servir_logist t
                                            , tournee_detail td
                                            , modele_tournee_jour mtj
                                    SET
                                            t.tournee_jour_id = mtj.id
                                            , t.point_livraison_ordre = td.ordre
                                            , t.ordre_dans_arret = td.ordre_stop
                                    WHERE
                                            t.date_distrib = '".$oDateDuJour->format("Y/m/d")."' 
                                            AND t.date_distrib BETWEEN mtj.date_debut AND mtj.date_fin
                                ";
                        if($bMAJ_Toute=="0") 
                        {
                            $update .= " AND t.tournee_jour_id IS NULL ";
                        }
                        $update .= "        AND t.abonne_soc_id = td.num_abonne_id
                                            AND mtj.code = td.modele_tournee_jour_code
                                            AND mtj.jour_id=CAST(DATE_FORMAT(t.date_distrib, '%w') AS SIGNED)+1	
                            ";
                        
                        $em->getConnection()->executeQuery($update);
                        $em->clear();
                    } 
                    catch (DBALException $DBALException) {
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
            }
            else
            {  
                $this->suiviCommand->setMsg("Le jour MAX est anterieur au Jour MIN (Jour min : J".(($iJourATraiterMin>=0)?"+":"-").abs($iJourATraiterMin).". Jour max : J".(($iJourATraiterMax>=0)?"+":"-").abs($iJourATraiterMax).").");
                $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_WARNING));
                $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                $this->suiviCommand->setEtat("OK");
                $this->oLog->erreur("Le jour MAX est anterieur au Jour MIN (Jour min : J".(($iJourATraiterMin>=0)?"+":"-").abs($iJourATraiterMin).". Jour max : J".(($iJourATraiterMax>=0)?"+":"-").abs($iJourATraiterMax).").", E_USER_WARNING);
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
    	$this->oLog->info(date("d/m/Y H:i:s : ").'Fin Mise a jour des tournees de la table "client_a_servir_logist" - Commande : '.$this->sNomCommande);    
        return;
    }
}
