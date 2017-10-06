<?php

namespace Ams\ReferentielBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Ams\SilogBundle\Controller\GlobalController;

class DhtmlxController extends GlobalController {

    public function gridActionAction($repositoryName, Request $request) {
        if(!$this->verif_acces()) {
            $em = $this->getDoctrine()->getManager();
            $session=$this->get('session');
            $rowId = $request->get('gr_id');
            $newId ='';
            $action='';
            $msg='';
            $msgException='';
            $result=true;
            if ($request->get('!nativeeditor_status') == 'inserted') {
                $action = 'insert';
                $result = $em->getRepository($repositoryName)->insert($msg, $msgException, $_POST, $this->get('session')->get('UTILISATEUR_ID'), $newId);
            } elseif ($request->get('!nativeeditor_status') == 'updated') {
                $action = 'update';
                $result = $em->getRepository($repositoryName)->update($msg, $msgException, $_POST, $this->get('session')->get('UTILISATEUR_ID'), $newId);
            } elseif ($request->get('!nativeeditor_status') == 'deleted') {
                $action = 'delete';
                $result = $em->getRepository($repositoryName)->delete($msg, $msgException, $_POST);
            }
        }else{
            $action = 'timeout';
        }
        // log des erreurs
        if ($msg!='' || $msgException!='') {
                $em->getRepository('AmsPaieBundle:PaiSysErreur')->insert($session->get('UTILISATEUR_ID'),$session->get('depot_id'),$session->get('flux_id'),$session,$request,$_POST,$msg, $msgException);            
        }
        if (!$result) {
            $action="error";
            $response = $this->render('::grid_action_error.html.twig', array('action' => $action, 'rowId' => $rowId, 'newId' => $newId, 'msg' => $msg, 'msg_complet' => $msgException));
        }else{
            $valide = true;
            $level = '';
            $journal_id = '';
            $rows='';
            $response = $this->render('::grid_action.html.twig', array('action' => $action, 'rowId' => $rowId, 'newId' => $newId, 'rows' => '', 'valide' => true, 'msg' => $msg, 'level' => $level, 'journal_id' => $journal_id));
        }/* elseif ($msg!='') {
        }
            $action="invalid";
        }*/
        $response->headers->set('Content-Type', 'text/xml');
        return $response;
    }

}
