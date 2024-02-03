<?php

namespace App\Controller;

use App\Service\ScheduleHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/schedule", name="schedule")
 */
class ScheduleController extends AbstractExpenseController
{
    private ScheduleHelper $scheduleHelper;

    public function __construct(ScheduleHelper $scheduleHelper)
    {
        $this->scheduleHelper = $scheduleHelper;
    }

    /**
     * @Route("/add", methods={"POST"})
     */
    public function add(Request $request): JsonResponse
    {
        $scheduleData = $this->getRequestData($request);
        $response = $this->scheduleHelper->checkCreateSchedule($scheduleData);

        return $this->parseResponse($response);
    }

    /**
     * @Route("/edit/{id}",  requirements={"id"="\d+"}, methods={"POST"})
     */
    public function edit(Request $request, int $id): JsonResponse
    {
        $scheduleData = $this->getRequestData($request);
        $response = $this->scheduleHelper->checkEditSchedule($id, $scheduleData);

        return $this->parseResponse($response);
    }

    /**
     * @Route("/delete/{id}",  requirements={"id"="\d+"}, methods={"POST"})
     */
    public function delete(int $id): JsonResponse
    {
        $response = $this->scheduleHelper->checkDeleteSchedule($id);

        return $this->parseResponse($response);
    }

    /**
     * @Route("/get/{id}/all",  requirements={"id"="\d+"}, methods={"POST","GET"})
     */
    public function getAllCarSchedules(int $id): JsonResponse
    {
        $response = $this->scheduleHelper->checkGetCarSchedules($id);

        return $this->parseResponse($response);
    }

    /**
     * @Route("/get/{id}",  requirements={"id"="\d+"}, methods={"POST","GET"})
     */
    public function getCarSchedules(Request $request, int $id): JsonResponse
    {
        $scheduleData = $this->getRequestData($request);
        $response = $this->scheduleHelper->checkGetCarSchedules($id, $scheduleData);

        return $this->parseResponse($response);
    }

}