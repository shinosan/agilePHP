<?php
require_once __DIR__ . '/_dir.php';
require_once BASE::UTIL . 'ArrayUtil.php';
require_once BASE::DBMS . 'Dbms.php';


/**
 * ユーザ
 */
class User extends Model {
	public function modelName(): string {
		return __CLASS__;
	}

	public function getFields(): array {
		return self::fields;
	}

	const userId    = ['user_id',    'ユーザID', Types::STRING];
	const name      = ['name',       '氏名', Types::STRING];
	const addressId = ['address_id', '住所ID', Types::INT];
	const building  = ['building',   '建物', Types::STRING];
	const address   = ['address',    '住所', Types::MODEL, 'addressId', 'Address'];

	const fields = [
		Model::pkey,
		Model::createDate,
		Model::updateDate,
		Model::deleteFlag,
		self::userId,
		self::name,
		self::addressId,
		self::building,
		self::address,
	];

	/** 
	 * ユーザIDをセットする  
	 * @param string $val セットする値 
	 */
	public function setUserId(string $val) {
		$this->userId = $val;
	}
	/** 
	 * ユーザIDを取得する  
	 * @return null|string 取得する値 
	 */
	public function getUserId(): null|string {
		return $this->act()->userId;
	}
	private null|string $userId = null;

	public function setName(string $name) {
		$this->name = $name;
	}
	public function getName(): null|string {
		return $this->act()->name;
	}
	private null|string $name = null;

	public function setAddress(Address $address) {
		$this->address = $address;
	}
	public function getAddress(): null|Address {
		return $this->address;
	}
	private ?Address $address = null;

	public function setAddressId(int $val) {
		$this->address = Logic::getLogic('Address')->getModelBase($val);
	}
	public function getAddressId(): int {
		return $this->address ? $this->address->getPkey() : 0;
	}

	public function setBuilding(DBNull|null|string $building) { // null許容の場合
		return $this->building = $building;
	}
	public function getBuilding(): DBNull|null|string { // null許容の場合
		return $this->act()->building;
	}
	private null|string $building = null;

	protected function act(): User {
		return $this->actBase();
	}
}

class AddressLogic extends Logic {
	public static AddressLogic $THIS;
	protected function new(int $pk = 0): Model {
		$model = $this->register(new Address($pk));
		return $model;
	}
	public function logicName(): string {
		return __CLASS__;
	}
	public function tableName(): string {
		return 't_address';
	}
	public function getModel(int $pkey): Address {
		return $this->getModelBase($pkey);
	}

	protected function newModel(int $pkey): Address {
		return $this->register(new Address(0));
	}
}
AddressLogic::$THIS = new AddressLogic();

/**
 * 住所
 */
class Address extends Model {
	public function modelName(): string {
		return __CLASS__;
	}

	const zipCode = ['zip_code', '郵便番号', Types::STRING];
	const prefecture = ['prefecture', '都道府県', Types::STRING];
	const city = ['city', '市町村', Types::STRING];
	const street = ['street', '街区', Types::STRING];

	const fields = [
		Model::pkey,
		Model::createDate,
		Model::updateDate,
		Model::deleteFlag,
		self::zipCode,
		self::prefecture,
		self::city,
		self::street,
	];

	public function getFields(): array {
		return self::fields;
	}

	public function setZipCode(null|string $val) {
		$this->act()->zipCode = $val;
	}
	public function getZipCode(): null|string {
		return $this->act()->zipCode;
	}
	private null|string $zipCode = null;

	public function setPrefecture(null|string $val) {
		$this->act()->prefecture = $val;
	}
	public function getPrefecture(): null|string {
		return $this->act()->prefecture;
	}
	private null|string $prefecture = null;

	public function setCity(null|string $val) {
		$this->act()->city = $val;
	}
	public function getCity(): null|string {
		return $this->act()->city;
	}
	private null|string $city = null;

	public function setStreet(null|string $val) {
		$this->act()->street = $val;
	}
	public function getStreet(): null|string {
		return $this->act()->street;
	}
	private null|string $street = null;

	protected function act(bool $isDirty = false): Address {
		return $this->actBase($isDirty);
	}
}
class Container {
}
