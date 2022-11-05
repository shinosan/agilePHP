<?php
require_once __DIR__ . '/_dir.php';
require_once BASE::UTIL . 'ArrayUtil';

/**
 * DBMS上でnullとなることを明示する
 */
class DBNull {
	/**
	 * 自分自身のシングルトンを返す
	 */
	public static function self(): DBNull {
		if (self::$self === null) {
			self::$self = new DBNull();
		}
		return self::$self;
	}
	private static ?DBNull $self = null;

	/**
	 * インスタンス化禁止
	 */
	private function __construct() {
	}
}

/**
 * 条件演算子
 */
class Op {
	/** @var string EQual 等しい */
	const EQ = "{0} = {1}";
	/** @var string Not Equal 等しくない */
	const NE = "{0} <> {1}";
	/** @var string Littler Than ～より小さい */
	const LT = "{0} < {1}";
	/** @var string Greater Than ～より大きい */
	const GT = "{0} > {1}";
	/** @var string Littler Equel ～以下 */
	const LE = "{0} <= {1}";
	/** @var string Greater Equel ～以上 */
	const GE = "{0} >= {1}";
	/** @var string IN リストにある */
	const IN = "{0} in ({1})";
	/** @var string Between AとBの間 */
	const BETWEEN = '{0} between {1} and {2}';
	/** @var string Starts with ～で始まる */
	const STARTS = "{0} like '{1}%'";
	/** @var string Ends with ～で終わる */
	const ENDS = "{0} like '%{1}'";
	/** @var string Contains ～を含む */
	const CONTAINS = "{0} like '%{1}%'";
	/** @var string is null NULLである */
	const NULL = "{0} is null";
	/** @var string is not null NULLでない */
	const NOTNULL = "{0} is not null";
	/** @var string AND 両者が真となる */
	const AND = " AND ";
	/** @var string OR どちらかが真となる */
	const OR = " OR ";

	/** @var array 全ての比較演算子 */
	const ALL_OPs = [self::EQ, self::NE, self::LT, self::GT, self::LE, self::GE, self::IN, self::BETWEEN, self::STARTS, self::ENDS, self::CONTAINS, self::NULL, self::NOTNULL];

	/**
	 * パラメータが必要な比較演算子かどうか
	 * @param string $op 比較演算子
	 * @return bool true:パラメータが必要
	 */
	public static function hasParam(string $op): bool {
		return ($op !== self::NULL && $op !== self::NOTNULL);
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
				if ($target && in_array($target, OP::ALL_OPs)) {;
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
}
