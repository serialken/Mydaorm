<?php

namespace Ams\SilogBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Categorie de page
 *
 * @ORM\Table(name="categorie")
 * @ORM\Entity(repositoryClass="Ams\SilogBundle\Repository\CategorieRepository")
 */
class Categorie
{

     /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     */
    private $id;


    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=50,  nullable=false)
     */
    private $libelle;
    
    
    /**
     * @var string
     *
     * @ORM\Column(name="page_defaut", type="string", length=50, nullable=false)
     */
    private $pageDefaut;

     /**
     * @var \SousCategorie
     *
     * @ORM\OneToMany(targetEntity="SousCategorie" , mappedBy="categorie")
     */
    private $ssCategories;

    
        
    /**
     * @var string
     *
     * @ORM\Column(name="class_image", type="string", length=120 , nullable=false)
     */
    private $urlImage;
  
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ssCategories = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
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
     * Set libelle
     *
     * @param string $libelle
     * @return Categorie
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;
    
        return $this;
    }

    /**
     * Get libelle
     *
     * @return string 
     */
    public function getLibelle()
    {
        return $this->libelle;
    }
    
    
      /**
     * Set libelle
     *
     * @param string $libelle
     * @return Categorie
     */
    public function setclassImage($urlImage)
    {
        $this->urlImage = $urlImage;
    
        return $this;
    }

    /**
     * Get url_image
     *
     * @return string 
     */
    public function getclassImage()
    {
        return $this->urlImage;
    }
    

    /**
     * Add ssCategories
     *
     * @param \Ams\SilogBundle\Entity\SousCategorie $ssCategories
     * @return Categorie
     */
    public function addSsCategorie(\Ams\SilogBundle\Entity\SousCategorie $ssCategories)
    {
        $this->ssCategories[] = $ssCategories;
    
        return $this;
    }

    /**
     * Remove ssCategories
     *
     * @param \Ams\SilogBundle\Entity\SousCategorie $ssCategories
     */
    public function removeSsCategorie(\Ams\SilogBundle\Entity\SousCategorie $ssCategories)
    {
        $this->ssCategories->removeElement($ssCategories);
    }

    /**
     * Get ssCategories
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSsCategories()
    {
        return $this->ssCategories;
    }

    /**
     * Set pageDefaut
     *
     * @param string $pageDefaut
     * @return Categorie
     */
    public function setPageDefaut($pageDefaut)
    {
        $this->pageDefaut = $pageDefaut;
    
        return $this;
    }

    /**
     * Get pageDefaut
     *
     * @return string 
     */
    public function getPageDefaut()
    {
        return $this->pageDefaut;
    }

    /**
     * Set urlImage
     *
     * @param string $urlImage
     * @return Categorie
     */
    public function setUrlImage($urlImage)
    {
        $this->urlImage = $urlImage;

        return $this;
    }

    /**
     * Get urlImage
     *
     * @return string 
     */
    public function getUrlImage()
    {
        return $this->urlImage;
    }

    /**
     * Add ssCategories
     *
     * @param \Ams\SilogBundle\Entity\SousCategorie $ssCategories
     * @return Categorie
     */
    public function addSsCategory(\Ams\SilogBundle\Entity\SousCategorie $ssCategories)
    {
        $this->ssCategories[] = $ssCategories;

        return $this;
    }

    /**
     * Remove ssCategories
     *
     * @param \Ams\SilogBundle\Entity\SousCategorie $ssCategories
     */
    public function removeSsCategory(\Ams\SilogBundle\Entity\SousCategorie $ssCategories)
    {
        $this->ssCategories->removeElement($ssCategories);
    }
}
