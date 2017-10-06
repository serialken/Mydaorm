<?php

namespace Ams\DistributionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TypeInfoPortage
 *
 * @ORM\Table(name="type_info_portage")
 * @ORM\Entity(repositoryClass="Ams\DistributionBundle\Repository\TypeInfoPortageRepository")
 */
class TypeInfoPortage
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=255)
     */
    protected $libelle;
    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=10)
     */
    protected $code;


        
    /**
     * @var Boolean
     * @ORM\Column(name="active", type="boolean", nullable=false)
    */
    private $active = true;
    
    
    
    /**
     * INFO_TECHNIQUE : type info venant du fichier
     * INFO_UTILISATEUR : type info ajouter par les utilisateurs
     * @var string
     * @ORM\Column(name="categorie", columnDefinition="ENUM('INFO_TECHNIQUE', 'INFO_UTILISATEUR')")
    */
    private $categorie ="INFO_UTILISATEUR";
    
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
     * @return TypeInfoPortage
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
     * Set code
     *
     * @param string $code
     * @return TypeInfoPortage
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
     * Set active
     *
     * @param boolean $active
     * @return TypeInfoPortage
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean 
     */
    public function getActive()
    {
        return $this->active;
    }
    
    
    /**
     * Set categorie
     * @param smallint $active
     * @return TypeInfoPortage
     */
    public function setCategorie($categorie)
    {
        $this->categorie = $categorie;

        return $this;
    }

    /**
     * Get categorie
     *
     * @return smallint 
     */
    public function getCategorie()
    {
        return $this->categorie;
    }
}
