<?php


namespace App\Controller;


use App\Entity\AccessToken;
use App\Entity\User;
use App\ErrorHelper;
use App\Services\RequestStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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

	/**
	 * @Route("/users")
	 * @param Request        $request
	 *
	 * @param RequestStorage $storage
	 *
	 * @return JsonResponse
	 */
	public function list(Request $request, RequestStorage $storage)
	{
		$query = $request->query->all();
		$limit = 10;
		$page = 1;

		$token = $storage->get('token');

		if (in_array('page', $query)) $page = (int)$query['page'];

		if ((int)$page <= 0) return $this->json(ErrorHelper::invalidRequest());


		if (in_array('limit', $query) && (int)$query['limit'] > 0) $limit = (int)$query['limit'];

		$users = $this->getDoctrine()->getRepository(User::class)->paginate($page, $limit);

		/** @var  AccessToken $token */
		if (!$token || !(($token->getMask() & User::CAN_GET_FULL_USER_INFO) !== User::CAN_GET_FULL_USER_INFO)) {
			$users = array_map(function ($user) {
				unset($user['email'], $user['mask']);
				return $user;
			}, $users);
		}

		return $this->json(['success' => true, 'items' => $users]);
	}
}
