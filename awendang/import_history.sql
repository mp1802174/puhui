-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2024-12-10 19:12:17
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

--
-- 转储表的索引
--

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
-- 使用表AUTO_INCREMENT `import_history`
--
ALTER TABLE `import_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
