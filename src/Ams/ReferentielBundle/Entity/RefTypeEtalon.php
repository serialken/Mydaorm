<?php

namespace Ams\ReferentielBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RefTypeEtalon
 *
 * @ORM\Table(name="ref_typeetalon")
 * @ORM\Entity(repositoryClass="Ams\ReferentielBundle\Repository\RefTypeEtalonRepository")
 */
class RefTypeEtalon
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
     * @ORM\Column(name="libelle", type="string", length=32, nullable=false)
     */
    private $libelle;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="lundi", type="boolean", nullable=false)
     */
    private $lundi;

    /**
     * @var boolean
     *
     * @ORM\Column(name="mardi", type="boolean", nullable=false)
     */
    private $mardi;

    /**
     * @var boolean
     *
     * @ORM\Column(name="mercredi", type="boolean", nullable=false)
     */
    private $mercredi;

    /**
     * @var boolean
     *
     * @ORM\Column(name="jeudi", type="boolean", nullable=false)
     */
    private $jeudi;

    /**
     * @var boolean
     *
     * @ORM\Column(name="vendredi", type="boolean", nullable=false)
     */
    private $vendredi;

    /**
     * @var boolean
     *
     * @ORM\Column(name="samedi", type="boolean", nullable=false)
     */
    private $samedi;

    /**
     * @var boolean
     *
     * @ORM\Column(name="dimanche", type="boolean", nullable=false)
     */
    private $dimanche;
}
