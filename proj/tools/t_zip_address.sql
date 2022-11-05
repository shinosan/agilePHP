-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- ホスト: 127.0.0.1
-- 生成日時: 2022-10-22 12:36:35
-- サーバのバージョン： 10.4.22-MariaDB
-- PHP のバージョン: 8.1.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- データベース: `agile_common`
--

-- --------------------------------------------------------

--
-- テーブルの構造 `t_zip_address`
--

CREATE TABLE `t_zip_address` (
  `org_code` varchar(11) NOT NULL COMMENT '地方公共団体コード',
  `zip_code_old` varchar(6) NOT NULL COMMENT '旧郵便番号',
  `zip_code` varchar(11) NOT NULL COMMENT '郵便番号',
  `pref_kana` varchar(10) NOT NULL COMMENT '都道府県カナ',
  `city_kana` varchar(10) NOT NULL COMMENT '市区町村カナ',
  `town_kana` varchar(500) NOT NULL COMMENT '町域名カナ',
  `pref` varchar(4) NOT NULL COMMENT '都道府県',
  `city` varchar(10) NOT NULL COMMENT '市区町村',
  `town` varchar(500) NOT NULL COMMENT '町域名',
  `flg_zips` tinyint(4) NOT NULL COMMENT '町域が複数の郵便番号持つ',
  `flg_koaza` tinyint(4) NOT NULL COMMENT '小字ごとに番地持つ',
  `flg_choume` tinyint(4) NOT NULL COMMENT '～丁目あり',
  `flg_towns` tinyint(4) NOT NULL COMMENT '１郵便番号で複数の町域',
  `flg_update` tinyint(4) NOT NULL COMMENT '変更内容(0:変更なし,1:変更あり,2:廃止)',
  `flg_reason` tinyint(4) NOT NULL COMMENT '変更理由'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
