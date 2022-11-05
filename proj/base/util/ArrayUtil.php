<?php
require_once __DIR__ . '/_dir.php';
require_once BASE::UTIL . 'TypeUtil.php';

/**
 * 配列の操作ユーティリティ
 */
class ArrayUtil {
	/** インスタンス化禁止 */
	private function __construct() {
	}

	/**
	 * 配列の要素を取得する
	 * @param mixed $array アクセスする配列であるはずのもの
	 * @param mixed $key 要素のキーであるはずのもの
	 * @param mixed $def = null キーが存在しなかった場合のデフォルト値
	 * @return mixed 要素の値
	 */
	public static function get(mixed $array, mixed $key, mixed $def = null): mixed {
		$val = $def;
		if (self::enable($array, $key)) {
			$val = $array[$key];
		}
		return $val;
	}

	/**
	 * キーを指定して配列の要素を削除する
	 * @param ?array $array アクセスする配列
	 * @param int|string|null $key 要素のキー
	 * @return bool true:削除できた場合
	 */
	public static function remove(?array $array, int|string|null $key): bool {
		if (self::enable($array, $key)) {
			unset($array[$key]);
			return true;
		}
		return false;
	}
	/**
	 * 値を指定して配列の要素を削除する
	 * @param ?array $array アクセスする配列
	 * @param mixed $value 要素の値
	 * @return bool true:削除できた場合
	 */
	public static function removeValue(?array $array, mixed $value) {
		$idx = array_search($array, $value);
		if ($idx !== false) {
			unset($array[$idx]);
			return true;
		}
		return false;
	}
	/**
	 * 配列とキーが有効かどうか
	 * @param mixed $array アクセスする配列であるはずのもの
	 * @param mixed $key 要素のキーであるはずのもの
	 * @return bool true:有効な場合
	 */
	public static function enable(mixed $array, mixed $key): bool {
		if (!$array || !is_array($array)) return false;
		if ($key === null || !is_string($key) && !is_numeric($key)) return false;
		return isset($array[$key]);
	}

	/**
	 * 配列が連想配列(keyが文字列)かどうか
	 * @param array $array 対象の配列
	 * @return bool true:連想配列
	 */
	public static function isKeyValue(array $array): bool {
		$result = false;
		$keys = array_keys($array);
		$result = is_string($keys[0]);
		return $result;
	}

	/**
	 * 配列を指定した分離記号で結合し、文字列化する。
	 * 配列の中に配列があると、再帰処理する。
	 * キーが文字列なら連想配列として処理する。
	 * objectの場合、 DateTime と Model に対応。
	 * @param array $array 結合する配列
	 * @param string $delimiter 分離記号(省略=',')
	 * @param int $offset スキップする数(省略=0)
	 * @param int $limit 配列を結合する最大数(省略=PHP_INT_MAX)
	 * @return string 結合した文字列
	 */
	public static function toString(array $array, string $delimiter = ',', int $offset = 0, int $limit = PHP_INT_MAX): string {
		$str = '';
		$dlm = '';
		$myOffset = $offset;
		foreach ($array as $key => $val) {
			if ($myOffset-- > 0) continue;
			if ($limit-- <= 0) break;

			$val = StrUtil::toString($val, $delimiter);
			if (is_string($key)) {
				$str .= ($dlm . $key . ':' . $val);
			} else {
				$str .= ($dlm . $val);
			}
			$dlm = $delimiter;
		}
		return $str;
	}
	/**
	 * 連想配列のキーは無視して、値をのみを数値インデックスの配列として出力する
	 * @param array $array 連想配列
	 * @return array 数値インデックスの配列
	 */
	public static function values(array $array): array {
		$output = [];
		foreach ($array as $val) {
			$output[] = $val;
		}
		return $output;
	}

	/**
	 * 配列の値を指定した分離記号で結合し、文字列化する。
	 * キーが文字列でも、値のみを対象とする。
	 * @param array $array 結合する配列
	 * @param string $delimiter 分離記号(省略=',')
	 * @return string 結合した文字列
	 */
	public static function valueString(array $array, string $delimiter = ','): string {
		$str = '';
		$dlm = '';
		foreach ($array as $val) {
			$val = self::toString($val, $delimiter);
			$str .= ($dlm . $val);
			$dlm = $delimiter;
		}
		return $str;
	}
}
