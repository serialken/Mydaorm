<?php
namespace Ams\PaieBundle\Repository;

use Doctrine\DBAL\DBALException;
use Ams\SilogBundle\Repository\GlobalRepository;

class PaiPrdTourneeRepository extends GlobalRepository {

    public function insert($user, $tournee_id, $produit_id, $natureclient_id, $qte, $nbcli, $nbrep=0) {
        $sql = "INSERT INTO pai_prd_tournee(tournee_id,produit_id,natureclient_id,qte,nbcli,nbrep,utilisateur_id,date_creation)
                VALUES(?,?,?,?,?,?,?,NOW())";
        return $this->_em->getConnection()->executeUpdate($sql,array($tournee_id,$produit_id,$natureclient_id,$qte,$nbcli,$nbrep,$user));
    }
    public function update($user, $tournee_id, $produit_id, $natureclient_id, $qte, $nbcli) {
        $sql = "UPDATE pai_prd_tournee SET 
                qte = ?,
                nbcli = ?,
                utilisateur_id = ?,
                date_modif = NOW()
                WHERE tournee_id = ?
                AND produit_id= ?
                AND natureclient_id = ?"; // Abonné
        return $this->_em->getConnection()->executeUpdate($sql,array($qte,$nbcli,$user,$tournee_id,$produit_id,$natureclient_id));
    }
    public function update_rep($user, $tournee_id, $produit_id, $natureclient_id, $nbrep) {
        $sql = "UPDATE pai_prd_tournee SET 
                nbrep = ?,
                utilisateur_id = ?,
                date_modif = NOW()
                WHERE tournee_id = ?
                AND produit_id= ?
                AND natureclient_id = ?"; // Abonné
        return $this->_em->getConnection()->executeUpdate($sql,array($nbrep,$user,$tournee_id,$produit_id,$natureclient_id));
    }
    public function delete($tournee_id, $produit_id, $natureclient_id) {
        $sql = "DELETE from pai_prd_tournee
                WHERE tournee_id =?
                AND produit_id=?
                AND natureclient_id = ?"; // Abonné
        return $this->_em->getConnection()->executeUpdate($sql,array($tournee_id,$produit_id,$natureclient_id));
    }
    public function nettoyage($tournee_id, $produit_id, $natureclient_id) {
        $sql = "DELETE from pai_prd_tournee
                WHERE tournee_id =?
                AND produit_id=?
                AND natureclient_id = ?
                AND qte=0
                AND nbcli=0
                AND nbrep=0";
        return $this->_em->getConnection()->executeUpdate($sql,array($tournee_id,$produit_id,$natureclient_id));
    }

    public function ajouter(&$msg, &$msgException, $user, $depot_id, $flux_id, $date_distrib, $produit_id, $natureclient_id) {
        try {
            $this->_em->getConnection()->executeQuery("
            insert into pai_prd_tournee(
                utilisateur_id,date_creation
                ,tournee_id,produit_id,natureclient_id
                ,qte,nbcli,nbcli_unique,nbadr
              )
            select 
                ".$user."
                ,sysdate()
                ,pt.id
                ,".$produit_id."
                ,".$natureclient_id."
                ,0
                ,0
                ,0
                ,0
            from pai_tournee pt
            where pt.depot_id=".$depot_id." and pt.flux_id=".$flux_id." and pt.date_distrib='".$date_distrib."'
            limit 1
            ;");            
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "","","");
        }
        return true;
    }

