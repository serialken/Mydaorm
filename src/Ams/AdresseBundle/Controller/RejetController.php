<?php

namespace Ams\AdresseBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ams\SilogBundle\Controller\GlobalController;
use Ams\AdresseBundle\Entity\AdresseRnvp;
use Ams\AdresseBundle\Form\AdresseType;
use Ams\AdresseBundle\Entity\Adresse;

/**
 * Gesttion des rejets d'adresse
 *
 */
class RejetController extends GlobalController {

    public function getRepositoryName() {
        return 'AmsAdresseBundle:Adresse';
    }

    public function getRouteName() {
        return "adresse_liste_rejet";
    }

    /**
     *  Affiche la grid des adresses rejetees 
     */
    public function gridAction() {

        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        // on récupère la liste des adresse rejetés qui sont sur les dépôts de l'utilisateur connecté
        $listeRejet = $em->getRepository('AmsAdresseBundle:Adresse')->getListeRejets(array_keys($session->get('DEPOTS')));
//        var_dump(count($listeRejet));
//        exit();
        $response = $this->renderView('AmsAdresseBundle:Rejet:grid.xml.twig', array(
            'liste' => $listeRejet,
            'depots' => $session->get('DEPOTS'))
        );
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }

    /**
     * Affiche la liste des adresse rejetees
     * Rejet RNVP et rejet GeoCodage
     */
    public function listeAction(Request $request) {
        return $this->render('AmsAdresseBundle:Rejet:liste.html.twig', array(
                    'route' => $this->getRouteName(),
                    'repository' => $this->getRepositoryName())
        );
    }

    /**
     * Edit d'une adresse 
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function modifAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $session = $this->get('session');
        $ResRNVP = NULL;

        if ($request->get('adresseId') > 0) {
            $adresseId = $request->get('adresseId');
            $session->set('adresseId', $request->get('adresseId'));
        } else {
            $adresseId = $session->get('adresseId');
        }

        $typeRejet = '';
        if ($request->get('type_rejet') != '') {
            $typeRejet = $request->get('type_rejet');
            $session->set('type_rejet', $request->get('type_rejet'));
        } else {
            $typeRejet = $session->get('type_rejet');
        }

        $adresse = $em->getRepository('AmsAdresseBundle:Adresse')->findOneById($adresseId);
        $adresseInitiale = clone($adresse);
        $param = array(
            'vol3' => $adresse->getVol3(),
            'vol4' => $adresse->getVol4(),
            'vol5' => $adresse->getVol5(),
            'cp' => $adresse->getCp(),
            'ville' => $adresse->getVille()
        );

        $listeAbonnes = $em->getRepository('AmsAdresseBundle:Adresse')->getListeAbonneByAdresse($param);

        // on met en session les adresse à mettre à jour
        $listeAdresseIds = array();
        foreach ($listeAbonnes as $listeAbonne) {
            $listeAdresseIds[] = $listeAbonne->getId();
        }

        $session->set('listeAdresseIds', $listeAdresseIds);
        $msg = null;
        $etat = "alert";
        $form = $this->createForm(new AdresseType(), $adresse);
        $form->handleRequest($request);

        if ($form->isValid()) {     
            $rnvp = $this->get('AdresseRnvp');
            $ResRNVP = $rnvp->normaliseAdresse($adresse, true, false);
            $session->set('resultatRnvp', $ResRNVP);
            //            die(var_dump($ResRNVP));
            if ($ResRNVP->etatRetourRnvp != 'RNVP_OK') {
                $msg = "Attention le RNVP n'a pas bien fonctionné [ " . $ResRNVP->etatRetourRnvp . " ] ! Voici l'adresse proposée:";
                $etat = "alert-warning";
            } else {
                $msg = "Le RNVP a bien fonctionné, vous pouvez passer au géocodage!";
                $etat = "alert-success";
            }
        }

        $modal = $this->renderView('AmsAdresseBundle:Rejet:modif.html.twig', array(
            'form' => $form->createView(),
            'ResRNVP' => $ResRNVP,
            'message' => $msg,
            'etat' => $etat,
            'listeAbonnes' => $listeAbonnes,
            'adresseInitiale' => $adresseInitiale
        ));

        return new Response($modal, 200);
    }

    /**
     * 
     * @param request
     * 
     * Liste adresses geocodees creation et/ou recuperation rnvp_id
     * mise à jour de l'adresse editeur
     */
    public function adresseGeocodeAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $rnvp = $this->get('AdresseRnvp');

