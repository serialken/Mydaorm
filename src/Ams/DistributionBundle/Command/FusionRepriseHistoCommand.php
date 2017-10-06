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
 * Reprise historique depot, tournee Neopress + Prise en compte des depot-commune Neopress pour le flux de jour
 * 
 * 
 * @author aandrianiaina
 *
 */
class FusionRepriseHistoCommand extends GlobalCommand
{
    protected function configure()
    {
    	$this->sNomCommande	= 'fusion_reprise_histo';
        $sJourATraiterMinParDefaut = "J+1";
        $sJourATraiterMaxParDefaut = "J+2";
        $this->jourATraiterMaxRef   = 3;
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console fusion_reprise_histo Expl : php app/console fusion_reprise_histo J+1 J+3  --env=prod
        $this
            ->setDescription('Reprise historique depot, tournee Neopress + Prise en compte des depot-commune Neopress pour le flux de jour.')
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
        
    	$this->oLog->info(date("d/m/Y H:i:s : ")."Debut Reprise historique depot, tournee Neopress + Prise en compte des depot-commune Neopress pour le flux de jour - Commande : ".$this->sNomCommande);    
                
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
                $sUpdate    = " UPDATE
                                    client_a_servir_logist csl
                                    INNER JOIN tournee_detail td ON csl.abonne_soc_id = td.num_abonne_id AND CAST(DATE_FORMAT(csl.date_distrib, '%w') AS SIGNED)+1 = td.jour_id AND csl.flux_id = td.flux_id
                                    INNER JOIN modele_tournee_jour mtj ON td.modele_tournee_jour_code = mtj.code AND csl.date_distrib BETWEEN mtj.date_debut AND mtj.date_fin
                                    INNER JOIN depot d ON LEFT(td.modele_tournee_jour_code, 3) = d.code
                                    INNER JOIN produit p ON csl.produit_id = p.id
                                SET
                                    csl.tournee_jour_id = mtj.id
                                    , csl.tournee_jour_id = mtj.id
                                    , csl.point_livraison_ordre = td.ordre
                                    , csl.depot_id = d.id
                                WHERE
                                    csl.date_distrib = '".$oDateATraiterV->format('Y-m-d')."' 
                                    AND csl.flux_id = 2 
                                    /*AND p.libelle LIKE '[%'*/
                                ";
                $em->getConnection()->executeQuery($sUpdate);
                $em->clear();


                // Mise a jour des depots des abonnes jour que l'on ne connait pas
                // ------ A supprimer quand les tables "repar_*prod*" seront bien remplis - [LM/(V) Télé Loisirs]/Gala/(V) Télé 2 semaines/(V) Elle Decoration/...)
                /*
                $sUpdate    = " UPDATE
                                    client_a_servir_logist csl
                                    INNER JOIN fusion_neo_depot_commune f ON csl.commune_id = f.commune_id
                                    INNER JOIN produit p ON csl.produit_id = p.id
                                SET
                                    csl.depot_id = f.depot_id
                                WHERE
                                    csl.date_distrib = '".$oDateATraiterV->format('Y-m-d')."'
                                    AND csl.flux_id = 2 
                                    AND csl.tournee_jour_id IS NULL AND csl.societe_id NOT IN (3, 26, 28, 162) ";
                //echo "\r\n";print_r($sUpdate);echo "\r\n";
                $em->getConnection()->executeQuery($sUpdate);
                $em->clear();
                */
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
        $this->oLog->info(date("d/m/Y H:i:s : ")."Fin Reprise historique depot, tournee Neopress + Prise en compte des depot-commune Neopress pour le flux de jour - Commande : ".$this->sNomCommande);
        return;
    }
}
