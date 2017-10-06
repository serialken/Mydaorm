<?php

namespace Ams\SilogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NavPageElement
 *
 * @ORM\Table(name="page_element")
 * @ORM\Entity(repositoryClass="Ams\SilogBundle\Repository\PageElementRepository")
 */
class PageElement
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     */
    private $id;

    /**
     * @var \Page
     *
     * @ORM\ManyToOne(targetEntity="Page", inversedBy="pageElements")
     * @ORM\JoinColumn(name="pag_id", referencedColumnName="id")
     */
    private $page;

    /**
     * @var string
     *
     * @ORM\Column(name="desc_court", type="string", length=30, nullable=false)
     */
    private $descCourt;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=45, nullable=false)
     */
    private $libelle;

    /**
     * @var integer
     *
     * @ORM\Column(name="oblig", type="integer", nullable=false)
     */
    private $obligatoire;

   
    
     /**
     * @var \Profil
     *
     * @ORM\ManyToMany(targetEntity="Profil" , mappedBy="pageElements")
     */
    private $profils;
    

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
     * Set descCourt
     *
     * @param string $descCourt
     * @return PageElement
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
     * Set libelle
     *
     * @param string $libelle
     * @return PageElement
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
     * Set obligatoire
     *
     * @param integer $obligatoire
     * @return PageElement
     */
    public function setObligatoire($obligatoire)
    {
        $this->obligatoire = $obligatoire;
    
        return $this;
    }

    /**
     * Get obligatoire
     *
     * @return integer 
     */
    public function getObligatoire()
    {
        return $this->obligatoire;
    }

    /**
     * Set page
     *
     * @param \Ams\SilogBundle\Entity\Page $page
     * @return PageElement
     */
    public function setPage(\Ams\SilogBundle\Entity\Page $page = null)
    {
        $this->page = $page;
    
        return $this;
    }

    /**
     * Get page
     *
     * @return \Ams\SilogBundle\Entity\Page 
     */
    public function getPage()
    {
        return $this->page;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->profils = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add profils
     *
     * @param \Ams\SilogBundle\Entity\Profil $profils
     * @return PageElement
     */
    public function addProfil(\Ams\SilogBundle\Entity\Profil $profils)
    {
        $this->profils[] = $profils;

        return $this;
    }

    /**
     * Remove profils
     *
     * @param \Ams\SilogBundle\Entity\Profil $profils
     */
    public function removeProfil(\Ams\SilogBundle\Entity\Profil $profils)
    {
        $this->profils->removeElement($profils);
    }

    /**
     * Get profils
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProfils()
    {
        return $this->profils;
    }
}
