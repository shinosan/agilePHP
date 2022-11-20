<?php
require_once __DIR__ . '/_dir.php';
require_once BASE::UTIL . 'StrUtil.php';
require_once BASE::UTIL . 'Logger.php';

final class DateUtil {
	/** インスタンス化を禁止 */
	private function __construct() {
	}

	/** @var string 標準の書式(日時) */
	const YmdHis = 'Y-m-d H:i:s';

	/** @var string 標準の書式(日付のみ) */
	const Ymd = 'Y-m-d';

	/** @var string 標準の書式(時刻のみ) */
	const His = 'H:i:s';

	/** @var string 標準の書式(日時マイクロ秒) */
	const YmdHisU = 'Y-m-d H:i:s.u';

	/** @var string 標準の書式(タイムスタンプ) */
	const Timestamp = 'YmdHisu';

	/**
	 * DateTimeにキャストする
	 * @param mixed $val キャスト対象
	 * @return ?DateTime
	 */
	public static function cast(mixed $val): ?DateTime {
		return ($val instanceof DateTime) ? $val : null;
	}

	/**
	 * 年を取得
	 * @param DateTime $date 日時
	 * @return int 年
	 */
	public static function year(DateTime $date): int {
		$str = $date->format('Y');
		return intval($str);
	}
	/**
	 * 憑きを取得
	 * @param DateTime $date 日時
	 * @return int 月
	 */
	public static function month(DateTime $date): int {
		$str = $date->format('m');
		return intval($str);
	}
	/**
	 * 日を取得
	 * @param DateTime $date 日時
	 * @return int 日
	 */
	public static function day(DateTime $date): int {
		$str = $date->format('d');
		return intval($str);
	}
	/**
	 * 時を取得
	 * @param DateTime $date 日時
	 * @return int 時
	 */
	public static function hour(DateTime $date): int {
		$str = $date->format('H');
		return intval($str);
	}
	/**
	 * 分を取得
	 * @param DateTime $date 日時
	 * @return int 分
	 */
	public static function minute(DateTime $date): int {
		$str = $date->format('i');
		return intval($str);
	}
	/**
	 * 秒を取得
	 * @param DateTime $date 日時
	 * @return int 秒
	 */
	public static function second(DateTime $date): int {
		$str = $date->format('S');
		return intval($str);
	}
	/**
	 * マイクロ秒を取得
	 * @param DateTime $date 日時
	 * @return int マイクロ秒
	 */
	public static function micorosec(DateTime $date): int {
		$str = $date->format('u');
		return intval($str);
	}
	/**
	 * 年,月,日,時,分,秒の数字から日時型を得る
	 * @param int $year   年
	 * @param int $month  月
	 * @param int $day    日
	 * @param int $hour   時
	 * @param int $mitute 分
	 * @param int $sec    秒
	 * @param int $msec   マイクロ秒
	 * @return DateTime 
	 */
	public static function new(int $year, int $month = 1, int $day = 1, int $hour = 0, int $minute = 0, int $sec = 0, int $msec = 0): DateTime {
		$str = $year . '-' . $month . '-' . $day . ' ' . $hour . ':' . $minute . ':' . $sec . '.' . $msec;
		$date = new DateTime($str);
		return $date;
	}

	/**
	 * 日時に変換する。
	 * 変換できるもの : 日時として有効な文字列 or それに変換可能な数値、配列。
	 * 配列 : [年,月,日(,時,分,秒)] or ['Y'=>年,'m'=>月,'d'=>日(,'H'=>時,'i'=>分,'s'=>秒)]
	 * @param mixed $val 変換する値
	 * @return null|DateTime null:変換失敗
	 */
	public static function toDateTime(mixed $val): ?DateTime {
		if ($val === null) return null;

		$result = null;
		if (is_object($val) && get_class($val) == 'DateTime') {
			// DateTimeならそのまま
			$result = $val;
		} else if (is_array($val)) {
			if (ArrayUtil::isKeyValue($val)) {
				// 連想配列の場合
				$val = $val['Y'] . '-' . $val['m'] . '-' . $val['d'] . isset($val['H']) ? (' ' . $val['H'] . ':' . $val['i'] . ':' . $val['s']) : '';
			} else {
				// 普通の配列ならYmd(His)の文字列にする
				if (count($val) > 3) {
					$val = ArrayUtil::toString($val, '-', 0, 3) . ' ' . ArrayUtil::toString($val, ':', 3);
				} else {
					$val = ArrayUtil::toString($val, '-');
				}
			}
		} else {
			// それ以外は、とりあえず文字列にする
			$val = '' . $val;
		}
		// 文字列なら書式をチェックして変換
		if (is_string($val) && self::isDateTime($val)) {
			try {
				$result = new DateTime($val);
			} catch (\Throwable $e) {
				$result = null; // 変換できなかった場合
			}
		}
		return $result;
	}

