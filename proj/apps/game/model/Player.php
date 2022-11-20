<?php
require_once __DIR__ . '/_dir.php';
require_once GAME::MODEL . 'Chara.php';

/**
 * プレイヤー
 */
class Player extends Chara {
	/** @var string ユーザID */
	public string $userId;
}
