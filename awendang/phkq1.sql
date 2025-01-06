-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2024-11-21 16:46:36
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
  `开户日期` varchar(12) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `客户编号` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `营销人名称一十二` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `customer_info`
--

INSERT INTO `customer_info` (`id`, `开户日期`, `客户编号`, `对公客户账号`, `客户名称`, `核算机构`, `客户责任部门`, `账户性质`, `核算机构编号`, `经办人员工编号`, `业务标识号`, `营销人一`, `营销人二`, `营销人三`, `营销人四`, `营销人五`, `营销人六`, `营销人七`, `营销人八`, `营销人九`, `营销人一十`, `营销人一十一`, `营销人一十二`, `营销人名称一`, `营销人名称二`, `营销人名称三`, `营销人名称四`, `营销人名称五`, `营销人名称六`, `营销人名称七`, `营销人名称八`, `营销人名称九`, `营销人名称一十`, `营销人名称一十一`, `营销人名称一十二`) VALUES
(3, '2004/10/30', '768890000003844553', '370016308410****0017', '市经济开发投资公司', '建行淄博分行本级', '公司业务部', '委贷基金账户', '370630841', 'CN0003700163084105000001715610', 'Q04wMDAzNzAwMTYzMDg0MTA1MDAwMDAxNzE1NjEw', '370630009-12977500:100%', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '建行淄博分行公司业务部-尚爱民:100%', '', '', '', '', '', '', '', ''),
(4, '2004/10/30', '115890000009443022', '370016308410****0023', '淄博市供水建设基金', '建行淄博分行本级', '公司业务部', '委贷基金账户', '370630841', 'CN0003700163084105000002315610', 'Q04wMDAzNzAwMTYzMDg0MTA1MDAwMDAyMzE1NjEw', '370630009-12977500:100%', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '建行淄博分行公司业务部-尚爱民:100%', '', '', '', '', '', '', '', '');

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

--
-- 转存表中的数据 `daily_balance`
--

INSERT INTO `daily_balance` (`id`, `customer_id`, `日期`, `账户余额`, `时点存款比昨日`, `时点存款比月初`, `时点存款比年初`, `月日均存款余额`, `年日均最新日期`, `年日均存款余额`, `年日均存款比昨日`, `年日均存款比月初`, `年日均存款比年初`, `认定状态`, `认定日期`) VALUES
(1, 3, '2024-11-21', '11480000.00', '0.00', '0.00', '0.00', '11480000.00', '20240721', '11480000.00', '0.00', '0.00', '0.00', '营销人已认定', '20240416'),
(2, 4, '2024-11-21', '33255721.98', '0.00', '0.00', '0.00', '33255721.98', '20240721', '33255721.98', '0.00', '0.00', '0.00', '营销人已认定', '20240704');

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

--
-- 转存表中的数据 `daily_record`
--

INSERT INTO `daily_record` (`id`, `开户日期`, `客户名称`, `客户编号`, `核算机构`, `客户责任部门`, `账户性质`, `账户余额`, `时点存款比昨日`, `时点存款比月初`, `时点存款比年初`, `月日均存款余额`, `年日均最新日期`, `年日均存款余额`, `年日均存款比昨日`, `年日均存款比月初`, `年日均存款比年初`, `经办人员工编号`, `认定状态`, `认定日期`, `对公客户账号`, `业务标识号`, `核算机构编号`, `营销人一`, `营销人二`, `营销人三`, `营销人四`, `营销人五`, `营销人六`, `营销人七`, `营销人八`, `营销人九`, `营销人一十`, `营销人一十一`, `营销人一十二`, `营销人名称一`, `营销人名称二`, `营销人名称三`, `营销人名称四`, `营销人名称五`, `营销人名称六`, `营销人名称七`, `营销人名称八`, `营销人名称九`, `营销人名称一十`, `营销人名称一十一`, `营销人名称一十二`) VALUES
('768890000003844553/370016308410****0017', '2004/10/30', '市经济开发投资公司', '768890000003844553', '建行淄博分行本级', '公司业务部', '委贷基金账户', '11480000.00', '0.00', '0.00', '0.00', '11480000.00', '20240721', '11480000.00', '0.00', '0.00', '0.00', 'CN0003700163084105000001715610', '营销人已认定', '20240416', '370016308410****0017', 'Q04wMDAzNzAwMTYzMDg0MTA1MDAwMDAxNzE1NjEw', '370630841', '370630009-12977500:100%', '', '', '', '', '', '', '', '', NULL, NULL, NULL, '', '', '', '建行淄博分行公司业务部-尚爱民:100%', '', '', '', '', '', NULL, NULL, NULL),
('115890000009443022/370016308410****0023', '2004/10/30', '淄博市供水建设基金', '115890000009443022', '建行淄博分行本级', '公司业务部', '委贷基金账户', '33255721.98', '0.00', '0.00', '0.00', '33255721.98', '20240721', '33255721.98', '0.00', '0.00', '0.00', 'CN0003700163084105000002315610', '营销人已认定', '20240704', '370016308410****0023', 'Q04wMDAzNzAwMTYzMDg0MTA1MDAwMDAyMzE1NjEw', '370630841', '370630009-12977500:100%', '', '', '', '', '', '', '', '', NULL, NULL, NULL, '', '', '', '建行淄博分行公司业务部-尚爱民:100%', '', '', '', '', '', NULL, NULL, NULL);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `daily_balance`
--
ALTER TABLE `daily_balance`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
