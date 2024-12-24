-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2024-11-18 15:50:41
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
-- 表的结构 `customer_info`
--

CREATE TABLE `customer_info` (
  `id` int(11) NOT NULL,
  `客户编号` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `对公客户账号` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `客户名称` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `核算机构` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `客户责任部门` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `账户性质` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `核算机构编号` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `经办人员工编号` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `业务标识号` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人一` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人二` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人三` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人四` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人五` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人六` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人七` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人八` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人九` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人名称一` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人名称二` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人名称三` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人名称四` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人名称五` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人名称六` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人名称七` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人名称八` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `营销人名称九` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `daily_balance`
--

CREATE TABLE `daily_balance` (
  `id` bigint(20) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `日期` date NOT NULL,
  `账户余额` decimal(15,2) DEFAULT NULL,
  `时点存款比昨日` decimal(15,2) DEFAULT NULL,
  `时点存款比月初` decimal(15,2) DEFAULT NULL,
  `时点存款比年初` decimal(15,2) DEFAULT NULL,
  `月日均存款余额` decimal(15,2) DEFAULT NULL,
  `年日均存款余额` decimal(15,2) DEFAULT NULL,
  `年日均存款比昨日` decimal(15,2) DEFAULT NULL,
  `年日均存款比月初` decimal(15,2) DEFAULT NULL,
  `年日均存款比年初` decimal(15,2) DEFAULT NULL,
  `认定状态` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `认定日期` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `daily_record`
--

CREATE TABLE `daily_record` (
  `id` varchar(100) DEFAULT NULL,
  `开户日期` varchar(12) DEFAULT NULL,
  `客户名称` varchar(100) DEFAULT NULL,
  `客户编号` varchar(20) DEFAULT NULL,
  `核算机构` varchar(11) DEFAULT NULL,
  `客户责任部门` varchar(100) DEFAULT NULL,
  `账户性质` varchar(100) DEFAULT NULL,
  `账户余额` varchar(20) DEFAULT NULL,
  `时点存款比昨日` varchar(20) DEFAULT NULL,
  `时点存款比月初` varchar(20) DEFAULT NULL,
  `时点存款比年初` varchar(20) DEFAULT NULL,
  `月日均存款余额` varchar(20) DEFAULT NULL,
  `年日均最新日期` varchar(12) DEFAULT NULL,
  `年日均存款余额` varchar(20) DEFAULT NULL,
  `年日均存款比昨日` varchar(20) DEFAULT NULL,
  `年日均存款比月初` varchar(20) DEFAULT NULL,
  `年日均存款比年初` varchar(20) DEFAULT NULL,
  `经办人员工编号` varchar(100) DEFAULT NULL,
  `认定状态` varchar(100) DEFAULT NULL,
  `认定日期` varchar(12) DEFAULT NULL,
  `对公客户账号` varchar(100) DEFAULT NULL,
  `业务标识号` varchar(100) DEFAULT NULL,
  `核算机构编号` varchar(9) DEFAULT NULL,
  `营销人一` varchar(100) DEFAULT NULL,
  `营销人二` varchar(100) DEFAULT NULL,
  `营销人三` varchar(100) DEFAULT NULL,
  `营销人四` varchar(100) DEFAULT NULL,
  `营销人五` varchar(100) DEFAULT NULL,
  `营销人六` varchar(100) DEFAULT NULL,
  `营销人七` varchar(100) DEFAULT NULL,
  `营销人八` varchar(100) DEFAULT NULL,
  `营销人九` varchar(100) DEFAULT NULL,
  `营销人一十` varchar(100) DEFAULT NULL,
  `营销人一十一` varchar(100) DEFAULT NULL,
  `营销人一十二` varchar(100) DEFAULT NULL,
  `营销人名称一` varchar(100) DEFAULT NULL,
  `营销人名称二` varchar(100) DEFAULT NULL,
  `营销人名称三` varchar(100) DEFAULT NULL,
  `营销人名称四` varchar(100) DEFAULT NULL,
  `营销人名称五` varchar(100) DEFAULT NULL,
  `营销人名称六` varchar(100) DEFAULT NULL,
  `营销人名称七` varchar(100) DEFAULT NULL,
  `营销人名称八` varchar(100) DEFAULT NULL,
  `营销人名称九` varchar(100) DEFAULT NULL,
  `营销人名称一十` varchar(100) DEFAULT NULL,
  `营销人名称一十一` varchar(100) DEFAULT NULL,
  `营销人名称一十二` varchar(100) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转储表的索引
--

--
-- 表的索引 `customer_info`
--
ALTER TABLE `customer_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_customer` (`客户编号`,`对公客户账号`);

--
-- 表的索引 `daily_balance`
--
ALTER TABLE `daily_balance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_date` (`日期`),
  ADD KEY `idx_customer_date` (`customer_id`,`日期`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `customer_info`
--
ALTER TABLE `customer_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `daily_balance`
--
ALTER TABLE `daily_balance`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- 限制导出的表
--

--
-- 限制表 `daily_balance`
--
ALTER TABLE `daily_balance`
  ADD CONSTRAINT `daily_balance_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer_info` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
