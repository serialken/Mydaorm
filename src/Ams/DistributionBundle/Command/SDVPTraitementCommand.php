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
 * Traitements specifiques concernant les produits de SDVP
 * 
 * - Tournees
 * 
 * Par defaut, on ne fait le calcul que le jour J+1.
 * Parametre : 
 *          Jour minimum a calculer. C'est optionnel
 *          Jour maximum a calculer. C'est optionnel
 * Expl : J+1 J+5
 * Si les parametres sont renseignes, le traitement concerne les jours de distribution de "jour minimum" a "jour maximum"
 * Expl : J-1. => calculs a faire concernent J-1, J, J+1, J+2 & J+3
 * 
 * Exemple de commande : 
 *                      php app/console sdvp_traitement J+0 J+3 --id_sh=cron_test --id_ai=1  --env=prod
 * 
 * 
 * 	Verification des lignes qui n'a pas de tournee
SELECT
	csl.date_distrib, p.libelle, count(*) AS nb
FROM
	client_a_servir_logist csl
	LEFt JOIN produit p ON csl.produit_id = p.id AND p.libelle LIKE '(%'
WHERE
	1 = 1
	AND csl.date_distrib = '2014/09/01'
	AND p.id IS NOT NULL
	AND csl.tournee_jour_id IS NULL
GROUP BY
	csl.date_distrib, p.libelle
ORDER BY
	csl.date_distrib, p.libelle, nb 
 * 
 * 
 * 
 * @author aandrianiaina
 *
 */
