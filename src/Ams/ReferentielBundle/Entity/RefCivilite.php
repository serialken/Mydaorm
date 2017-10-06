<?php

namespace Ams\ReferentielBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Jour
 *
 * @ORM\Table(name="ref_civilite")
 * @ORM\Entity(repositoryClass="Ams\ReferentielBundle\Repository\RefCiviliteRepository")
 */
class RefCivilite
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false, nullable=false)
     * @ORM\Id
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=4, nullable=false, unique=true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=32, nullable=false)
     */
    private $libelle;

}
