<?php

namespace App\Controller;

use App\Repository\ExpenseRepository;
use App\Service\ExpenseHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/expense", name="expense")
 */
class ExpenseController extends AbstractExpenseController
{
    private ExpenseHelper $expenseHelper;
    private ExpenseRepository $expenseRepository;

    public function __construct(
        ExpenseHelper $expenseHelper,
        ExpenseRepository $expenseRepository
    )
    {
        $this->expenseHelper = $expenseHelper;
        $this->expenseRepository = $expenseRepository;
    }


    /**
     * @Route("/add", methods={"POST"})
     */
    public function add(Request $request) : JsonResponse
    {
        $expense = $this->getRequestData($request);
        $response = $this->expenseHelper->checkCreateExpense($expense);

        return $this->parseResponse($response);
    }

    /**
     * @Route("/edit/{id}", requirements={"id"="\d+"}, methods={"POST"})
     */
    public function edit(Request $request, int $id) : JsonResponse
    {
        $data = $this->getRequestData($request);
        $response = $this->expenseHelper->checkEditExpense($id, $data);

        return $this->parseResponse($response);
    }

    /**
     * @Route("/delete/{id}", requirements={"id"="\d+"}, methods={"POST"})
     */
    public function delete($id) : JsonResponse
    {
        $response = $this->expenseHelper->checkDeleteExpense($id);

        return $this->parseResponse($response);
    }

    /**
     * @Route("/get/car/{carId}", requirements={"carId"="\d+"}, methods={"POST", "GET"})
     */
    public function getExpense($carId, Request $request) : JsonResponse
    {
        $data = $this->getRequestData($request);
        $response = $this->expenseHelper->checkGetExpense($carId, $data);

        return $this->parseResponse($response);
    }

    /**
     * @Route("/get/user/{userId}", requirements={"userId"="\d+"}, methods={"POST", "GET"})
     */
    public function getUserExpense($userId, Request $request) : JsonResponse
    {
        $data = $this->getRequestData($request);
        $response = $this->expenseHelper->checkGetUserExpenses($userId, $data);

        return $this->parseResponse($response);
    }

    /**
     * @Route("/import/{userId}/{carId}", requirements={"userId"="\d+", "carId"="\d+"}, methods={"POST"})
     */
    public function importExpenses($userId, $carId, Request $request) : JsonResponse
    {
        $fileData = $this->getRequestData($request);
        $response = $this->expenseHelper->checkImport($userId, $carId, $fileData);

        return $this->parseResponse($response);
    }
}