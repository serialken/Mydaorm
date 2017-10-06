<?php

namespace Ams\EmployeBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use HTML2PDF;
use Ams\EmployeBundle\Controller\GlobalEmployeController;

class ContratController extends GlobalEmployeController {

    public function getRepositoryName() {
        return $this->getBundleName() . ':Contrat';
    }

    public function getServiceName() {
        return 'ams.repository.employecontrat';
    }

    public function gridAction() {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $response = $this->renderView($this->getTwigGrid(), array(
            'curseur' => $this->get($this->getServiceName())->select($session->get("depot_id"), $session->get("flux_id"), $session->get("anneemois_id")),
            'comboEmploye' => $this->getCombo($em->getRepository('AmsEmployeBundle:Employe')->selectComboRH($session->get("depot_id"), $session->get("flux_id"), $session->get("anneemois_id"))),
            'comboDepot' => $this->getCombo($em->getRepository('AmsSilogBundle:Depot')->selectCombo()),
            'comboFlux' => $this->getCombo($em->getRepository('AmsReferentielBundle:RefFlux')->selectCombo()),
            'comboSociete' => $this->getCombo($em->getRepository('AmsReferentielBundle:RefEmpSociete')->selectCombo()),
            'comboEmploi' => $this->getCombo($em->getRepository('AmsReferentielBundle:RefEmploi')->selectCombo()),
            'comboTypeTournee' => $this->getCombo($em->getRepository('AmsReferentielBundle:RefTypeTournee')->selectComboAll()),
            'comboTypeContrat' => $this->getCombo($em->getRepository('AmsReferentielBundle:RefTypeContrat')->selectCombo()),
        ));
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }

    public function listeAction(Request $request) {
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
        $this->setDerniere_page();
        $session = $this->get('session');

        $this->saveUrl2Session($session, $request, '');
        $form = $this->initFiltreDepotFluxMois($session, $request);

        return $this->render($this->getTwigListe(), array(
                    'isModif' => false,
                    'isAlim' => $this->isPageElement('ALIM', 'alimentation_employe'),
                    'form' => $form->createView(),
                    'route' => $this->page_courante_route,
                    'titre' => $this->titre_page,
                    'repository' => $this->getServiceName(),
        ));
    }

    public function alimentationAction(Request $request) {
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
        $session = $this->get('session');

        $this->get('PleiadesNG')->alimentation($idtrt, $session->get("UTILISATEUR_ID"), $session->get("depot_id"), $session->get("flux_id"));
        //$this->get('doctrine')
        //                  ->getRepository('AmsEmployeBundle:Employe','pleiadesng')->alimentation();
        return new Response(null, 200, array('Content-Type' => 'Application/xml'));
    }

    public function exportAnnexePDF($employe_id, $date, &$filename) {
        $em = $this->getDoctrine()->getManager();

        $entete = $em->getRepository('AmsEmployeBundle:EmpContrat')->getAnnexeEntete($employe_id, $date);
        $html = $this->renderView('AmsEmployeBundle:Contrat:annexe.html.twig', array(
            'entete' => $entete,
            'tournees' => $em->getRepository('AmsEmployeBundle:EmpContrat')->getAnnexeTournees($employe_id, $date),
            'reference' => $em->getRepository('AmsEmployeBundle:EmpContrat')->getAnnexeReference($employe_id, $date),
        ));
        $filename = $entete[0]["fichier"];
        $html2pdf = new HTML2PDF('P', 'A4', 'fr');
        $html2pdf->pdf->SetDisplayMode('real');
        $html2pdf->writeHTML($html);
        return $html2pdf;
    }

    public function exportAnnexeAction(Request $request) {
        $now = new \DateTime();
        $now->setTime(0, 0, 0);

        $html2pdf = $this->exportAnnexePDF($request->get('employe_id'), $now->format('Y-m-d'), $filename);
        $result = $html2pdf->Output("commande.pdf", true);

        $response = new Response();
        $response->setContent($result);
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-disposition', 'filename=' . $filename);
        return ($response);
    }

    public function exportAnnexeFichierAction() {
        ini_set('max_execution_time', 999000);

        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $now = new \DateTime();
        $now->setTime(0, 0, 0);

        $oDepot = $em->getRepository('AmsSilogBundle:Depot')->find($session->get("depot_id"));
        $depot = $oDepot->getCode();

        $rep_annexe_paie = $this->container->getParameter('REP_ANNEXE_PAIE');
        $dir = $rep_annexe_paie . $depot . '/';
        $dirTmp = $rep_annexe_paie . $depot . '/TMP_CONTRAT_' . $session->get("flux_id") . '/';
        $this->delTree($dirTmp);
        $this->cree_repertoire($dirTmp);

        $employes = $em->getRepository('AmsEmployeBundle:EmpContrat')->getAnnexeEmployes($session->get("depot_id"), $session->get("flux_id"), $now->format('Y-m-d'));
        foreach ($employes as $employe) {
            $html2pdf = $this->exportAnnexePDF($employe["employe_id"], $now->format('Y-m-d'), $filename);
            $filename = $dirTmp . str_replace("/", "", $filename);
            $html2pdf->Output($filename, 'F');
        }
        $filename = $depot . '_' . $now->format('Y-m-d') . '_annexe_contrat.pdf';
        if ($session->get("flux_id") == 1) {
            $filename = "proximy_" . $filename;
        } else {
            $filename = "mediapresse_" . $filename;
        }
        $filename = $dir . $filename;
        
        if (is_dir($dirTmp)) {
            // Un seul fichier, on recopie
            if (count(array_diff(scandir($dirTmp), array('.', '..'))) == 1) {
                exec("cp -f " . $dirTmp . array_diff(scandir($dirTmp), array('.', '..'))[2] . " " . $filename);
                // Pleusieurs fichiers, on merge
            } elseif (count(array_diff(scandir($dirTmp), array('.', '..'))) > 1) {
                unlink($filename);
                exec("cd " . $dirTmp . " && pdfunite * " . $filename);
                //exec("pdftk " . $dirTmp . "* cat output " . $dir . $filename);
            }
            $this->delTree($dirTmp);
        }

        $response = new BinaryFileResponse($filename);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);

        return $response;
    }

    protected function cree_repertoire($_sStr) {
        if (is_dir($_sStr)) {
            return $_sStr;
        } else {
            if (mkdir($_sStr, 0777, true)) {

                return $_sStr;
            } else {
                trigger_error('Erreur lors de la creation du repertoire ' . $_sStr, E_USER_ERROR);
                return "";
            }
        }
    }

    protected function delTree($dir) {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }
}
