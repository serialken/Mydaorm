<?php

namespace Ams\PaieBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ams\PaieBundle\Controller\PaiActiviteController;
class PaiActiviteHPController extends PaiActiviteController {
    protected $est_hors_presse=true;
            
    public function getRepositoryNameHP() {
        return $this->getBundleName() . ':PaiActiviteHP';
    }

    public function getRoute() {
        return 'liste_pai_activiteHP';
    }

    protected function getTwigListe() {
        return $this->getRepositoryNameHP() . ':liste.html.twig';
    }
    protected function getTwigGrid() {
        return $this->getRepositoryNameHP() . ':grid.xml.twig';
    }
    protected function getTwigRows() {
        return $this->getRepositoryNameHP() . ':rows.xml.twig';
    }
}
