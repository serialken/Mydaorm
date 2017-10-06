<?php

namespace Ams\PaieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PaiRefPoids
 *
 * @ORM\Table(name="pai_ref_poids", uniqueConstraints={@ORM\UniqueConstraint(name="un_pai_ref_poids", columns={"typetournee_id", "produit_type_id", "produit_id", "date_debut"})}, indexes={@ORM\Index(name="fk_pai_ref_poids_produit_type1_idx", columns={"produit_type_id"}), @ORM\Index(name="fk_pai_ref_poids_produit1_idx", columns={"produit_id"}), @ORM\Index(name="IDX_231D9078C955D1E1", columns={"typetournee_id"})})
 * @ORM\Entity(repositoryClass="Ams\PaieBundle\Repository\PaiRefPoidsRepository")
 */
class PaiRefPoids
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
     * @var \ProduitType
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ProduitBundle\Entity\ProduitType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="produit_type_id", referencedColumnName="id")
     * })
     */
    private $produitType;

    /**
     * @var \Produit
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ProduitBundle\Entity\Produit")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="produit_id", referencedColumnName="id")
     * })
     */
    private $produit;

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
