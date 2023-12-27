<?php

namespace App\Service;

use App\Entity\FuelType;
use App\Repository\FuelTypeRepository;
use Doctrine\ORM\EntityManagerInterface;

class FuelTypeHelper
{
    private FuelTypeRepository $fuelTypeRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        FuelTypeRepository $repo,
        EntityManagerInterface $entityManager
    ) {
        $this->fuelTypeRepository = $repo;
        $this->entityManager = $entityManager;
    }

    public function checkCreateFuelType(array $fuelQuery) : array
    {
        $response = [
            "success" => false,
            "message" => "Missing fuel name"
        ];

        $name = $fuelQuery['name'];
        $displayName = !empty($fuelQuery['displayName']) ? $fuelQuery['displayName'] : '';

        if (empty($name)) {
            return $response;
        }

        $name = strtolower($name);
        $existingFuel = $this->fuelTypeRepository->getByName($name);
        if (!empty($existingFuel)) {
            $response['message'] = "Fuel type already exists";
            return $response;
        }

        $displayName = !empty($displayName)
            ? strtolower($displayName)
            : null;

        $fuelType = new FuelType();
        $fuelType->setName($name);
        if (!empty($displayName)) {
            $fuelType->setDisplayName($displayName);
        }

        $this->fuelTypeRepository->add($fuelType, true);

        $fuelId = $fuelType->getId();

        return [
            'success'   => !empty($fuelId),
            'message'   => "Successfully created FuelType",
            'id'        => $fuelId
        ];
    }

    public function checkDeleteFuelType($param) : array
    {
        $response = [
            "success" => false,
            "message" => "Fuel type does not exist"
        ];
        if (is_numeric($param)) {
            $fuelType = $this->fuelTypeRepository->find($param);
        } else {
            $fuelType = $this->fuelTypeRepository->findByName($param);
        }

        if (empty($fuelType)) {
            return $response;
        }

        if (is_array($fuelType)) {
            $fuelType = $fuelType[0];
        }

        $this->fuelTypeRepository->remove($fuelType, true);
        $success = empty($fuelType->getId());

        return [
            'success' => $success,
            'message' => $success ? "Successfully deleted fuel type {$param}" : "Error with execution"
        ];
    }
}