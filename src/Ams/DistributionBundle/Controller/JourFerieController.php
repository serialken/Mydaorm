<?php

namespace Ams\DistributionBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ams\SilogBundle\Controller\GlobalController;
use APY\DataGridBundle\Grid\Column\ActionsColumn;
use APY\DataGridBundle\Grid\Action\RowAction;
use Ams\DistributionBundle\Form\JourFerieType;
use Ams\DistributionBundle\Entity\JourFerie;

/**
 * Description of JourFerieController
 *
 * @author DDEMESSENCE
 */
class JourFerieController extends GlobalController {

    /**
    *  Affiche la grid des jours fériés 
    */
    public function gridFerieAction() {
        //$session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        // on récupère la liste des jours fériés
        $listeJours = $em->getRepository('AmsDistributionBundle:JourFerie')->findAll();
        //var_dump($listeJours);
        //exit();
        $response = $this->renderView('AmsDistributionBundle:JourFerie:gridFerie.xml.twig', array('liste' => $listeJours));
        return new Response($response, 200, array('Content-Type' => 'application/xml')); 
    }
    
    /**
     * Affiche la liste des jours feriés
     * 
    */
    public function listeFerieAction() {
        // verifie si on a droit a acceder a cette page
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
        $em = $this->getDoctrine()->getManager();
        $listeJours = $em->getRepository('AmsDistributionBundle:JourFerie')->findAll();
        if (count($listeJours) > 0)
        {
            $aff = true;
        } else {
            $aff = false;
        }
       return $this->render('AmsDistributionBundle:JourFerie:listeFerie.html.twig', array('affiche' => $aff));
    }

    public function AjoutAction() {

        $em = $this->getDoctrine()->getManager();
        $jour = new JourFerie();
        $form = $this->createForm(new JourFerieType, $jour);

        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                // a la creation le jour est désactivé
                $jour->setActif(1);
                $em->persist($jour);
                $em->flush();
                return $this->redirect($this->generateUrl('feries_index'));
            }

            $alert = $this->renderView('::modalAlerte.html.twig', array('type' => 'danger', 'message' => '<strong>Attention!</strong> Une erreur est survenue!'));
            $modal = $this->renderView('AmsDistributionBundle:JourFerie:form_ajout.html.twig', array('form' => $form->createView()));

            $response = array("modal" => $modal, "alert" => $alert);
            $return = json_encode($response);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        }

        return $this->render('AmsDistributionBundle:JourFerie:form_ajout.html.twig', array('form' => $form->createView()));
    }

     public function changeAction($id) {

        $em = $this->getDoctrine()->getManager();
        $jour = $em->getRepository('AmsDistributionBundle:JourFerie')->find($id);
        if (count($jour) > 0)
        {
            $etat = "success";
            $msg = "Vos modifications ont été enregistrées avec succés.";
        } else {
            $etat = "warning";
            $msg = "Attention une erreur est survenu lors de l'activation du jour.";
        }
        $jour->bascule();
        $em->persist($jour);
        $em->flush();
        $response = array('msg' => $msg, 'etat' => $etat);
        $return = json_encode($response);
        return new Response($return, 200, array('Content-Type' => 'Application/json'));
    }
    
    public function SupprimerAction($id) {

        $bVerifAcces = $this->verif_acces();

        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
        
        $em = $this->getDoctrine()->getManager();
        $jour = $em->getRepository('AmsDistributionBundle:JourFerie')->find($id);
        
        if (!$jour) {
            throw $this->createNotFoundException("Le jour férié n'existe pas");
        }
       
        $em->remove($jour);
        $em->flush();
        return $this->redirect($this->generateUrl('feries_index'));
    }
    
   

}
