<?php

namespace App\Controller;

use App\Entity\Tag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TagController extends AbstractController
{
	/**
	 * @Route("/tags", name="tag")
	 */
	public function index()
	{
		$tagsList = $this->getDoctrine()->getRepository(Tag::class)->findAll();
		$tags['items'] = array_map(function ($tag) { return $tag->export(); }, $tagsList);
		$tags['count'] = count($tags['items']);
		asort($tags);
		return $this->json($tags);
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

		$tag = $this->getDoctrine()->getRepository(Tag::class)->paginatePosts($tagId, $page, $limit);


//		$postsList = $tag->getPosts();
//		$posts = [];
		return $this->json($tag);
	}
}
