<?php

namespace Ams\AdresseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraint as Assert;
use Ams\ExtensionBundle\Validator\Constraints as AmsAssert;
use Doctrine\ORM\Mapping\EntityListeners;
use DateTime;

/**
 * DepotCommune
 *
 * @ORM\Table(name="depot_commune")
 * @ORM\Entity(repositoryClass="Ams\AdresseBundle\Repository\DepotCommuneRepository")
 * @ORM\EntityListeners({ "Ams\AdresseBundle\Listener\DepotCommuneListener" })
 * 
 */
class DepotCommune
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
     private $id;
    
     /**
     * @var \Depot
     *
     * @ORM\ManyToOne(targetEntity="Ams\SilogBundle\Entity\Depot", inversedBy="depotCommunes")
     * @ORM\JoinColumn(name="depot_id", referencedColumnName="id", nullable=false)
     */
    private $depot;
    
    /**
     * @var \Commune
     *
     * @ORM\ManyToOne(targetEntity="Ams\AdresseBundle\Entity\Commune", inversedBy="depotCommunes")
     * @ORM\JoinColumn(name="commune_id", referencedColumnName="id", nullable=false)
     */
    private $commune;  
    
    /**
     * @var \Utilisateur
     *
     * @ORM\ManyToOne(targetEntity="\Ams\SilogBundle\Entity\Utilisateur")
     * @ORM\JoinColumn(name="utilisateur_modif", referencedColumnName="id", nullable=true)
     */
    private $utilisateurModif;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_modif", type="datetime", nullable=true)
     */
    private $dateModif;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_debut", type="datetime", nullable=true)
     * @AmsAssert\DatePosterieure
     */
    private $dateDebut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_fin", type="datetime", nullable=true)
     */
    private $dateFin;
    
    
    /**
     * @var Ams\ReferentielBundle\Entity\RefFlux
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefFlux")
     * @ORM\JoinColumn(name="flux_id", referencedColumnName="id", nullable=true)
     */
    private $flux;
    
    
    public function __construct($date_fin) {
        $this->dateFin =  $date_fin;
    }
    
    /**
     * Set flux
     *
     * @param \Ams\ReferentielBundle\Entity\RefFlux $flux
     * @return Produit
     */
    public function setFlux(\Ams\ReferentielBundle\Entity\RefFlux $flux)
    {
        $this->flux = $flux;
    
        return $this;
    }

    /**
     * Get flux
     *
     * @return \Ams\ReferentielBundle\Entity\RefFlux 
     */
    public function getFlux()
    {
        return $this->flux;
    } 
    
    /**
     * Set depot
     *
     * @param \Ams\SilogBundle\Entity\Depot $depot
     * @return DepotCommune
     */
    public function setDepot(\Ams\SilogBundle\Entity\Depot $depot)
    {
        $this->depot = $depot;
    
        return $this;
    }

    /**
     * Get depot
     *
     * @return \Ams\SilogBundle\Entity\Depot 
     */
    public function getDepot()
    {
        return $this->depot;
    }

    /**
     * Set commune
     *
     * @param \Ams\AdresseBundle\Entity\Commune $commune
     * @return DepotCommune
     */
    public function setCommune(\Ams\AdresseBundle\Entity\Commune $commune)
    {
        $this->commune = $commune;
    
        return $this;
    }

    /**
     * Get commune
     *
     * @return \Ams\AdresseBundle\Entity\Commune 
     */
    public function getCommune()
    {
        return $this->commune;
    }

    /**
     * Set dateModif
     *
     * @param \DateTime $dateModif
     * @return DepotCommune
     */
    public function setDateModif($dateModif)
    {
        $this->dateModif = $dateModif;
    
        return $this;
    }

    /**
     * Get dateModif
     *
     * @return \DateTime 
     */
    public function getDateModif()
    {
        return $this->dateModif;
    }

    /**
     * Set dateDebut
     *
     * @param \DateTime $dateDebut
     * @return DepotCommune
     */
    public function setDateDebut($dateDebut)
    {
        $this->dateDebut = $dateDebut;
    
        return $this;
    }

    /**
     * Get dateDebut
     *
     * @return \DateTime 
     */
    public function getDateDebut()
    {
        return $this->dateDebut;
    }

    /**
     * Set dateFin
     *
     * @param \DateTime $dateFin
     * @return DepotCommune
     */
    public function setDateFin($dateFin)
    {
        $this->dateFin = $dateFin;
    
        return $this;
    }

    /**
     * Get dateFin
     *
     * @return \DateTime 
     */
    public function getDateFin()
    {
        return $this->dateFin;
    }

    /**
     * Set utilisateurModif
     *
     * @param \Ams\SilogBundle\Entity\Utilisateur $utilisateurModif
     * @return DepotCommune
     */
    public function setUtilisateurModif(\Ams\SilogBundle\Entity\Utilisateur $utilisateurModif = null)
    {
        $this->utilisateurModif = $utilisateurModif;
    
        return $this;
    }

    /**
     * Get utilisateurModif
     *
     * @return \Ams\SilogBundle\Entity\Utilisateur 
     */
    public function getUtilisateurModif()
    {
        return $this->utilisateurModif;
    }
    
}