<?php

namespace App\Repository;

use App\Entity\Authorization;
use App\Entity\Permission;
use App\Entity\Role;
use App\Entity\Status;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Authorization>
 *
 * @method Authorization|null find($id, $lockMode = null, $lockVersion = null)
 * @method Authorization|null findOneBy(array $criteria, array $orderBy = null)
 * @method Authorization[]    findAll()
 * @method Authorization[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuthorizationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Authorization::class);
    }

    public function add(Authorization $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Authorization $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
    * @return Authorization|null Return one or null Authorization objects
    */
    public function findByCodePermission($code, $user): array
    {
        return $this->createQueryBuilder('a')
            ->from('App\Entity\Permission', 'p')
            ->from('App\Entity\Role', 'r')
            ->from('App\Entity\Status', 's')
            ->from('App\Entity\User', 'u')
            ->where('u = :user')
            ->setParameter('user', $user)
            ->andWhere('u.role = r')
            ->andWhere('a.role = r')
            ->andWhere('a.permission = p')
            ->andWhere('a.status = s')
            ->andWhere('r.status = s')
            ->andWhere('p.status = s')
            ->andWhere('s.code = :status')
            ->setParameter('status', 3)
            ->andWhere('p.code = :code')
            ->setParameter('code', $code)
            ->orderBy('a.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

//    /**
//     * @return Authorization[] Returns an array of Authorization objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Authorization
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
