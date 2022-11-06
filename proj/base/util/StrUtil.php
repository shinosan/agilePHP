<?php
require_once __DIR__ . '/_dir.php';
require_once BASE::UTIL . 'TypeUtil.php';

use Types as T;

/**
 * 文字列操作ユーティリティ
 */
class StrUtil {
	/**
	 * インスタンス化禁止
	 */
	private function __construct() {
	}

	/**
	 * テンプレート文字列に値を埋め込む
	 * @param string $template 値を埋め込むテンプレート。 例: '文字列の中の{キー}を値に置き換える'
	 * @param array $keyVals [キー=>値, ...]の配列
	 * @param string $parenthesis 括弧(省略='{}')
	 * @return string 値が埋め込まれた文字列
	 */
	public static function embed(string $template, array $keyVals, string $parenthesis = '{}'): string {
		$p = mb_str_split($parenthesis);
		$str = $template;
		foreach ($keyVals as $key => $val) {
			$place = $p[0] . $key . $p[1];
			$str = self::replace($place, $val, $str);
		}
		return $str;
	}

	/**
	 * １文字目だけを大文字にする
	 * @param string $orgStr 元の文字列
	 * @return string 変換後の文字列
	 */
	public static function upper1st(string $value): string {
		$first = substr($value, 0, 1);
		$other = substr($value, 1);
		return strtoupper($first) . $other;
	}

	/**
	 * １文字目だけを小文字にする
	 * @param string $orgStr 元の文字列
	 * @return string 変換後の文字列
	 */
	public static function lower1st(string $orgStr): string {
		$first = mb_substr($orgStr, 0, 1);
		$first = mb_strtolower($first);
		$converted = $first . mb_substr($orgStr, 1);
		return $converted;
	}

	/**
	 * マルチバイト対応の文字列置換
	 * @param string $search 検索文字列
	 * @param string $replace 置換文字列
	 * @param string  $target 対象文字列
	 * @return string
	 */
	public static function replace(string $search, string $replace, string  $target): string {
		$searchLen = mb_strlen($search);
		$replaceLen = mb_strlen($replace);
		$offset = self::find($target, $search);
		while ($offset >= 0) {
			$replaceEnd = $offset + $searchLen;
			$done   = mb_substr($target, 0, $offset);
			$remain = mb_substr($target, $replaceEnd);
			$target = $done . $replace . $remain;
			$nextStart = $offset + $replaceLen;
			$offset = self::find($target, $search, $nextStart);
		}
		return $target;
	}

	/**
	 * 対象文字列から検索文字列を探し、その位置を返す。
	 * @param string $target 対象文字列
	 * @param string $search 検索文字列
	 * @param int $offset 検索開始位置(省略:0)
	 * @return int 見つかった検索文字列の位置。見つからないと-1
	 */
	public static function find(string $target, string $search, int $offset = 0): int {
		$pos = mb_strpos($target, $search, $offset);
		if ($pos === false) return -1;
		return $pos;
	}

	/**
	 * 対象文字列が検索文字列で始まるかどうか。
	 * @param string $target 対象文字列
	 * @param string $search 検索文字列
	 * @return bool true:見つかった場合
	 */
	public static function startsWith(string $target, string $search): bool {
		$pos = self::find($target, $search);
		if ($pos == 0) return true;
		return false;
	}
	/**
	 * 対象文字列が検索文字列で終わるかどうか。
	 * @param string $target 対象文字列
	 * @param string $search 検索文字列
	 * @return bool true:見つかった場合
	 */
	public static function endsWith(string $target, string $search): bool {
		$end = mb_strlen($target);
		$end -= mb_strlen($search);
		$pos = self::find($target, $search);
		if ($pos == $end) return true;
		return false;
	}

	/**
	 * UTF-8に文字エンコードを変換する
	 * @param string $value 対象文字列
	 * @param string $prevEncoding 変換前の文字エンコード(省略='SJIS')
	 * @return string 変換した文字列
	 */
	public static function toUTF8(string $value, string $prevEncoding = 'SJIS'): string {
		$result = mb_convert_encoding($value, 'UTF-8', $prevEncoding);
		return $result;
	}

	/**
	 * SHIFT-JISに文字エンコードを変換する
	 * @param string $value 対象文字列
	 * @param string $prevEncoding 変換前の文字エンコード(省略='UTF-8')
	 * @return string 変換した文字列
	 */
	public static function toSJIS(string $value, string $prevEncoding = 'UTF-8'): string {
		$result = mb_convert_encoding($value, 'SJIS', $prevEncoding);
		return $result;
	}

	/**
	 * 対象文字列に検索文字列があるかどうか。
	 * @param string $target 対象文字列
	 * @param string|array $search 検索文字列 or その配列
	 * @param bool $isAll 全部見つける 省略=false
	 * @return bool true:見つかった場合
	 */
	public static function exists(string $target, string|array $search, bool $isAll = false): bool {
		if (is_array($search)) {
			$count = 0;
			foreach ($search as $word) {
				$pos = self::find($target, $word);
				if ($pos >= 0) $count++;
			}
			return $isAll ? (count($search) == $count) : ($count > 0);
		}
		$pos = self::find($target, $search);
		if ($pos < 0) return false;
		return true;
	}

