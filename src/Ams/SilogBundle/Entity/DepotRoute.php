<?php

namespace Ams\SilogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * DepotRoute
 * 
 * @ORM\Table(name="depot_route")
 * @ORM\Entity(repositoryClass="Ams\SilogBundle\Repository\DepotRouteRepository")
 * @UniqueEntity(fields="code_route", message="Le code route saisi existe deja !")
 */
class DepotRoute
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
     * @var string
     * 
     * @ORM\Column(name="code_route", type="string", length=10, unique=true, nullable=false)
     */
    private $codeRoute;
    
    /**
     * @var string
     * 
     * @ORM\Column(name="libelle_route", type="string", length=40, nullable=true) 
     */
    private $libelleRoute;
    
    /**
     * @var string
     * 
     * @ORM\Column(name="code_centre", type="string", length=3, nullable=false)
     */
    private $codeCentre;
    
    /**
     * @var string
     * 
     * @ORM\Column(name="libelle_centre", type="string", length=40, nullable=true)
     */
    private $libelleCentre;
    
    /**
     *
     * @var \Utilisateur
     * 
     * @ORM\ManyToOne(targetEntity="Utilisateur")
     * @ORM\JoinColumn(name="utilisateur_id", referencedColumnName="id", nullable=true)
     */
    private $utilisateur;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="date", nullable=true)
     */
    private $updatedAt;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="date", nullable=false)
     */
    private $createdAt;
    
    /**
     * @var boolean
     * 
     * @ORM\Column(name="actif", type="boolean")
     */
    private $actif;

    
    

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
     * Set codeRoute
     *
     * @param string $codeRoute
     * @return DepotRoute
     */
    public function setCodeRoute($codeRoute)
    {
        $this->codeRoute = $codeRoute;

        return $this;
    }

    /**
     * Get codeRoute
     *
     * @return string 
     */
    public function getCodeRoute()
    {
        return $this->codeRoute;
    }

    /**
     * Set libelleRoute
     *
     * @param string $libelleRoute
     * @return DepotRoute
     */
    public function setLibelleRoute($libelleRoute)
    {
        $this->libelleRoute = $libelleRoute;

        return $this;
    }

    /**
     * Get libelleRoute
     *
     * @return string 
     */
    public function getLibelleRoute()
    {
        return $this->libelleRoute;
    }

    /**
     * Set codeCentre
     *
     * @param string $codeCentre
     * @return DepotRoute
     */
    public function setCodeCentre($codeCentre)
    {
        $this->codeCentre = $codeCentre;

        return $this;
    }

    /**
     * Get codeCentre
     *
     * @return string 
     */
    public function getCodeCentre()
    {
        return $this->codeCentre;
    }

    /**
     * Set libelleCentre
     *
     * @param string $libelleCentre
     * @return DepotRoute
     */
    public function setLibelleCentre($libelleCentre)
    {
        $this->libelleCentre = $libelleCentre;

        return $this;
    }

    /**
     * Get libelleCentre
     *
     * @return string 
     */
    public function getLibelleCentre()
    {
        return $this->libelleCentre;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return DepotRoute
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return DepotRoute
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set actif
     *
     * @param boolean $actif
     * @return DepotRoute
     */
    public function setActif($actif)
    {
        $this->actif = $actif;

        return $this;
    }

    /**
     * Get actif
     *
     * @return boolean 
     */
    public function getActif()
    {
        return $this->actif;
    }

    /**
     * Set utilisateur
     *
     * @param \Ams\SilogBundle\Entity\Utilisateur $utilisateur
     * @return DepotRoute
     */
    public function setUtilisateur(\Ams\SilogBundle\Entity\Utilisateur $utilisateur = null)
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    /**
     * Get utilisateur
     *
     * @return \Ams\SilogBundle\Entity\Utilisateur 
     */
    public function getUtilisateur()
    {
        return $this->utilisateur;
    }
}
