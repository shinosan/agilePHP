<?php
require_once __DIR__ . '/_dir.php';
require_once BASE::UTIL . 'ArrayUtil.php';

/**
 * enum 操作ユーティリティ  
 * enum を名前やID(定義順)で取得したり、対応するIDを取得する  
 * 定義したenum毎に派生クラスを作成する
 * @example /proj/test/base/util/EnumUtilTest.php 下記のように派生クラスを作成
 * ```php
 * enum Sex {
 *     case MALE;
 *     case FEMALE;
 * }
 * class SexUtil extends EnumUtil {
 *     public static function register(Sex ...$enums) {
 *         self::registerBase(self::$byIdList, self::$byNameList, ...$enums);
 *     }
 *     public static function get(int|string $key): Sex {
 *         return self::getBase(self::$byIdList, self::$byNameList, $key);
 *     }
 *     public static function id(Sex $enum): int {
 *         return self::idBase(self::$byNameList, $enum);
 *     }
 *     public static function getEnums(): array {
 *         return self::$byIdList;
 *     }
 *     private static array $byIdList = [];
 *     private static array $byNameList = [];
 * }
 * SexUtil::register(Sex::cases());
 * ```
 */
class EnumUtil {
	/**
	 * enumを登録する
	 * @param array &$byIdList IDでenumを得るための配列
	 * @param array &$byNameList 名称でenumを得るための配列
	 * @param array $enums 登録するenumのリスト
	 */
	protected static function registerBase(array &$byIdList, array &$byNameList, mixed ...$enums) {
		foreach ($enums as $idx => $enum) {
			if ($enum === null) continue;

			$byIdList[$idx] = $enum;
			$byNameList[$enum->name] = $enum;
		}
	}

	/**
	 * IDまたは名称で対応するenumを返す
	 * @param array &$byIdList IDでenumを得るための配列
	 * @param array &$byNameList 名称でenumを得るための配列
	 * @param int|string $key enumのIDまたは名称
	 * @return mixed 対応するenum
	 */
	protected static function getBase(array $byIdList, array $byNameList, int|string $key): mixed {
		$enum = is_int($key) ? ArrayUtil::get($byIdList, $key) : ArrayUtil::get($byNameList, $key);
		return $enum;
	}

	/**
	 * 指定したenumのIDを返す
	 * @param array &$byNameList 名称でenumを得るための配列
	 * @param mixed $enum
	 * @return int enumのID
	 */
	protected static function idBase(array $byNameList, mixed $enum): int {
		$names = array_keys($byNameList);
		$id = array_search($enum->name, $names);
		return ($id === false) ? -1 : $id;
	}
}
