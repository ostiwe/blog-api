<?php

namespace App\Controller;

use App\Entity\File;
use App\Entity\User;
use App\ErrorHelper;
use App\Services\RequestStorage;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class FileController extends AbstractController
{
	private $allowedMimeTypes = [
		'image/jpeg' => '.jpeg',
		'image/jpg' => '.jpg',
		'image/png' => '.png',
	];

	/**
	 * @Route("/file", methods={"POST"}, name="upload_file")
	 * @param Request        $request
	 *
	 * @param RequestStorage $storage
	 *
	 * @return JsonResponse
	 */
	public function index(Request $request, RequestStorage $storage)
	{
		$files = $request->files;
		if ($files->count() > 1)
			return $this->json(ErrorHelper::invalidRequest());

		$file = $files->get('file');
		$mimeType = $file->getMimeType();
		if (!$this->isAllowMimeType($mimeType))
			return $this->json(ErrorHelper::noAllowedFileType());

		$fileName = bin2hex(Uuid::uuid4());
		$fileName .= $this->allowedMimeTypes[$mimeType];

		$filePath = $_SERVER['DOCUMENT_ROOT'] . '/upload/images/' . $fileName;

		if (!move_uploaded_file($file->getPathName(), $filePath))
			return $this->json(ErrorHelper::uploadError());

		$fileSize = filesize($filePath);

		$newFile = (new File())
			->setOwner($storage->get('user_info'))
			->setFileName($fileName)
			->setFileType($mimeType)
			->setPath($filePath)
			->setSize((int)$fileSize)
			->setUploaded(time());

		$this->getDoctrine()->getManager()->persist($newFile);

		/** @var User $user $ */
		$user = $storage->get('user_info');
		$user->setAvatar($fileName);

		$this->getDoctrine()->getManager()->persist($user);

		$this->getDoctrine()->getManager()->flush();

		return $this->json([
			'success' => true,
		]);
	}

	/**
	 * @Route("/avatar/{fileName}", methods={"GET"})
	 * @param $fileName
	 *
	 * @return Response
	 */
	public function getAvatar($fileName)
	{
		$file = $this->getDoctrine()->getRepository(File::class)->findOneBy([
			'fileName' => $fileName,
		]);
		if (!$file) return new Response(null, 404);

		if (!file_exists($file->getPath())) return new Response('s2', 404);

		$responseFile = $this->file($file->getPath());

		return new Response(file_get_contents($file->getPath()), 200,
			['Content-Type' => $responseFile->getFile()->getMimeType()]);
	}

	private function isAllowMimeType($mimeType)
	{
		return key_exists($mimeType, $this->allowedMimeTypes);
	}

}
