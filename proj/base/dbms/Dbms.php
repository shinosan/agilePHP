<?php
require_once __DIR__ . '/_dir.php';
require_once BASE::UTIL . 'Logger.php';
require_once BASE::UTIL . 'TypeUtil.php';
require_once BASE::DBMS . 'Query.php';

/**
 * DBエラーの列挙体
 */
enum DbErrors: int {
	/** 処理成功  */
	case SUCCESS = 0;

	/** 該当データなし */
	case NO_DATA = -1;

	/** 接続失敗  */
	case ERR_CONNECT = -101;
	/** トランザクション開始失敗  */
	case ERR_BGINTXN = -102;
	/** SQLの文法エラー  */
	case ERR_STATEMENT = -103;
	/** SQLへの値のバインド失敗  */
	case ERR_BIND = -104;
	/** SQL文の実行失敗  */
	case ERR_EXECUTE = -105;
	/** 結果の取得失敗  */
	case ERR_FETCH = -106;
	/** トランザクション反映の失敗  */
	case ERR_COMMIT = -107;
	/** トランザクション撤回の失敗  */
	case ERR_ROLLBACK = -108;
	/** 接続断の失敗  */
	case ERR_CLOSE = -109;
	/** PDO系の失敗  */
	case ERR_PDO = -110;
}

/**
 * DBMSの抽象基底クラス  
 * 連想配列ベースでデータの読み書き検索を行う  
 * TODO:呼ばれたAPP毎に違うDBにアクセスする仕組みが必要 -> DB名だけ個別のdbconfig.iniから読み取る
 */
abstract class Dbms {
	use Logger;

	/**
	 * 派生クラスでのシングルトン以外、インスタンス化は不可
	 */
	protected function __construct() {
	}

	abstract public function className(): string;

	/**
	 * DB接続文字列を返す
	 * @return string DB接続文字列
	 */
	abstract protected function getConnectionString(): string;

	/** @var string const DB設定ファイル  */
	const CONFIG_FILE = 'dbconfig.ini';
	/** @var string const DBユーザ  */
	const USER = 'USER';
	/** @var string const DBパスワード  */
	const PASS = 'PASS';

	/** @var ?PDO DB接続クラス */
	private static ?PDO $PDO = null;

	/** @var int トランザクション開始カウント */
	private static int $transactionBegun = 0;

	/**
	 * DBサーバ接続
	 * @return DbErrors
	 */
	public function connect(): DbErrors {
		$this->start(__METHOD__);
		$result = DbErrors::SUCCESS;
		if (!self::$PDO) {
			try {
				$str = $this->getConnectionString();
				$user = ConfigUtil::get(self::USER, self::CONFIG_FILE);
				$pass = ConfigUtil::get(self::PASS, self::CONFIG_FILE);
				self::$PDO = new PDO($str, $user, $pass);
				self::$PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$this->trace(1, __METHOD__, 'success');
			} catch (PDOException $e) {
				$this->error(__METHOD__, $e->getMessage());
				$result = DbErrors::ERR_CONNECT;
			}
		}
		return $this->end(__METHOD__, $result);
	}
	/**
	 * DBサーバ切断
	 * @return DbErrors
	 */
	public function disconnect(): DbErrors {
		$this->start(__METHOD__);
		$result = DbErrors::SUCCESS;
		if (self::$PDO) {
			try {
				$this->rollback();
				self::$PDO = null;
				$this->trace(1, __METHOD__, 'done');
			} catch (PDOException $e) {
				$this->error(__METHOD__, $e->getMessage());
				$result = DbErrors::ERR_CLOSE;
			}
		}
		return $this->end(__METHOD__, $result);
	}

