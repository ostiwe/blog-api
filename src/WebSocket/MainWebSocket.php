<?php


namespace App\WebSocket;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class MainWebSocket implements MessageComponentInterface
{

	protected $connections = [];
	protected $channels = [];

	/**
	 * A new websocket connection
	 *
	 * @param ConnectionInterface $conn
	 */
	public function onOpen(ConnectionInterface $conn)
	{
		$this->connections[] = $conn;
		$conn->send(json_encode(['type' => 'service', 'payload' => ['notification_type' => 'info', 'notification_message' => 'Hi!']]));
		echo "New connection {$conn->resourceId} \n";
	}

	/**
	 * Handle message sending
	 *
	 * @param ConnectionInterface $from
	 * @param string              $msg
	 */
	public function onMessage(ConnectionInterface $from, $msg)
	{
		$messageData = json_decode(trim($msg), true);


	}

	/**
	 * A connection is closed
	 *
	 * @param ConnectionInterface $conn
	 */
	public function onClose(ConnectionInterface $conn)
	{
		foreach ($this->connections as $key => $conn_element) {
			if ($conn === $conn_element) {
				unset($this->connections[$key]);
				break;
			}
		}
	}

	/**
	 * Error handling
	 *
	 * @param ConnectionInterface $conn
	 * @param \Exception          $e
	 */
	public function onError(ConnectionInterface $conn, \Exception $e)
	{
		$conn->send("Error : " . $e->getMessage());
		$conn->close();
	}


}