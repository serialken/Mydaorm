<?php 
namespace Ams\EmployeBundle\Repository;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityRepository;

class EmpPopDepotRepository extends EntityRepository
{
    
  /**
   * retourne la societe de l'employe
   * @param type $employe_id
   * return int
   */
    /*
    public function getSocieteByEmployeId($employe_id){
        
        $sql = "SELECT societe_id  FROM emp_pop_depot WHERE employe_id = '".$employe_id."' LIMIT 1 ";
        $result = $this->_em->getConnection()->fetchAssoc($sql);
       
        return  $result['societe_id'];
    }
 */
}