	/**
	 * 開始していなければ、トランザクションを開始する。
	 * @return DbErrors SUCCESS:成功, ERR_*:エラー
	 */
	public function beginTransaction(): DbErrors {
		$this->start(__METHOD__);
		$result = DbErrors::SUCCESS;
		if (self::$PDO && self::$transactionBegun++ == 0) {
			try {
				self::$PDO->beginTransaction();
				$this->trace(1, __METHOD__, 'begun', 'realy');
			} catch (PDOException $e) {
				$this->error(__METHOD__, $e->getMessage());
				$result = DbErrors::ERR_BGINTXN;
			}
		}
		return $this->end(__METHOD__, $result);
	}
	/**
	 * 開始したトランザクションがすべて成功していればコミットする
	 * @return DbErrors
	 */
	public function commit(): DbErrors {
		$this->start(__METHOD__);
		$result = DbErrors::SUCCESS;
		if (self::$PDO && --self::$transactionBegun == 0) {
			try {
				self::$PDO->commit();
				$this->trace(1, __METHOD__, 'done');
			} catch (PDOException $e) {
				$this->error(__METHOD__, $e->getMessage());
				$result = DbErrors::ERR_COMMIT;
			}
		}
		return $this->end(__METHOD__, $result);
	}
	/**
	 * ロールバックする
	 * @return DbErrors
	 */
	public function rollback(): DbErrors {
		$this->start(__METHOD__);
		$result = DbErrors::SUCCESS;
		if (self::$transactionBegun > 0 && self::$PDO) {
			try {
				self::$transactionBegun = 0;
				self::$PDO->rollback();
				$this->trace(1, __METHOD__, 'done');
			} catch (PDOException $e) {
				$this->error(__METHOD__, $e->getMessage());
				$result = DbErrors::ERR_ROLLBACK;
			}
		}
		return $this->end(__METHOD__, $result);
	}

	/**
	 * 指定したクエリ定義で検索を行う
	 * @param Query $query 検索条件
	 * @param array $params 条件にバインドする連想配列
	 * @param bool $lock true:更新用にロックする(省略 = false)
	 * @return int|array 成功:検索結果の連装配列 失敗:エラーコード
	 */
	public function select(Query $query, array $params, bool $lock = false): int|array {
		$this->start(__METHOD__);
		// SQL文の構築
		$sql = $this->makeSelect($query, $params, $lock);
		// SQL文の実行＆値の取得
		$rows = $this->executeFetch($sql, PDO::FETCH_ASSOC, $params, $query->fieldTypes);
		return $this->end(__METHOD__, $rows, $this->maxArray);
	}

	/**
	 * 指定したテーブルの主キーが一致するレコードを得る
	 * @param string $table テーブル
	 * @param int $pkey 主キー
	 * @param bool $lock true:更新用にロックする(省略 = false)
	 * @param string $pkeyName 主キー名(省略 = 'pkey')
	 * @return array|DbErrors 成功:1件分の連装配列 失敗:DbErrors
	 */
	public function get(string $table, int $pkey, array $fieldTypes, bool $lock = false, string $pkeyName = 'pkey'): array|DbErrors {
		$this->start(__METHOD__);
		// SQL文の構築
		$query = new Query($table, $fieldTypes, [$pkeyName, Op::EQ, $pkeyName]);
		$params = [$pkeyName => $pkey];
		$sql = $this->makeSelect($query, $params, $lock);
		// SQL文の実行＆値の取得
		$ret = $this->executeFetch($sql, PDO::FETCH_ASSOC, $params, $query->fieldTypes);
		$row = is_array($ret) ? $ret[0] : DbErrors::NO_DATA;
		return $this->end(__METHOD__, $row);
	}

	/**
	 * 指定したクエリ定義にマッチする件数を返す
	 * @param Query $query 検索条件
	 * @param array $params 条件にバインドする連想配列
	 * @return array 検索結果の連装配列
	 */
	public function count(Query $query, array $params): int {
		$this->start(__METHOD__);
		$val = -1;
		// SQL文の構築
		$sql = $this->makeSelect($query, $params, false);
		// SQL文の実行
		$row = $this->executeFetch($sql, PDO::FETCH_NUM);
		if (is_array($row)) {
			$val = $row[0];
		} else {
			$val = $row;
		}
		return $this->end(__METHOD__, $val);
	}

	/**
	 * 指定したテーブルの数値カラムの最大値を返す
	 * @param string $table 
	 * @param string $column 
	 * @return int 得られた最大値
	 */
	public function getMax(string $table, string $column): int {
		$this->start(__METHOD__);
		$val = -1;
		// SQL文の構築
		$sql = $this->makeGetMax($table, $column);
		// SQL文の実行
		$row = $this->executeFetch($sql, PDO::FETCH_NUM);
		if (is_array($row)) {
			$val = $row[0];
		} else {
			$val = $row;
		}
		return $this->end(__METHOD__, $val);
	}

	/**
	 * 指定したテーブルにレコードを挿入する
	 * @param string $table テーブル
	 * @param array $params 値の連想配列
	 * @param array $types データ型の連想配列
	 * @param bool $isMultiRows true:複数行の配列 (省略=false) 
	 * @return int 結果コード
	 */
	public function create(string $table, array $params, array $types, bool $isMultiRows = false): int {
		$this->start(__METHOD__);
		// カラムとパラメータリストの生成
		list($columns, $paramList) = $this->getKeyParam($params, $isMultiRows);
		// SQL文の構築
		$sql = $this->makeInsert($table, $columns);
		// SQL文の実行
		$result = $this->execute($sql, $paramList, $types);
		return $this->end(__METHOD__, $result, $isMultiRows ? $this->maxArray : -1);
	}

