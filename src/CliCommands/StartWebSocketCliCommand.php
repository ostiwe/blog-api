<?php


namespace App\CliCommands;


use App\WebSocket\MainWebSocket;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StartWebSocketCliCommand extends Command
{
	/**
	 * Configure a new Command Line
	 */
	protected function configure()
	{
		$this
			->setName('websocket:server')
			->setDescription('Start the websocket server.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{

		$server = IoServer::factory(new HttpServer(
			new WsServer(
				new MainWebSocket()
			)
		), 9909);

		$server->run();

	}

}