<?php

namespace Ams\InvenduBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Ams\InvenduBundle\Entity\Invendu;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('AmsInvenduBundle:Default:index.html.twig', array('name' => $name));
    }
}
