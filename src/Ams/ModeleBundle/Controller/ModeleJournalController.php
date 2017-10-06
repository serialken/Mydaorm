<?php
namespace Ams\ModeleBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Ams\ModeleBundle\Controller\GlobalModeleController;

class ModeleJournalController extends GlobalModeleController {

    public function getRepositoryName() { return $this->getBundleName().':ModeleJournal'; }

    public function gridAction() {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

         $response = $this->renderView($this->getTwigGrid(), array(
            'isModif' => false,
            'curseur' => $this->getRepository()->select($session->get("depot_id"),$session->get("flux_id")),
            'comboJour' => $this->getCombo($em->getRepository('AmsReferentielBundle:RefJour')->selectCombo()),
            'comboEmploye' => $this->getCombo($em->getRepository('AmsEmployeBundle:Employe')->selectComboModeleJournal($session->get("depot_id"),$session->get("flux_id"))),
            'comboTournee' => $this->getCombo($em->getRepository('AmsModeleBundle:ModeleTournee')->selectComboModele($session->get("depot_id"),$session->get("flux_id"))),
            'comboTourneeJour' => $this->getCombo($em->getRepository('AmsModeleBundle:ModeleTourneeJour')->selectComboModele($session->get("depot_id"),$session->get("flux_id"))),
            'comboActivite' => $this->getCombo($em->getRepository('AmsReferentielBundle:RefActivite')->selectCombo()),
            'comboRemplacement' => $this->getCombo($em->getRepository('AmsModeleBundle:Remplacement')->selectComboJournal($session->get("depot_id"),$session->get("flux_id"))),
        ));
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }

    public function listeAction(Request $request) {
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) { return $bVerifAcces; }
        $this->setDerniere_page();
        $session = $this->get('session');

        $this->saveUrl2Session($session,$request,'journal_id');
        $form = $this->initFiltre($session,$request);
        
        return $this->render($this->getTwigListe(), array(
            'isModif' => false,
            'form' => $form->createView(),
            'titre' => $this->titre_page,
            'route' => $this->page_courante_route,
            'depot_id' => $session->get("depot_id"),
            'flux_id' => $session->get("flux_id"),
            'journal_id' => $session->get("journal_id"),
         ));
    }
}
