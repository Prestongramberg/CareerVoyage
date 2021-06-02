<?php

namespace App\EntityExtend\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;

/**
 * Custom Entity Extend Queries can be written here or you can also define
 * your own Repository Classes manually in the main Repository Folder as well.
 *
 * Class EntityExtendRepository
 * @package App\EntityExtend\Repository
 */
class EntityExtendRepository
{
    /**
     * @var ManagerRegistry $doctrine
     */
    private $doctrine;

    /**
     * WelcomeController constructor.
     * @param ManagerRegistry $doctrine
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @param $entityClass
     * @return ObjectRepository|null
     */
    public function getRepository($entityClass)
    {
        $manager = $this->doctrine->getManagerForClass($entityClass);

        if(!$manager) {
            return null;
        }

        return $manager->getRepository($entityClass);
    }

    /**
     * @param $entityClass
     * @return mixed
     */
    public function getQueryBuilder($entityClass)
    {
        $repository = $this->getRepository($entityClass);

        if(!$repository) {
            return null;
        }

        return $repository->createQueryBuilder($entityClass);
    }
}