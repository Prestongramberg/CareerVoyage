<?php

namespace App\Repository;

use App\Entity\Lesson;
use App\Entity\SecondaryIndustry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Lesson|null find($id, $lockMode = null, $lockVersion = null)
 * @method Lesson|null findOneBy(array $criteria, array $orderBy = null)
 * @method Lesson[]    findAll()
 * @method Lesson[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LessonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lesson::class);
    }

    public function findAll(array $orderBy = null)
    {
        return $this->findBy(array(), $orderBy);
    }

    // /**
    //  * @return Lesson[] Returns an array of Lesson objects
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
    public function findOneBySomeField($value): ?Lesson
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
     * @param SecondaryIndustry $secondaryIndustries []
     * @param int               $limit
     *
     * @return mixed
     * @throws \Doctrine\DBAL\DBALException*@throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function findBySecondaryIndustries($secondaryIndustries, $limit = 6) {

        $whereClause = [];
        foreach($secondaryIndustries as $secondaryIndustry) {
            $whereClause[] = sprintf("secondary_industry_id = %s", $secondaryIndustry->getId());
        }

        $whereClause = !empty($whereClause) ? sprintf('(%s)', implode(" OR ", $whereClause)) : '';

        $query = sprintf("select l.id, l.title, l.short_description from lesson l inner join lesson_secondary_industry lsi on l.id = lsi.lesson_id WHERE l.created_at >= DATE(NOW()) - INTERVAL 7 DAY AND deleted=0 AND %s GROUP BY l.id order by l.created_at DESC LIMIT %s", $whereClause, $limit);

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @param int $days
     *
     * @return mixed
     * @throws \Doctrine\DBAL\DBALException*@throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function findAllLessonsFromPastDays($days = 7) {
        $query = sprintf("select l.id, l.title, l.short_description from lesson l WHERE l.created_at >= DATE(NOW()) - INTERVAL %d DAY AND deleted=0 GROUP BY l.id order by l.created_at DESC", $days);

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

}
