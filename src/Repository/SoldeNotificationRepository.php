<?php

namespace App\Repository;

use App\Entity\SoldeNotification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SoldeNotification>
 *
 * @method SoldeNotification|null find($id, $lockMode = null, $lockVersion = null)
 * @method SoldeNotification|null findOneBy(array $criteria, array $orderBy = null)
 * @method SoldeNotification[]    findAll()
 * @method SoldeNotification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SoldeNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SoldeNotification::class);
    }

    public function add(SoldeNotification $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SoldeNotification $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return SoldeNotification[] Returns an array of SoldeNotification objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?SoldeNotification
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
