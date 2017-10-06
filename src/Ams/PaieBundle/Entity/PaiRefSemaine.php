<?php

namespace Ams\PaieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PaiRefMois
 *
 * @ORM\Table(name="pai_ref_semaine"
 *                ,indexes={@ORM\Index(name="idx1_pai_ref_semaine", columns={"date_debut","date_fin"})
 *                          }
 * )
 * @ORM\Entity(repositoryClass="Ams\PaieBundle\Repository\PaiRefSemaineRepository")
 */
class PaiRefSemaine
{
    /**
     * @var string
     *
     * @ORM\Column(name="anneesem", type="string", length=6, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $anneesem;
    /**
     * @var string
     *
     * @ORM\Column(name="annee", type="string", length=4, nullable=false)
     */
    private $annee;

    /**
     * @var string
     *
     * @ORM\Column(name="numsem", type="integer", nullable=false)
     */
    private $numsem;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_debut", type="date", nullable=false)
     */
    private $date_debut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_fin", type="date", nullable=false)
     */
    private $date_fin;
}
