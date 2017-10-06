<?php

namespace Ams\DistributionBundle\Controller;

use Ams\ModeleBundle\Controller\GlobalModeleController;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Ams\DistributionBundle\Form\ParutionSpecialeType;
use Ams\DistributionBundle\Entity\ParutionSpeciale;
use Ams\ExtensionBundle\Validator\Constraints\DatePosterieure;
use Symfony\Component\HttpFoundation\Request;
use Ams\SilogBundle\Controller\GlobalController;

/**
 * Calendrier  controller.
 *
 */
class ParutionSpecialeController extends GlobalModeleController {

    function calendrierAction() {
        $em = $this->getDoctrine();
        $feries = $em->getRepository('AmsDistributionBundle:JourFerie')->findByActif(1);
        $feries = serialize($feries);
        $evenements = $em->getRepository('AmsReferentielBundle:RefEvenement')->findAll();
        $parutions = $em->getRepository('AmsDistributionBundle:ParutionSpeciale')->getParutions();
        return $this->render('AmsDistributionBundle:ParutionSpeciale:calendrier.html.twig', array(
                    'Jferies' => json_encode($feries),
                    'parutions' => $parutions,
                    'evenements' => $evenements,
        ));
    }

    /** grid xml */
    public function gridAction(Request $request) {
        $this->init();
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $acces_data_grid=false;
        $page_accessibles = $this->getDoctrine()->getManager()->getRepository('AmsSilogBundle:PageElement')
                ->getElementAcessible($this->srv_session->get('PROFIL_ID'), "calendrier_operation_special_add");
        if($page_accessibles){
            $acces_data_grid=true;
        }
        

        if (isset($_GET['id']) && $_GET['id'] > 0) { //clique sur un evenement 
            $id = $_GET['id'];
            $event = $em->getRepository('AmsDistributionBundle:ParutionSpeciale')->find($id);
            $parutions = $em->getRepository('AmsDistributionBundle:ParutionSpeciale')->getParutionsByDate($event->getDateParution());
        } elseif (isset($_GET['date_debut']) && isset($_GET['date_fin'])) {
            $produit = NULL;
            if ($_GET['produit_id'] > 0)
                $produit = $em->getRepository('AmsProduitBundle:Produit')->findOneById($_GET['produit_id']);
            $parutions = $em->getRepository('AmsDistributionBundle:ParutionSpeciale')->getParutionsBetweenDate($_GET['date_debut'], $_GET['date_fin'], $produit);
        }

        else {

            $debut = new \DateTime($_GET['date_debut']['date']);
            $parutions = $em->getRepository('AmsDistributionBundle:ParutionSpeciale')->getParutionsByDate($debut->format('Y-m-d'));
        }

        $evenementList = $em->getRepository('AmsReferentielBundle:RefEvenement')->findAll();
        $comboEvenement = $this->getOptionList($evenementList, true);

        $produitList = $em->getRepository('AmsProduitBundle:Produit')->findAll();
        $comboProduit = $this->getOptionList($produitList, true);

        $response = $this->renderView('AmsDistributionBundle:ParutionSpeciale:grid.xml.twig', array(
            'parutions' => $parutions,
            'comboEvenement' => $comboEvenement,
            'comboProduit' => $comboProduit,
            'isModif' => 1,
            'acces_data_grid'=>$acces_data_grid
        ));
        return new Response($response, 200, array('Content-Type' => 'text/xml'));
    }

