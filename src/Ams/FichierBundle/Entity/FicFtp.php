<?php

namespace Ams\FichierBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FicFtp
 *
 * @ORM\Table(name="fic_ftp")
 * @ORM\Entity
 */
class FicFtp
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
     * @ORM\Column(name="code", type="string", length=40, unique=true, nullable=false)
     */
    private $code;
    
    /**
     * Identifiant de la societe de distrib. Expl : Pour Jade, c'est "39"
     * @var string
     *
     * @ORM\Column(name="id_soc_distrib", type="string", length=10, nullable=true)
     */
    private $idSocDistrib;

    /**
     * @var string
     *
     * @ORM\Column(name="serveur", type="string", length=45, nullable=false)
     */
    private $serveur;

    /**
     * @var string
     *
     * @ORM\Column(name="login", type="string", length=45, nullable=false)
     */
    private $login;

    /**
     * @var string
     *
     * @ORM\Column(name="mdp", type="string", length=45, nullable=false)
     */
    private $mdp;

    /**
     * @var string
     *
     * @ORM\Column(name="repertoire", type="string", length=255, nullable=true)
     */
    private $repertoire;

    /**
     * @var string
     *
     * @ORM\Column(name="rep_sauvegarde", type="string", length=255, nullable=true)
     */
    private $repSauvegarde;



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
     * Set code
     *
     * @param string $code
     * @return FicFtp
     */
    public function setCode($code)
    {
        $this->code = $code;
    
        return $this;
    }
    
    /**
     * Get idSocDistrib
     *
     * @return string 
     */
    public function getIdSocDistrib()
    {
        return $this->idSocDistrib;
    }

    /**
     * Set idSocDistrib
     *
     * @param string $idSocDistrib
     * @return FicFtp
     */
    public function setIdSocDistrib($idSocDistrib)
    {
        $this->idSocDistrib = $idSocDistrib;
    
        return $this;
    }

    /**
     * Set serveur
     *
     * @param string $serveur
     * @return FicFtp
     */
    public function setServeur($serveur)
    {
        $this->serveur = $serveur;
    
        return $this;
    }

    /**
     * Get serveur
     *
     * @return string 
     */
    public function getServeur()
    {
        return $this->serveur;
    }

    /**
     * Set login
     *
     * @param string $login
     * @return FicFtp
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
     * Set mdp
     *
     * @param string $mdp
     * @return FicFtp
     */
    public function setMdp($mdp)
    {
        $this->mdp = $mdp;
    
        return $this;
    }

    /**
     * Get mdp
     *
     * @return string 
     */
    public function getMdp()
    {
        return $this->mdp;
    }

    /**
     * Set repertoire
     *
     * @param string $repertoire
     * @return FicFtp
     */
    public function setRepertoire($repertoire)
    {
        $this->repertoire = $repertoire;
    
        return $this;
    }

    /**
     * Get repertoire
     *
     * @return string 
     */
    public function getRepertoire()
    {
        return $this->repertoire;
    }

    /**
     * Set repSauvegarde
     *
     * @param string $repSauvegarde
     * @return FicFtp
     */
    public function setRepSauvegarde($repSauvegarde)
    {
        $this->repSauvegarde = $repSauvegarde;
    
        return $this;
    }

    /**
     * Get repSauvegarde
     *
     * @return string 
     */
    public function getRepSauvegarde()
    {
        return $this->repSauvegarde;
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
}
