<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Tag;
use App\Entity\User;
use App\ErrorHelper;
use App\ParamsChecker;
use App\Services\RequestStorage;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
	private $cacheController = null;

	public function __construct()
	{
		if (!$this->cacheController) {
			$this->cacheController = new CacheController();
		}
	}

	/**
	 * @Route("/posts",methods={"GET"})
	 * @param Request $request
	 *
	 * @return JsonResponse
	 */
	public function index(Request $request)
	{
		$params = $request->query->all();

		if (!$params) $params = [];
		if (!key_exists('page', $params)) $params['page'] = 1;
		if (!key_exists('show_all', $params)) $params['show_all'] = false;

		$showAll = filter_var($params['show_all'], FILTER_VALIDATE_BOOLEAN);

		$posts = $this->getDoctrine()->getRepository(Post::class)->paginate($params['page'], 5, $showAll);

		return $this->json($posts);
	}

	/**
	 * @Route("/posts",methods={"POST"})
	 * @param Request        $request
	 * @param RequestStorage $requestStorage
	 *
	 * @return JsonResponse
	 */
	public function createPost(Request $request, RequestStorage $requestStorage)
	{
		$body = json_decode($request->getContent(), true);
		$publishNow = false;
		$errors = ParamsChecker::check([
			'title',
			'content',
			'published',
			'tags',
		], $body);

		if (count($errors) > 0) return $this->json(ErrorHelper::requestWrongParams($errors));

		if (is_array($body['tags']) && count($body['tags']) === 0) {
			$errors['tags'][] = 'need one or more tags';
			return $this->json(ErrorHelper::requestWrongParams($errors));
		} else if (!is_array($body['tags'])) {
			$errors['tags'][] = 'tags params mast be a array';
			return $this->json(ErrorHelper::requestWrongParams($errors));
		}

		if (key_exists('publish_now', $body)) $publishNow = boolval((int)$body['publish_now']);

		if (!$publishNow && !$this->isValidTimeStamp($body['published'])) return $this->json(ErrorHelper::requestWrongParams([
			'published' => ['published must be a unix time and the number must be greater than ' . time() . ', you set ' . $body['published']]]));

		$findPost = $this->getDoctrine()->getRepository(Post::class)->findBy([
			'title' => $body['title'],
		]);

		if ($findPost) return $this->json(ErrorHelper::postAllreadyCreated());

		if ($this->cacheController->inCache('tags.allid')) {
			$tagsIdList = $this->cacheController->getItemFromCache('tags.allid');
		} else {
			$tagController = new TagController();
			$tagController->setContainer($this->container);
			$tagController->getTagsIdList();
			$tagsIdList = $this->cacheController->getItemFromCache('tags.allid');
		}
		$tagsError = [];
		foreach ($body['tags'] as $tag) {
			if (!in_array((int)$tag, $tagsIdList['data'])) $tagsError[] = "tag with id '$tag' not found";
		}
		if (count($tagsError) > 0) return $this->json(ErrorHelper::requestWrongParams($tagsError));
		unset($tag);


		/** @var User $author */
		$author = $requestStorage->get('user_info');

		$newPost = (new Post())
			->setCreator($author)
			->setTitle($body['title'])
			->setContent($body['content'])
			->setDescription($body['description'] ?? null)
			->setPublished(!$publishNow ? (int)$body['published'] : time())
			->setViews(0);


		foreach ($body['tags'] as $tag) {
			$tagObject = $this->getDoctrine()->getRepository(Tag::class)->find($tag);
			$newPost->addTag($tagObject);
		}

		try {
			$this->getDoctrine()->getManager()->persist($newPost);
			$this->getDoctrine()->getManager()->flush();
		} catch (Exception $exception) {
			return $this->json(['error' => true, 'message' => 'Unable to create a post at this time, try again later']);
		}
		$this->cacheController->flushCache();
		return $this->json(['success' => true, 'post_id' => $newPost->getId()]);
	}

	/**
	 * @Route("/post/{id}",methods={"GET"})
	 *
	 * @param $id
	 *
	 * @return JsonResponse
	 */
	public function getPost($id)
	{
		if ((int)$id === 0) return $this->json(ErrorHelper::invalidRequest());

		if ($this->cacheController->inCache("posts.post_$id")) {
			return $this->json($this->cacheController->getItemFromCache("posts.post_$id"));
		}

		$post = $this->getDoctrine()->getRepository(Post::class)->find($id);
		if (!$post || $post->getPublished() > time()) return $this->json(ErrorHelper::postNotFound());

		$res = $post->export();
		$this->cacheController->setCache("posts.post_$id", $res);

		return $this->json($res);
	}

	/** @Route("/posts/count",methods={"GET"}) */
	public function count()
	{
		$posts = $this->getDoctrine()->getRepository(Post::class)->getCount();
		return $this->json(['success' => true, 'count' => $posts]);
	}

	/**
	 * @Route("/posts/{postId}",methods={"DELETE"})
	 * @param $postId
	 *
	 * @return JsonResponse
	 */
	public function delete($postId)
	{
		if ((int)$postId <= 0) return $this->json(ErrorHelper::invalidRequest());

		$post = $this->getDoctrine()->getRepository(Post::class)->find($postId);

		if (!$post) return $this->json(ErrorHelper::postNotFound());

		try {
			$this->getDoctrine()->getManager()->remove($post);
			$this->getDoctrine()->getManager()->flush();

		} catch (Exception $e) {
			return $this->json(['error' => true, 'message' => 'try again later', '_code' => $e->getCode()]);
		}

		return $this->json(['success' => true, 'message' => 'post deleted']);
	}

	private function isValidTimeStamp($timestamp)
	{
		return ((int)$timestamp <= PHP_INT_MAX) && ((int)$timestamp >= time());
	}


}
