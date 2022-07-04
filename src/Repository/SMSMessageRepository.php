<?php

namespace App\Repository;

use App\Entity\SMSMessage;
use App\Entity\User;
use App\Entity\Brand;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SMSMessage>
 *
 * @method SMSMessage|null find($id, $lockMode = null, $lockVersion = null)
 * @method SMSMessage|null findOneBy(array $criteria, array $orderBy = null)
 * @method SMSMessage[]    findAll()
 * @method SMSMessage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SMSMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SMSMessage::class);
    }

    public function add(SMSMessage $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SMSMessage $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
    * @return SMSMessage[] Returns an array of SMSMessage objects
    */
    public function userTypeFindBy($userType, $params): array
    {
        $query = $this->createQueryBuilder('m')->from(User::class, "u")->from(Brand::class, "b");//->from(SMSCampaign::class, "c");
        $query->andWhere('m.manager = u')->andWhere('u.brand = b');

        // si selection suivant un utilisateur
        if(isset($params["manager"])) $query->andWhere('m.manager = :manager')->setParameter('manager', $params["manager"]);

        // si rechercher selon status
        if(isset($params["status"])) $query->andWhere('m.status = :status')->setParameter('status', $params["status"]);

        // si rechercher selon brand
        if(isset($params["brand"])) $query->andWhere('u.brand = :brand')->setParameter('brand', $params["brand"]);

        // si rechercher selon sender
        if(isset($params["sender"])) $query->andWhere('m.sender = :sender')->setParameter('sender', $params["sender"]);

        // si recherche selon campagne
        if(isset($params["from"])) $query->andWhere('m.createFrom = :from')->setParameter('from', $params["from"]);

        // si recherche selon campagne
        if(isset($params["from"])) $query->andWhere('m.createFrom = :from')->setParameter('from', $params["from"]);

        if(isset($params["campaign"]) && $params["from"] == "campaign"){
            $query->from(SMSCampaign::class, "c")->andWhere('m.campaign = :campaign')->setParameter('campaign', $params["campaign"]);
        }

        $query->andWhere('m.sendingAt > :send')->setParameter('send', $params["lastday"]);

        switch ($userType) {
            case 1:
                // si this user est un manager de compte
                $query->andWhere('u.accountManager = :account')
                    ->setParameter('account', $params["managerby"]);
                break;

            case 2:
                // si this user est un revender
                $query->andWhere('b.manager = :reseller')
                    ->setParameter('reseller', $params["reselby"]);
                break;

            case 3:
                // si this user est un Affilié de revender
                $query->andWhere('b.manager = :reseller')
                    ->setParameter('reseller', $params["reselby"]);
                break;

            case 4:
                // si this user est un utilisateur simple
                $query->andWhere('m.manager = :user')
                    ->setParameter('user', $params["user"]);
                break;

            case 5:
                // si this user est un Affilié d'un utilisateur simple
                $query->andWhere('m.manager = :user')
                    ->setParameter('user', $params["user"]);
                break;
            default:
                // si type de this user n'est pas défini données vide
                if(!isset($params["master"])) $query->andWhere('m.manager is null');
                break;
        }

        //dd($query->orderBy('s.id', 'ASC')->getQuery());

        return $query->orderBy('m.id', 'ASC')
           ->getQuery()
           ->getResult()
       ;
    }

//    /**
//     * @return SMSMessage[] Returns an array of SMSMessage objects
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

//    public function findOneBySomeField($value): ?SMSMessage
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
