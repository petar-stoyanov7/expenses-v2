<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractExpenseController extends AbstractController
{
    protected function getRequestData(Request $request) : array
    {
        if ('json' === $request->getContentType()) {
            $data = $request->getContent();
            if (empty($data)) {
                return [];
            }

            return json_decode($data, true);
        }

        $data = $request->request->all();
        if (empty($data)) {
            return [];
        }

        return $data;
    }

    protected function parseResponse(array $data) : JsonResponse
    {
        $response = [
            'success' => false,
            'message' => "Error with execution"
        ];

        if (empty($data) || !isset($data['success']) || empty($data['message'])) {
            return $this->json($response, 400);
        }

        return $this->json($data);

    }
    protected function parseDbResponse(array $data) : JsonResponse
    {
        $success = false;
        $message = "No data";
        $responseCode = 400;
        $resultData = [];

        if (!empty($data)) {
            $success = true;
            $responseCode = 200;
            $resultData = $data;
            $message = "Data successfully extracted";
        }

        return $this->json(
            [
                'success'   => $success,
                'message'   => $message,
                'data'      => $resultData
            ],
            $responseCode
        );
    }
}