	/**
	 * 指定したテーブルの主キーが一致するレコードを更新する
	 * @param string $table テーブル
	 * @param array $params 値の連想配列
	 * @param array $types データ型の連想配列
	 * @param bool $isMultiRows true:複数行の配列 (省略=false) 
	 * @param string $pkeyName 主キーのカラム名(省略 = 'pkey')
	 * @return int 結果コード
	 */
	public function update(string $table, array $params, array $types, bool $isMultiRows = false, string $pkeyName = 'pkey'): int {
		$this->start(__METHOD__);
		// カラムとパラメータリストの生成
		list($columns, $paramList) = $this->getKeyParam($params, $isMultiRows);
		// SQL文の構築
		$sql = $this->makeUpdate($table, $columns, $pkeyName);
		// SQL文の実行
		$result = $this->execute($sql, $paramList, $types);
		return $this->end(__METHOD__, $result, $isMultiRows ? $this->maxArray : -1);
	}

	/**
	 * 指定したテーブルの主キーが一致するレコードを削除する
	 * @param string $table テーブル
	 * @param array $pkeys 主キー配列
	 * @param string $pkeyName 主キーのカラム名(省略 = 'pkey')
	 * @return int 結果コード
	 */
	public function delete(string $table, int|array $pkeys, string $pkeyName = 'pkey'): int {
		$this->start(__METHOD__, $pkeys);
		// SQL文の構築
		if (is_array($pkeys) === false) {
			$pkeys = [$pkeys];
		}
		$sql = $this->makeDelete($table, $pkeyName);
		// SQL文の実行
		$result = $this->execute($sql, $pkeys, [$pkeyName => Types::INT]);
		return $this->end(__METHOD__, $result);
	}

	/**
	 * ログ出力の最大件数を指定する(デフォルトは10件)
	 * @param int $val セットする値
	 */
	public function setMaxArray(int $val) {
		$this->maxArray = $val;
	}
	private int $maxArray = 10;


	//// 内部メソッド ////


	/**
	 * 更新系SQLの実行
	 * @param string $sql 実行するSQL
	 * @param array $paramList パラメータ配列
	 * @param array $types [列名 => 型] の配列
	 * @param DbErrors 処理結果 1:成功 負数:エラーコード
	 */
	protected function execute(string $sql, array $paramList, array $types): DbErrors {
		$this->start(__METHOD__, $sql);
		$result = 1;
		try {
			// SQLステートメント生成
			$statement = self::$PDO->prepare($sql);
			if (!$statement) {
				return $this->error(__METHOD__, DbErrors::ERR_STATEMENT->name, $sql);
			}
			// 値のバインドと実行
			foreach ($paramList as $params) {
				$ret = $this->bindValue($statement, $params, $types);
				if (!$ret) {
					return $this->error(__METHOD__, DbErrors::ERR_BIND->name);
				}
				$ret = $statement->execute();
				if (!$ret) {
					return $this->error(__METHOD__, DbErrors::ERR_EXECUTE->name);
				}
			}
		} catch (PDOException $ex) {
			return $this->error(__METHOD__, DbErrors::ERR_PDO->name, $ex->getMessage(), $ex);
		}
		return $this->end(__METHOD__, $result);
	}

	/**
	 * SQL実行＆値取得
	 * @param string $sql SQL
	 * @param int $fetchType 取得タイプ
	 * @param ?array $params 検索パラメータ (省略 = null) 
	 * @param ?array $types データ型配列 (省略 = null)
	 * @return DBErrors|array エラー情報|取得した値の配列
	 */
	protected function executeFetch(string $sql, int $fetchType, ?array $params = null, ?array $types = null): DBErrors|array {
		$this->start(__METHOD__, $sql);
		$result = [];
		try {
			$statement = self::$PDO->prepare($sql);
			if (!$statement) {
				return $this->error(__METHOD__, DbErrors::ERR_STATEMENT->name, $sql);
			}
			if ($params && $types) {
				$ret = $this->bindValue($statement, $params, $types);
				if (!$ret) {
					return $this->error(__METHOD__, DbErrors::ERR_BIND->name, $sql);
				}
			}
			$ret = $statement->execute();
			if (!$ret) {
				return $this->error(__METHOD__, DbErrors::ERR_EXECUTE->name);
			}
			$result = $statement->fetchAll($fetchType);
			if (!$result) {
				return $this->error(__METHOD__, DbErrors::ERR_FETCH->name);
			}
		} catch (PDOException $ex) {
			return $this->error(__METHOD__, DbErrors::ERR_PDO->name, $ex);
		}
		$this->end(__METHOD__, count($result) . 'rows fetched');
		return $result;
	}

