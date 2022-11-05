<?php
require_once __DIR__ . '/_dir.php';
require_once BASE::UTIL . 'ArrayUtil.php';
require_once BASE::DBMS . 'Dbms.php';
require_once BASE::MODEL . 'Model.php';

/**
 * モデルと対に自動生成され、モデルのCRUDを提供する。  
 */
abstract class Logic {
	use Logger;

	// public static Logic $THIS; //シングルトン
	/** @var array 全ロジック */
	protected static array $allLogics = [];

	/** @var array このロジックの管理する全モデル */
	protected array $allModels = [];

	/** @var array 新規作成された全モデル */
	protected array $createdModels = [];

	/** @var array 更新された全モデル */
	protected array $updatedModels = [];

	/** @var Dbms 対応するDBMS */
	protected Dbms $dbms;

	/**
	 * コンストラクタ
	 * 自分自身をLogic::$allLogicsに登録する
	 */
	public function __construct() {
		self::$allLogics[$this->logicName()] = $this;
	}

	/**
	 * ロジック名を返す  
	 * 派生クラスで下記のように実装する
	 * ```php
	 * public function logicName(): string { return __CLASS__; }
	 * ```
	 * @return string ロジック名
	 */
	abstract public function logicName(): string;

	/**
	 * ロジック名を返す  
	 * 派生クラスで下記のように実装する
	 * ```php
	 * public function tableName(): string { return 't_table_name'; }
	 * ```
	 * @return string ロジック名
	 */
	abstract public function tableName(): string;

	/**
	 * モデルを新規に生成する。  
	 * 派生クラスで下記のように実装する。
	 * ```php
	 * protected function newModel(int $pkey): Model {
	 *     return $this->register(new User($eypk));
	 * }
	 * ```
	 */
	abstract protected function newModel(int $pkey): Model;

	/**
	 * モデル名で対応するロジックを取得する
	 * @param string $modelName モデル名
	 * @return ?Logic null:見つからなかった場合
	 */
	public static function getLogic(string $modelName): ?Logic {
		return ArrayUtil::get(self::$allLogics, $modelName . 'Logic');
	}

	/**
	 * モデルを$allModelsに登録する。
	 * @param Model $model 登録するモデル
	 * @param bool $updated true:更新された(省略 = false) 
	 * @return Model
	 */
	public function register(Model $model, bool $updated = false): Model {
		if ($model->getPkey() === 0) {
			$model->setPkey($this->tmpPkey--);
			$this->createdModels[] = $model;
		} else if ($updated) {
			$this->updatedModels[] = $model;
		}
		$this->allModels[$model->getPkey()] = $model;
		return $model;
	}
	private int $tmpPkey = -1;

	public function removeUpdate(Model $model) {
		ArrayUtil::removeValue($this->updatedModels, $model);
	}

	/**
	 * モデルを取得する  
	 * 派生クラスで下記のように実装する。
	 * ```php
	 * protected function getModel(int $pkey): User { return $this->getModelBase($pkey); }
	 * ```
	 * @param ?int $pkey 主キー
	 * @return Model
	 */
	public function getModelBase(?int $pkey): Model {
		$model = ArrayUtil::get($this->allModels, $pkey);
		if (!$model) {
			if ($pkey === null) {
				$pkey = --$this->newPkey;
			}
			$model = $this->newModel($pkey);
		}
		return $model;
	}
	private int $newPkey = 0;

	/**
	 * 自動保存をキャンセルする
	 */
	public function cancel(Model $model) {
		ArrayUtil::remove($this->updatedModels, $model->getPkey());
	}

	/**
	 * 主キーにより1件のモデルをストレージからロードする
	 * @param Model $model ロードするモデル(主キーだけセットする)
	 * @param bool $lock true:更新用にロックする (省略 = false)
	 */
	public function load(Model $model, bool $lock = false): bool {
		// Modelのpkで連想配列を取得する
		$table = $this->tableName();
		$pkey = $model->getPkey();
		if ($pkey === null) return false;

		$types = $model->getTypes();
		// $lockなら行ロックをかける
		$values = $this->dbms->get($table, $pkey, $types, $lock);
		if (!is_array($values)) return false;

		// Modelの値がnullのフィールドだけセットする
		$model->fromArray($values, true);
		$model->activated = true; // 成功した場合
		// $更新用なら保存対象に登録
		if ($lock) {
			$this->updatedModels[$pkey] = $model;
		}
		return true;
	}
	/**
	 * 更新された全モデルをストレージに保存する
	 */
	public function saveAll(): bool {
		$model = ArrayUtil::get($this->allModels, 0);
		$model = Model::castModel($model);
		if ($model === null) {
			return false; // モデルが１件もない
		}

		// 依存対象のモデルを先に保存する
		foreach ($model->getDependModel() as $name => $id) {
			$logic = Logic::getLogic($name . 'Logic');
			$logic->saveAll();
		}

		// 現在の主キーの最大値を得る
		$table = $this->tableName();
		$lastPkey = $this->dbms->getMax($table, 'pKey');

		// 新規登録
		$models = [];
		$types = [];
		foreach ($this->createdModels as $model) {
			$model = Model::castModel($model);
			$values = $model->toArray(true);
			if (!$types) {
				$types = $model->getTypes($values);
			}
			$models[] = $values;
		}
		if ($models) {
			$this->dbms->create($table, $models, $types, true);
		}
		// 主キーをセットする
		foreach ($this->createdModels as $model) {
			$model->setPkey(++$lastPkey);
			$model->clear();
		}
		$this->createdModels = [];

		// 更新
		$models = [];
		foreach ($this->updatedModels as $model) {
			$model = Model::castModel($model);
			$values = $model->toArray(true);
			if (!$types) {
				$types = $model->getTypes($values);
			}
			$models[] = $values;
		}
		if ($models) {
			$this->dbms->update($table, $models, $types, true);
		}
		$this->updatedModels = [];

		// 物理削除 TODO:実装

		return true;
	}
	/**
	 * 検索
	 */
	public function select(Query $query, array $params, bool $lock = false): array {
		$models = [];
		$rows = $this->dbms->select($query, $params, $lock);
		if (is_array($rows)) {
			foreach ($rows as $row) {
				$pkey = $row[Model::pkey[Model::FLD_COLUMN_NAME]];
				$model = $this->getModelBase($pkey);
				$model->setActivate(true);
				$model->fromArray($row, false, false);
			}
		}
		return $models;
	}
	/**
	 * Modelを論理削除する
	 * @param Model $model 論理削除するモデル
	 */
	public function delete(Model $model): bool {
		$model->setDeleteFlag(1);
		return true;
	}
	/**
	 * Modelの主キーで物理削除する
	 * @param Model $model 物理削除するモデル
	 */
	public function deletePhysical(Model $model): bool {
		return true;
	}
}
// Logic::$THIS = new Logic(); // シングルトン