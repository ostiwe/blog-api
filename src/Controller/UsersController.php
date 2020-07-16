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

	private RequestStorage $storage;

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

		$this->storage = $storage;

		if (in_array('page', $query)) $page = (int)$query['page'];

		if ((int)$page <= 0) return $this->json(ErrorHelper::invalidRequest());


		if (in_array('limit', $query) && (int)$query['limit'] > 0) $limit = (int)$query['limit'];

		$usersList = $this->getDoctrine()->getRepository(User::class)->paginate($page, $limit);
		$users = [];
		foreach ($usersList as $user) {
			$users[] = $this->removeConfidentialInfo($user);
		}


		return $this->json(['success' => true, 'items' => $users]);
	}

	/** @Route("/users/count",methods={"GET"}) */
	public function count()
	{
		$users = $this->getDoctrine()->getRepository(User::class)->getCount();
		return $this->json(['success' => true, 'count' => $users]);
	}

	/**
	 * @Route("/users/search", methods={"GET"})
	 * @param Request        $request
	 *
	 * @param RequestStorage $storage
	 *
	 * @return JsonResponse
	 */
	public function search(Request $request, RequestStorage $storage)
	{
		$query = $request->query->all();

		$this->storage = $storage;

		$params = $this->prepareSearchParams($query);
		$search = $this->getDoctrine()->getRepository(User::class)->search($params);

		$users = [];
//		var_dump($search);
//		if (!$search) return $this->json(ErrorHelper::userNotFound());

		foreach ($search as $item) {
			$users[] = $this->removeConfidentialInfo($item);
		}

		return $this->json(['success' => true, 'items' => $users]);
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


	private function prepareSearchParams($query = []): array
	{
		$maySearch = [
			'first_name',
			'last_name',
			'login',
			'email',
		];
		$prepared = [];

		foreach ($query as $key => $value) {
			if (in_array($key, $maySearch) && !empty($value)) $prepared[$key] = $value;
		}
		return $prepared;
	}

	private function removeConfidentialInfo(User $user)
	{
		$token = $this->storage->get('token');
		$exportedUser = $user->export();

		/** @var  AccessToken $token */
		if (!$token || !(($token->getMask() & User::CAN_GET_FULL_USER_INFO) !== User::CAN_GET_FULL_USER_INFO)) {
			unset($exportedUser['email'], $exportedUser['mask']);
		}
		return $exportedUser;
	}
}
