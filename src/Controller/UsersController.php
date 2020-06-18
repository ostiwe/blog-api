<?php


namespace App\Controller;


use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class UsersController extends AbstractController
{
	/** @Route("/users/count",methods={"GET"}) */
	public function count()
	{
		$users = $this->getDoctrine()->getRepository(User::class)->getCount();
		return $this->json(['success' => true, 'count' => $users]);
	}
}