class SDVPTraitementCommand extends GlobalCommand
{
    protected function configure()
    {
    	$this->sNomCommande	= 'sdvp_traitement';
        $sJourATraiterMinParDefaut = "J+1";
        $sJourATraiterMaxParDefaut = "J+2";
        $this->jourATraiterMaxRef   = 3;
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console sdvp_traitement Expl : php app/console sdvp_traitement J+1 J+3 --id_sh=cron_test --id_ai=1  --env=prod
        $this
            ->setDescription('Divers traitements concernant les abonnes et produits Neopress.')
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
        $aFTP   = array(
                        "SERVEUR"   => "10.151.93.2"
                        , "LOGIN"   => "SDVP-LP"
                        , "MDP"     => "Sdvp753"
                        , "SS_REPERTOIRE" => "./PRD/LP_TEMPO/TOU"
                        );
        
        $em    = $this->getContainer()->get('doctrine')->getManager();
        $conn    = $em->getConnection();
        
    	$sJourATraiterMin  = $input->getArgument('jour_a_traiter_min');
        $sJourATraiterMax  = $input->getArgument('jour_a_traiter_max');
        $this->oLog->info(date("d/m/Y H:i:s : ")."Debut Divers traitements concernant les abonnes et produits SDVP - Commande : ".$this->sNomCommande.' '.$sJourATraiterMin.' '.$sJourATraiterMax);    
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
        
        
        $aDatesYmdARecuperer    = array();
        foreach ($aoJourATraiter as $iJourATraiter => $oDateATraiterV)
        {
            $aDatesYmdARecuperer[]  = $oDateATraiterV->format('Ymd');
        }
        
        
        
        $sSepCSV        = "|";
        $sFicRegexARecuperer    = "/^(\.\/)?(SDVP)(".implode("|", $aDatesYmdARecuperer).")\.txt$/i";
        $aFichierATraiter   = array();
        
        $aCodesSocTit	= array();
        $sSlctCodesSocTit	= "	SELECT
                                            id, societe_id, soc_code_ext, prd_code_ext, spr_code_ext, flux_id, prd_code_neopress
                                        FROM
                                            produit
                                        WHERE
                                            prd_code_neopress IS NOT NULL";
        $aResSelect = $em->getConnection()->fetchAll($sSlctCodesSocTit);
        foreach($aResSelect as $aRes)
        {
            $aCodesSocTit[$aRes['flux_id']][strtoupper($this->suppr_accent(trim($aRes['prd_code_neopress'])))]['soc_code_ext']	= trim($aRes['soc_code_ext']);
            $aCodesSocTit[$aRes['flux_id']][strtoupper($this->suppr_accent(trim($aRes['prd_code_neopress'])))]['prd_code_ext']	= trim($aRes['prd_code_ext']);
            $aCodesSocTit[$aRes['flux_id']][strtoupper($this->suppr_accent(trim($aRes['prd_code_neopress'])))]['spr_code_ext']	= trim($aRes['spr_code_ext']);
            $aCodesSocTit[$aRes['flux_id']][strtoupper($this->suppr_accent(trim($aRes['prd_code_neopress'])))]['societe_id']	= trim($aRes['societe_id']);
            $aCodesSocTit[$aRes['flux_id']][strtoupper($this->suppr_accent(trim($aRes['prd_code_neopress'])))]['produit_id']	= trim($aRes['id']);
            $aCodesSocTit[$aRes['flux_id']][strtoupper($this->suppr_accent(trim($aRes['prd_code_neopress'])))]['flux_id']	= trim($aRes['flux_id']);
        }
        $em->clear();
        
    	// Repertoire ou l'on recupere les fichiers a traiter
        $this->sRepTmp	= $this->cree_repertoire($this->sRepFichiersPrinc.'/'.$this->getContainer()->getParameter('SOUSREP_FICHIERS_TMP').'/'.'SDVPTraitement');
        

        // Repertoire Backup Local
    	$this->sRepBkpLocal	= $this->cree_repertoire($this->sRepFichiersPrinc.'/'.$this->getContainer()->getParameter('SOUSREP_FICHIERS_BKP').'/'.'SDVPTraitement');
        
        // Connexion au FTP
        try 
        {
            $srv_ftp    = $this->getContainer()->get('ijanki_ftp');
            
            $srv_ftp->connect($aFTP["SERVEUR"]);
            $srv_ftp->login($aFTP["LOGIN"], $aFTP["MDP"]);
            $srv_ftp->chdir($aFTP["SS_REPERTOIRE"]);
            $aTousFicFTP = $srv_ftp->nlist('.');
            
            
            $this->oLog->info("- Debut Importation des fichiers depuis le serveur ".$aFTP["SERVEUR"]."/".$aFTP["SS_REPERTOIRE"]);
            // Importation de tous les fichiers a traiter
            if(!empty($aTousFicFTP))
            {
                foreach($aTousFicFTP as $sFicV)
                {
                    if(preg_match_all($sFicRegexARecuperer, $sFicV, $aArrReg))
                    {
                        if(file_exists($this->sRepTmp.'/'.$sFicV) && is_writable($this->sRepTmp.'/'.$sFicV))
                        {
                            unlink($this->sRepTmp.'/'.$sFicV);
                        }
                        if(!file_exists($this->sRepTmp.'/'.$sFicV))
                        {
                            if($srv_ftp->get($this->sRepTmp.'/'.$sFicV, $sFicV, FTP_BINARY)===false)
                            {
                                $this->suiviCommand->setMsg("Probleme d'importation du fichier ".$sFicV.'du FTP '.$aFTP["SERVEUR"].'/'.$aFTP["SS_REPERTOIRE"]);
                                $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
                                $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                                $this->suiviCommand->setEtat("KO");
                                $this->oLog->erreur("Probleme d'importation du fichier ".$sFicV.'du FTP '.$aFTP["SERVEUR"].'/'.$aFTP["SS_REPERTOIRE"], E_USER_ERROR);
                                $this->registerError();
                                if($input->getOption('id_ai') && $input->getOption('id_sh')){
                                    $this->registerErrorCron($idAi);
                                }
                            }
                            else 
                            {
                                $this->oLog->info("Fichier importe du FTP ".$aFTP["SERVEUR"].'/'.$aFTP["SS_REPERTOIRE"].' : '.$sFicV);
                                $date	= $aArrReg[3][0];
				$aFichierATraiter[substr($date, 0, 4).'-'.substr($date, 4, 2).'-'.substr($date, 6, 2)][]	= $sFicV;
                            }
                        }
                    }
                }
            }
            
            $srv_ftp->close();
        } 
        catch (FtpException $e) 
        {
             $this->suiviCommand->setMsg("Probleme d'acces au FTP ".$aFTP["SERVEUR"].' : '.$e->getMessage());
            $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
            $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
            $this->suiviCommand->setEtat("KO");
            $this->oLog->erreur("Probleme d'acces au FTP ".$aFTP["SERVEUR"].' : '.$e->getMessage(), E_USER_ERROR);
            $this->registerError();
            if($input->getOption('id_ai') && $input->getOption('id_sh')){
                $this->registerErrorCron($idAi);
            }
        }
      
        ksort($aFichierATraiter); 
        
            
        if(!empty($aFichierATraiter))
        {
            foreach($aFichierATraiter as $sDateDistrib => $aArrFic)
            {
                foreach($aArrFic as $sFicV)
                {
                    try {
                        $this->oLog->info("- Debut Traitement du fichier ".$sFicV);

                        /*
                         * 
                                0   => num parution
                                1   => date parution
                                2   => num abo
                                3   => vol1
                                4   => vol2
                                5   => vol3
                                6   => vol4
                                7   => vol5
                                8   => cp
                                9   => ville
                                10  => type dis. (SERVICE | ARRETES/SUSPENDUS | NOUVEAUX)
                                11  => societe
                                12  => titre
                                13  => edition
                                14  => nb exemplaire
                                15  => info 1
                                16  => info 2
                                17  => info 3
                                18  => info 4
                                19  => code depot DCS
                                20  => code tournee DCS
                                21  => ordre tournee DCS
                         * 
                         */
                        $sqlLoadDataInFile     = " LOAD DATA LOCAL INFILE '".$this->sRepTmp.'/'.$sFicV."' 
                                                    INTO TABLE sdvp_traitement_tmp
                                                    FIELDS TERMINATED BY '".$sSepCSV."'  ESCAPED BY '\\\\'
                                                        LINES TERMINATED BY '\\n'
                                                    (
                                                        @COL_0, @COL_1, @COL_2, @COL_3, @COL_4
                                                        , @COL_5, @COL_6, @COL_7, @COL_8, @COL_9
                                                        , @COL_10, @COL_11, @COL_12, @COL_13, @COL_14
                                                        , @COL_15, @COL_16, @COL_17, @COL_18, @COL_19
                                                        , @COL_20, @COL_21
                                                    )
                                                    SET
                                                        num_parution = TRIM(@COL_0)
                                                        , date_distrib = TRIM(@COL_1)
                                                        , date_parution = TRIM(@COL_1)
                                                        , numabo_ext = TRIM(@COL_2)
                                                        , vol1 = TRIM(@COL_3)
                                                        , vol2 = TRIM(@COL_4)
                                                        , vol3 = TRIM(@COL_5)
                                                        , vol4 = TRIM(@COL_6)
                                                        , vol5 = TRIM(@COL_7)
                                                        , cp = TRIM(@COL_8)
                                                        , ville = TRIM(@COL_9)
                                                        , type_dis = TRIM(@COL_10)
                                                        , soc_code_ext = TRIM(@COL_11)
                                                        , prd_code_ext = TRIM(@COL_12)
                                                        , spr_code_ext = IF(TRIM(@COL_13)='','001',TRIM(@COL_13))
                                                        , qte = TRIM(@COL_14)
                                                        , divers1 = TRIM(@COL_15)
                                                        , info_comp1 = TRIM(@COL_16)
                                                        , info_comp2 = TRIM(@COL_17)
                                                        , divers2 = TRIM(@COL_18)
                                                        , depot_dcs = TRIM(@COL_19)
                                                        , tournee_dcs = TRIM(@COL_20)
                                                        , ordre_dcs = TRIM(@COL_21)
                                                    ;
                                                    ";


                        $Truncate    = $conn->prepare("TRUNCATE TABLE sdvp_traitement_tmp");
                        $Truncate->execute();

                        $aConnParam = $conn->getParams();
                        $nouveauPdoConn = new \PDO('mysql:host=' . $aConnParam['host'] . ';dbname=' .  $aConnParam['dbname'] , $aConnParam['user'], $aConnParam['password'], array(
                                        \PDO::MYSQL_ATTR_LOCAL_INFILE => true
                                    ));   
                        $Load    = $nouveauPdoConn->prepare($sqlLoadDataInFile);
                        $retour = $Load->execute(); 
                        if($retour!==true)
                        {
                            $this->suiviCommand->setMsg("Erreur lors de l integration du fichier ".$this->sRepTmp.'/'.$sFicV." dans la table sdvp_traitement_tmp ");
                            $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
                            $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                            $this->suiviCommand->setEtat("KO");
                            $this->oLog->erreur("Erreur lors de l integration du fichier ".$this->sRepTmp.'/'.$sFicV." dans la table sdvp_traitement_tmp ", E_USER_ERROR);
                            $this->registerError();
                            if($input->getOption('id_ai') && $input->getOption('id_sh')){
                                $this->registerErrorCron($idAi);
                            }
                        }
                        else
                        {
                            // Mise a jour abonne_soc_id
                            $sUpdateAbonneSoc    = "  UPDATE sdvp_traitement_tmp t
                                                        LEFT JOIN abonne_soc a ON t.numabo_ext = a.numabo_ext AND t.soc_code_ext = a.soc_code_ext
                                                        SET
                                                            t.abonne_soc_id = a.id
                                                    ";
                            $em->getConnection()->executeQuery($sUpdateAbonneSoc);
                            $em->clear();
                            
                            // Prise en compte des NOUVEAUX | ARRETES/SUSPENDUS 
                            $sUpdateNouveauAbonne    = "  UPDATE sdvp_traitement_tmp t
                                                        INNER JOIN abonne_soc a ON t.abonne_soc_id = a.id AND t.type_dis IN ('NOUVEAUX')
                                                        SET
                                                            a.date_service_1 = t.date_distrib
                                                    ";
                            $em->getConnection()->executeQuery($sUpdateNouveauAbonne);
                            $em->clear();                         
                            $sUpdateArretAbonne    = "  UPDATE sdvp_traitement_tmp t
                                                        INNER JOIN abonne_soc a ON t.abonne_soc_id = a.id AND t.type_dis IN ('ARRETES/SUSPENDUS')
                                                        SET
                                                            a.date_stop= t.date_distrib
                                                    ";
                            $em->getConnection()->executeQuery($sUpdateArretAbonne);
                            $em->clear();  

                            // Mise a jour societe_id
                            $sUpdateSoc    = "  UPDATE
                                                        sdvp_traitement_tmp t
                                                        LEFT JOIN (SELECT MIN(societe_id) AS societe_id, soc_code_ext FROM produit GROUP BY soc_code_ext) p ON t.soc_code_ext = p.soc_code_ext 
                                                    SET
                                                        t.societe_id = p.societe_id
                                                    ";
                            $em->getConnection()->executeQuery($sUpdateSoc);
                            $em->clear();

                            // Mise a jour depot_id
                            $sUpdateDepot    = " UPDATE sdvp_traitement_tmp t
                                                    LEFT JOIN depot d ON t.depot_dcs = d.code
                                                SET
                                                    t.depot_id = d.id
                                                ";
                            $em->getConnection()->executeQuery($sUpdateDepot);
                            $em->clear();

                            // Mise a jour des tournees 
                            $updateTournee  = " UPDATE
                                                    sdvp_traitement_tmp t
                                                    INNER JOIN modele_tournee mt ON t.tournee_dcs = mt.codeDCS AND mt.actif = 1 
                                                    INNER JOIN groupe_tournee gt ON gt.id = mt.groupe_id
                                                    INNER JOIN depot d ON gt.depot_id = d.id AND t.depot_dcs = d.code
                                                    LEFT JOIN modele_tournee_jour mtj ON mt.id = mtj.tournee_id 
                                                                                                                            AND mtj.jour_id=CAST(DATE_FORMAT(t.date_distrib, '%w') AS SIGNED)+1
                                                SET
                                                    t.tournee_id = mt.id
                                                    , t.tournee_jour_id = mtj.id
                                                    , t.tournee_jour_code = mtj.code
                                                    , t.jour_id = mtj.jour_id    
                                                WHERE
                                                    t.date_distrib BETWEEN mtj.date_debut AND mtj.date_fin
                                                ";
                            $em->getConnection()->executeQuery($updateTournee);
                            $em->clear(); 


                            // Mise a jour des champs geox, geoy, insee, point_livraison_id
                            $updateAdrInfosSupp  = " UPDATE
                                                        sdvp_traitement_tmp t
                                                        LEFT JOIN client_a_servir_logist csl ON t.date_distrib = csl.date_distrib AND t.abonne_soc_id = csl.abonne_soc_id 
                                                        LEFT JOIN adresse_rnvp r ON csl.point_livraison_id = r.id
                                                    SET
                                                        t.geox = r.geox
                                                        , t.geoy = r.geoy
                                                        , t.insee = r.insee
                                                        , t.point_livraison_id = csl.point_livraison_id
                                                    WHERE
                                                        csl.id IS NOT NULL ";
                            $em->getConnection()->executeQuery($updateAdrInfosSupp);
                            $em->clear(); 


                            // Ajout de nouvelles donnees de couple tournee/jour dans la table "tournee_detail"
                            $this->oLog->info("Ajout de nouvelles donnees de couple tournee/jour dans la table tournee_detail");
                            $insertNouvTournee  = " INSERT INTO tournee_detail 
                                                        (ordre, longitude, latitude, duree_conduite, heure_debut, 
                                                        duree, etat, distance_trajet, trajet_cumule, 
                                                        debut_plage_horaire, fin_plage_horaire, duree_viste_fixe, 
                                                        exclure_ressource, assigner_ressource, a_traiter, 
                                                        modele_tournee_jour_code, num_abonne_soc, num_abonne_id, 
                                                        nb_stop, temps_conduite, temps_tournee, temps_visite, 
                                                        ordre_stop, soc, titre, insee, flux_id, jour_id, point_livraison_id, date_modification, source_modification)
                                                    SELECT DISTINCT 
                                                        MAX(ordre_dcs) AS ordre, MAX(t.geox) AS longitude, MAX(t.geoy) AS latitude, NULL AS duree_conduite, NULL AS heure_debut,
                                                        NULL AS duree, 'A l\'heure' AS etat, NULL AS distance_trajet, NULL AS trajet_cumule, 
                                                        NULL AS debut_plage_horaire, NULL AS fin_plage_horaire, '00:00:30' AS duree_viste_fixe,
                                                        '' AS exclure_ressource, '' AS assigner_ressource, 1 AS a_traiter, 
                                                        t.tournee_jour_code AS modele_tournee_jour_code, t.numabo_ext AS num_abonne_soc, t.abonne_soc_id AS num_abonne_id,
                                                        NULL AS nb_stop, NULL AS temps_conduite, NULL AS temps_tournee, NULL AS temps_visite,
                                                        NULL AS ordre_stop, t.soc_code_ext AS soc, p.prd_code_ext AS titre, t.insee AS insee, 1 AS flux_id, t.jour_id, t.point_livraison_id, now() AS date_modification, 'SDVPTraitement - insert nouv. tournee CAS' as source_modification
                                                    FROM
                                                        sdvp_traitement_tmp t
                                                        LEFT JOIN tournee_detail td ON t.abonne_soc_id = td.num_abonne_id AND t.jour_id = td.jour_id
                                                        LEFT JOIN (SELECT soc_code_ext, MIN(prd_code_ext) AS prd_code_ext FROM produit GROUP BY soc_code_ext) p ON t.soc_code_ext = p.soc_code_ext
                                                    WHERE
                                                        t.tournee_jour_id IS NOT NULL
                                                        AND td.id IS NULL
                                                    GROUP BY
                                                        t.tournee_jour_code, t.numabo_ext, t.abonne_soc_id,
                                                        t.soc_code_ext, p.prd_code_ext, t.insee, t.point_livraison_id 
                                                    ";
                            $em->getConnection()->executeQuery($insertNouvTournee);
                            $em->clear();

                            // Mise a jour la table "tournee_detail"
                            // 
                            $this->oLog->info("Debut Mise a jour la table tournee_detail");
                            $updateTourneeDetail  = " UPDATE
                                                            sdvp_traitement_tmp t
                                                            LEFT JOIN tournee_detail td ON t.abonne_soc_id = td.num_abonne_id AND t.jour_id = td.jour_id
                                                            LEFT JOIN (SELECT soc_code_ext, MIN(prd_code_ext) AS prd_code_ext FROM produit GROUP BY soc_code_ext) p ON t.soc_code_ext = p.soc_code_ext
                                                        SET
                                                            td.longitude = t.geox
                                                            , td.latitude = t.geoy
                                                            , td.insee = t.insee
                                                            , td.modele_tournee_jour_code = t.tournee_jour_code
                                                            , td.ordre = t.ordre_dcs
                                                            , td.date_modification = now()
                                                            , td.source_modification = 'SDVPTraitement - update chgmnt tournee CAS'
                                                        WHERE
                                                            t.tournee_jour_id IS NOT NULL
                                                            AND td.id IS NOT NULL AND (td.modele_tournee_jour_code IS NULL OR t.tournee_jour_code <> td.modele_tournee_jour_code OR td.ordre <> t.ordre_dcs OR td.ordre IS NULL OR td.ordre = 0)
                                                        ";
                            $em->getConnection()->executeQuery($updateTourneeDetail);
                            $em->clear();

                            // Mise a jour des champs tournee, depot de la table "client_a_servir_logist"
                            $this->oLog->info("Debut Mise a jour du champs tournee de la table client_a_servir_logist");
                            $updateTournee  = " UPDATE
                                                    client_a_servir_logist csl
                                                    INNER JOIN (    SELECT
                                                                        date_distrib, abonne_soc_id, MAX(depot_id) AS depot_id, MAX(tournee_jour_id) AS tournee_jour_id, MAX(ordre_dcs) AS ordre_dcs
                                                                    FROM
                                                                        sdvp_traitement_tmp
                                                                    GROUP BY 
                                                                        date_distrib, abonne_soc_id ) t ON csl.date_distrib = t.date_distrib AND csl.abonne_soc_id = t.abonne_soc_id
                                                SET 
                                                    csl.depot_id = t.depot_id
                                                    , csl.tournee_jour_id = t.tournee_jour_id 
                                                    , csl.point_livraison_ordre = t.ordre_dcs
                                                WHERE
                                                    csl.date_distrib = '".$sDateDistrib."'
                                                    AND t.abonne_soc_id IS NOT NULL
                                                ";
                            $em->getConnection()->executeQuery($updateTournee);
                            $em->clear();

                            // En Local - Sauvegarde du fichier
                            rename($this->sRepTmp.'/'.$sFicV, $this->sRepBkpLocal.'/'.$this->oString->renommeFicDeSvgrde($sFicV, $this->sDateCourantYmd, $this->sHeureCourantYmd));

                        }
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
        }
        $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
        $this->endTraitement();     
        $this->oLog->info(date("d/m/Y H:i:s : ")."Fin Divers traitements concernant les abonnes et produits SDVP - Commande : ".$this->sNomCommande.' '.$sJourATraiterMin.' '.$sJourATraiterMax);
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
