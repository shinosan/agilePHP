<?php
require_once __DIR__ . '/_dir.php';
require_once BASE::UTIL . 'ArrayUtil.php';
require_once BASE::UTIL . 'TypeUtil.php';
require_once BASE::DBMS . 'Dbms.php';
require_once BASE::LOGIC . 'Logic.php';

/**
 * モデルの抽象基底クラス  
 * フィールドの値はnullを必ず含めること。  
 * デフォルトで全フィールドをANDで繋いだ検索定義を持つ。
 */
abstract class Model {
	public function __construct(int $pkey = 0) {
		$this->pkey = $pkey;
	}

	abstract public function modelName(): string;

	abstract public function getFields(): array;

	const FLD_COLUMN_NAME = 0;
	const FLD_JPANESE_NAME = 1;
	const FLD_DATA_TYPE = 2;
	const FLD_MODEL_PKEY = 3;
	const FLD_MODEL_NAME = 4;

	const pkey = ['pkey', '主キー', Types::INT];
	const createDate = ['create_date', '作成日', Types::DATETIME];
	const updateDate = ['update_date', '更新日', Types::DATETIME];
	const deleteFlag = ['delete_flag', '削除フラグ', Types::BOOL];

	public function clear() {
		$this->activated = false;
		$logic = $this->getLogic();
		$logic->removeUpdate($this);
	}
	private bool $activated = false;

	public function setActivate(bool $activated) {
		$this->activated = $activated;
	}

	public static function castModel(mixed $var): ?Model {
		return ($var instanceof Model) ? $var : null;
	}

	/**
	 * 連想配列からフィールドに値を読み込む
	 * @param array $values フィールド名をキーとした連想配列
	 * @param bool $onlyNull true:値がnullのフィールドにのみセットする (省略 = false)
	 * @param bool $update true:値をセットしたら更新対象にする (省略 = true)
	 */
	public function fromArray(array $values, bool $onlyNull = false, bool $update = true) {
		foreach ($this->getFields() as $field => $info) {
			$val = $this->getValue($field);
			if ($onlyNull && $val !== null) continue;

			$org = ArrayUtil::get($values, $field);
			if ($org === null) continue;

			$type = $info[self::FLD_DATA_TYPE];
			if ($type == Types::MODEL) continue;

			$value = TypeUtil::convertVal($type, $org);
			if ($org !== null && $value === null) {
				// 型変換失敗
				Results::self()->error($this->modelName() . '.' . $field, 'error.convert', $type, $org);
				continue;
			}
			$this->setValue($field, $value, $update);
		}
	}

	/**
	 * 連想配列にフィールドの値を書き出す
	 * @param bool $ignoreNull true:値がnullなら書き出さない (省略 = true)
	 * @return array [フィールド名=>値]
	 */
	public function toArray(bool $ignoreNull = true): array {
		$values = [];
		foreach ($this->getFields() as $field => $info) {
			// モデルを保持するフィールドは書き込まない
			$type = $info[self::FLD_DATA_TYPE];
			if ($type == Types::MODEL) continue;

			// 値の取得
			$val = $this->getValue($field);
			if ($ignoreNull && $val === null) continue;

			// 主キーがマイナスは新規作成なので、書き出さない
			if ($field == 'pkey' && $val < 0) continue;

			// 値を連想配列にセット
			$values[$field] = $val;
		}
		return $values;
	}

	/**
	 * データ型の配列を返す
	 * @param ?array $values 更新対象の値配列 null:全フィールド
	 * @return array [フィールド名=>データ型]
	 */
	public function getTypes(?array $values = null): array {
		$types = [];
		if ($values) {
			$names = array_keys($values);
			$fields = $this->getFields();
			foreach ($names as $name) {
				$info = ArrayUtil::get($fields, $name);
				$types[$name] = $info[self::FLD_DATA_TYPE];
			}
		} else {
			$fields = $this->getFields();
			foreach ($fields as $name => $info) {
				$types[$name] = $info[self::FLD_DATA_TYPE];
			}
		}
		return $types;
	}

	/**
	 * このモデルが依存しているモデル  
	 * 相手を先に新規作成して、主キーを有効にする
	 * @return array [モデル名 => 主キーのフィールド名]
	 */
	public function getDependModel(): array {
		$depends = [];
		foreach ($this->getFields() as $field => $info) {
			$modelName = ArrayUtil::get($info, self::FLD_MODEL_NAME);
			$modelPkey = ArrayUtil::get($info, self::FLD_MODEL_PKEY);
			if ($modelName) {
				$depends[$modelName] = $modelPkey;
			}
		}
		return $depends;
	}

	//// プロパティのセット/取得 ////

	public function setPkey(?int $val) {
		$this->pkey = $val;
	}
	public function getPkey(): ?int {
		return $this->pkey;
	}
	private ?int $pkey = null;

	public function setCreateDate(?int $val, bool $update = true) {
		$this->actBase($update)->createDate = $val;
	}
	public function getCreateDate(): ?int {
		return $this->actBase()->createDate;
	}
	private ?int $createDate = null;

	public function setUpdateDate(?int $val, bool $update = true) {
		$this->actBase($update)->updateDate = $val;
	}
	public function getUpdateDate(): ?int {
		return $this->actBase()->updateDate;
	}
	private ?int $updateDate = null;

	public function setDeleteFlag(?int $val, bool $update = true) {
		$this->actBase($update)->deleteFlag = $val;
	}
	public function getDeleteFlag(): ?int {
		return $this->actBase()->deleteFlag;
	}
	private ?int $deleteFlag = null;

	/**
	 * モデルの値をロードし活性化する。  
	 * 派生クラスで自クラスにキャストするものをオーバーロードする
	 * ```php
	 * private function act(): User { return $this->actBase(); }
	 * ```
	 * @param bool $updated true:更新された (省略=false)
	 * @return Model 
	 */
	protected function actBase(bool $updated = false): Model {
		$logic = $this->getLogic();
		if (!$logic) return $this;

		if ($updated) {
			$logic->register($this, $updated);
			return $this;
		}
		if ($this->activated) return $this;

		$logic->load($this);
		return $this;
	}

	protected function getLogic(): Logic {
		$logic = Logic::getLogic($this->modelName());
		return $logic;
	}

	protected function getValue(string $field): mixed {
		$getter = 'get' . StrUtil::upper1st($field);
		$value = $this->$getter();
		return $value;
	}
	protected function setValue(string $field, mixed $value, bool $update = true) {
		$setter = 'set' . StrUtil::upper1st($field);
		$this->$setter($value, $update);
	}
}
