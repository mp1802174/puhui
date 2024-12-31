<?php
declare (strict_types = 1);

namespace app\controller;

use think\facade\View;
use think\facade\Log;

class ImportController
{
    /**
     * 显示导入页面
     */
    public function index()
    {
        return view();
    }

    /**
     * 获取PDO连接
     */
    private function getPDO()
    {
        $pdo = new \PDO(
            'mysql:host=localhost;dbname=phkq;charset=utf8mb4',
            'root',
            '760516',
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
        );
        Log::info("直接MySQL连接测试：成功");
        return $pdo;
    }

    /**
     * 导入数据
     */
    public function importData()
    {
        Log::info('=== 开始导入数据 ===');
        
        try {
            $pdo = $this->getPDO();
            
<<<<<<< HEAD
            // 先获取原始数据数量
            $sourceCount = $pdo->query("SELECT COUNT(*) FROM daily_record")->fetchColumn();
            Log::info("原始数据数量: {$sourceCount} 条记录");
            
=======
>>>>>>> a1021d9b1a416bfe73b01d5733c1d6e40893f15f
            // 1. 导入客户基本信息
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
            $pdo->exec("TRUNCATE TABLE customer_info");
            
            $infoSql = "INSERT INTO customer_info (
                开户日期, 客户编号, 对公客户账号, 客户名称, 
                核算机构, 客户责任部门, 账户性质, 核算机构编号,
                经办人员工编号, 业务标识号, 
                营销人一, 营销人二, 营销人三, 营销人四, 营销人五,
                营销人六, 营销人七, 营销人八, 营销人九, 营销人一十,
                营销人一十一, 营销人一十二,
                营销人名称一, 营销人名称二, 营销人名称三, 营销人名称四, 营销人名称五,
                营销人名称六, 营销人名称七, 营销人名称八, 营销人名称九, 营销人名称一十,
                营销人名称一十一, 营销人名称一十二
            )
            SELECT 
                开户日期, 客户编号, 对公客户账号, 客户名称,
                核算机构, 客户责任部门, 账户性质, 核算机构编号,
                经办人员工编号, 业务标识号,
                营销人一, 营销人二, 营销人三, 营销人四, 营销人五,
                营销人六, 营销人七, 营销人八, 营销人九, 营销人一十,
                营销人一十一, 营销人一十二,
                营销人名称一, 营销人名称二, 营销人名称三, 营销人名称四, 营销人名称五,
                营销人名称六, 营销人名称七, 营销人名称八, 营销人名称九, 营销人名称一十,
                营销人名称一十一, 营销人名称一十二
            FROM daily_record";
            
            $pdo->exec($infoSql);
            
            $infoCount = $pdo->query("SELECT COUNT(*) FROM customer_info")->fetchColumn();
            Log::info("客户基本信息导入完成: {$infoCount} 条记录");
            
            // 2. 导入余额信息
            $pdo->exec("TRUNCATE TABLE daily_balance");
            
            $balanceSql = "INSERT INTO daily_balance (
                customer_id,
                日期,
                账户余额,
                时点存款比昨日,
                时点存款比月初,
                时点存款比年初,
                月日均存款余额,
                年日均存款余额,
                年日均存款比昨日
            )
            SELECT 
                i.ID as customer_id,
                r.年日均最新日期 as 日期,
                r.账户余额,
                r.时点存款比昨日,
                r.时点存款比月初,
                r.时点存款比年初,
                r.月日均存款余额,
                r.年日均存款余额,
                r.年日均存款比昨日
            FROM daily_record r
            INNER JOIN customer_info i ON 
                r.客户编号 = i.客户编号 AND 
                r.对公客户账号 = i.对公客户账号 AND 
                r.账户性质 = i.账户性质";
<<<<<<< HEAD
            
            $pdo->exec($balanceSql);
            
            $balanceCount = $pdo->query("SELECT COUNT(*) FROM daily_balance")->fetchColumn();
            Log::info("余额信息导入完成: {$balanceCount} 条记录");
            
            // 3. 清空原始数据表
            $pdo->exec("TRUNCATE TABLE daily_record");
            Log::info("清空原始数据表 daily_record");
            
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
            
            $pdo = null;
            
            // 清理缓存文件
            $this->clearCache();
            
            // 返回简单的消息
            return "<script>alert('导入完成！\\n原始数据：{$sourceCount} 条\\n客户信息：{$infoCount} 条\\n余额信息：{$balanceCount} 条');window.location.href='/daily_record/index';</script>";
=======
                
            $pdo->exec($balanceSql);
            
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
            
            $balanceCount = $pdo->query("SELECT COUNT(*) FROM daily_balance")->fetchColumn();
            Log::info("余额信息导入完成: {$balanceCount} 条记录");
            
            $pdo = null;
            
            return json([
                'code' => 1,
                'msg' => '数据导入完成',
                'data' => [
                    'info_count' => $infoCount,
                    'balance_count' => $balanceCount
                ]
            ]);
>>>>>>> a1021d9b1a416bfe73b01d5733c1d6e40893f15f
            
        } catch (\Exception $e) {
            if (isset($pdo)) {
                $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
                $pdo = null;
            }
            
            // 发生错误时也要清理缓存
            $this->clearCache();
            
            Log::error("导入失败: " . $e->getMessage());
            return "<script>alert('导入失败：" . addslashes($e->getMessage()) . "');window.location.href='/daily_record/index';</script>";
        }
    }

    /**
     * 清理缓存文件
     */
    private function clearCache()
    {
        // 清理 runtime/cache 目录
        $runtimePath = root_path() . 'runtime' . DIRECTORY_SEPARATOR . 'cache';
        $this->deleteFiles($runtimePath);
        
        // 清理 public/storage/excel 目录
        $excelPath = public_path() . 'storage' . DIRECTORY_SEPARATOR . 'excel';
        $this->deleteFiles($excelPath);
    }

    /**
     * 删除目录下的所有文件
     * @param string $path 目录路径
     */
    private function deleteFiles($path)
    {
        if (!is_dir($path)) {
            return;
        }
        
        $files = scandir($path);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                $filePath = $path . DIRECTORY_SEPARATOR . $file;
                if (is_file($filePath)) {
                    unlink($filePath);
                } elseif (is_dir($filePath)) {
                    $this->deleteFiles($filePath);
                    rmdir($filePath);
                }
            }
        }
    }
}