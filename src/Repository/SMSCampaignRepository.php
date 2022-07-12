<?php

namespace App\Repository;

use App\Entity\SMSCampaign;
use App\Entity\User;
use App\Entity\Brand;
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

	/**
	* @return SMSCampaign[] Returns an array of SMSCampaign objects
	*/
	public function userTypeFindBy($userType, $params): array
	{
		$query = $this->createQueryBuilder('c')->from(User::class, "u")->from(Brand::class, "b");
		$query->andWhere('c.manager = u')->andWhere('u.brand = b');

		// si selection suivant un utilisateur
		if(isset($params["manager"])) $query->andWhere('c.manager = :manager')->setParameter('manager', $params["manager"]);

		// si rechercher selon status
		if(isset($params["status"])){
			if(is_array($params["status"])){
				$conditions = "";
				foreach ($params["status"] as $status) {
					if($conditions != "") $conditions .= ' or ';
					$conditions .= 'c.status = '.$status;
				}
				$query->andWhere($conditions);
			}
			else $query->andWhere('c.status = :status')->setParameter('status', $params["status"]);
		}

		// si rechercher selon brand
		if(isset($params["brand"])) $query->andWhere('b = :brand')->setParameter('brand', $params["brand"]);

		// si rechercher selon sender
		if(isset($params["sender"])) $query->andWhere('c.sender = :sender')->setParameter('sender', $params["sender"]);

		$query->andWhere('c.createdAt > :create')->setParameter('create', $params["lastday"]);

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
				$query->andWhere('c.manager = :user')
					->setParameter('user', $params["user"]);
				break;

			case 5:
				// si this user est un Affilié d'un utilisateur simple
				$query->andWhere('c.manager = :user')
					->setParameter('user', $params["user"]);
				break;
			default:
				// si type de this user n'est pas défini données vide
				if(!isset($params["master"])) $query->andWhere('c.manager is null');
				break;
		}

		//dd($query->orderBy('s.id', 'ASC')->getQuery());

		return $query->orderBy('c.id', 'ASC')
		   ->getQuery()
		   ->getResult()
	   ;
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
