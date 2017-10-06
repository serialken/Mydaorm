<?php

namespace Ams\PaieBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ams\PaieBundle\Controller\GlobalPaiController;

class PaiAnnexeController extends GlobalPaiController {

    public function gridAction() {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $response = $this->renderView('AmsPaieBundle:PaiAnnexe:grid.xml.twig', array(
            'curseur' => $em->getRepository('AmsPaieBundle:PaiTournee')->getAnnexeDetail($session->get("employe_depot_hst_id")),
            'flux_id' => $session->get("flux_id"),
        ));
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }

    public function gridResumeAction() {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $response = $this->renderView('AmsPaieBundle:PaiAnnexe:grid_resume.xml.twig', array(
            'curseur' => $em->getRepository('AmsPaieBundle:PaiTournee')->getAnnexeEv($session->get("employe_depot_hst_id")),
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

        $this->saveUrl2Session($session, $request, 'employe_depot_hst_id');
        $form = $this->initFiltreAnnexe($session, $request);

        $employe = $em->getRepository('AmsPaieBundle:PaiTournee')->getAnnexeEmploye($session->get("employe_depot_hst_id"));
        return $this->render('AmsPaieBundle:PaiAnnexe:liste.html.twig', array(
            'form' => $form->createView(),
            'titre' => $this->titre_page,
            'route' => $this->page_courante_route,
            'isModif' => false,
            'grid_route' => "grid_pai_annexe",
            'periode' => (isset($employe[0]) ? 'du ' . $employe[0]['date_debut'] . ' au ' . $employe[0]['date_fin'] : ''),
            'date_calcul' => (isset($employe[0]) ? "Calculé le  ".$employe[0]['date_calcul'] : ''),
            'nbabo' => (isset($employe[0]) ? ($employe[0]['societe_id']==1?"Nb abonnés semaine : ":"Nb clients : ").$employe[0]['nbabo'] : ''),
            'taux_qualite' => (isset($employe[0]) ? "Taux de qualité ".($employe[0]['societe_id']==1?"semaine ":'').": ".$employe[0]['taux_qualite'] : ''),
            'nbabo_DF' => (isset($employe[0]) && $employe[0]['societe_id']==1 ? "Nb clients DF : ".$employe[0]['nbabo_DF'] : ''),
            'taux_qualite_DF' => (isset($employe[0]) && $employe[0]['societe_id']==1 ? "Taux de qualité DF : ".$employe[0]['taux_qualite_DF'] : ''),
            'provisoire' => (isset($employe[0]) && $employe[0]['provisoire'] ? 'provisoire' : ''),
        ));
    }

    public function exportAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $response = $this->get('ams.pai.annexe_paie')->getPDF($session->get("depot_id"), $session->get("flux_id"), $session->get("employe_depot_hst_id"), $session->get("employe_depot_hst_id") < 0);
        return new Response($response, 200, array('Content-Type' => 'Application/pdf'));
    }

    public function listeAnnexeAction(Request $request) {
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
        $this->setDerniere_page();
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        if ($request->getMethod() == 'POST') {
            $depot_id = $request->request->get('form_filtre')['depot_id'];
            $flux_id = $request->request->get('form_filtre')['flux_id'];
        } else {
            $depot_id = $session->get("depot_id");
            $flux_id = $session->get("flux_id");
        }
        $form = $this->initFiltreDepotFlux($session, $request);

        $depot = $em->getRepository('AmsSilogBundle:Depot')->findOneById($depot_id);
        $liste = array();
        // Répertoire des commission à faire en premier à cause de la suppression des doublons
        $dir = $this->container->getParameter('REP_COMMISSION_VCP') . $depot->getCode() . '/';
        $this->extractAttestationCommission($request, $dir, $depot_id, $flux_id, $liste);
        $dir = $this->container->getParameter('REP_ANNEXE_PAIE') . $depot->getCode() . '/';
        $this->extractAnnexePaie($request, $dir, $depot_id, $flux_id, $liste);

        // Obtient une liste de colonnes
        foreach ($liste as $key => $row) {
            $fin[$key] = $row['fin'];
            $debut[$key] = $row['debut'];
        }
        // Trie les données par fin décroissant, debut croissant
        // Ajoute $data en tant que dernier paramètre, pour trier par la clé commune
        if (count($liste) > 0) {
            array_multisort($fin, SORT_DESC, $debut, SORT_ASC, $liste);
        }

        return $this->render('AmsPaieBundle:PaiAnnexe:liste_annexe.html.twig', array(
            'liste' => $liste,
            'form' => $form->createView(),
            'isModif' => false,
            'titre' => $this->titre_page,
            'route' => $this->page_courante_route,
                )
        );
    }

    public function downloadAction(Request $request) {
        $filename = $request->get('file');
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $depot = $em->getRepository('AmsSilogBundle:Depot')->findOneById($session->get("depot_id"));
        if ($request->get('type') == 'Annexe') {
            $dir = $dir = $this->container->getParameter('REP_ANNEXE_PAIE') . $depot->getCode() . '/';
        } else {
            $dir = $dir = $this->container->getParameter('REP_COMMISSION_VCP') . $depot->getCode() . '/';
        }
        return $this->downloadFile($dir, $filename);
    }

    protected function extractAnnexePaie(Request $request, $dir, $depot_id, $flux_id, &$liste) {
        $doted = array(".", "..");
        if (is_dir($dir)) {
            $files = array_diff(scandir($dir), $doted);
            foreach ($files as $file) {
                if (is_file($dir . $file)) {
                    $fileInfos = array();
                    $tmp = explode('_', $file);
                    if ($tmp[2] == $depot_id && $tmp[3] == $flux_id) {
                        $fileInfos["societe"] = $tmp[0];
                        $fileInfos["debut"] = $tmp[4];
                        $fileInfos["fin"] = $tmp[6];
                        $fileInfos["type"] = 'Annexe';
                        $fileInfos["libelle"] = 'Annexe Paie';
                        $fileInfos["file"] = $file;
                        $liste[] = $fileInfos;
                    }
                }
            }
        }
    }

    protected function extractAttestationCommission(Request $request, $dir, $depot_id, $flux_id, &$liste) {
        $em = $this->getDoctrine()->getManager();
        $doted = array(".", "..");
        if (is_dir($dir)) {
            $files = array_diff(scandir($dir), $doted);
            foreach ($files as $file) {
                if (is_file($dir . $file)) {
                    $fileInfos = array();
                    $tmp = explode('_', $file);
                    $d = \DateTime::createFromFormat('Ymd', $tmp[0])->format('Y-m-d');
                    $mois = $em->getRepository('AmsPaieBundle:PaiRefMois')->getAnneemoisByDate($d);

                    $fileInfos["societe"] = 'VCP';
                    $fileInfos["debut"] = $mois['date_debut'];
                    $fileInfos["fin"] = $mois['date_fin'];
                    $fileInfos["libelle"] = 'Attestation de Commission';
                    $fileInfos["type"] = 'Commission';
                    $fileInfos["file"] = $file;
                    $liste[] = $fileInfos;
                }
            }

            // Obtient une liste de colonnes
            foreach ($liste as $key => $row) {
                $fin[$key] = $row['file'];
            }
            // Trie les données par fin décroissant, debut croissant
            // Ajoute $data en tant que dernier paramètre, pour trier par la clé commune
            if (count($liste) > 0) {
                array_multisort($fin, SORT_DESC, $liste);
            }
            // On supprime les doublons
            $date_debut = '';
            foreach ($liste as $key => $row) {
                if ($date_debut == $row['debut']) {
                    unset($liste[$key]);
                } else {
                    $date_debut = $row['debut'];
                }
            }
        }
    }

}
