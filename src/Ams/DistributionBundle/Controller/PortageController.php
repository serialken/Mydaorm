<?php

namespace Ams\DistributionBundle\Controller;

use Ams\SilogBundle\Controller\GlobalController;
use Ams\DistributionBundle\Entity\InfoPortage;
use Ams\DistributionBundle\Form\InfoPortageType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Ams\AdresseBundle\Entity\AdresseRnvp;
use Ams\AdresseBundle\Entity\Adresse;
use Ams\AbonneBundle\Entity\AbonneSoc;
use Symfony\Component\HttpFoundation\Session\Session;
use Ams\SilogBundle\Entity\Utilisateur;
use Ams\DistributionBundle\Repository\TypeInfoPortageRepository;
use DateTime;

/**
 * Description of PortageController
 *
 * @author DDEMESSENCE
 */
class PortageController extends GlobalController {

    /**
     * Ajout infoPortage et modification
     *
     */
    public function ajoutAction(Request $request) {

        if (!$this->verif_acces())
            return $bVerifAcces;
        else
            $this->setDerniere_page();

        $em = $this->getDoctrine()->getManager();
        $abonneId = $request->query->get('abonneId');
        $session = $this->get('session');
        $livraison = new AdresseRnvp();
        $adresse = new Adresse();
        $pointLivraison = '';
        $listeAbonnes = array(); // liste des abonnés rattachés au point de livraison
        // On récupere l'adresse RNVP et le point de livraion de l'abonne
        $aAdresse = $em->getRepository('AmsAdresseBundle:Adresse')->findByAbonneSoc($abonneId);
        $dateJour = new \DateTime;
        foreach($aAdresse as $oAdresse){
            if($oAdresse->getDateDebut() <= $dateJour && $oAdresse->getDateFin() >= $dateJour){
                $adresse = $oAdresse;break;
            }
        }
        $abonne = $adresse->getAbonneSoc();
        $volets = $adresse->getVol1() . " " . $adresse->getVol2();
        $livraison = $adresse->getPointLivraison();
        if ($livraison != NULL) {
            $pointLivraison = $livraison->getCAdrs() . " " . $livraison->getAdresse() . " " . $livraison->getCp() . " " . $livraison->getVille();
            $listeAbonnes = $em->getRepository('AmsAdresseBundle:Adresse')->getAdresseByCritere(array('point_livraison_id' => $livraison->getId()));
        }
        $utilisateur = $em->getRepository('AmsSilogBundle:Utilisateur')->findOneById($session->get('UTILISATEUR_ID'));
        // formulaire de creation d'une info portage
        $infoPortage = new InfoPortage();
        $checkAbonne = false;
        $checkLivraion = false;

        if ($request->query->get('id') > 0) {
            $infoPortage = $em->getRepository('AmsDistributionBundle:InfoPortage')->find($request->query->get('id'));
            if (count($infoPortage->getAbonnes()) > 0){
                $checkAbonne = true;}
            if (count($infoPortage->getLivraisons()) > 0){
                $checkLivraion = true;}
        }
 
       $form =  $this->createFormulaire($infoPortage, $checkAbonne, $checkLivraion);

       $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $date_fin = new DateTime($this->container->getParameter('DATE_FIN'));
            $date_debut = $infoPortage->getDateDebut();
            $infoPortage->setDateFin($date_fin);
            $infoPortage->setOrigine('1'); //application
            $infoPortage->setUtilisateur($utilisateur);
            $infoPortage->setActive(1);
            
            $data = $request->request->all();
            
            //si infoportage abonne
            if (isset($data['form']['abonne']) && ($data['form']['abonne'] == 1)) {
                $infoPortage->addAbonne($abonne);
            }
            //si infoportage point de livraison
            if (isset($data['form']['livraison']) && ( $data['form']['livraison'] == 1)) {
                $infoPortage->addLivraison($livraison);
            }
            // creation d'une date J -1'
            $date_fin = new DateTime($infoPortage->getDateDebut()->format('Y-m-d'));
            $date_fin->sub(new \DateInterval('P1D'));
            
            $this->setInfoPortageDateFin($abonne, $infoPortage, $date_fin);
            $em->persist($infoPortage);
            $em->flush();
            return $this->redirect($this->generateUrl('fiche_abonne', array('id' => $abonneId)));
        }

