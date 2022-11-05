<?php
require_once __DIR__ . '/_dir.php';
require_once BASE::UTIL . 'Results.php';

/**
 * CSV操作ユーティリティ
 */
class CsvUtil {
	/** インスタンス化禁止 */
	private function __construct() {
	}

	public static function load(string $path, string $enc = self::SJIS_UTF8): array {
		$csvList = [];
		try {
			$input = fopen($path, 'r');
			while (true) {
				$line = fgets($input);
				if ($line === false) break;

				$csv = self::convert($line, $enc);
				$csvList[] = $csv;
			}
			fclose($input);
			return $csvList;
		} catch (Exception $ex) {
			return [];
		}
	}

	const SJIS_UTF8 = 'sjis-utf8';
	const NONE = '';

	public static int $lines = 0;

	public static function convert(string $line, string $enc = self::SJIS_UTF8): array {
		self::$lines++;
		if ($enc == self::SJIS_UTF8) {
			$line = StrUtil::toUTF8($line);
		}
		$line = StrUtil::replace('"', '', $line);
		$line = trim($line);
		$csv = explode(',', $line);
		return $csv;
	}

	public static function save(string $path, array $csvList, string $header = ''): int {
		try {
			$fp = fopen($path, 'w');
			$length = 0;
			if ($header) fputs($fp, $header);
			foreach ($csvList as $csv) {
				$length += fputcsv($fp, $csv);
			}
			fclose($fp);
			return $length;
		} catch (Exception $ex) {
			return -1;
		}
	}
}
