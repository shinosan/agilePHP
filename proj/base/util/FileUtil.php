<?php
require_once __DIR__ . '/_dir.php';
require_once BASE::UTIL . 'Logger.php';

/**
 * ファイル操作ユーティリティ
 */
class FileUtil {
	use Logger;

	public static function self(): FileUtil {
		if (!self::$self) {
			self::$self = new FileUtil();
		}
		return self::$self;
	}
	private static ?FileUtil $self = null;

	/** インスタンス化を禁止 */
	private function __construct() {
	}

	/** 
	 * ファイルを一行ずつ読み出し、コールバックで処理して配列で返す。
	 * @param string $path 読み出すファイルのパス
	 * @param array $callback コールバック [インスタンス,'メソッド名'] method(int|string $idx, mixed $value) : mixed;
	 * @return array 処理結果
	 */
	public static function load(string $path, array $callback): array {
		self::self()->start(__METHOD__);
		$results = [];
		try {
			$fp = fopen($path, 'r');
			$key = 0;
			while (true) {
				$line = fgets($fp);
				if ($line === false) break;

				$line = trim($line);
				// コールバックに処理をさせる。
				$result = $callback($key, $line);
				// nullはスキップする
				if ($result !== null) {
					$results[$key] = $result;
				}
				// $key は callback 内で string に変わるかも。
				if (is_int($key)) $key++;
			}
			fclose($fp);
		} catch (\Exception $ex) {
			self::self()->error(__METHOD__, '読み込みエラー', $path, $ex);
		}
		return self::self()->end(__METHOD__, $results);
	}

	/** 
	 * 配列を一件ずつコールバックで処理して文字列とし、ファイルへ書き出す。
	 * @param string $path 書き出すファイルのパス
	 * @param array $callback コールバック [インスタンス,'メソッド名'] method(int|string $key, mixed $value) : string;
	 * @param array $values 処理する配列
	 * @return bool true:処理成功
	 */
	public static function save(string $path, array $callback, array $values): bool {
		self::self()->start(__METHOD__);
		$result = false;
		try {
			$fp = fopen($path, 'w');
			foreach ($values as $key => $value) {
				$value = $callback($key, $value);
				fputs($fp, $value);
			}
			fclose($fp);
			$result = true;
		} catch (\Exception $ex) {
			self::self()->error(__METHOD__, $ex->getMessage(), $path, $callback, $values);
		}
		return self::self()->end(__METHOD__, $result);
	}

	/**
	 * アップロードされた一時ファイルを移動してリネームする
	 * @param array  $fileInfoList ファイル情報
	 * @param string $uploadDir ファイルを格納するディレクトリ
	 * @param string $prefix ファイル名の接頭子(省略='')
	 * @return string アップロードされたファイル名
	 */
	public static function upload(array $fileInfo, string $uploadDir, string $prefix = ''): string {
		self::self()->start(__METHOD__, 'args=' . $fileInfo, $uploadDir, $prefix);
		$orgName  = $fileInfo['name'];     // 元のファイル名
		$tmpFile  = $fileInfo['tmp_name']; // 一時ファイル名

		$resultFile = '';
		if (!is_uploaded_file($tmpFile)) {
			// アップロードされたファイルでなかった場合
			self::self()->error(__METHOD__, 'not-upload-file=', $tmpFile);
			return $resultFile;
		} else {
			// 移動先のパスを作成
			$path = pathinfo($orgName); // フルパスをパス情報に分割する
			$ext = $path['extension'];    // 拡張子
			$movedFile = tempnam($uploadDir, $prefix) . '.' . $ext;
			// 一時ファイルを移動しリネームする
			if (move_uploaded_file($tmpFile, $movedFile)) {
				$resultFile = $movedFile; // 移動先のファイル名を結果として返す
			} else {
				self::self()->error(__METHOD__, 'move-file-failed=', $tmpFile, $movedFile);
			}
		}
		return self::self()->end(__METHOD__, 'resultFile=' . $resultFile);
	}
}
