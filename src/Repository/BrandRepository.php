<?php

namespace App\Repository;

use App\Entity\Brand;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Brand>
 *
 * @method Brand|null find($id, $lockMode = null, $lockVersion = null)
 * @method Brand|null findOneBy(array $criteria, array $orderBy = null)
 * @method Brand[]    findAll()
 * @method Brand[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BrandRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Brand::class);
    }

    public function add(Brand $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Brand $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
    * @return Brand[] Returns an array of Sender objects
    */
    public function findBrandBy($userType, $params): array
    {
        $query = $this->createQueryBuilder('b')->from(User::class, "u");

        // si selection suivant un utilisateur
        if(isset($params["manager"])) $query->andWhere('b.manager = :manager')->setParameter('manager', $params["manager"]);

        switch ($userType) {
            case 1:
                // si this user est un manager de compte
                $query->andWhere('b.manager = u')
                    ->andWhere('u.accountManager = :account')
                    ->setParameter('account', $params["managerby"]);
                break;

            case 2:
                // si this user est un revender
                $query->andWhere('b.manager = :reseller')
                    ->setParameter('reseller', $params["reselby"]);
                break;

            case 3:
                // si this user est un Affilié d'un revender
                $query->andWhere('b.manager = :reseller')
                    ->setParameter('reseller', $params["reselby"]);
                break;

            case 4:
                // si this user est un utilisateur simple
                $query->andWhere('u.brand = b')
                    ->andWhere('u = :user')
                    ->setParameter('user', $params["user"]);
                break;

            case 5:
                // si this user est un Affilié d'un utilisateur simple
                $query->andWhere('u.brand = b')
                    ->andWhere('u = :user')
                    ->setParameter('user', $params["user"]);
                break;
            default:
                // si type de this user n'est pas défini données vide
                if(!isset($params["master"])) $query->andWhere('b.manager is null');
                break;
        }

        return $query->orderBy('b.id', 'ASC')
           ->getQuery()
           ->getResult()
       ;
    }

//    /**
//     * @return Brand[] Returns an array of Brand objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Brand
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
