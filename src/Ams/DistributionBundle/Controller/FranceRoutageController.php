<?php

namespace Ams\DistributionBundle\Controller;

use Ams\SilogBundle\Controller\GlobalController;
use Ijanki\Bundle\FtpBundle\Exception\FtpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ams\DistributionBundle\Entity\FranceRoutageTraitement;
use Symfony\Component\HttpFoundation\Session\Session;
use Ams\DistributionBundle\Form\ReportType;
use Ams\DistributionBundle\Form\ParutionSpecialeType;
use Ams\DistributionBundle\Entity\ParutionSpeciale;
use Ams\ExtensionBundle\Validator\Constraints\DatePosterieure;
use Ams\DistributionBundle\Entity\JourFerie;
use Ams\DistributionBundle\Entity\Reperage;
use Ams\DistributionBundle\Form\ReperageType;
use HTML2PDF;
use Symfony\Component\Process\Process;

class FranceRoutageController extends GlobalController {

    CONST USERNAME = 'France-Routage';
    CONST PASS = 'zj6cUaVQ';
    CONST HOST = 'ftp.jade-presse.fr';

    public function indexAction() {
        // verifie si on a droit d'acceder à cette page
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
        try {
            $ftp = $this->container->get('ijanki_ftp');
            $ftp->connect(self::HOST);
            $ftp->login(self::USERNAME, self::PASS);
            $ftp->pasv(true);
            $content = $ftp->nlist('.');
            $data = $this->sortPath($content, $ftp);
            $folder = $this->display($data['folder'], 'img_folder');
            $files = $this->display($data['files'], 'img_file', '/');

            $em = $this->getDoctrine()->getManager();
            $cancelFilesAuto = $em->getRepository('AmsDistributionBundle:FranceRoutageTraitement')->cancelFilesAuto();
            // code societe pour les fichies france routage
            //
//            $societe = array(
//                'CL' => 'Closer',
//                'DF' => 'Detour en France',
//                'GP' => 'Grazia Pocket',
//                'JL' => 'Jalouse',
//                'GR' => 'Grazia',
//                'LO' => 'L\'Optimum',
//                'OA' => 'Officiel Art',
//                'OC' => 'Officiel Couture',
//                'OH' => 'Officiel Homme',
//                'O1' => 'Officiel 1000 Modèles',
//                'RM' => 'Revue des montres',
//                'SM' => 'Santé magazine',
//                'TS' => 'Téléstar',
//                'OV' => 'Officiel voyage',
//                );
            $produit = $em->getRepository('AmsDistributionBundle:FranceRoutageProduit')->findAll();
            return $this->render('AmsDistributionBundle:FranceRoutage:index.html.twig', array('folders' => $folder, 'files' => $files, 'fileAriane' => '/', 'cancelFilesAuto' => $cancelFilesAuto, 'societe' => $produit));
        } catch (FtpException $e) {
            echo 'Error: ', $e->getMessage();
        }
    }

    public function navigationAction(Request $request) {
        try {
            $ftp = $this->container->get('ijanki_ftp');
            $ftp->connect(self::HOST);
            $ftp->login(self::USERNAME, self::PASS);
            $ftp->pasv(true);

            /** TEST SI UN CHEMIN EXISTE* */
            if ($request->get('pathTest') == 'true') {
                $path = $request->get('path') . '/OUT';
                $test = @$ftp->chdir($path);
                if ($test === false)
                    $jsonData = array('breadCrumb' => $request->get('path') . '/');
                else
                    $jsonData = array('breadCrumb' => $path . '/');
                return new Response(json_encode($jsonData), 200, array('Content-Type' => 'application/json'));
            }

            else {
                /** PASSAGE PAR LE FIL D ARIANNE* */
                if ($request->get('byBreadCrumb') == 'true') {
                    $path = str_replace('racine', '', trim($request->get('breadCrumb')));
                }
                /** PASSAGE PAR "CLICK SUR DOSSIER" * */ else {
                    $path = str_replace('racine', '', trim($request->get('breadCrumb')) . '/' . trim($request->get('name')));
                }
                /** RETIRE LE PREMIER SLASH CONFORMEMENT AU PATH ENREGISTRER EN BDD * */
                $path = (substr($path, 0, 1) == '/') ? substr($path, 1, (strlen($path))) : $path;
                $ftp->chdir($path);
                $content = $ftp->nlist($ftp->pwd());
                $data = $this->sortPath($content, $ftp);
                $html = $this->display($data['folder'], 'img_folder');
                $html.= $this->display($data['files'], 'img_file', $path);
                $jsonData = array('list' => $html, 'breadCrumb' => $ftp->pwd());

                return new Response(json_encode($jsonData), 200, array('Content-Type' => 'application/json'));
            }
        } catch (FtpException $e) {
            echo 'Error: ', $e->getMessage();
        }
    }

