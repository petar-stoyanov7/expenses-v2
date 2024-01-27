<?php

namespace App\Service;

use App\Entity\Schedule;
use App\Repository\CarRepository;
use App\Repository\ScheduleRepository;
use DateTime;
use Exception;

class ScheduleHelper
{
    private ScheduleRepository $scheduleRepository;
    private CarRepository $carRepository;

    public function __construct(
        ScheduleRepository $repository,
        CarRepository      $carRepository
    )
    {
        $this->scheduleRepository = $repository;
        $this->carRepository = $carRepository;
    }

    public function checkCreateSchedule(array $data): array
    {
        $response = $this->_checkScheduleData($data);
        if (!$response['success']) {
            return $response;
        }

        $schedule = new Schedule();
        $schedule->setCar($response['car']);
        $schedule->setName($data['name']);

        if (!empty($data['mileage'])) {
            $schedule->setMileage($data['mileage']);
        }
        if (!empty($data['date'])) {
            $schedule->setDate(new DateTime($data['date']));
        }
        if (!empty($data['notes'])) {
            $schedule->setNotes($data['notes']);
        }

        $this->scheduleRepository->add($schedule, true);
        $scheduleId = $schedule->getId();
        $success = !empty($scheduleId);

        return [
            'success'   => $success,
            'message'   => $success ? "Schedule created successfully" : "Something went wrong",
            'data'      => ['scheduleId' => $scheduleId]
        ];
    }

    public function checkEditSchedule(int $scheduleId, array $data)
    {
        $response = [
            'success' => false,
            'message' => "Missing schedule data"
        ];
        if (empty($scheduleId)) {
            return $response;
        }

        $schedule = $this->scheduleRepository->find($scheduleId);
        if (empty($schedule)) {
            return $response;
        }

        $response = $this->_checkScheduleData($data, true);
        if (!$response['success']) {
            return $response;
        }

        if ($schedule->getCar()->getId() !== $data['carId']) {
            $car = $this->carRepository->find($data['carId']);
            $schedule->setCar($car);
        }

        if (!empty($data['name'])) {
            $schedule->setName($data['name']);
        }
        if (!empty($data['mileage'])) {
            $schedule->setMileage($data['mileage']);
        }
        if (!empty($data['date'])) {
            $schedule->setDate(new DateTime($data['date']));
        }
        if (!empty($data['notes'])) {
            $schedule->setNotes($data['notes']);
        }

        $this->scheduleRepository->edit($schedule, true);

        return [
            'success'   => true,
            'message'   => "Schedule edited successfully",
            'data'      => ['scheduleId' => $schedule->getId()]
        ];
    }

    public function checkDeleteSchedule(int $scheduleId) : array
    {
        $response = [
            'success' => false,
            'message' => "Missing schedule data"
        ];

        if (empty($scheduleId)) {
            return $response;
        }

        $schedule = $this->scheduleRepository->find($scheduleId);
        if (empty($schedule)) {
            $response['message'] = "No such schedule exist";
            return $response;
        }

        $this->scheduleRepository->remove($schedule, true);
        $success = empty($schedule->getId());

        return [
            'success'   => $success,
            'message'   => $success ? "Schedule successfully deleted" : "Something went wrong",
            'data'      => ['scheduleId' => $scheduleId]
        ];
    }

    private function _checkScheduleData(array $scheduleData, bool $isEdit = false) : array
    {
        $response = [
            'success' => false,
            'message' => "Missing car"
        ];
        if (empty($scheduleData['carId'])) {
            return $response;
        }

        if (empty($scheduleData['name'])) {
            $response['message'] = "Schedule must have a valid name";
            return $response;
        }

        if (empty($scheduleData['mileage']) && empty($scheduleData['date'])) {
            $response['message'] = "You must provide a date or mileage";
            return $response;
        }

        if (!empty($scheduleData['date'])) {
            try {
                //TODO: check if in the past
                $dateTime = new DateTime($scheduleData['date']);
            } catch (Exception $e) {
                $response['message'] = "Invalid date";
                return $response;
            }
        }

        $car = $this->carRepository->find($scheduleData['carId']);
        if (empty($car)) {
            $response['message'] = "No such car exists";
            return $response;
        }

        if (!empty($scheduleData['mileage']) && $car->getMileage() > $scheduleData['mileage']) {
            $response['message'] = "Invalid mileage";
            return $response;
        }

        $response['car'] = $car;
        $response['success'] = true;

        return $response;
    }
}