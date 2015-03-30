<?php

namespace Acme\DemoBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DoctrineController extends Controller
{
    /**
     * @Route("/", name="doctrine_index")
     * @Template()
     */
    public function indexAction()
    {
        return [];
    }
}