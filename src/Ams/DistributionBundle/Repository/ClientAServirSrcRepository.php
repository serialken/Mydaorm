<?php 

namespace Ams\DistributionBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\DBALException;
use Ams\DistributionBundle\Exception\ClientsAServirSQLException;

class ClientAServirSrcRepository extends EntityRepository
{
    
    /**
     * Suppression des donnees de date de distribu $dateDistrib et de socCodeExt $socCodeExt.
     * Cette methode est appele quand un fichier d'une societe est a traite de nouveau pour une date donnee
     * 
     * @param \DateTime $dateDistrib
     * @param type $socCodeExt
     * @return type
     */
    public function suppressionAvecDateSoc(\DateTime $dateDistrib, $socCodeExt)
    {
        $qb = $this->createQueryBuilder('c');
        $qb->delete()
                ->where('c.dateDistrib = :dateDistrib')
                ->andWhere('c.socCodeExt = :socCodeExt')
                ->setParameters(array(':dateDistrib' => $dateDistrib, ':socCodeExt' => $socCodeExt));
        return $qb->getQuery()->getResult();
    }
    
    /**
     * Transfert des donnees de la table temporaire client_a_servir_src_tmp1 vers la table client_a_servir_src
     */
    public function tmpVersClientAServirSrc($ficRecapId)
    {
        try {
            // Mettre a NULL la colonne "TMP_ID" de client_a_servir_src
            $update = " UPDATE client_a_servir_src SET TMP_ID = NULL WHERE TMP_ID IS NOT NULL ";
            $this->_em->getConnection()->prepare($update)->execute();

            $sInsert = "INSERT INTO client_a_servir_src "
                        . " (date_distrib, date_parution, num_parution, numabo_ext, vol1, vol2, vol3, vol4, vol5, cp, ville, commune_id, depot_id, type_portage, qte, divers1, info_comp1, info_comp2, divers2, soc_code_ext, prd_code_ext, spr_code_ext, fic_recap_id, abonne_soc_id, client_type, adresse_id, rnvp_id, point_livraison_id, societe_id, produit_id, tmp_id)"
                        . "SELECT "
                        . " date_distrib, date_parution, num_parution, numabo_ext, vol1, vol2, vol3, vol4, vol5, cp, ville, commune_id, depot_id, type_portage, qte, divers1, info_comp1, info_comp2, divers2, soc_code_ext, prd_code_ext, spr_code_ext, fic_recap_id, abonne_soc_id, client_type, adresse_id, rnvp_id, point_livraison_id, societe_id, produit_id, id"
                        . "     FROM client_a_servir_src_tmp1";
            $this->_em->getConnection()->prepare($sInsert)->execute();
        } 
        catch (DBALException $ex) {
            throw ClientsAServirSQLException::transfertSrc($ficRecapId, $ex->getMessage());
        }
    }
    
    public function deleteByFicRecap(\Ams\FichierBundle\Entity\FicRecap $ficRecap)
    {
        $qb = $this->createQueryBuilder('cliServSrc');
        $qb ->delete()
            ->where('cliServSrc.ficRecap = :ficRecap')
            ->setParameter(':ficRecap', $ficRecap);
        
        return $qb->getQuery()->getResult();
    }
    
    public function getByFicRecap(\Ams\FichierBundle\Entity\FicRecap $ficRecap)
    {
        $qb =   $this->createQueryBuilder('cliServSrc')
                ->where('cliServSrc.ficRecap = :ficRecap')
                ->setParameter(':ficRecap', $ficRecap);
        
        return $qb->getQuery()->getResult();
    }
    
}