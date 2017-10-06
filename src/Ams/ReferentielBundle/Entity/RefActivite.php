<?php

namespace Ams\ReferentielBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RefActivite
 *
 * @ORM\Table(name="ref_activite")
 * @ORM\Entity(repositoryClass="Ams\ReferentielBundle\Repository\RefActiviteRepository")
 */
class RefActivite
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
     * @ORM\Column(name="code", type="string", length=2, unique=true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=32, unique=true)
     */
    private $libelle;

    /**
     * @var string
     *
     * @ORM\Column(name="couleur", type="string", length=7)
     */
    private $couleur;
   
    /**
     * @var boolean
     *
     * @ORM\Column(name="actif", type="boolean", nullable=false, options={"default"=1})
     */
    private $actif;

    /**
     * @var boolean
     *
     * @ORM\Column(name="affichage_modele", type="boolean", nullable=false, options={"default"=0})
     */
    private $afficheModele;

    /**
     * @var boolean
     *
     * @ORM\Column(name="km_paye", type="boolean", nullable=false, options={"default"=1})
     */
    private $kmPaye;

    /**
     * @var boolean
     *
     * @ORM\Column(name="est_hors_presse", type="boolean", nullable=false, options={"default"=0})
     */
    private $estHorsPresse;

    /**
     * @var boolean
     *
     * @ORM\Column(name="est_hors_travail", type="boolean", nullable=false, options={"default"=0})
     */
    private $estHorsTravail;

    /**
     * @var boolean
     *
     * @ORM\Column(name="est_pleiades", type="boolean", nullable=false, options={"default"=0})
     */
    private $estPleiades;

    /**
     * @var boolean
     *
     * @ORM\Column(name="est_garantie", type="boolean", nullable=false, options={"default"=0})
     */
    private $estGarantie;

    /**
     * @var boolean
     *
     * @ORM\Column(name="est_badge", type="boolean", nullable=false, options={"default"=1})
     */
    private $estBadge;

    /**
     * @var boolean
     *
     * @ORM\Column(name="est_JTPX", type="boolean", nullable=false, options={"default"=0})
     */
    private $estJTPX;

    /**
     * @var boolean
     *
     * @ORM\Column(name="est_1mai", type="boolean", nullable=false, options={"default"=0})
     */
    private $est1Mai;
    
    /**
     * @var \Utilisateur
     *
     * @ORM\ManyToOne(targetEntity="Ams\SilogBundle\Entity\Utilisateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="utilisateur_id", referencedColumnName="id", nullable=false)
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
     * @return RefActivite
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
     * Set libelle
     *
     * @param string $libelle
     * @return RefActivite
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
     * Set date_debut
     *
     * @param \DateTime $dateDebut
     * @return RefActivite
     */
    public function setDateDebut($dateDebut)
    {
        $this->date_debut = $dateDebut;

        return $this;
    }

    /**
     * Get date_debut
     *
     * @return \DateTime 
     */
    public function getDateDebut()
    {
        return $this->date_debut;
    }

    /**
     * Set date_fin
     *
     * @param \DateTime $dateFin
     * @return RefActivite
     */
    public function setDateFin($dateFin)
    {
        $this->date_fin = $dateFin;

        return $this;
    }

    /**
     * Get date_fin
     *
     * @return \DateTime 
     */
    public function getDateFin()
    {
        return $this->date_fin;
    }

    /**
     * Set afficheModele
     *
     * @param boolean $afficheModele
     * @return RefActivite
     */
    public function setAfficheModele($afficheModele)
    {
        $this->afficheModele = $afficheModele;

        return $this;
    }

    /**
     * Get afficheModele
     *
     * @return boolean 
     */
    public function getAfficheModele()
    {
        return $this->afficheModele;
    }

    /**
     * Set afficheDuree
     *
     * @param boolean $afficheDuree
     * @return RefActivite
     */
    public function setAfficheDuree($afficheDuree)
    {
        $this->afficheDuree = $afficheDuree;

        return $this;
    }

    /**
     * Get afficheDuree
     *
     * @return boolean 
     */
    public function getAfficheDuree()
    {
        return $this->afficheDuree;
    }

    /**
     * Set afficheKm
     *
     * @param boolean $afficheKm
     * @return RefActivite
     */
    public function setAfficheKm($kmPaye)
    {
        $this->kmPaye = $kmPaye;

        return $this;
    }

    /**
     * Get afficheKm
     *
     * @return boolean 
     */
    public function getAfficheKm()
    {
        return $this->kmPaye;
    }
}
