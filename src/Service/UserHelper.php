<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\CarFuelsRepository;
use App\Repository\CarRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class UserHelper
{
    private UserRepository $userRepository;
    private CarRepository $carRepository;
    private CarFuelsRepository $carFuelsRepository;
    private CarHelper $carHelper;
    private EntityManagerInterface $entityManager;


    public function __construct(
        UserRepository $userRepo,
        CarRepository $carRepository,
        CarFuelsRepository $carFuelsRepository,
        CarHelper $carHelper,
        EntityManagerInterface $entityManager
    )
    {
        $this->userRepository = $userRepo;
        $this->carRepository = $carRepository;
        $this->carFuelsRepository = $carFuelsRepository;
        $this->carHelper = $carHelper;
        $this->entityManager = $entityManager;
    }

    public function getUser($param, $hidePassword = true) : array
    {
        $response = [
            'success' => false,
            'message' => "Insufficient data"
        ];
        if (empty($param)) {
            return $response;
        }

        if (is_numeric($param)) {
            $user = $this->userRepository->getById($param);
        } elseif (filter_var($param, FILTER_VALIDATE_EMAIL)) {
            $user = $this->userRepository->getByEmail($param);
        } else {
            $user = $this->userRepository->getByUsername($param);
        }


        if (empty($user)) {
            $response['message'] = "User does not exist";
            return $response;
        }
        $user = $user[0];

        if ($hidePassword) {
            unset($user['password']);
        }

        return [
            'success'   => true,
            'message'   => "User data extracted successfully",
            'data'      => $user
        ];
    }

    public function getUserDetails($param, $hidePassword = true) : array
    {
        $userData = $this->getUser($param, $hidePassword);
        if (!$userData['success']) {
            return $userData;
        }

        $user = $userData['data'];

        $cars = $this->getUserCars($param);
        $user['cars'] = [];
        if (!empty($cars) && !empty($cars['data'])) {
            $user['cars'] = $cars['data'];
        }

        return [
            'success'   => true,
            'message'   => "User data extracted successfully",
            'data'      => $user
        ];
    }

    public function getAll() : array
    {
        $response = [
            'success' => false,
            'message' => "No users exist"
        ];

        $users = $this->userRepository->getAllUsers();
        if (empty($users)) {
            return $response;
        }

        foreach ($users as $i => $user) {
            unset($users[$i]['password']);
        }

        return [
            'success'   => true,
            'message'   => "User list successfully generated.",
            'data'      => $users
        ];
    }

    public function getAllDetail() : array
    {
        $userData = $this->getAll();
        if (!$userData['success']) {
            return $userData;
        }

        $users = $userData['data'];

        foreach ($users as $i => $user) {
            unset($users[$i]['password']);
            $cars = $this->getUserCars($user['id']);
            $users[$i]['cars'] = [];
            if (!empty($cars) && !empty($cars['data'])) {
                $users[$i]['cars'] = $cars['data'];
            }
        }

        return [
            'success'   => true,
            'message'   => "Detailed user list successfully generated",
            'data'      => $users
        ];
    }

    public function checkCreateUser($data) : array
    {
        $response = [
            "success" => false,
            "message" => "Missing Data"
        ];
        if (
            empty($data['username']) ||
            empty($data['password']) ||
            empty($data['email']) ||
            empty($data['currency'])
        ) {
            return $response;
        }

        if (strlen($data['password']) < 6) {
            $response['message'] = "Password is too short";
            return $response;
        }

        $existing = $this->userRepository->getByUsername($data['username']);
        if (!empty($existing)) {
            $response['message'] = "Username is already in use";
            return $response;
        }

        $existing = $this->userRepository->getByEmail($data['email']);
        if (!empty($existing)) {
            $response['message'] = "Email is already in use";
            return $response;
        }

        $user = new User();
        $user->setUsername($data['username']);
        $user->setPassword(password_hash($data['password'], PASSWORD_DEFAULT));
        $user->setEmail($data['email']);
        $user->setCurrency($data['currency']);

        if (!empty($data['firstName'])) {
            $user->setFirstName($data['firstName']);
        }
        if (!empty($data['lastName'])) {
            $user->setLastName($data['lastName']);
        }
        if (!empty($data['notes'])) {
            $user->setNotes($data['notes']);
        }
        if (!empty($data['gender'])) {
            $user->setGender($data['gender']);
        }

        $this->userRepository->add($user, true);
        $userId = $user->getId();
        $user = $this->userRepository->getById($userId);
        $success = false;
        $data = [];
        if (count($user) > 0) {
            $success = true;
            $data = $user[0];
            unset($data['password']);
        }


        return [
            'success'   => $success,
            'message'   => $success ? "User successfully created user" : "Error creating user",
            'data'      => $data
        ];
    }

    public function getUserCars($userParam) : array
    {
        $response = [
            'success' => false,
            'message' => "Empty data"
        ];

        if (empty($userParam)) {
            return $response;
        }

        if (is_numeric($userParam)) {
            $user = $this->userRepository->getById($userParam);
        } else {
            $user = $this->userRepository->getByUsername($userParam);
        }

        if (empty($user)) {
            $response['message'] = "No such user exists";
            return $response;
        }

        $user = $user[0];

        $cars = $this->carRepository->getByUserId($user['id']);

        if (empty($cars)) {
            $response['message'] = "User has no cars";
            return $response;
        }

        foreach ($cars as $i => $car) {
            $fuel = $this->carFuelsRepository->getCarFuels($car['id']);
            if (empty($fuel)) {
                continue;
            }

            $cars[$i]['fuel'] = $fuel;
        }

        return [
            'success'   => true,
            'message'   => "Data extracted successfully",
            'data'      => $cars
        ];
    }

    public function checkEditUser($param, $userData) : array
    {
        $response = [
            'success' => false,
            'message' => 'User does not exist'
        ];

        if (empty($param)) {
            return $response;
        }

        if (is_numeric($param)) {
            $user = $this->userRepository->find($param);
        } else {
            $user = $this->userRepository->findByUsername($param);
        }

        if (empty($user)) {
            return $response;
        }
        $isChanged = false;

        if (!empty($userData['username']) && $user->getUsername() !== $userData['username']) {
            $user->setUsername($userData['username']);
            $isChanged = true;
        }
        $loginResponse = $this->login($userData);
        if (!empty($userData['password']) && !$loginResponse['success']) {
            $user->setPassword(password_hash($userData['password'], PASSWORD_DEFAULT));
            $isChanged = true;
        }
        if (!empty($userData['firstName']) && $user->getFirstName() !== $userData['firstName']) {
            $user->setFirstName($userData['firstName']);
            $isChanged = true;
        }
        if (!empty($userData['lastName']) && $user->getLastName() !== $userData['lastName']) {
            $user->setLastName($userData['lastName']);
            $isChanged = true;
        }
        if (!empty($userData['email']) && $user->getEmail() !== $userData['email']) {
            $user->setEmail($userData['email']);
            $isChanged = true;
        }
        if (!empty($userData['notes']) && $user->getNotes() !== $userData['notes']) {
            $user->setNotes($userData['notes']);
            $isChanged = true;
        }

        if ($isChanged) {
            $this->userRepository->edit($user, true);
            return [
                'success' => true,
                'message' => $user->getUsername() . " has been successfully modified",
            ];
        }

        $response['message'] = "There is no actual change in the user";
        return $response;
    }

    public function login(array $userData) : array
    {
        $response = [
            'success' => false,
            'message' => 'Incomplete data'
        ];
        if (empty($userData['password']) || empty($userData['username'])) {
            return $response;
        }

        $username = $userData['username'];
        $password = $userData['password'];

        $user = $this->getUserDetails($username, false);
        if (!$user['success']) {
            $response['message'] = "User does not exist";
            return $response;
        }

        $user = $user['data'];

        if (password_verify($password, $user['password'])) {
            unset($user['password']);
            return [
                'success'   => true,
                'message'   => "User login successful",
                'user'      => $user
            ];
        }

        $response['message'] = "Username or password incorrect";
        return $response;
    }

    public function checkDeleteUser($param) : array
    {
        $response = [
            'success' => false,
            'message' => "Missing data"
        ];
        if (empty($param)) {
            return $response;
        }

        if (is_numeric($param)) {
            $user = $this->userRepository->find($param);
        } else {
            $user = $this->userRepository->findByUsername($param);
        }
        if (empty($user)) {
            $response['message'] = "No such user exist";
            return $response;
        }

        $userId = $user->getId();

        $userCars = $this->carRepository->getByUserId($userId);
        if (!empty($userCars)) {
            foreach ($userCars as $car) {
                $this->carHelper->checkDeleteCar($car['id']);
            }
            $this->entityManager->flush();
        }

        try {
            $this->userRepository->remove($user, true);
        } catch (Exception $e) {
            $response['message'] = "Something went wrong";
            return $response;
        }

        return [
            'success'   => true,
            'message'   => "User successfully deleted",
            'data'      => ['userId' => $userId]
        ];
    }
}