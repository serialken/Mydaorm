<?php
namespace Ams\DistributionBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ams\SilogBundle\Command\GlobalCommand;
use Doctrine\DBAL\DBALException;
use Ams\DistributionBundle\Entity\FeuillePortage;

/**
 *
 * Generation des feuilles de portage
 *
 *
 * Par defaut, on ne fait le calcul que pour le jour J+1 et pour tous les centres, pour toutes les tournees, pour tous les flux & pour toutes les societes.
 * Parametre :
 *          Jour minimum a calculer. C'est optionnel
 *          Jour maximum a calculer. C'est optionnel
 *          Liste des depots a traiter. C'est optionnel. Pour les renseigner : --cd=code_cd1,code_cd2,code_cd3,code_cd4,...
 *          Liste des codes tournees (Attention !!!! PAS de TOURNEE JOUR) a traiter. C'est optionnel. Pour les renseigner : --trn=code_tournee1,code_tournee2,code_tournee3,code_tournee4,...
 *          Liste des societes a traiter. C'est optionnel. Pour les renseigner : --soc=code_soc1,code_soc2,code_soc3,code_soc4,...
 *          Le flux a traiter. C'est optionnel. Pour les renseigner : --flux=N ou --flux=J ou --flux=N,J
 * Expl : J+1 J+5
 * Si les parametres sont renseignes, le traitement concerne les jours de distribution de "jour minimum" a "jour maximum"
 * Expl : J-1. => calculs a faire concernent J-1, J, J+1, J+2 & J+3
 *
 * Exemple de commande : php app/console feuille_portage J+0 J+3 --cd=007,010 --trn=007NBF001,010NCA001 --soc=FI,AF --flux=N --env=dev
 * Exemple de commande : php app/console feuille_portage J-4 J-4 --flux=1 --env=local
 *
 *
 * @author kevin jean-baptiste
 *
 */
class FeuillePortageCommand extends GlobalCommand
{

