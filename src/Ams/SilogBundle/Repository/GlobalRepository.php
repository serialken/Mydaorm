<?php

namespace Ams\SilogBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Lib\SqlField;

class GlobalRepository extends EntityRepository {

    public $sqlField;

    public function __construct($em, $class) {
        parent::__construct($em, $class);

        $this->sqlField =  new SqlField();
    }
    
    public function executeProc($sql, $name="") {
        try {
            $proc = $this->_em->getConnection()->prepare($sql);
            $proc->execute();
            $proc->closeCursor();
            if ($name!='') {
                $variables = $this->_em->getConnection()->fetchAll("select ".$name);
                return $variables[0][$name];
            } else {
                return null;
            }
        } catch (DBALException $ex) {
            throw $ex;
        }
    }

    public function executeSelect($sql) {
        try {
            $proc = $this->_em->getConnection()->prepare($sql);
            $proc->execute();
            return $proc->fetchAll();
        } catch (DBALException $ex) {
            throw $ex;
        }
    }


}
