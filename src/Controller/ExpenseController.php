<?php

namespace App\Controller;

use App\Repository\ExpenseRepository;
use App\Service\ExpenseHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
     * @Route("/expense/add", methods={"POST"})
     */
    public function add(Request $request) : JsonResponse
    {
        $expense = $this->getRequestData($request);
        $response = $this->expenseHelper->checkCreateExpense($expense);

        return $this->parseResponse($response);
    }

    /**
     * @Route("/expense/edit/{id}", requirements={"id"="\d+"}, methods={"POST"})
     */
    public function edit(Request $request, int $id) : JsonResponse
    {
        $data = $this->getRequestData($request);
        $response = $this->expenseHelper->checkEditExpense($id, $data);

        return $this->parseResponse($response);
    }

    /**
     * @Route("/expense/delete/{id}", requirements={"id"="\d+"}, methods={"POST"})
     */
    public function delete($id) : JsonResponse
    {
        $response = $this->expenseHelper->checkDeleteExpense($id);

        return $this->parseResponse($response);
    }
}