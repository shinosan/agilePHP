<?php
require_once __DIR__ . '/_dir.php';
require_once GAME::MODEL . 'SerializeTrait.php';

/**
 * マップ上で動く形を持つもの  
 * ゲームでサーバが全クライアントに配信する。
 * 16バイト固定。
 */
class Figure {
	use SerializeTrait;

	/** @var int キャラクタID(0-9999 4bytes)) */
	public int $charaId;
	/** @var int X座標(0-255 3bytes) */
	public int $x;
	/** @var int Y座標(0-255) 3bytes) */
	public int $y;
	/** @var int Z座標(0-255 3bytes) */
	public int $z;
	/** @var int 方向(0-3 1bytes) */
	public int $dir;
	/** @var int 表示パターン(0-99) 2bytes) */
	public int $pattern;

	/**
	 * 送信用にシリアライズする
	 * @return string シリアライズされた文字列(16文字)
	 */
	public function serialize(): string {
		$serialized = '';
		$serialized .= self::int2str($this->charaId, 4);
		$serialized .= self::int2str($this->x, 3);
		$serialized .= self::int2str($this->y, 3);
		$serialized .= self::int2str($this->z, 3);
		$serialized .= self::int2str($this->dir, 1);
		$serialized .= self::int2str($this->pattern, 2);
		return $serialized;
	}

	/**
	 * 受診したシリアライズ文字列を読み込む
	 * @param string $serialized シリアライズされた文字列(16文字)
	 */
	public function deserialize(string $serialized) {
		$idx = 0;
		$this->charaId = self::str2int($serialized, $idx, 4);
		$this->x       = self::str2int($serialized, $idx, 3);
		$this->y       = self::str2int($serialized, $idx, 3);
		$this->z       = self::str2int($serialized, $idx, 3);
		$this->dir     = self::str2int($serialized, $idx, 1);
		$this->pattern = self::str2int($serialized, $idx, 2);
	}
}
