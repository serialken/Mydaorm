<?php

namespace Ams\ModeleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint as UniqueConstraint;

/**
 * PaiTournee
 *
 * @ORM\Table(name="etalon_tournee"
 *           )
 * @ORM\Entity(repositoryClass="Ams\ModeleBundle\Repository\EtalonTourneeRepository")
 */
/*
 *  *                ,uniqueConstraints={@UniqueConstraint(name="un_etalon_tournee",columns={"etalon_id","modele_tournee_id","jour_id"})
 *                                   ,@UniqueConstraint(name="un2_etalon_tournee",columns={"etalon_id","date_distrib","employe_id"})
 *                                   }
 */
class EtalonTournee
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
     * @var \Etalon
     *
     * @ORM\ManyToOne(targetEntity="Ams\ModeleBundle\Entity\Etalon")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="etalon_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $etalon;

    /**
     * @var \ModeleTournee
     *
     * @ORM\ManyToOne(targetEntity="Ams\ModeleBundle\Entity\ModeleTournee")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="modele_tournee_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $tournee;

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
     * @var \DateTime
     *
     * @ORM\Column(name="date_distrib", type="date", nullable=true)
     */
    private $date_distrib;
   
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
     * @var string
     *
     * @ORM\Column(name="tauxhoraire", type="decimal", precision=8, scale=5, nullable=false)
     */
    private $tauxhoraire;

    /**
     * @var decimal
     *
     * @ORM\Column(name="valrem_reelle", type="decimal", precision=7, scale=5, nullable=true)
     */
    private $valremReelle;
    
    /**
     * @var decimal
     *
     * @ORM\Column(name="valrem_calculee", type="decimal", precision=7, scale=5, nullable=false)
     */
    private $valremCalculee;
    
    /**
     * @var decimal
     *
     * @ORM\Column(name="valrem_moyen", type="decimal", precision=7, scale=5, nullable=false)
     */
    private $valremMoyenne;
    
    /**
     * @var decimal
     *
     * @ORM\Column(name="etalon_calcule", type="decimal", precision=7, scale=6, nullable=false)
     */
    private $etalonCalcule;
    
    /**
     * @var decimal
     *
     * @ORM\Column(name="etalon_moyen", type="decimal", precision=7, scale=6, nullable=false)
     */
    private $etalonMoyen;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="heure_debut", type="time", nullable=false)
     */
    private $heureDebut;

    /**
     * @var string
     *
     * @ORM\Column(name="duree", type="time", nullable=true)
     */
    private $duree;
    
     /**
     * @var time
     *
     * @ORM\Column(name="duree_totale", type="time", nullable=false, options={"default"="00:00:00"})
     */
    private $dureeTotale;   
    
     /**
     * @var time
     *
     * @ORM\Column(name="duree_tournee", type="time", nullable=false, options={"default"="00:00:00"})
     */
    private $dureeTournee;
    
     /**
     * @var time
     *
     * @ORM\Column(name="duree_reperage", type="time", nullable=false, options={"default"="00:00:00"})
     */
    private $dureeReperage;
    
     /**
     * @var time
     *
     * @ORM\Column(name="duree_supplement", type="time", nullable=false, options={"default"="00:00:00"})
     */
    private $dureeSupplement;
    
      /**
     * @var time
     *
     * @ORM\Column(name="duree_nuit", type="time", nullable=false, options={"default"="00:00:00"})
     */
    private $dureeNuit;

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
     * @ORM\Column(name="nbkm", type="decimal", precision=3, scale=0, nullable=true)
     */
    private $nbkm;

    /**
     * @var string
     *
     * @ORM\Column(name="nbkm_paye", type="decimal", precision=3, scale=0, nullable=true)
     */
    private $nbkmPaye;

    /**
     * @var string
     *
     * @ORM\Column(name="nbtitre", type="decimal", precision=6, scale=0, nullable=false, options={"default"=0})
     */
    private $nbtitre;

    /**
     * @var string
     *
     * @ORM\Column(name="nbspl", type="decimal", precision=6, scale=0, nullable=false, options={"default"=0})
     */
    private $nbspl;

    /**
     * @var string
     *
     * @ORM\Column(name="nbprod", type="decimal", precision=6, scale=0, nullable=false, options={"default"=0})
     */
    private $nbprod;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="nbrep", type="decimal", precision=6, scale=0, nullable=false, options={"default"=0})
     */
    private $nbReperage;
    
    /**
     * @var string
     *
     * @ORM\Column(name="nbcli", type="decimal", precision=6, scale=0, nullable=false, options={"default"=0})
     */
    private $nbcli;
	
    /**
     * @var boolean
     * 1 si ce modèle a le dépot comme point de départ
     * 0 si le point de depart est le premier abonné à livrer
     * 
     * @ORM\Column(name="depart_depot", type="boolean", nullable = false, options={"default" : true})
     * 
     */
    private $departDepot;

    /**
     * @var boolean
     * 1 si cette tournée se termine par un retour au dépot
     * 0 si le dernier point de la tournée est le dernier abonné à livrer
     * 
     * @ORM\Column(name="retour_depot", type="boolean", nullable = false, options={"default" : true})
     * 
     */
    private $retourDepot;

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
