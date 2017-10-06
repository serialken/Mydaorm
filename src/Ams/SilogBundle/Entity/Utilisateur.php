<?php

namespace Ams\SilogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Utilisateur
 *
 * @ORM\Table(name="utilisateur")
 * @ORM\Entity(repositoryClass="Ams\SilogBundle\Repository\UtilisateurRepository")
 * @UniqueEntity(fields="login", message="Attention ce login  est déjà utilisé")
 */
class Utilisateur
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
     * @var string
     *
     * @ORM\Column(name="login", type="string", length=20, unique=true, nullable=false)
     */
    private $login;

    /**
     * @var string
     *
     * @ORM\Column(name="mot_de_passe", type="string", length=20, nullable=false)
     */
    private $motDePasse;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=45, nullable=false)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="prenom", type="string", length=45, nullable=true)
     */
    private $prenom;

    /**
     * @var string
     * @Assert\Email()
     * @ORM\Column(name="email", type="string", length=45, nullable=true)
     */
    private $email;
    
    
    /**
     *
     * @ORM\ManyToOne(targetEntity="GroupeDepot")
     * @ORM\JoinColumn(name="grp_depot_id", referencedColumnName="id")
     */
    private $grpdepot;
    
    
    
    /**
     *
     * @ORM\ManyToOne(targetEntity="Profil")
     * @ORM\JoinColumn(name="profil_id", referencedColumnName="id")
     */
    private $profil;
    
    /**
     * @var boolean
     * 0 => utilisateur desactive
     * 1 => utilisateur actif
     * @ORM\Column(name="actif", type="boolean", nullable=true,options={"default":1})
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
     * Set login
     *
     * @param string $login
     * @return Utilisateur
     */
    public function setLogin($login)
    {
        $this->login = $login;
    
        return $this;
    }

    /**
     * Get login
     *
     * @return string 
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * Set motDePasse
     *
     * @param string $motDePasse
     * @return Utilisateur
     */
    public function setMotDePasse($motDePasse)
    {
        $this->motDePasse = $motDePasse;
    
        return $this;
    }

    /**
     * Get motDePasse
     *
     * @return string 
     */
    public function getMotDePasse()
    {
        return $this->motDePasse;
    }

    /**
     * Set nom
     *
     * @param string $nom
     * @return Utilisateur
     */
    public function setNom($nom)
    {
        $this->nom = $nom;
    
        return $this;
    }

    /**
     * Get nom
     *
     * @return string 
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set prenom
     *
     * @param string $prenom
     * @return Utilisateur
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;
    
        return $this;
    }

    /**
     * Get prenom
     *
     * @return string 
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Utilisateur
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set grpdepot
     *
     * @param \Ams\SilogBundle\Entity\GroupeDepot $grpdepot
     * @return Utilisateur
     */
    public function setGrpdepot(\Ams\SilogBundle\Entity\GroupeDepot $grpdepot = null)
    {
        $this->grpdepot = $grpdepot;
    
        return $this;
    }

    /**
     * Get grpdepot
     *
     * @return \Ams\SilogBundle\Entity\GroupeDepot 
     */
    public function getGrpdepot()
    {
        return $this->grpdepot;
    }

    /**
     * Set profil
     *
     * @param \Ams\SilogBundle\Entity\Profil $profil
     * @return Utilisateur
     */
    public function setProfil(\Ams\SilogBundle\Entity\Profil $profil = null)
    {
        $this->profil = $profil;
    
        return $this;
    }

    /**
     * Get profil
     *
     * @return \Ams\SilogBundle\Entity\Profil 
     */
    public function getProfil()
    {
        return $this->profil;
    }

    /**
     * Set actif
     *
     * @param boolean $actif
     * @return Utilisateur
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
}
