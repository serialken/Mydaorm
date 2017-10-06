<?php 
namespace Ams\DistributionBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ams\SilogBundle\Command\GlobalCommand;
use Doctrine\DBAL\DBALException;
use Ams\DistributionBundle\Entity\FeuillePortage;
use Ams\ProduitBundle\Repository\ProduitAdditionnelRepository;

/**
 * 
 * @author kevin jean-baptiste
 *
 */
class DistributionProduitAdditionnelCommand extends GlobalCommand
{
    
    protected function configure()
    {
    	$this->sNomCommande	= 'distrib_prod_additionnel';
        $prodRef = false;
        $prodAdd = false;
        
    	$this->setName($this->sNomCommande);
        // php app/console distrib_prod_additionnel --id_sh=cron_test --id_ai=1 --env prod
        $this
            ->setDescription('Insertion dans casl des produits additionnels')
            ->addOption('id_sh',null, InputOption::VALUE_REQUIRED, 'Libelle du CRON')
            ->addOption('id_ai',null, InputOption::VALUE_REQUIRED, 'Id du CRON')
//            ->addArgument('produit_reference', InputArgument::OPTIONAL, 'Jour a traiter Min. Format : J<+Numerique> (Expl : J+1 ou J+2 ou J ...)', $prodRef)
//            ->addArgument('produit_additionnel', InputArgument::OPTIONAL, 'Jour a traiter Max. Format : J<+Numerique> (Expl : J+1 ou J+2 ou J ...)', $prodAdd)
//            ->addArgument('date', InputArgument::OPTIONAL, 'Y-m-d', false)
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
        
        $this->oLog->info(date("d/m/Y H:i:s : ")."Debut Insertion dans CASL des produits additionnels - Commande : ".$this->sNomCommande);
        /** INITIALISATION DE VARIABLE **/
        $em = $this->getContainer()->get('doctrine')->getManager();
        $dateDay = $this->dateDay(date('Y-m-d',strtotime("+1 day")));  
        
        /** VERIF PRODUIT A DISTRIBUER **/
        $produitAdd = $em->getRepository('AmsProduitBundle:ProduitAdditionnel')->findBydateDistrib($dateDay);
        if(!empty($produitAdd)){
            /**  IL Y A BIEN DES PRODUITS ADDITIONNELS POUR CE JOUR, ON MET A JOUR CLIENT A SERVIR LOGIST **/
            foreach($produitAdd as $produit){
                $produitRef = $produit->getProduitReference()->getId();
                $produitAdd = $produit->getProduitAdditionnel()->getId();
                $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->deleteByDateProduct($dateDay->format('Y-m-d'),$produitAdd);
                $em->getRepository('AmsDistributionBundle:ClientAServirAdditionnel')->insertProduitAdditionnel($dateDay->format('Y-m-d'),$produitRef,$produitAdd);
            }
        }else{
            echo 'pas de produit additionnel prevu a ce jour';
        }
        $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
        $this->endTraitement();
        $this->oLog->info(date("d/m/Y H:i:s : ")."Fin Insertion dans CASL des produits additionnels - Commande : ".$this->sNomCommande);
    }
    
    private function getOptions($input,&$aOptions){
        $aOptions['produit_ref'] = ($input->getArgument('produit_reference')) ? $input->getArgument('produit_reference') : '';
        $aOptions['produit_add'] = ($input->getArgument('produit_additionnel')) ? $input->getArgument('produit_additionnel') : '';
        echo "Les options :\n";print_r($aOptions);echo "\n";
    }
    
    private function dateDay($date){
        list($years,$month,$day)= explode('-',$date);
        $dateDay = new \DateTime;
        return $dateDay->setDate($years,$month,$day)
                       ->setTime(0,0,0);
    }
}
