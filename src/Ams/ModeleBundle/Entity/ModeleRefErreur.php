<?php

namespace Ams\ModeleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PaiJournal
 *
 * @ORM\Table(name="modele_ref_erreur"
 *            ,indexes={@ORM\Index(name="idx1_pai_ref_erreur", columns={"rubrique","code"})
 *                     }
 *          )
 * @ORM\Entity(repositoryClass="Ams\ModeleBundle\Repository\ModeleRefErreurRepository")
 */
class ModeleRefErreur {

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
     * @ORM\Column(name="rubrique", type="string", length=2, nullable=false)
     */
    private $rubrique;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=3, nullable=false)
     */
    private $code;

    /**
     * @var integer
     *
     * @ORM\Column(name="level",  type="decimal", precision=1, scale=0, nullable=false)
     */
    private $level;

    /**
     * @var string
     *
     * @ORM\Column(name="msg", type="string", length=128, nullable=false)
     */
    private $msg;

    /**
     * @var boolean
     *
     * @ORM\Column(name="valide", type="boolean", nullable=false)
     */
    private $valide;

    /**
     * @var string
     *
     * @ORM\Column(name="couleur", type="string", length=7, options={"default" : "#FFFFFF"})
     */
    private $couleur;

}
