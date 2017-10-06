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
 * Traitements specifiques concernant les produits de Neopress
 * 
 * - Tournees
 * 
 * Par defaut, on ne fait le calcul que le jour J+1.
 * Parametre : 
 *          Jour minimum a calculer. C'est optionnel
 *          Jour maximum a calculer. C'est optionnel
 *          Flux a traiter. C_A_S (clients a servir) ou REPER (reperage) [--flux=..]
 *          Flux "jour" ou "nuit" [--jn=..]
 *          Environnement [--env=..]
 * Expl : J+1 J+5
 * Si les parametres sont renseignes, le traitement concerne les jours de distribution de "jour minimum" a "jour maximum"
 * Expl : J-1. => calculs a faire concernent J-1, J, J+1, J+2 & J+3
 * 
 * Exemple de commande : 
 *                      php app/console neopress_traitement J+0 J+3 --flux=C_A_S --jn=nuit --id_sh=cron_test --id_ai=1  --env=prod
 *                      php app/console neopress_traitement J+0 J+3 --flux=C_A_S --jn=jour --id_sh=cron_test --id_ai=1  --env=prod
 *                      php app/console neopress_traitement J+0 J+3 --flux=C_A_S --jn=tout --id_sh=cron_test --id_ai=1  --env=prod
 *                      php app/console neopress_traitement J+0 J+3 --flux=REPER --jn=nuit --id_sh=cron_test --id_ai=1  --env=prod
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
class NeopressTraitementCommand extends GlobalCommand
{
    protected function configure()
    {
    	$this->sNomCommande	= 'neopress_traitement';
        $sJourATraiterMinParDefaut = "J+1";
        $sJourATraiterMaxParDefaut = "J+2";
        $sFluxDefaut = "CAS";
        $sJourOuNuitDefaut = "tout";
        $this->jourATraiterMaxRef   = 3;
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console neopress_traitement Expl : php app/console neopress_traitement J+1 J+3 --env=prod
        $this
            ->setDescription('Divers traitements concernant les abonnes et produits Neopress.')
            ->addArgument('jour_a_traiter_min', InputArgument::OPTIONAL, 'Jour a traiter Min. Format : J<+Numerique> (Expl : J+1 ou J+2 ou J ...)', $sJourATraiterMinParDefaut)
            ->addArgument('jour_a_traiter_max', InputArgument::OPTIONAL, 'Jour a traiter Max. Format : J<+Numerique> (Expl : J+1 ou J+2 ou J ...)', $sJourATraiterMaxParDefaut)
            ->addOption('flux',null, InputOption::VALUE_REQUIRED, 'Flux ? Clients a servir (C_A_S) ou reperage (REPER)', $sFluxDefaut)
            ->addOption('jn',null, InputOption::VALUE_REQUIRED, 'jour et/ou nuit ?', $sJourOuNuitDefaut)
            ->addOption('id_sh',null, InputOption::VALUE_REQUIRED, 'Libelle du CRON')
            ->addOption('id_ai',null, InputOption::VALUE_REQUIRED, 'Id du CRON')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
        $sFlux  = "C_A_S";
        $sFluxClientsAServir    = "C_A_S";
        $sFluxReperage    = "REPER";
        $sJourOuNuit    = "tout";
        
        if ($input->getOption('flux')) {
            $sFlux   = $input->getOption('flux');
        }
        if ($input->getOption('jn')) {
            $sJourOuNuit   = $input->getOption('jn');
        }
         if($input->getOption('id_sh')){
            $idSh = $input->getOption('id_sh');
        }
        if($input->getOption('id_ai')){
            $idAi = $input->getOption('id_ai');
        }
        if($input->getOption('id_ai') && $input->getOption('id_sh')){
            $this->associateToCron($idAi,$idSh);
        }
    	$this->oLog->info(date("d/m/Y H:i:s : ")."Debut Divers traitements concernant les abonnes et produits Neopress - Commande : ".$this->sNomCommande);    
        /*
        $aFTP   = array(
                        "SERVEUR"   => "10.151.93.3"
                        , "LOGIN"   => "sdvp"
                        , "MDP"     => "sdvp"
                        , "SS_REPERTOIRE" => "./FTPtest/Neopress"
                        );
        */
        
        $aFTP   = array(
                        "SERVEUR"   => "FTP.neopress.fr"
                        , "LOGIN"   => "Proximy"
                        , "MDP"     => "5ry33XJh"
                        , "SS_REPERTOIRE" => "."
                        );
        
        $aFTP   = array(
                        "SERVEUR"   => "10.151.93.3"
                        , "LOGIN"   => "sdvp"
                        , "MDP"     => "sdvp"
                        , "SS_REPERTOIRE" => "./FTPtest/Neopress"
                        );
        
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
        
        
        $aDatesYmdARecuperer    = array();
        foreach ($aoJourATraiter as $iJourATraiter => $oDateATraiterV)
        {
            $aDatesYmdARecuperer[]  = $oDateATraiterV->format('Ymd');
        }
        
        
        
        $sSepCSVNeopress        = ";";
        $sFicRegexARecuperer    = "/^(\.\/)?DISPRESS_(NUIT|JOUR)_(".implode("|", $aDatesYmdARecuperer).")\.csv$/i";
        if($sJourOuNuit=='nuit')
        {
            $sFicRegexARecuperer    = "/^(\.\/)?DISPRESS_(NUIT)_(".implode("|", $aDatesYmdARecuperer).")\.csv$/i";
        }
        else if($sJourOuNuit=='jour')
        {
            $sFicRegexARecuperer    = "/^(\.\/)?DISPRESS_(JOUR)_(".implode("|", $aDatesYmdARecuperer).")\.csv$/i";
        }
        
        $regexFluxNeopressNuit  ='/.+_NUIT_.+$/i';
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
        $this->sRepTmp	= $this->cree_repertoire($this->sRepFichiersPrinc.'/'.$this->getContainer()->getParameter('SOUSREP_FICHIERS_TMP').'/'.'NeopressTraitement');
        

        // Repertoire Backup Local
    	$this->sRepBkpLocal	= $this->cree_repertoire($this->sRepFichiersPrinc.'/'.$this->getContainer()->getParameter('SOUSREP_FICHIERS_BKP').'/'.'NeopressTraitement');
        
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
				$aFichierATraiter[$date][]	= $sFicV;
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
                    $this->oLog->info("- Debut Traitement du fichier ".$sFicV);
                    $flux_id	= 2; // Flux jour
                    if (preg_match($regexFluxNeopressNuit, $sFicV))
                    {
                            $flux_id	= 1; // Flux nuit
                    }

                    // Vider la table temporaire
                    $truncate = " TRUNCATE TABLE neopress_traitement_tmp ";
                    $em->getConnection()->executeQuery($truncate);
                    $em->clear();

                    // Integration du fichier en cours dans table de traitement
                    if ($oFichierSource = fopen($this->sRepTmp.'/'.$sFicV,"r"))
                    {
                        $iNumLigne	= 0;
                        while(!feof($oFichierSource)) 
                        {
                            $sLigneSource = fgets($oFichierSource);
                            $iNumLigne++;
                            if (trim($sLigneSource) != '' && $iNumLigne > 1 ) // 1ère ligne = titre de chaque champs
                            {								
                                $aLigneSource	= explode($sSepCSVNeopress, trim($sLigneSource));
                                $aInsert    = array();
                                if (count($aLigneSource)>=22) // Normalement, une ligne contient 22 colonnes
                                {
//					0	=> 'SS_TYPE'	// libelle Produit reference dans Dispress
//					1	=> 'CP'
//					2	=> 'VILLE',
//					3	=> 'N_ABONNE'	// Concatenation Nom produit et Id Abonne
//					4	=> 'ID_facturation'	// Par defaut IDF
//					5	=> 'NOM'		// Nom prenom
//					6	=> 'Compl_adresse'	// complement Adresse
//					7	=> 'ADRESSE'	// adresse
//					8	=> 'COMPLNOM'	// complement Nom - Raison sociale
//					9	=> 'DEPOT'
//					10	=> 'Acces'		// information acces éditeur
//					11	=> 'QTE'
//					12	=> 'TOURNEE'	// Code Tournee Neopress
//					13	=> 'X'			// coordonnees au format WGS 84
//					14	=> 'Y'			// coordonnees au format WGS 84
//					15	=> 'ID_adresse'	// Hexacle de l'adresse  issu de la normalisation
//					16	=> 'Type_client'
//									PAD	par defaut (Portage a domicile)
//									PAD1	codification specifique LE Monde PARIS pour la facturation
//									PAD2	codification specifique Le Monde Petite couronne
//									PAD3	codification specifique Le Monde Grande couronne
//									PN1	codification specifique PN Le Monde Paris intra Muros
//									PN2	codification specifique PN Le Monde Petite couronne (cf regle de facturation)
//									PN3	codification specifique PN Le Monde Grande couronne
//									PN	Portage en nombre autre titre que le Monde
//									OP1	Operation speciale necessitant un emargement par le client
//									OP2	Operation speciale 
//									REP	Abonne en reperage sans quantite a desservir
//					17	=> 'Statut'
//									S 	Sortant
//									E	Entrant sur adresse connue
//									B	Entrant sur adresse nouvelle (ou non desservie depuis 90 jours)
//									S 	Sortant
//									R	Reperage
//									A	Reperage sur adresse nouvelle  (ou non desservie depuis 90 jours)
//					18	=> 'Sequence'	// Sequence dans la tournee
//					19	=> 'PointRemiseRH'	// Champ qui contient le numero d'abonnement pour les titres geres par le prestataire ARVATO
//					20	=> 'nuAbonne'	// Numero d'abonne fournit par le client
//					21	=> 'dateDistribution'	// Date de distribution format JJ/MM/AAAA

                                    $sTypeFlux	= 'autre';
                                    $bEntrantOuSortant    = '';
                                    $soc_code_ext   = '';
                                    $numabo_ext = '';
                                    if ($aLigneSource[17]=='S') // Sortant
                                    {
                                            $sTypeFlux	= 'autre';
                                            $bEntrantOuSortant    = 'S'; // Arret/Sortie
                                    }
                                    else if (in_array($aLigneSource[17], array('A', 'R'))) // Reperage
                                    {
                                            $sTypeFlux	= $sFluxReperage;
                                    }
                                    else // Statut client = 'X' ou 'E' ou 'B'
                                    {
                                            $sTypeFlux	= $sFluxClientsAServir;
                                            if(in_array($aLigneSource[17], array('E', 'B')))
                                            {
                                                $bEntrantOuSortant    = 'E'; // Nouveau / Reprise / Entrant
                                            }
                                    }
                                    $bProduitConnu	= true;

                                    // on supprime les accents et on met en majuscule
                                    $prdExt	= strtoupper($this->suppr_accent(trim($aLigneSource[0]), 'iso-8859-1'));

                                    if(!isset($aCodesSocTit[$flux_id][$prdExt]))
                                    {
                                        $bProduitConnu	= false;
                                        if($prdExt!='SS_TYPE')
                                        {
                                            $this->suiviCommand->setMsg("Produit inconnu ... aCodesSocTit[$flux_id][$prdExt] : Ligne $iNumLigne - ".$sLigneSource);
                                            $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
                                            $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                                            $this->suiviCommand->setEtat("KO");
                                            $this->oLog->erreur("Produit inconnu ... aCodesSocTit[$flux_id][$prdExt] : Ligne $iNumLigne - ".$sLigneSource, E_USER_ERROR);	
                                            $this->registerError();
                                            if($input->getOption('id_ai') && $input->getOption('id_sh')){
                                                $this->registerErrorCron($idAi);
                                            }
                                        }
                                    }

                                    if($bProduitConnu==true)
                                    {
                                        $date_dmY	= $aLigneSource[21];
                                        $date_Y_m_d	= substr($date_dmY,6,4)."/".substr($date_dmY,3,2)."/".substr($date_dmY,0,2);
                                        $soc_code_ext   = $aCodesSocTit[$flux_id][$prdExt]['soc_code_ext'];
                                        $numabo_ext     = str_replace($aLigneSource[0], '', $aLigneSource[3]);	// Num abo NEOPRESS sans le libelle du produit
                                    }
                                    if($bProduitConnu==true && in_array($sTypeFlux, array($sFluxClientsAServir, $sFluxReperage)))
                                    {
                                        $aInsert['societe_id']  = $aCodesSocTit[$flux_id][$prdExt]['societe_id'];
                                        $aInsert['produit_id']  = $aCodesSocTit[$flux_id][$prdExt]['produit_id'];
                                        $aInsert['flux_id']     = $aCodesSocTit[$flux_id][$prdExt]['flux_id'];
                                        $aInsert['soc_code_ext']  = $soc_code_ext;
                                        $aInsert['prd_code_ext']  = $aCodesSocTit[$flux_id][$prdExt]['prd_code_ext'];
                                        $aInsert['spr_code_ext']  = $aCodesSocTit[$flux_id][$prdExt]['spr_code_ext'];
                                        $aInsert['date_distrib']  = $date_Y_m_d;
                                        $aInsert['numabo_ext']  = $numabo_ext;	// Num abo NEOPRESS sans le libelle du produit
                                        $aInsert['vol1']        = addslashes($aLigneSource[5]);
                                        $aInsert['vol2']        = addslashes($aLigneSource[8]);
                                        $aInsert['vol3']        = addslashes($aLigneSource[6]);
                                        $aInsert['vol4']        = addslashes($aLigneSource[7]);
                                        $aInsert['vol5']        = '';
                                        $aInsert['cp']        = $aLigneSource[1];
                                        $aInsert['ville']        = addslashes($aLigneSource[2]);
                                        $aInsert['neo_produit'] = addslashes($aLigneSource[0]);
                                        $aInsert['neo_tournee'] = $aLigneSource[12];
                                        $aInsert['tournee_ordre'] = $aLigneSource[18];
                                        $aInsert['geox'] = $aLigneSource[13];
                                        $aInsert['geoy'] = $aLigneSource[14];
                                        $aInsert['type_flux'] = $sTypeFlux;

                                        $insert = " INSERT INTO neopress_traitement_tmp 
                                                        (".implode(', ', array_keys($aInsert)).")
                                                    VALUES
                                                        ('".implode("', '", $aInsert)."')
                                                ";
                                        $em->getConnection()->executeQuery($insert);
                                        $em->clear();
                                    }
                                    
                                    if($bProduitConnu==true)
                                    {
                                        
                                        // Prise en charge des nouveaux/Reprises/Arrets
                                        if($bEntrantOuSortant=='S')
                                        {
                                            $sUpdateDates0_Arret    = " UPDATE abonne_soc SET date_stop = '".$date_Y_m_d."' WHERE numabo_ext = '".$numabo_ext."' AND soc_code_ext = '".$soc_code_ext."' ";
                                            $em->getConnection()->executeQuery($sUpdateDates0_Arret);
                                            $em->clear();
                                        }
                                        else if($bEntrantOuSortant=='E')
                                        {
                                            $sUpdateDates0_Arret    = " UPDATE abonne_soc SET date_service_1 = '".$date_Y_m_d."' WHERE numabo_ext = '".$numabo_ext."' AND soc_code_ext = '".$soc_code_ext."' ";
                                            $em->getConnection()->executeQuery($sUpdateDates0_Arret);
                                            $em->clear();
                                        }
                                    }
                                }
                                else
                                {
                                    $nbCol = count($aLigneSource);
                                    $this->suiviCommand->setMsg($nbCol." colonnes trouvees a la ligne ".$iNumLigne);
                                    $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
                                    $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                                    $this->suiviCommand->setEtat("KO");
                                    $this->oLog->erreur($nbCol." colonnes trouvees a la ligne ".$iNumLigne, E_USER_ERROR);
                                    $this->registerError();
                                    if($input->getOption('id_ai') && $input->getOption('id_sh')){
                                        $this->registerErrorCron($idAi);
                                    }
                                }
                            }
                        }
                        fclose($oFichierSource); 

                        $this->oLog->info("Debut Mise a jour des champs abonne & tournee de la table temporaire");
                        
                        // Suppression des lignes dont le flux est different de ce que l on souhaite traiter
                        $deleteAutresFlux = " DELETE FROM neopress_traitement_tmp WHERE (type_flux IS NULL OR type_flux <> '".$sFlux."') ";
                        $em->getConnection()->executeQuery($deleteAutresFlux);
                        $em->clear();

                        // Mise a jour de "abonne_soc_id" de la table temporaire
                        $update = " UPDATE 
                                        neopress_traitement_tmp t 
                                        LEFT JOIN abonne_soc a ON t.numabo_ext = a.numabo_ext AND t.soc_code_ext = a.soc_code_ext 
                                    SET
                                        t.abonne_soc_id = a.id
                                        ";
                        $em->getConnection()->executeQuery($update);
                        $em->clear();                     

                        // Suppression des lignes dont l abonne n est pas connu 
                        $this->oLog->info("Suppression des lignes dont l abonne n est pas connu");
                        $deleteAboInconnu  = " DELETE FROM neopress_traitement_tmp WHERE abonne_soc_id IS NULL ";
                        $em->getConnection()->executeQuery($deleteAboInconnu);
                        $em->clear();

                        // Mise a jour des tournees 
                        $updateTournee  = " UPDATE
                                                neopress_traitement_tmp t
                                                LEFT JOIN modele_tournee mt ON t.neo_tournee = mt.libelle AND mt.actif = 1
                                                LEFT JOIN modele_tournee_jour mtj ON mt.id = mtj.tournee_id 
                                                                                    AND mtj.jour_id=CAST(DATE_FORMAT(t.date_distrib, '%w') AS SIGNED)+1
                                            SET
                                                t.tournee_id = mt.id
                                                , t.tournee_code = mt.code
                                                , t.tournee_jour_id = mtj.id
                                                , t.tournee_jour_code = mtj.code
                                                , t.jour_id = mtj.jour_id
                                            WHERE
                                                t.date_distrib BETWEEN mtj.date_debut AND mtj.date_fin
                                            ";
                        $em->getConnection()->executeQuery($updateTournee);
                        $em->clear(); 
                        
                        // Traitement des clients a servir
                        if($sFlux == $sFluxClientsAServir)
                        {
                            // Prise en compte des infos de Neopress concernant les adresses rejetees (RNVP & Geocodage)
                            $this->oLog->info("Debut Prise en compte des infos de Neopress concernant les adresses rejetees");
                            $updateAdr  = " UPDATE
                                                client_a_servir_logist csl
                                                LEFT JOIN neopress_traitement_tmp t ON csl.abonne_soc_id = t.abonne_soc_id AND csl.date_distrib = t.date_distrib AND t.type_flux = '".$sFluxClientsAServir."'
                                                LEFT JOIN adresse a ON csl.adresse_id = a.id
                                                LEFT JOIN adresse_rnvp ar ON a.rnvp_id = ar.id
                                            SET
                                                ar.geox = t.geox
                                                , ar.geoy = t.geoy
                                                , csl.rnvp_id = a.rnvp_id
                                                , csl.point_livraison_id = a.rnvp_id
                                                , a.adresse_rnvp_etat_id = 2
                                                , a.point_livraison_id = a.rnvp_id
                                                , ar.geo_etat = 2
                                            WHERE
                                                1 = 1 
                                            AND t.id IS NOT NULL
                                                AND (a.adresse_rnvp_etat_id > 2 OR ar.geo_etat = 0 OR ar.geo_etat IS NULL)
                                            ";
                            $em->getConnection()->executeQuery($updateAdr);
                            $em->clear();
                            
                            
                            // Suppression des lignes de tournee_detail avec incoherence flux
                            $this->oLog->info("Debut Suppression des lignes de tournee_detail avec incoherence flux");
                            $aTdIdASuppr    = array();                            
                            $sSlctIdASuppr  = " SELECT
                                                    DISTINCT td.id
                                                FROM
                                                    neopress_traitement_tmp t
                                                    INNER JOIN tournee_detail td ON t.abonne_soc_id = td.num_abonne_id AND CAST(DATE_FORMAT(t.date_distrib, '%w') AS SIGNED)+1 = td.jour_id
                                                WHERE
                                                    t.flux_id = ".$flux_id."
                                                    AND t.flux_id <> td.flux_id
                                             ";
                            $rSlctIdASuppr = $em->getConnection()->fetchAll($sSlctIdASuppr);
                            //print_r($sSlctIdASuppr);
                            foreach ($rSlctIdASuppr as $aArr) {
                                $aTdIdASuppr[] = $aArr['id'];
                            }
                            if (!empty($aTdIdASuppr)) {
                                $aTdIdASupprTmp = array();
                                $iNbASuppr = 100;
                                $iNb = 0;
                                foreach ($aTdIdASuppr as $iIdASuppr) {
                                    $aTdIdASupprTmp[] = $iIdASuppr;
                                    $iNb++;
                                    if (!empty($aTdIdASupprTmp) && ($iNb % $iNbASuppr == 0)) {
                                        $sDelete = " DELETE FROM tournee_detail WHERE id IN (" . implode(', ', $aTdIdASupprTmp) . ") ";
                                        //echo "$sDelete\n";
                                        $em->getConnection()->executeQuery($sDelete);
                                        $em->clear();
                                        $aTdIdASupprTmp = array();
                                        $iNb = 0;
                                    }
                                }
                                if (!empty($aTdIdASupprTmp)) {
                                    $sDelete = " DELETE FROM tournee_detail WHERE id IN (" . implode(', ', $aTdIdASupprTmp) . ") ";
                                    //echo "$sDelete\n";
                                    $em->getConnection()->executeQuery($sDelete);
                                    $em->clear();
                                }
                            }
                            
                            
                            ////// Rajouter ici .... Mise a jour ordre de tournee_detail
                            // Afin de ne prendre en compte que l'ordre de Neopress
                            $this->oLog->info("Mise a jour de l ordre afin de prendre en compte l ordre de Neopress");
                            $updateOrdre  = " UPDATE
                                                        neopress_traitement_tmp t
                                                        INNER JOIN client_a_servir_logist csl ON csl.abonne_soc_id = t.abonne_soc_id AND csl.date_distrib = t.date_distrib AND t.type_flux = '".$sFluxClientsAServir."'
                                                        INNER JOIN tournee_detail td ON t.abonne_soc_id = td.num_abonne_id AND t.jour_id = td.jour_id
                                                    SET
                                                        td.ordre = t.tournee_ordre
                                                        , td.reperage = 0
                                                        , td.date_modification = now()
                                                        , td.source_modification = 'NeopressTraitement - update ordre CAS'
                                                    WHERE
                                                        1 = 1
                                                    ";
                            $em->getConnection()->executeQuery($updateOrdre);
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
                                                        ordre_stop, soc, titre, insee, flux_id, jour_id, date_modification, source_modification, reperage)
                                                    SELECT
                                                        IFNULL(t.tournee_ordre, 0) AS ordre, ar.geox AS longitude, ar.geoy AS latitude, NULL AS duree_conduite, NULL AS heure_debut, 
                                                        NULL AS duree, 'A l\'heure' AS etat, NULL AS distance_trajet, NULL AS trajet_cumule, 
                                                        NULL AS debut_plage_horaire, NULL AS fin_plage_horaire, '00:00:30' AS duree_viste_fixe,
                                                        '' AS exclure_ressource, '' AS assigner_ressource, 1 AS a_traiter, 
                                                        t.tournee_jour_code AS modele_tournee_jour_code, t.numabo_ext AS num_abonne_soc, t.abonne_soc_id AS num_abonne_id, 
                                                        NULL AS nb_stop, NULL AS temps_conduite, NULL AS temps_tournee, NULL AS temps_visite, 
                                                        NULL AS ordre_stop, t.soc_code_ext AS soc, t.prd_code_ext AS titre, ar.insee AS insee, csl.flux_id AS flux_id, t.jour_id, now() AS date_modification, 'NeopressTraitement - insert nouv. tournee CAS' as source_modification, 0 AS reperage 
                                                    FROM
                                                        client_a_servir_logist csl
                                                        LEFT JOIN neopress_traitement_tmp t ON csl.abonne_soc_id = t.abonne_soc_id AND csl.date_distrib = t.date_distrib AND t.type_flux = '".$sFluxClientsAServir."'
                                                        LEFT JOIN tournee_detail td ON csl.abonne_soc_id = td.num_abonne_id AND t.jour_id = td.jour_id 
                                                        LEFT JOIN adresse_rnvp ar ON csl.rnvp_id = ar.id
                                                    WHERE
                                                        1 = 1 
                                                        AND t.id IS NOT NULL 
                                                        AND t.tournee_jour_code IS NOT NULL 
                                                        AND td.id IS NULL 
                                                    GROUP BY t.abonne_soc_id, t.jour_id                                                        
                                                    ";
                            $em->getConnection()->executeQuery($insertNouvTournee);
                            $em->clear();

                            // Mise a jour la table "tournee_detail"
                            // 
                            $this->oLog->info("Debut Mise a jour la table tournee_detail");
                            $updateTourneeDetail  = " UPDATE
                                                        client_a_servir_logist csl
                                                        LEFT JOIN neopress_traitement_tmp t ON csl.abonne_soc_id = t.abonne_soc_id AND csl.date_distrib = t.date_distrib AND t.type_flux = '".$sFluxClientsAServir."'
                                                        LEFT JOIN tournee_detail td ON csl.abonne_soc_id = td.num_abonne_id AND t.jour_id = td.jour_id
                                                        LEFT JOIN adresse_rnvp ar ON csl.point_livraison_id = ar.id
                                                    SET
                                                        td.longitude = ar.geox
                                                        , td.latitude = ar.geoy
                                                        , td.insee = ar.insee
                                                        , td.modele_tournee_jour_code = t.tournee_jour_code
                                                        , td.ordre = t.tournee_ordre
                                                        , td.reperage = 0
                                                        , td.date_modification = now()
                                                        , td.source_modification = 'NeopressTraitement - update chgmnt tournee CAS'
                                                    WHERE
                                                        1 = 1 
                                                        AND t.id IS NOT NULL 
                                                        AND t.tournee_jour_code IS NOT NULL 
                                                        AND td.id IS NOT NULL AND (td.modele_tournee_jour_code IS NULL OR t.tournee_jour_code <> td.modele_tournee_jour_code /*OR td.ordre <> t.tournee_ordre*/)
                                                    ";
                            $em->getConnection()->executeQuery($updateTourneeDetail);
                            $em->clear();


                            // Mise a jour du champs tournee de la table "client_a_servir_logist"
                            $this->oLog->info("Debut Mise a jour du champs tournee de la table client_a_servir_logist");
                            $updateTournee  = " UPDATE 
                                                    client_a_servir_logist csl
                                                    LEFT JOIN neopress_traitement_tmp t ON csl.abonne_soc_id = t.abonne_soc_id AND csl.date_distrib = t.date_distrib AND t.type_flux = '".$sFluxClientsAServir."'
                                                SET 
                                                    csl.tournee_jour_id = t.tournee_jour_id 
                                                    , csl.point_livraison_ordre = t.tournee_ordre 
                                                WHERE
                                                    1 = 1
                                                    AND t.id IS NOT NULL 
                                                    /*AND t.tournee_jour_code IS NOT NULL*/
                                                ";
                            $em->getConnection()->executeQuery($updateTournee);
                            $em->clear();


                            // A partir des codes tournees de Neopress, on met a jour le depot <=> c est la tournee de neopress qui prime par rapport a l adresse concernant le classement dans un depot
                            $this->oLog->info("Debut Mise a jour du champs depot a partir des codes tournees Neopress");
                            $updateDepot  = "   UPDATE
                                                    neopress_traitement_tmp t
                                                    LEFT JOIN client_a_servir_logist csl ON csl.abonne_soc_id = t.abonne_soc_id AND csl.date_distrib = t.date_distrib
                                                    LEFT JOIN client_a_servir_src css ON csl.client_a_servir_src_id = css.id 
                                                    LEFT JOIN modele_tournee mt ON t.tournee_id = mt.id AND mt.actif = 1
                                                    LEFT JOIN groupe_tournee gt ON mt.groupe_id = gt.id
                                                SET
                                                    csl.depot_id = gt.depot_id
                                                    , css.depot_id = gt.depot_id
                                                WHERE
                                                    csl.depot_id <> gt.depot_id
                                                    AND gt.depot_id IS NOT NULL
                                                    AND t.type_flux = '".$sFluxClientsAServir."'
                                                ";
                            $em->getConnection()->executeQuery($updateDepot);
                            $em->clear();
                        }
                        // Traitement des reperages
                        else if ($sFlux == $sFluxReperage)
                        {
                            // Prise en compte des infos de Neopress concernant les adresses rejetees (RNVP & Geocodage)
                            $this->oLog->info("Debut Prise en compte des infos de Neopress concernant les adresses rejetees");
                            $updateAdr  = " UPDATE
                                                reperage r
                                                LEFT JOIN neopress_traitement_tmp t ON r.abonne_soc_id = t.abonne_soc_id AND r.date_demar = t.date_distrib AND t.type_flux = '".$sFluxReperage."'
                                                LEFT JOIN adresse a ON r.adresse_id = a.id
                                                LEFT JOIN adresse_rnvp ar ON a.rnvp_id = ar.id
                                            SET
                                                ar.geox = t.geox
                                                , ar.geoy = t.geoy
                                                , r.point_livraison_id = a.rnvp_id
                                                , a.adresse_rnvp_etat_id = 2
                                                , a.point_livraison_id = a.rnvp_id
                                                , ar.geo_etat = 2
                                            WHERE
                                                1 = 1 
                                            AND t.id IS NOT NULL
                                                AND (a.adresse_rnvp_etat_id > 2 OR ar.geo_etat = 0 OR ar.geo_etat IS NULL)
                                                            ";
                            $em->getConnection()->executeQuery($updateAdr);
                            $em->clear();
                            
                            // Mise a jour la table "tournee_detail"
                            // La requete suivante remplace les deux requetes mises en commentaire juste apres
                            //
                            /*
                            $this->oLog->info("Debut Mise a jour la table tournee_detail");
                            $updateTourneeDetail  = " UPDATE
                                                        neopress_traitement_tmp t
                                                        LEFT JOIN reperage r ON r.abonne_soc_id = t.abonne_soc_id AND r.date_demar = t.date_distrib 
                                                        LEFT JOIN tournee_detail td ON r.abonne_soc_id = td.num_abonne_id AND t.jour_id = td.jour_id
                                                        LEFT JOIN adresse_rnvp ar ON r.point_livraison_id = ar.id 
                                                                                    AND td.longitude = ar.geox AND td.latitude = ar.geoy
                                                    SET
                                                        td.longitude = ar.geox
                                                        , td.latitude = ar.geoy
                                                        , td.insee = ar.insee
                                                        , td.modele_tournee_jour_code = t.tournee_jour_code
                                                        , td.ordre = t.tournee_ordre
                                                        , td.reperage = 1
                                                        , td.date_modification = now()
                                                        , td.source_modification = 'NeopressTraitement - update REPER'
                                                    WHERE
                                                        t.type_flux = '".$sFluxReperage."'
                                                        AND r.id IS NOT NULL 
                                                        AND t.tournee_jour_code IS NOT NULL 
                                                        AND td.id IS NOT NULL 
                                                        AND ar.geox IS NOT NULL 
                                                    ";
                            $em->getConnection()->executeQuery($updateTourneeDetail);
                            $em->clear();
                            */
                            
                            
                            // Ajout de nouvelles donnees de couple tournee/jour dans la table "tournee_detail"
                            /*
                            $this->oLog->info("Ajout de nouvelles donnees de couple tournee/jour dans la table tournee_detail");
                            $insertNouvTournee  = " INSERT INTO tournee_detail 
                                                        (ordre, longitude, latitude, duree_conduite, heure_debut, 
                                                        duree, etat, distance_trajet, trajet_cumule, 
                                                        debut_plage_horaire, fin_plage_horaire, duree_viste_fixe, 
                                                        exclure_ressource, assigner_ressource, a_traiter, 
                                                        modele_tournee_jour_code, num_abonne_soc, num_abonne_id, 
                                                        nb_stop, temps_conduite, temps_tournee, temps_visite, 
                                                        ordre_stop, soc, titre, insee, flux_id, jour_id,source_modification, reperage)
                                                    SELECT
                                                        IFNULL(t.tournee_ordre, 0) AS ordre, ar.geox AS longitude, ar.geoy AS latitude, NULL AS duree_conduite, NULL AS heure_debut, 
                                                        NULL AS duree, 'A l\'heure' AS etat, NULL AS distance_trajet, NULL AS trajet_cumule, 
                                                        NULL AS debut_plage_horaire, NULL AS fin_plage_horaire, '00:00:30' AS duree_viste_fixe,
                                                        '' AS exclure_ressource, '' AS assigner_ressource, 1 AS a_traiter, 
                                                        t.tournee_jour_code AS modele_tournee_jour_code, t.numabo_ext AS num_abonne_soc, t.abonne_soc_id AS num_abonne_id, 
                                                        NULL AS nb_stop, NULL AS temps_conduite, NULL AS temps_tournee, NULL AS temps_visite, 
                                                        NULL AS ordre_stop, t.soc_code_ext AS soc, t.prd_code_ext AS titre, ar.insee AS insee, t.flux_id AS flux_id, t.jour_id, 'reperage Neopress Traitement' as source_modification, 1 AS reperage
                                                    FROM
                                                        neopress_traitement_tmp t
                                                        LEFT JOIN reperage r ON t.abonne_soc_id = r.abonne_soc_id AND r.date_demar = t.date_distrib
                                                        LEFT JOIN tournee_detail td ON r.abonne_soc_id = td.num_abonne_id AND t.jour_id = td.jour_id
                                                        LEFT JOIN adresse_rnvp ar ON r.rnvp_id = ar.id
                                                    WHERE
                                                        t.type_flux = '".$sFluxReperage."'
                                                        AND t.tournee_jour_code IS NOT NULL 
                                                        AND r.id IS NOT NULL 
                                                        AND td.id IS NULL 
                                                    ";
                            $em->getConnection()->executeQuery($insertNouvTournee);
                            $em->clear();
                            */

                            // Mise a jour la table "tournee_detail"
                            // 
                            /*
                            $this->oLog->info("Debut Mise a jour la table tournee_detail");
                            $updateTourneeDetail  = " UPDATE
                                                        neopress_traitement_tmp t
                                                        LEFT JOIN reperage r ON r.abonne_soc_id = t.abonne_soc_id AND r.date_demar = t.date_distrib 
                                                        LEFT JOIN tournee_detail td ON r.abonne_soc_id = td.num_abonne_id AND t.jour_id = td.jour_id
                                                        LEFT JOIN adresse_rnvp ar ON r.rnvp_id = ar.id
                                                    SET
                                                        td.longitude = ar.geox
                                                        , td.latitude = ar.geoy
                                                        , td.insee = ar.insee
                                                        , td.modele_tournee_jour_code = t.tournee_jour_code
                                                        , td.ordre = t.tournee_ordre
                                                        , td.reperage = 1
                                                    WHERE
                                                        t.type_flux = '".$sFluxReperage."'
                                                        AND t.id IS NOT NULL 
                                                        AND t.tournee_jour_code IS NOT NULL 
                                                        AND td.id IS NOT NULL AND (td.modele_tournee_jour_code IS NULL OR t.tournee_jour_code <> td.modele_tournee_jour_code)
                                                    ";
                            $em->getConnection()->executeQuery($updateTourneeDetail);
                            $em->clear();
                             *
                             */


                            // Mise a jour du champs tournee de la table "reperage"
                            $this->oLog->info("Debut Mise a jour du champs tournee de la table reperage");
                            $updateTournee  = " UPDATE 
                                                    neopress_traitement_tmp t 
                                                    LEFT JOIN reperage r ON r.abonne_soc_id = t.abonne_soc_id AND r.date_demar = t.date_distrib 
                                                    LEFT JOIN adresse a ON r.adresse_id = a.id
                                                SET 
                                                    r.tournee_id = t.tournee_id 
                                                    , r.point_livraison_id = a.point_livraison_id 
                                                WHERE
                                                    t.type_flux = '".$sFluxReperage."'
                                                    AND r.id IS NOT NULL
                                                    AND a.id IS NOT NULL
                                                ";
                            $em->getConnection()->executeQuery($updateTournee);
                            $em->clear();


                            // A partir des codes tournees de Neopress, on met a jour le depot <=> c est la tournee de neopress qui prime par rapport a l adresse concernant le classement dans un depot
                            $this->oLog->info("Debut Mise a jour du champs depot a partir des codes tournees Neopress");
                            $updateDepot  = "   UPDATE
                                                    neopress_traitement_tmp t
                                                    LEFT JOIN reperage r ON r.abonne_soc_id = t.abonne_soc_id AND r.date_demar = t.date_distrib 
                                                    LEFT JOIN modele_tournee mt ON t.tournee_id = mt.id AND mt.actif = 1
                                                    LEFT JOIN groupe_tournee gt ON mt.groupe_id = gt.id
                                                SET
                                                    r.depot_id = gt.depot_id
                                                WHERE
                                                    r.depot_id <> gt.depot_id
                                                    AND gt.depot_id IS NOT NULL
                                                    AND t.type_flux = '".$sFluxReperage."'
                                                ";
                            $em->getConnection()->executeQuery($updateDepot);
                            $em->clear();
                            
                            
                        }


                        // Mise a jour du champs tournee de la table "client_a_servir_logist"
                        /*
                        $this->oLog->info("Debut Mise a jour du champs tournee de la table client_a_servir_logist");
                        $updateTourneeReclam  = " UPDATE 
                                                    crm_detail c
                                                    LEFT JOIN neopress_traitement_tmp t ON c.abonne_soc_id = t.abonne_soc_id AND c.date_distrib = t.date_distrib
                                                SET 
                                                    csl.tournee_jour_id = t.tournee_jour_id 
                                                    , csl.point_livraison_ordre = t.tournee_ordre 
                                                WHERE
                                                    1 = 1
                                                    AND t.id IS NOT NULL 
                                                ";
                        $em->getConnection()->executeQuery($updateTourneeReclam);
                        $em->clear();
                        */

                        // En Local - Sauvegarde du fichier
                        rename($this->sRepTmp.'/'.$sFicV, $this->sRepBkpLocal.'/'.$this->oString->renommeFicDeSvgrde($sFicV, $this->sDateCourantYmd, $this->sHeureCourantYmd));


                    }
                    else 
                    {
                        $this->suiviCommand->setMsg("Erreur lors de la lecture du fichier ".$this->sRepTmp.'/'.$sFicV);
                        $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_ERROR));
                        $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                        $this->suiviCommand->setEtat("KO");
                        $this->oLog->erreur("Erreur lors de la lecture du fichier ".$this->sRepTmp.'/'.$sFicV, E_USER_ERROR);
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
        $this->oLog->info(date("d/m/Y H:i:s : ")."Fin Divers traitements concernant les abonnes et produits Neopress - Commande : ".$this->sNomCommande);
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
