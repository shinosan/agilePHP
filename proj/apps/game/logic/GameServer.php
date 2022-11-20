<?php
require_once __DIR__ . '/_dir.php';
require_once \GAME::MODEL . 'GameMap.php';
require_once \GAME::MODEL . 'Chara.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class GameServer implements MessageComponentInterface {

	protected $clients;

	public function __construct() {
		$this->clients = new SplObjectStorage;
	}

	public function onOpen(ConnectionInterface $conn) {
		$this->clients->attach($conn);
	}

	public function onMessage(ConnectionInterface $from, $msg) {
		foreach ($this->clients as $client) {
			$data = ['msg' => $msg];
			if ($from === $client) {
				$data['position'] = 'right';
			} else {
				$data['position'] = 'left';
			}
			$client->send(json_encode($data));
		}
	}

	public function onClose(ConnectionInterface $conn) {
		$this->clients->detach($conn);
	}

	public function onError(ConnectionInterface $conn, \Exception $e) {
		$conn->close();
	}
}
