<?php
require_once __DIR__ . '/_dir.php';
require_once BASE::UTIL . 'StrUtil.php';

/**
 * 処理結果記録クラス
 */
class Results {
	/** @var int エラーレベル */
	const ERROR = -1;
	/** @var int 警告 */
	const WARNING = 0;
	/** @var int トレースレベル */
	const TRACE = 1;

	/**
	 * シングルトンにアクセスする
	 * @return Results
	 */
	public static function self(): Results {
		if (!self::$self) {
			self::$self = new Results();
		}
		return self::$self;
	}
	private static ?Results $self = null;

	/** 複数インスタンスの生成を禁止 */
	private function __construct() {
	}

	/** @var int 配列を出力する最大数 */
	private int $maxArray = 10;

	/**
	 * 配列出力の最大数をセットする
	 * @param int $val セットする値
	 * @return Results
	 */
	public function setMaxArray(int $val): Results {
		$this->maxArray = $val;
		return $this;
	}

	/** @var array 処理結果情報の配列 */
	private array $list = [];

	/**
	 * エラーの記録
	 * @param string $name 処理名(クラス,メソッド,フィールド)
	 * @param string $message メッセージ(何が起こったか)
	 * @param mixed ...$params パラメータ
	 * @return ResultInfo 処理結果情報
	 */
	public function error(string $name, string $message, mixed ...$params): ResultInfo {
		return $this->trace(-1, $name,  $message,  ...$params);
	}

	/**
	 * 警告の記録
	 * @param string $name 処理名(クラス,メソッド,フィールド)
	 * @param string $message メッセージ(何が起こったか)
	 * @param mixed ...$params パラメータ
	 * @return ResultInfo 処理結果情報
	 */
	public function warning(string $name, string $message, mixed ...$params): ResultInfo {
		return $this->trace(0, $name,  $message,  ...$params);
	}

	/**
	 * 処理結果の記録
	 * @param int $level 出力レベル マイナス:エラー, 0:警告, 1以上:トレース(大きいほど詳細)
	 * @param string $name 処理名(クラス,メソッド,フィールド)
	 * @param string $message メッセージ(何が起こったか)
	 * @param mixed ...$params パラメータ
	 * @return ResultInfo 処理結果情報
	 */
	public function trace(int $level, string $name, string $message, mixed ...$params): ResultInfo {
		$info = new ResultInfo();

		$info->level = $level;
		$info->name = $name;
		$info->message = $message;

		// パラメータにエラー情報があった場合
		foreach ($params as $idx => $param) {
			if ($param instanceof Throwable) {
				$msg = $this->getErrorInfo($param);
				$params[$idx] = $msg;
			}
		}
		$info->params = $params;

		$this->list[] = $info;
		return $info;
	}

	/**
	 * エラーが無いことを確認
	 * @return bool true:エラーなし
	 */
	public function isOk(): bool {
		return count($this->errors()) == 0;
	}

	/**
	 * エラーがあるかを確認
	 * @return bool true:エラーあり
	 */
	public function hasError(): bool {
		return !$this->isOk();
	}

	/**
	 * エラーの結果情報を抽出
	 * @return array エラーリスト
	 */
	public function errors(): array {
		return $this->select(null, -1);
	}
	/**
	 * 警告の結果情報を抽出
	 * @return array 警告リスト
	 */
	public function warnings(): array {
		return $this->select(0, 0);
	}
	/**
	 * トレース情報を抽出
	 * @param ?int $maxLevel 最大レベル(省略=全件)
	 * @return array トレースリスト
	 */
	public function traces(?int $maxLevel = null): array {
		return $this->select(1, $maxLevel);
	}

	/**
	 * 結果情報を抽出
	 * @param ?int $levelStart 最小レベル(省略=全件)
	 * @param ?int $levelEnd 最大レベル(省略=全件)
	 * @return array 結果リスト
	 */
	protected function select(?int $levelStart, ?int $levelEnd): array {
		$list = [];
		foreach ($this->list as $info) {
			if ($levelStart !== null && $info->level < $levelStart) continue;
			if ($levelEnd !== null && $info->level > $levelEnd) continue;
			$list[] = $info;
		}
		return $list;
	}

	/**
	 * 実行時エラー情報の取得
	 * @param Throwable $err Throwされたエラー
	 * @param string $header エラーメッセージの先頭に入れる文言 (省略 = '')
	 * @return string
	 */
	protected function getErrorInfo(Throwable $err, string $header = ''): string {
		$header = ($header ? ($header . ' : ') : '');
		// エラーメッセージ
		$msg = 'ERROR:' . $header . $err->getMessage() . PHP_EOL;
		// ファイルパス＋行番号
		$msg .= "\t" . $err->getFile() . ':' . $err->getLine() . PHP_EOL;
		// スタックトレース
		foreach ($err->getTrace() as $info) {
			$class = ArrayUtil::get($info, 'class');
			$func  = ArrayUtil::get($info, 'function');
			$file  = ArrayUtil::get($info, 'file');
			$line  = ArrayUtil::get($info, 'line');
			if ($class) {
				$msg .= "\t" . $class . '::';
			} else {
				$msg .= "\t";
			}
			if ($func) {
				$msg .= $func . PHP_EOL;
			}
			if ($file) {
				$msg .= "\t" . $file . ':' . $line . PHP_EOL;
			}
		}
		return $msg;
	}
}

/**
 * 処理結果情報
 */
class ResultInfo {
	/** @var int 出力レベル マイナス:エラー, 0:警告, 1以上:トレース(大きいほど詳細) */
	public int $level = 0;
	/** @var string 処理名(クラス,メソッド,フィールド) */
	public string $name = '';
	/** @var string メッセージ(何が起こったか) */
	public string $message = '';
	/** @var array パラメータ */
	public array $params = [];

	/**
	 * 文字列化する
	 * @return string 変換した文字列
	 */
	public function toString(): string {
		if ($this->level <= Results::ERROR) {
			$output = 'ERROR';
		} else if ($this->level == Results::WARNING) {
			$output = 'WARNING';
		} else if ($this->level >= Results::TRACE) {
			$output = 'TRACE';
		}
		$output .= ': ' . $this->name . ' : ' . $this->message . ' : ' . ArrayUtil::toString($this->params);
		return $output;
	}
}
