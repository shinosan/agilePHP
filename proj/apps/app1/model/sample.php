<?php

namespace app1;

require_once __DIR__ . '/_dir.php';
require_once \BASE::MODEL . 'Model.php';

// 名前空間を use すれば、他者のアプリも見えてしまう
use app0;

require_once app0\APP::MODEL . 'Model.php';

// use app0\Sample; // 有効にすると、こちらの class Sample がエラーとなる

/**
 * app1のサンプルモデル
 */
class Sample extends \Model {
	public function modelName(): string {
		return '';
	}
	public function getFields(): array {
		return [];
	}
}
$sample0 = new app0\Sample(); // TODO:名前空間が分かれば他のAPPのコードも流用できてしまう
$sample1 = new Sample();