    protected function configure()
    {
    	$this->sNomCommande	= 'feuille_portage';
        $sJourATraiterMinParDefaut = "J+1";
        $sJourATraiterMaxParDefaut = "J+2";
        $sTourneesATraiterDefaut = $sSocATraiterDefaut = $sFluxDefaut = $sCDATraiterDefaut = "";

    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console feuille_portage J+1 J+5 --env=dev Expl : php app/console feuille_portage J+0 J+3 --cd=007,010 --trn=39467,39468 --soc=FI,AF --env=dev
        // php app/console feuille_portage J-2 J-2 --cd=034 --soc=LP
        $this
            ->setDescription('Generation des feuilles de portage.')
            ->addArgument('jour_a_traiter_min', InputArgument::OPTIONAL, 'Jour a traiter Min. Format : J<+Numerique> (Expl : J+1 ou J+2 ou J ...)', $sJourATraiterMinParDefaut)
            ->addArgument('jour_a_traiter_max', InputArgument::OPTIONAL, 'Jour a traiter Max. Format : J<+Numerique> (Expl : J+1 ou J+2 ou J ...)', $sJourATraiterMaxParDefaut)
            ->addOption('cd',null, InputOption::VALUE_REQUIRED, 'Listes des codes depots (A separer par des ",") ?', $sCDATraiterDefaut)
            ->addOption('trn',null, InputOption::VALUE_REQUIRED, 'Listes des codes tournees (A separer par des ",") ?', $sTourneesATraiterDefaut)
            ->addOption('soc',null, InputOption::VALUE_REQUIRED, 'Listes des codes societes (A separer par des ",") ?', $sSocATraiterDefaut)
            ->addOption('product',null, InputOption::VALUE_REQUIRED, 'Listes des produits (A separer par des ",")')
            ->addOption('flux',null, InputOption::VALUE_REQUIRED, 'Flux ?', $sFluxDefaut)
            ->addOption('user',null, InputOption::VALUE_OPTIONAL, 'Generation by "user,automatique"')
            ->addOption('task',null, InputOption::VALUE_OPTIONAL, 'id de la tache')
            ->addOption('exclude_stop',null, InputOption::VALUE_OPTIONAL, 'Listes des codes depots ou les nouveaux et arret ne sont pas pris en compte separé par (,)')
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
        $this->oLog->info("Debut Generation des feuilles de portage : ".$this->sNomCommande);

        /** INITIALISATION DE VARIABLE **/
        $em = $this->getContainer()->get('doctrine')->getManager();
        $iJourATraiter = 0;
        $aTotalNewByTournee = $aTotalStopByTournee = $aNewAbonneDepot = $aStopAbonneDepot = $aQteNewProductByTournee = $aQteStopProductByTournee =array();
        $aOptions = $aoJourATraiter = array();
        $pathImgToPdf= $this->getContainer()->getParameter("IMG_ROOT_DIR_TO_PDF");
        $folderPdf= $this->getContainer()->getParameter("REP_FEUILLE_PORTAGE").'/';
        $folderFont = $this->getContainer()->getParameter("DIR_TO_FONT");
        $this->launchFromMroad = false;
        $this->uniqueFolder = $folderPdf.uniqid();
        /** JOUR A TRAITER **/
        $aiJourATraiter = $this->getJourATraiter($input->getArgument('jour_a_traiter_max'),$input->getArgument('jour_a_traiter_min'));
        /** LES OPTIONS **/
        $this->getOptions($input,$aOptions);
        /** ACTIVATION DES LOGS**/
        $em->getConnection()->getConfiguration()->setSQLLogger(null);
        
        /** on desactives tous les infos portages dont la date de fin est dépassée */ 
        $em->getRepository('AmsDistributionBundle:InfoPortage')->desactiveInfoPortage();
        

        foreach($aiJourATraiter as $iJourATraiter)
        {
            $oDateDuJour    = new \DateTime(date('Y-m-d'));
            $oDateDuJour->setTime(0, 0, 0);
            $dateDistribATraiter   = $oDateDuJour;
            if($iJourATraiter<0) $dateDistribATraiter   = $oDateDuJour->sub(new \DateInterval('P'.abs($iJourATraiter).'D'));
            else $dateDistribATraiter   = $oDateDuJour->add(new \DateInterval('P'.$iJourATraiter.'D'));

            $aoJourATraiter[$iJourATraiter] = $dateDistribATraiter;
            echo "\n Jour a traiter : ".$dateDistribATraiter->format('d/m/Y')."\n";

            /** DOSSIER PRINCIPAL**/
            $this->isDirRecursive($folderPdf.$dateDistribATraiter->format('Y-m-d'));
            /** RECUPERATION DU / DES DEPOT(S) POUR LE JOUR DONNER **/
            $aDepotsByDate = $this->getDepots($aOptions['cd'],$dateDistribATraiter->format('Y-m-d'),$em);

            /** ITERATION SUR LES DEPOTS **/
            foreach($aDepotsByDate as $depot){
                /** TRUNCATE & INSERTION DATA TABLE FEUILLE PORTAGE_TMP**/
                $em->getRepository('AmsDistributionBundle:FeuillePortageTmp')->truncateFeuillePortageTmp();
                $em->getRepository('AmsDistributionBundle:FeuillePortageTmp')->insertFeuillePortageTmp($depot['code'],$input->getOption('flux'),$dateDistribATraiter->format('Y-m-d'));
                /** OBJET DEPOT**/
                $oDepot = $em->getRepository('AmsSilogBundle:Depot')->findOneByCode($depot['code']);
                
                if ($input->getOption('user') == ''){
                    /** CPAM PARTIE **/
                    $produit =  $em->getRepository('AmsProduitBundle:Produit')->findOneByCode('NA02001'); //code produit CPAM
                    if($produit){
                        $aParamCpam = array(
                            'depotCode'     => $depot['code'],
                            'fluxId'        => $aOptions['flux'],
                            'tourneeId'     => false,
                            'produitId'     => $produit->getId(),
                        );

                        $this->Cpam($aParamCpam,$folderPdf,$dateDistribATraiter,$em);
                    }
                    /** BORDEREAUX **/
                    $aBordereauLivraison = $em->getRepository('AmsDistributionBundle:Bordereau')->getBordereau($oDepot,$dateDistribATraiter->format('Y-m-d'),$aOptions['flux']);
                     
                    $this->Bordereaux($aBordereauLivraison,$depot['code'],$dateDistribATraiter,$input->getOption('flux'),$folderPdf,$pathImgToPdf,$em);
                }
                /** FEUILLE PORTAGE **/

                /** CREATION DU DOSSIER TEMPORAIRE **/
                $tmpFolder = $folderPdf.$dateDistribATraiter->format('Y-m-d').'/tournees_'.$depot['code'].'_'.$input->getOption('flux');
                $this->isDirRecursive($tmpFolder);
                $this->oLog->info("Traitement pour le depot =>".$depot['code']);

                $aParamFeuillePortageTmp = array(
                            'depotCode'  => $depot['code'],
                            'fluxId'     => $aOptions['flux'],
                            'tourneeId'  => $aOptions['trn'],
                            'produitId'  => $input->getOption('product'),
                            'socCode'    => explode(',',$input->getOption('soc')),
                        );
                $dataFeuillePortage  = $em->getRepository('AmsDistributionBundle:FeuillePortageTmp')->getFeuillePortageTmpByDepotFlux($aParamFeuillePortageTmp);
                if(!$dataFeuillePortage) continue;
                $aParamNewStop = array(
                    'dateDistrib' => $dateDistribATraiter->format('Y-m-d'),
                    'depot'        => $oDepot->getId(),
                    'produitId'      => $input->getOption('product'),
                    'socCode'    => explode(',',$input->getOption('soc')),
                );
                if(!in_array($depot['code'], explode(',', $aOptions['exclude_stop']))){
                    /** CALCUL DU NOMBRE D'ARRET D'ABONNE POUR UN DEPOT  **/
                    $aStopAbonneDepot  = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getAbonneStop($aParamNewStop);
                    $this->StopByTournee($aStopAbonneDepot,$aTotalStopByTournee,$aQteStopProductByTournee);
                }
                if(!in_array($depot['code'], explode(',', $aOptions['exclude_stop']))){
                    /** CALCUL DU NOMBRE DE NOUVEAU ABONNE POUR UN DEPOT  **/
                    $aNewAbonneDepot  = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getNewAbonne($aParamNewStop);
                    $this->NewByTournee($aNewAbonneDepot,$aTotalNewByTournee,$aQteNewProductByTournee);
                }
                
                /** CALCUL DU NOMBRE DE PRODUIT POUR UN DEPOT  **/
                $aTotalProductByTournee = array_map(function($item) {
                    return array($item['tournee_jour_id'] => $item['qte'], 'produit_id'=> $item['produit_id']);
                }, $dataFeuillePortage);

                $aProductTournee = $tmp = $tmpTournee = $aDataTournee = $aStopAbonneTournee = $aNewAbonneTournee = [];
                $this->dataFeuillePortage($dataFeuillePortage,$aProductTournee,$aNewAbonneTournee,$aStopAbonneTournee,$aDataTournee,$aTotalProductByTournee,$aQteNewProductByTournee,$aQteStopProductByTournee,$aNewAbonneDepot,$aTotalNewByTournee,$aTotalStopByTournee,$aStopAbonneDepot,$em,$dateDistribATraiter);

                $this->launchFromMroad = ($input->getOption('user') == '')? false : true;
                /** CREATION DE PDF POUR CHAQUE TOURNEE **/
                $aTourneeByDepot = array_count_values( array_map(function($item){return $item['tournee_jour_code'];}, $dataFeuillePortage));
                $this->generationPdf($aTourneeByDepot,$dataFeuillePortage,$aProductTournee,$aStopAbonneTournee,$aNewAbonneTournee,$dateDistribATraiter,$aDataTournee,$pathImgToPdf,$tmpFolder,$folderFont);

                /** MERGE PDF **/
                $fileNameDestination = ($input->getOption('user') == '')? $depot['code'].'_'.$input->getOption('flux').'_'.$dateDistribATraiter->format('Ymd').'_feuilleportage.pdf' : 'file.pdf';
                $additionnalData = ($input->getOption('user') == '')? false: array('id_user' => $input->getOption('user'),'id_task' => $input->getOption('task'));
                $tmpFolder = ($input->getOption('user') == '')? $tmpFolder : $this->uniqueFolder;
                $this->pdfMerge($folderPdf.$dateDistribATraiter->format('Y-m-d'),$fileNameDestination,$tmpFolder,$additionnalData);

                if($input->getOption('user') == ''){
                    /** ON MET A JOUR LA TABLE FEUILLE DE PORTAGE**/
                    $this->updateFeuillePortageTable($em,$dataFeuillePortage,$depot['code'],$input,$dateDistribATraiter->format('Y-m-d'));
                }
            }

            /** TRUNCATE DE LA TABLE FEUILLE_PORTAGE_TMP**/
            $em->getRepository('AmsDistributionBundle:FeuillePortageTmp')->truncateFeuillePortageTmp(); // on vide à fin du script
        } /** END foreach($aiJourATraiter as $iJourATraiter)  **/
        $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
        $this->endTraitement();
        $this->oLog->info("Fin Generation des feuilles de portage : ".$this->sNomCommande);
        return;
    }



