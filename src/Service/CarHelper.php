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

    public function checkEditCar(array $carData, $id) : array
    {
        $response = [
            'success' => false,
            'message' => "Missing data"
        ];
        if (empty($id) || empty($carData)) {
            return $response;
        }

        $car = $this->carRepository->find($id);
        if (empty($car)) {
            $response['message'] = "No such car exists";
            return $response;
        }

        $isChanged = false;
        if (!empty($carData['userId']) && $carData['userId'] !== $car->getUser()->getId()) {
            $user = $this->userRepository->find($carData['userId']);
            $car->setUser($user);
            $isChanged = true;
        }
        if (!empty($carData['brand']) && $carData['brand'] !== $car->getBrand()) {
            $car->setBrand($carData['brand']);
            $isChanged = true;
        }
        if (!empty($carData['model']) && $carData['model'] !== $car->getModel()) {
            $car->setModel($carData['model']);
            $isChanged = true;
        }
        if (!empty($carData['model']) && $carData['model'] !== $car->getModel()) {
            $car->setModel($carData['model']);
            $isChanged = true;
        }
        if (!empty($carData['color']) && $carData['color'] !== $car->getColor()) {
            $car->setColor($carData['color']);
            $isChanged = true;
        }
        if (!empty($carData['mileage']) && $carData['mileage'] !== $car->getMileage()) {
            $car->setMileage($carData['mileage']);
            $isChanged = true;
        }
        if (!empty($carData['year']) && $carData['year'] !== $car->getYear()) {
            $car->setYear($carData['year']);
            $isChanged = true;
        }
        if (!empty($carData['notes']) && $carData['notes'] !== $car->getNotes()) {
            $car->setNotes($carData['notes']);
            $isChanged = true;
        }

        if ($isChanged) {
            $this->carRepository->edit($car, true);
        }

        $newFuelIds = $carData['fuel'];
        if (
            !empty($newFuelIds) &&
            is_array($newFuelIds) &&
            $this->checkFuelValidity($newFuelIds)
        ) {
            $currentFuel = $this->carFuelsRepository->getCarFuels($id);
            $currentFuelIds = array_column($currentFuel, 'id');

            if (count($currentFuelIds) >= count($newFuelIds)) {
                foreach ($currentFuelIds as $i => $id) {
                    if (in_array($id, $newFuelIds)) {
                        $deleteKey = array_search($id, $newFuelIds);
                        unset($newFuelIds[$deleteKey]);
                        unset($currentFuelIds[$i]);
                    }
                }
            } else {
                foreach ($newFuelIds as $i => $id) {
                    if (in_array($id, $currentFuelIds)) {
                        $deleteKey = array_search($id, $newFuelIds);
                        unset($newFuelIds[$i]);
                        unset($currentFuelIds[$deleteKey]);
                    }
                }
            }

            if (!empty($currentFuelIds)) {
                $isChanged = true;
                foreach ($currentFuelIds as $fuelId) {
                    $carFuel = $this->carFuelsRepository->getByCarIdAndFuelId($car->getId(), $fuelId);
                    $this->carFuelsRepository->remove($carFuel, true);
                }
            }
            if (!empty($newFuelIds)) {
                $isChanged = true;
                foreach ($newFuelIds as $fuelId) {
                    $fuelType = $this->fuelTypeRepository->find($fuelId);
                    $carFuel = new CarFuels();
                    $carFuel
                        ->setCar($car)
                        ->setFuel($fuelType);
                    $this->carFuelsRepository->add($carFuel, true);
                }
            }
        }

        if ($isChanged) {
            return [
                'success'   => true,
                'message'   => "Car edited successfully."
            ];
        }

        $response['message'] = "Sent data is the same as the current car";
        return $response;
    }

    public function checkDeleteCar(int $carId) : array
    {
        $response = [
            'success'   => false,
            'message'   => "Missing data"
        ];
        if (empty($carId)) {
            return $response;
        }

        $car = $this->carRepository->find($carId);
        if (empty($car)) {
            $response['message'] = "No such car exists";
            return $response;
        }

        $carFuels = $this->carFuelsRepository->findByCarId($carId);
        if (!empty($carFuels)) {
            foreach ($carFuels as $carFuel) {
                $this->carFuelsRepository->remove($carFuel);
            }
            $this->entityManager->flush();
        }

        $this->carRepository->remove($car, true);

        return [
            'success' => true,
            'message' => "Car deleted successfully"
        ];
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