<?php

namespace Ams\DistributionBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Ams\SilogBundle\Controller\GlobalController;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Ams\ExtensionBundle\Validator\Constraints\DatePosterieure;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Border;
use PHPExcel_Style_Fill;
use PHPExcel_IOFactory;
use PHPExcel_Style_NumberFormat;

class ArbitrageController extends GlobalController {

    public $reclamations;

    public function gridAction(Request $request) {
        $depots = $this->get('session')->get('DEPOTS');
        $sDepots = implode(',', array_keys($depots));
        $isModif = $this->isPageElement('MODIF', 'arbitrage_vue_generale');
        $dateRange = false;

        if ($request->query->get('dateRange')) {
            $dateRange = $request->cookies->get('minDateArbitrage') . "_" . $request->cookies->get('maxDateArbitrage');
        }
        $reclamation = $this->getDoctrine()
                ->getManager()
                ->getRepository('AmsDistributionBundle:CrmDetail')
                ->fetchAllReclamation($sDepots, $this->getLastPayDate(), $dateRange);
        $session = new Session();
        $session->set('arbitrageExport', serialize($reclamation));

        $imputation = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('AmsDistributionBundle:ImputationService')->findBy(array('etat' => 1));

        $motif = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('AmsDistributionBundle:DemandeArbitrage')->findBy(array('etat' => 1));


        $ipp = '
			<option value="0">Non</option>
			<option value="1">Oui</option>';
        $demandeStatus = '<option value="0">Non</option>
			<option value="1">Oui</option>';

        $fDate = '
			<option value="0">Date not null</option>
			';
        $StringMotif = '';
        foreach ($motif as $m) {
            $StringMotif .='<option value="' . $m->getId() . '"> ' . $m->getLibelle() . '</option>';
        }

        $StringImputation = '';
        foreach ($imputation as $imp) {
            $StringImputation .='<option value="' . $imp->getId() . '"> ' . $imp->getLibelle() . '</option>';
        }

        $response = $this->renderView('AmsDistributionBundle:Arbitrage:grid.xml.twig', array(
            'isModif' => $isModif,
            'reclamation' => $reclamation,
            'imputation' => $StringImputation,
            'motif' => $StringMotif,
            'ipp' => $ipp,
            'fDate' => $fDate,
            'demandeStatus' => $demandeStatus,
        ));

        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }

