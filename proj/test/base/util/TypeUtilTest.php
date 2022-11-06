<?php
require_once __DIR__ . '/_dir.php';
require_once BASE::UTIL . 'TypeUtil.php';

const LF = "\n";

function isEnum(mixed $val) {
	echo (TypeUtil::isEnum($val) ? 'enum' : 'not enum') . LF;
}
isEnum(Types::ENUM);
isEnum('abc');

$val = TypeUtil::toEnum('Types::STRING');
echo $val->name . LF;
