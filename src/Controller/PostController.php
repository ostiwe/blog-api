<?php

namespace App\Controller;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
	/**
	 * @Route("/posts")
	 * @param Request $request
	 *
	 * @return JsonResponse
	 */
	public function index(Request $request)
	{
		if ($request->isMethod('GET')) {
			$params = $request->query->all();
		} else {
			$params = json_decode($request->getContent(), true);
		}
		if (!$params) $params = [];
		if (!key_exists('page', $params)) $params['page'] = 1;

		$posts = $this->getDoctrine()->getRepository(Post::class)->paginate($params['page']);


		return $this->json($posts);
	}
}
