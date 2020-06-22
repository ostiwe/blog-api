<?php

namespace App\Command;

use App\Controller\CacheController;
use App\Entity\Comment;
use App\Entity\File;
use App\Entity\Lang;
use App\Entity\Post;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

class InitCommand extends Command
{
	private ContainerInterface $container;
	private ObjectManager $em;

	public function __construct(ContainerInterface $container)
	{
		parent::__construct();
		$this->container = $container;
		$this->em = $container->get('doctrine')->getManager();
	}

	protected function configure()
	{
		$this
			->setName('app:init')
			->setDescription('Add a short description for your command');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$io = new SymfonyStyle($input, $output);

		if (!$io->confirm('Are you sure you want to continue? This action will delete all data in the selected database.')) {
			$io->text("Init canceled\n");
			return 0;
		}

		$cache = new CacheController();
		$cache->flushCache();

		$this->dropDbScheme($output);
		$this->createDbScheme($output);

		$langList = $this->createLangs();
		$user = $this->createUser($langList);
		$tag = $this->createTag();
		$post = $this->createPost($user, $tag);
		$this->createComment($user, $post);
		$avatar = $this->createFile($user);
		$user->setAvatar($avatar->getFileName());

		$this->em->flush();

		$io->success("App init done!\nYou can login on blog with login/pass below:\n\nadmin/123456789");

		return 0;
	}

	private function dropDbScheme($output)
	{
		$dropSchema = $this->getApplication()->find('doctrine:schema:drop');
		$dropSchema->run(new ArrayInput(['--force' => true]), $output);
	}

	private function createDbScheme($output)
	{
		$createSchema = $this->getApplication()->find('doctrine:schema:create');
		$createSchema->run(new ArrayInput([['--env=dev']]), $output);
	}

	private function createFile($user)
	{
		$newFile = (new File())
			->setOwner($user)
			->setFileName('193.jpg')
			->setFileType('image/jpg')
			->setUploaded(time())
			->setSize(0)
			->setPath(dirname(__DIR__, 2) . '/public/upload/images/193.jpg');

		$this->em->persist($newFile);
		return $newFile;
	}

	private function createLangs()
	{
		$newLangRU = (new Lang())->setName('Russian')->setCode('ru');
		$newLangEN = (new Lang())->setName('English')->setCode('en');

		$this->em->persist($newLangRU);
		$this->em->persist($newLangEN);

		return [$newLangRU, $newLangEN];
	}

	private function createUser($langList)
	{
		$newUser = (new User())
			->setLang($langList[0])
			->setEmail('admin@app.ru')
			->setFirstName('Admin')
			->setLastName('Admin')
			->setLogin('admin')
			->setPassword(password_hash('123456789', PASSWORD_DEFAULT))
			->setSex(0)
			->setMask(User::FULL_ADMIN);

		$this->em->persist($newUser);

		return $newUser;
	}

	private function createTag()
	{
		$newTag = (new Tag())
			->setName('news')
			->setRuName('новости');

		$this->em->persist($newTag);
		return $newTag;
	}

	private function createPost($user, $tag)
	{
		$newPost = (new Post())
			->setCreator($user)
			->setPublished(time())
			->setViews(0)
			->setTitle('Welcome to your blog!')
			->setDescription('Find out your blog’s features in this article.')
			->setContent('content')
			->addTag($tag);

		$this->em->persist($newPost);
		return $newPost;
	}

	private function createComment($user, $post)
	{
		$newComment = (new Comment())
			->setCreator($user)
			->setModerated(true)
			->setDeleted(false)
			->setCreatedAt(time())
			->setPost($post)
			->setText('Hi here!');

		$this->em->persist($newComment);
		return $newComment;
	}

}
