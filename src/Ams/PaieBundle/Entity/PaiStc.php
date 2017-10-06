<?php

namespace Ams\PaieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint as UniqueConstraint;

/**
 * PaiStc
 *
 * @ORM\Table(name="pai_stc"
 *                ,uniqueConstraints={@UniqueConstraint(name="un_pai_stc",columns={"rcoid"})
 *                                   }
 *          )
 * @ORM\Entity(repositoryClass="Ams\PaieBundle\Repository\PaiStcRepository")
 */
class PaiStc
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
     * identifiant Pleiades
     * @var string
     *
     * @ORM\Column(name="rcoid", type="string", length=36, nullable=false)
     */
    private $rcoid;

    /**
     * @var string
     *
     * @ORM\Column(name="anneemois", type="string", length=6, nullable=false)
     */
    private $anneemois;

    /**
     * @var \Employe
     *
     * @ORM\ManyToOne(targetEntity="Ams\EmployeBundle\Entity\Employe")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="employe_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $employe;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_stc", type="date", nullable=false)
     */
    private $date_stc;

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
     * @ORM\Column(name="date_modif", type="date", nullable=true)
     */
    private $date_modif;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_extrait", type="datetime", nullable=true)
     */
    private $date_extrait;
}
