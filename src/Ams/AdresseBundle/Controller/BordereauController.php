<?php

namespace Ams\AdresseBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Ams\SilogBundle\Controller\GlobalController;
use Symfony\Component\HttpFoundation\Response;
use Ams\AdresseBundle\Repository\CommuneRepository;
use Ams\DistributionBundle\Entity\Bordereau;


class BordereauController extends GlobalController {

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
                ->add('Adresse', 'text', array('required'=>false, 'label'=>'Adresse'))
                ->add('Compl', 'text', array('required'=>false, 'label'=>'Compl.adresse'))
                ->add('Lieu', 'text', array('required'=>false, 'label'=>'Lieu dit'))
                ->add('Zip', 'text', array('max_length'=>5,'required'=>true, 'label'=>'Cp'))
                ->add('Commune','entity', array('class'=> 'AmsAdresseBundle:Commune','property' => 'libelleWithCp','empty_value' => 'Choisissez une ville','required'=>false,                    
                        'query_builder' => function(CommuneRepository $er) use ($depotIds){
                            return $er->getCommuneByDepotIds($depotIds);
                        }
                      ))
                ->getForm();
        $result = false;
        if($request->getMethod() == 'POST'){
            $form->handleRequest($request);
            $data = $form->getData();
            $oCommune = $data['Commune'];
            $scommune = '';
            if($oCommune){
                if(strpos($oCommune->getLibelle(),'PARIS') !== false)
                    $scommune = 'PARIS';
                else
                    $scommune = $oCommune->getLibelle();
            }
            $info = array(
                'ZIPCODE' => $data['Zip'],
                'CITY' => $scommune,
                'LIEU' => $data['Lieu'],
                'COMPLEMENT' => $data['Compl'],
                'ADRESS' => $data['Adresse'],
            );
            $result = $em->getRepository('AmsAdresseBundle:AdresseRnvp')->getPointLivraisonByData($info);
        }

        return $this->render('AmsAdresseBundle:Bordereau:index.html.twig', array(
				'form' => $form->createView(),
				'query' => $result,
		));
        
    }
    
    public function crudAction(Request $request) {
       $em = $this->get("doctrine.orm.entity_manager") ;
       $checked = $request->get('checked');
       $point_livraison = $request->get('point_livraison');
       //$aDepot = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getCodeDepotByPointLivraison($point_livraison);
       
       $bordereau = $em->getRepository('AmsDistributionBundle:Bordereau')->find($point_livraison);
       /** DELETE **/
        if($bordereau){
            $em->remove($bordereau);
        }
       /** INSERT**/
        else {
            $bordereau = new Bordereau();
            $bordereau->setState(1);
            $bordereau->setPointLivraison($point_livraison);
            //$bordereau->setCode($aDepot['code']);
            $em->persist($bordereau);
        }
        $em->flush();
        exit();
    }
    
}
