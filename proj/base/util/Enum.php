<?php

/**
 * 数値と名称を紐づけるEnum基底クラス
 * @example /proj/test/base/util/EnumTest.php 下記のように派生クラスを作る
 * ```php
 * class Status extends Enum {
 *     const ALL = [
 *         [1, 'OK'],
 *         [-1, 'NG']
 *     ];
 *     public static Status $OK;
 *     public static Status $NG;
 *     public static function get(int|string $key): Status {
 *         return self::findByIdName(self::$enums, $key);
 *     }
 *     public static function all(): array {
 *         return self::$enums;
 *     }
 *     public static function initialize() {
 *         self::$enums = self::makeEnum(__CLASS__, self::ALL);
 *     }
 *     private static array $enums = [];
 * }
 * Status::initialize();
 * ```
 */
class Enum {
	/**
	 * @param int $id 数値
	 * @param string $name 名称
	 */
	protected function __construct(int $id, string $name) {
		$this->id = $id;
		$this->name = $name;
	}
	/** @var ?int 数値 */
	private ?int $id = null;
	/** @var ?string 名称 */
	private ?string $name = null;

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
	 * @param array $all Enum定義配列 [[ID,名称], ...] or [名称, ...]
	 * @param int $startId IDの開始(省略 = 0)
	 * @return array [0=>[ID=>Enum, ...], 1:=>[名称=>Enum, ...]]
	 */
	protected static function makeEnum(string $className, array $all, int $startId = 0): array {
		$maxId = $startId;
		$class = new ReflectionClass($className);
		$enumsId = [];
		$enumsName = [];
		foreach ($all as $seed) {
			if (is_array($seed)) {
				$id   = $seed[0];
				$name = $seed[1];
				$enum = new $className($id, $name);
				$maxId = $id + 1;
			}
			if (is_string($seed)) {
				$name = $seed;
				$enum = new $className($maxId++, $name);
			}
			$enumsId[$enum->id] = $enum;
			$enumsName[$enum->name] = $enum;
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
	protected static function findByIdName(array $enums, int|string $idName): ?Enum {
		if (is_int($idName)) $idx = 0;
		if (is_string($idName)) $idx = 1;
		if (isset($enums[$idx][$idName])) return $enums[$idx][$idName];
		return null;
	}
}
