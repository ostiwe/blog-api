<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Post::class);
	}

	public function paginate(int $page = 1, int $limit = 5)
	{
		if ($page <= 0) $page = 1;
		if ($limit < 5) $limit = 5;

		$offset = ($page * $limit) - $limit;
		$time = time();
		$sql = "SELECT p FROM App\Entity\Post p WHERE p.published <= $time ORDER BY p.id DESC";
		$query = $this->_em->createQuery($sql)
			->setFirstResult($offset)
			->setMaxResults($limit);
		$res = [];
		$paginator = new Paginator($query, $fetchJoinCollection = true);

		/** @var Post $item */
		foreach ($paginator as $item) {
			$res[] = $item->export();
		}

		return [
			'page' => $page,
			'offset' => $offset,
			'limit' => $limit,
			'items' => $res,
		];
	}

	public function getCount()
	{
		$sql = "SELECT COUNT(p) FROM App\Entity\Post p";
		return $this->_em->createQuery($sql)->execute()[0][1];
	}

//	public function findByExampleField($param, $value, $page = 1, $limit = 5)
//	{
//		if ($page <= 0) $page = 1;
//		if ($limit < 5) $limit = 5;
//
//		$offset = ($page * $limit) - $limit;
//
//		$query = $this->createQueryBuilder('p')
//			->andWhere('p.:param = :val')
//			->setParameter('val', $value)
//			->setParameter('param', $param)
//			->orderBy('p.id', 'ASC')
//			->setFirstResult($offset)
//			->setMaxResults($limit);
//		$paginator = new Paginator($query, $fetchJoinCollection = true);
//		$res = [];
//		/** @var Post $item */
//		foreach ($paginator as $item) {
//			$res[] = $item->export();
//		}
//
//		return [
//			'page' => $page,
//			'offset' => $offset,
//			'limit' => $limit,
//			'items' => $res,
//		];
//	}


	/*
	public function findOneBySomeField($value): ?Post
	{
		return $this->createQueryBuilder('p')
			->andWhere('p.exampleField = :val')
			->setParameter('val', $value)
			->getQuery()
			->getOneOrNullResult()
		;
	}
	*/
}
