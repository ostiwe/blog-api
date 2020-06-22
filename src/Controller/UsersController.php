<?php


namespace App\Controller;


use App\Entity\User;
use App\ErrorHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class UsersController extends AbstractController
{
	/** @Route("/users/count",methods={"GET"}) */
	public function count()
	{
		$users = $this->getDoctrine()->getRepository(User::class)->getCount();
		return $this->json(['success' => true, 'count' => $users]);
	}

	/**
	 * @Route("/users/{userId}",methods={"GET"})
	 * @param $userId
	 *
	 * @return JsonResponse
	 */
	public function getById($userId)
	{
		if ((int)$userId <= 0) return $this->json(ErrorHelper::invalidRequest());

		$user = $this->getDoctrine()->getRepository(User::class)->find($userId);
		if (!$user) return $this->json(ErrorHelper::userNotFound());
		$info = $user->export();

		unset($info['email'], $info['mask']);
		$info['created_posts'] = count($user->getPosts());
		$info['created_comments'] = count($user->getComments());

		return $this->json($info);
	}
}
