<?php

namespace Ams\AbonneBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Ams\SilogBundle\Controller\GlobalController;
use Symfony\Component\HttpFoundation\Response;
use Ams\AbonneBundle\Form\AbonneSocFormType;
use Ams\AbonneBundle\Entity\AbonneSoc;
use PHPExcel_Worksheet_PageSetup;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Border;
use PHPExcel_Style_Fill;
use PHPExcel_Style_NumberFormat;
use PHPExcel_Cell_DataType;

/**
 * Gestion des abonnés
 *
 */
class AbonneSocController extends GlobalController {

    /**
     * recherche par abonne
     */
    public function rechercheAbonneAction(Request $request) {

        // Liste des depôts accessible à l'utilisateur
        $listeAbonnes = array();
        $em = $this->getDoctrine()->getManager();
        $depots = $this->get("session")->get("DEPOTS");
        $form = $this->createForm(new \Ams\AbonneBundle\Form\AbonneSocType($depots));
        $filename = '';
        $aParam = array();
        $dateDistrib = date('d/m/Y');
        // on affiche la liste des abonnées
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $aData = $request->request->get('form');
            $aParam = array(
                'depotId' => $aData['depot'],
                'fluxId' => $aData['flux_id'],
                'societeId' => $aData['societe'],
                'numaboExt' => $aData['numaboExt'],
                'vol1' => $aData['vol1'],
                'vol2' => $aData['vol2'],
                'vol4' => $aData['vol4'],
                'communeId' => $aData['ville'],
            );
            $listeAbonnes = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->rechercheAbonne($aParam);
            if (!$listeAbonnes){
                $this->get('session')->getFlashBag()->add(
                        'recherche_abonne_vide', 'Aucun résultat disponible.'
                );
            }
            /** GENERATION FEUILLE EXCEL * */
            $filename = $this->getExcel($listeAbonnes);

            if ($request->isXmlHttpRequest()) {
                return $this->render('AmsAbonneBundle:AbonneSoc:liste_abonne.html.twig', array(
                            'listeAbonnes' => $listeAbonnes,
                ));
            }
        }
        

