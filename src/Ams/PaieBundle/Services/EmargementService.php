<?php

namespace Ams\PaieBundle\Services;

use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\DBALException;
use PHPExcel_Worksheet_PageSetup;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Border;
use PHPExcel_Style_Fill;
use PHPExcel_Worksheet;

/**
 *
 *  Génération des feuille d'emargement
 */
class EmargementService {

    private $em;
    private $phpexcel;
    private $styleHeader = array(
        'font' => array(
            'bold' => true,
            'size' => 10
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ),
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('argb' => 'FF729fcf'),
        ),
    );
    private $styleHeader2 = array(
        'font' => array(
            'bold' => true,
            'size' => 8
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ),
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('argb' => 'FF729fcf'),
        ),
    );
    private $styleHeaderLeft = array(
        'font' => array(
            'bold' => true,
            'size' => 10
        ),
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('argb' => 'FF729fcf'),
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
        ),
    );
    private $styleBold = array(
        'font' => array(
            'bold' => true,
            'size' => 10
        ),
    );
    private $styleArray = array(
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN,
            ),
        ),
        'alignment' => array(
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ),
    );
    private $styleLeft = array(
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
        ),
    );
    private $styleCentre = array(
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ),
    );
    private $styleBackGround = array(
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('argb' => 'FFeeeeec'),
        ),
    );

    public function __construct(\Doctrine\ORM\EntityManager $em, $phpexcel) {
        $this->em = $em;
        $this->phpexcel = $phpexcel;
    }

    /**
     * retourne les valeurs nécessaires pour la génération des feuilles d'émargement
     * @param type $depot_id
     * @param type $flux_id
     * @param type $date_distrib
     * @return array
     */
    public function getData($depot_id, $flux_id, $date_distrib) {
        $curseur = $this->em->getRepository('AmsPaieBundle:PaiHeure')->getEmargement($depot_id, $flux_id, $date_distrib);
        $groupes = array();
        foreach ($curseur as $key => $val) {
            $groupes[$val['groupe_id']]['code'] = $val['code'];
            $groupes[$val['groupe_id']]['heure_debut'] = $val['heure_debut'];
            $groupes[$val['groupe_id']]['duree_attente'] = $val['duree_attente'];
        }

        $curseur = $this->em->getRepository('AmsPaieBundle:PaiTournee')->getEmargement($depot_id, $flux_id, $date_distrib);
        $emargements = array();
        foreach ($curseur as $key => $val) {
            $emargements[$val['groupe_id']][$val['emp_id']]['emp_id'] = $val['emp_id'];
            $emargements[$val['groupe_id']][$val['emp_id']]['nom_prenom'] = $val['nom_prenom'];
            $emargements[$val['groupe_id']][$val['emp_id']]['nbkm_paye'] = $val['nbkm_paye'];
            $emargements[$val['groupe_id']][$val['emp_id']]['nb_reperage'] = $val['nb_reperage'];
            $emargements[$val['groupe_id']][$val['emp_id']]['duree'] = $val['duree'];
            $emargements[$val['groupe_id']][$val['emp_id']]['code_tournee'] = $val['code'];
        }

        // calcul du nombre de clients
        $produits = array();
        $curseur = $this->em->getRepository('AmsPaieBundle:PaiTournee')->getEmargementProduit($depot_id, $flux_id, $date_distrib);
        $nbClients = array();
        foreach ($curseur as $nbClient) {
            $nbClients[$nbClient['employe_id']][$nbClient['code_prd']] = $nbClient['nbClient'];
            $produits[$nbClient['groupe_id']][$nbClient['code_prd']] = $nbClient['code_prd'];
        }

        $curseur = $this->em->getRepository('AmsPaieBundle:PaiTournee')->getEmargementReclamation($depot_id, $flux_id, $date_distrib);
        $reclamations = array();
        foreach ($curseur as $reclamation) {
            $reclamations[$reclamation['employe_id']] = $reclamation['reclamation'];
        }
        return array(
            'groupes' => $groupes,
            'produits' => $produits,
            'emargements' => $emargements,
            'reclamations' => $reclamations,
            'nbAbonneParProduit' => $nbClients,
        );
    }

    public function setCell($i) {
        if ($i > 90) {
            $reste = $i % 90;
            $cell = "A" . chr(64 + $reste);
        } else {
            $cell = chr($i);
        }
        return $cell;
    }

    public function emargementTourneeExcel($depot_id, $flux_id, $date_distrib) {
        $phpExcelObject = $this->phpexcel->createPHPExcelObject();
        $phpExcelObject->getProperties()->setCreator("Mroad")->setDescription("Feuille Emargement.");
        $phpExcelObject->getDefaultStyle()->getFont()->setName('Arial');
        $phpExcelObject->getDefaultStyle()->getFont()->setSize(8);

        $depot = $this->em->getRepository('AmsSilogBundle:Depot')->findOneById($depot_id);
        $title = 'Le ' . strftime('%d/%m/%Y', strtotime($date_distrib)) . '   ' . $depot->getLibelle() . '  ' . ($flux_id == 1 ? 'Nuit' : 'Jour') . '  ';

        $data = $this->getData($depot_id, $flux_id, $date_distrib);
        $indSheet = 0;
        foreach ($data['groupes'] as $groupe_id => $groupe) {
            if (isset($data['produits'][$groupe_id]) && isset($data['emargements'][$groupe_id])) {
                if ($indSheet == 0) {
                    $objWorkSheet = $phpExcelObject->getActiveSheet();
                } else {
                    $objWorkSheet = $phpExcelObject->createSheet($indSheet);
                }
                $titleSheet="Groupe " . $groupe['code'];
                $this->feuilleEmargementTournee($objWorkSheet, $title, $titleSheet, $groupe, $data['produits'][$groupe_id], $data['emargements'][$groupe_id], $data['reclamations'], $data['nbAbonneParProduit']);
                $indSheet++;
            }
        }
        $phpExcelObject->setActiveSheetIndex(0);
        $writer = $this->phpexcel->createWriter($phpExcelObject, 'Excel5');
        $response = $this->phpexcel->createStreamedResponse($writer);
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment;filename=Emargement_Tournées_' . $date_distrib . '_' . $depot->getCode() . '_' . ($flux_id == 1 ? 'Nuit' : 'Jour') . '.xls');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        return $response;
    }

    private function feuilleEmargementTournee($objWorkSheet, $title, $titleSheet, $groupe, $produits, $emargement, $reclamations, $nbAbonneParProduit) {
        $colFirstProduit=66;
        $nbColFixe = 6;
        $nbRow = 64 + $nbColFixe + count($produits);
        $nbLine = (2*count($emargement) + 10*((int)((count($emargement)-1)/10)+1));
        $lastCol = $this->setCell($nbRow);
        $lastTitre = $this->setCell($nbRow - 1);
        $lastCell = $this->setCell($nbRow) . $nbLine;

        $objWorkSheet->setTitle($titleSheet);
        $objWorkSheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        $objWorkSheet->getPageSetup()->setHorizontalCentered(true);
        $objWorkSheet->getPageSetup()->setVerticalCentered(true);
        $objWorkSheet->getPageSetup()->setFitToWidth(1);
        $objWorkSheet->getPageSetup()->setFitToHeight(0);
//        $objWorkSheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 4);
        // margin is set in inches (0.5cm)
        $margin = 0.5 / 2.54;
        $objWorkSheet->getPageMargins()
                ->setTop($margin)
                ->setBottom($margin)
                ->setLeft($margin)
                ->setRight($margin);
        $objWorkSheet->setBreak( $lastCol."1" , PHPExcel_Worksheet::BREAK_COLUMN );
        
        $reliquat=(int)(90-5*count($produits))/3; // 90 = espace réservé aux titres + date
        // Permet d'augmenter la tailles des colonnes Tournee/Nom/Signature si pas beaucoup de titres
        $reliquat=0;
        $objWorkSheet->getDefaultRowDimension()->setRowHeight(19);
        $objWorkSheet->getDefaultColumnDimension()->setWidth(5);
        $objWorkSheet->getColumnDimension('A')->setWidth(15+$reliquat);
        $objWorkSheet->getColumnDimension('B')->setWidth(20+$reliquat);
        $objWorkSheet->getColumnDimension($lastTitre)->setWidth(9); // Durée
        $objWorkSheet->getColumnDimension($lastCol)->setWidth(30+$reliquat); // Signature

        $objWorkSheet->getStyle("A1:$lastCell")->applyFromArray($this->styleArray);
        $objWorkSheet->getStyle("A1:$lastCell")->getAlignment()->setWrapText(true);
        $objWorkSheet->getStyle("B1:B$nbLine")->applyFromArray($this->styleBold);
        
        $numEmp = 0;
        $lig = 1;
        foreach ($emargement as $employe_id => $employe) {
            if($numEmp % 10==0){
                $this->feuilleEmargementTourneeTop($objWorkSheet, $lig, $lastCol, $title, $titleSheet, $groupe, $produits);
            }
            $ligRec = $lig + 1;
            $objWorkSheet
                    ->setCellValue("A$lig", $employe['code_tournee'])
                    ->mergeCells("A$lig:A$ligRec")
                    ->setCellValue("B$lig", $employe['nom_prenom'])
                    ->mergeCells("B$lig:B$ligRec")
                    ->mergeCells("C$ligRec:$lastTitre$ligRec")
                    ->mergeCells("$lastCol$lig:$lastCol$ligRec")
            ;
            $numProd = $colFirstProduit;
            foreach ($produits as $produit_id => $produit) {
                $numProd++;
                if (isset($nbAbonneParProduit[$employe_id][$produit_id])) {
                    $objWorkSheet->setCellValue($this->setCell($numProd) . "$lig", $nbAbonneParProduit[$employe_id][$produit_id]);
                }
            }
            $objWorkSheet
                    ->setCellValue($this->setCell($numProd + 1) . "$lig", ($employe['nb_reperage'] == 0 ? '' : $employe['nb_reperage']))
                    ->setCellValue($this->setCell($numProd + 2) . "$lig", ($employe['nbkm_paye'] == 0 ? '' : $employe['nbkm_paye']))
                    ->setCellValue($this->setCell($numProd + 3) . "$lig", $employe['duree'])
            ;
            if (isset($reclamations[$employe_id]) && count($reclamations[$employe_id]) > 0) {
                $objWorkSheet->setCellValue("C$ligRec", $reclamations[$employe_id]);
            }
            $objWorkSheet->getStyle("C$ligRec")->applyFromArray($this->styleLeft);
            if ($numEmp % 2 == 1) {
                $objWorkSheet->getStyle("A$lig:$lastCol$ligRec")->applyFromArray($this->styleBackGround);
            }
            $lig = $lig + 2;
            $numEmp++;
            if($numEmp % 10==0){
                $this->feuilleEmargementTourneeBottom($objWorkSheet, $lig, $lastCol, $lastTitre);
            }
        }
        if($numEmp % 10!=0){
            $this->feuilleEmargementTourneeBottom($objWorkSheet, $lig, $lastCol, $lastTitre);
        }
    }
    private function feuilleEmargementTourneeTop($objWorkSheet, &$lig, $lastCol, $title, $titleSheet, $groupe, $produits) {
        $colFirstProduit=66;

        $objWorkSheet->getStyle("A$lig:" . $lastCol . $lig)->applyFromArray($this->styleHeader);
        $objWorkSheet
                ->mergeCells("A$lig:$lastCol$lig")
                ->setCellValue("A$lig", $title . $titleSheet);
        $lig++;
        $objWorkSheet->getStyle("A$lig:" . $lastCol . $lig)->applyFromArray($this->styleHeaderLeft);
        $objWorkSheet
                ->mergeCells("A$lig:B$lig")
                ->setCellValue("A$lig", 'Heure de début : ' . $groupe['heure_debut'])
                ->mergeCells("C$lig:$lastCol".($lig+1))
                ->setCellValue("C$lig", 'Responsable :');
        $lig++;
        $objWorkSheet->getStyle("A$lig:$lastCol$lig")->applyFromArray($this->styleHeaderLeft);
        $objWorkSheet
                ->mergeCells("A$lig:B$lig")
                ->setCellValue("A$lig", 'Attente : ' . $groupe['duree_attente']);
        $lig++;
        $objWorkSheet->getStyle("A$lig:$lastCol$lig")->applyFromArray($this->styleHeader2);
        $objWorkSheet
                ->setCellValue("A$lig", 'Tournée')
                ->setCellValue("B$lig", 'Nom');

        $numProd = $colFirstProduit;
        foreach ($produits as $produit) {
            $numProd++;
            $objWorkSheet->setCellValue($this->setCell($numProd) . "$lig", $produit);
        }
        $objWorkSheet
                ->setCellValue($this->setCell($numProd + 1) . "$lig", 'Rep')
                ->setCellValue($this->setCell($numProd + 2) . "$lig", 'Km')
                ->setCellValue($this->setCell($numProd + 3) . "$lig", 'Durée calculée')
                ->setCellValue($this->setCell($numProd + 4) . "$lig", 'Signature');
        $lig++;
    }
    private function feuilleEmargementTourneeBottom($objWorkSheet, &$lig, $lastCol, $lastTitre) {
        $objWorkSheet->getStyle("A$lig:B".($lig+1))->applyFromArray($this->styleHeader);
        $objWorkSheet->getStyle("$lastCol$lig:$lastCol".($lig+1))->applyFromArray($this->styleHeader);
        $objWorkSheet
                ->setCellValue("A$lig", 'Absences')
                ->mergeCells("A$lig:B".($lig+1))
                ->mergeCells("C$lig:$lastTitre".($lig+1))
                ->setCellValue("$lastCol$lig", 'Signature du Responsable')
                ->mergeCells("$lastCol$lig:$lastCol".($lig+1));
        $lig = $lig + 2;
        $objWorkSheet->getStyle("A$lig:B".($lig+1))->applyFromArray($this->styleHeader);
        $objWorkSheet
                ->setCellValue("A$lig", "Incident Terrain\n(n° tournée, nom et motif)")
                ->mergeCells("A$lig:B".($lig+1))
                ->mergeCells("C$lig:$lastTitre".($lig+1))
                ->mergeCells("$lastCol$lig:$lastCol".($lig+3));
        $lig = $lig + 2;
        $objWorkSheet->getStyle("A$lig:B".($lig+1))->applyFromArray($this->styleHeader);
        $objWorkSheet
                ->setCellValue("A$lig", "Remplacement\n(n° tournée, nom et motif)")
                ->mergeCells("A$lig:B".($lig+1))
                ->mergeCells("C$lig:$lastTitre".($lig+1));
        $objWorkSheet->setBreak( "A".($lig+1) , PHPExcel_Worksheet::BREAK_ROW );
        $lig = $lig + 2;
  //      $lig++;
    }
    /**
     *  Feuilles emargement presse et hors presse porteur polyvalent
     * @param type $depot_id
     * @param type $flux_id
     * @param type $date_distrib
     * @param type $type_emploi
     * @param type $type_presse
     * @return excel document
     */
    public function emargementHeureExcel($depot_id, $flux_id, $date_distrib) {
        $phpExcelObject = $this->phpexcel->createPHPExcelObject();
        $phpExcelObject->getProperties()->setCreator("Mroad")->setDescription("Feuille Emargement.");
        $phpExcelObject->getDefaultStyle()->getFont()->setName('Arial');
        $phpExcelObject->getDefaultStyle()->getFont()->setSize(8);

        $depot = $this->em->getRepository('AmsSilogBundle:Depot')->findOneById($depot_id);
        $title = 'Le ' . strftime('%d/%m/%Y', strtotime($date_distrib)) . '   ' . $depot->getLibelle() . '  ' . ($flux_id == 1 ? 'Nuit' : 'Jour') . '  ';

        $hors_presses = array(0, 1);
        $emplois = array('POL', 'POR');

        $indSheet = 0;
        foreach ($hors_presses as $hors_presse) {
            foreach ($emplois as $emploi) {
                $activites = $this->em->getRepository('AmsPaieBundle:PaiActivite')->getEmargement($depot_id, $flux_id, $date_distrib, $emploi, $hors_presse);
                if (count($activites) > 0) {
                    if ($indSheet == 0) {
                        $objWorkSheet = $phpExcelObject->getActiveSheet();
                    } else {
                        $objWorkSheet = $phpExcelObject->createSheet($indSheet);
                    }
                    $titleSheet = ($emploi == 'POR' ? 'Porteur' : 'Polyvalent') . '  ' . ($hors_presse ? 'HorsPresse' : 'Presse');
                    $this->feuilleEmargementActivite($objWorkSheet, $title, $titleSheet, $activites);
                    $indSheet++;
                }
            }
        }
        $phpExcelObject->setActiveSheetIndex(0);
        $writer = $this->phpexcel->createWriter($phpExcelObject, 'Excel5');
        $response = $this->phpexcel->createStreamedResponse($writer);
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');

        $response->headers->set('Content-Disposition', 'attachment;filename=Emargement_Activités_' . $date_distrib . '_' . $depot->getCode() . '_' . ($flux_id == 1 ? 'Nuit' : 'Jour') . '.xls');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        return $response;
    }

    private function feuilleEmargementActivite($objWorkSheet, $title, $titleSheet, $activites) {
        $objWorkSheet->setTitle($titleSheet);
        $objWorkSheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 2);
        $objWorkSheet->getColumnDimension('A')->setWidth(20);
        $objWorkSheet->getColumnDimension('B')->setWidth(30);
        $objWorkSheet->getColumnDimension('F')->setWidth(20);
        $objWorkSheet->getDefaultColumnDimension()->setWidth(12);
        $lastRow = 2 + count($activites);
        $objWorkSheet->getStyle("A1:F2")->applyFromArray($this->styleHeader);
        $objWorkSheet->getStyle("A1:F$lastRow")->applyFromArray($this->styleArray);
        $objWorkSheet->getStyle("A1:F$lastRow")->getAlignment()->setWrapText(true);
        $objWorkSheet->getRowDimension('1')->setRowHeight(38);
        $objWorkSheet->getRowDimension('2')->setRowHeight(38);
        $objWorkSheet
                ->mergeCells("A1:F1")
                ->setCellValue('A1', $title . $titleSheet)
                ->mergeCells('D1:F1')
                ->setCellValue('A2', 'Nom')
                ->setCellValue('B2', 'Activité')
                ->setCellValue('C2', 'Heure début')
                ->setCellValue('D2', 'Durée')
                ->setCellValue('E2', 'Km')
                ->setCellValue('F2', 'Signature')
        ;
        $i = 2;
        foreach ($activites as $activite) {
            $i++;
            $objWorkSheet
                    ->setCellValue("A$i", $activite['nom_prenom'])
                    ->setCellValue("B$i", $activite['libelle'])
                    ->setCellValue("C$i", $activite['heure_debut'])
                    ->setCellValue("D$i", $activite['duree'])
                    ->setCellValue("E$i", $activite['nbkm_paye'])
                    ->setCellValue("F$i", '')
            ;
            $objWorkSheet->getRowDimension("$i")->setRowHeight(38);
        }
        $objWorkSheet->getStyle("C3:D$i")->applyFromArray($this->styleCentre);
    }

}
