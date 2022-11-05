<?php
require_once 'dir.php';
require_once BASE::UTIL . 'StrUtil.php';
/**
 * 郵便番号CSVの加工ツール
 */
class ZipCodeCsv {
	/** @var int 全国地方公共団体コード（JIS X0401、X0402）………　半角数字 */
	const FLD_CODE = 0;
	/** @var int （旧）郵便番号（5桁）………………………………………　半角数字 */
	const FLD_ZIP_OLD = 1;
	/** @var int 郵便番号（7桁）………………………………………　半角数字 */
	const FLD_ZIP = 2;
	/** @var int 都道府県名　…………　半角カタカナ（コード順に掲載）　（注1） */
	const FLD_PREF_KANA = 3;
	/** @var int 市区町村名　…………　半角カタカナ（コード順に掲載）　（注1） */
	const FLD_CITY_KANA = 4;
	/** @var int 町域名　………………　半角カタカナ（五十音順に掲載）　（注1） */
	const FLD_TOWN_KANA = 5;
	/** @var int 都道府県名　…………　漢字（コード順に掲載）　（注1,2） */
	const FLD_PREF = 6;
	/** @var int 市区町村名　…………　漢字（コード順に掲載）　（注1,2） */
	const FLD_CITY = 7;
	/** @var int 町域名　………………　漢字（五十音順に掲載）　（注1,2） */
	const FLD_TOWN = 8;

	/** @var int 一町域が二以上の郵便番号で表される場合の表示　（注3）　（「1」は該当、「0」は該当せず） */
	const FLG_ZIPS = 9;
	/** @var int 小字毎に番地が起番されている町域の表示　（注4）　（「1」は該当、「0」は該当せず） */
	const FLG_KOAZA = 10;
	/** @var int 丁目を有する町域の場合の表示　（「1」は該当、「0」は該当せず） */
	const FLG_CHOUME = 11;
	/** @var int 一つの郵便番号で二以上の町域を表す場合の表示　（注5）　（「1」は該当、「0」は該当せず） */
	const FLG_TOWNS = 12;
	/** @var int 更新の表示（注6）（「0」は変更なし、「1」は変更あり、「2」廃止（廃止データのみ使用）） */
	const FLG_UPDATE = 13;
	/** @var int 変更理由　（「0」は変更なし、「1」市政・区政・町政・分区・政令指定都市施行、「2」住居表示の実施、「3」区画整理、「4」郵便区調整等、「5」訂正、「6」廃止（廃止データのみ使用）） */
	const FLG_REAZON = 14;

	public function __construct(string $inputPath, string $outputPath) {
		$this->inputPath = $inputPath;
		$this->outputPath = $outputPath;
	}
	private string $inputPath = '';
	private string $outputPath = '';
	private $input;
	private $output;
	private array $inputCsv = [];
	private array $outputCsv = [];

	public function open(): bool {
		$this->input = fopen($this->inputPath, 'r');
		$this->output = fopen($this->outputPath, 'w');
		return $this->input !== false && $this->output !== false;
	}
	public function read(): bool {
		while (true) {
			$line = fgets($this->input);
			if ($line === false) break;

			$line = StrUtil::toUTF8($line);
			$line = StrUtil::replace('"', '', $line);
			$line = StrUtil::replace("\r\n", '', $line);
			$csv = explode(',', $line);
			$this->inputCsv[] = $csv;
		}
		return !empty($this->inputCsv);
	}

	public function convert(): bool {
		$outIdx = 0;
		$isDivided = false;
		$count = count($this->inputCsv);
		for ($i = 0; $i < $count; $i++) {
			$inCsvOrg = $this->inputCsv[$i];
			// 町域までの列を変換する
			$inCsv = $this->han2zen($inCsvOrg);

			// 町域名が分割されている場合、結合する
			$town = $inCsv[self::FLD_TOWN];
			$townKana = $inCsv[self::FLD_TOWN_KANA];
			if (mb_strpos($town, '（') !== false && mb_strpos($town, '）') === false) {
				// 分割の開始：「（」だけあって「）」が無い
				$isDivided = true;
				$this->outputCsv[] = $inCsv; // まず丸ごとコピーする
				continue; // 分割中は$outIdxを加算しない
			} else if ($isDivided) {
				// 分割されている町域名を連結
				$this->outputCsv[$outIdx][self::FLD_TOWN_KANA] .= $townKana;
				$this->outputCsv[$outIdx][self::FLD_TOWN] .= $town;
				if (mb_strpos($town, '）') !== false) {
					// 分割の終了：「）」あり
					$isDivided = false;
				} else {
					continue; // 分割中は$outIdxを加算しない
				}
			} else {
				// 分割されていなければ丸ごとコピーする
				$this->outputCsv[] = $inCsv;
			}
			$outIdx++;
		}
		return true;
	}
	protected function han2zen(array $csv): array {
		for ($idx = self::FLD_PREF_KANA; $idx <= self::FLD_TOWN; $idx++) {
			$value = $csv[$idx];
			// 町域カナまでの列を全角（カナ→かな）に変換する
			if ($idx <= self::FLD_TOWN_KANA) {
				// 半角英数記号カナ → 全角英数記号かな 変換
				$value = StrUtil::zenHan($value, StrUtil::CNV_ZEN_HIRA);
			}
			$csv[$idx] = $value;
		}
		return $csv;
	}

	const HEADER = "org_code,zip_code_old,zip_code,pref_kana,city_kana,town_kana,pref,city,town,flg_zips,flg_koaza,flg_choume,flg_towns,flg_update,flg_reason\n";

	public function write(): bool {
		$length = 0;
		// fputs($this->output, self::HEADER);
		foreach ($this->outputCsv as $csv) {
			$length += fputcsv($this->output, $csv);
		}
		return $length > 0;
	}
	public function close(): bool {
		$ret = true;
		$ret &= fclose($this->input);
		$ret &= fclose($this->output);
		return $ret;
	}
}
if ($argc < 3) {
	echo "usage: php ZipCodeCsv.php inputPath outputPath \n";
	exit;
}
$tool = new ZipCodeCsv($argv[1], $argv[2]);
$ok = $tool->open();
if ($ok) {
	$ok = $tool->read();
}
if ($ok) {
	$ok = $tool->convert();
}
if ($ok) {
	$ok = $tool->write();
}
if ($ok) {
	$ok = $tool->close();
}
