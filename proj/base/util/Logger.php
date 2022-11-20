<?php
require_once __DIR__ . '/_dir.php';
require_once BASE::UTIL . 'Results.php';
require_once BASE::UTIL . 'DateUtil.php';
require_once BASE::UTIL . 'ConfigUtil.php';

/**
 * ログ出力機能を付加する
 */
trait Logger {

	/**
	 * 保持している処理結果を返す
	 */
	public function results(): Results {
		return Results::self();
	}

	/** @var string 設定ファイルでのログファイルのキー */
	private static string $LOG_FILE = 'LOG_FILE';
	/** @var string 設定ファイルでのログ出力レベルのキー */
	private static string $LOG_LEVEL = 'LOG_LEVEL';
	/** @var string ログ出力レベル */
	private static ?int $logLevel = null;

	public static function setLogLevel(int $val) {
		self::$logLevel = $val;
	}
	public static function getLogLevel(): int {
		if (self::$logLevel === null) {
			self::$logLevel = ConfigUtil::get(self::$LOG_LEVEL);
		}
		return self::$logLevel;
	}

	/**
	 * 処理の開始を記録する。
	 * @param string $methodName 処理名
	 * @param mixed ...$params パラメータ
	 */
	protected function start(string $methodName, mixed ...$params) {
		$this->trace(Results::TRACE, $methodName, 'start', ...$params);
	}

	/**
	 * 処理の終了を記録する。
	 * @param string $methodName 処理名
	 * @param mixed $results 処理結果(省略可)
	 * @param int $maxArray 処理結果が配列の場合の最大出力 (省略 = -1:全件)
	 * @return mixed 処理結果を返す
	 */
	protected function end(string $methodName, mixed $results = null, int $maxArray = -1): mixed {
		if ($maxArray == -1) {
			$maxArray = PHP_INT_MAX; // 全件
		}
		// 配列の出力数を制限
		$count = is_array($results) ? count($results) : 0;
		if ($count > $maxArray) {
			$reduced = array_slice($results, 0, $maxArray);
			array_push($reduced, '... count=' . $count);
			$this->trace(Results::TRACE, $methodName, 'end', $reduced);
		} else {
			$this->trace(Results::TRACE, $methodName, 'end', $results);
		}
		return $results;
	}

	/**
	 * 警告を記録する。
	 * @param string $methodName メソッド名
	 * @param string $happend 起こった事
	 * @param mixed ...$params その他パラメータなど
	 * @return mixed パラメータの１件目(通常はエラーコード)
	 */
	public function warning(string $methodName, string $happend, mixed ...$params): mixed {
		$this->trace(Results::WARNING, $methodName, $happend, ...$params);
		return ArrayUtil::get($params, 0, 'no error code');
	}

	/**
	 * エラーを記録する。
	 * @param string $methodName メソッド名
	 * @param string $happend 起こった事
	 * @param mixed ...$params その他パラメータなど
	 * @return mixed パラメータの１件目(通常はエラーコード)
	 */
	public function error(string $methodName, string $happend,  mixed ...$params): mixed {
		$this->trace(Results::ERROR, $methodName, $happend, ...$params);
		return ArrayUtil::get($params, 0, 'no error code');
	}

	/**
	 * トレースを記録し、ログ出力する。
	 * @param int $level トレースレベル
	 * @param string $methodName メソッド名
	 * @param string $happend 起こった事
	 * @param mixed ...$params その他パラメータなど
	 * @return ResultInfo 処理結果情報
	 */
	public function trace(int $level, string $methodName, string $happend, mixed ...$params): ResultInfo {
		$info = $this->results()->trace($level, $methodName, $happend, ...$params);

		// トレースレベルがログ出力レベルより詳細なら出力しない
		if ($level > self::getLogLevel()) return $info;

		$now = DateUtil::format(new DateTime(), DateUtil::YmdHisU);
		$line = $info->toString();
		$path = ConfigUtil::get(self::$LOG_FILE);
		try {
			$today = DateUtil::toString(DateUtil::today(), DateUtil::Ymd);
			$path = StrUtil::replace('{today}', $today, $path);
			$fp = fopen($path, 'a');
			fputs($fp, $now . ' : ' . $line . PHP_EOL);
			fclose($fp);
		} catch (Exception $ex) {
			$this->results()->error(__METHOD__, 'ログ出力', $ex, $line);
		}
		return $info;
	}
}