	/**
	 * SELECT文の構築
	 * @param Query $query 検索条件
	 * @param array $params 検索パラメータ
	 * @param bool $lock 行ロック
	 * @return string 構築したSQL
	 */
	public function makeSelect(Query $query, array $params, bool $lock): string {
		$columns = array_keys($query->fieldTypes);
		$colStr = ArrayUtil::toString($columns);
		$where = $this->buildWhere($query, $params);
		$sql = 'select ' . $colStr . ' from ' . $query->table . ' where ' . $where;
		if ($query->sort) {
			$sortStr = ArrayUtil::toString($query->sort);
			$sql .= ' order by ' . $sortStr;
		}
		if ($query->pageLines) {
			$sql .= ' limit ' . $query->pageLines;
			if ($query->page) {
				$offset = $query->page * $query->pageLines;
				$sql .= ' offset ' . $offset;
			}
		}
		if ($lock) {
			$sql .= ' for update';
		}
		return $sql;
	}

	/**
	 * 最大値取得SQLの構築
	 * @param string $table テーブル名
	 * @param string $column カラム名
	 * @return string 構築したSQL
	 */
	protected function makeGetMax(string $table, string $column): string {
		$sql = 'select max(' . $column . ') from ' . $table;
		return $sql;
	}

	/**
	 * INSERT文の構築
	 * @param string $table テーブル名
	 * @param string $column カラム名
	 * @return string 構築したSQL
	 */
	protected function makeInsert(string $table, array $columns): string {
		$colStr = ArrayUtil::toString($columns);
		$places = $this->getPlaces($columns);
		$sql = 'insert into ' . $table . ' (' . $colStr . ') values (' . $places . ')';
		return $sql;
	}

	/**
	 * UPDAT文の構築
	 * @param string $table テーブル名
	 * @param array $column カラム名配列
	 * @param string $pkeyName 主キー名
	 * @return string 構築したSQL
	 */
	protected function makeUpdate(string $table, array $columns, string $pkeyName): string {
		$sql = 'update ' . $table . ' set ';
		$dlm = '';
		foreach ($columns as $col) {
			$sql .= $dlm . $col . ' = :' . $col;
			$dlm = ',';
		}
		$sql .= ' where ' . $pkeyName . ' = :' . $pkeyName;
		return $sql;
	}
	/**
	 * DELETE文の構築
	 * @param string $table テーブル名
	 * @param string $pkeyName 主キー名
	 * @return string 構築したSQL
	 */
	protected function makeDelete(string $table, string $pkeyName): string {
		return 'delete from ' . $table . ' where ' . $pkeyName . ' = :' . $pkeyName;
	}

	/**
	 * 値の連想配列から列と値の配列を生成する
	 * @param array $params 値の連想配列
	 * @param bool $isMultiRows true:複数行の配列
	 * @return array [列配列, 値配列]
	 */
	protected function getKeyParam(array $params, bool $isMultiRows): array {
		if ($isMultiRows) {
			// 複数行の場合、最初の行でキーリストを作る
			$columns = array_keys($params[0]);
			$paramList = $params;
		} else {
			$columns = array_keys($params);
			// 単一行の場合、値配列をもう一段配列で包む
			$paramList = [$params];
		}
		return [$columns, $paramList];
	}

	protected function getPlaces(array $columns): string {
		$places = '';
		$dlm = '';
		foreach ($columns as $col) {
			$places .= $dlm . ':' . $col;
			$dlm = ',';
		}
		return $places;
	}

	protected function bindValue(PDOStatement $statement, array $params, array $types): bool {
		$ret = true;
		foreach ($params as $key => $val) {
			$place = ':' . $key;
			$type = $types[$key];
			$sqlType = self::sqlType($type);
			$sqlVal = self::toSqlVal($sqlType, $val);
			$ret = $statement->bindValue($place, $sqlVal, $sqlType);
			if (!$ret) {
				$this->error(__METHOD__, 'error.bindValue', $key, $val);
			}
		}
		return $ret;
	}

