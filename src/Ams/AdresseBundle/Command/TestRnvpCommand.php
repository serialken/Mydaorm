<?php 
namespace Ams\AdresseBundle\Command;

//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Ams\SilogBundle\Command\GlobalCommand;

/**
 * 
 * "Command" integration des clients a servir 
 * !!!! NE JAMAIS METTRE de "_" dans le nom de classe
 * @author aandrianiaina
 *
 */
class TestRnvpCommand extends GlobalCommand
{
    private $aFichierFluxParam;
    private $sRepTmp;
    private $sRepBkpLocal;
    protected function configure()
    {
    	$this->sNomCommande	= 'test_rnvp';
    	$this->setName($this->sNomCommande);
    	// Pour executer, faire : php app/console clients_a_servir <<fic_code>> Expl : php app/console test_rnvp
        $this
            ->setDescription('Integration des clients a servir')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	parent::execute($input, $output); // Obligatoire pour tout "command". Afin d'initialiser les fichiers de logs
    	$this->oLog->info($this->sNomCommande);
    	$this->oLog->info("Debut Test RNVP");
        
    	
        if(!isset($oRNVP))
        {
            $oRNVP = $this->getContainer()->get('rnvp');
        }

        $aArr = array( 	"volet1" 	=> "SDVP",
                                        "volet2" 	=> "",
                                        "volet3" 	=> "",
                                        "volet4" 	=> "69/73 bd Vict Hugo",
                                        "volet5" 	=> "",
                                        "cp" 		=> "93558",
                                        "ville" 	=> "St Ouen cedex"
                                        );
        $aArr = array( 	"volet1" 	=> "SDVP",
                                        "volet2" 	=> "",
                                        "volet3" 	=> "SARL JUTHO MONTORGUEIL",
                                        "volet4" 	=> "2 RUE MONTMARTRE",
                                        "volet5" 	=> "",
                                        "cp" 		=> "75001",
                                        "ville" 	=> "PARIS 1"
                                        );
        $aArr = array( 	"volet1" 	=> "SDVP",
                                        "volet2" 	=> "",
                                        "volet3" 	=> "",
                                        "volet4" 	=> "7 TER RUE D ASTORG",
                                        "volet5" 	=> "",
                                        "cp" 		=> "75008",
                                        "ville" 	=> "PARIS"
                                        );
        $aArr = array( 	"volet1" 	=> "SDVP",
                                        "volet2" 	=> "",
                                        "volet3" 	=> "",
                                        "volet4" 	=> "66 RUE JEAN JACQUES ROUSSEAU",
                                        "volet5" 	=> "",
                                        "cp" 		=> "94207",
                                        "ville" 	=> "IVRY SUR SEINE CEDEX"
                                        );
        $aArr = array( 	"volet1" 	=> "",
                                        "volet2" 	=> "",
                                        "volet3" 	=> "",
                                        "volet4" 	=> "66 RUE JEAN JACQUES ROUSSEAU",
                                        "volet5" 	=> "",
                                        "cp" 		=> "94207",
                                        "ville" 	=> "IVRY SUR SEINE CEDEX"
                                        );
        $oResRNVP = $oRNVP->normalise($aArr);
        if($oResRNVP!==false && $oResRNVP->Elfyweb_RNVP_ExpertResult == 0)
        {
                echo "\n\nRNVP PRET A ETRE UTILISE\n\n";
                print_r($oResRNVP);
        }
        else
        {
                trigger_error("Webservice non passe pour nom : ".$aArr["volet1"]." - cplt nom : ".$aArr["volet2"]." - cplt adr : ".$aArr["volet3"]." - adr : ".$aArr["volet4"]." - lieu dit : ".$aArr["volet5"]." - cp : ".$aArr["cp"]." - ville : ".$aArr["ville"], E_USER_WARNING);
        }
        
        
    	
    	$this->oLog->info("Fin Test RNVP");
    	
        return;
    }
    
    
    
    
    
}