        return $this->render('AmsAbonneBundle:AbonneSoc:recherche_abonne.html.twig', array(
                    'form' => $form->createView(),
                    'listeAbonnes' => $listeAbonnes,
                    'file' => $filename,
                    'dateDistrib' => $dateDistrib,
                    'aParam' => json_encode($aParam)
        ));
    }

    /**
     * Fiche du client
     */
    public function ficheAbonneAction($id) {
        $em = $this->getDoctrine()->getManager();
        $abonne = $em->getRepository('AmsAbonneBundle:AbonneSoc')->find($id);
        $casl= $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->findBy(array('abonneSoc'=>$abonne));
        foreach($casl as $val){
            $ab_casl = $val;
            //break;
        }
        if (!$abonne) {
            $this->get('session')->getFlashBag()->add('fiche_abonne', "Cet abonné n'existe pas!");
            return $this->redirect($this->generateUrl('recherche_abonne'));
        }
        //infoportage du point de livraion
        $infoLivraisons = $em->getRepository('AmsDistributionBundle:InfoPortage')->getInfoPortageLivraisonByAboSoc($abonne->getId());
        
        $oAdresse = $em->getRepository('AmsAdresseBundle:Adresse')->getSingleAdressByAboSoc($abonne);
        if (!$oAdresse) {
            $this->get('session')->getFlashBag()->add('fiche_abonne', "Cet abonné n'existe pas!");
            return $this->redirect($this->generateUrl('recherche_abonne'));
        }
            $aTourneeDetail = $em->getRepository('AmsAdresseBundle:TourneeDetail')->getDataByAbonneSoc($abonne->getId());
        
        
        $depots = $this->get('session')->get('DEPOTS');
        $selectDepot = '<select name="depots">';
        foreach ($depots as $key => $depot) {
            $selectDepot .= '<option value="' . $key . '">' . $depot . '</option>';
        }
        $selectDepot .= '</select>';


        return $this->render('AmsAbonneBundle:AbonneSoc:fiche_abonne.html.twig', array(
                    'abonne' => $abonne,
                    'infoLivraisons' => $infoLivraisons,
                    'aTourneeDetail' => $aTourneeDetail,
                    'selectDepot' => $selectDepot,
                    'adresse' => $oAdresse,
                    'ab_casl' => $ab_casl
        ));
    }

    public function changeTourneeAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $aData = $request->get('data');
        $geoservice = $this->container->get('ams_carto.geoservice');
        $aPointsAClasser = array();
        foreach ($aData as $data) {
            $getAbonne = $em->getRepository('AmsAdresseBundle:TourneeDetail')->getAbonneJourId($data['abonneId'], $data['jourId']);
            $sourceModification = 'Interface abonne [utilisateurId => ' . $this->get('session')->get('UTILISATEUR_ID') . ']';
            if ($getAbonne['modele_tournee_jour_code'] != trim($data['tourneeCode'])) {
                $aParam = array(
                    'jour_id' => $data['jourId'],
                    'flux_id' => $data['flux'],
                    'tournee_jour_code' => trim($data['tourneeCode']),
                    'sourceModification' => $sourceModification,
                );
                $aPointsAClasser[] = $em->getRepository('AmsAbonneBundle:AbonneSoc')->classementAutoByAbonneSocId($data['abonneId'], $aParam);
            }
        }
        if (!empty($aPointsAClasser)) {
            $geoservice = $this->container->get('ams_carto.geoservice');
            $geoservice->classementAuto($aPointsAClasser);
        }
        exit;
    }

    public function xmlhttprequestAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $depotId = $request->get('depotId');
        $fluxId = $request->get('fluxId');
        $jourId = $request->get('jourId');

        if ($request->get('detail') == 'true') {
            /** RECUPERATION DES MTJ (jourId,depotId,fluxId) * */
            $aModele = $em->getRepository('AmsModeleBundle:ModeleTourneeJour')->getTourneeByDepotFlux($depotId, $fluxId, $jourId);
            $selectMTJ = '';
            // $selectMTJ = '<option value=""> Choisissez une tournee </option>';
            foreach ($aModele as $aMod)
                $selectMTJ.= '<option value="' . $aMod['id'] . '"> ' . $aMod['code'] . ' </option>';

            $response = json_encode(array('selectMTJ' => $selectMTJ));
        } else {
            /** RECUPERATION DES VILLES,CP DANS CASL (depotId,depotId,dateDistrib) * */
            $aCity = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getCityByDateFluxDepot($depotId, $fluxId);
            $selectCity = '<option value=""> Choisissez une ville </option>';
            foreach ($aCity as $aC)
                $selectCity.= '<option value="' . $aC['commune_id'] . '"> ' . $aC['cp'] . ' - ' . $aC['libelle'] . ' </option>';

            $response = json_encode(array('selectCity' => $selectCity));
        }
        return new Response($response, 200, array('Content-Type' => 'application/json'));
    }

    private function getExcel($listeAbonnes) {

        if (!$this->container->hasParameter("PARAM_LOCAL")) {
            $this->dumpTmpFile();
        }

        $lineExcel = 2;
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        $even = $this->getStyle('styleEventRows');
        $odd = $this->getStyle('styleOddRows');
        $styleArray = $this->getStyle('styleEventRows');
        $phpExcelObject->getActiveSheet()->getStyle("A1:H1")->applyFromArray($styleArray);

        /** ENTETE * */
        $cLetterEnd = 'H';
        $aSize = array('A' => 15, 'B' => 30, 'C' => 20, 'D' => 20, 'E' => 20, 'F' => 60, 'G' => 60, 'H' => 20);
        $aHeaderTitle = array('A' => 'N° abonné', 'B' => 'Nom Prénom', 'C' => 'Rais. Sociale', 'D' => 'Depôt', 'E' => 'Société', 'F' => 'Adresse de l\'abonné', 'G' => 'Adresse du point de livraison', 'H' => 'Début');
        for ($cLetter = 'A'; $cLetter <= $cLetterEnd; $cLetter++) {
            $phpExcelObject->getActiveSheet()->getColumnDimension($cLetter)->setWidth($aSize[$cLetter]);
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($cLetter . '1', $aHeaderTitle[$cLetter]);
        }
        $phpExcelObject->getActiveSheet()->getStyle("A1:H1")->applyFromArray($this->getStyle('styleHeader'));


        foreach ($listeAbonnes as $abonne) {
            $phpExcelObject->setActiveSheetIndex(0)
                    ->setCellValue('A' . $lineExcel, $abonne['abonne_id'])
                    ->setCellValue('B' . $lineExcel, $abonne['vol1'])
                    ->setCellValue('C' . $lineExcel, $abonne['vol2'])
                    ->setCellValue('D' . $lineExcel, $abonne['depotLibelle'])
                    ->setCellValue('E' . $lineExcel, $abonne['socLibelle'])
                    ->setCellValue('F' . $lineExcel, $abonne['vol4'] . ' ' . $abonne['cp'] . ' ' . $abonne['ville'])
                    ->setCellValue('G' . $lineExcel, $abonne['pointLivraisonAdresse'] . ' ' . $abonne['pointLivraisonCp'] . ' ' . $abonne['pointLivraisonVille'])
                    ->setCellValue('H' . $lineExcel, $abonne['date_debut'])
            ;
            $style = (($lineExcel % 2) == 0) ? $odd : $even;
            $phpExcelObject->getActiveSheet()->getStyle("A$lineExcel:H$lineExcel")->applyFromArray($style);
            $lineExcel++;
        }

        $phpExcelObject->getActiveSheet()->setTitle('Recherche abonné');
        $filename = 'tmp/recherche-abonne_' . uniqid() . '.xls';
        $writer->save($filename);
        return $filename;
    }

    public function getStyle($item) {

        if ($item == 'styleEventRows')
            return array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('argb' => 'FFE3EFFF'),
                    'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP,),
            ));
        if ($item == 'styleOddRows')
            return array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('argb' => 'FFFFFFFF'),
                    'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP,),
            ));

        if ($item == 'styleArray')
            return array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN,),),);
        if ($item == 'styleHeader')
            return array('font' => array('bold' => true, 'size' => 10),
                'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,),
                'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('argb' => 'FFE1E6FA'),),
            );
        if ($item == 'styleCell')
            return array('font' => array('bold' => false, 'size' => 13,),
                'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('argb' => 'FFC4D7ED'),),);
    }

    /** EFFACE LES FICHIERS SUPERIEUR A 1 HEURE* */
    function dumpTmpFile() {
        if ($handle = opendir('tmp')) {
            while (false !== ($file = readdir($handle))) {
                if (!preg_match('/^recherche-abonne*/', $file))
                    continue;
                if ((time() - filemtime('tmp/' . $file)) > 3600)
                    unlink('tmp/' . $file);
            }
        }
        closedir($handle);
    }

}
