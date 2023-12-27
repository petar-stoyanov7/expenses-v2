<?php

namespace App\Service;

use App\Entity\FuelType;
use App\Repository\FuelTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

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

    public function checkCreateFuelType(Request $request)
    {
        $name = $request->get('name');
        $displayName = $request->get('displayName');

        if (empty($name)) {
            return "Missing fuel name";
        }

        $name = strtolower($name);
        $existingFuel = $this->fuelTypeRepository->getByName($name);
        if (!empty($existingFuel)) {
            return "Fuel type already exists";
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
            'id'        => $fuelId
        ];
    }

    public function checkDeleteFuelType($param)
    {
        if (is_numeric($param)) {
            $fuelType = $this->fuelTypeRepository->find($param);
        } else {
            $fuelType = $this->fuelTypeRepository->findByName($param);
        }

        if (empty($fuelType)) {
            return "Fuel type does not exist";
        }

        if (is_array($fuelType)) {
            $fuelType = $fuelType[0];
        }

        $this->fuelTypeRepository->remove($fuelType, true);

        return [
            'success' => empty($fuelType->getId())
        ];
    }
}