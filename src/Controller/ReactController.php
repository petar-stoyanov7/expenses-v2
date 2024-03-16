<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

class ReactController extends AbstractExpenseController
{
    /**
     * @Template("index/index.html.twig")
     * @Route("/", name="react-routes-home",)
     */
    public function home() : array
    {
        return [];
    }

    /**
     * @Template("index/index.html.twig")
     * @Route("/new", name="react-routes-new",)
     */
    public function new() : array
    {
        return [];
    }
}