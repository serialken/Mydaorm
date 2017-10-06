<?php

namespace Ams\SilogBundle\Services;

use Symfony\Component\Config\Definition\Exception\Exception;
use Doctrine\ORM\EntityManager;

class Parametres {

    private $em;
    private $param;

    function  __construct(\Doctrine\ORM\EntityManager $em) {
        $this->em = $em;
        $this->param = array();

        $params = $this->em->getRepository('AmsSilogBundle:Parametre')->findAll();
        foreach ($params as $val) {
            $this->param[$val->getAttr()] = $val->getValeur();
        }
    }

    function defini($attr) {
        if (isset($this->param[$attr])) {
            return true;
        }
        return false;
    }

    function get($attr) {
        if ($this->defini($attr)) {
            return $this->param[$attr];
        }
        return '';
    }

    function get_nom_appli() {
        return (isset($this->param['NOM_APPLI']) ? $this->param['NOM_APPLI'] : 'SILOG');
    }

}
