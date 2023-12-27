<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserHelper
{
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;


    public function __construct(UserRepository $userRepo, EntityManagerInterface $entityManager)
    {
        $this->userRepository = $userRepo;
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
            'id'        => $userId
        ];
    }

    public function checkEditUser($userId, $userData) : array
    {
        //TODO: implement
    }
}