<?php

namespace App\Service;

use App\Entity\Car;
use App\Entity\CarFuels;
use App\Repository\CarFuelsRepository;
use App\Repository\CarRepository;
use App\Repository\FuelTypeRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class CarHelper
{
    private CarRepository $carRepository;
    private FuelTypeRepository $fuelTypeRepository;
    private CarFuelsRepository $carFuelsRepository;
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        CarRepository $carRepo,
        FuelTypeRepository $fuelTypeRepository,
        EntityManagerInterface $entityManager,
        CarFuelsRepository $carFuelsRepository,
        UserRepository $userRepository
    )
    {
        $this->carRepository = $carRepo;
        $this->fuelTypeRepository = $fuelTypeRepository;
        $this->carFuelsRepository = $carFuelsRepository;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    public function checkCreateCar(array $carData) : array
    {
        $response = [
            'success' => false,
            'message' => 'Insufficient data'
        ];

        if (
            empty($carData['userId']) ||
            empty($carData['fuel']) ||
            empty($carData['brand']) ||
            empty($carData['model']) ||
            empty($carData['color'])
        ) {
            return $response;
        }

        //TODO: add check for duplicate car for user

        $mileage = !empty($carData['mileage']) ? (int)$carData['mileage'] : 0;

        $car = new Car();
        $user = $this->userRepository->find($carData['userId']);
        if (empty($user)) {
            $response['message'] = "Invalid user";
            return $response;
        }
        $car->setUser($user);
        $car->setBrand($carData['brand']);
        $car->setModel($carData['model']);
        $car->setColor($carData['color']);
        $car->setMileage($mileage);

        if (!empty($carData['year'])) {
            $car->setYear($carData['year']);
        }
        if (!empty($carData['notes'])) {
            $car->setNotes($carData['notes']);
        }

        if (!$this->checkFuelValidity($carData['fuel'])) {
            $response['message'] = "Invalid fuel type(s)";
            return $response;
        }

        $this->carRepository->add($car, true);
        $carId = $car->getId();


        if ($carId) {

            if (is_array($carData['fuel'])) {
                foreach ($carData['fuel'] as $fuelId) {
                    $fuel = $this->fuelTypeRepository->find($fuelId);
                    if (empty($fuel)) {
                        continue;
                    }
                    $carFuel = new CarFuels();
                    $carFuel->setCar($car);
                    $carFuel->setFuel($fuel);
                    $this->carFuelsRepository->add($carFuel, true);
                }
            } else {
                $fuel = $this->fuelTypeRepository->find($carData['fuel']);
                if (!empty($fuel)) {
                    $carFuel = new CarFuels();
                    $carFuel->setCar($car);
                    $carFuel->setFuel($fuel);
                    $this->carFuelsRepository->add($carFuel, true);
                }
            }

            return [
                'success'   => true,
                'message'   => "Successfully created car",
                'data'      => ['carId' => $carId]
            ];

        }

        $response['message'] = "Error with execution";
        return $response;
    }

    public function getAllCars() : array
    {
        $response = [
            'success' => false,
            'message' => "No cars present"
        ];
        $cars = $this->carRepository->getAllCars();

        if (empty($cars)) {
            return $response;
        }

        foreach ($cars as $i => $car) {
            $cars[$i]['fuel'] = $this->carFuelsRepository->getCarFuels($car['id']);
        }


        return [
            'success'   => true,
            'message'   => "Cars provided",
            'data'      => $cars
        ];
    }

    public function checkEditCar(array $carData) : array
    {
        //TODO: implement
        return [];
    }

    private function checkFuelValidity($fuelData) : bool
    {
        if (is_array($fuelData)) {
            foreach ($fuelData as $fuelId) {
                $fuel = $this->fuelTypeRepository->getById($fuelId);
                if (empty($fuel)) {
                    return false;
                }
            }

            return true;
        } else {
            $fuel = $this->fuelTypeRepository->getById($fuelData);
            return !empty($fuel);
        }
    }

}