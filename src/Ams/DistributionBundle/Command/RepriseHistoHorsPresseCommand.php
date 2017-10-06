<?php 
namespace Ams\DistributionBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ijanki\Bundle\FtpBundle\Exception\FtpException;
use Ams\SilogBundle\Command\GlobalCommand;
use Doctrine\DBAL\DBALException;

/**
 * 
 * Reprise historique depotet tournee pour le Hors Presse uniquement (produit_type_id = 10)
 * 
 * 
 * 
 * 
 * 
 * @author aandrianiaina
 *
 */
class RepriseHistoHorsPresseCommand extends GlobalCommand
{
    
    protected function configure()
    {
    	$this->sNomCommande	= 'reprise_histo_hors_presse';
        $sJourATraiterMinParDefaut = "J+1";
        $sJourATraiterMaxParDefaut = "J+2";
        $this->jourATraiterMaxRef   = 3;
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console reprise_histo_hors_presse Expl : php app/console reprise_histo_hors_presse J+1 J+10 --id_sh=cron_test --id_ai=1  --env=prod
        $this
            ->setDescription('Reprise historique depot et tournee Hors Presse.')
            ->addArgument('jour_a_traiter_min', InputArgument::OPTIONAL, 'Jour a traiter Min. Format : J<+Numerique> (Expl : J+1 ou J+2 ou J ...)', $sJourATraiterMinParDefaut)
            ->addArgument('jour_a_traiter_max', InputArgument::OPTIONAL, 'Jour a traiter Max. Format : J<+Numerique> (Expl : J+1 ou J+2 ou J ...)', $sJourATraiterMaxParDefaut)
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
    	$this->oLog->info(date("d/m/Y H:i:s : ")."Debut Reprise historique depot et tournee Hors Presse - Commande : ".$this->sNomCommande);    
                
        $em    = $this->getContainer()->get('doctrine')->getManager();
        
    	$sJourATraiterMin  = $input->getArgument('jour_a_traiter_min');
        $sJourATraiterMax  = $input->getArgument('jour_a_traiter_max');
        $iJourATraiter  = 0;
        $aiJourATraiter   = array();
        $aoJourATraiter   = array();
        if(preg_match('/^J([\-\+][0-9]+)?$/', $sJourATraiterMin, $aiJourATraiterMin) && preg_match('/^J([\-\+][0-9]+)?$/', $sJourATraiterMax, $aiJourATraiterMax))
        {
            $iJourATraiterMin = 0;
            $iJourATraiterMax = 0;
            if(isset($aiJourATraiterMin[1]))
            {
                $iJourATraiterMin  = intval($aiJourATraiterMin[1]);
            }
            
            if(isset($aiJourATraiterMax[1]))
            {
                $iJourATraiterMax  = intval($aiJourATraiterMax[1]);
            }
            
            if($iJourATraiterMax >= $iJourATraiterMin)
            {
                for($i=$iJourATraiterMin; $i<=$iJourATraiterMax; $i++)
                {
                    $aiJourATraiter[]    = $i;
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
        
        foreach($aiJourATraiter as $iJourATraiter)
        {
            $oDateDuJour    = new \DateTime();
            $oDateDuJour->setTime(0, 0, 0);
            $dateDistribATraiter   = $oDateDuJour;
            if($iJourATraiter<0)
            {
                $dateDistribATraiter   = $oDateDuJour->sub(new \DateInterval('P'.abs($iJourATraiter).'D'));
            }
            else
            {
                $dateDistribATraiter   = $oDateDuJour->add(new \DateInterval('P'.$iJourATraiter.'D'));
            }
            
            $aoJourATraiter[$iJourATraiter] = $dateDistribATraiter;
        }
        
        foreach ($aoJourATraiter as $iJourATraiter => $oDateATraiterV)
        {
            try {
            
                // Mettre a jour des infos tournee et depot de la table "client_a_servir_logist" en fonction de ce que l'on a dans tournee_detail
                // JL 10/02/2017
                // ATTENTION, le type_id=10 n'existe plus
                // Pour sélectionner les produits hors-presse, il faut faire une jointure sur produit_type est selectionner est_horspresse=1
                $sUpdate    = " UPDATE
                                    client_a_servir_logist csl
                                    INNER JOIN tournee_detail td ON csl.point_livraison_id = td.point_livraison_id AND CAST(DATE_FORMAT(csl.date_distrib, '%w') AS SIGNED)+1 = td.jour_id AND csl.flux_id = td.flux_id
                                    INNER JOIN modele_tournee_jour mtj ON td.modele_tournee_jour_code = mtj.code AND csl.date_distrib BETWEEN mtj.date_debut AND mtj.date_fin
                                    INNER JOIN depot d ON LEFT(td.modele_tournee_jour_code, 3) = d.code
                                    INNER JOIN produit p ON csl.produit_id = p.id
                                    INNER JOIN produit_type pt ON p.type_id=pt.id
                                SET
                                    csl.depot_id = d.id
                                WHERE
                                    csl.date_distrib = '".$oDateATraiterV->format('Y-m-d')."' 
                                    AND csl.flux_id = p.flux_id 
                                --    AND p.type_id = 10 
                                    AND pt.hors_presse
                                ";
                //echo "\r\n";print_r($sUpdate);echo "\r\n";/
                $em->getConnection()->executeQuery($sUpdate);
                $em->clear();

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
            
            try {
            
                // Mettre a jour des infos depot de la table "client_a_servir_logist" en fonction de ce que l'on a dans tournee_detail SANS PRENDRE EN COMPTE LE JOUR TYPE
                $sUpdate    = " UPDATE
                                    client_a_servir_logist csl
                                    INNER JOIN tournee_detail td ON csl.point_livraison_id = td.point_livraison_id AND csl.flux_id = td.flux_id
                                    INNER JOIN modele_tournee_jour mtj ON td.modele_tournee_jour_code = mtj.code AND csl.date_distrib BETWEEN mtj.date_debut AND mtj.date_fin
                                    INNER JOIN depot d ON LEFT(td.modele_tournee_jour_code, 3) = d.code
                                    INNER JOIN produit p ON csl.produit_id = p.id
                                SET
                                    csl.depot_id = d.id
                                WHERE
                                    csl.date_distrib = '".$oDateATraiterV->format('Y-m-d')."' 
                                    AND csl.flux_id = p.flux_id 
                                    AND p.type_id = 10 
                                    AND depot_id IS NULL 
                                ";
                //echo "\r\n";print_r($sUpdate);echo "\r\n";/
                $em->getConnection()->executeQuery($sUpdate);
                $em->clear();

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
        
        $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
        $this->endTraitement();       
        $this->oLog->info(date("d/m/Y H:i:s : ")."Fin Reprise historique depot et tournee Hors Presse - Commande : ".$this->sNomCommande);
        return;
    }

    
}
