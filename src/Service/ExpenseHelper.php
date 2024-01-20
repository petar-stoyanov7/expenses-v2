<?php

namespace App\Service;

use App\Entity\Expense;
use App\Repository\CarFuelsRepository;
use App\Repository\CarRepository;
use App\Repository\ExpenseRepository;
use App\Repository\ExpenseTypeRepository;
use App\Repository\FuelTypeRepository;
use Doctrine\ORM\EntityManagerInterface;

class ExpenseHelper
{
    private ExpenseRepository $expenseRepository;
    private ExpenseTypeRepository $expenseTypeRepository;
    private FuelTypeRepository $fuelTypeRepository;
    private CarRepository $carRepository;
    private CarFuelsRepository $carFuelsRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        ExpenseRepository      $expenseRepository,
        ExpenseTypeRepository  $expenseTypeRepository,
        FuelTypeRepository     $fuelTypeRepository,
        CarRepository          $carRepository,
        CarFuelsRepository     $carFuelsRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->expenseRepository = $expenseRepository;
        $this->expenseTypeRepository = $expenseTypeRepository;
        $this->fuelTypeRepository = $fuelTypeRepository;
        $this->carRepository = $carRepository;
        $this->carFuelsRepository = $carFuelsRepository;
        $this->entityManager = $entityManager;
    }

    public function checkCreateExpense(array $data): array
    {
        $response = $this->_checkExpenseData($data);
        if (!$response['success']) {
            return $response;
        }

        $expense = new Expense();
        $expense = $this->_setExpenseData($expense, $response);

        $this->expenseRepository->add($expense, true);
        $expenseId = $expense->getId();

        $success = !empty($expenseId);
        return [
            'success'   => $success,
            'message'   => "Expense added successfully",
            'data'      => ['expenseId' => $expenseId]
        ];
    }

    public function checkEditExpense(int $expenseId, array $data): array
    {
        $response = [
            'success' => false,
            'message' => "Missing data"
        ];
        if (empty($expenseId)) {
            return $response;
        }

        $expense = $this->expenseRepository->find($expenseId);
        if (empty($expense)) {
            $response['message'] = "No such expense exist";
            return $response;
        }

        $response = $this->_checkExpenseData($data);
        if (!$response['success']) {
            return $response;
        }

        $expense = $this->_setExpenseData($expense, $response);
        $this->expenseRepository->edit($expense, true);

        return [
            'success'   => true,
            'message'   => "Expense edited successfully",
            'data'      => ['expenseId' => $expense->getId()]
        ];
    }

    public function checkDeleteExpense(int $expenseId): array
    {
        $response = [
            'success' => false,
            'message' => "Missing expense data"
        ];
        if (empty($expenseId)) {
            return $response;
        }
        $expense = $this->expenseRepository->find($expenseId);
        if (empty($expense)) {
            $response['message'] = "No such expense exists";
            return $response;
        }

        $this->expenseRepository->remove($expense, true);
        $success = empty($expense->getId());

        return [
            'success'   => $success,
            'message'   => $success ? "Expense deleted successfully" : "Something went wrong"
        ];
    }

    public function deleteCarExpenses(int $carId) : array
    {
        $response = [
            'success' => false,
            'message' => "Missing data"
        ];
        if (empty($carId)) {
            return $response;
        }

        $car = $this->carRepository->find($carId);
        if (empty($car)) {
            $response['message'] = "This car does not exists";
            return $response;
        }

        $this->expenseRepository->deleteByCarId($carId);

        return $response;
    }

    private function _checkExpenseData($data): array
    {
        $response = [
            'success' => false,
            'message' => "Missing data"
        ];

        if (
            empty($data) ||
            empty($data['carId']) ||
            empty($data['expenseId']) ||
            empty($data['value'])
        ) {
            return $response;
        }
        $response['value'] = $data['value'];
        $response['notes'] = empty($data['notes']) ? null : $data['notes'];

        $car = $this->carRepository->find($data['carId']);
        if (empty($car)) {
            $response['message'] = "No such car exists";
            return $response;
        }
        $response['car'] = $car;

        $expenseType = $this->expenseTypeRepository->find($data['expenseId']);
        if (empty($expenseType)) {
            $response['message'] = "No such expense type exists";
            return $response;
        }
        $response['expenseType'] = $expenseType;

        /* check if fuel data is consistent */
        if (
            1 === $data['expenseId'] &&
            (
                empty($data['liters']) ||
                empty($data['fuelId']) ||
                !is_numeric($data['liters']) ||
                !is_numeric($data['fuelId'])
            )
        ) {
            $response['message'] = "Missing/incorrect fuel data";
            return $response;
        }


        if (1 === $data['expenseId'] && !empty($data['fuelId'])) {
            $fuelType = $this->fuelTypeRepository->find($data['fuelId']);
            if (empty($fuelType)) {
                $response['message'] = "No such fuel type exists";
                return $response;
            }

            $carFuels = $this->carFuelsRepository->getCarFuels($car->getId());
            $carFuels = array_column($carFuels, 'id');
            if (!in_array($data['fuelId'], $carFuels)) {
                $response['message'] = "This is not the correct fuel type for this car";
                return $response;
            }

            $response['liters'] = $data['liters'];
            $response['fuelType'] = $fuelType;
        }

        $mileage = empty($data['mileage']) ? 0 : $data['mileage'];
        $carMileage = $car->getMileage();
        if ($mileage < $carMileage) {
            $mileage = $carMileage;
        } elseif ($mileage > $carMileage) {
            $car->setMileage($mileage);
            $this->carRepository->edit($car, true);
            $response['car'] = $car;
        }
        $response['mileage'] = $mileage;

        $response['success'] = true;
        return $response;
    }



    private function _setExpenseData(Expense $expense, array $data) : Expense
    {
        $expense->setValue($data['value']);
        $expense->setCar($data['car']);
        $expense->setExpenseType($data['expenseType']);
        $expense->setMileage($data['mileage']);
        $expense->setNotes($data['notes']);

        if (empty($data['fuelType'])) {
            $expense->setLiters(null);
            $expense->setFuelType(null);
        } else {
            $expense->setFuelType($data['fuelType']);
        }
        if (!empty($data['liters'])) {
            $expense->setLiters($data['liters']);
        }

        return $expense;
    }
}