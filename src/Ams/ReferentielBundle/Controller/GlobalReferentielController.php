<?php

namespace Ams\ReferentielBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Ams\SilogBundle\Controller\GlobalController;

class GlobalReferentielController extends GlobalController {

    protected function getBundleName() {
        return 'AmsReferentielBundle';
    }

    protected function getTwigListe() {
        return $this->getRepositoryName() . ':liste.html.twig';
    }

    protected function getTwigGrid() {
        return $this->getRepositoryName() . ':grid.xml.twig';
    }

    public function getRepository() {
        $em = $this->getDoctrine()->getManager();
        return $em->getRepository($this->getRepositoryName());
    }

    public function getCombo($curseur, $withBlanck = false) {
        $combo = '<![CDATA[';
        if ($withBlanck) {
            $combo .= '<option value =""></option>';
        }
        foreach ($curseur as $row) {
            $combo .= '<option value ="' . $row['id'] . '">' . $row['libelle'] . '</option>';
        }
        return $combo . ']]>';
    }

    /**
     * Convertit n°/ID de jour de la semaine MRoad en n° de jour PHP (format(w))
     * @param int $iId L'ID du jour dans la table ref_jour
     * @return int $iPhpDow le jour de la semaine façon PHP\date de 0 (pour dimanche) à 6 (pour samedi)
     */
    public static function convertMroadDay2PHPDOW($iId) {
        if ((int) $iId < 8 && (int) $iId >= 0) {
            $iPhpDow = $iId - 1;
            return $iPhpDow;
        }
    }

//    /**
//     * Renvoit la date du premier jour de la semaine J à partir d'une date donnée D
//     * @param int $iDay Le jour de la semaine au format numérique PHP\date de 0 (pour dimanche) à 6 (pour samedi)
//     * @param string $sDate La date au format MySQL Date YYYY-MM-DD
//     * @return string $premiereDate La date du premier jour J à partir de la date D
//     */
//    public static function trouverPremierJourDepuisDate($iDay, $sDate){
//        
//    }
}
