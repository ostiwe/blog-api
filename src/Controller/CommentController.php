<?php


namespace App\Controller;


use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\ErrorHelper;
use App\Services\RequestStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{

	/** @Route("/comments/{postID}",methods={"GET"})
	 * @param $postID
	 *
	 * @return JsonResponse
	 */
	public function index($postID)
	{
		if ((int)$postID === 0) return $this->json(ErrorHelper::invalidRequest());
		$post = $this->getDoctrine()->getRepository(Post::class)->find($postID);

		$comments = [];
		foreach ($post->getComments() as $comment) {
			$comments[] = $comment->export();
		}

		return $this->json($comments);
	}

	/** @Route("/comments/{postID}",methods={"PUT"})
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
		if (!key_exists('text', $body) || empty(trim($body['text']))) return $this->json(ErrorHelper::invalidRequest());

		$post = $this->getDoctrine()->getRepository(Post::class)->find($postID);
		if (!$post) return $this->json(ErrorHelper::postNotFound());
		/** @var User $user */
		$user = $storage->get('user_info');
		$comment = (new Comment())
			->setCreatedAt(time())
			->setCreator($user)
			->setPost($post)
			->setText($body['text']);

		$this->getDoctrine()->getManager()->persist($comment);
		$this->getDoctrine()->getManager()->flush();

		return $this->json(['success' => true, 'comment_id' => $comment->getId()]);
	}
}