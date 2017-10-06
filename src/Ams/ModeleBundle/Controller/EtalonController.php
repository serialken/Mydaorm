<?php
/* test*/
namespace Ams\ModeleBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use HTML2PDF;
use Ams\ModeleBundle\Controller\GlobalModeleController;

class EtalonController extends GlobalModeleController {

    public function getRepositoryName() {
        return $this->getBundleName() . ':Etalon';
    }

    public function getRoute() {
        return 'liste_etalon';
    }

    public function gridAction() {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $isValider = $this->isPageElement('VALID', 'liste_etalon_tournee');
        $response = $this->renderView($this->getTwigGrid(), array(
            'isModif' => $this->getIsModif(),
            'isValid' => $isValider,
            'curseur' => $this->getRepository()->select($session->get("depot_id"), $session->get("flux_id")),
            'comboType' => $this->getCombo($em->getRepository('AmsReferentielBundle:RefTypeEtalon')->selectCombo()),
            'comboUtilisateur' => $this->getCombo($em->getRepository('AmsSilogBundle:Utilisateur')->selectCombo()),
            'comboEmploye' => $this->getCombo($em->getRepository('AmsEmployeBundle:Employe')->selectComboEtalon($session->get("depot_id"), $session->get("flux_id"))),
            'comboTournee' => $this->getCombo($em->getRepository('AmsModeleBundle:ModeleTournee')->selectComboModele($session->get("depot_id"), $session->get("flux_id")))
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
        $em = $this->getDoctrine()->getManager();

        $this->saveUrl2Session($session, $request, 'etalon_id');
        $form = $this->initFiltre($session, $request);
        return $this->render($this->getTwigListe(), array(
                    'isModif' => $this->getIsModif(),
                    'form' => $form->createView(),
                    'route' => $this->page_courante_route,
                    'titre' => $this->titre_page,
                    'repository' => $this->getRepositoryName(),
                    'etalon_id' => $session->get("etalon_id")
        ));
    }

    public function ajaxSauvegarderAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $_etalon_id = $session->get("etalon_id");
        if (!(isset($_etalon_id)) || $_etalon_id == 0) {
            $em->getRepository('AmsModeleBundle:Etalon')->insert($msg, $msgException, array(
                "depot_id" => $session->get('depot_id'),
                "flux_id" => $session->get('flux_id'),
                "type_id" => $request->query->get('type_id'),
                "employe_id" => $request->query->get('employe_id'),
                "commentaire" => $request->query->get('commentaire'),
                "date_application" => $request->query->get('date_application'),
                "date_requete" => $request->query->get('date_requete'),
                    ), $session->get('UTILISATEUR_ID'), $_etalon_id);
            $session->set('etalon_id', $_etalon_id);
        } else {
            $em->getRepository('AmsModeleBundle:Etalon')->update($msg, $msgException, array(
                "type_id" => $request->query->get('type_id'),
                "employe_id" => $request->query->get('employe_id'),
                "commentaire" => $request->query->get('commentaire'),
                "date_application" => $request->query->get('date_application'),
                "date_requete" => $request->query->get('date_requete'),
                    ), $session->get('UTILISATEUR_ID'), $_etalon_id);
        }
        return $this->ajaxResponse($msg, $msgException);
    }

    public function exportAnnexeAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $entete=$em->getRepository('AmsModeleBundle:Etalon')->getAnnexeEntete($request->get('etalon_id'));
        $html = $this->renderView('AmsModeleBundle:Etalon:annexe.html.twig', array(
            'entete' => $entete,
            'tournees' => $em->getRepository('AmsModeleBundle:Etalon')->getAnnexeTournees($request->get('etalon_id')),
            'reference' => $em->getRepository('AmsModeleBundle:Etalon')->getAnnexeReference($request->get('etalon_id')),
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
