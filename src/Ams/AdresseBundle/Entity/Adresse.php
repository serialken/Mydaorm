<?php

namespace Ams\AdresseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index AS index;

/**
 * Adresse
 *
 * @ORM\Table(name="adresse", indexes={@index(name="info_abo_idx", columns={"vol1", "vol2", "vol3", "vol4", "vol5", "cp", "ville"})
 *                                      , @index(name="adresse_ext_idx", columns={"vol3", "vol4", "vol5", "cp", "ville"})
 *                                      , @index(name="vol12_rnvp_idx", columns={"vol1", "vol2", "rnvp_id"})
 *                                      , @index(name="date_idx", columns={"date_debut", "date_fin"})
 *                                      , @index(name="date_debut_idx", columns={"date_debut"})
 *                                      , @index(name="date_fin_idx", columns={"date_fin"})
 *                                      , @index(name="type_adresse_idx", columns={"type_adresse"})
 *                                  }
 *              )
 *           
 * @ORM\Entity(repositoryClass="Ams\AdresseBundle\Repository\AdresseRepository")
 */
class Adresse
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
     * @var \Ams\AbonneBundle\Entity\AbonneSoc
     *
     * @ORM\ManyToOne(targetEntity="\Ams\AbonneBundle\Entity\AbonneSoc")
     * @ORM\JoinColumn(name="abonne_soc_id", referencedColumnName="id", nullable=false)
     */
    private $abonneSoc;

    /**
     * @var string
     *
     * @ORM\Column(name="vol1", type="string", length=100, nullable=false)
     */
    private $vol1;

    /**
     * @var string
     *
     * @ORM\Column(name="vol2", type="string", length=100, nullable=false)
     */
    private $vol2;

    /**
     * @var string
     *
     * @ORM\Column(name="vol3", type="string", length=100, nullable=false)
     */
    private $vol3;

    /**
     * @var string
     *
     * @ORM\Column(name="vol4", type="string", length=100, nullable=false)
     */
    private $vol4;

    /**
     * @var string
     *
     * @ORM\Column(name="vol5", type="string", length=100, nullable=false)
     */
    private $vol5;

    /**
     * @var string
     *
     * @ORM\Column(name="cp", type="string", length=5, nullable=false)
     */
    private $cp;

    /**
     * @var string
     *
     * @ORM\Column(name="ville", type="string", length=45, nullable=false)
     */
    private $ville;

    /**
     * @var \AdresseRnvp
     *
     * @ORM\ManyToOne(targetEntity="\Ams\AdresseBundle\Entity\AdresseRnvp", inversedBy="adresses")
     * @ORM\JoinColumn(name="rnvp_id", referencedColumnName="id", nullable=true)
     */
    private $rnvp;
    
    
    
    /**
     * La valeur de cet attribut est une adresse RNVP
     * @var \Ams\AdresseBundle\Entity\AdresseRnvp
     *
     * @ORM\ManyToOne(targetEntity="\Ams\AdresseBundle\Entity\AdresseRnvp", inversedBy="adresses_livraison")
     * @ORM\JoinColumn(name="point_livraison_id", referencedColumnName="id", nullable=true)
     */
    private $pointLivraison;

    /**
     * @var \Ams\AdresseBundle\Entity\Commune
     *
     * @ORM\ManyToOne(targetEntity="\Ams\AdresseBundle\Entity\Commune")
     * @ORM\JoinColumn(name="commune_id", referencedColumnName="id", nullable=true)
     */
    private $commune;

    /**
     * @var \Ams\AdresseBundle\Entity\AdresseRnvpEtat
     *
     * @ORM\ManyToOne(targetEntity="\Ams\AdresseBundle\Entity\AdresseRnvpEtat")
     * @ORM\JoinColumn(name="adresse_rnvp_etat_id", referencedColumnName="id", nullable=true)
     */
    private $rnvpEtat;

    /**
     * @var \Ams\AdresseBundle\Entity\TypeChangement
     *
     * @ORM\ManyToOne(targetEntity="\Ams\AdresseBundle\Entity\TypeChangement")
     * @ORM\JoinColumn(name="type_changement_id", referencedColumnName="id", nullable=true)
     */
    private $typeChangement;

    /**
     * @var \Date
     *
     * @ORM\Column(name="date_debut", type="date", nullable=true)
     */
    private $dateDebut;

    /**
     * @var \Date
     *
     * @ORM\Column(name="date_fin", type="date", nullable=true)
     */
    private $dateFin;

    /**
     * @var \Utilisateur
     *
     * @ORM\ManyToOne(targetEntity="\Ams\SilogBundle\Entity\Utilisateur")
     * @ORM\JoinColumn(name="utl_id_modif", referencedColumnName="id", nullable=true)
     */
    private $utilisateurModif;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_modif", type="datetime", nullable=true)
     */
    private $dateModif;

    /**
     * L : si Adresse a livrer
     * R : si Adresse a reperer
     * @var string
     *
     * @ORM\Column(name="type_adresse", type="string", length=2, nullable=true)
     */
    private $typeAdresse;
    




    public function __construct()
    {
        $this->dateModif = new \Datetime();
    }




    

    

    
    
    

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
     * Set vol1
     *
     * @param string $vol1
     * @return Adresse
     */
    public function setVol1($vol1)
    {
        $this->vol1 = $vol1;
    
        return $this;
    }

    /**
     * Get vol1
     *
     * @return string 
     */
    public function getVol1()
    {
        return $this->vol1;
    }

    /**
     * Set vol2
     *
     * @param string $vol2
     * @return Adresse
     */
    public function setVol2($vol2)
    {
        $this->vol2 = $vol2;
    
        return $this;
    }

    /**
     * Get vol2
     *
     * @return string 
     */
    public function getVol2()
    {
        return $this->vol2;
    }

    /**
     * Set vol3
     *
     * @param string $vol3
     * @return Adresse
     */
    public function setVol3($vol3)
    {
        $this->vol3 = $vol3;
    
        return $this;
    }

    /**
     * Get vol3
     *
     * @return string 
     */
    public function getVol3()
    {
        return $this->vol3;
    }

    /**
     * Set vol4
     *
     * @param string $vol4
     * @return Adresse
     */
    public function setVol4($vol4)
    {
        $this->vol4 = $vol4;
    
        return $this;
    }

    /**
     * Get vol4
     *
     * @return string 
     */
    public function getVol4()
    {
        return $this->vol4;
    }

    /**
     * Set vol5
     *
     * @param string $vol5
     * @return Adresse
     */
    public function setVol5($vol5)
    {
        $this->vol5 = $vol5;
    
        return $this;
    }

    /**
     * Get vol5
     *
     * @return string 
     */
    public function getVol5()
    {
        return $this->vol5;
    }

    /**
     * Set cp
     *
     * @param string $cp
     * @return Adresse
     */
    public function setCp($cp)
    {
        $this->cp = $cp;
    
        return $this;
    }

    /**
     * Get cp
     *
     * @return string 
     */
    public function getCp()
    {
        return $this->cp;
    }

    /**
     * Set ville
     *
     * @param string $ville
     * @return Adresse
     */
    public function setVille($ville)
    {
        $this->ville = $ville;
    
        return $this;
    }

    /**
     * Get ville
     *
     * @return string 
     */
    public function getVille()
    {
        return $this->ville;
    }

    /**
     * Set dateDebut
     *
     * @param \Date $dateDebut
     * @return Adresse
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
     * @return Adresse
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
     * Set dateModif
     *
     * @param \DateTime $dateModif
     * @return Adresse
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
     * Set typeAdresse
     *
     * @param string $typeAdresse
     * @return ClientAServirLogist
     */
    public function setTypeAdresse($typeAdresse)
    {
        $this->typeAdresse = $typeAdresse;

        return $this;
    }

    /**
     * Get typeAdresse
     *
     * @return string 
     */
    public function getTypeAdresse()
    {
        return $this->typeAdresse;
    }

    /**
     * Set abonneSoc
     *
     * @param \Ams\AbonneBundle\Entity\AbonneSoc $abonneSoc
     * @return Adresse
     */
    public function setAbonneSoc(\Ams\AbonneBundle\Entity\AbonneSoc $abonneSoc)
    {
        $this->abonneSoc = $abonneSoc;
    
        return $this;
    }

    /**
     * Get abonneSoc
     *
     * @return \Ams\AbonneBundle\Entity\AbonneSoc 
     */
    public function getAbonneSoc()
    {
        return $this->abonneSoc;
    }

    /**
     * Set rnvp
     *
     * @param \Ams\AdresseBundle\Entity\AdresseRnvp $rnvp
     * @return Adresse
     */
    public function setRnvp(\Ams\AdresseBundle\Entity\AdresseRnvp $rnvp = null)
    {
        $this->rnvp = $rnvp;
    
        return $this;
    }

    /**
     * Get rnvp
     *
     * @return \Ams\AdresseBundle\Entity\AdresseRnvp 
     */
    public function getRnvp()
    {
        return $this->rnvp;
    }

    /**
     * Set pointLivraison
     *
     * @param \Ams\AdresseBundle\Entity\AdresseRnvp $pointLivraison
     * @return Adresse
     */
    public function setPointLivraison(\Ams\AdresseBundle\Entity\AdresseRnvp $pointLivraison = null)
    {
        $this->pointLivraison = $pointLivraison;
    
        return $this;
    }

    /**
     * Get pointLivraison
     *
     * @return \Ams\AdresseBundle\Entity\AdresseRnvp 
     */
    public function getPointLivraison()
    {
        return $this->pointLivraison;
    }

    /**
     * Set commune
     *
     * @param \Ams\AdresseBundle\Entity\Commune $commune
     * @return Adresse
     */
    public function setCommune(\Ams\AdresseBundle\Entity\Commune $commune = null)
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
     * Set rnvpEtat
     *
     * @param \Ams\AdresseBundle\Entity\AdresseRnvpEtat $rnvpEtat
     * @return Adresse
     */
    public function setRnvpEtat(\Ams\AdresseBundle\Entity\AdresseRnvpEtat $rnvpEtat = null)
    {
        $this->rnvpEtat = $rnvpEtat;
    
        return $this;
    }

    /**
     * Get rnvpEtat
     *
     * @return \Ams\AdresseBundle\Entity\AdresseRnvpEtat 
     */
    public function getRnvpEtat()
    {
        return $this->rnvpEtat;
    }

    /**
     * Set typeChangement
     *
     * @param \Ams\AdresseBundle\Entity\TypeChangement $typeChangement
     * @return Adresse
     */
    public function setTypeChangement(\Ams\AdresseBundle\Entity\TypeChangement $typeChangement = null)
    {
        $this->typeChangement = $typeChangement;
    
        return $this;
    }

    /**
     * Get typeChangement
     *
     * @return \Ams\AdresseBundle\Entity\TypeChangement 
     */
    public function getTypeChangement()
    {
        return $this->typeChangement;
    }

    /**
     * Set utilisateurModif
     *
     * @param \Ams\SilogBundle\Entity\Utilisateur $utilisateurModif
     * @return Adresse
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
