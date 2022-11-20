<?php
require_once __DIR__ . '/_dir.php';

/**
 * 画面に表示する２Dイメージ
 */
class Image {
	/**
	 * イメージIDを取得する
	 * @return int 
	 */
	public function imageId(): int {
		return $this->imageId;
	}
	/**
	 * イメージIDをセットする
	 * @param int $val セットする値
	 * @return Image
	 */
	public function setImageId(int $val): Image {
		$this->imageId = $val;
		return $this;
	}
	/** @var int イメージID */
	private int $imageId;

	/**
	 * 横サイズを取得する
	 * @return int 
	 */
	public function width(): int {
		return $this->width;
	}
	/**
	 * 横サイズをセットする
	 * @param int $val セットする値
	 * @return Image
	 */
	public function setWidth(int $val): Image {
		$this->width = $val;
		return $this;
	}
	/** @var int 横サイズ */
	private int $width;

	/**
	 * 縦サイズを取得する
	 * @return int 
	 */
	public function height(): int {
		return $this->height;
	}
	/**
	 * 縦サイズをセットする
	 * @param int $val セットする値
	 * @return Image
	 */
	public function setHeight(int $val): Image {
		$this->height = $val;
		return $this;
	}
	/** @var int 縦サイズ */
	private int $height;

	/**
	 * バイナリを取得する
	 * @return array 
	 */
	public function binary(): array {
		return $this->binary;
	}
	/**
	 * バイナリをセットする
	 * @param array $val セットする値
	 * @return Image
	 */
	public function setBinary(array $val): Image {
		$this->binary = $val;
		return $this;
	}
	/** @var array バイナリ  */
	private array $binary = [];
}
