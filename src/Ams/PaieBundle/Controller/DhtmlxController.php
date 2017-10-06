<?php

namespace Ams\PaieBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Ams\SilogBundle\Controller\GlobalController;

class DhtmlxController extends GlobalController {

    public function gridActionAction($repositoryName, Request $request) {
        if ($this->verif_acces()) {
            $em = $this->getDoctrine()->getManager();
            $session=$this->get('session');
            $rowId = $request->get('gr_id');
            $newId = $request->get('gr_id');
            $action = '';
            $msg = '';
            $msgException = '';
            $result = true;
            $validation_id = true;
            // On passe par les services quand le Repository ne dépend pas directement d'une Entity
            if (strpos($repositoryName, 'ams.repository') !== FALSE) {
                $repository = $this->get($repositoryName);
            } else {
                if ($repositoryName=='AmsPaieBundle:PaiActiviteHP')
                    $repository = $em->getRepository('AmsPaieBundle:PaiActivite');
                else
                    $repository = $em->getRepository($repositoryName);
            }
            if ($request->get('!nativeeditor_status') == 'inserted') {
                $action = 'insert';
                $result = $repository->insert($msg, $msgException, $_POST, $session->get('UTILISATEUR_ID'), $newId);
            } elseif ($request->get('!nativeeditor_status') == 'updated') {
                $action = 'update';
                $result = $repository->update($msg, $msgException, $_POST, $session->get('UTILISATEUR_ID'), $newId);
            } elseif ($request->get('!nativeeditor_status') == 'deleted') {
                $action = 'delete';
                $result = $repository->delete($msg, $msgException, $_POST, $session->get('UTILISATEUR_ID'), $newId);
            } /* else {
              $result=false;
              $msg='Action inconnue.';
              } */
        }else{
            $action = 'timeout';
        }

        if ($result && \method_exists($repository, "validate")) {
            // Attention validate return false (en cas d'erreur) ou l'identifiant de la validation !!!!
            $validation_id = $repository->validate($msg, $msgException, $newId, $action, $_POST);
        }
        // log des erreurs
        if ($msg!='' || $msgException!='') {
                $em->getRepository('AmsPaieBundle:PaiSysErreur')->insert($session->get('UTILISATEUR_ID'),$session->get('depot_id'),$session->get('flux_id'),$session,$request,$_POST,$msg, $msgException);            
        }
        
        if (!$result || isset($validation_id) && !$validation_id) {
            $action = "error";
            $response = $this->render('::grid_action_error.html.twig', array('action' => $action, 'rowId' => $rowId, 'newId' => $newId, 'msg' => $msg, 'msg_complet' => $msgException));
        } else {
            $valide = true;
            $level = '';
            $journal_id = '';
            $rows='';
            if (isset($validation_id) && $validation_id){
                $em->getRepository('AmsPaieBundle:PaiJournal')->getMsg($validation_id, $valide, $msg, $level, $journal_id);
            }

            if ($repositoryName=='AmsPaieBundle:PaiActivite'
            || $repositoryName=='AmsPaieBundle:PaiActiviteHP'
            || $repositoryName=='AmsPaieBundle:PaiTournee' 
            || $repositoryName=='ams.repository.paiabonne' 
            || $repositoryName=='ams.repository.paireperage' 
            || $repositoryName=='ams.repository.paidiffuseur' 
            || $repositoryName=='ams.repository.paihorspresse' 
            || $repositoryName=='ams.repository.paiproduit' 
            || $repositoryName=='AmsPaieBundle:PaiReclamation' 
            || $repositoryName=='AmsPaieBundle:PaiIncident' 
            || $repositoryName=='AmsPaieBundle:PaiStc' 
//            || $repositoryName=='AmsPaieBundle:PaiRefUrssaf' 
            ){
                $controllerName='AmsPaieBundle:paiabonne';
                if (substr($repositoryName,0,15)=='ams.repository.') {
                    if ($repositoryName=='ams.repository.paiabonne') $controllerName='AmsPaieBundle:PaiAbonne';
                    if ($repositoryName=='ams.repository.paireperage') $controllerName='AmsPaieBundle:PaiReperage';
                    if ($repositoryName=='ams.repository.paidiffuseur') $controllerName='AmsPaieBundle:PaiDiffuseur';
                    if ($repositoryName=='ams.repository.paihorspresse') $controllerName='AmsPaieBundle:PaiHorsPresse';
                    if ($repositoryName=='ams.repository.paiproduit') $controllerName='AmsPaieBundle:PaiProduit';
                } else {
                        $controllerName=$repositoryName;
                }
                $rows = $this->forward($controllerName.':newGrid', array('param' => $_POST, 'newId' => $newId))->getContent();
            }
            
            $response = $this->render('::grid_action.html.twig', array('action' => $action, 'rowId' => $rowId, 'newId' => $newId, 'rows' => $rows, 'valide' => $valide, 'msg' => $msg, 'level' => $level, 'journal_id' => $journal_id));
        }/* elseif ($msg!='') {
          }
          $action="invalid";
          } */
        $response->headers->set('Content-Type', 'text/xml');
        return $response;
    }

}
