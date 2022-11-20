<?php
require_once __DIR__ . '/_dir.php';
require_once BASE::UTIL . 'DateUtil.php';

/**
 * ストップウォッチ
 *
 * 下記のように使用する。
 * ```php
 * $sw = StopWatch::start(); // 計測開始
 * // ... 何らかの処理
 * $rap = $sw->getRapTime(); // 途中の経過時間
 * // ... 何らかの処理
 * $time = $sw->stop(); // 途中の経過時間
 * ```
 */
class StopWatch {
	private DateTime $start;
	private DateTime $end;

	/**
	 * 計測開始
	 * @return StopWatch
	 */
	public static function start(): StopWatch {
		$watch = new StopWatch();
		$watch->start = new DateTime();
		return $watch;
	}
	/**
	 * 計測終了
	 * @return string 経過時間
	 */
	public function stop(): string {
		$this->end = new DateTime();
		return $this->getTime();
	}
	/**
	 * 途中の経過時間を返す
	 * @return string 経過時間
	 */
	public function getRapTime(): string {
		$now = new DateTime();
		return self::getTimeInner($this->start, $now);
	}
	/**
	 * 計測終了時の経過時間を返す
	 * @return string 経過時間
	 */
	public function getTime(): string {
		return self::getTimeInner($this->start, $this->end);
	}
	private static function getTimeInner(Datetime $start, Datetime $end): string {
		$result = '';
		$result .= DateUtil::diff($start, $end, DateFmt::DIFF_SECOND);
		$result .= '.';
		$result .= DateUtil::diff($start, $end, DateFmt::DIFF_MICRO, false) . ' secs';
		return $result;
	}
}
