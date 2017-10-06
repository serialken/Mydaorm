<?php

namespace Ams\PaieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PaiRefSupplement
 *
 * @ORM\Table(name="pai_ref_supplement", uniqueConstraints={@ORM\UniqueConstraint(name="un_pai_ref_supplement", columns={"typetournee_id", "date_debut"})})
 * @ORM\Entity(repositoryClass="Ams\PaieBundle\Repository\PaiRefSupplementRepository")
 */
class PaiRefSupplement
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
     * @ORM\Column(name="borne_inf", type="integer", nullable=false)
     */
    private $borne_inf;

    /**
     * @var string
     *
     * @ORM\Column(name="borne_sup", type="integer", nullable=false)
     */
    private $borne_sup;

    /**
     * @var string
     *
     * @ORM\Column(name="valeur", type="decimal", precision=6, scale=3, nullable=false)
     */
    private $valeur;
    
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
}
