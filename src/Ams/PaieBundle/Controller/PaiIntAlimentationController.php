<?php
namespace Ams\PaieBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Ams\PaieBundle\Controller\GlobalPaiController;


class PaiIntAlimentationController extends GlobalPaiController {

    public function getRoute() { return 'liste_pai_int_alimentation'; }

    public function listeAction(Request $request) {
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
        $this->setDerniere_page();
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $this->saveUrl2Session($session,$request,'');
        $form = $this->initFiltreFlux($session,$request);

        return $this->render("AmsPaieBundle:PaiIntAlimentation:index.html.twig", array(
            'titre' => $this->titre_page,
            'route' => $this->page_courante_route,
            'min_date_distrib' => $em->getRepository('AmsPaieBundle:PaiMois')->getDateDebut($session->get("flux_id")),
            'date_distrib' => date_format(new \DateTime(), 'd/m/Y'),
            'comboDepot' => $this->getComboHtmlFromArray($session->get('DEPOTS'),true),
            'comboFlux' => $this->getComboHtmlFromArray($session->get('FLUXS'),true),
         ));
    }
    
    protected function getIsPrivilege($droit) {
        $isPrivilegie = $this->isPageElement($droit, $this->getRoute());

        return ($isPrivilegie);
    }
    
    public function ajaxAlimenterAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $idtrt = null;
        $em->getRepository('AmsPaieBundle:PaiIntTraitement')->alimenterPaie($msg, $msgException, $idtrt, $session->get('UTILISATEUR_ID')
                , $request->get('date_distrib')
                , $request->get('date_org')
                , $request->get('depot_id')
                , $request->get('flux_id')
                , $request->get('alim_tournee')
                , $request->get('maz_duree_attente')
                , $request->get('maz_duree_retard')
                , $request->get('maz_nbkm_paye_tournee')
                , $request->get('alim_activite_presse')
                , $request->get('maz_nbkm_paye_activite_presse')
                , $request->get('maz_duree_activite_horspresse')
                , $request->get('maz_nbkm_paye_activite_horspresse')
                );
        return $this->ajaxResponse($msg, $msgException);
    }
}
