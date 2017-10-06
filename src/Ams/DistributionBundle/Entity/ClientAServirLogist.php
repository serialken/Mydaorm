<?php

namespace Ams\DistributionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * ClientAServirLogist
 *
 * @ORM\Table(name="client_a_servir_logist", indexes={@Index(name="casl_idx_dat_soc_ext", columns={"date_distrib", "soc_code_ext"})
 *                                                  , @Index(name="casl_idx_dat", columns={"date_distrib","depot_id", "flux_id", "tournee_jour_id"})
 *                                                  , @Index(name="casl_idx_pai_modele", columns={"depot_id", "flux_id", "date_distrib", "tournee_jour_id"})
 *                                                  , @Index(name="casl_idx_pai_tournee", columns={"pai_tournee_id"})
 *                                                  , @Index(name="casl_idx_pai_produit", columns={"client_type","type_service","produit_id","pai_tournee_id"})
 *                                                  , @Index(name="casl_idx_pai_abonne", columns={"client_type","type_service","abonne_soc_id","pai_tournee_id"})
 *                                                  , @Index(name="casl_idx_pai_abonne_unique", columns={"client_type","type_service","abonne_unique_id","pai_tournee_id"})
 *                                                  , @Index(name="casl_idx_pai_adresse", columns={"client_type","type_service","adresse_id","pai_tournee_id"})
 *                                                  , @Index(name="casl_idx_nouv_appar", columns={"nouv_appar"})
 *                                                  , @Index(name="casl_idx_date_absence", columns={"date_absence"})
 *                                                  }
 *              )
 * @ORM\Entity(repositoryClass="Ams\DistributionBundle\Repository\ClientAServirLogistRepository")
 */
