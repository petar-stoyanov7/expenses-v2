<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\UserHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractExpenseController
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
        $user = $this->getRequestData($request);
        $response = $this->userHelper->checkCreateUser($user);

        return $this->parseResponse($response);
    }

    /**
     * @Route("/user/edit/{param}", methods={"POST"})
     */
    public function edit(Request $request, $param) : JsonResponse
    {
        $data = $this->getRequestData($request);
        $response = $this->userHelper->checkEditUser($param, $data);

        return $this->parseResponse($response);
    }


    /**
     * @Route("/user/login", methods={"POST"})
     */
    public function login(Request $request) : JsonResponse
    {
        $data = $this->getRequestData($request);
        $response = $this->userHelper->login($data);

        return $this->parseResponse($response);
    }

    /**
     * @Route("/user/get/all", methods={"GET"})
     */
    public function getAll() : JsonResponse
    {
        $response = $this->userRepository->getAllUsers();

        return $this->parseDbResponse($response);
    }

    /**
     * @Route("/user/get/{id}", requirements={"id"="\d+"}, methods={"GET"})
     */
    public function getById(int $id) : JsonResponse
    {
        $response = $this->userRepository->getById($id);

        return $this->parseDbResponse($response);
    }

    /**
     * @Route("/user/get/{username}", methods={"GET"})
     */
    public function getByUsername(string $username) : JsonResponse
    {
        $response = $this->userRepository->getByUsername($username);

        return $this->parseResponse($response);
    }

}