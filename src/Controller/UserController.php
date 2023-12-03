<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/user/add", methods="GET|POST")
     * @param Request $request
     */
    public function create(Request $request)
    {
        $result = [
            'host' => $request->get('host'),
            'asd' => $request->get('asd')
        ];
        return $this->json($result);
    }

    /**
     * @Route("/user/get/all", methods="GET")
     */
    public function getUser()
    {
        return $this->json(['all' => 'users']);
    }
}