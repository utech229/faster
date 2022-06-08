<?php

namespace App\Repository;

use App\Entity\ExtraAuthorisation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExtraAuthorisation>
 *
 * @method ExtraAuthorisation|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExtraAuthorisation|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExtraAuthorisation[]    findAll()
 * @method ExtraAuthorisation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExtraAuthorisationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExtraAuthorisation::class);
    }

    public function add(ExtraAuthorisation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ExtraAuthorisation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return ExtraAuthorisation[] Returns an array of ExtraAuthorisation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ExtraAuthorisation
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