    private function updateFeuillePortageTable($em,$dataFeuillePortage,$depotCode,$input,$dateDistribATraiter){
        /** DELETE DOUBLON **/
        $oTmpDepot = $em->getRepository('AmsSilogBundle:Depot')->findOneByCode($depotCode);
        $em->getRepository('AmsDistributionBundle:FeuillePortage')->deleteDoublon($input->getOption('flux'),$oTmpDepot->getId(),$dateDistribATraiter);
        /** SAVE QUERY **/
        $values = '';
        foreach($dataFeuillePortage as $key=>$q){
          $adresse = $this->explode_adresse($q['adresse']);
          if($key)
              $values .=',';
          $values .= '
                ('.$q['tournee_jour_id'].','.$q['depot_id'].','.$q['flux'].',
                 "'.$dateDistribATraiter.'","'.$dateDistribATraiter.'",
                 "'.$q['num_abonne'].'","'.addslashes($q['vol1']).'","'.addslashes($q['vol2']).'",
                 "'.addslashes($adresse['numVoie']).'","'.  addslashes($adresse['voie']).'",'.$q['cp'].',
                 "'.addslashes($q['ville']).'","'.addslashes($q['valeur']).'",'.$q['qte'].','.$q['produit_id'].',
                 "'.addslashes($q['produit_libelle']).'",'.$q['point_livraison_ordre'].
                ')
                ';
        }
        $em->getRepository('AmsDistributionBundle:FeuillePortage')->insertMultiple($values);
    }

    private function getJourATraiter($sJourATraiterMax,$sJourATraiterMin){
        $aiJourATraiterMin = $aiJourATraiterMax = array();
        if(preg_match('/^J([\-\+][0-9]+)?$/', $sJourATraiterMin, $aiJourATraiterMin) && preg_match('/^J([\-\+][0-9]+)?$/', $sJourATraiterMax, $aiJourATraiterMax))
        {
            $iJourATraiterMin = $iJourATraiterMax = 0;
            if(isset($aiJourATraiterMin[1]))
                $iJourATraiterMin  = intval($aiJourATraiterMin[1]);

            if(isset($aiJourATraiterMax[1]))
                $iJourATraiterMax  = intval($aiJourATraiterMax[1]);

            if($iJourATraiterMax >= $iJourATraiterMin)
            {
                for($i=$iJourATraiterMin; $i<=$iJourATraiterMax; $i++)
                    $aiJourATraiter[]    = $i;
            }
            else{
                $this->suiviCommand->setMsg("Le jour MAX est anterieur au Jour MIN (Jour min : J".(($iJourATraiterMin>=0)?"+":"-").abs($iJourATraiterMin).". Jour max : J".(($iJourATraiterMax>=0)?"+":"-").abs($iJourATraiterMax).").");
                $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_WARNING));
                $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                $this->suiviCommand->setEtat("OK");
                $this->oLog->erreur("Le jour MAX est anterieur au Jour MIN (Jour min : J".(($iJourATraiterMin>=0)?"+":"-").abs($iJourATraiterMin).". Jour max : J".(($iJourATraiterMax>=0)?"+":"-").abs($iJourATraiterMax).").", E_USER_WARNING);
            }
        }
        else{
            $this->suiviCommand->setMsg("Jour a traiter. Format : J<+Numerique> (Expl : J-1 ou J-2 ou J ...)");
                $this->suiviCommand->setErrorType($this->oLog->get_libelle_err_type(E_USER_WARNING));
                $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
                $this->suiviCommand->setEtat("OK");
                $this->oLog->erreur("Jour a traiter. Format : J<+Numerique> (Expl : J-1 ou J-2 ou J ...)", E_USER_WARNING);
        }
            
