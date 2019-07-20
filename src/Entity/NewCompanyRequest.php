<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\NewCompanyRequestRepository")
 */
class NewCompanyRequest extends Request
{
}
