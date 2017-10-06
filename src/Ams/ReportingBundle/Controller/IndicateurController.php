<?php

namespace Ams\ReportingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Ams\SilogBundle\Controller\GlobalController;
use Ams\ReportingBundle\Form\FormIndicateurType;
use DateTime;
use DateInterval;
use PHPExcel_Worksheet_PageSetup;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Border;
use PHPExcel_Style_Fill;
use PHPExcel_Worksheet;

class IndicateurController extends GlobalController {

    public function IndicateurAction(Request $request) {
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
        $em = $this->getDoctrine()->getManager();
        $session = $this->get('session');
        $user = $em->getRepository('AmsSilogBundle:Utilisateur')->findOneById($session->get('UTILISATEUR_ID'));
        $depots = $user->getGrpdepot()->getDepots();
        
        $depotId = $request->request->get('indicateur_form')['depot'];
        $depot = $em->getRepository('AmsSilogBundle:Depot')->findOneBy(array('id' => $depotId));

        $date_debut = date("Y-m-d", strtotime(date('m').'/01/'.date('Y').' 00:00:00'));
        $date_fin = date("Y-m-d", strtotime('-1 second',strtotime('+1 month',strtotime(date('m').'/01/'.date('Y').' 00:00:00'))));
        $date_debut = new DateTime($date_debut);
        $date_fin = new DateTime($date_fin);

        $form = $this->createForm(new FormIndicateurType($date_debut, $date_fin, $depotId, $depots));
        if ($request->getMethod() == 'POST') {  
            $sociteId = $request->request->get('indicateur_form')['societe'];
            $dateDebut = $this->transformDateToDataBaseFormat($request->request->get('indicateur_form')['dateDebut'], '/', '-');
            $dateFin = $this->transformDateToDataBaseFormat($request->request->get('indicateur_form')['dateFin'], '/', '-');
            $depotId = $request->request->get('indicateur_form')['depot'];
            if (isset($request->request->get('indicateur_form')['reclamation'])) {
                $reclamations = $this->getReclamation($sociteId, $dateDebut, $dateFin, $depotId);
                $filename = "indicateur_reclamation";
                $writer = $this->indicateurExcel($reclamations['titres'],$reclamations['indicateurReclam'], $reclamations['totaux'],  $filename);
                
            }
            if (isset($request->request->get('indicateur_form')['reperage'])) {
                $reperages = $this->getReperage($sociteId, $dateDebut, $dateFin, $depotId);
                $filename = "indicateur_reperage";
                $writer = $this->indicateurExcel($reperages['titres'],$reperages['indicateurReperage'], $reperages['totaux'],  $filename);
            }
            
            $response = $this->get('phpexcel')->createStreamedResponse($writer);
            $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
            $response->headers->set('Content-Disposition', 'attachment;filename='.$filename.'_'.date_format($date_debut,'Ymd').'_'.date_format($date_fin,'Ymd').'.xls');
            $response->headers->set('Pragma', 'public');
            $response->headers->set('Cache-Control', 'maxage=1');
            return $response;   
        }
        
        return $this->render('AmsReportingBundle:Indicateur:indicateur.html.twig', array('depot' => $depot, 'form' => $form->createView()));
    }


