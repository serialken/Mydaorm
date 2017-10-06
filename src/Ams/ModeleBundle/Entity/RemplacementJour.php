<?php

namespace Ams\ModeleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint as UniqueConstraint;

/**
 * ModeleTournee
 *
 * @ORM\Table(name="modele_remplacement_jour"
 *                  ,uniqueConstraints={@UniqueConstraint(name="un_modele_remplacement_jour",columns={"remplacement_id","jour_id"})}
 *                  , indexes={@ORM\Index(name="idx1_modele_remplacement_jour", columns={"modele_tournee_id","jour_id","remplacement_id"})}
 * 			)
 * @ORM\Entity(repositoryClass="Ams\ModeleBundle\Repository\RemplacementJourRepository")
 */
class RemplacementJour
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
     * @var \Remplacement
     *
     * @ORM\ManyToOne(targetEntity="Ams\ModeleBundle\Entity\Remplacement")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="remplacement_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $remplacement;

    /**
     * @var \RefJour
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefJour")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="jour_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $jour;
    
    /**
     * @var \ModeleTournee
     *
     * @ORM\ManyToOne(targetEntity="ModeleTournee",)
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="modele_tournee_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $modeleTournee;
    
    /**
     * @var \PaieTournee
     *
     * @ORM\Column(name="pai_tournee_id", type="integer", nullable=true)
     */
    private $paiTournee;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_distrib", type="date", nullable=true)
     */
    private $date_distrib;
    
    /**
     * @var string
     *
     * @ORM\Column(name="duree", type="time", nullable=true)
     */
    private $duree;
    
    /**
     * @var string
     *
     * @ORM\Column(name="nbcli", type="decimal", precision=6, scale=0, nullable=true, options={"default"=0})
     */
    private $nbcli;

    /**
     * @var string
     *
     * @ORM\Column(name="tauxhoraire", type="decimal", precision=8, scale=5, nullable=false)
     */
    private $tauxhoraire;
    
    /**
     * @var decimal
     *
     * @ORM\Column(name="valrem", type="decimal", precision=7, scale=5, nullable=false)
     */
    private $valrem;
    
    /**
     * @var decimal
     *
     * @ORM\Column(name="etalon", type="decimal", precision=7, scale=6, nullable=false)
     */
    private $etalon;
    
    /**
     * @var decimal
     *
     * @ORM\Column(name="valrem_moyen", type="decimal", precision=7, scale=5, nullable=false)
     */
    private $valremMoyenne;
    
    /**
     * @var decimal
     *
     * @ORM\Column(name="etalon_moyen", type="decimal", precision=7, scale=6, nullable=false)
     */
    private $etalonMoyen;
    
    /**
     * @var \Utilisateur
     *
     * @ORM\ManyToOne(targetEntity="Ams\SilogBundle\Entity\Utilisateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="utilisateur_id", referencedColumnName="id")
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
