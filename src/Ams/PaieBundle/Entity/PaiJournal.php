<?php

namespace Ams\PaieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PaiJournal
 *
 * @ORM\Table(name="pai_journal"
 *            ,indexes={@ORM\Index(name="idx1_pai_journal", columns={"erreur_id","validation_id"})
 *                     ,@ORM\Index(name="idx2_pai_journal", columns={"date_extrait","date_distrib","depot_id","flux_id","tournee_id"})
 *                     ,@ORM\Index(name="idx3_pai_journal", columns={"date_extrait","date_distrib","depot_id","flux_id","activite_id"})
 *                     ,@ORM\Index(name="idx4_pai_journal", columns={"date_extrait","date_distrib","depot_id","flux_id","produit_id"})
 *                     ,@ORM\Index(name="idx5_pai_journal", columns={"date_extrait","date_distrib","depot_id","flux_id","employe_id"})
 *                      }
 * )
 * @ORM\Entity(repositoryClass="Ams\PaieBundle\Repository\PaiJournalRepository")
 */
class PaiJournal
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
     * @var \PaiRefErreur
     *
     * @ORM\ManyToOne(targetEntity="Ams\PaieBundle\Entity\PaiRefErreur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="erreur_id", referencedColumnName="id")
     * })
     */
    private $erreur_id;

    /**
     * @var integer
     *
     * @ORM\Column(name="validation_id", type="integer", nullable=false)
     */
    private $validation_id;

    /**
     * @var string
     *
     * @ORM\Column(name="anneemois", type="string", length=6, nullable=false)
     */
    private $anneemois;
	
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_distrib", type="date", nullable=true)
     */
    private $date_distrib;

    /**
     * @var \Depot
     *
     * @ORM\ManyToOne(targetEntity="Ams\SilogBundle\Entity\Depot")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="depot_id", referencedColumnName="id")
     * })
     */
    private $depot;

    /**
     * @var \RefFlux
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefFlux")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="flux_id", referencedColumnName="id")
     * })
     */
    private $flux;

    /**
     * @var \Employe
     *
     * @ORM\ManyToOne(targetEntity="Ams\EmployeBundle\Entity\Employe")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="employe_id", referencedColumnName="id")
     * })
     */
    private $employe;
	
    /**
     * @var \PaiActivite
     *
     * @ORM\ManyToOne(targetEntity="PaiActivite")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="activite_id", referencedColumnName="id")
     * })
     */
    private $activite;

    /**
     * @var \PaiTournee
     *
     * @ORM\ManyToOne(targetEntity="PaiTournee")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tournee_id", referencedColumnName="id")
     * })
     */
    private $tournee;

    /**
     * @var \PaiPrdTournee
     *
     * @ORM\ManyToOne(targetEntity="PaiPrdTournee")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="produit_id", referencedColumnName="id")
     * })
     */
    private $produit;

    /**
     * @var string
     *
     * @ORM\Column(name="commentaire", type="string", length=255, nullable=true)
     */
    private $commentaire;
    
    /**
     * @var string
     *
     * @ORM\Column(name="date_extrait", type="datetime", nullable=true)
     */
    private $date_extrait;
}
