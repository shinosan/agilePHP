<?php
require_once __DIR__ . '/_dir.php';
require_once BASE::UTIL . 'ArrayUtil.php';

/**
 * DBMS上でnullとなることを明示する
 */
enum DBNull {
	case null;
}

/**
 * 条件演算子の列挙体
 */
enum Op {
	/** @var string No Operation 空白 */
	case NOP;
	/** @var string EQual 等しい */
	case EQ;
	/** @var string Not Equal 等しくない */
	case NE;
	/** @var string Littler Than ～より小さい */
	case LT;
	/** @var string Greater Than ～より大きい */
	case GT;
	/** @var string Littler Equel ～以下 */
	case LE;
	/** @var string Greater Equel ～以上 */
	case GE;
	/** @var string IN リストにある */
	case IN;
	/** @var string Not IN リストにない */
	case NOT_IN;
	/** @var string Between AとBの間 */
	case BETWEEN;
	/** @var string Starts with ～で始まる */
	case STARTS;
	/** @var string Ends with ～で終わる */
	case ENDS;
	/** @var string Contains ～を含む */
	case CONTAINS;
	/** @var string is null NULLである */
	case NULL;
	/** @var string is not null NULLでない */
	case NOT_NULL;
	/** @var string AND 両者が真となる */
	case AND;
	/** @var string OR どちらかが真となる */
	case OR;

	/**
	 * パラメータが必要な比較演算子かどうか
	 * @param Op $op 比較演算子
	 * @return bool true:パラメータが必要
	 */
	public static function hasParam(Op $op): bool {
		switch ($op) {
			case self::NULL:
				return false;
			case self::NOT_NULL:
				return false;
			case self::AND:
				return false;
			case self::OR:
				return false;
			case self::NOP:
				return false;
			default:
				return true;
		}
	}
}

/**
 * 検索条件クラス
 */
class Query {
	/** @var string テーブル名 */
	public string $table = '';

	/** @var array 取得フィールド&データ型リスト */
	public array $fieldTypes = [];

	/** @var array 検索条件定義リスト */
	public array $conditions = [];

	/** @var array 並べ替えフィールドリスト */
	public array $sort = [];

	/** @var int 1ページの行数 */
	public int $pageLines = 0;

	/** @var int 取得ページ */
	public int $page = 0;

	/** @var array 検索パラメータ [パラメータ名=>値]の配列 */
	public array $params = [];

	/**
	 * コンストラクタ
	 * @param string $table テーブル名
	 * @param array $fieldTypes [フィールド名=>データ型]の配列
	 * @param array $conditions 条件式リスト
	 * @param array $sort 並び順配列(省略=[]) 
	 * @param int $pageLines 1ページの行数(省略=0) 
	 * @param int $page (ページ番号=0) 
	 */
	public function __construct(string $table, array $fieldTypes, array $conditions, array $sort = [], int $pageLines = 0, int $page = 0) {
		$this->table = $table;
		$this->fieldTypes = $fieldTypes;
		$this->conditions = $conditions;
		$this->sort = $sort;
		$this->pageLines = $pageLines;
		$this->page = $page;
	}

	/** @var int 定義配列:フィールド名 */
	const FIELD = 0;
	/** @var int 定義配列:比較演算子 */
	const OP = 1;
	/** @var int 定義配列:比較演算子パラメータ１ */
	const PARAM1 = 2;
	/** @var int 定義配列:比較演算子パラメータ２ */
	const PARAM2 = 3;

	/**
	 * クエリ定義配列の比較演算子を変更する
	 * @param array $queryDefs クエリ定義配列
	 * @param string $field 変換対象のフィールド名
	 * @param string $op 変更する比較演算子
	 * @param int &$count 変更する数(省略 = 1) 
	 */
	public static function changeDef(array $queryDefs, string $field, string $op, int &$count = 1): array {
		foreach ($queryDefs as $query) {
			if (is_array($query)) {
				$target = ArrayUtil::get($query, self::OP);
				if ($target instanceof Op) {;
					if ($target === $op) {
						$query[self::OP] = $op;
						if (--$count <= 0) break;
					}
				} else {
					$queryDefs = self::changeDef($queryDefs, $field, $op, $count);
				}
			}
		}
		return $queryDefs;
	}

	/**
	 * 比較演算子の一覧をセットする
	 * @param array $opList 比較演算子の一覧
	 */
	public static function setOpList(array $opList) {
		self::$opList = $opList;
	}
	/**
	 * 比較演算子の文字列表現を得る
	 * @param Op $op 比較演算子
	 * @return string 文字列表現
	 */
	public static function getOpStr(Op $op): string {
		return ($op == Op::NOP) ? '' : self::$opList[$op->name];
	}
	private static array $opList;
}
