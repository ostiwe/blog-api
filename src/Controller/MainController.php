<?php


namespace App\Controller;


//use App\Entity\AccessToken;
//use App\Entity\Lang;
//use App\Entity\Post;
//use App\Entity\Tag;
//use App\Entity\User;
//use Faker\Factory;
//use Symfony\Component\HttpFoundation\Response;
//use Symfony\Component\Routing\Annotation\Route;
//use Doctrine\ORM\Query;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class MainController extends AbstractController
{
//	/** @Route("/fabr") */
//	public function fabric()
//	{
//		$faker = Factory::create('ru_RU');
//		$users = 0;
//		$em = $this->getDoctrine()->getManager();
//		$lang = (new Lang())->setName('Russian')->setCode('RU');
//		$em->persist($lang);
//		do {
//			$user = (new User())
//				->setEmail($faker->safeEmail)
//				->setFirstName($faker->firstName)
//				->setLastName($faker->lastName)
//				->setLogin($faker->userName)
//				->setPassword(password_hash('12345', PASSWORD_DEFAULT))
//				->setMask(2)
//				->setLang($lang);
//
//			$em->persist($user);
//			$users++;
//		} while ($users < 4);
//		$em->flush();
//
//		$user = $this->getDoctrine()->getRepository(User::class)->find(rand(1, 3));
//		$tag = (new Tag())
//			->setName("news")
//			->setRuName('новости');
//		$em->persist($tag);
//
//		$newPost = (new Post())
//			->setCreator($user)
//			->setViews(0)
//			->setTitle("Добро пожаловать на ваш блог")
//			->setDescription('В этом посте вы узнаете немного о вашем блоге')
//			->setContent("some content")
//			->setPublished(time());
//		$newPost->getTags()->add($tag);
//		$em->persist($newPost);
//		$em->flush();
//		return $this->json(['success' => true]);
//	}
//
//	/** @Route("/users") */
//	public function users()
//	{
//		$res = $this->getDoctrine()->getRepository(User::class)->paginate(1);
//		return $this->json($res);
//	}
//
//	/** @Route("/tokens") */
//	public function tokens()
//	{
//		$tokens = $this->getDoctrine()->getRepository(AccessToken::class)->findAll();
//		$res = [];
//		foreach ($tokens as $token) {
//			$user = $token->getOwner();
//			$res[] = [
//				'value' => $token->getValue(),
//				'expired_at' => $token->getExpiredAt(),
//				'created_at' => $token->getCreatedAt(),
//				'mask' => $token->getMask(),
//				'owner' => $user->export(),
//			];
//		}
//		return $this->json($res);
//	}

}