<?php
require_once __DIR__ . '/_dir.php';
require_once BASE::UTIL . 'Results.php';
require_once BASE::UTIL . 'ArrayUtil.php';

/**
 * 設定ファイルを操作するユーティリティ
 */
class ConfigUtil {
	/** インスタンス化を禁じる */
	private function __construct() {
	}

	/** @var string デフォルトの設定ファイルのパス */
	const CONFIG_INI = 'config.ini';

	/** @property array Key/Valueペア配列の、ファイル名をキーにした配列 */
	private static array $keyVals = [];

	/**
	 * 指定した名前で始まるキーを配列で返す。名前を省略すると全件。
	 * @param string $prefix (省略:全件)
	 * @param string $path 設定ファイルのパス()
	 * @return array キーの配列
	 */
	public static function getKeys(string $prefix = null, string $path = null): array {
		self::load($path);
		$keys = array_keys(self::$keyVals);
		$filtered = [];
		foreach ($keys as $key) {
			if ($prefix == null || StrUtil::startsWith($key, $prefix)) {
				$filtered[] = $key;
			}
		}
		return $filtered;
	}

	/**
	 * 設定ファイルから指定したキーの値を読みだす
	 * @param string $key 取得したい値のキー
	 * @param string $fileName 設定ファイルのパス(省略=デフォルトのパス)
	 * @return string|null 読みだされた値(null:見つからない)
	 */
	public static function get(string $key, string $fileName = null): ?string {
		$keyVals = self::load($fileName);
		return ArrayUtil::get($keyVals, $key);
	}

	/**
	 * 設定ファイルからKey/Valueペア配列を読み込む
	 * @param string $fileName 設定ファイルのパス(省略=デフォルトのパス)
	 * @return array Key/Valueペア配列
	 */
	public static function load(string $fileName = null): array {
		// 設定ファイル名に対応したKey/Valueを取得
		$keyVals = ArrayUtil::get(self::$keyVals, $fileName);
		// 既に読みこんでいれば、それを返す
		if ($keyVals !== null) {
			return $keyVals;
		}
		if (!$fileName) {
			// ファイル名が指定されなければ、デフォルトを読み込む
			$fileName = self::CONFIG_INI;
		}
		$keyVals = parse_ini_file(BASE::CONF . $fileName, true, INI_SCANNER_TYPED);
		self::$keyVals[$fileName] = $keyVals;
		return $keyVals;
	}
}