	/** @var string カンマ */
	const COMMA = ',';

	/**
	 * 文字列にする。
	 * 数値の書式 : self::COMMA 3桁区切りか、printf() の書式。
	 * 日時の書式 : 'Y-m-d'などの形式。
	 * @param string $format 日付・数値の書式。配列は区分記号。
	 * @return string
	 */
	public static function toString(mixed $value, string $format = null): string {
		$type = TypeUtil::getType($value);
		$result = '';
		switch ($type) {
			case T::NULL: // null : 空白文字へ
				$result = '';
				break;
			case T::BOOL:
				$result = $value ? 'true' : 'false';
				break;
			case T::INT:
			case T::FLOAT:
				if ($format == self::COMMA) {
					$result = number_format($value); // 三桁区切り
				} else if ($format) {
					$result = sprintf($format, $value); // 多用途
				} else {
					$result = '' . $value;
				}
				break;
			case T::ARRAY: // 配列
				$result = ArrayUtil::toString($value, self::COMMA);
				break;
			case T::ENUM: // 列挙型
				$result = self::enum2str($value);
				break;
			case T::DATETIME: // 日時
				$result = DateUtil::toString($value);
				break;
			case T::OBJECT: // オブジェクト
				$result = get_class($value);
				break;
			case T::MODEL: // モデル
				$array = $value->toArray();
				$result = ArrayUtil::toString($array, self::COMMA);
				break;
			default:
				$result = $value;
				break;
		}
		return $result;
	}
	/**
	 * 列挙型を'クラス名::名称'に変換する
	 * @param mixed $value 列挙型
	 * @return string 変換した文字列
	 */
	public static function enum2str(mixed $value): string {
		return get_class($value) . '::' . $value->name;
	}

	//// 文字種別変換 ////

	/** @var string 半角英数記号へ変換 */
	const CNV_HAN_ALPH = 'r';
	/** @var string 半角数字へ変換 */
	const CNV_HAN_NUM = 'n';
	/** @var string 半角英字へ変換 */
	const CNV_HAN_ALNUM = 'a';
	/** @var string 半角カナへ変換 */
	const CNV_HAN_KATA = 'k';
	/** @var string 半角二重引用符へ変換 */
	const CNV_HAN_DQUART = '"';
	/** @var string 半角引用符へ変換 */
	const CNV_HAN_SQUART = "'";
	/** @var string 半角チルダへ変換 */
	const CNV_HAN_TILDE = '~';
	/** @var string 半角円記号へ変換 */
	const CNV_HAN_YEN = '\\';

	/** @var string 全角英数記号へ変換 */
	const CNV_ZEN_ALPH = 'R';
	/** @var string 全角数字へ変換 */
	const CNV_ZEN_NUM = 'N';
	/** @var string 全角英字へ変換 */
	const CNV_ZEN_ALNUM = 'A';
	/** @var string 全角カナへ変換 */
	const CNV_ZEN_KATA = 'KV';
	/** @var string 全角かなへ変換 */
	const CNV_ZEN_HIRA = 'HV';
	/** @var string 全角二重引用符へ変換 */
	const CNV_ZEN_DQUART = '”';
	/** @var string 全角引用符へ変換 */
	const CNV_ZEN_SQUART = '’';
	/** @var string 全角チルダへ変換 */
	const CNV_ZEN_TILDE = '～';
	/** @var string 全角円記号へ変換 */
	const CNV_ZEN_YEN = '￥';

	/** @var array 変換マップ */
	const CNV_MAP = [
		// 全角→半角
		self::CNV_HAN_DQUART => self::CNV_ZEN_DQUART,
		self::CNV_HAN_SQUART => self::CNV_ZEN_SQUART,
		self::CNV_HAN_TILDE => self::CNV_ZEN_TILDE,
		self::CNV_HAN_YEN => self::CNV_ZEN_YEN,
		// 半角→全角
		self::CNV_ZEN_DQUART => self::CNV_HAN_DQUART,
		self::CNV_ZEN_SQUART => self::CNV_HAN_SQUART,
		self::CNV_ZEN_TILDE => self::CNV_HAN_TILDE,
		self::CNV_ZEN_YEN => self::CNV_HAN_YEN,
	];
	/**
	 * 指定した文字種別を変換する。  
	 * 「" ' ~ \」は個別に指定する。
	 * @param string $value 変換元文字列
	 * @param string ...$convert 変換指定
	 * @return string 変換された文字列
	 */
	public static function zenHan(string $value, string ...$convert): string {
		$conv = '';
		$convSyms = [];
		foreach ($convert as $cv) {
			if (array_search($cv, self::CNV_MAP) !== false) {
				$convSyms[] = $cv;
			} else {
				$conv .= $cv;
			}
		}
		// 半角英数記号カナ → 全角英数記号かなカナ 変換
		$value = mb_convert_kana($value, $conv);

		// " ' ~ \ を変換する
		foreach ($convSyms as $key => $value) {
			$value = StrUtil::replace($value, $key, $value);
		}
		return $value;
	}
}
