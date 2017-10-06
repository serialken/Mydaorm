<?php

namespace Ams\ModeleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint as UniqueConstraint;

/**
 * ModeleTournee
 *
 * @ORM\Table(name="modele_tournee"
 * 			,uniqueConstraints={@UniqueConstraint(name="un_modele_tournee",columns={"groupe_id","numero"})}
 * 			)
 * @ORM\Entity(repositoryClass="Ams\ModeleBundle\Repository\ModeleTourneeRepository")
 */
class ModeleTournee
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \Groupe
     *
     * @ORM\ManyToOne(targetEntity="Ams\ModeleBundle\Entity\GroupeTournee", inversedBy="modeles_tournees")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="groupe_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $groupe;
    
    /**
     * @ORM\OneToMany(targetEntity="Ams\ModeleBundle\Entity\ModeleTourneeJour", mappedBy="tournee")
     */
    private $tourneesJour;
    
       /**
     * @var string
     *
     * @ORM\Column(name="numero", type="string", length=3, nullable=false)
     */
    private $numero;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=11, nullable=false, unique=true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="codeDCS", type="string", length=3, nullable=true, unique=true)
     */
    private $codeDCS;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=64, nullable=false)
     */
    private $libelle;

    /**
     * @var \Employe
     *
     * @ORM\ManyToOne(targetEntity="Ams\EmployeBundle\Entity\Employe")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="employe_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $employe;
   
    /**
     * @var boolean
     *
     * @ORM\Column(name="actif", type="boolean", nullable=false)
     */
    private $actif;
	
    /**
     * @var \Utilisateur
     *
     * @ORM\ManyToOne(targetEntity="Ams\SilogBundle\Entity\Utilisateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="utilisateur_id", referencedColumnName="id")
     * })
     */
    private $utilisateur;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_creation", type="datetime", nullable=false)
     */
    private $date_creation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_modif", type="datetime", nullable=true)
     */
    private $date_modif;

  

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
     * Set code
     *
     * @param string $code
     * @return ModeleTournee
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set codeDCS
     *
     * @param string $codeDCS
     * @return ModeleTournee
     */
    public function setCodeDCS($codeDCS)
    {
        $this->codeDCS = $codeDCS;

        return $this;
    }

    /**
     * Get codeDCS
     *
     * @return string 
     */
    public function getCodeDCS()
    {
        return $this->codeDCS;
    }

    /**
     * Set libelle
     *
     * @param string $libelle
     * @return ModeleTournee
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelle
     *
     * @return string 
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Set actif
     *
     * @param boolean $actif
     * @return ModeleTournee
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
     * Set numero
     *
     * @param string $numero
     * @return ModeleTournee
     */
    public function setNumero($numero)
    {
        $this->numero = $numero;

        return $this;
    }

    /**
     * Get numero
     *
     * @return string 
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * Set date_creation
     *
     * @param \DateTime $dateCreation
     * @return ModeleTournee
     */
    public function setDateCreation($dateCreation)
    {
        $this->date_creation = $dateCreation;

        return $this;
    }

    /**
     * Get date_creation
     *
     * @return \DateTime 
     */
    public function getDateCreation()
    {
        return $this->date_creation;
    }

    /**
     * Set date_modif
     *
     * @param \DateTime $dateModif
     * @return ModeleTournee
     */
    public function setDateModif($dateModif)
    {
        $this->date_modif = $dateModif;

        return $this;
    }

    /**
     * Get date_modif
     *
     * @return \DateTime 
     */
    public function getDateModif()
    {
        return $this->date_modif;
    }

    /**
     * Set groupe
     *
     * @param \Ams\ModeleBundle\Entity\GroupeTournee $groupe
     * @return ModeleTournee
     */
    public function setGroupe(\Ams\ModeleBundle\Entity\GroupeTournee $groupe)
    {
        $this->groupe = $groupe;

        return $this;
    }

    /**
     * Get groupe
     *
     * @return \Ams\ModeleBundle\Entity\GroupeTournee 
     */
    public function getGroupe()
    {
        return $this->groupe;
    }
    
    /**
     * Retourne les modèles tournée jour liés
     * 
     */
    public function getTourneesJour(){
        return $this->tourneesJour;
    }

    /**
     * Set employe
     *
     * @param \Ams\EmployeBundle\Entity\Employe $employe
     * @return ModeleTournee
     */
    public function setEmploye(\Ams\EmployeBundle\Entity\Employe $employe = null)
    {
        $this->employe = $employe;

        return $this;
    }

    /**
     * Get employe
     *
     * @return \Ams\EmployeBundle\Entity\Employe 
     */
    public function getEmploye()
    {
        return $this->employe;
    }

    /**
     * Set utilisateur
     *
     * @param \Ams\SilogBundle\Entity\Utilisateur $utilisateur
     * @return ModeleTournee
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
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tourneesJour = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add tourneesJour
     *
     * @param \Ams\ModeleBundle\Entity\ModeleTourneeJour $tourneesJour
     * @return ModeleTournee
     */
    public function addTourneesJour(\Ams\ModeleBundle\Entity\ModeleTourneeJour $tourneesJour)
    {
        $this->tourneesJour[] = $tourneesJour;

        return $this;
    }

    /**
     * Remove tourneesJour
     *
     * @param \Ams\ModeleBundle\Entity\ModeleTourneeJour $tourneesJour
     */
    public function removeTourneesJour(\Ams\ModeleBundle\Entity\ModeleTourneeJour $tourneesJour)
    {
        $this->tourneesJour->removeElement($tourneesJour);
    }
}
