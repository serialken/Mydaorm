<?php

namespace Ams\PaieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PaiRefQualite
 *
 * @ORM\Table(name="pai_ref_qualite"
 * , uniqueConstraints={@ORM\UniqueConstraint(name="un_pai_ref_qualite", columns={"societe_id", "emploi_code", "qualite", "borne_inf"})}
 * , indexes={@ORM\Index(name="IDX_C0F417BCC955D1E1", columns={"societe_id", "emploi_code"})}
 * )
 * @ORM\Entity(repositoryClass="Ams\PaieBundle\Repository\PaiRefQualiteRepository")
 */
class PaiRefQualite
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
     * @ORM\Column(name="qualite", type="string", length=1, nullable=false)
     */
    /**
     * @var \PaiRefRefQualite
     *
     * @ORM\ManyToOne(targetEntity="Ams\PaieBundle\Entity\PaiRefRefQualite")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="qualite", referencedColumnName="qualite", nullable=false)
     * })
     */
    private $qualite;

    /**
     * @var \RefPopulation
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefEmpSociete")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="societe_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $societe;

    /**
     * @var string
     *
     * @ORM\Column(name="emploi_code", type="string", length=3, nullable=false)
     */
    private $emploi;

    /**
     * @var string
     *
     * @ORM\Column(name="borne_inf", type="decimal", precision=7, scale=3, nullable=false)
     */
    private $borneInf;

    /**
     * @var string
     *
     * @ORM\Column(name="borne_sup", type="decimal", precision=7, scale=3, nullable=false)
     */
    private $borneSup;

    /**
     * @var string
     *
     * @ORM\Column(name="valeur", type="string", length=1, nullable=false)
     */
    private $valeur;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=128, nullable=false)
     */
    private $libelle;

    /**
     * @var boolean
     *
     * @ORM\Column(name="prime", type="boolean", nullable=false)
     */
    private $prime;

    /**
     * @var boolean
     *
     * @ORM\Column(name="envoiNG", type="boolean", nullable=false)
     */
    private $envoiNG;

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
