<?php
require_once __DIR__ . '/_dir.php';
require_once \GAME::MODEL . 'GameMap.php';
require_once \GAME::MODEL . 'Chara.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

/**
 * ゲームの送受信サーバ
 */
class GameServer implements MessageComponentInterface {

	protected $clients;

	public function __construct() {
		$this->clients = new SplObjectStorage;
	}

	public function onOpen(ConnectionInterface $conn) {
		$this->clients->attach($conn);
	}

	// protected float $sendDuration = 0.01; // 0.1秒に1回
	protected float $sendDuration = 3; // DEBUG: ３秒に1回
	protected float $prevSendTime = 0;

	/**
	 * 各プレイヤーからの入力データを受けつけ、処理する  
	 * 送信間隔時間が過ぎたら、全キャラの更新されたデータを配信する
	 */
	public function onMessage(ConnectionInterface $from, mixed $input) {
		// 入力データを処理
		$data = $this->process($input);

		// 送信間隔時間に達していなければ送信しない
		$curTime = microtime(true);
		if ($curTime - $this->prevSendTime < $this->sendDuration) return;

		// 全クライアントに配信
		foreach ($this->clients as $client) {
			$data['position'] = ($from === $client) ? 'right' : 'left';
			$client->send(json_encode($data));
		}
		$this->prevSendTime = $curTime;
	}

	public function onClose(ConnectionInterface $conn) {
		$this->clients->detach($conn);
	}

	public function onError(ConnectionInterface $conn, \Exception $e) {
		$conn->close();
	}

	/**
	 * データを処理する
	 */
	public function process(string $msg): ?array {
		// TODO: 本実装する
		$data = ['msg' => $msg];
		return $data;
	}
}
