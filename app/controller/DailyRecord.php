<?php
declare (strict_types = 1);

namespace app\controller;

use app\service\ExcelImport;
use think\facade\View;
use think\Request;
use think\facade\Filesystem;
use think\facade\Log;

class DailyRecord
{
    /**
     * 显示上传页面
     */
    public function index()
    {
        return View::fetch();
    }

    /**
     * 处理文件上传和导入
     */
    public function import(Request $request)
    {
        if ($request->isPost()) {
            try {
                $file = $request->file('file');
                if (!$file) {
                    return json(['code' => 0, 'msg' => '请选择要导入的Excel文件']);
                }

                // 获取原始文件名
                $originalName = $file->getOriginalName();
                
                // 保存文件
                $savename = Filesystem::disk('public')->putFile('excel', $file);
                $filePath = str_replace('/', DIRECTORY_SEPARATOR, public_path() . 'storage' . DIRECTORY_SEPARATOR . $savename);

                Log::info("控制器开始导入", [
                    '时间' => date('Y-m-d H:i:s.u'),
                    '文件路径' => $filePath,
                    '原始文件名' => $originalName
                ]);

                $service = new ExcelImport();
                // 传入原始文件名
                $result = $service->importDailyRecord($filePath, $originalName);
                
                Log::info("控制器导入完成", [
                    '时间' => date('Y-m-d H:i:s.u'),
                    '结果' => $result
                ]);
                
                return json([
                    'code' => 1, 
                    'msg' => $result['message'],
                    'filePath' => $filePath
                ]);
            } catch (\Exception $e) {
                Log::error("控制器导入异常", [
                    '时间' => date('Y-m-d H:i:s.u'),
                    '错误' => $e->getMessage(),
                    '文件' => $e->getFile(),
                    '行号' => $e->getLine(),
                    '堆栈' => $e->getTraceAsString()
                ]);
                return json(['code' => 0, 'msg' => '导入失败：' . $e->getMessage()]);
            }
        }
        return view();
    }

    /**
     * 获取导入进度
     */
    public function import_progress()
    {
        $filePath = input('filePath');
        
        Log::info("控制器进度查询", [
            '时间' => date('Y-m-d H:i:s.u'),
            '文件路径' => $filePath
        ]);
        
        $service = new ExcelImport();
        $progress = $service->getImportProgress($filePath);
        
        Log::info("控制器进度查询结果", [
            '时间' => date('Y-m-d H:i:s.u'),
            '进度' => $progress
        ]);
        
        return json(['code' => 1, 'data' => $progress]);
    }

    /**
     * 停止导入
     */
    public function stopImport()
    {
        try {
            $import = new ExcelImport();
            $import->stopImport();
            return json(['code' => 1, 'msg' => '导入已停止']);
        } catch (\Exception $e) {
            return json(['code' => 0, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 处理批量
     */
    public function importBatch(Request $request)
    {
        try {
            $params = json_decode($request->getContent(), true);
            if (empty($params['data'])) {
                return json(['code' => 0, 'msg' => '数据不能为空']);
            }

            $service = new ExcelImport();
            $result = $service->importBatch($params['data']);
            
            return json([
                'code' => 1,
                'msg' => '批次导入成功',
                'data' => [
                    'success' => $result['success'],
                    'error' => $result['error']
                ]
            ]);
        } catch (\Exception $e) {
            return json(['code' => 0, 'msg' => '导入失败：' . $e->getMessage()]);
        }
    }
} 