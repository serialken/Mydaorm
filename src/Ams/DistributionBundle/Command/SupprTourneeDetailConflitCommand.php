<?php 
namespace Ams\DistributionBundle\Command;

//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Ijanki\Bundle\FtpBundle\Exception\FtpException;

use Ams\SilogBundle\Command\GlobalCommand;
use Doctrine\DBAL\DBALException;

/**
 * 
 * Suppression des lignes dans la table "tournee_detail" ou la tournee n'est pas coherent par rapport au depot de M-ROAD
 * 
 * Par defaut, on ne fait le calcul que le jour J+1.
 * Parametre : 
 *          Jour minimum a calculer. C'est optionnel
 *          Jour maximum a calculer. C'est optionnel
 *          Flux "jour" ou "nuit" [--jn=..]. Par defaut, c'est "nuit"
 *          Environnement [--env=..]
 * Expl : J+1 J+5
 * Si les parametres sont renseignes, le traitement concerne les jours de distribution de "jour minimum" a "jour maximum"
 * Expl : J-1. => calculs a faire concernent J-1, J, J+1, J+2 & J+3
 * 
 * Exemple de commande : 
 *                      php app/console suppr_tournee_detail_conflit J+0 J+3 --jn=nuit --env=prod
 * 
 * 
 * 	
 * 
 * @author aandrianiaina
 *
 */
class SupprTourneeDetailConflitCommand extends GlobalCommand
{
    protected function configure()
    {
    	$this->sNomCommande	= 'suppr_tournee_detail_conflit';
        $sJourATraiterMinParDefaut = "J+30";
        $sJourATraiterMaxParDefaut = "J+30";
        $sJourOuNuitDefaut = "nuit";
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console suppr_tournee_detail_conflit Expl : php app/console suppr_tournee_detail_conflit J-30 J-10 --jn=nuit --env=prod
        $this
            ->setDescription('Divers traitements concernant les abonnes et produits Neopress.')
            ->addArgument('jour_a_traiter_min', InputArgument::OPTIONAL, 'Jour a traiter Min. Format : J<+Numerique> (Expl : J+1 ou J+2 ou J ...)', $sJourATraiterMinParDefaut)
            ->addArgument('jour_a_traiter_max', InputArgument::OPTIONAL, 'Jour a traiter Max. Format : J<+Numerique> (Expl : J+1 ou J+2 ou J ...)', $sJourATraiterMaxParDefaut)
            ->addOption('jn',null, InputOption::VALUE_REQUIRED, 'jour et/ou nuit ?', $sJourOuNuitDefaut)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
        
        $sCodeTraitement    = "SUPPR_TOURNEE_DETAIL_CONFLIT";
        
        $sJourOuNuit    = "nuit";
        
        if ($input->getOption('jn')) {
            $sJourOuNuit   = $input->getOption('jn');
        }
        $flux_id = 1;
        if($sJourOuNuit=='jour')
        {
            $flux_id = 2;
        }
        $em    = $this->getContainer()->get('doctrine')->getManager();
        $sJourATraiterMin  = $input->getArgument('jour_a_traiter_min');
        $sJourATraiterMax  = $input->getArgument('jour_a_traiter_max');
        
        // Repertoire ou sauvegarde le fichier généré
        $this->sRepTmp  = $this->cree_repertoire($this->sRepFichiersPrinc.'/'.$this->getContainer()->getParameter('SOUSREP_FICHIERS_TMP').'/'.$sCodeTraitement);
        
    	$this->oLog->info(date("d/m/Y H:i:s : ")."Debut Suppression des lignes dans la table tournee_detail ou la tournee n est pas coherent par rapport au depot de M-ROAD - Commande : ".$this->sNomCommande." ".$sJourATraiterMin." ".$sJourATraiterMax." --jn=".$sJourOuNuit);    
        
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
                $this->oLog->erreur("Le jour MAX est anterieur au Jour MIN (Jour min : J".(($iJourATraiterMin>=0)?"+":"-").abs($iJourATraiterMin).". Jour max : J".(($iJourATraiterMax>=0)?"+":"-").abs($iJourATraiterMax).").", E_USER_WARNING);
            }
        }
        else
        {
            $this->oLog->erreur("Jour a traiter. Format : J<+Numerique> (Expl : J-1 ou J-2 ou J ...)", E_USER_WARNING);
        }
        
        $aRequetes  = array();
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
        
        //print_r($aoJourATraiter);
        
        foreach ($aoJourATraiter as $iJourATraiter => $oDateATraiterV)
        {
            $this->oLog->info("- Debut Recuperation date ".$oDateATraiterV->format('d/m/Y'));
            $sSlct  = " SELECT 
                            mtj.code, csl.abonne_soc_id
                            , CONCAT('DELETE FROM tournee_detail WHERE modele_tournee_jour_code=''', mtj.code, ''' AND num_abonne_id=''', csl.abonne_soc_id, '''; ') AS requete
                        FROM
                            client_a_servir_logist csl
                            LEFT JOIN depot_commune dc ON csl.commune_id = dc.commune_id 
                            LEFT JOIN depot d_mroad ON dc.depot_id=d_mroad.id	
                            LEFT JOIN modele_tournee_jour mtj ON csl.tournee_jour_id=mtj.id
                        WHERE
                            1 = 1
                            AND csl.date_distrib = '".$oDateATraiterV->format('Y-m-d')."' 
                            AND csl.flux_id = 1
                            AND SUBSTRING(mtj.code, 1, 3) <> d_mroad.code
                            AND d_mroad.id IS NOT NULL
                        GROUP BY
                            mtj.code, csl.abonne_soc_id                
                     ";
            $res    = $em->getConnection()->fetchAll($sSlct);
            foreach($res as $aArr) {
                if(!in_array($aArr['requete'], $aRequetes))
                {
                    $aRequetes[]    = $aArr['requete'];
                }
            }
        }
        
        if(!empty($aRequetes))
        {
            $sFichierSortie    = date("YmdHis")."_".$sCodeTraitement.".txt";
            if ($oFichierSortie = fopen($this->sRepTmp.'/'.$sFichierSortie,"w+"))
            {
                foreach($aRequetes as $req)
                {
                    fwrite($oFichierSortie, $req."\n");
                }
                fclose($oFichierSortie);
                
                $this->oLog->info(" ---------------------------------------------------------------------- "); 
                $this->oLog->info(" |   Lancer les requetes du fichier ".$this->sRepTmp.'/'.$sFichierSortie."   | "); 
                $this->oLog->info(" ---------------------------------------------------------------------- "); 
            }
            
        }
        
        $this->oLog->info(date("d/m/Y H:i:s : ")."Fin Suppression des lignes dans la table tournee_detail ou la tournee n est pas coherent par rapport au depot de M-ROAD - Commande : ".$this->sNomCommande." ".$sJourATraiterMin." ".$sJourATraiterMax." --jn=".$sJourOuNuit); 
        
        return;
    }
    
    
    
		
    /**
     * Suuppression des accents
     */
    private function suppr_accent($str, $encodage='utf-8')
    {
        $str = htmlentities($str, ENT_NOQUOTES, $encodage);
        $str = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
        $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
        $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères  
        return $str;
    }

    
}
