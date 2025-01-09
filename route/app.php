<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

// 加载 app/route 目录下的所有路由文件
foreach (glob(__DIR__ . '/route/*.php') as $routeFile) {
    require_once $routeFile;
}

Route::get('think', function () {
    return 'hello,ThinkPHP6!';
});

Route::get('hello/:name', 'index/hello');

//Route::get('import', 'Import/index');
//Route::post('import/import', 'Import/import');
Route::get('import/data', 'ImportController/importData');
Route::get('import/check', 'ImportController/checkCustomerCount');

// 每日记录路由
Route::get('daily_record/index', 'DailyRecord/index');
Route::post('daily_record/import', 'DailyRecord/import');
Route::get('daily_record/import_progress', 'DailyRecord/import_progress');
Route::get('daily_record/stop_import', 'DailyRecord/stopImport');

// 批量导入接口
Route::post('daily_record/import_batch', 'DailyRecord/importBatch');

Route::get('hierarchy', 'HierarchyController/index');

