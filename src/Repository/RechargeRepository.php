<?php

namespace App\Repository;

use App\Entity\Recharge;
use App\Entity\User;
use App\Entity\Brand;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Recharge>
 *
 * @method Recharge|null find($id, $lockMode = null, $lockVersion = null)
 * @method Recharge|null findOneBy(array $criteria, array $orderBy = null)
 * @method Recharge[]    findAll()
 * @method Recharge[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RechargeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recharge::class);
    }

    public function add(Recharge $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Recharge $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //GET Recharge By or Reseller or Brand
    public function getRechargeBy($idManager = NULL, $idReseller = NULL, $idBrand = NULL){
        $q = $this->createQueryBuilder('r')->from(User::class, "u")->from(Brand::class, "b");
        if($idManager)  $q->andWhere('r.user = u.id')->andWhere('u.accountManager = :id')->setParameter('id', $idManager);
        if($idReseller) $q->andWhere('r.user = u.id')->andWhere('u.brand = b.id')->andWhere('b.manager = :id')->setParameter('id', $idReseller);
        if($idBrand)    $q->andWhere('r.user = u.id')->andWhere('u.brand = b.id')->andWhere('b.brand = :id')->setParameter('id', $idBrand);
        return $q->orderBy('r.id', 'DESC')->getQuery()->getResult();
    }
    //  //Manager id
    //  public function getRechargeByManager($idManager){
    //     return
    //     $this->createQueryBuilder('r')
    //     ->from(User::class, "u")
    //     ->andWhere('r.user = u.id')
    //     ->andWhere('u.accountManager = :id')
    //     ->setParameter('id', $idManager)->orderBy('r.id', 'DESC')->getQuery()->getResult();
    // }

    // //ReselleR id
    // public function getRechargeByReseller($idReseller){
    //     return $this->createQueryBuilder('r')->from(User::class, "u")->from(Brand::class, "b")->andWhere('r.user = u.id')->andWhere('u.brand = b.id')
    //     ->andWhere('b.manager = :id')->setParameter('id', $idReseller)->orderBy('r.id', 'DESC')->getQuery()->getResult();
    // }
    // //Getby
    // public function getRechargeByBrand($idBrand){
    //     return $this->createQueryBuilder('r')->from(User::class, "u")->from(Brand::class, "b")->andWhere('r.user = u.id')->andWhere('u.brand = b.id')
    //     ->andWhere('b.brand = :id')->setParameter('id', $idReseller)->orderBy('r.id', 'DESC')->getQuery()->getResult();
    // }
//    /**
//     * @return Recharge[] Returns an array of Recharge objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Recharge
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
