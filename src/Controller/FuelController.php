<?php

namespace App\Controller;

use App\Entity\FuelType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FuelController extends AbstractController
{
    /**
     * @Route("/fuel/new")
     */
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $name = $request->get('name');
        $displayName = $request->get('displayName');

        if (empty($name)) {
            $this->json('Error with execution - missing display name', 400);
        }
        $name = strtolower($name);
        $displayName = !empty($displayName)
            ? strtolower($displayName)
            : null;

        $fuelType = new FuelType();
        $fuelType->setName($name);
        if (!empty($displayName)) {
            $fuelType->setDisplayName($displayName);
        }

        $entityManager->persist($fuelType);
        $entityManager->flush();

        $fuelId = $fuelType->getId();

        if (!empty($fuelId)) {
            return $this->json([
                'success' => true,
                'id' => $fuelId
            ]);
        }

        return $this->json(
            'Error with execution',
            400
        );
    }

    /**
     * @Route("/fuel/get/all")
     */
    public function getAll(EntityManagerInterface $entityManager): JsonResponse
    {
        $repository = $entityManager->getRepository(FuelType::class);
        $fuelTypes = $repository->findAll();
        //TODO: find ways to transform to normal array


        if (!empty($fuelTypes)) {
            return $this->json($fuelTypes);
        }

        return $this->json('No Data!', 400);
    }

    /**
     * @Route("/fuel/get/{id}", requirements={"id"="\d+"})
     */
    public function getById(FuelType $fuelType): JsonResponse
    {
        //TODO: find ways to handle missing ID
        return $this->json($fuelType);
    }

    /**
     * @Route("/fuel/get/{name}")
     */
    public function getByName(FuelType $fuelType)
    {
        //TODO: find ways to handle missing ID
        return $this->json($fuelType);
    }
}