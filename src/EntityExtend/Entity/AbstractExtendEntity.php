<?php

namespace App\EntityExtend\Entity;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Doctrine\ORM\Mapping as ORM;

/**
 * Provides helper functions to access properties on extended entities
 *
 * Class AbstractExtendEntity
 * @package App\EntityExtend\Entity
 *
 * @ORM\MappedSuperclass
 */
abstract class AbstractExtendEntity
{

    /**
     * @var int
     */
    protected $id;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function get($id) {

        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        return $propertyAccessor->getValue($this, "field_{$id}");
    }
}