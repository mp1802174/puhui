-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2025-01-09 10:34:24
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
  `ID` int(11) NOT NULL,
  `开户日期` varchar(12) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `客户编号` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `账户状态` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `对公客户账号` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `客户名称` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `核算机构` varchar(11) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `客户责任部门` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `账户性质` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `核算机构编号` varchar(9) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `经办人员工编号` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `业务标识号` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `营销人名称一十二` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `导入日期` date DEFAULT NULL,
  `客户类型` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `daily_balance`
--

CREATE TABLE `daily_balance` (
  `id` bigint(20) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `日期` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `认定状态` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `认定日期` varchar(12) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `daily_record`
--

CREATE TABLE `daily_record` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `开户日期` varchar(12) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `客户名称` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `账户状态` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '默认值',
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

-- --------------------------------------------------------

--
-- 表的结构 `daily_record_bak`
--

CREATE TABLE `daily_record_bak` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `开户日期` varchar(12) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `客户名称` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `账户状态` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '默认值',
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

-- --------------------------------------------------------

--
-- 表的结构 `import_history`
--

CREATE TABLE `import_history` (
  `id` int(11) NOT NULL,
  `file_md5` varchar(32) NOT NULL COMMENT '文件MD5值',
  `file_name` varchar(255) NOT NULL COMMENT '文件名',
  `file_size` bigint(20) NOT NULL COMMENT '文件大小',
  `import_time` datetime NOT NULL COMMENT '导入时间',
  `success_count` int(11) NOT NULL DEFAULT '0' COMMENT '成功导入数量',
  `error_count` int(11) NOT NULL DEFAULT '0' COMMENT '失败数量'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='导入历史记录表';

-- --------------------------------------------------------

--
-- 表的结构 `jigou`
--

CREATE TABLE `jigou` (
  `市行机构号` int(9) NOT NULL,
  `市行名称` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `支行机构号` int(9) NOT NULL,
  `支行名称` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `核算机构编号` int(9) NOT NULL,
  `核算机构` text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转储表的索引
--

--
-- 表的索引 `customer_info`
--
ALTER TABLE `customer_info`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `idx_customer_account` (`客户编号`,`对公客户账号`,`账户性质`),
  ADD UNIQUE KEY `idx_unique_customer` (`客户编号`,`对公客户账号`,`账户性质`);

--
-- 表的索引 `daily_balance`
--
ALTER TABLE `daily_balance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_date` (`日期`),
  ADD KEY `idx_customer_date` (`customer_id`,`日期`);

--
-- 表的索引 `import_history`
--
ALTER TABLE `import_history`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_file_md5` (`file_md5`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `customer_info`
--
ALTER TABLE `customer_info`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `daily_balance`
--
ALTER TABLE `daily_balance`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `import_history`
--
ALTER TABLE `import_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 限制导出的表
--

--
-- 限制表 `daily_balance`
--
ALTER TABLE `daily_balance`
  ADD CONSTRAINT `daily_balance_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer_info` (`ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
