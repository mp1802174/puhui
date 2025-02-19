<?php
namespace app\controller;

use think\facade\Log;
use think\facade\Db;

class ImportController
{
    const BATCH_SIZE = 10000;
    
    private function getPDO()
    {
        try {
            // 测试直接MySQL连接
            $dsn = "mysql:host=127.0.0.1;port=3306;dbname=phkq;charset=utf8mb4";
            $username = "root";
            $password = "760516";
            
            $pdo = new \PDO($dsn, $username, $password, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
            ]);
            
            Log::info('直接MySQL连接测试：成功');
            return $pdo;
            
        } catch (\Exception $e) {
            Log::error('PDO连接失败：' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 简单导入测试
     */
    public function importData()
    {
        set_time_limit(0);
        Log::info('=== 开始数据同步 ===');
        
        try {
            $pdo = $this->getPDO();
            
            // 先获取原始数据数量
            $sourceCount = $pdo->query("SELECT COUNT(*) FROM daily_record")->fetchColumn();
            Log::info("原始数据数量: {$sourceCount} 条记录");
            
            // 检查重复记录
            $duplicatesSql = "SELECT 客户编号, 对公客户账号, 账户性质, COUNT(*) as count 
                              FROM daily_record 
                              GROUP BY 客户编号, 对公客户账号, 账户性质 
                              HAVING COUNT(*) > 1";
            $duplicates = $pdo->query($duplicatesSql)->fetchAll();
            $duplicateCount = count($duplicates);
            
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
            
            // 1. 导入客户基本信息
            // 获取 customer_info 表导入前的记录数
            $infoCountBefore = $pdo->query("SELECT COUNT(*) FROM customer_info")->fetchColumn();
            
            $infoSql = "INSERT INTO customer_info (
                开户日期, 客户编号, 对公客户账号, 客户名称,
                核算机构, 客户责任部门, 账户性质, 核算机构编号,
                经办人员工编号, 业务标识号,
                营销人一, 营销人二, 营销人三, 营销人四, 营销人五,
                营销人六, 营销人七, 营销人八, 营销人九, 营销人一十,
                营销人一十一, 营销人一十二,
                营销人名称一, 营销人名称二, 营销人名称三, 营销人名称四, 营销人名称五,
                营销人名称六, 营销人名称七, 营销人名称八, 营销人名称九, 营销人名称一十,
                营销人名称一十一, 营销人名称一十二,
                账户状态,
                created_at
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
                营销人名称一十一, 营销人名称一十二,
                账户状态,
                NOW() as created_at
            FROM daily_record
            ON DUPLICATE KEY UPDATE
                账户状态 = VALUES(账户状态),
                客户名称 = VALUES(客户名称),
                核算机构 = VALUES(核算机构),
                客户责任部门 = VALUES(客户责任部门),
                开户日期 = VALUES(开户日期),
                经办人员工编号 = VALUES(经办人员工编号),
                业务标识号 = VALUES(业务标识号),
                营销人一 = VALUES(营销人一),
                营销人二 = VALUES(营销人二),
                营销人三 = VALUES(营销人三),
                营销人四 = VALUES(营销人四),
                营销人五 = VALUES(营销人五),
                营销人六 = VALUES(营销人六),
                营销人七 = VALUES(营销人七),
                营销人八 = VALUES(营销人八),
                营销人九 = VALUES(营销人九),
                营销人一十 = VALUES(营销人一十),
                营销人一十一 = VALUES(营销人一十一),
                营销人一十二 = VALUES(营销人一十二),
                营销人名称一 = VALUES(营销人名称一),
                营销人名称二 = VALUES(营销人名称二),
                营销人名称三 = VALUES(营销人名称三),
                营销人名称四 = VALUES(营销人名称四),
                营销人名称五 = VALUES(营销人名称五),
                营销人名称六 = VALUES(营销人名称六),
                营销人名称七 = VALUES(营销人名称七),
                营销人名称八 = VALUES(营销人名称八),
                营销人名称九 = VALUES(营销人名称九),
                营销人名称一十 = VALUES(营销人名称一十),
                营销人名称一十一 = VALUES(营销人名称一十一),
                营销人名称一十二 = VALUES(营销人名称一十二)";
            
            $pdo->exec($infoSql);
            
            // 获取 customer_info 表导入后的记录数
            $infoCountAfter = $pdo->query("SELECT COUNT(*) FROM customer_info")->fetchColumn();
            $infoInsertCount = $infoCountAfter - $infoCountBefore; // 计算新增记录数，这里假设更新不算作新增
            $infoUpdateCount = 0; // 暂时简化更新计数，后续如果需要精确更新计数，需要更复杂的逻辑
            
            Log::info("客户基本信息导入完成: 总记录数 {$infoCountAfter} 条，新增: {$infoInsertCount} 条");
            
            // 2. 导入余额信息
            // 获取 daily_balance 表导入前的记录数
            $balanceCountBefore = $pdo->query("SELECT COUNT(*) FROM daily_balance")->fetchColumn();
            
            $balanceSql = "REPLACE INTO daily_balance (
                customer_id,
                日期,
                账户余额,
                时点存款比昨日,
                时点存款比月初,
                时点存款比年初,
                月日均存款余额,
                年日均存款余额,
                年日均存款比昨日,
                年日均存款比月初,
                年日均存款比年初,
                认定状态,
                认定日期,
                created_at
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
                r.年日均存款比昨日,
                r.年日均存款比月初,
                r.年日均存款比年初,
                r.认定状态,
                r.认定日期,
                NOW() as created_at
            FROM daily_record r
            INNER JOIN customer_info i ON
                r.客户编号 = i.客户编号 AND
                r.对公客户账号 = i.对公客户账号 AND
                r.账户性质 = i.账户性质";
            
            $balanceAffectedRows = $pdo->exec($balanceSql);
            
            // 获取 daily_balance 表导入后的记录数
            $balanceCountAfter = $pdo->query("SELECT COUNT(*) FROM daily_balance")->fetchColumn();
            $balanceInsertCount = $balanceCountAfter - $balanceCountBefore;  // 用差值计算实际新增数
            
            Log::info("余额信息导入完成: 总记录数 {$balanceCountAfter} 条，新增: {$balanceInsertCount} 条");
            
            // 3. 清空原始数据表
            $pdo->exec("INSERT INTO daily_record_bak SELECT * FROM daily_record");
            $pdo->exec("TRUNCATE TABLE daily_record");
            Log::info("清空原始数据表 daily_record");
            
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
            
            $pdo = null;
            
            // 清理缓存文件
            $this->clearCache();
            
            // 返回JSON格式的结果，包含详细计数信息
            return json([
                'code' => 1,
                'msg' => '数据同步完成！',
                'data' => [
                    'sourceCount' => $sourceCount,
                    'duplicateCount' => $duplicateCount,  // 添加重复记录数
                    'info' => [
                        'total' => $infoCountAfter,
                        'insert' => $infoInsertCount,
                        'update' => $infoUpdateCount
                    ],
                    'balance' => [
                        'total' => $balanceCountAfter,
                        'insert' => $balanceInsertCount  // 这里显示实际新增的记录数
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            if (isset($pdo)) {
                $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
                $pdo = null;
            }
            
            // 发生错误时也要清理缓存
            $this->clearCache();
            
            Log::error("导入失败: " . $e->getMessage());
            return json([
                'code' => 0,
                'msg' => '导入失败：' . $e->getMessage()
            ]);
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

    /**
     * 查询客户基本信息表记录数
     */
    public function checkCustomerCount()
    {
        try {
            $pdo = $this->getPDO();
            $sql = "SELECT COUNT(*) FROM customer_info";
            $count = $pdo->query($sql)->fetchColumn();
            
            Log::info("客户基本信息表记录数查询:\n" .
                "- 查询时间: " . date('Y-m-d H:i:s') . "\n" .
                "- 查询SQL: {$sql}\n" .
                "- 当前记录数: {$count}");
                
            $pdo = null;
            
            return json([
                'code' => 1,
                'msg' => '查询完成',
                'data' => [
                    'count' => $count,
                    'query_time' => date('Y-m-d H:i:s')
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error("查询失败: " . $e->getMessage());
            return json([
                'code' => 0,
                'msg' => '查询失败：' . $e->getMessage()
            ]);
        }
    }

    /**
     * 启动异步监控
     */
    private function startMonitoring()
    {
        // 使用后台进程执行监控
        $cmd = "php " . root_path() . "public/index.php /import/monitorCustomerInfo > /dev/null 2>&1 &";
        exec($cmd);
        Log::info("监控进程已启动");
    }

    /**
     * 监控客户基本信息表记录数
     */
    public function monitorCustomerInfo()
    {
        try {
            $pdo = $this->getPDO();
            Log::info("=== 开始监控客户基本信息表记录数 ===");
            
            for ($i = 1; $i <= 10; $i++) {
                $sql = "SELECT COUNT(*) FROM customer_info";
                $count = $pdo->query($sql)->fetchColumn();
                
                Log::info("第{$i}次查询:\n" .
                    "- 执行SQL: {$sql}\n" .
                    "- 查询时间: " . date('Y-m-d H:i:s') . "\n" .
                    "- 记录数: {$count}");
                    
                if ($i < 10) {
                    sleep(10);
                }
            }
            
            Log::info("=== 监控结束 ===");
            $pdo = null;
            
        } catch (\Exception $e) {
            Log::error("监控失败: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 全面检查数据库对象
     */
    public function checkDatabaseObjects()
    {
        try {
            $pdo = $this->getPDO();
            Log::info("=== 开始全面检查数据库对象 ===");
            
            // 1. 检查事件
            Log::info("检查数据库事件：");
            $events = $pdo->query("SHOW EVENTS WHERE db = 'phkq'")->fetchAll(\PDO::FETCH_ASSOC);
            if (empty($events)) {
                Log::info("- 未发现任何事件");
            } else {
                foreach ($events as $event) {
                    Log::info("- 发现事件：" . json_encode($event, JSON_UNESCAPED_UNICODE));
                }
            }
            
            // 2. 检查表结构
            Log::info("\n检查customer_info表结构：");
            $tableInfo = $pdo->query("SHOW CREATE TABLE customer_info")->fetch(\PDO::FETCH_ASSOC);
            Log::info($tableInfo['Create Table']);
            
            // 3. 检查触发器
            Log::info("\n检查表相关触发器：");
            $triggers = $pdo->query("SHOW TRIGGERS WHERE `Table` = 'customer_info'")->fetchAll(\PDO::FETCH_ASSOC);
            if (empty($triggers)) {
                Log::info("- 未发现任何触发器");
            } else {
                foreach ($triggers as $trigger) {
                    Log::info("- 发现触发器：" . json_encode($trigger, JSON_UNESCAPED_UNICODE));
                }
            }
            
            // 4. 检查存储过程
            Log::info("\n检查数据库存储过程：");
            $procedures = $pdo->query("SHOW PROCEDURE STATUS WHERE db = 'phkq'")->fetchAll(\PDO::FETCH_ASSOC);
            if (empty($procedures)) {
                Log::info("- 未发现任何存储过程");
            } else {
                foreach ($procedures as $proc) {
                    Log::info("- 发现存储过程：" . json_encode($proc, JSON_UNESCAPED_UNICODE));
                }
            }
            
            // 5. 检查外键关系
            Log::info("\n检查表的外键关系：");
            $foreignKeys = $pdo->query("
                SELECT 
                    TABLE_NAME,
                    COLUMN_NAME,
                    CONSTRAINT_NAME,
                    REFERENCED_TABLE_NAME,
                    REFERENCED_COLUMN_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE 
                    REFERENCED_TABLE_SCHEMA = 'phkq' AND 
                    (TABLE_NAME = 'customer_info' OR REFERENCED_TABLE_NAME = 'customer_info')
            ")->fetchAll(\PDO::FETCH_ASSOC);
            
            if (empty($foreignKeys)) {
                Log::info("- 未发现相关外键");
            } else {
                foreach ($foreignKeys as $fk) {
                    Log::info("- 发现外键关系：" . json_encode($fk, JSON_UNESCAPED_UNICODE));
                }
            }
            
            // 6. 开始实时监控
            Log::info("\n开始监控表记录变化（持续30秒）：");
            for ($i = 1; $i <= 30; $i++) {
                $count = $pdo->query("SELECT COUNT(*) FROM customer_info")->fetchColumn();
                $processCount = $pdo->query("
                    SELECT COUNT(*) 
                    FROM information_schema.processlist 
                    WHERE db = 'phkq' AND Command != 'Sleep'
                ")->fetchColumn();
                
                Log::info("第{$i}秒检查:\n" .
                    "- 时间: " . date('Y-m-d H:i:s') . "\n" .
                    "- 记录数: {$count}\n" .
                    "- 活动进程数: {$processCount}");
                    
                if ($processCount > 1) {
                    $processes = $pdo->query("
                        SELECT Id, User, Host, Command, Time, State, Info
                        FROM information_schema.processlist 
                        WHERE db = 'phkq' AND Command != 'Sleep'
                    ")->fetchAll(\PDO::FETCH_ASSOC);
                    Log::info("活动进程详情：" . json_encode($processes, JSON_UNESCAPED_UNICODE));
                }
                
                sleep(1);
            }
            
            Log::info("=== 检查完成 ===");
            $pdo = null;
            
            return json([
                'code' => 1,
                'msg' => '检查完成，请查看日志文件'
            ]);
            
        } catch (\Exception $e) {
            Log::error("检查失败: " . $e->getMessage());
            return json([
                'code' => 0,
                'msg' => '检查失败：' . $e->getMessage()
            ]);
        }
    }

    /**
     * 详细检查客户信息表
     */
    public function checkCustomerDetail()
    {
        try {
            $pdo = $this->getPDO();
            Log::info("=== 开始详细检查客户信息表 ===");
            
            // 1. COUNT(*) 查询
            $countAll = $pdo->query("SELECT COUNT(*) FROM customer_info")->fetchColumn();
            Log::info("COUNT(*) 查询结果: {$countAll}");
            
            // 2. 查询最大ID
            $maxId = $pdo->query("SELECT MAX(ID) FROM customer_info")->fetchColumn();
            Log::info("最大ID值: {$maxId}");
            
            // 3. 按ID统计
            $countById = $pdo->query("SELECT COUNT(ID) FROM customer_info")->fetchColumn();
            Log::info("COUNT(ID) 查询结果: {$countById}");
            
            // 4. 检查是否有ID间隔
            $sql = "SELECT 
                MIN(id) as start_id,
                MAX(id) as end_id,
                COUNT(*) as total,
                MAX(id) - MIN(id) + 1 as expected_total,
                MAX(id) - MIN(id) + 1 - COUNT(*) as missing_count
            FROM customer_info";
            
            $gapInfo = $pdo->query($sql)->fetch(\PDO::FETCH_ASSOC);
            Log::info("ID区间分析:\n" . json_encode($gapInfo, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            
            // 5. 如果发现差异，查找具体的ID间隔
            if ($gapInfo['missing_count'] > 0) {
                $sql = "
                    WITH RECURSIVE numbers AS (
                        SELECT {$gapInfo['start_id']} as num
                        UNION ALL
                        SELECT num + 1
                        FROM numbers
                        WHERE num < {$gapInfo['end_id']}
                    )
                    SELECT num as missing_id
                    FROM numbers
                    WHERE num NOT IN (SELECT id FROM customer_info)
                    LIMIT 10";
                    
                $missingIds = $pdo->query($sql)->fetchAll(\PDO::FETCH_COLUMN);
                Log::info("缺失的ID示例（前10个）: " . json_encode($missingIds));
            }
            
            $pdo = null;
            
            return json([
                'code' => 1,
                'msg' => '检查完成，请查看日志'
            ]);
            
        } catch (\Exception $e) {
            Log::error("检查失败: " . $e->getMessage());
            return json([
                'code' => 0,
                'msg' => '检查失败：' . $e->getMessage()
            ]);
        }
    }

    /**
     * 多维度查询比较
     */
    public function compareQueries()
    {
        try {
            $pdo = $this->getPDO();
            Log::info("=== 开始多维度查询比较 ===");
            
            // 1. 常规COUNT查询
            $count1 = $pdo->query("SELECT COUNT(*) FROM customer_info")->fetchColumn();
            Log::info("COUNT(*) 结果: {$count1}");
            
            // 2. 分页方式获取总数
            $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM customer_info LIMIT 1";
            $pdo->query($sql);
            $count2 = $pdo->query("SELECT FOUND_ROWS()")->fetchColumn();
            Log::info("SQL_CALC_FOUND_ROWS 结果: {$count2}");
            
            // 3. 按主键统计
            $count3 = $pdo->query("SELECT COUNT(ID) FROM customer_info")->fetchColumn();
            Log::info("COUNT(ID) 结果: {$count3}");
            
            // 4. 获取ID范围
            $idRange = $pdo->query("
                SELECT 
                    MIN(ID) as min_id,
                    MAX(ID) as max_id,
                    MAX(ID) - MIN(ID) + 1 as id_range,
                    COUNT(*) as actual_count
                FROM customer_info
            ")->fetch(\PDO::FETCH_ASSOC);
            
            Log::info("ID范围分析:\n" . 
                "- 最小ID: {$idRange['min_id']}\n" .
                "- 最大ID: {$idRange['max_id']}\n" .
                "- ID范围: {$idRange['id_range']}\n" .
                "- 实际记录数: {$idRange['actual_count']}");
                
            // 5. 检查第一条和最后一条记录
            $firstRecord = $pdo->query("
                SELECT ID, 客户编号, 对公客户账号, 账户性质
                FROM customer_info
                ORDER BY ID ASC LIMIT 1
            ")->fetch(\PDO::FETCH_ASSOC);
            
            $lastRecord = $pdo->query("
                SELECT ID, 客户编号, 对公客户账号, 账户性质
                FROM customer_info
                ORDER BY ID DESC LIMIT 1
            ")->fetch(\PDO::FETCH_ASSOC);
            
            Log::info("首条记录:\n" . json_encode($firstRecord, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            Log::info("末条记录:\n" . json_encode($lastRecord, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            
            // 6. 随机抽取中间的一条记录
            $midId = (int)(($idRange['max_id'] + $idRange['min_id']) / 2);
            $midRecord = $pdo->query("
                SELECT ID, 客户编号, 对公客户账号, 账户性质
                FROM customer_info
                WHERE ID >= {$midId}
                LIMIT 1
            ")->fetch(\PDO::FETCH_ASSOC);
            
            Log::info("中间记录:\n" . json_encode($midRecord, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            
            $pdo = null;
            
            return json([
                'code' => 1,
                'msg' => '比较完成，请查看日志'
            ]);
            
        } catch (\Exception $e) {
            Log::error("比较失败: " . $e->getMessage());
            return json([
                'code' => 0,
                'msg' => '比较失败：' . $e->getMessage()
            ]);
        }
    }

    public function allocation()
    {
        try {
            Log::info("=== 开始分配营销人 ===");
            $pdo = $this->getPDO();
            $pdo->beginTransaction();

            // 获取所有customer_info记录（建议后续改为分页处理）
            $customers = $pdo->query("SELECT id, 
                营销人名称一, 营销人名称二, 营销人名称三, 营销人名称四, 营销人名称五, 
                营销人名称六, 营销人名称七, 营销人名称八, 营销人名称九, 营销人名称一十, 
                营销人名称一十一, 营销人名称一十二 
                FROM customer_info")->fetchAll(\PDO::FETCH_ASSOC);

            // 修改预加载查询（正确获取二维数组结构）
            $existingData = $pdo->query("SELECT customer_id, marketer_index, marketer_ratio, remark 
                                       FROM customer_marketer")
                                ->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE);

            // 或者更明确的处理方式：
            $existingData = [];
            $rawData = $pdo->query("SELECT customer_id, marketer_index, marketer_ratio, remark 
                                  FROM customer_marketer")
                         ->fetchAll(\PDO::FETCH_ASSOC);
                         
            foreach ($rawData as $row) {
                $existingData[$row['customer_id']][$row['marketer_index']] = $row;
            }

            // 优化UPSERT语句（使用更高效的索引匹配）
            $upsertStmt = $pdo->prepare("INSERT INTO customer_marketer 
                (customer_id, marketer_name, marketer_ratio, marketer_index, remark) 
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                marketer_ratio = IF(ABS(VALUES(marketer_ratio) - marketer_ratio) > 0.0001, VALUES(marketer_ratio), marketer_ratio),
                remark = VALUES(remark)");

            $insertCount = 0;
            $updateCount = 0;

            foreach ($customers as $customer) {
                $totalRatio = 0;
                $customerMarketers = [];

                // 处理12个营销人字段
                for ($i = 1; $i <= 12; $i++) {
                    // 修正字段名生成逻辑
                    $fieldName = match($i) {
                        1 => '营销人名称一',
                        2 => '营销人名称二',
                        3 => '营销人名称三',
                        4 => '营销人名称四',
                        5 => '营销人名称五',
                        6 => '营销人名称六',
                        7 => '营销人名称七',
                        8 => '营销人名称八',
                        9 => '营销人名称九',
                        10 => '营销人名称一十',
                        11 => '营销人名称一十一',
                        12 => '营销人名称一十二',
                        default => ''
                    };

                    $value = $customer[$fieldName] ?? '';

                    if (!empty($value)) {
                        // 增强正则匹配（支持中文冒号、空格等）
                        if (preg_match('/-([\p{Han}a-zA-Z\s]+)[:：](\d+)%?/u', $value, $matches)) {
                            $name = trim($matches[1]);
                            $ratio = floatval($matches[2]);
                            $totalRatio += $ratio;

                            $customerMarketers[] = [
                                'name' => $name,
                                'ratio' => $ratio / 100, // 转换为小数
                                'index' => $i
                            ];
                        }
                    }
                }

                // 修改后的比例验证逻辑
                $baseRemark = "总分配比例: {$totalRatio}%";
                if ($totalRatio != 100) {
                    $ratioRemark = $baseRemark . "（比例异常）";
                    Log::warning("客户ID {$customer['id']} 营销人比例异常：当前总比例 {$totalRatio}%");
                } else {
                    $ratioRemark = $baseRemark;
                }

                // 在循环内部优化判断逻辑：
                foreach ($customerMarketers as $marketer) {
                    $key = $customer['id'] . '_' . $marketer['index'];
                    
                    // 直接通过唯一索引判断是否存在
                    if (isset($existingData[$customer['id']][$marketer['index']])) {
                        $old = $existingData[$customer['id']][$marketer['index']];
                        // 只有比例变化超过0.01%时才更新
                        if (abs($marketer['ratio'] - $old['marketer_ratio']) < 0.0001 
                            && $ratioRemark == $old['remark']) {
                            continue; // 跳过无变化的记录
                        }
                    }
                    
                    $upsertStmt->execute([
                        $customer['id'],
                        $marketer['name'],
                        $marketer['ratio'],
                        $marketer['index'],
                        $ratioRemark
                    ]);
                    
                    // 简化计数逻辑
                    $upsertStmt->rowCount() === 1 ? $insertCount++ : $updateCount++;
                }

                if (($insertCount + $updateCount) % 1000 === 0) {
                    $pdo->commit();
                    $pdo->beginTransaction();
                }
            }

            $pdo->commit();
            Log::info("营销人分配完成，新增：{$insertCount}，更新：{$updateCount}");

            return "<script>
                alert('营销人分配完成！\\n新增记录：{$insertCount}条\\n更新记录：{$updateCount}条');
                window.location.href = '/daily_record/index';
            </script>";

        } catch (\Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Log::error("营销人分配失败：" . $e->getMessage());
            return "<script>
                alert('分配失败：" . addslashes(str_replace(["\r","\n"], '', $e->getMessage())) . "');
                window.location.href = '/daily_record/index';
            </script>";
        }
    }

}










