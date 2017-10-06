<?php

namespace Ams\PaieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PaiRefPostepaieSupplement
 *
 * @ORM\Table(name="pai_ref_postepaie_supplement")
 * @ORM\Entity(repositoryClass="Ams\PaieBundle\Repository\PaiRefPostePaieSupplementRepository")
 */
class PaiRefPostePaieSupplement
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
     * @var \Produit
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ProduitBundle\Entity\Produit")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="produit_id", referencedColumnName="id",nullable=false, unique=true)
     * })
     */
    private $produit;

    /**
     * @var string
     *
     * @ORM\Column(name="poste_bf", type="string", length=10, nullable=false)
     */
    private $posteBF;

    /**
     * @var string
     *
     * @ORM\Column(name="poste_bdc", type="string", length=10, nullable=false)
     */
    private $posteBDC;

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
    private $date_modif;}