        return $aiJourATraiter;
    }

    private function getOptions($input,&$aOptions){
        $aOptions['cd'] = ($input->getOption('cd')) ? $input->getOption('cd') : '';
        $aOptions['exclude_stop'] = ($input->getOption('exclude_stop')) ? $input->getOption('exclude_stop') : '';
        $aOptions['trn'] = ($input->getOption('trn')) ? $input->getOption('trn') : '';
        $aOptions['soc'] = ($input->getOption('soc')) ? $input->getOption('soc') : '';
        $aOptions['flux'] = ($input->getOption('flux')) ? $input->getOption('flux') : '';
        echo "Les options :\n";print_r($aOptions);echo "\n";
    }

    private function getDepots($depotCode,$dateDistrib,$em){
        if(!$depotCode)
            $aDepotsByDate  = $em->getRepository('AmsDistributionBundle:FeuillePortage')->getDepotBydate($dateDistrib);
        else
            $aDepotsByDate = array(array('code' => $depotCode));
        return $aDepotsByDate;
    }
    private function Cpam($param,$folderPdf,$dateDistribATraiter,$em){
        $tmpFolder = $folderPdf.$dateDistribATraiter->format('Y-m-d').'/cpam/tmp';
        $this->isDirRecursive($tmpFolder);

        $this->oLog->info(" - Debut generation CPAM ".$param['depotCode']);
        $cpamTournes = $em->getRepository('AmsDistributionBundle:FeuillePortageTmp')->getFeuillePortageTmpByDepotFlux($param); // code produit CPAM
        if( count($cpamTournes) > 0 ) {
            $data = array();
            foreach($cpamTournes as $cpamTourne) {
                $data[$cpamTourne['tournee_jour_code']][]= $cpamTourne;
                $qte[$cpamTourne['tournee_jour_code']][]= $cpamTourne['qte'];
                foreach ($data as $code =>$tournee ) {
                    $header = $this->getContainer()->get('templating')
                       ->render('AmsDistributionBundle:FeuillePortage:header.html.twig',
                        array('tournee' => $code,'depot' => "Carnet de tournee - Centre :".$param['depotCode']));

                    $sFileHtmlCpam =
                        $this->getContainer()->get('templating')
                        ->render('AmsDistributionBundle:FeuillePortage:template_cpam.html.php',
                        array('query'=>$tournee,'date_distrib'=>$dateDistribATraiter,'depot_libelle' => $param['depotCode'],
                              'tournee_libelle' => $code,'total' => array_sum($qte[$code])
                        ));
                       $cpamFile = $param['depotCode'].'_'.$param['fluxId'].'_'.$dateDistribATraiter->format('Ymd').'_'.$code.'_cpam';
                      $this->pdfGeneration($cpamFile, $tmpFolder, $sFileHtmlCpam, $dateDistribATraiter->format('Y-m-d'), $header);
                }
            }
            $file = $param['depotCode'].'_'.$param['fluxId'].'_'.$dateDistribATraiter->format('Ymd').'_cpam.pdf';
            $folderDestination = $folderPdf.$dateDistribATraiter->format('Y-m-d').'/cpam';
            $this->pdfMerge($folderDestination,$file,$tmpFolder);
        }
    }

    private function Bordereaux($aBordereauLivraison,$depotCode,$dateDistribATraiter,$flux,$folderPdf,$pathImgToPdf,$em){
        if(!$aBordereauLivraison) return false;
        $tmpFolder = $folderPdf.$dateDistribATraiter->format('Y-m-d').'/bordereau/tmp';
        $this->isDirRecursive($tmpFolder);
        foreach($aBordereauLivraison as $bordereau){
            $dataAbonne = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getDataBordereau($bordereau['PL'],$bordereau['code_tournee'],$dateDistribATraiter->format('Y-m-d'),$flux);
            if(!$dataAbonne) continue;
            $sFileHtmlBordereau =
                $this->getContainer()
                ->get('templating')
                ->render('AmsDistributionBundle:FeuillePortage:template_bordereau.html.php',
                        array('query'=>$dataAbonne,
                              'date'=>$dateDistribATraiter->format('d/m/Y'),
                              'path'=>$pathImgToPdf,
                            ));
            
            $fileName = $depotCode.'_'.$bordereau['PL'];
            $this->pdfGeneration($fileName,$tmpFolder,$sFileHtmlBordereau);
        }
        /** MERGE BORDEREAU PDF && DELETE TMP FOLDER**/
        $file = $depotCode.'_'.$flux.'_'.$dateDistribATraiter->format('Ymd').'_bordereau.pdf';
        $folderDestination = $folderPdf.$dateDistribATraiter->format('Y-m-d').'/bordereau';
        $this->pdfMerge($folderDestination,$file,$tmpFolder);
    }

    private function StopByTournee($aStopAbonneDepot,&$aTotalStopByTournee,&$aQteStopProductByTournee){
        $aTotalStopByTournee = array_count_values(array_map(function($item) {
            if(is_numeric($item['tournee_jour_id']))
                return  $item['tournee_jour_id'];
            return 0;
        }, $aStopAbonneDepot));

        $aQteStopProductByTournee = array_map(function($item) {
            return array($item['id_recent'] => $item['produit_id'] );
                }, $aStopAbonneDepot);
    }
    private function NewByTournee($aNewAbonneDepot,&$aTotalNewByTournee,&$aQteNewProductByTournee){
        $aTotalNewByTournee = array_count_values(array_map(function($item) {
            if(is_numeric($item['tournee_jour_id']))
                return $item['tournee_jour_id'];
            return 0;
        }, $aNewAbonneDepot));

        $aQteNewProductByTournee = array_map(function($item) {
            return array($item['tournee_jour_id'] => $item['produit_id'] );
        }, $aNewAbonneDepot);
    }

    private function dataFeuillePortage($dataFeuillePortage,&$aProductTournee,&$aNewAbonneTournee,&$aStopAbonneTournee,&$aDataTournee,$aTotalProductByTournee,$aQteNewProductByTournee,$aQteStopProductByTournee,$aNewAbonneDepot,$aTotalNewByTournee,$aTotalStopByTournee,$aStopAbonneDepot,$em,$dateDistribATraiter){
        $tmp = $tmpTournee = [];
        foreach($dataFeuillePortage as $key=>$data){
            $rue_voie=$this->explode_adresse($data['adresse']);
            if($rue_voie['numVoie']==""){
              $second_adress=$this->recherche_num_rue($dateDistribATraiter->format('Y-m-d'),$em, $data);
              if(isset($second_adress[0]['adresse'])){
              $data['adresse']=$second_adress[0]['adresse'];
              $dataFeuillePortage[$key]["adresse"]=$second_adress[0]['adresse'];
              }
            }
            /** CALCUL DES QUANTITES(CLIENTS,ADRESSES,JOURNAUX) PAR PRODUIT**/
            $aDataTournee[$data['tournee_jour_code']][] = $data;
            if(!in_array($data['tournee_jour_code'].$data['produit_id'], $tmp)){
                if(!in_array($data['tournee_jour_code'], $tmpTournee)){
                    if($this->getStopAbonneTournee($aStopAbonneDepot,$data['tournee_jour_id']))
                        $aStopAbonneTournee[$data['tournee_jour_code']]= $this->getStopAbonneTournee($aStopAbonneDepot,$data['tournee_jour_id']);

                    if($this->getNewAbonneTournee($aNewAbonneDepot,$data['tournee_jour_id']))
                        $aNewAbonneTournee[$data['tournee_jour_code']]= $this->getNewAbonneTournee($aNewAbonneDepot,$data['tournee_jour_id']);
                }
                $aQtePointLivraisonByTournee = $this->countAdressByTournee($em,$data['tournee_jour_id']);
                $aQteAbonneByTournee = $this->countClientByTournee($em,$data['tournee_jour_id']);

                $tmp[] = $data['tournee_jour_code'].$data['produit_id'];
                $tmpTournee[] = $data['tournee_jour_code'];

                $aProductTournee[$data['tournee_jour_id']][$data['produit_id']] =
                array(
                    'produit_libelle' => $data['produit_libelle'], 
                    'img' => $data['path'],
                    'Qte_N' => $this->countNewByTourneeProduct($aQteNewProductByTournee,$data['tournee_jour_id'],$data['produit_id']),    // nb nouveau/(tournee/produit)
                    'Qte_S' => $this->countStopByTourneeProduct($aQteStopProductByTournee,$data['tournee_jour_id'],$data['produit_id']),    // nb d'arret/(tournee/produit)
                    'Qte_J' => $this->countProductByTournee($aTotalProductByTournee,$data['tournee_jour_id'],$data['produit_id']),// nb journaux/(tournee/produit)
                    'Qte_PL'=> $this->countVarByTourneeProduct($aQtePointLivraisonByTournee,$data['produit_id'],'point_livraison_id'), // nb point livraison/(tournee/produit)
                    'Qte_C' => $this->countVarByTourneeProduct($aQteAbonneByTournee,$data['produit_id'],'num_abonne'), // nb client/(tournee/produit)
                );
                $aProductTournee[$data['tournee_jour_id']]['STATS'] =  array(
                    'TOTAL_N' => (array_key_exists($data['tournee_jour_id'], $aTotalNewByTournee))  ? $aTotalNewByTournee[$data['tournee_jour_id']] : 0,
                    'TOTAL_S' => (array_key_exists($data['tournee_jour_id'], $aTotalStopByTournee)) ? $aTotalStopByTournee[$data['tournee_jour_id']]: 0, // Total arret/tournee
                    'TOTAL_J' => $this->countProductByTournee($aTotalProductByTournee,$data['tournee_jour_id']),
                    'TOTAL_PL' => count($aQtePointLivraisonByTournee),
                    'TOTAL_C' => count($aQteAbonneByTournee),
                );
            }
        }
        /** PRODUIT EN ARRET NON AFFICHER **/
        foreach($aStopAbonneDepot as $data){
            if(!isset($aProductTournee[$data['tournee_jour_id']][$data['produit_id']])){
                 $num = 0;
                 $tourneeId = $data['tournee_jour_id'];
                 $produitId = $data['produit_id'];
                 if(!$tourneeId) continue;
                 $test = array_map(function($item) use(&$num,$tourneeId,$produitId){
                    if( $item['tournee_jour_id'] == $tourneeId  && $item['produit_id'] == $produitId)
                    $num++;
                }, $aStopAbonneDepot);

                $aProductTournee[$data['tournee_jour_id']][$data['produit_id']] = array(
                    'produit_libelle' => $data['produit_libelle'], 
                    'img' => $data['path'],
                    'Qte_N' => 0,
                    'Qte_S' => $num,
                    'Qte_J' => 0,
                    'Qte_PL'=> 0,
                    'Qte_C' => 0,
                );
            }
        }
    }


    private function generationPdf($aTourneeByDepot,$dataFeuillePortage,$aProductTournee,$aStopAbonneTournee,$aNewAbonneTournee,$dateDistribATraiter,$aDataTournee,$pathImgToPdf,$tmpFolder,$folderFont){
        foreach($aTourneeByDepot as $tournee=>$val){
            /** HEADER PDF **/
            $header = $this->getContainer()
                           ->get('templating')->render('AmsDistributionBundle:FeuillePortage:header.html.twig',
                           array('tournee' => $tournee,'depot' => $this->getDepotNameByTournee($dataFeuillePortage,$tournee)));

            /** BODY PDF **/
            $sFileHtmlByTournee =
            $this->getContainer()
                ->get('templating')
                ->render('AmsDistributionBundle:FeuillePortage:template.html.php',
                array('query'=>$aDataTournee[$tournee],'date' => $dateDistribATraiter->format('d/m/Y'),
                      'path' => $pathImgToPdf ,'countProductTournee' => $aProductTournee ,'font'=>$folderFont,
                      'aStopAbonneTournee' => $aStopAbonneTournee ,'aNewAbonneTournee' => $aNewAbonneTournee ,
                ));
           
            if($this->launchFromMroad){
                $tmpFolder = $this->uniqueFolder;
                $this->isDirRecursive($tmpFolder);
            }
            $this->pdfGeneration($tournee,$tmpFolder,$sFileHtmlByTournee,$header);

            /** COUNT NB PAGE**/
            $fileNameTmp = $tournee.'.pdf';
            $nbPage = $this->nombre_de_pages($tmpFolder.'/'.$fileNameTmp);
            if(($nbPage % 2) != 0) $this->getWhitePage($tmpFolder,$fileNameTmp);
        }
    }


    private function countStopByTourneeProduct($aData,$iTournee,$iProduct){
        $num = 0;
        foreach($aData as $data){
            if(key($data) == $iTournee){
                if($data[$iTournee]== $iProduct)
                    $num++;
            }
        }
      return $num;
    }

    private function countNewByTourneeProduct($aData,$iTournee,$iProduct){
        return $this->countStopByTourneeProduct($aData,$iTournee,$iProduct);
    }


    private function getStopAbonneTournee($aStopAbonneDepot,$iTournee){
        $aData = array();
        foreach($aStopAbonneDepot as $key=>$data){
            if($data['id_recent'] == $iTournee)
              $aData[] = $data;
        }
      return $aData;
    }
    private function getNewAbonneTournee($aNewAbonneDepot,$iTournee){
       $aData = array();
        foreach($aNewAbonneDepot as $key=>$data){
            if($data['tournee_jour_id'] == $iTournee)
              $aData[] = $data;
        }
      return $aData;
    }

    private function countProductByTournee($aData,$iTournee,$iProduct = false){
      $qte = array();
      foreach($aData as $key=>$data){
          if($iProduct){
            if(key($data) == $iTournee && $data['produit_id'] == $iProduct){
              $qte[] = $data[$iTournee];
              unset($aData[$key]);
            }
          }
          else{
            if(key($data) == $iTournee){
              $qte[] = $data[$iTournee];
              unset($aData[$key]);
            }
          }
      }
      return array_sum($qte);
    }


    private function countAdressByTournee($em,$iTournee){
        $qtePointLivraison  = $em->getRepository('AmsDistributionBundle:FeuillePortageTmp')
                                 ->getPointLivraisonByTournee($iTournee);
        return $qtePointLivraison;
    }

    private function countVarByTourneeProduct($aData,$iProduct,$fields){
        $num = 0;
        $aTmp = array();
        foreach($aData as $data){
            if($data['produit_id'] == $iProduct && !in_array($data[$fields], $aTmp)){
                $aTmp[] = $data[$fields];
                $num++;
            }
        }
        return $num;
    }

    private function countClientByTournee($em,$iTournee){
        $qteAbonne  = $em->getRepository('AmsDistributionBundle:FeuillePortageTmp')
                                 ->getAbonneByTournee($iTournee);
        return $qteAbonne;
    }

    public function explode_adresse($adr){
        $retour = array('numVoie' => '', 'voie' => $adr);
        $regexAvecNumVoie = "/^([0-9]+\s?[a-z]\s|[0-9]+\s)(.+)$/i";
        if(preg_match_all($regexAvecNumVoie, $adr, $aArr))
        {
          $retour['numVoie']= $aArr[1][0];
          $retour['voie'] = $aArr[2][0];
        }
        return $retour;
      }

    private function getDepotNameByTournee($aData,$tournee){
        $aResult = array();
        foreach($aData as $data){
            if($data['tournee_jour_code'] == $tournee)
                return $data['depot_libelle'];
        }
    }

    private function getQteByProduct($data,$product){
        $quantite = 0;
        $libelle = '';
        foreach($data as $d){
            if($d['id_produit'] != $product) continue;
            $quantite += $d['qte'];
            $libelle = $d['product_libelle'];
        }
        $data = array('libelle'=>$libelle,'quantite' => $quantite);
        return $data;
    }

    private function pdfGeneration($fileName,$FolderGeneration,$fileHtml,$header= false){
        /** EXEMPLE wkhtmltopdf --header-html header.html -R 5 -L 5  /tmp/042NXB076DI.html tmp/042NXB076DI.pdf **/

        $sWkHtmlToPdf = '/usr/bin/wkhtmltopdf';
        $headerFile ='';
        if(strpos($fileName, 'cpam')){
            $aDateDistrib = explode('-',$header);
            $sWkHtmlToPdf .= '  -O landscape  --header-right "'.$aDateDistrib[2].'/'.$aDateDistrib[1].'/'.$aDateDistrib[0].'"';
        }
        else if($header){
            $headerFile = $FolderGeneration.'/header.html';
            $oFicHeader = fopen($headerFile, "w+");
            fputs($oFicHeader,$header);
            fclose($oFicHeader);
            $sWkHtmlToPdf = 'wkhtmltopdf --header-html '.$headerFile.' -R 5 -L 5 ';
        }

        $file    = $FolderGeneration.'/'.$fileName.'.html';
        $filePdf = $FolderGeneration.'/'.$fileName.'.pdf';
        $oFic = fopen($file, "w+");
        fputs($oFic,$fileHtml);
        fclose($oFic);
        /** EXECUTION COMMANDE **/
        exec($sWkHtmlToPdf.' '.$file.' '.$filePdf);
        unlink($file);
        if($header && !strpos($fileName, 'cpam')) unlink($headerFile);
    }

    private function getWhitePage($folderGeneration,$fileNameDestination){
        $folderPdf= $this->getContainer()->getParameter("REP_FEUILLE_PORTAGE").'/';
        exec('mv '.$folderGeneration.'/'.$fileNameDestination." ". $folderPdf);
        exec('cd '.$folderPdf.' && pdfunite '.$fileNameDestination.' page_blanche.pdf '.$folderGeneration.'/'.$fileNameDestination);
        exec('rm -f '.$folderPdf.'/'.$fileNameDestination);
    }

    private function isDirRecursive($path){
        $segments = explode('/', $path);
        $tmpPath= '';
        foreach($segments as $key=>$segment){
            $tmpPath .= (!$key) ? $segment : '/'.$segment ;
            if(!is_dir($tmpPath) && trim($tmpPath)!=''){
                mkdir($tmpPath, 0777);
            }
        }
    }

    private function pdfMerge($folderDestination,$fileNameDestination,$folderTmp,$additionnalData = false){
        if($additionnalData){
            $path =  $this->getContainer()->getParameter("REP_BACKGROUND_TMP").md5($additionnalData['id_user']).'/Feuille_Portage/'.$additionnalData['id_task'];
            $this->isDirRecursive($path);
            $folderDestination = $path;
        }
        $folderTmp = ($folderTmp)? $folderTmp : $folderDestination;
        $doted = array(".", "..");
        $ListFiles = array_diff(scandir($folderTmp), $doted);
        $numberFiles = count($ListFiles);
        if($numberFiles == 1){
            rename($folderTmp.'/'.current($ListFiles), $folderDestination.'/'.$fileNameDestination);
        }
        else{
            exec("cd ".$folderTmp." && pdfunite *.pdf " . $folderDestination.'/'.$fileNameDestination);
        }
        $this->delTree($folderTmp);
        chmod($folderDestination.'/'.$fileNameDestination,0777);
    }

    /**
     * @Method qui retourne la quantité par produit et par tournee
    **/
    private function calculQte($aQte){
        $qte = 0;
        foreach($aQte as $key=>$val)
            $qte += $key * $val;

        return $qte;
    }


    private function nombre_de_pages($pdf){
        if ( false !== ( $file = file_get_contents( $pdf ) ) ) {
            $pages = preg_match_all( "/\/Page\W/", $file, $matches );
            return $pages;

        }
    }

    private function timer($timestart,$timeend,$commentaire){
        $time=$timeend-$timestart;
        $page_load_time = number_format($time, 3);
        $this->oLog->info($commentaire.' '.$page_load_time.' sec');
    }
    public function recherche_num_rue($dateDistribATraiter,$em,$data) {
        $num=$em->getRepository('AmsAdresseBundle:AdresseRnvp')->getNumrue($dateDistribATraiter,$data);
        return $num;
    }

    public function trieData($datafeuille) {
        $datatab = array();
        $data_return=array();
        foreach ($datafeuille as $key => $value) {
            $adress_split=$this->getRue($value['adresse']);
            $datafeuille[$key]['rue'] = $adress_split['voie'];
        }
        $datatab = $this->array_group($datafeuille,"rue");
        $ordr = 0;
        $num_encien="";
        foreach ($datatab as $key => $value) {
            foreach ($value as $key => $v) {
                $adr_split = $this->getRue($v['adresse']);
                $num_rue= $adr_split['numVoie'];
                if($num_encien != $num_rue){
                    $ordr++;
                }
                $v['point_livraison_ordre'] = $ordr;
                $data_return[] = $v;
                $num_encien= $adr_split['numVoie'];
            }
        }
        return $data_return;
    }
    public function getRue($adr) {
        $voie = $adr;
        $regexAvecNumVoie = "/^([0-9]+\s?[a-z]\s|[0-9]+\s)(.+)$/i";
        if(preg_match_all($regexAvecNumVoie, $adr, $aArr))
        {
            $retour['numVoie']= $aArr[1][0];
            $retour['voie'] = $aArr[2][0];
        }
        return $retour;
    }
    function array_group(array $data, $by_column)
    {
        $result = [];
        foreach ($data as $item) {
            $column = $item[$by_column];
            unset($item[$by_column]);
            if (isset($result[$column])) {
                $result[$column][] = $item;
            } else {
                $result[$column] = array($item);
            }
        }
        return $result;
    }
}
