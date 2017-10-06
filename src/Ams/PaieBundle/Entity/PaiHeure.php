<?php

namespace Ams\PaieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint as UniqueConstraint;

/**
 * PaiHeure
 *
 * @ORM\Table(name="pai_heure"
 *                      ,uniqueConstraints={@UniqueConstraint(name="un_pai_heure",columns={"groupe_id","date_distrib"})}
 *           )
 * @ORM\Entity(repositoryClass="Ams\PaieBundle\Repository\PaiHeureRepository")
 */
class PaiHeure
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_distrib", type="date", nullable=false)
     */
    private $date_distrib;

    /**
     * @var \Groupe
     *
     * @ORM\ManyToOne(targetEntity="Ams\ModeleBundle\Entity\GroupeTournee")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="groupe_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $groupe;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="heure_debut_theo", type="time", nullable=false)
     */
    private $heureDebutTheo;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="duree_attente", type="time", nullable=false)
     */
    private $dureeAttente;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="heure_debut", type="time", nullable=true)
     */
    private $heureDebut;
	
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
