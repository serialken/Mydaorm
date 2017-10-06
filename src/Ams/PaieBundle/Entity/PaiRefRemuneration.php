<?php

namespace Ams\PaieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PaiRefRemuneration
 *
 * @ORM\Table(name="pai_ref_remuneration", uniqueConstraints={@ORM\UniqueConstraint(name="un_pai_ref_remuneration", columns={"population_id", "societe_id", "date_debut", "date_fin"})})
 * @ORM\Entity(repositoryClass="Ams\PaieBundle\Repository\PaiRefRemunerationRepository")
 */
class PaiRefRemuneration
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
     * @var \DateTime
     *
     * @ORM\Column(name="date_debut", type="date", nullable=false)
     */
    private $date_debut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_fin", type="date", nullable=false)
     */
    private $date_fin;

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

    /**
     * @var string
     *
     * @ORM\Column(name="valeur", type="decimal", precision=8, scale=5, nullable=false)
     */
    private $valeur;

    /**
     * @var string
     *
     * @ORM\Column(name="valeurHP", type="decimal", precision=8, scale=5, nullable=false)
     */
    private $valeurHP;

    /**
     * @var string
     *
     * @ORM\Column(name="valeurHP2", type="decimal", precision=8, scale=5, nullable=false)
     */
    private $valeurHP2;
}
