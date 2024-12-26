use think\facade\Route;

Route::get('get_import_result', function() {
    return json(session('import_result', ['code' => 0, 'msg' => '没有导入结果']));
}); 