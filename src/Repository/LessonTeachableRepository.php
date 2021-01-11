<?php

namespace App\Repository;

use App\Entity\Lesson;
use App\Entity\LessonTeachable;
use App\Entity\Region;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method LessonTeachable|null find($id, $lockMode = null, $lockVersion = null)
 * @method LessonTeachable|null findOneBy(array $criteria, array $orderBy = null)
 * @method LessonTeachable[]    findAll()
 * @method LessonTeachable[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LessonTeachableRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, LessonTeachable::class);
    }

    // /**
    //  * @return LessonTeachable[] Returns an array of LessonTeachable objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?LessonTeachable
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */


    /**
     * @param $lesson []
     *
     * @return mixed
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findByEducatorRequestors($lesson)
    {
        $query = sprintf("SELECT l.id FROM lesson l, lesson_teachable lt, educator_user e, user u WHERE l.id = %s AND lt.lesson_id = l.id AND lt.user_id = e.id AND e.id = u.id AND u.activated = %s", $lesson['lesson'], true);

        $em   = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $total = $stmt->fetchAll();

        if (sizeof($total) > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $lesson []
     *
     * @return mixed
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findByExpertPresenters($lesson)
    {
        $query = sprintf("SELECT l.id FROM lesson l, lesson_teachable lt, professional_user p, user u WHERE l.id = %s AND lt.lesson_id = l.id AND lt.user_id = p.id AND p.id = u.id AND u.activated = %s", $lesson['lesson'], true);

        $em   = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $total = $stmt->fetchAll();

        if (sizeof($total) > 0) {
            return true;
        } else {
            return false;
        }
    }

}
