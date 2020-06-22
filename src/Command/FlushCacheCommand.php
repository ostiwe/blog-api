<?php

namespace App\Command;

use App\Controller\CacheController;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FlushCacheCommand extends Command
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
			->setName('app:cache:flush')
			->setDescription('Flush app cache');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$cache = new CacheController();

		$cache->flushCache();

		$output->write("\n");
		return 0;
	}
}
