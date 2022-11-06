<?php
require_once __DIR__ . '/_dir.php';
require_once BASE::UTIL . 'EnumUtil.php';

/**
 * データ型の列挙体
 */
enum Types {
	case NULL;
	case BOOL;
	case INT;
	case FLOAT;
	case STRING;
	case ARRAY;
	case ENUM;
	case DATETIME;
	case OBJECT;
	case MODEL;
}

/**
 * 型判定ユーティリティ
 */
class TypeUtil extends EnumUtil {
	/** インスタンス化禁止 */
	private function __construct() {
	}

	/**
	 * 値の型名を得て、 既知の型はTypes、未知の型は文字列 として返す
	 * @param mixed $value 型名を知りたい値
	 * @param bool $checkClass object クラス名を返す(省略=true)
	 * @return Types|string 型名
	 */
	public static function getType(mixed $value, bool $checkClass = true): Types|string {
		$type = gettype($value);
		switch ($type) {
			case 'boolean':
				return Types::BOOL;
			case 'integer':
				return Types::INT;
			case 'double':
				return Types::FLOAT;
			case 'array':
				return Types::ARRAY;
			case 'string':
				return Types::STRING;
			case 'object':
				if ($value instanceof DateTime) {
					return Types::DATETIME;
				}
				if ($value instanceof Model) {
					return Types::MODEL;
				}
				if (self::isEnum($value)) {
					return Types::ENUM;
				}
				$type = $checkClass ? get_class($value) : Types::OBJECT;
				break;
		}
		return ($value === null) ? Types::NULL : $type;
	}
	public static function convertVal(Types $type, ?string $val): mixed {
		switch ($type) {
			case Types::NULL:
				return null;
			case Types::BOOL:
				return is_bool($val) ? boolval($val) : null;
			case Types::INT:
				return is_int($val) ? intval($val) : null;
			case Types::FLOAT:
				return is_float($val) ? floatval($val) : null;
			case Types::STRING:
				return is_string($val) ? $val : null;
			case Types::ARRAY:
				return is_array($val) ? explode(',', $val) : null;
			case Types::ENUM:
				return self::toEnum($val);
			case Types::OBJECT:
				return is_object($val) ? $val : null;
			case Types::DATETIME:
				return DateUtil::isDateTime($val) ? DateUtil::toDateTime($val) : null;
			case Types::MODEL:
				return ($val instanceof Model) ? $val : null;
		}
		return null;
	}

	public static function isEnum(mixed $val): bool {
		try {
			$class = new ReflectionClass($val);
			return $class->isEnum();
		} catch (Exception) {
			return false;
		}
	}
	public static function toEnum(string $val): mixed {
		list($enum, $case) = explode('::', $val);
		foreach ($enum::cases() as $enum) {
			if ($enum->name == $case) return $enum;
		}
		return null;
	}

	//// EnumUtil メソッド ////

	/**
	 * ID or 名称でデータ型を得る
	 * @param int|string $key データ型のID or 名称
	 * @return Types データ型
	 */
	public static function get(int|string $key): Types {
		return self::getBase(self::$byIdList, self::$byNameList, $key);
	}
	/**
	 * データ型のIDを得る
	 * @param Types $enum データ型
	 * @return int ID
	 */
	public static function id(Types $enum): int {
		return self::idBase(self::$byNameList, $enum);
	}
	/**
	 * データ型の一覧を返す
	 * @return array Typesの一覧
	 */
	public static function getEnums(): array {
		return self::$byIdList;
	}
	/**
	 * データ型の列挙体を配列に登録する
	 * @params Types ...$enums 登録する列挙体
	 */
	public static function register(Types ...$enums) {
		self::registerBase(self::$byIdList, self::$byNameList, ...$enums);
	}
	private static array $byIdList = [];
	private static array $byNameList = [];
}
TypeUtil::register(Types::NULL, Types::BOOL, Types::INT, Types::FLOAT, Types::STRING, Types::ARRAY, Types::DATETIME, Types::OBJECT, Types::MODEL);
