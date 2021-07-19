<?php

namespace App\Form\DataTransformer;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class UserIdToEntityTransformer implements DataTransformerInterface
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * UserIdToEntityTransformer constructor.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Transforms an object (issue) to a string (number).
     *
     * @param  User|null $issue
     *
     * @return array
     */
    public function transform($users)
    {
        if(empty($users) || $users->count() === 0) {
            return [];
        }

        $usersArray = [];
        foreach($users as $user) {
            $usersArray[] = $user;
        }

        return $usersArray;
    }

    /**
     * @param $users
     *
     * @return User[]
     */
    public function reverseTransform($users)
    {
        if (empty($users)) {
            return [];
        }

        return $users;

        /*return $this->userRepository->findBy([
            'id' => $userIds
        ]);*/
    }
}