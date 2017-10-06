<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Ams\SilogBundle\AmsSilogBundle(),
            new APY\DataGridBundle\APYDataGridBundle(),
            new Ijanki\Bundle\FtpBundle\IjankiFtpBundle(),
            new Ams\AdresseBundle\AmsAdresseBundle(),
            new Ams\FichierBundle\AmsFichierBundle(),
            new Ams\AbonneBundle\AmsAbonneBundle(),
            new Ams\DistributionBundle\AmsDistributionBundle(),
            new Ams\ExtensionBundle\AmsExtensionBundle(),
            new Ams\WebserviceBundle\AmsWebserviceBundle(),
            new Ams\PaieBundle\AmsPaieBundle(),
            new Ams\ModeleBundle\AmsModeleBundle(),
            new Ams\EmployeBundle\AmsEmployeBundle(),
            new Ams\ProduitBundle\AmsProduitBundle(),
            new Ams\ReferentielBundle\AmsReferentielBundle(),
            new Ams\CartoBundle\AmsCartoBundle(),
            new Liuggio\ExcelBundle\LiuggioExcelBundle(),
            new Ams\ReportingBundle\AmsReportingBundle(),
            new Ams\HorspresseBundle\AmsHorspresseBundle(),
            new Ams\InvenduBundle\AmsInvenduBundle(),
            new Ams\ExploitationBundle\AmsExploitationBundle()
        );

        if (in_array($this->getEnvironment(), array('dev', 'test','debug','local', 'preprod'))) {
            //$bundles[] = new Acme\DemoBundle\AcmeDemoBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
//            $bundles[] = new Ams\ExploitationBundle\AmsExploitationBundle();
            $bundles[] = new FirePHPBundle\FirePHPBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
