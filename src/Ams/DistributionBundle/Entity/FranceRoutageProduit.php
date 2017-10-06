<?php

namespace Ams\DistributionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FranceRoutageProduit
 *
 * @ORM\Table("france_routage_produit")
 * @ORM\Entity(repositoryClass="Ams\DistributionBundle\Entity\FranceRoutageProduitRepository")
 */
class FranceRoutageProduit
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
     * @ORM\Column(name="code", type="string", length=255)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="france_routage_script_code", type="string", length=255)
     */
    private $franceRoutageScriptCode;
    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=255)
     */
    private $libelle;


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
     * @return FranceRoutageProduit
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
     * Set franceRoutageScriptCode
     *
     * @param string $franceRoutageScriptCode
     * @return FranceRoutageProduit
     */
    public function setFranceRoutageScriptCode($franceRoutageScriptCode)
    {
        $this->franceRoutageScriptCode = $franceRoutageScriptCode;

        return $this;
    }

    /**
     * Get franceRoutageScriptCode
     *
     * @return string 
     */
    public function getFranceRoutageScriptCode()
    {
        return $this->franceRoutageScriptCode;
    }

    /**
     * Set libelle
     *
     * @param string $libelle
     * @return FranceRoutageProduit
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
}
