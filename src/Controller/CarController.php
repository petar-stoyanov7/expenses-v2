<?php

namespace App\Controller;

use App\Repository\CarRepository;
use App\Service\CarHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class CarController extends AbstractExpenseController
{
    private CarHelper $carHelper;
    private CarRepository $carRepository;

    public function __construct(CarHelper $helper, CarRepository $repo)
    {
        $this->carHelper = $helper;
        $this->carRepository = $repo;
    }

    /**
     * @Route("/car/add", methods={"POST"})
     */
    public function add(Request $request): JsonResponse
    {
        $carData = $this->getRequestData($request);
        $response = $this->carHelper->checkCreateCar($carData);

        return $this->parseResponse($response);
    }

    /**
     * @Route("/car/get/all", methods={"GET"})
     */
    public function getAll() : JsonResponse
    {
        $response = $this->carHelper->getAllCars();

        return $this->parseResponse($response);
    }

    /**
     * @Route("/car/get/{carId}", methods={"GET"})
     */
    public function getById(int $carId) : JsonResponse
    {
        $response = $this->carRepository->getByCarId($carId);

        return $this->parseDbResponse($response);
    }

    /**
     * @Route("/car/edit/{id}", methods={"POST"})
     */
    public function edit(Request $request, $id) : JsonResponse
    {
        $carData = $this->getRequestData($request);
        $response = $this->carHelper->checkEditCar($carData, $id);

        return $this->parseResponse($response);
    }

    /**
     * @Route("/car/delete/{id}", methods={"POST","GET"})
     */
    public function delete($id) : JsonResponse
    {
        $response = $this->carHelper->checkDeleteCar($id);

        return $this->parseResponse($response);
    }
}