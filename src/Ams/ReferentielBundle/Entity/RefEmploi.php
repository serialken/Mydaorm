<?php

namespace Ams\ReferentielBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RefEmploi
 *
 * @ORM\Table(name="ref_emploi")
 * @ORM\Entity(repositoryClass="Ams\ReferentielBundle\Repository\RefEmploiRepository")
 */
class RefEmploi
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
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=3, nullable=false)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=50, nullable=false)
     */
    private $libelle;

    /**
     * @var string
     *
     * @ORM\Column(name="codeNG", type="string", length=6, nullable=false, unique=true)
     */
    private $codeNG;

    /**
     * @var boolean
     *
     * @ORM\Column(name="paie", type="boolean", nullable=false)
     */
    private $paie;

    /**
     * @var boolean
     *
     * @ORM\Column(name="prime", type="boolean", nullable=false)
     */
    private $prime;

    /**
     * @var boolean
     *
     * @ORM\Column(name="affichage_modele_tournee", type="boolean", nullable=false)
     */
    private $affichageModeleTournee;

    /**
     * @var boolean
     *
     * @ORM\Column(name="affichage_modele_activite", type="boolean", nullable=false)
     */
    private $affichageModeleActivite;
        
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
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
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
     * Set code
     *
     * @param string $code
     * @return RefEmploi
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Set libelle
     *
     * @param string $libelle
     * @return RefEmploi
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Set id
     *
     * @param integer $id
     * @return RefEmploi
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set codeNG
     *
     * @param string $codeNG
     * @return RefEmploi
     */
    public function setCodeNG($codeNG)
    {
        $this->codeNG = $codeNG;

        return $this;
    }

    /**
     * Get codeNG
     *
     * @return string 
     */
    public function getCodeNG()
    {
        return $this->codeNG;
    }
}
