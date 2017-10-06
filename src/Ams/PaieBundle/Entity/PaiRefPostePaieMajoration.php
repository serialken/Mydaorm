<?php

namespace Ams\PaieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PaiRefPostepaieMajoration
 *
 * @ORM\Table(name="pai_ref_postepaie_majoration", uniqueConstraints={@ORM\UniqueConstraint(name="un_ref_postepaie_majoration", columns={"code", "population_id"})}, indexes={@ORM\Index(name="fk_pai_ref_postepaie_majoration_pai_ref_population1_idx", columns={"population_id"})})
 * @ORM\Entity(repositoryClass="Ams\PaieBundle\Repository\PaiRefPostePaieMajorationRepository")
 */
class PaiRefPostePaieMajoration
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
     * @ORM\Column(name="code", type="string", length=2, nullable=false)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="poste", type="string", length=10, nullable=false)
     */
    private $poste;

    /**
     * @var \RefPopulation
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefPopulation")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="population_id", referencedColumnName="id")
     * })
     */
    private $population;

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
     * @return PaiRefPostepaieMajoration
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
     * Set poste
     *
     * @param string $poste
     * @return PaiRefPostepaieMajoration
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
     * Set population
     *
     * @param \Ams\ReferentielBundle\Entity\RefPopulation $population
     * @return PaiRefPostepaieMajoration
     */
    public function setPopulation(\Ams\ReferentielBundle\Entity\RefPopulation $population = null)
    {
        $this->population = $population;

        return $this;
    }

    /**
     * Get population
     *
     * @return \Ams\ReferentielBundle\Entity\RefPopulation 
     */
    public function getPopulation()
    {
        return $this->population;
    }
}
