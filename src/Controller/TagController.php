<?php

namespace App\Controller;

use App\Entity\Tag;
use App\ErrorHelper;
use App\ParamsChecker;
use Exception;
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


		$paginateTaggedPosts = $this->getDoctrine()
			->getRepository(Tag::class)->paginatePosts($tagId, $page, $limit);


		return $this->json($paginateTaggedPosts);
	}

	/** @Route("/tags/stats", methods={"GET"}) */
	public function getStats()
	{
		$allTags = $this->getDoctrine()->getRepository(Tag::class)->findAll();

		$allTagsStats = array_map(function (Tag $tag) {
			$tagInfo = $tag->export();
			$tagInfo['posts'] = count($tag->getRealizedPosts());
			return $tagInfo;
		}, $allTags);

		return $this->json(['success' => true, 'data' => $allTagsStats]);
	}

	/** @Route("/tags", methods={"POST"})
	 * @param Request $request
	 *
	 * @return JsonResponse
	 */
	public function create(Request $request)
	{
		$body = json_decode($request->getContent(), true);

		$errors = ParamsChecker::check(['name'], $body);

		if (count($errors) > 0) return $this->json(ErrorHelper::requestWrongParams($errors));

		$tagName = $body['name'];
		$tagRuName = null;
		if (key_exists('ru_name', $body) && !empty(trim($body['ru_name']))) {
			$tagRuName = $body['ru_name'];
		}

		$tag = $this->getDoctrine()->getRepository(Tag::class)->findBy([
			'name' => $tagName,
		]);

		if ($tag) return $this->json(ErrorHelper::tagAlreadyCreated());

		$newTag = (new Tag())
			->setName($tagName)
			->setRuName($tagRuName);

		try {
			$this->getDoctrine()->getManager()->persist($newTag);
			$this->getDoctrine()->getManager()->flush();

			$tagsList = $this->getDoctrine()->getRepository(Tag::class)->findAll();
			$tags['items'] = array_map(function ($tag) {
				return $tag->export();
			}, $tagsList);
			$tags['count'] = count($tags['items']);
			asort($tags);

			$this->cacheController->setCache('tags.all', $tags);

		} catch (Exception $exception) {
			return $this->json(['error' => true, 'code' => $exception->getCode(), 'message' => 'server error']);
		}

		return $this->json(['success' => true, 'tag_id' => $newTag->getId()]);
	}
}
