<?php

namespace Ams\DistributionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * FeuillePortage
 *
 * @ORM\Table(name="feuille_portage", indexes={ @Index(name="fp_idx_dat", columns={"date_distrib"})
 *                                              , @Index(name="fp_idx_dat_depot", columns={"date_distrib","depot_id"})
 *                                                  }
 *              )
 * @ORM\Entity(repositoryClass="Ams\DistributionBundle\Repository\FeuillePortageRepository")
 */
class FeuillePortage
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
     * @var string
     *
     * @ORM\Column(name="numabo_ext", type="string", length=50, nullable=false)
     */
    private $numaboExt;

    /**
     * @var string
     *
     * @ORM\Column(name="vol1", type="string", length=38, nullable=true)
     */
    private $vol1;

    /**
     * @var string
     *
     * @ORM\Column(name="vol2", type="string", length=38, nullable=true)
     */
    private $vol2;

    /**
     * @var string
     * @ORM\Column(name="vol3", type="string", length=38, nullable=true)
     */
    private $vol3;

    /**
     * @var string
     *
     * @ORM\Column(name="vol4", type="string", length=38, nullable=true)
     */
    private $vol4;

    /**
     * @var string
     *
     * @ORM\Column(name="num_voie", type="string", length=10, nullable=true)
     */
    private $numVoie;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_voie", type="string", length=38, nullable=true)
     */
    private $nomVoie;

    /**
     * @var string
     * @ORM\Column(name="vol5", type="string", length=38, nullable=true)
     */
    private $vol5;

    /**
     * @var string
     * @ORM\Column(name="cp", type="string", length=5, nullable=false)
     */
    private $cp;

    /**
     * @var string
     * @ORM\Column(name="ville", type="string", length=45, nullable=false)
     */
    private $ville;
    
    /**
     * @var string
     * @ORM\Column(name="produit_libelle", type="string", length=45, nullable=false)
     */
    private $produitLibelle;

    /**
     * @var string
     *
     * @ORM\Column(name="info_portage", type="string", length=255, nullable=true)
     */
    private $infoPortage;

    /**
     * @var integer
     *
     * @ORM\Column(name="qte", type="integer", nullable=true)
     */
    private $qte;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="ordre", type="integer", nullable=true)
     */
    private $ordre;
    
    /**
     * Tournee
     * @var \Ams\ModeleBundle\Entity\ModeleTourneeJour
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ModeleBundle\Entity\ModeleTourneeJour")
     * @ORM\JoinColumn(name="tournee_jour_id", referencedColumnName="id", nullable=true)
     */
    private $tournee;

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
     * @ORM\ManyToOne(targetEntity="Ams\ProduitBundle\Entity\Produit")
     */
    private $produit;

    /**
     * N : Nouveau
     * S : Suspendu
     * - : normale
     * @var string
     *
     * @ORM\Column(name="situation", type="string", length=2, nullable=true)
     */
    private $situation;
    
    
    

    

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
     * @return FeuillePortage
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
     * @return FeuillePortage
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
     * @return FeuillePortage
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
     * Set numaboExt
     *
     * @param string $numaboExt
     * @return FeuillePortage
     */
    public function setNumaboExt($numaboExt)
    {
        $this->numaboExt = $numaboExt;

        return $this;
    }

    /**
     * Get numaboExt
     *
     * @return string 
     */
    public function getNumaboExt()
    {
        return $this->numaboExt;
    }

    /**
     * Set vol1
     *
     * @param string $vol1
     * @return FeuillePortage
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
     * @return FeuillePortage
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
     * Set vol3
     *
     * @param string $vol3
     * @return FeuillePortage
     */
    public function setVol3($vol3)
    {
        $this->vol3 = $vol3;

        return $this;
    }

    /**
     * Get vol3
     *
     * @return string 
     */
    public function getVol3()
    {
        return $this->vol3;
    }

    /**
     * Set vol4
     *
     * @param string $vol4
     * @return FeuillePortage
     */
    public function setVol4($vol4)
    {
        $this->vol4 = $vol4;

        return $this;
    }

    /**
     * Get vol4
     *
     * @return string 
     */
    public function getVol4()
    {
        return $this->vol4;
    }

    /**
     * Set numVoie
     *
     * @param string $numVoie
     * @return FeuillePortage
     */
    public function setNumVoie($numVoie)
    {
        $this->numVoie = $numVoie;

        return $this;
    }

    /**
     * Get numVoie
     *
     * @return string 
     */
    public function getNumVoie()
    {
        return $this->numVoie;
    }

    /**
     * Set nomVoie
     *
     * @param string $nomVoie
     * @return FeuillePortage
     */
    public function setNomVoie($nomVoie)
    {
        $this->nomVoie = $nomVoie;

        return $this;
    }

    /**
     * Get nomVoie
     *
     * @return string 
     */
    public function getNomVoie()
    {
        return $this->nomVoie;
    }

    /**
     * Set vol5
     *
     * @param string $vol5
     * @return FeuillePortage
     */
    public function setVol5($vol5)
    {
        $this->vol5 = $vol5;

        return $this;
    }

    /**
     * Get vol5
     *
     * @return string 
     */
    public function getVol5()
    {
        return $this->vol5;
    }

    /**
     * Set cp
     *
     * @param string $cp
     * @return FeuillePortage
     */
    public function setCp($cp)
    {
        $this->cp = $cp;

        return $this;
    }

    /**
     * Get cp
     *
     * @return string 
     */
    public function getCp()
    {
        return $this->cp;
    }

    /**
     * Set ville
     *
     * @param string $ville
     * @return FeuillePortage
     */
    public function setVille($ville)
    {
        $this->ville = $ville;

        return $this;
    }

    /**
     * Get ville
     *
     * @return string 
     */
    public function getVille()
    {
        return $this->ville;
    }

    /**
     * Set infoPortage
     *
     * @param string $infoPortage
     * @return FeuillePortage
     */
    public function setInfoPortage($infoPortage)
    {
        $this->infoPortage = $infoPortage;

        return $this;
    }

    /**
     * Get infoPortage
     *
     * @return string 
     */
    public function getInfoPortage()
    {
        return $this->infoPortage;
    }

    /**
     * Set qte
     *
     * @param integer $qte
     * @return FeuillePortage
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
     * Set situation
     *
     * @param string $situation
     * @return FeuillePortage
     */
    public function setSituation($situation)
    {
        $this->situation = $situation;

        return $this;
    }

    /**
     * Get situation
     *
     * @return string 
     */
    public function getSituation()
    {
        return $this->situation;
    }

    /**
     * Set tournee
     *
     * @param \Ams\ModeleBundle\Entity\ModeleTourneeJour $tournee
     * @return FeuillePortage
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

    /**
     * Set depot
     *
     * @param \Ams\SilogBundle\Entity\Depot $depot
     * @return FeuillePortage
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
     * Set flux
     *
     * @param \Ams\ReferentielBundle\Entity\RefFlux $flux
     * @return FeuillePortage
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
     * Set produitLibelle
     *
     * @param string $produitLibelle
     * @return FeuillePortage
     */
    public function setProduitLibelle($produitLibelle)
    {
        $this->produitLibelle = $produitLibelle;

        return $this;
    }

    /**
     * Get produitLibelle
     *
     * @return string 
     */
    public function getProduitLibelle()
    {
        return $this->produitLibelle;
    }

    /**
     * Set produit
     *
     * @param \Ams\ProduitBundle\Entity\Produit $produit
     * @return FeuillePortage
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
     * Set ordre
     *
     * @param integer $ordre
     * @return FeuillePortage
     */
    public function setOrdre($ordre)
    {
        $this->ordre = $ordre;

        return $this;
    }

    /**
     * Get ordre
     *
     * @return integer 
     */
    public function getOrdre()
    {
        return $this->ordre;
    }
}
