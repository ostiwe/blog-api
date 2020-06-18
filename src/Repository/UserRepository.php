<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, User::class);
	}

	public function paginate(int $page = 1, int $limit = 5)
	{
		if ($page <= 0) $page = 1;
		if ($limit < 5) $limit = 5;

		$offset = ($page * $limit) - $limit;

		$sql = "SELECT u FROM App\Entity\User u";
		$query = $this->_em->createQuery($sql)
			->setFirstResult($offset)
			->setMaxResults($limit);
		$res = [];
		$paginator = new Paginator($query, $fetchJoinCollection = true);

		/** @var User $item */
		foreach ($paginator as $item) {
			$res[] = $item->export();
		}

		return $res;
	}

	public function getCount()
	{
		$sql = "SELECT COUNT(u) FROM App\Entity\User u";
		return $this->_em->createQuery($sql)->execute()[0][1];
	}

	// /**
	//  * @return User[] Returns an array of User objects
	//  */
	/*
	public function findByExampleField($value)
	{
		return $this->createQueryBuilder('u')
			->andWhere('u.exampleField = :val')
			->setParameter('val', $value)
			->orderBy('u.id', 'ASC')
			->setMaxResults(10)
			->getQuery()
			->getResult()
		;
	}
	*/

	/*
	public function findOneBySomeField($value): ?User
	{
		return $this->createQueryBuilder('u')
			->andWhere('u.exampleField = :val')
			->setParameter('val', $value)
			->getQuery()
			->getOneOrNullResult()
		;
	}
	*/
}
