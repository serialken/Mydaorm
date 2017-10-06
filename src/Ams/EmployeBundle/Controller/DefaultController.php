<?php

namespace Ams\EmployeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('AmsEmployeBundle:Default:index.html.twig', array('name' => $name));
    }
}
