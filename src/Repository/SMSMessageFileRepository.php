<?php

namespace App\Repository;

use App\Entity\SMSMessageFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SMSMessageFile>
 *
 * @method SMSMessageFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method SMSMessageFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method SMSMessageFile[]    findAll()
 * @method SMSMessageFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SMSMessageFileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SMSMessageFile::class);
    }

    public function add(SMSMessageFile $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SMSMessageFile $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return SMSMessageFile[] Returns an array of SMSMessageFile objects
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

//    public function findOneBySomeField($value): ?SMSMessageFile
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
