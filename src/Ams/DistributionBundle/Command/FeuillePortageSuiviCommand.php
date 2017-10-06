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
 * Verification des fichiers de feuilles de portage generes
 * 
 * 
 * Par defaut, on ne fait le calcul que le jour J+1.
 * Parametre : 
 *          Jour minimum a calculer. C'est optionnel
 *          Jour maximum a calculer. C'est optionnel
 *          Flux "jour" ou "nuit" [--jn=..]
 *          Code depots (exemple : 028,029,042) [--cd=..]
 *          Destinataires du mail (exemple : mail1@amaury.com, mail2@amaury.com) [--dest=..]
 *          Environnement [--env=..]
 * Expl : J+1 J+1
 * Si les parametres sont renseignes, le traitement concerne les jours de distribution de "jour minimum" a "jour maximum"
 * 
 * Exemple de commande : 
 *                      php app/console feuille_portage_suivi J+1 J+1 --jn=nuit --cd=028,029,042 --dest="mail1@amaury.com, mail2@amaury.com" --id_sh=cron_test --id_ai=1  --env=dev
 *                      php app/console feuille_portage_suivi J+1 J+1 --id_sh=cron_test --id_ai=1  --env=dev
 * 
 * 
 * @author aandrianiaina
 *
 */
class FeuillePortageSuiviCommand extends GlobalCommand
{
    protected function configure()
    {
    	$this->sNomCommande	= 'feuille_portage_suivi';
        $sJourATraiterMinParDefaut = "J+1";
        $sJourATraiterMaxParDefaut = "J+1";
        $sJourOuNuitDefaut = "tout";    // Flux : jour ou nuit
        $sCDDefaut = "tout";    // Centre de distribution par defaut
        $sDestinatairesMailDefaut = "";    
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console feuille_portage_suivi Expl : php app/console feuille_portage_suivi J+1 J+1 --env=prod
        $this
            ->setDescription('Verification des fichiers de feuilles de portage generes.')
            ->addArgument('jour_a_traiter_min', InputArgument::OPTIONAL, 'Jour a traiter Min. Format : J<+Numerique> (Expl : J+1 ou J+2 ou J ...)', $sJourATraiterMinParDefaut)
            ->addArgument('jour_a_traiter_max', InputArgument::OPTIONAL, 'Jour a traiter Max. Format : J<+Numerique> (Expl : J+1 ou J+2 ou J ...)', $sJourATraiterMaxParDefaut)
            ->addOption('jn',null, InputOption::VALUE_REQUIRED, 'jour ou nuit ?', $sJourOuNuitDefaut)
            ->addOption('cd',null, InputOption::VALUE_REQUIRED, 'Liste des codes des centres de distribution separees par "," ?', $sCDDefaut)
            ->addOption('dest',null, InputOption::VALUE_REQUIRED, 'Liste des adresses mail des destinataires separees par "," ?', $sDestinatairesMailDefaut)
            ->addOption('id_sh',null, InputOption::VALUE_REQUIRED, 'Libelle du CRON')
            ->addOption('id_ai',null, InputOption::VALUE_REQUIRED, 'Id du CRON')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
    	
        if ($input->getOption('env')) {
            $sEnvironnement   = $input->getOption('env');
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
        $sJourOuNuitDefaut    = "tout";
        $sCDDefaut = "tout";
        $sDestinatairesMail = $sDestinatairesMailDefaut = "";
        $aCodesCD    = array();
        $sCodeJourOuNuit    = '';
                
        $sJourATraiterMin  = $input->getArgument('jour_a_traiter_min');
        $sJourATraiterMax  = $input->getArgument('jour_a_traiter_max');
        
        if ($input->getOption('jn')) {
            $sJourOuNuit   = $input->getOption('jn');
            if(trim(strtoupper($sJourOuNuit)) != trim(strtoupper($sCDDefaut)))
            {
                $sJourOuNuit    = strtoupper($sJourOuNuit);
                $sCodeJourOuNuit    = substr($sJourOuNuit, 0, 1);
            }
        }
        
        if ($input->getOption('cd')) {
            $sCD   = $input->getOption('cd');
            if(trim($sCD) != $sCDDefaut)
            {
                $aCodesCD    = explode(',', trim($sCD));
                foreach($aCodesCD as $k => $v)
                {
                    $aCodesCD[$k]    = trim($v);
                }
            }
        }
        
        if ($input->getOption('dest')) {
            $sDestinatairesMail   = $input->getOption('dest');
        }
        if(trim($sDestinatairesMail) == trim($sDestinatairesMailDefaut))
        {
            // on recupere les destinataires de la BDD
            $repoUtilisateur   = $this->getContainer()->get('doctrine')->getRepository('AmsSilogBundle:Utilisateur');
            try {
                $sDestinatairesMail = trim($repoUtilisateur->destinataires_mail_suivi_feuilles_portage());
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
        
        if(trim($sDestinatairesMail) == trim($sDestinatairesMailDefaut))
        {
            $this->suiviCommand->setMsg("Les destinataires de mail ne sont pas definis");
            $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_WARNING));
            $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
            $this->suiviCommand->setEtat("KO");
            $this->oLog->erreur("Les destinataires de mail ne sont pas definis", E_USER_WARNING);
        }
        else
        {
            $sRepPrinc  = $this->getContainer()->getParameter("REP_FEUILLE_PORTAGE");
            if($input->getOption('id_ai') && $input->getOption('id_sh')){
                $this->oLog->info(date("d/m/Y H:i:s : ")."Debut Verification des fichiers de feuilles de portage generes - Commande : ".$this->sNomCommande." $sJourATraiterMin $sJourATraiterMax --id_sh=".$idSh." --id_ai=".$idAi."  --env=$sEnvironnement");
            }else{
                $this->oLog->info(date("d/m/Y H:i:s : ")."Debut Verification des fichiers de feuilles de portage generes - Commande : ".$this->sNomCommande." $sJourATraiterMin $sJourATraiterMax --env=$sEnvironnement");
            }
            $em    = $this->getContainer()->get('doctrine')->getManager();

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

                $aoJourATraiter[$dateDistribATraiter->format("Y-m-d")] = $dateDistribATraiter;
            }

            ksort($aoJourATraiter);

            if(!empty($aoJourATraiter))
            {
                try {
                    $repoClientAServirLogist   = $this->getContainer()->get('doctrine')->getRepository('AmsDistributionBundle:ClientAServirLogist');
                    $paramFlux  = 0;
                    if($sCodeJourOuNuit!='')
                    {
                        $repoRefFlux   = $this->getContainer()->get('doctrine')->getRepository('AmsReferentielBundle:RefFlux');
                        $oJourOuNuit    = $repoRefFlux->findOneByCode($sCodeJourOuNuit);
                        if($oJourOuNuit)
                        {
                            $paramFlux  = $oJourOuNuit->getId();
                        }
                    }

                    foreach($aoJourATraiter as $dateDistribAVerif)
                    {
                        $this->oLog->info(date("d/m/Y H:i:s : ")." Verification des feuilles de portage du date ".$dateDistribAVerif->format('d/m/Y')." ");    
                        $aInfosFeuillesAVerifier    = $repoClientAServirLogist->getInfosFeuillesPortageAVerifier($dateDistribAVerif, $paramFlux, $aCodesCD);

                        $aRetourVerif   = $this->verif_fichiers_feuilles_portage($sRepPrinc, $aInfosFeuillesAVerifier);

                        if(isset($aRetourVerif['NOK']))
                        {
                            $this->container = $this->getContainer();
                            $oEmailService = $this->container->get('email');
                            $aMsg   = array();
                            $aMsg[] = 
                            $aMailDatas = array(
                                                'sMailDest' => $sDestinatairesMail,
                                                'sSubject' => '!!! Feuilles de portage non générées [MROAD '.ucfirst($sEnvironnement).']',
                                                'sDateCourant' => date('d/m/Y'),
                                                'sHeureCourant' => date('H:i:s'),
                                                'aListeFicAbsents' => $this->transfo_donnees($aRetourVerif['NOK']),
                                                'sContentHTML' => '<strong>test ....Suivi de l\'activité</strong><br/><br/>
                                            Le fichier a bien été généré.<br/>Vous pouvez le télécharger en cliquant sur le lien ci-dessous.<br/><br/>'
                                        );
                            $sTemplate = 'AmsDistributionBundle:Emails:mail_suivi_feuille_portage.mail.twig';

                            // Initialiser le cache 
                            $view =  $this->container->get('twig');
                            $view->setCache(false);

                            if ($oEmailService->send($sTemplate, $aMailDatas)) {
                                $this->oLog->info('Mail envoye a destination de ' . $aMailDatas['sMailDest']);
                            } else {
                                $this->suiviCommand->setMsg();
                                $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_WARNING));
                                $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                                $this->suiviCommand->setEtat("OK");
                                $this->oLog->erreur("Erreur lors de l' envoi du mail", E_USER_WARNING);
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
        }
        
        $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
        $this->endTraitement();
        if($input->getOption('id_ai') && $input->getOption('id_sh')){
            $this->oLog->info(date("d/m/Y H:i:s : ")."Fin Verification des fichiers de feuilles de portage generes - Commande : ".$this->sNomCommande." $sJourATraiterMin $sJourATraiterMax --id_sh=".$idSh." --id_ai=".$idAi."  --env=$sEnvironnement");    
        }else{
            $this->oLog->info(date("d/m/Y H:i:s : ")."Fin Verification des fichiers de feuilles de portage generes - Commande : ".$this->sNomCommande." $sJourATraiterMin $sJourATraiterMax --env=$sEnvironnement");    
        }
        return;
    }
    
    
    private function verif_fichiers_feuilles_portage($sRepPrinc, $aInfosFeuillesAVerifier)
    {
        $aRegexAverifier    = array();
        $aInfosFeuillePresente   = array();
        $aInfosFeuilleAbsente   = array();
        
        $aRetour    = array();
        foreach ($aInfosFeuillesAVerifier as $sDateK => $aParDate) {
            $sSsRep = $sRepPrinc.'/'.$sDateK;
            if(is_dir($sSsRep))
            {
                $aFic   = array();  // Liste des fichiers dans le sous repertoire $sSsRep
                $aFicRep = scandir($sSsRep);
                foreach ($aFicRep as $sFicRep)
                {
                    if(!in_array($sFicRep,array(".","..")) && is_file($sSsRep.'/'.$sFicRep))
                    {
                        $aFic[] = $sFicRep;
                    }
                }
                
                foreach($aParDate as $aInfoFicAttendu)
                {
                    $aFicPresent    = array();
                    $sRegexAVerifier    = '/'.$aInfoFicAttendu['depot_code'].'_'.$aInfoFicAttendu['flux_id'].'_'.$aInfoFicAttendu['date_Ymd'].'_feuilleportage\.pdf$/';
                    $aFicPresent    = preg_grep($sRegexAVerifier, $aFic);
                    if(!empty($aFicPresent))
                    {
                        $aInfosFeuillePresente[$sDateK][]    = $aInfoFicAttendu;
                    }
                    else
                    {
                        $aInfosFeuilleAbsente[$sDateK][]    = $aInfoFicAttendu;
                    }
                }
            }
            else
            {
                foreach($aParDate as $aArr)
                {
                    $aInfosFeuilleAbsente[$sDateK][]    = $aArr;
                }
            }
        }
        
        if(!empty($aInfosFeuillePresente))  $aRetour['OK']    = $aInfosFeuillePresente;
        if(!empty($aInfosFeuilleAbsente))  $aRetour['NOK']    = $aInfosFeuilleAbsente;
        
        return $aRetour;
    }
    
    
    private function transfo_donnees($aDonnees)
    {
        $aRetour    = array();
        
        // date_rowspan
        foreach($aDonnees as $sDate_Ymd => $aInfoFicsAbsents)
        {
            $aRowspan = array_keys($aInfoFicsAbsents);
            $iRowspan   = count($aRowspan);
            foreach($aInfoFicsAbsents as $aInfoFicAbsent)
            {
                $aRetour['date_rowspan'][$aInfoFicAbsent['date_dmY']]    = $iRowspan;
            }
        }
        
        foreach($aDonnees as $sDate_Ymd => $aInfoFicsAbsents)
        {
            foreach($aInfoFicsAbsents as $aInfoFicAbsent)
            {
                $aRetour['donnees'][$aInfoFicAbsent['date_dmY']][$aInfoFicAbsent['flux_libelle']][]    = $aInfoFicAbsent['depot_libelle'];
            }
        }
        
        // date_flux_rowspan
        foreach($aRetour['donnees'] as $sDate_dmY => $aFlux)
        {
            foreach($aFlux as $sFlux => $aInfoFicAbsent)
            {
                $aRetour['date_flux_rowspan'][$sDate_dmY][$sFlux]    = count($aInfoFicAbsent);
            }
        }
        
        // Nb Fichiers absents
        $aRetour['nb_fics_absents'] = 0;
        foreach($aRetour['donnees'] as $sDate_dmY => $aFlux)
        {
            foreach($aFlux as $sFlux => $aInfoFicAbsent)
            {
                $aRetour['nb_fics_absents'] += count($aInfoFicAbsent);
            }
        }
        return $aRetour;
    }
}
