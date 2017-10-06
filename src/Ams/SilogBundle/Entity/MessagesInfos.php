<?php

namespace Ams\SilogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MessagesInfos
 *
 * @ORM\Table(name="messages_infos")
 * @ORM\Entity(repositoryClass="Ams\SilogBundle\Repository\MessagesInfosRepository")
 */
class MessagesInfos
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
     * @ORM\Column(name="glyphicon", type="string", length=255)
     */
    private $glyphicon;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    private $description;




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
     * Set glyphicon
     *
     * @param string $glyphicon
     * @return MessagesInfos
     */
    public function setGlyphicon($glyphicon)
    {
        $this->glyphicon = $glyphicon;

        return $this;
    }

    /**
     * Get glyphicon
     *
     * @return string 
     */
    public function getGlyphicon()
    {
        return $this->glyphicon;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return MessagesInfos
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return MessagesInfos
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
}