        return $this->render('AmsDistributionBundle:InfoPortage:ajout.html.twig', array(
                    'form' => $form->createView(),
                    'volets' => $volets,
                    'pointLivraison' => $pointLivraison,
                    'abonneId' => $abonneId,
                    'listeAbonnes' => $listeAbonnes,
        ));
    }

    /**
     * formulaire création et modification
     * info portage
     */
    public function createFormulaire($infoPortage, $checkAbonne, $checkLivraion) {

        $form = $this->createFormBuilder($infoPortage)

                ->add('typeInfoPortage', 'entity', array(
                        'class'=>'AmsDistributionBundle:TypeInfoPortage',
                        'property'=>'libelle',
                        'label'=>'Type Info portage',
                        
                        'query_builder' => function(TypeInfoPortageRepository $er) {
                            return $er->createQueryBuilder('i')
                                    ->where("i.active = 1 AND i.categorie ='INFO_UTILISATEUR'" )
                            ->orderBy('i.libelle', 'ASC');},
                                          
                ))           
                ->add('valeur')
                ->add('abonne', 'checkbox', array(
                    'label' => 'Info portage abonné',
                    'required' => false,
                    'mapped' => false,
                    'attr' => array('checked' => $checkAbonne),
                        )
                )
                ->add('livraison', 'checkbox', array(
                    'label' => 'info portage point de livraison',
                    'required' => false,
                    'mapped' => false,
                    'attr' => array('checked' => $checkLivraion),
                    )
                )
                ->add('dateDebut', 'date', array(
                    'widget' => 'single_text',
                    'input' => 'datetime',
                    'format' => 'dd/MM/yyyy',
                    'attr' => array('class' => 'js-datepicker'),
                ))
                ->getForm();

        return $form;
    }

    /**
     * suppression infoPortage 
     *
     */
    public function suppAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $infoId = $request->query->get('id');
        $src = $request->query->get('src');
        $aboId = $request->query->get('cible');
        
        $infoPortage = $em->getRepository('AmsDistributionBundle:InfoPortage')->find($infoId);
        $adresse = $em->getRepository('AmsAdresseBundle:Adresse')->findOneByAbonneSoc($aboId);
        
        if($src == 'abo') {
            $abonne = $adresse->getAbonneSoc();
            $infoPortage->removeAbonne($abonne);
            $msg = "L'info portage portant sur l'abonné a bien été supprimé";
        } else {
            $livraison = $adresse->getPointLivraison();
            $infoPortage->removeLivraison($livraison);
            $msg = "L'info portage portant sur le point de livraison a bien été supprimé";
        }
        $em->persist($infoPortage);
        $em->flush();
        $return = array('message' => $msg);
        return new Response(json_encode($return),200, array('Content-Type'=>'application/json'));
    }
    
    
    /**
     * Vérifie si le type d'info portage existe 
     * alors on met la date de fin à la date debut du nouvel enregistrement
     * @param AbonneSoc $abonne
     * @param InfoPortage $infoportage 
     */
    public function setInfoPortageDateFin(AbonneSoc $abonne, InfoPortage $infoportage, \DateTime $date_fin) {
        
        $infoPortageAbonnes = $abonne->getInfosPortages();
        foreach ($infoPortageAbonnes as $infoPortageAbonne) {
            if ($infoPortageAbonne->getTypeInfoPortage() == $infoportage->getTypeInfoPortage()) {
                    //on sette la date de fin a j-1 du nouveau
                    $infoPortageAbonne->setDateFin($date_fin);
                }
        }
        
        $em = $this->getDoctrine()->getManager();
        $adresse = $em->getRepository('AmsAdresseBundle:Adresse')->findOneByAbonneSoc($abonne->getId());
        $infoLivraisons = $adresse->getRnvp()->getInfosPortagesLivraison();
        foreach ($infoLivraisons as $infoLivraison) {
            if ($infoLivraison->getTypeInfoPortage() == $infoportage->getTypeInfoPortage()) {
                     //on sette la date de fin a j-1 du nouveau
                    $infoLivraison->setDateFin($date_fin);
                }
        }
       
    }
    
    public function feuillePortageAction() {
       return $this->render('AmsDistributionBundle:FeuillePortage:header.html');
    }
  
  
  
    /**
     * tableau des infos portages
     * @param type $id
     * @return type
     */
    
    public function gridInfoPortageAbonneAction(Request $request) {
        $em = $this->getDoctrine()->getManager(); 
        $abonneId = $request->get('abonneId');
        $abonne = $em->getRepository('AmsAbonneBundle:AbonneSoc')->find($abonneId);
        //infoportage du point de livraion
        $infoLivraisons = $em->getRepository('AmsDistributionBundle:InfoPortage')->getInfoPortageLivraisonByAboSoc($abonneId);
        
        $actif = '
                    <option value="0">Désactivée</option>
                    <option value="1">Activée</option>';

        $response = $this->renderView('AmsDistributionBundle:InfoPortage:grid_portage.html.twig', array(
                    'infosPortages' => $abonne->getInfosPortages(),
                    'actif' => $actif,
                    'abonneId' => $abonneId,
                    'infoLivraisons' => $infoLivraisons,
        ));

        return new Response($response, 200, array('Content-Type' => 'Application/xml')); 
    }
    
    
    
     /**
     * modification info portage depuis la grid
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function infoPortageSaveAction(Request $request) {

        if (!$this->verif_acces())
            return false;
        $this->setDerniere_page();
        $em = $this->getDoctrine()->getManager();
        $mode = $request->get('!nativeeditor_status');
        $rowId = $request->get('gr_id');
        $newId = '';
        $action = '';
        $msg = '';
        $result = true;
        $session = new Session();
        $session->get('UTILISATEUR_ID');

        if ($mode == 'updated') {
            $user = $em->getRepository('AmsSilogBundle:Utilisateur')->find($session->get('UTILISATEUR_ID'));
            $action = 'update';
            $infoPortage = $em->getRepository('AmsDistributionBundle:InfoPortage')->find($request->get('c0'));
            $infoPortage->setActive($request->get('c1'));
            $infoPortage->setValeur($request->get('c3'));
            $infoPortage->setUtilisateur($user);
            $em->flush();
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
