<?php

/**
 * 数値と名称を紐づけるEnum基底クラス
 * @example /proj/test/base/util/EnumTest.php 下記のように派生クラスを作る
 * ```php
 * class Status extends Enum {
 *     const ALL = [
 *         'OK'=>1,
 *         'NG'=>-1
 *     ];
 *     public static Status $OK;
 *     public static Status $NG;
 *     public static function get(int|string $key): Status {
 *         return self::getBase(self::$enums, $key);
 *     }
 *     public static function all(): array {
 *         return self::$enums;
 *     }
 *     public static function init() {
 *         self::$enums = self::initBase(__CLASS__, self::ALL);
 *     }
 *     private static array $enums = [];
 * }
 * Status::init();
 * ```
 */
class Enum {
	/**
	 * コンストラクタ  
	 * initBase()からのみ呼ばれる
	 * @param int $id 数値
	 * @param string $name 名称
	 */
	protected function __construct(int $id, string $name) {
		$this->id = $id;
		$this->name = $name;
	}
	/** @var int 数値 */
	private int $id = 0;
	/** @var string 名称 */
	private string $name = '';

	/**
	 * IDを取得
	 * @return int ID
	 */
	public function id(): int {
		return $this->id;
	}
	/**
	 * 名称を取得
	 * @return string 名称
	 */
	public function name(): string {
		return $this->name;
	}

	//// 内部メソッド ////

	/**
	 * Enumの生成
	 * @param string $className Enumクラス名
	 * @param array $all Enum定義配列 [名称=>ID, ...] or [名称, ...]
	 * @param int $startId IDの開始(省略 = 0)
	 * @return array [0=>[ID=>Enum, ...], 1:=>[名称=>Enum, ...]]
	 */
	protected static function initBase(string $className, array $all, int $startId = 0): array {
		$maxId = $startId;
		$class = new ReflectionClass($className);
		$enumsId = []; // IDがキーの配列
		$enumsName = []; // 名称がキーの配列
		foreach ($all as $key => $val) {
			if (is_string($key)) {
				// キーが文字列の場合
				$id   = $val;
				$name = $key;
				$enum = new $className($id, $name);
				$maxId = $id + 1;
			}
			if (is_string($val)) {
				// 値が文字列の場合
				$name = $val;
				$enum = new $className($maxId++, $name);
			}
			$enumsId[$enum->id] = $enum;
			$enumsName[$enum->name] = $enum;
			// 名称と同名の静的メンバにセットする
			$class->setStaticPropertyValue($name, $enum);
		}
		return [$enumsId, $enumsName];
	}
	/**
	 * ID or 名称でEnumを得る
	 * @param array $enums [0=>[ID=>Enum, ...], 1:=>[名称=>Enum, ...]]
	 * @param int|string $idName ID or 名称
	 * @param ?Enum 見つかったEnum null:見つからなかった
	 */
	protected static function getBase(array $enums, int|string $idName): ?Enum {
		if (is_int($idName)) $idx = 0;
		if (is_string($idName)) $idx = 1;
		if (isset($enums[$idx][$idName])) return $enums[$idx][$idName];
		return null;
	}
}
