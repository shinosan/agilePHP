<?php
require_once __DIR__ . '/_dir.php';
require_once BASE::UTIL . 'ArrayUtil.php';

/**
 * enum 操作ユーティリティ  
 * enum を名前やID(定義順)で取得したり、対応するIDを取得する  
 * 定義したenum毎に派生クラスを作成する
 */
class EnumUtil {
	/**
	 * enumを登録する
	 * @param array &$byIdList IDでenumを得るための配列
	 * @param array &$byNameList 名称でenumを得るための配列
	 * @param mixed ...$enums 登録するenum(可変長)
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
