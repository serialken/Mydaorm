<?php
namespace Ams\PaieBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Ams\PaieBundle\Controller\GlobalPaiController;


class PaiMensuelController extends GlobalPaiController {

    public function getRoute() { return 'liste_pai_mensuel'; }

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

        return $this->render("AmsPaieBundle:PaiMensuel:index.html.twig", array(
            'isEnCours' => $em->getRepository('AmsPaieBundle:PaiMois')->isDateInMoisCourant(date('Y-m-d'), $session->get("flux_id"))>0
                            || $em->getRepository('AmsPaieBundle:PaiMois')->getDateFin($session->get("flux_id"))==date('d/m/Y') ,
            'isModif' => $this->getIsPrivilege("MODIF"),
            'isBlocage' => $this->getIsPrivilege("BLOCAGE"),
            'isCloture' => $this->getIsPrivilege("CLOTURE"),
            'form' => $form->createView(),
            'titre' => $this->titre_page,
            'route' => $this->page_courante_route,
            'anneemois' => $em->getRepository('AmsPaieBundle:PaiMois')->getLibelle($session->get("flux_id")),
            'paie_en_cours' => $em->getRepository('AmsPaieBundle:PaiIntTraitement')->paie_en_cours(),
            'blocage' => $em->getRepository('AmsPaieBundle:PaiMois')->getBlocage($session->get("flux_id")),
            'blocage_reclamation' => $em->getRepository('AmsPaieBundle:PaiMois')->getBlocageReclamation($session->get("flux_id")),
            'flux' => ($session->get("flux_id")==1?'Nuit':'Jour'),
         ));
    }
    
    protected function getIsPrivilege($droit) {
        $isPrivilegie = $this->isPageElement($droit, $this->getRoute());

        return ($isPrivilegie);
    }
    
    public function ajaxGenererAction(Request $request) {
        $session = $this->get('session');
        $sCmd = 'php '.$this->get('kernel')->getRootDir(). '/console '
            . 'generation_mensuel '
            . ' --env=' . $this->get('kernel')->getEnvironment()
            .' '.$session->get("flux_id")
            .' '.$session->get('UTILISATEUR_ID')
            .' '.$request->get("alim_employe")
            .' '.$request->get("alim_octime")
            .' '.$request->get("alim_pleiades");
        $this->bgCommandProxy($sCmd);
        return $this->ajaxResponse("", "");
    }
    
    public function ajaxCloturerAction(Request $request) {
/*        $sCmd = 'php '.$this->get('kernel')->getRootDir(). '/console '
            . 'generation_cloture '
            . ' --env=' . $this->get('kernel')->getEnvironment()
            .' '.$session->get("flux_id")
            .' '.$session->get('UTILISATEUR_ID');*/
        $sCmd = 'php '.$this->get('kernel')->getRootDir(). '/console '
            . 'generation_cloture '.$this->get('session')->get("flux_id").' '.$this->get('session')->get('UTILISATEUR_ID')
            . ' --env ' . $this->get('kernel')->getEnvironment();
        $this->bgCommandProxy($sCmd);
        return $this->ajaxResponse("", "");
    }    
    
    public function ajaxBloquerAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $idtrt = null;
        $em->getRepository('AmsPaieBundle:PaiMois')->bloquer($msg, $msgException, $session->get('UTILISATEUR_ID'), $this->get('session')->get("flux_id"));
        return $this->ajaxResponse($msg, $msgException);
    }
    
    public function ajaxDebloquerAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $idtrt = null;
        $em->getRepository('AmsPaieBundle:PaiMois')->debloquer($msg, $msgException, $session->get('UTILISATEUR_ID'), $this->get('session')->get("flux_id"));
        return $this->ajaxResponse($msg, $msgException);
    }
    
    public function ajaxBloquerReclamationAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $idtrt = null;
        $em->getRepository('AmsPaieBundle:PaiMois')->bloquerReclamation($msg, $msgException, $session->get('UTILISATEUR_ID'), $this->get('session')->get("flux_id"));
        return $this->ajaxResponse($msg, $msgException);
    }

}

