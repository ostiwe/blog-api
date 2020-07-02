<?php

namespace App\Controller;

use App\Entity\Tag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TagController extends AbstractController
{
	private $cacheController = null;

	public function __construct()
	{
		if (!$this->cacheController) {
			$this->cacheController = new CacheController();
		}
	}

	/**
	 * @Route("/tags", name="tag", methods={"GET"})
	 */
	public function index()
	{
		if ($this->cacheController->inCache('tags.all'))
			return $this->json($this->cacheController->getItemFromCache('tags.all'));

		$tagsList = $this->getDoctrine()->getRepository(Tag::class)->findAll();
		$tags['items'] = array_map(function ($tag) {
			return $tag->export();
		}, $tagsList);
		$tags['count'] = count($tags['items']);
		asort($tags);

		$this->cacheController->setCache('tags.all', $tags);
		return $this->json($tags);
	}

	/** @Route("/tags/id-list", methods={"GET"}) */
	public function getTagsIdList()
	{
		if ($this->cacheController->inCache('tags.allid')) {
			return $this->json($this->cacheController->getItemFromCache('tags.allid'));
		}
		$tagsList = $this->getDoctrine()->getRepository(Tag::class)->findAll();

		$tagsListId = array_map(function (Tag $tag) {
			return $tag->getId();
		}, $tagsList);
		$this->cacheController->setCache('tags.allid', ['success' => true, 'data' => $tagsListId]);

		return $this->json(['success' => true, 'data' => $tagsListId]);
	}

	/** @Route("/tag/{tagId}",methods={"GET"})
	 * @param Request $request
	 * @param         $tagId
	 *
	 * @return JsonResponse
	 */
	public function getPostByTag(Request $request, $tagId)
	{
		if (!$tagId || (int)$tagId === 0) $tagId = 1;
		$page = $request->query->get('page', 1);
		$limit = $request->query->get('limit', 5);

		if ($limit > 20) $limit = 20;

		if ($this->cacheController->inCache("tags.posts_by_tag_{$page}_{$limit}"))
			return $this->json($this->cacheController->getItemFromCache("tags.posts_by_tag_{$page}_{$limit}"));

		$paginateTaggedPosts = $this->getDoctrine()
			->getRepository(Tag::class)->paginatePosts($tagId, $page, $limit);

		$this->cacheController->setCache("tags.posts_by_tag_{$page}_{$limit}", $paginateTaggedPosts);

		return $this->json($paginateTaggedPosts);
	}
}
