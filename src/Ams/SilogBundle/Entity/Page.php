<?php

namespace Ams\SilogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NavPage
 *
 * @ORM\Table(name="page")
 * @ORM\Entity(repositoryClass="Ams\SilogBundle\Repository\PageRepository")
 */
class Page
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
     * @ORM\Column(name="id_route", type="string", length=45, unique=true, nullable=false)
     */
    private $idRoute;

    /**
     * @var string
     *
     * @ORM\Column(name="desc_court", type="string", length=25, nullable=false)
     */
    private $descCourt;



    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;


    /**
     * @var string
     *
     * @ORM\Column(name="menu", type="string", nullable=false)
     */
    private $menu;

 

    /**
     * @var string
     *
     * @ORM\Column(name="pag_defaut", type="string", length=45, nullable=true)
     */
    private $defaut;


     /**
     * @var \SousCategorie
     *
     * @ORM\ManyToOne(targetEntity="SousCategorie", inversedBy="pages")
     * @ORM\JoinColumn(name="ss_cat_id", referencedColumnName="id")
     */
    private $ssCategorie;

    
    /**
     * @var \Page
     *
     * @ORM\OneToMany(targetEntity="PageElement" , mappedBy="page")
     */
    private $pageElements;
    

 
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->pageElements = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set idRoute
     *
     * @param string $idRoute
     * @return Page
     */
    public function setIdRoute($idRoute)
    {
        $this->idRoute = $idRoute;
    
        return $this;
    }

    /**
     * Get idRoute
     *
     * @return string 
     */
    public function getIdRoute()
    {
        return $this->idRoute;
    }

    /**
     * Set descCourt
     *
     * @param string $descCourt
     * @return Page
     */
    public function setDescCourt($descCourt)
    {
        $this->descCourt = $descCourt;
    
        return $this;
    }

    /**
     * Get descCourt
     *
     * @return string 
     */
    public function getDescCourt()
    {
        return $this->descCourt;
    }

    
    /**
     * Set description
     *
     * @param string $description
     * @return Page
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set menu
     *
     * @param string $menu
     * @return Page
     */
    public function setMenu($menu)
    {
        $this->menu = $menu;
    
        return $this;
    }

    /**
     * Get menu
     *
     * @return string 
     */
    public function getMenu()
    {
        return $this->menu;
    }


    /**
     * Set defaut
     *
     * @param string $defaut
     * @return Page
     */
    public function setDefaut($defaut)
    {
        $this->defaut = $defaut;
    
        return $this;
    }

    /**
     * Get defaut
     *
     * @return string 
     */
    public function getDefaut()
    {
        return $this->defaut;
    }

    /**
     * Set ssCategorie
     *
     * @param \Ams\SilogBundle\Entity\SousCategorie $ssCategorie
     * @return Page
     */
    public function setSsCategorie(\Ams\SilogBundle\Entity\SousCategorie $ssCategorie = null)
    {
        $this->ssCategorie = $ssCategorie;
    
        return $this;
    }

    /**
     * Get ssCategorie
     *
     * @return \Ams\SilogBundle\Entity\SousCategorie 
     */
    public function getSsCategorie()
    {
        return $this->ssCategorie;
    }

    /**
     * Add pageElements
     *
     * @param \Ams\SilogBundle\Entity\PageElement $pageElements
     * @return Page
     */
    public function addPageElement(\Ams\SilogBundle\Entity\PageElement $pageElements)
    {
        $this->pageElements[] = $pageElements;
    
        return $this;
    }

    /**
     * Remove pageElements
     *
     * @param \Ams\SilogBundle\Entity\PageElement $pageElements
     */
    public function removePageElement(\Ams\SilogBundle\Entity\PageElement $pageElements)
    {
        $this->pageElements->removeElement($pageElements);
    }

    /**
     * Get pageElements
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPageElements()
    {
        return $this->pageElements;
    }
}
