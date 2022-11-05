<?php
require_once __DIR__ . '/_dir.php';
require_once BASE::DBMS . 'Dbms.php';

/**
 * MariaDB操作のロジック
 */
class MariaDb extends Dbms {
	/**
	 * 自分自身のシングルトンを返す
	 */
	public static function self(): MariaDb {
		if (self::$self === null) {
			self::$self = new MariaDb();
		}
		return self::$self;
	}
	private static ?MariaDb $self = null;

	public function className(): string {
		return __CLASS__;
	}

	/** @var string const DB接続文字列テンプレート */
	const DB_CONNECTION_STR = 'mysql:dbname={dbname};host={host};port={port};charset={charset}';

	const CNF_DBNAME  = 'DBNAME';
	const CNF_HOST    = 'HOST';
	const CNF_PORT    = 'PORT';
	const CNF_CHARSET = 'CHARSET';

	/** 
	 * 接続文字列を返す
	 * @return string DB接続文字列テンプレート
	 */
	protected function getConnectionString(): string {
		$keyVal = [
			'dbname'  => ConfigUtil::get(self::CNF_DBNAME, self::CONFIG_FILE),
			'host'    => ConfigUtil::get(self::CNF_HOST, self::CONFIG_FILE),
			'port'    => ConfigUtil::get(self::CNF_PORT, self::CONFIG_FILE),
			'charset' => ConfigUtil::get(self::CNF_CHARSET, self::CONFIG_FILE),
		];
		$str = StrUtil::embed(self::DB_CONNECTION_STR, $keyVal);
		return $str;
	}
}
