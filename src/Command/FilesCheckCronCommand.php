<?php


namespace App\Command;


use App\Entity\File;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FilesCheckCronCommand extends Command
{

	private ContainerInterface $container;

	public function __construct(ContainerInterface $container)
	{
		parent::__construct();
		$this->container = $container;
	}

	protected function configure()
	{
		$this
			->setName('cron:files:check')
			->setDescription('Check files on server');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$progress = new ProgressBar($output);
		$progress->setFormat('debug');

		$doctrine = $this->container->get('doctrine');
		$files = $doctrine->getRepository(File::class)->findAll();
		$progress->start(count($files));

		/** @var File $file */
		foreach ($files as $file) {
			if (!file_exists($file->getPath())) {
				$doctrine->getManager()->remove($file);
			}
			$progress->advance();
		}

		$doctrine->getManager()->flush();
		$progress->finish();
		$output->write("\n");

		return 0;
	}
}
