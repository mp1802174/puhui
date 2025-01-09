<?php

return [
    // 默认使用的数据库连接配置
    'default'         => env('database.driver', 'mysql'),

    // 自定义时间查询规则
    'time_query_rule' => [],

    // 自动写入时间戳字段
    // true为自动识别类型 false关闭
    // 字符串则明确指定时间字段类型 支持 int timestamp datetime date
    'auto_timestamp'  => true,

    // 时间字段取出后的默认时间格式
    'datetime_format' => 'Y-m-d H:i:s',

    // 时间字段配置 配置格式：create_time,update_time
    'datetime_field'  => '',

    // 数据库连接配置信息
    'connections'     => [
        'mysql' => [
            // 数据库类型
            'type'     => 'mysql',
            // 服务器地址
            'hostname' => '127.0.0.1',
            // 数据库名
            'database' => 'phkq',
            // 用户名
            'username' => 'root',
            // 密码
            'password' => '760516',
            // 端口
            'hostport' => '3306',
            // 设置SQL模式
            'params'         => [
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                PDO::ATTR_CASE => PDO::CASE_NATURAL,
                PDO::ATTR_STRINGIFY_FETCHES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4;SET sql_mode='ANSI_QUOTES,NO_AUTO_VALUE_ON_ZERO';"
            ],
            // 字符集
            'charset'        => 'utf8mb4',
            // 关闭严格模式
            'fields_strict'   => false,
            // 开启调试模式
            'debug'          => true,
            // 是否需要断线重连
            'break_reconnect' => true,
        ],
    ],
];
