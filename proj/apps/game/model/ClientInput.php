<?php
require_once __DIR__ . '/_dir.php';
require_once GAME::MODEL . 'SerializeTrait.php';

/**
 * クライアントから送られる入力データ
 * 8bytes固定
 */
class ClientInput {
	use SerializeTrait;

	/** @var int キャラクタID(0-9999 4bytes)) */
	public int $charaId;
	/** @var int 方向(0-3 1bytes) */
	public int $dir;
	/** @var int 移動速度(0-9 1bytes) */
	public int $speed;
	/** @var int 攻撃、魔法、話しかける、など(0-99 2bytes) */
	public int $command;

	/**
	 * 送信用にシリアライズする
	 * @return string シリアライズされた文字列(16文字)
	 */
	public function serialize(): string {
		$serialized = '';
		$serialized .= self::int2str($this->charaId, 4);
		$serialized .= self::int2str($this->dir,     1);
		$serialized .= self::int2str($this->speed,   1);
		$serialized .= self::int2str($this->command, 2);
		return $serialized;
	}

	/**
	 * 受診したシリアライズ文字列を読み込む
	 * @param string $serialized シリアライズされた文字列(16文字)
	 */
	public function deserialize(string $serialized) {
		$idx = 0;
		$this->charaId = self::str2int($serialized, $idx, 4);
		$this->dir     = self::str2int($serialized, $idx, 1);
		$this->speed   = self::str2int($serialized, $idx, 1);
		$this->command = self::str2int($serialized, $idx, 2);
	}
}
