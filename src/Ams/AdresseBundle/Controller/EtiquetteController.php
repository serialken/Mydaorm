<?php

namespace Ams\AdresseBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Ams\SilogBundle\Controller\GlobalController;
use Symfony\Component\HttpFoundation\Response;
use Ams\AdresseBundle\Repository\CommuneRepository;
use Ams\DistributionBundle\Entity\Etiquette;


class EtiquetteController extends GlobalController {

    public function indexAction(Request $request) {

        // verifie si on a droit a acceder a cette page
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
        $em = $this->get("doctrine.orm.entity_manager") ;
        $session = $this->get('session');
        $depots = $session->get('DEPOTS');
        $depotIds = array_keys($depots);
        
        $form = $this->createFormBuilder()
                ->add('Produit', 'entity', array('required'=>false, 'label'=>'Produit','class' => 'AmsProduitBundle:Produit' ,'property' => 'libelle','empty_value' => 'Choisissez un produit'))
                ->add('Adresse', 'text', array('required'=>false, 'label'=>'Adresse'))
                ->add('Compl', 'text', array('required'=>false, 'label'=>'Compl.adresse'))
                ->add('NomAbo', 'text', array('required'=>false, 'label'=>'Nom.abo'))
                ->add('NumAbo', 'text', array('required'=>false, 'label'=>'Num.abo'))
                ->add('Lieu', 'text', array('required'=>false, 'label'=>'Lieu dit'))
                ->add('Zip', 'text', array('max_length'=>5,'required'=>false, 'label'=>'Cp'))
                ->add('Flux', 'choice', array('required'=>false, 'label'=>'Flux','choices'=>array('1'=>'Nuit','2'=>'Jour'),'empty_value' => 'Choisissez un flux'))
                ->add('Tournee', 'choice', array('required'=>false, 'label'=>'Tournee'))
                ->add('Depot', 'choice', array('required'=>true, 'label'=>'Depot','choices' => $depots))
                ->add('Commune','entity', array('class'=> 'AmsAdresseBundle:Commune','property' => 'libelleWithCp','empty_value' => 'Choisissez une ville','required'=>false,                    
                        'query_builder' => function(CommuneRepository $er) use ($depotIds){
                            return $er->getCommuneByDepotIds($depotIds);
                        }
                      ))
                ->getForm();
        $result = false;
        $sSelectTournee = false;
        if($request->getMethod() == 'POST'){
            $form->handleRequest($request);
            $data = $form->getData();
            $oCommune = $data['Commune'];
            $oProduit = $data['Produit'];
            $produit = ($oProduit)?$oProduit->getId() : '';
            $city = ($oCommune)?$oCommune->getLibelle() : '';
            if($data['Zip'] >=75000 && $data['Zip'] < 76000){
                $city = 'PARIS';
            }
            
            /** RECUPERATION DU CODE POSTAL POUR LES ARRONDISSEMENTS DE PARIS **/
            if(empty($data['Zip']) && !empty($city)){
                if(strpos($city,'PARIS') !== false){
                    $data['Zip'] = $oCommune->getCp();
                    $city = '';
                }
            }
            $info = array(
                'ZIPCODE' => $data['Zip'],
                'CITY' => $city,
                'ADRESS' => $data['Adresse'],
                'NOM' => $data['NomAbo'],
                'NUMABO' => $data['NumAbo'],
                'DEPOT' =>   $data['Depot'],
                'PRODUIT' =>   $produit,
                'TOURNEE' => $request->get('Tournee'),
                'FLUX' => $data['Flux'],
            );
            
            $result = $em->getRepository('AmsAbonneBundle:AbonneSoc')->getAbonneByData($info);
            $tournees = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getTourneeByDepot($data['Depot']);
            $sSelectTournee ='<select id="form_Tournee" name="Tournee"> <option></option>';
            if($tournees){
                foreach($tournees as $tournee){
                    $selected = ( $tournee['code'] == $request->get('Tournee') )? 'selected' :'';
                    $sSelectTournee .='<option '.$selected.'> '.$tournee['code'].'</option>';
                }
            }
            else  $sSelectTournee .='<option> Il n\'y a pas de tournee pour ce depot </option>';
            $sSelectTournee .='</select>';
        }

        return $this->render('AmsAdresseBundle:Etiquette:index.html.twig', array(
				'form' => $form->createView(),
				'query' => $result,
				'sSelectTournee' => $sSelectTournee,
		));
        
    }
    
