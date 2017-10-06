<?php

namespace Ams\CartoBundle\Command;

//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ijanki\Bundle\FtpBundle\Exception\FtpException;
use Ams\SilogBundle\Lib\StringLocal;
use Ams\SilogBundle\Command\GlobalCommand;
use Ams\CartoBundle\Controller\CartoController;
use Ams\AdresseBundle\Command\TourneeDetailRepairOrderCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class ListeTourneesOptimCommand extends GlobalCommand {

    protected function configure() {
        $this->setName('optim_liste_tournees');
        /** php app/console optim_liste_tournees  * */
        $this
                ->setDescription("Liste les tournées comprises dans une requete d'export pour optimisation")
                ->addArgument('id', InputArgument::REQUIRED, 'Le numero d\'ID de la requete.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
        $time_start = microtime(true);

        $oAlertLogService = $this->getContainer()->get('alertlog');
        /* @var $oAlertLogService \Ams\SilogBundle\Services\Alerts */

        $em = $this->getContainer()->get('doctrine')->getManager();

        $iReqID = $input->getArgument('id'); // ex: 45
        
        // Controle sur l'ID
        if ((int)$iReqID <= 0){
            throw new \Exception('Mauvais ID de requete');
        }
        
        $oReqExport = $em->getRepository('AmsAdresseBundle:RequeteExport')->findOneById($iReqID);
        if (is_null($oReqExport)){
            throw new \Exception('La requete correspondant a cet ID est introuvable.');
        }
        
        $sListeTournees = $oReqExport->getListeTournees();
        if (empty($sListeTournees)){
            throw new \Exception('La liste des tournees de cette requete est vide.');
        }
        
        $aListeTournee =  unserialize(base64_decode($sListeTournees));
        if (empty($aListeTournee)){
            throw new \Exception('La liste des tournees de cette requete n\'a pas pu etre decodee.');
        }
        
        echo count($aListeTournee). ' tournees trouvees:'."\n";
        foreach ($aListeTournee as $sTournee){
            echo $sTournee."\n";
        }
        
        return;
    }

}
