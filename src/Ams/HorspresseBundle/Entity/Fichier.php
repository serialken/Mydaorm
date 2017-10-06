<?php

namespace Ams\HorspresseBundle\Entity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Fichier
 *
 * @ORM\Table("hp_fichier")
 * @ORM\Entity(repositoryClass="Ams\HorspresseBundle\Entity\FichierRepository")
 */
class Fichier
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
     * @ORM\Column(name="path", type="string", length=255)
     */
    private $path;

    /**
     * @var string
     *
     * @ORM\Column(name="fichier", type="string", length=255)
     * @Assert\File(mimeTypes={ "text/plain","application/csv","text/csv" })
     */
    private $fichier;

    /**
     * @var string
     *
     * @ORM\Column(name="structure", type="json_array")
     */
    private $structure;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var array
     *
     * @ORM\Column(name="config", type="json_array")
     */
    private $config;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var array
     *
     * @ORM\Column(name="datas", type="json_array")
     */
    private $datas;


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
     * Set path
     *
     * @param string $path
     * @return Fichier
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Fichier
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set config
     *
     * @param array $config
     * @return Fichier
     */
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Get config
     *
     * @return array 
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Fichier
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set datas
     *
     * @param array $datas
     * @return Fichier
     */
    public function setDatas($datas)
    {
        $this->datas = $datas;

        return $this;
    }

    /**
     * Get datas
     *
     * @return array 
     */
    public function getDatas()
    {
        return $this->datas;
    }

    /**
     * Set fichier
     *
     * @param string $fichier
     * @return Fichier
     */
    public function setFichier($fichier)
    {
        $this->fichier = $fichier;

        return $this;
    }

    /**
     * Get fichier
     *
     * @return string 
     */
    public function getFichier()
    {
        return $this->fichier;
    }

    /**
     * Set structure
     *
     * @param string $structure
     * @return Fichier
     */
    public function setStructure($structure)
    {
        $this->structure = $structure;

        return $this;
    }

    /**
     * Get structure
     *
     * @return string 
     */
    public function getStructure()
    {
        return $this->structure;
    }
}