	protected static function sqlType(Types $type): int {
		switch ($type) {
			case Types::NULL:
				return PDO::PARAM_NULL;
			case Types::BOOL:
				return PDO::PARAM_BOOL;
			case Types::INT:
				return PDO::PARAM_INT;
		}
		return PDO::PARAM_STR;
	}

	protected static function toSqlVal(int $type, ?string $val): mixed {
		switch ($type) {
			case PDO::PARAM_NULL:
				return null;
			case PDO::PARAM_BOOL:
				$valLC = strtolower($val);
				return ($valLC == 'true' || $valLC == 'false') ? boolval($val) : null;
			case PDO::PARAM_INT:
				return is_numeric($val) ? intval($val) : null;
			case PDO::PARAM_STR:
				return StrUtil::toString($val);
		}
		return $val;
	}

	/**
	 * Query とパラメータから where句を生成する
	 * @param Query $query 検索条件
	 * @param array $params 検索パラメータ
	 * @return string where句
	 */
	public function buildWhere(Query $query, array $params): string {
		$where = $this->buildCond($query->conditions, $params);
		return $where;
	}

	/**
	 * 条件式の生成  
	 * 括弧を再帰的に処理するため、メソッドとして切り出している
	 * @param array $condList 条件定義の配列
	 * @param array $params パラメータの配列
	 * @return string 条件式(where)の文字列
	 */
	protected function buildCond(array $condList, array $params): string {
		$condition = '';
		$andOr = Op::NOP;
		foreach ($condList as $cond) {
			// AND/ORは条件式の構築で使う
			if ($cond == Op::AND || $cond == Op::OR) {
				// 既に条件式が有って、カッコの開始でなければ登録する
				if ($condition && !StrUtil::endsWith($condition, '(')) {
					$andOr = $cond;
				}
				continue;
			}
			$field  = ArrayUtil::get($cond, Query::FIELD);
			$op     = ArrayUtil::get($cond, Query::OP);
			$param1 = ArrayUtil::get($cond, Query::PARAM1);
			$param2 = ArrayUtil::get($cond, Query::PARAM2);
			if (!$op || !($op instanceof Op)) {
				// 条件式でない配列なら、括弧扱いにして、その中身をビルドする
				$result = $this->buildCond($cond, $params);
				if ($result) {
					// 中身が無ければ無視
					$condition .= ' ' . $andOr->name . ' (' . $result . ')';
				}
				continue;
			}
			// パラメータが必要な演算子の場合、対応するパラメータが無ければ無視
			if (Op::hasParam($op) && $this->isHolder($field, $param1)) {
				$val1 = ArrayUtil::get($params, $param1);
				if ($val1 === null) continue;
			}

			// パラメータをプレースホルダまたは即値に変換
			$holder1 = $this->makeHolder($field, $param1);
			$holder2 = $this->makeHolder($field, $param2);

			// 比較演算子にフィールド名、パラメータ名を埋め込み、条件式を構築
			$embedParam = [$field, $holder1, $holder2];
			$opStr = Query::getOpStr($op);
			$andOrStr = Query::getOpStr($andOr);
			$condition .= $andOrStr .  StrUtil::embed($opStr, $embedParam);
			$andOr = Op::NOP;
		}
		return $condition;
	}
	/**
	 * パラメータがプレースホルダかどうか
	 * @param string $field フィールド名
	 * @param mixed $param パラメータ
	 * @return bool true:プレースホルダ
	 */
	protected function isHolder(string $field, mixed $param): bool {
		return is_string($param) && StrUtil::startsWith($param, $field);
	}
	/**
	 * パラメータがフィールド名で始まればプレースホルダ、それ以外は即値として返す
	 * @param string $field フィールド名
	 * @param mixed $param パラメータ
	 * @return bool|int|string プレースホルダ、または即値
	 */
	protected function makeHolder(string $field, mixed $param): bool|int|string {
		if ($param === null) return '';

		// 即値：配列(IN用)
		if (is_array($param)) {
			$str = '';
			$dlm = '';
			foreach ($param as $val) {
				if (is_numeric($val)) {
					$str .= $dlm . $val;
				} else {
					$str .= $dlm . "'" . $val . "'";
				}
				$dlm = ',';
			}
			return $str;
		}

		// 即値：論理値
		if (is_bool($param)) return $param ? 'true' : 'false';

		// 即値：数値
		if (is_numeric($param)) return $param;


		// プレースホルダ
		if ($this->isHolder($field, $param)) return ':' . $param;

		if (is_string($param)) {
			// 即値：文字列
			return "'" . $param . "'";
		}
		return '';
	}
}
