<?php 
namespace Ams\DistributionBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ams\SilogBundle\Command\GlobalCommand;
use Doctrine\DBAL\DBALException;

/**
 * 
 * Divers Nettoyages de donnees
 * 
 * Exemple de commande : php app/console nettoyage_donnees --env=prod
 * 
 * @author aandrianiaina
 *
 */
class NettoyageDonneesCommand extends GlobalCommand
{
    protected function configure()
    {
    	$this->sNomCommande	= 'nettoyage_donnees';
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console nettoyage_donnees --env=prod Expl : php app/console nettoyage_donnees --env=prod
        $this
            ->setDescription('Nettoyage diverses')
            ->addOption('id_sh',null, InputOption::VALUE_REQUIRED, 'Libelle du CRON')
            ->addOption('id_ai',null, InputOption::VALUE_REQUIRED, 'Id du CRON')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
    	if($input->getOption('id_sh')){
            $idSh = $input->getOption('id_sh');
        }
        if($input->getOption('id_ai')){
            $idAi = $input->getOption('id_ai');
        }
        if($input->getOption('id_ai') && $input->getOption('id_sh')){
            $this->associateToCron($idAi,$idSh);
        }
        $this->oLog->info(date("d/m/Y H:i:s : ").'Debut Nettoyage diverses - Commande : '.$this->sNomCommande);    
        
        $em    = $this->getContainer()->get('doctrine')->getManager();
        
        // Suppression des produits SDVP pour les depots autres que CD 28 NANTERRE, CD29 ARCUEIL, CD40 AUBERVILLIERS, CD41 BONDY & CD42 BERCY
        $this->oLog->info('Debut Suppression des produits SDVP pour les depots autres que CD 28 NANTERRE, CD29 ARCUEIL, CD40 AUBERVILLIERS, CD41 BONDY & CD42 BERCY');
        $dateDuJour = date("Y-m-d");
        $delete = " DELETE
                    FROM
                             client_a_servir_logist
                    WHERE
                            date_distrib>='".$dateDuJour."'
                            AND depot_id NOT IN (SELECT id FROM depot WHERE libelle LIKE '%NANTERRE%' 
                                                                        OR libelle LIKE '%ARCUEIL%' 
                                                                        OR libelle LIKE '%AUBERVILLIERS%' 
                                                                        OR libelle LIKE '%BONDY%' 
                                                                        OR libelle LIKE '%BERCY%' 
                                                                        ) 
                            AND produit_id NOT IN (
                                                                    SELECT
                                                                            p.id
                                                                    FROM
                                                                            produit p
                                                                            LEFT JOIN societe s ON p.societe_id=s.id
                                                                    WHERE
                                                                            s.libelle LIKE '%(v)%'
                                                                            )
                        ";
        $em->getConnection()->executeQuery($delete);
        $em->clear();
        $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
        $this->endTraitement();
    	$this->oLog->info(date("d/m/Y H:i:s : ").'Fin Nettoyage diverses - Commande : '.$this->sNomCommande);
        return;
    }
}
