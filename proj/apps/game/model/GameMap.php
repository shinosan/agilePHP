<?php
require_once __DIR__ . '/_dir.php';

/**
 * ゲームの舞台となるマップ。  
 * 疑似的な３Ｄで、高さはフィギュアをひとマス上に移動させる。  
 * プレイヤーがマップに入って来た時に配信される。  
 * 描画は全て画面側で行われる。  
 * 通行不可やイベントの判定などはサーバ側。
 */
class GameMap {
	/**
	 * チップ配列を返す
	 * @return array チップID配列
	 */
	public function chips(): array {
		return $this->chips;
	}
	/**
	 * チップID配列をセットする
	 * @param &array &$chips セットする値
	 * @return GameMap マップ
	 */
	public function setChips(array &$chips): GameMap {
		$this->chips = $chips;
		return $this;
	}
	/** @var array チップID配列 */
	private array $chips = [];

	/**
	 * 横サイズを返す
	 * @return int 横サイズ
	 */
	public function width(): int {
		return $this->width;
	}
	/**
	 * 横サイズをセットする
	 * @param int $width セットする値
	 * @return GameMap マップ
	 */
	public function setWidth(int $width): GameMap {
		$this->width = $width;
		return $this;
	}
	/** @var int 横サイズ */
	private int $width = 0;

	/**
	 * 縦サイズを返す
	 * @return int 縦サイズ
	 */
	public function height(): int {
		return $this->height;
	}
	/**
	 * 縦サイズをセットする
	 * @param int $height セットする値
	 * @return GameMap マップ
	 */
	public function setHeight(int $height): GameMap {
		$this->height = $height;
		return $this;
	}
	/** @var int 縦サイズ */
	private int $height = 0;

	/**
	 * 座標を指定してマップチップを得る
	 * @param int $x X座標
	 * @param int $y Y座標
	 * @return int チップID -1:範囲外
	 */
	public function getChipId(int $x, int $y): int {
		$idx = $this->index($x, $y);
		if ($idx < 0) return -1;

		$val = $this->chips[$idx];
		return $val;
	}
	/**
	 * 座標を指定してマップチップをセットする
	 * @param int $x X座標
	 * @param int $y Y座標
	 * @param int $chipId セットするマップチップ
	 */
	public function putChipId(int $x, int $y, int $chipId): bool {
		$idx = $this->index($x, $y);
		if ($idx < 0) return false;

		$this->chips[$idx] = $chipId;
		return true;
	}

	/**
	 * 座標を指定してマップチップ配列のインデックスを得る
	 * @param int $x X座標
	 * @param int $y Y座標
	 * @return int -1:範囲外
	 */
	protected function index(int $x, int $y): int {
		return ($x < 0 || $x >= $this->width) && ($y < 0 || $y >= $this->height) ? -1 : ($y * $this->width + $x);
	}
}

/**
 * マップを構成するチップ。  
 */
class MapChip {
	/**
	 * MapChipにキャストする
	 * @return ?MapChip null:キャスト失敗
	 */
	public static function cast(mixed $obj): ?MapChip {
		return ($obj instanceof MapChip) ? $obj : null;
	}

	/**
	 * チップIDを返す 
	 * @return int chipId チップID
	 */
	public function chipId(): int {
		return $this->chipId;
	}
	/**
	 * チップIDをセットする
	 * @param int $chipId セットする値
	 * @return MapChip 
	 */
	public function setChipId(int $chipId): MapChip {
		$this->chipId = $chipId;
		return $this;
	}
	/** @var int チップID */
	private int $chipId;
	/**
	 * 横サイズを返す
	 * @return int 横サイズ
	 */
	public function width(): int {
		return $this->width;
	}

	/**
	 * 横サイズをセットする
	 * @param int $width セットする値
	 * @return MapChip マップチップ
	 */
	public function setWidth(int $width): MapChip {
		$this->width = $width;
		return $this;
	}
	/** @var int 横サイズ */
	private int $width = 0;

	/**
	 * 縦サイズを返す
	 * @return int 縦サイズ
	 */
	public function height(): int {
		return $this->height;
	}
	/**
	 * 縦サイズをセットする
	 * @param int $height セットする値
	 * @return MapChip マップチップ
	 */
	public function setHeight(int $height): MapChip {
		$this->height = $height;
		return $this;
	}
	/** @var int 縦サイズ */
	private int $height = 0;

	/**
	 * 属性を返す
	 * @return int 属性
	 */
	public function attrib(): int {
		return $this->attrib;
	}
	/**
	 * 属性をセットする
	 * @param int $attrib セットする値
	 * @return MapChip 
	 */
	public function setAttrib(int $attrib): MapChip {
		$this->attrib = $attrib;
		return $this;
	}
	/** @var int 属性 */
	private int $attrib;

	/**
	 * イメージIDを返す
	 * @return int イメージID
	 */
	public function image(): int {
		return $this->image;
	}
	/**
	 * イメージIDをセットする
	 * @param int $image セットする値
	 * @return MapChip 
	 */
	public function setImage(int $image): MapChip {
		$this->image = $image;
		return $this;
	}
	/** @var int イメージID */
	private int $image;
}
