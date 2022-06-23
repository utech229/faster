<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Brand;
use App\Entity\Permission;
use App\Entity\Authorization;
use App\Entity\Role;
use App\Entity\Status;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function add(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);

        $this->add($user, true);
    }

        ///////////////////////////////////////////////////////start/////////////////////////////////////////////////////////////

    // returns user by country code
    /**
     * Summary of findUsers
     * @return User[] Return an of User objects
     */
    public function findUserByCountryCode($code) {
        $query = $this->getEntityManager()->createQuery(
            "SELECT u
            FROM App\Entity\User u
            WHERE u.country LIKE '%:\"$code\";%'"
        );

        // return an of Product objects
        return $query->getResult();
    }

    // Liste des utilisateurs n'ayant pas le status donné en paramètre
    public function findAllUserNoStatus($status)
    {
        return $this->createQueryBuilder('u')
            ->Where('u.status != :status ')
            ->setParameter('status', $status)
            ->orderBy('u.createdAt','DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    // Retourne l'utilisateur ayant le mail donné en paramètre et ayant un statut inférieur de celui en paramètre
    public function findUserByDatas($status,$email )
    {
        return $this->createQueryBuilder('u')
            ->Where('u.status != :status AND u.email = :email ')
            ->setParameter('status', $status)
            ->setParameter('email', $email)
            ->getQuery()
            ->getResult()
        ;
    }

    // Retourne l'utilisateur ayant le mail donné en paramètre et ayant un statut différent de celui en paramètre
    public function findUserNoDeletes($status,$email )
    {
        return $this->createQueryBuilder('u')
            ->Where('u.status != :status AND u.email = :email ')
            ->setParameter('status', $status)
            ->setParameter('email', $email)
            ->getQuery()
            ->getResult()
        ;
    }


    public function getBalance($uid = ""): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT SUM(u.balance) balance FROM User u WHERE :uid = '' OR (:uid <> '' AND u.uid = :uid)";

        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery(['uid' => $uid]);

        // returns an array of arrays (i.e. a raw data set)
        return $resultSet->fetchAllAssociative();
    }

    //User counter function
    public function countAllConnexionForToday()
    {
        $date = date('Y-m-d');
        return $this->createQueryBuilder('m')
        ->Where("m.lastLoginAt >=:lastLoginAt")
        ->setParameter('lastLoginAt', new \DatetimeImmutable(($date." 00:00:00")))
        ->select('COUNT(m.id)')
        ->getQuery()
        ->getResult()
        ;
    }


    // Retourne les utilisateur ayant un level de role inférieur à celui de l'utilisteur actuel
    public function findUserByLevel($level, $brand = null)
    {
        $qb = $this->createQueryBuilder('u');
        $qb->from(Role::class, 'r')
            ->Where('u.role = r')
            ->andWhere('r.level < :level')
            ->setParameter('level', $level);

        if ($brand) {
            $qb->from(Brand::class, 'b')
            ->andWhere('u.brand = :brand')
            ->setParameter('brand', $brand);
        };

        $query = $qb->getQuery();
        return $query->getResult();
    }

    // Récupérer un utilisateur à partir de son id de transaction au vérification du N° Tel
    /*public function getUserByPaiementCode($id, $creator): ?User
    {
        return $this->createQueryBuilder('u')
            ->where('u.accountPayment LIKE :idTransaction')
            ->andWhere('u.accountPayment LIKE :creator')
            ->setParameter('idTransaction', $id)
            ->setParameter('creator', $creator)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }*/

    // Récupérer tous utilisateurs sur créateur de la transaction au vérification du N° Tel
    /**
     * @return User[] Returns an array of User objects
     */
    public function getUsersByPaiementCode($creator)
    {
        return $this->createQueryBuilder('u')
            ->where("u.accountPayment NOT LIKE '%s:13:\"idTransaction\";s:0:\"\";%'")
            ->andWhere('u.accountPayment LIKE :creator')
            ->setParameter('creator', $creator)
            ->getQuery()
            ->getResult()
        ;
    }

    // Récupérer les utilisateurs à partir du code de permission avec ou sans son status et ou id du promoteur
    /**
    * @return User[] Returns an array of User objects
    */
    public function getUsersByPermission($codePermission, $userType = null, $user = null, $level = null, $status = null)
    {
        $qb = $this->createQueryBuilder('u');

        $qb->from(Permission::class, "p")
            ->from(Role::class, "r")
            ->from(Authorization::class, "a")
            ->from(Status::class, "s")
            ->from(Brand::class, "b")
            ->where('s.code = 3')
            ->andWhere('u.role = r')
            ->andWhere('a.role = r')
            ->andWhere('a.permission = p')
            ->andWhere('p.status = s')
            ->andWhere('r.status = s')
            ->andWhere('a.status = s')
            ->andWhere('u.brand = b');

        if($codePermission != "") {
            $qb->andwhere("p.code = :code")
                ->setParameter('code', $codePermission);
        }

        switch ($userType) {
            case 1: $qb->andWhere('u.accountManager = :manager')->setParameter('manager', $user); break;
            case 2: $qb->andWhere('b.manager = :manager')->setParameter('manager', $user); break;
            case 3: $qb->andWhere('b.manager = :manager')->setParameter('manager', $user); break;
            case 4: $qb->andWhere('u.affiliateManager = :manager')->setParameter('manager', $user); break;
            case 5: $qb->andWhere('u.affiliateManager = :manager')->setParameter('manager', $user); break;
            default: break;
        }

        if($level){
            switch ($level) {
                case 1: $qb->andWhere('u.affiliateManager is NULL'); break;
                case 2: $qb->andWhere('u.affiliateManager is not NULL'); break;
                default: break;
            }
        }
        
        $query = $qb->getQuery();
        //dd($query->execute());
        return $query->execute();
    }

//    /**
//     * @return User[] Returns an array of User objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?User
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
