<?php

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

require_once __DIR__ . '/_dir.php';
require_once dirname(\BASE::ROOT) . '/vendor/autoload.php';
require_once GAME::LOGIC . 'GameServer.php';

/**
 * WebSoketサーバの生成
 */
$server = IoServer::factory(
	new HttpServer(
		new WsServer(
			new \GameServer() // ゲームサーバを登録
		)
	),
	8282
);
// WebSocket サーバ詭道
$server->run();
