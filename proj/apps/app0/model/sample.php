<?php

// namespace aA12_㈱原田商会; // 名前空間の難読化
namespace app0;

require_once __DIR__ . '/_dir.php';
require_once \BASE::MODEL . 'Model.php';

/**
 * app0のサンプルモデル  
 * base の クラスは'\'が付く
 */
class Sample extends \Model {
	public function modelName(): string {
		return '';
	}
	public function getFields(): array {
		return [];
	}
}

// use app1\Sample;
