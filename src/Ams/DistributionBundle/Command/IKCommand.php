<?php
namespace Ams\DistributionBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Ams\SilogBundle\Command\GlobalCommand;

class IKCommand extends GlobalCommand
{
    protected function configure()
    {
        $this ->sNomCommande = 'IK';
        $this ->setName($this->sNomCommande);
        $this
            ->setDescription('Chargement des IKS')
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
        ini_set('memory_limit', '2024M');
        $chargementIK = $this->chargementIK();
//        \\\\10.150.5.15\\Data
        $file = "/var/www/Fic_Batch/Batch/MRoad/IK.txt";
        $fichier_txt = fopen($file, 'w+');
        
        $header = 'code;code_depot;date_distrib;code_tournee;nbkm;nbkm_paye;employe_id;code_soc;libelle;societe_id;code_vehic;libelle_vehic;';
        fwrite($fichier_txt,$header."\r\n");
        foreach($chargementIK as $ligne){
                $aLigne = array();
                
                $aLigne[]   = $ligne['code'];
                $aLigne[]   = $ligne['code_depot'];
                $aLigne[]   = $ligne['date_distrib'];
                $aLigne[]   = $ligne['code_tournee'];
                $aLigne[]   = $ligne['nbkm'];
                $aLigne[]   = $ligne['nbkm_paye'];
                $aLigne[]   = $ligne['employe_id'];
                $aLigne[]   = $ligne['code_soc'];
                $aLigne[]   = $ligne['libelle'];
                $aLigne[]   = $ligne['societe_id'];
                $aLigne[]   = $ligne['code_vehic'];
                $aLigne[]   = $ligne['libelle_vehic'];
                fwrite($fichier_txt, utf8_decode(implode(';', $aLigne))."\r\n");
        }
        fclose($fichier_txt);
        $this->suiviCommand->setHeureFin(new \DateTime(date("Y-m-d H:i:s")));
        $this->endTraitement();
        exit();

        $output->writeln($fichier_txt);
    }
    public function chargementIK()
    {
        $sql  = "SELECT 
                    d.code,
                    SUBSTR(d.code,2,2) code_depot, 
                    p.date_distrib,
                    p.code code_tournee,
                    p.nbkm,
                    p.nbkm_paye,
                    e.employe_id,
                    tt.code code_soc,
                    tt.libelle,
                    tt.societe_id,
                    rt.code code_vehic,
                    rt.libelle libelle_vehic
                FROM
                    pai_tournee p,
                    depot d,
                    emp_pop_depot e,
                    ref_typetournee tt,
                    ref_transport rt 
                WHERE e.employe_id = p.`employe_id` 
                    AND e.`depot_id` = p.`depot_id` 
                    AND d.id = p.`depot_id` 
                    AND e.`depot_id`= d.id 
                    AND p.transport_id = rt.`id`
                    AND e.`flux_id` = p.`flux_id` 
                    AND e.`typetournee_id` = tt.`id` 
                    AND p.`date_distrib` BETWEEN e.`date_debut` 
                    AND e.`date_fin` 
                    AND p.date_distrib BETWEEN '2014-12-21' 
                    AND CURDATE() 
               
                  ";
        $em = $this->getContainer()->get('doctrine')->getManager(); 
        return $em->getConnection()->fetchAll($sql);
    }
}

