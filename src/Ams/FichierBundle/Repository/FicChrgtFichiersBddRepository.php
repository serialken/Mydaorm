<?php 

namespace Ams\FichierBundle\Repository;

use Doctrine\ORM\EntityRepository;

class FicChrgtFichiersBddRepository extends EntityRepository
{
    public function getParamFluxByCode($sCode)
    {
        return $this->findOneBy(array('code' => $sCode));
    }
}