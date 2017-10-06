<?php 
namespace Ams\DistributionBundle\Command;

//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Ijanki\Bundle\FtpBundle\Exception\FtpException;
use Ams\SilogBundle\Lib\StringLocal;

use Ams\SilogBundle\Command\GlobalCommand;

class DeliveryPointOrderConsistencyCommand extends GlobalCommand
{

    protected function configure()
    {
        /** php app/console delivery_point_order --trn 34895 --date 2015-03-26  **/
        $this->setName('delivery_point_order');
        $this
            ->setDescription("attribut un ordre unique au point de livraison dans casl.")
            ->addOption('trn',null, InputOption::VALUE_REQUIRED, 'tournee jour id')
            ->addOption('date',null, InputOption::VALUE_REQUIRED, 'date de distribution dans casl')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $tourneeId = $input->getOption('trn');
        $date      = $input->getOption('date');
        $t = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->orderUniqueDeliveryPoint($tourneeId,$date);
        $this->oLog->info('Fin du traitement');
    }
    
    
    
}
