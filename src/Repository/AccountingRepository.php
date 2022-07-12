<?php

namespace App\Repository;

use App\Entity\Accounting;
use App\Entity\User;
use App\Entity\Brand;
use App\Entity\Status;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Accounting>
 *
 * @method Accounting|null find($id, $lockMode = null, $lockVersion = null)
 * @method Accounting|null findOneBy(array $criteria, array $orderBy = null)
 * @method Accounting[]    findAll()
 * @method Accounting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Accounting::class);
    }

    public function add(Accounting $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Accounting $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
    * @return Accounting[] Returns an array of Accounting objects
    */
    public function userTypeFindBy($userType, $params): array
    {
        $query = $this->createQueryBuilder('a')->from(User::class, "u")->from(Brand::class, "b")->from(Status::class, "s");

		$query->Where('a.user = u')->andWhere('u.brand = b')->andWhere('a.status = s');

        // si selection suivant un utilisateur
        if(isset($params["manager"])) $query->andWhere('a.user = :manager')->setParameter('manager', $params["manager"]);

        // si rechercher selon status
        if(isset($params["status"])) $query->andWhere('a.status = :status')->setParameter('status', $params["status"]);

        // si rechercher selon brand
        if(isset($params["brand"])) $query->andWhere('b = :brand')->setParameter('brand', $params["brand"]);

        switch ($userType) {
            case 1:
                // si this user est un manager de compte
                $query->andWhere('u.accountManager = :account')
                    ->setParameter('account', $params["managerby"]);
                break;

            case 2:
                // si this user est un revender ou son Affilié
                $query->andWhere('b.manager = :reseller')
                    ->setParameter('reseller', $params["reselby"]);
                break;

            case 3:
                // si this user est un revender ou son Affilié
                $query->andWhere('b.manager = :reseller')
                    ->setParameter('reseller', $params["reselby"]);
                break;

            case 4:
                // si this user est un utilisateur simple ou son Affilié
                $query->andWhere('a.user = :user')
                    ->setParameter('user', $params["user"]);
                break;

            case 5:
                // si this user est un utilisateur simple ou son Affilié
                $query->andWhere('a.user = :user')
                    ->setParameter('user', $params["user"]);
                break;
            default:
                // si type de this user n'est pas défini données vide
                if(!isset($params["master"])) $query->andWhere('a.user is null');
                break;
        }

        //dd($query->orderBy('s.id', 'ASC')->getQuery());

        return $query->orderBy('a.id', 'ASC')
           ->getQuery()
           ->getResult()
       ;
    }

//    /**
//     * @return Accounting[] Returns an array of Accounting objects
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

//    public function findOneBySomeField($value): ?Accounting
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
