<?php

namespace Ams\PaieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint as UniqueConstraint;

/**
 * PaiPrdTournee
 *
 * @ORM\Table(name="pai_prd_tournee"
 *          , uniqueConstraints={@ORM\UniqueConstraint(name="un_pai_prd_tournee", columns={"tournee_id", "produit_id", "natureclient_id"})}
 *                ,indexes={@ORM\Index(name="idx2_pai_prd_tournee", columns={"date_extrait","valide"})
 *                          }
 * )
 * @ORM\Entity(repositoryClass="Ams\PaieBundle\Repository\PaiPrdTourneeRepository")
 */
class PaiPrdTournee
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
     * @var \PaiTournee
     *
     * @ORM\ManyToOne(targetEntity="PaiTournee")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tournee_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $tournee;

    /**
     * @var \Produit
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ProduitBundle\Entity\Produit")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="produit_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $produit;

    /**
     * @var \RefNatureclient
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefNatureClient")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="natureclient_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $natureclient;

    /**
     * @var string
     *
     * @ORM\Column(name="qte", type="decimal", precision=4, scale=0, nullable=false, options={"default"=0})
     */
    private $qte;

    /**
     * @var string
     *
     * @ORM\Column(name="nbcli", type="decimal", precision=4, scale=0, nullable=false, options={"default"=0})
     */
    private $nbcli;

    /**
     * @var string
     *
     * @ORM\Column(name="nbrep", type="decimal", precision=4, scale=0, nullable=false, options={"default"=0})
     */
    private $nbrep;

    /**
     * @var string
     *
     * @ORM\Column(name="nbcli_unique", type="decimal", precision=4, scale=0, nullable=false, options={"default"=0})
     */
    private $nbcliUnique;

    /**
     * @var string
     *
     * @ORM\Column(name="nbadr", type="decimal", precision=4, scale=0, nullable=false, options={"default"=0})
     */
    private $nbadr;
    
     /**
     * @var time
     *
     * @ORM\Column(name="duree_supplement", type="time", nullable=false, options={"default"="00:00:00"})
     */
    private $dureeSupplement;
    
     /**
     * @var integer
     *
     * @ORM\Column(name="poids", type="integer", nullable=false, options={"default"=0})
     */
    private $poids;

    /**
     * @var string
     *
     * @ORM\Column(name="pai_qte", type="decimal", precision=9, scale=2, nullable=true)
     */
    private $paieQte;

    /**
     * @var string
     *
     * @ORM\Column(name="pai_taux", type="decimal", precision=8, scale=3, nullable=true)
     */
    private $paieTaux;

    /**
     * @var string
     *
     * @ORM\Column(name="pai_mnt", type="decimal", precision=9, scale=2, nullable=true)
     */
    private $paieMontant;

    /**
     * @var boolean
     *
     * @ORM\Column(name="valide", type="boolean", nullable=false, options={"default"=0})
     */
    private $valide=false;
    
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
    private $dateCreation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_modif", type="datetime", nullable=true)
     */
    private $dateModif;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_extrait", type="datetime", nullable=true)
     */
    private $date_extrait;
}
