<?php

namespace Ams\ReferentielBundle\Repository;

use Doctrine\ORM\EntityRepository;

class RefReperageQualifRepository extends EntityRepository
{
	public function getTopage() {

        $qb = $this->_em->createQueryBuilder()
        					 ->select('t')
        					 ->from($this->_entityName, 't')
						       ->groupBy('t.topage')
                   ->orderBy('t.topage', 'ASC');

        return $qb;
		
	}
   
}
