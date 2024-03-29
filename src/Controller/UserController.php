<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\UserHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user", name="user")
 */
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
     * @Route("/add", methods={"POST"})
     */
    public function add(Request $request) : JsonResponse
    {
        $user = $this->getRequestData($request);
        $response = $this->userHelper->checkCreateUser($user);

        return $this->parseResponse($response);
    }

    /**
     * @Route("/edit/{param}", methods={"POST"})
     */
    public function edit(Request $request, $param) : JsonResponse
    {
        $data = $this->getRequestData($request);
        $response = $this->userHelper->checkEditUser($param, $data);

        return $this->parseResponse($response);
    }

    /**
     * @Route("/delete/{param}", methods={"POST","GET"})
     */
    public function delete($param) : JsonResponse
    {
        $response = $this->userHelper->checkDeleteUser($param);

        return $this->parseResponse($response);
    }


    /**
     * @Route("/login", methods={"POST"})
     */
    public function login(Request $request) : JsonResponse
    {
        $data = $this->getRequestData($request);
        $response = $this->userHelper->login($data);

        return $this->parseResponse($response);
    }

    /**
     * @Route("/get/all", methods={"GET"})
     */
    public function getAll() : JsonResponse
    {
        $response = $this->userHelper->getAll();

        return $this->parseResponse($response);
    }

    /**
     * @Route("/get/all/detail", methods={"GET"})
     */
    public function getAllDetail() : JsonResponse
    {
        $response = $this->userHelper->getAllDetail();

        return $this->parseResponse($response);
    }

    /**
     * @Route("/get/{param}", methods={"GET"})
     */
    public function get($param) : JsonResponse
    {
        $response = $this->userHelper->getUserDetails($param);

        return $this->parseResponse($response);
    }

    /**
     * @Route("/get/{param}/detail", methods={"GET"})
     */
    public function getDetail($param) : JsonResponse
    {
        $response = $this->userHelper->getUserDetails($param);

        return $this->parseResponse($response);
    }

    /**
     * @Route("/get/{param}/cars", methods={"GET"})
     */
    public function getUserCars(int $param) : JsonResponse
    {
        $response = $this->userHelper->getUserCars($param);

        return $this->parseResponse($response);
    }
}