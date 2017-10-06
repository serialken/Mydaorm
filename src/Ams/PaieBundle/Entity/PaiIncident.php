<?php

namespace Ams\PaieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PaiIncident
 *
 * @ORM\Table(name="pai_incident"
 *                ,indexes={@ORM\Index(name="idx1_pai_incident", columns={"date_distrib","employe_id"})
 *                          }
 * )
 * @ORM\Entity(repositoryClass="Ams\PaieBundle\Repository\PaiIncidentRepository")
 */
class PaiIncident
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
     * @ORM\Column(name="date_distrib", type="date", nullable=false)
     */
    private $date_distrib;

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
     * @var \PaiRefIncident
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefIncident")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="incident_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $incident;

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
     * @var \DateTime
     *
     * @ORM\Column(name="date_extrait", type="datetime", nullable=true)
     */
    private $date_extrait;
    
}
