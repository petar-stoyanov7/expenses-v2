<?php

namespace App\Service;

use App\Entity\Schedule;
use App\Helpers\DateHelper;
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

    public function checkGetCarSchedules(int $carId, array $params = []) : array
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
            $response['message'] = "No such car exists";
            return $response;
        }

        $warning = null;
        $dbParams = [];

        if (!empty($params['dMileage'])) {
            if (!is_numeric($params['dMileage'])) {
                $warning = "Warning! Mileage interval needs to be a number. This is ignored in search results";
            } else {
                $dbParams['mileage'] = (int)$car->getMileage() + (int)$params['dMileage'];
            }
        }
        /* This is intentional - if both parameters are set - direct mileage takes higher priority */
        if (!empty($params['mileage'])) {
            if (!is_numeric($params['mileage'])) {
                $warning = "Warning! Mileage needs to be a number. This is ignored in search results";
            } else {
                $dbParams['mileage'] = (int)$params['mileage'];
            }
        }

        if (!empty($params['dTime'])) {
            if (!DateHelper::isValidInterval($params['dTime'])) {
                $warning = "Invalid interval provided. Use '2 day', '3 month', '1 year', etc. Date interval omitted";
            } else {
                $dbParams['date'] = DateHelper::createDateFromInterval($params['dTime']);
            }
        }
        /* Again - it's intentional the 'date' parameter to override the dTime parameter */
        if (!empty($params['date'])) {
            if (!DateHelper::isValidDate($params['date'])) {
                $warning = "Invalid date. It will be ignored";
            } else {
                $dbParams['date'] = $params['date'];
            }
        }

        $schedules = $this->scheduleRepository->getCarSchedules($carId, $dbParams);

        return [
            'success'   => true,
            'message'   => empty($warning) ? "Data extracted successfully" : $warning,
            'data'      => $schedules
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

    private function _checkScheduleData(array $data, bool $isEdit = false) : array
    {
        $response = [
            'success' => false,
            'message' => "Missing car"
        ];
        if (empty($data['carId'])) {
            return $response;
        }

        if (empty($data['name'])) {
            $response['message'] = "Schedule must have a valid name";
            return $response;
        }

        if (empty($data['mileage']) && empty($data['date'])) {
            $response['message'] = "You must provide a date or mileage";
            return $response;
        }

        if (!empty($data['date'])) {
            $date = $data['date'];
            if (!DateHelper::isValidDate($date)) {
                $response['message'] = "Invalid date format. Use 'Y-m-d' for date format";
                return $response;
            }
            if (DateHelper::isPastDate($date)) {
                $response['message'] = "Can not schedule events for the past";
                return $response;
            }
        }

        $car = $this->carRepository->find($data['carId']);
        if (empty($car)) {
            $response['message'] = "No such car exists";
            return $response;
        }

        if (!empty($data['mileage']) && $car->getMileage() > $data['mileage']) {
            $response['message'] = "Invalid mileage";
            return $response;
        }

        $response['car'] = $car;
        $response['success'] = true;

        return $response;
    }
}