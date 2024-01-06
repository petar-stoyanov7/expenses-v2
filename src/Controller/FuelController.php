<?php

namespace App\Controller;

use App\Repository\FuelTypeRepository;
use App\Service\FuelTypeHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FuelController extends AbstractExpenseController
{
    private FuelTypeRepository $fuelTypeRepository;

    private FuelTypeHelper $fuelTypeHelper;

    public function __construct(
        FuelTypeRepository $repository,
        FuelTypeHelper     $helper
    )
    {
        $this->fuelTypeRepository = $repository;
        $this->fuelTypeHelper = $helper;
    }

    /**
     * @Route("/fuel/add", methods={"POST"})
     */
    public function new(Request $request): JsonResponse
    {
        $query = $request->request->all();
        $response = $this->fuelTypeHelper->checkCreateFuelType($query);

        return $this->parseResponse($response);
    }

    /**
     * @Route("/fuel/get/all")
     */
    public function getAll(): JsonResponse
    {
        $data = $this->fuelTypeRepository->getAllFuels();

        return $this->parseDbResponse($data);
    }

    /**
     * @Route("/fuel/get/{id}", requirements={"id"="\d+"})
     */
    public function getById(int $id): JsonResponse
    {
        $data = $this->fuelTypeRepository->getById($id);

        return $this->parseDbResponse($data);
    }

    /**
     * @Route("/fuel/get/{name}")
     */
    public function getByName(string $name) : JsonResponse
    {
        $data = $this->fuelTypeRepository->getByName($name);

        return $this->parseDbResponse($data);
    }

    /**
     * @Route("/fuel/delete/{param}")
     */
    public function delete($param): JsonResponse
    {
        $response = $this->fuelTypeHelper->checkDeleteFuelType($param);

        return $this->parseResponse($response);
    }
}