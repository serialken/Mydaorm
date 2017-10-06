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

class AbonneChangeTourneeCommand extends GlobalCommand
{

    protected function configure()
    {
        /** php app/console abo_change_trn  --caslId=3890185 --trn=042NXK035LU --jourId=2 --numAbo=26306 --env local  **/
        $this->setName('abo_change_trn');
        $this
            ->setDescription("Permute un point d'une tournée vers une autre en mettant à jour les tables casl et tournee_detail.")
            ->addOption('trn',null, InputOption::VALUE_REQUIRED, 'tournee destination')
            ->addOption('caslId',null, InputOption::VALUE_REQUIRED, 'id casl ')
            ->addOption('tdId',null, InputOption::VALUE_REQUIRED, 'tournee detail id ')
            ->addOption('jourId',null, InputOption::VALUE_REQUIRED, 'Jour Id ')
            ->addOption('numAbo',null, InputOption::VALUE_REQUIRED, 'numAboId ')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
        /** ON RECUPERE TOURNEE DETAIL ID**/
        if($input->getOption('caslId') != ''){
            $em = $this->getContainer()->get('doctrine.orm.entity_manager');
            $param = array('jour' => $input->getOption('jourId') , 'numAbonneId' => $input->getOption('numAbo'));
            $oTourneeDetail = $em->getRepository('AmsAdresseBundle:TourneeDetail')->findOneBy($param);
        }
        else{
            $oTourneeDetail = $em->getRepository('AmsAdresseBundle:TourneeDetail')->find($input->getOption('tdId'));
        }

        if($oTourneeDetail){
            $aAdress = $em->getRepository('AmsAdresseBundle:Adresse')->getAdressByAboSoc($oTourneeDetail->getNumAbonneId());
            $aRnvp = $em->getRepository('AmsAdresseBundle:AdresseRnvp')->find($aAdress[0]['point_livraison_id']);
            $point = array();
            $point[] = array(
                'geox' => $aRnvp->getGeox(),
                'geoy' => $aRnvp->getGeoy(),
                'abonne_soc_id' => $oTourneeDetail->getNumAbonneId(),
                'tournee_jour_code' => $input->getOption('trn'),
                'point_livraison_id' => ($oTourneeDetail->getPointLivraison()) ? $oTourneeDetail->getPointLivraison()->getId() : $oTourneeDetail->getPointLivraison(),
                'debut_plage_horaire' => ($oTourneeDetail->getDebutPlageHoraire())?$oTourneeDetail->getDebutPlageHoraire()->format('H:i:s') :$oTourneeDetail->getDebutPlageHoraire(),
                'fin_plage_horaire' => ($oTourneeDetail->getFinPlageHoraire())?$oTourneeDetail->getFinPlageHoraire()->format('H:i:s') :$oTourneeDetail->getFinPlageHoraire(),
                'duree_viste_fixe' => $oTourneeDetail->getDureeVisteFixe()->format('H:i:s'),
                'numabo_ext' => $oTourneeDetail->getNumAbonneSoc(),
                'num_abonne_id' => $oTourneeDetail->getNumAbonneId(),
                'soc_code_ext' => $oTourneeDetail->getSoc(),
                'prd_code_ext' => $oTourneeDetail->getTitre(),
                'insee' => $oTourneeDetail->getInsee(),
                'flux_id' => $oTourneeDetail->getFlux(),
                'id_jour' => $oTourneeDetail->getJour()->getId(),
                'source_modification' => 'Changement de tournee abo (manuel)',
            );
            $geoservice = $this->getContainer()->get('ams_carto.geoservice');
            /** MISE A JOUR TOURNEE DETAIL**/
            $geoservice->classementAuto($point);
            /** MISE A JOUR CASL **/
            $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->miseAJourChangeTournee($input->getOption('jourId'),$input->getOption('numAbo'));
        }

        return;
    }



}
