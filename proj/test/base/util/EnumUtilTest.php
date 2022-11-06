<?php
require_once __DIR__ . '/_dir.php';
require_once BASE::UTIL . 'EnumUtil.php';
require_once BASE::UTIL . 'TypeUtil.php';

class TypesUtil extends EnumUtil {
	public static function register(array $enums) {
		self::registerBase(self::$byIdList, self::$byNameList, ...$enums);
	}
	public static function get(int|string $key): Types {
		return self::getBase(self::$byIdList, self::$byNameList, $key);
	}
	public static function id(Types $enum): int {
		return self::idBase(self::$byNameList, $enum);
	}
	public static function getEnums(): array {
		return self::$byIdList;
	}
	private static array $byIdList = [];
	private static array $byNameList = [];
}
TypesUtil::register(Types::cases());

enum Sex {
	case MALE;
	case FEMALE;
}
class SexUtil extends EnumUtil {
	public static function register(array $enums) {
		self::registerBase(self::$byIdList, self::$byNameList, ...$enums);
	}
	public static function get(int|string $key): Sex {
		return self::getBase(self::$byIdList, self::$byNameList, $key);
	}
	public static function id(Sex $enum): int {
		return self::idBase(self::$byNameList, $enum);
	}
	public static function getEnums(): array {
		return self::$byIdList;
	}
	private static array $byIdList = [];
	private static array $byNameList = [];
}
SexUtil::register(Sex::cases());

const LF = "\n";

function printType(Types $status) {
	echo TypesUtil::id($status) . ':' . $status->name . LF;
}
function printSex(Sex $status) {
	echo SexUtil::id($status) . ':' . $status->name . LF;
}
printType(Types::BOOL);
printSex(Sex::FEMALE);
$type = TypesUtil::get(2);
printType($type);
$sex = SexUtil::get(0);
printSex($sex);
