<?php
require_once __DIR__ . '/_dir.php';
require_once BASE::UTIL . 'EnumUtil.php';

/**
 * 都道府県(Prefecture)の列挙型
 */
enum Prefs: string {
	case KAIGAI		= '海外';
	case HOKKAIDO	= '北海道';
	case AOMORI		= '青森県';
	case IWATE		= '岩手県';
	case MIYAGI		= '宮城県';
	case AKITA		= '秋田県';
	case YAMAGATA	= '山形県';
	case FUKUSHIMA	= '福島県';
	case IBARAKI	= '茨城県';
	case TOCHIGI	= '栃木県';
	case GUNMA		= '群馬県';
	case SAITAMA	= '埼玉県';
	case CHIBA		= '千葉県';
	case TOUKYOU	= '東京都';
	case KANAGAWA	= '神奈川県';
	case NIIGATA	= '新潟県';
	case TOYAMA		= '富山県';
	case ISHIKAWA	= '石川県';
	case FUKUI		= '福井県';
	case YAMANASHI	= '山梨県';
	case NAGANO		= '長野県';
	case GIFU		= '岐阜県';
	case SHIZUOKA	= '静岡県';
	case AICHI		= '愛知県';
	case MIE		= '三重県';
	case SHIGA		= '滋賀県';
	case KYOUTO		= '京都府';
	case OOSAKA		= '大阪府';
	case HYOUGO		= '兵庫県';
	case NARA		= '奈良県';
	case WAKAYAMA	= '和歌山県';
	case TOTTORI	= '鳥取県';
	case SHIMANE	= '島根県';
	case OKAYAMA	= '岡山県';
	case HIROSIMA	= '広島県';
	case YAMAGUCHI	= '山口県';
	case TOKUSHIMA	= '徳島県';
	case KAGAWA		= '香川県';
	case EHIME		= '愛媛県';
	case KOUCHI		= '高知県';
	case FUKUOKA	= '福岡県';
	case SAGA		= '佐賀県';
	case NAGASAKI	= '長崎県';
	case KUMAMOTO	= '熊本県';
	case OOITA		= '大分県';
	case MIYAZAKI	= '宮崎県';
	case KAGOSHIMA	= '鹿児島県';
	case OKINAWA	= '沖縄県';
}

/**
 * 都道府県(Prefecture)の列挙型の操作ユーティリティ  
 * 下記の機能を提供する
 * - ID(定義順),名称から列挙型を得る
 * - 列挙型のIDを得る
 * - 列挙型の一覧を返す
 */
class PrefsUtil {
	use EnumUtil;

	/**
	 * ID or 名称で列挙型を得る
	 * @param int|string $key ID or 名称
	 * @return Prefs 列挙型
	 */
	public static function get(int|string $key): Prefs {
		return self::getBase(self::$byIdList, self::$byNameList, $key);
	}
	/**
	 * 列挙型のIDを得る
	 * @param Prefs $enum 列挙型
	 * @return int ID 
	 */
	public static function id(Prefs $enum): int {
		return self::idBase(self::$byNameList, $enum);
	}
	/**
	 * IDがキーの列挙型一覧を返す
	 * @return array
	 */
	public static function getEnums(): array {
		return self::$byIdList;
	}
	/**
	 * 起動時に列挙型一覧を登録する
	 * @param array $enums 列挙型一覧
	 */
	public static function register(array $enums) {
		self::registerBase(self::$byIdList, self::$byNameList, ...$enums);
	}
	private function __construct() {
	}
	private static array $byIdList = [];
	private static array $byNameList = [];
}
PrefsUtil::register(Prefs::cases());
