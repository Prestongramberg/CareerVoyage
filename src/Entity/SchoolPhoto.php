<?php

namespace App\Entity;

use App\Service\UploaderHelper;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SchoolPhotoRepository")
 */
class SchoolPhoto extends Image
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\School", inversedBy="schoolPhotos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $school;

    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function setSchool(?School $school): self
    {
        $this->school = $school;

        return $this;
    }

    public function getPath()
    {
        return UploaderHelper::SCHOOL_PHOTO.'/'.$this->getFileName();
    }
}