    public function listeAction() {
        $this->init();
        if (!$this->verif_acces())
            return $bVerifAcces;
        
        $acces=false;
        $page_accessibles = $this->getDoctrine()->getManager()->getRepository('AmsSilogBundle:PageElement')
                ->getElementAcessible($this->srv_session->get('PROFIL_ID'), "calendrier_operation_special_add");
        if($page_accessibles){
            $acces=true;
        }

        $em = $this->getDoctrine()->getManager();

        $produit_id = '';
        $debut = new \DateTime();
        $fin = '';
        $id = '';

        if (isset($_GET['id']) && ($_GET['id'] > 0)) { // clique sur un évenement
            $id = $_GET['id'];
            $parution = $em->getRepository('AmsDistributionBundle:ParutionSpeciale')->find($id);
            $debut = $parution->getDateParution();
        }

        if (isset($_GET['date_debut']) && ($_GET['date_debut'] != '')) {
            $debut = new \DateTime($_GET['date_debut']);
            $fin = new \DateTime($_GET['date_debut']);
        }


        $form = $this->createFormBuilder(array('date' => null), array('csrf_protection' => false))
                ->add('produit', 'entity', array(
                    'class' => 'AmsProduitBundle:Produit',
                    'required' => false,
                    'property' => 'libelle',
                    'empty_value' => 'Choisissez un produit',
                ))
                ->add('date_debut', 'date', array(
                    'widget' => 'single_text',
                    'input' => 'datetime',
                    'attr' => array('class' => 'date'),
                    'data' => $debut
                ))
                ->add('date_fin', 'date', array(
                    'widget' => 'single_text',
                    'input' => 'datetime',
                    'attr' => array('class' => 'date'),
                    'data' => $debut
                ))
                ->getForm();

        // Traitement du formulaire
        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->get('produit')->getData() != NULL)
                $produit_id = $_POST['form']['produit'];
            else
                $produit_id = 0;
            $debut = $_POST['form']['date_debut'];
            $fin = $_POST['form']['date_fin'];
        }

        return $this->render('AmsDistributionBundle:ParutionSpeciale:liste.html.twig', array(
                    'date_debut' => $debut,
                    'date_fin' => $fin,
                    'id' => $id,
                    'produit_id' => $produit_id,
                    'repository' => 'AmsDistributionBundle:ParutionSpeciale',
                    'route' => $this->page_courante_route,
                    'isModif' => 1,
                    'form' => $form->createView(),
                    'acces' => $acces
        ));
    }

    public function AddAction() {
        $bVerifAcces = null;
        if (!$this->getEltsAccessible())
             return $this->render('AmsDistributionBundle:ParutionSpeciale:erreur.html.twig');
        
        $em = $this->getDoctrine()->getManager();
        $parution = new ParutionSpeciale();
        if(isset($_GET['date_parution']))
            $dateParution = $debut = new \DateTime($_GET['date_parution']);
        else $dateParution = new \DateTime();
        $parution->setDateParution($dateParution);
        $form = $this->createForm(new ParutionSpecialeType, $parution);
        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->persist($parution);
                $em->flush();
                $parution = new ParutionSpeciale();
                $formParutionSpeciale = $this->createForm(new ParutionSpecialeType(), $parution);
                $alert = $this->renderView('::modalAlerte.html.twig', array('type' => 'success', 'message' => '<strong>Succès!</strong> La parution a bien été ajoutée!'));
            } else {
                $alert = $this->renderView('::modalAlerte.html.twig', array('type' => 'danger', 'message' => '<strong>Attention!</strong> Une erreur est survenue!'));
            }
            $modal = $this->renderView('AmsDistributionBundle:ParutionSpeciale:form_ajout.html.twig', array('form' => $form->createView()));

            $response = array("modal" => $modal, "alert" => $alert);
            $return = json_encode($response);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        }
        return $this->render('AmsDistributionBundle:ParutionSpeciale:form_ajout.html.twig', array('form' => $form->createView(), 'bVerifAcces'=>$bVerifAcces));
    }

    public function UpdateAndDeleteAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $mode = $request->get('!nativeeditor_status');
        $rowId = $request->get('gr_id');
        $newId = '';
        $action = '';
        $msg = '';
        $msgException = '';
        $result = true;
        if ($mode == 'deleted') {
            try {
                $parution = $em->getRepository('AmsDistributionBundle:ParutionSpeciale')->find($rowId);
                $em->remove($parution);
                $em->flush();
            } catch (Exception $e) {
                $result = '';
                $msgException = "Erreur lors de la suppression" . $e->getMessage();
            }
        }
        if ($mode == 'updated') {
            try {
                $em->getRepository('AmsDistributionBundle:ParutionSpeciale')->update($_GET);
            } catch (Exception $e) {
                $result = '';
                $msgException = "Erreur lors de la mise à jour" . $e->getMessage();
            }
        }

        if (!$result) {
            $action = "error";
            $response = $this->render('::grid_action_error.html.twig', array('action' => $action, 'rowId' => $rowId, 'newId' => $newId, 'msg' => $msg, 'msg_complet' => $msgException));
        } else
            $response = $this->render('::grid_action.html.twig', array('action' => $action, 'rowId' => $rowId, 'newId' => $newId, 'msg' => $msg));

        $response->headers->set('Content-Type', 'text/xml');
        return $response;
    }

}

//Controller