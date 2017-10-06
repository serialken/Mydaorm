<?php

namespace Ams\InvenduBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint as UniqueConstraint;

/**
 * LieuVente
 *
 * @ORM\Table(name="lieu_vente")
 * @ORM\Entity(repositoryClass="Ams\InvenduBundle\Repository\LieuVenteRepository")

 */
class LieuVente {

    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(name="numero", type="integer", unique=true, nullable=false)
     */
    private $numero;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=100, nullable=false)
     */
    private $libelle;

    /**
     * @var integer
     *
     * @ORM\Column(name="numero_regroupement", type="integer", nullable=true)
     */
    private $numeroRegroupement;

    /**
     * @var string
     *
     * @ORM\Column(name="code_depot", type="string", length=3, nullable=false)
     */
    private $codeDepot;

    /**
     * @var string
     *
     * @ORM\Column(name="adresse_1", type="string", length=50, nullable=true)
     */
    private $adresse1;

    /**
     * @var string
     *
     * @ORM\Column(name="adresse_2", type="string", length=50, nullable=true)
     */
    private $adresse2;

    /**
     * @var string
     *
     * @ORM\Column(name="adresse_3", type="string", length=50, nullable=true)
     */
    private $adresse3;

    /**
     * @var string
     *
     * @ORM\Column(name="cp", type="string", length=5, nullable=false)
     */
    private $cp;

    /**
     * @var string
     *
     * @ORM\Column(name="ville", type="string", length=50, nullable=false)
     */
    private $ville;

    /**
     * @var string
     *
     * @ORM\Column(name="telephone", type="string", length=10, nullable=true)
     */
    private $telephone;

    /**
     * @var string
     *
     * @ORM\Column(name="routage", type="string", length=20, nullable=true)
     */
    private $routage;

    /**
     * @var string
     * @ORM\Column(name="categorie", type="string",length=1, nullable=true)
     */
    private $categorie;

    /**
     * @var string
     * @ORM\Column(name="type", type="string",length=2, nullable=true )
     */
    private $type;

}
