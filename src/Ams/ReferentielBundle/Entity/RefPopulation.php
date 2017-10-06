<?php

namespace Ams\ReferentielBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RefPopulation
 *
 * @ORM\Table(name="ref_population")
 * @ORM\Entity(repositoryClass="Ams\ReferentielBundle\Repository\RefPopulationRepository")
 */
class RefPopulation
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
     * @ORM\Column(name="code", type="string", length=6, nullable=false, unique=true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=80, nullable=false)
     */
    private $libelle;

    /**
     * @var \RefEmploi
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefEmploi")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="emploi_id", referencedColumnName="id")
     * })
     */
    private $emploi;

    /**
     * @var \RefTypeContrat
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefTypeContrat")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="typecontrat_id", referencedColumnName="id", nullable=false)
     * })
     */
   private $typeContrat;

    /**
     * @var \RefTypeUrssaf
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefTypeUrssaf")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="typeurssaf_id", referencedColumnName="id", nullable=false)
     * })
     */
   private $typeUrssaf;

    /**
     * @var \RefTypeTournee
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefTypeTournee")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="typetournee_id", referencedColumnName="id", nullable=false)
     * })
     */
   private $typeTournee;

    /**
     * @var string
     *
     * @ORM\Column(name="majoration", type="decimal", precision=5, scale=2, nullable=false, options={"default"=0})
     */
    private $majoration;

    /**
     * @var string
     *
     * @ORM\Column(name="majoration_df", type="decimal", precision=5, scale=2, nullable=false, options={"default"=0})
     */
    private $majorationDF;

    /**
     * @var string
     *
     * @ORM\Column(name="majoration_dfq", type="decimal", precision=5, scale=2, nullable=false, options={"default"=0})
     */
    private $majorationDFQ;

    /**
     * @var string
     *
     * @ORM\Column(name="majoration_nuit", type="decimal", precision=5, scale=2, nullable=false, options={"default"=0})
     */
    private $majorationNuit;

    /**
     * @var string
     *
     * @ORM\Column(name="pop_paie_id", type="integer", nullable=false)
     */
    private $popPaie;

    /**
     * @var boolean
     *
     * @ORM\Column(name="est_badge", type="boolean", nullable=false, options={"default"=0})
     */
    private $estBadge;

    /**
     * @var boolean
     *
     * @ORM\Column(name="km_paye", type="boolean", nullable=false, options={"default"=1})
     */
    private $kmPaye;

    /**
     * @var boolean
     *
     * @ORM\Column(name="ouverture", type="boolean", nullable=false, options={"default"=0})
     */
    private $ouverture;
}
