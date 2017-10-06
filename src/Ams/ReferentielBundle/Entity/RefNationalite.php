<?php

namespace Ams\ReferentielBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Jour
 *
 * @ORM\Table(name="ref_nationalite")
 * @ORM\Entity(repositoryClass="Ams\ReferentielBundle\Repository\RefNationaliteRepository")
 */
class RefNationalite
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=3, nullable=false, unique=true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=40, nullable=false)
     */
    private $libelle;
   
    /**
     * @var boolean
     *
     * @ORM\Column(name="appartenanceue", type="boolean", nullable=false, options={"default"=0})
     */
    private $appartenanceue;
}
