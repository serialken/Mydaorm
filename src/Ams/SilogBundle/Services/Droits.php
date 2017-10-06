<?php

namespace Ams\SilogBundle\Services;

use Ams\SilogBundle\Repository\UtilisateurRepository;
use Ams\SilogBundle\Entity\Utilisateur;
use Ams\SilogBundle\Resources\Entites\NavigationArborescence;
use Doctrine\ORM\EntityManager;

class Droits {

    private $em; //entity manager
    private $param;
    private $session;
    private $utl_id;
    private $duree_session;
    private $utilisateur;

    function __construct(\Doctrine\ORM\EntityManager $em, $session, $param) {
        $this->em = $em;
        $this->param = $param;
        $this->session = $session;
        $this->utl_id = '';
        $this->utilisateur = new Utilisateur();
        $this->duree_session = $param->get('DUREE_SESSION');
    }

    /**
     * Authentification et enregistrement des variables de session
     * @param type $utl_id
     * @param type $mdp
     * @return boolean
     */
    public function authentification($utl_id, $mdp) {
       
        $this->utilisateur = $this->em->getRepository('AmsSilogBundle:Utilisateur')
                ->findOneBy(array('login' => $utl_id, 'motDePasse' => $mdp, 'actif' => 1));

        if ($this->utilisateur) {
            $this->init_session();
            return true;
           
        }
        return false;
    }

    // initialise session
    public function init_session() {
   
        $this->session->set('UTILISATEUR_ID', $this->utilisateur->getId());
        $this->session->set('NOM', $this->utilisateur->getNom());
        $this->session->set('PRENOM', $this->utilisateur->getPrenom());
        $this->session->set('EMAIL', $this->utilisateur->getEmail());
        $this->session->set('GRP_DEPOTS', 	$this->utilisateur->getGrpdepot()->getLibelle());
         
        $grpDepot = $this->em->getRepository('AmsSilogBundle:GroupeDepot')->getGroupeAvecDepot($this->utilisateur->getGrpdepot()->getId());
        $liste_depots = array();
        foreach( $grpDepot->getDepots() as $depot ){
            $liste_depots[$depot->getId()] = $depot->getLibelle();
        }
        $this->session->set('DEPOTS', $liste_depots);	
        $this->session->set('depot_id', array_keys( $liste_depots)[0]);	
        
        $fluxs = $this->em->getRepository('AmsReferentielBundle:RefFlux')->getFluxs();
        $liste_flux = array();
        foreach($fluxs as $flux ){
                  $liste_flux[$flux->getId()] = $flux->getLibelle();
        }
        $this->session->set('FLUXS', $liste_flux);	
        $auj = new \DateTime();
        if ( $auj->format('H') < 12 ) 
            $flux_id = 1; // matin
        else 
            $flux_id = 2; // Aprés midi        
        $this->session->set('flux_id', $flux_id);
        
        $anneemoiss = $this->em->getRepository('AmsPaieBundle:PaiRefMois')->selectCombo();
        $liste_anneemois = array();
        foreach($anneemoiss as $anneemois ){
                  $liste_anneemois[$anneemois['anneemois']] = $anneemois['libelle'];
        }
        $this->session->set('ANNEEMOIS', $liste_anneemois);	
        $this->session->set('anneemois_id', $this->em->getRepository('AmsPaieBundle:PaiRefMois')->getMoisCourant());
        $this->session->set('pai_date_debut', $this->em->getRepository('AmsPaieBundle:PaiRefMois')->getDateDebutCourant());
        $this->session->set('pai_date_fin', $this->em->getRepository('AmsPaieBundle:PaiRefMois')->getDateFinCourant());
        $this->session->set('etalon_date_debut', $this->em->getRepository('AmsPaieBundle:PaiRefSemaine')->getDateDebutPrecedente());
        $this->session->set('etalon_date_fin', $this->em->getRepository('AmsPaieBundle:PaiRefSemaine')->getDateFinPrecedente());
        
        $this->session->set('date_distrib', $auj->format('Y-m-d'));
   
        $this->session->set('PROFIL_ID', $this->utilisateur->getProfil()->getId());
        $this->session->set('PROFIL', $this->utilisateur->getProfil()->getCode());
        $this->session->set('PROFIL_LIBELLE', $this->utilisateur->getProfil()->getLibelle());
        
      
       $pages= array();
       foreach ($this->utilisateur->getProfil()->getPageElements() as $pageElement){
            $pages[$pageElement->getPage()->getId()]= $pageElement->getPage()->getIdRoute();   
        }
        
        $this->session->set('PAGES', $pages);
        $aIdsPage = $pages;

        $this->m_a_j_session();
    }

    public function session_ok() {
        $iTpsCourant = time();
        if ($this->session->has('HEURE_FIN_SESSION')) {
            if ($this->session->get('HEURE_FIN_SESSION') >= $iTpsCourant) {
                $this->m_a_j_session();
                return true;
            }
            return false;
        }
        return false;
    }

    public function m_a_j_session() {
        $this->session->set('HEURE_FIN_SESSION', time() + $this->duree_session);
    }

    public function detruit_session() {
        $this->session->clear();
    }

}
