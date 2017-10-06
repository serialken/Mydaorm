<?php

namespace Ams\EmployeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EmpContatHP
 *
 * @ORM\Table(name="emp_contrat_hp"
 *                ,uniqueConstraints={@ORM\UniqueConstraint(name="un_emp_contrat_hp", columns={"xaoid"})}
 *              )
 * @ORM\Entity(repositoryClass="Ams\EmployeBundle\Repository\EmpContratHPRepository")
*/
class EmpContratHP
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
     * @var \Employe
     *
     * @ORM\ManyToOne(targetEntity="Ams\EmployeBundle\Entity\Employe")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="employe_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $employe;

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
     * @var boolean
     *
     * @ORM\Column(name="lundi", type="boolean", nullable=false)
     */
    private $lundi;

    /**
     * @var boolean
     *
     * @ORM\Column(name="mardi", type="boolean", nullable=false)
     */
    private $mardi;

    /**
     * @var boolean
     *
     * @ORM\Column(name="mercredi", type="boolean", nullable=false)
     */
    private $mercredi;

    /**
     * @var boolean
     *
     * @ORM\Column(name="jeudi", type="boolean", nullable=false)
     */
    private $jeudi;

    /**
     * @var boolean
     *
     * @ORM\Column(name="vendredi", type="boolean", nullable=false)
     */
    private $vendredi;

    /**
     * @var boolean
     *
     * @ORM\Column(name="samedi", type="boolean", nullable=false)
     */
    private $samedi;

    /**
     * @var boolean
     *
     * @ORM\Column(name="dimanche", type="boolean", nullable=false)
     */
    private $dimanche;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="heure_debut", type="time", nullable=false)
     */
    private $heureDebut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="heure_debut_lundi", type="time", nullable=false)
     */
    private $heureDebutLundi;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="heure_debut_mardi", type="time", nullable=false)
     */
    private $heureDebutMardi;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="heure_debut_mercredi", type="time", nullable=false)
     */
    private $heureDebutMercredi;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="heure_debut_jeudi", type="time", nullable=false)
     */
    private $heureDebutJeudi;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="heure_debut_vendredi", type="time", nullable=false)
     */
    private $heureDebutVendredi;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="heure_debut_samedi", type="time", nullable=false)
     */
    private $heureDebutSamedi;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="heure_debut_dimanche", type="time", nullable=false)
     */
    private $heureDebutDimanche;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="nbheures_jour", type="decimal", precision=5, scale=2, nullable=false)
     */
    private $nbHeuresJour;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="nbheures_lundi", type="decimal", precision=5, scale=2, nullable=false)
     */
    private $nbHeuresLundi;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="nbheures_mardi", type="decimal", precision=5, scale=2, nullable=false)
     */
    private $nbHeuresMardi;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="nbheures_mercredi", type="decimal", precision=5, scale=2, nullable=false)
     */
    private $nbHeuresMercredi;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="nbheures_jeudi", type="decimal", precision=5, scale=2, nullable=false)
     */
    private $nbHeuresJuedi;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="nbheures_vendredi", type="decimal", precision=5, scale=2, nullable=false)
     */
    private $nbHeuresVendredi;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="nbheures_samedi", type="decimal", precision=5, scale=2, nullable=false)
     */
    private $nbHeuresSamedi;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="nbheures_dimanche", type="decimal", precision=5, scale=2, nullable=false)
     */
    private $nbHeuresDimanche;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="nbheures_mensuel", type="decimal", precision=5, scale=2, nullable=false)
     */
    private $nbHeuresMensuel;
    
    /**
     * @var string
     *
     * @ORM\Column(name="travhorspresse", type="string", length=1, nullable=false)
     */
    private $travhorspresse;    
    
    /**
     * @var string
     *
     * @ORM\Column(name="commentaire", type="string", length=1024, nullable=true)
     */
    private $commentaire;
    
    /**
     * identifiant Pleiades
     * @var string
     *
     * @ORM\Column(name="xaoid", type="string", length=36, nullable=false)
     */
    private $xaoid;
    
    /**
     * identifiant Pleiades
     * @var string
     *
     * @ORM\Column(name="rcoid", type="string", length=36, nullable=false)
     */
    private $rcoid;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="ordre", type="decimal", precision=2, scale=0, nullable=false)
     */
    private $ordre;
    
    /**
     * identifiant Pleiades
     * @var string
     *
     * @ORM\Column(name="xta_rcactivte", type="string", length=36, nullable=false)
     */
    private $xta_rcactivte;
    
    /**
     * identifiant Pleiades
     * @var string
     *
     * @ORM\Column(name="xta_rcmetier", type="string", length=36, nullable=false)
     */
    private $xta_rcmetier;
    
    /**
     * identifiant Pleiades
     * @var string
     *
     * @ORM\Column(name="xta_rcactivhpre", type="string", length=36, nullable=false)
     */
    private $xta_rcactivhpre;
    
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