    public function getReclamation($sociteId, $dateDebut, $dateFin, $depotId) {
        $em = $this->getDoctrine()->getManager();
        $reclamations = $em->getRepository('AmsDistributionBundle:CrmDetail')->getIndicateurReclamation($sociteId, $dateDebut, $dateFin, $depotId);
        $reclamJours = array();
        $titres = array('J_J2' => 'J à J+2', 'J3_J4' => 'J+3 à J+4', 'J5_J6' => 'J+5 à J+6', 'J7_' => ' > J+7');
        for ($i = strtotime($dateDebut); $i < strtotime($dateFin); $i = strtotime(date('Y-m-d', $i) . ' +1 day')) {
            $jour = date('Y-m-d', $i);
            foreach ($reclamations as $reclamation) {
                if ($reclamation['date_creation'] == $jour) {
                    if ($reclamation['delai'] == 'J_J2')
                        $reclamJours[$jour]['J_J2'] = $reclamation['nb_reclam'];
                    if ($reclamation['delai'] == 'J3_J4')
                        $reclamJours[$jour]['J3_J4'] = $reclamation['nb_reclam'];
                    if ($reclamation['delai'] == 'J5_J6')
                        $reclamJours[$jour]['J5_J6'] = $reclamation['nb_reclam'];
                    if ($reclamation['delai'] == 'J7_')
                        $reclamJours[$jour]['J7_'] = $reclamation['nb_reclam'];
                    if ($reclamation['delai'] == 'NON_REPONDU')
                        $reclamJours[$jour]['NON_REPONDU'] = $reclamation['nb_reclam'];
                }
            }
        }

        $indicateurReclam = array();
        foreach ($reclamJours as $key => $reclamJour) {
            $indicateurReclam[$key]['DATE'] = $this->transformDateToDataBaseFormat($key, '-', '/');
            $indicateurReclam[$key]['J_J2'] = key_exists('J_J2', $reclamJour) ? $reclamJour['J_J2'] : 0;
            $indicateurReclam[$key]['J3_J4'] = key_exists('J3_J4', $reclamJour) ? $reclamJour['J3_J4'] : 0;
            $indicateurReclam[$key]['J5_J6'] = key_exists('J5_J6', $reclamJour) ? $reclamJour['J5_J6'] : 0;
            $indicateurReclam[$key]['J7_'] = key_exists('J7_', $reclamJour) ? $reclamJour['J7_'] : 0;
            $indicateurReclam[$key]['NON_REPONDU'] = key_exists('NON_REPONDU', $reclamJour) ? $reclamJour['NON_REPONDU'] : 0;
            $indicateurReclam[$key]['TOTAL'] = $indicateurReclam[$key]['J_J2'] + $indicateurReclam[$key]['J3_J4'] + $indicateurReclam[$key]['J5_J6'] + $indicateurReclam[$key]['J7_'] + $indicateurReclam[$key]['NON_REPONDU'];
        }

        $indicateurValues = array_values($indicateurReclam);
        $totaux['J_J2'] = array_sum($this->array_column($indicateurValues, 'J_J2'));
        $totaux['J3_J4'] = array_sum($this->array_column($indicateurValues, 'J3_J4'));
        $totaux['J5_J6'] = array_sum($this->array_column($indicateurValues, 'J5_J6'));
        $totaux['J7_'] = array_sum($this->array_column($indicateurValues, 'J7_'));
        $totaux['NON_REPONDU'] = array_sum($this->array_column($indicateurValues, 'NON_REPONDU'));
        $totaux['TOTAL'] = array_sum($this->array_column($indicateurValues, 'TOTAL'));

        return array('titres' => $titres, 'indicateurReclam' => $indicateurReclam, 'totaux' => $totaux);
    }

   
    public function getReperage($sociteId, $dateDebut, $dateFin, $depotId) {
        $em = $this->getDoctrine()->getManager();
        $reperages = $em->getRepository('AmsDistributionBundle:Reperage')->getIndicateurReperage($sociteId, $dateDebut, $dateFin, $depotId);
        $reperageJours = array();
        $titres = array('J_J5' => 'J à J+5', 'J6_J8' => 'J+6 à J+8', 'J9_J10' => 'J+9 à J+10', 'J10_' => ' > J+10');
        for ($i = strtotime($dateDebut); $i < strtotime($dateFin); $i = strtotime(date('Y-m-d', $i) . ' +1 day')) {
            $jour = date('Y-m-d', $i);
            foreach ($reperages as $reperage) {
                if ($reperage['date_creation'] == $jour) {
                    if ($reperage['delai'] == 'J_J5')
                        $reperageJours[$jour]['J_J5'] = $reperage['nb_reperage'];
                    if ($reperage['delai'] == 'J6_J8')
                        $reperageJours[$jour]['J6_J8'] = $reperage['nb_reperage'];
                    if ($reperage['delai'] == 'J9_J10')
                        $reperageJours[$jour]['J9_J10'] = $reperage['nb_reperage'];
                    if ($reperage['delai'] == 'J10_')
                        $reperageJours[$jour]['J10_'] = $reperage['nb_reperage'];
                    if ($reperage['delai'] == 'NON_REPONDU')
                        $reperageJours[$jour]['NON_REPONDU'] = $reperage['nb_reperage'];
                }
            }
        }
        $indicateurReperage = array();
        foreach ($reperageJours as $key => $reperageJour) {
            $indicateurReperage[$key]['DATE'] = $this->transformDateToDataBaseFormat($key, '-', '/');
            $indicateurReperage[$key]['J_J5'] = key_exists('J_J5', $reperageJour) ? $reperageJour['J_J5'] : 0;
            $indicateurReperage[$key]['J6_J8'] = key_exists('J6_J8', $reperageJour) ? $reperageJour['J6_J8'] : 0;
            $indicateurReperage[$key]['J9_J10'] = key_exists('J9_J10', $reperageJour) ? $reperageJour['J9_J10'] : 0;
            $indicateurReperage[$key]['J10_'] = key_exists('J10_', $reperageJour) ? $reperageJour['J10_'] : 0;
            $indicateurReperage[$key]['NON_REPONDU'] = key_exists('NON_REPONDU', $reperageJour) ? $reperageJour['NON_REPONDU'] : 0;
            $indicateurReperage[$key]['TOTAL'] = $indicateurReperage[$key]['J_J5'] + $indicateurReperage[$key]['J6_J8'] + $indicateurReperage[$key]['J9_J10'] + $indicateurReperage[$key]['J10_'] + $indicateurReperage[$key]['NON_REPONDU'];
        }
        
        $reperageValues = array_values($indicateurReperage);
        $totaux['J_J5'] = array_sum($this->array_column($reperageValues, 'J_J5'));
        $totaux['J6_J8'] = array_sum($this->array_column($reperageValues, 'J6_J8'));
        $totaux['J9_J10'] = array_sum($this->array_column($reperageValues, 'J9_J10'));
        $totaux['J10_'] = array_sum($this->array_column($reperageValues, 'J10_'));
        $totaux['NON_REPONDU'] = array_sum($this->array_column($reperageValues, 'NON_REPONDU'));
        $totaux['TOTAL'] = array_sum($this->array_column($reperageValues, 'TOTAL'));

        return array('titres' => $titres, 'indicateurReperage' => $indicateurReperage, 'totaux' => $totaux);
    }
    

