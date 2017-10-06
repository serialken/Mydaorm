<?php

namespace Ams\SilogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Symfony\Component\Validator\Constraints as Assert;




/**
 * GroupeDepot
 *
 * @ORM\Table(name="groupe_depot")
 * @ORM\Entity(repositoryClass="Ams\SilogBundle\Repository\GroupeDepotRepository")
 * @UniqueEntity(fields="code", message="Attention ce code  est déjà utilisé")
 */
class GroupeDepot
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
     * @ORM\Column(name="code", type="string", length=10, unique=true, nullable=false)
     * @Assert\NotBlank()
     */
    private $code;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="libelle", type="string", length=45, nullable=false)
     */
    private $libelle;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Depot",  cascade={"persist"})
     * @ORM\JoinTable(name="dep_groupe_depot",
     *   joinColumns={
     *     @ORM\JoinColumn(name="grd_code", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="dep_code", referencedColumnName="id")
     *   }
     * )
     */
    private $depots;
    

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->depots = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set code
     *
     * @param string $code
     * @return GroupeDepot
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
     * @return GroupeDepot
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
     * Add depots
     *
     * @param \Ams\SilogBundle\Entity\Depot $depot
     * @return GroupeDepot
     */
    public function addDepot(\Ams\SilogBundle\Entity\Depot $depot)
    {
        $this->depots[] = $depot;
    
        return $this;
    }

    /**
     * Remove depots
     *
     * @param \Ams\SilogBundle\Entity\Depot $depot
     */
    public function removeDepot(\Ams\SilogBundle\Entity\Depot $depot)
    {
        $this->depots->removeElement($depot);
    }

    /**
     * Get depots
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDepots()
    {
        return $this->depots;
    }
}
