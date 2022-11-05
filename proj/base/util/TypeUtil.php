<?php

/**
 * 型判定ユーティリティ
 */
class TypeUtil {
	private function __construct() {
	}

	/** @var string null型 */
	const NULL = 'null';
	/** @var string bool型 */
	const BOOL = 'bool';
	/** @var string int型 */
	const INT = 'int';
	/** @var string float型 */
	const FLOAT = 'float';
	/** @var string string型 */
	const STRING = 'string';
	/** @var string 配列型 */
	const ARRAY = 'array';
	/** @var string 日時型 */
	const DATETIME = 'DateTime';
	/** @var string オブジェクト型 */
	const OBJECT = 'Object';
	/** @var string フレームワークが管理するモデル */
	const MODEL = 'Model';

	/**
	 * 値の型名を得て、 boolean=>bool, integer=>int, double=>float として返す
	 * @param mixed $value 型名を知りたい値
	 * @param bool $checkClass object クラス名を返す(省略=true)
	 * @return string 型名
	 */
	public static function getType(mixed $value, bool $checkClass = true): string {
		$type = gettype($value);
		switch ($type) {
			case 'boolean':
				return self::BOOL;
			case 'integer':
				return self::INT;
			case 'double':
				return self::FLOAT;
			case 'object':
				if ($value instanceof DateTime) {
					return self::DATETIME;
				}
				if ($value instanceof Model) {
					return self::MODEL;
				}
				$type = $checkClass ? get_class($value) : self::OBJECT;
				break;
		}
		return $type;
	}
	public static function convertVal(string $type, ?string $val): mixed {
		switch ($type) {
			case TypeUtil::NULL:
				return null;
			case TypeUtil::BOOL:
				return is_bool($val) ? boolval($val) : null;
			case TypeUtil::INT:
				return is_int($val) ? intval($val) : null;
			case TypeUtil::FLOAT:
				return is_float($val) ? floatval($val) : null;
			case TypeUtil::DATETIME:
				return DateUtil::isDateTime($val) ? DateUtil::toDateTime($val) : null;
		}
		return null;
	}
}
