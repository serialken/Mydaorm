<?php

namespace Ams\PaieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PaiRefPostepaieGeneral
 *
 * @ORM\Table(name="pai_ref_postepaie_general")
 * @ORM\Entity(repositoryClass="Ams\PaieBundle\Repository\PaiRefPostePaieGeneralRepository")
 */
class PaiRefPostePaieGeneral
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
     * @ORM\Column(name="code", type="string", length=3, nullable=false, unique=true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=128, nullable=false)
     */
    private $libelle;

    /**
     * @var string
     *
     * @ORM\Column(name="poste", type="string", length=10, nullable=false, unique=true)
     */
    private $poste;

    /**
     * @var string
     *
     * @ORM\Column(name="annexe", type="string", length=4, nullable=false)
     */
    private $annexe;

    /**
     * @var boolean
     *
     * @ORM\Column(name="semaine", type="boolean", nullable=false)
     */
    private $semaine;

    /**
     * @var boolean
     *
     * @ORM\Column(name="dimanche", type="boolean", nullable=false)
     */
    private $dimanche;

    /**
     * @var boolean
     *
     * @ORM\Column(name="ferie", type="boolean", nullable=false)
     */
    private $ferie;

    /**
     * @var boolean
     *
     * @ORM\Column(name="taux", type="boolean", nullable=false)
     */
    private $taux;

    /**
     * @var boolean
     *
     * @ORM\Column(name="montant", type="boolean", nullable=false)
     */
    private $montant;
    /**
     * @var string
     *
     * @ORM\Column(name="majoration", type="decimal", precision=5, scale=2, nullable=false)
     */
    private $majoration;

    /**
     * @var \RefTypeUrssaf
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefTypeUrssaf")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="typeurssaf_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $typeurssaf;

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
     * @return PaiRefPostepaieGeneral
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
     * @return PaiRefPostepaieGeneral
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
     * Set poste
     *
     * @param string $poste
     * @return PaiRefPostepaieGeneral
     */
    public function setPoste($poste)
    {
        $this->poste = $poste;

        return $this;
    }

    /**
     * Get poste
     *
     * @return string 
     */
    public function getPoste()
    {
        return $this->poste;
    }

    /**
     * Set semaine
     *
     * @param boolean $semaine
     * @return PaiRefPostepaieGeneral
     */
    public function setSemaine($semaine)
    {
        $this->semaine = $semaine;

        return $this;
    }

    /**
     * Get semaine
     *
     * @return boolean 
     */
    public function getSemaine()
    {
        return $this->semaine;
    }

    /**
     * Set dimanche
     *
     * @param boolean $dimanche
     * @return PaiRefPostepaieGeneral
     */
    public function setDimanche($dimanche)
    {
        $this->dimanche = $dimanche;

        return $this;
    }

    /**
     * Get dimanche
     *
     * @return boolean 
     */
    public function getDimanche()
    {
        return $this->dimanche;
    }

    /**
     * Set ferie
     *
     * @param boolean $ferie
     * @return PaiRefPostepaieGeneral
     */
    public function setFerie($ferie)
    {
        $this->ferie = $ferie;

        return $this;
    }

    /**
     * Get ferie
     *
     * @return boolean 
     */
    public function getFerie()
    {
        return $this->ferie;
    }

    /**
     * Set typeurssaf
     *
     * @param string $typeurssaf
     * @return PaiRefPostepaieGeneral
     */
    public function setTypeurssaf($typeurssaf)
    {
        $this->typeurssaf = $typeurssaf;

        return $this;
    }

    /**
     * Get typeurssaf
     *
     * @return string 
     */
    public function getTypeurssaf()
    {
        return $this->typeurssaf;
    }
}
