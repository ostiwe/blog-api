<?php


namespace App\Controller;


use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\ErrorHelper;
use App\ParamsChecker;
use App\Services\RequestStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{

	private $cacheController = null;

	public function __construct()
	{
		if (!$this->cacheController) {
			$this->cacheController = new CacheController();
		}
	}

	/**
	 * @Route("/comments/{postID}",methods={"GET"})
	 * @param $postID
	 *
	 * @return JsonResponse
	 */
	public function index($postID)
	{
		if ((int)$postID === 0) return $this->json(ErrorHelper::invalidRequest());

		if ($this->cacheController->inCache("comments.post_$postID")) {
			$comments = $this->cacheController->getItemFromCache("comments.post_$postID");
			return $this->json($comments);

		}
		$post = $this->getDoctrine()->getRepository(Post::class)->find($postID);

		if (!$post) return $this->json(ErrorHelper::postNotFound());
		$comments = [];
		foreach ($post->getComments() as $comment) {
			$comments[] = $comment->export();
		}
		$this->cacheController->setCache("comments.post_$postID", $comments);

		return $this->json($comments);
	}

	/**
	 * @Route("/comments/{postID}",methods={"POST"})
	 * @param Request        $request
	 * @param                $postID
	 *
	 * @param RequestStorage $storage
	 *
	 * @return JsonResponse
	 */
	public function add(Request $request, $postID, RequestStorage $storage)
	{
		if ((int)$postID === 0) return $this->json(ErrorHelper::invalidRequest());
		$body = json_decode($request->getContent(), true);

		$errors = ParamsChecker::check(['text'], $body);
		if (count($errors) > 0) return $this->json(ErrorHelper::invalidRequest());

		$post = $this->getDoctrine()->getRepository(Post::class)->find($postID);
		if (!$post) return $this->json(ErrorHelper::postNotFound());
		/** @var User $user */
		$user = $storage->get('user_info');
		$comment = (new Comment())
			->setCreatedAt(time())
			->setCreator($user)
			->setPost($post)
			->setText($body['text']);

		if (($user->getMask() & User::COMMENTS_NO_NEED_MODERATE) == User::COMMENTS_NO_NEED_MODERATE) $comment->setModerated(true);

		$this->getDoctrine()->getManager()->persist($comment);
		$this->getDoctrine()->getManager()->flush();

		$this->updateCommentsCache($postID);
		return $this->json(['success' => true, 'comment_id' => $comment->getId()]);
	}


	private function updateCommentsCache($postID)
	{
		$post = $this->getDoctrine()->getRepository(Post::class)->find($postID);

		$comments = [];
		foreach ($post->getComments() as $comment) {
			$comments[] = $comment->export();
		}
		$this->cacheController->setCache("comments.post_$postID", $comments);
	}
}
