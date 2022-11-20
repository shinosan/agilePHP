<?php
require_once __DIR__ . '/_dir.php';
require_once GAME::MODEL . 'Figure.php';
require_once BASE::MODEL . 'Model.php';

/**
 * ゲームキャラ
 * キャラの属性(生命力、魔力、筋力等)を保持する
 */
class Chara extends Model {
	/**
	 * モデル名を返す
	 */
	public function modelName(): string {
		return __CLASS__;
	}

	/** @var array キャラクタID */
	const charaId = ['chara_id', 'キャラクタID', Types::INT];
	/** @var array 対象ID */
	const targetId = ['target_id', '対象ID', Types::INT];
	/** @var array キャラクタ名(英字) */
	const name = ['name', 'キャラクタ名(英字)', Types::STRING];
	/** @var array キャラクタ名(日本語) */
	const nameJ = ['name_j', 'キャラクタ名(日本語)', Types::STRING];
	/** @var array フィギュアID */
	const figureId = ['figure_id', 'フィギュアID', Types::INT];
	/** @var array 生命力 */
	const life = ['life', '生命力', Types::INT];
	/** @var array 魔力 */
	const magic = ['magic', '魔力', Types::INT];
	/** @var array 筋力 */
	const physical = ['physical', '筋力', Types::INT];
	/** @var array シナリオID */
	const senarioId = ['senario_id', 'シナリオID', Types::INT];

	/** @var array フィールド一覧 */
	const fields = [
		Model::pkey,
		Model::createDate,
		Model::updateDate,
		Model::deleteFlag,
		self::charaId,
		self::targetId,
		self::name,
		self::nameJ,
		self::figureId,
		self::life,
		self::magic,
		self::physical,
		self::senarioId,
	];

	/**
	 * フィールド一覧を返す
	 */
	public function getFields(): array {
		return self::fields;
	}

	/**
	 * キャラクタIDを返す
	 * @return int キャラクタID
	 */
	public function charaId(): int {
		return $this->charaId;
	}
	/**
	 * キャラクタIDをセットする
	 * @param int $val セットする値
	 * @return Chara
	 */
	public function setCharaId(int $val): Chara {
		$this->charaId = $val;
		return $this;
	}
	/** @var int キャラクタID */
	protected int $charaId;

	/**
	 * 対象IDを返す
	 * @return int 対象ID
	 */
	public function targetId(): int {
		return $this->targetId;
	}
	/**
	 * 対象IDをセットする
	 * @param int $val セットする値
	 * @return Chara
	 */
	public function setTargetId(int $val): Chara {
		$this->targetId = $val;
		return $this;
	}
	/** @var int 対象ID */
	protected int $targetId;

	/**
	 * キャラクタ名を返す
	 * @return string ng
	 */
	public function name(): string {
		return $this->name;
	}
	/**
	 * キャラクタ名をセットする
	 * @param string $val セットする値
	 * @return Chara
	 */
	public function setName(string $val): Chara {
		$this->name = $val;
		return $this;
	}
	/** @var string キャラクタ名 */
	protected string $name;

	/**
	 * キャラクタ名(日本語)を返す
	 * @return string キャラクタ名(日本語)
	 */
	public function nameJ(): string {
		return $this->nameJ;
	}
	/**
	 * キャラクタ名(日本語)をセットする
	 * @param string $val セットする値
	 * @return Chara
	 */
	public function setNameJ(string $val): Chara {
		$this->nameJ = $val;
		return $this;
	}
	/** @var string キャラクタ名(日本語) */
	protected string $nameJ;

	/**
	 * マップに表示するフィギュアを返す
	 * @return Figure フィギュア
	 */
	public function figureId(): Figure {
		return $this->figureId;
	}
	/**
	 * マップに表示するフィギュアをセットする
	 * @param Figure $val セットする値
	 * @return Chara
	 */
	public function setFigure(Figure $val): Chara {
		$this->figureId = $val;
		return $this;
	}
	/** @var Figure マップに表示するフィギュア */
	protected Figure $figureId;

	/**
	 * 生命力を返す
	 * @return int 生命力
	 */
	public function life(): int {
		return $this->life;
	}
	/**
	 * 生命力をセットする
	 * @param int $val セットする値
	 * @return Chara
	 */
	public function setLife(int $val): Chara {
		$this->life = $val;
		return $this;
	}
	/** @var int 生命力 */
	protected int $life;

	/**
	 * 魔力を返す
	 * @return int 魔力
	 */
	public function magic(): int {
		return $this->magic;
	}
	/**
	 * 魔力をセットする
	 * @param int $val セットする値
	 * @return Chara
	 */
	public function setMagic(int $val): Chara {
		$this->magic = $val;
		return $this;
	}
	/** @var int 魔力 */
	protected int $magic;

	/**
	 * 筋力を返す
	 * @return int 筋力
	 */
	public function physical(): int {
		return $this->physical;
	}
	/**
	 * 筋力をセットする
	 * @param int $val セットする値
	 * @return Chara
	 */
	public function setPhysical(int $val): Chara {
		$this->physical = $val;
		return $this;
	}
	/** @var int 筋力 */
	protected int $physical;

	/**
	 * シナリオIDを返す
	 * @return int シナリオID
	 */
	public function senarioId(): int {
		return $this->senarioId;
	}
	/**
	 * シナリオIDをセットする
	 * @param int $val セットする値
	 * @return Chara
	 */
	public function setSenarioId(int $val): Chara {
		$this->senarioId = $val;
		return $this;
	}
	/** @var int シナリオID */
	protected int $senarioId;
}

/**
 * ゲーム中のアイテム
 */
class Item extends Model {
	/**
	 * モデル名を返す
	 */
	public function modelName(): string {
		return __CLASS__;
	}

	/** @var array 名称 */
	const name     = [];
	/** @var array 名称(日本語) */
	const nameJ    = [];
	/** @var array 価格 */
	const cost     = [];
	/** @var array 実売価格 */
	const costReal = [];
	/** @var array 生命力上昇 */
	const life     = [];
	/** @var array 魔力上昇 */
	const magic    = [];
	/** @var array 筋力上昇 */
	const physical = [];
	/** @var array 説明文 */
	const comment  = [];
	/** @var array 使用時のメッセージ */
	const message  = [];

	const fields = [];

	/**
	 * フィールド一覧を返す
	 */
	public function getFields(): array {
		return self::fields;
	}
}
