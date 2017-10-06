<?php

namespace Ams\InvenduBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Ams\SilogBundle\Controller\GlobalController;
use Symfony\Component\HttpFoundation\Request;
use Ams\InvenduBundle\Entity\Invendu;
use Symfony\Component\HttpFoundation\Response;

class InvenduController extends GlobalController {

    public function saisieInvenduAction(Request $request, $saisie) {
        $em = $this->getDoctrine()->getManager();
        $profilId = $this->get('session')->get('PROFIL_ID');
        $utilisateurId = $this->get('session')->get('UTILISATEUR_ID');
        if (!$utilisateurId) {
            return $this->redirect($this->generateUrl('_ams_authentification'));
        }
        $utilisateur = $em->getRepository('AmsSilogBundle:Utilisateur')->find($utilisateurId);
        $grpe_depot = $utilisateur->getGrpdepot();
        $depots = $grpe_depot->getDepots();

        $saisie = 'manuelle-douchette';
        $invendus = array();
        $lieuVente = array();
        $depot = array();
        $typeSaisie = null;
        $lVdateParution = null;
        $depotId = null;
        $lv = null;
        $dateParution = null;
        $lieuV = array();
        foreach ($depots as $d) {
            $codeDepot = $d->getCode();
            $lieuVentes = $em->getRepository('AmsInvenduBundle:Invendu')->selectLieuVente($codeDepot);
            foreach ($lieuVentes as $value) {
                $lieuV[$value['numero']] = $value['libelle'];
            }
        }
        if ($request->isMethod('POST')) {
            $totalLivre = null;
            $totalInvendu = null;
            $depotCode = null;
            if ($request->request->get('type') == "manuelle") {
                $typeSaisie = $request->request->get('saisieInvendu');
                $dateParution = $request->request->get('dateParution');
                $depotId = $request->request->get('depot');
                $lv = $request->request->get('lv');
                $depot = $em->getRepository('AmsSilogBundle:Depot')->find(intval($depotId));
                $depotCode = $depot->getCode();
                $invendus = $em->getRepository('AmsInvenduBundle:Invendu')->selectInvendu($depotCode, $lv, $dateParution);
                $date = new \DateTime($dateParution);
                $lieuVente = $em->getRepository('AmsInvenduBundle:Invendu')->selectLV($lv);
            } else {
                $lVdateParution = $request->request->get('lVdateParution');
                $lv_date = explode(" ", $lVdateParution);
                $dateParution = $lv_date[0];
                $lv = intval($lv_date[1]);
                $date = new \DateTime($dateParution);
                if (isset($lVdateParution)) {
                    $lvNumero = array();
                    foreach ($lieuV as $key => $value) {
                        $lvNumero[] = $key;
                    }
                    if (in_array($lv, $lvNumero)) {
                        $dateParution = $date->format('Y-m-d');
                        $invendus = $em->getRepository('AmsInvenduBundle:Invendu')->selectInvenduDouchette($lv, $dateParution);
                        $lieuVente = $em->getRepository('AmsInvenduBundle:Invendu')->selectLV($lv);
                        foreach ($lieuVente as $value) {
                            $depot = $em->getRepository('AmsSilogBundle:Depot')->findOneByCode($value['code_depot']);
                        }
                    }
                }
            }
            $totalLivreInvendu = $em->getRepository('AmsInvenduBundle:Invendu')->getTotalLivreInvendu($depotCode, $lv, $dateParution);
            $totalLivre = $totalLivreInvendu[0]["nblivree"];
            $totalInvendu = $totalLivreInvendu[0]["nbInvendu"];
            
            return $this->render('AmsInvenduBundle:Invendu:liste_invendu.html.twig', array(
                        'invendus' => $invendus,
                        'lieuVente' => $lieuVente,
                        'depot' => $depot,
                        'date' => $date,
                        'totalInvendu' => $totalInvendu,
                        'totalLivre' => $totalLivre,
            ));
        }
        return $this->render('AmsInvenduBundle:Invendu:index.html.twig', array(
                    'depots' => $depots,
                    'invendus' => $invendus,
                    'lieuVente' => $lieuVente,
                    'depot' => $depot,
                    'typeSaisie' => $typeSaisie,
                    'lVdateParution' => $lVdateParution,
                    'depotId' => $depotId,
                    'lvNum' => $lv,
                    'dateParution' => $dateParution,
                    'saisie' => $saisie,
                    'lieuV' => $lieuV
        ));
    }

    public function saisieNbExInvendusAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        if ($request->isXmlHttpRequest()) {
            $idInvendu = $request->get('idInvendu');
            $nbInvendu = $request->get('nbInvendu');
            $this->misAjourInvendu($em, $idInvendu, $nbInvendu);
            return new Response($nbInvendu);
        }
        if ($request->getMethod() == "POST" && !$request->isXmlHttpRequest()) {
            $tabInv = $request->get('nbInvendu');
            foreach ($tabInv as $id => $nb) {
                if ($nb != "") {
                    $this->misAjourInvendu($em, $id, $nb);
                }
            }
            return $this->redirect($this->generateUrl("saisie_invendu_index"));
        }
    }

    public function changeLvAction() {
        $em = $this->getDoctrine()->getManager();
        $depotID = $this->getRequest()->request->get('depotId');
        $utilisateurId = $this->get('session')->get('UTILISATEUR_ID');
        $utilisateur = $em->getRepository('AmsSilogBundle:Utilisateur')->find($utilisateurId);
        $lv = array();
        if ($depotID != "") {
            $depot = $em->getRepository('AmsSilogBundle:Depot')->find($depotID);
            $codeDepot = $depot->getCode();
            $lieuVentes = $em->getRepository('AmsInvenduBundle:Invendu')->selectLieuVente($codeDepot);
            if (count($lieuVentes) > 0) {
                foreach ($lieuVentes as $value) {
                    $lv[$value['numero']] = $value['libelle'];
                }
            }
        } else {
            $grpe_depot = $utilisateur->getGrpdepot();
            $depots = $grpe_depot->getDepots();
            foreach ($depots as $depot) {
                $codeDepot = $depot->getCode();
                $lieuVentes = $em->getRepository('AmsInvenduBundle:Invendu')->selectLieuVente($codeDepot);
                foreach ($lieuVentes as $value) {
                    $lv[$value['numero']] = $value['libelle'];
                }
            }
        }
        $response = new Response(json_encode($lv));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function misAjourInvendu($em, $id, $nb) {
        $em->getRepository('AmsInvenduBundle:Invendu')->updateInvendu($nb, $id);
    }
}
