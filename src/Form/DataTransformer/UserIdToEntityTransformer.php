<?php

namespace App\Form\DataTransformer;

use App\Entity\User;
use Symfony\Component\Form\DataTransformerInterface;

class UserIdToEntityTransformer implements DataTransformerInterface
{

    /**
     * Transforms an object (issue) to a string (number).
     *
     * @param $users
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
    }
}