<?php

namespace App\Repository;

use App\Entity\SMSCampaign;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SMSCampaign>
 *
 * @method SMSCampaign|null find($id, $lockMode = null, $lockVersion = null)
 * @method SMSCampaign|null findOneBy(array $criteria, array $orderBy = null)
 * @method SMSCampaign[]    findAll()
 * @method SMSCampaign[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SMSCampaignRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SMSCampaign::class);
    }

    public function add(SMSCampaign $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SMSCampaign $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return SMSCampaign[] Returns an array of SMSCampaign objects
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

//    public function findOneBySomeField($value): ?SMSCampaign
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
