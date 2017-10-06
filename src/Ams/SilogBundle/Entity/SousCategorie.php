<?php


namespace Ams\SilogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Sous Categorie de page
 *
 * @ORM\Table(name="sous_categorie")
 * @ORM\Entity(repositoryClass="Ams\SilogBundle\Repository\SousCategorieRepository")
 */
class SousCategorie
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
     * @ORM\Column(name="libelle", type="string", length=30, nullable=false)
     */
    private $libelle;
    
    
    
    
    /**
     * @var string
     *
     * @ORM\Column(name="class_image", type="string", length=120 , nullable=false)
     */
    private $classImage;
    
    
     /**
     * @var string
     *
     * @ORM\Column(name="page_defaut", type="string", length=50,  nullable=false)
     */
    private $pageDefaut;
    
     /**
     * @var \Categorie
     *
     * @ORM\ManyToOne(targetEntity="Categorie" , inversedBy="ssCategories")
     * @ORM\JoinColumn(name="cat_id", referencedColumnName="id")
     */
    private $categorie;
    
    
    /**
     * @var \Page
     *
     * @ORM\OneToMany(targetEntity="Page" , mappedBy="ssCategorie")
     */
    private $pages;
    


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->pages = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return SousCategorie
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;
    
        return $this;
    }

    /**
     * Get classImage
     *
     * @return string 
     */
    public function getClassImage()
    {
        return $this->$classImage;
    }

    
      /**
     * Set libelle
     *
     * @param string $libelle
     * @return SousCategorie
     */
    public function setClassImage($classImage)
    {
        $this->classImage = $classImage;
    
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
     * Set categorie
     *
     * @param \Ams\SilogBundle\Entity\Categorie $categorie
     * @return SousCategorie
     */
    public function setCategorie(\Ams\SilogBundle\Entity\Categorie $categorie = null)
    {
        $this->categorie = $categorie;
    
        return $this;
    }

    /**
     * Get categorie
     *
     * @return \Ams\SilogBundle\Entity\Categorie 
     */
    public function getCategorie()
    {
        return $this->categorie;
    }

    /**
     * Add pages
     *
     * @param \Ams\SilogBundle\Entity\Page $pages
     * @return SousCategorie
     */
    public function addPage(\Ams\SilogBundle\Entity\Page $pages)
    {
        $this->pages[] = $pages;
    
        return $this;
    }

    /**
     * Remove pages
     *
     * @param \Ams\SilogBundle\Entity\Page $pages
     */
    public function removePage(\Ams\SilogBundle\Entity\Page $pages)
    {
        $this->pages->removeElement($pages);
    }

    /**
     * Get pages
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * Set pageDefaut
     *
     * @param string $pageDefaut
     * @return SousCategorie
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
}
