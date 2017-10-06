<?php

namespace Ams\ReferentielBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ref_NatureClient
 *
 * @ORM\Table(name="ref_natureclient")
 * @ORM\Entity(repositoryClass="Ams\ReferentielBundle\Repository\RefNatureClientRepository")
 */
class RefNatureClient
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
     * @ORM\Column(name="code", type="string", length=1, nullable=false, unique=true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=32, nullable=false)
     */
    private $libelle;
    
    /**
     * @var time
     *
     * @ORM\Column(name="duree_livraison", type="time", nullable=false)
     */
    private $dureeLivraison;

    /**
     * @var \RefTypeUrssaf
     *
     * @ORM\ManyToOne(targetEntity="Ams\ReferentielBundle\Entity\RefTypeUrssaf")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="typeurssaf_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $typeurssaf;

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
     * @return RefNatureClient
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
     * Set typeurssaf
     *
     * @param string $typeurssaf
     * @return RefNatureClient
     */
    public function setTypeurssaf($typeurssaf)
    {
        $this->typeurssaf = $typeurssaf;

        return $this;
    }

    /**
     * Get typeurssaf
     *
     * @return string 
     */
    public function getTypeurssaf()
    {
        return $this->typeurssaf;
    }

    /**
     * Set id
     *
     * @param integer $id
     * @return RefNatureClient
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return RefNatureClient
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
     * Set dureeLivraison
     *
     * @param \DateTime $dureeLivraison
     * @return RefNatureClient
     */
    public function setDureeLivraison($dureeLivraison)
    {
        $this->dureeLivraison = $dureeLivraison;

        return $this;
    }

    /**
     * Get dureeLivraison
     *
     * @return \DateTime 
     */
    public function getDureeLivraison()
    {
        return $this->dureeLivraison;
    }
}