    public function excelExportAction() {
        $session = new Session();
        $reclamations = unserialize($session->get('arbitrageExport'));

        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
        $phpExcelObject->createSheet(0);
        $phpExcelObject->setActiveSheetIndex(0);
        $phpExcelObject->getActiveSheet()->setTitle('Arbitrage');
        $this->getHeaderXls($phpExcelObject);
        $phpExcelObject->getActiveSheet()->getStyle("A1:N1")->applyFromArray($this->getStyle('styleHeader'));
        $iLine = 2;
        foreach ($reclamations as $oReclam) {
            $treat = ($oReclam->getDateReponseArbitrage()) ? 'Oui' : 'Non';
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue('A' . $iLine, $treat);
            if ($oReclam->getDateDemandeArbitrage())
                $phpExcelObject->setActiveSheetIndex(0)->setCellValue('B' . $iLine, $oReclam->getDateDemandeArbitrage()->format('d/m/Y'));
            if ($oReclam->getDateCreat())
                $phpExcelObject->setActiveSheetIndex(0)->setCellValue('C' . $iLine, $oReclam->getDateCreat()->format('d/m/Y'));
            if ($oReclam->getDepot())
                $phpExcelObject->setActiveSheetIndex(0)->setCellValue('D' . $iLine, $oReclam->getDepot()->getLibelle());
            if ($oReclam->getSociete())
                $phpExcelObject->setActiveSheetIndex(0)->setCellValue('E' . $iLine, $oReclam->getSociete()->getLibelle());
            if ($oReclam->getTourneeJour()) {
                $objTournee = $this->getDoctrine()->getManager()->getRepository('AmsModeleBundle:ModeleTourneeJour')->find($oReclam->getTourneeJour()->getId());
                $phpExcelObject->setActiveSheetIndex(0)->setCellValue('F' . $iLine, $objTournee->getCode());
            }
            if ($oReclam->getCrmDemande())
                $phpExcelObject->setActiveSheetIndex(0)->setCellValue('G' . $iLine, $oReclam->getCrmDemande()->getLibelle());
            if ($oReclam->getNumaboExt())
                $phpExcelObject->setActiveSheetIndex(0)->setCellValue('H' . $iLine, $oReclam->getNumaboExt());
            if ($oReclam->getVille())
                $phpExcelObject->setActiveSheetIndex(0)->setCellValue('I' . $iLine, $oReclam->getVille());
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue('J' . $iLine, ($oReclam->getIpp()) ? 'Oui' : 'Non');
            if ($oReclam->getMotif()) {
                $objMotif = $this->getDoctrine()->getManager()->getRepository('AmsDistributionBundle:DemandeArbitrage')->find($oReclam->getMotif()->getId());
                $phpExcelObject->setActiveSheetIndex(0)->setCellValue('K' . $iLine, $objMotif->getLibelle());
            }
            if ($oReclam->getImputation()) {
                $objIpp = $this->getDoctrine()->getManager()->getRepository('AmsDistributionBundle:ImputationService')->find($oReclam->getImputation()->getId());
                $phpExcelObject->setActiveSheetIndex(0)->setCellValue('L' . $iLine, $objIpp->getLibelle());
            }
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue('M' . $iLine, $oReclam->getCmtReponseArbitrage());
            if ($oReclam->getDateReponseArbitrage())
                $phpExcelObject->setActiveSheetIndex(0)->setCellValue('N' . $iLine, $oReclam->getDateReponseArbitrage()->format('d/m/Y'));

            if (($iLine % 2) == 0)
                $phpExcelObject->getActiveSheet()->getStyle("A$iLine:N$iLine")->applyFromArray($this->getStyle('styleOddRows'));
            else
                $phpExcelObject->getActiveSheet()->getStyle("A$iLine:N$iLine")->applyFromArray($this->getStyle('styleEventRows'));

            $iLine++;
        }

        $nbReclam = count($reclamations) + 1;
        $phpExcelObject->getActiveSheet()->getDefaultColumnDimension()->setWidth(17);
        $phpExcelObject->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $phpExcelObject->getActiveSheet()->getColumnDimension('G')->setWidth(30);
        $phpExcelObject->getActiveSheet()->getColumnDimension('J')->setWidth(20);
        $phpExcelObject->getActiveSheet()->getColumnDimension('N')->setWidth(20);
        // $phpExcelObject->getActiveSheet()->getStyle('H')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $phpExcelObject->getActiveSheet()->getStyle("A1:N$nbReclam")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $this->export($phpExcelObject);
    }

    function getHeaderXls($phpExcelObject) {
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue('A1', 'Demande traitée');
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue('B1', 'Date demande arbitrage');
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue('C1', 'date réclamation');
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue('D1', 'Centre de distribution');
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue('E1', 'Société');
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue('F1', 'N°Tournée');
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue('G1', 'Réclamation');
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue('H1', 'N°Abonné');
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue('I1', 'Ville');
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue('J1', 'Réponse réclamation');
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue('K1', 'Motif');
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue('L1', 'Imputation');
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue('M1', 'Commentaire');
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue('N1', 'Date réponse arbitrage');
    }

