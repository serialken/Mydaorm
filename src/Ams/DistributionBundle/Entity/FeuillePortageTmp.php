<?php

namespace Ams\DistributionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FeuillePortageTmp
 *
 * @ORM\Table(name="feuille_portage_tmp")
 * @ORM\Entity(repositoryClass="Ams\DistributionBundle\Repository\FeuillePortageTmpRepository")
 */
class FeuillePortageTmp
{
    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="depot_id", type="integer")
     */
    private $depotId;

    /**
     * @var string
     *
     * @ORM\Column(name="depot_code", type="string", length=25)
     */
    private $depotCode;

    /**
     * @var string
     *
     * @ORM\Column(name="depot_libelle", type="string", length=255)
     */
    private $depotLibelle;

    /**
     * @var string
     *
     * @ORM\Column(name="vol1", type="string", length=255)
     */
    private $vol1;

    /**
     * @var string
     *
     * @ORM\Column(name="vol2", type="string", length=255)
     */
    private $vol2;
    
    /**
     * @var string
     *
     * @ORM\Column(name="vol3", type="string", length=255)
     */
    private $vol3;

    /**
     * @var integer
     *
     * @ORM\Column(name="qte", type="integer")
     */
    private $qte;

    /**
     * @var integer
     *
     * @ORM\Column(name="flux", type="integer")
     */
    private $flux;

    /**
     * @var integer
     *
     * @ORM\Column(name="produit_id", type="integer")
     */
    private $produitId;

    /**
     * @var string
     *
     * @ORM\Column(name="produit_code", type="string", length=255)
     */
    private $produitCode;

    /**
     * @var string
     *
     * @ORM\Column(name="produit_libelle", type="string", length=255)
     */
    private $produitLibelle;

    /**
     * @var integer
     *
     * @ORM\Column(name="tournee_jour_id", type="integer")
     */
    private $tourneeJourId;
    /**
     * @var string
     *
     * @ORM\Column(name="tournee_jour_code", type="string", length=255)
     */
    private $tourneeJourCode;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_porteur", type="string", length=255)
     */
    private $nomPorteur;

    /**
     * @var string
     *
     * @ORM\Column(name="prenom_porteur", type="string", length=255)
     */
    private $prenomPorteur;

    /**
     * @var string
     *
     * @ORM\Column(name="adresse", type="string", length=255)
     */
    private $adresse;

    /**
     * @var integer
     *
     * @ORM\Column(name="cp", type="integer")
     */
    private $cp;

    /**
     * @var string
     *
     * @ORM\Column(name="ville", type="string", length=255)
     */
    private $ville;

    /**
     * @var string
     *
     * @ORM\Column(name="num_abonne", type="string", length=255)
     */
    private $numAbonne;

    /**
     * @var string
     *
     * @ORM\Column(name="societe_code", type="string", length=255)
     */
    private $societeCode;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=255)
     */
    private $path;

    /**
     * @var integer
     *
     * @ORM\Column(name="point_livraison_id", type="integer")
     */
    private $pointLivraisonId;

    /**
     * @var integer
     *
     * @ORM\Column(name="point_livraison_ordre", type="integer")
     */
    private $pointLivraisonOrdre;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_service_1", type="datetime")
     */
    private $dateService1;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_stop", type="datetime")
     */
    private $dateStop;

    /**
     * @var string
     *
     * @ORM\Column(name="valeur", type="string", length=255)
     */
    private $valeur;

    /**
     * @var string
     *
     * @ORM\Column(name="info_portage_livraison", type="string", length=255)
     */
    private $infoPortageLivraison;


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
     * Set depotId
     *
     * @param integer $depotId
     * @return FeuillePortageTmp
     */
    public function setDepotId($depotId)
    {
        $this->depotId = $depotId;

        return $this;
    }

    /**
     * Get depotId
     *
     * @return integer 
     */
    public function getDepotId()
    {
        return $this->depotId;
    }

    /**
     * Set depotCode
     *
     * @param string $depotCode
     * @return FeuillePortageTmp
     */
    public function setDepotCode($depotCode)
    {
        $this->depotCode = $depotCode;

        return $this;
    }

    /**
     * Get depotCode
     *
     * @return string 
     */
    public function getDepotCode()
    {
        return $this->depotCode;
    }

    /**
     * Set depotLibelle
     *
     * @param string $depotLibelle
     * @return FeuillePortageTmp
     */
    public function setDepotLibelle($depotLibelle)
    {
        $this->depotLibelle = $depotLibelle;

        return $this;
    }

    /**
     * Get depotLibelle
     *
     * @return string 
     */
    public function getDepotLibelle()
    {
        return $this->depotLibelle;
    }

    /**
     * Set vol1
     *
     * @param string $vol1
     * @return FeuillePortageTmp
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
     * @return FeuillePortageTmp
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
     * @return FeuillePortageTmp
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
     * Set qte
     *
     * @param integer $qte
     * @return FeuillePortageTmp
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
     * Set flux
     *
     * @param integer $flux
     * @return FeuillePortageTmp
     */
    public function setFlux($flux)
    {
        $this->flux = $flux;

        return $this;
    }

    /**
     * Get flux
     *
     * @return integer 
     */
    public function getFlux()
    {
        return $this->flux;
    }

    /**
     * Set produitId
     *
     * @param integer $produitId
     * @return FeuillePortageTmp
     */
    public function setProduitId($produitId)
    {
        $this->produitId = $produitId;

        return $this;
    }

    /**
     * Get produitId
     *
     * @return integer 
     */
    public function getProduitId()
    {
        return $this->produitId;
    }

    /**
     * Set produitCode
     *
     * @param string $produitCode
     * @return FeuillePortageTmp
     */
    public function setProduitCode($produitCode)
    {
        $this->produitCode = $produitCode;

        return $this;
    }

    /**
     * Get produitCode
     *
     * @return string 
     */
    public function getProduitCode()
    {
        return $this->produitCode;
    }

    /**
     * Set produitLibelle
     *
     * @param string $produitLibelle
     * @return FeuillePortageTmp
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
     * Set tourneeJourCode
     *
     * @param string $tourneeJourCode
     * @return FeuillePortageTmp
     */
    public function setTourneeJourCode($tourneeJourCode)
    {
        $this->tourneeJourCode = $tourneeJourCode;

        return $this;
    }

    /**
     * Get tourneeJourCode
     *
     * @return string 
     */
    public function getTourneeJourCode()
    {
        return $this->tourneeJourCode;
    }

    /**
     * Set nomPorteur
     *
     * @param string $nomPorteur
     * @return FeuillePortageTmp
     */
    public function setNomPorteur($nomPorteur)
    {
        $this->nomPorteur = $nomPorteur;

        return $this;
    }

    /**
     * Get nomPorteur
     *
     * @return string 
     */
    public function getNomPorteur()
    {
        return $this->nomPorteur;
    }

    /**
     * Set prenomPorteur
     *
     * @param string $prenomPorteur
     * @return FeuillePortageTmp
     */
    public function setPrenomPorteur($prenomPorteur)
    {
        $this->prenomPorteur = $prenomPorteur;

        return $this;
    }

    /**
     * Get prenomPorteur
     *
     * @return string 
     */
    public function getPrenomPorteur()
    {
        return $this->prenomPorteur;
    }

    /**
     * Set adresse
     *
     * @param string $adresse
     * @return FeuillePortageTmp
     */
    public function setAdresse($adresse)
    {
        $this->adresse = $adresse;

        return $this;
    }

    /**
     * Get adresse
     *
     * @return string 
     */
    public function getAdresse()
    {
        return $this->adresse;
    }

    /**
     * Set cp
     *
     * @param integer $cp
     * @return FeuillePortageTmp
     */
    public function setCp($cp)
    {
        $this->cp = $cp;

        return $this;
    }

    /**
     * Get cp
     *
     * @return integer 
     */
    public function getCp()
    {
        return $this->cp;
    }

    /**
     * Set ville
     *
     * @param string $ville
     * @return FeuillePortageTmp
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
     * Set numAbonne
     *
     * @param string $numAbonne
     * @return FeuillePortageTmp
     */
    public function setNumAbonne($numAbonne)
    {
        $this->numAbonne = $numAbonne;

        return $this;
    }

    /**
     * Get numAbonne
     *
     * @return string 
     */
    public function getNumAbonne()
    {
        return $this->numAbonne;
    }

    /**
     * Set societeCode
     *
     * @param string $societeCode
     * @return FeuillePortageTmp
     */
    public function setSocieteCode($societeCode)
    {
        $this->societeCode = $societeCode;

        return $this;
    }

    /**
     * Get societeCode
     *
     * @return string 
     */
    public function getSocieteCode()
    {
        return $this->societeCode;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return FeuillePortageTmp
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set pointLivraisonId
     *
     * @param integer $pointLivraisonId
     * @return FeuillePortageTmp
     */
    public function setPointLivraisonId($pointLivraisonId)
    {
        $this->pointLivraisonId = $pointLivraisonId;

        return $this;
    }

    /**
     * Get pointLivraisonId
     *
     * @return integer 
     */
    public function getPointLivraisonId()
    {
        return $this->pointLivraisonId;
    }

    /**
     * Set pointLivraisonOrdre
     *
     * @param integer $pointLivraisonOrdre
     * @return FeuillePortageTmp
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
     * Set dateService1
     *
     * @param \DateTime $dateService1
     * @return FeuillePortageTmp
     */
    public function setDateService1($dateService1)
    {
        $this->dateService1 = $dateService1;

        return $this;
    }

    /**
     * Get dateService1
     *
     * @return \DateTime 
     */
    public function getDateService1()
    {
        return $this->dateService1;
    }

    /**
     * Set dateStop
     *
     * @param \DateTime $dateStop
     * @return FeuillePortageTmp
     */
    public function setDateStop($dateStop)
    {
        $this->dateStop = $dateStop;

        return $this;
    }

    /**
     * Get dateStop
     *
     * @return \DateTime 
     */
    public function getDateStop()
    {
        return $this->dateStop;
    }

    /**
     * Set valeur
     *
     * @param string $valeur
     * @return FeuillePortageTmp
     */
    public function setValeur($valeur)
    {
        $this->valeur = $valeur;

        return $this;
    }

    /**
     * Get valeur
     *
     * @return string 
     */
    public function getValeur()
    {
        return $this->valeur;
    }

    /**
     * Set infoPortageLivraison
     *
     * @param string $infoPortageLivraison
     * @return FeuillePortageTmp
     */
    public function setInfoPortageLivraison($infoPortageLivraison)
    {
        $this->infoPortageLivraison = $infoPortageLivraison;

        return $this;
    }

    /**
     * Get infoPortageLivraison
     *
     * @return string 
     */
    public function getInfoPortageLivraison()
    {
        return $this->infoPortageLivraison;
    }
}
