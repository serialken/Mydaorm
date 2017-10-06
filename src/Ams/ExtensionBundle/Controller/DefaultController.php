<?php

namespace Ams\ExtensionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('AmsExtensionBundle:Default:index.html.twig', array('name' => $name));
    }
}
