<?php

namespace App\EventSubscriber;

use App\Entity\AccessToken;
use App\ErrorHelper;
use App\Services\RequestStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Yaml\Yaml;

class RequestSubscriber extends AbstractController implements EventSubscriberInterface
{

	private Request $request;
	private $routesConfig;
	private $needPermission = null;
	private RequestStorage $storage;

	public function __construct(RequestStorage $storage)
	{
		$this->storage = $storage;
	}

	/**
	 * @param RequestEvent $event
	 *
	 * @return void
	 */
	public function onKernelRequest(RequestEvent $event)
	{
		if (!$event->isMasterRequest()) {
			return;
		}
		$this->setRoutesConfig();

		$this->request = $event->getRequest();
		$res = null;
		$routeName = $this->request->attributes->get('_route');
		$requestContentType = $this->request->getContentType();

		if (!$this->hasInConfig($routeName)) return;
		$needRequestContentType = $this->routesConfig['routes'][$routeName]['content_type'];

		if ($needRequestContentType !== $requestContentType) {
			return $event->setResponse($this->json(ErrorHelper::notValidRequestContentType($needRequestContentType)));
		}

		if ($this->needAccessToken($routeName)) {
			$this->setNeedPermission($this->routesConfig['routes'][$routeName]['permission']);
			$res = $this->accessTokenMiddleware();
		}

		if ($this->needAccessToken($routeName) && is_null($res)) {
			$event->setResponse($this->json(['error' => true, 'message' => 'server error'], 500));
			return;
		}

		if ($this->needAccessToken($routeName) && $res['error']) {
			$event->setResponse($this->json($res));
		}
	}

	private function hasInConfig($routeName): bool
	{
		return key_exists($routeName, $this->routesConfig['routes']);
	}


	private function needAccessToken($routeName)
	{
		return $this->routesConfig['routes'][$routeName]['token_need'];
	}


	public function accessTokenMiddleware()
	{
		$token = $this->request->headers->get('token');

		if (!$token || $token === '')
			return ErrorHelper::authorizationFailed(ErrorHelper::AUTH_FAILED_TOKEN);

		/** @var AccessToken $accessToken */
		$accessToken = $this
			->getDoctrine()
			->getRepository(AccessToken::class)
			->findOneBy(['value' => $token]);


		if (!$accessToken)
			return ErrorHelper::authorizationFailed(ErrorHelper::AUTH_FAILED_TOKEN_NOT_FOUND);

		if ($this->needPermission !== null && ($accessToken->getMask() & $this->needPermission) != $this->needPermission)
			return ErrorHelper::authorizationFailed(ErrorHelper::AUTH_FAILED_NOT_PERMISSION);

		$tokenInfo['token'] = $accessToken;
		$tokenInfo['user'] = $accessToken->getOwner();

		$this->storage->set('user_info', $tokenInfo['user']);
		$this->storage->set('token_info', $tokenInfo['token']);

		return [
			'error' => false,
		];

	}

	public static function getSubscribedEvents()
	{
		return [
			'kernel.request' => 'onKernelRequest',
		];
	}


	public function setNeedPermission($needPermission): void
	{
		$this->needPermission = $needPermission;
	}


	public function setRoutesConfig(): void
	{
		$this->routesConfig = Yaml::parseFile(dirname(__DIR__, 2) . '/config/routes_conf.yaml');
	}
}
