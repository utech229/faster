<?php

namespace App\Repository;

use App\Entity\Log;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Log>
 *
 * @method Log|null find($id, $lockMode = null, $lockVersion = null)
 * @method Log|null findOneBy(array $criteria, array $orderBy = null)
 * @method Log[]    findAll()
 * @method Log[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Log::class);
    }

    public function add(Log $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Log $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //Get all log count
    public function countAll()
    {
        return $this->createQueryBuilder('m')
        ->select('COUNT(m.id)')
        ->getQuery()
        ->getResult()
        ;
    }

    public function countAllForToday()
    {
        $date = date('Y-m-d');
        return $this->createQueryBuilder('m')
        ->Where("m.createdAt >=:createdAt")
        ->setParameter('createdAt', new \DatetimeImmutable(($date." 00:00:00")))
        ->select('COUNT(m.id)')
        ->getQuery()
        ->getResult()
        ;
    }

    public function countAllForMonth($startDate)
    {
        $date = date('Y-m-d');
        return $this->createQueryBuilder('m')
        ->Where("m.createdAt >=:startCreatedAt")
        ->andWhere("m.createdAt <=:createdAt")
        ->andWhere("m.task =:task")
        ->orWhere("m.task =:tasken")
        ->setParameter('startCreatedAt', new \DatetimeImmutable(($startDate." 00:00:00")))
        ->setParameter('createdAt', new \DatetimeImmutable(($date." 00:00:00")))
        ->setParameter('task', 'Connexion au compte')
        ->setParameter('tasken', 'Login to account')
        ->select('COUNT(m.id)')
        ->getQuery()
        ->getResult()
        ;
    }

    public function countAllConnexion()
    {
        return $this->createQueryBuilder('m')
        ->select('COUNT(m.id)')
        ->Where("m.task =:task")
        ->orWhere("m.task =:tasken")
        ->setParameter('task', 'Connexion au compte')
        ->setParameter('tasken', 'Login to account')
        ->getQuery()
        ->getResult()
        ;
    }

    
//    /**
//     * @return Log[] Returns an array of Log objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Log
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
