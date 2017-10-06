<?php

namespace Ams\ModeleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint as UniqueConstraint;

/**
 * ModeleTournee
 *
 * @ORM\Table(name="modele_remplacement"
 * 			,uniqueConstraints={@UniqueConstraint(name="un_modele_remplacement",columns={"contrattype_id","date_debut"})}
 *                      , indexes={@ORM\Index(name="idx1_modele_remplacement", columns={"employe_id","date_debut","date_fin"})
 *                                ,@ORM\Index(name="idx2_modele_remplacement", columns={"depot_id","flux_id","date_debut","date_fin"})
 *                                ,@ORM\Index(name="idx3_modele_remplacement", columns={"actif","date_debut","date_fin"})
 *                                }
 * 			)
 * @ORM\Entity(repositoryClass="Ams\ModeleBundle\Repository\RemplacementRepository")
 */
class Remplacement
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
     * @var \Depot
     *
     * @ORM\ManyToOne(targetEntity="Ams\SilogBundle\Entity\Depot")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="depot_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $depot;

    /**
     * @var \RefFlux
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefFlux")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="flux_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $flux;
    
//    On ne met pas de cle étrangère car le contrat peut-être supprimé dans Octime
    /**
     * @var \EmpContratType
     *
     * @ORM\Column(name="contrattype_id", type="integer", nullable=false)
     */
    private $contratType;

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
     * @var boolean
     *
     * @ORM\Column(name="actif", type="boolean", nullable=false)
     */
    private $actif;
    
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
