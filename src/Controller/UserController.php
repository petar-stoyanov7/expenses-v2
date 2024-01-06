<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\UserHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    private UserHelper $userHelper;

    private UserRepository $userRepository;
    public function __construct(UserHelper $userHelper, UserRepository $userRepository)
    {
        $this->userHelper = $userHelper;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/user/add", methods={"POST"})
     */
    public function add(Request $request) : JsonResponse
    {
        $user = $request->request->all();
        $response = $this->userHelper->checkCreateUser($user);

        return $this->json(
            $response,
            !empty($response['success']) ? 200 : 400
        );
    }

    /**
     * @Route("/user/edit/{param}", methods={"POST"})
     */
    public function edit(Request $request, $param) : JsonResponse
    {
        $data = $request->request->all();
        $response = $this->userHelper->checkEditUser($param, $data);

        return $this->json(
            $response,
            !empty($response['success']) ? 200 : 400
        );
    }


    /**
     * @Route("/user/login", methods={"POST"})
     */
    public function login(Request $request) : JsonResponse
    {
        $data = $request->request->all();

        $response = $this->userHelper->login($data);

        return $this->json(
            $response,
            !empty($response['success']) ? 200 : 400
        );
    }

    /**
     * @Route("/user/get/all", methods={"GET"})
     */
    public function getAll() : JsonResponse
    {
        $response = $this->userRepository->getAllUsers();

        return $this->json(
            $response,
            !empty($response['success']) ? 200 : 400
        );
    }

    /**
     * @Route("/user/get/{id}", requirements={"id"="\d+"}, methods={"GET"})
     */
    public function getById(int $id) : JsonResponse
    {
        $response = $this->userRepository->getById($id);

        return $this->json(
            $response,
            !empty($response) ? 200 : 400
        );
    }

    /**
     * @Route("/user/get/{username}", methods={"GET"})
     */
    public function getByUsername(string $username) : JsonResponse
    {
        $response = $this->userRepository->getByUsername($username);

        return $this->json(
            $response,
            !empty($response) ? 200 : 400
        );
    }
}