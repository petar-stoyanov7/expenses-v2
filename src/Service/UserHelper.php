<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\CarFuelsRepository;
use App\Repository\CarRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserHelper
{
    private UserRepository $userRepository;
    private CarRepository $carRepository;
    private CarFuelsRepository $carFuelsRepository;
    private EntityManagerInterface $entityManager;


    public function __construct(
        UserRepository $userRepo,
        CarRepository $carRepository,
        CarFuelsRepository $carFuelsRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->userRepository = $userRepo;
        $this->carRepository = $carRepository;
        $this->carFuelsRepository = $carFuelsRepository;
        $this->entityManager = $entityManager;
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
            empty($data['email'])
        ) {
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

        if (!empty($data['firstName'])) {
            $user->setFirstName($data['firstName']);
        }
        if (!empty($data['lastName'])) {
            $user->setLastName($data['lastName']);
        }
        if (!empty($data['notes'])) {
            $user->setNotes($data['notes']);
        }

        $this->userRepository->add($user, true);
        $userId = $user->getId();

        $success = !empty($userId);

        return [
            'success'   => $success,
            'message'   => $success ? "User successfully created user" : "Error creating user",
            'data'      => ['userId' => $userId]
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

        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $user = $this->userRepository->findByEmail($username);
        } else {
            $user = $this->userRepository->findByUsername($username);
        }

        if (empty($user)) {
            $response['message'] = "User does not exist";
            return $response;
        }

        if (password_verify($password, $user->getPassword())) {
            return [
                'success' => true,
                'message' => "User login successful"
            ];
        }

        $response['message'] = "Username or password incorrect";
        return $response;
    }
}