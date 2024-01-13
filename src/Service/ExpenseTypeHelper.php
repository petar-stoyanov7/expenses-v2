<?php

namespace App\Service;

use App\Entity\ExpenseType;
use App\Repository\ExpenseTypeRepository;

class ExpenseTypeHelper
{
    private ExpenseTypeRepository $expenseTypeRepository;

    public function __construct(ExpenseTypeRepository $expenseTypeRepository)
    {
        $this->expenseTypeRepository = $expenseTypeRepository;
    }

    public function checkCreateExpenseType($data) : array
    {
        $response = [
            'success' => false,
            'message' => "Missing data"
        ];
        if (empty($data) || empty($data['name'])) {
            return $response;
        }

        $existing = $this->expenseTypeRepository->getByName($data['name']);
        if (!empty($existing)) {
            $response['message'] = "This expense type already exists";
            return $response;
        }

        $expenseType = new ExpenseType();
        $expenseType->setName($data['name']);

        if (!empty($data['displayName'])) {
            $expenseType->setDisplayName($data['displayName']);
        }

        $this->expenseTypeRepository->add($expenseType, true);
        $expenseTypeId = $expenseType->getId();

        $success = !empty($expenseTypeId);

        return [
            'success'   => $success,
            'message'   => $success ? "Expense Type successfully created" : "Error creating Expense Type",
            'data'      => ['expenseTypeId' => $expenseTypeId]
        ];
    }

    public function getExpenseType($param) : array
    {
        $response = [
            'success' => false,
            'message' => "Missing identificator"
        ];
        if (empty($param)) {
            return $response;
        }

        if (is_numeric($param)) {
            $expenseType = $this->expenseTypeRepository->getById($param);
        } else {
            $expenseType = $this->expenseTypeRepository->getByName($param);
        }
        if (empty($expenseType)) {
            $response['message'] = "No such expense type exists";
            return $response;
        }

        return [
            'success'   => true,
            'message'   => "Expense type successfully extracted",
            'data'      => $expenseType
        ];
    }
}