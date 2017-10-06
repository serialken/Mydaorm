<?php

namespace Ams\AdresseBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Ams\SilogBundle\Controller\GlobalController;
use Symfony\Component\HttpFoundation\Response;
use Ams\AdresseBundle\Repository\CommuneRepository;
use Ams\DistributionBundle\Entity\Bordereau;


class AbonneTourneeController extends GlobalController {

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
                ->add('depots', 'choice', array('choices' => $depots,'required'=>true, 'label'=>'Depôts'))
                ->add('tournees','choice', array('empty_value' => 'Choisissez une tournée','required'=>true, 'label'=>'Tournées'))
                ->add('jour', 'entity', array('empty_value' => 'Choisissez un jour','class'=> 'AmsReferentielBundle:RefJour','property' => 'Libelle','required'=>true, 'label'=>'Jour'))
                ->add('flux', 'entity', array('empty_value' => 'Choisissez un flux','class'=> 'AmsReferentielBundle:RefFlux','property' => 'Libelle','required'=>true, 'label'=>'Flux'))
                ->getForm();
        
        return $this->render('AmsAdresseBundle:AbonneTournee:index.html.twig', array(
				'form' => $form->createView(),
		));
        
    }
    
    public function getTourneeAction(Request $request) {
       $em = $this->get("doctrine.orm.entity_manager") ;
       $flux = $request->get('flux');
       $depot = $request->get('depot');
       $jour = $request->get('jour');
       $tournees = $em->getRepository('AmsModeleBundle:ModeleTourneeJour')->getTourneeByDepotFlux($depot,$flux,$jour);
       
       $select = '<option>Choisissez une tournée</option>';
       foreach($tournees as $tournee)
        $select .= '<option value="'.$tournee['id'].'">'.$tournee['code'].'</option>';
       
       return new Response(json_encode($select),200, array('Content-Type'=>'application/json'));
        
    }
    
    public function getDataAction(Request $request) {
        $em = $this->get("doctrine.orm.entity_manager") ;
        $data = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getDataByTourneeJour($request->get('tournee'));
        $aTournees = $em->getRepository('AmsModeleBundle:ModeleTourneeJour')->getTourneeByDepotFlux($request->get('depot'),$request->get('flux'),$request->get('jour'));
        
        $tournees = '';
        foreach($aTournees as $tournee)
            $tournees .= '<option value="'.$tournee['code'].'">'.$tournee['code'].'</option>';

                
        $response = $this->renderView('AmsAdresseBundle:AbonneTournee:grid.xml.twig', array(
            'data' => $data,
            'tournees' => $tournees,
        ));
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
      
    }
    
    public function crudAction(Request $request) {
        $command = 'php '.$this->get('kernel')->getRootDir().'/console abo_change_trn  --caslId='.$request->get('caslId').' --trn='.$request->get('tourneeCode').' --jourId='.$request->get('jourId').' --numAbo='.$request->get('numAboId').' --env '.$this->container->get('kernel')->getEnvironment();
        exec($command);
        exit;
    }
    
}