class ClientAServirLogist
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
    private $dateDistrib;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_parution", type="date", nullable=false)
     */
    private $dateParution;

    /**
     * @var string
     *
     * @ORM\Column(name="num_parution", type="string", length=20, nullable=true)
     */
    private $numParution;

    /**
     * @var \FicRecap
     *
     * @ORM\ManyToOne(targetEntity="\Ams\FichierBundle\Entity\FicRecap")
     * @ORM\JoinColumn(name="fic_recap_id", referencedColumnName="id", nullable=true)
     */
    private $ficRecap;

    /**
     * @var string
     *
     * @ORM\Column(name="vol1", type="string", length=100, nullable=true)
     */
    private $vol1;

    /**
     * @var string
     *
     * @ORM\Column(name="vol2", type="string", length=100, nullable=true)
     */
    private $vol2;

    /**
     * @var string
     *
     * @ORM\Column(name="type_portage", type="string", length=1, nullable=true)
     */
    private $typePortage;

    /**
     * @var integer
     *
     * @ORM\Column(name="qte", type="integer", nullable=true)
     */
    private $qte;

    /**
     * @var \AbonneSoc
     *
     * @ORM\ManyToOne(targetEntity="\Ams\AbonneBundle\Entity\AbonneSoc")
     * @ORM\JoinColumn(name="abonne_soc_id", referencedColumnName="id", nullable=true)
     */
    private $abonneSoc;
    
    /**
     * @var integer
     * Si 0, c'est abonne
     * Si 1, c'est Lieu de vente
     * 
     *
     * @ORM\Column(name="client_type", type="integer", nullable=false)
     */
    private $clientType;
    
    /**
     * Nouvelle apparition de l'abonne dans la tournee de la ligne courante par rapport a il y a 7 jours
     * @var integer
     * Si 1 : nouvelle apparition de l'abonne dans sa tournee 
     * Si 0 : L'abonne est deja connu de la tournee courante il y a 7 jours
     *
     * @ORM\Column(name="nouv_appar", type="smallint", options={"default":0})
     */
    private $nouvAppar;
    
    /**
     * Date ou l'abonne n'est plus present dans la tournee de la ligne courante (en general, c'est J distrib courante + 7). 
     * Ce champ est utilise afin de dire que l'abonne n'est plus present dans la tournee de la ligne courante 7 jours plus tard
     * @var date
     *
     * @ORM\Column(name="date_absence", type="date", nullable=true)
     */
    private $dateAbsence;
    
    /**
     * @var \Ams\AbonneBundle\Entity\AbonneUnique
     *
     * @ORM\ManyToOne(targetEntity="\Ams\AbonneBundle\Entity\AbonneUnique")
     * @ORM\JoinColumn(name="abonne_unique_id", referencedColumnName="id", nullable=true)
     */
    private $abonneUnique;

    /**
     * @var \Adresse
     *
     * @ORM\ManyToOne(targetEntity="\Ams\AdresseBundle\Entity\Adresse")
     * @ORM\JoinColumn(name="adresse_id", referencedColumnName="id", nullable=true)
     */
    private $adresse;

    /**
     * @var \AdresseRnvp
     *
     * @ORM\ManyToOne(targetEntity="\Ams\AdresseBundle\Entity\AdresseRnvp")
     * @ORM\JoinColumn(name="rnvp_id", referencedColumnName="id", nullable=true)
     */
    private $rnvp;
    
    /**
     * Tournee
     * @var \Ams\ModeleBundle\Entity\ModeleTourneeJour
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ModeleBundle\Entity\ModeleTourneeJour")
     * @ORM\JoinColumn(name="tournee_jour_id", referencedColumnName="id", nullable=true)
     */
    private $tournee;
    
    /**
     * Tournee
     * @var \Ams\PaiBundle\Entity\PaiTournee
     *
     * @ORM\ManyToOne(targetEntity="\Ams\PaieBundle\Entity\PaiTournee")
     * @ORM\JoinColumn(name="pai_tournee_id", referencedColumnName="id", nullable=true)
     */
    private $paiTournee;
    
    /**
     * La valeur de cet attribut est une adresse RNVP
     * @var \Ams\AdresseBundle\Entity\AdresseRnvp
     *
     * @ORM\ManyToOne(targetEntity="\Ams\AdresseBundle\Entity\AdresseRnvp")
     * @ORM\JoinColumn(name="point_livraison_id", referencedColumnName="id", nullable=true)
     */
    private $pointLivraison;
    
    /**
     * Ordre du point de livraison
     * @var integer
     *
     * @ORM\Column(name="point_livraison_ordre", type="integer", nullable=true)
     */
    private $pointLivraisonOrdre;
    
    /**
     * Ordre de livraison d'un abonne par (au sein d'un) point de livraison
     * @var integer
     *
     * @ORM\Column(name="ordre_dans_arret", type="integer", nullable=true)
     */
    private $ordreDansArret;

    /**
     * @var \Ams\AdresseBundle\Entity\Commune
     *
     * @ORM\ManyToOne(targetEntity="\Ams\AdresseBundle\Entity\Commune")
     * @ORM\JoinColumn(name="commune_id", referencedColumnName="id", nullable=true)
     */
    private $commune;

    /**
     * @var \Ams\SilogBundle\Entity\Depot
     *
     * @ORM\ManyToOne(targetEntity="\Ams\SilogBundle\Entity\Depot")
     * @ORM\JoinColumn(name="depot_id", referencedColumnName="id", nullable=true)
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
     * @var \Societe
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ProduitBundle\Entity\Societe")
     * @ORM\JoinColumn(name="societe_id", referencedColumnName="id", nullable=true)
     */
    private $societe;

    /**
     * @var \Produit
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ProduitBundle\Entity\Produit")
     * @ORM\JoinColumn(name="produit_id", referencedColumnName="id", nullable=true)
     */
    private $produit;

    /**
     * @var string
     *
     * @ORM\Column(name="soc_code_ext", type="string", length=10, nullable=false)
     */
    private $socCodeExt;

    /**
     * @var \ClientAServirSrc
     *
     * @ORM\OneToOne(targetEntity="\Ams\DistributionBundle\Entity\ClientAServirSrc", inversedBy="clientAServirLogist")
     * @ORM\JoinColumn(name="client_a_servir_src_id", referencedColumnName="id", nullable=true)
     */
    private $clientAServirSrc;

    /**
     * L : si Abonne a livrer
     * R : si Abonne a reperer
     * @var string
     *
     * @ORM\Column(name="type_service", type="string", length=2, nullable=true)
     */
    private $typeService;
    
    /**
     * Le porteur (ou polyvalent ...)
     * @var \Ams\EmployeBundle\Entity\Employe
     *
     * @ORM\ManyToOne(targetEntity="\Ams\EmployeBundle\Entity\Employe")
     * @ORM\JoinColumn(name="employe_id", referencedColumnName="id", nullable=true)
     */
    private $employe;
    
    
    

    
    
    
    
    


    

    


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set dateDistrib
     *
     * @param \DateTime $dateDistrib
     * @return ClientAServirLogist
     */
    public function setDateDistrib($dateDistrib)
    {
        $this->dateDistrib = $dateDistrib;

        return $this;
    }

    /**
     * Get dateDistrib
     *
     * @return \DateTime 
     */
    public function getDateDistrib()
    {
        return $this->dateDistrib;
    }

    /**
     * Set dateParution
     *
     * @param \DateTime $dateParution
     * @return ClientAServirLogist
     */
    public function setDateParution($dateParution)
    {
        $this->dateParution = $dateParution;

        return $this;
    }

    /**
     * Get dateParution
     *
     * @return \DateTime 
     */
    public function getDateParution()
    {
        return $this->dateParution;
    }

    /**
     * Set numParution
     *
     * @param string $numParution
     * @return ClientAServirLogist
     */
    public function setNumParution($numParution)
    {
        $this->numParution = $numParution;

        return $this;
    }

    /**
     * Get numParution
     *
     * @return string 
     */
    public function getNumParution()
    {
        return $this->numParution;
    }

    /**
     * Set vol1
     *
     * @param string $vol1
     * @return ClientAServirLogist
     */
    public function setVol1($vol1)
    {
        $this->vol1 = $vol1;

        return $this;
    }

    /**
     * Get vol1
     *
     * @return string 
     */
    public function getVol1()
    {
        return $this->vol1;
    }

    /**
     * Set vol2
     *
     * @param string $vol2
     * @return ClientAServirLogist
     */
    public function setVol2($vol2)
    {
        $this->vol2 = $vol2;

        return $this;
    }

    /**
     * Get vol2
     *
     * @return string 
     */
    public function getVol2()
    {
        return $this->vol2;
    }

    /**
     * Set typePortage
     *
     * @param string $typePortage
     * @return ClientAServirLogist
     */
    public function setTypePortage($typePortage)
    {
        $this->typePortage = $typePortage;

        return $this;
    }

    /**
     * Get typePortage
     *
     * @return string 
     */
    public function getTypePortage()
    {
        return $this->typePortage;
    }

    /**
     * Set qte
     *
     * @param integer $qte
     * @return ClientAServirLogist
     */
    public function setQte($qte)
    {
        $this->qte = $qte;

        return $this;
    }

    /**
     * Get qte
     *
     * @return integer 
     */
    public function getQte()
    {
        return $this->qte;
    }

    /**
     * Set pointLivraisonOrdre
     *
     * @param integer $pointLivraisonOrdre
     * @return ClientAServirLogist
     */
    public function setPointLivraisonOrdre($pointLivraisonOrdre)
    {
        $this->pointLivraisonOrdre = $pointLivraisonOrdre;

        return $this;
    }

    /**
     * Get pointLivraisonOrdre
     *
     * @return integer 
     */
    public function getPointLivraisonOrdre()
    {
        return $this->pointLivraisonOrdre;
    }

    /**
     * Set ordreDansArret
     *
     * @param integer $ordreDansArret
     * @return ClientAServirLogist
     */
    public function setOrdreDansArret($ordreDansArret)
    {
        $this->ordreDansArret = $ordreDansArret;

        return $this;
    }

    /**
     * Get ordreDansArret
     *
     * @return integer 
     */
    public function getOrdreDansArret()
    {
        return $this->ordreDansArret;
    }

    /**
     * Set socCodeExt
     *
     * @param string $socCodeExt
     * @return ClientAServirLogist
     */
    public function setSocCodeExt($socCodeExt)
    {
        $this->socCodeExt = $socCodeExt;

        return $this;
    }

    /**
     * Get socCodeExt
     *
     * @return string 
     */
    public function getSocCodeExt()
    {
        return $this->socCodeExt;
    }

    /**
     * Set typeService
     *
     * @param string $typeService
     * @return ClientAServirLogist
     */
    public function setTypeService($typeService)
    {
        $this->typeService = $typeService;

        return $this;
    }

    /**
     * Get typeService
     *
     * @return string 
     */
    public function getTypeService()
    {
        return $this->typeService;
    }

    /**
     * Set ficRecap
     *
     * @param \Ams\FichierBundle\Entity\FicRecap $ficRecap
     * @return ClientAServirLogist
     */
    public function setFicRecap(\Ams\FichierBundle\Entity\FicRecap $ficRecap = null)
    {
        $this->ficRecap = $ficRecap;

        return $this;
    }

    /**
     * Get ficRecap
     *
     * @return \Ams\FichierBundle\Entity\FicRecap 
     */
    public function getFicRecap()
    {
        return $this->ficRecap;
    }

    /**
     * Set abonneSoc
     *
     * @param \Ams\AbonneBundle\Entity\AbonneSoc $abonneSoc
     * @return ClientAServirLogist
     */
    public function setAbonneSoc(\Ams\AbonneBundle\Entity\AbonneSoc $abonneSoc = null)
    {
        $this->abonneSoc = $abonneSoc;

        return $this;
    }

    /**
     * Get abonneSoc
     *
     * @return \Ams\AbonneBundle\Entity\AbonneSoc 
     */
    public function getAbonneSoc()
    {
        return $this->abonneSoc;
    }

    /**
     * Set clientType
     *
     * @param integer $clientType
     * @return AbonneSoc
     */
    public function setClientType($clientType)
    {
        $this->clientType = $clientType;
    
        return $this;
    }

    /**
     * Get clientType
     *
     * @return integer 
     */
    public function getClientType()
    {
        return $this->clientType;
    }

    /**
     * Set nouvAppar
     *
     * @param integer $nouvAppar
     * @return ClientAServirLogist
     */
    public function setNouvAppar($nouvAppar)
    {
        $this->nouvAppar = $nouvAppar;
    
        return $this;
    }

    /**
     * Get nouvAppar
     *
     * @return integer 
     */
    public function getNouvAppar()
    {
        return $this->nouvAppar;
    }

    /**
     * Set dateAbsence
     *
     * @param \DateTime $dateAbsence
     * @return ClientAServirLogist
     */
    public function setDateAbsence($dateAbsence)
    {
        $this->dateAbsence = $dateAbsence;

        return $this;
    }

    /**
     * Get dateAbsence
     *
     * @return \DateTime 
     */
    public function getDateAbsence()
    {
        return $this->dateAbsence;
    }

    /**
     * Set abonneUnique
     *
     * @param \Ams\AbonneBundle\Entity\AbonneUnique $abonneUnique
     * @return AbonneSoc
     */
    public function setAbonneUnique(\Ams\AbonneBundle\Entity\AbonneUnique $abonneUnique)
    {
        $this->abonneUnique = $abonneUnique;
    
        return $this;
    }

    /**
     * Get abonneUnique
     *
     * @return \Ams\AbonneBundle\Entity\AbonneUnique 
     */
    public function getAbonneUnique()
    {
        return $this->abonneUnique;
    }

    /**
     * Set adresse
     *
     * @param \Ams\AdresseBundle\Entity\Adresse $adresse
     * @return ClientAServirLogist
     */
    public function setAdresse(\Ams\AdresseBundle\Entity\Adresse $adresse = null)
    {
        $this->adresse = $adresse;

        return $this;
    }

    /**
     * Get adresse
     *
     * @return \Ams\AdresseBundle\Entity\Adresse 
     */
    public function getAdresse()
    {
        return $this->adresse;
    }

    /**
     * Set rnvp
     *
     * @param \Ams\AdresseBundle\Entity\AdresseRnvp $rnvp
     * @return ClientAServirLogist
     */
    public function setRnvp(\Ams\AdresseBundle\Entity\AdresseRnvp $rnvp = null)
    {
        $this->rnvp = $rnvp;

        return $this;
    }

    /**
     * Get rnvp
     *
     * @return \Ams\AdresseBundle\Entity\AdresseRnvp 
     */
    public function getRnvp()
    {
        return $this->rnvp;
    }

    /**
     * Set pointLivraison
     *
     * @param \Ams\AdresseBundle\Entity\AdresseRnvp $pointLivraison
     * @return ClientAServirLogist
     */
    public function setPointLivraison(\Ams\AdresseBundle\Entity\AdresseRnvp $pointLivraison = null)
    {
        $this->pointLivraison = $pointLivraison;

        return $this;
    }

    /**
     * Get pointLivraison
     *
     * @return \Ams\AdresseBundle\Entity\AdresseRnvp 
     */
    public function getPointLivraison()
    {
        return $this->pointLivraison;
    }

    /**
     * Set commune
     *
     * @param \Ams\AdresseBundle\Entity\Commune $commune
     * @return ClientAServirLogist
     */
    public function setCommune(\Ams\AdresseBundle\Entity\Commune $commune = null)
    {
        $this->commune = $commune;

        return $this;
    }

    /**
     * Get commune
     *
     * @return \Ams\AdresseBundle\Entity\Commune 
     */
    public function getCommune()
    {
        return $this->commune;
    }

    /**
     * Set depot
     *
     * @param \Ams\SilogBundle\Entity\Depot $depot
     * @return ClientAServirLogist
     */
    public function setDepot(\Ams\SilogBundle\Entity\Depot $depot = null)
    {
        $this->depot = $depot;

        return $this;
    }

    /**
     * Get depot
     *
     * @return \Ams\SilogBundle\Entity\Depot 
     */
    public function getDepot()
    {
        return $this->depot;
    }

    /**
     * Set societe
     *
     * @param \Ams\ProduitBundle\Entity\Societe $societe
     * @return ClientAServirLogist
     */
    public function setSociete(\Ams\ProduitBundle\Entity\Societe $societe = null)
    {
        $this->societe = $societe;

        return $this;
    }

    /**
     * Get societe
     *
     * @return \Ams\ProduitBundle\Entity\Societe 
     */
    public function getSociete()
    {
        return $this->societe;
    }

    /**
     * Set produit
     *
     * @param \Ams\ProduitBundle\Entity\Produit $produit
     * @return ClientAServirLogist
     */
    public function setProduit(\Ams\ProduitBundle\Entity\Produit $produit = null)
    {
        $this->produit = $produit;

        return $this;
    }

    /**
     * Get produit
     *
     * @return \Ams\ProduitBundle\Entity\Produit 
     */
    public function getProduit()
    {
        return $this->produit;
    }

    /**
     * Set clientAServirSrc
     *
     * @param \Ams\DistributionBundle\Entity\ClientAServirSrc $clientAServirSrc
     * @return ClientAServirLogist
     */
    public function setClientAServirSrc(\Ams\DistributionBundle\Entity\ClientAServirSrc $clientAServirSrc = null)
    {
        $this->clientAServirSrc = $clientAServirSrc;

        return $this;
    }

    /**
     * Get clientAServirSrc
     *
     * @return \Ams\DistributionBundle\Entity\ClientAServirSrc 
     */
    public function getClientAServirSrc()
    {
        return $this->clientAServirSrc;
    }

    /**
     * Set employe
     *
     * @param \Ams\EmployeBundle\Entity\Employe $employe
     * @return ClientAServirLogist
     */
    public function setEmploye(\Ams\EmployeBundle\Entity\Employe $employe = null)
    {
        $this->employe = $employe;

        return $this;
    }

    /**
     * Get employe
     *
     * @return \Ams\EmployeBundle\Entity\Employe 
     */
    public function getEmploye()
    {
        return $this->employe;
    }

    /**
     * Set paiTournee
     *
     * @param \Ams\PaieBundle\Entity\PaiTournee $paiTournee
     * @return ClientAServirLogist
     */
    public function setPaiTournee(\Ams\PaieBundle\Entity\PaiTournee $paiTournee = null)
    {
        $this->paiTournee = $paiTournee;

        return $this;
    }

    /**
     * Get paiTournee
     *
     * @return \Ams\PaieBundle\Entity\PaiTournee 
     */
    public function getPaiTournee()
    {
        return $this->paiTournee;
    }

    /**
     * Set flux
     *
     * @param \Ams\ReferentielBundle\Entity\RefFlux $flux
     * @return ClientAServirLogist
     */
    public function setFlux(\Ams\ReferentielBundle\Entity\RefFlux $flux)
    {
        $this->flux = $flux;

        return $this;
    }

    /**
     * Get flux
     *
     * @return \Ams\ReferentielBundle\Entity\RefFlux 
     */
    public function getFlux()
    {
        return $this->flux;
    }

    /**
     * Set tournee
     *
     * @param \Ams\ModeleBundle\Entity\ModeleTourneeJour $tournee
     * @return ClientAServirLogist
     */
    public function setTournee(\Ams\ModeleBundle\Entity\ModeleTourneeJour $tournee = null)
    {
        $this->tournee = $tournee;

        return $this;
    }

    /**
     * Get tournee
     *
     * @return \Ams\ModeleBundle\Entity\ModeleTourneeJour 
     */
    public function getTournee()
    {
        return $this->tournee;
    }
}
