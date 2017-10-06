<?php

namespace Ams\SilogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * Profil
 *
 * @ORM\Table(name="profil")
 * @ORM\Entity(repositoryClass="Ams\SilogBundle\Repository\ProfilRepository")
 * @UniqueEntity(fields="code", message="Attention ce code  est déjà utilisé")
 * @UniqueEntity(fields="libelle", message="Attention le libellé est déjà utilisé")
 */
class Profil
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
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=20, unique=true, nullable=false)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=45, unique=true, nullable=false)
     */
    private $libelle;



    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="PageElement",  cascade={"persist"},  inversedBy="profils")
     * @ORM\JoinTable(name="profil_page_element",
     *   joinColumns={
     *     @ORM\JoinColumn(name="profil_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="page_elem_id", referencedColumnName="id")
     *   }
     * )
     */
    private $pageElements;
    

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
     * Set code
     *
     * @param string $code
     * @return Profil
     */
    public function setCode($code)
    {
        $this->code = $code;
    
        return $this;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set libelle
     *
     * @param string $libelle
     * @return Profil
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
     * Constructor
     */
    public function __construct()
    {
        $this->pageElements = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add pageElement
     *
     * @param \Ams\SilogBundle\Entity\PageElement $pageElement
     * @return Profil
     */
    public function addPageElement(\Ams\SilogBundle\Entity\PageElement $pageElement)
    {
        $this->pageElement[] = $pageElement;
    
        return $this;
    }

    /**
     * Remove pageElement
     *
     * @param \Ams\SilogBundle\Entity\PageElement $pageElement
     */
    public function removePageElement(\Ams\SilogBundle\Entity\PageElement $pageElement)
    {
        $this->pageElements->removeElement($pageElement);
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
