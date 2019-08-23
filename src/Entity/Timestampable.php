<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trait Timestampable
 * @package App\Entity
 */
trait Timestampable
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $updatedAt;

    /**
     * Sets createdAt.
     *
     * @ORM\PrePersist()
     * @return $this
     * @throws \Exception
     */
    public function setCreatedAt()
    {
        if(!$this->createdAt) {
            $this->createdAt = new \DateTime();
        }

        return $this;
    }

    /**
     * Returns createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Sets updatedAt
     *
     * @ORM\PreUpdate()
     * @return $this
     * @throws \Exception
     */
    public function setUpdatedAt()
    {
        $this->updatedAt = new \DateTime();

        return $this;
    }

    /**
     * Returns updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}