    public function supprimer(&$msg, &$msgException, $user, $depot_id, $flux_id, $date_distrib, $produit_id, $natureclient_id) {
        try {
            $this->_em->getConnection()->executeQuery("
            delete ppt
            from pai_prd_tournee ppt
            inner join pai_tournee pt on ppt.tournee_id=pt.id
            where pt.depot_id=".$depot_id." and pt.flux_id=".$flux_id." and pt.date_distrib='".$date_distrib."'
            and ppt.produit_id=".$produit_id." and ppt.natureclient_id=".$natureclient_id."
            ;");            
            // recalcul des totaux dans tournée
            $this->recalcul_date_distrib($date_distrib, $depot_id, $flux_id);
       }
        catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "","","");
        }        
        return true;
    }

    public function dupliquer(&$msg, &$msgException, $user, $depot_id, $flux_id, $date_distrib, $produit_id_org, $produit_id_dst, $natureclient_id) {
        try {
                // pour la quantite
                // pour SDVP on prend le nombre de clients
                // pour Neo/Media on prend le nombre d'exemplaires
            $this->_em->getConnection()->executeQuery("
            insert into pai_prd_tournee(
                utilisateur_id,date_creation
                ,tournee_id,produit_id,natureclient_id
                ,qte,nbcli,nbcli_unique,nbadr
              )
            select 
                ".$user."
                ,sysdate()
                ,pt.id
                ,".$produit_id_dst."
                ,".$natureclient_id."
 --               ,case when pt.typetournee_id=1 then ppt.nbcli else ppt.qte end
                ,sum(ppt.nbcli)
                ,0
                ,0
                ,0
            from pai_tournee pt
            inner join pai_prd_tournee ppt on ppt.tournee_id=pt.id
            where pt.depot_id=".$depot_id." and pt.flux_id=".$flux_id." and pt.date_distrib='".$date_distrib."'
            and ppt.produit_id in (".$produit_id_org.") and ppt.natureclient_id=".$natureclient_id."
            group by pt.id
            ;");            
            $id = $this->_em->getConnection()->lastInsertId();
            $this->validate_date_distrib($msg, $msgException, $depot_id, $flux_id, $date_distrib);
            // recalcul des totaux dans tournée
            $this->recalcul_date_distrib($date_distrib, $depot_id, $flux_id);
       }
        catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "","","");
        }        
        return true;
    }

    public function validate(&$msg, &$msgException, $id, $action="", $param=null) {
        try {
            $sql = "call pai_valide_produit(@validation_id,null,null,null,null," . $this->sqlField->sqlId($id) . ")";
            $validation_id = $this->executeProc($sql, "@validation_id");
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "Erreur inconnue.", "", "");
       }
       return $validation_id;
     }

    public function validate_date_distrib(&$msg, &$msgException, $depot_id, $flux_id, $date_distrib) {
        try {
            $sql = "call pai_valide_produit(@validation_id," . $this->sqlField->sqlId($depot_id) . "," . $this->sqlField->sqlId($flux_id) . "," . $this->sqlField->sqlTrimQuote($date_distrib) . ",null,null)";
            $validation_id = $this->executeProc($sql, "@validation_id");
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "Erreur inconnue.", "", "");
       }
       return $validation_id;
     }

    public function validate_tournee(&$msg, &$msgException, $tournee_id) {
        try {
            $sql = "call pai_valide_produit(@validation_id,null,null,null," . $this->sqlField->sqlId($tournee_id) . ",null)";
            $validation_id = $this->executeProc($sql, "@validation_id");
        } catch (DBALException $ex) {
            return $this->sqlField->sqlError($msg, $msgException, $ex, "Erreur inconnue.", "", "");
       }
       return $validation_id;
     }

    public function selectComboAjouterTitre($depot_id, $flux_id, $date_distrib, $natureclient_id) {
        $sql="select distinct
                p.id
            ,   p.libelle
            from produit p
            where p.flux_id=".$flux_id."
            and p.type_id in (1,2)
            and '".$date_distrib."' between p.date_debut and coalesce(p.date_fin,'2999-01-01')
            and not exists(select null
                        from pai_tournee pt
                        inner join pai_prd_tournee ppt on ppt.tournee_id=pt.id
                        where pt.depot_id=".$depot_id." and pt.flux_id=".$flux_id." and pt.date_distrib='".$date_distrib."'
                        and p.id=ppt.produit_id
                        and ppt.natureclient_id=".$natureclient_id."
                        )
            order by p.libelle
        ;";            
        return $this->_em->getConnection()->fetchAll ($sql);
    }

    public function selectComboAjouterProduit($depot_id, $flux_id, $date_distrib, $natureclient_id) {
        $sql="select distinct
                p.id
            ,   p.libelle
            from produit p
            INNER JOIN produit_type pt ON p.type_id=pt.id AND pt.hors_presse
            where p.flux_id=".$flux_id."
            and '".$date_distrib."' between p.date_debut and coalesce(p.date_fin,'2999-01-01')
            and not exists(select null
                        from pai_tournee pt
                        inner join pai_prd_tournee ppt on ppt.tournee_id=pt.id
                        where pt.depot_id=".$depot_id." and pt.flux_id=".$flux_id." and pt.date_distrib='".$date_distrib."'
                        and p.id=ppt.produit_id
                        and ppt.natureclient_id=".$natureclient_id."
                        )
            order by p.libelle
        ;";            
        return $this->_em->getConnection()->fetchAll ($sql);
    }


    public function selectComboAjouterPresse($depot_id, $flux_id, $date_distrib, $natureclient_id) {
        $sql="select distinct
                p.id
            ,   p.libelle
            from produit p
            INNER JOIN produit_type pt ON p.type_id=pt.id AND pt.id not in (1,2,3) and not pt.hors_presse
            where p.flux_id=".$flux_id."
            and '".$date_distrib."' between p.date_debut and coalesce(p.date_fin,'2999-01-01')
            and not exists(select null
                        from pai_tournee pt
                        inner join pai_prd_tournee ppt on ppt.tournee_id=pt.id
                        where pt.depot_id=".$depot_id." and pt.flux_id=".$flux_id." and pt.date_distrib='".$date_distrib."'
                        and p.id=ppt.produit_id
                        and ppt.natureclient_id=".$natureclient_id."
                        )
            order by p.libelle
        ;";            
        return $this->_em->getConnection()->fetchAll ($sql);
    }

    public function selectComboSupprimerTitre($depot_id, $flux_id, $date_distrib, $natureclient_id) {
        $sql="select distinct
                p.id
            ,   p.libelle
            from pai_tournee pt
            inner join pai_prd_tournee ppt on ppt.tournee_id=pt.id
            inner join produit p on ppt.produit_id=p.id and p.type_id in (1,2,3)
            where pt.depot_id=".$depot_id." and pt.flux_id=".$flux_id." and pt.date_distrib='".$date_distrib."'
            and ppt.natureclient_id=".$natureclient_id."
            order by p.libelle
        ;";            
        return $this->_em->getConnection()->fetchAll ($sql);
    }

    public function selectComboSupprimerProduit($depot_id, $flux_id, $date_distrib, $natureclient_id) {
        $sql="select distinct
                p.id
            ,   p.libelle
            from pai_tournee pt
            inner join pai_prd_tournee ppt on ppt.tournee_id=pt.id
            inner join produit p on ppt.produit_id=p.id
            INNER JOIN produit_type t ON p.type_id=t.id AND t.hors_presse
            where pt.depot_id=".$depot_id." and pt.flux_id=".$flux_id." and pt.date_distrib='".$date_distrib."'
            and ppt.natureclient_id=".$natureclient_id."
            order by p.libelle
        ;";            
        return $this->_em->getConnection()->fetchAll ($sql);
    }


    public function selectComboSupprimerPresse($depot_id, $flux_id, $date_distrib, $natureclient_id) {
        $sql="select distinct
                p.id
            ,   p.libelle
            from pai_tournee pt
            inner join pai_prd_tournee ppt on ppt.tournee_id=pt.id
            inner join produit p on ppt.produit_id=p.id
            INNER JOIN produit_type t ON p.type_id=t.id and t.id not in (1,2,3) AND not t.hors_presse
            where pt.depot_id=".$depot_id." and pt.flux_id=".$flux_id." and pt.date_distrib='".$date_distrib."'
            and ppt.natureclient_id=".$natureclient_id."
            order by p.libelle
        ;";            
        return $this->_em->getConnection()->fetchAll ($sql);
    }

    public function selectComboDuplicationOrg($depot_id, $flux_id, $date_distrib, $natureclient_id, $produit_dst_id) {
        $sql="select distinct
                p.id
            ,   p.libelle
            from pai_tournee pt
            inner join pai_prd_tournee ppt on ppt.tournee_id=pt.id
            inner join produit p on ppt.produit_id=p.id
            inner join produit pdst on p.societe_id=pdst.societe_id
            where pt.depot_id=".$depot_id." and pt.flux_id=".$flux_id." and pt.date_distrib='".$date_distrib."'
            and p.type_id = 1
            and pdst.id=".$produit_dst_id."
            and ppt.natureclient_id=".$natureclient_id."
            and exists( select null
                        from produit p2 
                        where p.societe_id=p2.societe_id
                        and p2.flux_id=".$flux_id."  AND p2.type_id = 3 
                        and '".$date_distrib."' between p2.date_debut and coalesce(p2.date_fin,'2999-01-01')
                        and not exists(select null
                                        from pai_tournee pt
                                        inner join pai_prd_tournee ppt on ppt.tournee_id=pt.id
                                        where pt.depot_id=".$depot_id." and pt.flux_id=".$flux_id." and pt.date_distrib='".$date_distrib."'
                                        and p2.id=ppt.produit_id
                                        and ppt.natureclient_id=".$natureclient_id."
                                        )                        
                        )
            order by p.libelle
        ;";            
        return $this->_em->getConnection()->fetchAll ($sql);
    }

    public function selectComboDuplicationDst($depot_id, $flux_id, $date_distrib,$natureclient_id/*, $produit_org_id */) {
        if (!isset($produit_org_id) || $produit_org_id=='') {
            $produit_org_id=0;
        }
        $sql="select distinct
                p.id,
                p.libelle
            from produit p 
--            LEFT JOIN produit p2 USING(societe_id)
            where p.flux_id=".$flux_id."
--            and p2.id = ".$produit_org_id."
            and p.type_id in (3)
            and '".$date_distrib."' between p.date_debut and coalesce(p.date_fin,'2999-01-01')
            -- Le produit est un supplément lié à un titre existant
            and exists(select null
                        from pai_tournee pt
                        inner join pai_prd_tournee ppt on ppt.tournee_id=pt.id
                        inner join produit p2 on ppt.produit_id=p2.id
                        where pt.depot_id=".$depot_id." and pt.flux_id=".$flux_id." and pt.date_distrib='".$date_distrib."'
                        and p2.type_id = 1
                        and ppt.natureclient_id=".$natureclient_id."
                        and p.societe_id=p2.societe_id
                            )
            -- Le produit n'existe pas encore
            and not exists(select null
                        from pai_tournee pt
                        inner join pai_prd_tournee ppt on ppt.tournee_id=pt.id
                        where pt.depot_id=".$depot_id." and pt.flux_id=".$flux_id." and pt.date_distrib='".$date_distrib."'
                        and p.id=ppt.produit_id
                        and ppt.natureclient_id=".$natureclient_id."
                        )
            order by p.libelle
            ;";            
        return $this->_em->getConnection()->fetchAll ($sql);
    }

    public function recalcul_date_distrib($date_distrib,$depot_id=0,$flux_id=0) {
        try {
            $sql = "call recalcul_produit_date_distrib(".$this->sqlField->sqlTrimQuote($date_distrib).",".$this->sqlField->sqlIdOrNull($depot_id).",".$this->sqlField->sqlIdOrNull($flux_id).")";
            $this ->executeProc($sql);
            }
        catch (DBALException $ex) {
            throw $ex;
        }
    }
    public function recalcul_poids_groupe($date_distrib,$groupe_id, $produit_id) {
        try {
            $sql = "call recalcul_produit_poids_groupe(".$this->sqlField->sqlTrimQuote($date_distrib).",".$this->sqlField->sqlIdOrNull($groupe_id).",".$this->sqlField->sqlIdOrNull($produit_id).")";
            $this ->executeProc($sql);
            }
        catch (DBALException $ex) {
            throw $ex;
        }
    }
    public function recalcul_poids_PCO($date_distrib, $produit_id) {
        try {
            $sql = "call recalcul_produit_poids_PCO(".$this->sqlField->sqlTrimQuote($date_distrib).",".$this->sqlField->sqlIdOrNull($produit_id).")";
            $this ->executeProc($sql);
            }
        catch (DBALException $ex) {
            throw $ex;
        }
    }
    public function recalcul_tournee($tournee_id) {
        try {
            $sql = "call recalcul_produit_tournee_id(".$this->sqlField->sqlIdOrNull($tournee_id).")";
            $this ->executeProc($sql);
            }
        catch (DBALException $ex) {
            throw $ex;
        }
    }
    public function recalcul_tournee_produit_nature($tournee_id, $produit_id, $natureclient_id) {
        try {
            $sql = "call recalcul_produit_nature_id(".$this->sqlField->sqlIdOrNull($tournee_id).",".$this->sqlField->sqlIdOrNull($produit_id).",".$this->sqlField->sqlIdOrNull($natureclient_id).")";
            $this ->executeProc($sql);
            }
        catch (DBALException $ex) {
            throw $ex;
        }
    }
    public function recalcul_id($tournee_id, $id) {
        try {
            $sql = "call recalcul_produit_id(".$this->sqlField->sqlIdOrNull($tournee_id).",".$this->sqlField->sqlIdOrNull($id).")";
            $this ->executeProc($sql);
            }
        catch (DBALException $ex) {
            throw $ex;
        }
    }
}
