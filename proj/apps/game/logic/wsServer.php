<?php

/**
 * WebSocketサーバの起動処理
 */

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

require_once __DIR__ . '/_dir.php';
require_once dirname(\BASE::ROOT) . '/vendor/autoload.php';
require_once GAME::LOGIC . 'GameServer.php';

/** @var int クライアント・サーバ間のポート番号 */
const CLIENTS_SERVER_PORT = 8282;

/** @var string 全てのIPアドレスを受け入れる */
const ANY_CONNECTION = '0.0.0.0';

/**
 * @var IoServer WebSoketサーバ
 */
$server = IoServer::factory(
	new HttpServer(
		new WsServer(
			new \GameServer() // ゲームサーバを登録
		)
	),
	CLIENTS_SERVER_PORT,
	ANY_CONNECTION
);

/**
 * WebSocket サーバ起動
 */
$server->run();
