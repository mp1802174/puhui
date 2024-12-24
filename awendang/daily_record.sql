-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2024-12-04 18:23:12
-- 服务器版本： 5.7.26
-- PHP 版本： 7.3.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `phkq`
--

-- --------------------------------------------------------

--
-- 表的结构 `daily_record`
--

CREATE TABLE `daily_record` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `开户日期` varchar(12) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `客户名称` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `客户编号` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `核算机构` varchar(11) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `客户责任部门` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `账户性质` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `账户余额` decimal(15,2) DEFAULT NULL,
  `时点存款比昨日` decimal(15,2) DEFAULT NULL,
  `时点存款比月初` decimal(15,2) DEFAULT NULL,
  `时点存款比年初` decimal(15,2) DEFAULT NULL,
  `月日均存款余额` decimal(15,2) DEFAULT NULL,
  `年日均最新日期` varchar(12) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `年日均存款余额` decimal(15,2) DEFAULT NULL,
  `年日均存款比昨日` decimal(15,2) DEFAULT NULL,
  `年日均存款比月初` decimal(15,2) DEFAULT NULL,
  `年日均存款比年初` decimal(15,2) DEFAULT NULL,
  `经办人员工编号` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `认定状态` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `认定日期` varchar(12) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `对公客户账号` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `业务标识号` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `核算机构编号` varchar(9) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人一` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人二` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人三` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人四` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人五` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人六` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人七` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人八` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人九` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人一十` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人一十一` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人一十二` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人名称一` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人名称二` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人名称三` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人名称四` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人名称五` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人名称六` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人名称七` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人名称八` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人名称九` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人名称一十` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人名称一十一` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人名称一十二` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
