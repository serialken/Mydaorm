<?php 
namespace Ams\DistributionBundle\Command;

//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Ams\SilogBundle\Command\GlobalCommand;
use Doctrine\DBAL\DBALException;

/**
 * 
 * Suppression des lignes dans la table "tournee_detail" si un changement d'adresse a lieu
 * 
 * 
 * @author aandrianiaina
 *
 */
class ChgtAdresseSupprTourneesCommand extends GlobalCommand
{
    private $jourATraiterParDefaut;
    private $genereFicResumeChgtAdresse;
    
    protected function configure()
    {
    	$this->sNomCommande	= 'chgt_adresse_suppr_tournees';
        $sJourATraiterParDefaut = "J+1";
        $this->jourATraiterParDefaut   = $sJourATraiterParDefaut;
        $iGenereFicResumeChgtAdresseDefaut    = 0;
        $this->genereFicResumeChgtAdresseDefaut   = $iGenereFicResumeChgtAdresseDefaut;
        $this->genereFicResumeChgtAdresse   = $iGenereFicResumeChgtAdresseDefaut;
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console chgt_adresse_suppr_tournees J+1 --fic_resume=1  Expl : php app/console chgt_adresse_suppr_tournees J+1 --fic_resume=1 --env=prod
        $this
            ->setDescription('Suppression des lignes dans la table "tournee_detail" si un changement d adresse a lieu.')
            ->addArgument('jour_a_traiter', InputArgument::OPTIONAL, 'Jour a traiter. Format : J<+Numerique> (Expl : J+1 ou J+2 ou J ...)', $sJourATraiterParDefaut)
            ->addOption('fic_resume',null, InputOption::VALUE_OPTIONAL, 'Generer fichiers de resumes des changements d adresse ?. Valeur : 1 ou 0', $iGenereFicResumeChgtAdresseDefaut)
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
    	$this->oLog->info(date("d/m/Y H:i:s : ")."Debut Suppression des lignes dans la table 'tournee_detail' si un changement d'adresse a lieu - Commande : ".$this->sNomCommande);    
                
        $em    = $this->getContainer()->get('doctrine')->getManager();
        
        $this->genereFicResumeChgtAdresse   = ($input->getOption('fic_resume') ? intval($input->getOption('fic_resume')) : $this->genereFicResumeChgtAdresseDefaut);
        $aAbonneSoc_A_Suppr = array();
        $aDetail_A_Suppr = array(); // A suppimer si changement par rapport au passe et aucun chgmt par rapport au futur
        $aAbonneSoc_0_Chgt_passe = array();
        $aDetail_0_Chgt_passe = array();
        $aAbonneSoc_Avec_Chgt_PasseEtFutur = array();
        $aDetail_Avec_Chgt_PasseEtFutur = array();
        
        
        $sJourATraiter  = $input->getArgument('jour_a_traiter');
        $iJourATraiter  = 0;
        $aiJourATraiter   = array();
        $aoJourATraiter   = array();
        if(preg_match('/^J([\-\+][0-9]+)?$/', $sJourATraiter, $aiJourATraiterParam))
        {
            $iJourATraiter = 0;
            $iJourATraiterMax = 0;
            if(isset($aiJourATraiterParam[1]))
            {
                $iJourATraiter  = intval($aiJourATraiterParam[1]);
                $aiJourATraiter[]    = $iJourATraiter;
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
                // Selectionner de "client_a_servir_logist" et "adresse" les adresses susceptibles d'avoir changees
                // Pour chacun des abonnes concernes :
                //      . verifier si son adresse le jour a traiter est pareille que celle de son precedent jour de distribution
                //      . s'il y a changement (colet4-insee & pt_livraison different), verifier du "client_a_servir_logist" pour les dates futures et dont la tournee est renseignee si l'adresse est pareille que le jour a traiter
                //      . si pareille -> suppression de toutes les lignes de "tournees_detail" de cet abonne
                //      . si aucune distribution auparavant -> Par defaut, on ne supprime pas de lignes dans "tournees_detail" 
                
                // En gros, on supprime toutes les lignes de "tournees_detail" de cet abonne si l'adresse, l'adresse normalisee et le point de livraison ont change

                
                $sSlctEventuelChgtAdr  = " SELECT 
                                            csl.id AS csl_id_ref, csl.abonne_soc_id AS abonne_soc_id_ref,  csl.adresse_id, a.vol3 AS a_vol3, a.vol4 AS a_vol4, a.vol5 AS a_vol5, a.cp AS a_cp, a.ville AS a_ville, a.commune_id,
                                            ar.cadrs AS ar_cadrs, ar.adresse AS ar_adresse, ar.lieudit AS ar_lieudit, ar.cp AS ar_cp, ar.ville AS ar_ville, ar.insee AS ar_insee, 
                                            pt_livr.cadrs AS pt_livr_cadrs, pt_livr.adresse AS pt_livr_adresse, pt_livr.lieudit AS pt_livr_lieudit, pt_livr.cp AS pt_livr_cp, pt_livr.ville AS pt_livr_ville, pt_livr.insee AS pt_livr_insee
                                        FROM
                                            client_a_servir_logist csl
                                            INNER JOIN adresse a ON csl.adresse_id = a.id AND csl.date_distrib = a.date_debut
                                            INNER JOIN adresse_rnvp ar ON csl.rnvp_id = ar.id
                                            LEFT JOIN adresse_rnvp pt_livr ON csl.point_livraison_id = pt_livr.id
                                        WHERE
                                            csl.date_distrib = '".$oDateATraiterV->format('Y-m-d')."'
                                            /* AND csl.abonne_soc_id IN (624, 4768) */
                                        GROUP BY csl.abonne_soc_id ";
                $rSlctEventuelChgtAdr  = $em->getConnection()->fetchAll($sSlctEventuelChgtAdr);
                foreach($rSlctEventuelChgtAdr as $aArr)
                {
                    // Pour chaque abonne, verifier si son adresse le jour a traiter est pareille que celle de son precedent jour de distribution
                    
                    // Verifier si l'abonne courant a une historique dans client_a_servir_logist
                    $iHisto = 0;
                    $sSlctVerifHisto    = " SELECT
                                                COUNT(*) nb
                                            FROM
                                                client_a_servir_logist csl
                                                INNER JOIN adresse a ON csl.adresse_id = a.id /* AND csl.date_distrib = a.date_debut */
                                                INNER JOIN adresse_rnvp ar ON csl.rnvp_id = ar.id
                                            WHERE
                                                csl.abonne_soc_id = ".$aArr['abonne_soc_id_ref']."
                                                AND csl.date_distrib < '".$oDateATraiterV->format('Y-m-d')."'
                                             ";
                    $rSlctVerifHisto  = $em->getConnection()->fetchAll($sSlctVerifHisto);
                    foreach($rSlctVerifHisto as $aArr0)
                    {
                        $iHisto = $aArr0['nb'];
                    }
                    
                    if($iHisto > 0)
                    {
                        // Recuperer les infos adresse de cet abonne lors de la derniere distribution juste precedente du jour de traitement
                        $sSlctCASLPasse    = " SELECT
                                                    csl.id AS csl_id_passe, csl.date_distrib
                                                FROM
                                                    client_a_servir_logist csl
                                                    INNER JOIN adresse a ON csl.adresse_id = a.id /* AND csl.date_distrib = a.date_debut */
                                                    INNER JOIN adresse_rnvp ar ON csl.rnvp_id = ar.id
                                                WHERE
                                                    csl.abonne_soc_id = ".$aArr['abonne_soc_id_ref']."
                                                    AND csl.date_distrib < '".$oDateATraiterV->format('Y-m-d')."'
                                                ORDER BY csl.date_distrib DESC
                                                LIMIT 1
                                                 ";
                        $rSlctCASLPasse  = $em->getConnection()->fetchAll($sSlctCASLPasse);
                        foreach($rSlctCASLPasse as $aArr1)
                        {
                            $aiCASL_id_a_verifier   = array();
                            $aiCASL_id_a_verifier[] = $aArr1['csl_id_passe'];
                            
                            $aChangementAdressePasse  = $this->isChangementAdresse($em, $aArr['csl_id_ref'], $aiCASL_id_a_verifier, 'passe');
                            
                            if($aChangementAdressePasse['est_chgt_adr'] === 1)
                            {
                                $aChangementAdresseFutur  = array();
                                // Verification de changement d'adresse dans le futur et dont CASL.tournee_jour_id est renseigne
                                $iChgmtAdrFutur = 0;
                                $aiCASL_id_a_verifier_futur = array();
                                $sSlctCASLFutur    = " SELECT
                                                            csl.abonne_soc_id, csl.date_distrib, csl.id AS csl_futur_id
                                                        FROM
                                                            client_a_servir_logist csl
                                                        WHERE
                                                            csl.abonne_soc_id = ".$aArr['abonne_soc_id_ref']."
                                                            AND csl.date_distrib > '".$oDateATraiterV->format('Y-m-d')."'
                                                            AND csl.tournee_jour_id IS NOT NULL
                                                        GROUP BY 
                                                            csl.abonne_soc_id, csl.date_distrib
                                                        ORDER BY 
                                                            csl.abonne_soc_id, csl.date_distrib
                                                    ";
                                $rSlctCASLFutur  = $em->getConnection()->fetchAll($sSlctCASLFutur);
                                foreach($rSlctCASLFutur as $aArr3)
                                {
                                    $aiCASL_id_a_verifier_futur[]   = $aArr3['csl_futur_id'];
                                }
                                if(!empty($aiCASL_id_a_verifier_futur))
                                {
                                    $aChangementAdresseFutur  = $this->isChangementAdresse($em, $aArr['csl_id_ref'], $aiCASL_id_a_verifier_futur, 'futur');
                                    $iChgmtAdrFutur = $aChangementAdresseFutur['est_chgt_adr'];
                                }
                                
                                // S'il n'y a pas de changement d'adresse dans le futur par rapport a l'adresse du jour de traitement, on supprime les lignes de l'abonne de tournee_detail
                                if($iChgmtAdrFutur===0)
                                {
                                    $aAbonneSoc_A_Suppr[]   = $aArr['abonne_soc_id_ref'];
                                    $aDetail_A_Suppr[$aArr['abonne_soc_id_ref']][] = $aChangementAdressePasse;
                                    
                                    $sDelete    = " DELETE FROM tournee_detail WHERE num_abonne_id = ".$aArr['abonne_soc_id_ref']." ";
                                    $em->getConnection()->executeQuery($sDelete);
                                    $em->clear();
                                    $this->oLog->info(date("d/m/Y H:i:s : ").$sDelete);
                                    echo "\r\n$sDelete\r\n";
                                    
                                    $sUpdate    = " UPDATE client_a_servir_logist SET tournee_jour_id = NULL, point_livraison_ordre = NULL WHERE abonne_soc_id = ".$aArr['abonne_soc_id_ref']." AND date_distrib > CURDATE() ";
                                    $em->getConnection()->executeQuery($sUpdate);
                                    $em->clear();
                                    $this->oLog->info(date("d/m/Y H:i:s : ").$sUpdate);
                                    echo "\r\n$sUpdate\r\n";
                                    
                                }
                                else
                                {
                                    // Attention !!! Changement d'adresse entre J traitement et passe + Changement d'adresse entre J traitement et futur
                                    // Aucun traitement automatique a faire
                                    $aAbonneSoc_Avec_Chgt_PasseEtFutur[]   = $aArr['abonne_soc_id_ref'];
                                    $aDetail_Avec_Chgt_PasseEtFutur[$aArr['abonne_soc_id_ref']][] = $aChangementAdressePasse;
                                    $aDetail_Avec_Chgt_PasseEtFutur[$aArr['abonne_soc_id_ref']][] = $aChangementAdresseFutur;
                                }                                    
                            }
                            else
                            {
                                // Pas de changement d'adresse par rapport au passe => on ne fait rien
                                $this->oLog->info(date("d/m/Y H:i:s : ")." date_distrib=".$oDateATraiterV->format('Y-m-d')." , abonne_soc_id=".$aArr['abonne_soc_id_ref']." ==> aucun changement d adresse par rapport au passe ");
                                $aAbonneSoc_0_Chgt_passe[]  = $aArr['abonne_soc_id_ref'];
                                $aDetail_0_Chgt_passe[$aArr['abonne_soc_id_ref']][] = $aChangementAdressePasse;
                            }
                        }
                    }
                    else
                    {
                        //$this->oLog->info(date("d/m/Y H:i:s : ")."Aucune historique date anterieure a ".$oDateATraiterV->format('d/m/Y')." trouve de l abonne_soc_id ".$aArr['abonne_soc_id_ref']." -> Aucune suppression de lignes de tournee_detail");
                    }
                }
                
                
                if($this->genereFicResumeChgtAdresse > 0)
                {
                    echo "\r\nGeneration des fichiers de LOG\r\n";
                    // $this->sRepFichiersPrinc.'/'.$this->getContainer()->getParameter('REP_LOGS')
                    $sDateYmdHis = date("Ymd_His");
                    $sFicASuppr = "__".$oDateATraiterV->format('Ymd')."_"."chgt_adr_a_supprimer_".$sDateYmdHis.".txt";
                    $sFic0ChgtPasse = "__".$oDateATraiterV->format('Ymd')."_"."chgt_adr_0_chgt_passe_".$sDateYmdHis.".txt";
                    $sFicAvecChgtPasseEtFutur = "__".$oDateATraiterV->format('Ymd')."_"."chgt_adr_avec_chgt_passe_et_futur_".$sDateYmdHis.".txt";
                    
                    
                    $path   = $this->sRepFichiersPrinc.'/'.$this->getContainer()->getParameter('REP_LOGS');
                    
                    if(!empty($aAbonneSoc_A_Suppr))
                    {
                        if(($oFicASuppr = fopen($path."/".$sFicASuppr, "w+")) !== false)
                        {
                            $this->ficRemplissage($oFicASuppr, $aAbonneSoc_A_Suppr, $aDetail_A_Suppr, 'a_suppr');
                            fclose($oFicASuppr);
                        }
                    }
                    
                    if(!empty($aAbonneSoc_0_Chgt_passe))
                    {
                        if(($oFic0ChgtPasse = fopen($path."/".$sFic0ChgtPasse, "w+")) !== false)
                        {
                            $this->ficRemplissage($oFic0ChgtPasse, $aAbonneSoc_0_Chgt_passe, $aDetail_0_Chgt_passe, '0_chgt_passe');
                            fclose($oFic0ChgtPasse);
                        }
                    }
                    
                    if(!empty($aAbonneSoc_Avec_Chgt_PasseEtFutur))
                    {
                        if(($oFicAvecChgtPasseEtFutur = fopen($path."/".$sFicAvecChgtPasseEtFutur, "w+")) !== false)
                        {
                            $this->ficRemplissage($oFicAvecChgtPasseEtFutur, $aAbonneSoc_Avec_Chgt_PasseEtFutur, $aDetail_Avec_Chgt_PasseEtFutur, 'avec_chgt_passe_et_futur');
                            fclose($oFicAvecChgtPasseEtFutur);
                        }
                    }
                }
                
                
                

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
        $this->oLog->info(date("d/m/Y H:i:s : ")."Fin Suppression des lignes dans la table 'tournee_detail' si un changement d'adresse a lieu - Commande : ".$this->sNomCommande);
        return;
    }
    
    
    
    private function isChangementAdresse($em, $iCASL_id_ref, $aiCASL_id_a_verifier=array(), $periode_verif='passe') 
    {
        $aRetour    = array();
        $aRetour['est_chgt_adr']    = 0;
        foreach($aiCASL_id_a_verifier as $iCASL_id_a_verifier)
        {
            $sSlctVerifChgtAdr    = " SELECT
                                            if(
                                                (
                                                    (ar_ref_adresse<>'' AND ar_verif_adresse<>'' AND ar_ref_adresse=ar_verif_adresse)
                                                    OR (pt_livr_ref_adresse<>'' AND pt_livr_verif_adresse<>'' AND pt_livr_ref_adresse=pt_livr_verif_adresse)
                                                )
                                                , 
                                                0, 
                                                1
                                                ) AS chgt_adresse_par_rapport_verif
                                            , ar_ref_adresse, ar_verif_adresse, pt_livr_ref_adresse, pt_livr_verif_adresse
                                            , ar_ref_insee, ar_verif_insee
                                            , csl_ref_date_distrib, csl_verif_date_distrib
                                            , csl_ref_id
                                            , csl_verif_id
                                            , abonne_soc_id, numabo_ext, soc_code_ext
                                        FROM
                                            (
                                                SELECT
                                                    csl_ref.id AS csl_ref_id, csl_ref.abonne_soc_id, DATE_FORMAT(csl_ref.date_distrib, '%Y/%m/%d') AS csl_ref_date_distrib,
                                                    a.numabo_ext, a.soc_code_ext, 
                                                    csl_ref.adresse_id AS csl_ref_adresse_id, csl_ref.commune_id AS csl_ref_commune_id, 
                                                    a_ref.vol3 AS a_ref_vol3, a_ref.vol4 AS a_ref_vol4, a_ref.vol5 AS a_ref_vol5, a_ref.cp AS a_ref_cp, a_ref.ville AS a_ref_ville, 
                                                    ar_ref.cadrs AS ar_ref_cadrs, ar_ref.adresse AS ar_ref_adresse, ar_ref.lieudit AS ar_ref_lieudit, ar_ref.cp AS ar_ref_cp, ar_ref.ville AS ar_ref_ville, ar_ref.insee AS ar_ref_insee, 
                                                    pt_livr_ref.cadrs AS pt_livr_ref_cadrs, pt_livr_ref.adresse AS pt_livr_ref_adresse, pt_livr_ref.lieudit AS pt_livr_ref_lieudit, pt_livr_ref.cp AS pt_livr_ref_cp, pt_livr_ref.ville AS pt_livr_ref_ville, pt_livr_ref.insee AS pt_livr_ref_insee,

                                                    csl_verif.id AS csl_verif_id, DATE_FORMAT(csl_verif.date_distrib, '%Y/%m/%d') AS csl_verif_date_distrib, csl_verif.adresse_id AS csl_verif_adresse_id, csl_verif.commune_id AS csl_verif_commune_id,  
                                                    a_verif.vol3 AS a_verif_vol3, a_verif.vol4 AS a_verif_vol4, a_verif.vol5 AS a_verif_vol5, a_verif.cp AS a_verif_cp, a_verif.ville AS a_verif_ville, 
                                                    ar_verif.cadrs AS ar_verif_cadrs, ar_verif.adresse AS ar_verif_adresse, ar_verif.lieudit AS ar_verif_lieudit, ar_verif.cp AS ar_verif_cp, ar_verif.ville AS ar_verif_ville, ar_verif.insee AS ar_verif_insee, 
                                                    pt_livr_verif.cadrs AS pt_livr_verif_cadrs, pt_livr_verif.adresse AS pt_livr_verif_adresse, pt_livr_verif.lieudit AS pt_livr_verif_lieudit, pt_livr_verif.cp AS pt_livr_verif_cp, pt_livr_verif.ville AS pt_livr_verif_ville, pt_livr_verif.insee AS pt_livr_verif_insee
                                                FROM
                                                    client_a_servir_logist csl_ref
                                                    INNER JOIN abonne_soc a ON csl_ref.abonne_soc_id = a.id
                                                    INNER JOIN adresse a_ref ON csl_ref.adresse_id = a_ref.id /* AND csl_ref.date_distrib = a_ref.date_debut */
                                                    INNER JOIN adresse_rnvp ar_ref ON csl_ref.rnvp_id = ar_ref.id
                                                    LEFT JOIN adresse_rnvp pt_livr_ref ON csl_ref.point_livraison_id = pt_livr_ref.id

                                                    INNER JOIN client_a_servir_logist csl_verif
                                                    INNER JOIN adresse a_verif ON csl_verif.adresse_id = a_verif.id /* AND csl_verif.date_distrib = a_verif.date_debut */
                                                    INNER JOIN adresse_rnvp ar_verif ON csl_verif.rnvp_id = ar_verif.id
                                                    LEFT JOIN adresse_rnvp pt_livr_verif ON csl_verif.point_livraison_id = pt_livr_verif.id
                                                WHERE
                                                    csl_ref.id = ".$iCASL_id_ref."
                                                    AND csl_verif.id = ".$iCASL_id_a_verifier."
                                            ) t
                                     ";
            $rSlctVerifChgtAdr  = $em->getConnection()->fetchAll($sSlctVerifChgtAdr);
            foreach($rSlctVerifChgtAdr as $aArr2)
            {
                $aInfosAdr  = array();
                $aInfosAdr[$periode_verif]['abonne_soc_id'] = $aArr2['abonne_soc_id'];
                $aInfosAdr[$periode_verif]['numabo_ext'] = $aArr2['numabo_ext'];
                $aInfosAdr[$periode_verif]['soc_code_ext'] = $aArr2['soc_code_ext'];
                $aInfosAdr[$periode_verif]['csl_ref_date_distrib'] = $aArr2['csl_ref_date_distrib'];
                $aInfosAdr[$periode_verif]['csl_verif_date_distrib'] = $aArr2['csl_verif_date_distrib'];
                $aInfosAdr[$periode_verif]['ar_ref_adresse'] = $aArr2['ar_ref_adresse'];
                $aInfosAdr[$periode_verif]['ar_verif_adresse'] = $aArr2['ar_verif_adresse'];
                $aInfosAdr[$periode_verif]['ar_ref_insee'] = $aArr2['ar_ref_insee'];
                $aInfosAdr[$periode_verif]['ar_verif_insee'] = $aArr2['ar_verif_insee'];
                $aInfosAdr[$periode_verif]['pt_livr_ref_adresse'] = $aArr2['pt_livr_ref_adresse'];
                $aInfosAdr[$periode_verif]['pt_livr_verif_adresse'] = $aArr2['pt_livr_verif_adresse'];
                if($aArr2['chgt_adresse_par_rapport_verif']==1)
                {
                    $aRetour['est_chgt_adr']    = 1;
                    $aInfosAdr[$periode_verif]['chgt adresse'] = 1;
                }
                $aRetour['details'][]    = $aInfosAdr;
            }
        }
        return $aRetour;
    }

    private function ficRemplissage($oFic, $aIdConcernes, $aDetails=array(), $sType='')
    {
        $LN = "\n";
        // Nombre abonnes concernes
        $sStr   = "Nombre d'abonnes concernes : ".count($aIdConcernes).$LN.$LN.$LN;
        fwrite($oFic, $sStr);
        
        $sStr   = "SELECT * FROM adresse WHERE abonne_soc_id IN (".implode(', ', $aIdConcernes).") ORDER BY abonne_soc_id, date_debut DESC;".$LN.$LN.$LN;
        fwrite($oFic, $sStr);
        
        if(in_array($sType, array('a_suppr')))
        {
            $sStr   = "--------- tournee_detail : ".$LN."------------------------------------------".$LN;
            fwrite($oFic, $sStr);
            foreach($aIdConcernes as $iAboSocId)
            {
                $sStr   = "SELECT * FROM tournee_detail WHERE num_abonne_id = ".$iAboSocId." ORDER BY jour_id;".$LN;
                $sStr   .= "SELECT * FROM adresse WHERE abonne_soc_id = ".$iAboSocId." ORDER BY date_debut DESC;".$LN;
                $sStr   .= "SELECT * FROM client_a_servir_logist WHERE abonne_soc_id = ".$iAboSocId." ORDER BY date_distrib DESC;".$LN;
                $sStr   .= "SELECT * FROM client_a_servir_src WHERE abonne_soc_id = ".$iAboSocId." ORDER BY date_distrib DESC;".$LN;
                $sStr   .= "DELETE FROM tournee_detail WHERE num_abonne_id = ".$iAboSocId.";".$LN;
                $sStr   .= $LN.$LN;
                fwrite($oFic, $sStr);
            }
        }
        else
        {
            // requete client_a_servir_logist desc
            foreach($aIdConcernes as $iAboSocId)
            {
                $sStr   = "SELECT * FROM client_a_servir_logist WHERE abonne_soc_id = ".$iAboSocId." ORDER BY date_distrib DESC;".$LN;
                $sStr   .= "SELECT * FROM client_a_servir_src WHERE abonne_soc_id = ".$iAboSocId." ORDER BY date_distrib DESC;".$LN;
                fwrite($oFic, $sStr);
            }
        }
        
        // Details
        foreach($aIdConcernes as $iAboSocId)
        {
            if(isset($aDetails[$iAboSocId]))
            {
                $sStr   = "--------- Detail abonne_soc_id : ".$iAboSocId.$LN;
                fwrite($oFic, $sStr);
                foreach($aDetails[$iAboSocId] AS $aArr)
                {
                    $sStr   = $LN;
                    fwrite($oFic, print_r($aArr, TRUE));
                }
            }
            $sStr   = "SELECT * FROM client_a_servir_logist WHERE abonne_soc_id = ".$iAboSocId." ORDER BY date_distrib DESC;".$LN;
            fwrite($oFic, $sStr);
        }
        
        if(in_array($sType, array('a_suppr')))
        {
            $sStr   = $LN.$LN.$LN.$LN."--------- SUPPRESSION tournee_detail : ".$LN."------------------------------------------".$LN;
            foreach($aIdConcernes as $iAboSocId)
            {
                $sStr   .= "DELETE FROM tournee_detail WHERE abonne_soc_id = ".$iAboSocId.";".$LN;
            }
            fwrite($oFic, $sStr);
        }
    }
}
