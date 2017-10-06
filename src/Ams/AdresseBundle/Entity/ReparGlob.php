<?php

namespace Ams\AdresseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ReparGlob
 *
 * @ORM\Table(name="repar_glob")
 * @ORM\Entity(repositoryClass="Ams\AdresseBundle\Repository\ReparGlobRepository")
 */
class ReparGlob
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @var \Ams\ReferentielBundle\Entity\RefFlux
     * @ORM\ManyToOne(targetEntity="\Ams\ReferentielBundle\Entity\RefFlux")
     * @ORM\JoinColumn(name="flux_id", referencedColumnName="id", nullable=true)
     */
    private $fluxId;

    /**
     * @var \Ams\AdresseBundle\Entity\Commune
     *
     * @ORM\ManyToOne(targetEntity="\Ams\AdresseBundle\Entity\Commune")
     * @ORM\JoinColumn(name="commune_id", referencedColumnName="id", nullable=true)
     */
    private $communeId;

     /**
     * @var \Depot
     *
     * @ORM\ManyToOne(targetEntity="Ams\SilogBundle\Entity\Depot")
     * @ORM\JoinColumn(name="depot_id", referencedColumnName="id", nullable=false)
     */
    private $depotId;

    /**
     * @var \Utilisateur
     *
     * @ORM\ManyToOne(targetEntity="\Ams\SilogBundle\Entity\Utilisateur")
     * @ORM\JoinColumn(name="utilisateur_id", referencedColumnName="id", nullable=true)
     */
    private $utilisateurModif;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_modif", type="datetime")
     */
    private $dateModif;

    /**
     * @var \Date
     *
     * @ORM\Column(name="date_debut", type="datetime")
     */
    private $dateDebut;

    /**
     * @var \Date
     *
     * @ORM\Column(name="date_fin", type="datetime")
     */
    private $dateFin;
    /**
     * @var \Ams\ReferentielBundle\Entity\RefFlux
     * @ORM\ManyToOne(targetEntity="\Ams\ReferentielBundle\Entity\RefFlux")
     * @ORM\JoinColumn(name="flux_id", referencedColumnName="id", nullable=true)
     */
    private $fluxId2;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set fluxId
     *
     * @param \Ams\ReferentielBundle\Entity\RefFlux fluxId
     * @return ReparGlob
     */
    public function setFluxId(\Ams\ReferentielBundle\Entity\RefFlux $fluxId)
    {
        $this->fluxId = $fluxId;

        return $this;
    }

    /**
     * Get fluxId
     *
     * @return integer 
     */
    public function getFluxId()
    {
        return $this->fluxId;
    }

    /**
     * Set communeId
     *
     * @param \Ams\AdresseBundle\Entity\Commune $communeId
     * @return ReparGlob
     */
    public function setCommuneId(\Ams\AdresseBundle\Entity\Commune $communeId)
    {
        $this->communeId = $communeId;

        return $this;
    }

    /**
     * Get communeId
     *
     * @return integer 
     */
    public function getCommuneId()
    {
        return $this->communeId;
    }

    /**
     * Set depotId
     *
     * @param \Ams\SilogBundle\Entity\Depot $depotId
     * @return ReparGlob
     */
    public function setDepotId(\Ams\SilogBundle\Entity\Depot $depotId)
    {
        $this->depotId = $depotId;

        return $this;
    }

    /**
     * Get depotId
     *
     * @return integer 
     */
    public function getDepotId()
    {
        return $this->depotId;
    }

    /**
     * Set utilisateurModif
     *
     * @param \Ams\SilogBundle\Entity\Utilisateur $utilisateurModif
     * @return ReparGlob
     */
    public function setUtilisateurModif(\Ams\SilogBundle\Entity\Utilisateur $utilisateurModif)
    {
        $this->utilisateurModif = $utilisateurModif;

        return $this;
    }

    /**
     * Get utilisateurModif
     *
     * @return integer 
     */
    public function getUtilisateurModif()
    {
        return $this->utilisateurModif;
    }

    /**
     * Set dateModif
     *
     * @param \DateTime $dateModif
     * @return ReparGlob
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
     * @param \Date $dateDebut
     * @return ReparGlob
     */
    public function setDateDebut($dateDebut)
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    /**
     * Get dateDebut
     *
     * @return \Date
     */
    public function getDateDebut()
    {
        return $this->dateDebut;
    }

    /**
     * Set dateFin
     *
     * @param \Date $dateFin
     * @return ReparGlob
     */
    public function setDateFin($dateFin)
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    /**
     * Get dateFin
     *
     * @return \Date 
     */
    public function getDateFin()
    {
        return $this->dateFin;
    }
}
