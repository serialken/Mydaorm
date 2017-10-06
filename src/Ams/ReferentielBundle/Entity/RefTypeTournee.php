<?php

namespace Ams\ReferentielBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RefTypeTournee
 *
 * @ORM\Table(name="ref_typetournee")
 * @ORM\Entity(repositoryClass="Ams\ReferentielBundle\Repository\RefTypeTourneeRepository")
 */
class RefTypeTournee
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
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
     * @ORM\Column(name="libelle", type="string", length=32, nullable=false)
     */
    private $libelle;

    /**
     * @var \RefSociete
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefEmpSociete")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="societe_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $societe;

    /**
     * @var \RefPopulation
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefPopulation")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="population_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $population;
}
