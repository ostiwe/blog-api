<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Tag|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tag|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tag[]    findAll()
 * @method Tag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TagRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Tag::class);
	}

	public function paginatePosts(int $tag = 1, int $page = 1, int $limit = 5)
	{
		if ($page <= 0) $page = 1;
		if ($limit < 5) $limit = 5;

		$offset = ($page * $limit) - $limit;

		$sql = "SELECT * FROM post_tag pt inner join post p on pt.post_id = p.id where pt.tag_id = $tag LIMIT $limit OFFSET $offset";
		$query = $this->_em->getConnection()->prepare($sql);
		$query->execute();
		$queryRes = $query->fetchAll();

		$res = [];
		foreach ($queryRes as $item) {
			$item['creator'] = $this->_em->getRepository(User::class)->find((int)$item['creator_id'])->export();
			$_tags = $this->_em->getRepository(Post::class)->find((int)$item['post_id'])->getTags();
			$tags = [];
			foreach ($_tags as $_tag) {
				$tags[] = $_tag->export();
			}
			$item['tags'] = $tags;
			unset($item['creator_id'], $item['post_id'], $item['tag_id']);
			$res[] = $item;
		}

		return [
			'page' => $page,
			'offset' => $offset,
			'limit' => $limit,
			'items' => $res,
		];
	}

	// /**
	//  * @return Tag[] Returns an array of Tag objects
	//  */
	/*
	public function findByExampleField($value)
	{
		return $this->createQueryBuilder('t')
			->andWhere('t.exampleField = :val')
			->setParameter('val', $value)
			->orderBy('t.id', 'ASC')
			->setMaxResults(10)
			->getQuery()
			->getResult()
		;
	}
	*/

	/*
	public function findOneBySomeField($value): ?Tag
	{
		return $this->createQueryBuilder('t')
			->andWhere('t.exampleField = :val')
			->setParameter('val', $value)
			->getQuery()
			->getOneOrNullResult()
		;
	}
	*/
}