	/**
	 * 日時に変換できる値を, 指定した書式に変換する
	 * @param mixed $val 日時に変換できる値
	 * @param string $format 書式(省略=self::YmdHis)
	 * @return string 変換できないと空文字列
	 */
	public static function format(mixed $val, string $format = self::YmdHis): string {
		$date = self::toDateTime($val);
		if ($date) {
			return self::toString($date, $format);
		} else {
			return '';
		}
	}

	/**
	 * DateTimeを文字列に変換
	 * @param DateTime $val 変換する値
	 * @param string $format 書式(省略=self::YmdHis)
	 * @return string
	 */
	public static function toString(DateTime $val, string $format = null): string {
		if (!$format) {
			$format = self::YmdHis;
		}
		$result = $val ? $val->format($format) : '';
		return $result;
	}


	/** @var string グレゴリオ暦の開始日 */
	public const GREGORIAN_START = '1582-10-15';
	/** @var int グレゴリオ暦の開始年 */
	public const GREGORIAN_YEAR = 1582;

	/** @var array 各月の日数 */
	public const DAYS_OF_MONTH = [0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

	/**
	 * 日時に変換可能な文字列かどうかを返す。
	 * @param string $value チェックする文字列
	 * @return bool true:変換可能
	 */
	public static function isDateTime(string $value): bool {
		$value = self::convEnglishDate($value); // 米国式日付を世界標準に変更
		$dateTime = self::divideDateStr($value);
		if (!isset($dateTime[0])) {
			return false;
		}

		$ymd = $dateTime[0];
		$year  = intval($ymd[0]);
		$month = intval($ymd[1]);
		$day   = intval($ymd[2]);
		$leap = self::isLeapYear($year);
		if (!self::between($year, self::GREGORIAN_YEAR, 9999)) {
			return false;
		}
		if (!self::between($month, 1, 12)) {
			return false;
		}
		if ($month == 2) {
			if ($leap) {
				if (!self::between($day, 1, 29)) {
					return false;
				}
			} else {
				if (!self::between($day, 1, 28)) {
					return false;
				}
			}
		} else {
			if (!self::between($day, 1, self::DAYS_OF_MONTH[$month])) {
				return false;
			}
		}
		if (!isset($dateTime[1])) {
			return true;
		}

		// 時刻あり
		$his = $dateTime[1];
		$hour   = intval($his[0]);
		$minute = intval($his[1]);
		$sec    = floatval($his[2]);
		if (!self::between($hour, 0, 23)) {
			return false;
		}
		if (!self::between($minute, 0, 59)) {
			return false;
		}
		if (!self::between($sec, 0, 59.999999)) {
			return false;
		}
		return true;
	}

	/**
	 * 指定した年が閏年かどうかを返す。
	 * @param int $year 年
	 * @return bool true:閏年
	 */
	public static function isLeapYear(int $year): bool {
		if ($year % 400 === 0) {
			return true;
		}
		if ($year % 200 === 0) {
			return false;
		}
		if ($year % 4 === 0) {
			return true;
		}
		return false;
	}

	private static function between(int|float $value, int|float $min, int|float $max): bool {
		return ($value >= $min && $value <= $max);
	}

	/**
	 * 日時を表す文字列を、[年月日, 時分秒]の配列に切り分ける
	 * @param string $str 日時の文字列
	 * @return array [ [年,月,日], ([時,分,秒]) ] 時分秒はないケースもあり
	 */
	private static function divideDateStr(string $str): array {
		$dateTime = [];
		$str = str_replace(['T', '/'], [' ', '-'], $str);
		if (StrUtil::exists($str, '-')) {
			// ハイフン、コロンで区切られた形式
			$array = explode(' ', $str);
			$dateTime[] = explode('-', $array[0]);
			if (isset($array[1])) { // 時分秒あり
				$dateTime[] = explode(':', $array[1]);
			}
		} else {
			// 数字が連続する形式
			$len = strlen($str);
			if ($len >= 8) {
				$date = [];
				$date[] = substr($str, 0, 4);
				$date[] = substr($str, 4, 2);
				$date[] = substr($str, 6, 2);
				$dateTime[] = $date;
			}
			if ($len > 8) { // 時分秒あり
				$time = [];
				$time[] = substr($str, 8, 2);
				$time[] = substr($str, 10, 2);
				$time[] = substr($str, 12);
				$dateTime[] = $time;
			}
		}
		return $dateTime;
	}

	const WEEK = ['Sun,', 'Mon,', 'Tue,', 'Wed,', 'Thu,', 'Fri,', 'Sat,'];
	const MONTHS = ['', 'Jun', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

	protected static function convEnglishDate(string $value): string {
		if (empty($value)) return $value;

		$div = explode(' ', $value);
		if (array_search($div[0], self::WEEK) === false) return $value;

		$day = ArrayUtil::get($div, 1, '');
		$month = ArrayUtil::get($div, 2, '');
		$month = array_search($month, self::MONTHS);
		$year = ArrayUtil::get($div, 3, '');
		$time = ArrayUtil::get($div, 4, '');

		return $year . '-' . $month . '-' . $day . ' ' . $time;
	}

	private static ?DateTime $now = null;

	/**
	 * 指定した日時を現在の日時にセットする
	 * @param string $date 現在にしたい日時文字列 省略:設定ファイルで指定する
	 */
	public static function setNow(string $date = '') {
		if (!$date) {
			$date = ConfigUtil::get('NOW_DATE');
		}
		if ($date) {
			$val = new DateTime($date);
			self::$now = $val;
		}
	}
	/**
	 * テスト用の現在の日時をクリアする
	 */
	public static function clearNow() {
		self::$now = null;
	}

	/**
	 * 現在の日時を得る
	 * @return DateTime
	 */
	public static function now(): DateTime {
		if (self::$now) {
			return self::$now;
		}
		return new DateTime('now');
	}

	/**
	 * 空値を意味する日時を返す
	 * @return DateTime
	 */
	public static function empty(): DateTime {
		if (!self::$empty) {
			self::$empty = new DateTime('0000-01-01');
		}
		return self::$empty;
	}
	private static ?DateTime $empty = null;

	/**
	 * 今日の日付を得る。時分秒ミリ秒は０。
	 * @param string $duration 今日から前後にずらす 省略=ずらさない ('n days|months|years')
	 * @param bool $sub 前にずらす 省略=後ろにずらす
	 * @return DateTime
	 */
	public static function today(string $duration = null, bool $sub = false): DateTime {
		$date = self::now();
		$date->setTime(0, 0, 0, 0);
		if ($duration) {
			$interval = DateInterval::createFromDateString($duration);
			if ($sub) {
				$date->sub($interval);
			} else {
				$date->add($interval);
			}
		}
		return $date;
	}


	/**
	 * PHP 8 以前で、ミリ秒までの現在日時を文字列で得る
	 * @param string $format 書式 (省略=self::YmdHis)
	 * @return string
	 */
	public static function nowStr(string $format = self::YmdHis): string {
		$result = '';
		$date = new DateTime();
		$result = $date->format($format);
		$ms = microtime(true) . ''; // 小数点以下まで文字列で得る
		$array = explode('.', $ms);
		$result .= '.' . substr($array[1], 0, 3); // 小数点以下3桁
		return $result;
	}

	/**
	 * 和暦で文字列化する。
	 * @param DateTime $val 日時型
	 * @param string $format 書式(省略:YmdHis)
	 * @param bool $isShort 英字の略称を使う(省略=false)
	 * @return string
	 */
	public static function toStrJP(DateTime $val, string $format = null, bool $isShort = false): string {
		if (!$format) $format = self::YmdHis;

		// 年号を検索する
		$era = EraName::find($val);
		if ($era == null) return '';

		// 西暦での年月日と時刻を得る
		$dateTime = $val->format(self::YmdHis);
		$array = explode(' ', $dateTime);
		$date = explode('-', $array[0]); // 年月日
		$time = explode(':', $array[1]); // 時刻

		// 和暦の年月日
		$date[0] = $era->yearJP($val, $isShort);

		// 書式に埋め込む
		$result = $format;
		$result = StrUtil::replace('Y', $date[0], $result);
		$result = StrUtil::replace('m', $date[1], $result);
		$result = StrUtil::replace('d', $date[2], $result);
		$result = StrUtil::replace('H', $time[0], $result);
		$result = StrUtil::replace('i', $time[1], $result);
		$result = StrUtil::replace('s', $time[2], $result);

		return $result;
	}

	/**
	 * 左辺と右辺の日時の間隔を取り、書式に従って文字列化、必要なら整数値で返す。
	 * @param DateTime $lhs 左辺
	 * @param DateTime $rhs 右辺
	 * @param string $fmt 書式 省略=総日数
	 * @param bool $toInt 結果を整数に変換 省略=変換する
	 * @return int|string 間隔を書式で変換した数値or文字列
	 */
	public static function diff(DateTime $lhs, DateTime $rhs, string $fmt = DateFmt::DIFF_DAYS, bool $toInt = true): int|string {
		$interval = $lhs->diff($rhs);
		$str = $interval->format($fmt);
		return $toInt ? intval($str) : $str;
	}
}
/**
 * 年号の情報
 */
class EraName {
	private $name;
	private $shortName;
	private $start;

	/** @property array 年号の配列 */
	private static $eraNames = [];

	/**
	 * コンストラクタ config.ini にERA_NAMESのキーで、'|'で区切って新しい年号から登録すること。
	 * @param string $eraStr 書式： "名称:英字略称,開始年月日" 
	 */
	public function __construct(string $eraStr) {
		$array = explode(':', $eraStr);
		$this->name = $array[0];
		$array = explode(',', $array[1]);
		$this->shortName = $array[0];
		$this->start     = new DateTime($array[1]);
	}

	/**
	 * 日付で年号を検索する。
	 * @param DateTime $date
	 * @return null|EraName
	 */
	public static function find(DateTime $date): ?EraName {
		// 初回のみ年号を読み込む
		if (!self::$eraNames) {
			$eraNames = ConfigUtil::get('ERA_NAMES');
			$array = explode('|', $eraNames);
			foreach ($array as $eraStr) {
				self::$eraNames[] = new EraName($eraStr);
			}
		}
		// 検索
		foreach (self::$eraNames as $era) {
			if ($era->start() < $date) {
				return $era;
			}
		}
		return null;
	}

	/**
	 * 年号の名称
	 * @return string
	 */
	public function name(): string {
		return $this->name;
	}
	/**
	 * 年号の英字名称
	 * @return string
	 */
	public function shortName(): string {
		return $this->shortName;
	}
	/**
	 * 年号の開始年月日
	 * @return DateTime
	 */
	public function start(): DateTime {
		return $this->start;
	}
	/**
	 * 和暦の年
	 * @param DateTime $date 日付
	 * @param bool $isShort 英字略称を使う(省略=false)
	 * @return string
	 */
	public function yearJP(DateTime $date, bool $isShort = false): string {
		$nengo  = $isShort ? $this->shortName() : $this->name();
		$year0  = intval($date->format('Y'));
		$year   = $year0 - intval($this->start()->format('Y')) + 1;
		return $nengo . $year;
	}
}

/**
 * 日時書式
 */
class DateFmt {
	//// 以下はDateUtil::toString()で使用する書式

	/** インスタンス化を禁止 */
	private function __construct() {
	}

	/** @var string 書式 yyyy-mm-dd HH:mm:ss.uuuuuu */
	const YMDHISU = 'Y-m-d H:i:s.u';
	/** @var string 書式 yyyy-mm-ddTHH:mm:ss.uuuuuu */
	const YMDHISU_T = 'Y-m-dTH:i:s.u';

	/** @var string 書式 yyyy-mm-dd HH:mm:ss */
	const YMDHIS = 'Y-m-d H:i:s';
	/** @var string 書式 yyyy-mm-ddTHH:mm:ss */
	const YMDHIS_T = 'Y-m-dTH:i:s';

	/** @var string 書式 yyyy-mm-dd */
	const YMD = 'Y-m-d';

	/** @var string 書式 yyyymmddHHmmssuuuuuu */
	const TIMESTAMP = 'YmdHisu';

	/** @var string 書式 yyyymmddHHmmss */
	const LONG = 'YmdHis';

	/** @var string 書式 yyyymmdd */
	const SHORT = 'Ymd';

	//// 以下はDateUtil::diff()で使用する書式

	/** @var string 差分の総日数 */
	const DIFF_DAYS = '%a';

	/** @var string 差分の年 */
	const DIFF_YEAR = '%y';

	/** @var string 差分の月 */
	const DIFF_MONTH = '%M';

	/** @var string 差分の日 */
	const DIFF_DAY = '%M';

	/** @var string 差分の時 */
	const DIFF_HOUR = '%H';

	/** @var string 差分の分 */
	const DIFF_MINUTE = '%I';

	/** @var string 差分の秒 */
	const DIFF_SECOND = '%S';

	/** @var string 差分のマイクロ秒 */
	const DIFF_MICRO = '%F';
}
