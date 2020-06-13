<?php


namespace App\Controller;


use App\Entity\AccessToken;
use App\Entity\Post;
use App\Entity\Tag;
use App\Entity\User;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\Query;


class MainController extends AbstractController
{
	/** @Route("/fabr") */
	public function fabric()
	{
		$faker = Factory::create('ru_RU');
		$users = 0;
		$em = $this->getDoctrine()->getManager();

		do {
			$user = (new User())
				->setEmail($faker->safeEmail)
				->setFirstName($faker->firstName)
				->setLastName($faker->lastName)
				->setLogin($faker->userName)
				->setPassword(password_hash('12345', PASSWORD_DEFAULT))
				->setMask(99999);

			$em->persist($user);
			$users++;
		} while ($users < 20);
		$em->flush();

		unset($user);
		$tokens = 0;
		do {
			$user = $em->getRepository(User::class)->find(rand(1, 7));

			$token = (new AccessToken())
				->setOwner($user)
				->setMask((int)$user->getMask())
				->setValue(bin2hex(rand(PHP_INT_MIN, PHP_INT_MAX)))
				->setCreatedAt(time())
				->setExpiredAt(time() + 6900);
			$em->persist($token);
			$tokens++;
		} while ($tokens < 5);
		$em->flush();
	}

	/** @Route("/users") */
	public function users()
	{
		$res = $this->getDoctrine()->getRepository(User::class)->paginate(1);
		return $this->json($res);
	}

	/** @Route("/tokens") */
	public function tokens()
	{
		$tokens = $this->getDoctrine()->getRepository(AccessToken::class)->findAll();
		$res = [];
		foreach ($tokens as $token) {
			$user = $token->getOwner();
			$res[] = [
				'value' => $token->getValue(),
				'expired_at' => $token->getExpiredAt(),
				'created_at' => $token->getCreatedAt(),
				'mask' => $token->getMask(),
				'owner' => $user->export(),
			];
		}
		return $this->json($res);
	}

}