    public function crudAction(Request $request) {
       $em = $this->get("doctrine.orm.entity_manager") ;
       $abonne_soc_add = $request->get('abonne_soc_id_add');
       $abonne_soc_del = $request->get('abonne_soc_id_delete');
       
       /** AJOUT DES ETIQUETTE POUR LES ABONNEES**/
       if(!empty($abonne_soc_add)){
           $aAbonne_soc_id = explode('_', $abonne_soc_add);
           foreach($aAbonne_soc_id as $abonne_soc_id){
                $etiquette = $em->getRepository('AmsDistributionBundle:Etiquette')->findOneByAbonneSocId($abonne_soc_id);
                if(!$etiquette){
                    $etiquette = new Etiquette();
                    $etiquette->setAbonneSocId($abonne_soc_id);
                    $em->persist($etiquette);
                }
           }
       }
       /** SUPPRESSION DES ETIQUETTE POUR LES ABONNEES**/
       if(!empty($abonne_soc_del)){
           $aAbonne_soc_id = explode('_', $abonne_soc_del);
           foreach($aAbonne_soc_id as $abonne_soc_id){
                $etiquette = $em->getRepository('AmsDistributionBundle:Etiquette')->findOneByAbonneSocId($abonne_soc_id);
                if($etiquette)
                   $em->remove($etiquette);
           }
       }
       $em->flush();
       exit;
    }
    
    public function getTourneeByDepotAction(Request $request) {
        $em = $this->get("doctrine.orm.entity_manager") ;
        $iDepot = $request->get('iDepot');
        if($iDepot)
            $tournees = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getTourneeByDepot($iDepot);
        else $tournees = false;
        
        $select ='<select id="form_Tournee" name="Tournee"> <option></option>';
        if($tournees)
            foreach($tournees as $tournees)
                $select .='<option> '.$tournees['code'].'</option>';
        else  $select .='<option> Il n\'y a pas de tournee pour ce depot </option>';
        $select .='</select>';
        
        $response = array("tournees" => $select);
        $return = json_encode($response);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
            
       
    }
 
    public function crudAllProduitAction(Request $request) {
       $em = $this->get("doctrine.orm.entity_manager") ;
       $soc_add = $request->get('abonne_soc_id_add');
       $soc_del = $request->get('abonne_soc_id_delete');
       
       /** AJOUT DES ETIQUETTE POUR LES ABONNEES**/
       if(!empty($soc_add)){    
           $societe = $em->getRepository('AmsProduitBundle:Societe')->find($soc_add);  
           $allAbonnee = $em->getRepository('AmsAbonneBundle:AbonneSoc')->getAllBySociete($societe);  
           foreach($allAbonnee as $abonne_soc){
                $etiquette = $em->getRepository('AmsDistributionBundle:Etiquette')->findOneByAbonneSocId($abonne_soc->getId());
                if(!$etiquette){
                    $etiquette = new Etiquette();
                    $etiquette->setAbonneSocId($abonne_soc->getId());
                    $em->persist($etiquette);
                }
           }
           $societe->setEtiquette(true);
           $em->persist($societe);
       }
       /** SUPPRESSION DES ETIQUETTE POUR LES ABONNEES**/
       if(!empty($soc_del)){
           $societe = $em->getRepository('AmsProduitBundle:Societe')->find($soc_del);  
           $allAbonnee = $em->getRepository('AmsAbonneBundle:AbonneSoc')->getAllBySociete($societe);  
           foreach($allAbonnee as $abonne_soc){
                $etiquette = $em->getRepository('AmsDistributionBundle:Etiquette')->findOneByAbonneSocId($abonne_soc->getId());
                if($etiquette)
                   $em->remove($etiquette);
           }
           $societe->setEtiquette(false);
           $em->persist($societe);
       }
       $em->flush();
       exit;
    }
}
