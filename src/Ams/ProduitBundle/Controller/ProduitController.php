<?php

namespace Ams\ProduitBundle\Controller;

use Ams\SilogBundle\Controller\GlobalController;
use Symfony\Component\HttpFoundation\Response;
use Ams\ProduitBundle\Entity\Produit;
use Ams\ProduitBundle\Entity\PrdRefSaisie;
use Ams\ProduitBundle\Form\ProduitType;
use Ams\ProduitBundle\Entity\PrdCaractConstante;
use Ams\ProduitBundle\Entity\PrdCaract;
use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Produit  controller.
 *
 */
class ProduitController extends GlobalController {

    // Creation d'un produit et de ses caractéristiques
    public function createAjaxAction() {
        // verifie si on a droit a acceder a cette page
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }

        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        // on recupere l'id de la societe lié au produit passé en param
        $id = $request->query->get('param1');
        $societe = $em->getRepository('AmsProduitBundle:Societe')->find($id);
        $produit = new Produit();
        $produit->setSociete($societe);
        $listeConstante = $em->getRepository('AmsProduitBundle:PrdCaract')->getListeConst();
        $form = $this->createForm(new ProduitType($id), $produit);
        $form->handleRequest($request);
        if ($request->getMethod() == 'POST') {
            if ($form->isValid()) {
                //on recupere l'utilisateur courant pour setter le produit créé
                $session = $this->get('session');
                $utilisateur = $em->getRepository('AmsSilogBundle:Utilisateur')->findOneById($session->get('UTILISATEUR_ID'));
                $produit->setUtilisateurModif($utilisateur);
                //on change la date de modification
                $produit->setDateModif(new \DateTime());
                //on recupere les dependances pour les enregistrer s'il en existe
                $parents = $form->get('parents')->getData();
                if (count($parents) > 0) {
                    foreach ($parents as $parent) {
                        $produit->addParent($parent);
                    }
                }
                // Pour le moment on sette tout a 0 en attendant de savoir a quoi servent ces champs 
                $produit->setLundi(0);
                $produit->setMardi(0);
                $produit->setMercredi(0);
                $produit->setJeudi(0);
                $produit->setVendredi(0);
                $produit->setSamedi(0);
                $produit->setDimanche(0);
                // on persist l'entité produit provenant du formulaire
                $em->persist($produit);
                $em->flush();
                // on check si l'utilisateur a selectioné un produit type
                if ($form->get('produitType')->getData() != null) {
                    // on check si le produit type a des caract constante on recupere la liste et on set l'entité concerné
                    $produitTypeId = $form->get('produitType')->getData()->getId();
                    $listePrdCaractConst = $em->getRepository('AmsProduitBundle:PrdCaract')->getListConstById($produitTypeId);
                    // si la liste comporte des resultats on enregistre car le le produittype selectionné a des caractéristiques de type constante
                    if (count($listePrdCaractConst) > 0) {  
                            foreach ($listePrdCaractConst as $const)
                            { 
                                //pour chaque caract de type constante on récupére les données en BDD pour avoir un objet 
                                //etles valeurs  en POST METHOD pour avoir les données
                                $prdCaract = $em->getRepository('AmsProduitBundle:PrdCaract')->findOneBy(array('id' => $const['id']));
                                $name = "ajout_caract_".$const['id'];
                                $type = $const['constType'];
                                $valeur = $request->get($name);
                                // Creation dun objet pour setter les differentes infos
                                $produitConstante = new PrdCaractConstante();
                                $produitConstante->setProduit($produit);
                                $produitConstante->setPrdCaract($prdCaract);
                                $produitConstante->setUtilisateur($utilisateur);
                                $produitConstante->setDateModif(new \DateTime());
                                if ($type == "chaine") {
                                    $produitConstante->setValeurString($valeur);
                                } 
                                else if ($type == "entier") {
                                    $produitConstante->setValeurInt($valeur);
                                } 
                                else if ($type == "reel") {
                                    $produitConstante->setValeurFloat($valeur);
                                }
                                // on persist l'entité produitConstante provenant de l'evenement Select Produit type du formulaire
                                $em->persist($produitConstante);
                            }  
                            //on flush ttes les entrées
                            $em->flush();
                    }
                    
                }
                //on cree un nouveau produit qui sera affiche automatiquement apres la sauvegarde de l'enregistrement precedent
                $produit = new Produit();
                $produit->setSociete($societe);
                $form = $this->createForm(new ProduitType($id), $produit);
                $alert = $this->renderView('::modalAlerte.html.twig', array(
                                                                        'type' => 'success',
                                                                        'message' => '<strong>Succès!</strong> Votre produit a bien été ajouté!'));
            } else {
                $alert = $this->renderView('::modalAlerte.html.twig', array(
                                                                        'type' => 'danger',
                                                                        'message' => '<strong>Attention!</strong> Une erreur est survenue!'));
            }
            //au deuxieme appel ajax on re affiche le formulaire et on met a jour le background
            $background = $this->ajaxListeProduitsAction($societe->getId());
            $modal = $this->renderView('AmsProduitBundle:Produit:formProduit.html.twig', array(
                                                                                            'societe' => $societe,
                                                                                            'produitForm' => $form->createView(),
                                                                                            'is_new' => true,
                                                                                            'const' => '',
                                                                                            'liste' => $listeConstante));
            $response = array("modal" => $modal, "background" => $background, "alert" => $alert);
            $return = json_encode($response);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        }
        // au premier appel ajax on affiche le formulaire dans la modale
        $response = $this->renderView('AmsProduitBundle:Produit:formProduit.html.twig', array(
                                                                                            'societe' => $societe,
                                                                                            'produitForm' => $form->createView(),
                                                                                            'is_new' => true,
                                                                                            'const' => '',
                                                                                            'liste' => $listeConstante));
        $return = json_encode($response);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }

    
    
    // Modification d'un produit et de ses caractéristiques
    public function updateAjaxAction() {
        // verifie si on a droit a acceder a cette page
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
        
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        // on recupere le produit selectioné
        $id = $request->query->get('param1');
        $produit = $em->getRepository('AmsProduitBundle:Produit')->find($id);
        //$produitTypeIdOld = $produit->getProduitType()->getId();
        //on recupere les caract constante liéé au produit s'il y en a en BDD bien sur 
        $liste = $em->getRepository('AmsProduitBundle:PrdCaractConstante')->getConstByProduitId($id);
        // on recupere l'id de la societte lié au produit selectionné
        $societe = $produit->getSociete()->getId();
        $listeConstante = $em->getRepository('AmsProduitBundle:PrdCaract')->getListeConst();
        $form = $this->createForm(new ProduitType($societe), $produit);
        $form->handleRequest($request);
        if ($request->getMethod() == 'POST') {
            if ($form->isValid()) {
                //on recupere l'utilisateur courant pour setter la table produit
                $session = $this->get('session');
                $utilisateur = $em->getRepository('AmsSilogBundle:Utilisateur')->findOneById($session->get('UTILISATEUR_ID'));
                $produit->setUtilisateurModif($utilisateur);
                //on change la date de modification
                $produit->setDateModif(new \DateTime());
                //on recupere les dependances du formulaire
                $parents = $form->get('parents')->getData();
                //on recupere les dependances de la BDD
                $parentsProduit = $em->getRepository('AmsProduitBundle:Produit')->getParentsByEnfants($produit->getId());
                
                if ($parents){
                    if (count($parentsProduit) > 0){
                      $Form = array(); 
                      $BDD = array(); 
                      //on rempli un tableau avec les données du formulaire
                      foreach($parents as $parent){
                        $Form[] = $parent->getId();
                      }
                      //on rempli un tableau avec les données de la BDD
                      foreach($parentsProduit as $parentProduit){
                      $BDD[] = $parentProduit['parent_id'];
                      }
                      // on crée le tableau des elements a supprimer
                      $supp = array_values(array_diff($BDD, $Form));
                      $lenSupp = count($supp);
                      if ($lenSupp > 0){
                        if ($lenSupp == 1) {
                            //var_dump($supp);
                            $obj = $em->getRepository('AmsProduitBundle:Produit')->findOneById($supp[0]);                           
                            $res = $em->getRepository('AmsProduitBundle:Produit')->delDataByParentId($supp[0], $produit->getId());
                        }
                        else {
                            for ($idx = 0; $idx < $lenSupp ; $idx++){   
                                $obj = $em->getRepository('AmsProduitBundle:Produit')->findOneById($supp[$idx]);                           
                                $res = $em->getRepository('AmsProduitBundle:Produit')->delDataByParentId($supp[$idx], $produit->getId());
                            }
                        }
                      }
                      // on crée le tableau des elements a ajouter
                      $ajout = array_values(array_diff($Form, $BDD));
                      //$ajout = array_values($ajout);
                      $lenAjout = count($ajout);
                      if ($lenAjout > 0) {
                            if ($lenAjout == 1){
                                //var_dump($ajout);exit();
                                $obj = $em->getRepository('AmsProduitBundle:Produit')->findOneById($ajout[0]);
                                $produit->addParent($obj);
                                $em->persist($produit);
                            }
                            else {
                                for ($idx = 0; $idx < $lenAjout ; $idx++){
                                    $obj = $em->getRepository('AmsProduitBundle:Produit')->findOneById($ajout[$idx]);
                                    $produit->addParent($obj);
                                    $em->persist($produit);   
                                }
                            }
                        }
                    }
                    else
                    {
                        foreach($parents as $parent){
                            $produit->addParent($parent);
                        }
                        $em->persist($produit);
                    }
                }
                $em->flush();
                $alert = $this->renderView('::modalAlerte.html.twig', array(
                                                                        'type' => 'success', 
                                                                        'message' => '<strong>Succès!</strong> Votre produit a bien été Modifié!'));
            } else {
                $alert = $this->renderView('::modalAlerte.html.twig', array(
                                                                        'type' => 'danger',
                                                                        'message' => '<strong>Attention!</strong> Une erreur est survenue!'));
            }
            //au deuxieme appel ajax on re affiche le formulaire et on met a jour la page principale
            $background = $this->ajaxListeProduitsAction($societe);
            //on recupere la derniere version des constantes liees au produit dans la bdd
            $liste = $em->getRepository('AmsProduitBundle:PrdCaractConstante')->getConstByProduitId($id);
            $modal = $this->renderView('AmsProduitBundle:Produit:formProduit.html.twig', array(
                                                                                            'produit' => $produit,
                                                                                            'produitForm' => $form->createView(),
                                                                                            'liste' => $listeConstante,
                                                                                            'const' => $liste,
                                                                                            'is_new' => false));

            $response = array("modal" => $modal, "background" => $background, "alert" => $alert);
            $return = json_encode($response);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        }
        // au premier appel ajax on affiche le formulaire dans la modale
        $response = $this->renderView('AmsProduitBundle:Produit:formProduit.html.twig', array(
                                                                                            'produit' => $produit,
                                                                                            'produitForm' => $form->createView(), 
                                                                                            'liste' => $listeConstante,
                                                                                            'const' => $liste,
                                                                                            'is_new' => false));
        $return = json_encode($response);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }

   
    
    // details dun produit 
     public function detailAjaxAction() {

//        // verifie si on a droit a acceder a cette page
//        $bVerifAcces = $this->verif_acces();
//        if ($bVerifAcces !== true) {
//            return $bVerifAcces;
//        }
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $id = $request->get('param1');
        //on recupere les infors du produit
        $produit = $em->getRepository('AmsProduitBundle:Produit')->find($id);
        //on recupere les caract constante liéé au produit s'il y en a en BDD bien sur 
        $liste = $em->getRepository('AmsProduitBundle:PrdCaractConstante')->getConstByProduitId($id);
        if (count($liste) > 0){
            $constante = true;
        }
        else {
            $constante = false;
        }
        
        $modal = $this->renderView('AmsProduitBundle:Produit:detailProduitNew.html.twig', array(
                                                                    'produit' => $produit,
                                                                    'constante' => $constante,
                                                                    'liste' => $liste
                                                                        ));
        $return = json_encode($modal);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
     }
     

    // Liste des produits
      public function ajaxListeProduitsAction($id) {
        $societe = $this->getDoctrine()->getManager()->getRepository('AmsProduitBundle:Societe')->find($id);
        //var_dump($societe);exit();
        return $this->renderView('AmsProduitBundle:Produit:liste_produits.html.twig', array('societe' => $societe));
    }
    
    public function deleteProduitsAction() { 
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        // on recupere le produit selectioné a supprimer
        $id = $request->get('produitId');
        $produit = $em->getRepository('AmsProduitBundle:Produit')->find($id);
        //on recupere l'id de la societe lié au produit
        //$idSociete = $produit->getSociete()->getId();
        //on recupere la liste des caract contante s'il yen a 
        $listeCaractConst = $em->getRepository('AmsProduitBundle:PrdCaractConstante')->getConstByProduitId($id);
        $msgDel = ' !!!!!!!!';
        if (count($listeCaractConst) > 0) {
            $delRes = $em->getRepository('AmsProduitBundle:PrdCaractConstante')->delDataByProduitId($id);
            $msgDel = ', ainsi que toutes ses caractéristiques constantes !!!!!!!!';
        }
        //on récupere ttes les dependances liées au produit
        $listeParents = $em->getRepository('AmsProduitBundle:Produit')->getDataByEnfantsId($id);
        $listeEnfants = $em->getRepository('AmsProduitBundle:Produit')->getDataByParentsId($id);
        $ct = 0;
        if (count($listeParents) > 0) {
           foreach ($listeParents as $elem) {
               $resP = $em->getRepository('AmsProduitBundle:Produit')->delDataByParentId($elem['parent_id'], $produit->getId());
           }
           $ct++;
        }
        if (count($listeEnfants) > 0) {
            foreach($listeEnfants as $elem){
                $resE = $em->getRepository('AmsProduitBundle:Produit')->delDataByEnfantsId($elem['enfant_id'], $produit->getId());
            }
            $ct++;
        }
        if ($ct > 0) {
            $msgDep = ' et toutes ses dépendances ont bien été supprimés';
        }
        else {
            $msgDep = ' a bien été supprimé';
        }
        $message = '<strong>Succès!</strong> Le produit [ ' . $produit->getLibelle() . ' ]' . $msgDep . $msgDel;
        $em->remove($produit);
        $em->flush();
        $response = array("alert" => $message);
        $return = json_encode($response);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }
    
    /**
     * Retourne un flux JSON contenant la liste complète des produits.
     */
    public function ajaxListeTousProduitsAction(){
        $aList =array();
        $em = $this->getDoctrine()->getManager();
        $aObjList = $em->getRepository('AmsProduitBundle:Produit')->findBy(array());
        
        if (!empty($aObjList)){
            foreach ($aObjList as $oProd){
                $aList[] = array('id' => $oProd->getId(), 'libelle' => $oProd->getLibelle());
            }
        }
        
        $return = json_encode(array('produits' => $aList));
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }
    
    /**
     * Teste une société, un produit ou la relation d'appartenance entre un produit et une société.
     * Fournit la réponse sous forme de JSON pour les requêtes AJAX
     * @param Request $request
     * @return JsonResponse
     */
    public function checkAction(Request $request){
        $aTestResult = array('exists' => FALSE);
        $aTests = $request->query->get('tests');
        
        if (!is_null($request->query->get('type'))){
            $em = $this->getDoctrine()->getManager();
            
            switch ($request->query->get('type')){
                case 'societe':
                    $iSocieteId = (int)$request->query->get('id');
                    if ($iSocieteId){
                        $oSociete = $em->getRepository('AmsProduitBundle:Societe')->findOneById($iSocieteId);
                        if (!empty($oSociete)){
                            $aTestResult['exists'] = TRUE;
                        }
                    }
                    break;
                case 'produit':
                    $iProdId = (int)$request->query->get('id');
                    if ($iProdId){
                        $oProd = $em->getRepository('AmsProduitBundle:Produit')->findOneById($iProdId);
                        if (!empty($oProd)){
                            $aTestResult['exists'] = TRUE;
                        }
                    }
                    
                    // Tests additionnels si besoin
                    if (!empty($aTests)){
                        foreach ($aTests as $sTest){
                            switch ($sTest){
                                case 'own_prod':
                                    $aTestResult['own_prod'] = FALSE;
                                    
                                    // Récupération de l'ID société
                                    $iSocieteId = (int)$request->query->get('ste_id');
                                    $oSociete = $em->getRepository('AmsProduitBundle:Societe')->findOneById($iSocieteId);
                                    if (!empty($oSociete)){
                                        $aProduits = $oSociete->getProduits();
                                        foreach($aProduits as $oProd){
                                            if ($oProd->getId() == $iProdId){
                                                $aTestResult['own_prod'] = TRUE;
                                            }
                                        }
                                    }
                                    break;
                            }
                        }
                    }
                    break;
            }
        }
        
        return new JsonResponse($aTestResult);
    }
}
//Controller