    public function getStyle($item) {
        if ($item == 'mergedVertical')
            return array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,),
            ));

        if ($item == 'styleEventRows')
            return array(
                'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'font' => array('size' => 10), 'color' => array('argb' => 'FFE3EFFF'),
                    'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP,),
            ));
        if ($item == 'styleOddRows')
            return array(
                'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'font' => array('size' => 10), 'color' => array('argb' => 'FFFFFFFF'),
                    'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP,),
            ));
        if ($item == 'styleArray')
            return array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN,),),
                'font' => array('bold' => false, 'size' => 10,));
        if ($item == 'styleHeader')
            return array('font' => array('bold' => true, 'size' => 10),
                'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,),
                'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('argb' => 'FFE1E6FA'),),
            );
        if ($item == 'styleCell')
            return array('font' => array('bold' => false, 'size' => 10,),
                'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('argb' => 'FFC4D7ED'),),);
    }

    function export($phpExcelObject) {
        $response = new Response();
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename="arbitrage.xls"');
        $response->headers->set('Cache-Control', 'max-age=0');
        $response->sendHeaders();
        $objWriter = PHPExcel_IOFactory::createWriter($phpExcelObject, 'Excel5');
        $objWriter->save('php://output');
    }

    public function listeAction() {

        // verifie si on a droit d'acceder à cette page
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }

        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AmsSilogBundle:Utilisateur')->find($session->get('UTILISATEUR_ID'));
        $request = $this->getRequest();
        $isModif = $this->isPageElement('MODIF', 'arbitrage_vue_generale');

        if ($request->getMethod() == 'POST') {

            /** UPDATE MULTIPLE ARBITRAGE * */
            if ($request->request->get('filter')) {
                $idArbitrageTab = explode('_', $request->request->get('idArbitrageTab'));
                if (count($idArbitrageTab) >= 300) {

                    $this->get('session')->getFlashBag()->add(
                            'arbitrage_limite', 'Vous pouvez enregistrer "en masse" qu\'un maximum de 300 résultats'
                    );
                    return $this->redirect($this->generateUrl('arbitrage_vue_generale'));
                }
                foreach ($idArbitrageTab as $idArbitrage) {
                    if (!empty($idArbitrage)) {
                        $result = $em->getRepository('AmsDistributionBundle:CrmDetail')->find($idArbitrage);
                        $motif = $em->getRepository('AmsDistributionBundle:DemandeArbitrage')->find($request->request->get('form')['motif']);
                        $imputation = $em->getRepository('AmsDistributionBundle:ImputationService')->find($request->request->get('form')['imputation']);
                        $ipp = '';
                        if ($request->request->get('reponse') == 2)
                            $ipp = 0;
                        else
                            $ipp = ($request->request->get('reponse') == 1) ? 1 : null;

                        $result->setMotif($motif);
                        $result->setImputation($imputation);
                        $result->setIpp($ipp);
                        $result->setCmtReponseArbitrage($request->request->get('form')['cmtReponseArbitrage']);
                        $result->setDateReponseArbitrage(new \DateTime());
                        $result->setUtlReponseArbitrage($user);
                        $em->flush();
                    }
                }
                return $this->redirect($request->headers->get('referer'));
            }else {
                $idArbitrageTab = explode('_', $request->request->get('idArbitrageTab'));
                if (count($idArbitrageTab) >= 300) {
                    $this->get('session')->getFlashBag()->add('arbitrage_limite', 'Vous pouvez mettre à jour la date de réponse arbitrage que pour \'un maximum de 300 résultats');
                    return $this->redirect($this->generateUrl('arbitrage_vue_generale'));
                }
                foreach ($idArbitrageTab as $idArbitrage) {
                    if (!empty($idArbitrage)) {
                        $result = $em->getRepository('AmsDistributionBundle:CrmDetail')->find($idArbitrage);
                        $result->setDateReponseArbitrage(new \DateTime());
                        $result->setUtlReponseArbitrage($user);
                        $em->flush();
                    }
                }
                $return = array('msg' => 'success');
                $return['responseCode'] = 200;

                return new Response(json_encode($return), $return['responseCode'], array('Content-Type' => 'application/json'));
            }
        }

        /** DEFINITION DES DATES MAX,MIN D'ARBITRAGE * */
        $cookie = $this->getRequest()->cookies;
        $DateDemArbitrageMax = ($cookie->get('dateRange') == 0) ? date('d/m/Y') : $cookie->get('maxDateArbitrage');
        $DateDemArbitrageMin = ($cookie->get('dateRange') == 0) ? $this->getDateFormatFr($this->getLastPayDate()) : $cookie->get('minDateArbitrage');
        $session->set('DateDemArbitrageMax', $DateDemArbitrageMax);
        $session->set('DateDemArbitrageMin', $DateDemArbitrageMin);


        $disabled = false;
        if (!$DateDemArbitrageMax)
            $disabled = true;

        $form = $this->createFormBuilder()
                ->add('DateDemArbitrageMin', 'text', array('required' => true, 'label' => 'Date début arbitrage', 'disabled' => $disabled))
                ->add('DateDemArbitrageMax', 'text', array('required' => true, 'label' => 'Date fin arbitrage', 'disabled' => $disabled))
                ->getForm();

        $formFilter = $this->createFormBuilder()
                ->add('DateBeginReclam', 'text', array('required' => false, 'label' => 'Date début réclamation'))
                ->add('DateEndReclam', 'text', array('required' => true, 'label' => 'Date fin réclamation'))
                ->add('motif', 'entity', array('class' => 'AmsDistributionBundle:DemandeArbitrage', 'property' => 'libelle', 'empty_value' => ' -- Choisissez --', 'required' => false))
                ->add('imputation', 'entity', array('class' => 'AmsDistributionBundle:ImputationService', 'property' => 'libelle', 'empty_value' => ' -- Choisissez --', 'required' => false))
                ->add('cmtReponseArbitrage', 'textarea', array('required' => false, 'label' => 'Commentaire'))
                ->getForm();

        return $this->render('AmsDistributionBundle:Arbitrage:liste.html.twig', array(
                    'isModif' => $isModif,
                    'form' => $form->createView(),
                    'formFilter' => $formFilter->createView(),
                    'dateRange' => $this->getRequest()->cookies->get('dateRange'),
        ));
    }

    /**
     * [dataProcessorAction description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function dataProcessorAction(Request $request) {

        if (!$this->verif_acces())
            return false;
        $this->setDerniere_page();
        $em = $this->getDoctrine()->getManager();
        $mode = $request->get('!nativeeditor_status');
        $rowId = $request->get('gr_id');
        $newId = '';
        $action = '';
        $msg = '';
        $msgException = '';
        $result = true;

        $session = new Session();
        $session->get('UTILISATEUR_ID');

        if ($mode == 'updated') {

            $user = $em->getRepository('AmsSilogBundle:Utilisateur')->find($session->get('UTILISATEUR_ID'));
            $motif = (is_numeric($request->get('c2'))) ? $em->getRepository('AmsDistributionBundle:DemandeArbitrage')->find($request->get('c2')) : $em->getRepository('AmsDistributionBundle:DemandeArbitrage')->findOneByLibelle(trim($request->get('c2')));

            $imputation = (is_numeric($request->get('c3'))) ? $em->getRepository('AmsDistributionBundle:ImputationService')->find($request->get('c3')) : $em->getRepository('AmsDistributionBundle:ImputationService')->findOneByLibelle(trim($request->get('c3')));


            if (trim($request->get('c5')) == trim('N/A'))
                $ipp = null;
            else
                $ipp = (trim($request->get('c5')) == trim('Oui') || $request->get('c5') == 1 ) ? 1 : 0;

            $action = 'update';
            $result = $em->getRepository('AmsDistributionBundle:CrmDetail')->find($request->get('c0'));
            $result->setMotif($motif);
            $result->setImputation($imputation);
            $result->setIpp($ipp);
            $result->setCmtReponseArbitrage($request->get('c4'));
            $result->setDateReponseArbitrage(new \DateTime());
            $result->setUtlReponseArbitrage($user);
            $em->flush();
        }
        if (!$result) {
            $action = "error";
            $response = $this->render('::grid_action_error.html.twig', array('action' => $action, 'rowId' => $rowId, 'newId' => $newId, 'msg' => $msg, 'msg_complet' => $msgException));
        } else
            $response = $this->render('::grid_action.html.twig', array('action' => $action, 'rowId' => $rowId, 'newId' => $newId, 'msg' => $msg));

        $response->headers->set('Content-Type', 'text/xml');
        return $response;
    }

    private function getLastPayDate() {
        $dLastMonth = date('Y-m-d', strtotime("-1 month", strtotime(date('Y-m-d'))));
        $aLastMonth = explode('-', $dLastMonth);
        return $aLastMonth[0] . '-' . $aLastMonth[1] . '-21';
    }

    private function getDateFormatFr($date) {
        $aDate = explode('-', $date);
        return $aDate[2] . '/' . $aDate[1] . '/' . $aDate[0];
    }

}
