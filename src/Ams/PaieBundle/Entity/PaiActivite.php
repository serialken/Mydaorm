<?php

namespace Ams\PaieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PaiActivite
 *
 * @ORM\Table(name="pai_activite"
 *                ,indexes={@ORM\Index(name="idx1_pai_activite", columns={"flux_id","depot_id","date_distrib"})
 *                         ,@ORM\Index(name="idx2_pai_activite", columns={"date_extrait","date_distrib","flux_id","depot_id"})
 *                         ,@ORM\Index(name="idx3_pai_activite", columns={"date_distrib","employe_id"})
 *                          }
 * )
 * @ORM\Entity(repositoryClass="Ams\PaieBundle\Repository\PaiActiviteRepository")
 */
class PaiActivite
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
     * @var \PaiTournee
     *
     * @ORM\ManyToOne(targetEntity="Ams\PaieBundle\Entity\PaiTournee")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tournee_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $tournee;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_distrib", type="date", nullable=false)
     */
    private $date_distrib;

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
     * @var \RefTypeJour
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefTypeJour")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="typejour_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $typeJour;

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
     * identifiant Pleiades
     * @var string
     *
     * @ORM\Column(name="xaoid", type="string", length=36, nullable=true)
     */
    private $xaoid;
    /**
     * @var \Employe
     *
     * @ORM\ManyToOne(targetEntity="Ams\EmployeBundle\Entity\Employe")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="employe_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $employe;

    /**
     * @var boolean
     *
     * @ORM\Column(name="ouverture", type="boolean", nullable=false, options={"default"=0})
     */
    private $ouverture;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="heure_debut", type="time", nullable=true)
     */
    private $heureDebut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="heure_debut_calculee", type="time", nullable=true)
     */
    private $heureDebutCalcule;

    /**
    * @var string
     *
     * @ORM\Column(name="duree", type="time", nullable=true)
     */
    private $duree;

    /**
    * @var string
     *
     * @ORM\Column(name="duree_garantie", type="time", nullable=false, options={"default"="00:00:00"})
     */
    private $duree_garantie;
    
      /**
     * @var time
     *
     * @ORM\Column(name="duree_nuit", type="time", nullable=false, options={"default"="00:00:00"})
     */
    private $tpsNuit;

    /**
    * @var string
     *
     * @ORM\Column(name="nbkm_paye", type="decimal", precision=3, scale=0, nullable=true)
     */
    private $nbkmPaye;

    /**
     * @var \RefTransport
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefTransport")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="transport_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $transport;

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
