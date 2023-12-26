<?php

namespace App\Controller;

use App\Repository\FuelTypeRepository;
use App\Service\FuelTypeHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FuelController extends AbstractController
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
     * @Route("/fuel/new", methods={"POST"})
     */
    public function new(Request $request): JsonResponse
    {
        $result = $this->fuelTypeHelper->checkCreateFuelType($request);

        return $this->json(
            $result,
            !empty($result['success']) ? 200 : 400
        );
    }

    /**
     * @Route("/fuel/get/all")
     */
    public function getAll(): JsonResponse
    {
        $data = $this->fuelTypeRepository->getAllFuels();

        return $this->parseResult($data);
    }

    /**
     * @Route("/fuel/get/{id}", requirements={"id"="\d+"})
     */
    public function getById(int $id): JsonResponse
    {
        $data = $this->fuelTypeRepository->getById($id);

        return $this->parseResult($data);
    }

    /**
     * @Route("/fuel/get/{name}")
     */
    public function getByName(string $name) : JsonResponse
    {
        $data = $this->fuelTypeRepository->getByName($name);

        return $this->parseResult($data);
    }

    /**
     * @Route("/fuel/delete/{param}")
     */
    public function delete($param): JsonResponse
    {
        $response = $this->fuelTypeHelper->checkDeleteFuelType($param);

        return $this->json(
            $response,
            !empty($result['success']) ? 200 : 400
        );
    }

    private function parseResult($data, $errorMessage = 'No data'): JsonResponse
    {
        if (!empty($data)) {
            return $this->json($data);
        }

        return $this->json($errorMessage, 400);
    }
}