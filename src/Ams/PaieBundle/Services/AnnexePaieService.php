<?php

namespace Ams\PaieBundle\Services;

use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\DBALException;
use HTML2PDF;
use TCPDF;
/**
 *
 *  Génération des annexes tournee
 */
class AnnexePaieService {

    private $em;
    private $templating;

    public function __construct(\Doctrine\ORM\EntityManager $em, $templating) {
        $this->em = $em;
        $this->templating = $templating;
    }

    public function getPDF($depot_id,$flux_id,$employe_depot_hst_id,$provisoire=false) {
        $employe=$this->em->getRepository('AmsPaieBundle:PaiTournee')->getAnnexeEmploye($employe_depot_hst_id);
        $detail=$this->em->getRepository('AmsPaieBundle:PaiTournee')->getAnnexeDetail($employe_depot_hst_id);
        $ev=$this->em->getRepository('AmsPaieBundle:PaiTournee')->getAnnexeEv($employe_depot_hst_id);
        $filename = "annexe_paie_" . $employe[0]['nom'] . "_" . $employe[0]['prenom1'] . ".pdf";

        $html = $this->templating->render('AmsPaieBundle:PaiAnnexe:export.pdf.twig', array(
        'flux_id' =>  $flux_id, 
        'employe' =>  $employe, 
        'ev' =>  $ev, 
        'detail' =>  $detail, 
        'provisoire' =>  (isset($employe[0]) && $employe[0]['provisoire']?'provisoire':''), 
            ));

        $html2pdf = new HTML2PDF('P', 'A4', 'fr');
//        $html2pdf->setModeDebug();
        $html2pdf->pdf->SetDisplayMode('real');
        $html2pdf->writeHTML($html);
        return $html2pdf->Output($filename, 'D');
    }
    
    public function writePDF($depot_id,$flux_id,$employe_depot_hst_id,$filename,$provisoire=false) {
        $employe=$this->em->getRepository('AmsPaieBundle:PaiTournee')->getAnnexeEmploye($employe_depot_hst_id);
        $detail=$this->em->getRepository('AmsPaieBundle:PaiTournee')->getAnnexeDetail($employe_depot_hst_id);
        $ev=$this->em->getRepository('AmsPaieBundle:PaiTournee')->getAnnexeEv($employe_depot_hst_id);
        if (count($detail)) {
            $html = $this->templating->render('AmsPaieBundle:PaiAnnexe:export.pdf.twig', array(
            'flux_id' =>  $flux_id, 
            'employe' =>  $employe, 
            'ev' =>  $ev, 
            'detail' =>  $detail, 
            'provisoire' =>  (isset($employe[0]) && $employe[0]['provisoire']?'provisoire':''), 
                ));
            $html2pdf = new HTML2PDF('P', 'A4', 'fr');
            $html2pdf->pdf->SetDisplayMode('real');
            $html2pdf->writeHTML($html);
            $html2pdf->Output($filename, 'F');
        }
    }
        
    public function getTCPDF($depot_id,$flux_id,$employe_depot_hst_id) {
        $employe=$this->em->getRepository('AmsPaieBundle:PaiTournee')->getAnnexeEmploye($employe_depot_hst_id);
        $detail=$this->em->getRepository('AmsPaieBundle:PaiTournee')->getAnnexeDetail($employe_depot_hst_id);
        $ev=$this->em->getRepository('AmsPaieBundle:PaiTournee')->getAnnexeEv($employe_depot_hst_id);
        $filename = "annexe_paie_" . $employe[0]['nom'] . "_" . $employe[0]['prenom1'] . ".pdf";

        $html = $this->templating->render('AmsPaieBundle:PaiAnnexe:export.tcpdf.twig', array(
        'flux_id' =>  $flux_id, 
        'employe' =>  $employe, 
        'ev' =>  $ev, 
        'detail' =>  $detail, 
            ));

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Nicola Asuni');
        $pdf->SetTitle('TCPDF Example 049');
        $pdf->SetSubject('TCPDF Tutorial');
        $pdf->SetKeywords('TCPDF, PDF, example, test, guide');

//        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 049', PDF_HEADER_STRING);

        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->SetFont('times', '', 11);
        $pdf->AddPage();
        $pdf->writeHTML($html, true, 0, true, 0);

        $pdf->lastPage();
        return $pdf->Output($filename, 'D');
    }
}