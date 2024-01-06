<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class AbstractExpenseController extends AbstractController
{
    protected function parseResponse(array $data) : JsonResponse
    {
        if (empty($data)) {
            return $this->json($data, 400);
        }
        $success = false;
        $message = 'Error with execution';
        $responseCode = empty($data['success'])  ? 400 : 200;

        if (!empty($data['success'])) {
            $success = $data['success'];
        }

        if (!empty($data['message'])) {
            $message = $data['message'];
        }

        $response = [
            'success' => $success,
            'message' => $message
        ];

        if (!empty($data['data'])) {
            $response['data'] = $data;
        }

        return $this->json(
            $response,
            $responseCode
        );

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