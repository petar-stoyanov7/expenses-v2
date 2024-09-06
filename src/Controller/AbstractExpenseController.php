<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Exception;

abstract class AbstractExpenseController extends AbstractController
{
    protected function getRequestData(Request $request)
    {
        $contentType = $request->getContentType();

        if ('json' === $contentType) {
            $data = $request->getContent();
            if (empty($data)) {
                return [];
            }

            try {
                $result = json_decode($data, true);
            } catch (Exception $e) {
                $result = [];
            }
            return $result;
        } elseif ('form' === $contentType && !empty($_FILES)) {
            $file = $request->files->all();
            /** @var UploadedFile $file */
            if (empty($file['file'])) {
                return [];
            }
            return $file['file']->getContent();
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