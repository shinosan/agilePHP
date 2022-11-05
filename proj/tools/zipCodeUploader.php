<?php
require_once __DIR__ . '/dir.php';
require_once BASE::UTIL . 'CsvUtil.php';
require_once BASE::DBMS . 'MariaDb.php';

class ZipCodeUploader {
	public function execute(string $inputPath): string {
		$ret = $this->load($inputPath);
		if ($ret) {
			$ret = $this->convert();
		}
		if ($ret) {
			$ret = $this->create();
		}
		return $ret ? 'success' : 'error';
	}
	protected function load(string $inputPath): bool {
		$this->csvList = CsvUtil::load($inputPath, CsvUtil::NONE);
		return count($this->csvList) > 0;
	}
	private array $csvList = [];

	const TYPES = [
		'org_code' => TypeUtil::STRING,
		'zip_code_old' => TypeUtil::STRING,
		'zip_code' => TypeUtil::STRING,
		'pref_kana' => TypeUtil::STRING,
		'city_kana' => TypeUtil::STRING,
		'town_kana' => TypeUtil::STRING,
		'pref' => TypeUtil::STRING,
		'city' => TypeUtil::STRING,
		'town' => TypeUtil::STRING,
		'flg_zips' => TypeUtil::INT,
		'flg_koaza' => TypeUtil::INT,
		'flg_choume' => TypeUtil::INT,
		'flg_towns' => TypeUtil::INT,
		'flg_update' => TypeUtil::INT,
		'flg_reason' => TypeUtil::INT,
	];
	protected function convert(): bool {
		$ret = true;
		$fields = array_keys(self::TYPES);
		foreach ($this->csvList as $csv) {
			$row = [];
			$idx = 0;
			foreach ($csv as $val) {
				$fld = $fields[$idx++];
				$row[$fld] = $val;
			}
			$this->paramsList[] = $row;
		}
		return $ret;
	}
	private array $paramsList = [];

	protected function create(): bool {
		$ret = MariaDb::self()->connect();
		if ($ret >= 0) {
			$ret = MariaDb::self()->beginTransaction();
		}
		if ($ret >= 0) {
			$ret = MariaDb::self()->create('t_zip_address_org', $this->paramsList, self::TYPES, true);
		}
		if ($ret >= 0) {
			$ret = MariaDb::self()->commit();
		}
		if ($ret >= 0) {
			$ret = MariaDb::self()->disconnect();
		}
		return $ret >= 0;
	}
}
if ($argc < 2) {
	echo 'usage: php .\\ZipCodeUploader.php csvFilePath' . PHP_EOL;
	exit;
}
$zip = new ZipCodeUploader();
$zip->execute($argv[1]);
