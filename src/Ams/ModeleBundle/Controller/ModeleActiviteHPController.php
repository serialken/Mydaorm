<?php

namespace Ams\ModeleBundle\Controller;

use Ams\ModeleBundle\Controller\ModeleActiviteController;

class ModeleActiviteHPController extends ModeleActiviteController {
    protected $est_hors_presse=true;

    public function getRoute() {
        return 'liste_modele_activiteHP';
    }

}
