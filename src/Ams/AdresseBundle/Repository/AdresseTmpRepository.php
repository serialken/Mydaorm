<?php

namespace Ams\AdresseBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\DBALException;

class AdresseTmpRepository extends EntityRepository {
    
    /**
     * Truncate table adresse_tmp
     */
    public function truncate() {
        try {
            $this->_em->getConnection()
                    ->executeQuery("TRUNCATE TABLE adresse_tmp");
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    
    /**
     * Verifie si la table adresse_tmp est vide
     * @return boolean 
     */
    public function estVide() {
        try {
            $nb = $this->createQueryBuilder('a')
                        ->select('COUNT(a)')
                        ->getQuery()
                        ->getSingleScalarResult();
            return (($nb==0) ? true : false);
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Recupere les adresses deja normalisees
     * $flux prend la valeur 'CAS' ou 'REPER'
     * 
     * @param string $flux
     * @throws \Doctrine\DBAL\DBALException
     */
    public function init($flux='CAS')
    {
        try {
            //if($this->estVide()===true)
            //{
                $repoAdresse = $this->_em->getRepository('AmsAdresseBundle:Adresse');
                $repoAdresse->tmpAdresse($flux);
            //}
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }        
    }
}
