<?php

namespace Ams\PaieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint as UniqueConstraint;

/**
 * PaiReclamation
 *
 * @ORM\Table(name="pai_reclamation"
 *          , uniqueConstraints={@ORM\UniqueConstraint(name="un_pai_reclamation_tournee", columns={"anneemois","type_id","tournee_id","societe_id"})
 *                              }
 * )
 * @ORM\Entity(repositoryClass="Ams\PaieBundle\Repository\PaiReclamationRepository")
 */
class PaiReclamation
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
     * @var integer
     *
     * @ORM\Column(name="type_id", type="integer", nullable=false)
     * 1 crm
     * 2 manuelle
     * 3 pepp
     */
    private $type;
    
    /**
     * @var string
     *
     * @ORM\Column(name="anneemois", type="string", length=6, nullable=false)
     */
    private $anneemois;

    /**
     * @var \PaiTournee
     *
     * @ORM\ManyToOne(targetEntity="PaiTournee")
     * @ORM\JoinColumn(name="tournee_id", referencedColumnName="id", nullable=false)
     */
    private $tournee;

    /**
     * @var \Societe
     *
     * @ORM\ManyToOne(targetEntity="Ams\ProduitBundle\Entity\Societe")
     * @ORM\JoinColumn(name="societe_id", referencedColumnName="id")
     */
    private $societe;

    /**
     * @var string
     *
     * @ORM\Column(name="nbrec_abonne_brut", type="decimal", precision=4, scale=0, nullable=false, options={"default"=0})
     */
    private $nbrecAbonneBrut;

    /**
     * @var string
     *
     * @ORM\Column(name="nbrec_diffuseur_brut", type="decimal", precision=4, scale=0, nullable=false, options={"default"=0})
     */
    private $nbrecDiffuseurBrut;

    /**
     * @var string
     *
     * @ORM\Column(name="nbrec_abonne", type="decimal", precision=4, scale=0, nullable=false, options={"default"=0})
     */
    private $nbrecAbonne;

    /**
     * @var string
     *
     * @ORM\Column(name="nbrec_diffuseur", type="decimal", precision=4, scale=0, nullable=false, options={"default"=0})
     */
    private $nbrecDiffuseur;

    /**
     * @var string
     *
     * @ORM\Column(name="commentaire", type="string", length=1024, nullable=true)
     */
    private $commentaire;
	
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
    
    /**
     * @var string
     *
     * @ORM\Column(name="date_extrait", type="datetime", nullable=true)
     */
    private $date_extrait;
   
}
