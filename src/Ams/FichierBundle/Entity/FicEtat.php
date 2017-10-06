<?php

namespace Ams\FichierBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FicEtat
 *
 * @ORM\Table(name="fic_etat")
 * @ORM\Entity(repositoryClass="Ams\FichierBundle\Repository\FicEtatRepository")
 */
class FicEtat
{
    const STATE_CODE_OK = 0;
    const STATE_CODE_COPY = 2;
    const STATE_CODE_OVERWRITE = 40;
    const STATE_CODE_EMPTY_FILE = 51;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="code", type="integer", unique=true, nullable=false)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=100, nullable=false)
     */
    private $libelle;

    /**
     * @var integer
     *
     * @ORM\Column(name="poids_erreur", type="integer", nullable=false)
     */
    private $poidsErreur;


    /**
     * @var string
     *
     * @ORM\Column(name="couleur", type="string", length=7, nullable=false)
     */
    private $couleur;

    

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
     * @param integer $code
     * @return FicEtat
     */
    public function setCode($code)
    {
        $this->code = $code;
    
        return $this;
    }

    /**
     * Get code
     *
     * @return integer 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set libelle
     *
     * @param string $libelle
     * @return FicEtat
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
     * Set poidsErreur
     *
     * @param integer $poidsErreur
     * @return FicEtat
     */
    public function setPoidsErreur($poidsErreur)
    {
        $this->poidsErreur = $poidsErreur;
    
        return $this;
    }

    /**
     * Get poidsErreur
     *
     * @return integer 
     */
    public function getPoidsErreur()
    {
        return $this->poidsErreur;
    }

    /**
     * Set couleur
     *
     * @param string $couleur
     * @return FicEtat
     */
    public function setCouleur($couleur)
    {
        $this->couleur = $couleur;
    
        return $this;
    }

    /**
     * Get couleur
     *
     * @return string 
     */
    public function getCouleur()
    {
        return $this->couleur;
    }
}
