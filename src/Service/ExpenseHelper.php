<?php

namespace App\Service;

use App\Entity\Expense;
use App\Repository\CarFuelsRepository;
use App\Repository\CarRepository;
use App\Repository\ExpenseRepository;
use App\Repository\ExpenseTypeRepository;
use App\Repository\FuelTypeRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class ExpenseHelper
{
    private userRepository $userRepository;
    private ExpenseRepository $expenseRepository;
    private ExpenseTypeRepository $expenseTypeRepository;
    private FuelTypeRepository $fuelTypeRepository;
    private CarRepository $carRepository;
    private CarFuelsRepository $carFuelsRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        UserRepository         $userRepository,
        ExpenseRepository      $expenseRepository,
        ExpenseTypeRepository  $expenseTypeRepository,
        FuelTypeRepository     $fuelTypeRepository,
        CarRepository          $carRepository,
        CarFuelsRepository     $carFuelsRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->userRepository = $userRepository;
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

    public function checkGetExpense(int $carId, $parameters = []) : array
    {
        $response = [
            'success' => false,
            'message' => "Missing car"
        ];
        if (empty($carId)) {
            return $response;
        }

        $car = $this->carRepository->find($carId);
        if (empty($car)) {
            $response['message'] = "No such car exists";
            return $response;
        }

        $parameters['car'] = $carId;
        $expenses = $this->expenseRepository->getExpenses($parameters);

        return [
            'success'   => true,
            'message'   => !empty($expenses) ? "Data retrieved successfully" : "No expenses with these parameters",
            'data'      => $expenses
        ];
    }

    public function checkGetUserExpenses(int $userId, $parameters = []) : array
    {
        $response = [
            'success' => false,
            'message' => "Missing user"
        ];
        if (empty($userId)) {
            return $response;
        }

        $user = $this->userRepository->find($userId);
        if (empty($user)) {
            $response['message'] = "No such user exists";
            return $response;
        }

        $parameters['user'] = $userId;
        $expenses = $this->expenseRepository->getExpenses($parameters);

        return [
            'success'   => true,
            'message'   => !empty($expenses) ? "Data retrieved successfully" : "No expenses with these parameters",
            'data'      => $expenses
        ];
    }

    public function checkImport(
        int $userId,
        int $carId,
        ?string $data,
        ?string $rowsSeparator = PHP_EOL,
        ?string $separator = ':'
    ): array
    {
        $response = [
            'success'   => false,
            'message'   => "Invalid import"
        ];
        $dataArray = explode($rowsSeparator, $data);

        $user = $this->userRepository->find($userId);
        if (empty($user)) {
            $response['message'] = "No user found";
            return $response;
        }
        $hasErrors = false;
        $failedImports = [];

        foreach ($dataArray as $row) {
            $row = preg_replace('/[\r\n]+/', '', $row); //sometimes the csv export contains extra newlines
            $fuelType = $liters = null;
            $entry = explode($separator, $row);
            if (empty($entry)) {
                continue; //empty row
            }
            if (!is_array($entry) || count($entry) < 6) {
                $hasErrors = true;
                $failedImports[] = $row;
                continue;
            }

            /* The API expects date to be shown as Y-m-d, but in the export files it's stored as Ymd */
            $date = $entry[5];
            if (!empty($date) && preg_match('/\d{8}/', $date)) {
                $date = DateTime::createFromFormat('Ymd', $date)->format('Y-m-d');
            }

            $expenseData = [
                'mileage'   => $entry[0],
                'value'     => $entry[3],
                'notes'     => $entry[4],
                'carId'     => $carId,
                'date'      => $date
            ];

            switch(mb_strtolower($entry[1])) {
                case 'gas':
                case 'gasoline':
                case 'бенз':
                case 'бензин':
                    $expenseType = 1;
                    $fuelType = 1;
                    $liters = $entry[2];
                    break;
                case 'diesel':
                case 'дизел':
                    $expenseType = 1;
                    $fuelType = 2;
                    $liters = $entry[2];
                    break;
                case 'lpg':
                case 'газ':
                    $expenseType = 1;
                    $fuelType = 3;
                    $liters = $entry[2];
                    break;
                case 'застраховка':
                case 'zastrahovka':
                case 'insurance':
                case 'каско':
                case 'kasko':
                case 'го':
                case 'go':
                    $expenseType = 2;
                    break;
                case 'ремонт':
                case 'remont':
                case 'maint':
                case 'maintenance':
                    $expenseType = 3;
                    break;
                case 'данък':
                case 'danak':
                case 'tax':
                    $expenseType = 5;
                    break;
                case 'глоба':
                case 'globa':
                case 'fine':
                    $expenseType = 6;
                    break;
                default:
                    $expenseType = 0;
                    break;
            }
            $expenseData['expenseId'] = $expenseType;

            if (!is_null($fuelType) && !is_null($liters)) {
                $expenseData['fuelId'] = $fuelType;
                $expenseData['liters'] = $liters;
            }

            $response = $this->_checkExpenseData($expenseData);
            if (!$response['success']) {
                $hasErrors = true;
                $failedImports[] = $row;
                continue;
            }

            $expense = $this->_setExpenseData(new Expense(), $response);
            $this->expenseRepository->add($expense);
        }

        $this->entityManager->flush();

        $response = [
            'success' => true,
            'message' => 'Import completed successfully'
        ];

        if ($hasErrors) {
            $response['message'] = "There were errors during import";
            $response['data'] = json_encode($failedImports);
        }

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
            empty($data['expenseId']) && $data['expenseId'] !== 0 || // expenseId = 0 is "Others"
            empty($data['value'])
        ) {
            return $response;
        }
        $response['value'] = $data['value'];
        $response['notes'] = empty($data['notes']) ? null : $data['notes'];

        if (!empty($data['date'])) {
            $response['date'] = $data['date'];
        }

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
        if ($mileage > $carMileage) {
            $car->setMileage($mileage);
            $this->carRepository->edit($car, true);
            $response['car'] = $car;
        }
        $response['mileage'] = $mileage;

        $response['success'] = true;
        $response['message'] = "No errors found with expense";
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
        if (!empty($data['date']) && DateTime::createFromFormat('Y-m-d', $data['date']) !== false) {
            $expense->setUpdatedAt(new DateTime($data['date']));
        }

        return $expense;
    }
}