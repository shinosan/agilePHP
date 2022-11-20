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

	/** @var SplObjectStorage クライアントからの接続を保持する */
	protected SplObjectStorage $clients;

	/**
	 * コンストラクタ
	 */
	public function __construct() {
		$this->clients = new SplObjectStorage;
	}

	/**
	 * クライアントからの接続情報を登録する
	 * @param ConnectionInterface $conn 接続情報
	 */
	public function onOpen(ConnectionInterface $conn) {
		$this->clients->attach($conn); // 接続情報を登録する
	}

	/** @var float サーバからの送信間隔 */
	protected float $sendDuration = 3; // DEBUG: ３秒に1回
	// protected float $sendDuration = 0.01; // 0.1秒に1回

	/** @var float 前回送信時刻 */
	protected float $prevSendTime = 0;

	/**
	 * 各プレイヤーからの入力データを受けつけ、処理する  
	 * 送信間隔時間が過ぎたら、全キャラの更新されたデータを配信する
	 * @param ConnectionInterface $from 接続情報
	 * @param mixed $input 入力データ
	 */
	public function onMessage(ConnectionInterface $from, mixed $input) {
		// 入力データを登録
		$this->register($input);

		// 送信間隔時間に達していなければ送信しない
		$curTime = microtime(true); // 現在時刻
		if ($curTime - $this->prevSendTime < $this->sendDuration) return;

		// 全入力データを処理
		$data = $this->process();

		// 全クライアントに配信
		foreach ($this->clients as $client) {
			$client->send(json_encode($data));
		}
		$this->prevSendTime = $curTime; // 前回送信時刻を更新
	}

	/**
	 * クライアントからの接続断
	 * @param ConnectionInterface $conn 接続情報
	 */
	public function onClose(ConnectionInterface $conn) {
		$this->clients->detach($conn);
	}

	/**
	 * エラー処理
	 * @param ConnectionInterface $conn 接続情報
	 * @param Exception $e エラー情報
	 */
	public function onError(ConnectionInterface $conn, Exception $e) {
		$conn->close();
		// TODO:ログ出力
	}

	/** @var array 入力データのリスト */
	protected array $inputList = [];

	/**
	 * 入力データを登録する
	 * @param string $input 入力データ
	 */
	public function register(string $input) {
		$this->inputList[] = $input;
		// 将来的には送信間隔の待ち時間を有効に生かすため、
		// 全データの更新処理はここで別プロセス(GameEngine？)に送り、
		// process()では結果を取得するだけにしたい
	}

	/**
	 * 全データを処理する
	 * @return ?array 処理された全データ
	 */
	public function process(): ?array {
		// TODO: 本実装すること。GameEngineクラスに任せる？
		$allMsg = '';
		foreach ($this->inputList as $input) {
			$allMsg .= $input . '<br>';
		}
		$data = ['msg' => $allMsg];
		$data['position'] = 'left';
		return $data;
	}
}
