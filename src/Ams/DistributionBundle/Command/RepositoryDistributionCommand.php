<?php

namespace Ams\DistributionBundle\Command;

//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ams\SilogBundle\Command\GlobalCommand;

class RepositoryDistributionCommand extends GlobalCommand {

    /** 
     * php app/console commandRepositoryDistribution ClientAServirLogist getDistibutionsDate --args=5_quote,5_no-quote
     * en tache de fond :
     * $sCmd = 'php '.$this->get('kernel')->getRootDir().'/console commandRepositoryDistribution ClientAServirLogist getDistibutionsDate '
             . '--args=5_string,5_int '
             . '--env ' . $this->get('kernel')->getEnvironment();
        $this->bgCommandProxy($sCmd);
     **/
    protected function configure() {
        $this->setName('commandRepositoryDistribution')
                ->setDescription("Mise a jour de toutes les tournées ClientAServirLogist pour aujourd'hui")
                ->addArgument('repository',InputArgument::OPTIONAL,'Choix du repository','')
                ->addArgument('method',InputArgument::OPTIONAL, "methode du repository",'')
                ->addOption('args', NULL, InputOption::VALUE_OPTIONAL, "argument separé par une virgule. l'underscore est suivi de quote ou no-quote",'')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
        $this->oLog->info("Debut de mise a jour de toutes les tournees CASL");
        $repository = $input->getArgument('repository');
        $method = $input->getArgument('method');
        $args = explode(',', $input->getOption('args'));
        $nbArguments = count(explode(',', $input->getOption('args')));
        $query = $this->executeMethod($repository,$method,$args,$nbArguments);
        var_dump($query);

        return;
    }
    
    private function executeMethod($repository,$method,$args,$nbArguments){
        $em = $this->getContainer()->get('doctrine')->getManager();
        switch ($nbArguments){
            case 0 :
                return $em->getRepository('AmsDistributionBundle:'.$repository)->$method();
            case 1 :
                return $em->getRepository('AmsDistributionBundle:'.$repository)->$method($this->getArgumentType($args[0]));
            case 2 :
                return $em->getRepository('AmsDistributionBundle:'.$repository)->$method($this->getArgumentType($args[0]),$this->getArgumentType($args[1]));
            case 3 :
                return $em->getRepository('AmsDistributionBundle:'.$repository)->$method($this->getArgumentType($args[0]),$this->getArgumentType($args[1]),$this->getArgumentType($args[2]));
        }

    }
    
    private function getArgumentType($arg){
        $type = explode('_', $arg);
        switch ($type[1]){
            case 'quote' :
                return '"'.$type[0].'"';
            case 'no-quote' :
                return $type[0];
        }
    }

}
