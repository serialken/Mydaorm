<?php

namespace Ams\PaieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PaiRefPostepaieActivite
 *
 * @ORM\Table(name="pai_ref_postepaie_activite", uniqueConstraints={@ORM\UniqueConstraint(name="un_ref_postepaie_activite", columns={"activite_id", "typejour_id"})})
 * @ORM\Entity(repositoryClass="Ams\PaieBundle\Repository\PaiRefPostePaieActiviteRepository")
 */
class PaiRefPostePaieActivite
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
     * @var RefTypeJour
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefTypeJour")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="typejour_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $typeJour;

    /**
     * @var \RefActivite
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefActivite")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="activite_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $activite;

    /**
     * @var string
     *
     * @ORM\Column(name="poste_hj", type="string", length=10, nullable=true)
     */
    private $posteHj;

    /**
     * @var string
     *
     * @ORM\Column(name="poste_hn", type="string", length=10, nullable=true)
     */
    private $posteHn;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=128, nullable=false)
     */
    private $libelle;

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