        $resultatRnvp = $session->get('resultatRnvp'); // retour rnvp
        $listeAdresseIds = $session->get('listeAdresseIds'); //liste des adresse
        $typeRejet = $session->get('type_rejet'); //type du rejet
        $utilisateur = $em->getRepository('AmsSilogBundle:Utilisateur')->findOneById($session->get('UTILISATEUR_ID'));

        $adresseRnvp = new AdresseRnvp();
        $ResRNVP1 = $rnvp->insertRnvp($resultatRnvp, $utilisateur); // création et/ou recupération rnvp_id 
        $adresseRnvp = $em->getRepository('AmsAdresseBundle:AdresseRnvp')->findOneById($ResRNVP1);
        $srvGeocodage = $this->get('geocodage');
        $param = array(
            "City" => $resultatRnvp->po_ville,
            "PostalCode" => $resultatRnvp->po_cp,
            "AddressLine" => $resultatRnvp->pio_adresse
        );
        
        
        // si on  l'utilisateur modifie le formulaire de geocodage 
        $adresse = new Adresse();  
        $adresse->setVol4($adresseRnvp->getAdresse());
        $adresse->setVille($adresseRnvp->getVille());
        $adresse->setCp($adresseRnvp->getCp()); 
        $form = $this->createForm(new AdresseType(), $adresse);
        $form->handleRequest($request);
        if ($form->isValid()) {     
            $param = array(
                "City" => $adresse->getVille(),
                "PostalCode" => $adresse->getCp(),
                "AddressLine" => $adresse->getVol4()
            );
        }
        
       
    
        $listeAdrGeoconcept = $srvGeocodage->geocode($param, 'GEOCONCEPT'); //liste des adresses retournees par geoconcept
        $listeAdrGoogle = $srvGeocodage->geocode($param, 'GOOGLE'); //liste des adresses retournees par geoconcept
        if($listeAdrGoogle['GeocodeEtat'] == "AUCUNE_PROPOSITION")
            $listeAdrGoogle = array();
        
 
  
        if (($request->isXmlHttpRequest()) && ($request->request->get('geox') > 0)) {
          
            if ($adresseRnvp) {
                $adresseRnvp->setGeox($request->request->get('geox'));
                $adresseRnvp->setGeoy($request->request->get('geoy'));
                $adresseRnvp->setGeoScore($request->request->get('geoscore'));
                $adresseRnvp->setGeoType($request->request->get('geotype'));
                $adresseRnvp->setStopLivraisonPossible('1');
                $adresseRnvp->setTypeRnvp(1);
                $adresseRnvp->setGeoEtat(2);
                $adresseRnvp->setUtilisateurModif($utilisateur);
                $date = new \DateTime();
                $adresseRnvp->setDateModif($date);
                $em->persist($adresseRnvp);
                try {
                    $em->flush();
                } catch (\Doctrine\DBAL\DBALException $e) {
                    
                }
                // mise à jour du point de livraison apres le geocodage avec par defaut le rnvp_id comme point de livraison
                $adresseUpdatePtLivraison = $em->getRepository('AmsAdresseBundle:Adresse')->updatePointLivraison($session->get('UTILISATEUR_ID'), $ResRNVP1, $listeAdresseIds);
                if ($typeRejet == 'RNVP') {
                    // mise à jour de l'adresse editeur de l'adresse dans crm_detail
                    foreach ($listeAdresseIds as $adresseId) {
                        $rnvp->miseAJourAdresse($adresseId, $resultatRnvp, $adresseRnvp->getId(), $traitementRejet = true, $session->get('UTILISATEUR_ID'));
                        $em->getRepository('AmsDistributionBundle:CrmDetail')->updateCrmAdresse($adresseId);
                    }
                }
                foreach ($listeAdresseIds as $adresseId) {
                    $em->getRepository('AmsAdresseBundle:Adresse')->updateRnvpId($utilisateur->getId(), $adresseRnvp->getId(), $adresseId);
                }
              
            }
            exit();
        }
    
        $modal = $this->renderView('AmsAdresseBundle:Rejet:adresse_geocode.html.twig', array(
            'form' => $form->createView(),
            'listeAdrGeoconcept' => $listeAdrGeoconcept,
            'listeAdrGoogle' => $listeAdrGoogle,
            'ResRNVP' => $adresseRnvp,
        ));
      
        $return = json_encode($modal);
        return new Response($return, 200, array('Content-Type' => 'Application/json'));
    }

}