    public function crudAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        if ($request->get('action') == 'delete_cancel') {
            $oFranceRoutage = $em->getRepository('AmsDistributionBundle:FranceRoutageTraitement')->find($request->get('id'));
            $oFranceRoutage->setRead(1);
            $em->flush();
            exit;
        }

        if ($request->get('action') == 'delete') {
            $oFranceRoutage = $em->getRepository('AmsDistributionBundle:FranceRoutageTraitement')->find($request->get('idFranceRoutage'));
            $oFranceRoutage->setSucces(NULL);
            $oFranceRoutage->setNomFichierGenere(NULL);
            $oFranceRoutage->setDateDebutTraitement(NULL);
            $oFranceRoutage->setDateFinTraitement(NULL);
            $oFranceRoutage->setDateGenerationFranceRoutage(NULL);
            $oFranceRoutage->setDateAnnulation(new \DateTime());
            $em->flush();
            exit;
        }

        /** Traitement possedant le meme nom de fichier* */
        // $aFranceRoutage = $em->getRepository('AmsDistributionBundle:FranceRoutageTraitement')->findBy(array('nomFichier' => $request->get('nom_fichier')));
        // $aFranceRoutage = $em->getRepository('AmsDistributionBundle:FranceRoutageTraitement')->findBy(
        //                                 array('codeSociete' => $request->get('code_societe'),
        //                                       'dateParution' => new \DateTime($this->dateFormatEn($request->get('date_parution')))
        //                                     ));
        // var_dump($aFranceRoutage);
        // exit;
        // exit('lool');
        $dateParution = new \DateTime($this->dateFormatEn($request->get('date_parution')));
        $dirSource = $this->pathComformiteBdd($request->get('repertoire_ftp_source'));
        $dirDestination = $this->pathComformiteBdd($request->get('repertoire_ftp_destination'));
        $oFranceRoutageTraitement = new FranceRoutageTraitement();
        $oFranceRoutageTraitement->setRepertoireFtpSource($dirSource);
        $oFranceRoutageTraitement->setNomFichier($request->get('nom_fichier'));
        $oFranceRoutageTraitement->setCodeSociete($request->get('nom_societe'));
        $oFranceRoutageTraitement->setDateParution($dateParution);
        $oFranceRoutageTraitement->setRepertoireFtpDestination($dirDestination);
        $oFranceRoutageProduit = $em->getRepository('AmsDistributionBundle:FranceRoutageProduit')->findBy(array(
            'code' => $request->get('nom_societe')
        ));
//        var_dump($oFranceRoutageProduit[0]->getFranceRoutageScriptCode());die;
        if ($oFranceRoutageProduit) {

            $oFranceRoutageTraitement->setFranceRoutageScriptCode($oFranceRoutageProduit[0]->getFranceRoutageScriptCode());
        }
        $em->persist($oFranceRoutageTraitement);
        $em->flush();

//        $action = $request->request->get('coche');
//        if($action){ 
//            $script = $em->getRepository('AmsDistributionBundle:FranceRoutageScript')->findBy(array('code'=>$action));
//            if($script[0]){
//                $oFranceRoutageTraitement->setFranceRoutageScriptCode($script[0]->getCode());
//                $em->persist($oFranceRoutageTraitement);
//                $em->flush();
//            }
//        }
        exit;
    }

    public function execAction(Request $request) {

        // Appel des jobs Talend en web service 
        $baseurl = $this->container->getParameter('MROAD_TALEND_BASE_URL');
        $authPass = $this->container->getParameter('MROAD_TALEND_AUTHPASS');
        $authUser = $this->container->getParameter('MROAD_TALEND_AUTHUSER');

        // Appel job plan (ancien puis nouveau script)
        // Vérifions qu'il est prêt à être exécuté 
        $json_config_test_status = '{
                                        "actionName": "getTaskStatus",
                                        "authPass": "' . $authPass . '",
                                        "authUser": "' . $authUser . '",
                                        "taskId": ' . $this->container->getParameter('MROAD_FRANCE_ROUTAGE_ID_PLAN_JOB') . '
                                      }';
        $json_config_test_status_encoded = base64_encode($json_config_test_status);
        $json_result = file_get_contents($baseurl . $json_config_test_status_encoded);
        $result = json_decode($json_result);
        if ($result->status == "READY_TO_RUN") {
            // exécution de l'ancien script
            $json_config_run = '{
                                        "actionName": "runTask",
                                        "authPass": "' . $authPass . '",
                                        "authUser": "' . $authUser . '",
                                        "mode": "asynchronous",
                                        "taskId": ' . $this->container->getParameter('MROAD_FRANCE_ROUTAGE_ID_PLAN_JOB') . '
                                      }';
            $json_config_run_encoded = base64_encode($json_config_run);
            $json_result = file_get_contents($baseurl . $json_config_run_encoded);
        }

        exit();
    }

    public function listFileAction() {
        $em = $this->getDoctrine()->getManager();
        $waitingFiles = $em->getRepository('AmsDistributionBundle:FranceRoutageTraitement')->waitingFiles();
        $html = '
            <table class="table table-striped table-condensed">
                <tr>
                    <th> Repertoire Source</th>
                    <th> Nom  Fichier</th>
                    <th> Code Societe</th>
                    <th> Repertoire Destination</th>
                    <th> Etat</th>
                    <th> Action(s) </th>
                </tr>';
        $actionActivate = true;
        foreach ($waitingFiles as $files) {
            if (($files['date_debut_traitement'] != ''))
                $actionActivate = false;
            $state = ($files['date_debut_traitement'] != '') ? 'En cours' : 'Attente';
            $html.='<tr>
                        <td>' . $files['repertoire_ftp_source'] . '</td>
                        <td>' . $files['nom_fichier'] . '</td>
                        <td>' . $files['code_societe'] . '</td>
                        <td>' . $files['repertoire_ftp_destination'] . '</td>
                        <td>' . $state . '</td>';
            $html.=($files['date_annulation'] != '') ? '<td> <strong style="color:red"> Annulé </strong> </td>' : '<td> <input id="' . $files['id'] . '_france_routage" type="button" value="Annuler" name="cancel" class="btn btn-danger btn-xs"/></td>';
            $html.='</tr>';
        }
        $html.='</table> <br /> ';
        if ($actionActivate)
            $html.='<button id="launch_script" class="btn btn-danger btn-xs"> Lancer le script</button> ';
        $html.='<button class="btn close-modal btn-xs"> Annuler</button>';
        exit($html);
    }

    private function dateFormatEn($date) {
        $tmp = explode('/', $date);
        return $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0];
    }

    private function sortPath($content, $oFtp) {
        $aDirectory = $aFiles = array();
        $nombreFichier = 0;
        foreach ($content as $docName) {
            if ($oFtp->size($docName) == '-1') {
                $aDirectory[] = utf8_encode($docName);
            } else {
                if (isset($aFiles[$oFtp->mdtm($docName)])) {
                    $aFiles[$oFtp->mdtm($docName)] .= "|" . utf8_encode($docName);
                } else {
                    $aFiles[$oFtp->mdtm($docName)] = utf8_encode($docName);
                }
            }
            $nombreFichier ++;
        }
        krsort($aFiles);
        return array('folder' => $aDirectory, 'files' => $aFiles);
    }

    private function pathComformiteBdd($path) {
        $sPath = (substr($path, 0, 1) == '/') ? substr($path, 1, (strlen($path))) : $path;
        $sPath = (substr($sPath, (strlen($sPath) - 1), 1) == '/') ? $sPath : $sPath . '/';
        return $sPath;
    }

    private function display($aData, $className, $pathFile = false) {
        $html = '';
        if ($pathFile !== false) {
            $pathFile = $this->pathComformiteBdd($pathFile);
            $aFranceRoutageTraitement = $this->franceRoutageTraitement();
            foreach ($aData as $date => $d) {
                $datas = explode("|", $d);
                sort($datas);
                foreach ($datas as $data) {
                    if (array_key_exists($pathFile, $aFranceRoutageTraitement)) {
                        if (array_key_exists($data, $aFranceRoutageTraitement[$pathFile])) {
                            if (!$this->isCancelTreatment($data)) {
                                $class = (is_null($aFranceRoutageTraitement[$pathFile][$data])) ? 'attente' : 'present';
                                $html.='<li class="' . $className . '"> <span class="img"></span> <span class="element">' . $data . '</span> <span class="' . $class . '"> </span> <span class="date"> ' . date('d/m/Y H:i:s', $date) . '</span>  </li>';
                            } else
                                $html.='<li class="' . $className . '"> <span class="img"></span> <span class="element">' . $data . '</span>  <span class="absent"> </span> <span class="date"> ' . date('d/m/Y H:i:s', $date) . '</span> </li>';
                            continue;
                        }
                    }
                    $html.='<li class="' . $className . '"> <span class="img"></span> <span class="element">' . $data . '</span>  <span class="absent"> </span> <span class="date"> ' . date('d/m/Y H:i:s', $date) . '</span> </li>';
                }
            }
        }
        else {
            foreach ($aData as $data) {
                $html.='<li class="' . $className . '"> <span class="element">' . $data . '</span> <span class="img"></span></li>';
            }
        }
        return $html;
    }

    private function isCancelTreatment($nomFichier) {
        $em = $this->getDoctrine()->getManager();
        $aFranceRoutage = $em->getRepository('AmsDistributionBundle:FranceRoutageTraitement')->findBynomFichier($nomFichier);
        foreach ($aFranceRoutage as $oFranceRoutage) {
            if ($oFranceRoutage->getDateAnnulation() === null)
                return false;
        }
        return true;
    }

    private function franceRoutageTraitement() {
        $em = $this->getDoctrine()->getManager();
        /** PRENDRE QUE LES ACTIFS * */
        $aFranceRoutageTraitements = $em->getRepository('AmsDistributionBundle:FranceRoutageTraitement')->activeFilesAuto();
        $aDistinctPath = array();
        foreach ($aFranceRoutageTraitements as $aFranceRoutageTraitement) {
            $nameFile = trim($aFranceRoutageTraitement['nom_fichier']);
            $namePath = trim($aFranceRoutageTraitement['repertoire_ftp_source']);
            if (!array_key_exists($namePath, $aDistinctPath)) {
                $aDistinctPath[$namePath][] = '';
            }
            if (!array_key_exists($nameFile, $aDistinctPath[$namePath])) {
                $aDistinctPath[$namePath][$nameFile] = $aFranceRoutageTraitement['date_debut_traitement'];
            }
        }
        return $aDistinctPath;
    }

}