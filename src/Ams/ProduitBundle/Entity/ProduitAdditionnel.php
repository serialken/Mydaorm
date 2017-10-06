<?php

namespace Ams\ProduitBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProduitAdditionnel
 *
 * @ORM\Table(name="produit_additionnel")
 * @ORM\Entity(repositoryClass="Ams\ProduitBundle\Repository\ProduitAdditionnelRepository")
 */
class ProduitAdditionnel
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
     * @var string
     *
     * @ORM\Column(name="titre", type="string", length=255)
     */
    private $titre;

    /**
     * @var string
     *
     * @ORM\Column(name="auteur", type="string", length=255)
     */
    private $auteur;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_distrib", type="datetime")
     */
    private $dateDistrib;
    
    /**
     * @var \Ams\ProduitBundle\Entity\Produit
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ProduitBundle\Entity\Produit")
     * @ORM\JoinColumn(name="produit_reference", referencedColumnName="id", nullable=true)
     */
    protected $produitReference;
    
    /**
     * @var \Ams\ProduitBundle\Entity\Produit
     *
     * @ORM\ManyToOne(targetEntity="\Ams\ProduitBundle\Entity\Produit")
     * @ORM\JoinColumn(name="produit_additionnel", referencedColumnName="id", nullable=true)
     */
    protected $produitAdditionnel;


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
     * Set tirtre
     *
     * @param string $tirtre
     * @return ProduitAdditionnel
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;

        return $this;
    }

    /**
     * Get tirtre
     *
     * @return string 
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * Set auteur
     *
     * @param string $auteur
     * @return ProduitAdditionnel
     */
    public function setAuteur($auteur)
    {
        $this->auteur = $auteur;

        return $this;
    }

    /**
     * Get auteur
     *
     * @return string 
     */
    public function getAuteur()
    {
        return $this->auteur;
    }

    /**
     * Set dateDistrib
     *
     * @param \DateTime $dateDistrib
     * @return ProduitAdditionnel
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
     * Set produitReference
     *
     * @param \Ams\ProduitBundle\Entity\Produit $produitReference
     * @return ProduitAdditionnel
     */
    public function setProduitReference(\Ams\ProduitBundle\Entity\Produit $produitReference = null)
    {
        $this->produitReference = $produitReference;

        return $this;
    }

    /**
     * Get produitReference
     *
     * @return \Ams\ProduitBundle\Entity\Produit 
     */
    public function getProduitReference()
    {
        return $this->produitReference;
    }

    /**
     * Set produitAdditionnel
     *
     * @param \Ams\ProduitBundle\Entity\Produit $produitAdditionnel
     * @return ProduitAdditionnel
     */
    public function setProduitAdditionnel(\Ams\ProduitBundle\Entity\Produit $produitAdditionnel = null)
    {
        $this->produitAdditionnel = $produitAdditionnel;

        return $this;
    }

    /**
     * Get produitAdditionnel
     *
     * @return \Ams\ProduitBundle\Entity\Produit 
     */
    public function getProduitAdditionnel()
    {
        return $this->produitAdditionnel;
    }
}
