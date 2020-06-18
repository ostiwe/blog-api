<?php

namespace App\Controller;

use App\Entity\AccessToken;
use App\Entity\Lang;
use App\Entity\User;
use App\ErrorHelper;
use App\Services\RequestStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
	/**
	 * @Route("/auth/info", methods={"POST"})
	 * @param Request $request
	 *
	 * @return JsonResponse
	 */
	public function index(Request $request)
	{
		$body = json_decode($request->getContent(), true);
		$errors = [];
		if (!key_exists('access_token', $body) || empty($body['access_token'])) $errors[] = 'field "access_token" can’t be empty';
		if (count($errors) > 0) return $this->json(ErrorHelper::requestWrongParams($errors));
		$em = $this->getDoctrine()->getManager();
		$token = $em->getRepository(AccessToken::class)->findOneBy([
			'value' => $body['access_token'],
		]);
		if (!$token) return $this->json(ErrorHelper::authorizationFailed(ErrorHelper::AUTH_FAILED_TOKEN_NOT_FOUND));

		return $this->json([
			'success' => true,
			'data' => [
				'user_info' => $token->getOwner()->export(),
				'access_token' => $token->export(),
			],
		]);
	}

	/**
	 * @Route("/auth/login", methods={"POST"})
	 * @param Request $request
	 *
	 * @return JsonResponse
	 */
	public function login(Request $request)
	{
		$body = json_decode($request->getContent(), true);
		$errors = [];
		if (!key_exists('login', $body) || empty($body['login'])) $errors[] = 'field "login" can’t be empty';
		if (!key_exists('password', $body) || empty($body['password'])) $errors[] = 'field "password" can’t be empty';

		if (count($errors) > 0) return $this->json(ErrorHelper::requestWrongParams($errors));

		$em = $this->getDoctrine()->getManager();
		$user = $em->getRepository(User::class)->findOneBy([
			'login' => $body['login'],
		]);
		if (!$user) return $this->json(ErrorHelper::userNotFound());

		if (!password_verify($body['password'], $user->getPassword()))
			return $this->json(ErrorHelper::authorizationFailed(ErrorHelper::AUTH_FAILED_PASSWORD));

		$newToken = (new AccessToken())
			->setOwner($user)
			->setCreatedAt(time())
			->setExpiredAt(time() + 86600)
			->setMask($user->getMask())
			->generate();
		$em->persist($newToken);
		$em->flush();

		return $this->json([
			'success' => true,
			'data' => [
				'user_info' => $user->export(),
				'access_token' => $newToken->export(),
			],
		]);
	}

	/**
	 * @Route("/auth/register",methods={"POST"})
	 * @param Request $request
	 *
	 * @return JsonResponse
	 */
	public function register(Request $request)
	{
		$body = json_decode($request->getContent(), true);
		$errors = [];
		if (!key_exists('login', $body) || empty($body['login'])) $errors[] = 'field "login" can’t be empty';
		if (!key_exists('password', $body) || empty($body['password'])) $errors[] = 'field "password" can’t be empty';
		if (!key_exists('email', $body) || empty($body['email'])) $errors[] = 'field "email" can’t be empty';
		if (!key_exists('age', $body) || empty($body['age'])) $errors[] = 'field "age" can’t be empty';
		if (!key_exists('first_name', $body) || empty($body['first_name'])) $errors[] = 'field "first_name" can’t be empty';
		if (!key_exists('last_name', $body) || empty($body['last_name'])) $errors[] = 'field "last_name" can’t be empty';
		if (!key_exists('sex', $body) || empty($body['sex'])) $errors[] = 'field "sex" can’t be empty';

		$userRepo = $this->getDoctrine()->getRepository(User::class);

		$existLoginUser = $userRepo->findBy([
			'login' => $body['login'],
		]);
		$existEmailUser = $userRepo->findBy([
			'email' => $body['email'],
		]);

		if ($existLoginUser)
			return $this->json(ErrorHelper::registerError(ErrorHelper::REGISTER_USER_ALREADY_EXIST));
		if ($existEmailUser)
			return $this->json(ErrorHelper::registerError(ErrorHelper::REGISTER_USER_ALREADY_EXIST));

		$lang = $this->getDoctrine()->getRepository(Lang::class)->find(1);
		$newUser = (new User())
			->setMask(User::USER_DEFAULT_MASK)
			->setPassword(password_hash($body['password'], PASSWORD_DEFAULT))
			->setLogin($body['login'])
			->setLastName($body['last_name'])
			->setFirstName($body['first_name'])
			->setSex($body['sex'])
			->setEmail($body['email'])
			->setLang($lang);

		$this->getDoctrine()->getManager()->persist($newUser);


		$this->getDoctrine()->getManager()->flush();
		return $this->json([
			'success' => true,
		]);
	}

	/**
	 * @Route("/auth/logout",methods={"POST"})
	 * @param Request        $request
	 * @param RequestStorage $storage
	 *
	 * @return JsonResponse
	 */
	public function logOut(Request $request, RequestStorage $storage)
	{
		$token = $this->getDoctrine()
			->getRepository(AccessToken::class)->find($storage->get('token_info')->getId());

		$this->getDoctrine()->getManager()->remove($token);
		$this->getDoctrine()->getManager()->flush();


		return $this->json(['success' => true,]);
	}

}
