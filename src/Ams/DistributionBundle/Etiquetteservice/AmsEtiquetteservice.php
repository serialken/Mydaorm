<?php

/**
 * Classe fournissant des méthodes liées à la génération d'étiquettes
 * @author madelise
 */

namespace Ams\DistributionBundle\EtiquetteService;

use \Symfony\Component\DependencyInjection\ContainerAware;
use Ams\DistributionBundle\Command;

class AmsEtiquetteservice extends ContainerAware {

    /**
     * Méthode de génération du contenu du fichier à transférer à l'imprimante
     * @param array $aLabels Le tableau contenant les informations sur les étiquettes à imprimer
     * @param string $sCodeTournee Le code de la tournée
     * @param string $sNomPorteur Le nom du porteur
     * @param string $sTemplate le template à utiliser pour les étiquettes
     * @param string $sPart La partie du fichier à imprimer (T|D|M|F) -> Tout, Début, Milieu, Fin
     * @return string $contenuFichier Le contenu du fichier texte contenant les étiquettes
     */
    public function generer($aLabels, $sCodeTournee, $sNomPorteur, $sTemplate = 'modele1', $sPart = 'T'){
        $sTagStr = ''; // La variable contenant le texte
        if (!empty($aLabels)){
            $view =  $this->container->get('twig');
            
            // Récupération du code de tournée si vide
            if (empty($sCodeTournee)){
                $sCodeTournee = !empty($aLabels) ? $aLabels[0]['code_tournee'] : '';
            }
            
            $sTagStr = "\n".$view->render('AmsDistributionBundle:Etiquette:'.$sTemplate.'.txt.twig', 
                    array(
                        'clients' => $aLabels, 
                        'code_tournee' => $sCodeTournee, 
                        'part' => $sPart,
                        'nom_porteur' => $sNomPorteur
                    )
            );
        }
        return $sTagStr;
    }

}
