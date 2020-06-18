<?php

namespace App\Controller;

use App\Entity\Post;
use App\ErrorHelper;
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

		if ($this->cacheController->inCache("posts.page_{$params['page']}")) {
			$posts = $this->cacheController->getItemFromCache("posts.page_{$params['page']}");
		} else {
			$posts = $this->getDoctrine()->getRepository(Post::class)->paginate($params['page']);
			if (count($posts) !== 0)
				$this->cacheController->setCache("posts.page_{$params['page']}", $posts);
		}

		return $this->json($posts);
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
		if (!$post) return $this->json(ErrorHelper::postNotFound());

		$res = $post->export();
		$this->cacheController->setCache("posts.post_$id", $res);

		return $this->json($res);
	}

	/** @Route("/posts/count") */
	public function count()
	{
		$posts = $this->getDoctrine()->getRepository(Post::class)->getCount();
		return $this->json(['success' => true, 'count' => $posts]);
	}


}