    private function indicateurExcel($titres, $indicateurs, $totaux, $filename) {
        
        if(count($indicateurs) ==  0 ) {
            $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
            $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5'); 
        }else {
            $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
            $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
            $periode = array_keys($titres);
            $entete = array_values($titres);
            $phpExcelObject->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'Date')
                    ->setCellValue('B1', $entete[0])
                    ->setCellValue('C1', $entete[1])
                    ->setCellValue('D1', $entete[2])
                    ->setCellValue('E1', $entete[3])
                    ->setCellValue('F1', 'NON REPONDU')
                    ->setCellValue('G1', 'Nombre de réclam');

            $lineExcel = 2;
            foreach ($indicateurs as $indicateur) {
                $phpExcelObject->setActiveSheetIndex(0)
                        ->setCellValue('A' . $lineExcel, $indicateur['DATE'])
                        ->setCellValue('B' . $lineExcel, $indicateur[$periode[0]])
                        ->setCellValue('C' . $lineExcel, $indicateur[$periode[1]])
                        ->setCellValue('D' . $lineExcel, $indicateur[$periode[2]])
                        ->setCellValue('E' . $lineExcel, $indicateur[$periode[3]])
                        ->setCellValue('F' . $lineExcel, $indicateur['NON_REPONDU'])
                        ->setCellValue('G' . $lineExcel, $indicateur['TOTAL']);
                $lineExcel++;
            }

            // calcul des totaux
            $lineExcelTotaux = $lineExcel + 1;
            $phpExcelObject->setActiveSheetIndex(0)
                    ->setCellValue('A' . $lineExcelTotaux, 'TOTAL')
                    ->setCellValue('B' . $lineExcelTotaux, $totaux[$periode[0]])
                    ->setCellValue('C' . $lineExcelTotaux, $totaux[$periode[1]])
                    ->setCellValue('D' . $lineExcelTotaux, $totaux[$periode[2]])
                    ->setCellValue('E' . $lineExcelTotaux, $totaux[$periode[3]])
                    ->setCellValue('F' . $lineExcelTotaux, $totaux['NON_REPONDU'])
                    ->setCellValue('G' . $lineExcelTotaux, $totaux['TOTAL']);

            $lineExcelTaux = $lineExcelTotaux + 1;
            $phpExcelObject->setActiveSheetIndex(0)
                    ->setCellValue('A' . $lineExcelTaux, 'Taux (%)')
                    ->setCellValue('B' . $lineExcelTaux, number_format($totaux[$periode[0]] / $totaux['TOTAL'], 4) * 100)
                    ->setCellValue('C' . $lineExcelTaux, number_format($totaux[$periode[1]] / $totaux['TOTAL'], 4) * 100)
                    ->setCellValue('D' . $lineExcelTaux, number_format($totaux[$periode[2]] / $totaux['TOTAL'], 4) * 100)
                    ->setCellValue('E' . $lineExcelTaux, number_format($totaux[$periode[3]] / $totaux['TOTAL'], 4) * 100)
                    ->setCellValue('F' . $lineExcelTaux, number_format($totaux['NON_REPONDU'] / $totaux['TOTAL'], 4) * 100)
                    ->setCellValue('G' . $lineExcelTaux, 100);

            // Rename worksheet
            $phpExcelObject->getActiveSheet()->setTitle('Indicateurs');
            $phpExcelObject->setActiveSheetIndex(0);
            $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        }
        return $writer;
    }

    /**
     * [transformDateToDataBaseFormat transforme la date au format définit en base de données
     * @param  [string] $date  [valeur de date]
     * @return [string]        [valeur de date edans le format définit en base de données]
     */
    private function transformDateToDataBaseFormat($date, $delim, $delim2 = null) {
        $delim1 = empty($delim2) ? $delim : $delim2;
        $dateItems = explode($delim, $date);
        return $dateItems[2] . $delim1 . $dateItems[1] . $delim1 . $dateItems[0];
    }

}
