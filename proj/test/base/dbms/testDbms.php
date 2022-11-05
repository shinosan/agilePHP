<?php
require_once __DIR__ . '/_dir.php';
require_once BASE::DBMS . 'MariaDb.php';

class ZipAddress {
	/** @var string  */
	const org_code = 'org_code';
	/** @var string  */
	const zip_code_old = 'zip_code_old';
	/** @var string  */
	const zip_code = 'zip_code';
	/** @var string  */
	const pref_kana = 'pref_kana';
	/** @var string  */
	const city_kana = 'city_kana';
	/** @var string  */
	const town_kana = 'town_kana';
	/** @var string  */
	const pref = 'pref';
	/** @var string  */
	const city = 'city';
	/** @var string  */
	const town = 'town';
	/** @var string  */
	const flg_zips = 'flg_zips';
	/** @var string  */
	const flg_koaza = 'flg_koaza';
	/** @var string  */
	const flg_choume = 'flg_choume';
	/** @var string  */
	const flg_towns = 'flg_towns';
	/** @var string  */
	const flg_update = 'flg_update';
	/** @var string  */
	const flg_reason = 'flg_reason';

	const FIELD_TYPES = [
		self::org_code => TypeUtil::STRING,
		self::zip_code_old => TypeUtil::STRING,
		self::zip_code => TypeUtil::STRING,
		self::pref_kana => TypeUtil::STRING,
		self::city_kana => TypeUtil::STRING,
		self::town_kana => TypeUtil::STRING,
		self::pref => TypeUtil::STRING,
		self::city => TypeUtil::STRING,
		self::town => TypeUtil::STRING,
		self::flg_zips => TypeUtil::INT,
		self::flg_koaza => TypeUtil::INT,
		self::flg_choume => TypeUtil::INT,
		self::flg_towns => TypeUtil::INT,
		self::flg_update => TypeUtil::INT,
		self::flg_reason => TypeUtil::INT,
	];
}

use ZipAddress as ZA;

const Q_ZIP_ADDR = [
	[ZA::org_code, Op::EQ, ZA::org_code],
	Op::AND,
	[ZA::zip_code_old, Op::EQ, ZA::zip_code_old],
	Op::AND,
	[ZA::zip_code, Op::EQ, ZA::zip_code],
	Op::AND,
	[ZA::pref_kana, Op::EQ, ZA::pref_kana],
	Op::AND,
	[ZA::city_kana, Op::EQ, ZA::city_kana],
	Op::AND,
	[ZA::town_kana, Op::EQ, ZA::town_kana],
	Op::AND,
	[ZA::pref, Op::EQ, ZA::pref],
	Op::AND,
	[ZA::city, Op::EQ, ZA::city],
	Op::AND,
	[ZA::town, Op::EQ, ZA::town],
	Op::AND,
	[ZA::flg_zips, Op::EQ, ZA::flg_zips],
	Op::AND,
	[ZA::flg_koaza, Op::EQ, ZA::flg_koaza],
	Op::AND,
	[ZA::flg_choume, Op::EQ, ZA::flg_choume],
	Op::AND,
	[ZA::flg_towns, Op::EQ, ZA::flg_towns],
	Op::AND,
	[ZA::flg_update, Op::EQ, ZA::flg_update],
	Op::AND,
	[ZA::flg_reason, Op::EQ, ZA::flg_reason],
];

$dbms = MariaDb::self();

$params = [
	ZA::pref => '東京都',
	ZA::city => '千代田区',
	ZA::flg_choume => 1,
];
$query = new Query('t_zip_address_org', ZA::FIELD_TYPES, Q_ZIP_ADDR, [ZA::org_code, ZA::zip_code]);
$sql = $dbms->makeSelect($query, $params, false);
echo $sql . PHP_EOL;

$dbms->connect();
$dbms->beginTransaction();
$rows = $dbms->select($query, $params);
if (is_array($rows)) {
	foreach ($rows as $row) {
		foreach ($row as $key => $value) {
			$str = StrUtil::toString($value);
			echo $key . ':' . $str . PHP_EOL;
		}
	}
} else {
	echo $rows;
}
$dbms->commit();
$dbms->disconnect();
