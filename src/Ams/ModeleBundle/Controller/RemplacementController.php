<?php

namespace Ams\ModeleBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use HTML2PDF;
use Ams\ModeleBundle\Controller\GlobalModeleController;

class RemplacementController extends GlobalModeleController {

    public function getRepositoryName() {
        return $this->getBundleName() . ':Remplacement';
    }

    public function getRoute() {
        return 'liste_remplacement';
    }

    public function gridAction() {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $response = $this->renderView($this->getTwigGrid(), array(
            'isModif' => $this->getIsModif(),
            'curseur' => $this->getRepository()->select($session->get("depot_id"), $session->get("flux_id"), $session->get("anneemois_id")),
            'comboEmploye' => $this->getCombo($em->getRepository('AmsEmployeBundle:Employe')->selectComboRemplacement($session->get("depot_id"), $session->get("flux_id"), $session->get("anneemois_id")), true),
            'comboTourneeLundi' => $this->getCombo($em->getRepository('AmsModeleBundle:ModeleTourneeJour')->selectComboRemplacement($session->get("depot_id"), $session->get("flux_id"), $session->get("anneemois_id"), 2), true),
            'comboTourneeMardi' => $this->getCombo($em->getRepository('AmsModeleBundle:ModeleTourneeJour')->selectComboRemplacement($session->get("depot_id"), $session->get("flux_id"), $session->get("anneemois_id"), 3), true),
            'comboTourneeMercredi' => $this->getCombo($em->getRepository('AmsModeleBundle:ModeleTourneeJour')->selectComboRemplacement($session->get("depot_id"), $session->get("flux_id"), $session->get("anneemois_id"), 4), true),
            'comboTourneeJeudi' => $this->getCombo($em->getRepository('AmsModeleBundle:ModeleTourneeJour')->selectComboRemplacement($session->get("depot_id"), $session->get("flux_id"), $session->get("anneemois_id"), 5), true),
            'comboTourneeVendredi' => $this->getCombo($em->getRepository('AmsModeleBundle:ModeleTourneeJour')->selectComboRemplacement($session->get("depot_id"), $session->get("flux_id"), $session->get("anneemois_id"), 6), true),
            'comboTourneeSamedi' => $this->getCombo($em->getRepository('AmsModeleBundle:ModeleTourneeJour')->selectComboRemplacement($session->get("depot_id"), $session->get("flux_id"), $session->get("anneemois_id"), 7), true),
            'comboTourneeDimanche' => $this->getCombo($em->getRepository('AmsModeleBundle:ModeleTourneeJour')->selectComboRemplacement($session->get("depot_id"), $session->get("flux_id"), $session->get("anneemois_id"), 1), true),
        ));
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }

    public function newGridAction($param, $newId) {
        $session = $this->get('session');

//        $sqlCondition = "AND (mr.contrattype_id=" . $param["contrattype_id"] . ")";
        $sqlCondition = "";

        $response = $this->renderView($this->getTwigRows(), array(
            'isModif' => $this->getIsModif(),
            'curseur' => $this->getRepository()->select($session->get("depot_id"), $session->get("flux_id"), $session->get("anneemois_id"), $sqlCondition),
        ));
        return new Response($response);
    }

    public function listeAction(Request $request) {
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
        $this->setDerniere_page();
        $session = $this->get('session');

        $this->saveUrl2Session($session, $request, 'remplacement_id');
        $form = $this->initFiltreDepotFluxMois($session, $request);
        return $this->render($this->getTwigListe(), array(
                    'isModif' => $this->getIsModif(),
                    'form' => $form->createView(),
                    'titre' => $this->titre_page,
                    'route' => $this->page_courante_route,
                    'repository' => $this->getRepositoryName(),
                    'depot_id' => $session->get("depot_id"),
                    'flux_id' => $session->get("flux_id"),
                    'remplacement_id' => $session->get("remplacement_id"),
        ));
    }

    public function exportAnnexeAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $entete=$em->getRepository('AmsModeleBundle:Remplacement')->getAnnexeEntete($request->get('remplacement_id'));
        $html = $this->renderView('AmsModeleBundle:Remplacement:annexe.html.twig', array(
            'entete' => $entete,
            'tournees' => $em->getRepository('AmsModeleBundle:Remplacement')->getAnnexeTournees($request->get('remplacement_id')),
            'reference' => $em->getRepository('AmsModeleBundle:Remplacement')->getAnnexeReference($request->get('remplacement_id')),
        ));

        $html2pdf = new HTML2PDF('P', 'A4', 'fr');
        $html2pdf->pdf->SetDisplayMode('real');
        $html2pdf->writeHTML($html);
        $result = $html2pdf->Output("commande.pdf", true);

        $response = new Response();
        $response->setContent($result);
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-disposition', 'filename='.$entete[0]["fichier"]);
        return ($response);
    }
}
