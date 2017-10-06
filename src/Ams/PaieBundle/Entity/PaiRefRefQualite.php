<?php

namespace Ams\PaieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PaiRefRefQualite
 *
 * @ORM\Table(name="pai_ref_ref_qualite"
 * )
 * @ORM\Entity
 */
class PaiRefRefQualite
{
    /**
     * @var string
     *
     * @ORM\Column(name="qualite", type="string", length=1, nullable=false)
     * @ORM\Id
     */
    private $qualite;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=128, nullable=false)
     */
    private $libelle;

    /**
     * @var integer
     *
     * @ORM\Column(name="ordre", type="integer", nullable=false)
     */
    private $ordre;
}
