<?php

namespace App\Repository;

use App\Entity\Sender;
use App\Entity\User;
use App\Entity\Brand;
use App\Entity\Status;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sender>
 *
 * @method Sender|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sender|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sender[]    findAll()
 * @method Sender[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SenderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sender::class);
    }

    public function add(Sender $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Sender $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
    * @return Sender[] Returns an array of Sender objects
    */
    public function userTypeFindBy($userType, $params): array
    {
        $query = $this->createQueryBuilder('s')->from(User::class, "u")->from(Brand::class, "b")->from(Status::class, "t");

        // si selection suivant un utilisateur
        if(isset($params["manager"])) $query->andWhere('s.manager = :manager')->setParameter('manager', $params["manager"]);

        // si rechercher selon status
        if(isset($params["status"])) $query->andWhere('s.status = :status')->setParameter('status', $params["status"]);

        // si rechercher selon brand
        if(isset($params["brand"])) $query->andWhere('b = :brand')->setParameter('brand', $params["brand"]);

        switch ($userType) {
            case 1:
                // si this user est un manager de compte
                $query->andWhere('s.manager = u')
                    ->andWhere('u.accountManager = :account')
                    ->setParameter('account', $params["managerby"]);
                break;

            case 2:
                // si this user est un revender ou son Affilié
                $query->andWhere('s.manager = u')
                    ->andWhere('u.brand = b')
                    ->andWhere('b.manager = :reseller')
                    ->setParameter('reseller', $params["reselby"]);
                break;

            case 3:
                // si this user est un revender ou son Affilié
                $query->andWhere('s.manager = u')
                    ->andWhere('u.brand = b')
                    ->andWhere('b.manager = :reseller')
                    ->setParameter('reseller', $params["reselby"]);
                break;

            case 4:
                // si this user est un utilisateur simple ou son Affilié
                $query->andWhere('s.manager = :user')
                    ->setParameter('user', $params["user"]);
                break;

            case 5:
                // si this user est un utilisateur simple ou son Affilié
                $query->andWhere('s.manager = :user')
                    ->setParameter('user', $params["user"]);
                break;
            default:
                // si type de this user n'est pas défini données vide
                if(!isset($params["master"])) $query->andWhere('s.manager = null');
                break;
        }

        //dd($query->orderBy('s.id', 'ASC')->getQuery());

        return $query->orderBy('s.id', 'ASC')
           ->getQuery()
           ->getResult()
       ;
    }

//    /**
//     * @return Sender[] Returns an array of Sender objects
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

//    public function findOneBySomeField($value): ?Sender
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
