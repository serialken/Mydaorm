<?php 
namespace Ams\SilogBundle\Command;

//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Ijanki\Bundle\FtpBundle\Exception\FtpException;
use Ams\SilogBundle\Lib\StringLocal;

use Ams\SilogBundle\Command\GlobalCommand;

class InitdbCommand extends GlobalCommand
{

    protected function configure()
    {
	$this->setName('init_db');
        $this
            ->setDescription("Initialisation de la base de donnes d'MRoad")
            ->addOption('run',NULL, InputOption::VALUE_OPTIONAL, "L'action que vous souhaitez lancer: paie | mroad",'action_par_defaut')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
    	$this->oLog->info("Debut traitement");
        
        $sAction= $input->getOption('run');	// Expl : prerequis_paie
        
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        $conn=$em->getConnection();
        $rootDir = $container->get('kernel')->getRootDir();
        
        switch ($sAction){
            case 'paie':
                $sqlDir = $rootDir.$container->getParameter('INIT_PAIE_SQL_FOLDER');        
                $aListeSQL = $container->getParameter('INIT_PAIE_SQL');
                break;
            case 'mroad':
                $sqlDir = $rootDir.$container->getParameter('MROAD_CORE_SQL_FOLDER');        
                $aListeSQL = $container->getParameter('MROAD_CORE_SQL');
                break;
            default:
                $this->oLog->info("L'action ".$sAction." n'est pas reconnue. Fin d'exécution.");
                exit();
                break;
        }
        
        if (!empty($aListeSQL)){
            foreach ($aListeSQL as $sqlFile){
                if(!file_exists($sqlDir.$sqlFile)) {
                    $this->oLog->info("Fichier ".$sqlDir.$sqlFile." non trouvé.");
                }
                else{
                    $dump = file_get_contents($sqlDir.$sqlFile);
                    
                    // Exécution du dump SQL
                    $this->oLog->info("Exécution du fichier ".$sqlDir.$sqlFile." ...");
                    $conn->query($this->remove_utf8_bom($dump));
                }
            }
        }
        
       
    	$this->oLog->info("Fin traitement");
			return;
    }
    
    function remove_utf8_bom($text)
    {
        $bom = pack('H*','EFBBBF');
        $text = preg_replace("/^$bom/", '', $text);
        return $text;
    }
}
