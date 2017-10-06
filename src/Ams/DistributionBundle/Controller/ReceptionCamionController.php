<?php


namespace Ams\DistributionBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Ams\SilogBundle\Controller\GlobalController;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Ams\DistributionBundle\Entity\CptrReceptionCamion;

class ReceptionCamionController extends GlobalController
{
    /**
     * [ReceptionDepotAction description]
     */
    public function IndexAction(Request $request)
    {
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
        
        $flux = 0;
        $date = date('Y-m-d');
        $datepicker = date('d/m/Y');
        if($request->getMethod() == 'POST'){
            $data = $request->request->get('form'); 
            $flux = (!empty($data['flux'])) ? $data['flux'] : $flux;
            $date = (!empty($data['date'])) ? $this->getDate($data['date']) : $date;
            $datepicker =(!empty($data['date']))? $data['date'] : $datepicker;
        }

        $form = $this->createFormBuilder()
            ->add(
                'flux', 'entity',
                array(
                    'class' => 'AmsReferentielBundle:RefFlux','property' => 'libelle','required' => false,'empty_value' => 'Choisissez un flux','data' => 1
                )
            )
            ->add(
                'date', 'text',
                array(
                    'required' => false
                )
            )
            ->getForm();
        return $this->render('AmsDistributionBundle:ReceptionCamion:ReceptionCamion.html.twig',
                                array(
                                    'form' => $form->createView(),
                                    'flux' => $flux,
                                    'date' => $date,
                                    'datepicker' => $datepicker,
                                )
                            );
    }
    
    public function GridAction(Request $request)
    {
     $date = $request->get('date');
     $flux = $request->get('flux');
     $em = $this->getDoctrine()->getManager();
     $data = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->fetchAllByDate($date,$flux);
     
     $response = $this->renderView('AmsDistributionBundle:ReceptionCamion:grid.xml.twig', array(
         'aReception' => $data,
         'date' => $date,
     ));
     
     return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }
    
    public function CrudAction(Request $request)
    {
        if(!$this->verif_acces()) return false;
        $this->setDerniere_page();
        $em = $this->getDoctrine()->getManager();
        $mode = $request->get('!nativeeditor_status');
        $rowId = $request->get('gr_id');
        $newId = $action = $msgException = $msg='';
        $result=true;
        
        $session = new Session();
        $session->get('UTILISATEUR_ID');
        $user = $em->getRepository('AmsSilogBundle:Utilisateur')->find($session->get('UTILISATEUR_ID'));
        
        if($mode == 'updated') {
            
            /** IF REGISTRATION EXIST**/
            $oReceptCamion = $em->getRepository('AmsDistributionBundle:CptrReceptionCamion')->findOneBy(array('idCasl'=>$request->get('c1')));
            if(empty($oReceptCamion))
                $oReceptCamion = new CptrReceptionCamion();
            $date = new \DateTime;
            $hour = $date::createFromFormat('d/m/Y H:i', $request->get('c5'));

            $iProduit = $request->get('c0');
            $oProduit = $em->getRepository('AmsProduitBundle:Produit')->find($iProduit);
            $oReceptCamion->setIdCasl($request->get('c1'));
            $oReceptCamion->setDateCptRendu(new \DateTime());
            $oReceptCamion->setUser($user);
            $oReceptCamion->setProduit($oProduit);
            $oReceptCamion->setQtePrevue($request->get('c3'));
            $oReceptCamion->setQteRecue($request->get('c4'));
            $oReceptCamion->setHeureReception($hour);
            $oReceptCamion->setCommentaires($request->get('c6'));
            
            $em->persist($oReceptCamion);
            $em->flush();

        }
        
        if (!$result) {
            $action="error";
            $response = $this->render('::grid_action_error.html.twig', array('action' => $action, 'rowId' => $rowId, 'newId' => $newId, 'msg' => $msg, 'msg_complet' => $msgException));
        }
        else
            $response = $this->render('::grid_action.html.twig', array('action' => $action, 'rowId' => $rowId, 'newId' => $newId, 'msg' => $msg));
        
        $response->headers->set('Content-Type', 'text/xml');
        return $response;
    }
    
    private function getDate($date){
        $date = explode('/', $date);
        return $date[2].'-'.$date[1].'-'.$date[0];
    }

}
