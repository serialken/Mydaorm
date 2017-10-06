<?php

namespace Ams\DistributionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ClientAServirAdditionnel
 *
 * @ORM\Table(name="client_a_servir_additionnel")
 * @ORM\Entity(repositoryClass="Ams\DistributionBundle\Repository\ClientAServirAdditionnelRepository")
 */
class ClientAServirAdditionnel
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
     * @ORM\Column(name="numabo_ext", type="string", length=255)
     */
    private $numaboExt;

    /**
     * @var string
     *
     * @ORM\Column(name="adresse", type="string", length=255)
     */
    private $adresse;

    /**
     * @var integer
     *
     * @ORM\Column(name="cp", type="integer")
     */
    private $cp;

    /**
     * @var string
     *
     * @ORM\Column(name="ville", type="string", length=255)
     */
    private $ville;

    /**
     * @var string
     *
     * @ORM\Column(name="vol1", type="string", length=255)
     */
    private $vol1;

    /**
     * @var string
     *
     * @ORM\Column(name="vol2", type="string", length=255)
     */
    private $vol2;


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
     * Set numaboExt
     *
     * @param string $numaboExt
     * @return ClientAServirAdditionnel
     */
    public function setNumaboExt($numaboExt)
    {
        $this->numaboExt = $numaboExt;

        return $this;
    }

    /**
     * Get numaboExt
     *
     * @return string 
     */
    public function getNumaboExt()
    {
        return $this->numaboExt;
    }

    /**
     * Set adresse
     *
     * @param string $adresse
     * @return ClientAServirAdditionnel
     */
    public function setAdresse($adresse)
    {
        $this->adresse = $adresse;

        return $this;
    }

    /**
     * Get adresse
     *
     * @return string 
     */
    public function getAdresse()
    {
        return $this->adresse;
    }

    /**
     * Set cp
     *
     * @param integer $cp
     * @return ClientAServirAdditionnel
     */
    public function setCp($cp)
    {
        $this->cp = $cp;

        return $this;
    }

    /**
     * Get cp
     *
     * @return integer 
     */
    public function getCp()
    {
        return $this->cp;
    }

    /**
     * Set ville
     *
     * @param string $ville
     * @return ClientAServirAdditionnel
     */
    public function setVille($ville)
    {
        $this->ville = $ville;

        return $this;
    }

    /**
     * Get ville
     *
     * @return string 
     */
    public function getVille()
    {
        return $this->ville;
    }

    /**
     * Set vol1
     *
     * @param string $vol1
     * @return ClientAServirAdditionnel
     */
    public function setVol1($vol1)
    {
        $this->vol1 = $vol1;

        return $this;
    }

    /**
     * Get vol1
     *
     * @return string 
     */
    public function getVol1()
    {
        return $this->vol1;
    }

    /**
     * Set vol2
     *
     * @param string $vol2
     * @return ClientAServirAdditionnel
     */
    public function setVol2($vol2)
    {
        $this->vol2 = $vol2;

        return $this;
    }

    /**
     * Get vol2
     *
     * @return string 
     */
    public function getVol2()
    {
        return $this->vol2;
    }
}
