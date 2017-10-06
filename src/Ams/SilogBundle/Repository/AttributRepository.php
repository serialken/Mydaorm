<?php 

namespace Ams\SilogBundle\Repository;

use Doctrine\ORM\EntityRepository;

class AttributRepository extends EntityRepository
{
    public function insert($libelle)
    {
        $this->_em->getConnection()->insert('attribut', array('libelle' => $libelle));
        return $this->_em->getConnection()->lastInsertId();
    }
}