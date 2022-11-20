<?php
require_once __DIR__ . '/_dir.php';

/**
 * 文字列とのシリアライズ・デシリアライズを提供する
 */
trait SerializeTrait {
	/**
	 * 整数を指定したバイト数の文字列に変換する
	 * @param int $int 整数値
	 * @param int $bytes バイト数
	 * @return string 指定したバイト数分'0'埋めされた文字列
	 */
	protected static function int2str(int $int, int $bytes): string {
		$result = '' . $int;
		$bytes -= strlen($result);
		if ($bytes > 0) {
			$result = str_repeat('0', $bytes) . $result;
		}
		return $result;
	}

	/**
	 * 文字列から指定した位置とバイト数を整数に変換する
	 * @param string $str 文字列
	 * @param int &$idx 整数化する位置
	 * @return int 指定したバイト数の整数
	 * @param int $bytes バイト数
	 */
	protected static function str2int(string $str, int &$idx, int $bytes): int {
		$val = intval(substr($str, $idx, $bytes));
		$idx += $bytes;
		return $val;
	}
}
