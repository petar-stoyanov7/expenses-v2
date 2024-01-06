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


}