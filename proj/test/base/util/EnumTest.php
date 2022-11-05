<?php
require_once __DIR__ . '/_dir.php';
require_once BASE::UTIL . 'Enum.php';

class Status extends Enum {
	const ALL = [
		[1, 'OK'],
		[-1, 'NG']
	];
	public static Status $OK;
	public static Status $NG;
	public static function get(int|string $key): Status {
		return self::findByIdName(self::$enums, $key);
	}
	public static function all(): array {
		return self::$enums;
	}
	public static function initialize() {
		self::$enums = self::makeEnum(__CLASS__, self::ALL);
	}
	private static array $enums = [];
}
Status::initialize();

class Prefecture extends Enum {
	const ALL = [
		'海外',
		'北海道',
		'青森県',
		'岩手県',
		'宮城県',
		'秋田県',
		'山形県',
		'福島県',
		'茨城県',
		'栃木県',
		'群馬県',
		'埼玉県',
		'千葉県',
		'東京都',
		'神奈川県',
		'新潟県',
		'富山県',
		'石川県',
		'福井県',
		'山梨県',
		'長野県',
		'岐阜県',
		'静岡県',
		'愛知県',
		'三重県',
		'滋賀県',
		'京都府',
		'大阪府',
		'兵庫県',
		'奈良県',
		'和歌山県',
		'鳥取県',
		'島根県',
		'岡山県',
		'広島県',
		'山口県',
		'徳島県',
		'香川県',
		'愛媛県',
		'高知県',
		'福岡県',
		'佐賀県',
		'長崎県',
		'熊本県',
		'大分県',
		'宮崎県',
		'鹿児島県',
		'沖縄県',
	];
	public static Prefecture $海外;
	public static Prefecture $北海道;
	public static Prefecture $青森県;
	public static Prefecture $岩手県;
	public static Prefecture $宮城県;
	public static Prefecture $秋田県;
	public static Prefecture $山形県;
	public static Prefecture $福島県;
	public static Prefecture $茨城県;
	public static Prefecture $栃木県;
	public static Prefecture $群馬県;
	public static Prefecture $埼玉県;
	public static Prefecture $千葉県;
	public static Prefecture $東京都;
	public static Prefecture $神奈川県;
	public static Prefecture $新潟県;
	public static Prefecture $富山県;
	public static Prefecture $石川県;
	public static Prefecture $福井県;
	public static Prefecture $山梨県;
	public static Prefecture $長野県;
	public static Prefecture $岐阜県;
	public static Prefecture $静岡県;
	public static Prefecture $愛知県;
	public static Prefecture $三重県;
	public static Prefecture $滋賀県;
	public static Prefecture $京都府;
	public static Prefecture $大阪府;
	public static Prefecture $兵庫県;
	public static Prefecture $奈良県;
	public static Prefecture $和歌山県;
	public static Prefecture $鳥取県;
	public static Prefecture $島根県;
	public static Prefecture $岡山県;
	public static Prefecture $広島県;
	public static Prefecture $山口県;
	public static Prefecture $徳島県;
	public static Prefecture $香川県;
	public static Prefecture $愛媛県;
	public static Prefecture $高知県;
	public static Prefecture $福岡県;
	public static Prefecture $佐賀県;
	public static Prefecture $長崎県;
	public static Prefecture $熊本県;
	public static Prefecture $大分県;
	public static Prefecture $宮崎県;
	public static Prefecture $鹿児島県;
	public static Prefecture $沖縄県;
	public static function get(int|string $key): Prefecture {
		return self::findByIdName(self::$enums, $key);
	}
	public static function all(): array {
		return self::$enums;
	}
	public static function initialize() {
		self::$enums = self::makeEnum(__CLASS__, self::ALL, 1);
	}
	private static array $enums = [];
}
Prefecture::initialize();

const LF = "\n";
function printStatus(Status $status) {
	echo $status->id() . ':' . $status->name() . LF;
}
function printPrefecture(Prefecture $status) {
	echo $status->id() . ':' . $status->name() . LF;
}
printStatus(Status::$OK);
printStatus(Status::$NG);
printPrefecture(Prefecture::$北海道);
printPrefecture(Prefecture::$沖縄県);

$status = Status::get('NG');
printStatus($status);
$pref = Prefecture::get(1);
printPrefecture($pref);
// printStatus($pref);
