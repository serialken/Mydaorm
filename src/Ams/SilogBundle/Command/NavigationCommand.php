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

class NavigationCommand extends GlobalCommand
{

    protected function configure()
    {
			$this->setName('navigation_update');
			/** php app/console navigation_update  **/
        $this
            ->setDescription("Mise à jour de la navigation et des routes à partir des fichiers SQL")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
    	$this->oLog->info("Debut traitement");
        
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        $conn=$em->getConnection();
        $rootDir = $container->get('kernel')->getRootDir();
        $sqlDir = $rootDir.$container->getParameter('NAV_DUMP_SQL_FOLDER');
        
        $nbSqlFichier = 0;
        
        $aListeSQL = $container->getParameter('NAV_DUMP_SQL');
        if (!empty($aListeSQL)){
            foreach ($aListeSQL as $sqlFile){
                if(!file_exists($sqlDir.$sqlFile)) {
                    $this->oLog->info("Fichier ".$sqlDir.$sqlFile." non trouvé.");
                }
                else{
                    $dump = file_get_contents($sqlDir.$sqlFile);

                    // Exécution du dump SQL
                    $this->oLog->info("Exécution du fichier ".$sqlDir.$sqlFile." ...");
                    $conn->executeUpdate($dump);
                }
            }
        }
        
       
    	$this->oLog->info("Fin traitement");
			return;
    }
}
