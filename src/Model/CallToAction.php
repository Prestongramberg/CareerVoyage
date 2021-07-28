<?php

namespace App\Model;

use App\Entity\ProfessionalUser;
use App\Entity\User;

class CallToAction
{



    public function get(User $user) {

        if($user instanceof ProfessionalUser) {

            return [







            ];
        }











    }
}