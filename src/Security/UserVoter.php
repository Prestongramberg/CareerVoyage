<?php

namespace App\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\RoleVoter;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserInterface;

class UserVoter extends RoleVoter
{
    protected function extractRoles(TokenInterface $token)
    {
        $user = $token->getUser();
        return $user instanceof UserInterface ? array_map(function(string $role)
        {
            return $role;
        }, $user->getRoles()) : [];
    }
}