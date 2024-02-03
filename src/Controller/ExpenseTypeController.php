<?php

namespace App\Controller;

use App\Repository\ExpenseTypeRepository;
use App\Service\ExpenseTypeHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/expense-type", name="expense-type")
 */
class ExpenseTypeController extends AbstractExpenseController
{
    private ExpenseTypeHelper $expenseTypeHelper;
    private ExpenseTypeRepository $expenseTypeRepository;

    public function __construct(
        ExpenseTypeHelper $expenseTypeHelper,
        ExpenseTypeRepository $expenseTypeRepository
    )
    {
        $this->expenseTypeHelper = $expenseTypeHelper;
        $this->expenseTypeRepository = $expenseTypeRepository;
    }

    /**
     * @Route("/add", methods={"POST"})
     */
    public function add(Request $request) : JsonResponse
    {
        $data = $this->getRequestData($request);
        $response = $this->expenseTypeHelper->checkCreateExpenseType($data);

        return $this->parseResponse($response);
    }

    /**
     * @Route("/get/all", methods={"GET"})
     */
    public function getAll() : JsonResponse
    {
        $response = $this->expenseTypeRepository->getAllExpenseTypes();

        return $this->parseDbResponse($response);
    }

    /**
     * @Route("/get/{param}", methods={"GET"})
     */
    public function getExpenseType($param) : JsonResponse
    {
        $response = $this->expenseTypeHelper->getExpenseType($param);

        return $this->parseResponse($response);
    }
}